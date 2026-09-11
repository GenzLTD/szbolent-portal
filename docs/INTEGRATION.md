# Looma 门户集成文档

> 门户 git 仓：[szpeter2026/szbolent-portal](https://github.com/szpeter2026/szbolent-portal)  
> 技术栈：Vue 3 + Vite + Pinia  
> 维护联系人：Jamie (szpeter)  
> **最后更新：** 2026-07-20（Plan A + Plan B 收口）

---

## L1 · 架构图

```
┌─────────────────────────────────────────────────────┐
│                   szbolent-portal                    │
│               (Vite dev :3000 / 构建产物)              │
│  http://localhost:3000                               │
│  vite.config.ts: proxy 三路后端                        │
│                                                      │
│  ◄── /v1/*       ▸ Looma :5200       (诗词/RAG/认证)   │
│  ◄── /v1/menus   ▸ Page Engine :5300 (动态菜单/路由)   │
│  ◄── /wp-json/*  ▸ WordPress :8800   (博客 CMS·本地)  │
│                                                      │
│  前端层：                                              │
│    ├─ Header/Footer   ← Page Engine menus API        │
│    ├─ router          ← await menusInit + 动态注册    │
│    ├─ Poetry          ← Looma v1/poetry/* + v1/ask   │
│    ├─ ChatDialog      ← Looma auth + consent + RAG   │
│    ├─ Blog            ← WordPress /wp-json/*          │
│    └─ Cases/Study     ← router 重定向                 │
└─────────────────────────────────────────────────────┘
```

## L2 · 接口清单

| # | 接口 | 方法 | 路径 | 端口 | 状态 |
|---|------|------|------|------|------|
| 1 | 诗词列表 | GET | `/v1/poetry/poems` | :5200 | ✅ |
| 2 | 诗人列表 | GET | `/v1/poetry/poets` | :5200 | ✅ |
| 3 | 诗词详情 | GET | `/v1/poetry/poems/by-id/{id}/with-verses` | :5200 | ✅ |
| 4 | 诗人详情 | GET | `/v1/poetry/poets/by-id/{id}/with-poems` | :5200 | ✅ |
| 5 | 诗词搜索 | GET | `/v1/poetry/search?q={query}` | :5200 | ✅ |
| **6** | **RAG AI 问答** | **POST** | **`/v1/ask`** | **:5200** | **✅** |
| **7** | **用户登录** | **POST** | **`/v1/auth/login`** | **:5200** | **✅** |
| **8** | **用户信息** | **GET** | **`/v1/auth/profile`** | **:5200** | **✅** |
| **9** | **同意授权** | **POST** | **`/v1/compliance/consent/grant`** | **:5200** | **✅** |
| 10 | 动态菜单 | GET | `/v1/menus?product=szbolent` | :5300 | ✅ |
| 11 | 产品列表 | GET | `/v1/products?code=szbolent` | :5300 | ✅ |
| 12 | 页面数据 | GET | `/v1/pages/{slug}` | :5300 | ✅ |
| 13 | WordPress 代理 | GET | `/wp-json/wp/v2/posts` | :8800（本地） | ✅ |
| 14 | WordPress 分类 | GET | `/wp-json/wp/v2/categories` | :8800（本地） | ✅ |

> **端口真源：** 本地 WP = `:8800`（`vite.config.ts` + `docker-compose.yml`）。  
> 生产 `docker-compose.wp.prod.yml` 绑定 `127.0.0.1:8080` 仅供本机 Nginx 反代，**不要**用 :8080 做本地冒烟。

---

## L3 · chatsdialog 全链路

```
用户点击 💬 → ChatDialog 展开
  │
  ├─ 未认证 → 邮箱+密码登录
  │    POST /v1/auth/login { email, password }
  │    ← { access_token }  → localStorage
  │    → 自动刷新用户上下文 → 进下一步
  │
  ├─ 已认证 未授权 ask_rag → 同意页
  │    POST /v1/compliance/consent/grant { scope: "ask_rag" }
  │    ← { already_granted: false/true, consent_id }
  │    → 本地缓存 → 进入聊天模式
  │
  └─ 已认证 已授权 → 聊天模式
       POST /v1/ask { query, top_k? }
       ← { answer, intent, intent_confidence, extracted?, sources[], tokens_used }

  自愈逻辑：
    ┌─ 401 → 清 token → 回登录屏
    └─ CONSENT_REQUIRED (action="grant_consent") → 自动 re-grant → 重试问卷
```

---

## L4 · ask API 详情

**端点：** `POST /v1/ask`

**请求：**
```json
{
  "query": "李白是谁",
  "top_k": 3
}
```

**响应：**
```json
{
  "answer": "李白墓 · 白居易（唐）\n可憐荒壠窮泉骨，曾有驚天動地文。\n悼念李白，赞其才华与命运多舛",
  "intent": "poetry",
  "intent_confidence": 0.95,
  "extracted": {
    "title": "李白墓",
    "author": "白居易",
    "dynasty": "唐",
    "content": "可憐荒壠窮泉骨，曾有驚天動地文...",
    "theme": "悼念"
  },
  "sources": [
    {
      "title": "李白墓",
      "author": "白居易",
      "content_snippet": "可憐荒壠窮泉骨...",
      "score": 0.92
    }
  ],
  "tokens_used": 156
}
```

**前置条件：** 用户需先授权 `ask_rag` scope（`POST /v1/compliance/consent/grant`）

**状态流转：**
- 200 → 正常回答
- 401 → 未登录 / token 过期
- 200 + `action: "grant_consent"` → 需要先授权 ask_rag

---

## L5 · Page Engine 路由闭环

Page Engine (:5300) 管理动态菜单和路由注入：

```typescript
// main.ts — 先拿菜单，再挂路由
const menus = await pageEngineApi.getMenus('szbolent')
const dynamicRoutes = menusToRoutes(menus)  // blog, careers, cases 等
router.addRoute(dynamicRoutes)
app.use(router)
app.mount('#app')

// Header/Footer 同源 → Page Engine menus API
// /cases → /case-study → 路由 redirect
```

---

## L6 · 编码约定

| 类别 | 约定 |
|------|------|
| API 命名 | `apiPost<T>('/path', body)` / `apiGet<T>('/path', params)` |
| token 存储 | `localStorage['looma_token']`，axios 拦截器自动带 `Authorization: Bearer` |
| 错误处理 | axios 拦截器统一处理 401（清 token），业务层按 `e.code`/`e.response.status` 分流 |
| chat 状态机 | `COLLAPSED → LOGIN → CONSENT → CHAT`，每步有独立 UI |
| 响应式 | ChatDialog `position:fixed`，移动端自适应 |

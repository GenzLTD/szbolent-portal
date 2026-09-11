# 溯源与边界

> **叙事线：** `bolent-content`  
> **交付目录：** `/Users/jason/THOMAS/variety`  
> **SurfaceZervi 角色：** 博物馆 + MANIFEST 登记，非日常改代码入口

## 继承关系

```
archive-gitee/szbenyx/bolent/web-public   ← 2026-07-03 fork 基线
        ↓
/Users/jason/THOMAS/variety       ← 本工程（www.szbolent.cn canonical；API 走 api.genz.ltd）
        ↓ 内容 / API / AI
├── GitHub/szjason72/Poetry-modown         ← WordPress headless CMS
├── /Users/jason/Projects/looma-zervi      ← Looma API（诗词 + RAG + 用户 + 支付）
│   ├── backend/src/db/manager.py          ← SQLite poems 表
│   ├── backend/src/rag/chroma_client.py   ← ChromaDB 向量检索（含诗词专用）
│   ├── backend/src/agents/poetry_search.py ← 诗词语义搜索
│   └── backend/src/api/routes/poetry_routes.py ← /v1/poetry/* HTTP 端点
└── GitHub/szjason72/poetries-of-bolent    ← 战略决策索引
```

**历史说明：**
- `archive-gitee/szbenyx/bolent/backend`（legacy Sanic :8001）→ 已由 Looma API 替代
- `gitea-repos/planetx-tatha`（poetry_rag）→ 已融入 Looma `backend/src/rag/`，不再独立部署

## 当前架构（2026-07 审阅确认）

```
szbolent-portal (Vue3 + Vite, 纯前端 SPA)
    ├── HTTP → Looma API (:5200)
    │   ├── /v1/poetry/*      ← 诗词浏览、搜索（SQLite + ChromaDB）
    │   ├── /v1/auth/*        ← JWT 认证
    │   ├── /v1/payment/*     ← 支付（P1 Stub → 微信支付）
    │   └── /v1/ask           ← 通用 RAG 问答
    └── HTTP → WordPress REST API (:8800)
        └── /wp-json/wp/v2/*  ← 博客内容
```

## 勿混淆

| 不要 | 应该 |
|------|------|
| 在 SurfaceZervi 根目录再开 Bolent 代码副本 | 只在本仓与 Poetry-modown 改代码 |
| 把本门户写进 JobFirst 首屏 | Bolent 与求职主线分离 |
| 用 `szbolent-frontend` 当门户壳 | 本仓已继承 `web-public` 门户结构 |
| 把 Tatha 当独立品牌站后端 | Tatha RAG 已完全融入 Looma（`backend/src/rag/`），无需独立部署 |
| 部署 legacy bolent Sanic :8001 | 诗词数据走 Looma `/v1/poetry/*` |

## MANIFEST

SurfaceZervi `MANIFEST.yaml` → `id: szbolent-portal`

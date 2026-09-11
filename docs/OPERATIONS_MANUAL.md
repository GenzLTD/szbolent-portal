## 运营维护手册（szbolent-portal）

> 维护联系人：Jamie (szpeter)  
> 最后更新：2026-08-11（WP 本地端口真源统一为 :8800）

### 1. 系统架构

```
用户浏览器
    │
    ├── 前端：szbolent-portal (Vue 3 + Vite)
    │   端口：:3000 (dev) / 构建产物 (prod)
    │
    ├── 动态内容：Page Engine
    │   端口：:5300 → /v1/menus, /v1/products, /v1/pages/*
    │
    ├── 业务 API：Looma
    │   端口：:5200 → /v1/poetry/*, /v1/ask, /v1/auth/*
    │
    └── CMS 内容：WordPress（可选博客）
        本地：:8800 → /wp-json/*   ← 真源：vite.config.ts + docker-compose.yml
        生产 compose：127.0.0.1:8080（仅本机，经 Nginx 反代，勿与本地冒烟端口混淆）
```

### 2. 数据流

```
门户加载
  ├─ main.ts             → await menusInit()
  │   ├─ GET /v1/menus?product=szbolent  → Page Engine :5300
  │   └─ 动态路由注入 (blog, careers, case-study 等)
  │
  ├─ Header.vue          → GET /v1/menus?product=szbolent  → Page Engine :5300
  ├─ Footer.vue          → GET /v1/products?code=szbolent  → Page Engine :5300
  │
  ├─ 诗词模块             → Looma :5200
  │   ├─ List.vue / Detail.vue / Poet*
  │   └─ ChatDialog.vue  → POST /v1/ask, /v1/auth/login, /v1/compliance/consent/grant
  │
  └─ 博客模块             → WordPress :8800（本地）
      └─ Blog / BlogDetail → GET /wp-json/wp/v2/posts
```

### 3. 启动命令

重启时，严格按照以下顺序执行：

```bash
# ─── 服务层 ───

# 1. Looma 后端（诗词/RAG/认证 :5200）
cd /Users/jason/Projects/looma-zervi/backend && ./dev.sh
# 验证：
curl -s "http://localhost:5200/v1/poetry/browse?per_page=1"

# 2. Page Engine（动态菜单/路由 :5300）
cd /Users/jason/THOMAS/variety/backend && cargo run
# 验证：
curl -s "http://localhost:5300/v1/menus?product=szbolent"

# 3. WordPress（博客 CMS · 本地 :8800）
cd /Users/jason/THOMAS/variety
docker compose -f docker-compose.yml up -d
# 验证：
curl -s "http://localhost:8800/wp-json/wp/v2/posts?per_page=1"

# ─── 前端层 ───

# 4. szbolent-portal（Vite :3000）
cd /Users/jason/THOMAS/variety && npm run dev
```

本地四端口冒烟：

```bash
bash scripts/smoke-local.sh
```

### 4. 健康检查脚本

```bash
#!/bin/bash
# verify-all.sh — 全服务状态验证（本地端口以 :8800 为准）
set -e

echo "=== Looma :5200 ==="
curl -s -o /dev/null -w "auth/profile: %{http_code}\n" http://localhost:5200/v1/auth/profile
curl -s -o /dev/null -w "poetry:      %{http_code}\n" "http://localhost:5200/v1/poetry/browse?per_page=1"
curl -s -o /dev/null -w "ask (POST):  %{http_code}\n" http://localhost:5200/v1/ask \
  -X POST -H "Content-Type: application/json" -d '{"query":"test"}'

echo "=== Page Engine :5300 ==="
curl -s -o /dev/null -w "menus:       %{http_code}\n" "http://localhost:5300/v1/menus?product=szbolent"
curl -s -o /dev/null -w "products:    %{http_code}\n" "http://localhost:5300/v1/products?code=szbolent"

echo "=== WordPress :8800（本地）==="
curl -s -o /dev/null -w "wp posts:    %{http_code}\n" "http://localhost:8800/wp-json/wp/v2/posts?per_page=1"

echo "=== Portal :3000 ==="
curl -s -o /dev/null -w "portal:      %{http_code}\n" http://localhost:3000/
curl -s -o /dev/null -w "poetry:      %{http_code}\n" http://localhost:3000/poetry
curl -s -o /dev/null -w "blog:        %{http_code}\n" http://localhost:3000/blog
```

### 5. 故障排查清单

| 现象 | 检查项 | 排查命令 |
|------|--------|---------|
| 诗词页为空 | Looma :5200 存活？ | `curl "http://localhost:5200/v1/poetry/browse?per_page=1"` |
| 菜单/Header异常 | Page Engine :5300 存活？ | `curl "http://localhost:5300/v1/menus?product=szbolent"` |
| 博客页空白 / 「内容暂不可用」 | WordPress :8800 + MySQL 健康？ | `curl "http://localhost:8800/wp-json/wp/v2/posts?per_page=1"`；`docker compose -f docker-compose.yml ps` |
| ChatDialog 不出现 | Vite 编译正常？ | `cat /tmp/vite*.log \| tail -20` |
| 登录失败 | Looma auth 端点？ | `curl -X POST http://localhost:5200/v1/auth/login -H 'Content-Type: application/json' -d '{"email":"beta_admin@looma.test","password":"looma123"}'` |
| AI 回答为空 | token/consent 有效？ | 检查 localStorage `looma_token` + `looma_consent_ask_rag` |
| consent 授权失败 | 路径是否正确？ | 应调用 `/v1/compliance/consent/grant`，非 `/v1/consent/grant` |

### 6. 日常检查命令

```bash
# 快速检查所有端口（本地）
lsof -i :5200 -i :5300 -i :8800 -i :3000 2>/dev/null | grep LISTEN

# 检查 Vite 错误
tail -20 /tmp/vite*.log

# 查看最近 Playwright 控制台日志
ls -lt .playwright-cli/console-*.log | head -1 | xargs cat

# 清理 + 硬重启门户
kill $(lsof -ti :3000) 2>/dev/null
cd /Users/jason/THOMAS/variety && npx vite --port 3000 > /tmp/vite.log 2>&1 &
```

### 7. 系统检查列表

| 任务 | 频率 | 命令 |
|------|------|------|
| Portal 页面可访问 | 每次部署后 | `curl -s -o /dev/null -w '%{http_code}' http://localhost:3000/` |
| 诗词数据加载 | 每次部署后 | `curl -s "http://localhost:5200/v1/poetry/browse?per_page=1"` |
| 鉴权服务状态 | 每天 | `curl -s -o /dev/null http://localhost:5200/v1/auth/profile` |
| **Page Engine 菜单** | **每天** | `curl -s "http://localhost:5300/v1/menus?product=szbolent"` |
| **RAG ask 端点** | **每天** | `curl -s http://localhost:5200/v1/ask -X POST -H 'Content-Type: application/json' -d '{"query":"test"}'` |
| WordPress 可用（本地） | 每次联调前 | `curl -s "http://localhost:8800/wp-json/wp/v2/posts?per_page=1"` |
| 前端 js 文件引用 | 每次部署后 | 检查 `/assets/` 目录所有 js 文件可加载 |

### 8. 测试账号

| 账号 | 用途 |
|------|------|
| `beta_admin@looma.test` / `looma123` | Looma 管理账号，含 ask_rag 授权 |

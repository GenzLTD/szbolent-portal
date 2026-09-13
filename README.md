# szbolent-portal

> **www.szbolent.cn** 智能化诗词门户 — 日常工程交付目录  
> **叙事线：** `bolent-content`（与 JobFirst 分离）

继承自 [SurfaceZervi/archive-gitee/szbenyx/bolent/web-public](file:///Users/jason/SurfaceZervi/archive-gitee/szbenyx/bolent/web-public)，集成 WordPress headless、深伯乐 诗词 API 与可选 Tatha `poetry_rag`。

## 架构

```
szbolent-portal (Vue3 + Vite)     ← 本仓 · :3000
    ├── Looma API                 ← looma-zervi · :5200
    │   ├── /v1/poetry/*          ← 诗词浏览、搜索（SQLite + ChromaDB）
    │   ├── /v1/ask               ← 通用 RAG 问答（原 Tatha RAG 已融入）
    │   ├── /v1/auth/*            ← JWT 认证
    │   └── /v1/payment/*         ← 支付（P1）
    └── WordPress REST API        ← Poetry-modown · :8800
```

> **注意：** 原 Tatha RAG（planetx-tatha poetry_rag）已完全融入 Looma `backend/src/rag/`，不再需要独立部署。Legacy bolent Sanic `:8001` 已由 Looma API 替代。

## 快速开始

```bash
cd /Users/jason/Projects/szbolent-portal
cp .env.example .env.local
npm install
npm run dev
```

访问 http://localhost:3000

## 文档

| 文件 | 说明 |
|------|------|
| [docs/DUAL_REPO_WORK_GUIDE.md](./docs/DUAL_REPO_WORK_GUIDE.md) | **双仓协作工作指引（与 looma-zervi 同文同步）** |
| [docs/PROJECTS_DIRECTORY_AUDIT.md](./docs/PROJECTS_DIRECTORY_AUDIT.md) | **Projects 目录勘查记录（2026-07-03 · 暂留未删）** |
| [docs/TENCENT_CLOUD_COMMERCE.md](./docs/TENCENT_CLOUD_COMMERCE.md) | **腾讯云备案 / 支付 / P0–P2（与 looma-zervi 同步）** |
| [docs/LINEAGE.md](./docs/LINEAGE.md) | 溯源与边界 |
| [docs/INTEGRATION.md](./docs/INTEGRATION.md) | WP / API / Tatha 联调 |
| [docs/OPERATIONS_MANUAL.md](./docs/OPERATIONS_MANUAL.md) | **运维手册（部署/排障/备份/验证）** |
| [docs/ARCHITECTURE_DECISION_MEMO.md](./docs/ARCHITECTURE_DECISION_MEMO.md) | 架构决策备忘（Composable 门户边界） |
| [docs/COMPOSABLE_PORTAL_LEARNING.md](./docs/COMPOSABLE_PORTAL_LEARNING.md) | **Composable 门户学习清单（对标案例）** |
| [docs/SITE_POSITIONING_MEMO.md](./docs/SITE_POSITIONING_MEMO.md) | **建站定位备忘（文化智能 / 行业信任 / 双线）** |
| [docs/GROWTH_PORTAL_NAMING.md](./docs/GROWTH_PORTAL_NAMING.md) | **增长型门户称谓 + §8 适配清单**（基线 `6df73a2`：主 CTA 只落 PlanetX，ref 归因冻结） |
| [SOURCE.json](./SOURCE.json) | 机器可读来源登记 |

## SurfaceZervi 对照

| 用途 | 路径 |
|------|------|
| 战略决策 | `SurfaceZervi/GitHub/szjason72/poetries-of-bolent` |
| WordPress | `SurfaceZervi/GitHub/szjason72/Poetry-modown` |
| 基线来源 | `SurfaceZervi/archive-gitee/szbenyx/bolent/web-public` |
| MANIFEST | `SurfaceZervi/MANIFEST.yaml` → `szbolent-portal` |

## 脚本

```bash
npm run dev      # 开发
npm run build    # 生产构建 → dist/
npm run preview  # 预览构建
npm run deploy   # 部署到 47.115.168.107
npm run verify   # 运维验证脚本（bash scripts/verify.sh）
```

## License

MIT

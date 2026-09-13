# 入口契约（entry contract）

**`entry.json` 是本仓入口分流的唯一真源。**

vite 开发代理、生产 nginx 配置、冒烟脚本全部由它驱动。此前散落的 `nginx.conf`、`scripts/nginx-wp-aliyun.conf`（内容重复）、`vite.config.ts` 里的手写 proxy 表——同一份规则写四遍、改一处漏三处，是生产事故的温床。

## 结构

| 字段 | 作用 |
|---|---|
| `dev` | 开发端口 / host |
| `upstreams` | 上游服务定义：`dev` / `prod` 目标、环境变量覆盖名、Host 头策略 |
| `routes` | 路径前缀 → 上游。**顺序即匹配优先级**，兜底前缀必须最后 |
| `sites` | 生产 nginx 站点：`portal`（门户入口）、`api`（Looma API 入口） |

## 用法

```bash
# 生成 nginx 配置（产物：nginx.conf、nginx-api.genz.ltd.conf）
node scripts/gen-entry.mjs

# 校验产物是否与契约同步（CI 用，不同步则退出码 1）
node scripts/gen-entry.mjs --check

# 冒烟（读取契约，逐条验分流）
bash scripts/smoke.sh
```

## 约束

- 生成物首行标注 `GENERATED`，**不要手改**，改契约后重新生成。
- 新增后端分流：改 `routes` + `upstreams`，然后重跑生成器 + 冒烟。
- `routes` 里 `/v1` 是兜底项，Page Engine 的三条 `/v1/*` 必须排在它前面。

## 站点切换（variant 机制）

`sites.portal.defaultLocation.mode` 控制生产 `location /` 的去向：

- `upstream` + `wordpress` → 现状：`szbolent.cn` 为 WP 内容站
- `static` + `staticRoot` → 门户 SPA 产物接管（`try_files ... /index.html`）

这是「一个契约、多变体」的落点：门户上线切换不改代码，只改契约一个字段再重生成。

## 站点登记 vs 渲染

`sites` 里每个 key 有两种状态：

| 状态 | 判定 | 生成器行为 | L1 断言 |
|---|---|---|---|
| **渲染** | 未标 `render: false` | 按 key 分派 renderer（`portal` / `api`）生成产物 | 产物必须与契约逐字一致 |
| **仅登记** | `render: false` + `note` | 跳过渲染，只打印一行登记信息 | `file` 指向的参考副本必须存在 |

含 SSL/ACME 的站点（443 与 301 段由 certbot 注入改写）暂不纳入渲染，用「仅登记」占位，`note` 里写明机器真源路径。
升级为渲染站点 = 先给 `gen-entry.mjs` 补 SSL/ACME 能力，再去掉 `render: false`。

## 已知漂移（2026-09-13 实测）

| 站点 | 契约描述 | 实际 | 处理 |
|---|---|---|---|
| `portal` | 门户入口 — 阿里云 `47.115.168.107` | 企业域三记录（`szbolent.com.cn` / `www` / `api`）已全部指向腾讯云 `1.14.202.161`；本机（阿里云）按 2026-09-12 改造记录只剩个人域内容站 | **待重签**：按阿里云现况改写本条目 |
| `api` | `api.genz.ltd` → `looma-local` | 未变更 | 无需改 |
| `enterprise` | —（新增登记） | 腾讯云企业站，含 SSL/ACME | 已登记，`render: false` |

**待办（需人工拍板）**：①按阿里云现况重签 `portal` 站（个人域 WP + 诗词 H5，含服务名收敛）；
②给生成器补 SSL/ACME 渲染能力，再把 `enterprise` 从「仅登记」升为渲染站点。
在此之前，**企业站以机器文件为准**（`nginx-tencent-portal.conf` 仅为参考副本）。

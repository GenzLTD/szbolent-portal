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

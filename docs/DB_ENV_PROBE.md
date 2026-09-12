# DB 环境回执（回传口径）

> 脚本：`scripts/report-db-env.sh`（只读）
> 同族：`scripts/verify-offline.sh`（**断言**）· `scripts/report-prod.sh`（**生产回执**）
> 适用仓：本仓（门户与多端组合层）

---

## 1. 为什么把这件事交出去

DB 环境是典型的「**结论随机器而变**」。

维护者本机没有 pgvector，这只说明「**这台**机器上没有」，既不能证明「成员那边没有」，也不能证明「本仓需要它」。反过来，成员本机有 pgvector，也不代表它是本仓的依赖。

因此本仓的做法是：

> **不对 DB 可用性下断言，只收集回执，多机汇总后再定。**

每条回执都是事实，不是结论；断言留给 §2 里真正与机器无关的那一层。

---

## 2. 两类东西，勿混

| 类型 | 脚本 | 结论性质 | 换台机器还成立吗 |
|---|---|---|---|
| **断言** | `bash scripts/verify-offline.sh` | 仓对不对（L1 契约同步 + L2 类型/构建） | ✅ 成立，成员 `npm ci` 后可复现 |
| 回执 | `bash scripts/report-prod.sh` | 公网 + 生产机当时的状态 | ❌ 随时点/网络而变 |
| 回执 | `bash scripts/report-db-env.sh` | **本机** DB 环境（MySQL / PostgreSQL / pgvector） | ❌ 随机器而变 |

推论：`report-db-env.sh` **不进 CI、不进离线门禁**，它是给人看的证据。把回执当断言，等于逼所有人复刻同一台机器。

---

## 3. 本仓的 DB 事实（先读，避免误配）

| 项 | 值 | 依据 |
|---|---|---|
| 引擎 | **MySQL**（SQLx 0.8） | `backend/README.md` · `backend/src/services/menu_service.rs` |
| DSN | `mysql://looma:looma@127.0.0.1:3306/looma` | `backend/.env.example` |
| 库 / 表 | `looma` · `share_menus` · `share_pages` … | `backend/migrations/001_init.sql` |
| compose 的 MySQL | `3307` / `modown_wp`（WP 内容，**另一套**，非 Page Engine 用） | `docker-compose.yml` |
| **pgvector** | **不是本仓依赖**，属 Looma 生态 | 本仓无任何 `vector` 类型/扩展引用 |

> Page Engine 要的 `looma` 库**不在 compose 编排里**，README 里靠手工建库+导入。
> 这正是「成员拿不到一致 DB」的根因；`report-db-env.sh` 先把各机现状采清楚，再谈统一。

---

## 4. 怎么跑、回传什么

```bash
bash scripts/report-db-env.sh
```

跑完贴回**最后一行**回执即可（形如）：

```text
DB-ENV 回执 @ <sha> | os=Darwin/arm64 | tcp3306=closed | … | pgvector_verdict=files-only
```

只读保证：不写库、不建库、不 `CREATE EXTENSION`、不启停服务、不装东西、**不打印口令**。
缺 `mysql` / `psql` 客户端时自动降级到「端口层 + 扩展文件层」，所以**人人可跑**。

---

## 5. pgvector 四态怎么读（关键三分）

| verdict | 含义 | 说明 |
|---|---|---|
| `extension-in-db` | 某可达 PG 库里**已启用** `vector`（有 extversion） | 最强证据：能直接用 |
| `files-only` | 只找到扩展**文件**（`vector.control`），无库启用 | 装了扩展但没实例 / 没 `CREATE EXTENSION` |
| `unknown-needs-client-or-creds` | 有 PG 端口在听，但缺客户端或口令，连不上 | 需人工补一步，**不是**「没有」 |
| `none` | 既无监听端口也无扩展文件 | 该机确实没有 pgvector |

**「扩展文件在」≠「扩展已启用」** —— 这一条踩过：单看文件会误判「有」，单看连接会误判「没有」。脚本把两件事分别采，就是这个原因。

---

## 6. 回传模板

```text
机器：<谁 / 哪台>　　用途：<开发 / 联调 / 生产>
DB-ENV 回执 @ <sha> | <整行粘贴>
补充：<若 verdict=unknown，写明是否有 psql、是否有口令/.pgpass>
```

---

## 7. 边界（本脚本刻意不做）

- 不判定「一致 / 不一致」—— 比对是汇总阶段的事，不是单机的事。
- 不连生产、不 SSH、不打公网（那是 `report-prod.sh`）。
- 不改任何配置、不给出「应该怎么配」的建议 —— 先采事实。

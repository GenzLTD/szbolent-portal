# szbolent 门户 × 诗词站 局域网联合调试方案

_2026-09-22 · Mac（192.168.3.96）× Windows（192.168.3.44）· 全链路已单侧验证，待双向联调_

---

## 1. 拓扑与角色

```
┌─ Mac 192.168.3.96（门户侧）──────────────┐      ┌─ Windows 192.168.3.44（诗词侧）─────┐
│ nginx :80/:443   门户静态 + 反代           │      │ nginx :80/:443  博朗特诗词 WP        │
│ Zeroclaw :42617  中间层（导航契约/记忆/编排）│ ◄──► │ WP REST  /wp-json/wp/v2/*          │
│ Ollama :11434    qwen2.5:7b, bge-m3 …     │      │ Ollama :11434   qwen2.5:3b         │
│ 中继 :4443→.44:443（绕 ServBay 出站限制）   │      │ MySQL :3306                        │
└──────────────────────────────────────────┘      └────────────────────────────────────┘
```

| 资产 | Mac（.96） | Windows（.44） |
|---|---|---|
| 站点 | 深伯乐门户（Vue3 SPA）| 博朗特诗词（WordPress）|
| 内容 API | `GET /v1/menus?product=`（公开，Zeroclaw 契约）| `GET /wp-json/wp/v2/posts` 等（公开读）|
| AI | Ollama：qwen2.5:7b（赏析）、bge-m3（向量检索）| Ollama：qwen2.5:3b |
| 编排 | Zeroclaw 网关（`/webhook` SOP、agents、记忆库）| — |
| 证书 | SzBolent Test CA 签（**本包附 `szbolent-test-ca.crt`**）| ServBay User CA 签（我方已装 Root）|

## 2. 信任前提（已完成 / 待办）

| 事项 | 状态 |
|---|---|
| .96 装 .44 的 Root（ServBay-Private-CA-ECC-Root.crt）| ✅ 已装，浏览器零警告 |
| .44 装 .96 的 Root（本包 `szbolent-test-ca.crt`）| ⬜ **对方待做**（双击 .crt → 安装到「受信任的根证书颁发机构」）|
| hosts 域名映射（可选）| ⬜ 双方各自加：`192.168.3.44 szbolent.cn` / `192.168.3.96 szbolent.local` |

## 3. 联调阶段

### P0 连通性互测（双方各跑一个脚本）
- 我方：`scripts/01-mac-selfcheck.sh`（10 项检查，全绿为过）
- 对方：`scripts/02-peer-checks.sh`（Git Bash 直接跑；或照 `scripts/02-peer-checks.ps1` 用 PowerShell）
- **验收标准**：两端脚本各自输出 `PASS x/x`

### P1 内容互通（只读，无鉴权）
1. 门户侧消费诗词：`https://szbolent.local/peer-wp-json/wp/v2/posts` 已通（严格 TLS 校验）。下一步：门户新增「诗词」页，列表取自该反代。
2. 诗词侧消费契约：对方页面可调 `https://192.168.3.96/v1/menus?product=szbolent` 获取统一导航（camelCase JSON 数组）。

### P2 AI 赏析 PoC（本地 AI 参与）
- 演示脚本：`scripts/03-demo-ai-appreciation.sh`
- 链路：`WP 取诗 → 提取正文 → Ollama 生成赏析 → 落 JSON 文件`
- 默认用 .44 的 qwen2.5:3b（就在诗词旁边，数据不出局域网）；`AI=local` 环境变量可切 .96 的 qwen2.5:7b
- **验收标准**：对《静夜思》产出 ≥100 字结构化赏析，`poem-appreciation.json` 双方可读

### P3 自动化（联调通过后）
1. **新诗 webhook**：对方发布新诗 → 通知 .96 Zeroclaw `/webhook` → agent 自动生成赏析 → 写回 WP 草稿（需对方建 Application Password，权限限 `publish_posts` 以下）。
2. **智能检索**：bge-m3 向量化诗词库，门户提供语义搜索框。

## 4. AI 参与分工

| 场景 | 推理节点 | 模型 | 说明 |
|---|---|---|---|
| 诗词赏析（P2 PoC）| .44 | qwen2.5:3b | 就近推理，展示 WP+AI 最小闭环 |
| 诗词赏析（P3 正式）| .96 | qwen2.5:7b | 质量更好，由 Zeroclaw agent 编排 + 记忆沉淀 |
| 语义检索（P3）| .96 | bge-m3 | 诗词向量库，门户侧搜索 |

## 5. 安全前提（对方联调前应完成，P2 起强制）

1. **Ollama 绑回本机**：`OLLAMA_HOST=127.0.0.1` 重启 —— 现状全局域网可白嫖 GPU；联调期间若需跨机调用，改绑 `192.168.3.44` 并加防火墙只放行 .96
2. **WP `/wp-json/wp/v2/users` 限读**（当前泄露管理员用户名）
3. **关闭 xmlrpc.php**（未被使用）
4. MySQL 3306 不应对 `0.0.0.0` 监听

## 6. 联调交付物清单（本包）

```
联调交付包-2026-09-22/
├── 联调方案-szbolent门户x诗词站.md   ← 本文档
├── szbolent-test-ca.crt             ← 对方需安装的我方根证书
└── scripts/
    ├── 01-mac-selfcheck.sh          ← 我方自检（已跑通）
    ├── 02-peer-checks.sh            ← 对方自检（Git Bash）
    ├── 02-peer-checks.ps1           ← 对方自检（PowerShell 备选）
    └── 03-demo-ai-appreciation.sh   ← AI 赏析 PoC（已跑通）
```

## 7. 验收清单

- [ ] 双方脚本 `PASS 10/10`
- [ ] 门户反代在严格 TLS 校验下取到诗词列表
- [ ] 对方浏览器零警告访问 `https://192.168.3.96`
- [ ] 《静夜思》AI 赏析 JSON 产出并双方确认质量
- [ ] （P3）新诗发布触发自动赏析回写草稿

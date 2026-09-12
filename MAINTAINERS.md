# Maintainers（草稿）

> 规则：谁最活跃谁维护（按近 60 天提交统计，2026-09-12 生成）。
> 分歧由 maintainer 拍板；重大架构决策走 docs 仓 ADR；意见不合时开 issue 或直接发补丁表达（分歧用补丁，不辩论）。

## Maintainer
- @zervi-genz（近 60 天最活跃）

## 协作约定
- 直接 push main 已被 `githooks/pre-push` 拦截，改动请走 分支 + PR
- clone 后执行一次：`sh scripts/setup.sh` 启用钩子
- PR 尽量小、描述清楚 why；merge 前先 `git pull --rebase`

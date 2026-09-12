#!/bin/bash
# ============================================================
# Page Engine Service — 集成测试脚本
# ============================================================
set -e

BASE="${1:-http://127.0.0.1:5300}"

echo "=== Page Engine Service Integration Tests ==="
echo "Base URL: $BASE"
echo ""

# ---- 环境前检 ----
# 服务不可达 → 退 2（环境不足），与「断言失败」（退 1）区分：
# 成员该看到「你没起 Page Engine」，而不是「测试失败」。
# 注意：-w '%{http_code}' 在连不上时也会向 stdout 写 "000"，且 curl 自身退出码为 7。
# 故此处不可在命令替换里再追加 `|| echo 000`（会拼成 "000 000" 使判断失效，已踩过）。
# 用 `|| true` 让赋值语句本身不触发 set -e 即可，PROBE 拿到的就是 curl 写的 "000"。
PROBE="$(curl -s -o /dev/null --max-time 3 -w '%{http_code}' "$BASE/v1/menus?product=szbolent" 2>/dev/null)" || true
if [ "$PROBE" = "000" ]; then
    echo "❌ 无法连接 $BASE —— 集成测试需要 Page Engine 在跑。"
    echo "   启动: cd backend && cargo run"
    echo "   换目标: bash backend/test-integration.sh http://host:port"
    echo ""
    exit 2
fi

# ---- Helper ----
# 失败必须计数并影响退出码，否则脚本无论对错都退 0（假绿）。
FAILED=0
check() {
    local desc="$1"
    local expected="$2"
    local actual="$3"
    if echo "$actual" | grep -q "$expected"; then
        echo "  ✅ $desc"
    else
        echo "  ❌ $desc (expected: $expected)"
        FAILED=$((FAILED + 1))
    fi
}

# ============================================================
# 1. Health Check (optional)
# ============================================================
echo "--- 1. Menus ---"

# 1a. 公开菜单（无需认证）
RESP=$(curl -s "$BASE/v1/menus?product=szbolent")
check "GET /v1/menus returns menu tree" '"title"' "$RESP"
check "Public menu has home" '"首页"' "$RESP"

# 1b. 博客菜单
RESP=$(curl -s "$BASE/v1/menus?product=blog")
check "Blog menu has 博客" '"博客"' "$RESP"

# 1c. JobFirst 菜单
RESP=$(curl -s "$BASE/v1/menus?product=jobfirst")
check "JobFirst menu has 职位搜索" '"职位搜索"' "$RESP"

# 1d. 不存在的产品
RESP=$(curl -s "$BASE/v1/menus?product=unknown")
check "Unknown product returns empty" '^\[\]' "$RESP"

echo ""
echo "--- 2. Pages ---"

# 2a. 获取简历页面（公开可见组件）
RESP=$(curl -s "$BASE/v1/pages/by-path?path=/blog/resume")
check "GET /v1/pages/by-path returns page" '"id"' "$RESP"
check "Resume page has Hero component" '"Hero"' "$RESP"
check "Resume page has Skills component" '"Skills"' "$RESP"
# Timeline 需要 authenticated，公开访问时不应该出现
if echo "$RESP" | grep -q '"Timeline"'; then
    echo "  ❌ Resume page Timeline visible without auth (should be hidden)"
else
    echo "  ✅ Resume page hides Timeline for unauthenticated"
fi

# 2b. 获取不存在的页面
RESP=$(curl -s "$BASE/v1/pages/by-path?path=/nonexistent")
check "Nonexistent page returns 404" '"not_found"' "$RESP"

# 2c. JobFirst 欢迎页
RESP=$(curl -s "$BASE/v1/pages/by-path?path=/")
check "JobFirst landing has Hero" '"Hero"' "$RESP"

echo ""
echo "--- 3. Permissions ---"

# 3a. 未认证的权限检查
RESP=$(curl -s -X POST "$BASE/v1/permissions/check" \
    -H "Content-Type: application/json" \
    -d '{"resource":"public:pages","action":"read"}')
check "Unauthed permission check returns 401" '"unauthorized"' "$RESP"

echo ""
echo "--- 4. Admin (requires JWT) ---"

# 4a. 尝试管理员端点（无 token）
RESP=$(curl -s "$BASE/v1/menus/admin?product=szbolent")
check "Admin endpoint without token returns 401" '"unauthorized"' "$RESP"

echo ""
echo "=== Integration Test Complete ==="
if [ "$FAILED" -gt 0 ]; then
    echo "❌ ${FAILED} 项失败"
    exit 1
fi
echo "✅ 全部通过"
exit 0

#!/bin/bash
# 本地仿真烟雾测试脚本
# 跑前确保：
#   1. 3 容器已起：cd deploy-local && docker compose -f docker-compose.local.yml up -d
#   2. servbay nginx 已加载 szbolent-portal-local.conf
#   3. /etc/hosts 含：127.0.0.1 szbolent.local
#
# 用法：bash deploy-local/scripts/smoke-test.sh

set -u
LOCAL="${LOCAL:-szbolent.local}"
FAIL=0

echo "═════════════════════════════════════════════════════════════"
echo "  Szbolent Portal 本地仿真烟雾测试"
echo "═════════════════════════════════════════════════════════════"
echo ""

# 颜色
R='\033[0;31m'; G='\033[0;32m'; Y='\033[1;33m'; N='\033[0m'
ok()   { echo -e "${G}✅ $*${N}"; }
warn() { echo -e "${Y}⚠️  $*${N}"; }
fail() { echo -e "${R}❌ $*${N}"; FAIL=$((FAIL+1)); }

# === 1. 容器健康 ===
echo "──── 1/5 容器健康检查 ────"
for c in looma-backend-stub-local bolent-wp-local bolent-wp-mysql-local; do
    status=$(docker inspect --format='{{.State.Status}}' "$c" 2>/dev/null)
    if [ "$status" = "running" ]; then
        ok "  $c → running"
    else
        fail "  $c → $status (not running)"
    fi
done
echo ""

# === 2. 直连反代 ===
echo "──── 2/5 直连 127.0.0.1:5200 (looma stub) ────"
code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 http://127.0.0.1:5200/health 2>/dev/null)
[ "$code" = "200" ] && ok "  /health → 200" || fail "  /health → $code"

code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 http://127.0.0.1:5200/v1/health 2>/dev/null)
[ "$code" = "200" ] && ok "  /v1/health → 200" || fail "  /v1/health → $code"
echo ""

echo "──── 3/5 直连 127.0.0.1:8080 (WordPress) ────"
code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 http://127.0.0.1:8080/ 2>/dev/null)
if [ "$code" = "200" ] || [ "$code" = "302" ]; then
    ok "  / → $code (302=首次安装，正常)"
else
    fail "  / → $code"
fi

# 容器内 WP 未启 pretty permalink：直连必须走 ?rest_route=，
# /wp-json/ 只有经 nginx rewrite 才可用（见第 4 步）。
code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "http://127.0.0.1:8080/index.php?rest_route=/" 2>/dev/null)
if [ "$code" = "200" ] || [ "$code" = "302" ]; then
    ok "  /index.php?rest_route=/ → $code"
else
    fail "  /index.php?rest_route=/ → $code"
fi
echo ""

# === 4. nginx 反代（通过 szbolent.local）===
echo "──── 4/5 nginx 反代（http://${LOCAL}） ────"
code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "http://$LOCAL/" 2>/dev/null)
[ "$code" = "200" ] && ok "  / (SPA) → 200" || { warn "  / → $code"; [ "$code" != "000" ] && FAIL=$((FAIL+0)) || fail "  / → connection failed (检查 nginx)"; }

code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "http://$LOCAL/v1/health" 2>/dev/null)
[ "$code" = "200" ] && ok "  /v1/health (反代到 :5200) → 200" || warn "  /v1/health → $code (nginx 可能未启用)"

code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "http://$LOCAL/wp-json/" 2>/dev/null)
[ "$code" = "200" ] || [ "$code" = "302" ] && ok "  /wp-json/ (反代到 :8080) → $code" || warn "  /wp-json/ → $code (nginx 可能未启用)"
echo ""

# === 5. SPA fallback（Vue Router history 模式）===
echo "──── 5/5 SPA 路由 fallback ────"
for path in /blog /chat /shelf /about; do
    code=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "http://$LOCAL$path" 2>/dev/null)
    if [ "$code" = "200" ]; then
        ok "  $path → 200 (SPA fallback OK)"
    else
        warn "  $path → $code"
    fi
done
echo ""

# === 总结 ===
echo "═════════════════════════════════════════════════════════════"
if [ $FAIL -eq 0 ]; then
    echo -e "${G}  ✅ 全部通过${N}"
    echo "  下一步："
    echo "    1. 改 portal 仓 src/* 后跑 npm run build"
    echo "    2. 修改 nginx 配置 root 指向真 dist"
    echo "    3. 全部 OK 后 git push origin main 触发腾讯云部署"
else
    echo -e "${R}  ❌ 失败 $FAIL 项${N}"
    echo "  排查方向："
    echo "    - nginx 未启用：servbay GUI 加 site"
    echo "    - 容器没起：cd deploy-local && docker compose up -d"
    echo "    - /etc/hosts 没加 szbolent.local"
fi
echo "═════════════════════════════════════════════════════════════"
exit $FAIL
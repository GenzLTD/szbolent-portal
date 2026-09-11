#!/usr/bin/env bash
# =============================================================
# smoke.sh — 契约驱动的冒烟
#
# 不硬编码分流规则：检查项从 contracts/entry.json 读取，
# 契约改了冒烟自动跟着改（这是「契约即测试」的落点）。
#
# 用法:
#   bash scripts/smoke.sh              # 本地冒烟
#   SMOKE_PORTAL=http://host:3000 bash scripts/smoke.sh
#   LOOMA_SMOKE_BASE=http://127.0.0.1:5200 bash scripts/smoke.sh   # 走本地 Looma
# =============================================================
set -u

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

GREEN='\033[0;32m'; RED='\033[0;31m'; YELLOW='\033[0;33m'; DIM='\033[2m'; NC='\033[0m'
PASS=0; FAIL=0

PORTAL="${SMOKE_PORTAL:-http://127.0.0.1:3000}"
PORTAL="${PORTAL%/}"
LOOMA_LOCAL_OVERRIDE="${LOOMA_SMOKE_BASE:-}"

check() { # name url expect
  local name="$1" url="$2" expect="${3:-200}" code
  code=$(curl -sS -o /dev/null -w "%{http_code}" --connect-timeout 5 "$url" 2>/dev/null) || code="000"
  printf "  %-34s %-58s " "$name" "$url"
  if [[ "$code" == "$expect" || ( "$expect" == "2xx" && "$code" =~ ^2[0-9][0-9]$ ) || ( "$expect" == "up" && "$code" != "000" ) ]]; then
    echo -e "${GREEN}PASS${NC} ($code)"; PASS=$((PASS+1))
  elif [[ "$code" == "000" ]]; then
    echo -e "${RED}FAIL${NC} (unreachable)"; FAIL=$((FAIL+1))
  else
    echo -e "${YELLOW}WARN${NC} ($code, want $expect)"; FAIL=$((FAIL+1))
  fi
}

# ── 0. 契约自身必须处于同步状态，否则后面全是假绿 ──────────────
echo ""
echo "============================================================"
echo "  Variety 冒烟 · 契约驱动"
echo "  $(date '+%Y-%m-%d %H:%M:%S')"
echo "============================================================"
echo ""
echo "--- 0. 契约与生成物一致性 ---"
if node scripts/gen-entry.mjs --check >/dev/null 2>&1; then
  echo -e "  ${GREEN}PASS${NC}  nginx 配置与 contracts/entry.json 同步"
  PASS=$((PASS+1))
else
  echo -e "  ${RED}FAIL${NC}  nginx 配置与契约不同步 → 运行: node scripts/gen-entry.mjs"
  FAIL=$((FAIL+1))
fi

# ── 1. 直连各上游（服务是否活着）──────────────────────────────
echo ""
echo "--- 1. 上游直连 ---"
# 从契约读 upstreams: name<TAB>dev
while IFS=$'\t' read -r name dev; do
  [[ -z "$name" ]] && continue
  target="$dev"
  # 允许 Looma 指向本地实例
  if [[ "$name" == "looma" && -n "$LOOMA_LOCAL_OVERRIDE" ]]; then
    target="$LOOMA_LOCAL_OVERRIDE"
  fi
  check "$name" "$target/" "up"
done < <(node -e "
const c=require('$ROOT/contracts/entry.json');
for (const [n,u] of Object.entries(c.upstreams)) console.log(n+'\t'+u.dev);
" 2>/dev/null)

# ── 2. 门户本体 ──────────────────────────────────────────────
echo ""
echo "--- 2. 门户 ${PORTAL} ---"
check "portal /"      "$PORTAL/"       "200"
check "portal /poetry" "$PORTAL/poetry" "200"
check "portal /blog"  "$PORTAL/blog"   "200"

# ── 3. 代理分流（契约真正的价值：请求有没有打到对的后端）──────
echo ""
echo "--- 3. 代理分流（经门户 :3000 → 各上游）---"
while IFS=$'\t' read -r prefix upstream desc; do
  [[ -z "$prefix" ]] && continue
  probe="$prefix"
  # 需要查询参数的端点补上，否则会因缺参 400/422 而误判
  case "$prefix" in
    /v1/menus) probe="/v1/menus?product=szbolent" ;;
    /v1/pages) probe="/v1/pages/by-path?path=/" ;;
  esac
  check "→ $upstream" "${PORTAL}${probe}" "up"
done < <(node -e "
const c=require('$ROOT/contracts/entry.json');
for (const r of c.routes) console.log(r.prefix+'\t'+r.upstream+'\t'+(r.desc||''));
" 2>/dev/null)

# ── 汇总 ─────────────────────────────────────────────────────
echo ""
echo "------------------------------------------------------------"
printf "  PASS=${GREEN}%d${NC}  FAIL/WARN=${RED}%d${NC}\n" "$PASS" "$FAIL"
echo "------------------------------------------------------------"
if [[ "$FAIL" -gt 0 ]]; then
  echo -e "${YELLOW}提示${NC}：先确认服务已起 —— docker compose up -d（WP/MySQL），"
  echo "      Page Engine: cd backend && cargo run，Looma: looma-zervi/backend/dev.sh"
  exit 1
fi
echo -e "${GREEN}全部通过${NC}"

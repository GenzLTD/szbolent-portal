#!/usr/bin/env bash
# 01-mac-selfcheck.sh — Mac（.96 门户侧）联调自检
# 用法: bash 01-mac-selfcheck.sh   （预期输出 PASS 10/10）
set -u
PASS=0; FAIL=0
check() {
  if [ "$2" -eq 0 ]; then echo "PASS  $1"; PASS=$((PASS+1)); else echo "FAIL  $1"; FAIL=$((FAIL+1)); fi
}
# 本机域名走自签 CA；对方域名走对方 Root（peer-root.crt）
c()  { curl -4 -s --noproxy '*' --cacert /Applications/ServBay/ssl/import/szbolent/szbolent-test-ca.crt --resolve szbolent.local:443:127.0.0.1 "$@"; }
cp_() { curl -4 -s --noproxy '*' --cacert /Applications/ServBay/ssl/import/szbolent/peer-root.crt --resolve szbolent.cn:443:192.168.3.44 "$@"; }

# 本机服务
code=$(c -o /dev/null -w '%{http_code}' -m 8 "https://szbolent.local/");              check "本机门户 https 首页 200（实际 ${code}）" $([ "$code" = 200 ]; echo $?)
code=$(c -o /dev/null -w '%{http_code}' -m 8 "http://szbolent.local/v1/health");      check "Looma stub /v1/health（实际 ${code}）" $([ "$code" = 200 ]; echo $?)
n=$(c -m 8 "https://szbolent.local/v1/menus?product=szbolent" | python3 -c 'import json,sys;print(len(json.load(sys.stdin)))' 2>/dev/null); check "Zeroclaw /v1/menus 返回 7 条（实际 ${n:-0}）" $([ "$n" = 7 ]; echo $?)
lsof -nP -iTCP:42617 -sTCP:LISTEN >/dev/null 2>&1; check "Zeroclaw 网关 :42617 监听" $?
lsof -nP -iTCP:4443 -sTCP:LISTEN >/dev/null 2>&1;  check "TCP 中继 :4443 存活" $?

# 对方服务（严格 TLS）
code=$(cp_ -o /dev/null -w '%{http_code}' -m 8 "https://szbolent.cn/");               check "对方站点 https 200（实际 ${code}）" $([ "$code" = 200 ]; echo $?)
code=$(c -o /dev/null -w '%{http_code}' -m 10 "https://szbolent.local/peer-wp-json/wp/v2/posts?per_page=1"); check "门户反代取对方诗词（实际 ${code}）" $([ "$code" = 200 ]; echo $?)
code=$(curl -4 -s --noproxy '*' -m 5 -o /dev/null -w '%{http_code}' http://192.168.3.44:11434/api/version); check "对方 Ollama 可达（实际 ${code}）" $([ "$code" = 200 ]; echo $?)

# 本机 AI
curl -4 -s --noproxy '*' -m 5 http://127.0.0.1:11434/api/tags | grep -q "qwen2.5:7b"; check "本机 Ollama qwen2.5:7b 就绪" $?
curl -4 -s --noproxy '*' -m 5 http://127.0.0.1:11434/api/tags | grep -q "bge-m3";     check "本机 Ollama bge-m3（向量）就绪" $?

echo "-----"
echo "RESULT: PASS $PASS/10, FAIL $FAIL"
[ $FAIL -eq 0 ]

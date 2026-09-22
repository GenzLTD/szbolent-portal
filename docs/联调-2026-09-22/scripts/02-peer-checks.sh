#!/usr/bin/env bash
# 02-peer-checks.sh — Windows（.44 诗词侧）联调自检（Git Bash 运行；PowerShell 用户见 02-peer-checks.ps1）
# v2 修正（2026-09-22 验收反馈）：
#   1) 改用 --resolve szbolent.local:443:192.168.3.96 —— IP 直访无 SNI 会命中 .96 ServBay
#      Demo 默认站证书导致假失败；szbolent.local 在我方证书 SAN 里，带 SNI 严格校验即过
#   2) Windows curl (schannel) 对自签 CA 报 "revocation status is unknown" —— 加 --ssl-no-revoke
#      （仅在 Windows 环境加，macOS curl 无此 flag）
# 前提：已安装 szbolent-test-ca.crt 为受信任根；未装则自动降级 -k 并提示
set -u
PEER_IP=192.168.3.96
CA="${TEMP:-/tmp}/szbolent-test-ca.crt"
PASS=0; FAIL=0
check() { if [ "$2" -eq 0 ]; then echo "PASS  $1"; PASS=$((PASS+1)); else echo "FAIL  $1"; FAIL=$((FAIL+1)); fi; }

# Windows curl 需要 --ssl-no-revoke（Git Bash / curl.exe 均为 schannel 后端）
case "$(uname -s)" in
  MINGW*|CYGWIN*|Windows*) REV="--ssl-no-revoke" ;;
 *) REV="" ;;
esac

RES="--resolve szbolent.local:443:${PEER_IP}"
BASE="https://szbolent.local"

# 取出包内 CA 到临时目录（若同目录存在）
[ -f szbolent-test-ca.crt ] && cp szbolent-test-ca.crt "$CA"
K="-k"; CAOPT=""
if [ -s "$CA" ] && curl -s --noproxy '*' $REV $RES --cacert "$CA" -o /dev/null "${BASE}/v1/menus?product=szbolent" 2>/dev/null; then
  CAOPT="--cacert $CA"; K=""
  echo ">> 我方根证书已安装，走严格校验"
else
  echo ">> 警告：szbolent-test-ca.crt 未安装/未验证，本次降级 -k（请按方案第 2 节安装）"
fi

# ① 本机服务自检
code=$(curl -sk --noproxy '*' -o /dev/null -w '%{http_code}' -m 6 https://127.0.0.1/wp-json/wp/v2/posts?per_page=1)
check "本机 WP REST（实际 ${code}）" $([ "$code" = 200 ]; echo $?)
code=$(curl -s --noproxy '*' -o /dev/null -w '%{http_code}' -m 5 http://127.0.0.1:11434/api/version)
check "本机 Ollama（实际 ${code}）" $([ "$code" = 200 ]; echo $?)

# ② 跨机访问 .96（带 SNI，严格 TLS）
code=$(curl -s --noproxy '*' $K $REV $RES $CAOPT -o /dev/null -w '%{http_code}' -m 8 "${BASE}/")
check "门户首页 https（实际 ${code}）" $([ "$code" = 200 ]; echo $?)
code=$(curl -s --noproxy '*' $K $REV $RES $CAOPT -o /dev/null -w '%{http_code}' -m 8 "${BASE}/v1/menus?product=szbolent")
check "Zeroclaw 导航契约（实际 ${code}）" $([ "$code" = 200 ]; echo $?)
body=$(curl -s --noproxy '*' $K $REV $RES $CAOPT -m 8 "${BASE}/v1/menus?product=szbolent" 2>/dev/null | head -c 50)
echo "$body" | grep -q '"title"'; check "契约返回 camelCase JSON（${body}…）" $?

# ③ 连通性
ping -n 1 -w 2000 ${PEER_IP} >/dev/null 2>&1 || ping -c1 -W2 ${PEER_IP} >/dev/null 2>&1
check "ping ${PEER_IP} 可达" $?

echo "-----"
echo "RESULT: PASS $PASS/6, FAIL $FAIL"
[ $FAIL -eq 0 ]

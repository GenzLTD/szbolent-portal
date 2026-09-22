# 02-peer-checks.ps1 — Windows（.44）联调自检（PowerShell 版，管理员非必需）
# 用法: 在 scripts 目录下执行  powershell -ExecutionPolicy Bypass -File .\02-peer-checks.ps1
# v2 修正（2026-09-22 验收反馈）：
#   1) 改用 --resolve szbolent.local:443:192.168.3.96（IP 直访无 SNI 会命中 .96 ServBay Demo 站证书 → 假失败）
#   2) 加 --ssl-no-revoke（Windows curl schannel 对自签 CA 报 revocation unknown）
$ErrorActionPreference = "SilentlyContinue"
$peerIp = "192.168.3.96"
$res = "--resolve", "szbolent.local:443:$peerIp"
$base = "https://szbolent.local"
$ca = Join-Path $env:TEMP "szbolent-test-ca.crt"
$pass = 0; $fail = 0
function Check($name, $ok) {
  if ($ok) { Write-Host "PASS  $name"; $script:pass++ } else { Write-Host "FAIL  $name"; $script:fail++ }
}

# ① 本机服务
$r = curl.exe -sk --noproxy "*" --ssl-no-revoke -o NUL -w "%{http_code}" -m 6 "https://127.0.0.1/wp-json/wp/v2/posts?per_page=1"
Check "本机 WP REST（实际 $r）" ($r -eq "200")
$r = curl.exe -s --noproxy "*" -o NUL -w "%{http_code}" -m 5 "http://127.0.0.1:11434/api/version"
Check "本机 Ollama（实际 $r）" ($r -eq "200")

# ② 跨机访问 .96（带 SNI，严格 TLS；有 CA 则严格校验，否则 -k 降级）
if (Test-Path "szbolent-test-ca.crt") { Copy-Item "szbolent-test-ca.crt" $ca -Force }
$k = "-k"; $caopt = @()
if ((Test-Path $ca) -and (curl.exe -s --noproxy "*" --ssl-no-revoke @res --cacert $ca -o NUL "$base/v1/menus?product=szbolent")) {
  $k = ""; $caopt = @("--cacert", $ca); Write-Host ">> 我方根证书已安装，走严格校验"
} else {
  Write-Host ">> 警告：szbolent-test-ca.crt 未安装/未验证，本次降级 -k"
}
$r = curl.exe $k --noproxy "*" --ssl-no-revoke @res @caopt -o NUL -w "%{http_code}" -m 8 "$base/"
Check "门户首页 https（实际 $r）" ($r -eq "200")
$r = curl.exe $k --noproxy "*" --ssl-no-revoke @res @caopt -o NUL -w "%{http_code}" -m 8 "$base/v1/menus?product=szbolent"
Check "Zeroclaw 导航契约（实际 $r）" ($r -eq "200")
$body = curl.exe $k --noproxy "*" --ssl-no-revoke @res @caopt -m 8 "$base/v1/menus?product=szbolent"
Check "契约返回 camelCase JSON" ($body -match '"title"')

# ③ 连通性
Check "ping $peerIp 可达" ((Test-Connection -ComputerName $peerIp -Count 1 -Quiet))

Write-Host "-----"
Write-Host "RESULT: PASS $pass/6, FAIL $fail"

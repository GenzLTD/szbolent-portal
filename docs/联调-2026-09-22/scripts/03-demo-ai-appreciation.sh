#!/usr/bin/env bash
# 03-demo-ai-appreciation.sh — AI 诗词赏析 PoC（联调方案 P2 验收件）
# 链路: WP 取诗 → 提取正文 → Ollama 生成赏析 → poem-appreciation.json
# 用法:
#   bash 03-demo-ai-appreciation.sh            # 默认用 .44 的 qwen2.5:3b（数据不出局域网）
#   AI=local bash 03-demo-ai-appreciation.sh   # 用 .96 的 qwen2.5:7b（质量更好）
set -u
WP="https://192.168.3.44/wp-json/wp/v2/posts?per_page=1"
OUT="poem-appreciation.json"

if [ "${AI:-peer}" = "local" ]; then
  OLLAMA="http://127.0.0.1:11434"; MODEL="qwen2.5:7b"; NODE="Mac .96"
else
  OLLAMA="http://192.168.3.44:11434"; MODEL="qwen2.5:3b"; NODE="Windows .44"
fi

echo ">> [1/3] 从 WP 取诗…"
RAW=$(curl -4 -sk --noproxy '*' -m 10 "$WP")
[ -z "$RAW" ] && { echo "FAIL: WP 不可达"; exit 1; }

echo ">> [2/3] 提取正文并调用 ${MODEL}（${NODE}）生成赏析…"
POEM_JSON=$(printf '%s' "$RAW" | python3 -c '
import json,sys,re,html
d=json.load(sys.stdin)[0]
title=re.sub(r"<[^>]+>","",d["title"]["rendered"]).strip()
body=re.sub(r"<[^>]+>","",d["content"]["rendered"])
body=html.unescape(body).strip()
print(json.dumps({"title":title,"body":body},ensure_ascii=False))
')
PROMPT=$(printf '%s' "$POEM_JSON" | python3 -c '
import json,sys
p=json.load(sys.stdin)
prompt=("你是古典文学赏析助手。请对下面这首诗写一段 120-180 字的赏析，"
        "包含：意象分析、情感主旨、艺术手法三部分，用简洁的中文。"
        "只输出赏析正文，不要标题和客套话。\n\n诗题："+p["title"]+"\n诗文：\n"+p["body"])
print(json.dumps({"prompt":prompt},ensure_ascii=False))
')

echo ">> [3/3] 推理中（3b 约 20-40s，7b 约 60-120s）…"
APPRECIATION=$(curl -s --noproxy '*' -m 180 "$OLLAMA/api/generate" \
  -d "$(printf '%s' "$PROMPT" | python3 -c 'import json,sys;p=json.load(sys.stdin);print(json.dumps({"model":"'"$MODEL"'","prompt":p["prompt"],"stream":False},ensure_ascii=False))')" \
  | python3 -c 'import json,sys;print(json.load(sys.stdin)["response"].strip())')

printf '%s' "$POEM_JSON" | python3 -c '
import json,sys
p=json.load(sys.stdin)
p["appreciation"]=r"""'"$APPRECIATION"'"""
p["meta"]={"model":"'"$MODEL"'","node":"'"$NODE"'","generated_at":"'"$(date +%F\ %T)"'"}
json.dump(p,open("'"$OUT"'","w"),ensure_ascii=False,indent=2)
print("✅ 已写出 '"$OUT"'")
'
echo "----- 赏析预览 -----"
python3 -c "import json;print(json.load(open('$OUT'))['appreciation'][:150],'…')"

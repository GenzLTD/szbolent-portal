#!/usr/bin/env python3
# 04-auto-appreciate.py — P3: 扫描 WP 诗库 → 缺赏析的用本地 AI 生成 → 草稿回写（幂等，可重复跑）
#
# 用法:
#   python3 04-auto-appreciate.py                 # 实跑：扫描全部文章，缺赏析的生成草稿
#   DRY_RUN=1 python3 04-auto-appreciate.py       # 只报告，不写回
#   MODEL=qwen2.5:3b OLLAMA=http://192.168.3.44:11434 python3 04-auto-appreciate.py   # 换对方节点推理
#
# 凭据: 从 ENV_FILE（默认 /Users/jason/THOMAS/.build/p3/wp-bot.env）读 WP_USER / WP_APP_PASS
#       该文件不入版本库、不进交付包
import json, os, re, html, subprocess, sys, urllib.parse

ENV_FILE = os.environ.get("ENV_FILE", "/Users/jason/THOMAS/.build/p3/wp-bot.env")
if os.path.exists(ENV_FILE):
    for line in open(ENV_FILE, encoding="utf-8"):
        line = line.strip()
        if line.startswith("export "):
            line = line[7:]
        if "=" in line:
            k, v = line.split("=", 1)
            os.environ.setdefault(k.strip(), v.strip().strip('"'))
WP_USER = os.environ.get("WP_USER")
WP_APP_PASS = os.environ.get("WP_APP_PASS")
if not WP_USER or not WP_APP_PASS:
    sys.exit("❌ 缺凭据：请设置 WP_USER / WP_APP_PASS（或检查 ENV_FILE）")
WP_IP  = os.environ.get("WP_IP", "192.168.3.44")
MODEL  = os.environ.get("MODEL", "qwen2.5:7b")
OLLAMA = os.environ.get("OLLAMA", "http://127.0.0.1:11434")
DRY    = os.environ.get("DRY_RUN", "0") == "1"
WP = "https://szbolent.cn"

CURL = "/usr/bin/curl"
def curl(*args, body=None, timeout=30):
    cmd = [CURL, "-4", "-s", "-S", "--noproxy", "*", "--resolve", f"szbolent.cn:443:{WP_IP}",
           "-m", str(timeout), "-u", f"{WP_USER}:{WP_APP_PASS}"]
    if body is not None:
        cmd += ["-X", "POST", "-H", "Content-Type: application/json",
                "-d", json.dumps(body, ensure_ascii=False)]
    r = subprocess.run(cmd + list(args), capture_output=True, text=True)
    if r.returncode != 0 or not r.stdout.strip():
        print(f"    curl 诊断: rc={r.returncode} stderr={r.stderr.strip()[:200]}", file=sys.stderr)
    return r.stdout

def strip_html(s):
    return html.unescape(re.sub(r"<[^>]+>", "", s)).strip()

def appreciate(title, body):
    prompt = ("你是古典文学赏析助手。请对下面这首诗写一段 150-220 字的赏析，"
              "包含意象分析、情感主旨、艺术手法三部分，只输出正文，不要标题和客套话。\n\n"
              f"诗题：{title}\n诗文：\n{body}")
    payload = json.dumps({"model": MODEL, "prompt": prompt, "stream": False}, ensure_ascii=False)
    r = subprocess.run(["/usr/bin/curl", "-4", "-s", "--noproxy", "*", "-m", "300", "-d", payload,
                        f"{OLLAMA}/api/generate"], capture_output=True, text=True)
    return json.loads(r.stdout)["response"].strip()

posts = json.loads(curl(f"{WP}/wp-json/wp/v2/posts?per_page=20&_fields=id,title,content"))
print(f"诗库共 {len(posts)} 篇；推理节点 {MODEL} @ {OLLAMA}{'；DRY_RUN' if DRY else ''}")
created = skipped = 0
for p in posts:
    title = strip_html(p["title"]["rendered"])
    apt_title = f"赏析：{title}"
    q = urllib.parse.quote(apt_title)
    existing = json.loads(curl(
        f"{WP}/wp-json/wp/v2/posts?search={q}&status=draft,publish&_fields=id,title"))
    if any(strip_html(e["title"]["rendered"]) == apt_title for e in existing):
        print(f"  跳过《{title}》（已有赏析）")
        skipped += 1
        continue
    body = strip_html(p["content"]["rendered"])
    print(f"  生成《{title}》赏析（{MODEL}，约 60-120s）…")
    text = appreciate(title, body)
    if DRY:
        print(f"    [DRY_RUN] 生成 {len(text)} 字，未写回")
        continue
    resp = curl(f"{WP}/wp-json/wp/v2/posts", body={
        "title": apt_title, "status": "draft",
        "content": (f"<p>{html.escape(text)}</p>\n"
                    f"<p><em>—— 由 bot-appreciator（{MODEL}，局域网本地推理）自动生成，待编辑审阅发布。</em></p>")
    }, timeout=30)
    d = json.loads(resp)
    if d.get("id"):
        print(f"    ✅ 草稿 id={d['id']}（{len(text)} 字）")
        created += 1
    else:
        print(f"    ❌ 失败：{resp[:200]}")
print(f"完成：新增 {created} 篇，跳过 {skipped} 篇")

#!/usr/bin/env bash
# ============================================================
# report-db-env.sh — 本机 DB 环境回执（只读 · 不是断言脚本）
#
# 用法:
#   bash scripts/report-db-env.sh
#
# ── 这是什么 ────────────────────────────────────────────────
#   打印「本机 DB 环境」的回执，把「你这台机器到底有没有 MySQL /
#   PostgreSQL / pgvector」变成可回传的事实。它不下断言，也不判对错。
#
# ── 为什么要有它 ────────────────────────────────────────────
#   DB 环境是典型的「结论随机器而变」：维护者本机没有 pgvector，不代表成员没有；
#   反之亦然。与其在单台机器上争论，不如每台机器各出一份回执、汇总比对。
#
# ── 与另两个脚本的分工（勿混）──────────────────────────────
#   verify-offline.sh   断言：结论与机器无关（L1 契约同步 + L2 类型/构建）
#   report-prod.sh      回执：打公网 + SSH 到生产机，结论随公网与时点而变
#   report-db-env.sh    回执：打本机 DB 环境（本脚本）
#
# ── 本仓的 DB 事实（先读，别搞混）──────────────────────────
#   本仓 Page Engine 用 **MySQL**（backend/.env.example:
#   mysql://looma:looma@127.0.0.1:3306/looma）。
#   **pgvector 不是本仓依赖**，它属 Looma 生态；这里一并采集，是因为
#   「跨机 DB 一致性」需要两侧事实，不是因为门户需要它。
#
# ── 只读保证（可放心在成员机器上跑）────────────────────────
#   - 不写库、不建库、不 CREATE EXTENSION、不启停任何服务、不安装任何东西
#   - 不打印口令：口令仅经环境变量（MYSQL_PWD / PGPASSWORD）传给客户端，
#     绝不进命令行参数、绝不进日志（ps 里看不到）
#   - 客户端缺失不算失败：缺 mysql / psql 时自动降级到
#     「端口层 + 扩展文件层」两层探测
#
# ── 退出码 ──────────────────────────────────────────────────
#   0 = 回执已产出（包括「什么都没有」——那本身是有价值的结论）
#   2 = 探针自身跑不起来（bash 基础能力缺失）
# ============================================================
set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

GREEN='\033[0;32m'; RED='\033[0;31m'; YELLOW='\033[0;33m'; DIM='\033[2m'; NC='\033[0m'

OS_DESC="$(uname -s)/$(uname -m)"
SHA="$(git rev-parse --short HEAD 2>/dev/null || echo 'no-git')"

declare -a REC=()
rec() { REC+=("$1=$2"); }

printf "\n============================================\n"
printf "  report-db-env — 本机 DB 环境回执（只读）\n"
printf "  %s  @ %s\n" "$(date '+%Y-%m-%d %H:%M:%S')" "$SHA"
printf "============================================\n"
rec os "$OS_DESC"

# ── 1. 端口层：零依赖，先判「有没有东西在听」──────────────
printf "\n--- 1. 端口层（零依赖）---\n"
tcp_open() {  # tcp_open <host> <port> → 0 = 有服务在听
  if command -v nc >/dev/null 2>&1; then
    nc -z -w 2 "$1" "$2" >/dev/null 2>&1
  else
    (exec 3<>"/dev/tcp/$1/$2") >/dev/null 2>&1
  fi
}
port_line() {  # port_line <port> <说明> <回执键>
  local st
  if tcp_open 127.0.0.1 "$1"; then st="open"; else st="closed"; fi
  printf "  :%-5s %-7s %s\n" "$1" "$st" "$2"
  rec "$3" "$st"
}
port_line 3306 "Page Engine 的 MySQL（本仓契约要求 looma 库）" tcp3306
port_line 3307 "compose 的 WordPress MySQL（modown_wp）"     tcp3307
port_line 5432 "PostgreSQL 通用口（ServBay / Homebrew 常用）" tcp5432
port_line 5433 "Looma 生态约定的 pgvector 口"                 tcp5433

# ── 2. 客户端可用性 ────────────────────────────────────────
printf "\n--- 2. 客户端 ---\n"
MYSQL_BIN=""
for c in mysql mariadb; do
  if command -v "$c" >/dev/null 2>&1; then MYSQL_BIN="$c"; break; fi
done
PSQL_BIN="$(command -v psql 2>/dev/null || true)"
printf "  mysql / mariadb CLI: %s\n" "${MYSQL_BIN:-（无）}"
printf "  psql CLI:            %s\n" "${PSQL_BIN:-（无）}"
rec mysql_cli "${MYSQL_BIN:-absent}"
rec psql_cli "${PSQL_BIN:-absent}"

# ── 3. MySQL 深探（本仓契约：mysql://…@127.0.0.1:3306/looma）──
printf "\n--- 3. MySQL 深探（本仓契约目标）---\n"
M_HOST="127.0.0.1"; M_PORT="3306"; M_DB="looma"; M_USER="looma"; M_PASS="looma"
M_SRC=".env.example 默认值"
if [ -f "$ROOT/backend/.env" ]; then
  DSN="$(sed -n 's/^[[:space:]]*DATABASE_URL[[:space:]]*=[[:space:]]*//p' "$ROOT/backend/.env" | tail -1)"
  # 仅解析，不打印；口令留在变量里，只经 MYSQL_PWD 传递。
  if [[ "$DSN" =~ ^mysql://([^:]+):([^@]*)@([^:/]+):([0-9]+)/(.+)$ ]]; then
    M_USER="${BASH_REMATCH[1]}"; M_PASS="${BASH_REMATCH[2]}"
    M_HOST="${BASH_REMATCH[3]}"; M_PORT="${BASH_REMATCH[4]}"; M_DB="${BASH_REMATCH[5]}"
    M_SRC="backend/.env"
  fi
fi
printf "  目标: %s@%s:%s/%s  ${DIM}(来源 %s；口令不打印)${NC}\n" \
  "$M_USER" "$M_HOST" "$M_PORT" "$M_DB" "$M_SRC"
rec mysql_target "${M_HOST}:${M_PORT}/${M_DB}"

MYSQL_STATE="absent"
if [ -n "$MYSQL_BIN" ]; then
  MYSQL_STATE="no"
  M_VER="$(MYSQL_PWD="$M_PASS" "$MYSQL_BIN" --protocol=TCP \
    -h "$M_HOST" -P "$M_PORT" -u "$M_USER" --connect-timeout=3 \
    -N -B -e 'SELECT VERSION()' 2>/dev/null)"
  if [ -n "$M_VER" ]; then
    MYSQL_STATE="yes"
    printf "  连接: ${GREEN}成功${NC}  server=%s\n" "$M_VER"
    rec mysql_version "$M_VER"

    HAS_DB="$(MYSQL_PWD="$M_PASS" "$MYSQL_BIN" --protocol=TCP \
      -h "$M_HOST" -P "$M_PORT" -u "$M_USER" --connect-timeout=3 \
      -N -B -e "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME='$M_DB'" 2>/dev/null)"
    if [ -n "$HAS_DB" ]; then
      printf "  库 %s: ${GREEN}存在${NC}\n" "$M_DB"
      rec mysql_target_db present
      TBL="$(MYSQL_PWD="$M_PASS" "$MYSQL_BIN" --protocol=TCP \
        -h "$M_HOST" -P "$M_PORT" -u "$M_USER" --connect-timeout=3 -N -B \
        -e "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA='$M_DB' AND TABLE_NAME IN ('share_menus','share_pages')" 2>/dev/null)"
      printf "  关键表 share_menus / share_pages: %s/2\n" "${TBL:-0}"
      rec mysql_schema_tables "${TBL:-0}of2"
    else
      printf "  库 %s: ${YELLOW}不存在${NC}（Page Engine 起不来：它会连这个库）\n" "$M_DB"
      rec mysql_target_db absent
    fi
  else
    printf "  连接: ${RED}失败${NC}（客户端在，但连不上：库/账号/口令/未启动，四者之一）\n"
  fi
else
  printf "  连接: ${YELLOW}未探测${NC}（无 mysql / mariadb 客户端）\n"
fi
rec mysql_connect "$MYSQL_STATE"

# ── 4. PostgreSQL / pgvector 深探 ──────────────────────────
# 关键三分：端口开着 / 扩展文件在 / 库里真的 CREATE EXTENSION 了。
# 「ServBay 带 pgvector 文件」与「这个库启用了 pgvector」是两件事，勿混。
printf "\n--- 4. PostgreSQL / pgvector 深探 ---\n"
PG_TRIED=0
PG_VECTOR_INSTALLED=no
for p in 5432 5433; do
  if ! tcp_open 127.0.0.1 "$p"; then
    printf "  :%s ${DIM}跳过（端口未开）${NC}\n" "$p"
    continue
  fi
  PG_TRIED=1
  if [ -z "$PSQL_BIN" ]; then
    printf "  :%s ${YELLOW}端口开着，但无 psql 客户端 → 无法判定是否启用 pgvector${NC}\n" "$p"
    continue
  fi
  # -w: 永不交互索要口令，避免探针挂住
  PG_VER="$(PGCONNECT_TIMEOUT=3 "$PSQL_BIN" -w -h 127.0.0.1 -p "$p" \
    -U "${PGUSER:-$USER}" -d "${PGDATABASE:-postgres}" -tAc 'SHOW server_version' 2>/dev/null)"
  if [ -z "$PG_VER" ]; then
    printf "  :%s ${YELLOW}连不上${NC}（-w 不交互：需口令或 .pgpass 未配）\n" "$p"
    continue
  fi
  printf "  :%s ${GREEN}已连接${NC}  server_version=%s\n" "$p" "$PG_VER"
  rec "pg${p}_server" "$PG_VER"

  AVAIL="$(PGCONNECT_TIMEOUT=3 "$PSQL_BIN" -w -h 127.0.0.1 -p "$p" \
    -U "${PGUSER:-$USER}" -d "${PGDATABASE:-postgres}" -tAc \
    "SELECT COUNT(*) FROM pg_available_extensions WHERE name='vector'" 2>/dev/null)"
  INST="$(PGCONNECT_TIMEOUT=3 "$PSQL_BIN" -w -h 127.0.0.1 -p "$p" \
    -U "${PGUSER:-$USER}" -d "${PGDATABASE:-postgres}" -tAc \
    "SELECT COALESCE((SELECT extversion FROM pg_extension WHERE extname='vector'),'')" 2>/dev/null)"
  [ "$AVAIL" = "1" ] && AVAIL_TXT="yes" || AVAIL_TXT="no"
  [ -n "$INST" ] && { INST_TXT="$INST"; PG_VECTOR_INSTALLED=yes; } || INST_TXT="no"
  printf "      pgvector 可用（扩展文件已装）: %s\n" "$AVAIL_TXT"
  printf "      pgvector 已启用（本库 extversion）: %s\n" "$INST_TXT"
  rec "pg${p}_vector_available" "$AVAIL_TXT"
  rec "pg${p}_vector_installed" "$INST_TXT"
done

# ── 5. 文件层：pgvector 扩展文件（零客户端也能判）──────────
printf "\n--- 5. 文件层：pgvector 扩展文件（零客户端）---\n"
VLIST=""
for pat in \
  "/Applications/ServBay/package/postgresql"/*/*/share/extension/vector.control \
  "/Applications/ServBay/package/postgresql"/*/share/postgresql*/extension/vector.control \
  "/opt/homebrew/share/postgresql"*/extension/vector.control \
  "/opt/homebrew/opt/postgresql"*"/share/postgresql"*/extension/vector.control \
  "/usr/local/share/postgresql"*/extension/vector.control \
  "/usr/local/opt/postgresql"*"/share/postgresql"*/extension/vector.control \
  "/usr/share/postgresql"/*/extension/vector.control \
  "/Applications/Postgres.app/Contents/Versions"/*/share/postgresql/extension/vector.control ; do
  for f in $pat; do
    [ -e "$f" ] && VLIST="${VLIST}${f}
"
  done
done
VUNIQ="$(printf '%s' "$VLIST" | sed '/^[[:space:]]*$/d' | sort -u)"
VHITS="$(printf '%s\n' "$VUNIQ" | grep -c . 2>/dev/null || true)"
[ -n "$VUNIQ" ] || VHITS=0
if [ "$VHITS" -gt 0 ]; then
  printf "  找到 %s 处 pgvector 扩展文件：\n" "$VHITS"
  printf '%s\n' "$VUNIQ" | sed 's/^/      /'
  VDIR="$(dirname "$(printf '%s\n' "$VUNIQ" | head -1)")"
  VSAMPLE="$(ls "$VDIR" 2>/dev/null | grep -E '^vector--[0-9]' | sort -V | tail -1)"
  printf "  版本脚本样例: %s\n" "${VSAMPLE:-（未找到 vector--*.sql）}"
else
  printf "  ${DIM}未找到 pgvector 扩展文件（系统自带 PG 或未装扩展时属正常）${NC}\n"
fi
rec vector_files "$VHITS"

# ── 6. Docker 容器（若有且 daemon 在跑）────────────────────
printf "\n--- 6. Docker 容器（若有）---\n"
DOCKER_STATE="unavailable"
if command -v docker >/dev/null 2>&1 && docker info >/dev/null 2>&1; then
  DOCKER_STATE="ok"
  HIT="$(docker ps --format '{{.Names}} | {{.Image}} | {{.Ports}}' 2>/dev/null \
    | grep -iE 'mysql|maria|postgres|pgvector|chroma' | head -10)"
  if [ -n "$HIT" ]; then printf '%s\n' "$HIT" | sed 's/^/      /'; else
    printf "  ${DIM}无 DB 相关容器在跑${NC}\n"
  fi
else
  printf "  ${DIM}docker 不可用或 daemon 未运行${NC}\n"
fi
rec docker "$DOCKER_STATE"

# ── 7. 判定：pgvector 到底在哪一层 ─────────────────────────
if [ "$PG_VECTOR_INSTALLED" = "yes" ]; then
  VERDICT="extension-in-db"
elif [ "$VHITS" -gt 0 ]; then
  VERDICT="files-only"
elif [ "$PG_TRIED" = "1" ]; then
  VERDICT="unknown-needs-client-or-creds"
else
  VERDICT="none"
fi
case "$VERDICT" in
  extension-in-db)             VNOTE="本机某可达 PG 库里已启用 vector 扩展" ;;
  files-only)                  VNOTE="只有扩展文件，没有任何库启用它（缺实例或缺 CREATE EXTENSION）" ;;
  unknown-needs-client-or-creds) VNOTE="有 PG 端口在听但连不上（缺 psql 或口令），需人工补一步" ;;
  *)                           VNOTE="既无监听端口也无扩展文件" ;;
esac

LINE=""
for r in "${REC[@]}"; do LINE="${LINE}${LINE:+ | }$r"; done

printf "\n============================================\n"
printf "  DB-ENV 回执 @ %s\n" "$SHA"
printf "  %s | pgvector_verdict=%s\n" "$LINE" "$VERDICT"
printf "============================================\n"
printf "  pgvector 判定: %s —— %s\n" "$VERDICT" "$VNOTE"
printf "\n"
printf "  ${DIM}把上面 DB-ENV 回执整行贴回即可。本脚本是回执不是断言，\n"
printf "  「什么都没有」也是有效结论——那正说明 DB 环境需要统一。${NC}\n"
printf "  ${DIM}口径与解读见 docs/DB_ENV_PROBE.md${NC}\n\n"

exit 0

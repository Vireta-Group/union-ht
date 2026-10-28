#!/usr/bin/env bash
# Start the ViretaDev dev server so it stays up and every request is logged.
#
# WHY A SCRIPT: `php -S` dies the moment its terminal closes, so every session
# had to restart it by hand. This detaches it (setsid + nohup), writes its own
# stderr, and tails the request log so the tenant Host header — the thing you
# cannot type into a browser address bar here, because /etc/hosts needs sudo —
# is visible for every hit.
#
# Usage:
#   ./dev.sh          start (idempotent — won't double-start)
#   ./dev.sh stop     stop
#   ./dev.sh restart  stop + start + clear log
#   ./dev.sh log      tail the request log (follow)
#   ./dev.sh status   is it up, how many hits today
#
# Routes:
#   Tenant portal   Host: dhum.local        http://localhost:8080/
#   Product site    Host: localhost         http://localhost:8080/
#   Admin (login)   Host: dhum.local        http://localhost:8080/login_check
#   Diagnostics     Host: dhum.local        http://localhost:8080/t_diag
#
# IMPORTANT: router.php is REQUIRED (not `-t .`). It mirrors the /.htaccess deny
# rules because php -S ignores .htaccess — without it .env and any dump are
# served raw. It also logs each request (host, method, path, status) to
# application/logs/dev-request.log.

set -uo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PORT="${PORT:-8080}"
LOG_DIR="$ROOT/application/logs"
REQ_LOG="$LOG_DIR/dev-request.log"
SRV_LOG="$LOG_DIR/dev-server.log"
PID_FILE="$LOG_DIR/dev-server.pid"

is_up() { [ -f "$PID_FILE" ] && kill -0 "$(cat "$PID_FILE")" 2>/dev/null; }

start() {
  mkdir -p "$LOG_DIR"
  if is_up; then
    echo "already running (pid $(cat "$PID_FILE")) on http://localhost:$PORT"
    return 0
  fi
  cd "$ROOT"
  # setsid detaches from this shell's session so it survives the terminal.
  setsid nohup php -S "localhost:$PORT" "$ROOT/router.php" \
    >>"$SRV_LOG" 2>&1 < /dev/null &
  echo $! > "$PID_FILE"
  sleep 1
  if is_up; then
    echo "started (pid $(cat "$PID_FILE")) on http://localhost:$PORT"
    echo "request log : $REQ_LOG"
    echo "server log  : $SRV_LOG"
    echo "tail it with: ./dev.sh log"
  else
    echo "failed to start — see $SRV_LOG"
    return 1
  fi
}

stop() {
  if is_up; then
    kill "$(cat "$PID_FILE")" 2>/dev/null
    sleep 1
    kill -9 "$(cat "$PID_FILE")" 2>/dev/null
    echo "stopped"
  else
    echo "not running"
  fi
  rm -f "$PID_FILE"
}

case "${1:-start}" in
  start)   start ;;
  stop)    stop ;;
  restart) stop; : > "$REQ_LOG"; start ;;
  log)     touch "$REQ_LOG"; tail -f "$REQ_LOG" ;;
  status)
    if is_up; then
      echo "up (pid $(cat "$PID_FILE")) on http://localhost:$PORT"
      [ -f "$REQ_LOG" ] && echo "hits logged: $(wc -l < "$REQ_LOG")"
    else
      echo "down — ./dev.sh start"
    fi ;;
  *) echo "usage: ./dev.sh [start|stop|restart|log|status]"; exit 1 ;;
esac
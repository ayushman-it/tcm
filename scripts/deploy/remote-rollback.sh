#!/usr/bin/env bash
set -euo pipefail

deploy_root=${1:?deploy root is required}
backup_id=${2:?backup id is required}
public_root=${3:?public root is required}
health_url=${4:?health URL is required}

case "$deploy_root" in /home/*/deployments/thecodemunk) ;; *) echo "Unsafe deploy root" >&2; exit 1 ;; esac
case "$public_root" in /home/*/domains/thecodemunk.in/public_html) ;; *) echo "Unsafe public root" >&2; exit 1 ;; esac
[[ "$backup_id" =~ ^[0-9a-f]{7,64}$ ]] || { echo "Invalid backup id" >&2; exit 1; }

backup="$deploy_root/backups/$backup_id"
recovery_root="$deploy_root/rollback-recovery"
recovery="$recovery_root/$(date -u +%Y%m%d%H%M%S)-$backup_id"
lock_file="$deploy_root/deploy.lock"

[[ -f "$backup/app.php" && -f "$backup/index.html" ]] || { echo "Backup is missing or incomplete: $backup_id" >&2; exit 1; }
mkdir -p "$recovery"
exec 9>"$lock_file"
flock -n 9 || { echo "Another production operation is already running" >&2; exit 1; }

find "$backup" -type f -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
rsync -a --delete \
  --exclude='/.env' --exclude='/uploads/***' --exclude='/storage/***' \
  --exclude='/.well-known/***' --exclude='/error_log' \
  "$public_root/" "$recovery/"

restore_required=1
restore_current() {
  status=$?
  if (( restore_required )); then
    echo "Rollback failed; restoring the pre-rollback application" >&2
    rsync -a --delete \
      --exclude='/.env' --exclude='/uploads/***' --exclude='/storage/***' \
      --exclude='/.well-known/***' --exclude='/error_log' \
      "$recovery/" "$public_root/"
  fi
  exit "$status"
}
trap restore_current EXIT

rsync -a --delete-delay \
  --exclude='/.env' --exclude='/uploads/***' --exclude='/storage/***' \
  --exclude='/.well-known/***' --exclude='/error_log' \
  "$backup/" "$public_root/"

test -f "$public_root/.env"
chmod 0600 "$public_root/.env"
curl --fail --silent --show-error --retry 3 --retry-delay 2 --max-time 15 "$health_url/health" >/dev/null
curl --fail --silent --show-error --retry 3 --retry-delay 2 --max-time 15 "$health_url/" >/dev/null

restore_required=0
trap - EXIT
find "$recovery_root" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -nr | tail -n +4 | cut -d' ' -f2- | xargs -r rm -rf --
echo "Rollback to backup $backup_id completed and passed health checks."

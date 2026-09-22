#!/usr/bin/env bash
set -euo pipefail

deploy_root=${1:?deploy root is required}
release_sha=${2:?release SHA is required}
public_root=${3:?public root is required}
health_url=${4:?health URL is required}

case "$deploy_root" in /home/*/deployments/thecodemunk) ;; *) echo "Unsafe deploy root" >&2; exit 1 ;; esac
case "$public_root" in /home/*/domains/thecodemunk.in/public_html) ;; *) echo "Unsafe public root" >&2; exit 1 ;; esac
[[ "$release_sha" =~ ^[0-9a-f]{7,64}$ ]] || { echo "Invalid release SHA" >&2; exit 1; }

candidate="$deploy_root/candidates/$release_sha"
backup_root="$deploy_root/backups"
backup="$backup_root/$release_sha"
lock_file="$deploy_root/deploy.lock"

[[ -f "$candidate/.release-manifest" && -f "$candidate/app.php" ]] || { echo "Incomplete release candidate" >&2; exit 1; }
mkdir -p "$backup_root"
exec 9>"$lock_file"
flock -n 9 || { echo "Another deployment is already running" >&2; exit 1; }

find "$candidate" -type f -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null

mkdir -p "$backup"
rsync -a --delete \
  --exclude='/.env' --exclude='/uploads/***' --exclude='/storage/***' \
  --exclude='/.well-known/***' --exclude='/error_log' \
  "$public_root/" "$backup/"

rollback_required=1
rollback() {
  status=$?
  if (( rollback_required )); then
    echo "Deployment failed; restoring $release_sha backup" >&2
    rsync -a --delete \
      --exclude='/.env' --exclude='/uploads/***' --exclude='/storage/***' \
      --exclude='/.well-known/***' --exclude='/error_log' \
      "$backup/" "$public_root/"
  fi
  exit "$status"
}
trap rollback EXIT

rsync -a --delete-delay \
  --exclude='/.release-manifest' --exclude='/.env' --exclude='/uploads/***' --exclude='/storage/***' \
  --exclude='/.well-known/***' --exclude='/error_log' \
  "$candidate/" "$public_root/"

chmod 0600 "$public_root/.env"

curl --fail --silent --show-error --retry 3 --retry-delay 2 --max-time 15 "$health_url/health" >/dev/null
curl --fail --silent --show-error --retry 3 --retry-delay 2 --max-time 15 "$health_url/" >/dev/null

rollback_required=0
trap - EXIT
find "$deploy_root/candidates" -mindepth 1 -maxdepth 1 -type d ! -name "$release_sha" -mtime +1 -exec rm -rf -- {} +
find "$backup_root" -mindepth 1 -maxdepth 1 -type d -printf '%T@ %p\n' | sort -nr | tail -n +6 | cut -d' ' -f2- | xargs -r rm -rf --
echo "Deployment $release_sha completed and passed health checks."

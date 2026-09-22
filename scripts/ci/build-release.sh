#!/usr/bin/env bash
set -euo pipefail

release_dir=${1:-release}
case "$release_dir" in
  ''|/|.|..|~|"$HOME") echo "Unsafe release directory: $release_dir" >&2; exit 1 ;;
esac

mkdir -p "$release_dir"
find "$release_dir" -mindepth 1 -depth -delete

rsync -a --delete \
  --exclude="/$release_dir/***" \
  --exclude='/.git/' \
  --exclude='/.github/' \
  --exclude='/docs/***' \
  --exclude='/scripts/' \
  --exclude='/.env' \
  --exclude='/.env.*' \
  --exclude='/*.md' \
  --exclude='/uploads/***' \
  --exclude='/storage/***' \
  --exclude='/setup.php' \
  --exclude='/prod-debug.php' \
  --exclude='/quick-debug.php' \
  --exclude='/test*.php' \
  --exclude='/check*.php' \
  --exclude='/fix*.php' \
  --exclude='/migrate*.php' \
  --exclude='/generate-content.php' \
  --exclude='/generate_icons.php' \
  --exclude='/setup-ai-content.php' \
  --exclude='/verify_fixes.php' \
  --exclude='/bin/install.php' \
  --exclude='/bin/test-mail.php' \
  ./ "$release_dir/"

printf '%s\n' "${GITHUB_SHA:-local}" > "$release_dir/.release-manifest"

required=(.htaccess app.php index.html src/bootstrap.php)
for path in "${required[@]}"; do
  [[ -f "$release_dir/$path" ]] || { echo "Release is missing $path" >&2; exit 1; }
done

for forbidden in .env .env.production setup.php prod-debug.php quick-debug.php; do
  [[ ! -e "$release_dir/$forbidden" ]] || { echo "Forbidden release file: $forbidden" >&2; exit 1; }
done

echo "Release package built at $release_dir"

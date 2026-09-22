#!/usr/bin/env bash
set -euo pipefail

patterns=(
  '-----BEGIN (RSA |EC |OPENSSH )?PRIVATE KEY-----'
  'sk-or-v1-[A-Za-z0-9_-]{20,}'
  'sk-[A-Za-z0-9]{32,}'
  'GOCSPX-[A-Za-z0-9_-]{20,}'
  'AKIA[0-9A-Z]{16}'
  '(DB_PASSWORD|MAIL_PASSWORD|GOOGLE_CLIENT_SECRET|OPENROUTER_API_KEY)[[:space:]]*=[[:space:]]*[^[:space:]#]+'
)

allowlist='(your[-_]|change[-_]|replace[-_]|example|placeholder|\.env\.example|\.env\.production)'
failed=0
for pattern in "${patterns[@]}"; do
  matches=$(git grep -n -I -E -e "$pattern" -- . ':!scripts/ci/check-secrets.sh' || true)
  matches=$(printf '%s\n' "$matches" | grep -Eiv "$allowlist" || true)
  if [[ -n "$matches" ]]; then
    printf 'Potential committed secret matching %s:\n%s\n' "$pattern" "$matches" >&2
    failed=1
  fi
done

if (( failed )); then
  echo "Secret scan failed. Remove the value from Git and rotate it if it was real." >&2
  exit 1
fi

echo "Secret scan passed."

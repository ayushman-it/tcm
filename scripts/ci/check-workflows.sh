#!/usr/bin/env bash
set -euo pipefail

failed=0
while IFS= read -r use_line; do
  action_ref=${use_line#*@}
  action_ref=${action_ref%% *}
  if [[ ! "$action_ref" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Mutable GitHub Action reference: $use_line" >&2
    failed=1
  fi
done < <(grep -RhoE 'uses:[[:space:]]+[^[:space:]]+@[A-Za-z0-9._/-]+([[:space:]]+#[^[:space:]]+)?' .github/workflows || true)

if grep -Rni 'StrictHostKeyChecking=accept-new' .github/workflows scripts/deploy; then
  echo "Trust-on-first-use is forbidden in deployment automation." >&2
  failed=1
fi

if (( failed )); then
  exit 1
fi

echo "Workflow supply-chain and SSH trust checks passed."

# Production delivery

The GitHub Actions workflow in `.github/workflows/ci-cd.yml` is the only supported automated deployment path for `thecodemunk.in`.

## Flow

1. Pull requests and `main` pushes run the tracked-secret scan, PHP syntax checks, and a production-package build.
2. A successful `main` run uploads an immutable candidate outside `public_html`.
3. The host validates every PHP file, locks deployment, and snapshots the current application files.
4. `rsync` activates the candidate while preserving `.env`, `uploads`, `storage`, `.well-known`, and the server error log.
5. `/health` and `/` must return successful HTTP responses. A failed check automatically restores the snapshot.
6. The five newest backups are retained under `~/deployments/thecodemunk/backups`.

Database migrations are deliberately not executed by this workflow. They require a separately reviewed, backward-compatible migration and backup plan.

## Required GitHub production configuration

Environment secrets:

- `HOSTINGER_SSH_PRIVATE_KEY`
- `HOSTINGER_KNOWN_HOSTS`

Environment variables:

- `HOSTINGER_SSH_HOST`
- `HOSTINGER_SSH_PORT`
- `HOSTINGER_SSH_USER`

The `production` environment accepts only `main`, requires one founder's approval, prevents self-review and prevents administrator bypass. The SSH host pin must be compared with an already trusted connection before it is stored. Every connection uses batch mode and strict host-key checking.

Protect `main` so `PHP and release checks` and one independent approval are required before merging. Stale approvals must be dismissed, conversations resolved, force pushes blocked and deletion disabled.

Deployment is disabled by default. A repository administrator must create the `production` environment, add the secrets above, then set the repository variable `HOSTINGER_DEPLOY_ENABLED` to `true`. Until then, CI runs normally and the production job is skipped.

## Manual rollback

Automatic rollback runs on failed deployment health checks. For an operator rollback, run **Roll back Hostinger production**, enter a retained backup directory ID and type `ROLLBACK`. The protected environment requires approval before SSH secrets become available.

The rollback process locks production, validates the selected backup and all PHP files, snapshots the current application, preserves `.env`, uploads and storage, then checks `/health` and `/`. If rollback verification fails, it restores the pre-rollback snapshot automatically. Backup directory IDs are the SHA of the deployment that created the pre-deploy snapshot; confirm the desired release from the deployment log before approval.

## Credential handling

Only `.env.example` may be tracked. `.env`, `.env.production` and every other environment-specific file are excluded and rejected by CI. A committed credential must be removed from current source and rotated at its provider; deleting the file from Git does not revoke historical values.

Firebase web configuration is distributed to browsers by design. Its Google API key must be restricted in Google Cloud to the approved web origins and required APIs; it is not a substitute for a privileged FCM server credential. Server credentials must never appear in browser assets.

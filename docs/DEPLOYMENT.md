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

## Required GitHub production secrets

- `HOSTINGER_SSH_HOST`
- `HOSTINGER_SSH_PORT`
- `HOSTINGER_SSH_USER`
- `HOSTINGER_SSH_PRIVATE_KEY`
- `HOSTINGER_KNOWN_HOSTS`

Configure branch protection so `PHP and release checks` is required before merging into `main`. Configure reviewers on the GitHub `production` environment if the account plan supports deployment approvals.

Deployment is disabled by default. A repository administrator must create the `production` environment, add the secrets above, then set the repository variable `HOSTINGER_DEPLOY_ENABLED` to `true`. Until then, CI runs normally and the production job is skipped.

## Manual rollback

Automatic rollback runs on failed health checks. For a manual rollback, select a known-good directory from `~/deployments/thecodemunk/backups` and synchronize it to `public_html` with the same persistent-file exclusions used by `scripts/deploy/remote-deploy.sh`. Do not overwrite `.env`, `uploads`, or `storage`.

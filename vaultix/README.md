# Vaultix

Shippin’s secrets product. Customers keep keys out of chats, Lovable prompts,
and git. They do not need to know Infisical exists.

| Surface | Intent |
|---------|--------|
| **Vaultix** | Customer name. Free with Shippin, like Zuul. |
| **Infisical** | Upstream we already use in cloud (`app.infisical.com`). MIT core. |
| **Not this** | HashiCorp Vault, Infisical `/ee` paid features, Warden |

**Live (2026-08-17):** MIT core is up on the Zuul host behind Caddy.

| | |
|--|--|
| URL | https://vaultix.shippin.cloud |
| First operator | https://vaultix.shippin.cloud/admin/signup |
| Pin | `infisical/infisical:v0.162.19` |
| Data | `/opt/vaultix/` on `192.227.210.218` |
| Keys | `/opt/vaultix/secrets/` — lose `ENCRYPTION_KEY` and the dump is paper |
| Backup | cron 03:15 UTC → `/opt/vaultix/backups/` |

Infisical Cloud remains the operator store until we export. The UI still says Infisical until the branding overlay lands. Create the admin on `/admin/signup`, then we lock signup.

Do not put a second copy on `devbackend.shippin.cloud` (1 GB).

Panel contract: [contracts/shippin.vaultix.panel.v1.json](contracts/shippin.vaultix.panel.v1.json)  
Panel adapter: [panel/](panel/) — Go, `compose/vaultix.yml` service `panel`, 127.0.0.1:8201  
Lovable → vibe path: [docs/0002-lovable-to-vibe.md](docs/0002-lovable-to-vibe.md)  
Panel flow + Infisical link: [docs/0004-panel-flow-and-infisical-link.md](docs/0004-panel-flow-and-infisical-link.md)  
API namespace + endpoint ledger: [docs/0005-canonical-api-namespace.md](docs/0005-canonical-api-namespace.md) — canon in [schemas/shippin.vaultix.api.v1.json](schemas/shippin.vaultix.api.v1.json), enforced by `go test` in `panel/`  
Wire verification + bootstrap + smoke runbook: [docs/0006-wire-protocol-verification.md](docs/0006-wire-protocol-verification.md) — identities via [scripts/bootstrap-identity.sh](scripts/bootstrap-identity.sh)  
Forked schema (Vaultix-owned): [db/](db/) — 504 migrations, 280 schemas; ownership + rebrand in [docs/0007-schema-fork.md](docs/0007-schema-fork.md)  
Layman UX + Doppler gap: [docs/0008-doppler-gap-and-layman-ux.md](docs/0008-doppler-gap-and-layman-ux.md)  
Clyffy specialist boundary (viewport-operator): [docs/0009-clyffy-specialist-boundary.md](docs/0009-clyffy-specialist-boundary.md) — source API live in [panel/internal/source/](panel/internal/source/) + [web/…/vaultix/](../shippin/web/src/domain/vaultix/)  
Doppler capability gap ledger: [docs/0010-doppler-capability-gap-ledger.md](docs/0010-doppler-capability-gap-ledger.md) — from [docs/references/](docs/references/) screenshots + the forked schema

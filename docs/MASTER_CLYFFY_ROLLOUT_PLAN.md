---
wardenclyffe_touchpoint:
  version: 1
  kind: master-clyffy-rollout
  namespace: wardenclyffe.clyffy.master
  owner: docs/MASTER_CLYFFY_ROLLOUT_PLAN.md
  module: module-02-clyffe
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  reads:
    - docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md
    - docs/FOUNDATION_SERVICE_MATRIX.md
    - docs/FOUNDATION_APP_RESEARCH_2026_05.md
    - docs/WARDEN_CLYFFE_PILOT_ROADMAP.md
    - docs/CLYFFY_DYNAMIC_UI_SPEC.md
    - docs/CLYFFY_DYNAMIC_UI_POAM.md
    - wardenclyffe/docs/infra-state.md
    - wardenclyffe/docs/decisions/0027-clyffy-ai-operator-estate-and-supabase-deprecation.md
---

# Master Clyffy Rollout Plan

This is the narrow rollout plan for making `master.clyffy.ai` resolve and serve
the initial personal/master Clyffy service without confusing it with the later
WardenClyffe customer portal.

Warden owns the infrastructure, route intent, DNS, certificates, Postgres
maintenance, and Proxmox inventory. Clyffe consumes Warden APIs for
customer-safe service views. Clyffy is the personal/master AI assistant surface
being brought online first.

## Current Verified State

Read-only checks on 2026-05-22 found:

| Item | Fact |
|---|---|
| Public homebase | `104.176.44.101` |
| Proxmox host | `server1`, Proxmox VE `9.1.9` |
| Public bridge | `vmbr0` |
| Internal bridge | `vmbr1`, `10.0.0.1/24` |
| Postgres VMID | LXC `110`, `clyffy-pg-master`, `10.0.0.110/24`, tags `adr0017;clyffy-master;pg;plane-b` |
| Planned app VMID | LXC `120`, not present yet |
| Public edge VMID | LXC `115`, `clyffy-edge`, `10.0.0.115` |
| Legacy edge | VM `501`, still running but removed from public NAT |
| `master.clyffy.ai` public DNS | no public A record found |
| `clyffy.ai` public DNS | Cloudflare proxied records reach `clyffy-edge` `/healthz` |

This means `master.clyffy.ai` cannot be completed by DNS alone. It needs the
internal app target, edge route, TLS, and identity path to exist.

## Naming Contract

Use these names consistently:

| Name | Meaning |
|---|---|
| Warden | operator/server-control platform |
| Clyffe | customer portal, KB, tickets, CRM, customer-safe service panel |
| Clyffy | personal/master AI assistant and assistant-as-a-service runtime |
| `clyffy.master` | first internal namespace for the personal/master assistant |
| `clyffe.wardenclyffe` | future customer-safe WardenClyffe portal namespace |

Do not overload `master.clyffy.ai` as the final customer portal. It is the
first master assistant entry point. The later WardenClyffe/Clyffe UI should use
its own namespace and route intent so its node-network and workspace views are
customer-safe from the start.

## Postgres Update Position

Postgres remains the correct Warden/Clyffe product-truth database for tenants,
inventory, RBAC, tickets, CRM, support workflows, route intent, and audit.

As of the 2026-05-14 PostgreSQL release train, the current major is PostgreSQL
18.4 and the current PostgreSQL 17 minor is 17.10. LXC `110` is documented as
Postgres 17. Therefore:

1. Patch LXC `110` within its current major first, after a snapshot and backup.
2. Treat PostgreSQL 18 as a controlled major upgrade, not a same-command patch.
3. Prefer a side-by-side restore drill before promoting PostgreSQL 18 for
   Warden/Clyffe product truth.
4. Do not change schemas, users, or auth rules as part of the package update
   unless a migration explicitly owns that change.

Minimum live preflight before any Postgres write:

```text
psql --version
pg_lsclusters
systemctl status postgresql
pg_dumpall --globals-only
pg_dump --format=custom <database>
```

Minimum rollback material:

- Proxmox snapshot or backup of LXC `110`.
- `pg_dumpall --globals-only`.
- custom-format dump for every Warden/Clyffe database.
- recorded package versions before and after.
- restore test on a non-production target.

## DNS And Route Contract

Use split-horizon DNS deliberately:

| Scope | Record | Target |
|---|---|---|
| Public Cloudflare | `master.clyffy.ai` A | `104.176.44.101` |
| Internal PowerDNS | `master.clyffy.ai` A | `10.0.0.120` |
| Internal edge route | `master.clyffy.ai` | `10.0.0.120:8080` behind Authentik |
| Future customer portal | separate Clyffe route | Warden-owned tenant-safe route |

Public DNS should not point directly at `10.0.0.120`. Public traffic lands on
the homebase public IP, then Warden-owned edge routing sends it to the internal
service.

## Rollout Phases

### Phase 0: Guardrails

Completed in scaffold:

- The LXC `120` provisioner must default to `vmbr1` when using
  `10.0.0.120/24`.
- The legacy VM `501` Caddy route script must refuse to mutate VM `501` unless
  the operator intentionally exports a legacy override.

### Phase 1: Postgres Safe Update

Goal: bring LXC `110` current inside PostgreSQL 17, then decide when to do the
major PostgreSQL 18 migration.

Live sequence:

1. Confirm exact installed Postgres minor and cluster state.
2. Snapshot or back up LXC `110`.
3. Export globals and custom-format database dumps.
4. Apply OS and PostgreSQL 17 package updates.
5. Verify service health, database connectivity, extensions, and migrations.
6. Record the exact package versions in the Warden inventory.

PostgreSQL 18 promotion is a separate gate:

1. Create a side-by-side restore target.
2. Restore dumps and run migrations/tests.
3. Point a staging Clyffy instance at the restored target.
4. Promote only after rollback and restore are proven.

### Phase 2: Provision Master Clyffy App Target

Goal: create LXC `120` on the internal network.

Target:

| Field | Value |
|---|---|
| VMID | `120` |
| Hostname | `clyffy-ops` initially, or `clyffy-master` if renamed before creation |
| Bridge | `vmbr1` |
| IP | `10.0.0.120/24` |
| Gateway | `10.0.0.1` |
| Service | `clyffy-master.service` |
| Runtime port | `8080` |

The current Go-side script is a useful scaffold, but it should be treated as an
operator script until Warden owns this as an audited action.

### Phase 3: Internal DNS

Goal: internal clients resolve `master.clyffy.ai` to the app target.

Use PowerDNS LXC `109` to create or upsert:

```text
master.clyffy.ai. A 10.0.0.120
```

This should run only after LXC `120` exists and health-checks locally.

### Phase 4: Dedicated Edge

Goal: stop adding new routes to VM `501`.

Preferred path:

1. LXC `115` is the dedicated Caddy edge.
2. Current public HTTP/HTTPS routing has moved from VM `501` to LXC `115`.
3. PVE host firewall/NAT forwards `:80` and `:443` to LXC `115`.
4. Render Caddy routes from Warden route intent instead of hand-maintained
   one-off files.
5. Add health checks and rollback for every hostname.

Temporary path:

- Only if the operator consciously accepts the risk, route through VM `501`
  with `ALLOW_LEGACY_VM501_EDGE=1`.
- Document that as temporary and migrate it to LXC `115`.

### Phase 5: Public DNS

Goal: `master.clyffy.ai` resolves publicly to the homebase.

Cloudflare public record:

```text
master.clyffy.ai. A 104.176.44.101
```

Preferred helper script:

```bash
scripts/dns/upsert-cloudflare-a-record.sh \
  --zone-name clyffy.ai \
  --name master.clyffy.ai \
  --target 104.176.44.101
```

The Linux helper reads the canonical Infisical secret
`WARDEN_CLOUDFLARE_DNS_ADMIN` when `CLOUDFLARE_API_TOKEN` is not already set.
Run it without `--apply` first. Apply only after the backend and edge route
answer.

Proxy mode should match the TLS and Caddy plan:

- Use proxied mode only after Cloudflare origin TLS and Caddy certificates are
  correct.
- Use DNS-only temporarily only if direct Caddy TLS is ready and intended.

### Phase 6: Future WardenClyffe Clyffe Namespace

Goal: create the separate customer-safe WardenClyffe/Clyffe UI that visualizes
node networks, workspaces, and MCP mesh touchpoints without exposing operator
internals.

Required model:

- Warden node graph: hosts, Proxmox nodes, guests, storage, networks, domains,
  certs, routes, health, and audit.
- Clyffe customer graph: tenant, project namespace, assigned VM/LXC services,
  tickets, KB articles, CRM account, allowed actions, and assistant context.
- MCP/intelligence graph: markdown touchpoints, registry entries, Qdrant
  collections, SurrealDB projections, and sync status.

This future UI must read through Warden/Clyffe APIs. It should not talk
directly to Proxmox, Postgres admin endpoints, Qdrant, SurrealDB, PowerDNS, or
Cloudflare from the browser.

### Phase 7: Dynamic Clyffy UI

Goal: make `master.clyffy.ai` populate from live contracts instead of static
page copy.

The controlling spec and sprint POA&M are:

- `docs/CLYFFY_DYNAMIC_UI_SPEC.md`
- `docs/CLYFFY_DYNAMIC_UI_POAM.md`

Minimum dynamic cards:

- current focus and blockers;
- Warden Devstation and future Clyffe Code workspaces;
- infrastructure health;
- active POA&M/sprint state;
- knowledge/touchpoint freshness;
- assistant context and suggested next actions.

## Live Mutation Rule

For this rollout, do not perform live writes until the exact target is clear:

- no Postgres major upgrade without backup and restore drill.
- no public DNS change until the target route can answer.
- no new public route on VM `501` unless explicitly accepted as temporary.
- no customer-facing Clyffe route until Authentik, Warden tenancy, audit, and
  route ownership are wired.

## References

- `docs/PUBLIC_IP_HOMEBASE_FOUNDATION.md`
- `docs/FOUNDATION_SERVICE_MATRIX.md`
- `docs/FOUNDATION_APP_RESEARCH_2026_05.md`
- `wardenclyffe/docs/infra-state.md`
- `wardenclyffe/docs/decisions/0027-clyffy-ai-operator-estate-and-supabase-deprecation.md`
- PostgreSQL release notes: `https://www.postgresql.org/docs/release/`

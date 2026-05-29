---
# CLYFFY TOUCHPOINT v2 — intelligence-layer routing (ADR 0033)
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.scale-local
  workspace_uuid: null                            # backfill when assigned in federation_workspace
  project_key: wardenclyffescale-rust-local-edition
  persona: clyffy-operator
  kind: shared-component
  owner: WardenClyffeScale/AGENTS.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml

  scopes:
    - clyffy:operate                              # operator-only; Scale is on-prem infra

  observability:
    semconv_version: "1.40.0"
    trace_context_via_meta: true
---

# WardenClyffeScale — Agent Context (canonical, Rust local edition)

This is the root agent context for the **WardenClyffeScale Rust local
deployment edition**. It is the source of truth for this repo's
structure, conventions, and stack rules.

> Renamed from `WardenClyffe-latest/` on 2026-05-29 per
> [ADR 0036](../wardenclyffe/docs/decisions/0036-wardenclyffescale-rust-local-go-web-split.md).
> The `-latest` suffix is gone for good.

---

## What this is

WardenClyffeScale is a **MariaDB / MySQL replication, clustering, and
load balancing** product. Single Rust binary. Sub-millisecond push
replication. Strong consistency via WAL. MySQL-compatible proxy.
WardenClyffeDisk + WardenClyffeNet companion features for shared storage
and private networking.

This repo is the **Rust local-deployment edition** per ADR 0036:

- **Native single-binary on-prem install.** No orchestrator required.
- **Standalone product.** No dependency on the WardenClyffe engine or
  Master Clyffy orchestrator.
- **Fork of `wolfsoftwaresystemsltd/WolfScale`** with WardenClyffe
  Software Systems Ltd modifications.

A future **Go web/SaaS edition** will live in the WardenClyffe engine
repo (`../wardenclyffe/`) as a bounded context, integrated with the
orchestrator's contracts. The two editions are independent code, same
wire protocol. See ADR 0036 for the boundary.

GitHub remote: `EonsofStupid/WardenClyffe-latest.git` (rename to
`WardenClyffeScale.git` deferred).

Public README + download instructions: [`README.md`](README.md).

---

## Layout

```
WardenClyffeScale/
├── AGENTS.md                      this file (canonical agent context)
├── README.md                      public Scale README (marketing + install)
├── LICENSE                        MIT
├── Cargo.toml, Cargo.lock         Rust build manifest
├── .gitignore                     Rust-targeted ignores (/target/, *.rs.bk, etc.)
├── install_service.sh             systemd installer (older interactive wizard)
├── run.sh                         daemon launcher (start/proxy/status/info modes)
├── setup.sh                       cluster-node setup helper
├── setup_lb.sh                    load-balancer setup helper
├── wardenclyffescale.toml.example default config template (operator copies + edits)
├── wardenclyffescale.logrotate    logrotate config
├── wardenclyffescale.png          logo
└── scripts/
    ├── install-service.sh         newer systemd installer (--node-id, --bootstrap, --uninstall)
    ├── run.sh                     daemon launcher (--config, --log-level, --bootstrap)
    ├── publish.sh                 publish-to-GitHub helper
    └── nginx-example.conf         nginx LB config (alternative to the built-in LB)
```

Future Rust source tree (`src/`, `tests/`, `examples/`) returns when the
build is rehydrated from upstream WolfScale + WardenClyffe modifications.
It was removed from outer git history in baseline commit `900bad3` per
the no-Rust-in-engine rule that ADR 0036 has since narrowed.

---

## Build + run

Standard Rust:

```bash
cargo build --release
cargo test
./run.sh --bootstrap   # first node (cluster leader bootstrap)
./run.sh               # subsequent follower nodes
```

For systemd deployment:

```bash
sudo ./scripts/install-service.sh --node-id node-1 --bootstrap   # leader
sudo ./scripts/install-service.sh --node-id node-2               # follower
sudo ./scripts/install-service.sh --node-id node-3               # follower
```

For the load balancer (on any server that needs DB access):

```bash
sudo ./setup_lb.sh
```

The LB auto-discovers cluster nodes via UDP broadcast — no manual peer
config needed.

---

## Stack constraints (this repo only)

- **This repo IS Rust.** Per ADR 0036, the "no Rust in WardenClyffe"
  stack-constraints rule applies to the WardenClyffe engine
  (`../wardenclyffe/`) and Master Clyffy orchestrator
  (`../Master-Clyffy/`), NOT this standalone shared-component product.
  Rust stays here.
- **Do not import from `../wardenclyffe/`.** The Go edition (when
  authored) lives there as a bounded context; the two editions are
  independent. Avoid coupling.
- **Wire protocol is the alignment point.** When the protocol spec is
  authored at `../wardenclyffe/docs/specs/<NN>-wardenclyffescale-protocol.md`
  (parked follow-up), that spec becomes the source of truth for
  replication frame format, leader election state machine, and MySQL
  proxy passthrough behavior.

---

## Safety rules

- Never commit secrets: MariaDB root passwords, replication tokens,
  TLS private keys, or live database content.
- Use `wardenclyffescale.toml.example` as the config shape; operator
  copies to `wardenclyffescale.toml` locally and edits.
  `wardenclyffescale.toml` (without `.example`) should be gitignored.
- WAL data lives in `/var/lib/wardenclyffescale/`. Never include that
  path's contents in the repo.
- Cargo dependencies are public crates only. No vendored secret-bearing
  crates.

---

## Relationship to siblings

| Sibling | What it is | Relationship |
|---|---|---|
| `../wardenclyffe/` | WardenClyffe engine (Go) — infrastructure management, MCP mesh, agent runtime | Sibling repo; future Go Scale edition will live there as a bounded context per ADR 0036 |
| `../Master-Clyffy/` | Master Clyffy orchestrator (Go) — federation, UI, tenancy | Sibling repo; eventually offers Scale as a managed-database catalog item via the WardenClyffe catalog |

This repo has no runtime dependency on either sibling.

---

## Read next

For Scale-internal work, the existing public Scale [`README.md`](README.md)
plus the (rehydrated) `src/` tree are the relevant material.

For the **broader WardenClyffe ecosystem context** (when you need to
understand how Scale fits with the rest of the product family), see:

1. [`../wardenclyffe/docs/decisions/0036-wardenclyffescale-rust-local-go-web-split.md`](../wardenclyffe/docs/decisions/0036-wardenclyffescale-rust-local-go-web-split.md) — this product's defining ADR
2. [`../wardenclyffe/docs/WARDEN_CLYFFE_ARCHITECTURE.md`](../wardenclyffe/docs/WARDEN_CLYFFE_ARCHITECTURE.md) — high-level architecture
3. [`../Master-Clyffy/AGENTS.md`](../Master-Clyffy/AGENTS.md) § "Comprehensive WardenClyffe ecosystem orientation" — 27-doc reading path absorbed from the prior root contract

---

## Commit conventions

Conventional Commits. Suggested areas:

- `core` — replication engine, WAL, leader election state machine
- `proxy` — MySQL-compatible proxy
- `cluster` — node discovery, peer state, cluster membership
- `api` — HTTP control-plane API
- `cli` — `wardenclyffectl` command-line interface
- `install` — systemd installer scripts
- `docs` — README, AGENTS.md, ADRs (if Scale grows its own ADR series)
- `ci` — GitHub Actions, build matrix, release workflow

---

## When this file is wrong

Edit it. This is the canonical WardenClyffeScale Rust-edition context.
Update the v2 frontmatter (especially `workspace_id`, `project_key`,
`persona`) before any structural change. Run the touchpoint validator
from the sibling engine repo:

```bash
python3 ../wardenclyffe/scripts/foundation/validate-touchpoints.py --root . --strict
```

That validator works against this directory tree because it's
dependency-free and frontmatter-only — no SurrealDB connection
required to lint shape.

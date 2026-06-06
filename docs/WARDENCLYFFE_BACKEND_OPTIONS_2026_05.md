---
wardenclyffe_touchpoint:
  version: 1
  kind: backend-options
  namespace: wardenclyffe.backend
  owner: docs/WARDENCLYFFE_BACKEND_OPTIONS_2026_05.md
  module_scope:
    - module-01-warden
    - module-02-clyffe
---

# Backend Options Review - May 2026

This is not a final decision. It is the current option map for WardenClyffe.

## Current Recommendation

Use Postgres as the dedicated Warden/Clyffe product-truth backend. That is the
boring, modern control-plane choice for tenants, RBAC, inventory, tickets, CRM,
KB, workflow state, route intent, billing references, and audit.

The bleeding-edge move is not replacing Postgres with a novelty database. It is
using a clear polyglot backend where each datastore has one job:

| Role | Default | Why |
|---|---|---|
| Product truth | Postgres 18 target, Postgres 17 live until migration drill | relational integrity, RLS, JSONB, UUIDv7, migrations, audit |
| Cache/session/locks | Dragonfly | Redis/Memcached-compatible, high-throughput ephemeral state |
| Events/jobs | NATS JetStream | durable streams, pull consumers, replay, lifecycle events |
| Vector retrieval | Qdrant | vector/hybrid retrieval and tenant-filtered payloads |
| AI graph projection | SurrealDB | graph, realtime, vector/full-text projection for agent context |
| Observability/usage analytics | ClickHouse later | logs, traces, session replay, high-volume aggregates |
| Files/artifacts | S3/R2/MinIO-compatible object storage | attachments, reports, backups metadata, build artifacts |
| Distributed SQL option | CockroachDB/Yugabyte/Citus later | only if active-active or tenant scale demands it |

MariaDB stays valuable for WardenClyffeScale and managed MySQL/MariaDB service
offerings. It should not be the default Warden/Clyffe product-control database
unless a code spike proves tenant isolation, audit, and migrations are at least
as clean as Postgres.

## Current Local Evidence

| Evidence | Meaning |
|---|---|
| `src/executor/mariadb.rs` | root Rust code has real MariaDB/MySQL executor work |
| `Cargo.toml` | root Rust package is WardenClyffeScale, with `sqlx` MySQL enabled |
| `web/wardenclyffe-mysql.php` | current docs imagine a multi-database manager |
| `wardenclyffe/docs/decisions/0007-postgres-userdata-surrealdb-ai.md` | nested Go repo accepted Postgres for user/product truth and SurrealDB for AI state |
| `wardenclyffe/templates/compose/qdrant.yml` | Qdrant is already part of the AI retrieval direction |
| `wardenclyffe/templates/compose/surrealdb.yml` | SurrealDB is already part of AI graph/reasoning experiments |
| `wardenclyffe/templates/compose/authentik.yml` | Authentik template uses Postgres and optional Dragonfly |

The local repo does not support a one-sentence answer. MariaDB is real here.
Postgres is real in the Go Warden architecture. SurrealDB and Qdrant are real
for intelligence work. These should be separated by role.

## Option Matrix

| Backend | Best fit | Strengths | Risks |
|---|---|---|---|
| MariaDB 11.8 LTS | WardenClyffeScale, managed MySQL/MariaDB databases, database-manager feature | mature MySQL ecosystem, current LTS to June 2028, native vector support, existing Rust code | weaker fit for tenant-heavy app core than Postgres RLS ecosystem, Galera wants careful quorum design |
| MariaDB 12.x rolling | future MariaDB feature tracking | fast-moving features | rolling release is not the boring default for control-plane truth |
| PostgreSQL 18.x | Warden/Clyffe product truth: tenants, RBAC, inventory, tickets, CRM, KB, audit | strong relational integrity, RLS, JSONB, extensions, UUIDv7, huge tooling ecosystem | multi-primary HA is not simple; scale-out needs extensions or distributed variants |
| Citus on Postgres | multi-tenant SaaS scale-out later | stays Postgres extension, strong for tenant-sharded SaaS and analytics | extra operations; not needed for two-server pilot |
| CockroachDB | distributed SQL if active-active scale becomes required | Postgres wire protocol, distributed SQL, strong HA story | not full Postgres behavior; app design must account for distributed SQL differences |
| YugabyteDB | open-source distributed SQL with Postgres-compatible API | distributed SQL, Apache 2.0, strong scaling story | more moving parts than needed now; compatibility still needs validation |
| SurrealDB 3.0 | AI graph, agent memory, knowledge graph, real-time projection | native multi-model, vector/full-text/graph/realtime, Rust binary | young compared to Postgres/MariaDB for product truth; licensing and operational maturity need review |
| Qdrant | vector retrieval | hybrid dense/sparse/multivector retrieval, filtering, good RAG fit | not product truth; needs metadata and auth boundaries around it |
| Dragonfly | cache, sessions, queues, ephemeral coordination | Redis/Memcached-compatible, high single-node throughput | not durable product truth |
| SQLite/libSQL | local dev, edge cache, single-node embedded state | boring, embedded, reliable | not primary multi-user Warden/Clyffe control-plane DB |

## Current Versions And Sources

- PostgreSQL release page lists PostgreSQL 18.4 as released on May 14, 2026:
  https://www.postgresql.org/docs/release/
- PostgreSQL 18 release notes include async I/O, JSONB improvements, and
  `uuidv7()`:
  https://www.postgresql.org/docs/current/release-18.html
- MariaDB 11.8 is an LTS maintained until June 2028:
  https://mariadb.com/docs/release-notes/community-server/11.8/what-is-mariadb-118
- MariaDB 12.2 is a rolling release:
  https://mariadb.com/docs/release-notes/community-server/12.2/12.2.2
- SurrealDB describes itself as multi-model with vector, full-text, hybrid,
  graph, realtime, and event-driven capabilities:
  https://surrealdb.com/docs/what-is-surrealdb
- Qdrant documents vector search, filtering, hybrid queries, and multivector
  inference:
  https://qdrant.tech/documentation/
- Dragonfly documents Redis and Memcached compatibility:
  https://www.dragonflydb.io/docs
- CockroachDB documents Postgres wire protocol compatibility and differences:
  https://www.cockroachlabs.com/docs/stable/postgresql-compatibility
- YugabyteDB stable docs describe a PostgreSQL-compatible distributed database:
  https://docs.yugabyte.com/stable/
- Citus docs describe an open-source Postgres extension for horizontal scale:
  https://learn.microsoft.com/en-us/postgresql/citus/

## Recommended Evaluation Tracks

### Track A: Postgres product core plus MariaDB Scale

Evaluate PostgreSQL for Warden/Clyffe product truth:

```text
tenants
users
memberships
RBAC
Proxmox inventory cache
resource ownership
tickets
CRM
knowledge base
audit
workflow state
action requests
```

Keep MariaDB for WardenClyffeScale and managed database features.

This is the most boring SaaS/control-plane path.

### Track B: MariaDB-first product core

Use MariaDB for Warden/Clyffe core to preserve existing Rust direction.

This is reasonable if the product center of gravity is database replication and
managed MySQL/MariaDB services. It needs a deliberate tenant/RBAC pattern to
match what Postgres RLS gives more naturally.

### Track C: SurrealDB-heavy AI/control hybrid

Use SurrealDB for more than AI projection.

This is interesting but should require a proof first:

- strict schema mode.
- tenant authorization model.
- backup/restore proof.
- migration story.
- query performance proof for tickets/CRM/audit.
- driver maturity proof in the chosen Rust stack.

### Track D: Distributed SQL later

Evaluate CockroachDB, YugabyteDB, or Citus only when scale demands it.

For the first two-server pilot, distributed SQL is probably more complexity
than value unless there is a hard active-active requirement. Two nodes also
make quorum-based designs awkward without a witness, third node, or managed
control plane.

## Practical Shortlist

For the first internal WardenClyffe build:

1. Pick one relational product-truth candidate for a spike: Postgres or
   MariaDB.
2. Keep Qdrant as vector retrieval.
3. Keep SurrealDB as AI graph/reasoning projection unless a proof promotes it.
4. Keep Dragonfly optional for cache/session/queue acceleration.
5. Keep object storage separate for files, reports, screenshots, and backup
   metadata exports.

## Spike Questions

Before deciding, answer these in code:

1. Can the database enforce tenant isolation without trusting every handler?
2. Can it model Proxmox resources, ownership, tickets, KB, audit, and workflow
   state without awkward workarounds?
3. Can Rust migrations and tests be boring?
4. Can Go Warden data be imported without losing audit meaning?
5. Can Clyffe safely query only customer-visible data?
6. Can backups and restores be proven on the two-server setup?
7. Can the AI bridge project to SurrealDB and Qdrant without dual writes?

## Bias To Carry Forward

MariaDB is a solid database. It is not automatically wrong.

Postgres is the safest default for tenant-heavy Warden/Clyffe product truth.
MariaDB is the strongest fit for WardenClyffeScale and managed MySQL-family
features. SurrealDB and Qdrant are best treated as intelligence layers unless
specific proofs promote them.

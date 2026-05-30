# Wardenclyffe Template Schema

**Version:** 0.1.0
**Last updated:** 2026-05-05

Every template in `templates/compose/<service>.yml` is a valid `docker-compose.yml`
with a single Wardenclyffe extension key: `x-warden`. This makes templates:

- Runnable directly with plain `docker compose up` (no Warden required)
- Discoverable by Warden — it parses `x-warden` to know how to manage the service
- Forward-compatible — adding new fields to the schema doesn't break existing templates
- Source-of-truth for both deploy automation AND human reference

The `x-` prefix is Docker's official extension mechanism. Compose ignores any
top-level field starting with `x-`, so this metadata is invisible to the runtime
but visible to tooling that knows to look for it.

---

## Top-level layout

```yaml
x-warden:
  # IDENTITY (required)
  name: <kebab-case>           # globally unique slug, matches filename
  version: <semver>            # template version (NOT the upstream service version)
  upstream_version: <string>   # what version of the upstream service this targets
  category: <enum>             # for Warden UI grouping (see "categories" below)
  estate: <enum>               # optional; defaults by category, media must be mediastack
  default_visibility: <enum>   # internal | public; media must be internal
  description: <string>        # one-line human description
  homepage: <url>              # upstream project URL
  license: <SPDX-id>           # upstream service license
  source: <url>                # where this template was derived from (Coolify, official docs, original)
  
  # NETWORKING (required)
  ports: [...]                 # what ports the service exposes
  
  # HEALTH (required, primary health check at minimum)
  health: { primary: {...}, detailed: [...] }
  
  # SECRETS (required if any are needed)
  secrets: [...]               # what Warden must generate before first run
  
  # BOOTSTRAP (optional but most services have one)
  bootstrap: {...}             # what user has to do after first start
  
  # CONFIGURATION (optional)
  configurable: [...]          # env vars the user can/should override
  
  # DEPENDENCIES (optional)
  depends_on: { required: [...], optional: [...] }
  
  # RESOURCES (optional, hints to Warden for sizing)
  resources: { ... }
  
  # BACKUP (optional, what Warden should back up)
  backup: { ... }

  # DEPLOYMENT LIFECYCLE (recommended for resilient operations)
  lifecycle: { ... }           # create/upgrade/destroy hooks and checks
  backups: { ... }             # schedule + retention + restore expectations
  recovery: { ... }            # restore workflow and verification path
  placement: { ... }           # provider/cluster eligibility + anti-affinity
  networking: { ... }          # VLAN/segment + ingress and exposure posture
  compliance: { ... }          # secret policy, encryption, audit controls

  # STORAGE CLASSES (required for media templates)
  storage_classes: { ... }
  
  # UPGRADE (optional, how to upgrade safely)
  upgrade: { ... }

services:
  # standard docker-compose services
  ...
```

---

## Field reference

### Identity

```yaml
x-warden:
  name: authentik
  version: 0.1.0
  upstream_version: "2025.10.0"
  category: identity
  estate: aiaas
  default_visibility: public
  description: "Open-source identity provider with SSO, MFA, LDAP/RADIUS"
  homepage: https://goauthentik.io/
  license: MIT
  source:
    type: derived            # one of: original | derived | imported
    from: coolify-template   # if derived/imported, where from
    url: https://github.com/coollabsio/coolify/blob/v4.x/templates/compose/authentik.yaml
    modifications: |
      - Removed Coolify-specific SERVICE_FQDN_* magic vars
      - Replaced with WARDEN_FQDN_* convention
      - Added explicit health check path
      - Added Warden secret generation hints
```

**Categories** (closed enum, extend in this doc as needed):
- `identity` — IdPs, SSO, RADIUS
- `secrets` — vault, secret managers (Infisical, OpenBao)
- `database` — Postgres, MySQL, MongoDB, Redis, etc.
- `observability` — metrics, logs, tracing
- `monitoring` — uptime, alerting
- `messaging` — message queues, pubsub
- `storage` — S3-compatible, file servers
- `media` — Plex, Jellyfin, etc.
- `productivity` — Nextcloud, BookStack, etc.
- `development` — Gitea, code-server, registries
- `analytics` — Plausible, Umami
- `automation` — n8n, Activepieces
- `ai` — Ollama, OpenWebUI, vector DBs
- `networking` — VPNs, mesh, proxies
- `other` — fallback

**Estates**:
- `aiaas` — default for deployable AIaaS infrastructure and product services
- `homelab` — personal/internal services that are not part of AIaaS infrastructure
- `mediastack` — required for media acquisition/library/streaming stacks
- `wardenclyffe-control` — control-plane services only
- `production` — externally exposed production/customer-serving estate

**Visibility**:
- `internal` — private by default; no public route unless a deploy-time override permits it
- `public` — Warden may create DNS/edge routing for declared public ports

Media templates (`category: media`) have a stricter boundary:

- `estate: mediastack` is required.
- `default_visibility: internal` is required.
- `storage_classes:` is required.
- Every `ports[].public` value must be `false` by default.

### Networking — ports

```yaml
ports:
  - internal: 9000          # port inside the container
    external: 9000          # port on the LXC host (often same)
    protocol: http          # http | https | tcp | udp | grpc | ws
    public: true            # should Warden expose via edge routing?
    label: "Authentik UI"   # human label, used in Warden UI
    fqdn_var: WARDEN_FQDN   # env var name that holds the public FQDN (if public:true)
  - internal: 9300
    external: 9300
    protocol: tcp
    public: false           # internal-only, don't add edge routing
    label: "Outpost gRPC"
```

**Convention:** when `public: true`, the template MUST reference `${WARDEN_FQDN}` in
the relevant env vars (e.g. `AUTHENTIK_HOST`, `BASE_URL`). Warden's deploy module
fills this in based on user-chosen subdomain.

### Health checks

Every template has a `health.primary` check. Warden uses this to know when the
service is "actually ready" (not just "container started"). Optional `detailed`
list adds richer diagnostics shown in the Warden UI.

```yaml
health:
  primary:
    type: http              # http | tcp | exec | none
    path: /-/health/live/   # for http
    port: 9000              # which exposed port to hit
    method: GET             # for http (default GET)
    expect_status: [200, 301, 302]
    expect_body_contains: ""  # optional substring check
    interval_sec: 30
    timeout_sec: 5
    grace_period_sec: 90    # how long after first start before failing counts
    retries: 3              # consecutive failures before marking unhealthy
  
  detailed:
    - type: http
      path: /-/health/ready/
      port: 9000
      label: "ready"
      shows_in_ui: true
    - type: exec             # docker exec into a container
      service: postgres      # which service in the compose
      command: ["pg_isready", "-U", "authentik"]
      label: "db-ready"
      shows_in_ui: true
```

### Secrets

Warden's install pipeline reads this list and generates `.env` values before the
first `docker compose up`. The compose file references them as `${VAR_NAME}`.

```yaml
secrets:
  - name: PG_PASS
    type: random_alphanumeric    # see "secret types" below
    length: 32
    description: "Postgres password for the authentik user"
    rotatable: true              # can be rotated without data loss
    rotation_strategy: "alter user, restart service"
  - name: AUTHENTIK_SECRET_KEY
    type: random_base64
    length: 60
    description: "Used to sign cookies and tokens"
    rotatable: false             # rotation invalidates existing sessions
    rotation_strategy: "generate new, restart, all users re-login"
```

**Secret types** (closed enum):
- `random_alphanumeric` — A-Z a-z 0-9, no special chars (safe for any context)
- `random_alphanumeric_symbols` — adds !@#$%^&* (avoid in URLs/headers)
- `random_base64` — base64-encoded random bytes, common for signing keys
- `random_hex` — hex-encoded random bytes
- `random_uuid` — RFC 4122 UUID
- `user_provided` — user enters via Warden UI (not auto-generated)
- `external_reference` — pulled from secret manager (Infisical, etc.)

### Bootstrap

What the user does after first start. Warden surfaces this in the deploy UI.

```yaml
bootstrap:
  initial_setup_path: /if/flow/initial-setup/
  description: "Visit this URL within 5 minutes of first start to create the admin account"
  expires: "5 minutes after first start"
  notes: |
    - First user to hit the URL becomes admin
    - After admin creation, this URL stops working
    - If you miss the window: docker compose exec server ak make_admin
  warden_can_open: true     # Warden can open this URL via "Bootstrap" button
```

### Configurable env vars

Things the user might want to override. Warden's UI exposes these.

```yaml
configurable:
  - name: AUTHENTIK_LOG_LEVEL
    default: info
    type: enum
    allowed: [debug, info, warning, error, trace]
    description: "Logging verbosity"
  - name: AUTHENTIK_EMAIL_HOST
    default: ""
    type: string
    description: "SMTP host for password reset emails (leave empty to disable)"
    sensitive: false
  - name: AUTHENTIK_EMAIL_PASSWORD
    default: ""
    type: string
    description: "SMTP password"
    sensitive: true                # Warden masks this in UI
  - name: WORKER_REPLICAS
    default: 1
    type: integer
    range: [1, 10]
    description: "Number of authentik-worker containers"
```

### Dependencies

Other Warden services this expects (or works better with).

```yaml
depends_on:
  required: []                # nothing required for this template
  optional:
    - service: smtp
      reason: "Password reset, account verification emails"
      auto_wire: false        # Warden won't auto-configure even if smtp is deployed
    - service: postgres
      reason: "External postgres if you don't want the bundled one"
      auto_wire: false
      replaces: postgres      # if user picks external, the bundled service is removed
```

### Resources (hints)

Help Warden suggest LXC sizing. Not enforced, just hints.

```yaml
resources:
  minimum:
    cpu_cores: 2
    ram_mb: 1024
    disk_gb: 8
  recommended:
    cpu_cores: 4
    ram_mb: 4096
    disk_gb: 32
  scale_per:                  # if applicable, what scales linearly
    metric: "users"
    ram_mb_per_unit: 0.5
    cpu_cores_per_1000: 1
```

### Backup

What needs to be backed up if the user enables Warden's backup module.

```yaml
backup:
  strategy: pg_dump_plus_volumes
  volumes:
    - postgres-data         # volume name from compose
    - media
    - certs
    - custom-templates
  databases:
    - service: postgres
      type: postgres
      database: authentik
      user_env: POSTGRES_USER
      password_env: PG_PASS
  exclude_volumes: []
  pre_backup_hook: ""        # optional command to run before backup
  post_restore_hook: |       # optional, after restore
    docker compose exec server ak migrate
```

### Lifecycle / recovery / placement / compliance

These blocks are optional in early templates but are strongly recommended for
production templates. They allow Warden to reason about safe promotion,
migration, and recovery before rollout.

```yaml
lifecycle:
  create:
    preflight: ["validate-secrets", "validate-network"]
    post_deploy_checks: ["health.primary", "bootstrap.url"]
  upgrade:
    strategy: rolling
    max_unavailable: 1
  destroy:
    requires_confirmation: true
    preserve_backups: true

backups:
  schedule: "0 */6 * * *"
  retention: "14d"
  restore_test: "monthly"
  target: "volume+database"

recovery:
  profile: standard
  rto: "2h"
  rpo: "15m"
  restore_runbook: "docs/runbooks/<service>-restore.md"
  failback: manual

placement:
  substrates: ["proxmox-x", "solus-x"]
  clusters: ["server1-general", "server2-compute"]
  anti_affinity:
    - key: tenant
      required: true
  resource_class: standard

networking:
  segment: "tenant"
  vlan: "120"
  ingress: caddy
  exposure: private

compliance:
  secret_policy: "generated-or-infisical"
  encryption:
    at_rest: true
    in_transit: true
  audit:
    deployment_events: true
    admin_actions: true
```

### Storage classes

Storage classes describe ownership and backup posture for data that does not
fit a single app-volume backup rule. They are required for `category: media`.

```yaml
storage_classes:
  config:
    backup: required
  library:
    backup: optional
  staging:
    backup: never
  cache:
    backup: never
```

### Upgrade

How Warden should upgrade this service safely.

```yaml
upgrade:
  strategy: rolling           # rolling | blue_green | stop_start
  pre_upgrade_hook: |
    docker compose exec server ak shell -c "from authentik.lib.healthcheck import run_pre_upgrade; run_pre_upgrade()"
  pull_images: true
  restart_order: [postgres, dragonfly, server, worker]
  post_upgrade_hook: ""
  rollback_strategy: snapshot  # snapshot | volume_backup | none
  breaking_changes_doc: https://goauthentik.io/docs/releases/
```

---

## Variable conventions

Wardenclyffe templates use the `WARDEN_*` prefix for all template-injected vars.
This avoids collision with upstream service env vars and Coolify's `SERVICE_*`.

| Variable | Set by | Purpose |
|---|---|---|
| `WARDEN_FQDN` | Warden deploy | Public FQDN if `public: true` |
| `WARDEN_FQDN_<PORT>` | Warden deploy | Per-port FQDN if multiple public ports |
| `WARDEN_INTERNAL_HOST` | Warden deploy | Internal LAN IP/hostname (e.g. `10.0.0.103`) |
| `WARDEN_DATA_DIR` | Warden deploy | Bind mount root inside the LXC (e.g. `/opt/<service>`) |
| `<SECRET_NAME>` | Warden secret gen | Each entry from `secrets:` list |

User-overridable vars from `configurable:` keep their natural upstream names
(`AUTHENTIK_LOG_LEVEL`, not `WARDEN_AUTHENTIK_LOG_LEVEL`) so upstream docs
stay relevant.

---

## Validation

A template is valid if:

1. It parses as YAML
2. `x-warden.name` matches the filename (without `.yml`)
3. `x-warden.version` is valid semver
4. Every var referenced as `${X}` in the compose file is either:
   - In the `secrets:` list (Warden generates it)
   - In the `configurable:` list (user can set it, has a default)
   - In the `WARDEN_*` reserved namespace (Warden injects it)
   - Listed as required env in the deploy script
5. `health.primary` resolves to a port that exists in `ports:`
6. `category` is in the allowed enum
7. Media templates declare the mediastack estate boundary and are private by default

A future Warden CLI command `warden template validate <file>` will check these.

---

## Naming and authoring rules

- **Filename:** `<service-slug>.yml` — kebab-case, matches `x-warden.name`
- **Original templates** carry `source.type: original` and `source.from: wardenclyffe`
- **Derived templates** must include the upstream URL and a list of modifications
- **Imported templates** (taken as-is from another catalog with minimal change) carry `source.type: imported`
- Template versions follow semver — bump major when breaking changes (env var renames, port changes, secret schema changes)
- Document the upstream service version in `upstream_version` and bump it when the image tag changes; a `version` bump may or may not require it

---

## Index

The full catalog index lives at `templates/INDEX.md` (auto-generated from the templates).
Don't edit `INDEX.md` directly — edit templates and re-run the indexer when it exists.

---

## Why this schema

- **x-warden** is the trending convention as of May 2026 — Docker, Compose Bridge,
  Portainer, OneUptime, and Docker Desktop Extensions all converged on `x-` fields
  for metadata. We're not inventing a pattern.
- **Rich schema now** because the SVG engine and auto-deploy module both consume
  this same data. Shipping with stub fields means re-editing 280 templates later.
- **Reserved fields stay empty in v0.1** rather than absent, so future Warden parsing
  code doesn't have to handle missing keys defensively.

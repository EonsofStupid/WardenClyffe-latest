-- WardenClyffe canonical schema — migration 0001 (init)
-- System of record for the Warden server. Schema names are CANONICAL
-- (docs/WARDENCLYFFE_NAMING_CONVENTIONS.md): do not rename or add synonyms.
--
-- Apply: psql -h 127.0.0.1 -U warden -d wardenclyffe -f data/schema/sql/0001_init.sql
-- Idempotent: safe to re-run.

BEGIN;

-- ---------------------------------------------------------------------------
-- Extensions & schemas
-- ---------------------------------------------------------------------------
CREATE EXTENSION IF NOT EXISTS pgcrypto;     -- gen_random_uuid()

CREATE SCHEMA IF NOT EXISTS warden_core;     -- subjects, RBAC, action requests, tenants link
CREATE SCHEMA IF NOT EXISTS warden_infra;    -- hosts, nodes, resources (VMs/LXCs), storage
CREATE SCHEMA IF NOT EXISTS warden_audit;    -- append-only event history
CREATE SCHEMA IF NOT EXISTS clyffe_core;     -- customer accounts, orders, services
CREATE SCHEMA IF NOT EXISTS clyffe_support;  -- tickets (later)
CREATE SCHEMA IF NOT EXISTS clyffe_kb;       -- knowledge base (later)
CREATE SCHEMA IF NOT EXISTS clyffe_crm;      -- crm notes (later)
CREATE SCHEMA IF NOT EXISTS ai_bridge;       -- AI identity grants / projections bridge

-- migration ledger
CREATE TABLE IF NOT EXISTS warden_core.schema_migrations (
    version     text PRIMARY KEY,
    applied_at  timestamptz NOT NULL DEFAULT now()
);

-- ---------------------------------------------------------------------------
-- Shared trigger: maintain updated_at
-- ---------------------------------------------------------------------------
CREATE OR REPLACE FUNCTION warden_core.set_updated_at()
RETURNS trigger LANGUAGE plpgsql AS $$
BEGIN
    NEW.updated_at = now();
    RETURN NEW;
END $$;

-- ===========================================================================
-- warden_core
-- ===========================================================================

-- An authenticated principal. kind separates human/operator/customer from AI.
DO $$ BEGIN
    CREATE TYPE warden_core.subject_kind AS ENUM ('operator','customer','service','ai');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS warden_core.subjects (
    id            uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    kind          warden_core.subject_kind NOT NULL,
    external_id   text,                       -- Authentik subject / OIDC sub
    display_name  text NOT NULL,
    email         text,
    is_active     boolean NOT NULL DEFAULT true,
    created_at    timestamptz NOT NULL DEFAULT now(),
    updated_at    timestamptz NOT NULL DEFAULT now(),
    UNIQUE (kind, external_id)
);

-- Tenancy boundary. Customers belong to a tenant; operators are tenant NULL.
CREATE TABLE IF NOT EXISTS warden_core.tenants (
    id          uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    slug        text NOT NULL UNIQUE,
    name        text NOT NULL,
    created_at  timestamptz NOT NULL DEFAULT now(),
    updated_at  timestamptz NOT NULL DEFAULT now()
);

-- Plan -> approve -> execute control-plane workflow.
DO $$ BEGIN
    CREATE TYPE warden_core.action_status AS ENUM
        ('planned','approved','executing','succeeded','failed','rejected');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS warden_core.action_requests (
    id            uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id     uuid REFERENCES warden_core.tenants(id),
    requested_by  uuid REFERENCES warden_core.subjects(id),
    kind          text NOT NULL,              -- e.g. 'provision_workspace'
    status        warden_core.action_status NOT NULL DEFAULT 'planned',
    payload       jsonb NOT NULL DEFAULT '{}'::jsonb,
    result        jsonb,
    error         text,
    created_at    timestamptz NOT NULL DEFAULT now(),
    updated_at    timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS action_requests_status_idx ON warden_core.action_requests(status);
CREATE INDEX IF NOT EXISTS action_requests_tenant_idx ON warden_core.action_requests(tenant_id);

-- ===========================================================================
-- warden_infra  (the fleet: hosts, nodes, resources/workspaces)
-- ===========================================================================
DO $$ BEGIN
    CREATE TYPE warden_infra.hypervisor AS ENUM ('proxmox','libvirt','baremetal','other');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS warden_infra.hosts (
    id           uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    stable_id    text NOT NULL UNIQUE,        -- host.us-wi.foundation-01
    hypervisor   warden_infra.hypervisor NOT NULL DEFAULT 'proxmox',
    region       text,
    api_endpoint text,                        -- https://10.0.0.1:8006
    node         text,                        -- proxmox node name (server1)
    note         text,
    created_at   timestamptz NOT NULL DEFAULT now(),
    updated_at   timestamptz NOT NULL DEFAULT now()
);

DO $$ BEGIN
    CREATE TYPE warden_infra.resource_kind AS ENUM ('vm','lxc','workspace','capsule','devstation');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    CREATE TYPE warden_infra.resource_state AS ENUM
        ('requested','provisioning','running','stopped','error','destroyed');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

-- A provisioned guest / workspace. stable_id e.g. resource.proxmox.server1.116
CREATE TABLE IF NOT EXISTS warden_infra.resources (
    id           uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    stable_id    text NOT NULL UNIQUE,
    tenant_id    uuid REFERENCES warden_core.tenants(id),
    owner_id     uuid REFERENCES warden_core.subjects(id),
    host_id      uuid REFERENCES warden_infra.hosts(id),
    kind         warden_infra.resource_kind NOT NULL DEFAULT 'devstation',
    state        warden_infra.resource_state NOT NULL DEFAULT 'requested',
    vmid         integer,
    hostname     text,
    tier         text,                        -- starter|builder|premium-pilot|power|gpu
    cores        integer,
    memory_mb    integer,
    disk_gb      integer,
    ip_cidr      text,
    ssh_alias    text,
    spec         jsonb NOT NULL DEFAULT '{}'::jsonb,   -- full descriptor (matches host yaml)
    created_at   timestamptz NOT NULL DEFAULT now(),
    updated_at   timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS resources_tenant_idx ON warden_infra.resources(tenant_id);
CREATE INDEX IF NOT EXISTS resources_state_idx  ON warden_infra.resources(state);

-- ===========================================================================
-- warden_audit  (append-only)
-- ===========================================================================
CREATE TABLE IF NOT EXISTS warden_audit.events (
    id           bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    occurred_at  timestamptz NOT NULL DEFAULT now(),
    actor_id     uuid REFERENCES warden_core.subjects(id),
    tenant_id    uuid REFERENCES warden_core.tenants(id),
    action       text NOT NULL,               -- 'resource.provision_requested'
    target_kind  text,
    target_id    text,
    data         jsonb NOT NULL DEFAULT '{}'::jsonb
);
CREATE INDEX IF NOT EXISTS audit_events_action_idx ON warden_audit.events(action);
CREATE INDEX IF NOT EXISTS audit_events_target_idx ON warden_audit.events(target_kind, target_id);

-- ===========================================================================
-- clyffe_core  (customer-facing)
-- ===========================================================================
DO $$ BEGIN
    CREATE TYPE clyffe_core.order_status AS ENUM
        ('pending','approved','provisioning','active','failed','cancelled');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS clyffe_core.orders (
    id            uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id     uuid NOT NULL REFERENCES warden_core.tenants(id),
    placed_by     uuid REFERENCES warden_core.subjects(id),
    product       text NOT NULL DEFAULT 'clyffe-code',   -- capsule|devstation|clyffe-code
    tier          text NOT NULL DEFAULT 'builder',
    repo          text,
    region        text,
    status        clyffe_core.order_status NOT NULL DEFAULT 'pending',
    action_request_id uuid REFERENCES warden_core.action_requests(id),
    resource_id   uuid REFERENCES warden_infra.resources(id),
    created_at    timestamptz NOT NULL DEFAULT now(),
    updated_at    timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS orders_tenant_idx ON clyffe_core.orders(tenant_id);
CREATE INDEX IF NOT EXISTS orders_status_idx ON clyffe_core.orders(status);

-- ===========================================================================
-- ai_bridge  (which auth method an AI identity used; restriction policy)
-- ===========================================================================
DO $$ BEGIN
    CREATE TYPE ai_bridge.auth_method AS ENUM ('radius','client_cert','password_failsafe');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS ai_bridge.identity_grants (
    id            uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    subject_id    uuid NOT NULL REFERENCES warden_core.subjects(id),
    method        ai_bridge.auth_method NOT NULL,
    allowed_cidrs text[] NOT NULL DEFAULT '{}',
    policy        jsonb NOT NULL DEFAULT '{}'::jsonb,
    is_active     boolean NOT NULL DEFAULT true,
    created_at    timestamptz NOT NULL DEFAULT now(),
    updated_at    timestamptz NOT NULL DEFAULT now()
);

-- ---------------------------------------------------------------------------
-- updated_at triggers
-- ---------------------------------------------------------------------------
DO $$
DECLARE t text;
BEGIN
  FOR t IN SELECT unnest(ARRAY[
      'warden_core.subjects','warden_core.tenants','warden_core.action_requests',
      'warden_infra.hosts','warden_infra.resources',
      'clyffe_core.orders','ai_bridge.identity_grants'])
  LOOP
    EXECUTE format(
      'DROP TRIGGER IF EXISTS set_updated_at ON %s; '||
      'CREATE TRIGGER set_updated_at BEFORE UPDATE ON %s '||
      'FOR EACH ROW EXECUTE FUNCTION warden_core.set_updated_at();', t, t);
  END LOOP;
END $$;

INSERT INTO warden_core.schema_migrations(version) VALUES ('0001_init')
  ON CONFLICT (version) DO NOTHING;

COMMIT;

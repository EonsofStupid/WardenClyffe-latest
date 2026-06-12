-- WardenClyffe canonical schema — migration 0002
-- Capture the managed compute PLATFORMS (Proxmox, the Coolify headless Docker
-- service) and the managed SERVICES (the foundation guests/containers) so
-- Warden — not ad-hoc IP pokes — is the system of record. Clyffy reads THIS.
--
-- Apply: psql -h 127.0.0.1 -U warden -d wardenclyffe -f data/schema/sql/0002_platforms_services.sql
-- Idempotent.

BEGIN;

-- ---------------------------------------------------------------------------
-- warden_infra.platforms — a control surface that runs workloads.
--   proxmox  = hypervisor managing LXCs/VMs (server1)
--   coolify  = the headless Docker PaaS managing containers (legacy 'fozzy')
--   docker   = a plain docker host
-- ---------------------------------------------------------------------------
DO $$ BEGIN
    CREATE TYPE warden_infra.platform_kind AS ENUM ('proxmox','coolify','docker','systemd');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS warden_infra.platforms (
    id             uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    stable_id      text NOT NULL UNIQUE,        -- platform.proxmox.server1
    kind           warden_infra.platform_kind NOT NULL,
    host_id        uuid REFERENCES warden_infra.hosts(id),
    api_endpoint   text,                         -- https://10.0.0.1:8006 ; coolify api
    network_name   text,                         -- e.g. coolify external network id
    managed        boolean NOT NULL DEFAULT true,
    credential_ref text,                         -- Infisical path; NEVER a value
    state          text NOT NULL DEFAULT 'unknown',
    note           text,
    created_at     timestamptz NOT NULL DEFAULT now(),
    updated_at     timestamptz NOT NULL DEFAULT now()
);

-- ---------------------------------------------------------------------------
-- warden_infra.services — a managed foundation service (LXC or container).
-- This is the Warden capture of FOUNDATION_SERVICE_MATRIX.
-- ---------------------------------------------------------------------------
DO $$ BEGIN
    CREATE TYPE warden_infra.service_role AS ENUM (
      'sql','vector','graph','identity','dns','edge','ca','embedder',
      'llm-gateway','observability','cache','secrets','network','operator',
      'scheduler','devstation','portal','package-cache','warden');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS warden_infra.services (
    id             uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    stable_id      text NOT NULL UNIQUE,         -- service.qdrant
    name           text NOT NULL,
    role           warden_infra.service_role NOT NULL,
    platform_id    uuid REFERENCES warden_infra.platforms(id),
    host_id        uuid REFERENCES warden_infra.hosts(id),
    vmid           integer,
    address        text,                         -- 10.0.0.106
    port           integer,                      -- 6333
    endpoint       text,                         -- http://10.0.0.106:6333
    auth_required  boolean NOT NULL DEFAULT true,
    credential_ref text,                         -- Infisical path; NEVER a value
    keep_decision  text,                         -- keep / replace / park
    customer_ready boolean NOT NULL DEFAULT false,
    owed           text[] NOT NULL DEFAULT '{}', -- configuration still owed
    state          text NOT NULL DEFAULT 'unknown',
    last_probe_at  timestamptz,
    last_probe_ok  boolean,
    note           text,
    created_at     timestamptz NOT NULL DEFAULT now(),
    updated_at     timestamptz NOT NULL DEFAULT now()
);
CREATE INDEX IF NOT EXISTS services_role_idx ON warden_infra.services(role);
CREATE INDEX IF NOT EXISTS services_platform_idx ON warden_infra.services(platform_id);

DO $$
DECLARE t text;
BEGIN
  FOR t IN SELECT unnest(ARRAY['warden_infra.platforms','warden_infra.services'])
  LOOP
    EXECUTE format(
      'DROP TRIGGER IF EXISTS set_updated_at ON %s; '||
      'CREATE TRIGGER set_updated_at BEFORE UPDATE ON %s '||
      'FOR EACH ROW EXECUTE FUNCTION warden_core.set_updated_at();', t, t);
  END LOOP;
END $$;

INSERT INTO warden_core.schema_migrations(version) VALUES ('0002_platforms_services')
  ON CONFLICT (version) DO NOTHING;

COMMIT;

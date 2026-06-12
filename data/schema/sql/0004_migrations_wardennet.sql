-- WardenClyffe canonical schema — migration 0004
-- Make the public IP migration (104 -> 204) first-class + auditable, and add the
-- WardenNet (Headscale) platform. Spec: docs/WARDEN_DEVSTATION_TURNKEY_WARDENNET_PLAN.md
-- Apply: psql -h 127.0.0.1 -U warden -d wardenclyffe -f data/schema/sql/0004_migrations_wardennet.sql
-- Idempotent.

BEGIN;

DO $$ BEGIN
    CREATE TYPE warden_infra.migration_state AS ENUM
        ('planned','dual_homed','cutover','draining','complete','rolled_back');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE TABLE IF NOT EXISTS warden_infra.ip_migrations (
    id          uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    scope       text NOT NULL,                 -- 'public-ingress' | host stable_id
    from_ip     text NOT NULL,
    to_ip       text NOT NULL,
    state       warden_infra.migration_state NOT NULL DEFAULT 'planned',
    started_at  timestamptz,
    cutover_at  timestamptz,
    note        text,
    created_at  timestamptz NOT NULL DEFAULT now(),
    updated_at  timestamptz NOT NULL DEFAULT now()
);

-- WardenNet (Headscale) as a network platform Warden manages.
INSERT INTO warden_infra.platforms (stable_id, kind, host_id, api_endpoint, network_name, managed, credential_ref, state, note)
VALUES ('platform.wardennet', 'docker',
   (SELECT id FROM warden_infra.hosts WHERE stable_id='host.us-wi.foundation-01'),
   NULL, 'wardennet', true, '/warden/wardennet/headscale', 'planned',
   'WardenNet: our branded Tailscale (Headscale control plane). Overlay that keeps devstations/DevForge reachable independent of the public ingress IP; OPNsense WireGuard is the failsafe.')
ON CONFLICT (stable_id) DO UPDATE SET note=EXCLUDED.note, network_name=EXCLUDED.network_name, updated_at=now();

DROP TRIGGER IF EXISTS set_updated_at ON warden_infra.ip_migrations;
CREATE TRIGGER set_updated_at BEFORE UPDATE ON warden_infra.ip_migrations
  FOR EACH ROW EXECUTE FUNCTION warden_core.set_updated_at();

INSERT INTO warden_core.schema_migrations(version) VALUES ('0004_migrations_wardennet')
  ON CONFLICT (version) DO NOTHING;

COMMIT;

-- WardenClyffe canonical schema — migration 0005
-- Warden owns route intent: the single-public-IP -> hostname -> internal ip:port
-- map, plus a port-assignment registry, plus per-service brand. This is what
-- "maximize our public IP" and "track the ports being assigned" require.
-- Spec: docs/WARDEN_DEVSTATION_TURNKEY_WARDENNET_PLAN.md (§3-4, edge), FOUNDATION_SERVICE_MATRIX.
-- Apply: psql -h 127.0.0.1 -U warden -d wardenclyffe -f data/schema/sql/0005_routes_brand.sql
-- Idempotent.

BEGIN;

-- brand each service (WardenClyffe-branded foundation under Warden)
ALTER TABLE warden_infra.services ADD COLUMN IF NOT EXISTS brand text;

DO $$ BEGIN
    CREATE TYPE warden_infra.route_exposure AS ENUM ('public','wardennet','internal');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    CREATE TYPE warden_infra.auth_gate AS ENUM ('none','authentik_oidc','authentik_forward','warden_proxy','self');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

-- The route/port registry. One row per exposed endpoint.
CREATE TABLE IF NOT EXISTS warden_infra.routes (
    id               uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    service_id       uuid REFERENCES warden_infra.services(id),
    exposure         warden_infra.route_exposure NOT NULL DEFAULT 'internal',
    public_hostname  text,                       -- id.clyffy.ai (null if not public)
    public_ip        text,                       -- the maximized public IP (104.. -> 204..)
    public_port      integer NOT NULL DEFAULT 443,
    edge             text,                       -- which edge terminates it (service.clyffy-edge)
    internal_address text NOT NULL,              -- 10.0.0.103
    internal_port    integer NOT NULL,           -- 9000
    protocol         text NOT NULL DEFAULT 'https', -- https|http|grpc|tcp|udp
    tls              boolean NOT NULL DEFAULT true,
    auth_gate        warden_infra.auth_gate NOT NULL DEFAULT 'warden_proxy',
    path_prefix      text,
    state            text NOT NULL DEFAULT 'planned',
    note             text,
    created_at       timestamptz NOT NULL DEFAULT now(),
    updated_at       timestamptz NOT NULL DEFAULT now(),
    -- a public hostname is unique; an internal ip:port is unique (no double-assign)
    UNIQUE (public_hostname),
    UNIQUE (internal_address, internal_port, protocol)
);
CREATE INDEX IF NOT EXISTS routes_service_idx ON warden_infra.routes(service_id);
CREATE INDEX IF NOT EXISTS routes_exposure_idx ON warden_infra.routes(exposure);

DO $$ BEGIN
  DROP TRIGGER IF EXISTS set_updated_at ON warden_infra.routes;
  CREATE TRIGGER set_updated_at BEFORE UPDATE ON warden_infra.routes
    FOR EACH ROW EXECUTE FUNCTION warden_core.set_updated_at();
END $$;

INSERT INTO warden_core.schema_migrations(version) VALUES ('0005_routes_brand')
  ON CONFLICT (version) DO NOTHING;

COMMIT;

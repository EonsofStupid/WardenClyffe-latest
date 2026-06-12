-- WardenClyffe canonical schema — migration 0003
-- Establish the official connector/plugin DESIGNATION + audiences on captured
-- services, with CHECK constraints that ENFORCE the privilege invariant:
--   plugin    => audiences = {ai}                 (AI-only intelligence plane)
--   connector => {operator,ai} ⊆ audiences        (operator+AI shared, Warden-brokered)
-- Spec: docs/WARDEN_CONNECTOR_PLUGIN_DESIGNATION.md
--
-- Apply: psql -h 127.0.0.1 -U warden -d wardenclyffe -f data/schema/sql/0003_designation.sql
-- Idempotent.

BEGIN;

DO $$ BEGIN
    CREATE TYPE warden_infra.designation AS ENUM ('connector','plugin','platform','core');
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

-- RRD / Reason Ready Daemon introduces a new intelligence role: the reranker.
ALTER TYPE warden_infra.service_role ADD VALUE IF NOT EXISTS 'reranker';

ALTER TABLE warden_infra.services
    ADD COLUMN IF NOT EXISTS designation warden_infra.designation NOT NULL DEFAULT 'connector',
    ADD COLUMN IF NOT EXISTS audiences   text[] NOT NULL DEFAULT ARRAY['operator','ai'];

-- audiences may only contain known principal kinds
ALTER TABLE warden_infra.services DROP CONSTRAINT IF EXISTS services_audiences_valid;
ALTER TABLE warden_infra.services ADD CONSTRAINT services_audiences_valid
    CHECK (audiences <@ ARRAY['operator','ai','customer']);

-- enforce the designation -> audiences invariant
ALTER TABLE warden_infra.services DROP CONSTRAINT IF EXISTS services_designation_invariant;
ALTER TABLE warden_infra.services ADD CONSTRAINT services_designation_invariant CHECK (
    (designation = 'plugin'    AND audiences = ARRAY['ai'])
 OR (designation = 'connector' AND audiences @> ARRAY['operator','ai'] AND NOT ('customer' = ANY(audiences)))
 OR (designation = 'platform'  AND audiences @> ARRAY['operator'])
 OR (designation = 'core')
);

INSERT INTO warden_core.schema_migrations(version) VALUES ('0003_designation')
  ON CONFLICT (version) DO NOTHING;

COMMIT;

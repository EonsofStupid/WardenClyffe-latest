-- Shippin canonical schema — migration 0008 (identity credentials + sessions)
-- Local password credentials and browser sessions for the shippin-auth
-- gateway service (DevForge gate and future product surfaces).
--
-- Apply: psql -h 127.0.0.1 -U shippin -d shippin_mesh -f data/schema/sql/0008_identity_sessions.sql
-- Idempotent: safe to re-run.

BEGIN;

-- Local password credential for a subject. Hash is pgcrypto crypt() bf.
CREATE TABLE IF NOT EXISTS identity.credentials (
    subject_id    uuid PRIMARY KEY REFERENCES shippin_core.subjects(id) ON DELETE CASCADE,
    password_hash text NOT NULL,
    created_at    timestamptz NOT NULL DEFAULT now(),
    updated_at    timestamptz NOT NULL DEFAULT now()
);

-- Browser session issued by shippin-auth. Stores sha256(token), never the token.
CREATE TABLE IF NOT EXISTS identity.sessions (
    token_hash   text PRIMARY KEY,
    subject_id   uuid NOT NULL REFERENCES shippin_core.subjects(id) ON DELETE CASCADE,
    created_at   timestamptz NOT NULL DEFAULT now(),
    expires_at   timestamptz NOT NULL,
    last_seen_at timestamptz NOT NULL DEFAULT now(),
    ip           text,
    user_agent   text
);

CREATE INDEX IF NOT EXISTS sessions_subject_idx ON identity.sessions (subject_id);
CREATE INDEX IF NOT EXISTS sessions_expires_idx ON identity.sessions (expires_at);

-- Verify a password and return the subject if the account is active.
CREATE OR REPLACE FUNCTION identity.verify_password(p_email citext, p_password text)
RETURNS uuid
LANGUAGE sql
STABLE
AS $$
    SELECT s.id
    FROM shippin_core.subjects s
    JOIN identity.credentials c ON c.subject_id = s.id
    WHERE s.email = p_email
      AND s.is_active
      AND c.password_hash = crypt(p_password, c.password_hash)
$$;

INSERT INTO shippin_core.schema_migrations(version) VALUES ('0008_identity_sessions')
  ON CONFLICT (version) DO NOTHING;

COMMIT;

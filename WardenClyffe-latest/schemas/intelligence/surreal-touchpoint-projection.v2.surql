-- ============================================================================
-- WardenClyffe touchpoint projection v2 (proposal)
-- ============================================================================
--
-- Purpose:
-- - Project Markdown touchpoints into dynamic, queryable SurrealDB graph rows.
-- - Keep Markdown as manifests, not memory.
-- - Feed Warden UI status, Qdrant sync, and generated context packs.
--
-- Safety:
-- - Additive proposal only. Do not apply to a live database without preflight.
-- - Product/customer truth remains in Postgres/Warden APIs.
-- ============================================================================

-- USE NS clyffy DB ai_memory;

DEFINE TABLE OVERWRITE touchpoint_source SCHEMAFULL CHANGEFEED 30d;
DEFINE FIELD OVERWRITE path ON touchpoint_source TYPE string;
DEFINE FIELD OVERWRITE source_hash ON touchpoint_source TYPE string;
DEFINE FIELD OVERWRITE source_commit ON touchpoint_source TYPE option<string>;
DEFINE FIELD OVERWRITE title ON touchpoint_source TYPE option<string>;
DEFINE FIELD OVERWRITE key ON touchpoint_source TYPE string ASSERT $value IN ["clyffy_touchpoint", "wardenclyffe_touchpoint"];
DEFINE FIELD OVERWRITE version ON touchpoint_source TYPE option<int>;
DEFINE FIELD OVERWRITE body_words ON touchpoint_source TYPE int DEFAULT 0;
DEFINE FIELD OVERWRITE heavy_sync ON touchpoint_source TYPE bool DEFAULT false;
DEFINE FIELD OVERWRITE heavy_touchpoint ON touchpoint_source TYPE bool DEFAULT false;
DEFINE FIELD OVERWRITE sync_qdrant ON touchpoint_source TYPE bool DEFAULT false;
DEFINE FIELD OVERWRITE sync_surreal ON touchpoint_source TYPE bool DEFAULT false;
DEFINE FIELD OVERWRITE warnings ON touchpoint_source TYPE array<string> DEFAULT [];
DEFINE FIELD OVERWRITE metadata ON touchpoint_source TYPE object FLEXIBLE DEFAULT {};
DEFINE FIELD OVERWRITE first_seen_at ON touchpoint_source TYPE datetime DEFAULT time::now();
DEFINE FIELD OVERWRITE updated_at ON touchpoint_source TYPE datetime DEFAULT time::now();
DEFINE INDEX OVERWRITE touchpoint_source_path ON touchpoint_source FIELDS path UNIQUE;
DEFINE INDEX OVERWRITE touchpoint_source_hash ON touchpoint_source FIELDS source_hash;

DEFINE TABLE OVERWRITE touchpoint_projection SCHEMAFULL CHANGEFEED 30d;
DEFINE FIELD OVERWRITE workspace_id ON touchpoint_projection TYPE option<string>;
DEFINE FIELD OVERWRITE project_key ON touchpoint_projection TYPE option<string>;
DEFINE FIELD OVERWRITE persona ON touchpoint_projection TYPE option<string>;
DEFINE FIELD OVERWRITE kind ON touchpoint_projection TYPE option<string>;
DEFINE FIELD OVERWRITE owner ON touchpoint_projection TYPE option<string>;
DEFINE FIELD OVERWRITE module ON touchpoint_projection TYPE option<string>;
DEFINE FIELD OVERWRITE source ON touchpoint_projection TYPE record<touchpoint_source>;
DEFINE FIELD OVERWRITE status ON touchpoint_projection TYPE string DEFAULT "active"
  ASSERT $value IN ["active", "stale", "broken", "archived", "ignored"];
DEFINE FIELD OVERWRITE projection_policy ON touchpoint_projection TYPE string DEFAULT "manifest_summary"
  ASSERT $value IN ["metadata_only", "manifest_summary", "full_summary", "no_sync"];
DEFINE FIELD OVERWRITE qdrant_point_id ON touchpoint_projection TYPE option<string>;
DEFINE FIELD OVERWRITE surreal_projection_id ON touchpoint_projection TYPE option<string>;
DEFINE FIELD OVERWRITE last_projected_at ON touchpoint_projection TYPE option<datetime>;
DEFINE FIELD OVERWRITE metadata ON touchpoint_projection TYPE object FLEXIBLE DEFAULT {};
DEFINE FIELD OVERWRITE created_at ON touchpoint_projection TYPE datetime DEFAULT time::now();
DEFINE FIELD OVERWRITE updated_at ON touchpoint_projection TYPE datetime DEFAULT time::now();
DEFINE INDEX OVERWRITE touchpoint_projection_scope ON touchpoint_projection FIELDS workspace_id, project_key;
DEFINE INDEX OVERWRITE touchpoint_projection_owner ON touchpoint_projection FIELDS owner;
DEFINE INDEX OVERWRITE touchpoint_projection_status ON touchpoint_projection FIELDS status;

DEFINE TABLE OVERWRITE projection_run SCHEMAFULL CHANGEFEED 30d;
DEFINE FIELD OVERWRITE run_key ON projection_run TYPE string;
DEFINE FIELD OVERWRITE worker ON projection_run TYPE string;
DEFINE FIELD OVERWRITE source_commit ON projection_run TYPE option<string>;
DEFINE FIELD OVERWRITE status ON projection_run TYPE string DEFAULT "running"
  ASSERT $value IN ["running", "completed", "failed", "partial"];
DEFINE FIELD OVERWRITE counts ON projection_run TYPE object FLEXIBLE DEFAULT {};
DEFINE FIELD OVERWRITE started_at ON projection_run TYPE datetime DEFAULT time::now();
DEFINE FIELD OVERWRITE ended_at ON projection_run TYPE option<datetime>;
DEFINE FIELD OVERWRITE trace_id ON projection_run TYPE option<string>;
DEFINE FIELD OVERWRITE metadata ON projection_run TYPE object FLEXIBLE DEFAULT {};
DEFINE INDEX OVERWRITE projection_run_key ON projection_run FIELDS run_key UNIQUE;
DEFINE INDEX OVERWRITE projection_run_status ON projection_run FIELDS status, started_at;

DEFINE TABLE OVERWRITE projection_event SCHEMAFULL CHANGEFEED 30d;
DEFINE FIELD OVERWRITE run ON projection_event TYPE record<projection_run>;
DEFINE FIELD OVERWRITE level ON projection_event TYPE string DEFAULT "info"
  ASSERT $value IN ["debug", "info", "warn", "error"];
DEFINE FIELD OVERWRITE event_type ON projection_event TYPE string;
DEFINE FIELD OVERWRITE message ON projection_event TYPE string;
DEFINE FIELD OVERWRITE source ON projection_event TYPE option<record<touchpoint_source>>;
DEFINE FIELD OVERWRITE projection ON projection_event TYPE option<record<touchpoint_projection>>;
DEFINE FIELD OVERWRITE trace_id ON projection_event TYPE option<string>;
DEFINE FIELD OVERWRITE metadata ON projection_event TYPE object FLEXIBLE DEFAULT {};
DEFINE FIELD OVERWRITE created_at ON projection_event TYPE datetime DEFAULT time::now();
DEFINE INDEX OVERWRITE projection_event_run_time ON projection_event FIELDS run, created_at;
DEFINE INDEX OVERWRITE projection_event_level ON projection_event FIELDS level, created_at;

DEFINE TABLE OVERWRITE projection_drift SCHEMAFULL CHANGEFEED 30d;
DEFINE FIELD OVERWRITE source ON projection_drift TYPE record<touchpoint_source>;
DEFINE FIELD OVERWRITE projection ON projection_drift TYPE option<record<touchpoint_projection>>;
DEFINE FIELD OVERWRITE drift_type ON projection_drift TYPE string
  ASSERT $value IN ["v1_deprecated", "missing_field", "oversized_sync", "broken_route", "registry_mismatch", "owner_missing", "stale_hash"];
DEFINE FIELD OVERWRITE severity ON projection_drift TYPE string DEFAULT "warn"
  ASSERT $value IN ["info", "warn", "error", "critical"];
DEFINE FIELD OVERWRITE status ON projection_drift TYPE string DEFAULT "open"
  ASSERT $value IN ["open", "acknowledged", "fixed", "ignored"];
DEFINE FIELD OVERWRITE message ON projection_drift TYPE string;
DEFINE FIELD OVERWRITE detected_at ON projection_drift TYPE datetime DEFAULT time::now();
DEFINE FIELD OVERWRITE resolved_at ON projection_drift TYPE option<datetime>;
DEFINE FIELD OVERWRITE metadata ON projection_drift TYPE object FLEXIBLE DEFAULT {};
DEFINE INDEX OVERWRITE projection_drift_status ON projection_drift FIELDS status, severity, drift_type;

DEFINE TABLE OVERWRITE context_pack SCHEMAFULL CHANGEFEED 7d;
DEFINE FIELD OVERWRITE workspace_id ON context_pack TYPE string;
DEFINE FIELD OVERWRITE project_key ON context_pack TYPE string;
DEFINE FIELD OVERWRITE pack_key ON context_pack TYPE string;
DEFINE FIELD OVERWRITE purpose ON context_pack TYPE string;
DEFINE FIELD OVERWRITE summary ON context_pack TYPE string;
DEFINE FIELD OVERWRITE source_hashes ON context_pack TYPE array<string> DEFAULT [];
DEFINE FIELD OVERWRITE source_query_hash ON context_pack TYPE string;
DEFINE FIELD OVERWRITE expires_at ON context_pack TYPE option<datetime>;
DEFINE FIELD OVERWRITE created_by ON context_pack TYPE string;
DEFINE FIELD OVERWRITE trace_id ON context_pack TYPE option<string>;
DEFINE FIELD OVERWRITE metadata ON context_pack TYPE object FLEXIBLE DEFAULT {};
DEFINE FIELD OVERWRITE created_at ON context_pack TYPE datetime DEFAULT time::now();
DEFINE INDEX OVERWRITE context_pack_key ON context_pack FIELDS pack_key UNIQUE;
DEFINE INDEX OVERWRITE context_pack_scope ON context_pack FIELDS workspace_id, project_key, created_at;

DEFINE TABLE OVERWRITE projection_checkpoint SCHEMAFULL;
DEFINE FIELD OVERWRITE worker ON projection_checkpoint TYPE string;
DEFINE FIELD OVERWRITE checkpoint ON projection_checkpoint TYPE object FLEXIBLE DEFAULT {};
DEFINE FIELD OVERWRITE updated_at ON projection_checkpoint TYPE datetime DEFAULT time::now();
DEFINE INDEX OVERWRITE projection_checkpoint_worker ON projection_checkpoint FIELDS worker UNIQUE;

DEFINE TABLE OVERWRITE projection_target SCHEMAFULL CHANGEFEED 30d;
DEFINE FIELD OVERWRITE target_key ON projection_target TYPE string;
DEFINE FIELD OVERWRITE target_type ON projection_target TYPE string
  ASSERT $value IN ["registry", "mcp_server", "tool", "service", "module", "task", "audit", "host", "doc"];
DEFINE FIELD OVERWRITE label ON projection_target TYPE option<string>;
DEFINE FIELD OVERWRITE metadata ON projection_target TYPE object FLEXIBLE DEFAULT {};
DEFINE FIELD OVERWRITE updated_at ON projection_target TYPE datetime DEFAULT time::now();
DEFINE INDEX OVERWRITE projection_target_key ON projection_target FIELDS target_key UNIQUE;
DEFINE INDEX OVERWRITE projection_target_type ON projection_target FIELDS target_type;

-- Graph relations
DEFINE TABLE OVERWRITE routes_to TYPE RELATION FROM touchpoint_projection TO projection_target;
DEFINE TABLE OVERWRITE depends_on TYPE RELATION FROM touchpoint_projection TO touchpoint_projection;
DEFINE TABLE OVERWRITE uses_source TYPE RELATION FROM context_pack TO touchpoint_source;
DEFINE TABLE OVERWRITE emits_pack TYPE RELATION FROM projection_run TO context_pack;
DEFINE TABLE OVERWRITE supersedes_projection TYPE RELATION FROM touchpoint_projection TO touchpoint_projection;
DEFINE TABLE OVERWRITE mentions_service TYPE RELATION FROM touchpoint_projection TO projection_target;

-- Reactive enqueue events. These create event rows for workers/UI; external
-- workers still own Qdrant writes and context-pack generation.
DEFINE EVENT OVERWRITE touchpoint_source_changed ON TABLE touchpoint_source
ASYNC RETRY 3 MAXDEPTH 2
WHEN $event = "CREATE" OR $event = "UPDATE"
THEN {
  CREATE projection_event SET
    run = projection_run:system,
    level = "info",
    event_type = "touchpoint_source_changed",
    message = string::concat("touchpoint source changed: ", $after.path),
    source = $after.id,
    metadata = {
      source_hash: $after.source_hash,
      sync_qdrant: $after.sync_qdrant,
      sync_surreal: $after.sync_surreal
    },
    created_at = time::now();
};

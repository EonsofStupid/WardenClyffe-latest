package core

// This file is the ONLY place in Vaultix that speaks the pinned core
// image's wire dialect. Nothing Vaultix-facing — contract routes,
// capabilities, audit actions, config — uses these names. When our fork
// rebrands its API surface, this file is the single swap point.
//
// All paths are on the NON-deprecated upstream surface (verified against
// v0.162.19 source, doc 0006): /api/v1/projects and /api/v4/secrets.
// The v1 /workspace and v3 /secrets routes live in files upstream names
// deprecated-* — we do not build on those.
const (
	wireLoginPath        = "/api/v1/auth/universal-auth/login"
	wireTokenRenewPath   = "/api/v1/auth/token/renew"
	wireProjectsPath     = "/api/v1/projects"      // POST create; GET /{projectId}
	wireSecretsPath      = "/api/v4/secrets"       // GET list; POST/PATCH /{name}
	wireSecretsBatchPath = "/api/v4/secrets/batch" // POST create-many; PATCH update-many
	wireStatusPath       = "/api/status"
)

// StatusPath is exported for the instance health probe.
const StatusPath = wireStatusPath

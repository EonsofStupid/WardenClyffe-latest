package connect

import (
	"net/http"

	"github.com/go-chi/chi/v5"

	"github.com/shippin/shippin-api/internal/platform"
)

// Handler exposes the Connect & Launch surface under /api/warden/connect.
// Status is open to the console; activations are operator-gated and never log
// the supplied secret.
type Handler struct {
	store *Store
	authz platform.Authorizer
}

func NewHandler(store *Store, authz platform.Authorizer) *Handler {
	return &Handler{store: store, authz: authz}
}

func (h *Handler) Routes(r chi.Router) {
	r.Get("/api/warden/connect/status", h.status)
	r.With(platform.RequireOperator(h.authz)).Post("/api/warden/connect/infisical", h.infisical)
	r.With(platform.RequireOperator(h.authz)).Post("/api/warden/connect/github", h.github)
}

func (h *Handler) status(w http.ResponseWriter, r *http.Request) {
	items := h.store.Status(r.Context())
	platform.JSON(w, http.StatusOK, map[string]any{"items": items, "count": len(items)})
}

func (h *Handler) infisical(w http.ResponseWriter, r *http.Request) {
	var in InfisicalInput
	if err := platform.DecodeJSON(r, &in); err != nil || in.ClientSecret == "" {
		platform.Error(w, http.StatusBadRequest, "client_secret is required")
		return
	}
	out, err := h.store.ActivateInfisical(r.Context(), in)
	if err != nil {
		// out carries installer/status text (no secret); surface it to the panel.
		platform.JSON(w, http.StatusBadGateway, map[string]any{"ok": false, "output": out, "error": err.Error()})
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"ok": true, "output": out})
}

func (h *Handler) github(w http.ResponseWriter, r *http.Request) {
	var in struct {
		Token string `json:"token"`
	}
	if err := platform.DecodeJSON(r, &in); err != nil || in.Token == "" {
		platform.Error(w, http.StatusBadRequest, "token is required")
		return
	}
	out, err := h.store.ActivateGitHub(r.Context(), in.Token)
	if err != nil {
		platform.JSON(w, http.StatusBadGateway, map[string]any{"ok": false, "output": out, "error": err.Error()})
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"ok": true, "output": out})
}

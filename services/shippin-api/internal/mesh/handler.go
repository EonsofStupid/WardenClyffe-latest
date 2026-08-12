package mesh

import (
	"net/http"

	"github.com/go-chi/chi/v5"

	"github.com/shippin/shippin-api/internal/platform"
)

// Handler exposes the mesh context under /api/warden/mesh.
type Handler struct {
	store *Store
	authz platform.Authorizer
}

// NewHandler wires the mesh store and the operator authorizer used to gate
// mutating routes (sync/run).
func NewHandler(store *Store, authz platform.Authorizer) *Handler {
	return &Handler{store: store, authz: authz}
}

func (h *Handler) Routes(r chi.Router) {
	r.Get("/api/warden/mesh/plugins", h.plugins)
	r.Get("/api/warden/mesh/plugins/{id}/connect", h.connect)
	r.Get("/api/warden/mesh/intelligence", h.intelligence)
	r.Get("/api/warden/mesh/projection", h.projection)
	r.With(platform.RequireOperator(h.authz)).
		Post("/api/warden/mesh/sync/run", h.syncRun)
}

func (h *Handler) plugins(w http.ResponseWriter, r *http.Request) {
	items, err := h.store.ListPlugins()
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"items": items, "count": len(items)})
}

func (h *Handler) connect(w http.ResponseWriter, r *http.Request) {
	d, ok := h.store.GetConnectDescriptors(chi.URLParam(r, "id"))
	if !ok {
		platform.Error(w, http.StatusNotFound, "no connect descriptors for this plugin (not shipped on W)")
		return
	}
	platform.JSON(w, http.StatusOK, d)
}

func (h *Handler) intelligence(w http.ResponseWriter, r *http.Request) {
	inv, err := h.store.IntelligenceInventory(r.Context())
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, inv)
}

// projection returns the latest sync projection plan. Read-only; the empty
// state (sync never run) is a normal 200 with present=false.
func (h *Handler) projection(w http.ResponseWriter, r *http.Request) {
	plan, ok, err := h.store.ReadProjection()
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"present": ok, "plan": plan})
}

// syncRun triggers an intelligence-sync run (operator-gated) and returns the
// resulting plan. Content-hash idempotent: a no-change run reports "unchanged".
func (h *Handler) syncRun(w http.ResponseWriter, r *http.Request) {
	plan, err := h.store.RunSync(r.Context())
	if err != nil {
		platform.Error(w, http.StatusBadGateway, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"ran": true, "plan": plan})
}

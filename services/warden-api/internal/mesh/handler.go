package mesh

import (
	"net/http"

	"github.com/go-chi/chi/v5"

	"github.com/wardenclyffe/warden-api/internal/platform"
)

// Handler exposes the mesh context under /api/warden/mesh.
type Handler struct{ store *Store }

func NewHandler(store *Store) *Handler { return &Handler{store: store} }

func (h *Handler) Routes(r chi.Router) {
	r.Get("/api/warden/mesh/plugins", h.plugins)
	r.Get("/api/warden/mesh/plugins/{id}/connect", h.connect)
	r.Get("/api/warden/mesh/intelligence", h.intelligence)
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

package edge

import (
	"errors"
	"net/http"

	"github.com/go-chi/chi/v5"
	"github.com/jackc/pgx/v5"

	"github.com/wardenclyffe/warden-api/internal/platform"
)

// Handler exposes the public-IP inventory under /api/warden/edge.
// Reads are open to the console; mutations are operator-gated.
type Handler struct {
	store *Store
	authz platform.Authorizer
}

func NewHandler(store *Store, authz platform.Authorizer) *Handler {
	return &Handler{store: store, authz: authz}
}

func (h *Handler) Routes(r chi.Router) {
	r.Get("/api/warden/edge/ips", h.list)
	r.With(platform.RequireOperator(h.authz)).Post("/api/warden/edge/ips", h.create)
	r.With(platform.RequireOperator(h.authz)).Patch("/api/warden/edge/ips/{id}", h.update)
}

func (h *Handler) list(w http.ResponseWriter, r *http.Request) {
	items, err := h.store.ListIPs(r.Context())
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"items": items, "count": len(items)})
}

func (h *Handler) create(w http.ResponseWriter, r *http.Request) {
	var in CreateIPInput
	if err := platform.DecodeJSON(r, &in); err != nil || in.Address == "" || in.Role == "" {
		platform.Error(w, http.StatusBadRequest, "address and role are required")
		return
	}
	p, err := h.store.CreateIP(r.Context(), in)
	if err != nil {
		platform.Error(w, http.StatusBadRequest, err.Error())
		return
	}
	platform.JSON(w, http.StatusCreated, p)
}

func (h *Handler) update(w http.ResponseWriter, r *http.Request) {
	var in UpdateIPInput
	if err := platform.DecodeJSON(r, &in); err != nil {
		platform.Error(w, http.StatusBadRequest, "invalid request body")
		return
	}
	p, err := h.store.UpdateIP(r.Context(), chi.URLParam(r, "id"), in)
	if errors.Is(err, pgx.ErrNoRows) {
		platform.Error(w, http.StatusNotFound, "public ip not found")
		return
	}
	if err != nil {
		platform.Error(w, http.StatusBadRequest, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, p)
}

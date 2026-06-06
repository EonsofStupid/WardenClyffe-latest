package automation

import (
	"net/http"

	"github.com/go-chi/chi/v5"

	"github.com/wardenclyffe/warden-api/internal/platform"
)

type Handler struct{ svc *Service }

func NewHandler(svc *Service) *Handler { return &Handler{svc: svc} }

func (h *Handler) Routes(r chi.Router) {
	r.Post("/api/warden/provision", h.provision)
}

func (h *Handler) provision(w http.ResponseWriter, r *http.Request) {
	var in ProvisionInput
	if err := platform.DecodeJSON(r, &in); err != nil {
		platform.Error(w, http.StatusBadRequest, "invalid body: "+err.Error())
		return
	}
	res, err := h.svc.Provision(r.Context(), in)
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusCreated, res)
}

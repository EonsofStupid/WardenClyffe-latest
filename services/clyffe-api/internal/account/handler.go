package account

import (
	"net/http"

	"github.com/go-chi/chi/v5"

	"github.com/wardenclyffe/clyffe-api/internal/platform"
)

// Handler exposes the customer portal under /api/clyffe.
type Handler struct{ store *Store }

func NewHandler(store *Store) *Handler { return &Handler{store: store} }

func (h *Handler) Routes(r chi.Router) {
	r.Get("/api/clyffe/home", h.home)
	r.Get("/api/clyffe/accounts", h.accounts)
	r.Get("/api/clyffe/orders", h.orders)
}

func (h *Handler) accounts(w http.ResponseWriter, r *http.Request) {
	items, err := h.store.ListAccounts(r.Context())
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"items": items, "count": len(items)})
}

func (h *Handler) orders(w http.ResponseWriter, r *http.Request) {
	tenant := r.URL.Query().Get("tenant_id")
	items, err := h.store.ListOrders(r.Context(), tenant)
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"items": items, "count": len(items)})
}

// home is the customer portal overview: the account(s) and a roll-up of orders.
func (h *Handler) home(w http.ResponseWriter, r *http.Request) {
	ctx := r.Context()
	accounts, err := h.store.ListAccounts(ctx)
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	orders, err := h.store.ListOrders(ctx, "")
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	byStatus := map[string]int{}
	for _, o := range orders {
		byStatus[o.Status]++
	}
	platform.JSON(w, http.StatusOK, map[string]any{
		"portal":  "clyffe",
		"persona": "clyffe-customer",
		"summary": map[string]any{
			"accounts":         len(accounts),
			"orders":           len(orders),
			"orders_by_status": byStatus,
		},
		"accounts": accounts,
		"note":     "Clyffe is the customer plane. It reads customer-safe data only; operator infra is reached through sanitized Warden endpoints.",
	})
}

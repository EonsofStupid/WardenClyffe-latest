package identity

import (
	"net/http"
	"strings"

	"github.com/go-chi/chi/v5"

	"github.com/wardenclyffe/warden-api/internal/platform"
)

// Handler exposes the identity context under /api/warden/identity.
type Handler struct {
	store    *Store
	accounts *Accounts
}

func NewHandler(store *Store, accounts *Accounts) *Handler {
	return &Handler{store: store, accounts: accounts}
}

func (h *Handler) Routes(r chi.Router) {
	r.Post("/api/warden/identity/login", h.login)
	r.Post("/api/warden/identity/logout", h.logout)
	r.Get("/api/warden/identity/me", h.me)
	r.Post("/api/warden/identity/customers", h.createCustomer)
	r.Post("/api/warden/identity/verify-email", h.verifyEmail)
}

type createCustomerRequest struct {
	Email       string `json:"email"`
	DisplayName string `json:"display_name"`
}

func (h *Handler) createCustomer(w http.ResponseWriter, r *http.Request) {
	op, ok := h.store.Validate(bearer(r))
	if !ok || op.Role != "operator" {
		platform.Error(w, http.StatusUnauthorized, "unauthorized")
		return
	}
	var in createCustomerRequest
	if err := platform.DecodeJSON(r, &in); err != nil || in.Email == "" || in.DisplayName == "" {
		platform.Error(w, http.StatusBadRequest, "email and display_name are required")
		return
	}
	res, err := h.accounts.CreateCustomer(r.Context(), in.Email, in.DisplayName)
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusCreated, res)
}

type verifyEmailRequest struct {
	Token string `json:"token"`
}

func (h *Handler) verifyEmail(w http.ResponseWriter, r *http.Request) {
	var in verifyEmailRequest
	if err := platform.DecodeJSON(r, &in); err != nil || in.Token == "" {
		platform.Error(w, http.StatusBadRequest, "token is required")
		return
	}
	ok, err := h.accounts.VerifyEmail(r.Context(), in.Token)
	if err != nil {
		platform.Error(w, http.StatusInternalServerError, err.Error())
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"verified": ok})
}

type loginRequest struct {
	Username string `json:"username"`
	Password string `json:"password"`
}

func (h *Handler) login(w http.ResponseWriter, r *http.Request) {
	var in loginRequest
	if err := platform.DecodeJSON(r, &in); err != nil {
		platform.Error(w, http.StatusBadRequest, "invalid request body")
		return
	}
	tok, op, err := h.store.Login(in.Username, in.Password)
	if err != nil {
		platform.Error(w, http.StatusUnauthorized, "invalid credentials")
		return
	}
	platform.JSON(w, http.StatusOK, map[string]any{"token": tok, "operator": op})
}

func (h *Handler) me(w http.ResponseWriter, r *http.Request) {
	op, ok := h.store.Validate(bearer(r))
	if !ok {
		platform.Error(w, http.StatusUnauthorized, "unauthorized")
		return
	}
	platform.JSON(w, http.StatusOK, op)
}

func (h *Handler) logout(w http.ResponseWriter, r *http.Request) {
	h.store.Logout(bearer(r))
	platform.JSON(w, http.StatusNoContent, nil)
}

// bearer extracts the token from an Authorization: Bearer <token> header.
func bearer(r *http.Request) string {
	h := r.Header.Get("Authorization")
	if after, ok := strings.CutPrefix(h, "Bearer "); ok {
		return strings.TrimSpace(after)
	}
	return ""
}

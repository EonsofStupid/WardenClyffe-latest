package platform

import (
	"encoding/json"
	"log"
	"net/http"
	"strings"
)

// JSON writes v as a JSON response with the given status code.
func JSON(w http.ResponseWriter, status int, v any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	if v == nil {
		return
	}
	if err := json.NewEncoder(w).Encode(v); err != nil {
		log.Printf("json encode error: %v", err)
	}
}

// Error writes a structured JSON error.
func Error(w http.ResponseWriter, status int, msg string) {
	JSON(w, status, map[string]any{
		"error": map[string]any{
			"status":  status,
			"message": msg,
		},
	})
}

// DecodeJSON decodes a request body into v, rejecting unknown fields.
func DecodeJSON(r *http.Request, v any) error {
	dec := json.NewDecoder(r.Body)
	dec.DisallowUnknownFields()
	return dec.Decode(v)
}

// Authorizer reports whether a bearer token belongs to an authenticated
// operator. It is a function adapter so platform need not import identity —
// the identity context supplies the closure at wiring time.
type Authorizer func(token string) bool

// Bearer extracts the token from an Authorization: Bearer <token> header.
func Bearer(r *http.Request) string {
	if after, ok := strings.CutPrefix(r.Header.Get("Authorization"), "Bearer "); ok {
		return strings.TrimSpace(after)
	}
	return ""
}

// RequireOperator gates a handler: 401 unless the bearer resolves to an
// operator via authorize. Reusable across contexts (mesh, future admin writes).
func RequireOperator(authorize Authorizer) func(http.Handler) http.Handler {
	return func(next http.Handler) http.Handler {
		return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
			if !authorize(Bearer(r)) {
				Error(w, http.StatusUnauthorized, "operator authorization required")
				return
			}
			next.ServeHTTP(w, r)
		})
	}
}

// CORS is a permissive dev CORS middleware so the Vite console (different port)
// can call the API during development.
func CORS(next http.Handler) http.Handler {
	return http.HandlerFunc(func(w http.ResponseWriter, r *http.Request) {
		w.Header().Set("Access-Control-Allow-Origin", "*")
		w.Header().Set("Access-Control-Allow-Methods", "GET, POST, PUT, PATCH, DELETE, OPTIONS")
		w.Header().Set("Access-Control-Allow-Headers", "Content-Type, Authorization")
		if r.Method == http.MethodOptions {
			w.WriteHeader(http.StatusNoContent)
			return
		}
		next.ServeHTTP(w, r)
	})
}

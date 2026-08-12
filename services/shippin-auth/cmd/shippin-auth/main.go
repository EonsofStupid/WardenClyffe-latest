// shippin-auth is the session gate in front of hosted product surfaces
// (first consumer: the DevForge web IDE behind the nginx gateway).
//
// nginx integration:
//   - `auth_request /auth/check` → 204 when the session cookie is valid, 401 otherwise
//   - /auth/login (GET form, POST credentials), /auth/logout
//
// Credentials and sessions live in shippin_mesh (identity.credentials,
// identity.sessions); passwords verify through identity.verify_password.
package main

import (
	"context"
	"crypto/rand"
	"crypto/sha256"
	"encoding/hex"
	"html/template"
	"log"
	"net/http"
	"os"
	"strings"
	"time"

	"github.com/jackc/pgx/v5/pgxpool"
)

type app struct {
	db         *pgxpool.Pool
	cookieName string
	ttl        time.Duration
	secure     bool
}

func env(key, fallback string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return fallback
}

func main() {
	addr := env("SHIPPIN_AUTH_ADDR", "127.0.0.1:8092")
	dbURL := env("SHIPPIN_AUTH_DB_URL", "postgres://shippin:shippin_dev_local@127.0.0.1:5432/shippin_mesh?sslmode=disable")
	ttl, err := time.ParseDuration(env("SHIPPIN_AUTH_SESSION_TTL", "168h"))
	if err != nil {
		log.Fatalf("bad SHIPPIN_AUTH_SESSION_TTL: %v", err)
	}

	pool, err := pgxpool.New(context.Background(), dbURL)
	if err != nil {
		log.Fatalf("db: %v", err)
	}
	a := &app{
		db:         pool,
		cookieName: env("SHIPPIN_AUTH_COOKIE", "shippin_session"),
		ttl:        ttl,
		secure:     env("SHIPPIN_AUTH_SECURE_COOKIE", "1") == "1",
	}

	mux := http.NewServeMux()
	mux.HandleFunc("GET /auth/check", a.check)
	mux.HandleFunc("GET /auth/login", a.loginForm)
	mux.HandleFunc("POST /auth/login", a.login)
	mux.HandleFunc("GET /auth/logout", a.logout)
	mux.HandleFunc("GET /auth/healthz", func(w http.ResponseWriter, _ *http.Request) {
		w.WriteHeader(http.StatusNoContent)
	})

	log.Printf("shippin-auth listening on %s (cookie=%s ttl=%s)", addr, a.cookieName, a.ttl)
	log.Fatal(http.ListenAndServe(addr, mux))
}

func hashToken(token string) string {
	sum := sha256.Sum256([]byte(token))
	return hex.EncodeToString(sum[:])
}

// subjectForRequest returns the subject id for a valid session cookie, or "".
func (a *app) subjectForRequest(r *http.Request) string {
	c, err := r.Cookie(a.cookieName)
	if err != nil || c.Value == "" {
		return ""
	}
	var subject string
	err = a.db.QueryRow(r.Context(),
		`UPDATE identity.sessions
		 SET last_seen_at = now(), expires_at = now() + $2::interval
		 WHERE token_hash = $1 AND expires_at > now()
		 RETURNING subject_id::text`,
		hashToken(c.Value), a.ttl.String()).Scan(&subject)
	if err != nil {
		return ""
	}
	return subject
}

func (a *app) check(w http.ResponseWriter, r *http.Request) {
	if subject := a.subjectForRequest(r); subject != "" {
		w.Header().Set("X-Auth-Subject", subject)
		w.WriteHeader(http.StatusNoContent)
		return
	}
	http.Error(w, "unauthenticated", http.StatusUnauthorized)
}

// safeNext only allows same-origin absolute paths.
func safeNext(next string) string {
	if strings.HasPrefix(next, "/") && !strings.HasPrefix(next, "//") {
		return next
	}
	return "/"
}

var loginTmpl = template.Must(template.New("login").Parse(`<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Sign in — DevForge</title>
<style>
	:root { color-scheme: dark; }
	* { box-sizing: border-box; margin: 0; }
	body {
		min-height: 100vh; display: grid; place-items: center;
		background: radial-gradient(1200px 600px at 20% -10%, #16233a 0%, #0b1220 55%, #070d18 100%);
		color: #e6edf3; font: 15px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif;
	}
	form {
		width: min(360px, 92vw); padding: 2rem;
		background: rgba(17, 26, 44, 0.85); border: 1px solid #223047; border-radius: 12px;
		box-shadow: 0 18px 50px rgba(0, 0, 0, 0.45);
	}
	h1 { font-size: 1.15rem; margin-bottom: .25rem; }
	p.sub { color: #8b98ab; font-size: .85rem; margin-bottom: 1.25rem; }
	label { display: block; font-size: .8rem; color: #aab6c8; margin: .8rem 0 .3rem; }
	input {
		width: 100%; padding: .6rem .7rem; border-radius: 8px;
		border: 1px solid #2c3b55; background: #0d1626; color: #e6edf3; font-size: .95rem;
	}
	input:focus { outline: none; border-color: #3b82f6; }
	button {
		width: 100%; margin-top: 1.25rem; padding: .65rem;
		background: #2563eb; color: #fff; border: 0; border-radius: 8px;
		font-size: .95rem; font-weight: 600; cursor: pointer;
	}
	button:hover { background: #1d4ed8; }
	.err {
		margin-bottom: 1rem; padding: .55rem .7rem; border-radius: 8px;
		background: rgba(190, 60, 60, 0.15); border: 1px solid #7f2f2f; color: #f0b0b0; font-size: .85rem;
	}
</style>
</head>
<body>
<form method="post" action="/auth/login">
	<h1>DevForge</h1>
	<p class="sub">Sign in to your Shippin instance</p>
	{{if .Error}}<div class="err">{{.Error}}</div>{{end}}
	<input type="hidden" name="next" value="{{.Next}}">
	<label for="email">Email</label>
	<input id="email" name="email" type="email" autocomplete="username" required autofocus>
	<label for="password">Password</label>
	<input id="password" name="password" type="password" autocomplete="current-password" required>
	<button type="submit">Sign in</button>
</form>
</body>
</html>`))

func (a *app) loginForm(w http.ResponseWriter, r *http.Request) {
	next := safeNext(r.URL.Query().Get("next"))
	if a.subjectForRequest(r) != "" {
		http.Redirect(w, r, next, http.StatusFound)
		return
	}
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	_ = loginTmpl.Execute(w, map[string]string{"Next": next, "Error": ""})
}

func (a *app) login(w http.ResponseWriter, r *http.Request) {
	if err := r.ParseForm(); err != nil {
		http.Error(w, "bad request", http.StatusBadRequest)
		return
	}
	next := safeNext(r.PostFormValue("next"))
	email := strings.TrimSpace(r.PostFormValue("email"))
	password := r.PostFormValue("password")

	var subject string
	err := a.db.QueryRow(r.Context(),
		`SELECT identity.verify_password($1, $2)::text`, email, password).Scan(&subject)
	if err != nil || subject == "" {
		// Uniform failure path; slow it down a little.
		time.Sleep(400 * time.Millisecond)
		w.Header().Set("Content-Type", "text/html; charset=utf-8")
		w.WriteHeader(http.StatusUnauthorized)
		_ = loginTmpl.Execute(w, map[string]string{"Next": next, "Error": "Invalid email or password."})
		return
	}

	raw := make([]byte, 32)
	if _, err := rand.Read(raw); err != nil {
		http.Error(w, "internal error", http.StatusInternalServerError)
		return
	}
	token := hex.EncodeToString(raw)

	ip := r.Header.Get("X-Forwarded-For")
	_, err = a.db.Exec(r.Context(),
		`INSERT INTO identity.sessions (token_hash, subject_id, expires_at, ip, user_agent)
		 VALUES ($1, $2::uuid, now() + $3::interval, $4, $5)`,
		hashToken(token), subject, a.ttl.String(), ip, r.UserAgent())
	if err != nil {
		log.Printf("session insert: %v", err)
		http.Error(w, "internal error", http.StatusInternalServerError)
		return
	}

	http.SetCookie(w, &http.Cookie{
		Name:     a.cookieName,
		Value:    token,
		Path:     "/",
		MaxAge:   int(a.ttl.Seconds()),
		HttpOnly: true,
		Secure:   a.secure,
		SameSite: http.SameSiteLaxMode,
	})
	http.Redirect(w, r, next, http.StatusFound)
}

func (a *app) logout(w http.ResponseWriter, r *http.Request) {
	if c, err := r.Cookie(a.cookieName); err == nil && c.Value != "" {
		_, _ = a.db.Exec(r.Context(),
			`DELETE FROM identity.sessions WHERE token_hash = $1`, hashToken(c.Value))
	}
	http.SetCookie(w, &http.Cookie{
		Name: a.cookieName, Value: "", Path: "/", MaxAge: -1,
		HttpOnly: true, Secure: a.secure, SameSite: http.SameSiteLaxMode,
	})
	http.Redirect(w, r, "/auth/login", http.StatusFound)
}

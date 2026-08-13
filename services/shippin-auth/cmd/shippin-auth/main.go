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
	"fmt"
	"html/template"
	"log"
	"net/http"
	"os"
	"path/filepath"
	"sort"
	"strings"
	"time"

	"github.com/jackc/pgx/v5/pgxpool"
)

type app struct {
	db           *pgxpool.Pool
	cookieName   string
	ttl          time.Duration
	secure       bool
	projectsRoot string
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
		db:           pool,
		cookieName:   env("SHIPPIN_AUTH_COOKIE", "shippin_session"),
		ttl:          ttl,
		secure:       env("SHIPPIN_AUTH_SECURE_COOKIE", "1") == "1",
		projectsRoot: env("DEVFORGE_PROJECTS_ROOT", "/workspace/warden-storage/projects"),
	}

	mux := http.NewServeMux()
	mux.HandleFunc("GET /auth/check", a.check)
	mux.HandleFunc("GET /auth/login", a.loginForm)
	mux.HandleFunc("POST /auth/login", a.login)
	mux.HandleFunc("GET /auth/logout", a.logout)
	mux.HandleFunc("GET /projects", a.chooser)
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

type projectItem struct {
	Name     string
	Path     string
	Type     string // detected stack label, e.g. "Rust"
	TClass   string // css class, e.g. "t-rust"
	TypeHint string // tooltip for the stack badge
	Branch   string // current git branch (or short sha if detached)
	Rel      string // relative last-modified, e.g. "2h ago"
	ModUnix  int64  // for recency ranking
}

// detectStack returns a friendly stack label from marker files (cheap, no subprocess).
func detectStack(dir string) (label, class, hint string) {
	markers := []struct{ file, label, class, hint string }{
		{"Cargo.toml", "Rust", "t-rust", "Rust project — Cargo.toml"},
		{"go.mod", "Go", "t-go", "Go module — go.mod"},
		{"deno.json", "Deno", "t-deno", "Deno project — deno.json"},
		{"deno.jsonc", "Deno", "t-deno", "Deno project — deno.jsonc"},
		{"pyproject.toml", "Python", "t-python", "Python project — pyproject.toml"},
		{"requirements.txt", "Python", "t-python", "Python project — requirements.txt"},
		{"package.json", "Node", "t-node", "Node project — package.json"},
		{"index.html", "Web", "t-web", "Web project — index.html"},
	}
	for _, m := range markers {
		if _, err := os.Stat(filepath.Join(dir, m.file)); err == nil {
			return m.label, m.class, m.hint
		}
	}
	if _, err := os.Stat(filepath.Join(dir, ".git")); err == nil {
		return "Repo", "t-repo", "Git repository"
	}
	return "", "", ""
}

// gitBranch reads .git/HEAD directly (no subprocess).
func gitBranch(dir string) string {
	b, err := os.ReadFile(filepath.Join(dir, ".git", "HEAD"))
	if err != nil {
		return ""
	}
	s := strings.TrimSpace(string(b))
	if ref, ok := strings.CutPrefix(s, "ref: refs/heads/"); ok {
		return ref
	}
	if len(s) >= 7 {
		return s[:7] // detached HEAD → short sha
	}
	return ""
}

// newestMod approximates last activity from the dir and git metadata mtimes.
func newestMod(dir string) time.Time {
	var newest time.Time
	if st, err := os.Stat(dir); err == nil {
		newest = st.ModTime()
	}
	for _, f := range []string{".git/HEAD", ".git/index"} {
		if st, err := os.Stat(filepath.Join(dir, f)); err == nil && st.ModTime().After(newest) {
			newest = st.ModTime()
		}
	}
	return newest
}

func relTime(t, now time.Time) string {
	if t.IsZero() {
		return ""
	}
	d := now.Sub(t)
	switch {
	case d < time.Minute:
		return "just now"
	case d < time.Hour:
		return fmt.Sprintf("%dm ago", int(d.Minutes()))
	case d < 24*time.Hour:
		return fmt.Sprintf("%dh ago", int(d.Hours()))
	case d < 30*24*time.Hour:
		return fmt.Sprintf("%dd ago", int(d.Hours()/24))
	default:
		return t.Format("Jan 2")
	}
}

var chooserTmpl = template.Must(template.New("chooser").Parse(`<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Open a project — DevForge</title>
<style>
	:root { color-scheme: dark; }
	* { box-sizing: border-box; margin: 0; }
	body {
		min-height: 100vh; padding: 3.4rem 1.5rem;
		background: radial-gradient(1200px 600px at 20% -10%, #16233a 0%, #0b1220 55%, #070d18 100%);
		color: #e6edf3; font: 15px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif;
	}
	.wrap { max-width: 900px; margin: 0 auto; }
	header { display: flex; align-items: baseline; justify-content: space-between; margin-bottom: .3rem; }
	.brand { display: flex; align-items: baseline; gap: .65rem; }
	h1 { font-size: 1.4rem; letter-spacing: .3px; }
	.idle { font-size: .64rem; letter-spacing: .12em; text-transform: uppercase; color: #6b7686;
		border: 1px solid #223047; border-radius: 20px; padding: 2px 9px; cursor: help; }
	.logout { color: #7f8ea3; font-size: .8rem; text-decoration: none; }
	.logout:hover { color: #cbd5e1; }
	p.sub { color: #8b98ab; font-size: .9rem; margin-bottom: 1.7rem; }
	.slabel { font-size: .68rem; letter-spacing: .14em; text-transform: uppercase; color: #79859b; margin-bottom: .55rem; }
	.suggest { margin-bottom: 1.8rem; }
	a.continue {
		display: flex; align-items: center; justify-content: space-between; gap: 1rem; text-decoration: none;
		padding: 1rem 1.2rem; border-radius: 12px; color: #e6edf3;
		background: linear-gradient(100deg, rgba(37,99,235,.16), rgba(17,26,44,.55)); border: 1px solid #2b3f63;
		transition: border-color .12s, transform .12s;
	}
	a.continue:hover { border-color: #3b82f6; transform: translateY(-1px); }
	a.continue .cn { font-weight: 650; font-size: 1.06rem; }
	a.continue .cmeta { color: #93a1b8; font-size: .78rem; margin-top: .25rem; display: flex; gap: .7rem; flex-wrap: wrap; }
	a.continue .go { color: #93a1b8; font-size: .82rem; white-space: nowrap; }
	.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(212px, 1fr)); gap: .8rem; margin-top: .6rem; }
	a.card {
		display: flex; flex-direction: column; min-height: 98px; padding: .95rem 1rem; text-decoration: none;
		background: rgba(17, 26, 44, 0.85); border: 1px solid #223047; border-radius: 12px;
		color: #e6edf3; transition: border-color .12s, transform .12s, background .12s;
	}
	a.card:hover { border-color: #3b82f6; background: rgba(23, 35, 58, 0.95); transform: translateY(-1px); }
	.name { font-weight: 600; font-size: 1rem; }
	.badges { display: flex; gap: .4rem; flex-wrap: wrap; margin-top: .5rem; }
	.badge { font-size: .66rem; padding: 2px 7px; border-radius: 6px; border: 1px solid #2a3a54;
		color: #a9b6cb; display: inline-flex; align-items: center; gap: .32rem; cursor: help; }
	.badge .dot { width: 6px; height: 6px; border-radius: 50%; background: #6b7a90; }
	.t-rust .dot { background: #e9873b; } .t-go .dot { background: #37b0c4; } .t-node .dot { background: #57b957; }
	.t-python .dot { background: #4c8fff; } .t-deno .dot { background: #c9a227; } .t-web .dot { background: #e35d9e; }
	.t-repo .dot { background: #8a93a6; }
	.meta { margin-top: auto; padding-top: .55rem; color: #6b7a90; font-size: .72rem; }
	.empty { color: #8b98ab; }
	.foot { margin-top: 2.1rem; color: #5f6b80; font-size: .74rem; max-width: 60ch; }
</style>
</head>
<body>
<div class="wrap">
	<header>
		<div class="brand"><h1>DevForge</h1><span class="idle" title="Clyffy activates when you connect an AI subscription seat. The workbench works fully without one.">Clyffy idle</span></div>
		<a class="logout" href="/auth/logout" title="End your session">Sign out</a>
	</header>
	<p class="sub">Choose a project to open in your hosted workbench.</p>

	{{if .Recent}}
	<div class="suggest">
		<div class="slabel">Continue where you left off</div>
		<a class="continue" href="/?folder={{.Recent.Path}}" title="Open {{.Recent.Name}} — most recently changed">
			<div>
				<div class="cn">{{.Recent.Name}}</div>
				<div class="cmeta">{{if .Recent.Type}}<span>{{.Recent.Type}}</span>{{end}}{{if .Recent.Branch}}<span>⎇ {{.Recent.Branch}}</span>{{end}}{{if .Recent.Rel}}<span>{{.Recent.Rel}}</span>{{end}}</div>
			</div>
			<span class="go">Open →</span>
		</a>
	</div>
	{{end}}

	{{if .Items}}
	<div class="slabel">All projects · {{len .Items}}</div>
	<div class="grid">
		{{range .Items}}<a class="card" href="/?folder={{.Path}}" title="Open {{.Name}} in DevForge&#10;{{.Path}}">
			<div class="name">{{.Name}}</div>
			<div class="badges">{{if .Type}}<span class="badge {{.TClass}}" title="{{.TypeHint}}"><span class="dot"></span>{{.Type}}</span>{{end}}{{if .Branch}}<span class="badge" title="Current git branch">⎇ {{.Branch}}</span>{{end}}</div>
			<div class="meta">{{if .Rel}}{{.Rel}}{{end}}</div>
		</a>
		{{end}}
	</div>
	{{else}}
	<p class="empty">No projects found under the projects root.</p>
	{{end}}

	<p class="foot">Your selection opens the full workbench. Clyffy stays idle until you connect an AI subscription — everything here works without one.</p>
</div>
</body>
</html>`))

// chooser lists project folders under projectsRoot with quality-of-life signal
// (stack, git branch, recency) and a seeded "continue" suggestion, and links each
// to /?folder=<path>. Session-gated (nginx also gates /projects).
func (a *app) chooser(w http.ResponseWriter, r *http.Request) {
	if a.subjectForRequest(r) == "" {
		http.Redirect(w, r, "/auth/login?next=/projects", http.StatusFound)
		return
	}
	entries, err := os.ReadDir(a.projectsRoot)
	if err != nil {
		http.Error(w, "cannot list projects", http.StatusInternalServerError)
		return
	}
	now := time.Now()
	items := make([]projectItem, 0, len(entries))
	for _, e := range entries {
		name := e.Name()
		if strings.HasPrefix(name, ".") {
			continue
		}
		dir := filepath.Join(a.projectsRoot, name)
		isDir := e.IsDir()
		if !isDir {
			// Follow symlinks that point at directories.
			if st, serr := os.Stat(dir); serr == nil {
				isDir = st.IsDir()
			}
		}
		if !isDir {
			continue
		}
		label, class, hint := detectStack(dir)
		mod := newestMod(dir)
		items = append(items, projectItem{
			Name: name, Path: dir,
			Type: label, TClass: class, TypeHint: hint,
			Branch:  gitBranch(dir),
			Rel:     relTime(mod, now),
			ModUnix: mod.Unix(),
		})
	}
	// Seeded suggestion: the most recently touched project (copy before sorting,
	// so the pointer survives the reorder below).
	var recent *projectItem
	for i := range items {
		if recent == nil || items[i].ModUnix > recent.ModUnix {
			rc := items[i]
			recent = &rc
		}
	}
	sort.Slice(items, func(i, j int) bool {
		return strings.ToLower(items[i].Name) < strings.ToLower(items[j].Name)
	})
	w.Header().Set("Content-Type", "text/html; charset=utf-8")
	_ = chooserTmpl.Execute(w, map[string]any{"Items": items, "Recent": recent})
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

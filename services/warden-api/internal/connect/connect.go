// Package connect is the devstation click-to-auth surface ("Connect & Launch").
// It reports per-tool authentication state and activates auth from a single
// operator-supplied credential, writing secrets ONLY to root-only paths or the
// git credential store — never to logs, chat, or the repo.
//
// This is the supply point for the one live credential the devstation needs:
// a working Infisical machine-identity client secret (or PAT), from which the
// secret broker materializes everything else (GitHub PAT, DB creds) to
// /run/warden-secrets.
package connect

import (
	"bytes"
	"context"
	"fmt"
	"os"
	"os/exec"
	"path/filepath"
	"strings"
	"time"
)

// Store activates and inspects devstation auth. repoRoot locates the turnkey
// installer; home locates per-user credential files.
type Store struct {
	repoRoot string
	home     string
}

func NewStore(repoRoot string) *Store {
	home, _ := os.UserHomeDir()
	return &Store{repoRoot: repoRoot, home: home}
}

// ToolStatus is one tool's auth state for the Connect panel.
type ToolStatus struct {
	Tool      string `json:"tool"`
	Connected bool   `json:"connected"`
	Detail    string `json:"detail"`
}

const secretsDir = "/run/warden-secrets"

// Status reports auth state across the tools the devstation brokers.
func (s *Store) Status(ctx context.Context) []ToolStatus {
	out := []ToolStatus{}

	// Infisical secret broker: service active + secrets materialized.
	brokerActive := systemctlActive(ctx, "infisical-agent.service")
	keys := countEnvKeys(filepath.Join(secretsDir, "warden.env"))
	out = append(out, ToolStatus{
		Tool:      "infisical",
		Connected: brokerActive && keys > 0,
		Detail:    fmt.Sprintf("broker=%v, secrets=%d", brokerActive, keys),
	})

	// GitHub: a usable credential (git credential store or gh login).
	ghDetail, ghOK := githubState(s.home)
	out = append(out, ToolStatus{Tool: "github", Connected: ghOK, Detail: ghDetail})

	// Claude Code / Codex / Gemini: presence of their credential files.
	out = append(out, fileStatus("claude-code", filepath.Join(s.home, ".claude", ".credentials.json")))
	out = append(out, fileStatus("codex", filepath.Join(s.home, ".codex", "auth.json")))
	out = append(out, fileStatus("gemini", filepath.Join(s.home, ".gemini", "oauth_creds.json")))
	return out
}

// ActivateInfisical writes the machine identity (root-only) and runs the
// turnkey installer so the broker authenticates. Returns the installer/status
// output. The client secret is passed via stdin, never via argv or logs.
func (s *Store) ActivateInfisical(ctx context.Context, in InfisicalInput) (string, error) {
	env, err := s.composeMIEnv(in)
	if err != nil {
		return "", err
	}
	// Write /etc/warden/infisical-mi.env (root-only) from stdin.
	if out, err := runStdin(ctx, env, "sudo", "install", "-m", "0600", "/dev/stdin", "/etc/warden/infisical-mi.env"); err != nil {
		return out, fmt.Errorf("write machine identity: %w", err)
	}
	installer := filepath.Join(s.repoRoot, "modules", "warden", "infrastructure",
		"devstation", "turnkey", "bin", "install-devstation-turnkey.sh")
	out, err := run(ctx, s.repoRoot, "sudo", installer)
	return out, err
}

// ActivateGitHub configures the git credential store with a PAT and verifies it
// against origin. The token is written only to ~/.git-credentials (0600).
func (s *Store) ActivateGitHub(ctx context.Context, token string) (string, error) {
	token = strings.TrimSpace(token)
	if token == "" {
		return "", fmt.Errorf("empty token")
	}
	credPath := filepath.Join(s.home, ".git-credentials")
	line := fmt.Sprintf("https://x-access-token:%s@github.com\n", token)
	if err := os.WriteFile(credPath, []byte(line), 0o600); err != nil {
		return "", fmt.Errorf("write git credentials: %w", err)
	}
	if _, err := run(ctx, s.repoRoot, "git", "config", "--global", "credential.helper", "store"); err != nil {
		return "", err
	}
	// Verify without exposing the token: ls-remote uses the stored credential.
	out, err := run(ctx, s.repoRoot, "git", "ls-remote", "--heads", "origin")
	if err != nil {
		return "github credential written, but verification failed: " + out, err
	}
	return "github connected; origin reachable", nil
}

// InfisicalInput is the activate payload. ClientSecret is required; the rest
// fall back to the existing ~/.infisical-mi.env values.
type InfisicalInput struct {
	ClientSecret string `json:"client_secret"`
	ClientID     string `json:"client_id"`
	ProjectID    string `json:"project_id"`
	Env          string `json:"env"`
	APIURL       string `json:"api_url"`
}

// composeMIEnv merges the supplied values over the existing MI env so the
// operator can paste only the rotated client secret.
func (s *Store) composeMIEnv(in InfisicalInput) (string, error) {
	if strings.TrimSpace(in.ClientSecret) == "" {
		return "", fmt.Errorf("client_secret is required")
	}
	cur := readEnvFile(filepath.Join(s.home, ".infisical-mi.env"))
	pick := func(v, key string) string {
		if strings.TrimSpace(v) != "" {
			return v
		}
		return cur[key]
	}
	var b strings.Builder
	b.WriteString("# Warden devstation machine identity (written by Connect & Launch).\n")
	fmt.Fprintf(&b, "INFISICAL_CLIENT_ID=%s\n", pick(in.ClientID, "INFISICAL_CLIENT_ID"))
	fmt.Fprintf(&b, "INFISICAL_CLIENT_SECRET=%s\n", in.ClientSecret)
	fmt.Fprintf(&b, "INFISICAL_PROJECT_ID=%s\n", pick(in.ProjectID, "INFISICAL_PROJECT_ID"))
	fmt.Fprintf(&b, "INFISICAL_ENV=%s\n", pick(in.Env, "INFISICAL_ENV"))
	if u := pick(in.APIURL, "INFISICAL_API_URL"); u != "" {
		fmt.Fprintf(&b, "INFISICAL_API_URL=%s\n", u)
	}
	return b.String(), nil
}

// --- helpers (no secret values are ever returned in errors) ---

func run(ctx context.Context, dir string, name string, args ...string) (string, error) {
	c, cancel := context.WithTimeout(ctx, 90*time.Second)
	defer cancel()
	cmd := exec.CommandContext(c, name, args...)
	cmd.Dir = dir
	out, err := cmd.CombinedOutput()
	return strings.TrimSpace(string(out)), err
}

func runStdin(ctx context.Context, stdin string, name string, args ...string) (string, error) {
	c, cancel := context.WithTimeout(ctx, 30*time.Second)
	defer cancel()
	cmd := exec.CommandContext(c, name, args...)
	cmd.Stdin = bytes.NewBufferString(stdin)
	out, err := cmd.CombinedOutput()
	return strings.TrimSpace(string(out)), err
}

func systemctlActive(ctx context.Context, unit string) bool {
	out, _ := run(ctx, "", "systemctl", "is-active", unit)
	return out == "active"
}

func countEnvKeys(path string) int {
	b, err := os.ReadFile(path)
	if err != nil {
		return 0
	}
	n := 0
	for _, ln := range strings.Split(string(b), "\n") {
		ln = strings.TrimSpace(ln)
		if ln != "" && !strings.HasPrefix(ln, "#") && strings.Contains(ln, "=") {
			n++
		}
	}
	return n
}

func fileStatus(tool, path string) ToolStatus {
	st, err := os.Stat(path)
	if err == nil && st.Size() > 0 {
		return ToolStatus{Tool: tool, Connected: true, Detail: "credential present"}
	}
	return ToolStatus{Tool: tool, Connected: false, Detail: "no credential"}
}

func githubState(home string) (string, bool) {
	if st, err := os.Stat(filepath.Join(home, ".git-credentials")); err == nil && st.Size() > 0 {
		return "git credential store", true
	}
	if _, err := os.Stat(filepath.Join(home, ".config", "gh", "hosts.yml")); err == nil {
		return "gh login", true
	}
	return "not authenticated", false
}

func readEnvFile(path string) map[string]string {
	m := map[string]string{}
	b, err := os.ReadFile(path)
	if err != nil {
		return m
	}
	for _, ln := range strings.Split(string(b), "\n") {
		ln = strings.TrimSpace(ln)
		if ln == "" || strings.HasPrefix(ln, "#") {
			continue
		}
		if k, v, ok := strings.Cut(ln, "="); ok {
			m[strings.TrimSpace(k)] = strings.TrimSpace(v)
		}
	}
	return m
}

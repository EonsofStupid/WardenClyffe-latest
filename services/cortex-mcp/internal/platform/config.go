package platform

import (
	"errors"
	"os"
	"path/filepath"
)

// ErrNoRepoRoot means the WardenClyffe repo root could not be located.
var ErrNoRepoRoot = errors.New("repo root not found (set CORTEX_REPO_ROOT)")

// defaultWorkspace is the devstation's canonical repo checkout.
const defaultWorkspace = "/workspace/WardenClyffe-latest"

// Config holds runtime configuration for cortex-mcp. No secrets live here;
// the server only reads authored repo files and invokes the foundation
// scripts (which broker their own access).
type Config struct {
	RepoRoot     string // WardenClyffe repo root
	RegistryPath string // context-mesh.yaml
	DecisionsDir string // parking-lot decision touchpoints
	PythonBin    string // interpreter for the foundation scripts
}

// LoadConfig resolves the repo root from CORTEX_REPO_ROOT or by walking up
// from the working directory until AGENTS.md is found.
func LoadConfig() (Config, error) {
	root := os.Getenv("CORTEX_REPO_ROOT")
	if root == "" {
		dir, err := os.Getwd()
		if err != nil {
			return Config{}, err
		}
		root = findRoot(dir)
	}
	if root == "" {
		// ssh launches land in $HOME; fall back to the devstation's canonical
		// workspace before failing.
		if _, err := os.Stat(filepath.Join(defaultWorkspace, "AGENTS.md")); err == nil {
			root = defaultWorkspace
		} else {
			return Config{}, ErrNoRepoRoot
		}
	}
	python := os.Getenv("CORTEX_PYTHON_BIN")
	if python == "" {
		python = "python3"
	}
	return Config{
		RepoRoot:     root,
		RegistryPath: filepath.Join(root, "wardenclyffe", "registry", "context-mesh.yaml"),
		DecisionsDir: filepath.Join(root, "docs", "ai", "parking-lot", "decisions"),
		PythonBin:    python,
	}, nil
}

func findRoot(dir string) string {
	for {
		if _, err := os.Stat(filepath.Join(dir, "AGENTS.md")); err == nil {
			return dir
		}
		parent := filepath.Dir(dir)
		if parent == dir {
			return ""
		}
		dir = parent
	}
}

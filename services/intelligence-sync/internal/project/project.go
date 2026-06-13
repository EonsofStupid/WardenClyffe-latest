// Package project turns the touchpoint validator inventory into the
// projection plan: SurrealDB rows + Qdrant points, content-hash idempotent.
// Dry-run first per SURREALDB_INTELLIGENCE_PROJECTION_V2 promotion order —
// live upserts (surrealdb.go v1.4.0 / qdrant go-client v1.18.2) activate when
// the brokered credentials land.
package project

import (
	"crypto/sha256"
	"encoding/hex"
	"encoding/json"
	"os"
	"os/exec"
	"path/filepath"
)

// Source is one validator inventory item (the fields we project).
type Source struct {
	Path        string   `json:"path"`
	Version     int      `json:"version"`
	WorkspaceID string   `json:"workspace_id"`
	ProjectKey  string   `json:"project_key"`
	Kind        string   `json:"kind"`
	Owner       string   `json:"owner"`
	Module      string   `json:"module"`
	SyncQdrant  bool     `json:"sync_qdrant"`
	SyncSurreal bool     `json:"sync_surreal"`
	BodyWords   int      `json:"body_words"`
	Warnings    []string `json:"warnings"`
}

// Projection is one planned touchpoint_projection row + Qdrant point.
type Projection struct {
	ProjectKey  string `json:"project_key"`
	WorkspaceID string `json:"workspace_id"`
	Path        string `json:"path"`
	Kind        string `json:"kind"`
	Owner       string `json:"owner"`
	Module      string `json:"module"`
	ContentHash string `json:"content_hash"`
	BodyWords   int    `json:"body_words"`
	ToSurreal   bool   `json:"to_surreal"`
	ToQdrant    bool   `json:"to_qdrant"`
	Status      string `json:"status"` // new | changed | unchanged
}

// Plan is one projection run's output.
type Plan struct {
	Summary struct {
		Total, New, Changed, Unchanged int `json:",omitempty"`
	} `json:"summary"`
	Projections []Projection `json:"projections"`
}

// Inventory runs the validator and decodes sync-enabled sources.
func Inventory(repoRoot string) ([]Source, error) {
	cmd := exec.Command("python3",
		filepath.Join(repoRoot, "scripts", "foundation", "validate-touchpoints.py"), "--json")
	cmd.Dir = repoRoot
	raw, err := cmd.Output()
	if err != nil {
		return nil, err
	}
	var all []Source
	if err := json.Unmarshal(raw, &all); err != nil {
		return nil, err
	}
	out := []Source{}
	for _, s := range all {
		if s.SyncQdrant || s.SyncSurreal {
			out = append(out, s)
		}
	}
	return out, nil
}

// Build hashes each source file and diffs against the previous plan.
func Build(repoRoot string, sources []Source, prev *Plan) (*Plan, error) {
	prevHash := map[string]string{}
	if prev != nil {
		for _, p := range prev.Projections {
			prevHash[p.Path] = p.ContentHash
		}
	}
	plan := &Plan{Projections: []Projection{}}
	for _, s := range sources {
		b, err := os.ReadFile(filepath.Join(repoRoot, s.Path))
		if err != nil {
			return nil, err
		}
		sum := sha256.Sum256(b)
		h := hex.EncodeToString(sum[:])
		status := "new"
		if old, ok := prevHash[s.Path]; ok {
			if old == h {
				status = "unchanged"
			} else {
				status = "changed"
			}
		}
		plan.Projections = append(plan.Projections, Projection{
			ProjectKey: s.ProjectKey, WorkspaceID: s.WorkspaceID, Path: s.Path,
			Kind: s.Kind, Owner: s.Owner, Module: s.Module,
			ContentHash: h, BodyWords: s.BodyWords,
			ToSurreal: s.SyncSurreal, ToQdrant: s.SyncQdrant, Status: status,
		})
		switch status {
		case "new":
			plan.Summary.New++
		case "changed":
			plan.Summary.Changed++
		default:
			plan.Summary.Unchanged++
		}
	}
	plan.Summary.Total = len(plan.Projections)
	return plan, nil
}

// LoadPlan reads a previous plan; missing file returns nil (first run).
func LoadPlan(path string) (*Plan, error) {
	b, err := os.ReadFile(path)
	if os.IsNotExist(err) {
		return nil, nil
	}
	if err != nil {
		return nil, err
	}
	var p Plan
	if err := json.Unmarshal(b, &p); err != nil {
		return nil, err
	}
	return &p, nil
}

// WritePlan persists the plan atomically.
func WritePlan(path string, p *Plan) error {
	if err := os.MkdirAll(filepath.Dir(path), 0o755); err != nil {
		return err
	}
	b, err := json.MarshalIndent(p, "", "  ")
	if err != nil {
		return err
	}
	tmp := path + ".tmp"
	if err := os.WriteFile(tmp, b, 0o644); err != nil {
		return err
	}
	return os.Rename(tmp, path)
}

// Package instances serves the overview list: every deployed Vaultix this
// panel knows about, metadata only. Registry is a JSON file the operator
// edits; health is probed live against each instance's /api/status.
package instances

import (
	"context"
	"encoding/json"
	"net/http"
	"os"
	"sync"
	"time"

	"shippin.cloud/vaultix/panel/internal/core"
)

type Instance struct {
	ID   string `json:"id"`
	Name string `json:"name"`
	Host string `json:"host"`
	URL  string `json:"url"`
	// LastBackup is operator-maintained until the backup job reports in.
	LastBackup string `json:"lastBackup,omitempty"`
}

// View is what the panel renders. No secret names, no secret values.
type View struct {
	Instance
	Health string `json:"health"` // ok | unreachable
}

type Registry struct {
	mu    sync.RWMutex
	items []Instance
	probe *http.Client
}

func Load(path string) (*Registry, error) {
	raw, err := os.ReadFile(path)
	if err != nil {
		return nil, err
	}
	var items []Instance
	if err := json.Unmarshal(raw, &items); err != nil {
		return nil, err
	}
	return &Registry{items: items, probe: &http.Client{Timeout: 3 * time.Second}}, nil
}

func (r *Registry) Get(id string) (Instance, bool) {
	r.mu.RLock()
	defer r.mu.RUnlock()
	for _, it := range r.items {
		if it.ID == id {
			return it, true
		}
	}
	return Instance{}, false
}

// Primary is the first registry entry — the instance link/import target.
func (r *Registry) Primary() (Instance, bool) {
	r.mu.RLock()
	defer r.mu.RUnlock()
	if len(r.items) == 0 {
		return Instance{}, false
	}
	return r.items[0], true
}

// List probes all instances concurrently and returns views.
func (r *Registry) List(ctx context.Context) []View {
	r.mu.RLock()
	items := make([]Instance, len(r.items))
	copy(items, r.items)
	r.mu.RUnlock()

	views := make([]View, len(items))
	var wg sync.WaitGroup
	for i, it := range items {
		wg.Add(1)
		go func(i int, it Instance) {
			defer wg.Done()
			views[i] = View{Instance: it, Health: r.health(ctx, it.URL)}
		}(i, it)
	}
	wg.Wait()
	return views
}

func (r *Registry) health(ctx context.Context, base string) string {
	req, err := http.NewRequestWithContext(ctx, http.MethodGet, base+core.StatusPath, nil)
	if err != nil {
		return "unreachable"
	}
	resp, err := r.probe.Do(req)
	if err != nil {
		return "unreachable"
	}
	defer resp.Body.Close()
	if resp.StatusCode == http.StatusOK {
		return "ok"
	}
	return "unreachable"
}

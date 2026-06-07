package volume

import (
	"context"
	"errors"
	"sync"

	"github.com/wardenclyffe/storage-broker-client/internal/contract"
)

// ErrNotFound is returned when a volume id is unknown.
var ErrNotFound = errors.New("volume not found")

// Store persists volume control-plane truth. The memory store ships now; a
// Postgres store (warden_core/clyffe_core) drops in behind the same interface.
type Store interface {
	Put(ctx context.Context, v *contract.Volume) error
	Get(ctx context.Context, id string) (*contract.Volume, error)
	List(ctx context.Context, tenantID string) ([]*contract.Volume, error)
	Delete(ctx context.Context, id string) error
}

type MemoryStore struct {
	mu sync.RWMutex
	m  map[string]*contract.Volume
}

func NewMemoryStore() *MemoryStore { return &MemoryStore{m: map[string]*contract.Volume{}} }

func (s *MemoryStore) Put(_ context.Context, v *contract.Volume) error {
	s.mu.Lock()
	defer s.mu.Unlock()
	cp := *v
	s.m[v.ID] = &cp
	return nil
}

func (s *MemoryStore) Get(_ context.Context, id string) (*contract.Volume, error) {
	s.mu.RLock()
	defer s.mu.RUnlock()
	v, ok := s.m[id]
	if !ok {
		return nil, ErrNotFound
	}
	cp := *v
	return &cp, nil
}

func (s *MemoryStore) List(_ context.Context, tenantID string) ([]*contract.Volume, error) {
	s.mu.RLock()
	defer s.mu.RUnlock()
	out := []*contract.Volume{}
	for _, v := range s.m {
		if tenantID == "" || v.TenantID == tenantID {
			cp := *v
			out = append(out, &cp)
		}
	}
	return out, nil
}

func (s *MemoryStore) Delete(_ context.Context, id string) error {
	s.mu.Lock()
	defer s.mu.Unlock()
	if _, ok := s.m[id]; !ok {
		return ErrNotFound
	}
	delete(s.m, id)
	return nil
}

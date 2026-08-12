// Package migrate is the owned, tiny migrations runner (buildplan P0-2).
// It applies the numbered SQL files in data/schema/sql against the
// shippin_core.schema_migrations ledger (created by 0001 and self-registered
// by every migration; the runner also records as a safety net).
package migrate

import (
	"context"
	"fmt"
	"os"
	"path/filepath"
	"sort"
	"strings"

	"github.com/jackc/pgx/v5/pgxpool"
)

// Migration is one numbered SQL file.
type Migration struct {
	Version string // filename without .sql, e.g. 0001_init
	Path    string
}

// ListDir returns the migrations in a directory, ordered by version.
func ListDir(dir string) ([]Migration, error) {
	entries, err := os.ReadDir(dir)
	if err != nil {
		return nil, err
	}
	out := []Migration{}
	for _, e := range entries {
		if e.IsDir() || !strings.HasSuffix(e.Name(), ".sql") {
			continue
		}
		out = append(out, Migration{
			Version: strings.TrimSuffix(e.Name(), ".sql"),
			Path:    filepath.Join(dir, e.Name()),
		})
	}
	sort.Slice(out, func(i, j int) bool { return out[i].Version < out[j].Version })
	return out, nil
}

// Applied returns the versions recorded in the ledger. A missing ledger table
// (fresh database) returns an empty set.
func Applied(ctx context.Context, db *pgxpool.Pool) (map[string]bool, error) {
	var exists bool
	err := db.QueryRow(ctx, `SELECT EXISTS (
	    SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace
	    WHERE n.nspname='shippin_core' AND c.relname='schema_migrations')`).Scan(&exists)
	if err != nil {
		return nil, err
	}
	applied := map[string]bool{}
	if !exists {
		return applied, nil
	}
	rows, err := db.Query(ctx, `SELECT version FROM shippin_core.schema_migrations`)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	for rows.Next() {
		var v string
		if err := rows.Scan(&v); err != nil {
			return nil, err
		}
		applied[v] = true
	}
	return applied, rows.Err()
}

// Up applies every pending migration in order. Each file manages its own
// transaction (BEGIN/COMMIT inside, per repo convention); after a file runs,
// the version is recorded ON CONFLICT DO NOTHING as a safety net.
func Up(ctx context.Context, db *pgxpool.Pool, dir string) ([]string, error) {
	migrations, err := ListDir(dir)
	if err != nil {
		return nil, err
	}
	applied, err := Applied(ctx, db)
	if err != nil {
		return nil, err
	}
	ran := []string{}
	for _, m := range migrations {
		if applied[m.Version] {
			continue
		}
		sql, err := os.ReadFile(m.Path)
		if err != nil {
			return ran, err
		}
		if _, err := db.Exec(ctx, string(sql)); err != nil {
			return ran, fmt.Errorf("apply %s: %w", m.Version, err)
		}
		if _, err := db.Exec(ctx,
			`INSERT INTO shippin_core.schema_migrations(version) VALUES ($1)
			 ON CONFLICT (version) DO NOTHING`, m.Version); err != nil {
			return ran, fmt.Errorf("record %s: %w", m.Version, err)
		}
		ran = append(ran, m.Version)
	}
	return ran, nil
}

// Status returns (applied, pending) version lists for the directory.
func Status(ctx context.Context, db *pgxpool.Pool, dir string) (done, pending []string, err error) {
	migrations, err := ListDir(dir)
	if err != nil {
		return nil, nil, err
	}
	applied, err := Applied(ctx, db)
	if err != nil {
		return nil, nil, err
	}
	for _, m := range migrations {
		if applied[m.Version] {
			done = append(done, m.Version)
		} else {
			pending = append(pending, m.Version)
		}
	}
	return done, pending, nil
}

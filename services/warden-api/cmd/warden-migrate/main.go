// Command warden-migrate applies the canonical schema (data/schema/sql) to
// the configured Postgres. Owned, tiny, deterministic — no AI, no framework.
//
//	warden-migrate status   show applied vs pending
//	warden-migrate up       apply pending migrations in order
package main

import (
	"context"
	"fmt"
	"log"
	"os"
	"path/filepath"

	"github.com/wardenclyffe/warden-api/internal/migrate"
	"github.com/wardenclyffe/warden-api/internal/platform"
)

func main() {
	cmd := "status"
	if len(os.Args) > 1 {
		cmd = os.Args[1]
	}

	cfg := platform.LoadConfig()
	dir := os.Getenv("WARDEN_MIGRATIONS_DIR")
	if dir == "" {
		dir = filepath.Join("data", "schema", "sql")
	}

	ctx := context.Background()
	pool, err := platform.NewPool(ctx, cfg.DBURL)
	if err != nil {
		log.Fatalf("warden-migrate: database: %v", err)
	}
	defer pool.Close()

	switch cmd {
	case "status":
		done, pending, err := migrate.Status(ctx, pool, dir)
		if err != nil {
			log.Fatalf("warden-migrate: %v", err)
		}
		fmt.Printf("applied (%d): %v\npending (%d): %v\n", len(done), done, len(pending), pending)
	case "up":
		ran, err := migrate.Up(ctx, pool, dir)
		if err != nil {
			log.Fatalf("warden-migrate: %v (ran: %v)", err, ran)
		}
		if len(ran) == 0 {
			fmt.Println("up to date")
		} else {
			fmt.Printf("applied: %v\n", ran)
		}
	default:
		log.Fatalf("warden-migrate: unknown command %q (status|up)", cmd)
	}
}

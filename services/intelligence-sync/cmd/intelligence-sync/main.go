// Command intelligence-sync projects sync-enabled touchpoints into the
// intelligence plane. v1 = dry-run (JSON plan on W) per the projection spec's
// promotion order; live Surreal/Qdrant upserts activate with brokered creds.
package main

import (
	"fmt"
	"log"
	"os"

	"github.com/wardenclyffe/intelligence-sync/internal/project"
)

func main() {
	repoRoot := os.Getenv("CORTEX_REPO_ROOT")
	if repoRoot == "" {
		repoRoot = "/workspace/WardenClyffe-latest"
	}
	planPath := os.Getenv("SYNC_PLAN_PATH")
	if planPath == "" {
		planPath = "/workspace/warden-storage/registry/projection-plan.json"
	}

	sources, err := project.Inventory(repoRoot)
	if err != nil {
		log.Fatalf("intelligence-sync: inventory: %v", err)
	}
	prev, err := project.LoadPlan(planPath)
	if err != nil {
		log.Fatalf("intelligence-sync: load previous plan: %v", err)
	}
	plan, err := project.Build(repoRoot, sources, prev)
	if err != nil {
		log.Fatalf("intelligence-sync: build: %v", err)
	}
	if err := project.WritePlan(planPath, plan); err != nil {
		log.Fatalf("intelligence-sync: write: %v", err)
	}
	fmt.Printf("plan: total=%d new=%d changed=%d unchanged=%d -> %s\n",
		plan.Summary.Total, plan.Summary.New, plan.Summary.Changed,
		plan.Summary.Unchanged, planPath)
}

// Command storage-broker is the Go control plane for managed per-tenant
// storage. It turns purchases into wardenclyffedisk volumes via deterministic
// scripts and exposes a small HTTP control API that warden-api/clyffe-api call.
// It holds NO data-plane authority — bytes live in the Rust disk (S3/FUSE).
package main

import (
	"context"
	"errors"
	"log"
	"net/http"
	"os/signal"
	"syscall"
	"time"

	"github.com/go-chi/chi/v5"
	"github.com/go-chi/chi/v5/middleware"

	"github.com/wardenclyffe/storage-broker-client/internal/disk"
	"github.com/wardenclyffe/storage-broker-client/internal/platform"
	"github.com/wardenclyffe/storage-broker-client/internal/volume"
)

func main() {
	cfg := platform.LoadConfig()
	svc := volume.NewService(disk.NewCLIDriver(cfg.BinDir), volume.NewMemoryStore())

	ctx, stop := signal.NotifyContext(context.Background(), syscall.SIGINT, syscall.SIGTERM)
	defer stop()

	r := chi.NewRouter()
	r.Use(middleware.RequestID, middleware.RealIP, middleware.Logger, middleware.Recoverer)

	r.Get("/healthz", func(w http.ResponseWriter, _ *http.Request) {
		platform.JSON(w, http.StatusOK, map[string]any{"status": "ok", "service": "storage-broker"})
	})

	r.Route("/api/storage/volumes", func(r chi.Router) {
		r.Post("/", func(w http.ResponseWriter, r *http.Request) {
			var in volume.PurchaseInput
			if err := platform.DecodeJSON(r, &in); err != nil {
				platform.Error(w, http.StatusBadRequest, err.Error())
				return
			}
			v, err := svc.Provision(r.Context(), in)
			if err != nil {
				platform.Error(w, http.StatusBadGateway, err.Error())
				return
			}
			platform.JSON(w, http.StatusCreated, v)
		})
		r.Get("/", func(w http.ResponseWriter, r *http.Request) {
			items, err := svc.List(r.Context(), r.URL.Query().Get("tenant_id"))
			if err != nil {
				platform.Error(w, http.StatusInternalServerError, err.Error())
				return
			}
			platform.JSON(w, http.StatusOK, map[string]any{"items": items, "count": len(items)})
		})
		r.Get("/{id}", func(w http.ResponseWriter, r *http.Request) {
			v, err := svc.Get(r.Context(), chi.URLParam(r, "id"))
			if err != nil {
				platform.Error(w, http.StatusNotFound, err.Error())
				return
			}
			platform.JSON(w, http.StatusOK, v)
		})
		r.Delete("/{id}", func(w http.ResponseWriter, r *http.Request) {
			if err := svc.Deprovision(r.Context(), chi.URLParam(r, "id")); err != nil {
				platform.Error(w, http.StatusBadGateway, err.Error())
				return
			}
			platform.JSON(w, http.StatusNoContent, nil)
		})
		r.Post("/{id}/mount-grant", func(w http.ResponseWriter, r *http.Request) {
			g, err := svc.GrantMount(r.Context(), chi.URLParam(r, "id"), r.URL.Query().Get("protocol"))
			if err != nil {
				platform.Error(w, http.StatusBadGateway, err.Error())
				return
			}
			platform.JSON(w, http.StatusOK, g)
		})
	})

	srv := &http.Server{Addr: cfg.Addr, Handler: r, ReadHeaderTimeout: 10 * time.Second}
	go func() {
		log.Printf("storage-broker listening on %s (bin=%s)", cfg.Addr, cfg.BinDir)
		if err := srv.ListenAndServe(); err != nil && !errors.Is(err, http.ErrServerClosed) {
			log.Fatalf("server: %v", err)
		}
	}()
	<-ctx.Done()
	shutCtx, cancel := context.WithTimeout(context.Background(), 10*time.Second)
	defer cancel()
	_ = srv.Shutdown(shutCtx)
}

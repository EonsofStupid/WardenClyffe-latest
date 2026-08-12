// Package platform holds config + HTTP helpers for storage-broker, mirroring
// the shippin-api conventions so the control plane stays consistent.
package platform

import (
	"encoding/json"
	"log"
	"net/http"
	"os"
)

type Config struct {
	Addr   string // listen address, e.g. ":8083"
	BinDir string // directory holding the deterministic volume scripts
	S3Base string // base S3 endpoint advertised to customers
}

func LoadConfig() Config {
	return Config{
		Addr:   env("STORAGE_BROKER_ADDR", ":8083"),
		BinDir: env("STORAGE_BROKER_BIN_DIR", "/usr/local/bin"),
		S3Base: env("STORAGE_BROKER_S3_BASE", "http://127.0.0.1:9000"),
	}
}

func env(k, def string) string {
	if v := os.Getenv(k); v != "" {
		return v
	}
	return def
}

func JSON(w http.ResponseWriter, status int, v any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	if v == nil {
		return
	}
	if err := json.NewEncoder(w).Encode(v); err != nil {
		log.Printf("json encode error: %v", err)
	}
}

func Error(w http.ResponseWriter, status int, msg string) {
	JSON(w, status, map[string]any{"error": map[string]any{"status": status, "message": msg}})
}

func DecodeJSON(r *http.Request, v any) error {
	dec := json.NewDecoder(r.Body)
	dec.DisallowUnknownFields()
	return dec.Decode(v)
}

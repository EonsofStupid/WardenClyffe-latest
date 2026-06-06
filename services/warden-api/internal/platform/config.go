package platform

import (
	"os"
)

// Config holds runtime configuration for warden-api. Secrets come from the
// environment (populated from Infisical / /run/warden-secrets in production).
type Config struct {
	Addr     string // listen address, e.g. ":8081"
	DBURL    string // postgres connection string
	LogLevel string
}

// LoadConfig reads configuration from the environment with sane local defaults
// matching the devstation Postgres provisioned for development.
func LoadConfig() Config {
	return Config{
		Addr:     env("WARDEN_API_ADDR", ":8081"),
		DBURL:    env("WARDEN_DB_URL", "postgres://warden:warden_dev_local@127.0.0.1:5432/wardenclyffe?sslmode=disable"),
		LogLevel: env("WARDEN_LOG_LEVEL", "info"),
	}
}

func env(key, def string) string {
	if v := os.Getenv(key); v != "" {
		return v
	}
	return def
}

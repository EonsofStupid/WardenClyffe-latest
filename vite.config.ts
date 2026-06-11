import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";
import { tanstackStart } from "@tanstack/react-start/plugin/vite";

// VERSION-SENSITIVE (2026): TanStack Start's vite plugin generates the route
// tree from src/routes/ and wires the client/server entries. Pin the
// @tanstack/* versions on install and confirm the plugin import path
// ("@tanstack/react-start/plugin/vite") matches the installed release.
export default defineConfig({
  plugins: [tanstackStart(), react()],
  server: {
    host: "127.0.0.1",
    port: 5173,
    proxy: {
      "/api": { target: "http://127.0.0.1:8081", changeOrigin: true },
      "/healthz": { target: "http://127.0.0.1:8081", changeOrigin: true },
    },
  },
});

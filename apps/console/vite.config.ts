import { defineConfig } from "vite";
import react from "@vitejs/plugin-react";

// The console talks to warden-api. In dev we proxy /api -> warden-api so the
// browser stays same-origin.
export default defineConfig({
  plugins: [react()],
  server: {
    host: "127.0.0.1",
    port: 5173,
    proxy: {
      "/api": { target: "http://127.0.0.1:8081", changeOrigin: true },
      "/healthz": { target: "http://127.0.0.1:8081", changeOrigin: true },
    },
  },
});

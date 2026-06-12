// TanStack Start root route — providers + shell + outlet.
// VERSION-SENSITIVE: createRootRoute / Outlet are the TanStack Router API as of
// late 2025/early 2026. Pin @tanstack/react-router + @tanstack/react-start to
// the current release on install; verify the import surface hasn't moved.
import { createRootRoute, Outlet } from "@tanstack/react-router";
import { AuthProvider } from "../domains/warden/identity";
import "../lib/design/index.css";

export const Route = createRootRoute({
  component: () => (
    <AuthProvider>
      <Outlet />
    </AuthProvider>
  ),
});

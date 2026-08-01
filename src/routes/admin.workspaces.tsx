import { createFileRoute } from "@tanstack/react-router";
import { RequireOperator } from "../domains/warden/identity";
import { WorkspacesView } from "../domains/warden/fleet";
import { AdminShell } from "../domains/admin/views/AdminShell";

export const Route = createFileRoute("/admin/workspaces")({
  component: () => (
    <RequireOperator>
      <AdminShell>
        <WorkspacesView />
      </AdminShell>
    </RequireOperator>
  ),
});

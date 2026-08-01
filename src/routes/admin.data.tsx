import { createFileRoute } from "@tanstack/react-router";
import { RequireOperator } from "../domains/warden/identity";
import { DataBrowserView } from "../domains/warden/data";
import { AdminShell } from "../domains/admin/views/AdminShell";

export const Route = createFileRoute("/admin/data")({
  component: () => (
    <RequireOperator>
      <AdminShell>
        <DataBrowserView />
      </AdminShell>
    </RequireOperator>
  ),
});

import { createFileRoute } from "@tanstack/react-router";
import { RequireOperator } from "../domains/warden/identity";
import { OrderDevstationView } from "../domains/warden/fleet";
import { AdminShell } from "../domains/admin/views/AdminShell";

export const Route = createFileRoute("/admin/order")({
  component: () => (
    <RequireOperator>
      <AdminShell>
        <OrderDevstationView />
      </AdminShell>
    </RequireOperator>
  ),
});

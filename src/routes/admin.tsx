import { createFileRoute } from "@tanstack/react-router";
import { RequireOperator } from "../domains/warden/identity";
import { AdminView } from "../domains/admin";

export const Route = createFileRoute("/admin")({
  component: () => (
    <RequireOperator>
      <AdminView />
    </RequireOperator>
  ),
});

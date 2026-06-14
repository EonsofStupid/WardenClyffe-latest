import { createFileRoute } from "@tanstack/react-router";
import { RequireOperator } from "../domains/warden/identity";
import { EdgeView } from "../domains/admin/edge";

export const Route = createFileRoute("/admin/edge")({
  component: () => (
    <RequireOperator>
      <EdgeView />
    </RequireOperator>
  ),
});

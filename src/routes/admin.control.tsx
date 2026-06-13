import { createFileRoute } from "@tanstack/react-router";
import { RequireOperator } from "../domains/warden/identity";
import { ControlLayerView } from "../domains/admin/control";

export const Route = createFileRoute("/admin/control")({
  component: () => (
    <RequireOperator>
      <ControlLayerView />
    </RequireOperator>
  ),
});

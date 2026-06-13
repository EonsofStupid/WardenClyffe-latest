import { createFileRoute } from "@tanstack/react-router";
import { RequireOperator } from "../domains/warden/identity";
import { IntelligenceLayerView } from "../domains/admin/intelligence";

export const Route = createFileRoute("/admin/intelligence")({
  component: () => (
    <RequireOperator>
      <IntelligenceLayerView />
    </RequireOperator>
  ),
});

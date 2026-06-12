import { createFileRoute } from "@tanstack/react-router";
import { IntelligenceLayerView } from "../domains/admin/intelligence";

export const Route = createFileRoute("/admin/intelligence")({ component: IntelligenceLayerView });

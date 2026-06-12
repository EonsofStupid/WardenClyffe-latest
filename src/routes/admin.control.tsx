import { createFileRoute } from "@tanstack/react-router";
import { ControlLayerView } from "../domains/admin/control";

export const Route = createFileRoute("/admin/control")({ component: ControlLayerView });

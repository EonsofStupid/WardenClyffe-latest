import { createFileRoute } from "@tanstack/react-router";
import { LandingView } from "../domains/landing";

export const Route = createFileRoute("/")({ component: LandingView });

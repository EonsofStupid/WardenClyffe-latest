// Router entry. TanStack Start's vite plugin imports `getRouter` from this
// file (the #tanstack-router-entry contract). `routeTree.gen.ts` is GENERATED
// from src/routes/ — do not hand-edit it.
import { createRouter } from "@tanstack/react-router";
import { routeTree } from "./routeTree.gen";

export function getRouter() {
  return createRouter({ routeTree });
}

declare module "@tanstack/react-router" {
  interface Register {
    router: ReturnType<typeof getRouter>;
  }
}

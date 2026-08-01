// TanStack Start root — full HTML document shell is required.
// Without <html>/<head>/<HeadContent/> + <Scripts/>, the browser gets a bare
// fragment: white page, black text, no CSS (exactly what we shipped by mistake).
import type { ReactNode } from "react";
import {
  createRootRoute,
  HeadContent,
  Outlet,
  Scripts,
} from "@tanstack/react-router";
import { AuthProvider } from "../domains/warden/identity";
import appCss from "../lib/design/index.css?url";
import clyffeCodeCss from "../domains/clyffe/code/clyffe-code.css?url";

export const Route = createRootRoute({
  head: () => ({
    meta: [
      { charSet: "utf-8" },
      { name: "viewport", content: "width=device-width, initial-scale=1" },
      {
        title: "Clyffe Code · WardenClyffe",
      },
      {
        name: "color-scheme",
        content: "dark",
      },
      {
        name: "theme-color",
        content: "#12141c",
      },
    ],
    links: [
      { rel: "stylesheet", href: appCss },
      { rel: "stylesheet", href: clyffeCodeCss },
    ],
  }),
  component: RootComponent,
});

function RootComponent() {
  return (
    <RootDocument>
      <AuthProvider>
        <Outlet />
      </AuthProvider>
    </RootDocument>
  );
}

function RootDocument({ children }: Readonly<{ children: ReactNode }>) {
  return (
    <html lang="en" data-theme="dark">
      <head>
        <HeadContent />
        {/* Critical fallback if a link is slow — never show naked browser defaults */}
        <style
          dangerouslySetInnerHTML={{
            __html: `
html, body { margin: 0; min-height: 100%; }
body {
  background: #12141c;
  color: #f3f4f8;
  font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
}
a { color: #9db7ff; }
`,
          }}
        />
      </head>
      <body>
        {children}
        <Scripts />
      </body>
    </html>
  );
}

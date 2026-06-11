# domain: admin

The authenticated shell + role gate. One login at `auth.rrflow.ai`, then this
decides the **Admin (Warden)** vs **Customer** surface for users who hold both.

The customer plane currently lives here as `admin/storage` (folded in per the
2026-06-11 decision; there is no top-level `clyffe` frontend domain yet). When
the customer view grows, keep operator and customer concerns **role-separated** —
never one blended screen. The Go `clyffe-api` customer boundary still stands.

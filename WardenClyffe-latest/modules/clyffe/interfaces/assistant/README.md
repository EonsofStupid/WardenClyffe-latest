# Clyffe Assistant Interface

Customer-safe Clyffy assistant surface.

It may answer using customer-safe KB and Warden-published service state. It may
open tickets or request safe actions. It does not execute infrastructure writes
directly.

The master/operator Clyffy surface is tracked separately in
`docs/CLYFFY_DYNAMIC_UI_SPEC.md`. Customer Clyffy features must reuse the same
dynamic content pattern, but with tenant-scoped sources and customer-safe
actions only.

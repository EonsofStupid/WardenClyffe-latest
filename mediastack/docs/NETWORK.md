---
wardenclyffe_touchpoint:
  version: 1
  kind: estate-doc
  namespace: wardenclyffe.mediastack
  owner: hades
---

# Mediastack — Network Boundary

> Owner: `hades` · isolated network · internal-only

The Mediastack VM runs on its **own isolated network segment**, distinct from
the AIaaS product fabric and the customer estates.

## Posture

- **Isolation:** dedicated network segment for the VM. It does not share a
  broadcast/L2 domain or data plane with AIaaS or customer workloads.
- **Visibility:** `internal`. No public DNS, no edge routing, no public ports by
  default.
- **Reachability:** members reach the stack over the private/mesh path only
  (e.g. via the WardenClyffe private mesh, `wardenclyffenet`), never via a
  public address.
- **Egress:** outbound is for media services' own needs only; it is not a
  general transit path for other estates.

## Rules

1. Every media service port defaults to `public: false` (enforced by the
   `category: media` rules in `../../wardenclyffe-catalog/SCHEMA.md`).
2. Any public exposure requires a deliberate, owner-approved deploy-time
   override — and should be the rare exception, documented in `ops/`.
3. Network/firewall changes are executed through Warden (infra authority), but
   must preserve this isolation; they may not attach Mediastack to shared AIaaS
   planes.

## TODO (fill in with live values when provisioned)

- [ ] VM host / Proxmox node
- [ ] Network segment / VLAN / subnet
- [ ] Mesh entry point (`wardenclyffenet` peer details)
- [ ] Firewall posture reference (OPNsense rule set)

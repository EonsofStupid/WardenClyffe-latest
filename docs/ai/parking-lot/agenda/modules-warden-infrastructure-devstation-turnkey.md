---
clyffy_touchpoint:
  version: 2
  workspace_id: wardenclyffe.intelligence
  project_key: modules-warden-infrastructure-devstation-turnkey
  persona: clyffy-operator
  kind: agenda
  owner: docs/ai/parking-lot/agenda/modules-warden-infrastructure-devstation-turnkey.md
  module: null
  focus_feature: "Building WardenClyffe"
  boundary: modules/warden/infrastructure/devstation/turnkey
  domain_status: existing
  source_skill: docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md
  mesh_registry: wardenclyffe/registry/context-mesh.yaml
  sync:
    qdrant: true
    surreal: true
---

# Parking-Lot Agenda — modules-warden-infrastructure-devstation-turnkey

Focus: **Building WardenClyffe**. Boundary: `modules/warden/infrastructure/devstation/turnkey`. Opened 2026-06-20.

Ordered decision items. State ∈ open|locked|parked; `rounds` capped at 12. Managed by `scripts/foundation/parking-lot-agenda.py` per `docs/ai/WARDENCLYFFE_PARKING_LOT_SKILL.md`. Decisions are captured by `parking-lot-capture.py`; the spec is emitted by `parking-lot-spec.py`.

<!-- agenda:start -->
- [1] locked rounds=2 || q=Local substrate to run Clyffy: Proxmox golden-template clone + Podman/Quadlet inside || boundary=modules/warden/infrastructure/devstation
- [2] locked rounds=2 || q=88-core vs Virginia role split, federated over WardenNet (not cross-WAN cluster) || boundary=modules/warden/infrastructure/edge
- [3] locked rounds=2 || q=Secret-leak core: broker-at-egress (alias in workspace, raw key never enters it) || boundary=services/storage-broker-client
- [4] open rounds=1 || q=Default-deny egress mechanism: nftables+DNS allowlist now, Cilium when/if k8s || boundary=modules/warden/infrastructure/devstation
- [5] open rounds=0 || q=DLP layer + role of the local LLM (adjunct classifier, not the gate) || boundary=modules/warden/infrastructure/devstation
- [6] open rounds=0 || q=Turnkey buttons: Connect & Launch UI + .mcpb one-click bundles || boundary=src/domains/admin
- [7] open rounds=0 || q=Preflight/attunement check-gate (goss-style if/then) || boundary=modules/warden/infrastructure/devstation
- [8] open rounds=0 || q=Execute the TURNKEY_VM_CAPSULE_RESEARCH brief as the evidence pass || boundary=docs
- [9] open rounds=0 || q=VPN overlay split: Tailscale = operator/internal infra only; WardenNet (branded Headscale, WardenClyffe identity) = customers + Clyffy fleet || boundary=modules/warden/infrastructure/edge
<!-- agenda:end -->

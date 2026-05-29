# Warden Infrastructure Adapters

Adapters for external systems live here when root implementation begins:

- Proxmox.
- host-local SSH/tools.
- database storage.
- Qdrant.
- SurrealDB.
- object storage.
- OIDC providers.

Infrastructure depends on domain/application contracts, not the other way
around.

Current access contracts:

- `proxmox-access/README.md` for Proxmox API tokens and read-only probes.
- `operator-access/README.md` for verified SSH host trust and host-local
  helper commands.
- `operator-capsule/README.md` for the Linux-first Warden operator workspace,
  agent CLIs, and brokered secret-file handling.
- `devstation/README.md` for the private VS Code/Cursor/Codex/Claude VM,
  SSH-tunneled hosted editor, and future Clyffe Code workspace template.

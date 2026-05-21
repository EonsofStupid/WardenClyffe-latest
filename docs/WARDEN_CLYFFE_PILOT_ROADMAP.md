# WardenClyffe Pilot Roadmap

This roadmap is for the internal two-server build first. The goal is to make WardenClyffe useful for our own operations before worrying about public packaging, licensing strategy, or deeper substrate replacement.

## Product Goal

Build a completely free, modern server/customer platform:

- **Warden**: admin/operator panel for servers, Proxmox-backed VMs and containers, storage, networking, backups, automation, and AI operations.
- **Clyffe**: customer portal, service panel, knowledge base, tickets, CRM, and support workflow.

Proxmox VE is the current infrastructure substrate. WardenClyffe should progressively replace the day-to-day Proxmox experience with a cleaner Warden/Clyffe product surface.

## Phase 1: Two-Server Warden Pilot

Required outcomes:

- Register two Proxmox hosts as Warden nodes.
- Read node health, cluster status, storage, networks, VMs, and LXC containers from Proxmox APIs.
- Store inventory in PostgreSQL.
- Show operator inventory through a Warden API.
- Poll Proxmox task status and write Warden audit events.
- Support safe lifecycle actions for assigned guests: start, shutdown, stop, restart.
- Keep all write actions behind explicit operator approval.

Core modules:

- `warden-proxmox`: Proxmox API client and task adapter.
- `warden-inventory`: node, guest, storage, network, and ownership records.
- `warden-audit`: immutable action/task log.
- `warden-api`: operator and customer-scoped HTTP API.

## Phase 2: Clyffe Customer Panel

Required outcomes:

- Customer login through OIDC-ready auth.
- Customer dashboard with assigned services only.
- VM/LXC status, resource usage, and allowed actions.
- Ticket creation, replies, internal notes, assignment, status, and escalation.
- Knowledge base with customer-safe documentation.
- CRM records for organizations, contacts, service notes, and account history.

Customer-safe actions:

- Start assigned guest.
- Graceful shutdown assigned guest.
- Restart assigned guest.
- Open console for assigned guest.
- Request restore from backup.
- Request rebuild from approved template.
- Open support ticket.

Blocked direct customer actions:

- Delete guest.
- Change network ownership.
- Change storage backend.
- Touch another tenant's resources.
- Run raw Proxmox actions.
- Execute arbitrary host commands.

## Phase 3: AI And Knowledge

Required outcomes:

- Qdrant-backed knowledge retrieval for docs, runbooks, and customer help.
- Warden AI assistant for operator diagnostics and proposed fixes.
- Clyffe AI assistant for customer-safe service help and ticket drafting.
- Full audit trail for AI-suggested actions.
- No autonomous destructive actions.

Data separation:

- Operator memory is private to Warden.
- Customer knowledge is scoped to Clyffe and tenant permissions.
- Project/personal assistant memory stays separate from customer-visible support answers.

## Phase 4: Turnkey Packaging

Required outcomes:

- One-command internal install for Warden on Proxmox hosts.
- Clyffe portal deployment through the same stack.
- Caddy or Nginx reverse proxy config.
- PostgreSQL and Qdrant bootstrap.
- Backup/restore for WardenClyffe control-plane data.
- Clear upgrade path for the two-server deployment.

## Modern Web Direction

The current PHP web tree is migration source material.

Target stack:

- Astro for public site, docs, changelog, and knowledge base.
- A real app frontend for Warden and Clyffe panels.
- Rust API services for Warden control plane.
- PostgreSQL as system-of-record.
- Qdrant for vector search and AI memory.
- Object storage for screenshots, attachments, reports, and generated assets.


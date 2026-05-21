# WardenClyffe Architecture

This repo is being oriented around two branded products:

- **Warden** is the operator/server-control platform.
- **Clyffe** is the customer portal, customer knowledge base, and tenant-safe service surface.

Warden and Clyffe are not separate ideas. They are the two faces of WardenClyffe:

- Warden owns infrastructure authority.
- Clyffe consumes scoped Warden APIs on behalf of customers.

## Internal-First Goal

The first target is a turnkey WardenClyffe deployment for our own two servers.

For this phase, Proxmox VE is treated as the local infrastructure substrate:

- Proxmox provides KVM, LXC, storage, networking, backups, tasks, consoles, and cluster primitives.
- Warden provides the modern operator experience, automation layer, AI assistant, and API.
- Clyffe provides the customer portal and knowledge base using Warden's tenant-safe API.

The long-term direction is that WardenClyffe progressively absorbs the workflows we need from Proxmox and modernizes them under our own Warden/Clyffe product identity. We are not starting with a hard fork. We are starting with a Proxmox-native control plane that can later become deeper and more self-contained where it makes sense.

## Product Boundary

### Warden

Warden is for trusted operators and server owners.

Responsibilities:

- Discover and manage Proxmox nodes and clusters.
- Manage VMs, LXC containers, templates, backups, snapshots, networks, storage, and firewalls.
- Provide fleet health, logs, metrics, alerts, status pages, and issue scans.
- Run approved automation and provisioning workflows.
- Host the infrastructure AI assistant and MCP meshnode.
- Own credentials, audit logs, tenant boundaries, and infrastructure inventory.

Warden may talk directly to Proxmox APIs, local Linux tools, WardenClyffeNet, WardenClyffeDisk, Qdrant, PostgreSQL, and other internal services.

### Clyffe

Clyffe is for customers.

Responsibilities:

- Show only customer-owned or assigned VMs, containers, services, tickets, invoices, and docs.
- Provide safe actions such as start, stop, restart, console access, rebuild from approved template, backup restore request, and support request.
- Provide a customer knowledge base and AI-assisted help surface.
- Provide customer tickets, account history, service notes, contact records, and CRM-style relationship tracking.
- Track customer service ownership across VMs, containers, domains, backups, support plans, and managed operations.
- Never expose Proxmox directly.
- Never bypass Warden's API, permissions, audit log, or rate limits.

### Clyffe Customer Panel

The Clyffe panel should be built around customer operations, not infrastructure jargon.

Core customer views:

- Dashboard: active services, health, resource usage, open tickets, recent changes.
- Services: assigned VMs, containers, databases, domains, storage, backups, and status.
- Console: controlled console access only for assigned guests.
- Backups: available restore points and restore requests.
- Tickets: support requests, incidents, approvals, notes, attachments, and status.
- Knowledge base: WardenClyffe docs, service guides, runbooks, and customer-safe AI answers.
- Account/CRM: contacts, organizations, service notes, plan details, invoices or billing-provider links.

Customer actions must be permission-scoped, rate-limited, and audit-logged. Any action that can destroy data, alter networking, change billing, or affect other tenants should become a request or approval workflow rather than direct execution.

### Warden Operator Panel

The Warden panel should be the command center for our servers.

Core operator views:

- Fleet: Proxmox nodes, native Warden nodes, health, metrics, alerts, and inventory.
- Guests: VMs, LXC containers, templates, snapshots, backups, consoles, and ownership.
- Customers: tenants, contacts, assigned services, support state, notes, and account health.
- Tickets/CRM: queues, incidents, tasks, customer notes, internal notes, and escalation.
- Provisioning: templates, products, plans, resource limits, hooks, and approval flows.
- Automation: scheduled jobs, workflows, AI-proposed fixes, and run history.
- Knowledge: docs, runbooks, embeddings status, Qdrant collections, and AI sources.
- Audit: operator actions, customer actions, Proxmox tasks, API calls, and approval trails.

## Proxmox Strategy

The immediate strategy is to study Proxmox deeply and wrap it properly:

- Use the Proxmox REST API as the primary integration path.
- Use local `pvesh`, `qm`, and `pct` only where needed as host-local helpers or fallbacks.
- Model Proxmox tasks as first-class Warden jobs.
- Translate Proxmox users, pools, guests, storage, nodes, and permissions into Warden-owned records.
- Keep customer-facing actions scoped through Clyffe and Warden's tenant API.

The first useful integration surfaces are:

1. Node discovery and health.
2. VM and LXC inventory.
3. Guest lifecycle: start, stop, restart, shutdown.
4. Task polling and audit logging.
5. Template inventory and customer-safe provisioning.
6. Console access with tenant guardrails.
7. Backups, snapshots, and restores.

## Data Layer Direction

The Warden/Clyffe control plane should not be based on MariaDB just because WardenClyffeScale currently exists.

Recommended control-plane storage:

- **PostgreSQL** for tenants, users, RBAC, inventory, Proxmox resources, audit logs, support records, and workflow state.
- **Qdrant** for AI memory, project embeddings, semantic knowledge-base search, and Clyffy assistant retrieval.
- **Object storage** for screenshots, generated reports, backups metadata exports, docs assets, and customer artifacts.
- **Redis/Dragonfly or NATS** as optional infrastructure for sessions, queues, events, and long-running job coordination.

WardenClyffeScale can remain a separate database replication product or managed service offering. It should not define the primary Warden/Clyffe platform architecture.

## Auth Direction

The preferred identity direction is external OIDC-first auth instead of custom auth inside the server manager.

Candidate direction:

- ZITADEL for modern self-hosted identity, organizations, projects, OIDC, and customer/operator separation.
- Keycloak as a heavier enterprise alternative.
- Ory as a composable alternative if we want to own more assembly.

For the internal two-server phase, Warden can start with a small local admin bootstrap, but the architecture should assume OIDC and tenant-aware permissions.

## AI Direction

Warden's AI system is an operator assistant first:

- Understand server inventory, Proxmox state, WardenClyffe docs, logs, incidents, and projects.
- Propose actions with risk levels.
- Require explicit approval before any write or destructive operation.
- Store embeddings in Qdrant.
- Keep personal/project memory separate from customer-visible Clyffe knowledge.

Clyffe's AI system is customer-safe:

- Answer docs and service questions.
- Explain assigned VM/container status.
- Open support tickets.
- Suggest safe actions.
- Escalate to Warden/operator when needed.

## Branding Rule

The platform should be presented as WardenClyffe:

- Warden for server/operator control.
- Clyffe for customer portal and knowledge base.
- No legacy-branded filenames, graphics, links, screenshots, CLI names, or documentation should remain in final product surfaces.

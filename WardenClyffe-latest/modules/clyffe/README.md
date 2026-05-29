---
wardenclyffe_touchpoint:
  version: 1
  kind: product-module
  namespace: wardenclyffe.modules.clyffe
  owner: modules/clyffe/README.md
  module: module-02-clyffe
---

# Clyffe

Clyffe is Module 2.

Clyffe is the customer portal, knowledge base, tickets, CRM, and
customer-safe service panel. It consumes Warden APIs and never talks directly
to Proxmox.

## Bounded Contexts

| Context | Purpose |
|---|---|
| `services` | assigned VMs, containers, domains, databases, backups, safe service actions |
| `support` | tickets, incidents, messages, approvals |
| `knowledge-base` | customer-safe articles, guides, and assistant sources |
| `account` | organizations, contacts, CRM notes, plan/account view |


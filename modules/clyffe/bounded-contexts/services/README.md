# Clyffe Services Context

Owns customer-visible services:

- assigned VMs and containers.
- domains.
- databases.
- backups.
- safe lifecycle actions.
- restore requests.

Raw Proxmox objects are translated into Warden resources before reaching this
context.


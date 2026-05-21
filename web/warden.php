<?php
$page_title = 'Warden Server Manager - WardenClyffe Docs';
$page_desc = 'Warden is the operator/server-control panel for Proxmox-backed WardenClyffe infrastructure.';
$active = 'warden.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">
        <div class="content-section">
            <h2>Warden Server Manager</h2>
            <p>Warden is the operator side of WardenClyffe. It is the control plane for our servers, Proxmox nodes, VMs, LXC containers, storage, networking, backups, automation, tickets, CRM context, and AI-assisted operations.</p>

            <h3>Internal First</h3>
            <p>The first Warden target is our own two-server deployment. Proxmox VE is the infrastructure substrate for KVM, LXC, storage, tasks, consoles, and backups. Warden wraps that with a modern API, operator workflow, audit trail, and customer-safe boundaries.</p>

            <h3>Core Operator Areas</h3>
            <ul>
                <li><strong>Fleet</strong> - Proxmox hosts, native Warden nodes, health, metrics, and inventory</li>
                <li><strong>Guests</strong> - VMs, LXC containers, templates, snapshots, backups, ownership, and consoles</li>
                <li><strong>Customers</strong> - tenants, contacts, assigned services, account history, and service notes</li>
                <li><strong>Tickets and CRM</strong> - support queues, incidents, tasks, internal notes, and escalation</li>
                <li><strong>Provisioning</strong> - products, templates, resource limits, hooks, and approval workflows</li>
                <li><strong>Automation</strong> - scheduled jobs, AI-proposed fixes, workflow history, and rollback notes</li>
                <li><strong>Audit</strong> - operator actions, customer actions, Proxmox tasks, API calls, and approvals</li>
            </ul>

            <h3>Proxmox Role</h3>
            <p>Warden uses Proxmox as the current host substrate, not as the customer-facing product. Proxmox actions are wrapped as Warden tasks, stored in Warden inventory, and exposed to Clyffe only through tenant-scoped APIs.</p>
        </div>

        <div class="page-nav"><a href="wardenclyffe.php" class="prev">&larr; WardenClyffe Overview</a><a href="clyffe.php" class="next">Clyffe Customer Portal &rarr;</a></div>
    </main>
<?php include 'includes/footer.php'; ?>


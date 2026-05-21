<?php
$page_title = 'Clyffe Customer Portal - WardenClyffe Docs';
$page_desc = 'Clyffe is the customer portal, knowledge base, tickets, CRM, and customer-safe service panel for WardenClyffe.';
$active = 'clyffe.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">
        <div class="content-section">
            <h2>Clyffe Customer Portal</h2>
            <p>Clyffe is the customer-facing side of WardenClyffe. It gives each customer a clean panel for assigned services, support, tickets, knowledge base articles, and safe operations without exposing Proxmox directly.</p>

            <h3>Customer Views</h3>
            <ul>
                <li><strong>Dashboard</strong> - active services, health, resource usage, open tickets, and recent changes</li>
                <li><strong>Services</strong> - assigned VMs, containers, databases, domains, storage, backups, and status</li>
                <li><strong>Console</strong> - controlled console access only for assigned guests</li>
                <li><strong>Backups</strong> - restore points, restore requests, and operator approvals</li>
                <li><strong>Tickets</strong> - support requests, replies, incidents, approvals, notes, and attachments</li>
                <li><strong>Knowledge Base</strong> - customer-safe WardenClyffe docs, guides, and AI-assisted answers</li>
                <li><strong>Account and CRM</strong> - contacts, organizations, service notes, plan details, and billing links</li>
            </ul>

            <h3>Allowed Customer Actions</h3>
            <ul>
                <li>Start, shutdown, or restart an assigned VM or container</li>
                <li>Open a console for an assigned guest</li>
                <li>Request restore from an available backup</li>
                <li>Request rebuild from an approved template</li>
                <li>Open and reply to support tickets</li>
                <li>Ask customer-safe AI questions grounded in the knowledge base and assigned service state</li>
            </ul>

            <h3>Boundaries</h3>
            <p>Clyffe never talks to Proxmox directly. Every customer action goes through Warden's tenant-scoped API, permission checks, rate limits, and audit logs. Actions that can destroy data, alter networking, affect another tenant, or change billing become request or approval workflows.</p>
        </div>

        <div class="page-nav"><a href="warden.php" class="prev">&larr; Warden Server Manager</a><a href="proxmox.php" class="next">Proxmox Substrate &rarr;</a></div>
    </main>
<?php include 'includes/footer.php'; ?>


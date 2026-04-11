<?php
$page_title = 'WolfHost — Enterprise Web Hosting Plugin for WolfStack';
$page_desc = 'WolfHost transforms WolfStack into a complete web hosting business platform. Manage customers, hosting plans, billing, domains, FTP, SSL, databases, email, app installer, container provisioning, and a white-label customer portal.';
$active = 'wolfhost.php';
$page_css = '.wh-feature-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}
.wh-feature-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}
.wh-feature-card:hover {
    border-color: rgba(220, 38, 38, 0.3);
    transform: translateY(-2px);
    box-shadow: 0 6px 24px rgba(0,0,0,0.2);
}
.wh-feature-card h4 {
    font-size: 0.95rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    gap: 8px;
}
.wh-feature-card p {
    font-size: 0.82rem;
    color: var(--text-secondary);
    line-height: 1.6;
}
.wh-arch-diagram {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    margin: 1.5rem 0;
}
.wh-arch-boxes {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin: 1rem 0;
}
.wh-arch-box {
    background: var(--bg-tertiary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 1rem 1.5rem;
    min-width: 140px;
    font-size: 0.85rem;
    font-weight: 600;
}
.wh-arch-arrow {
    font-size: 1.5rem;
    color: var(--text-muted);
    display: flex;
    align-items: center;
}
.wh-badge-row { display:flex; gap:10px; flex-wrap:wrap; margin-bottom:16px; }
.wh-badge { display:inline-flex; align-items:center; gap:8px; border-radius:8px; padding:8px 16px; font-size:0.85rem; }
.wh-badge-enterprise { background:linear-gradient(135deg,rgba(220,38,38,0.1),rgba(239,68,68,0.05)); border:1px solid rgba(220,38,38,0.25); }
.wh-badge-dev { background:linear-gradient(135deg,rgba(245,158,11,0.1),rgba(251,191,36,0.05)); border:1px solid rgba(245,158,11,0.3); }
@media (max-width: 768px) {
    .wh-feature-grid { grid-template-columns: 1fr; }
    .wh-arch-boxes { flex-direction: column; align-items: center; }
    .wh-arch-arrow { transform: rotate(90deg); }
}';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content" style="max-width:1100px;">

            <div class="content-section">
                <h2>WolfHost</h2>
                <div class="wh-badge-row">
                    <div class="wh-badge wh-badge-enterprise">
                        <span style="font-size:1.1rem;">&#128273;</span>
                        <span><strong>Enterprise Plugin</strong> &mdash; requires an active <a href="enterprise.php" style="color:#ef4444;font-weight:600;">Enterprise license</a></span>
                    </div>
                    <div class="wh-badge wh-badge-dev">
                        <span style="font-size:1.1rem;">&#128679;</span>
                        <span><strong>In Development</strong> &mdash; WolfHost is currently under active development and not yet available for download.</span>
                    </div>
                </div>
                <p style="font-size:1.05rem;color:var(--text-secondary);line-height:1.8;margin-bottom:1rem;">
                    WolfHost is an <strong>enterprise WolfStack plugin</strong> that transforms your server into a complete web hosting business platform.
                    Manage customers, create hosting plans, handle billing, provision containers across your cluster, and give your customers a
                    fully white-labelled self-service portal to manage their websites, domains, FTP accounts, SSL certificates, databases,
                    email, and one-click apps &mdash; all from within WolfStack.
                </p>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="wolfstack-plugins.php" class="btn" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:var(--bg-tertiary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);text-decoration:none;font-size:0.85rem;font-weight:600;">&#128268; Plugin System</a>
                    <a href="enterprise.php" class="btn" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;background:linear-gradient(135deg,#dc2626,#ef4444);border:none;border-radius:8px;color:white;text-decoration:none;font-size:0.85rem;font-weight:600;">Enterprise Licensing</a>
                </div>
            </div>

            <!-- Architecture -->
            <div class="content-section">
                <h2>How It Works</h2>
                <p>WolfHost runs as a WolfStack plugin with three components: an admin API integrated into the WolfStack dashboard,
                   a customer-facing self-service portal, and a provisioning engine that creates and configures containers across your cluster.</p>
                <div class="wh-arch-diagram">
                    <div class="wh-arch-boxes">
                        <div class="wh-arch-box" style="border-color:rgba(220,38,38,0.3);">&#128100; Admin<br><small style="font-weight:400;color:var(--text-muted);">WolfStack Dashboard</small></div>
                        <div class="wh-arch-arrow">&rarr;</div>
                        <div class="wh-arch-box" style="border-color:rgba(59,130,246,0.3);">&#9881; WolfHost API<br><small style="font-weight:400;color:var(--text-muted);">Port 9200</small></div>
                        <div class="wh-arch-arrow">&rarr;</div>
                        <div class="wh-arch-box" style="border-color:rgba(168,85,247,0.3);">&#128230; Provisioning<br><small style="font-weight:400;color:var(--text-muted);">LXC Containers</small></div>
                        <div class="wh-arch-arrow">&rarr;</div>
                        <div class="wh-arch-box" style="border-color:rgba(16,185,129,0.3);">&#127760; Customer Portal<br><small style="font-weight:400;color:var(--text-muted);">Port 8443</small></div>
                    </div>
                    <p style="font-size:0.82rem;color:var(--text-muted);margin-top:1rem;">Admin manages hosting through WolfStack. WolfHost provisions containers across the cluster. Customers access their own portal.</p>
                </div>
            </div>

            <!-- Features -->
            <div class="content-section">
                <h2>Features</h2>
                <div class="wh-feature-grid">
                    <div class="wh-feature-card">
                        <h4>&#128100; Customer Management</h4>
                        <p>Create and manage customer accounts with full contact details, addresses, notes, and account status. Two-factor authentication (TOTP) for customer portal security. View all services, invoices, and tickets per customer.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128230; Hosting Plans</h4>
                        <p>Define hosting plans with disk space, bandwidth, domain limits, FTP accounts, email accounts, databases, and SSL certificates. Monthly and yearly pricing.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128176; Billing &amp; Invoices</h4>
                        <p>Automatic invoice generation, payment tracking, and billing history. Multi-currency support (USD, EUR, GBP, CAD, AUD). Customers view and pay invoices from the portal.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#127760; Domain Management</h4>
                        <p>Manage primary domains, addon domains, and subdomains with DNS record storage. Automatic Apache vhost provisioning with document roots, mod_rewrite, and reverse proxy support. DNS status tracking with active, pending, and suspended states.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128274; SSL Certificates</h4>
                        <p>Automatic SSL provisioning via Let&rsquo;s Encrypt with Certbot. Customers can request, view, and manage certificates from the self-service portal.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128450; FTP Accounts</h4>
                        <p>Create FTP accounts for customers with configurable home directories and quotas. Automatic vsftpd provisioning inside the customer&rsquo;s container.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128229; Email Accounts</h4>
                        <p>Full mail server setup with Postfix, Dovecot, and DKIM signing. Manage email accounts, aliases, and forwarders per domain. Automatic SPF, DKIM, and DMARC DNS record generation. Pre-configured SMTP (587), IMAP (993), and POP3 (995).</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128451; Databases</h4>
                        <p>Create MySQL/MariaDB and PostgreSQL databases for customers with configurable size limits. Optional central database server or per-container databases.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128196; Support Tickets</h4>
                        <p>Built-in ticketing system with priority levels and status tracking. Customers submit tickets from the portal; admins respond from the WolfStack dashboard.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128187; Customer Portal</h4>
                        <p>Self-service web portal where customers log in to manage their websites, domains, FTP, email, databases, SSL, backups, apps, and billing. Fully white-labelled.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128640; Container Provisioning</h4>
                        <p>Automatically provision LXC containers for each customer across your WolfStack cluster. Smart node selection picks the least-loaded server. Full LAMP stack setup inside the container. Live provisioning logs streamed in real time via SSE. Works with both native LXC and Proxmox.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128279; Reverse Proxy</h4>
                        <p>Automatic Nginx reverse proxy from host to container with ACME challenge passthrough, WebSocket support, large file uploads (256&nbsp;MB), and proper header forwarding. FTP port forwarding with automatic port allocation.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128200; Container Stats</h4>
                        <p>Real-time container resource monitoring in the customer portal. Live CPU percentage, memory usage, network I/O, and process count. Visual disk and bandwidth usage meters with plan limits.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#127919; One-Click App Installer</h4>
                        <p>12 apps available: WordPress, Joomla, Drupal, PrestaShop, Nextcloud, Ghost, Laravel, Matomo, Roundcube, phpMyAdmin, Adminer, and File Browser. Automatic database creation, domain configuration, and installation inside the customer&rsquo;s container.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#127912; White-Label Branding</h4>
                        <p>Fully customise the customer portal with your company name, logo, accent colour, tagline, support email, terms URL, custom CSS, and favicon. Your customers see your brand, not WolfHost.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128747; Multi-Server Cluster</h4>
                        <p>WolfHost integrates with the WolfStack cluster. View all nodes, container stats, and provision new containers across any server in the cluster. Supports both WolfStack native and Proxmox nodes.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128451; Central Database Server</h4>
                        <p>Optionally connect to a central MySQL/MariaDB server for customer databases instead of per-container installs. Configure from the admin panel with connection test.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128190; Backups</h4>
                        <p>Customers can create and restore backups of their hosting accounts from the portal. Backup scheduling and retention policies.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128221; File Manager</h4>
                        <p>Browser-based file manager in the customer portal for uploading, editing, and managing website files without FTP.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128640; Proxmox Integration</h4>
                        <p>WolfHost auto-detects Proxmox hosts and uses <code>pct</code> for container management. Provision containers, start/stop guests, and manage hosting across Proxmox clusters.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128274; Two-Factor Auth</h4>
                        <p>TOTP-based two-factor authentication for customer portal accounts. Customers can enable 2FA from their account settings for an extra layer of security on their hosting account.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#127760; Node IP Overrides</h4>
                        <p>Override node IP addresses from the admin panel for environments where the detected IP differs from the public-facing address. Fallback chain: override, public IP, internal address.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#128268; DirectAdmin Backend</h4>
                        <p>Attach an existing DirectAdmin server as a backend. WolfHost acts as a <strong>white-label frontend</strong> to DirectAdmin &mdash; customers see domains, email, databases, DNS, and files from DA transparently through the WolfHost portal. If DirectAdmin is later removed, WolfHost takes over management natively with no customer disruption.</p>
                    </div>
                    <div class="wh-feature-card">
                        <h4>&#127760; DNS Record Management</h4>
                        <p>View, add, edit, and delete DNS records for customer domains. Works with both DirectAdmin-managed zones and PowerDNS. Supports A, AAAA, CNAME, MX, TXT, SRV, and NS record types with TTL configuration.</p>
                    </div>
                </div>
            </div>

            <!-- Page links -->
            <div class="content-section">
                <h2>Documentation</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;">
                    <a href="wolfhost-admin.php" style="display:block;background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:1.2rem;text-decoration:none;color:var(--text-primary);transition:all 0.2s ease;">
                        <strong style="display:block;margin-bottom:4px;">&#128100; Admin Dashboard</strong>
                        <span style="font-size:0.82rem;color:var(--text-secondary);">Customer, plan, invoice, ticket, and server management</span>
                    </a>
                    <a href="wolfhost-portal.php" style="display:block;background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:1.2rem;text-decoration:none;color:var(--text-primary);transition:all 0.2s ease;">
                        <strong style="display:block;margin-bottom:4px;">&#127760; Customer Portal</strong>
                        <span style="font-size:0.82rem;color:var(--text-secondary);">White-label self-service portal and branding</span>
                    </a>
                    <a href="wolfhost-provisioning.php" style="display:block;background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:1.2rem;text-decoration:none;color:var(--text-primary);transition:all 0.2s ease;">
                        <strong style="display:block;margin-bottom:4px;">&#128640; Provisioning</strong>
                        <span style="font-size:0.82rem;color:var(--text-secondary);">Container provisioning, reverse proxy, and mail server</span>
                    </a>
                    <a href="wolfhost-setup.php" style="display:block;background:var(--bg-card);border:1px solid var(--border-color);border-radius:10px;padding:1.2rem;text-decoration:none;color:var(--text-primary);transition:all 0.2s ease;">
                        <strong style="display:block;margin-bottom:4px;">&#9881; Setup &amp; Configuration</strong>
                        <span style="font-size:0.82rem;color:var(--text-secondary);">Installation, configuration, and technical details</span>
                    </a>
                </div>
            </div>

            <div class="page-nav"><a href="wolfstack-plugins.php" class="prev">&larr; Plugin System</a><a href="wolfhost-admin.php" class="next">Admin Dashboard &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

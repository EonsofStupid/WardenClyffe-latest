<?php
$page_title = 'Compare WolfStack — Feature Comparison';
$page_desc = 'See how WolfStack compares to Proxmox, Kubernetes, Portainer, CasaOS, and Cockpit. Full feature-by-feature comparison table.';
$active = 'comparison.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">

            <div class="content-section">
                <h2>WolfStack vs the Competition</h2>
                <p>WolfStack replaces multiple separate tools with a single platform. Here&rsquo;s how it compares feature-by-feature.</p>
            </div>

            <div class="content-section">
                <h2>Container &amp; VM Management</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th style="text-align:left;">Feature</th>
                                <th style="text-align:center;color:var(--accent-primary);">WolfStack</th>
                                <th style="text-align:center;">Proxmox</th>
                                <th style="text-align:center;">Kubernetes</th>
                                <th style="text-align:center;">Portainer</th>
                                <th style="text-align:center;">CasaOS</th>
                                <th style="text-align:center;">Cockpit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Docker Containers</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">Limited</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">Plugin</td></tr>
                            <tr><td>LXC System Containers</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>KVM/QEMU Virtual Machines</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">Basic</td></tr>
                            <tr><td>Proxmox VE Integration</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">Native</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Container Clone &amp; Migrate</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Container Cron Jobs</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">CronJobs</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Docker Image Management</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-section">
                <h2>Orchestration &amp; High Availability</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th style="text-align:left;">Feature</th><th style="text-align:center;color:var(--accent-primary);">WolfStack</th><th style="text-align:center;">Proxmox</th><th style="text-align:center;">Kubernetes</th><th style="text-align:center;">Portainer</th><th style="text-align:center;">CasaOS</th><th style="text-align:center;">Cockpit</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Container Orchestration</td><td style="text-align:center;color:var(--success);font-weight:600;">WolfRun</td><td style="text-align:center;">No</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">Swarm</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Container Failover (HA)</td><td style="text-align:center;color:var(--success);font-weight:600;">Standby HA</td><td style="text-align:center;">HA (paid)</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">Swarm HA</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Kubernetes Management</td><td style="text-align:center;color:var(--success);font-weight:600;">WolfKube</td><td style="text-align:center;">No</td><td style="text-align:center;">Native</td><td style="text-align:center;">Paid</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Pod Terminal &amp; Monitoring</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">CLI only</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>K8s PVC Storage</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">CLI only</td><td style="text-align:center;">Paid</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Multi-Server Clustering</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">Paid</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-section">
                <h2>Networking, Security &amp; Authentication</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th style="text-align:left;">Feature</th><th style="text-align:center;color:var(--accent-primary);">WolfStack</th><th style="text-align:center;">Proxmox</th><th style="text-align:center;">Kubernetes</th><th style="text-align:center;">Portainer</th><th style="text-align:center;">CasaOS</th><th style="text-align:center;">Cockpit</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Built-in User Accounts</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">Linux/PAM</td><td style="text-align:center;">RBAC</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">Linux/PAM</td></tr>
                            <tr><td>Two-Factor Auth (2FA/TOTP)</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">Yes (paid)</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Disable Linux Login</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">N/A</td><td style="text-align:center;">N/A</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Login Rate Limiting</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Encrypted Mesh Network</td><td style="text-align:center;color:var(--success);font-weight:600;">WolfNet</td><td style="text-align:center;">No</td><td style="text-align:center;">CNI plugins</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Built-in VPN</td><td style="text-align:center;color:var(--success);font-weight:600;">WolfNet VPN</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>WireGuard VPN Bridge</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Firewall Management</td><td style="text-align:center;color:var(--success);font-weight:600;">Fail2ban &amp; UFW</td><td style="text-align:center;">Firewall</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">Firewalld</td></tr>
                            <tr><td>SSL/TLS Certificates</td><td style="text-align:center;color:var(--success);font-weight:600;">Let&rsquo;s Encrypt</td><td style="text-align:center;">Let&rsquo;s Encrypt</td><td style="text-align:center;">cert-manager</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">Certbot</td></tr>
                            <tr><td>Nginx/Apache Configurator</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-section">
                <h2>Storage</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th style="text-align:left;">Feature</th><th style="text-align:center;color:var(--accent-primary);">WolfStack</th><th style="text-align:center;">Proxmox</th><th style="text-align:center;">Kubernetes</th><th style="text-align:center;">Portainer</th><th style="text-align:center;">CasaOS</th><th style="text-align:center;">Cockpit</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>S3/R2 Object Storage</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">CSI</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>NFS Mounts</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">PVC</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>SSHFS</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Ceph Management</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">Rook</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>ZFS Management</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Disk Partitioning &amp; SMART</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">Basic</td></tr>
                            <tr><td>Backup &amp; Restore</td><td style="text-align:center;color:var(--success);font-weight:600;">S3, PBS, Local</td><td style="text-align:center;color:var(--success);">PBS</td><td style="text-align:center;">Velero</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-section">
                <h2>Monitoring, Tools &amp; UI</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th style="text-align:left;">Feature</th><th style="text-align:center;color:var(--accent-primary);">WolfStack</th><th style="text-align:center;">Proxmox</th><th style="text-align:center;">Kubernetes</th><th style="text-align:center;">Portainer</th><th style="text-align:center;">CasaOS</th><th style="text-align:center;">Cockpit</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Web Terminal</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;color:var(--success);">Yes</td></tr>
                            <tr><td>File Manager</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td></tr>
                            <tr><td>App Store</td><td style="text-align:center;color:var(--success);font-weight:600;">500+ apps</td><td style="text-align:center;">No</td><td style="text-align:center;">Helm</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;color:var(--success);">Yes</td><td style="text-align:center;">No</td></tr>
                            <tr><td>AI Agent</td><td style="text-align:center;color:var(--success);font-weight:600;">Claude &amp; Gemini</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Status Pages &amp; Alerting</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">Add-ons</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Workflow Automation</td><td style="text-align:center;color:var(--success);font-weight:600;">WolfFlow</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Database Editor</td><td style="text-align:center;color:var(--success);font-weight:600;">MySQL &amp; PostgreSQL</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>3D VR Server Room</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes (WebXR)</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>VR Terminal &amp; Console</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-section">
                <h2>Platform</h2>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th style="text-align:left;">Feature</th><th style="text-align:center;color:var(--accent-primary);">WolfStack</th><th style="text-align:center;">Proxmox</th><th style="text-align:center;">Kubernetes</th><th style="text-align:center;">Portainer</th><th style="text-align:center;">CasaOS</th><th style="text-align:center;">Cockpit</th></tr>
                        </thead>
                        <tbody>
                            <tr><td>Install Complexity</td><td style="text-align:center;color:var(--success);font-weight:600;">1 command</td><td style="text-align:center;">ISO install</td><td style="text-align:center;">Very complex</td><td style="text-align:center;">Moderate</td><td style="text-align:center;color:var(--success);">1 command</td><td style="text-align:center;color:var(--success);">1 command</td></tr>
                            <tr><td>Runs On</td><td style="text-align:center;color:var(--success);font-weight:600;">Any Linux + Proxmox</td><td style="text-align:center;">Debian only</td><td style="text-align:center;">Any Linux</td><td style="text-align:center;">Docker host</td><td style="text-align:center;">Debian/Ubuntu</td><td style="text-align:center;">Any Linux</td></tr>
                            <tr><td>Written In</td><td style="text-align:center;color:var(--success);font-weight:600;">Rust</td><td style="text-align:center;">Perl/C</td><td style="text-align:center;">Go</td><td style="text-align:center;">Go</td><td style="text-align:center;">Go</td><td style="text-align:center;">Python/C</td></tr>
                            <tr><td>Single Binary</td><td style="text-align:center;color:var(--success);font-weight:600;">Yes</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td><td style="text-align:center;">Container</td><td style="text-align:center;">No</td><td style="text-align:center;">No</td></tr>
                            <tr><td>Price</td><td style="text-align:center;color:var(--success);font-weight:600;">Free &amp; Open Source</td><td style="text-align:center;">Free + Paid</td><td style="text-align:center;">Free</td><td style="text-align:center;">Free + Paid</td><td style="text-align:center;">Free</td><td style="text-align:center;">Free</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-section" style="text-align:center;padding:40px 20px;">
                <h2>Ready to switch?</h2>
                <p style="margin-bottom:20px;">One command to install. Free forever.</p>
                <div style="display:flex;justify-content:center;gap:12px;flex-wrap:wrap;">
                    <a href="wolfstack.php" class="btn btn-primary" style="padding:12px 28px;">Get Started</a>
                    <a href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank" class="btn btn-secondary" style="border-color:var(--success);color:var(--success);padding:12px 28px;">Fund Open Source</a>
                </div>
            </div>

<div class="page-nav"><a href="wolfstack.php" class="prev">&larr; Overview</a><a href="enterprise.php" class="next">Enterprise &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

<?php
$page_title = 'WardenClyffe Overview — WardenClyffe Docs';
$page_desc = 'WardenClyffe — The Universal Server Management Platform. Overview, installation, and feature guide.';
$active = 'wardenclyffe.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">

                <div class="content-section">
                    <h2>What is WardenClyffe?</h2>
                    <p>WardenClyffe is an all-in-one server management platform that lets you monitor, manage, and control
                        your entire infrastructure from a single beautiful web dashboard. Whether you have one machine
                        or hundreds, WardenClyffe scales with you.</p>

                    <p>Built entirely in <strong>Rust</strong> for maximum performance and reliability, WardenClyffe
                        installs on any Linux distribution and auto-adapts to your system. It comes with <a
                            href="wardenclyffenet.php">WardenClyffeNet</a> — an encrypted mesh network that connects all your servers
                        automatically, even across different data centres.</p>

                    <img src="images/dashboard-overview.png" alt="WardenClyffe Dashboard"
                        style="width: 100%; border-radius: 10px; margin: 1.5rem 0; border: 1px solid var(--border-color); box-shadow: 0 8px 32px rgba(0,0,0,0.3);">

                    <h3>Key Capabilities</h3>
                    <ul>
                        <li><strong>Real-time monitoring</strong> — CPU, memory, disk, and network metrics with
                            interactive graphs</li>
                        <li><strong>Container management</strong> — Create, clone, migrate, and manage Docker and LXC
                            containers</li>
                        <li><strong>Multi-server clustering</strong> — Manage your entire fleet from one dashboard</li>
                        <li><strong>Proxmox &amp; libvirt integration</strong> — Manages Proxmox VE, libvirt/virsh, or standalone QEMU VMs</li>
                        <li><strong>WardenClyffeRun orchestration</strong> — Schedule and scale containers across nodes,
                            replacing Kubernetes</li>
                        <li><strong>File & config management</strong> — Browse and edit files on any node via the web UI
                        </li>
                        <li><strong>Web terminal</strong> — Full SSH terminal in your browser for any node or container
                        </li>
                        <li><strong>App Store</strong> — 510+ one-click apps across 20 categories</li>
                        <li><strong>Issues Scanner</strong> — AI-powered proactive monitoring for hardware and service
                            issues</li>
                        <li><strong>Alerting</strong> — Discord, Slack, Telegram, and email notifications for threshold
                            breaches</li>
                        <li><strong>Status Pages</strong> — Built-in uptime monitoring with public status pages and incident tracking</li>
                        <li><strong>WardenClyffeFlow automation</strong> — Visual workflow editor with drag-and-drop steps, cron scheduling, and cross-node execution</li>
                        <li><strong>AI Agent</strong> — Ask questions about your infrastructure in natural language</li>
                        <li><strong>Beautiful themes</strong> — Dark, Glass, Midnight, Amber Terminal, and more</li>
                        <li><strong>USB/PCI device passthrough</strong> — Pass through GPUs, NVMe drives, USB devices, and network cards to VMs with IOMMU group detection and conflict checking</li>
                        <li><strong>Docker image update watcher</strong> — Monitors upstream registries for new images, auto-backs up before update, one-click rollback</li>
                    </ul>
                </div>

                <div class="content-section">
                    <h2>⚡ Quick Start</h2>
                    <h3>Step 1: Install prerequisites</h3>
                    <p>Make sure <code>curl</code> and <code>sudo</code> are installed. Most servers have these already &mdash; if not, run as root:</p>
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:8px;margin:10px 0 16px;">
                        <div class="code-block" style="margin:0;"><div class="code-header"><span>Debian / Ubuntu</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div><pre><code>apt install -y sudo curl</code></pre></div>
                        <div class="code-block" style="margin:0;"><div class="code-header"><span>RHEL / Fedora</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div><pre><code>dnf install -y sudo curl</code></pre></div>
                        <div class="code-block" style="margin:0;"><div class="code-header"><span>Arch Linux</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div><pre><code>pacman -S --noconfirm sudo curl</code></pre></div>
                        <div class="code-block" style="margin:0;"><div class="code-header"><span>openSUSE</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div><pre><code>zypper install -y sudo curl</code></pre></div>
                    </div>

                    <h3>Step 2: Install WardenClyffe</h3>
                    <p>Run this on every machine you want to manage:</p>
                    <div class="code-block">
                        <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn"
                                onclick="copyCode(this)">Copy</button></div>
                        <pre><code>curl -sSL https://raw.githubusercontent.com/wardenclyffesoftwaresystemsltd/WardenClyffe/master/setup.sh | sudo bash</code></pre>
                    </div>
                    <p>The installer automatically detects your Linux distribution and installs WardenClyffe as a systemd
                        service.</p>

                    <h3>Step 2: Get the Token</h3>
                    <p>After installation, each server displays its cluster token. You can also retrieve it with:</p>
                    <div class="code-block">
                        <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn"
                                onclick="copyCode(this)">Copy</button></div>
                        <pre><code>wardenclyffe --show-token</code></pre>
                    </div>

                    <h3>Step 3: Open the Web UI</h3>
                    <p>Navigate to <code>http://your-server-ip:8553</code> and log in with your Linux credentials. You
                        only need to connect to <strong>one</strong> server — it manages the rest.</p>

                    <h3>Step 4: Add Your Other Nodes</h3>
                    <p>Click the <strong>+</strong> button in the sidebar to add each server using its token. You can
                        add both WardenClyffe and Proxmox nodes.</p>

                    <h3>Step 5: Connect WardenClyffeNet</h3>
                    <p>Go into your cluster settings and click <strong>🔗 Update WardenClyffeNet Connections</strong> to
                        automatically set up encrypted peer-to-peer networking between all your nodes.</p>

                    <p><strong>That's it!</strong> You now have a fully managed, encrypted cluster. 🐺</p>
                </div>

                <div class="content-section">
                    <h2>System Requirements</h2>
                    <h3>Supported Platforms</h3>
                    <ul>
                        <li>Debian 11, 12, 13 (Trixie)</li>
                        <li>Ubuntu 20.04, 22.04, 24.04, 25.04, 25.10</li>
                        <li>AlmaLinux 8, 9</li>
                        <li>Rocky Linux 8, 9</li>
                        <li>Fedora 38+</li>
                        <li>Arch Linux</li>
                        <li>Proxmox VE 7, 8</li>
                        <li>Any Linux with glibc 2.31+</li>
                    </ul>

                    <h3>Minimum Requirements</h3>
                    <ul>
                        <li><strong>CPU:</strong> 1 core (any architecture supported by Rust)</li>
                        <li><strong>RAM:</strong> 256 MB free</li>
                        <li><strong>Disk:</strong> 50 MB for the binary</li>
                        <li><strong>Network:</strong> One open port (8553 default)</li>
                    </ul>
                </div>

                <div class="content-section">
                    <h2>Dashboard Features</h2>
                    <img src="images/node-detail.png" alt="Node Detail View"
                        style="width: 100%; border-radius: 10px; margin-bottom: 1.5rem; border: 1px solid var(--border-color); box-shadow: 0 8px 32px rgba(0,0,0,0.3);">

                    <h3>Datacenter View</h3>
                    <p>The datacenter view shows a global map of your infrastructure with real-time status for every
                        node. At a glance, see CPU usage, memory consumption, disk space, and uptime for your entire
                        fleet.</p>

                    <h3>Node Detail</h3>
                    <p>Click any node to see detailed metrics including interactive CPU, memory, disk, and network
                        graphs. View running services, manage containers, browse files, and open a web terminal — all
                        from one page.</p>

                    <h3>Themes</h3>
                    <img src="images/settings-themes.png" alt="WardenClyffe Themes"
                        style="width: 100%; border-radius: 10px; margin-bottom: 1rem; border: 1px solid var(--border-color); box-shadow: 0 8px 32px rgba(0,0,0,0.3);">
                    <p>WardenClyffe includes multiple beautiful themes including WardenClyffe Dark, Midnight, Glass
                        (glassmorphism), Amber Terminal, and more. Switch themes from the Settings page.</p>
                </div>

                <div class="content-section">
                    <h2>What's Included</h2>
                    <p>WardenClyffe comes with a suite of integrated tools:</p>
                    <ul>
                        <li><a href="wardenclyffe-containers.php"><strong>Container Management</strong></a> — Docker & LXC
                            with cloning, migration, and resource limits</li>
                        <li><a href="wardenclyffe-storage.php"><strong>Storage Manager</strong></a> — S3/R2, NFS, SSHFS, WardenClyffeDisk
                            mounts from the dashboard</li>
                        <li><a href="wardenclyffe-files.php"><strong>File Manager</strong></a> — Browse, edit, upload, and
                            download files on any node</li>
                        <li><a href="wardenclyffe-networking.php"><strong>Networking</strong></a> — IP management, port
                            forwarding, firewall rules</li>
                        <li><a href="wardenclyffe-clustering.php"><strong>Multi-Server Clustering</strong></a> — Join
                            nodes into clusters with auto-discovery</li>
                        <li><a href="wardenclyffe-mysql.php"><strong>Databases</strong></a> — Unified cluster-wide
                            Database Manager for MariaDB, MySQL, PostgreSQL, and Percona with per-connection routing,
                            enterprise ACL, and AI agent access</li>
                        <li><a href="wardenclyffe-security.php"><strong>Security</strong></a> — Linux PAM authentication,
                            API tokens, audit logging</li>
                        <li><a href="wardenclyffe-certificates.php"><strong>Certificates</strong></a> — SSL/TLS
                            certificate management</li>
                        <li><a href="wardenclyffe-cron.php"><strong>Cron Jobs</strong></a> — Schedule and manage cron
                            tasks on any node</li>
                        <li><a href="wardenclyffeflow.php"><strong>WardenClyffeFlow Automation</strong></a> — Visual workflow
                            automation with drag-and-drop editor, cron scheduling, and cross-node execution</li>
                        <li><a href="wardenclyffe-terminal.php"><strong>Terminal</strong></a> — Full web-based SSH
                            terminal</li>
                        <li><a href="wardenclyffe-issues.php"><strong>Issues Scanner</strong></a> — AI-powered server
                            health monitoring</li>
                        <li><a href="wardenclyffe-alerting.php"><strong>Alerting</strong></a> — Discord, Slack, Telegram,
                            and email notifications</li>
                        <li><a href="wardenclyffe-statuspage.php"><strong>Status Pages</strong></a> — Uptime monitoring
                            with public status pages</li>
                        <li><a href="wardenclyfferun.php"><strong>WardenClyffeRun Orchestration</strong></a> — Schedule, scale, and
                            manage
                            services across your cluster — replaces Kubernetes</li>
                        <li><a href="proxmox.php"><strong>Proxmox Integration</strong></a> — Install on Proxmox to
                            manage
                            VE clusters from the WardenClyffe dashboard</li>
                        <li><a href="app-store.php"><strong>App Store</strong></a> — 510+ one-click apps
                        </li>
                        <li><a href="wardenclyffe-vms.php"><strong>USB/PCI Device Passthrough</strong></a> — Pass GPUs, NVMe, USB devices, and NICs directly to VMs with IOMMU group detection</li>
                        <li><a href="wardenclyffeflow.php"><strong>WardenClyffeFlow Automation Nodes</strong></a> — 16 action types including HTTP requests, Docker image updates, NetBird/TrueNAS/Unifi connectors, and conditional branching</li>
                        <li><a href="wardenclyffe-containers.php"><strong>Docker Image Update Watcher</strong></a> — Checks upstream registries for new images, backup-before-update, one-click rollback</li>
                        <li><a href="wardenclyffe-ai.php"><strong>AI Agent</strong></a> — Natural language infrastructure
                            queries</li>
                        <li><a href="wardenclyffe-settings.php"><strong>Settings</strong></a> — Themes, alerting, Docker
                            registries, node and cluster configuration</li>
                        <li><a href="wardenclyffehost.php"><strong>WardenClyffeHost</strong></a> — Complete web hosting platform with customer management, billing, domains, email, SSL, and white-label portal <span style="font-size:0.65rem;background:rgba(220,38,38,0.15);color:#ef4444;padding:1px 5px;border-radius:3px;margin-left:4px;vertical-align:middle;">Enterprise</span></li>
                    </ul>
                </div>

                <div class="page-nav">
                    <a href="index.php" class="prev">← Home</a>
                    <a href="wardenclyffe-containers.php" class="next">Container Management →</a>
                </div>
            
    </main>
<?php include 'includes/footer.php'; ?>

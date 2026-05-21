<?php
$page_title = '📦 Container Management — WardenClyffe Docs';
$page_desc = 'Create, manage, clone, and migrate Docker & LXC containers across your fleet';
$active = 'wardenclyffe-containers.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Overview</h2>
                <img src="images/screenshots/lxc.png" alt="WardenClyffe container management with card view and pie charts" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WardenClyffe provides comprehensive container management for both <strong>Docker</strong> and <strong>LXC</strong> containers. Create, start, stop, restart, clone, and migrate containers across your entire fleet from a single dashboard.</p>
                <h3>Docker Containers</h3>
                <ul>
                    <li>View all running Docker containers with live CPU, memory, and network stats</li>
                    <li>Start, stop, restart, and remove containers</li>
                    <li>View real-time container logs</li>
                    <li>Open a web terminal (shell) into any container</li>
                    <li>Pull images and create containers from the App Store</li>
                    <li>Manage Docker volumes and networks</li>
                </ul>
                <h3>LXC Containers</h3>
                <ul>
                    <li>Create system containers from downloadable templates</li>
                    <li>Full lifecycle management: start, stop, freeze, unfreeze, destroy</li>
                    <li>Clone containers locally or across nodes</li>
                    <li>Migrate containers between servers in one click</li>
                    <li>Edit container configuration directly</li>
                    <li>Assign WardenClyffeNet IPs for cross-node communication</li>
                    <li>Set CPU, memory, and disk resource limits</li>
                    <li>Autostart containers on boot</li>
                </ul>
            </div>
            <div class="content-section">
                <h2>Creating an LXC Container</h2>
                <p>Click the <strong>Create Container</strong> button on any node page. Choose a distribution template (Debian, Ubuntu, AlmaLinux, Alpine, etc.), set the container name, and optionally configure resources. WardenClyffe handles the rest.</p>
                <h3>Container Networking</h3>
                <p>Each container automatically gets a bridge IP on the <code>lxcbr0</code> bridge. When WardenClyffeNet is enabled, containers also receive a WardenClyffeNet IP (10.10.10.x) for encrypted cross-node communication. The bridge IP matches the WardenClyffeNet last octet for easy identification.</p>
            </div>
            <div class="content-section">
                <h2>LXC Container Features</h2>
                <p>WardenClyffe lets you toggle advanced LXC features from the container's <strong>Settings</strong> page. These are applied to the container configuration and take effect on the next start.</p>
                <table>
                    <thead>
                        <tr><th>Feature</th><th>Description</th><th>Required By</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>TUN/TAP Device</strong></td>
                            <td>Enables <code>/dev/net/tun</code> inside the container for VPN and tunnel support</td>
                            <td>WardenClyffeDisk, Tailscale, WireGuard, OpenVPN</td>
                        </tr>
                        <tr>
                            <td><strong>FUSE</strong></td>
                            <td>Enables <code>/dev/fuse</code> for user-space filesystems</td>
                            <td>WardenClyffeDisk, AppImage, sshfs, rclone mount</td>
                        </tr>
                        <tr>
                            <td><strong>Nesting</strong></td>
                            <td>Run LXC or Docker inside the container</td>
                            <td>Docker-in-LXC, nested containers</td>
                        </tr>
                        <tr>
                            <td><strong>NFS</strong></td>
                            <td>NFS server/client support inside the container</td>
                            <td>NFS shares</td>
                        </tr>
                        <tr>
                            <td><strong>Keyctl</strong></td>
                            <td>Kernel key management for systemd support</td>
                            <td>Some systemd services</td>
                        </tr>
                    </tbody>
                </table>
                <div class="info-box" style="border-left: 4px solid #e74c3c; background: rgba(231, 76, 60, 0.1);">
                    <p>⚠️ <strong>Installing WardenClyffeDisk in a container?</strong> You <strong>must</strong> enable both <strong>TUN/TAP Device</strong> and <strong>FUSE</strong> in the container settings before WardenClyffeDisk will work. If installing via the App Store, these are enabled automatically. After changing settings, stop and start the container for them to take effect.</p>
                </div>
            </div>
            <div class="content-section">
                <h2>Cloning &amp; Migration</h2>
                <p><strong>Clone</strong> creates a copy of a container on the same node. <strong>Migrate</strong> moves a container to a different node in the cluster — WardenClyffe handles the file transfer, IP reassignment, and route configuration automatically.</p>
            </div>

            <div class="content-section">
                <h2>Card View</h2>
                <p>Every container and VM screen supports a <strong>card view</strong> as an alternative to the default table view. Toggle between the two layouts using the view switcher at the top of the page &mdash; your preference is saved per screen.</p>
                <ul>
                    <li><strong>SVG pie charts</strong> &mdash; Each card displays live CPU, RAM, and disk usage as small SVG pie charts for at-a-glance resource monitoring</li>
                    <li><strong>Responsive layout</strong> &mdash; 4 cards across on desktop, 2 on tablet, 1 on mobile</li>
                    <li><strong>Available everywhere</strong> &mdash; Card view works on Docker, LXC, and VM screens</li>
                    <li><strong>Per-screen preference</strong> &mdash; Switch the Docker screen to cards while keeping LXC as a table, or vice versa &mdash; each screen remembers its own setting</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Additional Features</h2>

                <h3>Container Cron Jobs</h3>
                <p>WardenClyffe lets you manage cron jobs inside individual Docker and LXC containers directly from the dashboard. View, create, edit, and delete crontab entries without needing to shell into the container.</p>

                <h3>Update Checks</h3>
                <p>For Docker containers, WardenClyffe can check whether a newer version of the container&rsquo;s image is available and apply updates from the dashboard.</p>

                <h3>Docker Image Management</h3>
                <ul>
                    <li>View all pulled Docker images on a node</li>
                    <li>Pull new images from Docker Hub or private registries</li>
                    <li>Search Docker Hub for images</li>
                    <li>Remove unused images</li>
                </ul>

                <h3>Docker Network Management</h3>
                <ul>
                    <li>View Docker networks on each node</li>
                    <li>Inspect network configuration and connected containers</li>
                </ul>
            </div>

<div class="page-nav"><a href="wardenclyffe.php" class="prev">&larr; Overview</a><a href="wardenclyffe-storage.php" class="next">Storage Manager &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

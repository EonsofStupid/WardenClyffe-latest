<?php
$page_title = '🥽 VR Server Room — WolfStack Docs';
$page_desc = 'Walk through your infrastructure in virtual reality. The 3D Server Room renders your entire cluster as a virtual data centre with live status LEDs, interactive terminals, and VR headset support.';
$active = 'wolfstack-vr.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">

            <div class="content-section">
                <h2>Overview</h2>
                <p>WolfStack&rsquo;s 3D Server Room is an interactive virtual data centre that visualises your entire infrastructure as physical server racks you can walk through, inspect, and manage &mdash; both on a regular screen and in <strong>virtual reality</strong> with a VR headset.</p>
                <p>Every node in your cluster is rendered as a server rack. Each rack contains individual server units representing the host machine and every Docker container, LXC container, and VM running on it. Status LEDs, container names, and cluster groupings update in real time.</p>

                <div style="margin:24px 0;border-radius:12px;overflow:hidden;border:1px solid var(--border-color);aspect-ratio:16/9;">
                    <iframe width="100%" height="100%" src="https://www.youtube.com/embed/QHoaes2mzPU" title="WolfStack VR Server Room" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen style="display:block;"></iframe>
                </div>
            </div>

            <div class="content-section">
                <h2>Desktop Mode</h2>
                <p>The 3D Server Room works in any modern browser without a VR headset. Click the <strong>🖥️</strong> icon in the sidebar to open it.</p>

                <h3>Navigation</h3>
                <table>
                    <thead>
                        <tr><th>Control</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Mouse drag</strong></td><td>Orbit the camera around the server room</td></tr>
                        <tr><td><strong>Scroll wheel</strong></td><td>Zoom in and out</td></tr>
                        <tr><td><strong>WASD / Arrow keys</strong></td><td>Walk forward, back, left, right</td></tr>
                        <tr><td><strong>Click a rack</strong></td><td>Show server info panel with CPU, memory, disk, and container list</td></tr>
                        <tr><td><strong>Double-click a rack</strong></td><td>Navigate to the node&rsquo;s dashboard</td></tr>
                    </tbody>
                </table>

                <h3>Info Panel</h3>
                <p>Click any server rack to see:</p>
                <ul>
                    <li>Hostname and IP address</li>
                    <li>Online/offline status</li>
                    <li>CPU, memory, and disk usage percentages</li>
                    <li>Full list of Docker containers, LXC containers, and VMs with running state</li>
                    <li><strong>Terminal</strong> button &mdash; open a console to the node or any container</li>
                    <li><strong>Dashboard</strong> button &mdash; jump to the node&rsquo;s management page</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>VR Mode</h2>
                <p>If you have a VR headset (Meta Quest, Valve Index, HTC Vive, or any WebXR-compatible device), click the <strong>Enter VR</strong> button to step into your server room.</p>

                <h3>Requirements</h3>
                <ul>
                    <li>A WebXR-compatible VR headset</li>
                    <li>A browser that supports WebXR (Meta Quest Browser, Chrome, Firefox)</li>
                    <li>HTTPS connection (WebXR requires a secure context)</li>
                </ul>

                <h3>VR Controls</h3>
                <table>
                    <thead>
                        <tr><th>Control</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Thumbsticks</strong></td><td>Walk around the server room</td></tr>
                        <tr><td><strong>Laser pointer</strong></td><td>Red laser beam from each controller for aiming</td></tr>
                        <tr><td><strong>Trigger (on server unit)</strong></td><td>Show info panel for that container/VM</td></tr>
                        <tr><td><strong>Trigger (on info panel button)</strong></td><td>Open terminal or navigate to dashboard</td></tr>
                        <tr><td><strong>Trigger (on keyboard)</strong></td><td>Press virtual keyboard keys in the terminal</td></tr>
                        <tr><td><strong>Trigger (on empty space)</strong></td><td>Dismiss the info panel</td></tr>
                    </tbody>
                </table>

                <h3>In-Scene Terminal</h3>
                <p>When you open a terminal in VR, a floating screen appears in front of you showing a live terminal session. A virtual QWERTY keyboard floats below the screen &mdash; point at keys with the laser and pull the trigger to type. The terminal connects directly to the selected host, Docker container, or LXC container.</p>
                <ul>
                    <li>Full QWERTY layout with shift, caps lock, backspace, tab, enter, escape</li>
                    <li>Keys flash red when pressed</li>
                    <li>Red close button (X) in the top-right corner</li>
                    <li>Only one terminal open at a time &mdash; opening a new one closes the previous</li>
                </ul>

                <h3>VNC Console (VMs)</h3>
                <p>QEMU virtual machines open a graphical VNC console instead of a text terminal. The VNC output is rendered directly in the VR scene via a hidden iframe capturing the noVNC client&rsquo;s canvas.</p>
            </div>

            <div class="content-section">
                <h2>Server Rack Layout</h2>
                <p>Racks are arranged in rows grouped by cluster. Each rack represents one node in your infrastructure:</p>

                <table>
                    <thead>
                        <tr><th>Position</th><th>What It Represents</th><th>Visual Indicators</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Top server (HOST)</strong></td>
                            <td>The physical/virtual host machine</td>
                            <td>Slightly taller, brighter panel, cluster-colour accent stripe</td>
                        </tr>
                        <tr>
                            <td><strong>Below: Docker containers</strong></td>
                            <td>One server unit per Docker container</td>
                            <td>Blue type indicator, container name label</td>
                        </tr>
                        <tr>
                            <td><strong>Below: LXC containers</strong></td>
                            <td>One server unit per LXC container</td>
                            <td>Green type indicator, container name label</td>
                        </tr>
                        <tr>
                            <td><strong>Bottom: VMs</strong></td>
                            <td>One server unit per virtual machine</td>
                            <td>Amber type indicator, VM name label</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Status LEDs</h2>
                <p>Each server unit displays three LED indicators on its front panel showing real-time resource usage:</p>

                <table>
                    <thead>
                        <tr><th>LED</th><th>Metric</th><th>Green</th><th>Amber</th><th>Red</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>1st (left)</strong></td>
                            <td>CPU</td>
                            <td>&lt; 50%</td>
                            <td>50 &ndash; 80%</td>
                            <td>&gt; 80%</td>
                        </tr>
                        <tr>
                            <td><strong>2nd (middle)</strong></td>
                            <td>Memory</td>
                            <td>&lt; 70%</td>
                            <td>70 &ndash; 90%</td>
                            <td>&gt; 90%</td>
                        </tr>
                        <tr>
                            <td><strong>3rd (right)</strong></td>
                            <td>Storage</td>
                            <td>&lt; 70%</td>
                            <td>70 &ndash; 90%</td>
                            <td>&gt; 90%</td>
                        </tr>
                    </tbody>
                </table>

                <p>LEDs only appear when real metrics data is available. Offline servers show a single dim red LED. A pulsing red <strong>!</strong> warning appears above any rack with critical metrics.</p>
                <p>Container LEDs show per-container CPU and memory usage from Docker/LXC stats. Host LEDs show node-level metrics. Stats refresh every 60 seconds.</p>
            </div>

            <div class="content-section">
                <h2>Cluster Visualisation</h2>
                <ul>
                    <li>Racks are grouped by cluster with colour-coded accent strips (red, blue, green, amber, etc.)</li>
                    <li>Cluster name labels float above each group of racks</li>
                    <li>Server hostnames are displayed vertically on the side of each rack</li>
                    <li>Supports mixed WolfStack and Proxmox clusters in the same view</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Features</h2>
                <ul>
                    <li><strong>Real-time updates</strong> &mdash; Node status, container counts, and metrics refresh automatically</li>
                    <li><strong>Container names</strong> &mdash; Lazy-loaded from all nodes on scene load, showing real Docker/LXC/VM names</li>
                    <li><strong>Proxmox support</strong> &mdash; PVE guests fetched via the Proxmox API with per-guest CPU/memory/disk stats</li>
                    <li><strong>VR Ready indicator</strong> &mdash; HUD shows whether a VR headset is detected</li>
                    <li><strong>Responsive</strong> &mdash; Resizes when the sidebar is toggled</li>
                    <li><strong>Dark atmosphere</strong> &mdash; Moody server room lighting with fog, shadows, and reflective floor</li>
                    <li><strong>Toggle labels</strong> &mdash; Show or hide hostname and cluster labels</li>
                    <li><strong>Auto-rotate</strong> &mdash; Optional camera rotation (off by default)</li>
                </ul>
            </div>

<div class="page-nav"><a href="wolfstack-wolfnote.php" class="prev">&larr; WolfNote Integration</a><a href="wolfdisk.php" class="next">WolfDisk &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

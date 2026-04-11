<?php
$page_title = '📝 WolfNote Integration — WolfStack Docs';
$page_desc = 'Connect WolfStack to WolfNote for automatic infrastructure documentation — AI-generated notes, event logging, incident reports, backup logs, and one-click page captures';
$page_keywords = 'wolfstack, wolfnote, integration, notes, documentation, infrastructure, sysadmin, self-hosted';
$active = 'wolfstack-wolfnote.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">

            <div class="content-section">
                <h1>WolfNote Integration</h1>
                <img src="images/screenshots/wolfflow.png" alt="WolfStack WolfNote documentation system" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WolfStack integrates directly with <a href="https://wolfnote.org" target="_blank" rel="noopener">WolfNote</a>, a self-hosted note-taking app by Wolf Software Systems. Connect your WolfNote account and WolfStack will automatically document your infrastructure &mdash; AI-generated reports, event logs, incident notes, backup results, and one-click captures of any dashboard view.</p>

                <div class="info-box"><strong>Requires WolfNote:</strong> You need a WolfNote account at <a href="https://app.wolfnote.org" target="_blank" rel="noopener">app.wolfnote.org</a> (or a self-hosted instance). WolfStack authenticates with your WolfNote credentials and stores a session token locally.</div>
            </div>

            <!-- Video demo -->
            <div class="content-section">
                <h2>Video Walkthrough</h2>
                <p>See the WolfStack &times; WolfNote integration in action:</p>
                <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:10px;margin:20px 0;">
                    <iframe src="https://www.youtube.com/embed/vKzO5t4XpFQ" title="WolfStack &times; WolfNote Integration" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen style="position:absolute;top:0;left:0;width:100%;height:100%;border-radius:10px;"></iframe>
                </div>
            </div>

            <!-- Connecting -->
            <div class="content-section">
                <h2>Connecting to WolfNote</h2>
                <ol>
                    <li>In the WolfStack dashboard, go to <strong>Settings &rarr; WolfNote</strong></li>
                    <li>Enter your WolfNote instance URL (defaults to <code>https://app.wolfnote.org</code>)</li>
                    <li>Enter your WolfNote company name, username, and password</li>
                    <li>Click <strong>Connect</strong> &mdash; WolfStack authenticates and stores a JWT token locally</li>
                </ol>
                <p>Once connected, you&rsquo;ll see your WolfNote username, a list of your folders and recent notes, and toggles for each integration feature.</p>
                <div class="info-box"><strong>Self-hosted WolfNote:</strong> If you run your own WolfNote instance, change the URL field to your instance address (e.g. <code>https://notes.internal:3000</code>).</div>
            </div>

            <!-- Feature toggles -->
            <div class="content-section">
                <h2>Integration Features</h2>
                <p>Each feature can be independently toggled on or off, and each has its own folder selector so notes are organised exactly how you want them in WolfNote.</p>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr><th>Feature</th><th>What It Does</th><th>Default</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>AI Note Creation</strong></td>
                                <td>The AI Agent can create notes in WolfNote when answering infrastructure queries &mdash; ask it to &ldquo;write a note about the current disk usage&rdquo; and it saves directly to your chosen folder</td>
                                <td>On</td>
                            </tr>
                            <tr>
                                <td><strong>Event Logging</strong></td>
                                <td>Automatically log server events (node join/leave, service restarts, config changes) as timestamped notes</td>
                                <td>Off</td>
                            </tr>
                            <tr>
                                <td><strong>Incident Notes</strong></td>
                                <td>When a <a href="wolfstack-statuspage.php">Status Page</a> incident is created or resolved, a note is automatically added with full incident details</td>
                                <td>Off</td>
                            </tr>
                            <tr>
                                <td><strong>Backup Logs</strong></td>
                                <td>Each <a href="wolfstack-backups.php">backup</a> run (success or failure) is logged as a note with target, destination, size, and duration</td>
                                <td>Off</td>
                            </tr>
                            <tr>
                                <td><strong>Alert Notes</strong></td>
                                <td>When an <a href="wolfstack-alerting.php">alert</a> fires or clears, a note is created with the metric value, threshold, and affected node</td>
                                <td>Off</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- One-click capture -->
            <div class="content-section">
                <h2>One-Click Page Capture</h2>
                <p>When connected to WolfNote, a <strong>Save to WolfNote</strong> button appears in the WolfStack top bar. Click it from any dashboard view to instantly capture the current page as a clean, professional sysadmin report in WolfNote.</p>

                <h3>What Gets Captured</h3>
                <ul>
                    <li><strong>Tables</strong> &mdash; resource usage, container lists, storage status, and any other tabular data on the page</li>
                    <li><strong>Charts &amp; metrics</strong> &mdash; CPU, memory, disk, and network graphs are captured as summary data</li>
                    <li><strong>Layout sections</strong> &mdash; div-based card layouts (storage pools, network interfaces, etc.) are extracted cleanly</li>
                    <li><strong>Page context</strong> &mdash; the note title includes the node name and view name so you know exactly what was captured</li>
                </ul>
                <p>Captured notes are saved to the AI Notes folder (configurable in Settings) and open directly in WolfNote when clicked.</p>
            </div>

            <!-- Folder management -->
            <div class="content-section">
                <h2>Folder Management</h2>
                <p>From the WolfNote settings panel in WolfStack, you can:</p>
                <ul>
                    <li><strong>Browse folders</strong> &mdash; see your full WolfNote folder hierarchy with colour indicators</li>
                    <li><strong>Create folders</strong> &mdash; add new folders with custom colours directly from WolfStack</li>
                    <li><strong>Assign per-feature folders</strong> &mdash; choose which folder receives notes for each feature (AI, events, incidents, backups, alerts)</li>
                    <li><strong>Quick notes</strong> &mdash; create ad-hoc notes with a title, content, and folder from the settings panel</li>
                </ul>
            </div>

            <!-- How it works -->
            <div class="content-section">
                <h2>How It Works</h2>
                <p>WolfStack acts as a proxy between your browser and WolfNote:</p>
                <ol>
                    <li><strong>Authentication</strong> &mdash; WolfStack sends your credentials to WolfNote&rsquo;s <code>/api/auth/login</code> endpoint and stores the returned JWT token in <code>/etc/wolfstack/wolfnote.json</code></li>
                    <li><strong>API proxy</strong> &mdash; all WolfNote API calls from the frontend go through WolfStack&rsquo;s backend, which adds the JWT token and forwards to the WolfNote instance. This avoids CORS issues and keeps the token out of the browser.</li>
                    <li><strong>Auto-creation</strong> &mdash; background tasks (backups, alerts, status page monitors) create notes via the same API client when their respective feature toggle is enabled</li>
                </ol>
                <p>Config is stored at <code>/etc/wolfstack/wolfnote.json</code> and synced across cluster nodes like all other WolfStack configuration.</p>
            </div>

<div class="page-nav"><a href="wolfstack-ai.php" class="prev">&larr; AI Agent</a><a href="wolfstack-vr.php" class="next">VR Server Room &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

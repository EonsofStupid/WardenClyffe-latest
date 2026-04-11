<?php
$page_title = '🔔 Alerting &amp; Notifications — WolfStack Docs';
$page_desc = 'Discord, Slack, Telegram, and email notifications for infrastructure events';
$active = 'wolfstack-alerting.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Overview</h2>
                <img src="images/screenshots/issues.png" alt="WolfStack alerting and issue detection" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WolfStack sends alerts when resource thresholds are exceeded or critical events occur. Configure alert rules and notification channels from the Settings page.</p>

                <h3>Notification Channels</h3>
                <table>
                    <thead>
                        <tr><th>Channel</th><th>Method</th><th>Configuration</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Discord</strong></td>
                            <td>Webhook</td>
                            <td>Paste your Discord webhook URL</td>
                        </tr>
                        <tr>
                            <td><strong>Slack</strong></td>
                            <td>Webhook</td>
                            <td>Paste your Slack incoming webhook URL</td>
                        </tr>
                        <tr>
                            <td><strong>Telegram</strong></td>
                            <td>Bot API</td>
                            <td>Bot token + chat ID</td>
                        </tr>
                        <tr>
                            <td><strong>Email</strong></td>
                            <td>SMTP</td>
                            <td>SMTP server, credentials, and recipient address</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Alert Types</h2>
                <h3>System Thresholds</h3>
                <ul>
                    <li><strong>CPU usage</strong> &mdash; Alert when CPU exceeds a configurable percentage</li>
                    <li><strong>Memory usage</strong> &mdash; Alert when RAM exceeds a configurable percentage</li>
                    <li><strong>Disk usage</strong> &mdash; Alert when disk space exceeds a configurable percentage</li>
                    <li><strong>Swap usage</strong> &mdash; Alert on excessive swap usage</li>
                    <li><strong>Load average</strong> &mdash; Alert on high system load</li>
                </ul>

                <h3>Container Alerts</h3>
                <ul>
                    <li><strong>Container memory</strong> &mdash; Per-container memory thresholds for Docker and LXC</li>
                    <li><strong>Container stopped</strong> &mdash; Alert when a monitored container stops unexpectedly</li>
                </ul>

                <h3>Infrastructure Events</h3>
                <ul>
                    <li><strong>Node offline</strong> &mdash; Alert when a cluster node becomes unreachable</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Behaviour</h2>
                <ul>
                    <li>Alert checks run every <strong>60 seconds</strong> (configurable)</li>
                    <li>A <strong>15-minute cooldown</strong> prevents repeated alerts for the same threshold breach</li>
                    <li>In a cluster, one node is automatically elected as the <strong>primary alerter</strong> to avoid duplicate notifications</li>
                    <li>Use the <strong>Test</strong> button to verify your notification channels before enabling alerts</li>
                </ul>

                <h3>Configuration</h3>
                <p>Go to <strong>Settings &rarr; Alerting</strong> to configure webhook URLs, email settings, and thresholds.</p>
            </div>

<div class="page-nav"><a href="wolfstack-issues.php" class="prev">&larr; Issues Scanner</a><a href="wolfstack-statuspage.php" class="next">Status Pages &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

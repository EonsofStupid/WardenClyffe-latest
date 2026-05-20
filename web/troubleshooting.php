<?php
$page_title = '🔍 Troubleshooting — WardenClyffe Docs';
$page_desc = 'Common issues and solutions for WardenClyffeScale and WardenClyffe';
$active = 'troubleshooting.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Common Issues</h2>
                <h3>WardenClyffeScale won&rsquo;t connect to peers</h3>
                <ul>
                    <li>Ensure WardenClyffeNet is running: <code>systemctl status wardenclyffenet</code></li>
                    <li>Check that ports are open between nodes</li>
                    <li>Verify the cluster name matches in config.toml</li>
                </ul>
                <h3>Replication lag is high</h3>
                <ul>
                    <li>Check network latency between nodes</li>
                    <li>Ensure the database isn&rsquo;t under heavy write load</li>
                    <li>Verify binlog format is set to ROW</li>
                </ul>
                <h3>WardenClyffe dashboard not loading</h3>
                <ul>
                    <li>Check the service: <code>systemctl status wardenclyffe</code></li>
                    <li>Verify port 8553 is accessible</li>
                    <li>Check logs: <code>journalctl -u wardenclyffe -f</code></li>
                </ul>
                <h3>Containers can&rsquo;t ping across nodes</h3>
                <ul>
                    <li>Ensure WardenClyffeNet is running on all nodes</li>
                    <li>Check routes: <code>ip route | grep 10.10.10</code></li>
                    <li>Restart WardenClyffe: <code>systemctl restart wardenclyffe</code></li>
                    <li>Update WardenClyffeNet connections from cluster settings</li>
                </ul>
            </div>

<div class="page-nav"><a href="cli.php" class="prev">&larr; CLI Reference</a><a href="index.php" class="next">Home &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

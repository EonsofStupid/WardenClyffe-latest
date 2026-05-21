<?php
$page_title = '🔍 Troubleshooting — WardenClyffe Docs';
$page_desc = 'Common issues and solutions for WardenClyffe server management';
$active = 'wardenclyffe-troubleshooting.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>WardenClyffe Troubleshooting</h2>
                <p>Common issues and solutions for WardenClyffe server management. For WardenClyffeScale database replication issues, see <a href="troubleshooting.php">WardenClyffeScale Troubleshooting</a>.</p>

                <h3>Dashboard not loading</h3>
                <ul>
                    <li>Check the service: <code>systemctl status wardenclyffe</code></li>
                    <li>Verify port 8553 is accessible</li>
                    <li>Check logs: <code>journalctl -u wardenclyffe -f</code></li>
                </ul>

                <h3>Nodes added but not seeing each other</h3>
                <p>After adding nodes to a cluster, they may not appear connected or may show as offline. This is normal &mdash; you need to update WardenClyffeNet connections:</p>
                <ol>
                    <li>Click on your cluster name in the sidebar</li>
                    <li>Go to <strong>Settings</strong></li>
                    <li>Click <strong>Update WardenClyffeNet Connections</strong></li>
                </ol>
                <p>This establishes encrypted networking between all nodes and propagates the configuration to every node in the cluster. See <a href="wardenclyffe-clustering.php">Clustering</a> for more details.</p>

                <h3>Default cluster is called &ldquo;WardenClyffe&rdquo;</h3>
                <p>This is expected. When you install WardenClyffe on the first node, it automatically creates a cluster named &ldquo;WardenClyffe&rdquo;. This is just a virtual label &mdash; it does not affect functionality. To rename it:</p>
                <ol>
                    <li>Click on the cluster name in the sidebar</li>
                    <li>Go to <strong>Settings</strong></li>
                    <li>Edit the <strong>Cluster Name</strong> and save</li>
                </ol>
                <p>You can then add your other nodes to this cluster as normal.</p>

                <h3>Not sure which node type to select when adding a node</h3>
                <p>It doesn&rsquo;t matter. WardenClyffe automatically detects whether a node is running Proxmox and adjusts accordingly. The type field is just a label and can be changed at any time. Simply add the node and WardenClyffe will configure it correctly.</p>

                <h3>Containers can&rsquo;t ping across nodes</h3>
                <ul>
                    <li>Ensure WardenClyffeNet is running on all nodes: <code>systemctl status wardenclyffenet</code></li>
                    <li>Check routes: <code>ip route | grep 10.10.10</code></li>
                    <li>Restart WardenClyffe: <code>systemctl restart wardenclyffe</code></li>
                    <li>Update WardenClyffeNet connections from <strong>Cluster &rarr; Settings</strong></li>
                </ul>
            </div>

<div class="page-nav"><a href="wardenclyffe-settings.php" class="prev">&larr; Settings</a><a href="index.php" class="next">Home &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

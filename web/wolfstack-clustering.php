<?php
$page_title = '🔗 Multi-Server Clustering — WolfStack Docs';
$page_desc = 'Join servers into managed clusters with automatic discovery and fleet-wide management';
$active = 'wolfstack-clustering.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Overview</h2>
                <img src="images/screenshots/topology.png" alt="WolfStack cluster topology with connected nodes" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WolfStack&rsquo;s clustering feature lets you group servers into logical clusters and manage them as a single unit. Add WolfStack nodes, Proxmox servers, or a mix of both.</p>
                <h3>How It Works</h3>
                <ol>
                    <li>Install WolfStack on each server you want to manage</li>
                    <li>Log in to any one server&rsquo;s web UI</li>
                    <li>Click <strong>+</strong> to add nodes by entering their join token</li>
                    <li>Click <strong>🔗 Update WolfNet Connections</strong> to set up encrypted networking</li>
                </ol>
                <h3>Features</h3>
                <ul>
                    <li>Multiple clusters in one dashboard</li>
                    <li>Fleet-wide metrics and health monitoring</li>
                    <li>Cross-cluster container migration</li>
                    <li>Centralised settings and configuration</li>
                    <li>Node health checks with automatic offline detection</li>
                    <li>Per-cluster WolfNet configuration</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Default Cluster Name</h2>
                <p>When you install WolfStack on your first node, it automatically creates a cluster called <strong>&ldquo;Wolfstack&rdquo;</strong>. This is just a virtual label &mdash; it does not affect functionality in any way. You can rename it to anything you like:</p>
                <ol>
                    <li>Click on your cluster name in the sidebar</li>
                    <li>Go to <strong>Settings</strong></li>
                    <li>Edit the <strong>Cluster Name</strong> field and save</li>
                </ol>
                <p>All your nodes will continue to work exactly the same regardless of the cluster name.</p>
            </div>

            <div class="content-section">
                <h2>Adding Nodes</h2>
                <h3>Node Type Is Auto-Detected</h3>
                <p>When adding a new node to your cluster, you may be asked to select a node type (e.g. WolfStack or Proxmox). <strong>Don&rsquo;t worry about which type you choose</strong> &mdash; WolfStack automatically detects whether a node is running Proxmox and adjusts accordingly. The type is just a label and can be changed at any time.</p>

                <h3>Nodes Not Seeing Each Other?</h3>
                <p>After adding nodes to a cluster, they may not immediately see each other over WolfNet. To fix this:</p>
                <ol>
                    <li>Click on your cluster name in the sidebar</li>
                    <li>Go to <strong>Settings</strong></li>
                    <li>Click <strong>Update WolfNet Connections</strong></li>
                </ol>
                <p>This tells all nodes in the cluster about each other and establishes encrypted WolfNet connections between them. It will also propagate to all other nodes automatically. You only need to do this once after adding new nodes &mdash; if all your nodes are on the same local network, they will discover each other immediately.</p>
            </div>

<div class="page-nav"><a href="wolfstack-networking.php" class="prev">&larr; Networking</a><a href="wolfstack-mysql.php" class="next">Databases &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

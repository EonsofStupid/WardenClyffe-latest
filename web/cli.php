<?php
$page_title = '💻 WardenClyffeScale CLI Reference — WardenClyffe Docs';
$page_desc = 'Command-line interface reference for WardenClyffeScale';
$active = 'cli.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>CLI Commands</h2>
                <h3>wardenclyffescale start</h3>
                <p>Start the WardenClyffeScale replication agent.</p>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code>wardenclyffescale start</code></pre>
                </div>
                <h3>wardenclyffescale status</h3>
                <p>Show replication status and peer connections.</p>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code>wardenclyffescale status</code></pre>
                </div>
                <h3>wardenclyffescale peers</h3>
                <p>List connected peers and their replication lag.</p>
                <h3>wardenclyffescale failover</h3>
                <p>Manually trigger a failover to a specific node.</p>
            </div>

<div class="page-nav"><a href="performance.php" class="prev">&larr; Performance</a><a href="troubleshooting.php" class="next">Troubleshooting &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

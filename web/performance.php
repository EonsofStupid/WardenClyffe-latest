<?php
$page_title = '🚀 WardenClyffeScale Performance — WardenClyffe Docs';
$page_desc = 'Performance tuning and benchmarks for WardenClyffeScale';
$active = 'performance.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Performance</h2>
                <p>WardenClyffeScale is built in Rust for maximum performance. The replication engine is asynchronous and non-blocking, with minimal overhead on the database.</p>
                <h3>Benchmarks</h3>
                <ul>
                    <li>Replication lag: &lt; 100ms typical</li>
                    <li>Throughput: 10,000+ transactions/second</li>
                    <li>Memory usage: ~50MB base</li>
                    <li>CPU overhead: &lt; 1% on modern hardware</li>
                </ul>
                <h3>Tuning Tips</h3>
                <ul>
                    <li>Use ROW-based binlog format for best performance</li>
                    <li>Enable GTID mode for efficient failover</li>
                    <li>Place WardenClyffeScale nodes close to their database for low latency</li>
                    <li>Use WardenClyffeNet for encrypted inter-node communication</li>
                </ul>
            </div>

<div class="page-nav"><a href="configuration.php" class="prev">&larr; Configuration</a><a href="cli.php" class="next">CLI Reference &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

<?php
$page_title = '⚡ WardenClyffeScale Quick Start — WardenClyffe Docs';
$page_desc = 'Install and configure WardenClyffeScale database replication in minutes';
$active = 'quickstart.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Installation</h2>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code>curl -sSL https://raw.githubusercontent.com/wardenclyffesoftwaresystemsltd/WardenClyffeScale/master/setup.sh | sudo bash</code></pre>
                </div>
                <h3>Configuration</h3>
                <p>Edit <code>/etc/wardenclyffescale/config.toml</code> with your database connection details:</p>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">toml</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code>[database]
host = "127.0.0.1"
port = 3306
user = "replicator"
password = "your-password"
database = "mydb"

[cluster]
name = "production"
node_id = "node-1"</code></pre>
                </div>
                <h3>Start Replicating</h3>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code>sudo systemctl start wardenclyffescale</code></pre>
                </div>
                <p>Repeat on each node you want to replicate to. WardenClyffeScale automatically discovers peers and begins synchronisation.</p>
            </div>

<div class="page-nav"><a href="index.php" class="prev">&larr; Home</a><a href="features.php" class="next">Features &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

<?php
$page_title = '🌍 WardenClyffeServe — WardenClyffe Docs';
$page_desc = 'Apache2-compatible web server with PHP FastCGI and Rust FFI bridge';
$active = 'wardenclyffeserve.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Overview</h2>
                <p>WardenClyffeServe is an Apache2-compatible web server that serves PHP via FastCGI. It reads your existing Apache vhost configs directly, making migration seamless.</p>
                <h3>Features</h3>
                <ul>
                    <li><strong>Drop-in Apache2 replacement</strong> &mdash; Reads vhost configs</li>
                    <li><strong>PHP via FastCGI</strong> (php-fpm)</li>
                    <li><strong>Rust FFI bridge</strong> for calling Rust functions from PHP</li>
                    <li><strong>Shared sessions</strong> across multiple servers</li>
                    <li><strong>Admin dashboard</strong></li>
                    <li><strong>Static file serving</strong> with caching</li>
                    <li><strong>Virtual hosts</strong> with SNI support</li>
                </ul>
                <h3>Installation</h3>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code># Stop Apache2 first
sudo systemctl stop apache2
# Install and start WardenClyffeServe
curl -sSL https://raw.githubusercontent.com/wardenclyffesoftwaresystemsltd/WardenClyffeScale/master/wardenclyffeserve/install.sh | sudo bash</code></pre>
                </div>
            </div>

<div class="page-nav"><a href="wardenclyffeproxy.php" class="prev">&larr; WardenClyffeProxy</a><a href="about.php" class="next">About &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

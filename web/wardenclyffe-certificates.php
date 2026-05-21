<?php
$page_title = '🔐 Certificates — WardenClyffe Docs';
$page_desc = 'SSL/TLS certificate management for your infrastructure';
$active = 'wardenclyffe-certificates.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Overview</h2>
                <img src="images/screenshots/certificates.png" alt="WardenClyffe SSL certificate management" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WardenClyffe includes tools for managing SSL/TLS certificates across your nodes. Generate self-signed certificates, request Let&rsquo;s Encrypt certificates, or upload your own.</p>
                <h3>Features</h3>
                <ul>
                    <li>View installed certificates with expiry dates</li>
                    <li>Generate self-signed certificates for development</li>
                    <li>Upload custom certificates and keys</li>
                    <li>Certificate expiry monitoring</li>
                </ul>
            </div>

<div class="page-nav"><a href="wardenclyffe-security.php" class="prev">&larr; Security</a><a href="wardenclyffe-cron.php" class="next">Cron Jobs &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

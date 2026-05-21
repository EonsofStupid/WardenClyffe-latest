<?php
$page_title = '💾 Storage Manager — WardenClyffe Docs';
$page_desc = 'Manage S3/R2, NFS, SSHFS, and WardenClyffeDisk storage mounts from the dashboard';
$active = 'wardenclyffe-storage.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Overview</h2>
                <img src="images/screenshots/storage.png" alt="WardenClyffe storage mount management — S3, NFS, SSHFS" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WardenClyffe&rsquo;s Storage Manager lets you mount and manage remote storage directly from the dashboard.</p>

                <h3>Supported Storage Types</h3>
                <table>
                    <thead>
                        <tr><th>Type</th><th>Description</th><th>Use Case</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>S3/R2 Object Storage</strong></td>
                            <td>Mount buckets from AWS S3, Cloudflare R2, MinIO, Wasabi, Backblaze B2, or any S3-compatible provider. Pure Rust implementation &mdash; works on all architectures including IBM Power (ppc64le).</td>
                            <td>Cloud backups, media storage, offsite archives</td>
                        </tr>
                        <tr>
                            <td><strong>NFS Shares</strong></td>
                            <td>Mount NFS exports from your network</td>
                            <td>Shared storage between servers, NAS access</td>
                        </tr>
                        <tr>
                            <td><strong>SSHFS</strong></td>
                            <td>Mount remote directories over SSH using SFTP</td>
                            <td>Secure remote file access without NFS</td>
                        </tr>
                        <tr>
                            <td><strong>Directory</strong></td>
                            <td>Bind-mount a local directory to another path</td>
                            <td>Path aliasing, container storage paths</td>
                        </tr>
                        <tr>
                            <td><strong>WardenClyffeDisk</strong></td>
                            <td>Mount a <a href="wardenclyffedisk.php">WardenClyffeDisk</a> distributed filesystem drive</td>
                            <td>Replicated shared storage across your cluster</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Features</h2>
                <ul>
                    <li>View storage usage and capacity at a glance</li>
                    <li>Browse files in mounted storage via the <a href="wardenclyffe-files.php">File Manager</a></li>
                    <li>Configure credentials and mount points from the web UI</li>
                    <li>Auto-mount on boot</li>
                    <li>S3 sync operations for syncing local directories with S3 buckets</li>
                    <li>Import storage configurations from <code>rclone.conf</code></li>
                    <li>Replicate storage mount configurations across all cluster nodes</li>
                </ul>
            </div>

<div class="page-nav"><a href="wardenclyffe-containers.php" class="prev">&larr; Containers</a><a href="wardenclyffe-files.php" class="next">File Manager &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

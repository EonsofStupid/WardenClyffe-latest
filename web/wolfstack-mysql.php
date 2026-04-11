<?php
$page_title = '🗄️ Database Editor — WolfStack Docs';
$page_desc = 'Browse tables, run queries, and manage MySQL, MariaDB, PostgreSQL, and Percona databases from the web dashboard';
$active = 'wolfstack-mysql.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Overview</h2>
                <img src="images/screenshots/mysql-editor.png" alt="WolfStack MySQL/MariaDB database editor" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WolfStack includes a built-in database editor that lets you browse tables, run queries, and manage databases on any node directly from the web dashboard.</p>

                <h3>Supported Databases</h3>
                <table>
                    <thead>
                        <tr><th>Database</th><th>Connection</th><th>Notes</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>MariaDB</strong></td>
                            <td>TCP or Unix socket</td>
                            <td>Full support including all MariaDB-specific features</td>
                        </tr>
                        <tr>
                            <td><strong>MySQL</strong></td>
                            <td>TCP or Unix socket</td>
                            <td>MySQL 5.7+ and 8.0+</td>
                        </tr>
                        <tr>
                            <td><strong>Percona Server</strong></td>
                            <td>TCP or Unix socket</td>
                            <td>Compatible with MySQL protocol</td>
                        </tr>
                        <tr>
                            <td><strong>PostgreSQL</strong></td>
                            <td>TCP</td>
                            <td>Full query execution and table browsing</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Features</h2>
                <ul>
                    <li>Browse databases, tables, and column structure</li>
                    <li>View table data with pagination</li>
                    <li>Run raw SQL queries with syntax highlighting</li>
                    <li>Export query results and database dumps</li>
                    <li>Manage users and permissions</li>
                    <li>Auto-detect databases running in Docker and LXC containers</li>
                    <li>Connect to databases on any node in your cluster</li>
                </ul>
            </div>

<div class="page-nav"><a href="wolfstack-clustering.php" class="prev">&larr; Clustering</a><a href="wolfstack-security.php" class="next">Security &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

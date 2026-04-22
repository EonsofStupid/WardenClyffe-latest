<?php
$page_title = '🗄️ Databases — WolfStack Docs';
$page_desc = 'Manage every MariaDB, MySQL, and PostgreSQL database across your cluster from one unified Database Manager — connection profiles with cluster-aware routing, enterprise per-user ACL, and full integration with WolfStack AI agents and WolfFlow automation.';
$page_keywords = 'wolfstack, database manager, mysql, mariadb, postgresql, cluster, sql, enterprise, acl, ai agent, wolfflow';
$active = 'wolfstack-mysql.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">

            <div class="content-section">
                <h2>Overview</h2>
                <img src="images/screenshots/mysql-editor.png" alt="WolfStack unified Database Manager" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p><strong>One Database Manager, cluster-wide.</strong> The Databases page at the top of the sidebar is the single place in WolfStack to reach every configured database — MariaDB, MySQL, or PostgreSQL — regardless of which node in the cluster actually owns it. Pick a saved connection on the left, browse the schema tree, open a table for data or structure, or run free-form SQL — all from one screen.</p>
                <p>Connections are stored as <strong>profiles</strong>: a label, engine type, host/port/user/password, and the cluster+node that can reach it. Because profiles are replicated across every WolfStack node, any node can act as the query proxy — if the main node goes down, the others pick up seamlessly.</p>
            </div>

            <div class="content-section">
                <h2>Supported Databases</h2>
                <table>
                    <thead>
                        <tr><th>Database</th><th>Driver</th><th>Notes</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>MariaDB</strong></td><td>mysql_async (pure Rust)</td><td>All MariaDB versions, TCP + SSL/TLS</td></tr>
                        <tr><td><strong>MySQL</strong></td><td>mysql_async (pure Rust)</td><td>MySQL 5.7+ and 8.0+</td></tr>
                        <tr><td><strong>Percona Server</strong></td><td>mysql_async (pure Rust)</td><td>Compatible with MySQL protocol</td></tr>
                        <tr><td><strong>PostgreSQL</strong></td><td>tokio-postgres + deadpool</td><td>Pooled connections, SSL prefer/require/disable</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Cluster-Aware Routing</h2>
                <p>When you add a connection you pick the target cluster, then the owning node, then the IP reachable from that node — the dropdown includes host interfaces, Docker containers, LXC containers, and VMs with their names so you can find them at a glance. You can also paste a shorthand like <code>WolfStack-Proxmox:wolfstack-1:10.10.10.105</code>.</p>
                <p>Queries are executed on the node that owns the connection. If you open a connection from a different node, WolfStack proxies the query to the owning peer over the cluster-secret-authenticated inter-node channel — no exposing database ports on the public network, no copying credentials between nodes. Profile changes replicate to every WolfStack peer automatically.</p>
            </div>

            <div class="content-section">
                <h2>Database Manager Features</h2>
                <ul>
                    <li><strong>Schema tree</strong> — every database and table on the connection, searchable</li>
                    <li><strong>Data tab</strong> — browse table rows (200-row page, truncation badge when results exceed caps)</li>
                    <li><strong>Structure tab</strong> — column names, types, nullability, defaults</li>
                    <li><strong>Query tab</strong> — free-form SQL with Ctrl/Cmd+Enter shortcut, permission tier selector (Read / Update / Delete), configurable timeout</li>
                    <li><strong>One-click export</strong> — copy results as CSV or Markdown to the clipboard</li>
                    <li><strong>Test Connection</strong> — probes the database through the correct node, surfaces the server version string</li>
                    <li><strong>Safety caps</strong> — 10,000-row / 10 MB result ceiling, 30s execution timeout (configurable per query), classify-before-execute so an <code>UPDATE</code> can't sneak in under a Read request</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Enterprise ACL</h2>
                <p>Each connection profile can be restricted to a list of named WolfStack users. Operators without access don't see the profile at all — not in the Databases page, not in the Settings editor, not via the API. A lock badge next to the connection in the list indicates ACL is active.</p>
                <p>ACL is an Enterprise feature; on Community installs all authenticated operators share every profile. The restriction applies uniformly to the UI, the REST API, and the AI agent tool surface — there is no bypass path.</p>
            </div>

            <div class="content-section">
                <h2>AI Agents &amp; WolfFlow</h2>
                <p>Agents and workflows execute SQL through the same pipeline as the UI — same profiles, same routing, same audit log, same permission classifier. Per-agent settings gate what they can touch via three independent checks:</p>
                <ol>
                    <li><strong>Flags</strong> — each agent has separate <code>sql_read</code>, <code>sql_update</code>, <code>sql_delete</code> switches (all default off)</li>
                    <li><strong>Allowlist</strong> — a set of connection IDs the agent is allowed to query (empty list = none)</li>
                    <li><strong>Parser</strong> — every query is classified by <code>sqlparser</code>; an <code>UPDATE</code> pretending to be a <code>SELECT</code> is rejected before it hits the database</li>
                </ol>
                <p>Every execution — agent, workflow, or UI — is appended to <code>/var/log/wolfstack/sql-audit.log</code> with caller tag, connection ID, query, outcome, row count, and elapsed time.</p>
            </div>

            <div class="content-section">
                <h2>Security Model</h2>
                <ul>
                    <li><strong>Encrypted at rest</strong> — passwords stored AES-256-GCM encrypted with the cluster secret (same scheme as OIDC client secrets); only decrypted in memory at connect time</li>
                    <li><strong>Cluster-secret auth on inter-node calls</strong> — session cookies can't reach the <code>/query-proxy</code>, <code>/receive</code>, or <code>/audit</code> endpoints; only trusted peers</li>
                    <li><strong>Hardcoded API denylist</strong> — agents using the generic <code>wolfstack_api</code> tool cannot POST to <code>/api/sql-connections/*</code> or the node-IP enumerator; the only way to run SQL is through the dedicated <code>sql_query</code> tool, which respects all three permission gates</li>
                    <li><strong>Filesystem denylist</strong> — <code>/etc/wolfstack/sql-connections.json</code> and <code>/var/log/wolfstack/sql-audit.log</code> are off-limits to agent filesystem tools</li>
                    <li><strong>Bounded execution</strong> — 5s connect timeout, 30s default query timeout, per-query override capped at 300s; hung databases can't starve the actix workers</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>What Changed in v20.0</h2>
                <p>The old per-node MySQL editor has been retired. One Database Manager lives at the datacenter level — pick a connection and it just works. Behind the scenes:</p>
                <ul>
                    <li>New <code>🗄️ Databases</code> main-sidebar page</li>
                    <li>Connection profiles with cluster/node/IP routing — IP picker pre-populates with Docker containers, LXC containers, and VMs on the chosen node</li>
                    <li>Profile replication across every WolfStack peer so any node can proxy</li>
                    <li>Enterprise per-user ACL</li>
                    <li>AI agent SQL tool with three-gate permission chain and full audit trail</li>
                    <li>WolfFlow SQL action nodes for automation</li>
                    <li>Test Connection and Query routing both flow through the cluster — remote-only database hosts (e.g. a container IP only reachable from one peer) probe correctly, no hangs</li>
                </ul>
            </div>

<div class="page-nav"><a href="wolfstack-clustering.php" class="prev">&larr; Clustering</a><a href="wolfstack-security.php" class="next">Security &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

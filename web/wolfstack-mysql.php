<?php
$page_title = '🗄️ Databases — WolfStack Docs';
$page_desc = 'One cluster-wide Database Manager for MariaDB, MySQL, and PostgreSQL — ER diagrams, visual query builder, inline row editing, dumps, CSV import, schema compare, AI agent access, per-user saved queries, enterprise per-user ACL.';
$page_keywords = 'wolfstack, database manager, mysql, mariadb, postgresql, cluster, sql, er diagram, query builder, enterprise, acl, ai agent, wolfflow, dbeaver, navicat';
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
                <p><strong>One Database Manager, cluster-wide.</strong> The <code>🗄️ Databases</code> page at the top of the sidebar is the single place in WolfStack to reach every configured database — MariaDB, MySQL, or PostgreSQL — regardless of which node in the cluster actually owns it. <em>The old per-node MySQL editor has been retired</em>: there is now one manager at the datacenter level that serves every connection.</p>
                <p>Connections are stored as <strong>profiles</strong>: a label, engine type, host/port/user/password, and the cluster+node that can reach it. Because profiles replicate to every WolfStack peer, any node can proxy queries — if the main node goes down, the others pick up seamlessly.</p>
            </div>

            <div class="content-section">
                <h2>Supported Databases</h2>
                <table>
                    <thead><tr><th>Database</th><th>Driver</th><th>Notes</th></tr></thead>
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
                <p>Queries execute on the node that owns the connection. If you open a connection from a different node, WolfStack proxies the query to the owning peer over the cluster-secret-authenticated inter-node channel — no exposing database ports on the public network, no copying credentials between nodes. Profile changes replicate automatically.</p>
            </div>

            <div class="content-section">
                <h2>Database Manager — Features</h2>
                <p>The Manager takes the whole page when a connection is open. Six tabs across the top:</p>
                <h3>📊 Data</h3>
                <ul>
                    <li>Browse rows with pagination (50 / 100 / 250 / 500 / 1000 rows per page)</li>
                    <li>Click column headers to sort ascending, click again for descending</li>
                    <li>Quick text filter across all visible cells</li>
                    <li><strong>Inline row editing</strong>: click ✏️ for a full row-edit modal with one input per column, NULL toggles, PK fields protected, only-changed-columns UPDATE</li>
                    <li>Edit / Delete buttons <strong>pinned to the left</strong> — always visible, even on wide rows</li>
                    <li>➕ Row — insert a new row via prompts for each column</li>
                    <li>📥 Import CSV — upload a CSV, header row maps to columns, rows inserted in a single batch</li>
                    <li>📤 Export — CSV / JSON / SQL INSERTs of the current page</li>
                </ul>
                <h3>🏗️ Structure</h3>
                <ul>
                    <li>Full DDL editing with <strong>Schema</strong> permission: Add column / Drop column, Add index / Drop index, Add foreign key / Drop foreign key</li>
                    <li>Engine-aware syntax: <code>DROP CONSTRAINT</code> on Postgres, <code>DROP FOREIGN KEY</code> on MySQL/MariaDB</li>
                </ul>
                <h3>🗺️ Diagram (ERD)</h3>
                <ul>
                    <li>SVG schema diagram — tables rendered as draggable boxes with PK 🔑 markers</li>
                    <li>Foreign-key relationships drawn as bezier arrows</li>
                    <li>Mousewheel zoom, drag-empty-space pan, double-click a table to jump to its Structure tab</li>
                    <li>Auto-layout places tables in a grid (heights variable so wide tables don't overlap)</li>
                    <li>💾 Save SVG — exports a standalone <code>.svg</code> file for docs and hand-off</li>
                </ul>
                <h3>🧱 Build (Visual Query Builder)</h3>
                <ul>
                    <li>Drag tables from the picker onto the canvas</li>
                    <li>Tick columns to include in the <code>SELECT</code></li>
                    <li>Click-drag column-to-column to draw a <code>JOIN</code> (INNER by default)</li>
                    <li>Add WHERE conditions in the bottom panel (table/column/operator/value, one row per condition)</li>
                    <li>Configurable row limit</li>
                    <li>Generated SQL updates live at the bottom — hit <strong>Send to Query</strong> to open it in the Query tab</li>
                </ul>
                <h3>⚡ Query</h3>
                <ul>
                    <li>Multi-tab editor (<code>+</code> new tab, <code>✕</code> close, double-click to rename)</li>
                    <li>Lightweight SQL syntax highlighting (keywords / strings / numbers / comments / operators)</li>
                    <li>Live <code>sqlparser</code>-backed validation with tier badge (Read / Update / Delete / Schema)</li>
                    <li>Ctrl+Space autocomplete with context: after <code>FROM</code>/<code>JOIN</code> suggests tables, after <code>.</code> suggests columns, otherwise keywords + tables. Each candidate shows its source ("table in <em>schema</em>", "column in <em>schema.table</em>", "SQL keyword")</li>
                    <li>💾 Saved queries — named, per-user, server-side (follow you across devices)</li>
                    <li>Recent — auto ring-buffer of the last 20 executed queries per connection, per user</li>
                    <li>🔍 Explain / ✨ Format / 📂 Run SQL file (splits on <code>;</code>, handles <code>DELIMITER</code>, runs via <code>/query-multi</code>)</li>
                    <li>Per-tab schema context (Navicat-style): new tabs inherit the currently-selected schema, existing tabs keep theirs</li>
                    <li>Destructive-query confirm on TRUNCATE, DROP TABLE, unqualified DELETE, unqualified UPDATE</li>
                    <li>Unlimited-query warning when a SELECT/WITH has no LIMIT</li>
                    <li>Draggable splitter between editor and result panel</li>
                    <li>⛶ Fullscreen toggle for the whole Databases page</li>
                </ul>
                <h3>🖥️ Server</h3>
                <ul>
                    <li>Server version, database, current user, host, port</li>
                    <li>Key variables — click ✏️ to change (modal with session / global / ALTER SYSTEM scope, requires Schema permission, auto-runs <code>pg_reload_conf()</code> after Postgres ALTER SYSTEM)</li>
                    <li>Active sessions (process list) — click ✕ to kill via <code>KILL &lt;id&gt;</code> (MySQL) or <code>pg_terminate_backend(pid)</code> (Postgres)</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Schema tree — left panel</h2>
                <ul>
                    <li>Tables + Views grouped per schema with counts</li>
                    <li>Procedures / Functions / Triggers listed read-only — click to view the CREATE definition in a modal</li>
                    <li>Per-schema <strong>+ New table</strong>, <strong>Dump schema</strong>, <strong>Dump + data</strong> actions</li>
                    <li>◀ / 📁 ▶ toggle to hide the tree and give the editor full width</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Create Table &amp; Dump / Restore</h2>
                <p><strong>Create Table wizard</strong> — form with a column grid (name, type, NOT NULL, PK, default), live SQL preview, runs at Schema permission.</p>
                <p><strong>Dump to SQL</strong> — client-driven export of a whole schema. Choose schema-only (fast) or schema + data (paginated 500-row batches). Writes a standalone <code>.sql</code> file with <code>DROP TABLE IF EXISTS</code> / <code>CREATE TABLE</code> / <code>INSERT</code> statements ready for restore. Postgres uses information_schema reconstruction since it has no <code>SHOW CREATE TABLE</code>.</p>
                <p><strong>Restore from SQL</strong> — the Query tab's <code>📂 Run SQL file</code> button accepts any <code>.sql</code> upload. The client splits on <code>;</code> honouring comments, string literals, and <code>DELIMITER</code> directives; the new <code>/query-multi</code> endpoint executes each statement sequentially with stop-on-error and reports per-statement outcomes including the line number where any failure occurred.</p>
            </div>

            <div class="content-section">
                <h2>Schema Compare</h2>
                <p>The <strong>🧭 Compare schemas</strong> button on the Databases page header opens a side-by-side diff of two schemas (same connection or different). Reports:</p>
                <ul>
                    <li>Tables only in source / only in target / in both-with-differences</li>
                    <li>Per-table: column additions, removals, type / nullability / default changes</li>
                    <li>Index and foreign-key additions / removals</li>
                </ul>
                <p>Click <strong>📜 Generate migration SQL</strong> to produce <code>CREATE TABLE</code> / <code>DROP TABLE</code> / <code>ALTER TABLE</code> statements that would bring the target in line with the source. The generator leaves complex type-changes as TODO comments for the operator to review — always treat the output as a starting point.</p>
            </div>

            <div class="content-section">
                <h2>Enterprise ACL</h2>
                <p>Each connection profile can be restricted to a list of named WolfStack users. Operators without access don't see the profile at all — not in the Databases page, not in the Settings editor, not via the API. A 🔒 badge next to the connection in the list indicates ACL is active.</p>
                <p>ACL is an Enterprise feature; on Community installs all authenticated operators share every profile. The restriction applies uniformly to the UI, the REST API, and the AI agent tool surface — there is no bypass path.</p>
            </div>

            <div class="content-section">
                <h2>AI Agents &amp; WolfFlow</h2>
                <p><strong>AI agents can talk to the database.</strong> Agents configured with SQL permissions use the same pipeline as the UI — same profiles, same cluster routing, same audit log, same <code>sqlparser</code> classifier, same enterprise ACL. An agent asked <em>"how many customers signed up yesterday?"</em> can issue <code>SELECT COUNT(*) FROM customers WHERE created_at &gt; NOW() - INTERVAL 1 DAY</code> and feed the result back into its reply.</p>
                <p>Per-agent settings gate what the agent can touch via three independent checks:</p>
                <ol>
                    <li><strong>Flags</strong> — each agent has separate <code>sql_read</code>, <code>sql_update</code>, <code>sql_delete</code> switches (all default off). DDL via <code>sql_schema</code> is <strong>never</strong> granted to agents — AI writing ALTER TABLE unsupervised is not a surface we want open.</li>
                    <li><strong>Allowlist</strong> — an operator-set list of connection IDs the agent is allowed to query (empty list = none).</li>
                    <li><strong>Parser</strong> — every query is classified by <code>sqlparser</code> before dispatch. An <code>UPDATE</code> pretending to be a <code>SELECT</code> is rejected before it hits the database. Multi-statement queries are refused outright.</li>
                </ol>
                <p>WolfFlow workflows get a matching <strong>SQL Query</strong> action node with the same gates — operators pick a connection, pick a permission tier, write the query; the workflow runs it on schedule or trigger and can feed the result into subsequent steps. Every execution — agent, workflow, or UI — is appended to <code>/var/log/wolfstack/sql-audit.log</code> with caller tag, connection ID, query, outcome, row count, and elapsed time.</p>
            </div>

            <div class="content-section">
                <h2>Security Model</h2>
                <ul>
                    <li><strong>Encrypted at rest</strong> — passwords stored AES-256-GCM encrypted with the cluster secret (same scheme as OIDC client secrets); only decrypted in memory at connect time</li>
                    <li><strong>Cluster-secret auth on inter-node calls</strong> — session cookies can't reach the <code>/query-proxy</code>, <code>/receive</code>, or <code>/audit</code> endpoints; only trusted peers</li>
                    <li><strong>Hardcoded API denylist</strong> — agents using the generic <code>wolfstack_api</code> tool cannot POST to <code>/api/sql-connections/*</code> or the node-IP enumerator; the only way to run SQL is through the dedicated <code>sql_query</code> tool, which respects all three permission gates</li>
                    <li><strong>Filesystem denylist</strong> — <code>/etc/wolfstack/sql-connections.json</code>, <code>/etc/wolfstack/sql-saved-queries.json</code>, and <code>/var/log/wolfstack/sql-audit.log</code> are off-limits to agent filesystem tools</li>
                    <li><strong>Per-user saved queries</strong> — operators' pinned SQL is keyed to their username, not shared across the cluster</li>
                    <li><strong>Bounded execution</strong> — 5s connect timeout, 30s default query timeout (configurable per query), 10,000-row / 10 MB result cap; hung databases can't starve the actix workers</li>
                </ul>
            </div>

<div class="page-nav"><a href="wolfstack-clustering.php" class="prev">&larr; Clustering</a><a href="wolfstack-security.php" class="next">Security &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

<?php
$page_title = 'WolfNote — AI-Powered Notes System';
$page_desc = 'A beautiful web-based notes system with rich text editing, task lists, AI assistant, time tracking, billing, and team collaboration. Single binary, deploys in seconds.';
$page_keywords = 'notes app, task management, AI notes, todo list, time tracking, billing, team collaboration, Rust, self-hosted, WolfNote';
$active = 'wolfnote.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">

            <div class="content-section">
                <h2>WolfNote</h2>
                <p style="font-size:1.1em;color:var(--text-secondary);margin-bottom:1.5em;">AI-powered notes system with rich text editing, task lists, time tracking, billing, and real-time team collaboration. Single binary, deploys in seconds.</p>

                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:2em;">
                    <a href="https://github.com/wolfsoftwaresystemsltd/wolfnote" target="_blank" class="button button-primary" style="text-decoration:none;">View on GitHub</a>
                    <a href="https://github.com/wolfsoftwaresystemsltd/wolfnote/releases" target="_blank" class="button button-secondary" style="text-decoration:none;">Download Binary</a>
                </div>

                <h3>Features</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:2em;">
                    <div class="feature-card">
                        <h4>Rich Text Editor</h4>
                        <p>Full toolbar: bold, italic, headings, lists, tables, code blocks, font colours, background colours, highlighting, line numbers. Content saved as HTML.</p>
                    </div>
                    <div class="feature-card">
                        <h4>Task Lists</h4>
                        <p>Checkboxes with tick completion, assignees from user list, sections, drag-and-drop reordering. Start/stop time tracking with automatic duration calculation.</p>
                    </div>
                    <div class="feature-card">
                        <h4>AI Assistant</h4>
                        <p>Chat bubble with Claude, Google Gemini, or local Ollama. Web search grounding to reduce hallucinations. Meeting notes to todo list extraction.</p>
                    </div>
                    <div class="feature-card">
                        <h4>Time Tracking &amp; Billing</h4>
                        <p>Per-task start/end timestamps, automatic hours calculation. Configurable hourly rate and currency. Print invoices with per-task costs and totals.</p>
                    </div>
                    <div class="feature-card">
                        <h4>Folder Hierarchy</h4>
                        <p>File manager home view with nested folders, breadcrumb navigation. Create notes and task lists directly inside folders.</p>
                    </div>
                    <div class="feature-card">
                        <h4>Team Collaboration</h4>
                        <p>Share notes and entire folder trees with other users. Live sync &mdash; edits appear within 3 seconds. Role-based access (view or edit).</p>
                    </div>
                    <div class="feature-card">
                        <h4>3D Graph View</h4>
                        <p>Three.js interactive visualisation showing how notes connect to each other through links and folders. Click a node to navigate.</p>
                    </div>
                    <div class="feature-card">
                        <h4>12 Themes</h4>
                        <p>Dark, Light, Midnight, Forest, Amber Terminal, Obsidian, Carbon, Twilight, Arctic, Void, Deep Red, TTY. Visual preview cards for easy selection.</p>
                    </div>
                    <div class="feature-card">
                        <h4>User Management</h4>
                        <p>Admin panel with user creation, role management, registration toggle. First registered user is always admin. bcrypt + JWT authentication.</p>
                    </div>
                </div>

                <h3>Quick Deploy</h3>
                <p>Download the pre-built static binary &mdash; no Rust toolchain, no dependencies, runs on any Linux x86_64:</p>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code>mkdir wolfnote && cd wolfnote
gh release download v0.2.0 -R wolfsoftwaresystemsltd/wolfnote
mkdir -p static
mv index.html favicon.svg logo.svg manifest.json sw.js static/
chmod +x wolfnote-v0.2.0-linux-x86_64
mv wolfnote-v0.2.0-linux-x86_64 wolfnote
cp .env.example .env
nano .env   # Set a strong JWT_SECRET
./wolfnote</code></pre>
                </div>
                <p>Open <code>http://localhost:3000</code> &mdash; the first user to register becomes admin.</p>

                <h3>Build from Source</h3>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code>git clone https://github.com/wolfsoftwaresystemsltd/wolfnote.git
cd wolfnote
cp .env.example .env
cargo run</code></pre>
                </div>

                <h3>Environment Variables</h3>
                <table class="config-table">
                    <thead><tr><th>Variable</th><th>Description</th><th>Default</th></tr></thead>
                    <tbody>
                        <tr><td><code>DATABASE_URL</code></td><td>SQLite database path</td><td><code>sqlite:wolfnote.db</code></td></tr>
                        <tr><td><code>JWT_SECRET</code></td><td>Secret for JWT token signing</td><td><code>dev-secret-change-me</code></td></tr>
                        <tr><td><code>PORT</code></td><td>Port to listen on</td><td><code>3000</code></td></tr>
                        <tr><td><code>SSL_DOMAIN</code></td><td>Domain for Let's Encrypt cert auto-detection</td><td><em>none</em></td></tr>
                        <tr><td><code>SSL_CERT</code></td><td>Path to SSL certificate file</td><td><em>auto</em></td></tr>
                        <tr><td><code>SSL_KEY</code></td><td>Path to SSL private key file</td><td><em>auto</em></td></tr>
                    </tbody>
                </table>
                <p>AI provider keys (Claude, Gemini, Ollama) are configured through the admin Settings UI.</p>

                <h3>Systemd Service</h3>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">ini</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code>[Unit]
Description=WolfNote
After=network.target

[Service]
Type=simple
WorkingDirectory=/opt/wolfnote
ExecStart=/opt/wolfnote/wolfnote
Restart=on-failure
RestartSec=5
Environment=RUST_LOG=info
Environment=PORT=3000

[Install]
WantedBy=multi-user.target</code></pre>
                </div>

                <h3>Tech Stack</h3>
                <ul>
                    <li><strong>Backend:</strong> Rust + Axum 0.8 (async, fast, safe)</li>
                    <li><strong>Database:</strong> SQLite via SQLx (zero-config, embedded)</li>
                    <li><strong>Auth:</strong> bcrypt + JWT (30-day tokens)</li>
                    <li><strong>AI:</strong> Claude / Google Gemini / Ollama (configurable)</li>
                    <li><strong>TLS:</strong> rustls + ring (pure Rust, no OpenSSL)</li>
                    <li><strong>Frontend:</strong> Vanilla HTML/CSS/JS + Three.js (no build step, no node_modules)</li>
                    <li><strong>Binary:</strong> Static MUSL build, single file, ~13MB</li>
                </ul>

                <h3>PWA &amp; Mobile</h3>
                <p>WolfNote is a Progressive Web App &mdash; install it on your phone's home screen for a native app experience. The sidebar auto-collapses on mobile with a hamburger menu. Touch-friendly button sizes and responsive layouts throughout.</p>

                <h3>License</h3>
                <p>MIT &mdash; free for personal and commercial use. <a href="https://github.com/wolfsoftwaresystemsltd/wolfnote/blob/master/LICENSE" target="_blank">View license</a>.</p>
            </div>

<div class="page-nav"><a href="wolfserve.php" class="prev">&larr; WolfServe</a><a href="about.php" class="next">About &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

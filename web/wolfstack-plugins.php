<?php
$page_title = 'Plugin System — WolfStack Docs';
$page_desc = 'WolfStack plugin system — extend your server management platform with first-party and third-party plugins. Install, configure, and manage plugins from the Settings UI.';
$active = 'wolfstack-plugins.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content" style="max-width:1100px;">

            <div class="content-section">
                <h2>Plugin System</h2>
                <p>WolfStack&rsquo;s plugin system lets you extend the platform with additional functionality. Plugins can add new pages to the UI, provide backend APIs, and integrate with WolfStack&rsquo;s existing features.</p>
                <p>Plugins are managed from <strong>Settings &rarr; Plugins</strong> in the WolfStack dashboard.</p>
                <div class="info-box" style="border-left:4px solid var(--accent-primary);">
                    <p>&#x2728; <strong>Enterprise feature:</strong> The plugin system requires a WolfStack <a href="enterprise.php">Enterprise license</a>. Plugins are loaded, assets served, and backends proxied only when a valid license is present. Without a license, plugins in <code>/etc/wolfstack/plugins/</code> are listed but marked <code>requires_license</code> and remain inactive.</p>
                </div>
            </div>

            <div class="content-section">
                <h2>How Plugins Work</h2>
                <p>A plugin is a directory inside <code>/etc/wolfstack/plugins/</code> containing a manifest and optional frontend/backend code:</p>
<pre><code>/etc/wolfstack/plugins/my-plugin/
  manifest.json      &mdash; plugin metadata and configuration
  web/
    plugin.js        &mdash; frontend code (injected into the SPA)
    plugin.css       &mdash; styles (optional)
  bin/
    handler          &mdash; backend binary (optional)</code></pre>
                <p>On startup, WolfStack scans the plugins directory and loads each valid manifest. Frontend assets (JS/CSS) are automatically injected into the web UI, and API requests are proxied to the plugin&rsquo;s backend process.</p>
            </div>

            <div class="content-section">
                <h2>Plugin Manifest</h2>
                <p>Every plugin must include a <code>manifest.json</code> file describing its metadata, menu placement, and capabilities:</p>
<pre><code>{
  "id": "advanced-monitoring",
  "name": "Advanced Monitoring",
  "version": "1.0.0",
  "description": "Real-time metrics with Prometheus integration",
  "author": "Wolf Software Systems Ltd",
  "icon": "&#128202;",
  "enabled": true,
  "menu": {
    "section": "datacenter",
    "label": "Monitoring",
    "view": "monitoring"
  },
  "api_port": 9100,
  "settings": [
    {
      "id": "prometheus_url",
      "label": "Prometheus URL",
      "input_type": "text",
      "default": "http://localhost:9090",
      "description": "URL of your Prometheus instance"
    }
  ]
}</code></pre>
                <table class="feature-table" style="width:100%;font-size:0.85rem;">
                    <thead><tr><th style="width:160px;">Field</th><th style="width:80px;">Required</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>id</code></td><td>Yes</td><td>Unique plugin identifier (lowercase, hyphens allowed)</td></tr>
                        <tr><td><code>name</code></td><td>Yes</td><td>Display name shown in the UI</td></tr>
                        <tr><td><code>version</code></td><td>No</td><td>Semantic version (e.g. &ldquo;1.2.3&rdquo;)</td></tr>
                        <tr><td><code>description</code></td><td>No</td><td>Short description of what the plugin does</td></tr>
                        <tr><td><code>author</code></td><td>No</td><td>Plugin author or organisation</td></tr>
                        <tr><td><code>icon</code></td><td>No</td><td>Emoji icon for the sidebar and plugin list</td></tr>
                        <tr><td><code>enabled</code></td><td>No</td><td>Whether the plugin is active (default: <code>true</code>)</td></tr>
                        <tr><td><code>menu</code></td><td>No</td><td>Navigation menu placement (see below)</td></tr>
                        <tr><td><code>api_port</code></td><td>No</td><td>Port the plugin&rsquo;s backend binary listens on (for API proxying)</td></tr>
                        <tr><td><code>settings</code></td><td>No</td><td>Array of configurable settings shown in the plugin&rsquo;s settings panel</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Menu Placement</h2>
                <p>The <code>menu</code> object controls where the plugin appears in the WolfStack sidebar navigation:</p>
                <table class="feature-table" style="width:100%;font-size:0.85rem;">
                    <thead><tr><th style="width:120px;">Field</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>section</code></td><td><code>"datacenter"</code> &mdash; appears in the datacenter-level navigation (visible to all nodes)<br><code>"server"</code> &mdash; appears under each server&rsquo;s navigation<br><code>"settings"</code> &mdash; appears as a tab in the Settings page</td></tr>
                        <tr><td><code>label</code></td><td>Text shown in the sidebar (e.g. &ldquo;Monitoring&rdquo;)</td></tr>
                        <tr><td><code>view</code></td><td>View ID used internally &mdash; the plugin&rsquo;s page container will be <code>page-plugin-{view}</code></td></tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Frontend Plugin (plugin.js)</h2>
                <p>If your plugin includes <code>web/plugin.js</code>, it will be automatically loaded when WolfStack starts. The script has full access to the WolfStack SPA&rsquo;s DOM and JavaScript functions.</p>
                <p>Your plugin&rsquo;s page container is created automatically at <code>page-plugin-{view}</code>. Populate it in your JS:</p>
<pre><code>// web/plugin.js — Example: Advanced Monitoring plugin

// This runs when the plugin is loaded
(function() {
    const container = document.getElementById('plugin-monitoring-content');
    if (!container) return;

    // Render your plugin UI
    container.innerHTML = `
        &lt;div class="card"&gt;
            &lt;div class="card-header"&gt;&lt;h3&gt;Advanced Monitoring&lt;/h3&gt;&lt;/div&gt;
            &lt;div class="card-body" id="monitoring-data"&gt;Loading...&lt;/div&gt;
        &lt;/div&gt;
    `;

    // Fetch data from plugin backend
    loadMonitoringData();
})();

async function loadMonitoringData() {
    const resp = await fetch('/api/plugins/advanced-monitoring/api/metrics');
    const data = await resp.json();
    document.getElementById('monitoring-data').innerHTML =
        '&lt;pre&gt;' + JSON.stringify(data, null, 2) + '&lt;/pre&gt;';
}</code></pre>
                <p>Available WolfStack functions you can call from plugin JS:</p>
                <ul>
                    <li><code>showToast(message, type)</code> &mdash; show a notification (<code>type</code>: &lsquo;success&rsquo;, &lsquo;error&rsquo;, &lsquo;info&rsquo;)</li>
                    <li><code>apiUrl(path)</code> &mdash; resolve an API URL (handles node proxying)</li>
                    <li><code>escapeHtml(str)</code> &mdash; HTML-escape a string for safe rendering</li>
                    <li><code>formatBytes(n)</code> &mdash; format bytes as human-readable (e.g. &ldquo;4.2 GB&rdquo;)</li>
                    <li><code>selectView(view)</code> &mdash; navigate to a datacenter-level page</li>
                    <li><code>selectServerView(nodeId, view)</code> &mdash; navigate to a server-level page</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Backend Plugin (bin/handler)</h2>
                <p>If your plugin needs server-side logic, include a backend binary at <code>bin/handler</code>. WolfStack will proxy API requests to it:</p>
                <ul>
                    <li>Requests to <code>/api/plugins/{id}/api/*</code> are forwarded to <code>http://127.0.0.1:{api_port}/*</code></li>
                    <li>All HTTP methods (GET, POST, PUT, DELETE) are proxied</li>
                    <li>Authentication is handled by WolfStack &mdash; only authenticated requests reach the plugin</li>
                    <li>The backend receives <code>PORT</code> and <code>PLUGIN_DIR</code> environment variables</li>
                </ul>
                <p>The backend can be written in any language &mdash; it just needs to be a standalone HTTP server. Example in Python:</p>
<pre><code>#!/usr/bin/env python3
# bin/handler — simple plugin backend
import os, json
from http.server import HTTPServer, BaseHTTPRequestHandler

PORT = int(os.environ.get('PORT', 9100))

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200)
        self.send_header('Content-Type', 'application/json')
        self.end_headers()
        self.wfile.write(json.dumps({"status": "ok"}).encode())

HTTPServer(('127.0.0.1', PORT), Handler).serve_forever()</code></pre>
            </div>

            <div class="content-section">
                <h2>Plugin Store</h2>
                <p>WolfStack includes a <strong>built-in Plugin Store</strong> accessible from <strong>Settings &rarr; Plugins &rarr; Plugin Store</strong>. Browse available plugins and install them with a single click &mdash; no manual downloads or file management required.</p>
                <ul>
                    <li><strong>One-click install</strong> &mdash; Select a plugin from the store and click Install. WolfStack downloads, extracts, and activates it automatically.</li>
                    <li><strong>Automatic updates</strong> &mdash; Reinstalling a plugin that is already installed automatically updates it. WolfStack stops the existing plugin process, replaces the files, and starts the new version.</li>
                    <li><strong>Currently available</strong> &mdash; The Plugin Store ships with <a href="wolfhost.php"><strong>WolfHost</strong></a> (web hosting platform) and <a href="wolfcustom.php"><strong>WolfCustom</strong></a> (white-label branding).</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Installing Plugins</h2>
                <p>Plugins can be installed in three ways:</p>

                <h3>From the Plugin Store</h3>
                <p>Go to <strong>Settings &rarr; Plugins &rarr; Plugin Store</strong> and click <strong>Install</strong> on any available plugin. This is the easiest method and handles everything automatically.</p>

                <h3>From the UI</h3>
                <p>Go to <strong>Settings &rarr; Plugins &rarr; Install Plugin</strong> and paste the URL of a <code>.tar.gz</code> archive containing the plugin directory. WolfStack downloads and extracts it automatically.</p>

                <h3>From the command line</h3>
<pre><code># Download and extract to the plugins directory
curl -fsSL https://example.com/my-plugin.tar.gz | sudo tar xzf - -C /etc/wolfstack/plugins/

# Restart WolfStack to load the plugin
sudo systemctl restart wolfstack</code></pre>

                <h3>Manual installation</h3>
<pre><code># Create the plugin directory
sudo mkdir -p /etc/wolfstack/plugins/my-plugin

# Copy your plugin files
sudo cp manifest.json /etc/wolfstack/plugins/my-plugin/
sudo cp -r web/ /etc/wolfstack/plugins/my-plugin/
sudo cp -r bin/ /etc/wolfstack/plugins/my-plugin/

# Restart
sudo systemctl restart wolfstack</code></pre>
            </div>

            <div class="content-section">
                <h2>Managing Plugins</h2>
                <p>From <strong>Settings &rarr; Plugins</strong> you can:</p>
                <ul>
                    <li><strong>Enable/Disable</strong> &mdash; toggle a plugin on or off without uninstalling it</li>
                    <li><strong>Uninstall</strong> &mdash; remove the plugin and all its files</li>
                    <li><strong>Refresh</strong> &mdash; rescan the plugins directory for new or changed plugins</li>
                </ul>
                <p>Plugin status indicators:</p>
                <ul>
                    <li><strong style="color:#4ade80;">active</strong> &mdash; plugin is loaded and running</li>
                    <li><strong style="color:#9ca3af;">disabled</strong> &mdash; plugin is installed but not active</li>
                    <li><strong style="color:#fbbf24;">requires_license</strong> &mdash; enterprise-only plugin, needs a valid license</li>
                    <li><strong style="color:#f87171;">error</strong> &mdash; plugin failed to load (check manifest.json)</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Enterprise License Required</h2>
                <p>The plugin system as a whole is an <strong>Enterprise feature</strong>. All plugins &mdash; first-party or third-party &mdash; require a valid WolfStack Enterprise license to load.</p>
                <p>Without a license, plugins dropped into <code>/etc/wolfstack/plugins/</code> are still listed in <strong>Settings &rarr; Plugins</strong>, but their status is shown as <code>requires_license</code> and they remain inactive:</p>
                <ul>
                    <li>Plugin frontend JS and CSS are not served</li>
                    <li>Backend handler proxies return <code>403 Forbidden</code></li>
                    <li>Plugin menu entries do not appear in the sidebar</li>
                    <li>Plugin installation via URL is blocked</li>
                </ul>
                <p>See <a href="enterprise.php">Enterprise Licensing</a> for details on obtaining a license. This gating allows the WolfStack core to remain fully open-source while protecting the plugin ecosystem as part of the Enterprise offering.</p>
            </div>

            <div class="content-section">
                <h2>Plugin API Reference</h2>
                <table class="feature-table" style="width:100%;font-size:0.85rem;">
                    <thead><tr><th style="width:80px;">Method</th><th style="width:300px;">Endpoint</th><th>Description</th></tr></thead>
                    <tbody>
                        <tr><td><code>GET</code></td><td><code>/api/plugins</code></td><td>List all installed plugins with status</td></tr>
                        <tr><td><code>POST</code></td><td><code>/api/plugins/install</code></td><td>Install a plugin from a URL</td></tr>
                        <tr><td><code>POST</code></td><td><code>/api/plugins/reload</code></td><td>Rescan the plugins directory</td></tr>
                        <tr><td><code>DELETE</code></td><td><code>/api/plugins/{id}</code></td><td>Uninstall a plugin</td></tr>
                        <tr><td><code>POST</code></td><td><code>/api/plugins/{id}/toggle</code></td><td>Enable or disable a plugin</td></tr>
                        <tr><td><code>GET</code></td><td><code>/api/plugins/{id}/file/{path}</code></td><td>Serve plugin web assets (JS, CSS, images)</td></tr>
                        <tr><td><code>*</code></td><td><code>/api/plugins/{id}/api/{path}</code></td><td>Proxy requests to the plugin&rsquo;s backend</td></tr>
                        <tr><td><code>GET</code></td><td><code>/api/plugins/{id}/data/config</code></td><td>Read plugin config JSON from data directory</td></tr>
                        <tr><td><code>POST</code></td><td><code>/api/plugins/{id}/data/config</code></td><td>Write plugin config JSON to data directory</td></tr>
                        <tr><td><code>POST</code></td><td><code>/api/plugins/{id}/data/upload/{file}</code></td><td>Upload a file to the plugin&rsquo;s data directory</td></tr>
                        <tr><td><code>GET</code></td><td><code>/api/plugins/{id}/data/file/{file}</code></td><td>Serve an uploaded file from the plugin&rsquo;s data directory</td></tr>
                    </tbody>
                </table>
                <p style="margin-top:1rem;font-size:0.85rem;color:var(--text-secondary);">The <code>/data/</code> endpoints allow plugins to store configuration and uploaded files (logos, images) in <code>/etc/wolfstack/plugins/{id}/data/</code> without needing a backend binary.</p>
            </div>

            <div class="content-section">
                <h2>Available Plugins</h2>
                <p>Official plugins developed by Wolf Software Systems Ltd:</p>
                <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:16px; margin-top:16px;">
                    <a href="wolfhost.php" style="display:block; background:var(--bg-card); border:1px solid var(--border-color); border-radius:10px; padding:1.2rem; text-decoration:none; color:var(--text-primary); transition:all 0.2s;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                            <span style="font-size:1.3rem;">🌐</span>
                            <strong>WolfHost</strong>
                            <span style="font-size:0.6rem;background:rgba(220,38,38,0.15);color:#ef4444;padding:1px 5px;border-radius:3px;">Enterprise</span>
                            <span style="font-size:0.6rem;background:rgba(245,158,11,0.15);color:#f59e0b;padding:1px 5px;border-radius:3px;">In Development</span>
                        </div>
                        <p style="font-size:0.82rem; color:var(--text-secondary); margin:0; line-height:1.5;">Complete web hosting platform with customer management, billing, domains, email, SSL, and white-label customer portal.</p>
                    </a>
                    <a href="wolfcustom.php" style="display:block; background:var(--bg-card); border:1px solid var(--border-color); border-radius:10px; padding:1.2rem; text-decoration:none; color:var(--text-primary); transition:all 0.2s;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                            <span style="font-size:1.3rem;">🎨</span>
                            <strong>WolfCustom</strong>
                            <span style="font-size:0.6rem;background:rgba(220,38,38,0.15);color:#ef4444;padding:1px 5px;border-radius:3px;">Enterprise</span>
                        </div>
                        <p style="font-size:0.82rem; color:var(--text-secondary); margin:0; line-height:1.5;">White-label branding. Replace the WolfStack logo, name, colours, favicon, and copyright with your own company identity.</p>
                    </a>
                </div>
            </div>

            <div class="page-nav"><a href="wolfstack-settings.php" class="prev">&larr; Settings</a><a href="wolfcustom.php" class="next">WolfCustom &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

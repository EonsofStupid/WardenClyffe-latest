<?php
$page_title = '🤖 AI Agent — WolfStack Docs';
$page_desc = 'Natural language infrastructure queries and automated actions';
$active = 'wolfstack-ai.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">


            <div class="content-section">
                <h2>Overview</h2>
                <p>WolfStack&rsquo;s AI Agent lets you ask questions about your infrastructure in natural language. Get intelligent answers about server health, resource usage, container status, and configuration &mdash; powered by leading AI models.</p>

                <h3>Supported Providers</h3>
                <table>
                    <thead>
                        <tr><th>Provider</th><th>Models</th><th>Configuration</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>CodeWolf</strong></td>
                            <td>CodeWolf (Claude Opus, Sonnet, Haiku)</td>
                            <td>Built-in &mdash; no API key required</td>
                        </tr>
                        <tr>
                            <td><strong>Google Gemini</strong></td>
                            <td>Gemini Pro, Flash</td>
                            <td>API key from Google AI Studio</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Capabilities</h2>
                <ul>
                    <li><strong>Natural language queries</strong> &mdash; Ask questions like &ldquo;Which node has the most free disk space?&rdquo; or &ldquo;Show me containers using more than 2 GB of memory&rdquo;</li>
                    <li><strong>Fleet health summaries</strong> &mdash; Get an overview of your entire infrastructure in plain English</li>
                    <li><strong>AI-assisted diagnostics</strong> &mdash; Troubleshoot issues with context-aware suggestions based on your actual system state</li>
                    <li><strong>Cross-cluster metrics</strong> &mdash; Query metrics and resource usage across all nodes and clusters</li>
                    <li><strong>Command execution</strong> &mdash; The AI agent can run read-only commands on your infrastructure to gather information for answers</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Health Monitoring</h2>
                <p>The AI Agent can run automated health checks across your infrastructure and send email alerts when it detects problems. This works alongside the <a href="wolfstack-issues.php">Issues Scanner</a> and <a href="wolfstack-alerting.php">threshold-based alerting</a> to provide an additional layer of intelligent monitoring.</p>
                <ul>
                    <li>Scheduled scanning at configurable intervals (hourly, every 6 hours, every 12 hours, or daily)</li>
                    <li>AI analyses system state and identifies potential issues</li>
                    <li>Email alerts sent when problems are detected</li>
                    <li>Findings include context and suggested remediation steps</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Configuration</h2>
                <p>Set up the AI Agent in <strong>Settings &rarr; AI Agent</strong>:</p>
                <ol>
                    <li>Choose your AI provider (CodeWolf or Gemini)</li>
                    <li>Enter your API key</li>
                    <li>Select the model to use</li>
                    <li>Optionally configure health monitoring email alerts</li>
                </ol>
                <p>AI configuration is automatically synced across all nodes in your cluster.</p>
            </div>

<div class="page-nav"><a href="app-store.php" class="prev">&larr; App Store</a><a href="wolfdisk.php" class="next">WolfDisk &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

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
                <img src="images/screenshots/ai-agent.png" alt="WolfStack AI Agent settings — Claude, Gemini, or local AI" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WolfStack&rsquo;s AI Agent lets you ask questions about your infrastructure in natural language. Get intelligent answers about server health, resource usage, container status, and configuration &mdash; powered by leading AI models.</p>

                <h3>Supported Providers</h3>
                <table>
                    <thead>
                        <tr><th>Provider</th><th>Models</th><th>Configuration</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Anthropic Claude</strong></td>
                            <td>Claude Sonnet, Opus, Haiku</td>
                            <td>API key from <a href="https://console.anthropic.com" target="_blank">console.anthropic.com</a></td>
                        </tr>
                        <tr>
                            <td><strong>Google Gemini</strong></td>
                            <td>Gemini Pro, Flash</td>
                            <td>API key from Google AI Studio</td>
                        </tr>
                        <tr>
                            <td><strong>Local / Self-Hosted</strong></td>
                            <td>Any model your server supports</td>
                            <td>Any OpenAI-compatible API endpoint</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Local AI Support</h2>
                <p>WolfStack can connect to <strong>self-hosted AI servers</strong> running on your own hardware &mdash; no cloud API keys required, no data leaves your network, and zero ongoing API costs.</p>

                <h3>Supported Local AI Servers</h3>
                <p>Any server that exposes an <strong>OpenAI-compatible API</strong> works out of the box. Tested platforms include:</p>
                <ul>
                    <li><strong>Ollama</strong> &mdash; the most popular local AI runtime. Run <code>ollama serve</code> and WolfStack connects automatically.</li>
                    <li><strong>LM Studio</strong> &mdash; desktop app with a built-in OpenAI-compatible server</li>
                    <li><strong>LocalAI</strong> &mdash; drop-in OpenAI replacement with broad model support</li>
                    <li><strong>vLLM</strong> &mdash; high-performance serving engine for large language models</li>
                    <li><strong>text-generation-webui</strong> (oobabooga) &mdash; enable the OpenAI extension</li>
                    <li><strong>llama.cpp</strong> &mdash; lightweight C++ inference server with <code>--api</code> flag</li>
                </ul>

                <h3>Auto-Detection</h3>
                <p>When you point WolfStack at a local AI server, it <strong>automatically queries the available models</strong> and populates the model dropdown. No need to manually look up model names &mdash; just set the endpoint URL and WolfStack discovers what&rsquo;s installed.</p>

                <h3>Expert Knowledge Base</h3>
                <p>WolfStack ships with a <strong>built-in expert knowledge base</strong> that is injected into every AI conversation. This means even smaller local models can give deep, accurate answers about your infrastructure because they receive curated context about WolfStack&rsquo;s architecture, commands, and best practices alongside your question.</p>

                <h3>Why Run AI Locally?</h3>
                <ul>
                    <li><strong>Zero cost</strong> &mdash; no per-token API charges, run unlimited queries</li>
                    <li><strong>Complete privacy</strong> &mdash; infrastructure data never leaves your network</li>
                    <li><strong>No internet required</strong> &mdash; works in air-gapped environments</li>
                    <li><strong>Your choice of model</strong> &mdash; use whatever model fits your hardware and needs</li>
                </ul>
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
                    <li>Choose your AI provider (Claude or Gemini)</li>
                    <li>Enter your API key</li>
                    <li>Select the model to use</li>
                    <li>Optionally configure health monitoring email alerts</li>
                </ol>
                <p>AI configuration is automatically synced across all nodes in your cluster.</p>
            </div>

<div class="page-nav"><a href="app-store.php" class="prev">&larr; App Store</a><a href="wolfstack-wolfnote.php" class="next">WolfNote Integration &rarr;</a></div>
        
    </main>
<?php include 'includes/footer.php'; ?>

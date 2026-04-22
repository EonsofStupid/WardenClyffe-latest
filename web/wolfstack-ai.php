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
                <p class="note"><strong>Looking for named, persistent-memory agents?</strong> See <a href="wolfstack-agents.php">WolfAgents</a> &mdash; a multi-agent surface that sits on top of this same AI configuration. The AI Agent page (this one) configures the <em>provider + key</em>; WolfAgents lets you create named personalities that each have their own system prompt, conversation history, tool allowlist, and optional Discord / Telegram / WhatsApp channel binding.</p>

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
                    <li><strong>Proposed actions</strong> &mdash; When the AI identifies a fixable problem, it proposes specific commands you can approve and execute with one click</li>
                    <li><strong>Database access (NEW)</strong> &mdash; The AI can query MariaDB / MySQL / PostgreSQL through operator-defined connection profiles &mdash; ask "how many customers signed up yesterday?" and get a real answer from the live database</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Database Access &mdash; AI Talks to SQL</h2>
                <p>WolfStack&rsquo;s AI can run <strong>real queries against your databases</strong> using the same connection profiles the <a href="wolfstack-mysql.php">Database Manager</a> uses. Ask <em>"how many orders in the last 24 hours?"</em> and the AI writes the SQL, runs it, and feeds the numbers back into its answer &mdash; all routed through the cluster, all audit-logged, all enterprise-ACL-gated.</p>
                <h3>How it&rsquo;s gated</h3>
                <p>Every AI SQL call passes through three independent checks:</p>
                <ol>
                    <li><strong>Per-agent permission flags</strong> &mdash; each agent has separate <code>sql_read</code>, <code>sql_update</code>, and <code>sql_delete</code> switches. All default <strong>off</strong>. DDL (ALTER / CREATE / DROP) is never granted to agents regardless of flags &mdash; schema changes always require a human operator.</li>
                    <li><strong>Connection allowlist</strong> &mdash; an operator explicitly picks which connection IDs each agent is allowed to touch. Empty list means no database access.</li>
                    <li><strong>sqlparser classifier</strong> &mdash; every query is parsed and classified before dispatch. An <code>UPDATE</code> pretending to be a <code>SELECT</code> is rejected. Multi-statement queries are rejected outright. The tier the agent declared must equal-or-exceed what the parser detected.</li>
                </ol>
                <h3>Audit trail</h3>
                <p>Every AI query is appended to <code>/var/log/wolfstack/sql-audit.log</code> with the agent ID, connection ID, full query text, outcome, row count, and elapsed time. You can review exactly what an agent asked the database at any point in the past.</p>
                <h3>Why this is safe</h3>
                <p>AI agents use the <strong>same cluster-aware pipeline</strong> as the UI &mdash; encrypted passwords at rest, cluster-secret authentication for inter-node proxying, enterprise per-user ACL on every profile, and the hardcoded API denylist that blocks agents from reaching <code>/api/sql-connections/*</code> through the generic <code>wolfstack_api</code> tool. The only path to SQL is the dedicated, gated <code>sql_query</code> tool.</p>
            </div>

            <div class="content-section">
                <h2>AI Actions &mdash; Propose &amp; Fix</h2>
                <p>When the AI diagnoses an issue, it doesn&rsquo;t just tell you what&rsquo;s wrong &mdash; it <strong>proposes the fix</strong> and lets you approve it with one click.</p>

                <h3>How It Works</h3>
                <ol>
                    <li><strong>You ask a question</strong> or the AI detects an issue during a health scan</li>
                    <li><strong>The AI proposes actions</strong> &mdash; each fix is shown as a card with the exact command, a risk level, and an explanation of what it does</li>
                    <li><strong>You approve or dismiss</strong> &mdash; nothing executes without your explicit click</li>
                    <li><strong>Results appear instantly</strong> &mdash; command output is shown in the chat, and the action is logged to the audit trail</li>
                </ol>

                <h3>Risk Levels</h3>
                <table>
                    <thead><tr><th>Level</th><th>Examples</th><th>Badge</th></tr></thead>
                    <tbody>
                        <tr><td><strong>Low</strong></td><td>Service restarts, cache clears, config reloads</td><td style="color:#22c55e;">Green</td></tr>
                        <tr><td><strong>Medium</strong></td><td>Config file changes, package installs, firewall rules</td><td style="color:#f59e0b;">Amber</td></tr>
                        <tr><td><strong>High</strong></td><td>Disk operations, user management, network changes</td><td style="color:#ef4444;">Red</td></tr>
                    </tbody>
                </table>

                <h3>Safety Guardrails</h3>
                <ul>
                    <li><strong>Never auto-executes</strong> &mdash; every action requires explicit user approval</li>
                    <li><strong>Catastrophic commands blocked</strong> &mdash; patterns like <code>rm -rf /</code>, <code>dd</code> to disks, fork bombs, and filesystem wipes are permanently blocked even if approved</li>
                    <li><strong>30-second timeout</strong> &mdash; commands that hang are automatically killed</li>
                    <li><strong>10-minute expiry</strong> &mdash; proposed actions expire if not approved within 10 minutes</li>
                    <li><strong>Full audit log</strong> &mdash; every proposed, approved, rejected, and executed action is logged to <code>/etc/wolfstack/ai-actions.log</code> with timestamps, usernames, and results</li>
                    <li><strong>Cluster-wide</strong> &mdash; actions can target the local node or all nodes in the cluster</li>
                </ul>

                <h3>Actions in Alert Emails</h3>
                <p>When the AI health scanner detects an issue and proposes a fix, the <strong>alert email includes the proposed actions</strong> with their risk level and commands. You can then open the WolfStack dashboard to approve them.</p>
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

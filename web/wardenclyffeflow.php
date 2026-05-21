<?php
$page_title = 'WardenClyffeFlow Automation — WardenClyffe Docs';
$page_desc = 'WardenClyffeFlow — visual workflow automation for WardenClyffe. Design, schedule, and execute multi-step infrastructure automation across your entire cluster with a drag-and-drop editor.';
$page_keywords = 'WardenClyffeFlow, workflow automation, infrastructure automation, cron, scheduling, visual editor, WardenClyffe, n8n, devops, runbook, orchestration';
$active = 'wardenclyffeflow.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">

            <div class="content-section">
                <h1>Overview</h1>
                <p>WardenClyffeFlow is WardenClyffe&rsquo;s visual workflow automation engine. It lets you design multi-step runbooks in a drag-and-drop editor, schedule them with cron expressions, and execute them across your entire infrastructure &mdash; all from the WardenClyffe dashboard.</p>

                <p>Think of it as <strong>n8n or Rundeck built directly into your server management platform</strong>. No external dependencies, no separate services to install &mdash; WardenClyffeFlow runs inside WardenClyffe and has full access to every node, container, and VM in your cluster.</p>

                <h3>Key Features</h3>
                <ul>
                    <li><strong>Visual workflow editor</strong> &mdash; Drag-and-drop canvas with a toolbox of built-in actions, step-by-step flow, and per-step configuration</li>
                    <li><strong>16 built-in action types</strong> &mdash; Update packages, update WardenClyffe, restart services, run shell commands, clean logs, check disk space, restart containers, Docker prune, HTTP requests, Docker image updates, NetBird management, TrueNAS snapshots, Unifi device control, conditional branching, and more</li>
                    <li><strong>Flexible targeting</strong> &mdash; Execute on a single node, all nodes, a specific cluster, hand-picked nodes, or individual containers and VMs</li>
                    <li><strong>Cron scheduling</strong> &mdash; Full 5-field cron expressions with quick presets (daily, hourly, weekly, etc.) or manual-only execution</li>
                    <li><strong>Parallel cross-node execution</strong> &mdash; Steps run on all target nodes simultaneously, with results collected per-node before moving to the next step</li>
                    <li><strong>Failure policies</strong> &mdash; Choose Continue, Abort, or Alert per step to control what happens when something goes wrong</li>
                    <li><strong>Email notifications</strong> &mdash; Get HTML-formatted results emails with step-by-step output when workflows complete</li>
                    <li><strong>Per-step target overrides</strong> &mdash; Run most steps on all nodes but target a specific container for one step</li>
                    <li><strong>Infrastructure explorer</strong> &mdash; Browse your entire cluster tree (nodes, Docker containers, LXC containers, Proxmox VMs) when selecting targets</li>
                    <li><strong>Execution history</strong> &mdash; Full run logs with per-node, per-step status, duration, and output</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>The Visual Editor</h2>
                <p>WardenClyffeFlow&rsquo;s editor is a three-panel layout designed for fast workflow creation:</p>

                <h3>Left Panel &mdash; Toolbox</h3>
                <p>Eight action cards, each colour-coded by type. Click or drag an action to add it as a new step in your workflow.</p>

                <h3>Centre Panel &mdash; Canvas</h3>
                <p>Your workflow is displayed as a vertical flow of step cards connected by arrows. Each card shows the step name, action type, and target scope. Click the <strong>+</strong> button between steps to insert a new step, or drag from the toolbox to reorder. Click any step to select it and edit its properties.</p>

                <h3>Right Panel &mdash; Properties</h3>
                <p>When a step is selected, the properties panel shows:</p>
                <ul>
                    <li><strong>Step name</strong> &mdash; Editable label for the step</li>
                    <li><strong>Action type</strong> &mdash; Change the action (parameters update automatically)</li>
                    <li><strong>Action-specific fields</strong> &mdash; Text inputs, dropdowns, number fields depending on the action</li>
                    <li><strong>On failure policy</strong> &mdash; Continue, Abort, or Alert</li>
                    <li><strong>Target override</strong> &mdash; Optionally override the workflow-level target for this step only</li>
                </ul>

                <h3>Toolbar</h3>
                <p>The toolbar across the top of the editor provides:</p>
                <ul>
                    <li><strong>Workflow name</strong> &mdash; Large editable text field</li>
                    <li><strong>Schedule</strong> &mdash; Cron expression input with quick preset buttons</li>
                    <li><strong>Enabled toggle</strong> &mdash; Enable or disable the workflow</li>
                    <li><strong>Email results</strong> &mdash; Optional email address for completion notifications</li>
                    <li><strong>Save</strong> and <strong>Run Now</strong> buttons</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Action Types</h2>
                <p>WardenClyffeFlow includes 16 built-in actions that cover the most common infrastructure automation tasks:</p>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Action</th>
                                <th>Description</th>
                                <th>Parameters</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Update System Packages</strong></td>
                                <td>Runs the system package manager (apt, dnf, pacman, or zypper) to update and upgrade all packages</td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><strong>Update WardenClyffe</strong></td>
                                <td>Pulls and installs the latest WardenClyffe from GitHub</td>
                                <td><code>channel</code> &mdash; Branch name (default: master)</td>
                            </tr>
                            <tr>
                                <td><strong>Restart Service</strong></td>
                                <td>Restarts a systemd service by name</td>
                                <td><code>service_name</code> &mdash; e.g. nginx, docker, mysql</td>
                            </tr>
                            <tr>
                                <td><strong>Run Shell Command</strong></td>
                                <td>Executes an arbitrary shell command with timeout protection</td>
                                <td><code>command</code> &mdash; Shell command<br><code>timeout_secs</code> &mdash; Timeout (default: 300s)</td>
                            </tr>
                            <tr>
                                <td><strong>Clean Journal Logs</strong></td>
                                <td>Vacuums the systemd journal to free disk space</td>
                                <td><code>max_size_mb</code> &mdash; Maximum journal size (default: 500 MB)</td>
                            </tr>
                            <tr>
                                <td><strong>Check Disk Space</strong></td>
                                <td>Checks disk usage and fails if above the threshold. Automatically excludes network filesystems (NFS, CIFS, SSHFS) to prevent hangs</td>
                                <td><code>warn_threshold_pct</code> &mdash; Alert threshold (default: 90%)<br><code>mount_point</code> &mdash; Target mount or &ldquo;all&rdquo;</td>
                            </tr>
                            <tr>
                                <td><strong>Restart Container</strong></td>
                                <td>Restarts a Docker or LXC container by name</td>
                                <td><code>runtime</code> &mdash; docker or lxc<br><code>name</code> &mdash; Container name</td>
                            </tr>
                            <tr>
                                <td><strong>Docker Prune</strong></td>
                                <td>Removes all unused Docker images, containers, volumes, and networks (<code>docker system prune -af</code>)</td>
                                <td>None</td>
                            </tr>
                            <tr>
                                <td><strong>HTTP Request</strong></td>
                                <td>Sends an HTTP request to any URL. Useful for triggering webhooks, health checks, or external API calls</td>
                                <td><code>url</code> &mdash; Target URL<br><code>method</code> &mdash; GET, POST, PUT, DELETE<br><code>headers</code> &mdash; Optional headers (JSON)<br><code>body</code> &mdash; Optional request body</td>
                            </tr>
                            <tr>
                                <td><strong>Docker Image Update</strong></td>
                                <td>Checks upstream registries for newer images, backs up the running container, pulls the new image, and recreates the container. Supports one-click rollback</td>
                                <td><code>container_name</code> &mdash; Container to update<br><code>backup</code> &mdash; Create backup before update (default: true)</td>
                            </tr>
                            <tr>
                                <td><strong>NetBird Management</strong></td>
                                <td>Manages NetBird network peers &mdash; list peers, enable/disable routes, restart the NetBird service</td>
                                <td><code>action</code> &mdash; list_peers, enable_route, disable_route, restart<br><code>api_key</code> &mdash; NetBird API key<br><code>peer_id</code> &mdash; Target peer (optional)</td>
                            </tr>
                            <tr>
                                <td><strong>TrueNAS Integration</strong></td>
                                <td>Interacts with TrueNAS SCALE/CORE API for snapshot management, replication triggers, and pool health checks</td>
                                <td><code>action</code> &mdash; create_snapshot, list_snapshots, check_pools<br><code>api_url</code> &mdash; TrueNAS API endpoint<br><code>api_key</code> &mdash; TrueNAS API key<br><code>dataset</code> &mdash; Target dataset (optional)</td>
                            </tr>
                            <tr>
                                <td><strong>Unifi Device Control</strong></td>
                                <td>Controls Unifi network devices &mdash; restart APs, enable/disable ports, force provision, and query device status</td>
                                <td><code>action</code> &mdash; restart_device, enable_port, disable_port, provision<br><code>controller_url</code> &mdash; Unifi controller URL<br><code>api_key</code> &mdash; Unifi API key<br><code>device_mac</code> &mdash; Target device MAC</td>
                            </tr>
                            <tr>
                                <td><strong>Conditional Branch</strong></td>
                                <td>Evaluates a condition and skips subsequent steps if the condition is not met. Use to create if/else logic in workflows</td>
                                <td><code>condition</code> &mdash; Shell command that returns 0 (true) or non-zero (false)<br><code>skip_count</code> &mdash; Number of steps to skip if false (default: 1)</td>
                            </tr>
                            <tr>
                                <td><strong>Send Notification</strong></td>
                                <td>Sends a notification via Discord, Slack, Telegram, or email mid-workflow. Useful for status updates during long-running automations</td>
                                <td><code>channel</code> &mdash; discord, slack, telegram, email<br><code>message</code> &mdash; Notification text<br><code>webhook_url</code> &mdash; Webhook URL (Discord/Slack)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p>Every action has built-in timeout protection and captures stdout/stderr output (up to 5,000 characters per step) for review in the execution history.</p>
            </div>

            <div class="content-section">
                <h2>Execution Targets</h2>
                <p>WardenClyffeFlow supports five targeting scopes. Set a default target at the workflow level, then optionally override it per step.</p>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Target</th>
                                <th>Behaviour</th>
                                <th>Use Case</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Local</strong></td>
                                <td>Execute only on the primary WardenClyffe node</td>
                                <td>Single-node maintenance, leader-only tasks</td>
                            </tr>
                            <tr>
                                <td><strong>All Nodes</strong></td>
                                <td>Execute on every online WardenClyffe node</td>
                                <td>Fleet-wide updates, log cleanup, disk checks</td>
                            </tr>
                            <tr>
                                <td><strong>Cluster</strong></td>
                                <td>Execute on all nodes in a named cluster</td>
                                <td>Multi-cluster environments where you want to target one cluster at a time</td>
                            </tr>
                            <tr>
                                <td><strong>Specific Nodes</strong></td>
                                <td>Hand-pick individual nodes by ID</td>
                                <td>Staged rollouts, targeting a subset of your fleet</td>
                            </tr>
                            <tr>
                                <td><strong>Containers / VMs</strong></td>
                                <td>Target specific Docker containers, LXC containers, or Proxmox VMs</td>
                                <td>Container lifecycle management, targeted restarts</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <h3>Infrastructure Explorer</h3>
                <p>When selecting targets, click <strong>Set Target</strong> to open the infrastructure explorer. It displays your entire cluster tree with:</p>
                <ul>
                    <li>All clusters and nodes with online/offline status</li>
                    <li>Docker containers (blue icon) with running/stopped state</li>
                    <li>LXC containers (green icon) with running/stopped state</li>
                    <li>Proxmox VMs (orange icon) with running/stopped state</li>
                    <li>Multi-select checkboxes for picking exactly what you need</li>
                </ul>
                <p>The infrastructure tree is cached for 60 seconds and refreshes automatically.</p>
            </div>

            <div class="content-section">
                <h2>Scheduling</h2>
                <p>WardenClyffeFlow uses standard 5-field cron expressions (UTC) for scheduling:</p>
                <div class="code-block" style="margin:0.5rem 0 1rem 0;">
                    <div class="code-header"><span class="code-lang">format</span></div>
                    <pre><code>minute  hour  day-of-month  month  day-of-week</code></pre>
                </div>

                <h3>Quick Presets</h3>
                <p>The editor includes one-click preset buttons for common schedules:</p>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Preset</th>
                                <th>Expression</th>
                                <th>Meaning</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Daily 3am</td><td><code>0 3 * * *</code></td><td>Every day at 03:00 UTC</td></tr>
                            <tr><td>Midnight</td><td><code>0 0 * * *</code></td><td>Every day at 00:00 UTC</td></tr>
                            <tr><td>Hourly</td><td><code>0 * * * *</code></td><td>Every hour on the hour</td></tr>
                            <tr><td>15min</td><td><code>*/15 * * * *</code></td><td>Every 15 minutes</td></tr>
                            <tr><td>Weekly Sun</td><td><code>0 2 * * 0</code></td><td>Every Sunday at 02:00 UTC</td></tr>
                            <tr><td>Monthly</td><td><code>0 2 1 * *</code></td><td>1st of every month at 02:00 UTC</td></tr>
                            <tr><td>Manual</td><td><em>(blank)</em></td><td>No schedule &mdash; run manually only</td></tr>
                        </tbody>
                    </table>
                </div>

                <h3>Cron Syntax</h3>
                <ul>
                    <li><code>*</code> &mdash; Any value</li>
                    <li><code>5</code> &mdash; Specific value</li>
                    <li><code>1,15,30</code> &mdash; Multiple values</li>
                    <li><code>1-5</code> &mdash; Range</li>
                    <li><code>*/15</code> &mdash; Every N units</li>
                    <li><code>1-30/5</code> &mdash; Every 5th value in range 1&ndash;30</li>
                    <li>Day-of-week: Sunday = 0 or 7, Monday = 1 &hellip; Saturday = 6</li>
                </ul>

                <p>The built-in scheduler checks all enabled workflows every 60 seconds. Duplicate runs within the same minute are automatically prevented.</p>
            </div>

            <div class="content-section">
                <h2>Failure Handling</h2>
                <p>Each step has an <strong>on-failure policy</strong> that controls what happens when the step fails on one or more target nodes:</p>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Policy</th>
                                <th>Behaviour</th>
                                <th>Use Case</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>Abort</strong> (default)</td>
                                <td>Stop the entire workflow immediately. No remaining steps execute. Workflow marked as Failed.</td>
                                <td>Critical operations where proceeding after a failure would be dangerous</td>
                            </tr>
                            <tr>
                                <td><strong>Continue</strong></td>
                                <td>Log the failure and proceed to the next step. Workflow marked as Partial Failure at the end.</td>
                                <td>Non-critical checks, optional maintenance tasks</td>
                            </tr>
                            <tr>
                                <td><strong>Alert</strong></td>
                                <td>Log an error alert and continue. Similar to Continue but with elevated visibility in the task log.</td>
                                <td>Steps you want to be notified about but that shouldn&rsquo;t block the workflow</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p>When a workflow targets multiple nodes, each node executes independently. One node failing a step doesn&rsquo;t prevent other nodes from completing that step &mdash; the failure policy determines whether the <em>workflow</em> continues to the next step.</p>
            </div>

            <div class="content-section">
                <h2>Execution &amp; Monitoring</h2>

                <h3>How Workflows Execute</h3>
                <ol>
                    <li><strong>Trigger</strong> &mdash; A workflow runs either on its cron schedule or when you click <strong>Run Now</strong></li>
                    <li><strong>Resolve targets</strong> &mdash; WardenClyffeFlow resolves the target scope into a list of online nodes</li>
                    <li><strong>Execute steps sequentially</strong> &mdash; Each step runs on all target nodes <strong>in parallel</strong>, then results are collected before the next step begins</li>
                    <li><strong>Remote execution</strong> &mdash; For remote nodes, commands are sent via the WardenClyffe node proxy API with cluster secret authentication</li>
                    <li><strong>Collect results</strong> &mdash; Each step records per-node status (success/failure), output, and duration</li>
                    <li><strong>Email results</strong> &mdash; If an email address is configured, an HTML report is sent with a detailed step-by-step breakdown</li>
                </ol>

                <h3>Task Log Integration</h3>
                <p>Every workflow execution appears in WardenClyffe&rsquo;s main task log with live progress updates. You can see:</p>
                <ul>
                    <li>Which step is currently executing</li>
                    <li>Per-node status with colour-coded badges (success, failed, running)</li>
                    <li>Command output for each step</li>
                    <li>Total duration and final status</li>
                </ul>

                <h3>Run History</h3>
                <p>WardenClyffeFlow keeps a history of the last 500 workflow runs. Each run record includes:</p>
                <ul>
                    <li>Workflow name and run ID</li>
                    <li>Trigger type (Manual or Scheduled)</li>
                    <li>Start time and total duration</li>
                    <li>Overall status (Completed, Failed, Partial Failure, Running)</li>
                    <li>Per-step, per-node results with output</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Email Notifications</h2>
                <p>When an email address is set on a workflow, WardenClyffeFlow sends an HTML-formatted results email after every run. The email includes:</p>
                <ul>
                    <li><strong>Status header</strong> &mdash; Workflow name, overall status (Completed/Failed/Partial Failure), and trigger type</li>
                    <li><strong>Duration</strong> &mdash; Total execution time</li>
                    <li><strong>Step table</strong> &mdash; Each step with target node hostname, status badge, duration, and command output (truncated to 500 characters per step)</li>
                </ul>
                <p>Email uses the SMTP settings configured in <strong>Settings &rarr; AI Agent</strong>. Make sure SMTP is configured before enabling email notifications.</p>
            </div>

            <div class="content-section">
                <h2>Example Workflows</h2>

                <h3>Daily Fleet Maintenance</h3>
                <div class="code-block" style="margin:0.5rem 0 1rem 0;">
                    <div class="code-header"><span class="code-lang">workflow</span></div>
                    <pre><code>Schedule: 0 3 * * *  (Daily at 3 AM UTC)
Target:   All Nodes

Step 1: Check Disk Space        [on failure: Continue]
         warn_threshold_pct: 90
         mount_point: all

Step 2: Clean Journal Logs      [on failure: Continue]
         max_size_mb: 500

Step 3: Update System Packages  [on failure: Alert]

Step 4: Docker Prune            [on failure: Continue]

Email results to: admin@example.com</code></pre>
                </div>

                <h3>Staged WardenClyffe Update</h3>
                <div class="code-block" style="margin:0.5rem 0 1rem 0;">
                    <div class="code-header"><span class="code-lang">workflow</span></div>
                    <pre><code>Schedule: Manual only
Target:   Specific Nodes (staging-node-01)

Step 1: Update WardenClyffe        [on failure: Abort]
         channel: master

Step 2: Run Shell Command        [on failure: Abort]
         command: systemctl status wardenclyffe
         timeout_secs: 30

-- After verifying staging, run a second workflow
-- targeting production nodes</code></pre>
                </div>

                <h3>Container Lifecycle</h3>
                <div class="code-block" style="margin:0.5rem 0 1rem 0;">
                    <div class="code-header"><span class="code-lang">workflow</span></div>
                    <pre><code>Schedule: 0 2 * * 0  (Weekly, Sunday at 2 AM UTC)
Target:   Local

Step 1: Run Shell Command        [on failure: Abort]
         command: docker pull myapp:latest

Step 2: Restart Container        [on failure: Abort]
         runtime: docker
         name: myapp

Step 3: Run Shell Command        [on failure: Alert]
         command: curl -sf http://localhost:8080/health
         timeout_secs: 30</code></pre>
                </div>

                <h3>Multi-Cluster Health Check</h3>
                <div class="code-block" style="margin:0.5rem 0 1rem 0;">
                    <div class="code-header"><span class="code-lang">workflow</span></div>
                    <pre><code>Schedule: */15 * * * *  (Every 15 minutes)
Target:   All Nodes

Step 1: Check Disk Space        [on failure: Continue]
         warn_threshold_pct: 85
         mount_point: all

Step 2: Run Shell Command        [on failure: Continue]
         command: systemctl is-active docker || echo "Docker not running"

Email results to: oncall@example.com</code></pre>
                </div>
            </div>

            <div class="content-section">
                <h2>REST API</h2>
                <p>WardenClyffeFlow exposes a full REST API for programmatic access:</p>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Endpoint</th>
                                <th>Method</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>/api/wardenclyffeflow/workflows</code></td>
                                <td>GET</td>
                                <td>List all workflows (optional <code>?cluster=</code> filter)</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/workflows</code></td>
                                <td>POST</td>
                                <td>Create a new workflow</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/workflows/{id}</code></td>
                                <td>GET</td>
                                <td>Get a workflow by ID</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/workflows/{id}</code></td>
                                <td>PUT</td>
                                <td>Update a workflow</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/workflows/{id}</code></td>
                                <td>DELETE</td>
                                <td>Delete a workflow</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/workflows/{id}/run</code></td>
                                <td>POST</td>
                                <td>Trigger a workflow manually</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/runs</code></td>
                                <td>GET</td>
                                <td>List execution history (<code>?workflow_id=</code> and <code>?limit=</code> filters)</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/runs/{id}</code></td>
                                <td>GET</td>
                                <td>Get a single run with full step results</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/toolbox</code></td>
                                <td>GET</td>
                                <td>List available actions with field definitions</td>
                            </tr>
                            <tr>
                                <td><code>/api/wardenclyffeflow/infrastructure</code></td>
                                <td>GET</td>
                                <td>Full infrastructure tree (clusters, nodes, containers, VMs)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p>All endpoints require a valid <code>wardenclyffe_session</code> cookie. Inter-node execution uses the <code>X-WardenClyffe-Secret</code> header for authentication.</p>
            </div>

            <div class="content-section">
                <h2>Configuration &amp; Persistence</h2>
                <p>WardenClyffeFlow stores all data as JSON files:</p>
                <ul>
                    <li><code>/etc/wardenclyffe/wardenclyffeflow/workflows.json</code> &mdash; All workflow definitions</li>
                    <li><code>/etc/wardenclyffe/wardenclyffeflow/runs.json</code> &mdash; Execution history (last 500 runs)</li>
                </ul>
                <p>Data is loaded on startup and saved automatically after every create, update, delete, or run operation. The <code>/etc/wardenclyffe/wardenclyffeflow/</code> directory is created automatically if it doesn&rsquo;t exist.</p>
                <p>Back up the <code>/etc/wardenclyffe/wardenclyffeflow/</code> directory to preserve your workflows and run history.</p>
            </div>

            <div class="content-section">
                <h2>WardenClyffeFlow vs. Cron Jobs</h2>
                <p>WardenClyffe already has a <a href="wardenclyffe-cron.php">Cron Jobs</a> page for managing system crontabs. WardenClyffeFlow is a higher-level tool that builds on top of that concept:</p>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Feature</th>
                                <th>Cron Jobs</th>
                                <th>WardenClyffeFlow</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>Scope</td><td>Single node</td><td>Entire cluster (cross-node)</td></tr>
                            <tr><td>Interface</td><td>Crontab editor</td><td>Visual drag-and-drop editor</td></tr>
                            <tr><td>Multi-step</td><td>No (single command)</td><td>Yes (sequential steps with dependencies)</td></tr>
                            <tr><td>Failure handling</td><td>None</td><td>Continue / Abort / Alert per step</td></tr>
                            <tr><td>Targeting</td><td>Runs on one node</td><td>Local, all nodes, cluster, specific nodes, containers</td></tr>
                            <tr><td>Execution history</td><td>Syslog only</td><td>Full run history with per-step output</td></tr>
                            <tr><td>Email results</td><td>Basic (MAILTO)</td><td>Rich HTML reports</td></tr>
                            <tr><td>Built-in actions</td><td>No (shell commands)</td><td>16 purpose-built actions + shell commands</td></tr>
                        </tbody>
                    </table>
                </div>
                <p>Use Cron Jobs for simple, single-node tasks. Use WardenClyffeFlow for multi-step, multi-node automation with visibility and failure handling.</p>
            </div>

<div class="page-nav"><a href="wardenclyffe-cron.php" class="prev">&larr; Cron Jobs</a><a href="wardenclyffe-ai.php" class="next">AI Agent &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

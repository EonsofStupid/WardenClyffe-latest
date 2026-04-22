<?php
$page_title = 'Security, Users &amp; Authentication — WolfStack Docs';
$page_desc = 'WolfStack security features — user accounts, two-factor authentication (2FA), firewall management, Fail2ban, rate limiting, encryption, and best practices.';
$active = 'wolfstack-security.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content" style="max-width:1100px;">


            <div class="content-section">
                <h2>Security Overview</h2>
                <p>WolfStack provides a complete security layer for your infrastructure &mdash; from user authentication
                    with two-factor authentication to firewall management, encryption, and brute-force protection.</p>
            </div>

            <!-- Authentication -->
            <div class="content-section">
                <h2>Authentication</h2>
                <p>WolfStack supports three authentication modes, configurable from <strong>Settings &rarr; Users &amp; Auth</strong>:</p>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:12px;margin:16px 0;">
                    <div style="background:var(--bg-tertiary);border:1px solid var(--border-color);border-radius:8px;padding:16px;">
                        <h3 style="font-size:0.92rem;margin-bottom:6px;">Linux System Login</h3>
                        <p style="font-size:0.82rem;color:var(--text-secondary);">Authenticate with your existing Linux user credentials via <code>/etc/shadow</code>. This is the default mode &mdash; any user on the server can log in.</p>
                    </div>
                    <div style="background:var(--bg-tertiary);border:1px solid var(--border-color);border-radius:8px;padding:16px;">
                        <h3 style="font-size:0.92rem;margin-bottom:6px;">WolfStack Users Only</h3>
                        <p style="font-size:0.82rem;color:var(--text-secondary);">Disable Linux login entirely. Only WolfStack user accounts can log in. Supports optional two-factor authentication (2FA) per user.</p>
                    </div>
                    <div style="background:var(--bg-tertiary);border:1px solid var(--border-color);border-radius:8px;padding:16px;">
                        <h3 style="font-size:0.92rem;margin-bottom:6px;">Both</h3>
                        <p style="font-size:0.82rem;color:var(--text-secondary);">Allow either Linux system credentials or WolfStack user accounts. WolfStack users are checked first.</p>
                    </div>
                </div>

                <img src="images/screenshots/users-auth.png" alt="WolfStack Users &amp; Auth settings showing authentication mode selection and user management" class="screenshot" loading="lazy">
            </div>

            <!-- WolfStack User Accounts -->
            <div class="content-section">
                <h2>WolfStack User Accounts</h2>
                <p>Create dedicated WolfStack user accounts with their own passwords, independent of Linux system users. Each user has:</p>
                <ul>
                    <li><strong>Username and password</strong> &mdash; passwords are hashed using SHA-512 crypt (same algorithm as Linux <code>/etc/shadow</code>)</li>
                    <li><strong>Role</strong> &mdash; <code>admin</code> (full access) or <code>viewer</code> (read-only)</li>
                    <li><strong>Display name</strong> &mdash; optional friendly name shown in the UI</li>
                    <li><strong>Two-factor authentication</strong> &mdash; optional TOTP-based 2FA per user</li>
                </ul>

                <h3>Managing Users</h3>
                <p>Go to <strong>Settings &rarr; Users &amp; Auth</strong> to:</p>
                <ul>
                    <li>Create new users with the <strong>+ Add User</strong> button</li>
                    <li>Change passwords for existing users</li>
                    <li>Enable or disable two-factor authentication</li>
                    <li>Delete users</li>
                </ul>
                <p>User accounts are stored in <code>/etc/wolfstack/users.json</code> (configurable via <strong>Settings &rarr; File Locations</strong>).</p>

                <div class="warning-box">
                    <p><strong>Important:</strong> Before switching to <em>WolfStack Users Only</em> mode, create at least one WolfStack user account. Otherwise you will be locked out of the dashboard.</p>
                </div>
            </div>

            <!-- Two-Factor Authentication -->
            <div class="content-section">
                <h2>Two-Factor Authentication (2FA)</h2>
                <p>WolfStack supports TOTP-based two-factor authentication, compatible with:</p>
                <ul>
                    <li>Google Authenticator</li>
                    <li>Authy</li>
                    <li>1Password</li>
                    <li>Microsoft Authenticator</li>
                    <li>Any TOTP-compatible app (RFC 6238)</li>
                </ul>

                <h3>Setting Up 2FA</h3>
                <ol>
                    <li>Go to <strong>Settings &rarr; Users &amp; Auth</strong></li>
                    <li>Click <strong>Enable 2FA</strong> next to a user</li>
                    <li>Scan the QR code with your authenticator app (or enter the manual key)</li>
                    <li>Enter the 6-digit code from your app to confirm</li>
                    <li>2FA is now active &mdash; the user will be prompted for a code on every login</li>
                </ol>

                <h3>Login Flow with 2FA</h3>
                <p>When a user with 2FA enabled logs in:</p>
                <ol>
                    <li>Enter username and password as normal</li>
                    <li>If the password is correct, a <strong>two-factor code</strong> input appears</li>
                    <li>Enter the 6-digit code from the authenticator app</li>
                    <li>Login is complete</li>
                </ol>

                <div class="info-box">
                    <p><strong>Clock skew tolerance:</strong> WolfStack accepts codes from the current time step and &plusmn;1 step (30 seconds each way), so minor clock differences between your server and phone won&rsquo;t cause issues.</p>
                </div>
            </div>

            <!-- Cluster Authentication -->
            <div class="content-section">
                <h2>Cluster Authentication</h2>
                <table>
                    <thead>
                        <tr><th>Method</th><th>Purpose</th><th>Details</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Cluster secrets</strong></td>
                            <td>Inter-node API auth</td>
                            <td>Shared secret sent via <code>X-WolfStack-Secret</code> header. Generate a custom secret in Settings &rarr; Security.</td>
                        </tr>
                        <tr>
                            <td><strong>Join tokens</strong></td>
                            <td>Adding new nodes</td>
                            <td>Per-node tokens for cluster join operations. View with <code>wolfstack --show-token</code>.</td>
                        </tr>
                        <tr>
                            <td><strong>Session cookies</strong></td>
                            <td>Browser sessions</td>
                            <td>8-hour lifetime, HTTP-only, SameSite=Strict. Secure flag enabled when TLS is active.</td>
                        </tr>
                    </tbody>
                </table>

                <img src="images/screenshots/settings-security.png" alt="WolfStack cluster security settings showing custom cluster secret management" class="screenshot" loading="lazy">
            </div>

            <!-- Login Protection -->
            <div class="content-section">
                <h2>Login Protection</h2>
                <ul>
                    <li><strong>Rate limiting</strong> &mdash; Maximum 10 login attempts per 5 minutes per IP address. Lockouts clear automatically.</li>
                    <li><strong>Session management</strong> &mdash; Sessions expire after 8 hours. Expired sessions are cleaned up every 5 minutes.</li>
                    <li><strong>Disable direct login</strong> &mdash; Individual nodes can have direct login disabled, forcing users to access them through the primary dashboard node.</li>
                </ul>
            </div>

            <!-- Firewall Management -->
            <div class="content-section">
                <h2>Firewall Management</h2>
                <p>WolfStack provides a web interface for managing Linux firewall tools on each node.</p>

                <img src="images/screenshots/security.png" alt="WolfStack security view showing Fail2ban, UFW, and iptables management" class="screenshot" loading="lazy">

                <h3>Fail2ban</h3>
                <ul>
                    <li>Install and configure Fail2ban from the dashboard</li>
                    <li>View banned IPs across all jails</li>
                    <li>Unban IPs with one click</li>
                    <li>Rebuild configuration when needed</li>
                </ul>

                <h3>UFW (Uncomplicated Firewall)</h3>
                <ul>
                    <li>Enable or disable the firewall</li>
                    <li>Add and remove rules (allow/deny by port, protocol, source)</li>
                    <li>View all active rules</li>
                </ul>

                <h3>iptables</h3>
                <ul>
                    <li>View current iptables rules across all chains</li>
                    <li>Read-only view for advanced users who manage iptables directly</li>
                </ul>
            </div>

            <!-- Encryption -->
            <div class="content-section">
                <h2>Encryption</h2>
                <ul>
                    <li><strong>WolfNet</strong> &mdash; All inter-node traffic is encrypted with X25519 key exchange and ChaCha20-Poly1305 symmetric encryption</li>
                    <li><strong>HTTPS</strong> &mdash; The dashboard uses TLS by default. Auto-detects Let&rsquo;s Encrypt certificates or uses self-signed.</li>
                    <li><strong>Password storage</strong> &mdash; WolfStack user passwords are hashed with SHA-512 crypt (never stored in plaintext)</li>
                    <li><strong>TOTP secrets</strong> &mdash; Stored server-side in <code>/etc/wolfstack/users.json</code> (root-only readable)</li>
                </ul>
            </div>

            <!-- System Updates -->
            <div class="content-section">
                <h2>System Updates</h2>
                <ul>
                    <li>Check for available system package updates from the dashboard</li>
                    <li>Apply updates across nodes individually or in bulk</li>
                    <li>WolfStack itself can be updated via <strong>WolfFlow</strong> automation or the Issues page</li>
                </ul>
            </div>

            <!-- AI & WolfAgents -->
            <div class="content-section">
                <h2>AI &amp; WolfAgents</h2>
                <p>Enabling AI in WolfStack &mdash; Settings &rarr; AI Agent, or any named WolfAgent &mdash; changes your threat model. The LLM sees untrusted text (chat messages, log tails, alert payloads) and can invoke tools. Here's what to do, not just what to worry about.</p>

                <h3>Pick the right access level</h3>
                <ul>
                    <li><strong>Public-facing channel</strong> (Discord server, WhatsApp number, shared Telegram group) &rarr; start with <code>ReadOnly</code>. Move to <code>ConfirmAll</code> only once you're sure the channel audience is trusted. Never <code>Trusted</code>.</li>
                    <li><strong>Private DM with an oncall engineer</strong> &rarr; <code>ReadWrite</code> is usually right; mutating calls execute, destructive ones queue for your approval.</li>
                    <li><strong>Self-authored automation agent</strong> you wrote the prompt for, reachable only from the dashboard &rarr; <code>Trusted</code> is fine, but understand that the hardcoded safety denylist is the only remaining guard.</li>
                </ul>

                <h3>Narrow target scope &mdash; don't leave wildcards</h3>
                <ul>
                    <li>Fill in <strong>Allowed container patterns</strong> (<code>regions-*, app-*</code>). Empty means "any container the agent can reach", which is usually wider than you think.</li>
                    <li>Fill in <strong>Allowed paths</strong> before granting <code>write_file</code> or <code>schedule_script</code>. <code>/home/wolfgrid1/assetcache</code> is a scope; <code>/</code> is an incident waiting to happen.</li>
                    <li>If granting the Enterprise <code>wolfstack_api</code> tool, fill in <strong>Allowed API paths</strong>. A good starting set: <code>/api/nodes, /api/containers, /api/metrics, /api/alerts</code>. Never leave it empty.</li>
                    <li>If granting <code>send_email</code>, fill in <strong>Allowed email recipients</strong>. Use <code>@yourcompany.com</code> to permit anyone on your team without naming each inbox, but don't leave it empty-with-default-recipient if the agent is reachable from an external surface (Discord server, WhatsApp number). Empty means "only the default alerting recipient", which is safe &mdash; but easy to widen by accident.</li>
                    <li>Scope <strong>Allowed clusters</strong> to staging first. Promote to production only after you've seen the agent behave for a week.</li>
                </ul>

                <h3>Web-reaching tools have their own risks</h3>
                <ul>
                    <li><code>web_fetch</code> and <code>web_render</code> are classified Safe, but "safe" here means "won't mutate your cluster" &mdash; the agent can still exfiltrate what it has already read by encoding state into a query string. If an agent has <code>read_log</code> and <code>web_fetch</code>, treat it as if its memory is exposed to any URL it fetches. Don't enable both on an agent reachable from untrusted chat.</li>
                    <li>Block outbound egress at the firewall for nodes that don't need it &mdash; the SSRF guard stops private-address fetches, but has no opinion on <code>https://pastebin.com/api?data=...</code>.</li>
                    <li><code>web_render</code> shells out to headless Chromium. Install chromium only on nodes where you want this tool live; the absence of the binary is already a working "disabled" switch on the others.</li>
                </ul>

                <h3>Grant the minimum tool set</h3>
                <ul>
                    <li>New agents start with <em>no</em> tools ticked &mdash; keep it that way until the agent proves it needs a specific tool. Don't tick "all of them" to avoid debugging later; that's how a triage agent ends up with <code>exec_on_host</code>.</li>
                    <li>Review the <strong>Audit tab</strong> weekly. Tools the agent never uses should be untickened. Tools the agent uses suspiciously often (10x <code>exec_in_container</code> in an hour) are a prompt-injection indicator.</li>
                    <li>Pair <code>exec_*</code> tools with <code>ConfirmAll</code> unless you've already narrowed target scope to the point where the worst case is acceptable.</li>
                </ul>

                <h3>Treat external channels as hostile input</h3>
                <ul>
                    <li><strong>Discord Message Content intent</strong> &mdash; only enable if you want the agent to actually read messages. A bot with the intent disabled sees only mentions and commands; that's a safer default for shared servers.</li>
                    <li><strong>Telegram receiver</strong> is opt-in per install (Settings &rarr; Alerting &rarr; Enable Telegram receiver). Leave it off on installs that only need outbound alerts.</li>
                    <li><strong>Twilio webhook</strong> for WhatsApp is HMAC-SHA1 signed &mdash; rejected requests never reach the LLM. Don't expose the webhook URL on the public internet beyond what Twilio's egress IPs need; put it behind your reverse proxy with an IP allowlist if you can.</li>
                    <li>Log tails and alert payloads are untrusted too. An agent with <code>read_log</code> can be steered by text an attacker injects into an app log. If an agent reads logs, it should not also have unconfirmed mutating tools.</li>
                </ul>

                <h3>Rotate secrets like you rotate SSH keys</h3>
                <ul>
                    <li><strong>AI provider API keys</strong> (Anthropic, Gemini, OpenRouter) live in Settings &rarr; AI Agent. Rotate them when an operator leaves, after any leak suspicion, or at least every 90 days. WolfStack stores them write-only; the UI never echoes them back.</li>
                    <li><strong>Discord bot token</strong> and <strong>Twilio auth token</strong> &mdash; same rule. API exposes only <code>has_discord_bot</code> / <code>has_twilio_auth</code> booleans, so rotating is safe.</li>
                    <li><strong>Cluster secret</strong> &mdash; the universal <code>wolfstack_api</code> tool authenticates loopback calls with it. Rotate on incident (Settings &rarr; Security &rarr; Regenerate), then re-pair your nodes.</li>
                </ul>

                <h3>Keep prompts and memory private</h3>
                <ul>
                    <li>Memory and audit logs live at <code>0o600</code> in <code>/etc/wolfstack/agents/&lt;id&gt;/</code> with directories at <code>0o700</code>. If you have untrusted local users on a WolfStack host, don't put them in the <code>wolfstack</code> group.</li>
                    <li>Tell users not to paste production secrets into agent chats. Memory is append-only &mdash; a secret typed once lives forever in the file. If it happens, rotate the secret and <code>rm</code> the specific memory file (the agent starts fresh).</li>
                    <li>For sensitive workloads, run a <strong>local model</strong> (Ollama / LM Studio / vLLM) instead of a cloud provider &mdash; chat payloads never leave the host.</li>
                </ul>

                <h3>The safety denylist is a floor, not a ceiling</h3>
                <ul>
                    <li>WolfStack hardcodes denies for <code>rm -rf /</code>, disk wipes, firewall flushes, curl-pipe-bash, and access to <code>/etc/shadow</code>, <code>/etc/passwd</code>, <code>/boot</code>, and WolfStack's own config. These apply <em>above</em> access level &mdash; even a Trusted agent can't bypass them, and an operator can't approve around them.</li>
                    <li>Do not assume the denylist covers every footgun. Combine it with narrow scope (above) and <code>ConfirmAll</code> for anything reachable from outside your team.</li>
                    <li>If you find a novel attack the denylist doesn't catch, file an issue &mdash; the list grows when we see real incidents, not on request.</li>
                </ul>

                <h3>Monitor</h3>
                <ul>
                    <li><strong>Pending tab</strong> should be empty or small. A growing queue means the agent is attempting things it shouldn't &mdash; read them before approving.</li>
                    <li><strong>Audit tab</strong> &mdash; skim weekly. A tool repeatedly being called then denied is the signature of prompt-injection attempts.</li>
                    <li><strong>AI provider usage dashboard</strong> &mdash; a sudden spike in tokens from one agent is worth investigating, especially if it's on a public channel.</li>
                </ul>
            </div>

            <!-- Best Practices -->
            <div class="content-section">
                <h2>Best Practices</h2>
                <ul>
                    <li>Switch to <strong>WolfStack Users Only</strong> mode and enable 2FA for all users</li>
                    <li>Generate a <strong>custom cluster secret</strong> in Settings &rarr; Security</li>
                    <li>Enable <strong>Fail2ban</strong> to automatically ban brute-force attackers</li>
                    <li>Use <strong>UFW</strong> to restrict access to only required ports</li>
                    <li>Use <strong>WolfNet</strong> to keep management traffic off the public internet</li>
                    <li>Keep WolfStack updated to the latest version</li>
                    <li>Disable direct login on secondary nodes &mdash; manage them through the primary dashboard</li>
                </ul>
            </div>

<div class="page-nav"><a href="wolfstack-mysql.php" class="prev">&larr; Databases</a><a href="wolfstack-certificates.php" class="next">Certificates &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

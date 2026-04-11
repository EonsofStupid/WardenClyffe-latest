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

<div class="page-nav"><a href="wolfstack-mysql.php" class="prev">&larr; MySQL Editor</a><a href="wolfstack-certificates.php" class="next">Certificates &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

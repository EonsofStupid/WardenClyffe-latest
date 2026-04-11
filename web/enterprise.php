<?php
$page_title = 'Enterprise Licensing — WolfStack';
$page_desc = 'WolfStack Enterprise Licensing — full support, installation, ticketing and SLA for businesses. GitHub Sponsors get private Discord support and early access.';
$active = 'enterprise.php';
$page_css = '.pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
            margin: 2rem 0;
        }

        .pricing-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 14px;
            padding: 2rem 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .pricing-card:hover {
            border-color: rgba(220, 38, 38, 0.3);
            transform: translateY(-4px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .pricing-card.featured {
            border-color: rgba(220, 38, 38, 0.5);
            box-shadow: 0 4px 24px rgba(220, 38, 38, 0.15);
        }

        .pricing-card.featured::before {
            content: \'Most Popular\';
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            white-space: nowrap;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        .pricing-card.sponsor-card {
            border-color: rgba(34, 197, 94, 0.4);
        }
        .pricing-card.sponsor-card:hover {
            border-color: rgba(34, 197, 94, 0.6);
            box-shadow: 0 8px 32px rgba(34, 197, 94, 0.15);
        }

        .pricing-tier {
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
        }

        .pricing-name {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        .pricing-price {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--accent-primary);
            margin-bottom: 0.25rem;
        }

        .pricing-price .period {
            font-size: 0.82rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .pricing-price .currency {
            font-size: 1.2rem;
            vertical-align: super;
        }

        .pricing-subtitle {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-bottom: 1.5rem;
        }

        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 0 0 1.5rem;
            text-align: left;
            flex: 1;
        }

        .pricing-features li {
            padding: 0.4rem 0;
            font-size: 0.86rem;
            color: var(--text-secondary);
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
        }

        .pricing-features li:last-child {
            border-bottom: none;
        }

        .pricing-cta {
            display: inline-block;
            padding: 10px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
            width: 100%;
        }

        .pricing-cta-primary {
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            box-shadow: 0 4px 15px rgba(220, 38, 38, 0.3);
        }

        .pricing-cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 38, 38, 0.4);
            color: white;
        }

        .pricing-cta-secondary {
            background: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .pricing-cta-secondary:hover {
            border-color: var(--accent-primary);
            transform: translateY(-2px);
            color: var(--text-primary);
        }

        .pricing-cta-green {
            background: linear-gradient(135deg, #16a34a, #22c55e);
            color: white;
            box-shadow: 0 4px 15px rgba(34, 197, 94, 0.3);
        }

        .pricing-cta-green:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.4);
            color: white;
        }

        .enterprise-includes {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .include-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 1.25rem;
            transition: all 0.3s ease;
        }

        .include-card:hover {
            border-color: rgba(220, 38, 38, 0.2);
        }

        .include-card h4 {
            font-size: 0.95rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }

        .include-card p {
            font-size: 0.82rem;
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .contact-box {
            background: linear-gradient(135deg, rgba(220, 38, 38, 0.08), rgba(239, 68, 68, 0.04));
            border: 1px solid rgba(220, 38, 38, 0.2);
            border-radius: 14px;
            padding: 2rem;
            text-align: center;
            margin: 2rem 0;
        }

        .contact-box h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: var(--text-primary);
        }

        .contact-box p {
            font-size: 0.9rem;
            color: var(--text-secondary);
            margin-bottom: 1.25rem;
            line-height: 1.7;
        }

        .contact-email {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 12px 28px;
            background: linear-gradient(135deg, #dc2626, #ef4444);
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            box-shadow: 0 4px 20px rgba(220, 38, 38, 0.3);
            transition: all 0.3s ease;
        }

        .contact-email:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(220, 38, 38, 0.4);
            color: white;
        }

        .sponsor-highlight {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.08), rgba(34, 197, 94, 0.03));
            border: 1px solid rgba(34, 197, 94, 0.2);
            border-radius: 14px;
            padding: 2rem;
            margin: 2rem 0;
        }
        .sponsor-highlight h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--success);
            margin-bottom: 0.75rem;
        }
        .sponsor-highlight p {
            font-size: 0.88rem;
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 0.5rem;
        }
        .sponsor-highlight ul {
            list-style: none;
            padding: 0;
            margin: 1rem 0;
        }
        .sponsor-highlight li {
            padding: 6px 0;
            font-size: 0.88rem;
            color: var(--text-secondary);
            padding-left: 24px;
            position: relative;
        }
        .sponsor-highlight li::before {
            content: "\2713";
            position: absolute;
            left: 0;
            color: var(--success);
            font-weight: 700;
        }

        .comp-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.84rem;
            margin: 1.5rem 0;
        }
        .comp-table th, .comp-table td {
            padding: 10px 14px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            text-align: left;
        }
        .comp-table th {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .comp-table td:first-child {
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
        }
        .comp-table .hl {
            color: #22c55e;
            font-weight: 700;
        }
        .comp-table .paid {
            color: var(--accent-primary);
        }

        @media (max-width: 768px) {
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .enterprise-includes {
                grid-template-columns: 1fr;
            }

            .comp-table {
                font-size: 0.75rem;
            }
            .comp-table th, .comp-table td {
                padding: 6px 8px;
            }
        }';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content" style="max-width:1100px;">


                <div class="content-section">
                    <h2>Simple, Honest Pricing</h2>
                    <p>WolfStack is <strong>free and open-source</strong>. Every feature, unlimited servers, forever.
                        No trials, no feature gates, no node limits.</p>
                    <p>For businesses that need <strong>SLAs, SSO, and dedicated support</strong>, we offer Enterprise licensing.
                        For everyone else, <a href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank"
                        style="color:#22c55e;font-weight:600;">GitHub Sponsors</a> keeps development going.</p>
                </div>

                <!-- Pricing Cards -->
                <div class="content-section">
                    <div class="pricing-grid">

                        <!-- Community -->
                        <div class="pricing-card">
                            <div class="pricing-tier">Community</div>
                            <div class="pricing-name">Free</div>
                            <div class="pricing-price" style="color:#22c55e;">Free</div>
                            <div class="pricing-subtitle">Every feature. Every server. Forever.</div>
                            <ul class="pricing-features">
                                <li>All features included</li>
                                <li>Unlimited servers &amp; clusters</li>
                                <li>Docker, LXC &amp; VM management</li>
                                <li>WolfNet encrypted mesh VPN</li>
                                <li>510+ one-click apps</li>
                                <li>Backups, status pages, alerting</li>
                                <li>WolfFlow workflow automation</li>
                                <li>Full source code (MIT licence)</li>
                                <li>Public Discord community</li>
                            </ul>
                            <a href="wolfstack.php"
                                class="pricing-cta pricing-cta-secondary">Get Started Free</a>
                        </div>

                        <!-- Sponsor -->
                        <div class="pricing-card sponsor-card">
                            <div class="pricing-tier">Sponsor</div>
                            <div class="pricing-name">GitHub Sponsors</div>
                            <div class="pricing-price" style="color:#22c55e;">From <span class="currency">&pound;</span>3 <span class="period">/ month</span></div>
                            <div class="pricing-subtitle">Keep WolfStack free for everyone</div>
                            <ul class="pricing-features">
                                <li>Everything in Community</li>
                                <li><strong>Private sponsors-only Discord</strong></li>
                                <li><strong>Direct support from the dev team</strong></li>
                                <li><strong>Priority bug fixes</strong></li>
                                <li><strong>Early access to beta features</strong></li>
                                <li>Vote on the development roadmap</li>
                                <li>Your name on the Supporters page</li>
                            </ul>
                            <a href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank"
                                class="pricing-cta pricing-cta-green">Back This Project</a>
                        </div>

                        <!-- Enterprise -->
                        <div class="pricing-card featured">
                            <div class="pricing-tier">Enterprise</div>
                            <div class="pricing-name">Enterprise</div>
                            <div class="pricing-price"><span class="currency">&pound;</span>79 <span class="period">/ server / month</span></div>
                            <div class="pricing-subtitle" style="font-size:0.8rem;">$99 USD per server per month</div>
                            <ul class="pricing-features">
                                <li>Everything in Sponsor</li>
                                <li style="padding-top:0.6rem;font-size:0.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;border:none;">Enterprise-Exclusive Features</li>
                                <li><strong><a href="wolfstack-api.php" style="color:var(--text-primary);">REST API Keys</a></strong> &mdash; scoped tokens, audit logging</li>
                                <li><strong>OIDC / SSO</strong> &mdash; Authentik, Azure AD, Okta, Keycloak</li>
                                <li><strong><a href="wolfstack-plugins.php" style="color:var(--text-primary);">Plugin System</a></strong> &mdash; extend WolfStack with custom plugins</li>
                                <li><strong><a href="wolfhost.php" style="color:var(--text-primary);">WolfHost</a></strong> &mdash; web hosting platform with billing &amp; customer portal</li>
                                <li><strong><a href="wolfcustom.php" style="color:var(--text-primary);">WolfCustom</a></strong> &mdash; white-label branding (your logo, name, colours)</li>
                                <li style="padding-top:0.6rem;font-size:0.72rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.04em;border:none;">Professional Services</li>
                                <li>SLA with guaranteed response times</li>
                                <li>Dedicated support &amp; ticketing</li>
                            </ul>
                            <a href="enterprise-contact.php"
                                class="pricing-cta pricing-cta-primary">Contact Sales</a>
                        </div>

                    </div>
                </div>

                <!-- Competition Comparison -->
                <div class="content-section">
                    <h2>How WolfStack Compares</h2>
                    <p style="color:var(--text-secondary);font-size:0.88rem;">Everything your competitors charge for, WolfStack gives away free. Enterprise is purely for businesses that need SLAs and SSO &mdash; not for features.</p>

                    <div style="overflow-x:auto;">
                    <table class="comp-table">
                        <thead>
                            <tr>
                                <th>Platform</th>
                                <th>Price</th>
                                <th>What You Get</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Leading virtualisation platform</td>
                                <td class="paid">&euro;110&ndash;220 <small>/yr/socket</small></td>
                                <td>VM &amp; container management only. No Docker, no overlay VPN, no app store, no workflows.</td>
                            </tr>
                            <tr>
                                <td>Leading container platform</td>
                                <td class="paid">$180 <small>/yr/node</small></td>
                                <td>Docker management only. No VMs, no VPN, no status pages, no backups.</td>
                            </tr>
                            <tr>
                                <td>Tailscale Business</td>
                                <td class="paid">$18 <small>/user/mo</small></td>
                                <td>VPN only. No server management, no containers, no monitoring.</td>
                            </tr>
                            <tr>
                                <td>Coolify</td>
                                <td class="paid">$5 <small>/server/mo</small></td>
                                <td>App deployment only. No VMs, no LXC, no VPN, no clustering.</td>
                            </tr>
                            <tr>
                                <td>Better Stack</td>
                                <td class="paid">$25+ <small>/mo</small></td>
                                <td>Uptime monitoring only. No server management.</td>
                            </tr>
                            <tr>
                                <td>n8n Cloud</td>
                                <td class="paid">$20+ <small>/mo</small></td>
                                <td>Workflow automation only. No infrastructure management.</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="font-weight:700;color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.04em;padding-top:1.2rem;">Enterprise Per-Server Pricing</td>
                            </tr>
                            <tr>
                                <td>Leading virtualisation platform</td>
                                <td class="paid">&euro;110&ndash;220 <small>/yr/socket</small></td>
                                <td>VM management only &mdash; no Docker, no VPN, no apps, no monitoring.</td>
                            </tr>
                            <tr>
                                <td>Leading container platform</td>
                                <td class="paid">$180 <small>/yr/node</small></td>
                                <td>Docker only &mdash; no VMs, no VPN, no status pages, no backups.</td>
                            </tr>
                            <tr style="background:rgba(220,38,38,0.06);">
                                <td style="color:var(--accent-primary);">WolfStack Enterprise</td>
                                <td style="color:var(--accent-primary);font-weight:700;">&pound;79/$99 <small>/mo/server</small></td>
                                <td><strong>Everything included</strong> &mdash; Docker, LXC, VMs, VPN, 510+ apps, status pages, alerting,
                                    backups, workflows, SSO, API keys, plugins, WolfHost, WolfCustom, and dedicated support.</td>
                            </tr>
                            <tr>
                                <td colspan="3" style="font-weight:700;color:var(--text-muted);font-size:0.75rem;text-transform:uppercase;letter-spacing:0.04em;padding-top:1.2rem;">Individual Product Comparison</td>
                            </tr>
                            <tr style="background:rgba(34,197,94,0.06);">
                                <td style="color:#22c55e;">WolfStack</td>
                                <td class="hl">Free</td>
                                <td><strong>All of the above in a single binary.</strong> Docker, LXC, VMs, encrypted mesh VPN, 510+ apps,
                                    status pages, alerting, backups, workflow automation, clustering &mdash; unlimited servers, forever.</td>
                            </tr>
                            <tr style="background:rgba(220,38,38,0.06);">
                                <td style="color:var(--accent-primary);">WolfStack Enterprise</td>
                                <td style="color:var(--accent-primary);font-weight:700;">&pound;79 <small>/mo/server</small><br><small style="color:var(--text-muted);">($99 USD)</small></td>
                                <td><strong>Everything above, plus SSO/OIDC, REST API keys, plugin system, WolfHost, WolfCustom white-label,
                                    SLA &amp; dedicated support.</strong> Still cheaper than any single competitor above &mdash;
                                    and replaces all of them.</td>
                            </tr>
                        </tbody>
                    </table>
                    </div>

                    <p style="text-align:center;margin-top:1rem;font-size:0.84rem;color:var(--text-muted);">
                        A typical 5-server setup using Proxmox + Portainer + Tailscale + monitoring costs
                        <strong style="color:var(--accent-primary);">$3,000&ndash;6,000/year</strong>.
                        WolfStack is <strong style="color:#22c55e;">free</strong>. Enterprise is still
                        <strong style="color:var(--accent-primary);">cheaper than any single product above</strong>.
                    </p>
                </div>

                <!-- What's Included with Enterprise -->
                <div class="content-section">
                    <h2>What&rsquo;s Included with Enterprise</h2>
                    <div class="enterprise-includes">
                        <div class="include-card">
                            <h4>OIDC / SSO</h4>
                            <p>Single sign-on via Authentik, Azure AD, Okta, Google Workspace, Keycloak, or any OIDC provider.
                                Centralise authentication across your organisation.</p>
                        </div>
                        <div class="include-card">
                            <h4>API Keys</h4>
                            <p>Long-lived API tokens (<code>wsk_*</code>) with scoped permissions. Automate deployments,
                                integrate CI/CD pipelines, and build tooling against the WolfStack API.</p>
                        </div>
                        <div class="include-card">
                            <h4>Plugin System</h4>
                            <p>Extend WolfStack with custom plugins &mdash; new UI pages, backend services, and
                                API integrations. See the <a href="wolfstack-plugins.php">plugin docs</a>.</p>
                        </div>
                        <div class="include-card">
                            <h4>WolfHost</h4>
                            <p>Complete web hosting platform with customer management, billing, domain management,
                                email, SSL provisioning, and a white-label customer portal. See <a href="wolfhost.php">WolfHost docs</a>.</p>
                        </div>
                        <div class="include-card">
                            <h4>WolfCustom</h4>
                            <p>White-label WolfStack with your own branding &mdash; replace the logo, product name,
                                colours, favicon, and copyright with your company identity. See <a href="wolfcustom.php">WolfCustom docs</a>.</p>
                        </div>
                        <div class="include-card">
                            <h4>SLA Guarantee</h4>
                            <p>Service Level Agreement with guaranteed response times. Because your business can&rsquo;t
                                wait for community forum replies.</p>
                        </div>
                        <div class="include-card">
                            <h4>Dedicated Support</h4>
                            <p>Direct access to the development team via ticketing system. Priority bug fixes
                                and technical support from the people who build WolfStack.</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Box -->
                <div class="contact-box">
                    <h3>Ready to Get Started?</h3>
                    <p>WolfStack is free &mdash; just install it. Need Enterprise? Contact us and we&rsquo;ll get back to you within one business day.</p>
                    <a href="enterprise-contact.php" class="contact-email">
                        Contact Sales
                    </a>
                </div>

                <!-- FAQ -->
                <div class="content-section">
                    <h2>Frequently Asked Questions</h2>

                    <h3>Do I need to pay anything to use WolfStack?</h3>
                    <p>No. WolfStack is completely free and open-source under the MIT licence. Every feature,
                        unlimited servers, forever. Enterprise licensing is only for organisations that need
                        SLAs, SSO, and dedicated support.</p>

                    <h3>What do GitHub Sponsors get?</h3>
                    <p>Sponsors get access to a <strong>private Discord channel</strong> with direct support from the dev team,
                        early access to beta features, priority bug fixes, and a vote on the development roadmap. It&rsquo;s
                        the best way to support WolfStack if you don&rsquo;t need a formal SLA.</p>

                    <h3>What&rsquo;s the difference between Sponsor and Enterprise?</h3>
                    <p><strong>Sponsors</strong> fund open-source development and get direct dev access via Discord.
                        <strong>Enterprise</strong> is for businesses that need formal SLAs, SSO/OIDC integration,
                        API keys, a ticketing system, and professional services like installation and migration.</p>

                    <h3>How is Enterprise priced?</h3>
                    <p><strong>&pound;79/month per server</strong> ($99 USD). That&rsquo;s it &mdash; no per-socket, no per-core,
                        no per-user complexity. A 10-server cluster is &pound;790/month.</p>

                    <h3>Can I sponsor on GitHub and have Enterprise?</h3>
                    <p>Absolutely. Sponsorship funds continued development for everyone. You&rsquo;ll get the sponsor
                        perks on top of your enterprise benefits.</p>

                    <h3>Do I need to install the license on every server?</h3>
                    <p>No. Install the license on <strong>any single node</strong> in your cluster and it is
                        <strong>automatically propagated to every other node within 10 seconds</strong>.
                        WolfStack&rsquo;s cluster polling distributes the license key to all connected nodes &mdash;
                        no manual copying or per-server configuration required.</p>

                    <h3>What data does the Enterprise license report?</h3>
                    <p>Enterprise licenses include a lightweight daily heartbeat that reports only <strong>server hostnames</strong>,
                        <strong>WolfStack version</strong>, and <strong>cluster name</strong> to Wolf Software Systems. No user data,
                        container names, or configuration is ever transmitted. The heartbeat is used solely to verify your active
                        server count against your license. The heartbeat is non-blocking &mdash; WolfStack continues to function
                        normally regardless of connectivity to our licensing server.</p>
                </div>

                <!-- License Compliance -->
                <div class="content-section">
                    <h2>License Compliance</h2>
                    <p>Enterprise licenses include a lightweight daily heartbeat that reports the number of active servers
                        to Wolf Software Systems. This contains only <strong>server hostnames</strong>,
                        <strong>WolfStack version</strong>, and <strong>cluster name</strong> &mdash; no user data, container names,
                        or configuration.</p>
                    <p>If your active server count exceeds your licensed count for more than <strong>5 consecutive days</strong>,
                        we will issue an invoice for the additional servers at the standard rate of
                        <strong>&pound;79/$99 per server per month</strong>.</p>
                    <p>The heartbeat is non-blocking &mdash; WolfStack continues to function normally regardless of
                        connectivity to our licensing server.</p>
                </div>

                <div class="page-nav"><a href="licensing.php" class="prev">&larr; Licensing</a><a href="support.php"
                        class="next">Support &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

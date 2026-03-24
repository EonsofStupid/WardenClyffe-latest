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

        @media (max-width: 768px) {
            .pricing-grid {
                grid-template-columns: 1fr;
            }

            .enterprise-includes {
                grid-template-columns: 1fr;
            }

            .content-section [style*="grid-template-columns: 1fr 1fr"] {
                grid-template-columns: 1fr !important;
            }
        }';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content" style="max-width:1100px;">


                <div class="content-section">
                    <h2>How WolfStack is Funded</h2>
                    <p>WolfStack is <strong>free and open-source</strong> software, funded by the community through <a
                            href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank"
                            style="color:#22c55e;font-weight:600;">GitHub Sponsors</a>. This keeps it accessible to
                        everyone &mdash; from hobbyists running a single server to large organisations managing entire fleets.
                    </p>
                    <p>For businesses that need <strong>guaranteed support, professional installation, and a ticketing
                            system</strong>, we offer Enterprise Licensing. You get everything in the community edition,
                        plus dedicated support from the team that builds it.</p>
                </div>

                <!-- Sponsor Highlight -->
                <div class="sponsor-highlight">
                    <h3>GitHub Sponsors get extra love</h3>
                    <p>WolfStack is built by a small, dedicated team. GitHub Sponsors are the reason we can keep developing it full-time. In return, sponsors get perks that go beyond the free community edition:</p>
                    <ul>
                        <li><strong>Private sponsors-only Discord channel</strong> &mdash; direct access to the dev team for support, questions, and feedback</li>
                        <li><strong>Early access to beta features</strong> &mdash; test new functionality before public release</li>
                        <li><strong>Priority bug fixes</strong> &mdash; issues reported by sponsors are addressed first</li>
                        <li><strong>Vote on the development roadmap</strong> &mdash; help shape what gets built next</li>
                        <li><strong>Your name on the Supporters page</strong> &mdash; recognised as someone who keeps WolfStack alive</li>
                    </ul>
                    <p style="margin-top:1rem;margin-bottom:0;">Sponsorship starts from just <strong>&pound;3/month</strong>. Every contribution makes a difference.</p>
                    <p style="text-align:center;margin-top:1.25rem;">
                        <a href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank"
                            class="pricing-cta pricing-cta-green" style="width:auto;">Back This Project</a>
                    </p>
                </div>

                <!-- Pricing Cards -->
                <div class="content-section">
                    <h2>Plans &amp; Pricing</h2>
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin-bottom: 0.5rem;">All plans include
                        every feature. Enterprise adds dedicated support and SLAs. Enterprise plans are priced
                        <strong>per CPU socket per year</strong> &mdash; it doesn&rsquo;t matter how many cores your
                        CPU has.</p>
                    <div class="pricing-grid">

                        <!-- Community -->
                        <div class="pricing-card">
                            <div class="pricing-tier">Community</div>
                            <div class="pricing-name">Free</div>
                            <div class="pricing-price" style="color:#22c55e;">Free</div>
                            <div class="pricing-subtitle">Free forever, all features</div>
                            <ul class="pricing-features">
                                <li>All features included</li>
                                <li>Unlimited servers</li>
                                <li>Full source code access</li>
                                <li>Public Discord community</li>
                                <li>Documentation &amp; guides</li>
                                <li>Regular updates</li>
                            </ul>
                            <a href="wolfstack.php"
                                class="pricing-cta pricing-cta-secondary">Get Started</a>
                        </div>

                        <!-- Sponsor -->
                        <div class="pricing-card sponsor-card">
                            <div class="pricing-tier">Sponsor</div>
                            <div class="pricing-name">GitHub Sponsors</div>
                            <div class="pricing-price" style="color:#22c55e;">From <span class="currency">&pound;</span>3 <span class="period">/ month</span></div>
                            <div class="pricing-subtitle">Support development, get extra love</div>
                            <ul class="pricing-features">
                                <li>Everything in Community</li>
                                <li><strong>Private sponsors-only Discord</strong></li>
                                <li><strong>Direct support from the dev team</strong></li>
                                <li><strong>Early access to beta features</strong></li>
                                <li><strong>Priority bug fixes</strong></li>
                                <li>Your name on Supporters page</li>
                                <li>Vote on the roadmap</li>
                            </ul>
                            <a href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank"
                                class="pricing-cta pricing-cta-green">Back This Project</a>
                        </div>

                        <!-- Standard -->
                        <div class="pricing-card featured">
                            <div class="pricing-tier">Enterprise</div>
                            <div class="pricing-name">Standard</div>
                            <div class="pricing-price"><span class="currency">&pound;</span>450 <span class="period">/ year
                                    per socket</span></div>
                            <div class="pricing-subtitle">Professional services for businesses</div>
                            <ul class="pricing-features">
                                <li>Everything in Sponsor</li>
                                <li>Full installation &amp; onboarding</li>
                                <li>Migration from Proxmox/Portainer</li>
                                <li>SLA with 4-hour response time</li>
                                <li>Monthly check-ins</li>
                                <li>Team training sessions</li>
                            </ul>
                            <a href="enterprise-contact.php?plan=Standard"
                                class="pricing-cta pricing-cta-primary">Contact Sales</a>
                        </div>

                        <!-- Premium -->
                        <div class="pricing-card">
                            <div class="pricing-tier">Enterprise</div>
                            <div class="pricing-name">Premium</div>
                            <div class="pricing-price"><span class="currency">&pound;</span>900 <span class="period">/ year
                                    per socket</span></div>
                            <div class="pricing-subtitle">All you&rsquo;ll ever need</div>
                            <ul class="pricing-features">
                                <li>Everything in Standard</li>
                                <li>24/7 support</li>
                                <li>SLA with 2-hour response time</li>
                                <li>Dedicated account manager</li>
                                <li>Custom development requests</li>
                                <li>On-site or remote training</li>
                                <li>Architecture consulting</li>
                            </ul>
                            <a href="enterprise-contact.php?plan=Premium"
                                class="pricing-cta pricing-cta-secondary">Contact Sales</a>
                        </div>

                    </div>
                </div>

                <!-- What's Included -->
                <div class="content-section">
                    <h2>What&rsquo;s Included with Enterprise</h2>
                    <div class="enterprise-includes">
                        <div class="include-card">
                            <h4>Full Installation</h4>
                            <p>Our team will install and configure WolfStack across your entire infrastructure. We
                                handle the setup so you can focus on your business.</p>
                        </div>
                        <div class="include-card">
                            <h4>Ticketing System</h4>
                            <p>Dedicated ticketing system for reporting issues, requesting features, and tracking
                                resolution. No more waiting on community forums.</p>
                        </div>
                        <div class="include-card">
                            <h4>Direct Support</h4>
                            <p>Email and priority support directly from the WolfStack development team. Get answers from
                                the people who build the software.</p>
                        </div>
                        <div class="include-card">
                            <h4>Migration Help</h4>
                            <p>Moving from Proxmox, Portainer, or another platform? We&rsquo;ll help migrate your containers,
                                configurations, and workflows.</p>
                        </div>
                        <div class="include-card">
                            <h4>SLA Guarantee</h4>
                            <p>Enterprise Premium customers receive a Service Level Agreement with guaranteed response
                                times and uptime commitments.</p>
                        </div>
                        <div class="include-card">
                            <h4>Training</h4>
                            <p>Get your team up to speed with dedicated training sessions covering WolfStack, WolfNet,
                                container management, and more.</p>
                        </div>
                    </div>
                </div>

                <!-- Managed Setup Service -->
                <div class="content-section">
                    <h2>Managed Cluster Setup</h2>
                    <p>Don&rsquo;t want to set it up yourself? Our team will build your entire WolfStack cluster for you,
                        configured and ready to use. We handle everything via remote SSH access.</p>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin:1.5rem 0;">
                        <div>
                            <h3>What&rsquo;s included</h3>
                            <ul>
                                <li>WolfStack installed on all your servers</li>
                                <li>Cluster configured and nodes connected</li>
                                <li>WolfNet encrypted mesh networking set up</li>
                                <li>Storage mounts configured (S3, NFS, ZFS, Ceph, etc.)</li>
                                <li>Backup schedules configured</li>
                                <li>Status pages and alerting set up</li>
                                <li>Your applications deployed via the App Store</li>
                                <li>Firewall rules and security hardened</li>
                                <li>SSL certificates provisioned via Let&rsquo;s Encrypt</li>
                                <li>Handover walkthrough with your team</li>
                            </ul>
                        </div>
                        <div>
                            <h3>Pricing</h3>
                            <p style="font-size:2rem;font-weight:800;color:var(--accent-primary);margin:0.5rem 0 0.25rem;">From &pound;500</p>
                            <p>One-time fee. Price depends on cluster size and complexity.</p>
                            <ul>
                                <li>Includes up to <strong>5 servers</strong></li>
                                <li>Additional servers <strong>&pound;50 each</strong></li>
                                <li>Remote setup via SSH</li>
                                <li>On-site setup available (Premium customers)</li>
                            </ul>
                            <p style="margin-top:1rem;">
                                <a href="enterprise-contact.php?plan=ManagedSetup"
                                    class="pricing-cta pricing-cta-primary" style="width:auto;">Get a Quote</a>
                            </p>
                        </div>
                    </div>

                    <p style="color:var(--text-muted);font-size:0.82rem;margin-top:0.5rem;">
                        Managed setup is available as a standalone service &mdash; you don&rsquo;t need an enterprise subscription.
                        Pair it with a GitHub Sponsors membership for ongoing private Discord support after setup.</p>
                </div>

                <!-- Contact Box -->
                <div class="contact-box">
                    <h3>Ready to Get Started?</h3>
                    <p>Contact our sales team to discuss your requirements and find the right plan for your
                        organisation.<br>
                        We&rsquo;ll get back to you within one business day.</p>
                    <a href="enterprise-contact.php" class="contact-email">
                        Contact Sales
                    </a>
                </div>

                <!-- FAQ -->
                <div class="content-section">
                    <h2>Frequently Asked Questions</h2>

                    <h3>How many subscriptions do I need?</h3>
                    <p>Enterprise subscriptions are priced per <strong>physical CPU socket</strong> occupied on the
                        motherboard. It doesn&rsquo;t matter if your CPU has 4 cores or 128 cores &mdash; each occupied socket
                        counts as one. Empty slots do not require a subscription. Each server needs its own subscription
                        based on its socket count.</p>

                    <h3>Do I need an enterprise licence to use WolfStack?</h3>
                    <p>No. WolfStack is completely free and open-source under the MIT licence. The Community Edition
                        includes all features with no limits. Enterprise licensing is for organisations that want
                        dedicated support and professional services.</p>

                    <h3>What do GitHub Sponsors get?</h3>
                    <p>Sponsors get access to a <strong>private Discord channel</strong> with direct support from the dev team,
                        early access to beta features, priority bug fixes, and a vote on the development roadmap. It&rsquo;s
                        the best way to support WolfStack if you don&rsquo;t need a formal enterprise SLA.</p>

                    <h3>Can I switch plans later?</h3>
                    <p>Yes &mdash; you can upgrade or downgrade your enterprise plan at any time. Contact <a
                            href="mailto:sales@wolf.uk.com">sales@wolf.uk.com</a> to discuss changes.</p>

                    <h3>What payment methods do you accept?</h3>
                    <p>We accept bank transfer and invoice-based payments. Contact our sales team for details.</p>

                    <h3>Is support available outside business hours?</h3>
                    <p>24/7 support is included with the Enterprise Premium plan. Basic and Standard plans
                        include support during UK business hours (Mon&ndash;Fri, 9am&ndash;5pm GMT). GitHub Sponsors
                        get support via the private Discord channel.</p>

                    <h3>Can I still sponsor on GitHub if I have an enterprise licence?</h3>
                    <p>Absolutely! GitHub Sponsors support is always welcome and helps fund continued development for the entire
                        community. You&rsquo;ll get the sponsor perks on top of your enterprise benefits.</p>
                </div>

                <div class="page-nav"><a href="licensing.php" class="prev">&larr; Licensing</a><a href="support.php"
                        class="next">Fund Open Source &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>

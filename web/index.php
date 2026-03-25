<?php
$page_title = 'WolfStack — One Binary. Your Entire Infrastructure.';
$page_desc = 'WolfStack replaces Proxmox, Portainer, Uptime Kuma, and Ansible with a single Rust binary. Manage Docker, LXC, VMs, networking, storage, backups, status pages, and more from one dashboard.';
$page_keywords = 'server management, WolfStack, Proxmox alternative, Portainer alternative, Docker management, LXC, container orchestration, Rust, server dashboard, homelab, self-hosted, infrastructure management';
$page_canonical = 'https://wolfscale.org/';
$active = 'index.php';

// Load setup URL copy count
$_copyCountFile = __DIR__ . '/data/copy-counts.json';
$_setupCopies = 0;
if (file_exists($_copyCountFile)) {
    $_counts = json_decode(file_get_contents($_copyCountFile), true);
    if (isset($_counts['setup-url']['count'])) {
        $_setupCopies = (int)$_counts['setup-url']['count'];
    }
}

$page_css = '
/* ── Homepage redesign ─────────────────────── */

/* Hero split layout */
.hp2-hero-split {
    display: grid;
    grid-template-columns: 1.15fr 0.85fr;
    gap: 40px;
    align-items: center;
    max-width: 1400px;
    margin: 0 auto;
    padding: 70px 40px 40px;
    min-height: 70vh;
}
.hp2-hero-slider {
    position: relative;
}
.hp2-hero-slider .hp2-slider {
    max-width: 100%;
    padding: 0;
}
.hp2-hero-slider .hp2-slider-track {
    border-radius: 10px;
    box-shadow: 0 16px 60px rgba(0, 0, 0, 0.4);
}
[data-theme="light"] .hp2-hero-slider .hp2-slider-track {
    box-shadow: 0 16px 60px rgba(0, 0, 0, 0.1);
}
.hp2-hero-slider .hp2-slide-label {
    padding: 12px 16px 10px;
}
.hp2-hero-slider .hp2-slide-label h4 {
    font-size: 0.82rem;
}

.hp2-hero-text {
    text-align: left;
}

.hp2-headline {
    font-size: clamp(1.8rem, 3.5vw, 2.8rem);
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -0.03em;
    margin-bottom: 16px;
    color: var(--text-primary);
}
.hp2-headline em {
    font-style: normal;
    background: linear-gradient(135deg, #dc2626, #f87171);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hp2-sub {
    font-size: clamp(0.9rem, 1.3vw, 1.05rem);
    color: var(--text-secondary);
    margin: 0 0 24px;
    line-height: 1.65;
}

.hp2-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-start;
    flex-wrap: wrap;
    margin-bottom: 12px;
}

.hp2-meta {
    font-size: 0.76rem;
    color: var(--text-muted);
    margin-bottom: 0;
    text-align: left;
}
.hp2-meta a { color: var(--accent-primary); }

/* Trust bar */
.hp2-trust {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 28px;
    flex-wrap: wrap;
    padding: 20px 24px;
    max-width: 800px;
    margin: 0 auto;
}
.hp2-trust-item {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    color: var(--text-muted);
    font-weight: 500;
}
.hp2-trust-item strong {
    color: var(--text-primary);
    font-size: 0.9rem;
}
.hp2-trust-item img {
    width: 18px;
    height: 18px;
}

/* Hero screenshot */
.hp2-screenshot-wrap {
    max-width: 1200px;
    margin: 0 auto 0;
    padding: 0 24px;
}
.hp2-screenshot {
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--border-color);
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255,255,255,0.03);
}
[data-theme="light"] .hp2-screenshot {
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0,0,0,0.06);
}
.hp2-screenshot img {
    display: block;
    width: 100%;
    height: auto;
}

/* Distro bar */
.hp2-distros-section {
    margin: 0 0 20px;
    text-align: left;
}
.hp2-distros-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    color: var(--text-muted);
    margin-bottom: 10px;
}
.hp2-distros {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 12px 18px;
    flex-wrap: wrap;
    margin-bottom: 4px;
}
.hp2-distro {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.76rem;
    color: var(--text-muted);
    white-space: nowrap;
}
.hp2-distro img {
    width: 18px;
    height: 18px;
    opacity: 0.7;
}

/* Quick start steps */
.hp2-steps {
    list-style: none;
    counter-reset: step;
    padding: 0;
    margin: 0;
}
.hp2-steps li {
    counter-increment: step;
    position: relative;
    padding: 16px 0 16px 48px;
    border-bottom: 1px solid var(--border-color);
}
.hp2-steps li:last-child { border-bottom: none; }
.hp2-steps li::before {
    content: counter(step);
    position: absolute;
    left: 0;
    top: 18px;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: var(--accent-primary);
    color: #fff;
    font-size: 0.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
}
.hp2-steps li strong {
    display: block;
    font-size: 0.95rem;
    margin-bottom: 4px;
    color: var(--text-primary);
}
.hp2-step-desc {
    display: block;
    font-size: 0.86rem;
    color: var(--text-secondary);
    line-height: 1.6;
}
.hp2-step-hint {
    display: block;
    font-size: 0.78rem;
    color: var(--text-muted);
    line-height: 1.7;
    margin-top: 6px;
}

/* Section layout */
.hp2-section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 60px 24px;
}
.hp2-section-tight {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 24px;
}

.hp2-section-header {
    text-align: center;
    margin-bottom: 40px;
}
.hp2-section-header h2 {
    font-size: clamp(1.4rem, 3vw, 1.9rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    margin-bottom: 8px;
}
.hp2-section-header p {
    font-size: 0.95rem;
    color: var(--text-secondary);
    max-width: 550px;
    margin: 0 auto;
}

/* Replaces section */
.hp2-replaces {
    background: var(--bg-secondary);
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
}
.hp2-replaces-grid {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}
.hp2-replaces-item {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 14px 20px;
    text-align: center;
    min-width: 120px;
    transition: var(--transition);
}
.hp2-replaces-item:hover {
    border-color: var(--border-glow);
}
.hp2-replaces-item .tool-name {
    font-weight: 600;
    font-size: 0.88rem;
    color: var(--text-primary);
    display: block;
}
.hp2-replaces-item .tool-purpose {
    font-size: 0.72rem;
    color: var(--text-muted);
}
.hp2-replaces-plus {
    font-size: 1.2rem;
    font-weight: 300;
    color: var(--text-muted);
}
.hp2-replaces-equals {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 0 8px;
}
.hp2-replaces-equals .eq {
    font-size: 1.6rem;
    font-weight: 300;
    color: var(--accent-primary);
}
.hp2-replaces-result {
    background: var(--accent-subtle);
    border: 2px solid var(--accent-primary);
    border-radius: var(--radius);
    padding: 14px 28px;
    text-align: center;
}
.hp2-replaces-result .tool-name {
    font-weight: 800;
    font-size: 1rem;
    color: var(--accent-primary);
    display: block;
}
.hp2-replaces-result .tool-purpose {
    font-size: 0.76rem;
    color: var(--text-secondary);
}

/* Feature spotlight rows */
.hp2-spotlight {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: center;
    margin-bottom: 60px;
}
.hp2-spotlight:last-child { margin-bottom: 0; }
.hp2-spotlight.reverse .hp2-spot-text { order: 2; }
.hp2-spotlight.reverse .hp2-spot-visual { order: 1; }

.hp2-spot-text h3 {
    font-size: 1.3rem;
    font-weight: 700;
    margin-bottom: 10px;
    letter-spacing: -0.01em;
}
.hp2-spot-text p {
    color: var(--text-secondary);
    font-size: 0.9rem;
    line-height: 1.65;
    margin-bottom: 12px;
}
.hp2-spot-bullets {
    list-style: none;
    padding: 0;
    margin: 0;
}
.hp2-spot-bullets li {
    padding: 5px 0;
    font-size: 0.86rem;
    color: var(--text-secondary);
    display: flex;
    align-items: baseline;
    gap: 8px;
}
.hp2-spot-bullets li::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--accent-primary);
    flex-shrink: 0;
    margin-top: 6px;
}

.hp2-spot-visual img {
    width: 100%;
    border-radius: 10px;
    border: 1px solid var(--border-color);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
}
[data-theme="light"] .hp2-spot-visual img {
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
}

/* Feature grid (compact) */
.hp2-features {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
}
.hp2-feat {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 24px;
    transition: var(--transition);
}
.hp2-feat:hover {
    border-color: var(--border-glow);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}
.hp2-feat-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: var(--accent-subtle);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    margin-bottom: 14px;
    color: var(--accent-primary);
    font-weight: 700;
}
.hp2-feat h3 {
    font-size: 0.92rem;
    font-weight: 600;
    margin-bottom: 6px;
}
.hp2-feat p {
    color: var(--text-secondary);
    font-size: 0.82rem;
    line-height: 1.55;
    margin: 0;
}

/* Comparison mini table */
.hp2-compare {
    background: var(--bg-secondary);
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
}
.hp2-compare table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.84rem;
}
.hp2-compare th {
    padding: 12px 16px;
    text-align: center;
    font-size: 0.74rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    color: var(--text-muted);
    border-bottom: 2px solid var(--border-color);
    background: var(--bg-tertiary);
}
.hp2-compare th:first-child { text-align: left; }
.hp2-compare th.hl {
    color: var(--accent-primary);
    background: var(--accent-subtle);
    border-bottom-color: var(--accent-primary);
}
.hp2-compare td {
    padding: 10px 16px;
    text-align: center;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-secondary);
}
.hp2-compare td:first-child {
    text-align: left;
    font-weight: 500;
    color: var(--text-primary);
}
.hp2-compare td.hl {
    color: var(--success);
    font-weight: 600;
    background: rgba(220, 38, 38, 0.03);
}
.hp2-compare tr:last-child td { border-bottom: none; }
.hp2-compare .more-link {
    text-align: center;
    margin-top: 16px;
}
.hp2-compare .more-link a {
    color: var(--accent-primary);
    font-size: 0.84rem;
    font-weight: 500;
}

/* Products strip */
.hp2-products {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}
.hp2-product {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius);
    padding: 20px;
    transition: var(--transition);
    text-decoration: none;
}
.hp2-product:hover {
    border-color: var(--accent-primary);
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
}
.hp2-product h3 {
    font-size: 0.92rem;
    font-weight: 700;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.hp2-product p {
    color: var(--text-muted);
    font-size: 0.78rem;
    line-height: 1.5;
    margin: 0;
}
.hp2-tag {
    font-size: 0.58rem;
    padding: 2px 6px;
    border-radius: 100px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.hp2-tag-built {
    background: rgba(245, 158, 11, 0.12);
    color: var(--warning);
    border: 1px solid rgba(245, 158, 11, 0.3);
}
.hp2-tag-flag {
    background: var(--accent-subtle);
    color: var(--accent-primary);
    border: 1px solid rgba(220, 38, 38, 0.3);
}

/* CTA */
.hp2-cta {
    text-align: center;
    padding: 80px 24px;
    max-width: 900px;
    margin: 0 auto;
}
.hp2-cta h2 {
    font-size: clamp(1.5rem, 3vw, 2rem);
    font-weight: 800;
    letter-spacing: -0.02em;
    margin-bottom: 12px;
}
.hp2-cta p {
    color: var(--text-secondary);
    font-size: 0.95rem;
    margin-bottom: 28px;
    line-height: 1.6;
}
.hp2-cta-links {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 20px;
}
.hp2-cta-links a {
    color: var(--text-secondary);
    font-size: 0.82rem;
    font-weight: 500;
    transition: var(--transition);
}
.hp2-cta-links a:hover { color: var(--accent-primary); }

/* Screenshot slider */
.hp2-slider {
    position: relative;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 24px;
}
.hp2-slider-track {
    overflow: hidden;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(255,255,255,0.03);
    position: relative;
}
[data-theme="light"] .hp2-slider-track {
    box-shadow: 0 24px 80px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0,0,0,0.06);
}

.hp2-slider-inner {
    display: flex;
    transition: transform 0.5s ease;
}
.hp2-slide {
    min-width: 100%;
    position: relative;
}
.hp2-slide img {
    display: block;
    width: 100%;
    height: auto;
}
.hp2-slide-label {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 20px 24px 16px;
    background: linear-gradient(transparent, rgba(0,0,0,0.75));
    color: #fff;
}
.hp2-slide-label h4 {
    font-size: 0.92rem;
    font-weight: 700;
    margin-bottom: 2px;
}
.hp2-slide-label p {
    font-size: 0.78rem;
    opacity: 0.85;
    margin: 0;
}
.hp2-slider-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(0,0,0,0.6);
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
    font-size: 1.1rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    transition: var(--transition);
    backdrop-filter: blur(8px);
}
.hp2-slider-btn:hover {
    background: var(--accent-primary);
    border-color: var(--accent-primary);
}
.hp2-slider-prev { left: 32px; }
.hp2-slider-next { right: 32px; }

.hp2-slider-dots {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 16px;
}
.hp2-slider-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--text-muted);
    border: none;
    cursor: pointer;
    padding: 0;
    transition: var(--transition);
    opacity: 0.4;
}
.hp2-slider-dot.active {
    background: var(--accent-primary);
    opacity: 1;
    width: 24px;
    border-radius: 4px;
}

/* Gallery slider (wider, with captions below) */
.hp2-gallery {
    max-width: 1200px;
    margin: 0 auto;
}
.hp2-gallery .hp2-slider-track {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
}
[data-theme="light"] .hp2-gallery .hp2-slider-track {
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
}

@media (max-width: 768px) {
    .hp2-slider { padding: 0 16px; }
    .hp2-slider-btn { width: 32px; height: 32px; font-size: 0.9rem; }
    .hp2-slider-prev { left: 20px; }
    .hp2-slider-next { right: 20px; }
    .hp2-slide-label { padding: 12px 16px 10px; }
    .hp2-slide-label h4 { font-size: 0.82rem; }
    .hp2-slide-label p { font-size: 0.7rem; }
}

/* Managed setup service */
.hp2-service {
    max-width: 1100px;
    margin: 0 auto;
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 36px;
    display: grid;
    grid-template-columns: 1.2fr 0.8fr;
    gap: 32px;
    align-items: center;
}
.hp2-service:hover {
    border-color: var(--border-glow);
}
.hp2-service h3 {
    font-size: 1.3rem;
    font-weight: 800;
    margin-bottom: 8px;
    letter-spacing: -0.01em;
}
.hp2-service > div > p {
    font-size: 0.9rem;
    color: var(--text-secondary);
    line-height: 1.65;
    margin-bottom: 14px;
}
.hp2-service-features {
    list-style: none;
    padding: 0;
    margin: 0;
    columns: 2;
    column-gap: 20px;
}
.hp2-service-features li {
    padding: 4px 0;
    font-size: 0.84rem;
    color: var(--text-secondary);
    break-inside: avoid;
    display: flex;
    align-items: baseline;
    gap: 6px;
}
.hp2-service-features li::before {
    content: "";
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: var(--accent-primary);
    flex-shrink: 0;
    margin-top: 6px;
}
.hp2-service-cta {
    text-align: center;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 28px 24px;
}
.hp2-service-cta .price {
    font-size: 1.6rem;
    font-weight: 800;
    color: var(--accent-primary);
    margin-bottom: 4px;
}
.hp2-service-cta .price-note {
    font-size: 0.78rem;
    color: var(--text-muted);
    margin-bottom: 16px;
}
.hp2-service-cta .includes {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 12px;
    line-height: 1.6;
}
@media (max-width: 768px) {
    .hp2-service {
        grid-template-columns: 1fr;
        padding: 24px;
    }
    .hp2-service-features { columns: 1; }
}

/* Funding section */
.hp2-funding {
    background: var(--bg-secondary);
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
}
.hp2-funding-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-top: 8px;
}
.hp2-fund-sponsor {
    border-color: rgba(34, 197, 94, 0.4);
}
.hp2-fund-sponsor:hover {
    border-color: rgba(34, 197, 94, 0.6);
    box-shadow: 0 8px 24px rgba(34, 197, 94, 0.1);
}
.hp2-fund-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 28px 24px;
    text-align: center;
    transition: var(--transition);
    position: relative;
}
.hp2-fund-card:hover {
    border-color: var(--border-glow);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
}
.hp2-fund-card.featured {
    border-color: rgba(220, 38, 38, 0.4);
}
.hp2-fund-card.featured::before {
    content: "Most Popular";
    position: absolute;
    top: -10px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--accent-primary);
    color: white;
    padding: 3px 14px;
    border-radius: 100px;
    font-size: 0.68rem;
    font-weight: 700;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 0.03em;
}
.hp2-fund-label {
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
    margin-bottom: 4px;
}
.hp2-fund-name {
    font-size: 1.15rem;
    font-weight: 800;
    margin-bottom: 8px;
}
.hp2-fund-price {
    font-size: 1.8rem;
    font-weight: 800;
    color: var(--accent-primary);
    margin-bottom: 4px;
}
.hp2-fund-price .period {
    font-size: 0.78rem;
    font-weight: 500;
    color: var(--text-muted);
}
.hp2-fund-price.free {
    color: var(--success);
}
.hp2-fund-desc {
    font-size: 0.82rem;
    color: var(--text-secondary);
    margin-bottom: 16px;
    line-height: 1.5;
}
.hp2-fund-features {
    list-style: none;
    padding: 0;
    margin: 0 0 20px;
    text-align: left;
}
.hp2-fund-features li {
    padding: 5px 0;
    font-size: 0.82rem;
    color: var(--text-secondary);
    border-bottom: 1px solid var(--border-color);
}
.hp2-fund-features li:last-child { border-bottom: none; }

/* Responsive */
@media (max-width: 768px) {
    .hp2-hero-split {
        grid-template-columns: 1fr;
        padding: 60px 16px 32px;
        min-height: auto;
        gap: 24px;
    }
    .hp2-hero-text { text-align: center; }
    .hp2-hero-text .hp2-actions { justify-content: center; }
    .hp2-hero-text .hp2-meta { text-align: center; }
    .hp2-distros-section { text-align: center; }
    .hp2-distros { justify-content: center; }
    .hp2-spotlight {
        grid-template-columns: 1fr;
        gap: 24px;
    }
    .hp2-spotlight.reverse .hp2-spot-text { order: 1; }
    .hp2-spotlight.reverse .hp2-spot-visual { order: 2; }
    .hp2-features { grid-template-columns: 1fr; }
    .hp2-replaces-grid { flex-direction: column; }
    .hp2-replaces-plus { transform: rotate(90deg); }
    .hp2-replaces-equals .eq { transform: rotate(90deg); }
    .hp2-products { grid-template-columns: 1fr; }
    .hp2-screenshot-wrap { padding: 0 16px; }
    .hp2-funding-grid { grid-template-columns: 1fr; }
}
@media (min-width: 769px) and (max-width: 1100px) {
    .hp2-funding-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 769px) and (max-width: 900px) {
    .hp2-features { grid-template-columns: repeat(2, 1fr); }
}
';

include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>

    <main class="wiki-main">
        <div class="wiki-content" style="max-width:100%;padding-left:0;padding-right:0;">

            <!-- Top sponsor banner -->
            <div class="support-banner" id="support-banner">
                Every feature ships free to everyone. No VC funding, no ads &mdash; community funded.
                <a href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank" class="banner-cta">Back This Project</a>
                <a href="enterprise.php" class="banner-cta">Enterprise Licensing</a>
                <button class="banner-close" onclick="this.parentElement.style.display='none'" aria-label="Close">&times;</button>
            </div>

            <!-- ============================================ -->
            <!--  HERO: slider left, text right               -->
            <!-- ============================================ -->
            <section class="hp2-hero-split">
                <div class="hp2-hero-slider">
                    <div class="hp2-slider" id="hero-slider">
                        <div class="hp2-slider-track">
                            <div class="hp2-slider-inner">
                                <div class="hp2-slide">
                                    <img src="images/screenshots/hero-dashboard-2x.png" alt="Datacenter Overview" loading="eager">
                                    <div class="hp2-slide-label"><h4>Datacenter Overview</h4></div>
                                </div>
                                <div class="hp2-slide">
                                    <img src="images/screenshots/hero-containers-2x.png" alt="Container Management" loading="lazy">
                                    <div class="hp2-slide-label"><h4>Container Management</h4></div>
                                </div>
                                <div class="hp2-slide">
                                    <img src="images/screenshots/hero-appstore-2x.png" alt="App Store" loading="lazy">
                                    <div class="hp2-slide-label"><h4>App Store</h4></div>
                                </div>
                                <div class="hp2-slide">
                                    <img src="images/screenshots/dashboard-server.png" alt="Server Dashboard" loading="lazy">
                                    <div class="hp2-slide-label"><h4>Server Monitoring</h4></div>
                                </div>
                                <div class="hp2-slide">
                                    <img src="images/screenshots/networking.png" alt="Networking" loading="lazy">
                                    <div class="hp2-slide-label"><h4>Networking</h4></div>
                                </div>
                                <div class="hp2-slide">
                                    <img src="images/screenshots/statuspage.png" alt="Status Pages" loading="lazy">
                                    <div class="hp2-slide-label"><h4>Status Pages</h4></div>
                                </div>
                                <div class="hp2-slide">
                                    <img src="images/screenshots/wolfflow.png" alt="WolfFlow" loading="lazy">
                                    <div class="hp2-slide-label"><h4>WolfFlow Automation</h4></div>
                                </div>
                                <div class="hp2-slide">
                                    <img src="images/screenshots/terminal.png" alt="Web Terminal" loading="lazy">
                                    <div class="hp2-slide-label"><h4>Web Terminal</h4></div>
                                </div>
                                <div class="hp2-slide">
                                    <img src="images/screenshots/kubernetes.png" alt="Kubernetes" loading="lazy">
                                    <div class="hp2-slide-label"><h4>WolfKube Kubernetes</h4></div>
                                </div>
                            </div>
                        </div>
                        <button class="hp2-slider-btn hp2-slider-prev" onclick="slideHero(-1)" aria-label="Previous">&#8249;</button>
                        <button class="hp2-slider-btn hp2-slider-next" onclick="slideHero(1)" aria-label="Next">&#8250;</button>
                        <div class="hp2-slider-dots" id="hero-dots"></div>
                    </div>
                </div>

                <div class="hp2-hero-text">
                    <h1 class="hp2-headline">
                        One binary.<br><em>Your entire infrastructure.</em>
                    </h1>

                    <p class="hp2-sub">
                        Docker, LXC, VMs, Kubernetes, networking, storage, backups, status pages, and workflow automation &mdash;
                        managed from a single dashboard. Install in one command. No database required.
                    </p>

                    <div class="hp2-distros-section">
                        <div class="hp2-distros-label">Installs on</div>
                        <div class="hp2-distros">
                            <span class="hp2-distro"><img src="images/distros/ubuntu.svg" alt=""> Ubuntu</span>
                            <span class="hp2-distro"><img src="images/distros/debian.svg" alt=""> Debian</span>
                            <span class="hp2-distro"><img src="images/distros/fedora.svg" alt=""> Fedora</span>
                            <span class="hp2-distro"><img src="images/distros/rhel.svg" alt=""> RHEL / CentOS</span>
                        </div>
                        <div class="hp2-distros">
                            <span class="hp2-distro"><img src="images/distros/opensuse.svg" alt=""> openSUSE / SLES</span>
                            <span class="hp2-distro"><img src="images/distros/arch.svg" alt=""> Arch Linux</span>
                            <span class="hp2-distro"><img src="images/distros/proxmox.svg" alt=""> Proxmox VE</span>
                            <span class="hp2-distro"><img src="images/distros/raspberrypi.svg" alt=""> Raspberry Pi</span>
                        </div>
                    </div>

                    <div class="hp2-actions" style="justify-content:flex-start;">
                        <a href="#features" class="btn btn-primary">Learn More</a>
                    </div>

                    <p class="hp2-meta" style="text-align:left;">
                        Free &amp; open source &middot; MIT License &middot;
                        <a href="enterprise.php">Enterprise licensing</a> available
                        <?php if ($_setupCopies >= 1000): ?>
                        &middot; <strong><?= number_format($_setupCopies) ?> installs</strong>
                        <?php endif; ?>
                    </p>
                </div>
            </section>


            <!-- ============================================ -->
            <!--  STATS BAR                                    -->
            <!-- ============================================ -->
            <section class="hp2-section" style="padding:32px 0 0;">
                <div style="display:flex;justify-content:center;gap:48px;flex-wrap:wrap;max-width:900px;margin:0 auto;padding:0 20px;">
                    <div style="text-align:center;">
                        <div style="font-size:2.4rem;font-weight:800;color:var(--accent-primary);line-height:1;">500+</div>
                        <div style="font-size:0.82rem;color:var(--text-secondary);margin-top:4px;">One-Click Apps</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:2.4rem;font-weight:800;color:var(--text-primary);line-height:1;">1</div>
                        <div style="font-size:0.82rem;color:var(--text-secondary);margin-top:4px;">Binary to Install</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:2.4rem;font-weight:800;color:var(--text-primary);line-height:1;">8</div>
                        <div style="font-size:0.82rem;color:var(--text-secondary);margin-top:4px;">Linux Distros</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:2.4rem;font-weight:800;color:var(--text-primary);line-height:1;">0</div>
                        <div style="font-size:0.82rem;color:var(--text-secondary);margin-top:4px;">Databases Required</div>
                    </div>
                </div>
            </section>

            <!-- ============================================ -->
            <!--  REPLACES SECTION                            -->
            <!-- ============================================ -->
            <section id="features" class="hp2-replaces">
                <div class="hp2-section-tight">
                    <div class="hp2-section-header">
                        <h2>Stop duct-taping your infrastructure together</h2>
                        <p>WolfStack replaces a patchwork of tools with one unified platform.</p>
                    </div>

                    <div class="hp2-replaces-grid">
                        <div class="hp2-replaces-item">
                            <span class="tool-name">Proxmox</span>
                            <span class="tool-purpose">VMs &amp; LXC</span>
                        </div>
                        <span class="hp2-replaces-plus">+</span>
                        <div class="hp2-replaces-item">
                            <span class="tool-name">Portainer</span>
                            <span class="tool-purpose">Docker</span>
                        </div>
                        <span class="hp2-replaces-plus">+</span>
                        <div class="hp2-replaces-item">
                            <span class="tool-name">Uptime Kuma</span>
                            <span class="tool-purpose">Monitoring</span>
                        </div>
                        <span class="hp2-replaces-plus">+</span>
                        <div class="hp2-replaces-item">
                            <span class="tool-name">Ansible</span>
                            <span class="tool-purpose">Automation</span>
                        </div>
                        <span class="hp2-replaces-plus">+</span>
                        <div class="hp2-replaces-item">
                            <span class="tool-name">Tailscale</span>
                            <span class="tool-purpose">Networking</span>
                        </div>

                        <div class="hp2-replaces-equals">
                            <span class="eq">=</span>
                        </div>

                        <div class="hp2-replaces-result">
                            <span class="tool-name">WolfStack</span>
                            <span class="tool-purpose">All of the above. One binary.</span>
                        </div>
                    </div>
                </div>
            </section>


            <!-- ============================================ -->
            <!--  FEATURE SPOTLIGHTS (alternating)            -->
            <!-- ============================================ -->
            <section class="hp2-section">
                <div class="hp2-section-header">
                    <h2>Built for real infrastructure</h2>
                    <p>Not a toy dashboard. A production-grade platform for 1 to 200+ servers.</p>
                </div>

                <!-- Spotlight 1: Containers -->
                <div class="hp2-spotlight">
                    <div class="hp2-spot-text">
                        <h3>Docker + LXC + VMs + Kubernetes. One interface.</h3>
                        <p>Most tools only do Docker. WolfStack manages Docker containers, LXC containers, and virtual machines with the same UI, same clustering, and same orchestration.</p>
                        <ul class="hp2-spot-bullets">
                            <li>Create, clone, migrate, and snapshot containers across nodes</li>
                            <li>500+ one-click apps in the built-in App Store</li>
                            <li>Install on top of Proxmox VE &mdash; it auto-detects your cluster</li>
                            <li>WolfRun orchestration with automatic container failover</li>
                        </ul>
                    </div>
                    <div class="hp2-spot-visual">
                        <img src="images/screenshots/hero-containers-2x.png" alt="WolfStack container management showing Docker and LXC containers across multiple nodes" loading="lazy">
                    </div>
                </div>

                <!-- Spotlight 2: App Store -->
                <div class="hp2-spotlight reverse">
                    <div class="hp2-spot-text">
                        <h3>App Store with 500+ applications</h3>
                        <p>The largest self-hosted app store available. Deploy production-ready applications to any node with one click &mdash; every app comes pre-configured with the right ports, volumes, and environment variables.</p>
                        <ul class="hp2-spot-bullets">
                            <li>Media: Plex, Jellyfin, Sonarr, Radarr, Navidrome, and the full Servarr stack</li>
                            <li>Dev: GitLab, Gitea, Jenkins, SonarQube, Docker Registry, and more</li>
                            <li>AI/ML: Ollama, LocalAI, Stable Diffusion, ComfyUI, Jupyter</li>
                            <li>Home: Home Assistant, Node-RED, Frigate, AdGuard, Pi-hole</li>
                            <li>Deploy as Docker, LXC, or bare metal with sidecar databases</li>
                        </ul>
                    </div>
                    <div class="hp2-spot-visual">
                        <img src="images/screenshots/hero-appstore-2x.png" alt="WolfStack App Store showing one-click application deployment" loading="lazy">
                    </div>
                </div>

                <!-- Spotlight 3: Monitoring -->
                <div class="hp2-spotlight">
                    <div class="hp2-spot-text">
                        <h3>Monitoring, status pages, and alerting built in</h3>
                        <p>No more running a separate Uptime Kuma instance. Create public status pages, track 90-day uptime history, and get alerts when things go wrong.</p>
                        <ul class="hp2-spot-bullets">
                            <li>HTTP, TCP, ping, and container health monitors</li>
                            <li>Public status pages with custom branding and themes</li>
                            <li>Alerts via Discord, Slack, Telegram, and email</li>
                            <li>AI-powered health monitoring with Claude or Gemini</li>
                        </ul>
                    </div>
                    <div class="hp2-spot-visual">
                        <img src="images/screenshots/statuspage.png" alt="WolfStack status page monitoring with uptime tracking" loading="lazy">
                    </div>
                </div>
            </section>


            <!-- ============================================ -->
            <!--  FEATURE GRID                                -->
            <!-- ============================================ -->
            <section class="hp2-section" style="padding-top:0;">
                <div class="hp2-features">
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#9741;</div>
                        <h3>Multi-Server Clustering</h3>
                        <p>Add servers with one click. Real-time metrics, container migration between nodes, and automatic peer discovery.</p>
                    </div>
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#9951;</div>
                        <h3>Encrypted Mesh Network</h3>
                        <p>WolfNet creates an encrypted private network between all your servers. Works across data centres, clouds, and home labs.</p>
                    </div>
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#8635;</div>
                        <h3>Backup &amp; Restore</h3>
                        <p>Schedule backups to S3, Proxmox Backup Server, local paths, or remote nodes. Full container config preserved.</p>
                    </div>
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#9881;</div>
                        <h3>Workflow Automation</h3>
                        <p>WolfFlow&rsquo;s visual editor builds multi-step runbooks. Schedule with cron, target any node or container.</p>
                    </div>
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#9000;</div>
                        <h3>Kubernetes Management</h3>
                        <p>Provision k3s, MicroK8s, or kubeadm clusters. Pod management, logs, terminal, storage, and WolfNet load balancing.</p>
                    </div>
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#9878;</div>
                        <h3>Storage &amp; Disks</h3>
                        <p>Disk partitioning, SMART health, ZFS pools, Ceph clusters, and S3/NFS mount management with cluster-wide replication.</p>
                    </div>
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#9735;</div>
                        <h3>Container Failover</h3>
                        <p>WolfRun pre-stages standby containers. If a node goes down, standbys promote automatically. Zero-downtime HA.</p>
                    </div>
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#9749;</div>
                        <h3>Database Editor</h3>
                        <p>Browse and query MySQL, MariaDB, and PostgreSQL databases directly from the dashboard. No phpMyAdmin needed.</p>
                    </div>
                    <div class="hp2-feat">
                        <div class="hp2-feat-icon">&#9733;</div>
                        <h3>Security &amp; Firewall</h3>
                        <p>Fail2ban management, UFW rules, iptables, SSL certificates via Let&rsquo;s Encrypt, and Nginx/Apache configuration.</p>
                    </div>
                </div>
            </section>


            <!-- ============================================ -->
            <!--  COMPARISON TABLE                            -->
            <!-- ============================================ -->
            <section class="hp2-compare">
                <div class="hp2-section">
                    <div class="hp2-section-header">
                        <h2>How does WolfStack compare?</h2>
                        <p>One platform instead of six separate tools. Every feature, side by side.</p>
                    </div>

                    <!-- Container & VM Management -->
                    <h3 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:10px;">Container &amp; VM Management</h3>
                    <div class="table-wrapper" style="margin-bottom:24px;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Feature</th>
                                    <th class="hl">WolfStack</th>
                                    <th>Proxmox</th>
                                    <th>Kubernetes</th>
                                    <th>Portainer</th>
                                    <th>CasaOS</th>
                                    <th>Cockpit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Docker Containers</td><td class="hl">Yes</td><td>Limited</td><td>Yes</td><td>Yes</td><td>Yes</td><td>Plugin</td></tr>
                                <tr><td>LXC System Containers</td><td class="hl">Yes</td><td>Yes</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>KVM/QEMU Virtual Machines</td><td class="hl">Yes</td><td>Yes</td><td>No</td><td>No</td><td>No</td><td>Basic</td></tr>
                                <tr><td>Proxmox VE Integration</td><td class="hl">Yes</td><td>Native</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Container Clone &amp; Migrate</td><td class="hl">Yes</td><td>Yes</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Container Cron Jobs</td><td class="hl">Yes</td><td>No</td><td>CronJobs</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Docker Image Management</td><td class="hl">Yes</td><td>No</td><td>No</td><td>Yes</td><td>No</td><td>No</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Orchestration & HA -->
                    <h3 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:10px;">Orchestration &amp; High Availability</h3>
                    <div class="table-wrapper" style="margin-bottom:24px;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Feature</th>
                                    <th class="hl">WolfStack</th>
                                    <th>Proxmox</th>
                                    <th>Kubernetes</th>
                                    <th>Portainer</th>
                                    <th>CasaOS</th>
                                    <th>Cockpit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Container Orchestration</td><td class="hl">WolfRun</td><td>No</td><td>Yes</td><td>Swarm</td><td>No</td><td>No</td></tr>
                                <tr><td>Container Failover (HA)</td><td class="hl">Standby HA</td><td>HA (paid)</td><td>Yes</td><td>Swarm HA</td><td>No</td><td>No</td></tr>
                                <tr><td>Kubernetes Management</td><td class="hl">WolfKube</td><td>No</td><td>Native</td><td>Paid</td><td>No</td><td>No</td></tr>
                                <tr><td>Pod Terminal &amp; Monitoring</td><td class="hl">Yes</td><td>No</td><td>CLI only</td><td>Yes</td><td>No</td><td>No</td></tr>
                                <tr><td>K8s PVC Storage</td><td class="hl">Yes</td><td>No</td><td>CLI only</td><td>Paid</td><td>No</td><td>No</td></tr>
                                <tr><td>Multi-Server Clustering</td><td class="hl">Yes</td><td>Yes</td><td>Yes</td><td>Paid</td><td>No</td><td>No</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Networking, Security & Auth -->
                    <h3 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:10px;">Networking, Security &amp; Authentication</h3>
                    <div class="table-wrapper" style="margin-bottom:24px;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Feature</th>
                                    <th class="hl">WolfStack</th>
                                    <th>Proxmox</th>
                                    <th>Kubernetes</th>
                                    <th>Portainer</th>
                                    <th>CasaOS</th>
                                    <th>Cockpit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Built-in User Accounts</td><td class="hl">Yes</td><td>Linux/PAM</td><td>RBAC</td><td>Yes</td><td>Yes</td><td>Linux/PAM</td></tr>
                                <tr><td>Two-Factor Auth (2FA)</td><td class="hl">TOTP</td><td>Paid only</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Disable Linux Login</td><td class="hl">Yes</td><td>No</td><td>N/A</td><td>N/A</td><td>No</td><td>No</td></tr>
                                <tr><td>Login Rate Limiting</td><td class="hl">Yes</td><td>Yes</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Encrypted Mesh Network</td><td class="hl">WolfNet</td><td>No</td><td>CNI plugins</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Built-in VPN</td><td class="hl">WolfNet VPN</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>WireGuard VPN Bridge</td><td class="hl">Yes</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Firewall Management</td><td class="hl">Fail2ban &amp; UFW</td><td>Firewall</td><td>No</td><td>No</td><td>No</td><td>Firewalld</td></tr>
                                <tr><td>SSL/TLS Certificates</td><td class="hl">Let&rsquo;s Encrypt</td><td>Let&rsquo;s Encrypt</td><td>cert-manager</td><td>No</td><td>No</td><td>Certbot</td></tr>
                                <tr><td>Nginx/Apache Configurator</td><td class="hl">Yes</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Storage -->
                    <h3 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:10px;">Storage &amp; Backups</h3>
                    <div class="table-wrapper" style="margin-bottom:24px;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Feature</th>
                                    <th class="hl">WolfStack</th>
                                    <th>Proxmox</th>
                                    <th>Kubernetes</th>
                                    <th>Portainer</th>
                                    <th>CasaOS</th>
                                    <th>Cockpit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>S3/R2 Object Storage</td><td class="hl">Yes</td><td>No</td><td>CSI</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>NFS Mounts</td><td class="hl">Yes</td><td>Yes</td><td>PVC</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>SSHFS</td><td class="hl">Yes</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Ceph Management</td><td class="hl">Yes</td><td>Yes</td><td>Rook</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>ZFS Management</td><td class="hl">Yes</td><td>Yes</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Disk Partitioning &amp; SMART</td><td class="hl">Yes</td><td>No</td><td>No</td><td>No</td><td>No</td><td>Basic</td></tr>
                                <tr><td>Backup &amp; Restore</td><td class="hl">S3, PBS, Local</td><td>PBS</td><td>Velero</td><td>No</td><td>No</td><td>No</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Monitoring, Tools & UI -->
                    <h3 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:10px;">Monitoring, Tools &amp; UI</h3>
                    <div class="table-wrapper" style="margin-bottom:24px;">
                        <table>
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Feature</th>
                                    <th class="hl">WolfStack</th>
                                    <th>Proxmox</th>
                                    <th>Kubernetes</th>
                                    <th>Portainer</th>
                                    <th>CasaOS</th>
                                    <th>Cockpit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Web Terminal</td><td class="hl">Yes</td><td>Yes</td><td>No</td><td>Yes</td><td>No</td><td>Yes</td></tr>
                                <tr><td>File Manager</td><td class="hl">Yes</td><td>No</td><td>No</td><td>No</td><td>Yes</td><td>No</td></tr>
                                <tr><td>App Store</td><td class="hl">500+ apps</td><td>No</td><td>Helm</td><td>Yes</td><td>Yes</td><td>No</td></tr>
                                <tr><td>AI Infrastructure Agent</td><td class="hl">CodeWolf &amp; Gemini</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Status Pages &amp; Alerting</td><td class="hl">Built in</td><td>No</td><td>Add-ons</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Workflow Automation</td><td class="hl">WolfFlow</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>Database Editor</td><td class="hl">MySQL &amp; PostgreSQL</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>3D VR Server Room</td><td class="hl">Yes (WebXR)</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                                <tr><td>VR Terminal &amp; Console</td><td class="hl">Yes</td><td>No</td><td>No</td><td>No</td><td>No</td><td>No</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Platform -->
                    <h3 style="font-size:0.82rem;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--text-muted);margin-bottom:10px;">Platform</h3>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th style="text-align:left;">Feature</th>
                                    <th class="hl">WolfStack</th>
                                    <th>Proxmox</th>
                                    <th>Kubernetes</th>
                                    <th>Portainer</th>
                                    <th>CasaOS</th>
                                    <th>Cockpit</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>Install Complexity</td><td class="hl">1 command</td><td>ISO install</td><td>Very complex</td><td>Moderate</td><td>1 command</td><td>1 command</td></tr>
                                <tr><td>Runs On</td><td class="hl">Any Linux + Proxmox</td><td>Debian only</td><td>Any Linux</td><td>Docker host</td><td>Debian/Ubuntu</td><td>Any Linux</td></tr>
                                <tr><td>Written In</td><td class="hl">Rust</td><td>Perl/C</td><td>Go</td><td>Go</td><td>Go</td><td>Python/C</td></tr>
                                <tr><td>Single Binary</td><td class="hl">Yes</td><td>No</td><td>No</td><td>Container</td><td>No</td><td>No</td></tr>
                                <tr><td>Price</td><td class="hl">Free &amp; Open Source</td><td>Free + Paid</td><td>Free</td><td>Free + Paid</td><td>Free</td><td>Free</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>


            <!-- ============================================ -->
            <!--  WOLF TOOLKIT                                -->
            <!-- ============================================ -->
            <section class="hp2-section">
                <div class="hp2-section-header">
                    <h2>The Wolf Toolkit</h2>
                    <p>A complete ecosystem for server infrastructure.</p>
                </div>

                <div class="hp2-products">
                    <a href="wolfstack.php" class="hp2-product">
                        <h3>WolfStack <span class="hp2-tag hp2-tag-flag">Flagship</span></h3>
                        <p>The management platform. Dashboard, containers, VMs, monitoring, App Store, status pages, AI agent.</p>
                    </a>
                    <a href="wolfnet.php" class="hp2-product">
                        <h3>WolfNet</h3>
                        <p>Encrypted mesh networking with built-in VPN. Connect servers across any network.</p>
                    </a>
                    <a href="wolfrun.php" class="hp2-product">
                        <h3>WolfRun <span class="hp2-tag hp2-tag-built">Built in</span></h3>
                        <p>Container orchestration with failover. Schedule and scale services across your cluster.</p>
                    </a>
                    <a href="wolfflow.php" class="hp2-product">
                        <h3>WolfFlow <span class="hp2-tag hp2-tag-built">Built in</span></h3>
                        <p>Visual workflow automation. Drag-and-drop runbooks with cron scheduling.</p>
                    </a>
                    <a href="wolfdisk.php" class="hp2-product">
                        <h3>WolfDisk</h3>
                        <p>Distributed filesystem with content-addressed deduplication.</p>
                    </a>
                    <a href="wolfproxy.php" class="hp2-product">
                        <h3>WolfProxy</h3>
                        <p>NGINX-compatible reverse proxy with built-in firewall.</p>
                    </a>
                    <a href="wolfkube.php" class="hp2-product">
                        <h3>WolfKube <span class="hp2-tag hp2-tag-built">Built in</span></h3>
                        <p>Kubernetes cluster management. Provision k3s, MicroK8s, kubeadm, and more.</p>
                    </a>
                    <a href="wolfserve.php" class="hp2-product">
                        <h3>WolfServe</h3>
                        <p>Apache-compatible web server with PHP. Reads existing vhost configs.</p>
                    </a>
                </div>
            </section>


            <!-- ============================================ -->
            <!--  QUICK START                                 -->
            <!-- ============================================ -->
            <section id="quickstart" class="hp2-section" style="padding-top:0;">
                <div class="hp2-section-header">
                    <h2>Up and running in minutes</h2>
                    <p>Install WolfStack on every server you want to manage, then connect them into a cluster.</p>
                </div>

                <div style="max-width:720px;margin:0 auto;">
                    <ol class="hp2-steps">
                        <li>
                            <strong>Install WolfStack on each server</strong>
                            <span class="hp2-step-desc">Run the installer on every computer you want to manage &mdash; from a Raspberry Pi to a datacenter server or cloud VPS:</span>
                            <div class="code-block" style="margin:10px 0;">
                                <div class="code-header"><span>bash</span><button class="copy-btn" data-track="setup-url" onclick="copyCode(this)">Copy</button></div>
                                <pre><code>curl -sSL https://raw.githubusercontent.com/wolfsoftwaresystemsltd/WolfStack/master/setup.sh | sudo bash</code></pre>
                            </div>
                            <span class="hp2-step-hint">
                                If <code>sudo</code> or <code>curl</code> are not installed:
                                <strong>Debian/Ubuntu:</strong> <code>apt install sudo curl</code> &middot;
                                <strong>RHEL/Fedora:</strong> <code>dnf install sudo curl</code> &middot;
                                <strong>Arch:</strong> <code>pacman -S sudo curl</code> &middot;
                                <strong>openSUSE:</strong> <code>zypper install sudo curl</code>
                            </span>
                        </li>
                        <li>
                            <strong>Get the cluster token from each server</strong>
                            <span class="hp2-step-desc">After installation, each server displays its cluster token. You can also retrieve it at any time:</span>
                            <div class="code-block" style="margin:10px 0;">
                                <div class="code-header"><span>bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                                <pre><code>wolfstack --show-token</code></pre>
                            </div>
                        </li>
                        <li>
                            <strong>Open the web UI on one server</strong>
                            <span class="hp2-step-desc">Navigate to <code>https://your-server-ip:8553</code> in your browser and log in with your Linux credentials. You only need to log in to <strong>one</strong> server &mdash; it manages the rest.</span>
                        </li>
                        <li>
                            <strong>Add your other nodes to the cluster</strong>
                            <span class="hp2-step-desc">Click the <strong>+</strong> button in the dashboard and paste each server&rsquo;s cluster token. You can add WolfStack servers, Proxmox VE hosts, or a mix of both. Add as many clusters as you like for a single-pane-of-glass view.</span>
                        </li>
                        <li>
                            <strong>Set up encrypted networking</strong>
                            <span class="hp2-step-desc">Go to your cluster settings and click <strong>Update WolfNet Connections</strong> to automatically create an encrypted peer-to-peer mesh network between all your nodes &mdash; even across different data centres and cloud providers.</span>
                        </li>
                    </ol>

                    <div class="warning-box" style="margin-top:20px;">
                        <p><strong>Rust compilation:</strong> WolfStack compiles from source during installation. On low-powered devices like Raspberry Pi, the first build compiles all ~330 crates and can take <strong>30&ndash;60 minutes</strong>. The installer automatically creates temporary swap space to prevent out-of-memory failures. Subsequent upgrades only recompile WolfStack itself and take just a few minutes.</p>
                    </div>
                    <p style="color:var(--text-muted);font-size:0.8rem;margin-top:10px;text-align:center;">
                        <strong>Low disk space?</strong> Build on an external drive:
                        <code>curl -sSL ...setup.sh | sudo bash -s -- --install-dir /mnt/usb</code>
                    </p>
                </div>
            </section>

            <!-- Live USB section hidden until ISO is ready -->
            </section>

            <!-- ============================================ -->
            <!--  FUNDING                                      -->
            <!-- ============================================ -->
            <section class="hp2-funding">
                <div class="hp2-section">
                    <div class="hp2-section-header">
                        <h2>Support WolfStack</h2>
                        <p>WolfStack is free and open source, built by a small team. Your support keeps development going.</p>
                    </div>

                    <div class="hp2-funding-grid">
                        <!-- Community free -->
                        <div class="hp2-fund-card">
                            <div class="hp2-fund-label">Community</div>
                            <div class="hp2-fund-name">Free</div>
                            <div class="hp2-fund-price free">Free</div>
                            <div class="hp2-fund-desc">All features, unlimited servers, forever.</div>
                            <ul class="hp2-fund-features">
                                <li>All features included</li>
                                <li>Unlimited servers</li>
                                <li>Full source code access</li>
                                <li>Public Discord community</li>
                                <li>Regular updates</li>
                            </ul>
                            <a href="#quickstart" class="btn btn-secondary" style="width:100%;">Get Started</a>
                        </div>

                        <!-- GitHub Sponsors -->
                        <div class="hp2-fund-card hp2-fund-sponsor">
                            <div class="hp2-fund-label">Backer</div>
                            <div class="hp2-fund-name">Fund Open Source</div>
                            <div class="hp2-fund-price" style="color:var(--success);">From &pound;3 <span class="period">/ month</span></div>
                            <div class="hp2-fund-desc">Keep WolfStack free and independent. Every feature ships to everyone &mdash; backers make that possible.</div>
                            <ul class="hp2-fund-features">
                                <li>Everything in Community</li>
                                <li>Private backers-only Discord channel</li>
                                <li>Direct support from the dev team</li>
                                <li>Early access to beta features</li>
                                <li>Priority bug fixes</li>
                                <li>Your name on the Supporters page</li>
                                <li>Vote on the development roadmap</li>
                            </ul>
                            <a href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank" class="btn btn-sponsor" style="width:100%;">Back This Project</a>
                        </div>

                        <!-- Enterprise Standard -->
                        <div class="hp2-fund-card featured">
                            <div class="hp2-fund-label">Enterprise</div>
                            <div class="hp2-fund-name">Standard</div>
                            <div class="hp2-fund-price">&pound;450 <span class="period">/ year per socket</span></div>
                            <div class="hp2-fund-desc">Professional services for businesses that need formal support.</div>
                            <ul class="hp2-fund-features">
                                <li>Everything in Sponsor</li>
                                <li>Full installation &amp; onboarding</li>
                                <li>Migration from Proxmox/Portainer</li>
                                <li>SLA with 4-hour response time</li>
                                <li>Monthly check-ins</li>
                                <li>Team training sessions</li>
                            </ul>
                            <a href="enterprise-contact.php?plan=Standard" class="btn btn-primary" style="width:100%;">Contact Sales</a>
                        </div>

                        <!-- Enterprise Premium -->
                        <div class="hp2-fund-card">
                            <div class="hp2-fund-label">Enterprise</div>
                            <div class="hp2-fund-name">Premium</div>
                            <div class="hp2-fund-price">&pound;900 <span class="period">/ year per socket</span></div>
                            <div class="hp2-fund-desc">24/7 support, SLA guarantee, and a dedicated account manager.</div>
                            <ul class="hp2-fund-features">
                                <li>Everything in Standard</li>
                                <li>24/7 support</li>
                                <li>SLA with 2-hour response time</li>
                                <li>Dedicated account manager</li>
                                <li>Custom development requests</li>
                                <li>On-site or remote training</li>
                                <li>Architecture consulting</li>
                            </ul>
                            <a href="enterprise-contact.php?plan=Premium" class="btn btn-secondary" style="width:100%;">Contact Sales</a>
                        </div>
                    </div>

                    <p style="text-align:center;margin-top:16px;font-size:0.8rem;color:var(--text-muted);">
                        All plans include every feature. Enterprise adds dedicated support and SLAs.
                        <a href="enterprise.php" style="color:var(--accent-primary);">See all plans &rarr;</a>
                    </p>
                </div>
            </section>


            <!-- ============================================ -->
            <!--  MANAGED SETUP SERVICE                       -->
            <!-- ============================================ -->
            <section class="hp2-section">
                <div class="hp2-section-header">
                    <h2>Don&rsquo;t want to set it up yourself?</h2>
                    <p>We&rsquo;ll build your entire cluster for you, ready to go.</p>
                </div>

                <div class="hp2-service">
                    <div>
                        <h3>Managed Cluster Setup</h3>
                        <p>Our team will install WolfStack on your servers, configure clustering, networking, storage, backups,
                            monitoring, and deploy your applications &mdash; so you can start using it immediately without touching the command line.</p>
                        <ul class="hp2-service-features">
                            <li>WolfStack installed on all your servers</li>
                            <li>Cluster configured and nodes connected</li>
                            <li>WolfNet encrypted mesh set up</li>
                            <li>Storage mounts configured (S3, NFS, etc.)</li>
                            <li>Backup schedules configured</li>
                            <li>Status pages and alerting set up</li>
                            <li>Your applications deployed via App Store</li>
                            <li>Firewall and security hardened</li>
                            <li>SSL certificates provisioned</li>
                            <li>Handover walkthrough with your team</li>
                        </ul>
                    </div>
                    <div class="hp2-service-cta">
                        <div class="price">From &pound;500</div>
                        <div class="price-note">One-time fee &middot; price depends on cluster size</div>
                        <a href="enterprise-contact.php?plan=ManagedSetup" class="btn btn-primary" style="width:100%;">Get a Quote</a>
                        <div class="includes">Includes up to 5 servers.<br>Additional servers &pound;50 each.<br>Remote setup via SSH.</div>
                    </div>
                </div>
            </section>


            <!-- ============================================ -->
            <!--  CTA                                         -->
            <!-- ============================================ -->
            <section class="hp2-cta">
                <h2>Ready to simplify your infrastructure?</h2>
                <p>One command to install. No containers, no database, no dependencies &mdash; just a single Rust binary.
                    Free forever for personal use.</p>

                <div class="hp2-actions" style="justify-content:center;">
                    <a href="wolfstack.php" class="btn btn-primary">Learn More</a>
                    <a href="https://github.com/sponsors/wolfsoftwaresystemsltd" target="_blank" class="btn btn-sponsor">Fund Open Source</a>
                </div>

                <div class="hp2-cta-links">
                    <a href="https://discord.gg/q9qMjHjUQY" target="_blank">Discord</a>
                    <a href="https://www.reddit.com/r/WolfStack/" target="_blank">Reddit</a>
                    <a href="https://www.youtube.com/@wolfsoftwaresystems" target="_blank">YouTube</a>
                    <a href="https://github.com/wolfsoftwaresystemsltd/WolfScale" target="_blank">GitHub</a>
                    <a href="https://opensimsocial.com/@lonewolf" target="_blank" rel="me">Mastodon</a>
                </div>
            </section>

        </div>

<script>
// Slider logic
function createSlider(sliderId, dotsId) {
    var slider = document.getElementById(sliderId);
    if (!slider) return null;
    var inner = slider.querySelector('.hp2-slider-inner');
    var slides = slider.querySelectorAll('.hp2-slide');
    var dotsContainer = document.getElementById(dotsId);
    var current = 0;
    var total = slides.length;
    var autoTimer = null;

    // Create dots
    for (var i = 0; i < total; i++) {
        var dot = document.createElement('button');
        dot.className = 'hp2-slider-dot' + (i === 0 ? ' active' : '');
        dot.setAttribute('aria-label', 'Slide ' + (i + 1));
        (function(idx) {
            dot.addEventListener('click', function() { goTo(idx); });
        })(i);
        dotsContainer.appendChild(dot);
    }

    function goTo(idx) {
        current = ((idx % total) + total) % total;
        inner.style.transform = 'translateX(-' + (current * 100) + '%)';
        var dots = dotsContainer.querySelectorAll('.hp2-slider-dot');
        for (var j = 0; j < dots.length; j++) {
            dots[j].className = 'hp2-slider-dot' + (j === current ? ' active' : '');
        }
        resetAuto();
    }

    function advance(dir) { goTo(current + dir); }

    function resetAuto() {
        if (autoTimer) clearInterval(autoTimer);
        autoTimer = setInterval(function() { advance(1); }, 6000);
    }

    resetAuto();
    return { advance: advance, goTo: goTo };
}

var heroSlider = createSlider('hero-slider', 'hero-dots');
function slideHero(dir) { if (heroSlider) heroSlider.advance(dir); }
</script>

<?php include 'includes/footer.php'; ?>

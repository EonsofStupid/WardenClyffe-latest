# Brand And Asset Replacement Manifest

This repo is being moved to a clean WardenClyffe identity.

Final product language:

- **WardenClyffe**: the full platform.
- **Warden**: operator/server manager.
- **Clyffe**: customer portal, customer knowledge base, tickets, and CRM.
- **WardenClyffeNet**: private mesh networking.
- **WardenClyffeDisk**: distributed filesystem/storage.
- **WardenClyffeScale**: database replication product, if retained as a separate product.

## Current Asset Names

Root assets/config now use WardenClyffeScale naming:

- `wardenclyffescale.toml.example`
- `wardenclyffescale.logrotate`
- `wardenclyffescale.png`

Web pages now use WardenClyffe naming:

- `web/wardenclyffe.php`
- `web/wardenclyffe-ai.php`
- `web/wardenclyffe-alerting.php`
- `web/wardenclyffe-api.php`
- `web/wardenclyffe-backups.php`
- `web/wardenclyffe-certificates.php`
- `web/wardenclyffe-clustering.php`
- `web/wardenclyffe-containers.php`
- `web/wardenclyffe-cron.php`
- `web/wardenclyffe-files.php`
- `web/wardenclyffe-issues.php`
- `web/wardenclyffe-mysql.php`
- `web/wardenclyffe-networking.php`
- `web/wardenclyffe-plugins.php`
- `web/wardenclyffe-security.php`
- `web/wardenclyffe-settings.php`
- `web/wardenclyffe-statuspage.php`
- `web/wardenclyffe-storage.php`
- `web/wardenclyffe-terminal.php`
- `web/wardenclyffe-troubleshooting.php`
- `web/wardenclyffe-vms.php`
- `web/wardenclyffe-vr.php`
- `web/wardenclyffe-wardenclyffenote.php`
- `web/wardenclyffedisk.php`
- `web/wardenclyffedisk.html`
- `web/wardenclyffeflow.php`
- `web/wardenclyffehost.php`
- `web/wardenclyffenet.php`
- `web/wardenclyffenet-global.php`
- `web/wardenclyffenet-vpn.php`
- `web/wardenclyffenote.php`
- `web/wardenclyffeproxy.php`
- `web/wardenclyfferun.php`
- `web/wardenclyffeserve.php`

Web graphics now use WardenClyffe naming:

- `web/wardenclyffe-dashboard.png`
- `web/wardenclyffe-logo.png`
- `web/images/wardenclyffe-dashboard.png`
- `web/images/wardenclyffe-logo.png`
- `web/images/images/wardenclyffe-dashboard.png`
- `web/images/images/images/wardenclyffe-dashboard.png`
- `web/images/screenshots/global-wardenclyffenet.png`
- `web/images/screenshots/wardenclyffeflow.png`
- `web/images/screenshots/wardenclyffenet.png`
- `web/images/screenshots/wardenclyffenote.png`
- `web/images/screenshots/wardenclyfferun.png`

Rust/code/workflows now use WardenClyffe naming:

- `wardenclyffedisk/src/bin/wardenclyffediskctl.rs`
- `.github/workflows/wardenclyffedisk-release.yml`
- `.github/workflows/wardenclyffedisk-docker-publish.yml`

## Graphics Replacement Rule

Filenames have been moved to WardenClyffe naming, but some image pixels may still contain older visual identity. When an image is encountered in the app/site:

1. Confirm whether the graphic itself is brand-neutral or needs replacement.
2. Replace product logos with approved WardenClyffe, Warden, or Clyffe artwork.
3. Replace screenshots with current Warden and Clyffe UI captures once those panels exist.
4. Keep Proxmox references only where they describe the infrastructure substrate.

## Website Direction

The PHP web tree should be treated as legacy source material.

Recommended replacement:

- Use Astro for the WardenClyffe public site, docs, knowledge base, changelog, and customer help center.
- Serve the generated static site with Caddy or Nginx.
- Keep Clyffe customer portal as an app backed by Warden APIs, not as static PHP.


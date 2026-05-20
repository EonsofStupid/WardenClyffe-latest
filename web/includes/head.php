<!DOCTYPE html>
<html lang="en">
<head>
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-KK9NEE1S54"></script>
    <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config','G-KK9NEE1S54');</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
    // Auto-generate canonical URL if not explicitly set
    if (empty($page_canonical)) {
        $script = basename($_SERVER['SCRIPT_NAME']);
        $page_canonical = ($script === 'index.php')
            ? 'https://wardenclyffescale.org/'
            : 'https://wardenclyffescale.org/' . $script;
    }
?>
    <meta name="description" content="<?php echo htmlspecialchars($page_desc ?? 'WardenClyffe — The Universal Server Management Platform'); ?>">
    <meta name="keywords" content="<?php echo htmlspecialchars($page_keywords ?? 'server management, WardenClyffe, dashboard, Docker, LXC, monitoring, clustering, WardenClyffeScale, WardenClyffeDisk, WardenClyffeNet'); ?>">
    <meta name="author" content="WardenClyffe Software Systems Ltd">
    <link rel="canonical" href="<?php echo $page_canonical; ?>">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo $page_canonical; ?>">
    <meta property="og:title" content="<?php echo htmlspecialchars($page_title ?? 'WardenClyffe'); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($page_desc ?? 'WardenClyffe — The Universal Server Management Platform'); ?>">
    <meta property="og:image" content="https://wardenclyffescale.org/images/wardenclyffe-logo.png">
    <meta property="og:site_name" content="WardenClyffe">

    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo $page_canonical; ?>">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($page_title ?? 'WardenClyffe'); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($page_desc ?? 'WardenClyffe — The Universal Server Management Platform'); ?>">
    <meta name="twitter:image" content="https://wardenclyffescale.org/images/wardenclyffe-logo.png">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "WardenClyffe",
        "applicationCategory": "DeveloperApplication",
        "operatingSystem": "Linux",
        "description": "<?php echo htmlspecialchars($page_desc ?? 'WardenClyffe — The Universal Server Management Platform', ENT_QUOTES); ?>",
        "url": "https://wardenclyffescale.org/",
        "author": {
            "@type": "Organization",
            "name": "WardenClyffe Software Systems Ltd",
            "url": "https://wardenclyffe.uk.com"
        },
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "GBP"
        },
        "license": "https://wardenclyffescale.org/licensing.php"
    }
    </script>

    <title><?php echo htmlspecialchars($page_title ?? 'WardenClyffe'); ?></title>
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css?v=27">
    <script>
        (function(){var t=localStorage.getItem('wardenclyffescale-theme')||'light';document.documentElement.setAttribute('data-theme',t)})();
    </script>
<?php if (!empty($page_css)): ?>
    <style><?php echo $page_css; ?></style>
<?php endif; ?>
</head>

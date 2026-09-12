<?php
// includes/header.php
// Optional per-page overrides: $page_description, $canonical_url
$meta_description = isset($page_description) ? $page_description : 'Free calculator for 2x2 trial data. Reports the complete p-fr-nb evidence triplet: significance (p), fragility (fr), and robustness (nb).';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Fragility Metrics</title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8'); ?>">
    <?php if (isset($canonical_url)): ?>
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($canonical_url, ENT_QUOTES, 'UTF-8'); ?>">
    <?php endif; ?>
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Fragility Metrics">
    <meta property="og:title" content="<?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Fragility Metrics">
    <meta property="og:description" content="<?php echo htmlspecialchars($meta_description, ENT_QUOTES, 'UTF-8'); ?>">
    <meta property="og:image" content="https://www.fragilitymetrics.org/og-image.jpg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta name="twitter:card" content="summary_large_image">
    <link rel="stylesheet" href="css/style.css?v=<?php echo time(); ?>">
</head>
<body>
<nav>
    <div class="nav-container">
        <ul>
            <li><a href="index.php" <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'class="active"' : ''; ?>>Home</a></li>
            <li><a href="documentation.php" <?php echo basename($_SERVER['PHP_SELF']) == 'documentation.php' ? 'class="active"' : ''; ?>>Documentation</a></li>
            <li><a href="resources.php" <?php echo basename($_SERVER['PHP_SELF']) == 'resources.php' ? 'class="active"' : ''; ?>>Resources</a></li>
            <li><a href="faq.php" <?php echo basename($_SERVER['PHP_SELF']) == 'faq.php' ? 'class="active"' : ''; ?>>FAQ</a></li>
            <li><a href="about.php" <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'class="active"' : ''; ?>>About</a></li>
        </ul>
    </div>
</nav>
<div class="container">

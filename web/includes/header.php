<?php
// includes/header.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Fragility Metrics</title>
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

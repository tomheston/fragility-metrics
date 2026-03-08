<?php
// index.php
$page_title = 'Documentation';
include 'includes/header.php';

// 1. GitHub raw URL for FRAGILITY_METRICS.md (public repo)
$githubRawUrl = 'https://raw.githubusercontent.com/tomheston/fragility-metrics/main/FRAGILITY_METRICS.md';

// 2. Simple caching settings (optional but recommended)
$cacheFile = __DIR__ . '/cache/FRAGILITY_METRICS.md';
$cacheTtl  = 600; // seconds (10 minutes)

// Ensure cache directory exists
$cacheDir = dirname($cacheFile);
if (!is_dir($cacheDir)) {
    mkdir($cacheDir, 0777, true);
}

/**
 * Fetch Markdown from GitHub with optional caching.
 */
function getFragilityMarkdown(string $githubUrl, string $cacheFile, int $cacheTtl): ?string
{
    // Use cache if fresh enough
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTtl) {
        return file_get_contents($cacheFile) ?: null;
    }

    // Otherwise fetch from GitHub
    $markdown = fetchFromGithub($githubUrl);
    if ($markdown !== null) {
        file_put_contents($cacheFile, $markdown);
    }

    return $markdown;
}

/**
 * Fetch file contents from GitHub using cURL.
 */
function fetchFromGithub(string $url): ?string
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_USERAGENT      => 'FI-FQ-MFQ-docs-bot/1.0',
    ]);

    $data = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if ($httpCode !== 200 || $data === false) {
        error_log("Failed to fetch FRAGILITY_METRICS.md from GitHub: HTTP $httpCode; error: $err");
        return null;
    }

    return $data;
}

// 3. Load the Markdown
$markdown = getFragilityMarkdown($githubRawUrl, $cacheFile, $cacheTtl);

// 4. Extract version + last updated + render HTML
$htmlContent  = '';
$errorMessage = '';
$version      = null;
$lastUpdated  = file_exists($cacheFile) ? date('Y-m-d H:i:s', filemtime($cacheFile)) : null;

if ($markdown === null) {
    $errorMessage = 'Could not load the latest FRAGILITY_METRICS.md from GitHub.';
} else {
    // Try to extract version from the markdown text
    // Matches things like: "Version 10.3.6" or "Version: 10.3.6"
    if (preg_match('/Version[: ]+([0-9]+(?:\.[0-9]+)*)/i', $markdown, $m)) {
        $version = $m[1];
    }

    // Use Parsedown to convert Markdown → HTML
    require __DIR__ . '/Parsedown.php';

    $parsedown = new Parsedown();
    $parsedown->setSafeMode(false); // content is from your own repo
    $htmlContent = $parsedown->text($markdown);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Fragility Metrics Documentation</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }
        .error {
            padding: 1rem;
            border-radius: 8px;
            background: #ffecec;
            color: #b00000;
            margin-bottom: 1rem;
        }
        .meta {
            font-size: 0.875rem;
            color: #555;
            margin-bottom: 1.5rem;
            line-height: 1.5;
        }
        .meta strong {
            font-weight: 600;
        }
        .markdown h1, .markdown h2, .markdown h3 {
            margin-top: 1.8rem;
            margin-bottom: 0.8rem;
        }
        .markdown p {
            line-height: 1.6;
            margin-bottom: 1rem;
        }
        .markdown code {
            font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
            background: #f0f0f0;
            padding: 0.15rem 0.3rem;
            border-radius: 4px;
        }
        .markdown pre {
            background: #f0f0f0;
            padding: 1rem;
            overflow: auto;
            border-radius: 6px;
        }
        .markdown ul, .markdown ol {
            padding-left: 1.5rem;
        }
        .markdown a {
            color: #0066cc;
            text-decoration: none;
        }
        .markdown a:hover {
            text-decoration: underline;
        }
        code.inline {
            font-family: Menlo, Monaco, Consolas, "Courier New", monospace;
            background: #f7f7f7;
            padding: 0.1rem 0.25rem;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Fragility Metrics Documentation</h1>

    <div class="meta">
        <?php if ($version): ?>
            <div>Version: <strong><?php echo htmlspecialchars($version, ENT_QUOTES, 'UTF-8'); ?></strong></div>
        <?php endif; ?>

        <?php if ($lastUpdated): ?>
            <div>Last updated (from cache): <strong><?php echo htmlspecialchars($lastUpdated, ENT_QUOTES, 'UTF-8'); ?></strong></div>
        <?php endif; ?>

        <div>
            Source: <code class="inline"><?php echo htmlspecialchars($githubRawUrl, ENT_QUOTES, 'UTF-8'); ?></code>
        </div>
        <div>
            This page always reflects the latest committed <code class="inline">FRAGILITY_METRICS.md</code>
            on the configured branch, subject to a small cache delay.
        </div>
    </div>

    <div class="intro" style="margin-bottom: 1.5rem; line-height: 1.6;">
        <p>
            Fragility metrics provide a general framework for quantifying how robust or fragile
            statistical results are to small changes in the underlying data. The p–fr–nb framework
            described here is designed to apply across a wide range of study designs, including
            binary, continuous, and time-to-event outcomes.
        </p>
        <p>
            This documentation covers classical fragility measures such as the Fragility Index (FI),
            Fragility Quotient (FQ), and Marginal Fragility Quotient (MFQ), as well as more general
            extensions including the GFI and GFQ. It also outlines fragility methods for ANOVA,
            regression models, and other settings beyond simple two-arm 2×2 tables.
        </p>
        <p>
            The content below is synced automatically from the official
            <code class="inline">FRAGILITY_METRICS.md</code> file in the
            <a href="https://github.com/tomheston/fragility-metrics/blob/main/FRAGILITY_METRICS.md" target="_blank" rel="noopener">
                fragility-metrics GitHub repository
            </a>.
        </p>
    </div>


    <?php if ($errorMessage): ?>
        <div class="error">
            <?php echo htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8'); ?>
        </div>
    <?php else: ?>
        <div class="markdown">
            <?php echo $htmlContent; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>

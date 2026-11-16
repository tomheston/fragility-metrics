<?php
// includes/safety_net.php
// Last line of defense for the calculator pages: if PHP dies mid-request
// (time limit, memory limit, or any other fatal error), discard the partial
// output and show a readable page that points to the Python toolkit on
// Google Colab instead of a blank page or a server error.
//
// The calculators' own time budgets keep normal requests far below these
// limits; this only catches what slips past them. It cannot catch a
// Cloudflare proxy timeout (100 s), which is why the time limit set here
// stays well under that.

if (!defined('FM_COLAB_URL')) {
    define('FM_COLAB_URL', 'https://colab.research.google.com/drive/1x-MGyCif9h-mBQ1kfWotNDC-zYcSZnSH');
}

/**
 * Message block pointing to the Python toolkit on Colab. $lead is plain text.
 */
function fm_colab_notice_html($lead) {
    return '<div class="fm-colab-notice" style="background:#fff8e1; border-left:4px solid #f5a623; '
         . 'padding:12px 14px; border-radius:6px; margin:10px 0; color:#333;">'
         . '<strong>Note:</strong> ' . htmlspecialchars($lead, ENT_QUOTES, 'UTF-8') . ' '
         . 'For the full calculation, run the Python Fragility Metrics Toolkit (free, runs in your browser) on '
         . '<a href="' . FM_COLAB_URL . '" target="_blank" rel="noopener">Google Colab</a>.'
         . '</div>';
}

/**
 * Call before any computation. $pageTitle and $backHref describe the page
 * the fallback message is shown on.
 */
function fm_install_safety_net($pageTitle, $backHref) {
    // Some hosts disable set_time_limit; in PHP 8 calling a disabled
    // function throws even with @, so check first.
    if (function_exists('set_time_limit')) {
        @set_time_limit(60);
    }
    // Raw PHP error text never reaches the visitor; errors still go to the
    // server's error log.
    @ini_set('display_errors', '0');
    ob_start();
    register_shutdown_function(function () use ($pageTitle, $backHref) {
        $err = error_get_last();
        $fatal = array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);
        if ($err === null || !in_array($err['type'], $fatal, true)) {
            return;
        }
        // A memory-limit fatal leaves almost no room; give this page some.
        @ini_set('memory_limit', (string)(memory_get_usage(true) + 32 * 1048576));
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            http_response_code(200);
            header('Content-Type: text/html; charset=UTF-8');
        }
        $page_title = $pageTitle;
        include __DIR__ . '/header.php';
        echo '<div style="max-width:980px; margin:0 auto; padding:8px 20px 24px;">';
        echo '<h2>' . htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') . '</h2>';
        echo fm_colab_notice_html('This table is too large for the web calculator to finish within its time and memory limits.');
        echo '<p><a href="' . htmlspecialchars($backHref, ENT_QUOTES, 'UTF-8') . '">Back to the calculator</a></p>';
        echo '</div>';
        include __DIR__ . '/footer.php';
    });
}

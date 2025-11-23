<?php
/**
 * Plugin Name: W3C Validation Fixer VISIBLE DEBUG
 * Description: Debug visibile direttamente nell'HTML
 * Version: 3.4-DEBUG
 * Author: Auto-generated
 */

if (!defined('ABSPATH')) {
    exit;
}

// Variabile globale per tracciare lo stato
global $w3c_debug_info;
$w3c_debug_info = [
    'plugin_loaded' => true,
    'init_called' => false,
    'shutdown_called' => false,
    'buffer_started' => false,
    'buffer_captured' => false,
    'buffer_size' => 0,
    'final_output' => false
];

// 1. Hook INIT - avvia buffer
add_action('init', function() {
    global $w3c_debug_info;

    $w3c_debug_info['init_called'] = true;

    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        $w3c_debug_info['skipped'] = 'admin or ajax';
        return;
    }

    // Avvia buffer
    $started = ob_start();
    $w3c_debug_info['buffer_started'] = $started;
    $w3c_debug_info['buffer_level_after_start'] = ob_get_level();

}, 1);

// 2. Hook SHUTDOWN - cattura e processa
add_action('shutdown', function() {
    global $w3c_debug_info;

    $w3c_debug_info['shutdown_called'] = true;
    $w3c_debug_info['buffer_level_at_shutdown'] = ob_get_level();

    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }

    $levels = ob_get_level();
    $final_content = '';

    // Raccogli tutti i buffer
    while ($levels > 0) {
        $buffer = ob_get_contents();
        if ($buffer !== false && !empty($buffer)) {
            $final_content = $buffer;
            $w3c_debug_info['buffer_captured'] = true;
            $w3c_debug_info['buffer_size'] = strlen($buffer);
        }
        ob_end_clean();
        $levels--;
    }

    if (empty($final_content)) {
        // Stampa debug anche se fallisce
        echo "<!-- W3C DEBUG: Buffer vuoto! -->";
        echo "<!-- " . print_r($w3c_debug_info, true) . " -->";
        return;
    }

    // APPLICA CORREZIONI (solo le essenziali per test)

    // 1. Void elements
    $void_elements = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';
    $final_content = preg_replace(
        '/<(' . $void_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $final_content
    );

    // 2. SVG elements
    $svg_elements = 'path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g';
    $final_content = preg_replace(
        '/<(' . $svg_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $final_content
    );

    // 3. Speculation Rules
    $final_content = preg_replace(
        '/<script\s+type=["\']speculationrules(\+json)?["\']\s*>/i',
        '<script type="application/json" id="speculationrules">',
        $final_content
    );

    // 4. type="text/css"
    $final_content = preg_replace(
        '/<style\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i',
        '<style $1$2>',
        $final_content
    );

    // 5. type="text/javascript"
    $final_content = preg_replace(
        '/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i',
        '<script $1$2>',
        $final_content
    );

    // 6. pmdelayedscript
    $final_content = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']\s*/i',
        '$1 ',
        $final_content
    );

    $final_content = preg_replace(
        '/type\s*=\s*["\']pmdelayedscript["\']/i',
        '',
        $final_content
    );

    // 7. Pulizia spazi
    $final_content = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $final_content);
    $final_content = preg_replace('/\s+>/', '>', $final_content);

    $w3c_debug_info['final_output'] = true;
    $w3c_debug_info['final_size'] = strlen($final_content);

    // Aggiungi VISIBILE DEBUG COMMENT
    $debug_comment = "\n<!-- ========================================\n";
    $debug_comment .= "W3C FIXER VISIBLE DEBUG v3.4\n";
    $debug_comment .= "========================================\n";
    $debug_comment .= print_r($w3c_debug_info, true);
    $debug_comment .= "======================================== -->\n";

    $final_content = str_replace('</head>', $debug_comment . '</head>', $final_content);

    echo $final_content;

}, 999999);

// Filtri aggiuntivi
add_filter('wp_get_inline_script_tag', function($tag) {
    $tag = preg_replace(
        '/type=["\']speculationrules(\+json)?["\']/i',
        'type="application/json" id="speculationrules"',
        $tag
    );
    return $tag;
}, 999);

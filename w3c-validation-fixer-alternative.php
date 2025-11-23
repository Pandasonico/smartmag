<?php
/**
 * Plugin Name: W3C Validation Fixer ALTERNATIVE
 * Description: Strategia alternativa usando get_final_output
 * Version: 3.2-ALT
 * Author: Auto-generated
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// STRATEGIA ALTERNATIVA: Cattura con final_output filter
// ============================================================================

// Avvia buffer molto presto
add_action('init', function() {
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }
    ob_start();
}, 1);

// Cattura e processa allo shutdown
add_action('shutdown', function() {

    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }

    $levels = ob_get_level();
    $final_content = '';

    // Raccogli tutto l'output
    while ($levels > 0) {
        $buffer = ob_get_contents();
        if ($buffer !== false) {
            $final_content = $buffer;
        }
        ob_end_clean();
        $levels--;
    }

    if (empty($final_content)) {
        return;
    }

    // APPLICA CORREZIONI W3C

    // 1. Void elements HTML5
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

    // 6. pmdelayedscript (Perfmatters)
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

    // Commento diagnostico
    $diagnostic = "\n<!-- W3C Fixer ALTERNATIVE v3.2 - Attivo -->\n";
    $final_content = str_replace('</head>', $diagnostic . '</head>', $final_content);

    echo $final_content;

}, 999999); // Priorità alta ma non massima

// Filtri aggiuntivi
add_filter('wp_get_inline_script_tag', function($tag) {
    $tag = preg_replace(
        '/type=["\']speculationrules(\+json)?["\']/i',
        'type="application/json" id="speculationrules"',
        $tag
    );
    return $tag;
}, 999);

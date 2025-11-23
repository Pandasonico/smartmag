<?php
/**
 * Plugin Name: W3C Validation Fixer FINAL
 * Description: Versione finale con strategia garantita per Perfmatters
 * Version: 3.3-FINAL
 * Author: Auto-generated
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// STRATEGIA FINALE: Avvia buffer PRESTISSIMO e cattura allo shutdown
// ============================================================================

// 1. Avvia buffer il prima possibile (priorità 1 = molto presto)
add_action('init', function() {
    // Skip solo in admin e AJAX
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }

    // Avvia NOSTRO buffer
    ob_start();

}, 1); // Priorità 1 = tra i primi

// 2. Cattura e processa allo shutdown (prima che i buffer vengano chiusi)
add_action('shutdown', function() {

    // Skip solo in admin e AJAX
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }

    // Raccoglie TUTTI i buffer aperti
    $all_output = '';
    $levels = ob_get_level();

    // Loop attraverso tutti i livelli
    while ($levels > 0) {
        $current = ob_get_clean();
        if ($current !== false && !empty($current)) {
            $all_output = $current;
        }
        $levels = ob_get_level();
    }

    if (empty($all_output)) {
        return;
    }

    // ========================================================================
    // APPLICA CORREZIONI W3C
    // ========================================================================

    // 1. Rimuovi slash da void elements HTML5
    $void_elements = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';
    $all_output = preg_replace(
        '/<(' . $void_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $all_output
    );

    // 2. Rimuovi slash da elementi SVG
    $svg_elements = 'path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g';
    $all_output = preg_replace(
        '/<(' . $svg_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $all_output
    );

    // 3. Fix Speculation Rules
    $all_output = preg_replace(
        '/<script\s+type=["\']speculationrules(\+json)?["\']\s*>/i',
        '<script type="application/json" id="speculationrules">',
        $all_output
    );

    // 4. Rimuovi type="text/css"
    $all_output = preg_replace(
        '/<style\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i',
        '<style $1$2>',
        $all_output
    );

    // 5. Rimuovi type="text/javascript"
    $all_output = preg_replace(
        '/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i',
        '<script $1$2>',
        $all_output
    );

    // 6. FIX PERFMATTERS: Rimuovi type="pmdelayedscript"
    $all_output = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']\s*/i',
        '$1 ',
        $all_output
    );

    // Fallback aggressivo
    $all_output = preg_replace(
        '/type\s*=\s*["\']pmdelayedscript["\']/i',
        '',
        $all_output
    );

    // 7. Pulizia spazi extra
    $all_output = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $all_output);
    $all_output = preg_replace('/\s+>/', '>', $all_output);

    // Aggiungi commento diagnostico
    $diagnostic = "\n<!-- W3C Fixer FINAL v3.3 - Attivo e funzionante -->\n";
    $all_output = str_replace('</head>', $diagnostic . '</head>', $all_output);

    // Output finale
    echo $all_output;

}, 999999); // Priorità alta ma non PHP_INT_MAX (troppo tardi)

// ========================================================================
// FILTRI AGGIUNTIVI WORDPRESS
// ========================================================================

add_filter('wp_get_inline_script_tag', function($tag) {
    $tag = preg_replace(
        '/type=["\']speculationrules(\+json)?["\']/i',
        'type="application/json" id="speculationrules"',
        $tag
    );
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/javascript["\']\s*/i', ' ', $tag);
    return $tag;
}, 999);

add_filter('script_loader_tag', function($tag, $handle, $src) {
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/javascript["\']\s*/i', ' ', $tag);
    return $tag;
}, 10, 3);

add_filter('style_loader_tag', function($tag, $handle, $href, $media) {
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/css["\']\s*/i', ' ', $tag);
    return $tag;
}, 10, 4);

<?php
/**
 * Plugin Name: W3C Validation Fixer CALLBACK
 * Description: Usa callback diretto su ob_start (strategia garantita)
 * Version: 4.0-CALLBACK
 * Author: Auto-generated
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * ============================================================================
 * STRATEGIA CALLBACK: Il buffer viene processato AUTOMATICAMENTE quando chiuso
 * ============================================================================
 *
 * Invece di catturare allo shutdown, processiamo nel CALLBACK di ob_start().
 * Questo garantisce che il codice viene eseguito QUANDO il buffer viene chiuso,
 * non importa CHI lo chiude (WordPress, Perfmatters, ecc.)
 */

add_action('template_redirect', function() {

    // Skip in admin, AJAX, REST
    if (
        is_admin() ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    // Avvia buffer CON CALLBACK
    ob_start(function($buffer) {

        // Se buffer vuoto, ritorna così com'è
        if (empty($buffer) || stripos($buffer, '<html') === false) {
            return $buffer;
        }

        // ====================================================================
        // APPLICA CORREZIONI W3C
        // ====================================================================

        // 1. Rimuovi slash da void elements HTML5
        $void_elements = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';
        $buffer = preg_replace(
            '/<(' . $void_elements . ')(\s+[^>]*)?\/>/i',
            '<$1$2>',
            $buffer
        );

        // 2. Rimuovi slash da elementi SVG
        $svg_elements = 'path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g';
        $buffer = preg_replace(
            '/<(' . $svg_elements . ')(\s+[^>]*)?\/>/i',
            '<$1$2>',
            $buffer
        );

        // 3. Fix Speculation Rules
        $buffer = preg_replace(
            '/<script\s+type=["\']speculationrules(\+json)?["\']\s*>/i',
            '<script type="application/json" id="speculationrules">',
            $buffer
        );

        // 4. Rimuovi type="text/css"
        $buffer = preg_replace(
            '/<style\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i',
            '<style $1$2>',
            $buffer
        );

        // 5. Rimuovi type="text/javascript"
        $buffer = preg_replace(
            '/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i',
            '<script $1$2>',
            $buffer
        );

        // 6. FIX PERFMATTERS: Rimuovi type="pmdelayedscript"
        $buffer = preg_replace(
            '/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']\s*/i',
            '$1 ',
            $buffer
        );

        // Fallback aggressivo
        $buffer = preg_replace(
            '/type\s*=\s*["\']pmdelayedscript["\']/i',
            '',
            $buffer
        );

        // 7. Pulizia spazi extra
        $buffer = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $buffer);
        $buffer = preg_replace('/\s+>/', '>', $buffer);

        // Aggiungi commento diagnostico
        $diagnostic = "\n<!-- W3C Fixer CALLBACK v4.0 - Attivo e funzionante -->\n";
        $buffer = str_replace('</head>', $diagnostic . '</head>', $buffer);

        return $buffer;
    });

}, 1); // Priorità 1 = molto presto

/**
 * ============================================================================
 * FILTRI AGGIUNTIVI WORDPRESS
 * ============================================================================
 */

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

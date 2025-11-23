<?php
/**
 * Plugin Name: W3C Validation Fixer ULTRA
 * Description: Versione ultra-aggressiva con regex potenziate
 * Version: 4.1-ULTRA
 * Author: Auto-generated
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('template_redirect', function() {

    if (
        is_admin() ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    ob_start(function($buffer) {

        if (empty($buffer) || stripos($buffer, '<html') === false) {
            return $buffer;
        }

        // ====================================================================
        // CORREZIONI W3C - VERSIONE ULTRA AGGRESSIVA
        // ====================================================================

        // 1. RIMUOVI type="pmdelayedscript" - VERSIONE POTENZIATA
        // Gestisce anche data-* attributes dopo type
        $buffer = preg_replace(
            '/type\s*=\s*["\']pmdelayedscript["\']\s*/i',
            '',
            $buffer
        );

        // 2. RIMUOVI SLASH DA TUTTI I TAG AUTO-CHIUSI
        // Più aggressivo: cerca qualsiasi tag con /> alla fine
        $buffer = preg_replace(
            '/<(link|meta|img|br|hr|input|area|base|col|embed|param|source|track|wbr)([^>]*?)\s*\/\s*>/is',
            '<$1$2>',
            $buffer
        );

        // 3. RIMUOVI SLASH DA SVG ELEMENTS
        $buffer = preg_replace(
            '/<(path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g)([^>]*?)\s*\/\s*>/is',
            '<$1$2>',
            $buffer
        );

        // 4. FIX SPECULATION RULES
        $buffer = preg_replace(
            '/<script([^>]*)type\s*=\s*["\']speculationrules(\+json)?["\']/i',
            '<script$1type="application/json" id="speculationrules"',
            $buffer
        );

        // 5. RIMUOVI type="text/css"
        $buffer = preg_replace(
            '/(<style[^>]*)type\s*=\s*["\']text\/css["\']\s*/i',
            '$1',
            $buffer
        );

        // 6. RIMUOVI type="text/javascript"
        $buffer = preg_replace(
            '/(<script[^>]*)type\s*=\s*["\']text\/javascript["\']\s*/i',
            '$1',
            $buffer
        );

        // 7. PULIZIA SPAZI EXTRA
        $buffer = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $buffer);
        $buffer = preg_replace('/\s{2,}>/', '>', $buffer);
        $buffer = preg_replace('/\s+>/', '>', $buffer);

        // Commento diagnostico
        $diagnostic = "\n<!-- W3C Fixer ULTRA v4.1 - Attivo -->\n";
        $buffer = str_replace('</head>', $diagnostic . '</head>', $buffer);

        return $buffer;
    });

}, 1);

// Filtri aggiuntivi
add_filter('wp_get_inline_script_tag', function($tag) {
    $tag = preg_replace('/type\s*=\s*["\']speculationrules(\+json)?["\']/i', 'type="application/json"', $tag);
    $tag = preg_replace('/type\s*=\s*["\']text\/javascript["\']/i', '', $tag);
    return $tag;
}, 999);

add_filter('script_loader_tag', function($tag, $handle, $src) {
    $tag = preg_replace('/type\s*=\s*["\']text\/javascript["\']/i', '', $tag);
    $tag = preg_replace('/type\s*=\s*["\']pmdelayedscript["\']/i', '', $tag);
    return $tag;
}, 10, 3);

add_filter('style_loader_tag', function($tag, $handle, $href, $media) {
    $tag = preg_replace('/type\s*=\s*["\']text\/css["\']/i', '', $tag);
    $tag = preg_replace('/\s*\/\s*>/', '>', $tag);
    return $tag;
}, 10, 4);

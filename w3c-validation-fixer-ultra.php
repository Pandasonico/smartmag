<?php
/**
 * Plugin Name: W3C Validation Fixer ULTRA
 * Description: Strategia CALLBACK avanzata - cattura DOPO Perfmatters
 * Version: 4.3-ULTRA
 * Author: Auto-generated
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * STRATEGIA AVANZATA: Doppio buffering per catturare DOPO Perfmatters
 * Perfmatters aggiunge pmdelayedscript nel suo callback
 * Noi dobbiamo processare DOPO che Perfmatters ha finito
 */

// Hook 1: Avvia buffer presto
add_action('template_redirect', function() {
    if (
        is_admin() ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    ob_start();
}, 1);

// Hook 2: Shutdown con priorità MASSIMA - processa DOPO Perfmatters
add_action('shutdown', function() {
    if (
        is_admin() ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    // Cattura TUTTO il buffer (incluse modifiche di Perfmatters)
    $levels = ob_get_level();
    $buffer = '';

    for ($i = 0; $i < $levels; $i++) {
        $current = ob_get_contents();
        if ($current !== false && !empty($current)) {
            $buffer = $current;
        }
        ob_end_clean();
    }

    if (empty($buffer) || stripos($buffer, '<html') === false) {
        echo $buffer;
        return;
    }

    // ====================================================================
    // APPLICA CORREZIONI W3C - DOPO CHE PERFMATTERS HA MODIFICATO L'HTML
    // ====================================================================

    // 1. RIMUOVI type="pmdelayedscript" - ULTRA AGGRESSIVO
    $buffer = str_replace('type="pmdelayedscript"', '', $buffer);
    $buffer = str_replace("type='pmdelayedscript'", '', $buffer);
    $buffer = str_replace(' type="pmdelayedscript" ', ' ', $buffer);
    $buffer = str_replace(" type='pmdelayedscript' ", ' ', $buffer);
    $buffer = preg_replace('/\s*type\s*=\s*["\']pmdelayedscript["\']\s*/i', ' ', $buffer);
    $buffer = preg_replace('/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']([^>]*>)/i', '$1$2', $buffer);

    // 2. RIMUOVI SLASH SOLO DA HTML5 VOID ELEMENTS
    $buffer = preg_replace(
        '/<(link|meta|img|br|hr|input|area|base|col|embed|param|source|track|wbr)([^>]*?)\s*\/\s*>/is',
        '<$1$2>',
        $buffer
    );

    // 3. FIX SPECULATION RULES
    $buffer = preg_replace(
        '/<script([^>]*)type\s*=\s*["\']speculationrules(\+json)?["\']/i',
        '<script$1type="application/json" id="speculationrules"',
        $buffer
    );

    // 4. RIMUOVI type="text/css"
    $buffer = preg_replace(
        '/(<style[^>]*)type\s*=\s*["\']text\/css["\']\s*/i',
        '$1',
        $buffer
    );

    // 5. RIMUOVI type="text/javascript"
    $buffer = preg_replace(
        '/(<script[^>]*)type\s*=\s*["\']text\/javascript["\']\s*/i',
        '$1',
        $buffer
    );

    // 6. CONVERTI SECTION IN DIV
    $buffer = preg_replace('/<section(\s|>)/i', '<div$1', $buffer);
    $buffer = str_replace('</section>', '</div>', $buffer);
    $buffer = str_replace('</SECTION>', '</div>', $buffer);

    // 7. PULIZIA SPAZI EXTRA
    $buffer = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $buffer);
    $buffer = preg_replace('/\s{2,}>/', '>', $buffer);
    $buffer = preg_replace('/\s+>/', '>', $buffer);

    // Commento diagnostico
    $diagnostic = "\n<!-- W3C Fixer ULTRA v4.3 - SHUTDOWN strategy (post-Perfmatters) -->\n";
    $buffer = str_replace('</head>', $diagnostic . '</head>', $buffer);

    echo $buffer;

}, PHP_INT_MAX); // Priorità MASSIMA = esegue per ultimo

// Filtri aggiuntivi
add_filter('wp_get_inline_script_tag', function($tag) {
    $tag = preg_replace('/type\s*=\s*["\']speculationrules(\+json)?["\']/i', 'type="application/json"', $tag);
    $tag = preg_replace('/type\s*=\s*["\']text\/javascript["\']/i', '', $tag);
    $tag = str_replace('type="pmdelayedscript"', '', $tag);
    return $tag;
}, 999);

add_filter('script_loader_tag', function($tag, $handle, $src) {
    $tag = preg_replace('/type\s*=\s*["\']text\/javascript["\']/i', '', $tag);
    $tag = preg_replace('/type\s*=\s*["\']pmdelayedscript["\']/i', '', $tag);
    $tag = str_replace('type="pmdelayedscript"', '', $tag);
    return $tag;
}, 10, 3);

add_filter('style_loader_tag', function($tag, $handle, $href, $media) {
    $tag = preg_replace('/type\s*=\s*["\']text\/css["\']/i', '', $tag);
    $tag = preg_replace('/\s*\/\s*>/', '>', $tag);
    return $tag;
}, 10, 4);

<?php
/**
 * Plugin Name: W3C Validation Fixer DEBUG
 * Description: Versione debug per capire il problema
 * Version: 3.1-DEBUG
 * Author: Auto-generated
 */

if (!defined('ABSPATH')) {
    exit;
}

// ============================================================================
// VERSIONE DEBUG - Controlla cosa sta succedendo
// ============================================================================

// Log ogni fase
function w3c_debug_log($message) {
    error_log('[W3C FIXER DEBUG] ' . $message);
}

w3c_debug_log('Plugin caricato');

// Prova MULTIPLE strategie contemporaneamente
add_action('shutdown', function() {

    w3c_debug_log('Shutdown hook chiamato (priorità PHP_INT_MAX)');
    w3c_debug_log('Buffer level: ' . ob_get_level());

    // Skip checks
    if (is_admin()) {
        w3c_debug_log('SKIP: is_admin = true');
        return;
    }

    if (defined('DOING_AJAX') && DOING_AJAX) {
        w3c_debug_log('SKIP: DOING_AJAX = true');
        return;
    }

    if (ob_get_level() === 0) {
        w3c_debug_log('ERRORE: Nessun buffer attivo! ob_get_level() = 0');
        return;
    }

    // Cattura il buffer
    $content = ob_get_clean();

    if (empty($content)) {
        w3c_debug_log('ERRORE: Buffer vuoto!');
        return;
    }

    w3c_debug_log('Buffer catturato: ' . strlen($content) . ' bytes');

    // Conta prima delle correzioni
    $before_pm = substr_count($content, 'pmdelayedscript');
    $before_spec = substr_count($content, 'speculationrules');
    $before_slash = substr_count($content, '/>');

    w3c_debug_log("PRIMA - pmdelayedscript: $before_pm, speculationrules: $before_spec, slashes: $before_slash");

    // Trova esempio di pmdelayedscript
    if ($before_pm > 0) {
        if (preg_match('/<script[^>]*pmdelayedscript[^>]*>/i', $content, $match)) {
            w3c_debug_log('Esempio pmdelayedscript trovato: ' . substr($match[0], 0, 200));
        }
    }

    // APPLICA CORREZIONI

    // 1. Void elements
    $void_elements = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';
    $content = preg_replace(
        '/<(' . $void_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $content
    );

    // 2. SVG elements
    $svg_elements = 'path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g';
    $content = preg_replace(
        '/<(' . $svg_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $content
    );

    // 3. Speculation Rules
    $content = preg_replace(
        '/<script\s+type=["\']speculationrules(\+json)?["\']\s*>/i',
        '<script type="application/json" id="speculationrules">',
        $content
    );

    // 4. type="text/css"
    $content = preg_replace(
        '/<style\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i',
        '<style $1$2>',
        $content
    );

    // 5. type="text/javascript"
    $content = preg_replace(
        '/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i',
        '<script $1$2>',
        $content
    );

    // 6. pmdelayedscript - VERSIONE AGGRESSIVA
    $content = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']\s*/i',
        '$1 ',
        $content
    );

    $content = preg_replace(
        '/type\s*=\s*["\']pmdelayedscript["\']/i',
        '',
        $content
    );

    // 7. Pulizia spazi
    $content = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $content);
    $content = preg_replace('/\s+>/', '>', $content);

    // Conta dopo le correzioni
    $after_pm = substr_count($content, 'pmdelayedscript');
    $after_spec = substr_count($content, 'speculationrules');
    $after_slash = substr_count($content, '/>');

    w3c_debug_log("DOPO - pmdelayedscript: $after_pm, speculationrules: $after_spec, slashes: $after_slash");

    // Aggiungi commento diagnostico VISIBILE
    $diagnostic = sprintf(
        "\n<!-- W3C FIXER DEBUG | pm:%d→%d | spec:%d→%d | slash:%d→%d | buffer:%d bytes | level:%d -->\n",
        $before_pm, $after_pm,
        $before_spec, $after_spec,
        $before_slash, $after_slash,
        strlen($content),
        ob_get_level()
    );

    $content = str_replace('</head>', $diagnostic . '</head>', $content);

    w3c_debug_log('Output finale inviato');

    echo $content;

}, PHP_INT_MAX);

// Filtri aggiuntivi
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

w3c_debug_log('Tutti gli hook registrati');

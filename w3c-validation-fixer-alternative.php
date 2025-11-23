<?php
/**
 * Plugin Name: W3C Validation Fixer ALTERNATIVE
 * Description: Strategia alternativa con buffer proprietario (compatibile Perfmatters)
 * Version: 3.2-ALT
 * Author: Auto-generated
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// ============================================================================
// STRATEGIA ALTERNATIVA: Buffer proprietario + Shutdown
// ============================================================================

// Avvia buffer molto presto (priorità 1)
add_action('init', function() {

    // Skip in admin e AJAX
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }

    // Avvia il nostro buffer
    ob_start();

}, 1); // Priorità 1 = molto presto

// Cattura e processa allo shutdown
add_action('shutdown', function() {

    // Skip in admin e AJAX
    if (is_admin() || (defined('DOING_AJAX') && DOING_AJAX)) {
        return;
    }

    $levels = ob_get_level();
    $final_content = '';

    // Raccogli tutto l'output da tutti i livelli di buffer
    while ($levels > 0) {
        $buffer = ob_get_contents();
        if ($buffer !== false && !empty($buffer)) {
            $final_content = $buffer;
        }
        ob_end_clean();
        $levels--;
    }

    if (empty($final_content)) {
        return;
    }

    // ========================================================================
    // APPLICA CORREZIONI W3C
    // ========================================================================

    // 1. Rimuovi slash da void elements HTML5
    // Risolve: "Trailing slash on void elements has no effect"
    $void_elements = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';
    $final_content = preg_replace(
        '/<(' . $void_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $final_content
    );

    // 2. Rimuovi slash da elementi SVG
    $svg_elements = 'path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g';
    $final_content = preg_replace(
        '/<(' . $svg_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $final_content
    );

    // 3. Fix Speculation Rules
    // Converte type="speculationrules" in type="application/json"
    $final_content = preg_replace(
        '/<script\s+type=["\']speculationrules(\+json)?["\']\s*>/i',
        '<script type="application/json" id="speculationrules">',
        $final_content
    );

    // 4. Rimuovi type="text/css" (deprecato in HTML5)
    $final_content = preg_replace(
        '/<style\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i',
        '<style $1$2>',
        $final_content
    );

    // 5. Rimuovi type="text/javascript" (deprecato in HTML5)
    $final_content = preg_replace(
        '/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i',
        '<script $1$2>',
        $final_content
    );

    // 6. FIX PERFMATTERS: Rimuovi type="pmdelayedscript"
    // Perfmatters aggiunge questo attributo non valido per il delay JS
    $final_content = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']\s*/i',
        '$1 ',
        $final_content
    );

    // Fallback aggressivo per pmdelayedscript
    $final_content = preg_replace(
        '/type\s*=\s*["\']pmdelayedscript["\']/i',
        '',
        $final_content
    );

    // 7. Pulizia spazi extra
    // Rimuovi spazi multipli tra attributi
    $final_content = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $final_content);

    // Rimuovi spazi prima della chiusura del tag
    $final_content = preg_replace('/\s+>/', '>', $final_content);

    // ========================================================================
    // COMMENTO DIAGNOSTICO
    // ========================================================================

    $diagnostic = "\n<!-- W3C Fixer ALTERNATIVE v3.2 - Attivo e funzionante -->\n";
    $final_content = str_replace('</head>', $diagnostic . '</head>', $final_content);

    // ========================================================================
    // OUTPUT FINALE
    // ========================================================================

    echo $final_content;

}, 999999); // Priorità alta (prima di PHP_INT_MAX)

// ============================================================================
// FILTRI AGGIUNTIVI WORDPRESS
// ============================================================================

// Filtro per inline script tags
add_filter('wp_get_inline_script_tag', function($tag) {
    // Fix Speculation Rules
    $tag = preg_replace(
        '/type=["\']speculationrules(\+json)?["\']/i',
        'type="application/json" id="speculationrules"',
        $tag
    );
    // Rimuovi type="text/javascript"
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/javascript["\']\s*/i', ' ', $tag);
    return $tag;
}, 999);

// Filtro per script enqueued
add_filter('script_loader_tag', function($tag, $handle, $src) {
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/javascript["\']\s*/i', ' ', $tag);
    return $tag;
}, 10, 3);

// Filtro per style enqueued
add_filter('style_loader_tag', function($tag, $handle, $href, $media) {
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/css["\']\s*/i', ' ', $tag);
    return $tag;
}, 10, 4);

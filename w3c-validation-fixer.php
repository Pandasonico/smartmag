<?php
/**
 * Plugin Name: W3C Validation Fixer
 * Description: Corregge automaticamente output HTML per validazione W3C (compatibile Perfmatters)
 * Version: 3.1
 * Author: Auto-generated
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * ========================================================================
 * STRATEGIA DI COMPATIBILITÀ PERFMATTERS
 * ========================================================================
 *
 * Perfmatters usa shutdown hook con priorità ~10000
 * Noi usiamo PHP_INT_MAX per eseguire DOPO Perfmatters
 *
 * NON usiamo template_redirect per non interferire con il buffer di Perfmatters
 * Prendiamo solo l'ULTIMO buffer dopo che tutti i plugin hanno finito
 * ========================================================================
 */

add_action('shutdown', function() {

    // Skip in admin, AJAX, REST API
    if (
        is_admin() ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (defined('REST_REQUEST') && REST_REQUEST) ||
        (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST)
    ) {
        return;
    }

    // Verifica che ci sia un buffer attivo
    if (ob_get_level() === 0) {
        return;
    }

    // Prendi SOLO l'ultimo buffer (quello finale dopo Perfmatters)
    $content = ob_get_clean();

    if (empty($content)) {
        return;
    }

    // ========================================================================
    // CORREZIONI W3C
    // ========================================================================

    // 1. RIMOZIONE SLASH DA VOID ELEMENTS HTML5
    // Risolve: "Trailing slash on void elements has no effect"
    $void_elements = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';
    $content = preg_replace(
        '/<(' . $void_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $content
    );

    // 2. RIMOZIONE SLASH DA ELEMENTI SVG
    $svg_elements = 'path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g';
    $content = preg_replace(
        '/<(' . $svg_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $content
    );

    // 3. CORREZIONE SPECULATION RULES
    // Converte type="speculationrules" in type="application/json"
    $content = preg_replace(
        '/<script\s+type=["\']speculationrules(\+json)?["\']\s*>/i',
        '<script type="application/json" id="speculationrules">',
        $content
    );

    // 4. RIMOZIONE TYPE="TEXT/CSS" (deprecato in HTML5)
    $content = preg_replace(
        '/<style\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i',
        '<style $1$2>',
        $content
    );

    // 5. RIMOZIONE TYPE="TEXT/JAVASCRIPT" (deprecato in HTML5)
    $content = preg_replace(
        '/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i',
        '<script $1$2>',
        $content
    );

    // 6. FIX PERFMATTERS: RIMOZIONE TYPE="PMDELAYEDSCRIPT"
    // Perfmatters aggiunge questo attributo non valido per il delay JS
    $content = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']\s*/i',
        '$1 ',
        $content
    );

    // Fallback: rimuovi anche senza catturare tutto il tag
    $content = preg_replace(
        '/type\s*=\s*["\']pmdelayedscript["\']/i',
        '',
        $content
    );

    // 7. PULIZIA SPAZI EXTRA
    // Rimuovi spazi multipli tra attributi
    $content = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $content);

    // Rimuovi spazi prima della chiusura del tag
    $content = preg_replace('/\s+>/', '>', $content);

    // ========================================================================
    // OUTPUT FINALE
    // ========================================================================

    echo $content;

}, PHP_INT_MAX); // Priorità massima = esegue per ULTIMO, dopo Perfmatters

/**
 * ========================================================================
 * FILTRI AGGIUNTIVI WORDPRESS
 * ========================================================================
 */

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

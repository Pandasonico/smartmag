<?php
/**
 * Plugin Name: W3C Validation Fixer
 * Description: Corregge output HTML per validazione W3C
 * Version: 1.1
 * Author: Auto-generated
 */

// 🚀 AVVIA IL BUFFER IL PRIMA POSSIBILE
add_action('init', function() {

    // 🛑 ESCI: Controlli Elementor e Admin
    if (
        isset($_GET['elementor-preview']) ||
        isset($_GET['elementor_library']) ||
        (isset($_GET['action']) && $_GET['action'] === 'elementor') ||
        is_admin()
    ) {
        return;
    }

    // Avvia output buffering con callback
    ob_start(function($buffer) {

        error_log('W3C FILTER: Processing buffer, length=' . strlen($buffer));

        if (empty($buffer)) {
            return $buffer;
        }

        // 🔧 APPLICA TUTTE LE CORREZIONI

        // 1. Correzione pmdelayedscript
        $before_pm = substr_count($buffer, 'pmdelayedscript');
        $buffer = str_replace('type="pmdelayedscript"', '', $buffer);
        $buffer = str_replace("type='pmdelayedscript'", '', $buffer);
        $after_pm = substr_count($buffer, 'pmdelayedscript');

        // 2. Correzione speculationrules
        $before_spec = substr_count($buffer, 'type="speculationrules"');
        $buffer = str_replace('type="speculationrules"', 'type="application/json"', $buffer);
        $after_spec = substr_count($buffer, 'type="speculationrules"');

        // 3. Rimozione slash finale dai void elements HTML5
        $before_slash = substr_count($buffer, '/>');

        // HTML5 void elements
        $buffer = preg_replace(
            '/<(area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)(\s[^>]*?)?\s*\/>/i',
            '<$1$2>',
            $buffer
        );

        // SVG elements comuni (path, circle, rect, line, polyline, polygon, ellipse, use, stop)
        $buffer = preg_replace(
            '/<(path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate)(\s[^>]*?)?\s*\/>/i',
            '<$1$2>',
            $buffer
        );

        $after_slash = substr_count($buffer, '/>');

        // 4. Rimozione type="text/css" e type="text/javascript"
        $buffer = preg_replace('/<style\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i', '<style $1$2>', $buffer);
        $buffer = preg_replace('/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i', '<script $1$2>', $buffer);

        // 5. Fix attributi charset duplicati
        $buffer = preg_replace('/<meta\s+charset=["\'][^"\']*["\']\s+charset=["\'][^"\']*["\']/i', '<meta charset="UTF-8"', $buffer);

        // 🔍 AGGIUNGI COMMENTO DIAGNOSTICO
        $diagnostic = sprintf(
            "\n<!-- ✅ W3C FILTER EXECUTED | pmdelayedscript: %d→%d | speculationrules: %d→%d | slashes: %d→%d -->\n",
            $before_pm, $after_pm,
            $before_spec, $after_spec,
            $before_slash, $after_slash
        );

        $buffer = str_replace('</head>', $diagnostic . '</head>', $buffer);

        error_log("W3C FILTER: pmdelayedscript $before_pm → $after_pm, speculationrules $before_spec → $after_spec, slashes $before_slash → $after_slash");

        return $buffer;
    });

}, 1); // Priorità 1 = molto presto

// Filtro aggiuntivo per Speculation Rules
add_filter('wp_get_inline_script_tag', function($tag) {
    $tag = str_replace('type="speculationrules"', 'type="application/json"', $tag);
    return $tag;
}, 999);

// Filtro per script inline
add_filter('script_loader_tag', function($tag, $handle, $src) {
    // Rimuovi type="text/javascript" dagli script
    $tag = preg_replace('/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i', '<script $1$2>', $tag);
    return $tag;
}, 10, 3);

// Filtro per style inline
add_filter('style_loader_tag', function($tag, $handle, $href, $media) {
    // Rimuovi type="text/css" dagli style
    $tag = preg_replace('/<link\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i', '<link $1$2>', $tag);
    return $tag;
}, 10, 4);

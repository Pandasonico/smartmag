<?php
/**
 * Plugin Name: W3C Validation Fixer
 * Description: Corregge output HTML per validazione W3C (DEBUG MODE)
 * Version: 2.0-DEBUG
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

        // 1. Correzione pmdelayedscript (PIÙ AGGRESSIVA)
        $before_pm = substr_count($buffer, 'pmdelayedscript');

        // Trova e logga esempi
        if (preg_match('/<script[^>]*pmdelayedscript[^>]*>/i', $buffer, $matches)) {
            error_log('W3C FILTER: Found pmdelayedscript example: ' . substr($matches[0], 0, 200));
        }

        // Rimuovi type="pmdelayedscript" e type='pmdelayedscript' con spazi opzionali
        $buffer = preg_replace('/\s*type\s*=\s*["\']pmdelayedscript["\']\s*/i', ' ', $buffer);

        $after_pm = substr_count($buffer, 'pmdelayedscript');

        // 2. Correzione speculationrules
        $before_spec = substr_count($buffer, 'speculationrules');
        $buffer = preg_replace('/type\s*=\s*["\']speculationrules["\']/i', 'type="application/json"', $buffer);
        $after_spec = substr_count($buffer, 'speculationrules');

        // 3. Rimozione slash finale (PIÙ AGGRESSIVA CON MULTILINE)
        $before_slash = substr_count($buffer, '/>');

        // Trova e logga primi 3 esempi
        if (preg_match_all('/<(img|meta|link|br|input)[^>]*\/>/i', $buffer, $matches, PREG_SET_ORDER, 0, 0)) {
            $count = min(3, count($matches));
            for ($i = 0; $i < $count; $i++) {
                error_log('W3C FILTER: Slash example ' . ($i+1) . ': ' . substr($matches[$i][0], 0, 150));
            }
        }

        // Rimozione slash con flag MULTILINE e DOTALL
        // HTML5 void elements
        $buffer = preg_replace(
            '/<(area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr)\s*([^>]*?)\s*\/\s*>/is',
            '<$1 $2>',
            $buffer
        );

        // SVG elements
        $buffer = preg_replace(
            '/<(path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g)\s*([^>]*?)\s*\/\s*>/is',
            '<$1 $2>',
            $buffer
        );

        $after_slash = substr_count($buffer, '/>');

        // 4. Rimozione type="text/css" e type="text/javascript"
        $buffer = preg_replace('/\s*type\s*=\s*["\']text\/(css|javascript)["\']\s*/i', ' ', $buffer);

        // 5. Fix attributi charset duplicati
        $buffer = preg_replace('/<meta\s+charset=["\'][^"\']*["\']\s+charset=["\'][^"\']*["\']/i', '<meta charset="UTF-8"', $buffer);

        // 6. Pulizia spazi doppi negli attributi
        $buffer = preg_replace('/<([a-z][a-z0-9]*)\s+\s+/i', '<$1 ', $buffer);
        $buffer = preg_replace('/\s+>/i', '>', $buffer);

        // 🔍 AGGIUNGI COMMENTO DIAGNOSTICO
        $diagnostic = sprintf(
            "\n<!-- ✅ W3C FILTER v2.0 | pmdelayedscript: %d→%d | speculationrules: %d→%d | slashes: %d→%d -->\n",
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

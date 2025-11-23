<?php
/**
 * Plugin Name: W3C Validation Fixer
 * Description: Corregge automaticamente output HTML per validazione W3C
 * Version: 2.1
 * Author: Auto-generated
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Avvia output buffering all'init con priorità alta
 */
add_action('init', 'w3c_fixer_start_buffer', 1);

function w3c_fixer_start_buffer() {

    // Skip in admin, Elementor preview e AJAX
    if (
        is_admin() ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        isset($_GET['elementor-preview']) ||
        isset($_GET['elementor_library']) ||
        (isset($_GET['action']) && $_GET['action'] === 'elementor')
    ) {
        return;
    }

    ob_start('w3c_fixer_process_buffer');
}

/**
 * Processa il buffer HTML e applica tutte le correzioni W3C
 */
function w3c_fixer_process_buffer($buffer) {

    // Verifica che il buffer contenga HTML
    if (empty($buffer) || stripos($buffer, '<html') === false) {
        return $buffer;
    }

    // Statistiche per diagnostica
    $stats = [
        'pmdelayedscript' => ['before' => 0, 'after' => 0],
        'speculationrules' => ['before' => 0, 'after' => 0],
        'slashes' => ['before' => 0, 'after' => 0],
    ];

    // 1. CORREZIONE PMDELAYEDSCRIPT
    $stats['pmdelayedscript']['before'] = substr_count($buffer, 'pmdelayedscript');

    // Rimuovi completamente l'attributo type="pmdelayedscript"
    $buffer = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']\s*/i',
        '$1 ',
        $buffer
    );

    $stats['pmdelayedscript']['after'] = substr_count($buffer, 'pmdelayedscript');


    // 2. CORREZIONE SPECULATIONRULES
    $stats['speculationrules']['before'] = substr_count($buffer, 'type="speculationrules"') +
                                            substr_count($buffer, "type='speculationrules'");

    // Sostituisci type="speculationrules" con type="application/json"
    $buffer = str_replace('type="speculationrules"', 'type="application/json"', $buffer);
    $buffer = str_replace("type='speculationrules'", "type='application/json'", $buffer);

    $stats['speculationrules']['after'] = substr_count($buffer, 'type="speculationrules"') +
                                           substr_count($buffer, "type='speculationrules'");


    // 3. RIMOZIONE SLASH AUTO-CHIUSURA
    $stats['slashes']['before'] = substr_count($buffer, '/>');

    // Void elements HTML5 (elementi che non devono mai avere tag di chiusura)
    $void_elements = 'area|base|br|col|embed|hr|img|input|link|meta|param|source|track|wbr';

    $buffer = preg_replace(
        '/<(' . $void_elements . ')(\s+[^>]*)?\s*\/\s*>/i',
        '<$1$2>',
        $buffer
    );

    // SVG elements comuni
    $svg_elements = 'path|circle|rect|line|polyline|polygon|ellipse|use|stop|animateTransform|animate|image|g';

    $buffer = preg_replace(
        '/<(' . $svg_elements . ')(\s+[^>]*)?\s*\/\s*>/i',
        '<$1$2>',
        $buffer
    );

    $stats['slashes']['after'] = substr_count($buffer, '/>');


    // 4. RIMOZIONE TYPE DA STYLE E SCRIPT
    // In HTML5 type="text/css" e type="text/javascript" sono ridondanti
    $buffer = preg_replace(
        '/(<style[^>]*)\s+type\s*=\s*["\']text\/css["\']\s*/i',
        '$1 ',
        $buffer
    );

    $buffer = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']text\/javascript["\']\s*/i',
        '$1 ',
        $buffer
    );


    // 5. PULIZIA SPAZI EXTRA
    // Rimuovi spazi multipli tra attributi
    $buffer = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $buffer);

    // Rimuovi spazi prima della chiusura del tag
    $buffer = preg_replace('/\s+>/', '>', $buffer);


    // 6. AGGIUNGI COMMENTO DIAGNOSTICO
    $diagnostic = sprintf(
        "\n<!-- W3C Fixer v2.1 | pmdelayedscript:%d→%d | speculationrules:%d→%d | slashes:%d→%d -->\n",
        $stats['pmdelayedscript']['before'],
        $stats['pmdelayedscript']['after'],
        $stats['speculationrules']['before'],
        $stats['speculationrules']['after'],
        $stats['slashes']['before'],
        $stats['slashes']['after']
    );

    $buffer = str_replace('</head>', $diagnostic . '</head>', $buffer);


    // Log per debug (se WP_DEBUG è attivo)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log(sprintf(
            'W3C Fixer: pmdelayedscript %d→%d | speculationrules %d→%d | slashes %d→%d',
            $stats['pmdelayedscript']['before'],
            $stats['pmdelayedscript']['after'],
            $stats['speculationrules']['before'],
            $stats['speculationrules']['after'],
            $stats['slashes']['before'],
            $stats['slashes']['after']
        ));
    }

    return $buffer;
}

/**
 * Filtri aggiuntivi per script e style caricati dinamicamente
 */

// Filtro per inline script tags
add_filter('wp_get_inline_script_tag', 'w3c_fixer_inline_script', 999);
function w3c_fixer_inline_script($tag) {
    $tag = str_replace('type="speculationrules"', 'type="application/json"', $tag);
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/javascript["\']\s*/i', ' ', $tag);
    return $tag;
}

// Filtro per script enqueued
add_filter('script_loader_tag', 'w3c_fixer_script_tag', 10, 3);
function w3c_fixer_script_tag($tag, $handle, $src) {
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/javascript["\']\s*/i', ' ', $tag);
    return $tag;
}

// Filtro per style enqueued
add_filter('style_loader_tag', 'w3c_fixer_style_tag', 10, 4);
function w3c_fixer_style_tag($tag, $handle, $href, $media) {
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/css["\']\s*/i', ' ', $tag);
    return $tag;
}

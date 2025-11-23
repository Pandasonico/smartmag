<?php
/**
 * Plugin Name: W3C Validation Fixer (Perfmatters Compatible)
 * Description: Corregge output HTML per validazione W3C DOPO Perfmatters
 * Version: 3.1
 * Author: Auto-generated
 */

if (!defined('ABSPATH')) {
    exit;
}

// --- FIX: Corregge Speculation Rules, attributi e tag vuoti per validazione HTML ---

// IMPORTANTE: NON usare template_redirect, lascia che Perfmatters gestisca il suo buffer

// Usa shutdown con priorità ALTISSIMA per elaborare DOPO Perfmatters
add_action('shutdown', function() {

    // Verifica che ci sia almeno un buffer attivo
    if (ob_get_level() === 0) {
        return;
    }

    // Prendi SOLO l'ultimo buffer (quello finale dopo Perfmatters)
    $content = ob_get_clean();

    if (empty($content)) {
        return;
    }

    ## 🐛 Correzioni per Validazione W3C

    // 1. Rimuovi lo slash finale dai tag vuoti auto-chiusi in HTML5.
    $void_elements = implode('|', ['area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'link', 'meta', 'param', 'source', 'track', 'wbr']);
    $content = preg_replace(
        '/<(' . $void_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $content
    );

    // 2. Rimuovi anche slash da elementi SVG comuni
    $svg_elements = implode('|', ['path', 'circle', 'rect', 'line', 'polyline', 'polygon', 'ellipse', 'use', 'stop', 'animateTransform', 'animate', 'image', 'g']);
    $content = preg_replace(
        '/<(' . $svg_elements . ')(\s+[^>]*)?\/>/i',
        '<$1$2>',
        $content
    );

    ## 🛠️ Correzioni Speculation Rules

    // Correggi Speculation Rules in tutte le varianti
    $content = preg_replace(
        '/<script\s+type=["\']speculationrules(\+json)?["\']\s*>/i',
        '<script type="application/json" id="speculationrules">',
        $content
    );

    ## 🧹 Rimozione Attributi Deprecati/Inutili

    // Rimuovi type="text/css" dai tag <style> (non necessario in HTML5)
    $content = preg_replace('/<style\s+([^>]*)type=["\']text\/css["\']\s*([^>]*)>/i', '<style $1$2>', $content);

    // Rimuovi type="text/javascript" dai tag <script> (non necessario in HTML5)
    $content = preg_replace('/<script\s+([^>]*)type=["\']text\/javascript["\']\s*([^>]*)>/i', '<script $1$2>', $content);

    ## 🎯 FIX PERFMATTERS: Rimuovi type="pmdelayedscript" (aggiunto da Perfmatters)

    // Rimuovi completamente l'attributo type="pmdelayedscript"
    $content = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']pmdelayedscript["\']\s*/i',
        '$1 ',
        $content
    );

    // Fallback: rimuovi anche senza catturare il tag script
    $content = preg_replace(
        '/type\s*=\s*["\']pmdelayedscript["\']/i',
        '',
        $content
    );

    echo $content;

}, PHP_INT_MAX); // PRIORITÀ MASSIMA = esegue per ultimo, DOPO Perfmatters

// Filtro aggiuntivo per Speculation Rules
add_filter('wp_get_inline_script_tag', function($tag) {
    // Sostituisci entrambe le varianti
    $tag = preg_replace(
        '/type=["\']speculationrules(\+json)?["\']/i',
        'type="application/json" id="speculationrules"',
        $tag
    );
    return $tag;
}, 999);

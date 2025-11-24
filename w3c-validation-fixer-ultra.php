<?php
/**
 * Plugin Name: W3C Validation Fixer ULTRA
 * Description: Rimuove trailing slash e corregge errori W3C - Versione potenziata per Perfmatters
 * Version: 6.0-ULTRA-SHUTDOWN
 * Author: Auto-generated
 * License: GPL v2 or later
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Output buffer callback per rimuovere trailing slashes e correggere errori W3C
 */
function w3c_fixer_ultra_callback($buffer) {
    // Se il buffer è vuoto o non è HTML, ritorna invariato
    if (empty($buffer) || stripos($buffer, '<html') === false) {
        return $buffer;
    }

    // ====================================================================
    // CORREZIONI W3C - VERSIONE ULTRA AGGRESSIVA v5.0
    // ====================================================================

    // 1. RIMUOVI type="pmdelayedscript" (Perfmatters)
    $buffer = preg_replace(
        '/\s+type\s*=\s*["\']pmdelayedscript["\']/i',
        '',
        $buffer
    );

    // 2. RIMUOVI TRAILING SLASH DA VOID ELEMENTS - MULTI-PASS
    // Primo passaggio: pattern standard con spazi
    $void_elements = 'link|meta|img|br|hr|input|area|base|col|embed|param|source|track|wbr';

    // Pattern 1: slash con spazi prima (più comune)
    $buffer = preg_replace(
        '/<(' . $void_elements . ')(\s[^>]*?)\s+\/\s*>/is',
        '<$1$2>',
        $buffer
    );

    // Pattern 2: slash senza spazi prima
    $buffer = preg_replace(
        '/<(' . $void_elements . ')(\s[^>]*?)\/\s*>/is',
        '<$1$2>',
        $buffer
    );

    // Pattern 3: solo slash (edge case)
    $buffer = preg_replace(
        '/<(' . $void_elements . ')\s*\/\s*>/is',
        '<$1>',
        $buffer
    );

    // Pattern 4: attributi con spazi multipli prima dello slash
    $buffer = preg_replace(
        '/<(' . $void_elements . ')([^>]*?)\s{2,}\/\s*>/is',
        '<$1$2>',
        $buffer
    );

    // 3. SECONDO PASSAGGIO - Catch specifico per link e img (più aggressivo)
    // Questo gestisce casi specifici di Perfmatters
    $buffer = preg_replace(
        '/<link([^>]+?)\/>/is',
        '<link$1>',
        $buffer
    );

    $buffer = preg_replace(
        '/<img([^>]+?)\/>/is',
        '<img$1>',
        $buffer
    );

    $buffer = preg_replace(
        '/<meta([^>]+?)\/>/is',
        '<meta$1>',
        $buffer
    );

    // 4. FIX SPECULATION RULES
    $buffer = preg_replace(
        '/<script([^>]*)type\s*=\s*["\']speculationrules(\+json)?["\']/i',
        '<script$1type="application/json" id="speculationrules"',
        $buffer
    );

    // 5. RIMUOVI type="text/css" da <style>
    $buffer = preg_replace(
        '/(<style[^>]*)\s+type\s*=\s*["\']text\/css["\']/i',
        '$1',
        $buffer
    );

    // 6. RIMUOVI type="text/javascript" da <script>
    $buffer = preg_replace(
        '/(<script[^>]*)\s+type\s*=\s*["\']text\/javascript["\']/i',
        '$1',
        $buffer
    );

    // 7. CONVERTI SECTION IN DIV (se necessario)
    $buffer = preg_replace('/<section(\s|>)/i', '<div$1', $buffer);
    $buffer = str_replace('</section>', '</div>', $buffer);
    $buffer = str_replace('</SECTION>', '</div>', $buffer);

    // 8. PULIZIA SPAZI EXTRA NEGLI ATTRIBUTI
    // Rimuove spazi doppi negli attributi
    $buffer = preg_replace('/<([a-z][a-z0-9-]*)\s{2,}/i', '<$1 ', $buffer);

    // Rimuove spazi prima del carattere >
    $buffer = preg_replace('/\s+>/', '>', $buffer);

    // Rimuove spazi multipli tra attributi
    $buffer = preg_replace('/(["\'])\s{2,}([a-z-]+)\s*=/i', '$1 $2=', $buffer);

    // 9. TERZO PASSAGGIO - Ultra aggressivo per trailing slash rimanenti
    // Questo rimuove qualsiasi "/" seguito da ">" per i void elements
    $buffer = preg_replace(
        '/<(' . $void_elements . ')([^>]*)\s*\/\s*>/is',
        '<$1$2>',
        $buffer
    );

    // Commento diagnostico
    $diagnostic = "\n<!-- W3C Fixer ULTRA v6.0-SHUTDOWN - Attivo (Shutdown hook, Multi-pass slash removal, Perfmatters optimized) -->\n";
    $buffer = str_replace('</head>', $diagnostic . '</head>', $buffer);

    return $buffer;
}

/**
 * STRATEGIA SHUTDOWN HOOK per catturare l'output DOPO Perfmatters
 * Usa il shutdown hook che è l'ultimo possibile in WordPress
 */

// Variabile globale per tracciare se abbiamo avviato il buffer
global $w3c_fixer_buffer_started;
$w3c_fixer_buffer_started = false;

// Avvia output buffering il prima possibile
add_action('init', function() {
    global $w3c_fixer_buffer_started;

    // Non processare admin, ajax, o rest requests
    if (
        is_admin() ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (defined('REST_REQUEST') && REST_REQUEST)
    ) {
        return;
    }

    ob_start();
    $w3c_fixer_buffer_started = true;
}, 0); // Priorità 0 = avvia per primo

// Processa e chiudi il buffer nell'hook shutdown (ultimo possibile)
add_action('shutdown', function() {
    global $w3c_fixer_buffer_started;

    // Non processare admin, ajax, o rest requests
    if (
        is_admin() ||
        (defined('DOING_AJAX') && DOING_AJAX) ||
        (defined('REST_REQUEST') && REST_REQUEST) ||
        !$w3c_fixer_buffer_started
    ) {
        return;
    }

    // Cattura tutto l'output e chiudi il nostro buffer
    if (ob_get_level() > 0) {
        $buffer = ob_get_clean();

        // Processa il buffer
        $buffer = w3c_fixer_ultra_callback($buffer);

        // Output finale
        echo $buffer;
    }
}, PHP_INT_MAX); // Priorità massima = esegue per ultimo tra i shutdown hooks

/**
 * Filtri aggiuntivi per script e style inline - PRIORITÀ MASSIMA
 */
add_filter('wp_get_inline_script_tag', function($tag) {
    // Fix speculation rules
    $tag = preg_replace('/type\s*=\s*["\']speculationrules(\+json)?["\']/i', 'type="application/json"', $tag);

    // Rimuovi type text/javascript
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/javascript["\']/i', '', $tag);

    return $tag;
}, PHP_INT_MAX);

/**
 * Filtro per tag script - PRIORITÀ MASSIMA
 */
add_filter('script_loader_tag', function($tag, $handle, $src) {
    // Rimuovi type text/javascript
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/javascript["\']/i', '', $tag);

    // Rimuovi type pmdelayedscript
    $tag = preg_replace('/\s+type\s*=\s*["\']pmdelayedscript["\']/i', '', $tag);

    return $tag;
}, PHP_INT_MAX, 3);

/**
 * Filtro per tag style - PRIORITÀ MASSIMA
 */
add_filter('style_loader_tag', function($tag, $handle, $href, $media) {
    // Rimuovi type text/css
    $tag = preg_replace('/\s+type\s*=\s*["\']text\/css["\']/i', '', $tag);

    // Rimuovi trailing slash da link
    $tag = preg_replace('/\s*\/\s*>/', '>', $tag);

    return $tag;
}, PHP_INT_MAX, 4);

/**
 * Filtro generico per tutto l'output HTML - PRIORITÀ MASSIMA
 */
add_filter('the_content', function($content) {
    $void_elements = 'link|meta|img|br|hr|input|area|base|col|embed|param|source|track|wbr';

    // Rimuovi trailing slash
    $content = preg_replace(
        '/<(' . $void_elements . ')([^>]*?)\s*\/\s*>/is',
        '<$1$2>',
        $content
    );

    return $content;
}, PHP_INT_MAX);

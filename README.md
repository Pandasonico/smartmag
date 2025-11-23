# W3C Validation Fixer Plugin

Plugin WordPress che corregge automaticamente gli errori di validazione W3C più comuni nell'output HTML.

## 🎯 Caratteristiche

Il plugin applica automaticamente le seguenti correzioni:

- ✅ **pmdelayedscript**: Rimuove `type="pmdelayedscript"` (attributo non valido)
- ✅ **speculationrules**: Converte `type="speculationrules"` in `type="application/json"`
- ✅ **Self-closing tags**: Rimuove `/` da elementi void HTML5 (`<img />` → `<img>`)
- ✅ **SVG elements**: Rimuove `/` da elementi SVG auto-chiusi
- ✅ **Type ridondanti**: Rimuove `type="text/css"` e `type="text/javascript"` (HTML5)
- ✅ **Whitespace**: Pulisce spazi extra negli attributi

## 📦 Installazione

### Metodo 1: Upload diretto

1. Scarica il file `w3c-validation-fixer.php`
2. Carica nella cartella `wp-content/plugins/` del tuo WordPress
3. Attiva il plugin dalla dashboard WordPress (Plugin → Installed Plugins)

### Metodo 2: Come mu-plugin (must-use)

1. Crea la cartella `wp-content/mu-plugins/` se non esiste
2. Copia `w3c-validation-fixer.php` in quella cartella
3. Il plugin si attiverà automaticamente (non compare nella lista plugin)

## 🔍 Verifica funzionamento

Dopo l'attivazione:

1. Visita una pagina del tuo sito (frontend, non admin)
2. Visualizza il codice sorgente HTML (Ctrl+U / Cmd+U)
3. Cerca nel `<head>` il commento diagnostico:

```html
<!-- W3C Fixer v2.1 | pmdelayedscript:5→0 | speculationrules:1→0 | slashes:58→0 -->
```

I numeri indicano:
- **Prima → Dopo**: Quante occorrenze sono state trovate e corrette
- **Target**: Tutti i valori "Dopo" dovrebbero essere `0`

## 🐛 Debug

Il plugin scrive log automaticamente se `WP_DEBUG` è attivo.

### Attivare debug in WordPress:

Modifica `wp-config.php` e aggiungi:

```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

Poi controlla il file `wp-content/debug.log` per vedere:

```
W3C Fixer: pmdelayedscript 5→0 | speculationrules 1→0 | slashes 58→0
```

## ⚙️ Compatibilità

- **WordPress**: 5.0+
- **PHP**: 7.4+
- **Compatibile con**:
  - **Perfmatters** (delay JS, script optimization)
  - Elementor (si disattiva automaticamente in preview)
  - Plugin di cache (WP Rocket, W3 Total Cache, ecc.)
  - Tutti i temi WordPress

### 🎯 Nota su Perfmatters

Questo plugin è stato progettato specificamente per funzionare **DOPO** Perfmatters:
- Perfmatters aggiunge `type="pmdelayedscript"` durante l'ottimizzazione
- W3C Fixer lo rimuove prima dell'invio al browser
- **Ordine di esecuzione**: Perfmatters → W3C Fixer → Browser

### ⚠️ Nota sulla cache

Se usi un plugin di cache, **devi svuotare la cache** dopo l'attivazione del plugin, altrimenti vedrai ancora l'HTML vecchio.

## 🔧 Come funziona

1. **Output buffering**: Cattura tutto l'HTML prima dell'invio al browser
2. **Pattern matching**: Trova e corregge i problemi con regex ottimizzate
3. **Filtri WordPress**: Gestisce anche script/style caricati dinamicamente

Il plugin si attiva solo sul frontend, non in:
- Admin panel
- AJAX requests
- Elementor preview/editor

## 📊 Risultati attesi W3C Validator

Prima:
```
❌ Error: Attribute type="pmdelayedscript" not allowed
❌ Error: Attribute type="speculationrules" not allowed
❌ Error: Self-closing syntax ("/>") used on a non-void HTML element
```

Dopo:
```
✅ Document checking completed. No errors found.
```

## 🤝 Supporto

Per problemi o domande:
- Controlla il commento diagnostico nel `<head>`
- Abilita `WP_DEBUG` e controlla i log
- Svuota tutte le cache (browser + server + CDN)

## 📝 Versioni

- **v3.0**: Strategia completamente ridisegnata per compatibilità Perfmatters - usa shutdown hook con priorità massima
- **v2.1**: Rewrite completo con architettura migliorata
- **v2.0**: Aggiunto supporto SVG e debug mode
- **v1.0**: Versione iniziale

## ⚖️ Licenza

Generato automaticamente - Uso libero

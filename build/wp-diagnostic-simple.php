<?php
/**
 * Diagnostic ultra-simple pour PDF Builder Pro
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la bufferisation de sortie
ob_start();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Diagnostic Simple PDF Builder Pro</title>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f0f0f0;} .log{color:blue;font-weight:bold;} .error{color:red;} .success{color:green;}</style>\n";
echo "</head><body>\n";

echo "<h1>🔍 Diagnostic Ultra-Simple PDF Builder Pro</h1>\n";
echo "<p>Si vous voyez cette page, le PHP fonctionne.</p>\n";
echo "<div id='logs'></div>\n";

// JavaScript ultra-simple pour les logs
echo "<script>\n";
// Fonction pour ajouter des logs à la page et à la console
echo "function addLog(message, type) {\n";
echo "    console.log('[DIAGNOSTIC]', message);\n";
echo "    var div = document.createElement('div');\n";
echo "    div.className = type || 'log';\n";
echo "    div.textContent = '[DIAGNOSTIC] ' + message;\n";
echo "    document.getElementById('logs').appendChild(div);\n";
echo "}\n";

// Logs de base
echo "addLog('JavaScript chargé et exécuté');\n";
echo "addLog('User Agent: ' + navigator.userAgent);\n";
echo "addLog('URL: ' + window.location.href);\n";
echo "addLog('Timestamp: ' + new Date().toISOString());\n";

// Test des fonctionnalités de base
echo "try {\n";
echo "    addLog('Test Array.map: ' + (typeof Array.prototype.map !== 'undefined' ? 'OK' : 'ERREUR'));\n";
echo "    addLog('Test Promise: ' + (typeof Promise !== 'undefined' ? 'OK' : 'ERREUR'));\n";
echo "    addLog('Test localStorage: ' + (typeof localStorage !== 'undefined' ? 'OK' : 'ERREUR'));\n";
echo "} catch(e) {\n";
echo "    addLog('ERREUR JavaScript: ' + e.message, 'error');\n";
echo "}\n";

// Test jQuery
echo "if (typeof jQuery !== 'undefined') {\n";
echo "    addLog('jQuery trouvé - version: ' + jQuery.fn.jquery, 'success');\n";
echo "} else {\n";
echo "    addLog('jQuery NON trouvé', 'error');\n";
echo "}\n";

// Test variables WordPress
echo "addLog('Test ajaxurl: ' + (typeof ajaxurl !== 'undefined' ? 'défini' : 'NON défini'));\n";
echo "addLog('Test wpApiSettings: ' + (typeof wpApiSettings !== 'undefined' ? 'défini' : 'NON défini'));\n";

// Test au chargement du DOM
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    addLog('DOM Content Loaded - page prête');\n";
echo "    \n";
echo "    // Compter les scripts\n";
echo "    var scripts = document.getElementsByTagName('script');\n";
echo "    addLog('Nombre total de scripts: ' + scripts.length);\n";
echo "    \n";
echo "    // Chercher les scripts PDF Builder\n";
echo "    var pdfScripts = 0;\n";
echo "    for (var i = 0; i < scripts.length; i++) {\n";
echo "        var src = scripts[i].src || '';\n";
echo "        if (src.indexOf('pdf-builder') !== -1) {\n";
echo "            pdfScripts++;\n";
echo "            addLog('Script PDF Builder trouvé: ' + src);\n";
echo "        }\n";
echo "    }\n";
echo "    if (pdfScripts === 0) {\n";
echo "        addLog('AUCUN script PDF Builder trouvé', 'error');\n";
echo "    }\n";
echo "});\n";

// Test au chargement complet de la fenêtre
echo "window.addEventListener('load', function() {\n";
echo "    addLog('Window Load Complete - tout chargé');\n";
echo "    \n";
echo "    // Vérifier les erreurs\n";
echo "    setTimeout(function() {\n";
echo "        addLog('Test final après 1 seconde');\n";
echo "        \n";
echo "        // Test AJAX simple si jQuery disponible\n";
echo "        if (typeof jQuery !== 'undefined' && typeof ajaxurl !== 'undefined') {\n";
echo "            addLog('Test AJAX possible');\n";
echo "        } else {\n";
echo "            addLog('Test AJAX impossible - jQuery ou ajaxurl manquant', 'error');\n";
echo "        }\n";
echo "    }, 1000);\n";
echo "});\n";

// Capturer les erreurs JavaScript
echo "window.addEventListener('error', function(e) {\n";
echo "    addLog('ERREUR JAVASCRIPT: ' + e.message + ' (ligne ' + e.lineno + ')', 'error');\n";
echo "});\n";

echo "</script>\n";

echo "<h2>Informations PHP</h2>\n";
echo "<pre>\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Memory Limit: " . ini_get('memory_limit') . "\n";
echo "Max Execution Time: " . ini_get('max_execution_time') . "\n";
echo "Display Errors: " . (ini_get('display_errors') ? 'On' : 'Off') . "\n";
echo "Error Reporting: " . error_reporting() . "\n";
echo "</pre>\n";

echo "<h2>Test WordPress</h2>\n";

// Test si WordPress est disponible
if (function_exists('get_bloginfo')) {
    echo "<p class='success'>✅ WordPress détecté</p>\n";
    echo "<pre>\n";
    echo "Version WordPress: " . get_bloginfo('version') . "\n";
    echo "Nom du site: " . get_bloginfo('name') . "\n";
    echo "URL du site: " . get_bloginfo('url') . "\n";
    echo "</pre>\n";
} else {
    echo "<p class='error'>❌ WordPress NON détecté</p>\n";
}

// Test du plugin PDF Builder Pro
echo "<h2>Test Plugin PDF Builder Pro</h2>\n";

$plugin_file = __DIR__ . '/pdf-builder-pro.php';
if (file_exists($plugin_file)) {
    echo "<p class='success'>✅ Fichier principal du plugin trouvé</p>\n";

    // Test d'inclusion basique
    echo "<h3>Test d'inclusion basique</h3>\n";
    try {
        include_once $plugin_file;
        echo "<p class='success'>✅ Plugin inclus sans erreur fatale</p>\n";
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erreur lors de l'inclusion: " . $e->getMessage() . "</p>\n";
    } catch (Error $e) {
        echo "<p class='error'>❌ Erreur fatale lors de l'inclusion: " . $e->getMessage() . "</p>\n";
    }

} else {
    echo "<p class='error'>❌ Fichier principal du plugin NON trouvé: $plugin_file</p>\n";
}

echo "<h2>Logs JavaScript (aussi visibles dans la console F12)</h2>\n";
echo "<p>Ouvrez la console du navigateur (F12) pour voir tous les logs détaillés.</p>\n";

echo "</body></html>\n";

// Vider le buffer
ob_end_flush();
?>
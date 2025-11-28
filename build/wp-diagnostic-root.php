<?php
/**
 * Diagnostic WordPress pour PDF Builder Pro
 * À placer à la RACINE du site WordPress (pas dans le dossier plugins)
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la bufferisation de sortie
ob_start();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Diagnostic WordPress PDF Builder Pro</title>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;background:#f0f0f0;} .log{color:blue;font-weight:bold;} .error{color:red;} .success{color:green;} .warning{color:orange;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;overflow:auto;max-height:400px;}</style>\n";
echo "</head><body>\n";

echo "<h1>🔍 Diagnostic WordPress PDF Builder Pro</h1>\n";
echo "<div id='logs'></div>\n";

// JavaScript pour les logs
echo "<script>\n";
echo "function addLog(message, type) {\n";
echo "    console.log('[DIAGNOSTIC]', message);\n";
echo "    var div = document.createElement('div');\n";
echo "    div.className = type || 'log';\n";
echo "    div.textContent = '[DIAGNOSTIC] ' + message;\n";
echo "    document.getElementById('logs').appendChild(div);\n";
echo "}\n";

echo "addLog('Script de diagnostic démarré');\n";
echo "addLog('Test JavaScript: OK');\n";
echo "</script>\n";

echo "<h2>🔄 Chargement de WordPress...</h2>\n";

// Essayer de charger WordPress
$current_dir = __DIR__;
$wp_load_attempts = [
    // Remonter depuis le dossier plugin vers la racine WordPress
    dirname(dirname(dirname($current_dir))) . '/wp-load.php', // /wp-content/plugins/ -> /
    dirname(dirname(dirname(dirname($current_dir)))) . '/wp-load.php', // au cas où
    dirname($current_dir) . '/../../wp-load.php', // depuis wp-pdf-builder-pro/
    dirname($current_dir) . '/../wp-load.php', // depuis plugins/
    $current_dir . '/../../../../wp-load.php', // chemin absolu depuis plugin
    // Chemins standards
    '/var/www/nats/data/www/threeaxe.fr/wp-load.php',
    '/var/www/html/wp-load.php',
    '/home/user/public_html/wp-load.php'
];

$wp_loaded = false;
$wp_load_path = '';

foreach ($wp_load_attempts as $attempt) {
    if (file_exists($attempt)) {
        echo "<p>🔍 Tentative de chargement WordPress depuis : <code>$attempt</code></p>\n";
        try {
            require_once $attempt;
            if (function_exists('wp_get_current_user')) {
                $wp_loaded = true;
                $wp_load_path = $attempt;
                echo "<p class='success'>✅ WordPress chargé avec succès !</p>\n";
                break;
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Erreur lors du chargement : " . $e->getMessage() . "</p>\n";
        } catch (Error $e) {
            echo "<p class='error'>❌ Erreur fatale lors du chargement : " . $e->getMessage() . "</p>\n";
        }
    }
}

if (!$wp_loaded) {
    echo "<p class='error'>❌ Impossible de charger WordPress depuis les emplacements testés.</p>\n";
    echo "<p>Emplacements testés :</p>\n";
    echo "<ul>\n";
    foreach ($wp_load_attempts as $attempt) {
        echo "<li><code>$attempt</code> - " . (file_exists($attempt) ? 'existe' : 'n\'existe pas') . "</li>\n";
    }
    echo "</ul>\n";

    echo "<h2>Informations système</h2>\n";
    echo "<pre>\n";
    echo "Répertoire courant: " . __DIR__ . "\n";
    echo "Script exécuté depuis: " . $_SERVER['SCRIPT_FILENAME'] . "\n";
    echo "URL demandée: " . $_SERVER['REQUEST_URI'] . "\n";
    echo "PHP Version: " . phpversion() . "\n";
    echo "</pre>\n";

    echo "<h2>Instructions</h2>\n";
    echo "<p>Pour que ce diagnostic fonctionne, placez ce fichier à la racine de votre installation WordPress (au même niveau que wp-config.php).</p>\n";
    echo "<p>Si vous ne savez pas où est votre racine WordPress, cherchez le fichier wp-config.php.</p>\n";

    echo "</body></html>\n";
    ob_end_flush();
    exit;
}

echo "<script>addLog('WordPress chargé avec succès');</script>\n";

echo "<h2>✅ Test WordPress</h2>\n";
echo "<pre>\n";
echo "Version WordPress: " . get_bloginfo('version') . "\n";
echo "Nom du site: " . get_bloginfo('name') . "\n";
echo "URL du site: " . get_bloginfo('url') . "\n";
echo "Utilisateur actuel: " . (is_user_logged_in() ? wp_get_current_user()->user_login : 'Non connecté') . "\n";
echo "Est admin: " . (current_user_can('administrator') ? 'Oui' : 'Non') . "\n";
echo "</pre>\n";

echo "<script>addLog('Informations WordPress récupérées');</script>\n";

echo "<h2>🔍 Test Plugin PDF Builder Pro</h2>\n";

// Vérifier si le plugin est actif
$plugin_active = false;
if (function_exists('is_plugin_active')) {
    $plugin_active = is_plugin_active('wp-pdf-builder-pro/pdf-builder-pro.php');
    echo "<p>" . ($plugin_active ? '✅' : '❌') . " Plugin " . ($plugin_active ? '' : 'NON ') . "actif</p>\n";
} else {
    echo "<p class='warning'>⚠️ Fonction is_plugin_active non disponible</p>\n";
}

// Vérifier si le plugin existe
$plugin_file = WP_PLUGIN_DIR . '/wp-pdf-builder-pro/pdf-builder-pro.php';
if (file_exists($plugin_file)) {
    echo "<p class='success'>✅ Fichier principal du plugin trouvé : <code>$plugin_file</code></p>\n";

    // Tester l'inclusion du plugin
    echo "<h3>Test d'inclusion du plugin</h3>\n";
    try {
        // Ne pas inclure si déjà chargé
        if (!class_exists('PDF_Builder_Pro')) {
            include_once $plugin_file;
            echo "<p class='success'>✅ Plugin inclus sans erreur fatale</p>\n";
            echo "<script>addLog('Plugin inclus avec succès');</script>\n";
        } else {
            echo "<p class='success'>✅ Plugin déjà chargé</p>\n";
            echo "<script>addLog('Plugin déjà chargé');</script>\n";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Erreur lors de l'inclusion : " . $e->getMessage() . "</p>\n";
        echo "<script>addLog('Erreur inclusion plugin: " . addslashes($e->getMessage()) . "', 'error');</script>\n";
    } catch (Error $e) {
        echo "<p class='error'>❌ Erreur fatale lors de l'inclusion : " . $e->getMessage() . "</p>\n";
        echo "<script>addLog('Erreur fatale inclusion plugin: " . addslashes($e->getMessage()) . "', 'error');</script>\n";
    }

} else {
    echo "<p class='error'>❌ Fichier principal du plugin NON trouvé : <code>$plugin_file</code></p>\n";
    echo "<script>addLog('Fichier plugin non trouvé', 'error');</script>\n";
}

echo "<h2>🔧 Test des fonctionnalités JavaScript</h2>\n";

// Tester jQuery et les variables WordPress
echo "<script>\n";
echo "if (typeof jQuery !== 'undefined') {\n";
echo "    addLog('jQuery trouvé - version: ' + jQuery.fn.jquery, 'success');\n";
echo "} else {\n";
echo "    addLog('jQuery NON trouvé', 'error');\n";
echo "}\n";

echo "addLog('Test ajaxurl: ' + (typeof ajaxurl !== 'undefined' ? 'défini' : 'NON défini'));\n";
echo "addLog('Test wpApiSettings: ' + (typeof wpApiSettings !== 'undefined' ? 'défini' : 'NON défini'));\n";

// Test au chargement du DOM
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    addLog('DOM Content Loaded');\n";
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
echo "            addLog('Script PDF Builder: ' + src);\n";
echo "        }\n";
echo "    }\n";
echo "    if (pdfScripts === 0) {\n";
echo "        addLog('AUCUN script PDF Builder trouvé', 'error');\n";
echo "    } else {\n";
echo "        addLog(pdfScripts + ' script(s) PDF Builder trouvé(s)', 'success');\n";
echo "    }\n";
echo "});\n";

// Test au chargement complet
echo "window.addEventListener('load', function() {\n";
echo "    addLog('Window Load Complete');\n";
echo "    \n";
echo "    setTimeout(function() {\n";
echo "        addLog('Test final après 2 secondes');\n";
echo "        \n";
echo "        // Test AJAX si possible\n";
echo "        if (typeof jQuery !== 'undefined' && typeof ajaxurl !== 'undefined') {\n";
echo "            addLog('Test AJAX possible - envoi test...');\n";
echo "            jQuery.ajax({\n";
echo "                url: ajaxurl,\n";
echo "                type: 'POST',\n";
echo "                data: { action: 'test_pdf_builder' },\n";
echo "                success: function(response) {\n";
echo "                    addLog('AJAX test réussi', 'success');\n";
echo "                },\n";
echo "                error: function(xhr, status, error) {\n";
echo "                    addLog('AJAX test échoué: ' + error, 'error');\n";
echo "                }\n";
echo "            });\n";
echo "        } else {\n";
echo "            addLog('Test AJAX impossible', 'error');\n";
echo "        }\n";
echo "    }, 2000);\n";
echo "});\n";

// Capturer les erreurs JavaScript
echo "window.addEventListener('error', function(e) {\n";
echo "    addLog('ERREUR JS: ' + e.message + ' (ligne ' + e.lineno + ')', 'error');\n";
echo "});\n";

echo "</script>\n";

echo "<h2>📊 Résumé du diagnostic</h2>\n";
echo "<div id='summary'></div>\n";

echo "<script>\n";
echo "setTimeout(function() {\n";
echo "    var summary = document.getElementById('summary');\n";
echo "    var logs = document.querySelectorAll('#logs > div');\n";
echo "    var errors = document.querySelectorAll('#logs > div.error');\n";
echo "    var success = document.querySelectorAll('#logs > div.success');\n";
echo "    \n";
echo "    summary.innerHTML = '<p>Total logs: ' + logs.length + '</p>' +\n";
echo "                       '<p class=\"success\">Succès: ' + success.length + '</p>' +\n";
echo "                       '<p class=\"error\">Erreurs: ' + errors.length + '</p>';\n";
echo "    \n";
echo "    if (errors.length > 0) {\n";
echo "        summary.innerHTML += '<p class=\"error\">⚠️ Des erreurs ont été détectées - vérifiez les logs ci-dessus</p>';\n";
echo "    } else {\n";
echo "        summary.innerHTML += '<p class=\"success\">✅ Aucune erreur détectée</p>';\n";
echo "    }\n";
echo "}, 3000);\n";
echo "</script>\n";

echo "</body></html>\n";

// Vider le buffer
ob_end_flush();
?>
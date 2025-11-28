<?php
/**
 * Diagnostic WordPress pour PDF Builder Pro
 * À placer à la racine du site WordPress pour diagnostiquer les erreurs
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Démarrer la bufferisation de sortie
ob_start();

echo "<!DOCTYPE html>\n";
echo "<html><head><title>Diagnostic PDF Builder Pro</title>\n";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .warning{color:orange;} pre{background:#f5f5f5;padding:10px;border:1px solid #ddd;overflow:auto;}</style>\n";

// JavaScript pour les logs de diagnostic
echo "<script>\n";
echo "console.log('🔍 [DIAGNOSTIC] Starting JavaScript diagnostic...');\n";
echo "console.log('🔍 [DIAGNOSTIC] User Agent:', navigator.userAgent);\n";
echo "console.log('🔍 [DIAGNOSTIC] URL:', window.location.href);\n";
echo "console.log('🔍 [DIAGNOSTIC] Timestamp:', new Date().toISOString());\n";
echo "console.log('🔍 [DIAGNOSTIC] Screen size:', window.screen.width + 'x' + window.screen.height);\n";
echo "console.log('🔍 [DIAGNOSTIC] Viewport size:', window.innerWidth + 'x' + window.innerHeight);\n";

// Test des fonctionnalités JavaScript de base
echo "try {\n";
echo "    console.log('🔍 [DIAGNOSTIC] Testing basic JS features...');\n";
echo "    console.log('🔍 [DIAGNOSTIC] Array methods:', typeof Array.prototype.map);\n";
echo "    console.log('🔍 [DIAGNOSTIC] Promises:', typeof Promise);\n";
echo "    console.log('🔍 [DIAGNOSTIC] Fetch API:', typeof fetch);\n";
echo "    console.log('🔍 [DIAGNOSTIC] LocalStorage:', typeof localStorage);\n";
echo "    console.log('🔍 [DIAGNOSTIC] SessionStorage:', typeof sessionStorage);\n";
echo "} catch(e) {\n";
echo "    console.error('🔍 [DIAGNOSTIC] Error testing JS features:', e);\n";
echo "}\n";

// Test de jQuery si disponible
echo "if (typeof jQuery !== 'undefined') {\n";
echo "    console.log('🔍 [DIAGNOSTIC] jQuery version:', jQuery.fn.jquery);\n";
echo "    console.log('🔍 [DIAGNOSTIC] jQuery ready state:', document.readyState);\n";
echo "} else {\n";
echo "    console.warn('🔍 [DIAGNOSTIC] jQuery not loaded');\n";
echo "}\n";

// Test des variables globales WordPress
echo "console.log('🔍 [DIAGNOSTIC] WordPress globals:');\n";
echo "console.log('🔍 [DIAGNOSTIC] - ajaxurl:', typeof ajaxurl !== 'undefined' ? ajaxurl : 'undefined');\n";
echo "console.log('🔍 [DIAGNOSTIC] - wpApiSettings:', typeof wpApiSettings !== 'undefined' ? 'defined' : 'undefined');\n";
echo "console.log('🔍 [DIAGNOSTIC] - pdfBuilderAjax:', typeof pdfBuilderAjax !== 'undefined' ? 'defined' : 'undefined');\n";

// Test de chargement des scripts PDF Builder
echo "function checkPDFBuilderScripts() {\n";
echo "    console.log('🔍 [DIAGNOSTIC] Checking PDF Builder scripts...');\n";
echo "    \n";
echo "    var scripts = document.getElementsByTagName('script');\n";
echo "    var pdfScripts = [];\n";
echo "    for (var i = 0; i < scripts.length; i++) {\n";
echo "        var src = scripts[i].src || '';\n";
echo "        if (src.indexOf('pdf-builder') !== -1) {\n";
echo "            pdfScripts.push(src);\n";
echo "        }\n";
echo "    }\n";
echo "    console.log('🔍 [DIAGNOSTIC] PDF Builder scripts found:', pdfScripts.length);\n";
echo "    pdfScripts.forEach(function(script, index) {\n";
echo "        console.log('🔍 [DIAGNOSTIC] Script ' + (index + 1) + ':', script);\n";
echo "    });\n";
echo "}\n";

// Test de chargement des CSS PDF Builder
echo "function checkPDFBuilderCSS() {\n";
echo "    console.log('🔍 [DIAGNOSTIC] Checking PDF Builder CSS...');\n";
echo "    \n";
echo "    var links = document.getElementsByTagName('link');\n";
echo "    var pdfCSS = [];\n";
echo "    for (var i = 0; i < links.length; i++) {\n";
echo "        var href = links[i].href || '';\n";
echo "        if (href.indexOf('pdf-builder') !== -1 && links[i].rel === 'stylesheet') {\n";
echo "            pdfCSS.push(href);\n";
echo "        }\n";
echo "    }\n";
echo "    console.log('🔍 [DIAGNOSTIC] PDF Builder CSS found:', pdfCSS.length);\n";
echo "    pdfCSS.forEach(function(css, index) {\n";
echo "        console.log('🔍 [DIAGNOSTIC] CSS ' + (index + 1) + ':', css);\n";
echo "    });\n";
echo "}\n";

// Test des erreurs JavaScript globales
echo "window.addEventListener('error', function(e) {\n";
echo "    console.error('🔍 [DIAGNOSTIC] JavaScript error detected:');\n";
echo "    console.error('🔍 [DIAGNOSTIC] - Message:', e.message);\n";
echo "    console.error('🔍 [DIAGNOSTIC] - File:', e.filename);\n";
echo "    console.error('🔍 [DIAGNOSTIC] - Line:', e.lineno);\n";
echo "    console.error('🔍 [DIAGNOSTIC] - Column:', e.colno);\n";
echo "    console.error('🔍 [DIAGNOSTIC] - Error:', e.error);\n";
echo "});\n";

// Test des erreurs de chargement de ressources
echo "window.addEventListener('error', function(e) {\n";
echo "    if (e.target !== window) {\n";
echo "        console.error('🔍 [DIAGNOSTIC] Resource loading error:');\n";
echo "        console.error('🔍 [DIAGNOSTIC] - Target:', e.target);\n";
echo "        console.error('🔍 [DIAGNOSTIC] - Type:', e.type);\n";
echo "    }\n";
echo "}, true);\n";

// Test du DOM ready
echo "document.addEventListener('DOMContentLoaded', function() {\n";
echo "    console.log('🔍 [DIAGNOSTIC] DOM Content Loaded');\n";
echo "    checkPDFBuilderScripts();\n";
echo "    checkPDFBuilderCSS();\n";
echo "    \n";
echo "    // Test des éléments PDF Builder dans le DOM\n";
echo "    var pdfElements = document.querySelectorAll('[class*=\"pdf-builder\"], [id*=\"pdf-builder\"]');\n";
echo "    console.log('🔍 [DIAGNOSTIC] PDF Builder DOM elements found:', pdfElements.length);\n";
echo "    \n";
echo "    // Test des erreurs AJAX si jQuery est disponible\n";
echo "    if (typeof jQuery !== 'undefined') {\n";
echo "        jQuery(document).ajaxError(function(event, xhr, settings, thrownError) {\n";
echo "            console.error('🔍 [DIAGNOSTIC] AJAX Error:');\n";
echo "            console.error('🔍 [DIAGNOSTIC] - URL:', settings.url);\n";
echo "            console.error('🔍 [DIAGNOSTIC] - Status:', xhr.status);\n";
echo "            console.error('🔍 [DIAGNOSTIC] - Error:', thrownError);\n";
echo "        });\n";
echo "    }\n";
echo "});\n";

// Test du window load
echo "window.addEventListener('load', function() {\n";
echo "    console.log('🔍 [DIAGNOSTIC] Window Load Complete');\n";
echo "    console.log('🔍 [DIAGNOSTIC] Page fully loaded at:', new Date().toISOString());\n";
echo "    \n";
echo "    // Test final des fonctionnalités\n";
echo "    setTimeout(function() {\n";
echo "        console.log('🔍 [DIAGNOSTIC] Final check after 2 seconds...');\n";
echo "        console.log('🔍 [DIAGNOSTIC] All scripts loaded:', document.scripts.length);\n";
echo "        console.log('🔍 [DIAGNOSTIC] All links loaded:', document.links.length);\n";
echo "        \n";
echo "        // Vérifier si des erreurs ont été loggées\n";
echo "        if (console.error && console.error.toString().indexOf('[DIAGNOSTIC]') !== -1) {\n";
echo "            console.warn('🔍 [DIAGNOSTIC] Some errors were detected - check above');\n";
echo "        } else {\n";
echo "            console.log('🔍 [DIAGNOSTIC] No JavaScript errors detected');\n";
echo "        }\n";
echo "    }, 2000);\n";
echo "});\n";

echo "console.log('🔍 [DIAGNOSTIC] JavaScript diagnostic initialized');\n";
echo "</script>\n";
echo "</head><body>\n";
echo "<h1>🔍 Diagnostic PDF Builder Pro</h1>\n";
echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>\n";

$errors = [];
$warnings = [];

// Test 1: Environnement PHP
echo "<h2>1. Environnement PHP</h2>\n";
echo "<ul>\n";
echo "<li><strong>Version PHP:</strong> " . PHP_VERSION . "</li>\n";
echo "<li><strong>Memory limit:</strong> " . ini_get('memory_limit') . "</li>\n";
echo "<li><strong>Max execution time:</strong> " . ini_get('max_execution_time') . "</li>\n";
echo "<li><strong>Display errors:</strong> " . (ini_get('display_errors') ? 'On' : 'Off') . "</li>\n";
echo "<li><strong>Error reporting:</strong> " . error_reporting() . "</li>\n";
echo "</ul>\n";

// Test 2: Environnement WordPress
echo "<h2>2. Environnement WordPress</h2>\n";

if (defined('ABSPATH')) {
    echo "<p class='success'>✅ WordPress détecté (ABSPATH défini)</p>\n";

    // Test de chargement de WordPress
    echo "<h3>Test de chargement WordPress</h3>\n";

    try {
        // Essayer de charger wp-load.php
        $wp_load_path = ABSPATH . 'wp-load.php';
        if (file_exists($wp_load_path)) {
            echo "<p>Chargement de wp-load.php...</p>\n";

            // Capturer les erreurs pendant le chargement
            $error_buffer = '';
            set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$error_buffer) {
                $error_buffer .= "Erreur PHP [$errno]: $errstr in $errfile on line $errline\n";
            });

            require_once $wp_load_path;

            restore_error_handler();

            if (!empty($error_buffer)) {
                echo "<p class='error'>❌ Erreurs pendant le chargement de WordPress:</p>\n";
                echo "<pre>$error_buffer</pre>\n";
                $errors[] = "Erreurs WordPress: " . $error_buffer;
            } else {
                echo "<p class='success'>✅ WordPress chargé avec succès</p>\n";
            }

            // Test des fonctions WordPress de base
            echo "<h3>Test des fonctions WordPress</h3>\n";
            $wp_functions = ['get_option', 'wp_get_current_user', 'is_admin', 'wp_die'];

            foreach ($wp_functions as $func) {
                if (function_exists($func)) {
                    echo "<p class='success'>✅ $func existe</p>\n";
                } else {
                    echo "<p class='error'>❌ $func n'existe pas</p>\n";
                    $errors[] = "Fonction WordPress manquante: $func";
                }
            }

        } else {
            echo "<p class='error'>❌ wp-load.php non trouvé à: $wp_load_path</p>\n";
            $errors[] = "wp-load.php non trouvé";
        }

    } catch (Exception $e) {
        echo "<p class='error'>❌ Exception pendant le chargement WordPress: " . $e->getMessage() . "</p>\n";
        $errors[] = "Exception WordPress: " . $e->getMessage();
    } catch (Error $e) {
        echo "<p class='error'>❌ Erreur fatale pendant le chargement WordPress: " . $e->getMessage() . "</p>\n";
        $errors[] = "Erreur fatale WordPress: " . $e->getMessage();
    }

} else {
    echo "<p class='error'>❌ WordPress non détecté (ABSPATH non défini)</p>\n";
    $errors[] = "WordPress non détecté";
}

// Test 3: Plugin PDF Builder Pro
echo "<h2>3. Plugin PDF Builder Pro</h2>\n";

$plugin_path = ABSPATH . 'wp-content/plugins/wp-pdf-builder-pro/pdf-builder-pro.php';
if (file_exists($plugin_path)) {
    echo "<p class='success'>✅ Fichier plugin trouvé: $plugin_path</p>\n";

    // Test d'inclusion du plugin
    echo "<h3>Test d'inclusion du plugin</h3>\n";

    try {
        $plugin_error_buffer = '';
        set_error_handler(function($errno, $errstr, $errfile, $errline) use (&$plugin_error_buffer) {
            $plugin_error_buffer .= "Erreur plugin [$errno]: $errstr in $errfile on line $errline\n";
        });

        require_once $plugin_path;

        restore_error_handler();

        if (!empty($plugin_error_buffer)) {
            echo "<p class='error'>❌ Erreurs pendant l'inclusion du plugin:</p>\n";
            echo "<pre>$plugin_error_buffer</pre>\n";
            $errors[] = "Erreurs plugin: " . $plugin_error_buffer;
        } else {
            echo "<p class='success'>✅ Plugin inclus avec succès</p>\n";
        }

        // Test des classes principales
        echo "<h3>Test des classes principales</h3>\n";
        $classes_to_test = [
            'PDF_Builder_Global_Config_Manager',
            'PDF_Builder_Onboarding_Manager_Alias',
            'PDF_Builder_Onboarding_Manager_Standalone'
        ];

        foreach ($classes_to_test as $class) {
            if (class_exists($class)) {
                echo "<p class='success'>✅ Classe $class existe</p>\n";
            } else {
                echo "<p class='error'>❌ Classe $class n'existe pas</p>\n";
                $errors[] = "Classe manquante: $class";
            }
        }

    } catch (Exception $e) {
        echo "<p class='error'>❌ Exception plugin: " . $e->getMessage() . "</p>\n";
        $errors[] = "Exception plugin: " . $e->getMessage();
    } catch (Error $e) {
        echo "<p class='error'>❌ Erreur fatale plugin: " . $e->getMessage() . "</p>\n";
        $errors[] = "Erreur fatale plugin: " . $e->getMessage();
    }

} else {
    echo "<p class='error'>❌ Fichier plugin non trouvé: $plugin_path</p>\n";
    $errors[] = "Plugin file not found";
}

// Test 4: Plugins actifs
echo "<h2>4. Plugins actifs</h2>\n";

if (function_exists('get_option')) {
    $active_plugins = get_option('active_plugins', []);
    echo "<p><strong>Nombre de plugins actifs:</strong> " . count($active_plugins) . "</p>\n";

    if (!empty($active_plugins)) {
        echo "<ul>\n";
        foreach ($active_plugins as $plugin) {
            echo "<li>$plugin</li>\n";
        }
        echo "</ul>\n";
    }
} else {
    echo "<p class='warning'>⚠️ Impossible de récupérer la liste des plugins actifs</p>\n";
}

// Test 5: Thème actif
echo "<h2>5. Thème actif</h2>\n";

if (function_exists('wp_get_theme')) {
    $theme = wp_get_theme();
    echo "<p><strong>Thème actif:</strong> " . $theme->get('Name') . " (version " . $theme->get('Version') . ")</p>\n";
} else {
    echo "<p class='warning'>⚠️ Impossible de récupérer les informations du thème</p>\n";
}

// Résumé
echo "<h2>📊 Résumé du diagnostic</h2>\n";

if (!empty($errors)) {
    echo "<p class='error'>❌ <strong>" . count($errors) . " erreur(s) détectée(s):</strong></p>\n";
    echo "<ul>\n";
    foreach ($errors as $error) {
        echo "<li>$error</li>\n";
    }
    echo "</ul>\n";
} else {
    echo "<p class='success'>✅ Aucune erreur critique détectée</p>\n";
}

if (!empty($warnings)) {
    echo "<p class='warning'>⚠️ <strong>" . count($warnings) . " avertissement(s):</strong></p>\n";
    echo "<ul>\n";
    foreach ($warnings as $warning) {
        echo "<li>$warning</li>\n";
    }
    echo "</ul>\n";
}

// Informations système
echo "<h2>💻 Informations système</h2>\n";
echo "<ul>\n";
echo "<li><strong>Serveur:</strong> " . $_SERVER['SERVER_SOFTWARE'] . "</li>\n";
echo "<li><strong>PHP:</strong> " . PHP_VERSION . "</li>\n";
echo "<li><strong>OS:</strong> " . PHP_OS . "</li>\n";
echo "<li><strong>Memory utilisée:</strong> " . round(memory_get_peak_usage(true) / 1024 / 1024, 2) . " MB</li>\n";
echo "</ul>\n";

echo "<hr>\n";
echo "<p><em>Diagnostic terminé le " . date('Y-m-d H:i:s') . "</em></p>\n";
echo "</body></html>\n";

// Récupérer et afficher la sortie bufferisée
$content = ob_get_clean();
echo $content;

// Log des erreurs dans un fichier si possible
if (!empty($errors)) {
    $log_file = __DIR__ . '/diagnostic-errors-' . date('Y-m-d-H-i-s') . '.log';
    $log_content = "=== DIAGNOSTIC ERRORS ===\n";
    $log_content .= "Date: " . date('Y-m-d H:i:s') . "\n\n";
    $log_content .= "Errors:\n";
    foreach ($errors as $error) {
        $log_content .= "- $error\n";
    }
    $log_content .= "\n=== END ===\n";

    @file_put_contents($log_file, $log_content);
    echo "<!-- Log saved to: $log_file -->";
}
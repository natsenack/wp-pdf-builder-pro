<?php
/**
 * Plugin: PDF Builder Pro - Diagnostic Tool
 * Description: Outil de diagnostic pour identifier les problèmes de chargement du plugin
 * Version: 1.0.0
 * Author: Diagnostic Tool
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    die('Accès direct interdit');
}

// Ajouter le menu de diagnostic
add_action('admin_menu', 'pdf_builder_diagnostic_menu');

function pdf_builder_diagnostic_menu() {
    add_submenu_page(
        'tools.php',
        'Diagnostic PDF Builder',
        'Diagnostic PDF Builder',
        'manage_options',
        'pdf-builder-diagnostic',
        'pdf_builder_diagnostic_page'
    );
}

function pdf_builder_diagnostic_page() {
    echo "<div class='wrap'>";
    echo "<h1>🔍 Diagnostic PDF Builder Pro</h1>";
    echo "<p><strong>Date:</strong> " . date('Y-m-d H:i:s') . "</p>";
    echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
    echo "<p><strong>WordPress Version:</strong> " . get_bloginfo('version') . "</p>";
    echo "<p><strong>ABSPATH:</strong> " . ABSPATH . "</p>";

    // Test 1: Vérifier que WordPress est chargé
    echo "<h2>Test 1: Chargement WordPress</h2>";
    if (function_exists('wp_get_current_user')) {
        echo "<p style='color: green;'>✅ <strong>WordPress chargé correctement</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>ERREUR: WordPress NON chargé</strong></p>";
        return;
    }

    // Test 2: Vérifier les fonctions WordPress de base
    echo "<h2>Test 2: Fonctions WordPress de base</h2>";
    $wp_functions = [
        'get_option',
        'update_option',
        'wp_enqueue_script',
        'wp_enqueue_style',
        'add_action',
        'add_filter',
        'register_activation_hook',
        'register_deactivation_hook'
    ];

    $missing_functions = [];
    foreach ($wp_functions as $func) {
        if (!function_exists($func)) {
            $missing_functions[] = $func;
        }
    }

    if (empty($missing_functions)) {
        echo "<p style='color: green;'>✅ <strong>Toutes les fonctions WordPress de base sont disponibles</strong></p>";
    } else {
        echo "<p style='color: red;'>❌ <strong>Fonctions WordPress manquantes:</strong> " . implode(', ', $missing_functions) . "</p>";
    }

    // Test 3: Vérifier les fichiers du plugin
    echo "<h2>Test 3: Fichiers du plugin</h2>";
    $plugin_files = [
        'pdf-builder-pro.php',
        'bootstrap.php',
        'core/autoloader.php',
        'src/Core/PDF_Builder_Update_Manager.php',
        'src/Core/PDF_Builder_Metrics_Analytics.php',
        'src/utilities/PDF_Builder_Notification_Manager.php'
    ];

    $plugin_dir = plugin_dir_path(__FILE__);
    $missing_files = [];

    foreach ($plugin_files as $file) {
        $file_path = $plugin_dir . $file;
        if (!file_exists($file_path)) {
            $missing_files[] = $file;
            echo "<p style='color: red;'>❌ $file (MANQUANT)</p>";
        } else {
            echo "<p style='color: green;'>✅ $file (" . filesize($file_path) . " bytes)</p>";
        }
    }

    if (!empty($missing_files)) {
        echo "<p style='color: red;'><strong>Fichiers manquants:</strong> " . implode(', ', $missing_files) . "</p>";
    }

    // Test 4: Tester le chargement du plugin principal
    echo "<h2>Test 4: Chargement du plugin principal</h2>";

    try {
        // Essayer de charger le fichier principal
        $main_file = $plugin_dir . 'pdf-builder-pro.php';

        if (file_exists($main_file)) {
            echo "<p>🔄 Tentative de chargement de pdf-builder-pro.php...</p>";

            // Inclure le fichier avec gestion d'erreur
            ob_start();
            $result = include_once($main_file);
            $output = ob_get_clean();

            if ($result === false) {
                echo "<p style='color: red;'>❌ <strong>ERREUR lors de l'inclusion du fichier principal</strong></p>";
            } else {
                echo "<p style='color: green;'>✅ <strong>Fichier principal inclus avec succès</strong></p>";
                if (!empty($output)) {
                    echo "<p><strong>Sortie du fichier:</strong> <pre style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6;'>" . htmlspecialchars($output) . "</pre></p>";
                }
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>Fichier principal introuvable</strong></p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Exception lors du chargement:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    } catch (Error $e) {
        echo "<p style='color: red;'>❌ <strong>Erreur fatale lors du chargement:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    }

    // Test 5: Vérifier les classes du plugin
    echo "<h2>Test 5: Classes du plugin</h2>";
    $plugin_classes = [
        'PDF_Builder_Update_Manager',
        'PDF_Builder_Metrics_Analytics',
        'PDF_Builder_UI_Notification_Manager',
        'PDF_Builder_Intelligent_Loader',
        'PDF_Builder_Config_Manager'
    ];

    $missing_classes = [];
    foreach ($plugin_classes as $class) {
        if (!class_exists($class)) {
            $missing_classes[] = $class;
        } else {
            echo "<p style='color: green;'>✅ Classe $class disponible</p>";
        }
    }

    if (!empty($missing_classes)) {
        echo "<p style='color: red;'>❌ <strong>Classes manquantes:</strong> " . implode(', ', $missing_classes) . "</p>";
    }

    // Test 6: Vérifier les constantes du plugin
    echo "<h2>Test 6: Constantes du plugin</h2>";
    if (defined('PDF_BUILDER_PLUGIN_DIR')) {
        echo "<p style='color: green;'>✅ PDF_BUILDER_PLUGIN_DIR = " . PDF_BUILDER_PLUGIN_DIR . "</p>";
    } else {
        echo "<p style='color: red;'>❌ Constante PDF_BUILDER_PLUGIN_DIR NON définie</p>";
    }

    if (defined('PDF_BUILDER_VERSION')) {
        echo "<p style='color: green;'>✅ PDF_BUILDER_VERSION = " . PDF_BUILDER_VERSION . "</p>";
    } else {
        echo "<p style='color: orange;'>⚠️ Constante PDF_BUILDER_VERSION NON définie (normal si pas encore initialisée)</p>";
    }

    // Test 7: Plugins actifs
    echo "<h2>Test 7: Plugins actifs</h2>";
    if (function_exists('get_option')) {
        $active_plugins = get_option('active_plugins', []);
        echo "<p><strong>Plugins actifs:</strong> " . count($active_plugins) . "</p>";

        $pdf_builder_active = false;
        foreach ($active_plugins as $plugin) {
            if (strpos($plugin, 'wp-pdf-builder-pro') !== false) {
                $pdf_builder_active = true;
                echo "<p style='color: green;'>✅ <strong>PDF Builder Pro est activé:</strong> $plugin</p>";
                break;
            }
        }

        if (!$pdf_builder_active) {
            echo "<p style='color: red;'>❌ <strong>PDF Builder Pro n'est PAS activé</strong></p>";
            echo "<p><strong>Plugins actifs:</strong></p>";
            echo "<ul>";
            foreach ($active_plugins as $plugin) {
                echo "<li>$plugin</li>";
            }
            echo "</ul>";
        }
    }

    // Test 8: Vérifier les erreurs PHP récentes
    echo "<h2>Test 8: Erreurs PHP récentes</h2>";
    $log_file = ini_get('error_log');
    if ($log_file && file_exists($log_file)) {
        echo "<p><strong>Fichier de log:</strong> $log_file</p>";

        // Lire les dernières lignes du log
        $log_content = file_get_contents($log_file);
        $lines = explode("\n", $log_content);
        $recent_lines = array_slice($lines, -20); // Dernières 20 lignes

        echo "<p><strong>Dernières erreurs:</strong></p>";
        echo "<pre style='background: #f8f9fa; padding: 10px; border: 1px solid #dee2e6; max-height: 300px; overflow-y: auto;'>";
        foreach ($recent_lines as $line) {
            if (!empty(trim($line))) {
                echo htmlspecialchars($line) . "\n";
            }
        }
        echo "</pre>";
    } else {
        echo "<p style='color: orange;'>⚠️ Fichier de log PHP non accessible ou non configuré</p>";
    }

    // Test 9: Informations système
    echo "<h2>Test 9: Informations système</h2>";
    echo "<p><strong>Mémoire utilisée:</strong> " . memory_get_peak_usage(true) . " bytes</p>";
    echo "<p><strong>Temps d'exécution:</strong> " . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . " secondes</p>";
    echo "<p><strong>Extensions PHP chargées:</strong> " . implode(', ', get_loaded_extensions()) . "</p>";

    echo "<hr>";
    echo "<h2>🔧 Actions recommandées</h2>";
    echo "<ul>";
    echo "<li><strong>Si vous voyez des erreurs fatales:</strong> Corrigez les erreurs PHP dans les fichiers du plugin</li>";
    echo "<li><strong>Si des classes sont manquantes:</strong> Vérifiez que l'autoloader fonctionne correctement</li>";
    echo "<li><strong>Si le plugin n'est pas activé:</strong> Activez-le dans l'administration WordPress</li>";
    echo "<li><strong>Pour plus de détails:</strong> Consultez les logs d'erreur PHP</li>";
    echo "</ul>";

    echo "<p><a href='" . admin_url('plugins.php') . "' class='button button-primary'>Aller à la gestion des plugins</a></p>";
    echo "</div>";
}

// Activer le plugin automatiquement pour le diagnostic
register_activation_hook(__FILE__, function() {
    // Rien à faire pour le diagnostic
});

register_deactivation_hook(__FILE__, function() {
    // Rien à faire pour le diagnostic
});
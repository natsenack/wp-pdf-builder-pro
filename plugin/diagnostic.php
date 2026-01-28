<?php
/**
 * Diagnostic rapide pour PDF Builder Pro
 * Vérifie si le bootstrap se charge correctement
 * Version: 1.2 - Standalone execution
 */

// Essayer de charger WordPress si pas déjà fait
if (!defined('ABSPATH')) {
    // Essayer de trouver le répertoire WordPress
    $wp_paths = [
        '../../../wp-load.php',
        '../../../../wp-load.php',
        '../../../../../wp-load.php',
        dirname(__FILE__) . '/../../../../wp-load.php'
    ];

    $wp_loaded = false;
    foreach ($wp_paths as $wp_path) {
        if (file_exists($wp_path)) {
            require_once $wp_path;
            $wp_loaded = true;
            break;
        }
    }

    if (!$wp_loaded) {
        echo "<h1>Erreur: WordPress non trouvé</h1>";
        echo "<p>Le script diagnostic.php doit être placé dans le répertoire plugins de WordPress.</p>";
        echo "<p>Chemins testés :</p><ul>";
        foreach ($wp_paths as $path) {
            echo "<li>" . realpath(dirname(__FILE__) . '/' . $path) . " - " . (file_exists(dirname(__FILE__) . '/' . $path) ? "EXISTS" : "NOT FOUND") . "</li>";
        }
        echo "</ul>";
        exit;
    }
}

// Vérifier si la fonction existe
if (function_exists('pdf_builder_load_bootstrap')) {
    echo "<p style='color: green;'>✅ Fonction pdf_builder_load_bootstrap() existe</p>";
} else {
    echo "<p style='color: red;'>❌ Fonction pdf_builder_load_bootstrap() n'existe pas</p>";
}

// Vérifier si la classe existe
if (class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
    echo "<p style='color: green;'>✅ Classe PdfBuilderAdminNew existe</p>";
} else {
    echo "<p style='color: red;'>❌ Classe PdfBuilderAdminNew n'existe pas</p>";
    
    // Vérifier si le fichier existe
    $admin_file = PDF_BUILDER_PLUGIN_DIR . 'src/Admin/PDF_Builder_Admin.php';
    if (file_exists($admin_file)) {
        echo "<p style='color: blue;'>ℹ️ Fichier PDF_Builder_Admin.php existe (" . filesize($admin_file) . " octets)</p>";
        
        // Essayer de charger le fichier manuellement
        echo "<p style='color: blue;'>ℹ️ Tentative de chargement manuel...</p>";
        try {
            require_once $admin_file;
            if (class_exists('PDF_Builder\Admin\PdfBuilderAdminNew')) {
                echo "<p style='color: green;'>✅ Classe chargée avec succès après require_once manuel</p>";
            } else {
                echo "<p style='color: red;'>❌ Classe toujours introuvable après chargement manuel</p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: red;'>❌ Erreur lors du chargement: " . esc_html($e->getMessage()) . "</p>";
        } catch (Error $e) {
            echo "<p style='color: red;'>❌ Erreur fatale lors du chargement: " . esc_html($e->getMessage()) . "</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Fichier PDF_Builder_Admin.php introuvable: " . esc_html($admin_file) . "</p>";
    }
}

// Vérifier si le hook est enregistré
global $wp_filter;
if (isset($wp_filter['init'])) {
    echo "<p style='color: green;'>✅ Hook init existe</p>";

    $has_bootstrap_hook = false;
    foreach ($wp_filter['init']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            // Since bootstrap is loaded directly, check if the callback is the anonymous function from pdf-builder-pro.php
            if (is_callable($callback['function']) && !is_string($callback['function'])) {
                // It's an anonymous function, likely the one loading bootstrap
                $has_bootstrap_hook = true;
                echo "<p style='color: green;'>✅ Hook de chargement bootstrap détecté à la priorité $priority</p>";
                break 2;
            }
        }
    }

    if (!$has_bootstrap_hook) {
        echo "<p style='color: red;'>❌ Hook de chargement bootstrap NON détecté sur init</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Hook init n'existe pas</p>";
}

// Vérifier les erreurs PHP récentes
if (function_exists('error_get_last')) {
    $last_error = error_get_last();
    if ($last_error) {
        echo "<p style='color: orange;'>⚠️ Dernière erreur PHP: " . esc_html($last_error['message']) . " dans " . esc_html($last_error['file']) . ":" . $last_error['line'] . "</p>";
    } else {
        echo "<p style='color: green;'>✅ Aucune erreur PHP récente</p>";
    }
}

echo "<hr>";
echo "<h3>Actions de diagnostic:</h3>";
echo "<p><a href='" . admin_url('admin.php?page=pdf-builder-pro') . "' class='button'>Aller à PDF Builder (si visible)</a></p>";
echo "<p><a href='" . admin_url() . "' class='button'>Retour à l'admin</a></p>";
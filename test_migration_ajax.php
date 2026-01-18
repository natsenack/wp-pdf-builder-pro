<?php
/**
 * Script de test pour vérifier l'action AJAX de migration canvas
 */

// Simuler l'environnement WordPress
define('WP_USE_THEMES', false);
define('WP_ADMIN', true);

// Charger WordPress
$wp_load_paths = [
    __DIR__ . '/../wp-load.php',
    __DIR__ . '/../../wp-load.php',
    '/wp-load.php',
];

$wp_load_found = false;
foreach ($wp_load_paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_load_found = true;
        break;
    }
}

if (!$wp_load_found) {
    die("Erreur: Impossible de trouver wp-load.php\n");
}

echo "=== TEST ACTION AJAX MIGRATION CANVAS ===\n\n";

// Vérifier si l'action est enregistrée
global $wp_filter;
$ajax_actions = isset($wp_filter['wp_ajax_pdf_builder_migrate_canvas_settings']) ? $wp_filter['wp_ajax_pdf_builder_migrate_canvas_settings'] : null;

if ($ajax_actions) {
    echo "✅ Action AJAX 'pdf_builder_migrate_canvas_settings' est enregistrée\n";
    echo "Nombre de callbacks: " . count($ajax_actions->callbacks) . "\n";

    foreach ($ajax_actions->callbacks as $priority => $callbacks) {
        echo "Priorité $priority: " . count($callbacks) . " callback(s)\n";
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                echo "  - " . (is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0]) . "::" . $callback['function'][1] . "\n";
            } else {
                echo "  - " . $callback['function'] . "\n";
            }
        }
    }
} else {
    echo "❌ Action AJAX 'pdf_builder_migrate_canvas_settings' n'est PAS enregistrée\n";
}

// Vérifier si la fonction existe
if (function_exists('pdf_builder_migrate_canvas_settings_ajax')) {
    echo "✅ Fonction 'pdf_builder_migrate_canvas_settings_ajax' existe\n";
} else {
    echo "❌ Fonction 'pdf_builder_migrate_canvas_settings_ajax' n'existe pas\n";
}

// Vérifier si la classe PDF_Builder_Database_Updater existe
if (class_exists('PDF_Builder_Database_Updater')) {
    echo "✅ Classe 'PDF_Builder_Database_Updater' existe\n";

    try {
        $updater = PDF_Builder_Database_Updater::get_instance();
        echo "✅ Instance de PDF_Builder_Database_Updater créée avec succès\n";

        $pending = $updater->get_pending_migrations();
        echo "Migrations en attente: " . (empty($pending) ? 'aucune' : implode(', ', array_keys($pending))) . "\n";

    } catch (Exception $e) {
        echo "❌ Erreur lors de la création de l'instance: " . $e->getMessage() . "\n";
    }

} else {
    echo "❌ Classe 'PDF_Builder_Database_Updater' n'existe pas\n";
}

echo "\n=== FIN DU TEST ===\n";
<?php
/**
 * Script de migration pour créer la table des paramètres canvas
 */

// Simuler l'environnement WordPress
define('WP_USE_THEMES', false);

// Ajuster le chemin selon l'environnement
$wp_load_paths = [
    __DIR__ . '/../wp-load.php',  // Si le script est dans un sous-dossier du site WordPress
    __DIR__ . '/../../wp-load.php', // Si dans un sous-sous-dossier
    '/wp-load.php', // Chemin absolu depuis la racine
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
    die("Erreur: Impossible de trouver wp-load.php. Chemins testés: " . implode(', ', $wp_load_paths) . "\n");
}

echo "=== MIGRATION PDF BUILDER - TABLE PARAMÈTRES CANVAS ===\n\n";

// Inclure le gestionnaire de base de données
if (!class_exists('PDF_Builder_Database_Updater')) {
    require_once plugin_dir_path(dirname(__FILE__)) . 'src/Core/PDF_Builder_Database_Updater.php';
}

try {
    $updater = PDF_Builder_Database_Updater::get_instance();

    echo "Vérification des migrations en attente...\n";
    $pending = $updater->get_pending_migrations();

    if (empty($pending)) {
        echo "Aucune migration en attente.\n";
    } else {
        echo "Migrations en attente trouvées: " . implode(', ', array_keys($pending)) . "\n";

        // Exécuter la migration 1.4.0 si elle est en attente
        if (isset($pending['1.4.0'])) {
            echo "\nExécution de la migration 1.4.0 (Table des paramètres canvas)...\n";

            $result = $updater->run_migration('1.4.0', PDF_Builder_Database_Updater::MIGRATION_UP);

            if ($result) {
                echo "✅ Migration 1.4.0 exécutée avec succès!\n";

                // Vérifier que la table a été créée
                global $wpdb;
                $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_settings'") === $wpdb->prefix . 'pdf_builder_settings';

                if ($table_exists) {
                    echo "✅ Table wp_pdf_builder_settings créée avec succès!\n";

                    // Migrer les paramètres existants
                    echo "\nMigration des paramètres canvas existants...\n";
                    $existing_settings = get_option('pdf_builder_settings', []);
                    $canvas_settings_count = 0;

                    foreach ($existing_settings as $key => $value) {
                        if (strpos($key, 'pdf_builder_canvas_') === 0) {
                            // Le gestionnaire va automatiquement migrer les données
                            $canvas_settings_count++;
                        }
                    }

                    echo "Paramètres canvas migrés: {$canvas_settings_count}\n";

                } else {
                    echo "❌ Erreur: La table n'a pas été créée!\n";
                }

            } else {
                echo "❌ Erreur lors de l'exécution de la migration!\n";
            }
        }
    }

    // Vérifier l'état final
    echo "\n=== ÉTAT FINAL ===\n";
    $current_version = get_option(PDF_Builder_Database_Updater::DB_VERSION_OPTION, '0.0.0');
    echo "Version DB actuelle: {$current_version}\n";

    global $wpdb;
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_settings'") === $wpdb->prefix . 'pdf_builder_settings';
    echo "Table wp_pdf_builder_settings existe: " . ($table_exists ? 'OUI' : 'NON') . "\n";

    if ($table_exists) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}pdf_builder_settings WHERE setting_group = 'canvas'");
        echo "Nombre de paramètres canvas dans la table: {$count}\n";
    }

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DE LA MIGRATION ===\n";
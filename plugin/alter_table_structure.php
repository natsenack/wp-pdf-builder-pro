<?php
/**
 * Script pour modifier la structure de la table wp_pdf_builder_settings
 * pour correspondre à wp_options
 */

// Inclure WordPress
define('WP_USE_THEMES', false);

// Essayer différents chemins possibles pour wp-load.php
$possible_paths = [
    dirname(__FILE__) . '/../../../wp-load.php',  // plugin/../../../wp-load.php
    dirname(__FILE__) . '/../wp-load.php',        // plugin/../wp-load.php
    dirname(__FILE__) . '/../../wp-load.php',     // plugin/../../wp-load.php
    dirname(__FILE__) . '/../../../../wp-load.php', // plugin/../../../../wp-load.php
];

$wp_load_found = false;
foreach ($possible_paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $wp_load_found = true;
        break;
    }
}

if (!$wp_load_found) {
    die("Erreur: Impossible de trouver wp-load.php. Chemins essayés:\n" . implode("\n", $possible_paths) . "\n");
}

function alter_pdf_builder_settings_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'pdf_builder_settings';

    echo "Modification de la structure de la table $table_name...\n";

    // Vérifier si la table existe
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        echo "❌ La table $table_name n'existe pas.\n";
        return false;
    }

    $sql_commands = [
        // 1. Renommer setting_key en option_name
        "ALTER TABLE `$table_name` CHANGE `setting_key` `option_name` VARCHAR(191) NOT NULL",

        // 2. Renommer setting_value en option_value
        "ALTER TABLE `$table_name` CHANGE `setting_value` `option_value` LONGTEXT NOT NULL",

        // 3. Ajouter la colonne autoload
        "ALTER TABLE `$table_name` ADD `autoload` VARCHAR(20) NOT NULL DEFAULT 'yes' AFTER `option_value`",

        // 4. Supprimer les colonnes inutiles
        "ALTER TABLE `$table_name` DROP COLUMN `setting_type`",
        "ALTER TABLE `$table_name` DROP COLUMN `created_at`",
        "ALTER TABLE `$table_name` DROP COLUMN `updated_at`",

        // 5. Renommer id en option_id
        "ALTER TABLE `$table_name` CHANGE `id` `option_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT",

        // 6. Recréer les index
        "ALTER TABLE `$table_name` DROP INDEX `setting_key`",
        "ALTER TABLE `$table_name` DROP INDEX `setting_type`",
        "ALTER TABLE `$table_name` DROP INDEX `updated_at`",
        "ALTER TABLE `$table_name` ADD UNIQUE KEY `option_name` (`option_name`)",
        "ALTER TABLE `$table_name` ADD KEY `autoload` (`autoload`)"
    ];

    $success_count = 0;
    foreach ($sql_commands as $sql) {
        echo "Exécution: " . substr($sql, 0, 50) . "...\n";

        // Certaines commandes peuvent échouer si les colonnes/index n'existent pas
        $result = $wpdb->query($sql);

        if ($result !== false) {
            $success_count++;
            echo "✅ OK\n";
        } else {
            echo "⚠️  Échec (peut-être normal): " . $wpdb->last_error . "\n";
        }
    }

    echo "\n✅ Modification terminée: $success_count commandes exécutées avec succès.\n";

    // Vérifier la nouvelle structure
    echo "\nNouvelle structure de la table:\n";
    $columns = $wpdb->get_results("DESCRIBE $table_name");
    foreach ($columns as $column) {
        echo "- {$column->Field}: {$column->Type} {$column->Null} {$column->Key} {$column->Default} {$column->Extra}\n";
    }

    return true;
}

// Exécuter si appelé directement
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    alter_pdf_builder_settings_table();
    echo "\nScript terminé.\n";
}
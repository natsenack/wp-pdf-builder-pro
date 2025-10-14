<?php
/**
 * Script pour créer la table manquante wp_pdf_builder_order_canvases
 */

// Inclure WordPress
$paths = [
    '../../../wp-load.php',
    '../../../../wp-load.php',
    '../../../../../wp-load.php'
];

$wp_loaded = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once($path);
        $wp_loaded = true;
        echo "<p>✅ WordPress chargé depuis: $path</p>";
        break;
    }
}

if (!$wp_loaded) {
    die("<p>❌ Impossible de trouver wp-load.php</p>");
}

echo "<h1>🔧 Création de la table manquante</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";

global $wpdb;

$table_order_canvases = $wpdb->prefix . 'pdf_builder_order_canvases';

// Vérifier si la table existe déjà
$exists = $wpdb->get_var("SHOW TABLES LIKE '$table_order_canvases'") === $table_order_canvases;

if ($exists) {
    echo "<p class='success'>✅ La table $table_order_canvases existe déjà</p>";
} else {
    echo "<p class='info'>📋 Création de la table $table_order_canvases...</p>";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_order_canvases (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        order_id bigint(20) NOT NULL,
        canvas_data longtext NOT NULL,
        template_id mediumint(9) DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY order_id (order_id),
        KEY template_id (template_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Vérifier si la création a réussi
    $exists_after = $wpdb->get_var("SHOW TABLES LIKE '$table_order_canvases'") === $table_order_canvases;

    if ($exists_after) {
        echo "<p class='success'>✅ Table $table_order_canvases créée avec succès</p>";

        // Vérifier la structure
        $columns = $wpdb->get_results("DESCRIBE $table_order_canvases");
        echo "<p><strong>Colonnes créées :</strong></p>";
        echo "<ul>";
        foreach ($columns as $column) {
            echo "<li>{$column->Field}: {$column->Type}</li>";
        }
        echo "</ul>";

    } else {
        echo "<p class='error'>❌ Échec de la création de la table</p>";
        echo "<p>Dernière erreur MySQL: " . $wpdb->last_error . "</p>";
    }
}

// Vérifier aussi la table templates
$table_templates = $wpdb->prefix . 'pdf_builder_templates';
$templates_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_templates'") === $table_templates;

echo "<h2>📊 État des tables</h2>";
echo "<p>" . ($templates_exists ? '✅' : '❌') . " Table $table_templates " . ($templates_exists ? 'existe' : 'n\'existe pas') . "</p>";
echo "<p>" . ($exists_after ?? $exists ? '✅' : '❌') . " Table $table_order_canvases " . (($exists_after ?? $exists) ? 'existe' : 'n\'existe pas') . "</p>";

if (($exists_after ?? $exists) && $templates_exists) {
    echo "<h2>🎉 Toutes les tables sont prêtes !</h2>";
    echo "<p>Vous pouvez maintenant tester l'aperçu PDF dans le metabox WooCommerce.</p>";
} else {
    echo "<h2>⚠️ Problème de tables</h2>";
    echo "<p>Vérifiez les permissions de base de données ou contactez l'administrateur.</p>";
}

?>
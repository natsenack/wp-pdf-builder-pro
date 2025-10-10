<?php
/**
 * Vérification des templates PDF Builder Pro
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier les permissions admin
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

echo "<h1>🔍 Vérification des Templates PDF Builder Pro</h1>";

// Connexion à la base de données
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

// Vérifier si la table existe
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_templates'") == $table_templates;

if (!$table_exists) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 4px; margin: 20px 0;'>";
    echo "<h3>❌ Table manquante</h3>";
    echo "<p>La table <code>$table_templates</code> n'existe pas.</p>";
    echo "<a href='" . admin_url('admin.php?page=pdf-builder-repair') . "' class='button'>Réparer la base de données</a>";
    echo "</div>";
    exit;
}

// Compter les templates
$total_templates = $wpdb->get_var("SELECT COUNT(*) FROM $table_templates");

echo "<div style='background: #d4edda; color: #155724; padding: 15px; border: 1px solid #c3e6cb; border-radius: 4px; margin: 20px 0;'>";
echo "<h3>✅ Base de données OK</h3>";
echo "<p>Nombre de templates trouvés : <strong>$total_templates</strong></p>";
echo "</div>";

// Lister les templates
if ($total_templates > 0) {
    echo "<h2>Templates existants</h2>";
    $templates = $wpdb->get_results("SELECT id, name, created_at FROM $table_templates ORDER BY created_at DESC");

    echo "<table class='wp-list-table widefat fixed striped'>";
    echo "<thead><tr><th>ID</th><th>Nom</th><th>Date de création</th><th>Actions</th></tr></thead>";
    echo "<tbody>";

    foreach ($templates as $template) {
        echo "<tr>";
        echo "<td>{$template->id}</td>";
        echo "<td>{$template->name}</td>";
        echo "<td>{$template->created_at}</td>";
        echo "<td><a href='" . admin_url('admin.php?page=pdf-builder-editor&template_id=' . $template->id) . "' class='button'>Éditer</a></td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
}

echo "<br><a href='" . admin_url('admin.php?page=pdf-builder-templates') . "' class='button'>Retour aux templates</a>";
?>

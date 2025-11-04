<?php
/**
 * Script de test pour vérifier le cycle de vie des templates après migration vers posts WordPress
 */

// Simuler l'environnement WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Accès non autorisé');
}

echo "<h1>Test du cycle de vie des templates PDF Builder Pro</h1>";

// Test 1: Vérifier que le custom post type est enregistré
echo "<h2>Test 1: Custom Post Type 'pdf_template'</h2>";
$post_types = get_post_types([], 'objects');
if (isset($post_types['pdf_template'])) {
    echo "✅ Custom post type 'pdf_template' enregistré<br>";
} else {
    echo "❌ Custom post type 'pdf_template' non trouvé<br>";
}

// Test 2: Lister les templates existants
echo "<h2>Test 2: Templates existants</h2>";
$templates = get_posts([
    'post_type' => 'pdf_template',
    'posts_per_page' => -1,
    'post_status' => 'publish'
]);

echo "Nombre de templates trouvés: " . count($templates) . "<br>";
foreach ($templates as $template) {
    $template_data = get_post_meta($template->ID, '_pdf_template_data', true);
    $legacy_id = get_post_meta($template->ID, '_pdf_template_legacy_id', true);
    echo "- ID: {$template->ID}, Titre: {$template->post_title}";
    if ($legacy_id) echo " (ancien ID: $legacy_id)";
    echo "<br>";
}

// Test 3: Vérifier la table personnalisée (si elle existe encore)
echo "<h2>Test 3: Table personnalisée wp_pdf_builder_templates</h2>";
global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") === $table;

if ($table_exists) {
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    echo "Table existe encore avec $count enregistrements<br>";

    // Montrer quelques exemples
    $old_templates = $wpdb->get_results("SELECT id, name FROM $table LIMIT 5");
    foreach ($old_templates as $old) {
        echo "- ID: {$old->id}, Nom: {$old->name}<br>";
    }
} else {
    echo "Table personnalisée supprimée ou n'existe pas<br>";
}

echo "<h2>Test terminé</h2>";
?>
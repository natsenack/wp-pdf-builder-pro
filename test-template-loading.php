<?php
/**
 * Script de test pour diagnostiquer le chargement des templates
 */

require_once '../../../wp-load.php';

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

echo "<h1>Test de chargement des templates PDF Builder</h1>";

// Vérifier si la classe principale existe
if (!class_exists('PDF_Builder\Admin\PDF_Builder_Admin')) {
    echo "<p style='color:red'>ERREUR: Classe PDF_Builder_Admin non trouvée</p>";
    exit;
}

// Créer une instance de la classe admin
$admin = new PDF_Builder\Admin\PDF_Builder_Admin();

// Vérifier si le template processor existe
$template_processor = $admin->getTemplateProcessor();
if (!$template_processor) {
    echo "<p style='color:red'>ERREUR: Template processor est null</p>";
    exit;
}

echo "<p style='color:green'>Template processor trouvé</p>";

// Tester le chargement d'un template (utiliser ID 1 par défaut)
$template_id = isset($_GET['id']) ? intval($_GET['id']) : 1;
echo "<h2>Test du chargement du template ID: $template_id</h2>";

try {
    $template_data = $template_processor->loadTemplateRobust($template_id);

    if ($template_data && isset($template_data['elements'])) {
        echo "<p style='color:green'>SUCCÈS: Template chargé avec " . count($template_data['elements']) . " éléments</p>";
        echo "<h3>Données du template:</h3>";
        echo "<pre>" . print_r($template_data, true) . "</pre>";
    } else {
        echo "<p style='color:orange'>AVERTISSEMENT: Template chargé mais pas d'éléments trouvés</p>";
        echo "<pre>" . print_r($template_data, true) . "</pre>";
    }
} catch (Exception $e) {
    echo "<p style='color:red'>ERREUR lors du chargement: " . $e->getMessage() . "</p>";
}

// Lister les templates disponibles
echo "<h2>Templates disponibles en base:</h2>";
global $wpdb;
$table_templates = $wpdb->prefix . 'pdf_builder_templates';

if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") == $table_templates) {
    $templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY id", ARRAY_A);
    if ($templates) {
        echo "<ul>";
        foreach ($templates as $template) {
            echo "<li><a href='?id=" . $template['id'] . "'>Template " . $template['id'] . ": " . $template['name'] . "</a></li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Aucun template trouvé en base de données</p>";
    }
} else {
    echo "<p style='color:red'>Table $table_templates n'existe pas</p>";
}
?>
<?php
require_once 'wp-load.php';
global $wpdb;

// Vérifier les posts de type pdf_template
$pdf_templates = $wpdb->get_results("SELECT ID, post_title, post_status FROM $wpdb->posts WHERE post_type = 'pdf_template'");
echo "Posts de type 'pdf_template' : " . count($pdf_templates) . "\n";
foreach($pdf_templates as $template) {
    echo "ID: {$template->ID} - Titre: {$template->post_title} - Status: {$template->post_status}\n";
}

// Vérifier la table personnalisée pdf_builder_templates
$table_name = $wpdb->prefix . 'pdf_builder_templates';
$custom_templates = $wpdb->get_results("SELECT id, name FROM $table_name");
echo "\nTemplates dans table personnalisée : " . count($custom_templates) . "\n";
foreach($custom_templates as $template) {
    echo "ID: {$template->id} - Nom: {$template->name}\n";
}
?>
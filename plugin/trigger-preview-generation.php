<?php
/**
 * Script pour déclencher la génération des previews via WordPress
 */

// Simuler WordPress
define('ABSPATH', dirname(__DIR__) . '/');
define('WP_PLUGIN_DIR', dirname(__DIR__));

// Charger WordPress
require_once ABSPATH . 'wp-load.php';

// Charger le Template Manager
$template_manager = new PDF_Builder_Template_Manager();

// Récupérer tous les templates
$templates = $template_manager->get_builtin_templates();

echo "Déclenchement génération previews pour " . count($templates) . " templates...\n\n";

foreach ($templates as $template) {
    $template_id = $template['id'];
    echo "Template: {$template_id}\n";
    echo "Preview URL actuelle: " . ($template['preview_url'] ?: 'vide') . "\n";

    // Déclencher la génération
    $template_manager->get_template_preview_url($template_id);

    echo "Génération déclenchée\n\n";
}

echo "Terminé. Les previews seront générées en arrière-plan.\n";
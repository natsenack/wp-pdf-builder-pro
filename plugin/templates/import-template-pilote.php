<?php
/**
 * Script d'import du template pilote
 * Usage: php import-template-pilote.php
 */

// Charger WordPress
require_once '../../../wp-load.php';

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    die('Permissions insuffisantes');
}

echo "=== IMPORT TEMPLATE PILOTE ===\n\n";

// Chemin vers le template JSON
$template_path = __DIR__ . '/examples/facture-pilote.json';

if (!file_exists($template_path)) {
    die("Erreur: Template JSON non trouvé: $template_path\n");
}

// Charger le template JSON
$json_content = file_get_contents($template_path);
if ($json_content === false) {
    die("Erreur: Impossible de lire le fichier JSON\n");
}

$template_data = json_decode($json_content, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erreur JSON: " . json_last_error_msg() . "\n");
}

echo "Template chargé: {$template_data['name']}\n";
echo "Description: {$template_data['description']}\n";
echo "Éléments: " . count($template_data['elements']) . "\n\n";

// Créer un post WordPress pour le template
$post_data = [
    'post_title'   => $template_data['name'],
    'post_content' => wp_json_encode($template_data),
    'post_status'  => 'publish',
    'post_type'    => 'pdf_template',
    'meta_input'   => [
        'template_description' => $template_data['description'],
        'template_version'     => $template_data['version'],
        'canvas_width'         => $template_data['canvasWidth'],
        'canvas_height'        => $template_data['canvasHeight'],
        'element_count'        => count($template_data['elements'])
    ]
];

$post_id = wp_insert_post($post_data);

if (is_wp_error($post_id)) {
    echo "❌ ERREUR lors de la création du post:\n";
    echo $post_id->get_error_message() . "\n";
} else {
    echo "✅ SUCCÈS: Template importé!\n";
    echo "ID du post: $post_id\n";
    echo "Nom: {$template_data['name']}\n";
    echo "Type: pdf_template\n";
}

echo "\n=== FIN DE L'IMPORT ===\n";
<?php
// Script pour créer un template de test avec des éléments
require_once('../../../wp-load.php');

global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';

// Créer un template de test avec des éléments simples
$test_elements = [
    [
        'id' => 1,
        'type' => 'text',
        'text' => 'TEMPLATE DE TEST',
        'x' => 50,
        'y' => 50,
        'fontSize' => 24,
        'color' => '#000000',
        'fontFamily' => 'Arial',
        'fontWeight' => 'bold'
    ],
    [
        'id' => 2,
        'type' => 'text',
        'text' => 'Ceci est un élément de test',
        'x' => 50,
        'y' => 100,
        'fontSize' => 16,
        'color' => '#333333',
        'fontFamily' => 'Arial'
    ],
    [
        'id' => 3,
        'type' => 'rectangle',
        'x' => 50,
        'y' => 150,
        'width' => 200,
        'height' => 50,
        'backgroundColor' => '#f0f0f0',
        'borderColor' => '#cccccc',
        'borderWidth' => 1
    ]
];

$template_data = [
    'elements' => $test_elements,
    'canvasWidth' => 595,
    'canvasHeight' => 842,
    'version' => '2.0.0'
];

$json_data = json_encode($template_data);

if (json_last_error() !== JSON_ERROR_NONE) {
    die('Erreur JSON: ' . json_last_error_msg());
}

$data = [
    'name' => 'Template Test Automatique',
    'template_data' => $json_data,
    'created_at' => current_time('mysql'),
    'updated_at' => current_time('mysql')
];

$result = $wpdb->insert($table, $data);

if ($result) {
    $template_id = $wpdb->insert_id;
    echo "<h1>Template de test créé avec succès!</h1>";
    echo "<p>ID du template: <strong>$template_id</strong></p>";
    echo "<p>Nombre d'éléments: <strong>" . count($test_elements) . "</strong></p>";
    echo "<p>URL de test: <a href='admin.php?page=pdf-builder-editor&template_id=$template_id' target='_blank'>Tester ce template</a></p>";
    echo "<hr>";
    echo "<h2>Données du template:</h2>";
    echo "<pre>" . json_encode($template_data, JSON_PRETTY_PRINT) . "</pre>";
} else {
    echo "<h1 style='color: red;'>Erreur lors de la création du template</h1>";
    echo "<p>Erreur SQL: " . $wpdb->last_error . "</p>";
}
?>
<?php
// Script to create sample template data for testing
require_once('wp-load.php');

global $wpdb;
$table = $wpdb->prefix . 'pdf_builder_templates';

// Sample template data with fictional elements
$sample_template = [
    'elements' => [
        [
            'id' => 1,
            'type' => 'text',
            'text' => 'FACTURE',
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
            'text' => 'Numéro de facture: #12345',
            'x' => 50,
            'y' => 100,
            'fontSize' => 14,
            'color' => '#333333',
            'fontFamily' => 'Arial'
        ],
        [
            'id' => 3,
            'type' => 'text',
            'text' => 'Date: 15/10/2024',
            'x' => 400,
            'y' => 100,
            'fontSize' => 14,
            'color' => '#333333',
            'fontFamily' => 'Arial'
        ],
        [
            'id' => 4,
            'type' => 'rectangle',
            'x' => 50,
            'y' => 150,
            'width' => 500,
            'height' => 30,
            'backgroundColor' => '#f0f0f0',
            'borderColor' => '#cccccc',
            'borderWidth' => 1
        ],
        [
            'id' => 5,
            'type' => 'text',
            'text' => 'Description',
            'x' => 60,
            'y' => 165,
            'fontSize' => 12,
            'color' => '#000000',
            'fontFamily' => 'Arial',
            'fontWeight' => 'bold'
        ],
        [
            'id' => 6,
            'type' => 'text',
            'text' => 'Quantité',
            'x' => 350,
            'y' => 165,
            'fontSize' => 12,
            'color' => '#000000',
            'fontFamily' => 'Arial',
            'fontWeight' => 'bold'
        ],
        [
            'id' => 7,
            'type' => 'text',
            'text' => 'Prix',
            'x' => 450,
            'y' => 165,
            'fontSize' => 12,
            'color' => '#000000',
            'fontFamily' => 'Arial',
            'fontWeight' => 'bold'
        ],
        [
            'id' => 8,
            'type' => 'rectangle',
            'x' => 50,
            'y' => 190,
            'width' => 500,
            'height' => 25,
            'backgroundColor' => '#ffffff',
            'borderColor' => '#cccccc',
            'borderWidth' => 1
        ],
        [
            'id' => 9,
            'type' => 'text',
            'text' => 'Service de développement web',
            'x' => 60,
            'y' => 205,
            'fontSize' => 11,
            'color' => '#000000',
            'fontFamily' => 'Arial'
        ],
        [
            'id' => 10,
            'type' => 'text',
            'text' => '1',
            'x' => 360,
            'y' => 205,
            'fontSize' => 11,
            'color' => '#000000',
            'fontFamily' => 'Arial'
        ],
        [
            'id' => 11,
            'type' => 'text',
            'text' => '1500.00 €',
            'x' => 450,
            'y' => 205,
            'fontSize' => 11,
            'color' => '#000000',
            'fontFamily' => 'Arial'
        ]
    ],
    'canvasWidth' => 595,
    'canvasHeight' => 842,
    'version' => '2.0.0'
];

$template_json = json_encode($sample_template);

$data = [
    'name' => 'Template Facture Exemple',
    'template_data' => $template_json,
    'created_at' => current_time('mysql'),
    'updated_at' => current_time('mysql')
];

$result = $wpdb->insert($table, $data);

if ($result) {
    $template_id = $wpdb->insert_id;
    echo "Template créé avec succès! ID: $template_id\n";
    echo "Éléments: " . count($sample_template['elements']) . "\n";
} else {
    echo "Erreur lors de la création du template: " . $wpdb->last_error . "\n";
}
?>
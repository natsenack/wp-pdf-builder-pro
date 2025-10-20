<?php
// Script pour créer un template de test avec des éléments
// Définition des constantes nécessaires
define('ABSPATH', dirname(__FILE__) . '/');

// Simuler les fonctions WordPress nécessaires
function get_post($id) {
    // Simuler un post existant
    return (object) ['ID' => $id, 'post_title' => 'Test Template'];
}

function update_post_meta($post_id, $key, $value) {
    // Simuler la sauvegarde en fichier pour test
    $filename = __DIR__ . "/test_template_{$post_id}_{$key}.json";
    $result = file_put_contents($filename, json_encode($value, JSON_PRETTY_PRINT));
    echo "Éléments sauvegardés dans: $filename\n";
    return $result !== false;
}

$template_id = 1;

// Éléments de test pour simuler un PDF basique
$test_elements = [
    [
        'id' => 'header-text',
        'type' => 'text',
        'x' => 50,
        'y' => 50,
        'width' => 200,
        'height' => 30,
        'content' => 'PDF Builder Pro - Test Template',
        'fontSize' => 18,
        'fontWeight' => 'bold',
        'color' => '#007cba',
        'textAlign' => 'center'
    ],
    [
        'id' => 'order-info',
        'type' => 'text',
        'x' => 50,
        'y' => 100,
        'width' => 300,
        'height' => 60,
        'content' => 'Commande #{order_number}\nClient: {customer_name}\nDate: {order_date}',
        'fontSize' => 12,
        'color' => '#333333'
    ],
    [
        'id' => 'rectangle-bg',
        'type' => 'rectangle',
        'x' => 40,
        'y' => 40,
        'width' => 515,
        'height' => 802,
        'backgroundColor' => '#ffffff',
        'borderColor' => '#dddddd',
        'borderWidth' => 1
    ]
];

// Sauvegarder les éléments dans les métadonnées du template
$result = update_post_meta($template_id, 'pdf_builder_elements', $test_elements);

if ($result) {
    echo "✅ Éléments de test créés pour le template ID $template_id\n";
    echo "Nombre d'éléments: " . count($test_elements) . "\n";
} else {
    echo "❌ Erreur lors de la création des éléments de test\n";
}
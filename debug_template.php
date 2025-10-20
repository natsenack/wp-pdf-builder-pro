<?php
/**
 * Script pour créer des éléments de test pour le template PDF
 */

// === Configuration ===
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Déterminer le chemin WordPress
$wp_path = dirname(dirname(__FILE__));
if (!file_exists($wp_path . '/wp-config.php')) {
    $wp_path = dirname(dirname(dirname($wp_path)));
}

if (!file_exists($wp_path . '/wp-config.php')) {
    die("❌ Impossible de trouver wp-config.php. Assurez-vous de lancer ce script depuis le répertoire du plugin.\n");
}

// Charger WordPress
require_once($wp_path . '/wp-load.php');

echo "✅ WordPress chargé depuis: $wp_path\n";

// Maintenant exécuter la logique
$template_id = 1;
$template = get_post($template_id);

if (!$template) {
    echo "❌ Template ID 1 n'existe pas. Création d'un template de test...\n";

    $template_id = wp_insert_post([
        'post_title' => 'Template de Test PDF Builder',
        'post_type' => 'pdf_template',
        'post_status' => 'publish',
        'post_content' => 'Template de test pour l\'aperçu PDF'
    ]);

    if (is_wp_error($template_id)) {
        echo "❌ Erreur lors de la création du template: " . $template_id->get_error_message() . "\n";
        exit(1);
    }

    echo "✅ Template créé avec ID: $template_id\n";
} else {
    echo "✅ Template trouvé: {$template->post_title}\n";
}

// Éléments de test basiques
$test_elements = [
    [
        'id' => 'company_info_1',
        'type' => 'company_info',
        'x' => 20,
        'y' => 20,
        'width' => 170,
        'height' => 60,
        'properties' => [
            'show_name' => true,
            'show_address' => true,
            'show_phone' => true,
            'show_email' => true,
            'font_size' => 12,
            'font_family' => 'Arial'
        ]
    ],
    [
        'id' => 'order_number_1',
        'type' => 'order_number',
        'x' => 400,
        'y' => 20,
        'width' => 150,
        'height' => 20,
        'properties' => [
            'prefix' => 'Commande N°',
            'font_size' => 14,
            'font_family' => 'Arial',
            'bold' => true
        ]
    ],
    [
        'id' => 'customer_info_1',
        'type' => 'customer_info',
        'x' => 20,
        'y' => 100,
        'width' => 170,
        'height' => 80,
        'properties' => [
            'show_name' => true,
            'show_address' => true,
            'show_phone' => true,
            'show_email' => true,
            'font_size' => 11,
            'font_family' => 'Arial'
        ]
    ],
    [
        'id' => 'product_table_1',
        'type' => 'product_table',
        'x' => 20,
        'y' => 200,
        'width' => 550,
        'height' => 200,
        'properties' => [
            'show_sku' => true,
            'show_quantity' => true,
            'show_price' => true,
            'show_total' => true,
            'font_size' => 10,
            'font_family' => 'Arial',
            'border' => true
        ]
    ],
    [
        'id' => 'dynamic_text_1',
        'type' => 'dynamic-text',
        'x' => 20,
        'y' => 420,
        'width' => 550,
        'height' => 40,
        'properties' => [
            'text' => 'Merci pour votre commande !',
            'font_size' => 12,
            'font_family' => 'Arial',
            'alignment' => 'center'
        ]
    ]
];

// Sauvegarder les éléments
$result = update_post_meta($template_id, 'pdf_builder_elements', $test_elements);

if ($result) {
    echo "✅ Éléments de test créés avec succès pour le template ID $template_id\n";
    echo "Nombre d'éléments: " . count($test_elements) . "\n";
    echo "Types d'éléments: " . implode(', ', array_unique(array_column($test_elements, 'type'))) . "\n";
} else {
    echo "❌ Erreur lors de la sauvegarde des éléments\n";
}

// Vérifier que les éléments sont bien sauvegardés
$saved_elements = get_post_meta($template_id, 'pdf_builder_elements', true);
echo "\nVérification:\n";
echo "Éléments sauvegardés: " . (is_array($saved_elements) ? count($saved_elements) : 'NON') . "\n";

if (is_array($saved_elements) && count($saved_elements) > 0) {
    echo "✅ Premier élément type: " . $saved_elements[0]['type'] . "\n";
    echo "Éléments IDs: " . implode(', ', array_column($saved_elements, 'id')) . "\n";
} else {
    echo "❌ Aucun élément trouvé après sauvegarde\n";
}
?>
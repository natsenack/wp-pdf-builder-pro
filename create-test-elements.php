<?php
/**
 * Script direct pour créer des éléments de test PDF Builder
 * Accessible via URL: /wp-content/plugins/wp-pdf-builder-pro/create-test-elements.php?run=1
 */

// Charger WordPress
$wp_path = dirname(dirname(dirname(dirname(__FILE__))));
if (!file_exists($wp_path . '/wp-config.php')) {
    $wp_path = dirname(dirname(dirname(__FILE__)));
}
if (!file_exists($wp_path . '/wp-config.php')) {
    die('❌ Impossible de trouver wp-config.php');
}
require_once($wp_path . '/wp-config.php');
require_once($wp_path . '/wp-load.php');

// Vérifier si on doit exécuter
if (!isset($_GET['run']) || $_GET['run'] !== '1') {
    die('🔒 Utilisez ?run=1 pour exécuter le script');
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    die('🔒 Permissions insuffisantes');
}

echo "<h1>PDF Builder - Création d'Éléments de Test</h1>";
echo "<pre>";

$template_id = 1;

// Vérifier si le template existe
$template = get_post($template_id);
if (!$template) {
    echo "📝 Création du template de test...\n";
    $template_id = wp_insert_post([
        'post_title' => 'Template de Test PDF Builder',
        'post_type' => 'pdf_template',
        'post_status' => 'publish',
        'post_content' => 'Template de test pour l\'aperçu PDF'
    ]);

    if (is_wp_error($template_id)) {
        echo "❌ Erreur création template: " . $template_id->get_error_message() . "\n";
        exit;
    } else {
        echo "✅ Template créé avec ID: $template_id\n";
    }
} else {
    echo "✅ Template trouvé: {$template->post_title}\n";
}

// Éléments de test
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

echo "📦 Sauvegarde des éléments de test...\n";
$result = update_post_meta($template_id, 'pdf_builder_elements', $test_elements);

if ($result) {
    echo "✅ Éléments de test créés avec succès !\n";
    echo "- Template ID: $template_id\n";
    echo "- Nombre d'éléments: " . count($test_elements) . "\n";
    echo "- Types: " . implode(', ', array_unique(array_column($test_elements, 'type'))) . "\n";
} else {
    echo "❌ Erreur lors de la sauvegarde des éléments\n";
    exit;
}

// Vérifier
$saved_elements = get_post_meta($template_id, 'pdf_builder_elements', true);
echo "\n🔍 Vérification:\n";
echo "- Éléments sauvegardés: " . (is_array($saved_elements) ? count($saved_elements) : 'AUCUN') . "\n";

if (is_array($saved_elements) && count($saved_elements) > 0) {
    echo "- Premier élément: " . $saved_elements[0]['type'] . "\n";
}

echo "</pre>";
echo "<h2>🎉 Test terminé avec succès !</h2>";
echo "<p>Vous pouvez maintenant tester l'aperçu PDF dans la commande WooCommerce ID 9275.</p>";
echo "<p><a href='/wp-admin/post.php?post=9275&action=edit' style='background:#007cba;color:white;padding:10px 20px;text-decoration:none;border-radius:4px;'>Aller à la commande de test</a></p>";
?>
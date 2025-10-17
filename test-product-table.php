<?php
// Définir les constantes nécessaires pour éviter la protection d'accès direct
define('ABSPATH', __DIR__ . '/');
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

require_once 'includes/pdf-generator.php';

echo "=== Test génération PDF avec tableau produits ===\n";

$generator = new PDF_Generator();

$elements = [
    [
        'type' => 'product_table',
        'x' => 10,
        'y' => 10,
        'width' => 190,
        'height' => 100,
        'tableStyle' => 'default',
        'showHeaders' => true,
        'showBorders' => true,
        'columns' => [
            'image' => false,
            'name' => true,
            'sku' => false,
            'quantity' => true,
            'price' => true,
            'total' => true
        ]
    ]
];

try {
    echo "Génération du PDF...\n";
    $pdf_content = $generator->generate($elements);
    echo '✅ PDF généré avec succès, taille: ' . strlen($pdf_content) . ' octets' . PHP_EOL;

    // Vérifier les erreurs
    $errors = $generator->get_errors();
    if (!empty($errors)) {
        echo "Erreurs détectées:\n";
        foreach ($errors as $error) {
            echo "- $error\n";
        }
    } else {
        echo "Aucune erreur détectée.\n";
    }

} catch (Exception $e) {
    echo '❌ Erreur lors de la génération: ' . $e->getMessage() . PHP_EOL;
    echo 'Stack trace: ' . $e->getTraceAsString() . PHP_EOL;
}

echo "=== Fin du test ===\n";
?>
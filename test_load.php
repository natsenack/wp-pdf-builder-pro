<?php
echo "=== TEST DIRECT loadTemplateRobust ===\n";
require_once 'plugin/bootstrap.php';
try {
    $admin = new PDF_Builder_Admin();
    $processor = $admin->getTemplateProcessor();

    // Tester avec template ID 1
    $data = $processor->loadTemplateRobust(1);
    echo "Données retournées pour template 1:\n";
    if ($data && isset($data['name'])) {
        echo "Nom trouvé: " . $data['name'] . "\n";
    } else {
        echo "AUCUN NOM TROUVÉ\n";
        if (is_array($data)) {
            echo "Clés disponibles: " . implode(', ', array_keys($data)) . "\n";
        } else {
            echo "Données non-array\n";
        }
    }
    echo "Données complètes: " . json_encode($data, JSON_PRETTY_PRINT) . "\n";
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
?>
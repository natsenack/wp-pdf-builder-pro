<?php
/**
 * Test simple de chargement des classes
 */

echo "=== TEST CHARGEMENT CLASSES ===\n\n";

try {
    echo "1. Test inclusion pdf-preview-generator.php...\n";
    require_once __DIR__ . '/includes/pdf-preview-generator.php';
    echo "   ✅ Fichier inclus\n";

    echo "2. Test création PDF_Preview_Generator...\n";
    $preview_gen = new PDF_Preview_Generator();
    echo "   ✅ Classe instanciée\n";

    echo "3. Vérification du générateur...\n";
    if ($preview_gen->generator) {
        echo "   ✅ Générateur PDF disponible\n";
    } else {
        echo "   ⚠️ Générateur PDF null (fallback sera utilisé)\n";
    }

} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "   ❌ Erreur fatale: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . " ligne " . $e->getLine() . "\n";
}

echo "\n=== FIN TEST ===\n";
?>
<?php
// Script de diagnostic pour vérifier les modifications du nonce
echo "=== DIAGNOSTIC PDF BUILDER ===\n";

// Vérifier si la fonction ajax_load_canvas_elements existe et contient nos modifications
$admin_file = __DIR__ . '/includes/classes/class-pdf-builder-admin.php';
if (file_exists($admin_file)) {
    $content = file_get_contents($admin_file);

    echo "✅ Fichier admin trouvé\n";

    if (strpos($content, '_cachebust_') !== false) {
        echo "✅ Modifications _cachebust_ présentes\n";
    } else {
        echo "❌ Modifications _cachebust_ ABSENTES\n";
    }

    if (strpos($content, 'wp_create_nonce(\'pdf_builder_canvas_v3_') !== false) {
        echo "✅ Nouveau nonce trouvé\n";
    } else {
        echo "❌ Nouveau nonce ABSENT\n";
    }

    // Compter les occurrences
    $cachebust_count = substr_count($content, '_cachebust_');
    echo "Nombre d'occurrences _cachebust_: $cachebust_count\n";

} else {
    echo "❌ Fichier admin NON trouvé: $admin_file\n";
}

echo "\n=== TEST NONCE ===\n";
// Tester la génération du nonce
if (function_exists('wp_create_nonce')) {
    $test_nonce = wp_create_nonce('pdf_builder_canvas_v3_' . 1 . '_cachebust_' . time());
    echo "Test nonce généré: " . substr($test_nonce, 0, 10) . "...\n";
    echo "Longueur: " . strlen($test_nonce) . "\n";
} else {
    echo "❌ Fonction wp_create_nonce non disponible\n";
}

echo "\n=== FIN DIAGNOSTIC ===";
?>
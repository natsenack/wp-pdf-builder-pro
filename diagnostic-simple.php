<?php
// Script de diagnostic ULTRA SIMPLE
echo "=== DIAGNOSTIC ULTRA SIMPLE ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Script: " . __FILE__ . "\n";
echo "Dossier: " . __DIR__ . "\n";

// Test fichiers de base
echo "\n--- FICHIERS DE BASE ---\n";
$files = ['diagnostic-direct.php', 'pdf-builder-pro.php', 'bootstrap.php'];
foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "$file: " . (file_exists($path) ? "EXISTS" : "MISSING") . "\n";
}

// Test chargement plugin (TRÈS SIMPLE)
echo "\n--- TEST CHARGEMENT PLUGIN ---\n";
try {
    echo "Tentative d'inclusion du plugin...\n";
    @include_once __DIR__ . '/pdf-builder-pro.php';
    echo "Inclusion terminée\n";
} catch (Throwable $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

// Test classes de base
echo "\n--- CLASSES DE BASE ---\n";
$classes = ['PDF_Builder_Update_Manager', 'PDF_Builder_Metrics_Analytics'];
foreach ($classes as $class) {
    echo "$class: " . (class_exists($class) ? "EXISTS" : "MISSING") . "\n";
}

echo "\n=== FIN DU DIAGNOSTIC ULTRA SIMPLE ===\n";
?>
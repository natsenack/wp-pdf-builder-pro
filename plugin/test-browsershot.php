<?php
/**
 * Test Browsershot/Puppeteer
 * 
 * Exécuter: php test-browsershot.php
 */

require __DIR__ . '/vendor/autoload.php';

use Spatie\Browsershot\Browsershot;

echo "🧪 Test de Browsershot avec Puppeteer\n";
echo "=====================================\n\n";

try {
    $html = '<html><body><h1 style="color: blue; font-size: 48px;">✅ Browsershot fonctionne!</h1></body></html>';
    $output = __DIR__ . '/test-browsershot-output.png';
    
    echo "1. Génération de l'image...\n";
    
    Browsershot::html($html)
        ->windowSize(800, 600)
        ->save($output);
    
    if (file_exists($output)) {
        $size = filesize($output);
        echo "✅ Image générée avec succès!\n";
        echo "   Fichier: $output\n";
        echo "   Taille: " . number_format($size) . " octets\n";
    } else {
        echo "❌ Fichier non créé\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "   Fichier: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

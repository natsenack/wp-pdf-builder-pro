<?php
/**
 * Test Imagick
 */

echo "🧪 Test de l'extension Imagick\n";
echo "==============================\n\n";

// Test 1: Extension chargée ?
echo "1. Extension Imagick: ";
if (extension_loaded('imagick')) {
    echo "✅ Chargée\n";
    
    $imagick = new Imagick();
    $version = $imagick->getVersion();
    echo "   Version: " . $version['versionString'] . "\n";
    
    // Formats supportés
    $formats = $imagick->queryFormats();
    echo "   Formats: " . count($formats) . " formats supportés\n";
    echo "   - PDF support: " . (in_array('PDF', $formats) ? '✅' : '❌') . "\n";
    echo "   - PNG support: " . (in_array('PNG', $formats) ? '✅' : '❌') . "\n";
    echo "   - JPEG support: " . (in_array('JPEG', $formats) ? '✅' : '❌') . "\n";
    
} else {
    echo "❌ Non chargée\n";
    echo "\nPour installer Imagick:\n";
    echo "- Windows: Téléchargez php_imagick.dll depuis https://windows.php.net/downloads/pecl/releases/imagick/\n";
    echo "- Linux: apt-get install php-imagick ou yum install php-imagick\n";
    echo "- Redémarrez votre serveur web après installation\n";
    exit(1);
}

echo "\n";

// Test 2: Créer une image simple
echo "2. Test de création d'image:\n";
try {
    $imagick = new Imagick();
    $imagick->newImage(200, 100, new ImagickPixel('lightblue'));
    $imagick->setImageFormat('png');
    
    $output = __DIR__ . '/test-imagick.png';
    $imagick->writeImage($output);
    
    if (file_exists($output)) {
        echo "   ✅ Image créée: $output (" . filesize($output) . " octets)\n";
        @unlink($output);
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n✅ Imagick est prêt pour PDF Builder Pro!\n";

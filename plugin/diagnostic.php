<?php
/**
 * Diagnostic pour l'erreur fatale du plugin PDF Builder Pro
 */

// Basique WordPress loading
define('WP_USE_THEMES', false);
require('wp-load.php');

error_log('========== PDF Builder Pro Diagnostic ==========');

// 1. Vérifier les constantes du plugin
echo "1. Vérification des constantes...\n";
$constants = [
    'PDF_BUILDER_PLUGIN_FILE',
    'PDF_BUILDER_PLUGIN_DIR',
    'PDF_BUILDER_PLUGIN_URL',
    'ABSPATH',
];

foreach ($constants as $const) {
    if (defined($const)) {
        echo "   ✅ $const = " . (strlen(constant($const)) > 50 ? substr(constant($const), 0, 50) . '...' : constant($const)) . "\n";
    } else {
        echo "   ❌ $const NOT DEFINED\n";
    }
}

// 2. Vérifier les fichiers critiques
echo "\n2. Vérification des fichiers critiques...\n";
$plugin_dir = WP_CONTENT_DIR . '/plugins/wp-pdf-builder-pro/';
$critical_files = [
    'pdf-builder-pro.php',
    'bootstrap.php',
    'src/Core/core/autoloader.php',
    'src/Core/PDF_Builder_Unified_Ajax_Handler.php',
    'src/Core/PDF_Builder_Deactivation_Handler.php',
];

foreach ($critical_files as $file) {
    $path = $plugin_dir . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "   ✅ $file ($size bytes)\n";
    } else {
        echo "   ❌ MANQUANT: $file\n";
    }
}

// 3. Vérifier les erreurs PHP
echo "\n3. Vérification des erreurs PHP...\n";
$errors = error_get_last();
if ($errors) {
    echo "   ⚠️  Erreur détectée: " . $errors['message'] . "\n";
    echo "   📄 Fichier: " . $errors['file'] . "\n";
    echo "   📍 Ligne: " . $errors['line'] . "\n";
} else {
    echo "   ✅ Pas d'erreurs PHP\n";
}

// 4. Tester le chargement du plugin
echo "\n4. Test de chargement du plugin...\n";
try {
    $plugin_file = $plugin_dir . 'pdf-builder-pro.php';
    if (file_exists($plugin_file)) {
        ob_start();
        require_once($plugin_file);
        $output = ob_get_clean();
        
        if ($output) {
            echo "   ⚠️  Output lors du chargement: " . substr($output, 0, 100) . "\n";
        } else {
            echo "   ✅ Plugin chargé sans erreur\n";
        }
    }
} catch (Exception $e) {
    echo "   ❌ Exception: " . $e->getMessage() . "\n";
} catch (Throwable $t) {
    echo "   ❌ Erreur fatale: " . $t->getMessage() . "\n";
}

echo "\n========== Fin du diagnostic ==========\n";
?>

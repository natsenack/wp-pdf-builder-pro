<?php
/**
 * Test rapide de l'autoloader pour SettingsManager
 */

define('PHPUNIT_RUNNING', true);
define('ABSPATH', '/var/www/nats/data/www/threeaxe.fr/');

echo "Testing SettingsManager loading...\n";

try {
    require_once __DIR__ . '/core/autoloader.php';

    if (class_exists('PDF_Builder\Core\PdfBuilderAutoloader')) {
        \PDF_Builder\Core\PdfBuilderAutoloader::init(__DIR__ . '/');
        echo "Autoloader initialized\n";
    }

    if (class_exists('PDF_Builder\Admin\Managers\SettingsManager')) {
        echo "✅ SettingsManager loaded successfully\n";
        $settings = new \PDF_Builder\Admin\Managers\SettingsManager(null);
        echo "✅ SettingsManager instantiated successfully\n";
    } else {
        echo "❌ SettingsManager not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
?>
<?php
// Script pour déclencher manuellement la vérification d'expiration
define('PHPUNIT_RUNNING', true);

// Simuler un environnement WordPress minimal
if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/');
}

// Charger les dépendances nécessaires
require_once __DIR__ . '/plugin/bootstrap.php';
require_once __DIR__ . '/plugin/src/License/license-expiration-handler.php';

echo "=== DÉCLENCHEMENT MANUEL DE checkLicenseExpiration ===\n\n";

// Appeler la fonction
\PDFBuilderPro\License\License_Expiration_Handler::checkLicenseExpiration();

echo "\n=== FIN DU DÉCLENCHEMENT ===\n";

// Vérifier les logs (si disponibles)
if (function_exists('pdf_builder_get_option')) {
    $test_key_after = pdf_builder_get_option('pdf_builder_license_test_key', 'NOT_FOUND');
    $license_key_after = pdf_builder_get_option('pdf_builder_license_key', 'NOT_FOUND');
    echo "\nÉtat après vérification:\n";
    echo "Test key: " . ($test_key_after ?: 'NOT_FOUND') . "\n";
    echo "License key: " . ($license_key_after ?: 'NOT_FOUND') . "\n";
}
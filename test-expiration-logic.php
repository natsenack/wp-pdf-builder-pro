<?php
// Test script pour vérifier la logique d'expiration des licences
define('PHPUNIT_RUNNING', true);
require_once __DIR__ . '/plugin/bootstrap.php';

echo "=== TEST DE LA LOGIQUE D'EXPIRATION ===\n\n";

echo "1. État actuel des options :\n";
$license_expires = pdf_builder_get_option('pdf_builder_license_expires', '');
$license_status = pdf_builder_get_option('pdf_builder_license_status', 'free');
$test_key_expires = pdf_builder_get_option('pdf_builder_license_test_key_expires', '');
$test_key = pdf_builder_get_option('pdf_builder_license_test_key', '');

echo "License expires: " . ($license_expires ?: 'NOT_SET') . "\n";
echo "License status: " . $license_status . "\n";
echo "Test key expires: " . ($test_key_expires ?: 'NOT_SET') . "\n";
echo "Test key: " . ($test_key ?: 'NOT_SET') . "\n\n";

$now = new DateTime();
echo "Date actuelle: " . $now->format('Y-m-d H:i:s') . "\n\n";

echo "2. Test licence premium :\n";
if (!empty($license_expires) && $license_status !== 'free') {
    $expires_date = new DateTime($license_expires);
    echo "Date d'expiration licence: " . $expires_date->format('Y-m-d H:i:s') . "\n";
    echo "Licence expirée ? " . ($now > $expires_date ? 'OUI' : 'NON') . "\n";

    if ($now > $expires_date) {
        echo "SUPPRESSION de la clé de licence premium...\n";
        $result = pdf_builder_delete_option('pdf_builder_license_key');
        echo "Résultat: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
        pdf_builder_update_option('pdf_builder_license_status', 'expired');
        echo "Statut mis à 'expired'\n";
    }
} else {
    echo "Conditions non remplies pour vérifier la licence premium\n";
}

echo "\n3. Test clé de test :\n";
if (!empty($test_key_expires) && !empty($test_key)) {
    $expires_date = new DateTime($test_key_expires);
    echo "Date d'expiration clé test: " . $expires_date->format('Y-m-d H:i:s') . "\n";
    echo "Clé test expirée ? " . ($now > $expires_date ? 'OUI' : 'NON') . "\n";

    if ($now > $expires_date) {
        echo "SUPPRESSION de la clé de test...\n";
        $result1 = pdf_builder_delete_option('pdf_builder_license_test_key');
        $result2 = pdf_builder_delete_option('pdf_builder_license_test_key_expires');
        $result3 = pdf_builder_delete_option('pdf_builder_license_test_mode_enabled');
        echo "Résultat test_key: " . ($result1 ? 'SUCCESS' : 'FAILED') . "\n";
        echo "Résultat test_expires: " . ($result2 ? 'SUCCESS' : 'FAILED') . "\n";
        echo "Résultat test_mode: " . ($result3 ? 'SUCCESS' : 'FAILED') . "\n";
        pdf_builder_update_option('pdf_builder_license_status', 'free');
        echo "Statut mis à 'free'\n";
    }
} else {
    echo "Conditions non remplies pour vérifier la clé de test\n";
    echo "test_key_expires vide: " . (empty($test_key_expires) ? 'OUI' : 'NON') . "\n";
    echo "test_key vide: " . (empty($test_key) ? 'OUI' : 'NON') . "\n";
}

echo "\n4. Vérification finale :\n";
$license_key_after = pdf_builder_get_option('pdf_builder_license_key', 'NOT_FOUND');
$test_key_after = pdf_builder_get_option('pdf_builder_license_test_key', 'NOT_FOUND');
$license_status_after = pdf_builder_get_option('pdf_builder_license_status', 'free');

echo "License key après: " . ($license_key_after ?: 'NOT_FOUND') . "\n";
echo "Test key après: " . ($test_key_after ?: 'NOT_FOUND') . "\n";
echo "License status après: " . $license_status_after . "\n";

echo "\n=== TEST TERMINÉ ===\n";
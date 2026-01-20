<?php
// Test script pour vérifier les fonctions de licence
define('PHPUNIT_RUNNING', true);
require_once __DIR__ . '/plugin/bootstrap.php';

echo "=== TEST DES FONCTIONS DE LICENCE ===\n\n";

echo "1. État actuel des options :\n";
$test_key = pdf_builder_get_option('pdf_builder_license_test_key', 'NOT_FOUND');
$test_expires = pdf_builder_get_option('pdf_builder_license_test_key_expires', 'NOT_FOUND');
$license_key = pdf_builder_get_option('pdf_builder_license_key', 'NOT_FOUND');
$license_status = pdf_builder_get_option('pdf_builder_license_status', 'NOT_FOUND');

echo "Test key: " . ($test_key ?: 'NOT_FOUND') . "\n";
echo "Test expires: " . ($test_expires ?: 'NOT_FOUND') . "\n";
echo "License key: " . ($license_key ?: 'NOT_FOUND') . "\n";
echo "License status: " . ($license_status ?: 'NOT_FOUND') . "\n\n";

echo "2. Test de suppression des clés de test :\n";
if ($test_key !== 'NOT_FOUND' && !empty($test_key)) {
    echo "Suppression de la clé de test...\n";
    $result1 = pdf_builder_delete_option('pdf_builder_license_test_key');
    $result2 = pdf_builder_delete_option('pdf_builder_license_test_key_expires');
    echo "Résultat suppression test_key: " . ($result1 ? 'SUCCESS' : 'FAILED') . "\n";
    echo "Résultat suppression test_expires: " . ($result2 ? 'SUCCESS' : 'FAILED') . "\n";
} else {
    echo "Aucune clé de test trouvée à supprimer\n";
}

echo "\n3. Test de suppression de la clé de licence :\n";
if ($license_key !== 'NOT_FOUND' && !empty($license_key)) {
    echo "Suppression de la clé de licence...\n";
    $result = pdf_builder_delete_option('pdf_builder_license_key');
    echo "Résultat suppression license_key: " . ($result ? 'SUCCESS' : 'FAILED') . "\n";
} else {
    echo "Aucune clé de licence trouvée à supprimer\n";
}

echo "\n4. Vérification après suppression :\n";
$test_key_after = pdf_builder_get_option('pdf_builder_license_test_key', 'NOT_FOUND');
$test_expires_after = pdf_builder_get_option('pdf_builder_license_test_key_expires', 'NOT_FOUND');
$license_key_after = pdf_builder_get_option('pdf_builder_license_key', 'NOT_FOUND');

echo "Test key après: " . ($test_key_after ?: 'NOT_FOUND') . "\n";
echo "Test expires après: " . ($test_expires_after ?: 'NOT_FOUND') . "\n";
echo "License key après: " . ($license_key_after ?: 'NOT_FOUND') . "\n";

echo "\n=== TEST TERMINÉ ===\n";
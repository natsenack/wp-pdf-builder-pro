<?php
/**
 * Test script pour déboguer la validation JSON
 */

// Simuler les données qui arrivent côté serveur
echo "=== TEST DE VALIDATION JSON ===\n\n";

// Test 1: JSON valide simple
$test_json_1 = '[{"id":"test1","type":"text","content":"Hello","x":10,"y":10,"width":100,"height":50}]';
echo "Test 1 - JSON simple valide:\n";
echo "Longueur: " . strlen($test_json_1) . "\n";
echo "Premiers 100 chars: " . substr($test_json_1, 0, 100) . "\n";
echo "Début: [" . substr($test_json_1, 0, 1) . "] Fin: [" . substr($test_json_1, -1) . "]\n";

$decoded = json_decode($test_json_1, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON valide, éléments: " . count($decoded) . "\n";
} else {
    echo "❌ Erreur JSON: " . json_last_error_msg() . "\n";
}
echo "\n";

// Test 2: Simuler un JSON avec caractères spéciaux
$test_json_2 = '[{"id":"test2","type":"text","content":"Hello & World","x":10,"y":10,"width":100,"height":50}]';
echo "Test 2 - JSON avec entités HTML:\n";
echo "Longueur: " . strlen($test_json_2) . "\n";
echo "Contenu: " . $test_json_2 . "\n";

$decoded2 = json_decode($test_json_2, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON valide\n";
} else {
    echo "❌ Erreur JSON: " . json_last_error_msg() . "\n";
}
echo "\n";

// Test 3: Simuler un JSON URL-encoded
$test_json_3 = urlencode($test_json_1);
echo "Test 3 - JSON URL-encoded:\n";
echo "Longueur encodée: " . strlen($test_json_3) . "\n";
echo "Contenu encodé: " . substr($test_json_3, 0, 100) . "...\n";

$decoded3 = urldecode($test_json_3);
echo "Après décodage URL - Longueur: " . strlen($decoded3) . "\n";
echo "Contenu décodé: " . substr($decoded3, 0, 100) . "\n";

$decoded3_final = json_decode($decoded3, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON valide après décodage URL\n";
} else {
    echo "❌ Erreur JSON après décodage URL: " . json_last_error_msg() . "\n";
}
echo "\n";

// Test 4: Simuler un JSON avec des caractères problématiques
$test_json_4 = '[{"id":"test4","type":"text","content":"Test avec caractères spéciaux: àéèçñ","x":10,"y":10,"width":100,"height":50}]';
echo "Test 4 - JSON avec caractères UTF-8:\n";
echo "Contenu: " . $test_json_4 . "\n";

$decoded4 = json_decode($test_json_4, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON UTF-8 valide\n";
} else {
    echo "❌ Erreur JSON UTF-8: " . json_last_error_msg() . "\n";
}
echo "\n";

echo "=== FIN DES TESTS ===\n";
?>
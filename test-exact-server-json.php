<?php
/**
 * Test avec le JSON exact du fichier de debug
 */

// Lire le JSON exact du fichier de debug
$json_content = file_get_contents('debug_received_json_server.txt');

// Extraire seulement la partie JSON (après "Full content:\n")
$lines = explode("\n", $json_content);
$json = '';
$found_full_content = false;

foreach ($lines as $line) {
    if (strpos($line, 'Full content:') === 0) {
        $found_full_content = true;
        $json = substr($line, 13); // Enlever "Full content:"
        continue;
    }
    if ($found_full_content) {
        $json .= $line;
    }
}

$json = trim($json);

echo "=== TEST AVEC JSON EXACT DU SERVEUR ===\n\n";
echo "JSON lu du fichier:\n$json\n\n";
echo "Longueur: " . strlen($json) . "\n\n";

$decoded = json_decode($json, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON valide côté serveur !\n";
    echo "Nombre d'éléments: " . count($decoded) . "\n";
    echo "Types d'éléments: " . $decoded[0]['type'] . ", " . $decoded[1]['type'] . "\n";
} else {
    echo "❌ JSON invalide côté serveur: " . json_last_error_msg() . "\n";

    // Tester si c'est un problème d'encodage
    echo "\nTest de conversion d'encodage...\n";
    $json_utf8 = mb_convert_encoding($json, 'UTF-8', mb_detect_encoding($json));
    $decoded2 = json_decode($json_utf8, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ JSON valide après conversion UTF-8\n";
    } else {
        echo "❌ Toujours invalide après conversion UTF-8\n";
    }
}

echo "\n=== COMPARAISON AVEC PHP LOCAL ===\n\n";

// Test avec le même JSON en PHP local
$test_json = '[{"id":"test","type":"text","content":"Hello"}]';
$decoded_test = json_decode($test_json, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON simple fonctionne en local\n";
} else {
    echo "❌ Même JSON simple échoue en local: " . json_last_error_msg() . "\n";
}

echo "\n=== FIN ===\n";
?>
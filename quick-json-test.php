<?php
/**
 * Test rapide du problème JSON
 */

// Test des données des fichiers de debug
$debug_files = [
    'debug_received_json_server.txt',
    'debug_raw_post_elements_server.txt'
];

echo "=== ANALYSE RAPIDE DU PROBLÈME JSON ===\n\n";

foreach ($debug_files as $file) {
    $file_path = __DIR__ . '/' . $file;
    if (file_exists($file_path)) {
        echo "📄 Analyse de: {$file}\n";
        $content = file_get_contents($file_path);

        // Chercher le début du JSON
        $json_start = strpos($content, '[');
        if ($json_start !== false) {
            $json_content = substr($content, $json_start);
            echo "🔍 JSON trouvé à la position: {$json_start}\n";
            echo "📏 Longueur du JSON: " . strlen($json_content) . "\n";

            // Tester le JSON
            $json_test = json_decode($json_content, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "✅ JSON valide - " . count($json_test) . " éléments\n";
            } else {
                echo "❌ Erreur JSON: " . json_last_error_msg() . "\n";
                echo "🔍 Code d'erreur: " . json_last_error() . "\n";

                // Analyser les premiers caractères
                $first_chars = substr($json_content, 0, 50);
                echo "🔤 Premiers caractères: " . htmlspecialchars($first_chars) . "\n";

                // Chercher des caractères problématiques
                if (strpos($json_content, '\'') !== false) {
                    echo "⚠️  Apostrophes simples détectées (devraient être des guillemets)\n";
                }
                if (strpos($json_content, '""') !== false) {
                    echo "⚠️  Guillemets doubles consécutifs détectés\n";
                }
            }
        } else {
            echo "❌ Aucun JSON trouvé dans le fichier\n";
        }
        echo "\n";
    } else {
        echo "❌ Fichier {$file} non trouvé\n\n";
    }
}

// Test de simulation
echo "🧪 TEST DE SIMULATION\n";
$test_data = [
    'elements' => [
        [
            'id' => 'element_1',
            'type' => 'text',
            'x' => 20,
            'y' => 20,
            'width' => 200,
            'height' => 50,
            'text' => 'Test élément',
            'backgroundColor' => 'transparent'
        ]
    ],
    'canvasWidth' => 600,
    'canvasHeight' => 800
];

$json_string = json_encode($test_data);
echo "📤 JSON de test généré: " . strlen($json_string) . " caractères\n";
echo "🔍 Aperçu: " . substr($json_string, 0, 100) . "...\n";

$decoded = json_decode($json_string, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "✅ JSON de test valide\n";
} else {
    echo "❌ JSON de test invalide: " . json_last_error_msg() . "\n";
}

echo "\n=== FIN DE L'ANALYSE ===\n";
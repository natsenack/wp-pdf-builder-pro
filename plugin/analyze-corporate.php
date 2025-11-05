<?php
echo "🔍 Analyse détaillée du fichier corporate.json\n";
echo "==============================================\n\n";

$file = __DIR__ . '/templates/builtin/corporate.json';

if (!file_exists($file)) {
    echo "❌ Fichier corporate.json non trouvé\n";
    exit(1);
}

// Test JSON basique
$content = file_get_contents($file);
$data = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erreur JSON: " . json_last_error_msg() . "\n";
    echo "Contenu autour de l'erreur:\n";

    // Trouver la ligne approximative de l'erreur
    $lines = explode("\n", $content);
    $error_pos = json_last_error() === JSON_ERROR_SYNTAX ? strlen($content) - 100 : 0;

    $char_count = 0;
    for ($i = 0; $i < count($lines); $i++) {
        $char_count += strlen($lines[$i]) + 1; // +1 pour \n
        if ($char_count > $error_pos) {
            echo "Ligne " . ($i + 1) . ": " . trim($lines[$i]) . "\n";
            break;
        }
    }
    exit(1);
}

echo "✅ JSON syntaxiquement valide\n";
echo "📊 Structure générale:\n";
echo "   - Version: " . ($data['version'] ?? 'manquante') . "\n";
echo "   - Name: " . ($data['name'] ?? 'manquant') . "\n";
echo "   - Canvas: " . ($data['canvasWidth'] ?? '?') . "x" . ($data['canvasHeight'] ?? '?') . "\n";
echo "   - Éléments: " . count($data['elements'] ?? []) . "\n\n";

// Analyser les éléments
$elements = $data['elements'] ?? [];
$types = [];
$errors = [];

echo "🔧 Analyse des éléments:\n";

foreach ($elements as $index => $element) {
    $id = $element['id'] ?? 'unknown';
    $type = $element['type'] ?? 'unknown';

    $types[$type] = ($types[$type] ?? 0) + 1;

    // Vérifications de base
    if (!isset($element['id'])) {
        $errors[] = "Élément $index: propriété 'id' manquante";
    }
    if (!isset($element['type'])) {
        $errors[] = "Élément $index: propriété 'type' manquante";
    }
    if (!isset($element['x']) || !is_numeric($element['x'])) {
        $errors[] = "Élément $index ($id): propriété 'x' invalide";
    }
    if (!isset($element['y']) || !is_numeric($element['y'])) {
        $errors[] = "Élément $index ($id): propriété 'y' invalide";
    }
    if (!isset($element['width']) || !is_numeric($element['width'])) {
        $errors[] = "Élément $index ($id): propriété 'width' invalide";
    }
    if (!isset($element['height']) || !is_numeric($element['height'])) {
        $errors[] = "Élément $index ($id): propriété 'height' invalide";
    }

    // Vérifier les propriétés selon le type
    if ($type === 'text' && !isset($element['properties']['text'])) {
        $errors[] = "Élément $index ($id): texte manquant pour type 'text'";
    }
}

echo "Types d'éléments trouvés:\n";
foreach ($types as $type => $count) {
    echo "   $type: $count\n";
}

echo "\n";

if (empty($errors)) {
    echo "✅ Aucune erreur structurelle détectée\n";
} else {
    echo "❌ Erreurs trouvées:\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
}

echo "\n🏁 Analyse terminée\n";
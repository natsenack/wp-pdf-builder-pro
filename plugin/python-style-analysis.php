<?php
echo "🔍 Analyse Python-style du fichier corporate.json\n";
echo "================================================\n\n";

$file = __DIR__ . '/templates/builtin/corporate.json';

if (!file_exists($file)) {
    echo "❌ Fichier corporate.json non trouvé\n";
    exit(1);
}

$content = file_get_contents($file);
$data = json_decode($content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo "❌ Erreur JSON ligne approximative: " . json_last_error_msg() . "\n";
    exit(1);
}

echo "✅ JSON valide\n";
echo "📊 " . count($data['elements'] ?? []) . " éléments\n";
echo "📏 Canvas: " . ($data['canvasWidth'] ?? "?") . "x" . ($data['canvasHeight'] ?? "?") . "\n";

// Vérifier les types d'éléments
$types = [];
foreach ($data['elements'] ?? [] as $elem) {
    $t = $elem['type'] ?? 'unknown';
    $types[$t] = ($types[$t] ?? 0) + 1;
}

echo "🔧 Types d'éléments:\n";
ksort($types);
foreach ($types as $t => $count) {
    echo "   $t: $count\n";
}

echo "\n🏁 Analyse terminée\n";
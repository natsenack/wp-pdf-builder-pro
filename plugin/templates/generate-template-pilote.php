<?php
/**
 * Générateur de template JSON pilote
 * Usage: php generate-template-pilote.php
 */

echo "=== TEMPLATE JSON PILOTE ===\n\n";

// Charger le template JSON
$template_path = __DIR__ . '/examples/facture-pilote.json';

if (!file_exists($template_path)) {
    die("Erreur: Template JSON non trouvé: $template_path\n");
}

$json_content = file_get_contents($template_path);
$template_data = json_decode($json_content, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    die("Erreur JSON: " . json_last_error_msg() . "\n");
}

echo "📄 TEMPLATE: {$template_data['name']}\n";
echo "📝 Description: {$template_data['description']}\n";
echo "📐 Dimensions: {$template_data['canvasWidth']}x{$template_data['canvasHeight']}px (A4)\n";
echo "🔧 Éléments: " . count($template_data['elements']) . "\n\n";

echo "=== CONTENU JSON (à copier dans l'éditeur) ===\n\n";
echo $json_content . "\n\n";

echo "=== INSTRUCTIONS ===\n";
echo "1. Copiez le JSON ci-dessus\n";
echo "2. Allez dans l'admin WordPress > PDF Builder > Templates\n";
echo "3. Cliquez sur 'Nouveau template'\n";
echo "4. Collez le JSON dans l'éditeur\n";
echo "5. Sauvegardez le template\n";
echo "6. Testez dans l'éditeur React !\n\n";

echo "=== ÉLÉMENTS DU TEMPLATE ===\n";
foreach ($template_data['elements'] as $element) {
    echo "• {$element['id']} ({$element['type']})\n";
}

echo "\n=== FIN ===\n";
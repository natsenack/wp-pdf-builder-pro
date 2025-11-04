<?php
/**
 * Script pour mettre à jour les URLs de prévisualisation dans les templates prédéfinis
 */

// Dossier des templates prédéfinis
$templates_dir = __DIR__ . '/../templates/predefined/';
$templates = glob($templates_dir . '*.json');

echo "Mise à jour des URLs de prévisualisation...\n\n";

foreach ($templates as $template_file) {
    $filename = basename($template_file, '.json');

    // Charger le JSON
    $template_json = file_get_contents($template_file);
    $template_data = json_decode($template_json, true);

    if (!$template_data) {
        echo "❌ Erreur JSON: $filename\n";
        continue;
    }

    // Générer l'URL de prévisualisation (hardcoded pour éviter plugins_url)
    $preview_url = "/wp-content/plugins/wp-pdf-builder-pro/templates/predefined/{$filename}-preview.png";

    // Mettre à jour le champ previewImage
    $template_data['previewImage'] = $preview_url;

    // Sauvegarder
    $updated_json = json_encode($template_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($template_file, $updated_json);

    echo "✅ Mis à jour: $filename → $preview_url\n";
}

echo "\nTerminé !\n";
?>
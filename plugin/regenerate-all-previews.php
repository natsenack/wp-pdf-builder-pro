<?php
/**
 * Script simplifié pour régénérer tous les aperçus SVG
 * Usage: php regenerate-all-previews.php
 */

$templates = ['modern', 'classic', 'corporate', 'minimal'];

echo "🔄 Régénération de tous les aperçus SVG...\n\n";

foreach ($templates as $template) {
    echo "📄 Génération de {$template}...\n";

    $command = "php generate-svg-preview.php {$template}";
    $output = shell_exec($command);

    if (strpos($output, '✅') !== false) {
        echo "   ✅ {$template}-preview.svg généré\n";
    } else {
        echo "   ❌ Erreur pour {$template}: {$output}\n";
    }

    echo "\n";
}

echo "🎉 Tous les aperçus ont été régénérés !\n";
echo "📁 Fichiers dans: assets/images/templates/\n";
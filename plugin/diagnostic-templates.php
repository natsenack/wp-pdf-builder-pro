<?php
/**
 * Diagnostic script to check if builtin templates have been deployed correctly
 */

// Check if files exist and contain the right content
$templates_dir = __DIR__ . '/templates/builtin/';
$templates = ['classic.json', 'corporate.json', 'modern.json', 'minimal.json'];

echo "<h1>Diagnostic des templates prédéfinis</h1>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Template</th><th>Existe</th><th>Contient le texte modifié</th><th>Extrait</th></tr>";

foreach ($templates as $template) {
    $file_path = $templates_dir . $template;
    $exists = file_exists($file_path);
    $has_content = false;
    $extract = '';

    if ($exists) {
        $content = file_get_contents($file_path);
        $data = json_decode($content, true);

        if ($data && isset($data['elements'])) {
            // Look for text elements
            foreach ($data['elements'] as $element) {
                if (isset($element['properties']['text'])) {
                    $text = $element['properties']['text'];
                    if (strpos($text, 'FACTURE') !== false || strpos($text, 'simplifiée') !== false) {
                        $has_content = true;
                        $extract = substr($text, 0, 50) . '...';
                        break;
                    }
                }
            }
        }
    }

    echo "<tr>";
    echo "<td>{$template}</td>";
    echo "<td>" . ($exists ? '✅ Oui' : '❌ Non') . "</td>";
    echo "<td>" . ($has_content ? '✅ Oui' : '❌ Non') . "</td>";
    echo "<td>{$extract}</td>";
    echo "</tr>";
}

echo "</table>";
?>
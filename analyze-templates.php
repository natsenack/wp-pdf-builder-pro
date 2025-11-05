<?php
$templates = ['classic.json', 'corporate.json', 'minimal.json', 'modern.json'];
foreach ($templates as $template) {
    echo "\n=== $template ===\n";
    $file = 'plugin/templates/builtin/' . $template;
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $data = json_decode($content, true);
        if ($data && isset($data['elements'])) {
            foreach ($data['elements'] as $element) {
                if (isset($element['properties']) && isset($element['properties']['color']) && !isset($element['properties']['textColor'])) {
                    echo "- " . $element['type'] . " (id: " . $element['id'] . ") utilise 'color' au lieu de 'textColor'\n";
                }
            }
        }
    }
}
?>
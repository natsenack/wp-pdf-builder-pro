<?php
$json = file_get_contents('plugin/templates/builtin/corporate.json');
$data = json_decode($json, true);

echo "JSON valide: " . (json_last_error() === JSON_ERROR_NONE ? "OUI" : "NON - " . json_last_error_msg()) . "\n";

if ($data) {
    echo "Version: " . ($data['version'] ?? 'manquante') . "\n";
    echo "Name: " . ($data['name'] ?? 'manquante') . "\n";
    echo "Category: " . ($data['category'] ?? 'manquante') . "\n";
    echo "Description: " . ($data['description'] ?? 'manquante') . "\n";
    echo "Elements count: " . count($data['elements'] ?? []) . "\n";
    echo "Canvas: " . ($data['canvasWidth'] ?? 'X') . " x " . ($data['canvasHeight'] ?? 'Y') . "\n";
    
    // Check each element has id and type
    $bad_elements = [];
    foreach ($data['elements'] as $idx => $elem) {
        if (empty($elem['id']) || empty($elem['type'])) {
            $bad_elements[] = "Element $idx: id=" . ($elem['id'] ?? 'MISSING') . ", type=" . ($elem['type'] ?? 'MISSING');
        }
    }
    if (!empty($bad_elements)) {
        echo "Elements with issues:\n";
        foreach ($bad_elements as $issue) {
            echo "  " . $issue . "\n";
        }
    }
} else {
    echo "Erreur de décodage JSON\n";
}
?>

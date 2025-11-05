<?php
$content = file_get_contents('corporate.json');
$data = json_decode($content, true);
if (json_last_error() === JSON_ERROR_NONE) {
    echo "JSON valide\n";
    echo "Nombre d'éléments: " . count($data['elements']) . "\n";
} else {
    echo "Erreur JSON: " . json_last_error_msg() . "\n";
}
?>
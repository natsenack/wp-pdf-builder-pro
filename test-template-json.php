<?php
$content = file_get_contents('plugin/templates/builtin/facture-simple.json');
$data = json_decode($content, true);
echo 'JSON valide: ' . (json_last_error() === JSON_ERROR_NONE ? 'OUI' : 'NON - ' . json_last_error_msg()) . PHP_EOL;
if (json_last_error() === JSON_ERROR_NONE) {
    echo 'Éléments: ' . count($data['elements']) . PHP_EOL;
    echo 'Canvas: ' . $data['canvasWidth'] . 'x' . $data['canvasHeight'] . PHP_EOL;
    echo 'Nom: ' . $data['name'] . PHP_EOL;
    echo 'Types d\'éléments: ';
    $types = array_unique(array_column($data['elements'], 'type'));
    echo implode(', ', $types) . PHP_EOL;
}
?>
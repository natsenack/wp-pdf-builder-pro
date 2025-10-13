<?php
require_once 'includes/pdf-preview-generator.php';
$gen = new PDF_Preview_Generator();
$test_elements = [[
    'id' => 'divider-test',
    'type' => 'divider',
    'x' => 50,
    'y' => 50,
    'width' => 200,
    'height' => 10,
    'color' => '#ff0000',
    'thickness' => 3,
    'margin' => 5
]];
echo $gen->generate_html_preview($test_elements, 1.0);
?>
<?php
require_once 'wp-load.php';

if (function_exists('get_option')) {
    echo "Test de récupération des options Canvas:\n";
    $test_options = [
        'pdf_builder_canvas_width',
        'pdf_builder_canvas_height',
        'pdf_builder_canvas_format',
        'pdf_builder_canvas_grid_enabled',
        'pdf_builder_canvas_backup'
    ];

    foreach ($test_options as $option) {
        $value = get_option($option, 'NOT_SET');
        echo "$option: $value\n";
    }
} else {
    echo "WordPress not loaded properly\n";
}
?>
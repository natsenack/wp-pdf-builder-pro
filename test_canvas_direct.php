<?php
define('ABSPATH', '/var/www/html/'); // Chemin WordPress sur le serveur
require_once ABSPATH . 'wp-load.php';
require_once 'wp-content/plugins/wp-pdf-builder-pro/plugin/bootstrap.php';

if (class_exists('PDF_Builder_Canvas_Manager')) {
    $manager = PDF_Builder_Canvas_Manager::get_instance();

    // Tester la sauvegarde
    $test_params = ['default_canvas_width' => 1000, 'show_grid' => true];
    $result = $manager->save_canvas_settings($test_params);

    echo 'Sauvegarde test: ' . ($result ? 'OK' : 'ERREUR') . PHP_EOL;

    // Tester la récupération
    $settings = $manager->get_canvas_settings();
    echo 'Paramètre récupéré default_canvas_width: ' . ($settings['default_canvas_width'] ?? 'NON TROUVÉ') . PHP_EOL;
    echo 'Paramètre récupéré show_grid: ' . ($settings['show_grid'] ? 'true' : 'false') . PHP_EOL;
} else {
    echo 'Canvas Manager non trouvé' . PHP_EOL;
}
?>
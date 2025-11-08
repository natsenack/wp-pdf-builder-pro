<?php
require_once 'plugin/bootstrap.php';

if (class_exists('PDF_Builder_Canvas_Manager')) {
    $manager = PDF_Builder_Canvas_Manager::get_instance();
    $settings = $manager->get_canvas_settings();
    echo 'Paramètres canvas actuels:' . PHP_EOL;
    print_r($settings);
} else {
    echo 'Canvas Manager non trouvé' . PHP_EOL;
}
?>
<?php
// Vérifier quel fichier PHP est réellement chargé par WordPress
require_once('../../../wp-load.php');

echo "<h1>DIAGNOSTIC - Quel fichier PHP est chargé ?</h1>";

// Vérifier si les classes existent
$classes_to_check = [
    'PDF_Builder_Admin',
    'PDF_Builder_Admin_New'
];

foreach ($classes_to_check as $class_name) {
    if (class_exists($class_name)) {
        echo "<p><strong>$class_name:</strong> ✅ Existe</p>";

        // Vérifier si la méthode ajax_load_canvas_elements existe
        if (method_exists($class_name, 'ajax_load_canvas_elements')) {
            echo "<p><strong>$class_name::ajax_load_canvas_elements:</strong> ✅ Existe</p>";
        } else {
            echo "<p><strong>$class_name::ajax_load_canvas_elements:</strong> ❌ N'existe pas</p>";
        }
    } else {
        echo "<p><strong>$class_name:</strong> ❌ N'existe pas</p>";
    }
}

// Vérifier les actions AJAX enregistrées
global $wp_filter;
if (isset($wp_filter['wp_ajax_pdf_builder_load_canvas_elements'])) {
    echo "<h2>Actions AJAX enregistrées pour 'wp_ajax_pdf_builder_load_canvas_elements':</h2>";
    foreach ($wp_filter['wp_ajax_pdf_builder_load_canvas_elements']->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                $method = $callback['function'][1];
                echo "<p><strong>Classe:</strong> $class, <strong>Méthode:</strong> $method, <strong>Priorité:</strong> $priority</p>";
            } else {
                echo "<p><strong>Fonction:</strong> " . $callback['function'] . ", <strong>Priorité:</strong> $priority</p>";
            }
        }
    }
} else {
    echo "<p><strong>Aucune action AJAX enregistrée pour 'wp_ajax_pdf_builder_load_canvas_elements'</strong></p>";
}

// Vérifier les fichiers chargés
echo "<h2>Fichiers PHP chargés contenant 'pdf-builder-admin':</h2>";
$included_files = get_included_files();
foreach ($included_files as $file) {
    if (strpos($file, 'pdf-builder-admin') !== false) {
        echo "<p>$file</p>";
    }
}
?>
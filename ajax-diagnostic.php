<?php
// Diagnostic AJAX pour vérifier quelle classe gère les appels
require_once('../../../wp-load.php');

if (!is_user_logged_in()) {
    die('Vous devez être connecté.');
}

// Simuler un appel AJAX comme le fait JavaScript
$user_id = get_current_user_id();
$nonce = wp_create_nonce('pdf_builder_canvas_v4_' . $user_id);

$_POST = [
    'action' => 'pdf_builder_load_canvas_elements',
    'nonce' => $nonce,
    'template_id' => 1
];

echo "<h1>DIAGNOSTIC AJAX SIMULATION</h1>";
echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>Nonce généré:</strong> $nonce</p>";

// Vérifier quelle classe gère l'action
global $wp_filter;
$action = 'wp_ajax_pdf_builder_load_canvas_elements';

if (isset($wp_filter[$action])) {
    echo "<h2>Classe qui gère l'action AJAX:</h2>";
    foreach ($wp_filter[$action]->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                $method = $callback['function'][1];
                echo "<p><strong>Classe:</strong> $class, <strong>Méthode:</strong> $method</p>";
            }
        }
    }
}

// Tester l'appel AJAX
echo "<h2>Résultat de l'appel AJAX:</h2>";
echo "<pre>";
try {
    // Appeler directement la fonction AJAX si elle existe
    if (function_exists('wp_ajax_pdf_builder_load_canvas_elements')) {
        echo "Fonction wp_ajax_pdf_builder_load_canvas_elements existe\n";
        wp_ajax_pdf_builder_load_canvas_elements();
    } else {
        echo "Fonction wp_ajax_pdf_builder_load_canvas_elements n'existe pas\n";

        // Essayer d'appeler via do_action
        echo "Tentative via do_action...\n";
        ob_start();
        do_action('wp_ajax_pdf_builder_load_canvas_elements');
        $output = ob_get_clean();
        echo "Output: $output\n";
    }
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>
<?php
// Test final du nonce - simulation exacte de l'appel JavaScript
require_once('../../../wp-load.php');

if (!is_user_logged_in()) {
    die('Vous devez être connecté pour tester.');
}

$user_id = get_current_user_id();

// Générer le nonce exactement comme dans PDF_Builder_Admin_New
$nonce_action = 'pdf_builder_canvas_v4_' . $user_id;
$generated_nonce = wp_create_nonce($nonce_action);

echo "<h1>TEST FINAL DU NONCE</h1>";
echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>Action attendue:</strong> $nonce_action</p>";
echo "<p><strong>Nonce généré par PHP:</strong> $generated_nonce</p>";

// Simuler l'appel AJAX avec le nonce généré
$_POST = [
    'action' => 'pdf_builder_load_canvas_elements',
    'nonce' => $generated_nonce,
    'template_id' => 1
];

echo "<h2>Simulation de l'appel AJAX:</h2>";
echo "<p>Appel de: wp_ajax_pdf_builder_load_canvas_elements</p>";

// Tester si la fonction existe
if (function_exists('wp_ajax_pdf_builder_load_canvas_elements')) {
    echo "<p>✅ Fonction wp_ajax_pdf_builder_load_canvas_elements existe</p>";
} else {
    echo "<p>❌ Fonction wp_ajax_pdf_builder_load_canvas_elements n'existe pas</p>";
}

// Appeler directement l'action
echo "<h3>Résultat:</h3><pre>";
ob_start();
try {
    do_action('wp_ajax_pdf_builder_load_canvas_elements');
    $output = ob_get_clean();
    echo htmlspecialchars($output);
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}
echo "</pre>";

// Vérifier quelle classe gère l'action
global $wp_filter;
$action = 'wp_ajax_pdf_builder_load_canvas_elements';
if (isset($wp_filter[$action])) {
    echo "<h3>Classe qui gère l'action:</h3>";
    foreach ($wp_filter[$action]->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                $method = $callback['function'][1];
                echo "<p>$class::$method (priorité: $priority)</p>";
            }
        }
    }
}
?>
<?php
// TEST ULTIME DU NONCE - Version simplifiée
require_once('../../../wp-load.php');

if (!is_user_logged_in()) {
    die('Erreur: Non connecté');
}

$user_id = get_current_user_id();
$action = 'pdf_builder_canvas_v4_' . $user_id;
$nonce = wp_create_nonce($action);

echo "=== TEST ULTIME DU NONCE ===\n";
echo "User ID: $user_id\n";
echo "Action: $action\n";
echo "Nonce généré: $nonce\n\n";

// Vérifier quelle classe gère l'action
global $wp_filter;
$ajax_action = 'wp_ajax_pdf_builder_load_canvas_elements';

echo "=== CLASSES ENREGISTRÉES POUR L'ACTION AJAX ===\n";
if (isset($wp_filter[$ajax_action])) {
    foreach ($wp_filter[$ajax_action]->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                $method = $callback['function'][1];
                echo "- $class::$method (priorité: $priority)\n";
            }
        }
    }
} else {
    echo "AUCUNE CLASSE ENREGISTRÉE !\n";
}

echo "\n=== TEST DE VALIDATION DIRECT ===\n";
$valid = wp_verify_nonce($nonce, $action);
echo "Validation du nonce généré: " . ($valid ? "✅ VALIDE" : "❌ INVALIDE") . "\n";

echo "\n=== SIMULATION AJAX ===\n";
$_POST = [
    'action' => 'pdf_builder_load_canvas_elements',
    'nonce' => $nonce,
    'template_id' => 1
];

echo "Appel de l'action AJAX...\n";
ob_start();
do_action('wp_ajax_pdf_builder_load_canvas_elements');
$result = ob_get_clean();

if (empty($result)) {
    echo "❌ AUCUNE RÉPONSE AJAX\n";
} else {
    echo "✅ RÉPONSE AJAX REÇUE\n";
    echo "Résultat: " . substr($result, 0, 200) . "...\n";
}
?>
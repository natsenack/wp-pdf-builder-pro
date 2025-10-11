<?php
// VÉRIFICATION FINALE - Quelle classe génère réellement le nonce ?
require_once('../../../wp-load.php');

echo "<h1>VÉRIFICATION FINALE - Diagnostic complet</h1>";

// 1. Vérifier les classes chargées
echo "<h2>1. Classes chargées</h2>";
$classes = ['PDF_Builder_Admin', 'PDF_Builder_Admin_New'];
foreach ($classes as $class) {
    $loaded = class_exists($class);
    echo "<p><strong>$class:</strong> " . ($loaded ? "✅ CHARGÉE" : "❌ NON CHARGÉE") . "</p>";
}

// 2. Vérifier les instances
echo "<h2>2. Instances créées</h2>";
global $pdf_builder_admin_instance;
if (isset($pdf_builder_admin_instance)) {
    $instance_class = get_class($pdf_builder_admin_instance);
    echo "<p><strong>Instance globale:</strong> $instance_class</p>";
} else {
    echo "<p><strong>Instance globale:</strong> ❌ AUCUNE</p>";
}

// 3. Vérifier les actions AJAX
echo "<h2>3. Actions AJAX enregistrées</h2>";
global $wp_filter;
$action = 'wp_ajax_pdf_builder_load_canvas_elements';
if (isset($wp_filter[$action])) {
    echo "<p><strong>Action trouvée:</strong> ✅</p>";
    foreach ($wp_filter[$action]->callbacks as $priority => $callbacks) {
        foreach ($callbacks as $callback) {
            if (is_array($callback['function'])) {
                $class = is_object($callback['function'][0]) ? get_class($callback['function'][0]) : $callback['function'][0];
                $method = $callback['function'][1];
                echo "<p>   - <strong>$class::$method</strong> (priorité: $priority)</p>";
            }
        }
    }
} else {
    echo "<p><strong>Action NON trouvée:</strong> ❌</p>";
}

// 4. Générer le nonce actuel
echo "<h2>4. Nonce actuellement généré</h2>";
$user_id = get_current_user_id();
$current_nonce = wp_create_nonce('pdf_builder_canvas_v4_' . $user_id);
echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>Action utilisée:</strong> pdf_builder_canvas_v4_$user_id</p>";
echo "<p><strong>Nonce généré:</strong> $current_nonce</p>";

// 5. Tester la validation
echo "<h2>5. Test de validation</h2>";
$test_nonce = '1cff71fef9'; // Le nonce que JavaScript envoie
$valid = wp_verify_nonce($test_nonce, 'pdf_builder_canvas_v4_' . $user_id);
echo "<p><strong>Test nonce '$test_nonce':</strong> " . ($valid ? "✅ VALIDE" : "❌ INVALIDE") . "</p>";

// 6. Vérifier wp_localize_script
echo "<h2>6. Vérification wp_localize_script</h2>";
echo "<p>Recherche des appels wp_localize_script pour pdfBuilderAjax...</p>";

// Simuler ce que fait la classe admin
if (class_exists('PDF_Builder_Admin_New')) {
    echo "<p><strong>PDF_Builder_Admin_New existe:</strong> ✅</p>";
    try {
        $instance = PDF_Builder_Admin_New::getInstance(null);
        echo "<p><strong>Instance créée:</strong> ✅</p>";
    } catch (Exception $e) {
        echo "<p><strong>Erreur instance:</strong> " . $e->getMessage() . "</p>";
    }
} else {
    echo "<p><strong>PDF_Builder_Admin_New n'existe pas:</strong> ❌</p>";
}

echo "<hr>";
echo "<p><strong>CONCLUSION:</strong> Si le nonce JavaScript est '1cff71fef9' mais que PHP génère '$current_nonce', alors l'ancienne classe est encore utilisée.</p>";
?>
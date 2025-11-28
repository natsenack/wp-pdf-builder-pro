<?php
/**
 * Test du système de nonce AJAX
 * Vérifie que les nonces sont générés et validés correctement
 */

require_once(dirname(__FILE__) . '/plugin/pdf-builder-pro.php');

echo "<h1>Test du système de nonce AJAX</h1>";
echo "<p>Test exécuté le " . date('Y-m-d H:i:s') . "</p>";

// Test 1: Générer un nonce frais
echo "<h2>Test 1: Génération de nonce frais</h2>";
$nonce = wp_create_nonce('pdf_builder_ajax');
echo "<p>Nonce généré: <code>$nonce</code></p>";

// Test 2: Vérifier le nonce
echo "<h2>Test 2: Validation du nonce</h2>";
$is_valid = wp_verify_nonce($nonce, 'pdf_builder_ajax');
echo "<p>Nonce valide: " . ($is_valid ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "</p>";

// Test 3: Simuler une requête AJAX pour obtenir un nonce frais
echo "<h2>Test 3: Simulation requête AJAX get_fresh_nonce</h2>";

// Simuler $_POST pour la requête AJAX
$_POST = array(
    'action' => 'pdf_builder_get_fresh_nonce'
);

// Simuler les headers AJAX
$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

// Capturer la sortie
ob_start();
try {
    pdf_builder_ajax_handler_dispatch();
    $output = ob_get_clean();
    echo "<p>Réponse AJAX: <pre>" . htmlspecialchars($output) . "</pre></p>";

    // Parser le JSON
    $response = json_decode($output, true);
    if ($response && isset($response['success'])) {
        echo "<p>JSON valide: " . ($response['success'] ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "</p>";
        if ($response['success'] && isset($response['data']['nonce'])) {
            $fresh_nonce = $response['data']['nonce'];
            echo "<p>Nouveau nonce: <code>$fresh_nonce</code></p>";

            // Tester la validation du nouveau nonce
            $fresh_valid = wp_verify_nonce($fresh_nonce, 'pdf_builder_ajax');
            echo "<p>Nouveau nonce valide: " . ($fresh_valid ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "</p>";
        }
    } else {
        echo "<p>JSON invalide ou réponse d'erreur</p>";
    }
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "<p>Erreur lors de l'appel AJAX: " . $e->getMessage() . "</p>";
    echo "<p>Sortie: <pre>" . htmlspecialchars($output) . "</pre></p>";
}

// Test 4: Simuler une requête de sauvegarde avec nonce valide
echo "<h2>Test 4: Simulation requête de sauvegarde</h2>";

$_POST = array(
    'action' => 'pdf_builder_save_settings',
    'nonce' => $nonce,
    'current_tab' => 'general',
    'company_phone_manual' => '0123456789'
);

ob_start();
try {
    pdf_builder_ajax_handler_dispatch();
    $output = ob_get_clean();
    echo "<p>Réponse sauvegarde: <pre>" . htmlspecialchars($output) . "</pre></p>";

    $response = json_decode($output, true);
    if ($response && isset($response['success'])) {
        echo "<p>Sauvegarde réussie: " . ($response['success'] ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "</p>";
        if (!$response['success'] && isset($response['data']['message'])) {
            echo "<p>Message d'erreur: " . $response['data']['message'] . "</p>";
        }
    }
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "<p>Erreur lors de la sauvegarde: " . $e->getMessage() . "</p>";
    echo "<p>Sortie: <pre>" . htmlspecialchars($output) . "</pre></p>";
}

// Test 5: Simuler une requête avec nonce invalide
echo "<h2>Test 5: Test avec nonce invalide</h2>";

$_POST = array(
    'action' => 'pdf_builder_save_settings',
    'nonce' => 'invalid_nonce_12345',
    'current_tab' => 'general'
);

ob_start();
try {
    pdf_builder_ajax_handler_dispatch();
    $output = ob_get_clean();
    echo "<p>Réponse avec nonce invalide: <pre>" . htmlspecialchars($output) . "</pre></p>";

    $response = json_decode($output, true);
    if ($response && isset($response['success'])) {
        echo "<p>Requête rejetée correctement: " . (!$response['success'] ? '<span style="color:green">OUI</span>' : '<span style="color:red">NON</span>') . "</p>";
        if (!$response['success'] && isset($response['data']['message'])) {
            echo "<p>Message d'erreur: " . $response['data']['message'] . "</p>";
        }
    }
} catch (Exception $e) {
    $output = ob_get_clean();
    echo "<p>Erreur avec nonce invalide: " . $e->getMessage() . "</p>";
    echo "<p>Sortie: <pre>" . htmlspecialchars($output) . "</pre></p>";
}

echo "<hr>";
echo "<p><strong>Tests terminés</strong></p>";
?>
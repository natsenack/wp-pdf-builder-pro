<?php
// Debug complet du nonce - version simplifiée
require_once('../../../wp-load.php');

if (!is_user_logged_in()) {
    die('Vous devez être connecté.');
}

$user_id = get_current_user_id();
$action = 'pdf_builder_canvas_v4_' . $user_id;
$nonce = wp_create_nonce($action);

echo "<h1>DEBUG NONCE COMPLET</h1>";
echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>Action:</strong> $action</p>";
echo "<p><strong>Nonce généré:</strong> $nonce</p>";

// Test de validation
$valid = wp_verify_nonce($nonce, $action);
echo "<p><strong>Validation locale:</strong> " . ($valid ? '✅ OK' : '❌ FAIL') . "</p>";

// Simuler la réception du nonce comme en AJAX
if (isset($_GET['test_nonce'])) {
    $received = $_GET['test_nonce'];
    echo "<h2>Test avec nonce reçu: $received</h2>";

    $formats = [
        'pdf_builder_canvas_v4_' . $user_id,
        'pdf_builder_canvas_v3_' . $user_id,
        'pdf_builder_canvas_' . $user_id,
        'pdf_builder_canvas',
        'test_nonce_123'
    ];

    foreach ($formats as $format) {
        $test_valid = wp_verify_nonce($received, $format);
        echo "<p><strong>$format:</strong> " . ($test_valid ? '✅ VALIDE' : '❌ INVALIDE') . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>URL de test:</strong> ?test_nonce=VOTRE_NONCE_ICI</p>";
echo "<p><strong>Nonce actuel à tester:</strong> <a href='?test_nonce=$nonce'>$nonce</a></p>";
?>
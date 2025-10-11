<?php
// Test rapide du nonce PHP
require_once('../../../wp-load.php');

if (!is_user_logged_in()) {
    wp_die('Vous devez être connecté pour accéder à cette page.');
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Générer le nonce comme dans class-pdf-builder-admin-new.php
$nonce_action = 'pdf_builder_canvas_v4_' . $user_id;
$generated_nonce = wp_create_nonce($nonce_action);

echo "<h2>Test du nonce PHP</h2>";
echo "<p><strong>Utilisateur ID:</strong> $user_id</p>";
echo "<p><strong>Action du nonce:</strong> $nonce_action</p>";
echo "<p><strong>Nonce généré:</strong> $generated_nonce</p>";

// Tester la validation
$test_nonce = isset($_GET['test_nonce']) ? $_GET['test_nonce'] : $generated_nonce;
$is_valid = wp_verify_nonce($test_nonce, $nonce_action);

echo "<p><strong>Test de validation:</strong> " . ($is_valid ? '✅ VALIDE' : '❌ INVALIDE') . "</p>";

if (!$is_valid) {
    // Tester les anciens formats pour compatibilité
    $old_formats = [
        'pdf_builder_canvas_' . $user_id,
        'pdf_builder_canvas_v2_' . $user_id,
        'pdf_builder_canvas_v3_' . $user_id,
        'pdf_builder_canvas'
    ];

    echo "<h3>Test des anciens formats:</h3>";
    foreach ($old_formats as $old_action) {
        $old_valid = wp_verify_nonce($test_nonce, $old_action);
        echo "<p>$old_action: " . ($old_valid ? '✅ VALIDE' : '❌ INVALIDE') . "</p>";
    }
}

echo "<hr>";
echo "<p><strong>URL de test:</strong> ?test_nonce=VOTRE_NONCE_ICI</p>";
?>
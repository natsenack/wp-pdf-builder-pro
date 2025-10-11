<?php
/**
 * Test nonce direct - Diagnostic temporaire
 */

// Simuler un environnement WordPress basique
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

if (!is_user_logged_in()) {
    die('❌ Utilisateur non connecté');
}

$user_id = get_current_user_id();
$timestamp = time();
$nonce_action = 'pdf_builder_canvas_v4_' . $user_id . '_cachebust_' . $timestamp;
$generated_nonce = wp_create_nonce($nonce_action);

echo "<h1>Test Nonce Direct</h1>";
echo "<pre>";
echo "User ID: $user_id\n";
echo "Timestamp: $timestamp\n";
echo "Nonce Action: $nonce_action\n";
echo "Generated Nonce: $generated_nonce\n";
echo "Nonce Length: " . strlen($generated_nonce) . "\n";
echo "</pre>";
?>
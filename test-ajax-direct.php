<?php
// Test direct de l'AJAX call
require_once('../../../wp-load.php');

if (!is_user_logged_in()) {
    die('Vous devez être connecté pour tester.');
}

$user_id = get_current_user_id();
$nonce = wp_create_nonce('pdf_builder_canvas_v4_' . $user_id);

// Simuler l'appel AJAX
$_POST = [
    'action' => 'pdf_builder_load_canvas_elements',
    'nonce' => $nonce,
    'template_id' => 1
];

echo "<h1>Test AJAX simulé</h1>";
echo "<p><strong>User ID:</strong> $user_id</p>";
echo "<p><strong>Nonce envoyé:</strong> $nonce</p>";

// Inclure la classe et appeler la méthode
require_once('includes/classes/class-pdf-builder-admin-new.php');
$admin = new PDF_Builder_Admin_New();
$admin->ajax_load_canvas_elements();

// Note: Cette fonction utilise wp_send_json_error/wp_send_json_success qui terminent le script
?>
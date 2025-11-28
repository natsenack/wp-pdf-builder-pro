<?php
require_once('wp-load.php');

$nonce = wp_create_nonce('pdf_builder_ajax');
echo 'Generated nonce: ' . $nonce . PHP_EOL;

$valid = wp_verify_nonce($nonce, 'pdf_builder_ajax');
echo 'Verification result: ' . ($valid ? 'VALID' : 'INVALID') . ' (' . $valid . ')' . PHP_EOL;

// Test avec un nonce expiré
$old_nonce = wp_create_nonce('pdf_builder_ajax');
sleep(2); // Attendre 2 secondes
$still_valid = wp_verify_nonce($old_nonce, 'pdf_builder_ajax');
echo 'Old nonce still valid: ' . ($still_valid ? 'YES' : 'NO') . ' (' . $still_valid . ')' . PHP_EOL;
?>
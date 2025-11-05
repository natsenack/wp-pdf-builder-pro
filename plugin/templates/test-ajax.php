<?php
/**
 * Test script pour vérifier l'AJAX des templates builtin
 */

// Simuler un environnement WordPress basique
define('ABSPATH', dirname(dirname(__FILE__)) . '/');
define('WPINC', 'wp-includes');

// Charger WordPress
require_once '../../../wp-load.php';

if (!defined('ABSPATH')) {
    die('WordPress not loaded');
}

// Simuler une requête AJAX
$_POST['action'] = 'get_builtin_templates';
$_POST['nonce'] = wp_create_nonce('pdf_builder_nonce');

// Charger la fonction AJAX
require_once __DIR__ . '/../bootstrap.php';

// Tester la fonction
echo "=== TEST AJAX GET_BUILTIN_TEMPLATES ===\n\n";

try {
    // Appeler directement la fonction
    pdf_builder_ajax_get_builtin_templates();
} catch (Exception $e) {
    echo "ERREUR: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DU TEST ===\n";
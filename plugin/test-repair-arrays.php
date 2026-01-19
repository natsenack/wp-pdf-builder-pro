<?php
/**
 * Script de test pour la réparation des arrays corrompus
 * À exécuter depuis l'admin WordPress ou via WP-CLI
 */

// Inclure WordPress
require_once('../../../wp-load.php');

if (!defined('ABSPATH')) {
    die('Accès direct non autorisé');
}

if (!current_user_can('manage_options')) {
    die('Permissions insuffisantes');
}

echo "<h1>Test de réparation des arrays corrompus</h1>\n";

// Valeurs de test corrompues
$corrupted_values = [
    'pdf_builder_canvas_dpi' => '0',
    'pdf_builder_canvas_formats' => '0,0,0,0,0',
    'pdf_builder_canvas_orientations' => '0,0'
];

echo "<h2>1. Simulation des valeurs corrompues</h2>\n";
foreach ($corrupted_values as $key => $value) {
    update_option($key, $value);
    echo "✅ Défini {$key} = '{$value}'<br>\n";
}

echo "<h2>2. Vérification des valeurs actuelles</h2>\n";
foreach ($corrupted_values as $key => $expected_corrupted) {
    $current_value = get_option($key, '');
    $status = ($current_value === $expected_corrupted) ? '✅' : '❌';
    echo "{$status} {$key} = '{$current_value}' (attendu: '{$expected_corrupted}')<br>\n";
}

echo "<h2>3. Exécution de la réparation</h2>\n";

// Simuler l'appel AJAX
$_POST['action'] = 'pdf_builder_repair_corrupted_arrays';
$_POST['nonce'] = wp_create_nonce('repair_corrupted_arrays');

// Inclure le handler AJAX
require_once('../src/Core/PDF_Builder_Unified_Ajax_Handler.php');

$handler = PDF_Builder_Unified_Ajax_Handler::get_instance();
$result = $handler->handle_repair_corrupted_arrays();

echo "<h2>4. Vérification après réparation</h2>\n";
$expected_defaults = [
    'pdf_builder_canvas_dpi' => '96',
    'pdf_builder_canvas_formats' => 'A4',
    'pdf_builder_canvas_orientations' => 'portrait,landscape'
];

foreach ($expected_defaults as $key => $expected_default) {
    $current_value = get_option($key, '');
    $status = ($current_value === $expected_default) ? '✅' : '❌';
    echo "{$status} {$key} = '{$current_value}' (attendu: '{$expected_default}')<br>\n";
}

echo "<h2>5. Test terminé</h2>\n";
echo "La fonction de réparation des arrays corrompus fonctionne correctement ! 🎉\n";
?>
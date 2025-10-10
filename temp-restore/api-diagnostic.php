<?php
/**
 * Diagnostic API pour PDF Builder Pro
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier les permissions admin
if (!current_user_can('manage_options')) {
    wp_die('Accès refusé');
}

echo "<h1>🔍 Diagnostic API PDF Builder Pro</h1>";

// Test des endpoints REST
echo "<h2>Endpoints REST API</h2>";
$endpoints = [
    '/wp-json/wp/v2/users/me',
    '/wp-json/pdf-builder/v1/templates',
    '/wp-json/pdf-builder/v1/health'
];

foreach ($endpoints as $endpoint) {
    $response = wp_remote_get(rest_url($endpoint));
    $status = wp_remote_retrieve_response_code($response);

    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
    echo "<strong>$endpoint</strong><br>";
    echo "Status: <span style='color: " . ($status == 200 ? 'green' : 'red') . "'>$status</span>";
    echo "</div>";
}

// Test des classes PHP
echo "<h2>Classes PHP</h2>";
$classes = [
    'TCPDF' => 'Bibliothèque PDF',
    'PDF_Builder_Core' => 'Core du plugin',
    'PDF_Builder_Admin' => 'Administration'
];

foreach ($classes as $class => $description) {
    $exists = class_exists($class);
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
    echo "<strong>$class</strong> - $description<br>";
    echo "Status: <span style='color: " . ($exists ? 'green' : 'red') . "'>" . ($exists ? '✅ Disponible' : '❌ Manquant') . "</span>";
    echo "</div>";
}

// Test de la base de données
echo "<h2>Base de données</h2>";
global $wpdb;
$tables = [
    $wpdb->prefix . 'pdf_builder_templates',
    $wpdb->prefix . 'pdf_builder_elements'
];

foreach ($tables as $table) {
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table'") == $table;
    echo "<div style='margin: 10px 0; padding: 10px; border: 1px solid #ddd;'>";
    echo "<strong>$table</strong><br>";
    echo "Status: <span style='color: " . ($exists ? 'green' : 'red') . "'>" . ($exists ? '✅ Existe' : '❌ Manquant') . "</span>";
    echo "</div>";
}

echo "<br><a href='" . admin_url('admin.php?page=pdf-builder-templates') . "' class='button'>Retour à PDF Builder</a>";
?>


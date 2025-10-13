<?php
/**
 * Test de l'AJAX endpoint pour vérifier que les éléments React sont correctement traités
 */

define('PDF_GENERATOR_TEST_MODE', true);

// Définir les fonctions WordPress manquantes pour le test standalone
if (!function_exists('plugin_dir_path')) {
    function plugin_dir_path($file) {
        return dirname($file) . '/';
    }
}

if (!function_exists('plugin_dir_url')) {
    function plugin_dir_url($file) {
        return 'http://localhost/wp-content/plugins/wp-pdf-builder-pro/';
    }
}

// Simuler une requête AJAX comme le ferait le frontend React
$_POST['action'] = 'pdf_builder_generate_preview';
$_POST['elements'] = json_encode([
    [
        'id' => 'test-text-1',
        'type' => 'text',
        'content' => 'Texte rouge avec fond bleu',
        'x' => 50,
        'y' => 50,
        'width' => 150,
        'height' => 40,
        'fontSize' => 16,
        'fontFamily' => 'Arial, sans-serif',
        'fontWeight' => 'bold',
        'color' => '#ffffff',
        'backgroundColor' => '#0000ff', // Bleu
        'borderWidth' => 2,
        'borderColor' => '#ff0000', // Rouge
        'borderStyle' => 'solid',
        'padding' => 8,
        'textAlign' => 'center',
        'opacity' => 1,
        'rotation' => 0,
        'zIndex' => 1
    ],
    [
        'id' => 'test-text-2',
        'type' => 'text',
        'content' => 'Texte noir sans fond',
        'x' => 50,
        'y' => 100,
        'width' => 150,
        'height' => 30,
        'fontSize' => 14,
        'fontFamily' => 'Helvetica, sans-serif',
        'fontWeight' => 'normal',
        'color' => '#000000',
        'backgroundColor' => 'transparent',
        'borderWidth' => 1,
        'borderColor' => '#cccccc',
        'borderStyle' => 'solid',
        'padding' => 4,
        'textAlign' => 'left',
        'opacity' => 1,
        'rotation' => 0,
        'zIndex' => 2
    ]
]);

// Inclure WordPress et les dépendances
require_once __DIR__ . '/bootstrap.php';

// Simuler la fonction wp_die pour éviter qu'elle arrête le script
if (!function_exists('wp_die')) {
    function wp_die($message = '') {
        echo "WP_DIE: $message\n";
        exit;
    }
}

// Simuler la fonction wp_send_json pour capturer la réponse
if (!function_exists('wp_send_json')) {
    function wp_send_json($data) {
        echo "AJAX Response: " . json_encode($data) . "\n";
        exit;
    }
}

// Inclure le fichier AJAX
require_once __DIR__ . '/includes/pdf-preview-generator.php';

echo "Test terminé.\n";
<?php
/**
 * Script de test direct pour la fonction ajax_create_from_builtin
 */

// Simuler l'environnement WordPress
define('WP_USE_THEMES', false);
require_once('../../../wp-load.php');

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    die('Accès non autorisé');
}

echo "<h1>Test direct de ajax_create_from_builtin</h1>";

// Tester avec différents builtin_ids
$builtin_ids = ['classic', 'corporate', 'minimal', 'modern'];

foreach ($builtin_ids as $builtin_id) {
    echo "<h2>Test avec builtin_id: {$builtin_id}</h2>";

    // Simuler les données POST
    $_POST = [
        'builtin_id' => $builtin_id,
        'nonce' => wp_create_nonce('pdf_builder_create_from_builtin')
    ];

    // Créer une instance de PDF_Builder_Admin
    $admin = new PDF_Builder_Admin();

    // Capturer la sortie
    ob_start();
    try {
        $admin->ajax_create_from_builtin();
        $output = ob_get_clean();

        echo "<p>Résultat: {$output}</p>";
    } catch (Exception $e) {
        ob_end_clean();
        echo "<p>Exception: {$e->getMessage()}</p>";
    }

    echo "<hr>";
}

echo "<h2>Logs PHP récents:</h2>";
echo "<pre>";
$log_file = WP_CONTENT_DIR . '/debug.log';
if (file_exists($log_file)) {
    $lines = file($log_file);
    $recent_lines = array_slice($lines, -20); // Dernières 20 lignes
    foreach ($recent_lines as $line) {
        if (strpos($line, 'PDF Builder Debug') !== false) {
            echo htmlspecialchars($line);
        }
    }
} else {
    echo "Fichier de log non trouvé: {$log_file}";
}
echo "</pre>";
?>
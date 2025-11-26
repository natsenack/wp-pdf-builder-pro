<?php

/**
 * Test direct de ajaxGetTemplate
 * Simule l'appel AJAX pour diagnostiquer l'erreur 500
 */

echo "🔍 Test direct de ajaxGetTemplate\n";
echo "=================================\n\n";

// Inclure WordPress
require_once '../../../wp-load.php';

// Simuler les paramètres GET
$_GET['nonce'] = wp_create_nonce('pdf_builder_nonce');
$_GET['template_id'] = '1';

// Simuler un utilisateur connecté
if (!is_user_logged_in()) {
    wp_set_current_user(1); // Admin user
}

echo "✅ Environnement simulé\n";
echo "Utilisateur connecté: " . (is_user_logged_in() ? 'OUI' : 'NON') . "\n";
echo "Nonce généré: " . $_GET['nonce'] . "\n";
echo "Template ID: " . $_GET['template_id'] . "\n\n";

// Charger les classes nécessaires
require_once __DIR__ . '/../src/Admin/PDF_Builder_Admin.php';
require_once __DIR__ . '/../src/Admin/Handlers/AjaxHandler.php';

try {
    echo "📋 Initialisation des classes...\n";

    // Initialiser l'admin
    $admin = \PDF_Builder\Admin\PdfBuilderAdmin::getInstance();

    // Initialiser l'AjaxHandler
    $ajax_handler = new \PDF_Builder\Admin\Handlers\AjaxHandler($admin);

    echo "✅ Classes initialisées\n\n";

    echo "🚀 Test de ajaxGetTemplate...\n";

    // Appeler directement la méthode
    ob_start();
    $ajax_handler->ajaxGetTemplate();
    $output = ob_get_clean();

    echo "📄 Sortie de ajaxGetTemplate:\n";
    echo $output . "\n\n";

    echo "✅ Test terminé sans exception\n";

} catch (Exception $e) {
    echo "❌ Exception capturée: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\nTest terminé.\n";
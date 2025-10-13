<?php
/**
 * Script de test pour l'aperçu PDF Builder Pro
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure WordPress
require_once('../../../../wp-load.php');

echo "<h1>🔍 Test Aperçu PDF Builder Pro</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";

// Simuler un appel AJAX d'aperçu
echo "<h2>Test de l'action ajax_preview_order_pdf</h2>";

// Paramètres de test
$order_id = 123; // Remplacer par un ID de commande réel
$template_id = 1; // Remplacer par un ID de template réel

// Simuler les données POST
$_POST = array(
    'action' => 'pdf_builder_pro_preview_order_pdf',
    'order_id' => $order_id,
    'template_id' => $template_id,
    'nonce' => wp_create_nonce('pdf_builder_order_actions')
);

// Inclure la classe admin
require_once('includes/classes/class-pdf-builder-admin.php');

// Créer une instance
$admin = PDF_Builder_Admin::getInstance();

// Appeler la fonction d'aperçu
echo "<h3>Résultat de l'appel AJAX simulé :</h3>";
echo "<pre>";

try {
    $admin->ajax_preview_order_pdf();
} catch (Exception $e) {
    echo "Erreur: " . $e->getMessage();
}

echo "</pre>";

echo "<h2>Logs PHP (dernières lignes)</h2>";
echo "<p>Vérifiez les logs du serveur pour les messages de débogage détaillés.</p>";

?>
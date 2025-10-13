<?php
/**
 * Script de test rapide pour vérifier l'aperçu
 */

require_once('../wp-load.php');
require_once('includes/classes/class-pdf-builder-admin.php');

echo "✅ WordPress chargé\n";

try {
    $admin = PDF_Builder_Admin::getInstance();
    echo "✅ Classe PDF_Builder_Admin chargée\n";

    // Tester la fonction generate_unified_html avec un template par défaut
    $default_template = $admin->get_default_invoice_template();
    echo "✅ Template par défaut récupéré\n";

    // Créer un objet commande fictif pour les tests
    $order = null;
    if (function_exists('wc_get_order')) {
        // Essayer de récupérer une commande existante
        $orders = wc_get_orders(array('limit' => 1, 'status' => 'completed'));
        if (!empty($orders)) {
            $order = $orders[0];
            echo "✅ Commande de test trouvée: #" . $order->get_id() . "\n";
        }
    }

    // Générer l'HTML
    $html = $admin->generate_unified_html($default_template, $order);
    echo "✅ HTML généré, longueur: " . strlen($html) . " caractères\n";

    // Sauvegarder l'HTML pour inspection
    file_put_contents('test_preview_output.html', $html);
    echo "✅ HTML sauvegardé dans test_preview_output.html\n";

} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
?>
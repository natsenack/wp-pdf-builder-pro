<?php
/**
 * Script de débogage pour l'erreur AJAX "aperçu PDF" dans PDF Builder Pro
 */

// Activer l'affichage des erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Inclure WordPress
require_once('../../../wp-load.php');

echo "<h1>🔍 Diagnostic PDF Builder Pro - Aperçu PDF</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .error{color:red;} .success{color:green;} .info{color:blue;}</style>";

// Simuler les paramètres d'une commande réelle
$orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 1;
$templateId = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

echo "<h2>1. Test des classes et fonctions</h2>";

$tests = [
    'WordPress loaded' => function_exists('wp_verify_nonce'),
    'WooCommerce' => class_exists('WooCommerce'),
    'wc_get_order' => function_exists('wc_get_order'),
    'PDF_Builder_Admin class' => class_exists('PDF_Builder_Admin'),
];

foreach ($tests as $testName => $result) {
    echo "<p class='" . ($result ? 'success' : 'error') . "'>";
    echo ($result ? '✅' : '❌') . " <strong>$testName</strong>";
    echo "</p>";
}

// Test de l'action AJAX
echo "<h2>2. Test de l'action AJAX</h2>";
$ajax_action = 'pdf_builder_preview_order_pdf';
$has_action = has_action('wp_ajax_' . $ajax_action);

echo "<p class='" . ($has_action ? 'success' : 'error') . "'>";
echo ($has_action ? '✅' : '❌') . " Action AJAX <code>$ajax_action</code> " . ($has_action ? 'enregistrée' : 'NON enregistrée');
echo "</p>";

if (!$has_action) {
    echo "<p class='error'>❌ <strong>PROBLÈME :</strong> L'action AJAX n'est pas enregistrée. Vérifiez que PDF_Builder_Admin est instancié.</p>";
}

// Test de la commande WooCommerce
echo "<h2>3. Test de la commande WooCommerce</h2>";
$order = wc_get_order($orderId);

echo "<p class='" . ($order ? 'success' : 'error') . "'>";
echo ($order ? '✅' : '❌') . " Commande #$orderId : " . ($order ? 'Existe' : 'N\'existe pas');
echo "</p>";

if ($order) {
    echo "<p><strong>Numéro de commande :</strong> " . $order->get_order_number() . "</p>";
    echo "<p><strong>Statut :</strong> " . $order->get_status() . "</p>";
    echo "<p><strong>Total :</strong> " . $order->get_total() . " " . $order->get_currency() . "</p>";
}

// Test de simulation de la méthode ajax_preview_order_pdf
echo "<h2>4. Simulation de ajax_preview_order_pdf()</h2>";

try {
    // Simuler les vérifications de sécurité
    echo "<p>🔍 Test 1 - Vérification des permissions...</p>";
    if (!current_user_can('manage_woocommerce')) {
        echo "<p class='error'>❌ Permissions insuffisantes</p>";
    } else {
        echo "<p class='success'>✅ Permissions OK</p>";
    }

    // Simuler la vérification du nonce
    echo "<p>🔍 Test 2 - Vérification du nonce...</p>";
    $nonce = wp_create_nonce('pdf_builder_order_actions');
    if (wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
        echo "<p class='success'>✅ Nonce valide</p>";
    } else {
        echo "<p class='error'>❌ Nonce invalide</p>";
    }

    // Test du chargement du template
    echo "<p>🔍 Test 3 - Test du chargement du template...</p>";
    if (class_exists('PDF_Builder_Admin')) {
        $admin = new PDF_Builder_Admin();
        if (method_exists($admin, 'load_template_robust')) {
            echo "<p class='success'>✅ Méthode load_template_robust existe</p>";
        } else {
            echo "<p class='error'>❌ Méthode load_template_robust manquante</p>";
        }

        if (method_exists($admin, 'get_default_invoice_template')) {
            echo "<p class='success'>✅ Méthode get_default_invoice_template existe</p>";
        } else {
            echo "<p class='error'>❌ Méthode get_default_invoice_template manquante</p>";
        }

        if (method_exists($admin, 'generate_order_html')) {
            echo "<p class='success'>✅ Méthode generate_order_html existe</p>";
        } else {
            echo "<p class='error'>❌ Méthode generate_order_html manquante</p>";
        }
    } else {
        echo "<p class='error'>❌ Classe PDF_Builder_Admin non disponible</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Erreur lors de la simulation : " . $e->getMessage() . "</p>";
}

echo "<h2>5. Instructions de débogage</h2>";
echo "<ol>";
echo "<li><strong>Testez le bouton :</strong> Allez dans une commande WooCommerce et cliquez sur '👁️ Aperçu PDF'</li>";
echo "<li><strong>Vérifiez la console :</strong> Ouvrez F12 → Console pour voir les erreurs JavaScript</li>";
echo "<li><strong>Vérifiez les logs PHP :</strong> Dans <code>wp-content/debug.log</code></li>";
echo "<li><strong>Testez cette URL :</strong> <a href='?order_id=$orderId&template_id=$templateId' target='_blank'>Simuler l'appel AJAX</a></li>";
echo "</ol>";

echo "<p><strong>URL de ce script :</strong> <code>" . $_SERVER['REQUEST_URI'] . "</code></p>";
?>
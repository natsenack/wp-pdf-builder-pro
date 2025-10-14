<?php
/**
 * Diagnostic complet du système PDF Preview WooCommerce
 */

// Simuler un environnement WordPress
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

require_once('../../../wp-load.php');

echo "<h1>🔍 Diagnostic complet - PDF Preview WooCommerce</h1>";
echo "<style>body{font-family:Arial,sans-serif;margin:20px;} .success{color:green;} .error{color:red;} .warning{color:orange;} .info{color:blue;}</style>";

// 1. Vérifier les classes
echo "<h2>1. Vérification des classes</h2>";

if (class_exists('PDF_Builder_Pro')) {
    echo "<p class='success'>✅ Classe PDF_Builder_Pro chargée</p>";
    $main = new PDF_Builder_Pro();
} else {
    echo "<p class='error'>❌ Classe PDF_Builder_Pro non trouvée</p>";
    exit;
}

if (class_exists('PDF_Builder_WooCommerce_Integration')) {
    echo "<p class='success'>✅ Classe PDF_Builder_WooCommerce_Integration chargée</p>";
} else {
    echo "<p class='error'>❌ Classe PDF_Builder_WooCommerce_Integration non trouvée</p>";
}

// 2. Vérifier les hooks AJAX
echo "<h2>2. Vérification des hooks AJAX</h2>";

$ajax_hooks = [
    'wp_ajax_pdf_builder_pro_preview_order_pdf',
    'wp_ajax_pdf_builder_generate_order_pdf'
];

foreach ($ajax_hooks as $hook) {
    if (has_action($hook)) {
        echo "<p class='success'>✅ Hook $hook enregistré</p>";
    } else {
        echo "<p class='error'>❌ Hook $hook non enregistré</p>";
    }
}

// 3. Vérifier la nouvelle méthode publique
echo "<h2>3. Test de la méthode generate_order_pdf publique</h2>";

if (method_exists($main, 'generate_order_pdf')) {
    echo "<p class='success'>✅ Méthode generate_order_pdf existe</p>";

    // Tester avec une commande existante
    $order_id = 9275;
    echo "<p class='info'>🟡 Test avec commande #$order_id...</p>";

    $result = $main->generate_order_pdf($order_id, 0, true);

    if (is_wp_error($result)) {
        echo "<p class='error'>❌ Erreur méthode: " . $result->get_error_message() . "</p>";
        echo "<p class='info'>Code d'erreur: " . $result->get_error_code() . "</p>";
    } else {
        echo "<p class='success'>✅ Méthode fonctionne: <a href='$result' target='_blank'>Voir PDF</a></p>";
    }
} else {
    echo "<p class='error'>❌ Méthode generate_order_pdf n'existe pas</p>";
}

// 4. Vérifier WooCommerce
echo "<h2>4. Vérification WooCommerce</h2>";

if (class_exists('WooCommerce')) {
    echo "<p class='success'>✅ WooCommerce actif</p>";

    if (function_exists('wc_get_order')) {
        echo "<p class='success'>✅ Fonction wc_get_order disponible</p>";

        $order = wc_get_order(9275);
        if ($order) {
            echo "<p class='success'>✅ Commande #$order_id trouvée: " . $order->get_order_number() . "</p>";
            echo "<p class='info'>Statut: " . $order->get_status() . "</p>";
        } else {
            echo "<p class='error'>❌ Commande #$order_id non trouvée</p>";
        }
    } else {
        echo "<p class='error'>❌ Fonction wc_get_order non disponible</p>";
    }
} else {
    echo "<p class='error'>❌ WooCommerce non actif</p>";
}

// 5. Test des handlers AJAX directement
echo "<h2>5. Test des handlers AJAX</h2>";

if (class_exists('PDF_Builder_WooCommerce_Integration')) {
    $woo_integration = new PDF_Builder_WooCommerce_Integration($main);

    if (method_exists($woo_integration, 'ajax_preview_order_pdf')) {
        echo "<p class='success'>✅ Méthode ajax_preview_order_pdf existe</p>";
    } else {
        echo "<p class='error'>❌ Méthode ajax_preview_order_pdf n'existe pas</p>";
    }

    if (method_exists($woo_integration, 'ajax_generate_order_pdf')) {
        echo "<p class='success'>✅ Méthode ajax_generate_order_pdf existe</p>";
    } else {
        echo "<p class='error'>❌ Méthode ajax_generate_order_pdf n'existe pas</p>";
    }
}

echo "<hr><p><strong>Diagnostic terminé.</strong> Si tout est vert, le problème pourrait être côté serveur (cache, permissions, etc.).</p>";
?>
<?php
/**
 * PDF Builder Pro - Test WooCommerce Elements
 * Script de test pour vérifier le fonctionnement des éléments WooCommerce
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Inclure les classes nécessaires
require_once __DIR__ . '/PDF_Builder_WooCommerce_Data_Provider.php';

class PDF_Builder_WooCommerce_Test {

    public static function run_tests() {
        echo '<div class="wrap">';
        echo '<h1>🧪 Tests des Éléments WooCommerce</h1>';
        echo '<p>Test du fournisseur de données WooCommerce pour PDF Builder Pro.</p>';

        self::test_data_provider();
        self::test_element_types();
        self::test_woocommerce_integration();

        echo '<hr>';
        echo '<p><strong>✅ Tests terminés</strong></p>';
        echo '</div>';
    }

    private static function test_data_provider() {
        echo '<h2>📊 Test du Data Provider</h2>';

        $provider = PDF_Builder_WooCommerce_Data_Provider::getInstance();

        // Test des données de test
        echo '<h3>Données de test (sans commande) :</h3>';
        echo '<ul>';
        $test_elements = array(
            'woocommerce-invoice-number',
            'woocommerce-customer-name',
            'woocommerce-total',
            'woocommerce-products-table'
        );

        foreach ($test_elements as $element) {
            $data = $provider->get_element_data($element);
            echo '<li><strong>' . $element . ':</strong> ' . esc_html($data) . '</li>';
        }
        echo '</ul>';
    }

    private static function test_element_types() {
        echo '<h2>🏷️ Test des Types d\'Éléments</h2>';

        $element_types = array(
            // Facturation
            'woocommerce-invoice-number' => 'Numéro de Facture',
            'woocommerce-invoice-date' => 'Date de Facture',
            'woocommerce-order-number' => 'Numéro de Commande',
            'woocommerce-order-date' => 'Date de Commande',

            // Client
            'woocommerce-billing-address' => 'Adresse de Facturation',
            'woocommerce-shipping-address' => 'Adresse de Livraison',
            'woocommerce-customer-name' => 'Nom du Client',
            'woocommerce-customer-email' => 'Email du Client',

            // Paiement
            'woocommerce-payment-method' => 'Méthode de Paiement',
            'woocommerce-order-status' => 'Statut de Commande',

            // Produits et prix
            'woocommerce-products-table' => 'Tableau des Produits',
            'woocommerce-subtotal' => 'Sous-total',
            'woocommerce-discount' => 'Remise',
            'woocommerce-shipping' => 'Frais de Port',
            'woocommerce-taxes' => 'Taxes',
            'woocommerce-total' => 'Total',
            'woocommerce-refund' => 'Remboursement',
            'woocommerce-fees' => 'Frais Supplémentaires',

            // Devis
            'woocommerce-quote-number' => 'Numéro de Devis',
            'woocommerce-quote-date' => 'Date de Devis',
            'woocommerce-quote-validity' => 'Validité du Devis',
            'woocommerce-quote-notes' => 'Notes du Devis'
        );

        echo '<p><strong>Types d\'éléments supportés (' . count($element_types) . ') :</strong></p>';
        echo '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 10px; margin: 20px 0;">';

        foreach ($element_types as $type => $label) {
            echo '<div style="padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #f9f9f9;">';
            echo '<strong>' . esc_html($label) . '</strong><br>';
            echo '<code style="font-size: 12px; color: #666;">' . esc_html($type) . '</code>';
            echo '</div>';
        }

        echo '</div>';
    }

    private static function test_woocommerce_integration() {
        echo '<h2>🛒 Test d\'Intégration WooCommerce</h2>';

        if (!class_exists('WooCommerce')) {
            echo '<p style="color: #d63638;">⚠️ WooCommerce n\'est pas installé ou activé.</p>';
            return;
        }

        echo '<p style="color: #00a32a;">✅ WooCommerce est détecté et actif.</p>';

        // Tester avec une commande existante si possible
        $orders = wc_get_orders(array('limit' => 1, 'orderby' => 'date', 'order' => 'DESC'));

        if (!empty($orders)) {
            $order = $orders[0];
            echo '<h3>Test avec une vraie commande (#' . $order->get_order_number() . ') :</h3>';

            $provider = PDF_Builder_WooCommerce_Data_Provider::getInstance();

            echo '<ul>';
            $test_elements = array(
                'woocommerce-order-number',
                'woocommerce-customer-name',
                'woocommerce-total',
                'woocommerce-order-status'
            );

            foreach ($test_elements as $element) {
                $data = $provider->get_element_data($element, $order->get_id());
                echo '<li><strong>' . $element . ':</strong> ' . esc_html($data) . '</li>';
            }
            echo '</ul>';
        } else {
            echo '<p>Aucune commande trouvée pour les tests.</p>';
        }
    }
}

// Fonction pour afficher la page de test
function pdf_builder_woocommerce_test_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.'));
    }

    PDF_Builder_WooCommerce_Test::run_tests();
}
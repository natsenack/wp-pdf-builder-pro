<?php
/**
 * Test Script: Vérifier le rendu complet
 * 
 * Accès: http://website.com/wp-admin/?page=pdf-builder-test
 */

if (!defined('ABSPATH')) {
    die('No direct access');
}

add_action('admin_menu', function() {
    add_menu_page(
        'PDF Builder Test',
        'PDF Builder Test',
        'manage_woocommerce',
        'pdf-builder-test',
        'pdf_builder_test_page'
    );
});

function pdf_builder_test_page() {
    ?>
    <div class="wrap">
        <h1>PDF Builder - Test de rendu</h1>
        
        <form method="post" action="">
            <?php wp_nonce_field('pdf_builder_test'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row">Template ID:</th>
                    <td>
                        <select name="template_id">
                            <?php
                            global $wpdb;
                            $table = $wpdb->prefix . 'pdf_builder_templates';
                            $templates = $wpdb->get_results("SELECT id, name FROM $table ORDER BY id DESC");
                            foreach ($templates as $t) {
                                echo '<option value="' . $t->id . '">' . $t->name . ' (ID: ' . $t->id . ')</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Order ID:</th>
                    <td>
                        <input type="number" name="order_id" value="1" />
                    </td>
                </tr>
            </table>
            
            <?php submit_button('Tester le rendu'); ?>
        </form>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['template_id'])) {
            if (!wp_verify_nonce($_POST['_wpnonce'], 'pdf_builder_test')) {
                echo '<div class="notice notice-error"><p>Nonce invalide</p></div>';
                return;
            }
            
            $template_id = intval($_POST['template_id']);
            $order_id = intval($_POST['order_id'] ?? 1);
            
            echo '<h2>Résultats du test</h2>';
            
            // Récupérer le template
            global $wpdb;
            $table = $wpdb->prefix . 'pdf_builder_templates';
            $template = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $template_id));
            
            if (!$template) {
                echo '<div class="notice notice-error"><p>Template non trouvé</p></div>';
                return;
            }
            
            echo '<h3>1. Données du template</h3>';
            echo '<pre>' . wp_kses_post(substr($template->template_data, 0, 500)) . '...</pre>';
            
            $template_data = json_decode($template->template_data, true);
            if (!$template_data) {
                echo '<div class="notice notice-error"><p>Erreur JSON: ' . json_last_error_msg() . '</p></div>';
                return;
            }
            
            echo '<h3>2. Structure du JSON</h3>';
            echo '<ul>';
            echo '<li>Éléments: ' . count($template_data['elements'] ?? []) . '</li>';
            echo '<li>Canvas: ' . (isset($template_data['canvas']) ? 'Present' : 'Manquant') . '</li>';
            echo '</ul>';
            
            echo '<h3>3. Liste des éléments</h3>';
            echo '<table class="widefat"><thead><tr><th>Type</th><th>Position</th><th>Taille</th><th>Contenu</th></tr></thead><tbody>';
            foreach ($template_data['elements'] ?? [] as $el) {
                $content = '';
                if (isset($el['text'])) {
                    $content = substr($el['text'], 0, 50);
                } elseif (isset($el['imageUrl'])) {
                    $content = $el['imageUrl'];
                }
                echo '<tr>';
                echo '<td>' . $el['type'] . '</td>';
                echo '<td>(' . $el['x'] . ', ' . $el['y'] . ')</td>';
                echo '<td>' . $el['width'] . ' x ' . $el['height'] . '</td>';
                echo '<td>' . esc_html($content) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            
            // Tester la commande WooCommerce
            echo '<h3>4. Données WooCommerce (Order #' . $order_id . ')</h3>';
            $order = wc_get_order($order_id);
            if (!$order) {
                echo '<div class="notice notice-error"><p>Commande non trouvée</p></div>';
            } else {
                echo '<ul>';
                echo '<li>Client: ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '</li>';
                echo '<li>Produits: ' . count($order->get_items()) . '</li>';
                echo '<li>Total: ' . wc_price($order->get_total()) . '</li>';
                echo '</ul>';
                
                echo '<h4>Produits de la commande:</h4>';
                echo '<table class="widefat"><thead><tr><th>Produit</th><th>Quantité</th><th>Prix</th></tr></thead><tbody>';
                foreach ($order->get_items() as $item) {
                    $product = $item->get_product();
                    if ($product) {
                        echo '<tr>';
                        echo '<td>' . $product->get_name() . '</td>';
                        echo '<td>' . $item->get_quantity() . '</td>';
                        echo '<td>' . wc_price($product->get_price()) . '</td>';
                        echo '</tr>';
                    }
                }
                echo '</tbody></table>';
            }
        }
        ?>
    </div>
    <?php
}

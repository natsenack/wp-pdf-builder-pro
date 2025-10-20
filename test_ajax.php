<?php
// Test script pour vérifier que l'AJAX retourne des éléments
echo "=== TEST AJAX pdf_builder_get_canvas_elements ===\n\n";

// Simuler les données POST
$_POST = [
    'action' => 'pdf_builder_get_canvas_elements',
    'template_id' => '1',
    'nonce' => 'test-nonce'
];

// Simuler les fonctions WordPress nécessaires
define('MINUTE_IN_SECONDS', 60);

function wp_verify_nonce($nonce, $action) { return true; }
function current_user_can($cap) { return true; }
function get_post($id) { return (object) ['ID' => $id, 'post_title' => 'Test Template']; }
function get_transient($key) { return false; }
function set_transient($key, $value, $expiration) { return true; }
function get_post_meta($post_id, $key, $single) { return []; }
function get_current_user_id() { return 1; }

function wp_send_json_success($data) {
    echo "✅ AJAX SUCCESS\n";
    echo "Nombre d'éléments: " . count($data['elements']) . "\n";
    echo "Template ID: " . $data['template_id'] . "\n";
    exit;
}

function wp_send_json_error($message) {
    echo "❌ AJAX ERROR: $message\n";
    exit;
}

function log_message($message) {
    echo "LOG: $message\n";
}

// Simuler la classe
class PDF_Builder_WooCommerce_Integration {
    public function ajax_get_canvas_elements() {
        try {
            log_message('PDF Builder: ajax_get_canvas_elements called');

            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
            log_message('PDF Builder: Template ID: ' . $template_id);

            // TEMPORAIRE : Retourner des éléments de test
            log_message('PDF Builder: Using test elements for template ' . $template_id);
            $canvas_elements = [
                [
                    'id' => 'header-text',
                    'type' => 'text',
                    'x' => 50,
                    'y' => 50,
                    'width' => 200,
                    'height' => 30,
                    'content' => 'PDF Builder Pro - Test Template',
                    'fontSize' => 18,
                    'fontWeight' => 'bold',
                    'color' => '#007cba',
                    'textAlign' => 'center'
                ],
                [
                    'id' => 'order-info',
                    'type' => 'text',
                    'x' => 50,
                    'y' => 100,
                    'width' => 300,
                    'height' => 60,
                    'content' => 'Commande #{order_number}\nClient: {customer_name}\nDate: {order_date}',
                    'fontSize' => 12,
                    'color' => '#333333'
                ],
                [
                    'id' => 'rectangle-bg',
                    'type' => 'rectangle',
                    'x' => 40,
                    'y' => 40,
                    'width' => 515,
                    'height' => 802,
                    'backgroundColor' => '#ffffff',
                    'borderColor' => '#dddddd',
                    'borderWidth' => 1
                ]
            ];

            wp_send_json_success([
                'elements' => $canvas_elements,
                'template_id' => $template_id,
                'cached' => false,
                'element_count' => count($canvas_elements)
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }
}

// Exécuter le test
$integration = new PDF_Builder_WooCommerce_Integration();
$integration->ajax_get_canvas_elements();
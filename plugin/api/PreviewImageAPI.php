<?php
namespace WP_PDF_Builder_Pro\Api;

class PreviewImageAPI {
    public function __construct() {
        add_action('wp_ajax_wp_pdf_preview_image', array($this, 'generate_preview'));
    }

    public function generate_preview() {
        // Vérification nonce et permissions
        if (!wp_verify_nonce($_POST['nonce'], 'wp_pdf_preview_nonce')) {
            wp_die('Forbidden');
        }

        if (!current_user_can('manage_options')) {
            wp_die('Forbidden');
        }

        // Récupération données POST
        $template_data = json_decode(stripslashes($_POST['template_data']), true);
        $preview_type = sanitize_text_field($_POST['preview_type']);
        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;

        try {
            // Création générateur selon type
            $generator = $this->create_generator($template_data, $preview_type, $order_id);

            // Génération aperçu
            $image_path = $generator->generate_preview_image();

            // Retour URL de l'image
            wp_send_json_success(array(
                'image_url' => $image_path,
                'cache_key' => md5(serialize($template_data))
            ));

        } catch (Exception $e) {
            wp_send_json_error(array(
                'message' => $e->getMessage()
            ));
        }
    }

    private function create_generator($template_data, $preview_type, $order_id) {
        if ($preview_type === 'order' && $order_id && function_exists('wc_get_order')) {
            $order = wc_get_order($order_id);
            return new \WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, false, $order);
        } else {
            // Données fictives pour aperçu design
            return new \WP_PDF_Builder_Pro\Generators\PDFGenerator($template_data, true, null);
        }
    }
}
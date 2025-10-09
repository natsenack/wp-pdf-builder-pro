<?php
/**
 * PDF Builder Pro - WooCommerce Integration Manager
 * Handles automatic PDF generation, email attachments, and WooCommerce-specific features
 *
 * @package PDF_Builder_Pro
 * @version 1.0.0
 * @since Phase 5
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class PDF_Builder_WooCommerce_Integration {

    /**
     * Singleton instance
     */
    private static $instance = null;

    /**
     * WooCommerce order statuses that trigger PDF generation
     */
    private $pdf_trigger_statuses = ['processing', 'completed', 'on-hold'];

    /**
     * WooCommerce order statuses that attach PDF to emails
     */
    private $email_attach_statuses = ['completed', 'processing'];

    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor - Initialize WooCommerce hooks
     */
    private function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize WooCommerce hooks
     */
    private function init_hooks() {
        // Order status change hooks
        add_action('woocommerce_order_status_changed', [$this, 'handle_order_status_change'], 10, 4);

        // Email attachment hooks
        add_filter('woocommerce_email_attachments', [$this, 'attach_pdf_to_email'], 10, 4);

        // Admin order actions
        add_action('woocommerce_admin_order_actions_end', [$this, 'add_order_pdf_actions'], 10, 1);
        add_action('wp_ajax_regenerate_order_pdf', [$this, 'ajax_regenerate_order_pdf']);
        add_action('wp_ajax_download_order_pdf', [$this, 'ajax_download_order_pdf']);

        // Admin order meta boxes
        add_action('add_meta_boxes', [$this, 'add_order_pdf_meta_box']);

        // WooCommerce settings integration
        add_filter('woocommerce_get_settings_pages', [$this, 'add_woocommerce_settings_page']);
    }

    /**
     * Handle order status changes - Generate PDF when order reaches configured status
     */
    public function handle_order_status_change($order_id, $old_status, $new_status, $order) {
        // Check if PDF generation is enabled for this status
        $enabled_statuses = get_option('pdf_builder_wc_status_templates', []);
        if (!in_array($new_status, $enabled_statuses)) {
            return;
        }

        try {
            $this->generate_order_pdf($order_id, $new_status);
        } catch (Exception $e) {
            error_log('PDF Builder Pro - Order PDF generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Attach PDF to WooCommerce emails
     */
    public function attach_pdf_to_email($attachments, $email_id, $order, $email = null) {
        // Check if PDF attachment is enabled for this email type
        $enabled_emails = get_option('pdf_builder_wc_email_attachments', []);
        if (!in_array($email_id, $enabled_emails)) {
            return $attachments;
        }

        try {
            $pdf_path = $this->get_or_generate_order_pdf_for_email($order->get_id(), $email_id);
            if ($pdf_path && file_exists($pdf_path)) {
                $attachments[] = $pdf_path;
            }
        } catch (Exception $e) {
            error_log('PDF Builder Pro - Email PDF attachment failed: ' . $e->getMessage());
        }

        return $attachments;
    }

    /**
     * Generate PDF for order
     */
    public function generate_order_pdf($order_id, $status = null) {
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Order not found');
        }

        // Get template for this status
        $template_id = $this->get_template_for_status($status ?: $order->get_status());
        if (!$template_id) {
            throw new Exception('No template configured for status: ' . $status);
        }

        // Prepare order data for template
        $order_data = $this->prepare_order_data($order);

        // Generate PDF using core generator
        $pdf_generator = PDF_Builder_Core::get_instance()->get('pdf_generator');
        $pdf_content = $pdf_generator->generate_from_template($template_id, $order_data);

        // Save PDF to uploads directory
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder-order-pdfs/';
        wp_mkdir_p($pdf_dir);

        $filename = 'order-' . $order_id . '-' . $status . '.pdf';
        $filepath = $pdf_dir . $filename;

        file_put_contents($filepath, $pdf_content);

        // Store PDF path in order meta
        update_post_meta($order_id, '_pdf_builder_order_pdf_' . $status, $filepath);

        return $filepath;
    }

    /**
     * Generate PDF for order email
     */
    public function generate_order_pdf_for_email($order_id, $email_id, $template_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            throw new Exception('Order not found');
        }

        // Prepare order data for template
        $order_data = $this->prepare_order_data($order);

        // Generate PDF using core generator
        $pdf_generator = PDF_Builder_Core::get_instance()->get('pdf_generator');
        $pdf_content = $pdf_generator->generate_from_template($template_id, $order_data);

        // Save PDF to uploads directory
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder-order-pdfs/';
        wp_mkdir_p($pdf_dir);

        $filename = 'order-' . $order_id . '-email-' . $email_id . '.pdf';
        $filepath = $pdf_dir . $filename;

        file_put_contents($filepath, $pdf_content);

        // Store PDF path in order meta
        update_post_meta($order_id, '_pdf_builder_order_pdf_email_' . $email_id, $filepath);

        return $filepath;
    }

    /**
     * Get or generate PDF for order
     */
    private function get_or_generate_order_pdf($order_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        $status = $order->get_status();
        $meta_key = '_pdf_builder_order_pdf_' . $status;
        $pdf_path = get_post_meta($order_id, $meta_key, true);

        if ($pdf_path && file_exists($pdf_path)) {
            return $pdf_path;
        }

        // Generate if not exists
        try {
            return $this->generate_order_pdf($order_id, $status);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get or generate PDF for order email
     */
    private function get_or_generate_order_pdf_for_email($order_id, $email_id) {
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }

        // Get template for this email type
        $template_id = $this->get_template_for_email($email_id);
        if (!$template_id) {
            return false; // No template configured for this email
        }

        $meta_key = '_pdf_builder_order_pdf_email_' . $email_id;
        $pdf_path = get_post_meta($order_id, $meta_key, true);

        if ($pdf_path && file_exists($pdf_path)) {
            return $pdf_path;
        }

        // Generate if not exists
        try {
            return $this->generate_order_pdf_for_email($order_id, $email_id, $template_id);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get template ID for order status
     */
    private function get_template_for_status($status) {
        $status_templates = get_option('pdf_builder_wc_status_templates_config', []);
        return isset($status_templates[$status]) ? $status_templates[$status] : null;
    }

    /**
     * Get template ID for email type
     */
    private function get_template_for_email($email_id) {
        $email_templates = get_option('pdf_builder_wc_email_templates_config', []);
        return isset($email_templates[$email_id]) ? $email_templates[$email_id] : null;
    }

    /**
     * Prepare order data for template variables
     */
    private function prepare_order_data($order) {
        $data = [
            'order_id' => $order->get_id(),
            'order_number' => $order->get_order_number(),
            'order_date' => $order->get_date_created()->format('Y-m-d H:i:s'),
            'order_status' => $order->get_status(),
            'customer_name' => $order->get_formatted_billing_full_name(),
            'customer_email' => $order->get_billing_email(),
            'billing_address' => $order->get_formatted_billing_address(),
            'shipping_address' => $order->get_formatted_shipping_address(),
            'payment_method' => $order->get_payment_method_title(),
            'shipping_method' => $order->get_shipping_method(),
            'order_total' => $order->get_formatted_order_total(),
            'order_subtotal' => $order->get_subtotal_to_display(),
            'order_tax' => $order->get_tax_totals(),
            'order_discount' => $order->get_discount_total(),
            'order_shipping' => $order->get_shipping_total(),
            'order_items' => []
        ];

        // Add order items
        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $data['order_items'][] = [
                'name' => $item->get_name(),
                'quantity' => $item->get_quantity(),
                'price' => $item->get_total(),
                'sku' => $product ? $product->get_sku() : '',
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id()
            ];
        }

        return $data;
    }

    /**
     * Add PDF actions to order admin
     */
    public function add_order_pdf_actions($order) {
        $pdf_url = admin_url('admin-ajax.php?action=download_order_pdf&order_id=' . $order->get_id());
        echo '<a href="' . esc_url($pdf_url) . '" class="button wc-action-button wc-action-button-pdf" target="_blank">📄 PDF</a>';

        $regenerate_url = admin_url('admin-ajax.php?action=regenerate_order_pdf&order_id=' . $order->get_id());
        echo '<a href="' . esc_url($regenerate_url) . '" class="button wc-action-button wc-action-button-regenerate-pdf">🔄 Régénérer PDF</a>';
    }

    /**
     * AJAX handler for regenerating order PDF
     */
    public function ajax_regenerate_order_pdf() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Unauthorized');
        }

        $order_id = intval($_GET['order_id']);
        $order = wc_get_order($order_id);

        if (!$order) {
            wp_die('Order not found');
        }

        try {
            $this->generate_order_pdf($order_id, $order->get_status());
            wp_redirect(admin_url('post.php?post=' . $order_id . '&action=edit&pdf_regenerated=1'));
        } catch (Exception $e) {
            wp_die('PDF generation failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler for downloading order PDF
     */
    public function ajax_download_order_pdf() {
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Unauthorized');
        }

        $order_id = intval($_GET['order_id']);
        $pdf_path = $this->get_or_generate_order_pdf($order_id);

        if (!$pdf_path || !file_exists($pdf_path)) {
            wp_die('PDF not found');
        }

        // Force download
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="order-' . $order_id . '.pdf"');
        header('Content-Length: ' . filesize($pdf_path));
        readfile($pdf_path);
        exit;
    }

    /**
     * Add PDF meta box to order edit page
     */
    public function add_order_pdf_meta_box() {
        add_meta_box(
            'pdf-builder-order-pdf',
            'PDF Builder - Documents',
            [$this, 'render_order_pdf_meta_box'],
            'shop_order',
            'side',
            'default'
        );
    }

    /**
     * Render PDF meta box content
     */
    public function render_order_pdf_meta_box($post) {
        $order = wc_get_order($post->ID);
        if (!$order) {
            return;
        }

        echo '<div id="pdf-builder-order-pdf-meta-box">';
        echo '<p><strong>Documents PDF générés :</strong></p>';

        $pdfs = $this->get_order_pdfs($post->ID);
        if (empty($pdfs)) {
            echo '<p>Aucun PDF généré pour cette commande.</p>';
        } else {
            echo '<ul>';
            foreach ($pdfs as $status => $pdf_path) {
                if (file_exists($pdf_path)) {
                    $filename = basename($pdf_path);
                    $download_url = admin_url('admin-ajax.php?action=download_order_pdf&order_id=' . $post->ID);
                    echo '<li><a href="' . esc_url($download_url) . '" target="_blank">' . esc_html($filename) . '</a></li>';
                }
            }
            echo '</ul>';
        }

        $regenerate_url = admin_url('admin-ajax.php?action=regenerate_order_pdf&order_id=' . $post->ID);
        echo '<p><a href="' . esc_url($regenerate_url) . '" class="button">🔄 Régénérer PDF</a></p>';
        echo '</div>';
    }

    /**
     * Get all PDFs for an order
     */
    private function get_order_pdfs($order_id) {
        global $wpdb;
        $pdfs = [];

        $meta_keys = $wpdb->get_results(PDF_Builder_Debug_Helper::safe_wpdb_prepare(
            "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s",
            $order_id,
            '_pdf_builder_order_pdf_%'
        ));

        foreach ($meta_keys as $meta) {
            $status = str_replace('_pdf_builder_order_pdf_', '', $meta->meta_key);
            $pdfs[$status] = $meta->meta_value;
        }

        return $pdfs;
    }

    /**
     * Add WooCommerce settings page
     */
    public function add_woocommerce_settings_page($settings) {
        $settings[] = include_once plugin_dir_path(__FILE__) . 'PDF_Builder_WooCommerce_Settings.php';
        return $settings;
    }

    /**
     * Get WooCommerce integration settings
     */
    public function get_settings() {
        return [
            'enabled_statuses' => get_option('pdf_builder_wc_status_templates', []),
            'email_attachments' => get_option('pdf_builder_wc_email_attachments', []),
            'status_templates' => get_option('pdf_builder_wc_status_templates_config', [])
        ];
    }

    /**
     * Update WooCommerce integration settings
     */
    public function update_settings($settings) {
        if (isset($settings['enabled_statuses'])) {
            update_option('pdf_builder_wc_status_templates', $settings['enabled_statuses']);
        }
        if (isset($settings['email_attachments'])) {
            update_option('pdf_builder_wc_email_attachments', $settings['email_attachments']);
        }
        if (isset($settings['status_templates'])) {
            update_option('pdf_builder_wc_status_templates_config', $settings['status_templates']);
        }
    }
}

<?php
/**
 * PDF Builder Pro - WooCommerce Integration Manager
 * Gestion de l'intégration WooCommerce
 */

class PDF_Builder_WooCommerce_Integration {

    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance) {
        $this->main = $main_instance;
        $this->init_hooks();
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf']);
        add_action('wp_ajax_pdf_builder_pro_preview_order_pdf', [$this, 'ajax_preview_order_pdf']);
    }

    /**
     * Détecte automatiquement le type de document basé sur le statut de la commande
     */
    private function detect_document_type($order_status) {
        $status_mapping = [
            'pending' => 'devis',
            'processing' => 'commande',
            'on-hold' => 'commande',
            'completed' => 'facture',
            'cancelled' => 'annulation',
            'refunded' => 'remboursement',
            'failed' => 'erreur'
        ];

        return isset($status_mapping[$order_status]) ? $status_mapping[$order_status] : 'commande';
    }

    /**
     * Retourne le label du type de document
     */
    private function get_document_type_label($document_type) {
        $labels = [
            'devis' => 'Devis',
            'commande' => 'Bon de commande',
            'facture' => 'Facture',
            'annulation' => 'Annulation',
            'remboursement' => 'Remboursement',
            'erreur' => 'Document d\'erreur'
        ];

        return isset($labels[$document_type]) ? $labels[$document_type] : 'Document';
    }

    /**
     * Ajoute la meta box PDF Builder dans les commandes WooCommerce
     */
    public function add_woocommerce_order_meta_box() {
        // Vérifier que nous sommes sur la bonne page
        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // Support both legacy (shop_order) and HPOS (woocommerce_page_wc-orders) screens
        $valid_screens = ['shop_order', 'woocommerce_page_wc-orders'];
        if (!in_array($screen->id, $valid_screens)) {
            return;
        }

        add_meta_box(
            'pdf-builder-order-actions',
            __('PDF Builder Pro', 'pdf-builder-pro'),
            [$this, 'render_woocommerce_order_meta_box'],
            $screen->id,
            'side',
            'high'
        );
    }

    /**
     * Rend la meta box dans les commandes WooCommerce
     */
    /**
     * Rend la meta box dans les commandes WooCommerce - VERSION ULTRA-MODERNE 2025
     */
    public function render_woocommerce_order_meta_box($post_or_order) {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Handle both legacy (WP_Post) and HPOS (WC_Order) cases
        if (is_a($post_or_order, 'WC_Order')) {
            $order = $post_or_order;
            $order_id = $order->get_id();
        } elseif (is_a($post_or_order, 'WP_Post')) {
            $order_id = $post_or_order->ID;
            $order = wc_get_order($order_id);
        } else {
            // Try to get order ID from URL for HPOS
            $order_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
            $order = wc_get_order($order_id);
        }

        if (!$order) {
            echo '<div style="padding: 20px; text-align: center; color: #dc3545; background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%); border: 1px solid #f8bbd9; border-radius: 12px; box-shadow: 0 4px 12px rgba(220, 53, 69, 0.2);">
                    <div style="font-size: 48px; margin-bottom: 10px;">❌</div>
                    <strong style="font-size: 16px;">Commande invalide</strong><br>
                    <small style="color: #6c757d;">ID commande: ' . esc_html($order_id) . '</small>
                  </div>';
            return;
        }

        // Détecter automatiquement le type de document basé sur le statut de la commande
        $order_status = $order->get_status();
        $document_type = $this->detect_document_type($order_status);
        $document_type_label = $this->get_document_type_label($document_type);

        // Récupérer tous les templates disponibles
        $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name ASC", ARRAY_A);

        // Vérifier d'abord s'il y a un mapping spécifique pour ce statut de commande
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;
        $selected_template = null;

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            // Il y a un mapping spécifique pour ce statut
            $selected_template = $wpdb->get_row($wpdb->prepare(
                "SELECT id, name FROM $table_templates WHERE id = %d",
                $status_templates[$status_key]
            ), ARRAY_A);
        }

        // Si pas de mapping spécifique, utiliser la logique de détection automatique
        if (!$selected_template && !empty($all_templates)) {
            // Chercher un template dont le nom contient le type de document détecté
            foreach ($all_templates as $template) {
                if (stripos($template['name'], $document_type_label) !== false) {
                    $selected_template = $template;
                    break;
                }
            }

            // Fallback: prendre le premier template disponible
            if (!$selected_template) {
                $selected_template = $all_templates[0];
            }
        }

        wp_nonce_field('pdf_builder_order_actions', 'pdf_builder_order_nonce');
        ?>
        <style>
        /* PDF Builder Pro - Ultra Modern Meta Box Styles 2025 */
        #pdf-builder-ultra-metabox {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.5;
            color: #1a1a1a;
            position: relative;
        }

        /* Header Section - Ultra Modern */
        .ultra-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            margin: -12px -12px 20px -12px;
            border-radius: 12px 12px 0 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .ultra-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.08)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
            animation: float 20s ease-in-out infinite;
        }

        .ultra-header-content {
            position: relative;
            z-index: 1;
        }

        .ultra-order-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .ultra-order-title .order-icon {
            font-size: 24px;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
            animation: bounceIn 0.6s ease-out;
        }

        .ultra-order-meta {
            font-size: 14px;
            opacity: 0.9;
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .ultra-status-badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Document Type Section */
        .ultra-doc-type {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(240, 147, 251, 0.3);
        }

        .ultra-doc-type::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 4s ease-in-out infinite;
        }

        .ultra-doc-type-content {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .ultra-doc-icon {
            font-size: 28px;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3));
            animation: bounceIn 0.8s ease-out;
        }

        .ultra-doc-info h4 {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: 700;
        }

        .ultra-doc-info p {
            margin: 0;
            font-size: 13px;
            opacity: 0.9;
        }

        /* Template Section */
        .ultra-template-section {
            margin-bottom: 20px;
        }

        .ultra-template-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .ultra-template-title {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a1a;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ultra-template-toggle {
            font-size: 12px;
            color: #6c757d;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .ultra-template-toggle:hover {
            background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
            color: #495057;
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .ultra-template-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 18px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .ultra-template-display:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .ultra-template-icon {
            font-size: 20px;
            opacity: 0.9;
        }

        .ultra-template-info {
            flex: 1;
        }

        .ultra-template-name {
            font-weight: 600;
            font-size: 15px;
            margin-bottom: 2px;
        }

        .ultra-template-meta {
            font-size: 12px;
            opacity: 0.8;
        }

        .ultra-template-selector {
            display: none;
            margin-top: 12px;
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            padding: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            animation: slideInUp 0.3s ease-out;
        }

        .ultra-template-search {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            margin-bottom: 12px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .ultra-template-search:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        .ultra-template-list {
            max-height: 250px;
            overflow-y: auto;
            border-radius: 8px;
        }

        .ultra-template-list::-webkit-scrollbar {
            width: 6px;
        }

        .ultra-template-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .ultra-template-list::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }

        .ultra-template-list::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }

        .ultra-template-option {
            padding: 10px 14px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid transparent;
        }

        .ultra-template-option:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-color: #dee2e6;
            transform: translateX(4px);
        }

        .ultra-template-option.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-color: rgba(255,255,255,0.3);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .ultra-template-option .template-icon {
            font-size: 16px;
        }

        /* Action Buttons */
        .ultra-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 20px;
        }

        .ultra-btn {
            padding: 16px 22px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .ultra-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.6s ease;
        }

        .ultra-btn:hover::before {
            left: 100%;
        }

        .ultra-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .ultra-btn:active {
            transform: translateY(-1px) scale(1.01);
        }

        .ultra-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        }

        .ultra-btn.loading {
            color: transparent !important;
        }

        .ultra-btn.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 24px;
            height: 24px;
            margin: -12px 0 0 -12px;
            border: 3px solid rgba(255,255,255, 0.3);
            border-top: 3px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .ultra-btn-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .ultra-btn-preview:hover {
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .ultra-btn-generate {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .ultra-btn-generate:hover {
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .ultra-btn-download {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: #212529;
            display: none;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }

        .ultra-btn-download:hover {
            box-shadow: 0 8px 25px rgba(255, 193, 7, 0.4);
        }

        /* Status Messages */
        .ultra-status {
            padding: 14px 18px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            text-align: center;
            margin-top: 16px;
            opacity: 0;
            transform: translateY(-10px);
            transition: all 0.4s ease;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .ultra-status.show {
            opacity: 1;
            transform: translateY(0);
            animation: bounceIn 0.5s ease-out;
        }

        .ultra-status-loading {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            border: 1px solid #90caf9;
            animation: pulse 2s infinite;
        }

        .ultra-status-success {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            color: #2e7d32;
            border: 1px solid #81c784;
        }

        .ultra-status-error {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #c62828;
            border: 1px solid #e57373;
        }

        /* PDF Preview Modal - Ultra Modern 2025 */
        .ultra-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(12px);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s ease;
        }

        .ultra-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .ultra-modal {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5);
            max-width: 98vw;
            max-height: 98vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transform: scale(0.8) translateY(30px) rotate(-2deg);
            transition: all 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
        }

        .ultra-modal.show .ultra-modal {
            transform: scale(1) translateY(0) rotate(0deg);
        }

        .ultra-modal-header {
            padding: 28px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 20px 20px 0 0;
            position: relative;
            overflow: hidden;
        }

        .ultra-modal-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 50%);
            animation: float 15s ease-in-out infinite;
        }

        .ultra-modal-title {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }

        .ultra-modal-title .modal-icon {
            font-size: 32px;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.3));
            animation: bounceIn 0.6s ease-out;
        }

        .ultra-modal-controls {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 1;
        }

        .ultra-zoom-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.15);
            border-radius: 12px;
            padding: 8px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
        }

        .ultra-zoom-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 10px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 20px;
            color: white;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .ultra-zoom-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .ultra-zoom-btn:active {
            transform: scale(0.95);
        }

        .ultra-zoom-level {
            font-size: 16px;
            font-weight: 700;
            min-width: 60px;
            text-align: center;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }

        .ultra-close-btn {
            background: rgba(255,255,255,0.2);
            border: none;
            border-radius: 50%;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 26px;
            color: white;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .ultra-close-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .ultra-modal-body {
            flex: 1;
            padding: 28px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            overflow: auto;
        }

        .ultra-preview-container {
            background: white;
            border: 3px solid #e1e8ed;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 650px;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .ultra-preview-container:hover {
            box-shadow: 0 16px 50px rgba(0,0,0,0.2);
            transform: translateY(-2px);
        }

        .ultra-preview-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
            color: #6c757d;
            font-size: 18px;
            animation: bounceIn 0.8s ease-out;
        }

        .ultra-spinner {
            width: 56px;
            height: 56px;
            border: 5px solid #e1e8ed;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .ultra-preview-info {
            margin-top: 24px;
            padding: 24px;
            background: white;
            border: 1px solid #e1e8ed;
            border-radius: 16px;
            font-size: 15px;
            color: #495057;
            box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        }

        .ultra-preview-info-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 16px;
            font-weight: 600;
            font-size: 16px;
        }

        .ultra-preview-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 16px;
        }

        .ultra-preview-stat {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #f1f1f1;
        }

        .ultra-preview-stat:last-child {
            border-bottom: none;
        }

        .ultra-preview-stat-label {
            color: #6c757d;
            font-weight: 500;
        }

        .ultra-preview-stat-value {
            color: #495057;
            font-weight: 600;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
        }

        /* Animations */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-10px) rotate(5deg); }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3) rotate(-10deg);
            }
            50% {
                opacity: 1;
                transform: scale(1.05) rotate(5deg);
            }
            70% {
                transform: scale(0.9) rotate(-2deg);
            }
            100% {
                opacity: 1;
                transform: scale(1) rotate(0deg);
            }
        }

        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(102, 126, 234, 0.3); }
            50% { box-shadow: 0 0 30px rgba(102, 126, 234, 0.6); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .ultra-actions {
                grid-template-columns: 1fr;
            }

            .ultra-modal {
                max-width: 98vw;
                max-height: 98vh;
                border-radius: 16px;
            }

            .ultra-modal-header {
                padding: 20px 24px;
                flex-direction: column;
                gap: 16px;
                text-align: center;
            }

            .ultra-modal-title {
                font-size: 20px;
                justify-content: center;
            }

            .ultra-zoom-controls {
                display: none;
            }

            .ultra-modal-controls {
                width: 100%;
                justify-content: center;
            }

            .ultra-btn {
                padding: 14px 18px;
                font-size: 14px;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            #pdf-builder-ultra-metabox {
                color: #e9ecef;
            }

            .ultra-template-selector {
                background: #343a40;
                border-color: #495057;
            }

            .ultra-template-search {
                background: #495057;
                border-color: #6c757d;
                color: #e9ecef;
            }

            .ultra-template-option:hover {
                background: #495057;
            }
        }

        /* Loading states */
        .ultra-loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        </style>

        <div id="pdf-builder-ultra-metabox">
            <!-- Header Section -->
            <div class="ultra-header">
                <div class="ultra-header-content">
                    <h3 class="ultra-order-title">
                        <span class="order-icon">🚀</span>
                        Commande #<?php echo esc_html($order->get_order_number()); ?>
                    </h3>
                    <div class="ultra-order-meta">
                        <span><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span>
                        <span>•</span>
                        <span><?php echo esc_html($order->get_date_created()->format('d/m/Y H:i')); ?></span>
                        <span class="ultra-status-badge">
                            <?php echo esc_html($document_type_label); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Document Type Section -->
            <div class="ultra-doc-type">
                <div class="ultra-doc-type-content">
                    <span class="ultra-doc-icon">📄</span>
                    <div class="ultra-doc-info">
                        <h4><?php echo esc_html($document_type_label); ?> détecté automatiquement</h4>
                        <p>Statut: <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?> • Template intelligent sélectionné</p>
                    </div>
                </div>
            </div>

            <!-- Template Section -->
            <div class="ultra-template-section">
                <div class="ultra-template-header">
                    <h4 class="ultra-template-title">
                        <span>🎨</span>
                        Template sélectionné
                    </h4>
                    <span class="ultra-template-toggle" id="ultra-template-toggle">
                        Changer de template ▼
                    </span>
                </div>

                <div class="ultra-template-display" id="ultra-template-display">
                    <span class="ultra-template-icon">📋</span>
                    <div class="ultra-template-info">
                        <div class="ultra-template-name">
                            <?php echo $selected_template ? esc_html($selected_template['name']) : 'Aucun template disponible'; ?>
                        </div>
                        <div class="ultra-template-meta">
                            <?php if ($selected_template): ?>
                                Template automatiquement détecté • Prêt pour génération
                            <?php else: ?>
                                Aucun template trouvé dans la base de données
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="ultra-template-selector" id="ultra-template-selector">
                    <input type="text"
                           class="ultra-template-search"
                           placeholder="🔍 Rechercher un template..."
                           id="ultra-template-search">

                    <div class="ultra-template-list" id="ultra-template-list">
                        <?php if (!empty($all_templates)): ?>
                            <?php foreach ($all_templates as $template): ?>
                                <div class="ultra-template-option <?php echo ($selected_template && $selected_template['id'] == $template['id']) ? 'selected' : ''; ?>"
                                     data-template-id="<?php echo esc_attr($template['id']); ?>">
                                    <span class="template-icon">📄</span>
                                    <span><?php echo esc_html($template['name']); ?></span>
                                    <?php if ($selected_template && $selected_template['id'] == $template['id']): ?>
                                        <span style="margin-left: auto; color: rgba(255,255,255,0.8);">✓</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="text-align: center; color: #6c757d; padding: 40px; font-style: italic;">
                                <div style="font-size: 48px; margin-bottom: 16px;">📭</div>
                                Aucun template disponible<br>
                                <small>Créez d'abord des templates dans l'admin PDF Builder</small>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="ultra-actions">
                <button type="button" class="ultra-btn ultra-btn-preview" id="ultra-preview-btn">
                    <span>👁️</span>
                    Aperçu PDF
                </button>

                <button type="button" class="ultra-btn ultra-btn-generate" id="ultra-generate-btn">
                    <span>⚡</span>
                    Générer PDF
                </button>

                <button type="button" class="ultra-btn ultra-btn-download" id="ultra-download-btn">
                    <span>⬇️</span>
                    Télécharger PDF
                </button>
            </div>

            <!-- Status Messages -->
            <div class="ultra-status" id="ultra-status"></div>
        </div>

        <!-- Ultra Modern PDF Preview Modal -->
        <div class="ultra-modal-overlay" id="ultra-modal-overlay">
            <div class="ultra-modal">
                <div class="ultra-modal-header">
                    <h3 class="ultra-modal-title">
                        <span class="modal-icon">🔍</span>
                        Aperçu Ultra HD - <?php echo esc_html($document_type_label); ?>
                    </h3>

                    <div class="ultra-modal-controls">
                        <div class="ultra-zoom-controls">
                            <button class="ultra-zoom-btn" id="ultra-zoom-out" title="Zoom arrière (Ctrl+-)">−</button>
                            <span class="ultra-zoom-level" id="ultra-zoom-level">100%</span>
                            <button class="ultra-zoom-btn" id="ultra-zoom-in" title="Zoom avant (Ctrl++)">+</button>
                            <button class="ultra-zoom-btn" id="ultra-zoom-fit" title="Ajuster à la fenêtre (Ctrl+0)">🔍</button>
                        </div>

                        <button class="ultra-close-btn" id="ultra-close-modal" title="Fermer (Échap)">
                            ×
                        </button>
                    </div>
                </div>

                <div class="ultra-modal-body">
                    <div class="ultra-preview-container">
                        <div class="ultra-preview-loading" id="ultra-preview-loading">
                            <div class="ultra-spinner"></div>
                            <div>
                                <div style="font-weight: 700; margin-bottom: 8px; font-size: 20px;">Génération de l'aperçu Ultra HD...</div>
                                <div style="font-size: 16px; opacity: 0.8;">Analyse du template et rendu haute qualité</div>
                                <div style="font-size: 14px; opacity: 0.6; margin-top: 8px;">Veuillez patienter</div>
                            </div>
                        </div>
                    </div>

                    <div class="ultra-preview-info">
                        <div class="ultra-preview-info-header">
                            <span>📊</span>
                            Informations détaillées de l'aperçu
                        </div>

                        <div class="ultra-preview-stats">
                            <div class="ultra-preview-stat">
                                <span class="ultra-preview-stat-label">Template actif:</span>
                                <span class="ultra-preview-stat-value" id="ultra-info-template">
                                    <?php echo $selected_template ? esc_html($selected_template['name']) : 'Par défaut'; ?>
                                </span>
                            </div>
                            <div class="ultra-preview-stat">
                                <span class="ultra-preview-stat-label">Format du document:</span>
                                <span class="ultra-preview-stat-value">A4 (210×297mm)</span>
                            </div>
                            <div class="ultra-preview-stat">
                                <span class="ultra-preview-stat-label">Orientation:</span>
                                <span class="ultra-preview-stat-value">Portrait</span>
                            </div>
                            <div class="ultra-preview-stat">
                                <span class="ultra-preview-stat-label">Résolution d'aperçu:</span>
                                <span class="ultra-preview-stat-value">Ultra HD 4K</span>
                            </div>
                            <div class="ultra-preview-stat">
                                <span class="ultra-preview-stat-label">Généré le:</span>
                                <span class="ultra-preview-stat-value" id="ultra-info-date">
                                    <?php echo esc_html(current_time('d/m/Y H:i')); ?>
                                </span>
                            </div>
                            <div class="ultra-preview-stat">
                                <span class="ultra-preview-stat-label">Temps de génération:</span>
                                <span class="ultra-preview-stat-value" id="ultra-info-time">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Variables globales
            let currentTemplateId = <?php echo $selected_template ? intval($selected_template['id']) : 0; ?>;
            let currentZoom = 1.0;
            let originalPdfWidth = 0;
            let originalPdfHeight = 0;
            let previewStartTime = 0;

            // Fonctions utilitaires
            function showStatus(message, type = 'loading') {
                const $status = $('#ultra-status');
                const classes = {
                    'loading': 'ultra-status-loading',
                    'success': 'ultra-status-success',
                    'error': 'ultra-status-error'
                };

                $status.removeClass('ultra-status-loading ultra-status-success ultra-status-error show')
                       .addClass(classes[type])
                       .html(message)
                       .show();

                setTimeout(() => $status.addClass('show'), 10);

                if (type !== 'loading') {
                    setTimeout(() => hideStatus(), 6000);
                }
            }

            function hideStatus() {
                $('#ultra-status').removeClass('show');
                setTimeout(() => $('#ultra-status').hide(), 400);
            }

            function setButtonLoading($btn, loading) {
                if (loading) {
                    $btn.addClass('loading').prop('disabled', true);
                } else {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            }

            function updateTemplateDisplay(templateId, templateName) {
                $('#ultra-template-display .ultra-template-name').text(templateName);
                $('#ultra-info-template').text(templateName);
                currentTemplateId = templateId;

                // Animation de mise à jour
                $('#ultra-template-display').addClass('ultra-loading-shimmer');
                setTimeout(() => $('#ultra-template-display').removeClass('ultra-loading-shimmer'), 1000);
            }

            // Template selector avec animations avancées
            $('#ultra-template-toggle').on('click', function() {
                const $selector = $('#ultra-template-selector');
                const isVisible = $selector.is(':visible');

                if (isVisible) {
                    $selector.slideUp(400, 'easeOutCubic');
                    $(this).text('Changer de template ▼').removeClass('ultra-template-toggle-active');
                } else {
                    $selector.slideDown(400, 'easeOutCubic');
                    $(this).text('Masquer ▲').addClass('ultra-template-toggle-active');
                    $('#ultra-template-search').focus().addClass('ultra-template-search-focus');
                }
            });

            // Template search avec filtrage en temps réel
            $('#ultra-template-search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                let visibleCount = 0;

                $('.ultra-template-option').each(function() {
                    const templateName = $(this).text().toLowerCase();
                    const $option = $(this);

                    if (templateName.includes(searchTerm)) {
                        $option.slideDown(200);
                        visibleCount++;
                    } else {
                        $option.slideUp(200);
                    }
                });

                // Message si aucun résultat
                if (visibleCount === 0 && searchTerm.length > 0) {
                    if (!$('.ultra-no-results').length) {
                        $('#ultra-template-list').append(
                            '<div class="ultra-no-results" style="text-align: center; color: #6c757d; padding: 20px; font-style: italic;">' +
                            '<div style="font-size: 32px; margin-bottom: 8px;">🔍</div>' +
                            'Aucun template trouvé pour "' + searchTerm + '"' +
                            '</div>'
                        );
                    }
                } else {
                    $('.ultra-no-results').remove();
                }
            });

            // Template selection avec feedback visuel
            $(document).on('click', '.ultra-template-option', function() {
                const templateId = $(this).data('template-id');
                const templateName = $(this).find('span:last-child').text();

                $('.ultra-template-option').removeClass('selected');
                $(this).addClass('selected');

                // Animation de sélection
                $(this).css('animation', 'bounceIn 0.5s ease-out');
                setTimeout(() => $(this).css('animation', ''), 500);

                updateTemplateDisplay(templateId, templateName);
                $('#ultra-template-selector').slideUp(400);
                $('#ultra-template-toggle').text('Changer de template ▼').removeClass('ultra-template-toggle-active');

                // Notification de changement
                showStatus('<span style="color: #28a745;">✅ Template changé: ' + templateName + '</span>', 'success');
            });

            // Modal functions avec animations avancées
            function openModal() {
                previewStartTime = Date.now();
                $('#ultra-modal-overlay').addClass('show');
                $('body').addClass('ultra-modal-open');

                // Animation d'entrée
                setTimeout(() => {
                    $('.ultra-modal').addClass('show');
                }, 100);

                $(document).on('keydown.ultra-modal', handleKeydown);
            }

            function closeModal() {
                $('.ultra-modal').removeClass('show');
                $('#ultra-modal-overlay').removeClass('show');
                $('body').removeClass('ultra-modal-open');
                $(document).off('keydown.ultra-modal');

                // Reset zoom
                currentZoom = 1.0;
                $('#ultra-zoom-level').text('100%');
            }

            function handleKeydown(e) {
                if (e.keyCode === 27) { // Escape
                    closeModal();
                }

                if (e.ctrlKey || e.metaKey) {
                    switch (e.key) {
                        case '+':
                        case '=':
                            e.preventDefault();
                            applyZoom(currentZoom * 1.2);
                            break;
                        case '-':
                            e.preventDefault();
                            applyZoom(currentZoom / 1.2);
                            break;
                        case '0':
                            e.preventDefault();
                            applyZoom(1.0);
                            break;
                    }
                }
            }

            // Zoom functions améliorées
            function applyZoom(zoomLevel) {
                currentZoom = Math.max(0.25, Math.min(4.0, zoomLevel));
                $('#ultra-zoom-level').text(Math.round(currentZoom * 100) + '%');

                const $iframe = $('.ultra-preview-container iframe');
                if ($iframe.length) {
                    $iframe.css({
                        'transform': 'scale(' + currentZoom + ')',
                        'transform-origin': 'top center',
                        'width': (100 / currentZoom) + '%',
                        'height': (originalPdfHeight / currentZoom) + 'px',
                        'transition': 'all 0.3s ease'
                    });

                    $('.ultra-preview-container').css({
                        'min-height': (originalPdfHeight * currentZoom + 60) + 'px',
                        'transition': 'all 0.3s ease'
                    });
                }
            }

            // Event handlers avec améliorations UX
            $('#ultra-preview-btn').on('click', function() {
                console.log('Ultra Preview: Button clicked - Template ID:', currentTemplateId);

                showStatus('<div style="display: flex; align-items: center; gap: 10px;"><div class="ultra-spinner" style="width: 18px; height: 18px;"></div>Génération de l\'aperçu Ultra HD...</div>', 'loading');
                setButtonLoading($(this), true);

                openModal();

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_pro_preview_order_pdf',
                        order_id: <?php echo intval($order_id); ?>,
                        template_id: currentTemplateId,
                        nonce: '<?php echo wp_create_nonce('pdf_builder_order_actions'); ?>'
                    },
                    success: function(response) {
                        console.log('Ultra Preview: AJAX success', response);

                        if (response.success) {
                            const generationTime = Date.now() - previewStartTime;
                            originalPdfWidth = response.data.width;
                            originalPdfHeight = response.data.height;

                            const iframe = document.createElement('iframe');
                            iframe.style.width = '100%';
                            iframe.style.height = originalPdfHeight + 'px';
                            iframe.style.border = 'none';
                            iframe.style.background = 'white';
                            iframe.style.transformOrigin = 'top center';
                            iframe.style.borderRadius = '8px';
                            iframe.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';

                            iframe.onload = function() {
                                $('#ultra-preview-loading').fadeOut(300, function() {
                                    $('#ultra-preview-loading').hide();
                                });

                                try {
                                    const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                                    if (iframeDoc) {
                                        iframeDoc.body.innerHTML = response.data.html;
                                    }
                                } catch (e) {
                                    console.error('Ultra Preview: Error writing to iframe:', e);
                                    $('.ultra-preview-container').html(
                                        '<div style="color: #dc3545; padding: 40px; text-align: center; background: #f8d7da; border-radius: 12px; border: 1px solid #f5c6cb;">' +
                                        '<div style="font-size: 48px; margin-bottom: 16px;">⚠️</div>' +
                                        '<strong style="font-size: 18px;">Erreur lors du chargement de l\'aperçu</strong><br>' +
                                        '<small>Droits d\'accès iframe insuffisants</small>' +
                                        '</div>'
                                    );
                                }
                            };

                            $('.ultra-preview-container').html(iframe);
                            applyZoom(1.0);

                            // Update timing info
                            $('#ultra-info-time').text(generationTime + 'ms');
                            $('#ultra-info-date').text(new Date().toLocaleString('fr-FR'));

                            showStatus('<span style="color: #28a745;">✅ Aperçu Ultra HD généré avec succès (' + generationTime + 'ms)</span>', 'success');

                            // Animation de succès
                            $('.ultra-preview-container').addClass('animate__animated animate__fadeInUp');

                        } else {
                            console.error('Ultra Preview: Failed:', response.data);
                            $('.ultra-preview-container').html(
                                '<div style="color: #dc3545; padding: 40px; text-align: center; background: #f8d7da; border-radius: 12px; border: 1px solid #f5c6cb;">' +
                                '<div style="font-size: 48px; margin-bottom: 16px;">❌</div>' +
                                '<strong style="font-size: 18px;">Erreur de génération</strong><br>' +
                                '<small>' + response.data + '</small>' +
                                '</div>'
                            );
                            showStatus('<span style="color: #dc3545;">❌ Erreur lors de l\'aperçu Ultra HD</span>', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ultra Preview: AJAX error', status, error);
                        $('.ultra-preview-container').html(
                            '<div style="color: #dc3545; padding: 40px; text-align: center; background: #f8d7da; border-radius: 12px; border: 1px solid #f5c6cb;">' +
                            '<div style="font-size: 48px; margin-bottom: 16px;">🔌</div>' +
                            '<strong style="font-size: 18px;">Erreur de connexion</strong><br>' +
                            '<small>Impossible de contacter le serveur</small>' +
                            '</div>'
                        );
                        showStatus('<span style="color: #dc3545;">❌ Erreur AJAX lors de l\'aperçu</span>', 'error');
                    },
                    complete: function() {
                        setButtonLoading($('#ultra-preview-btn'), false);
                    }
                });
            });

            $('#ultra-generate-btn').on('click', function() {
                console.log('Ultra Generate: Button clicked - Template ID:', currentTemplateId);

                showStatus('<div style="display: flex; align-items: center; gap: 10px;"><div class="ultra-spinner" style="width: 18px; height: 18px;"></div>Génération du PDF haute qualité...</div>', 'loading');
                setButtonLoading($(this), true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_generate_order_pdf',
                        order_id: <?php echo intval($order_id); ?>,
                        template_id: currentTemplateId,
                        nonce: '<?php echo wp_create_nonce('pdf_builder_order_actions'); ?>'
                    },
                    success: function(response) {
                        console.log('Ultra Generate: AJAX success', response);

                        if (response.success) {
                            $('#ultra-download-btn').attr('href', response.data.url).show();

                            // Animation de succès
                            $('#ultra-download-btn').addClass('animate__animated animate__bounceIn').show();
                            showStatus('<span style="color: #28a745;">✅ PDF haute qualité généré avec succès</span>', 'success');

                            // Ouvrir automatiquement le PDF après un court délai
                            setTimeout(() => {
                                window.open(response.data.url, '_blank');
                            }, 800);

                            // Animation de célébration
                            $('#ultra-generate-btn').addClass('animate__animated animate__rubberBand');
                            setTimeout(() => $('#ultra-generate-btn').removeClass('animate__animated animate__rubberBand'), 1000);

                        } else {
                            console.error('Ultra Generate: Failed:', response.data);
                            showStatus('<span style="color: #dc3545;">❌ Erreur lors de la génération</span>', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Ultra Generate: AJAX error', status, error);
                        showStatus('<span style="color: #dc3545;">❌ Erreur AJAX lors de la génération</span>', 'error');
                    },
                    complete: function() {
                        setButtonLoading($('#ultra-generate-btn'), false);
                    }
                });
            });

            $('#ultra-download-btn').on('click', function() {
                const pdfUrl = $(this).attr('href');
                if (pdfUrl) {
                    window.open(pdfUrl, '_blank');
                }
            });

            // Modal controls avec animations
            $('#ultra-close-modal').on('click', function() {
                closeModal();
            });

            $('#ultra-modal-overlay').on('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Zoom controls avec feedback visuel
            $('#ultra-zoom-in').on('click', function() {
                applyZoom(currentZoom * 1.2);
                $(this).addClass('animate__animated animate__pulse');
                setTimeout(() => $(this).removeClass('animate__animated animate__pulse'), 300);
            });

            $('#ultra-zoom-out').on('click', function() {
                applyZoom(currentZoom / 1.2);
                $(this).addClass('animate__animated animate__pulse');
                setTimeout(() => $(this).removeClass('animate__animated animate__pulse'), 300);
            });

            $('#ultra-zoom-fit').on('click', function() {
                if (originalPdfWidth > 0) {
                    const containerWidth = $('.ultra-preview-container').width() - 60;
                    const fitZoom = containerWidth / originalPdfWidth;
                    applyZoom(fitZoom);
                }
                $(this).addClass('animate__animated animate__pulse');
                setTimeout(() => $(this).removeClass('animate__animated animate__pulse'), 300);
            });

            // Double-clic pour zoom fit
            $(document).on('dblclick', '.ultra-preview-container iframe', function() {
                if (originalPdfWidth > 0) {
                    const containerWidth = $('.ultra-preview-container').width() - 60;
                    const fitZoom = containerWidth / originalPdfWidth;
                    applyZoom(fitZoom);
                }
            });

            // Tooltips pour les boutons
            $('.ultra-btn, .ultra-zoom-btn, .ultra-close-btn').each(function() {
                const title = $(this).attr('title');
                if (title) {
                    $(this).tooltip({
                        placement: 'top',
                        trigger: 'hover'
                    });
                }
            });

            // Initialisation des animations
            $('.ultra-btn').addClass('animate__animated animate__fadeInUp');
            $('.ultra-doc-type').addClass('animate__animated animate__fadeInLeft');
            $('.ultra-template-section').addClass('animate__animated animate__fadeInRight');

            // Stagger animations
            $('.ultra-btn').each(function(index) {
                $(this).css('animation-delay', (index * 0.1) + 's');
            });
        });
        </script>
        <?php
    }

    /**
     * AJAX - Générer PDF pour une commande WooCommerce
     */
    public function ajax_generate_order_pdf() {
        // Désactiver l'affichage des erreurs PHP pour éviter les réponses HTML
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        // Vérifier les permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if (!$order_id) {
            wp_send_json_error('ID commande manquant');
        }

        // Vérifier que WooCommerce est actif
        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce n\'est pas installé ou activé');
        }

        // Vérifier que les fonctions WooCommerce nécessaires existent
        if (!function_exists('wc_get_order')) {
            wp_send_json_error('Fonction wc_get_order non disponible - WooCommerce mal installé');
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error('Commande non trouvée');
        }

        // Vérifier que l'objet order a les méthodes nécessaires
        if (!method_exists($order, 'get_id') || !method_exists($order, 'get_total')) {
            wp_send_json_error('Objet commande WooCommerce invalide');
        }

        try {
            // Charger le template
            if ($template_id > 0) {
                $template_data = $this->load_template_robust($template_id);
            } else {
                // Vérifier s'il y a un template spécifique pour le statut de la commande
                $order_status = $order->get_status();
                $status_templates = get_option('pdf_builder_order_status_templates', []);
                $status_key = 'wc-' . $order_status;

                if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                    $mapped_template_id = $status_templates[$status_key];
                    $template_data = $this->load_template_robust($mapped_template_id);
                } else {
                    $template_data = $this->get_default_invoice_template();
                }
            }

            // Générer le PDF avec les données de la commande
            $pdf_filename = 'order-' . $order_id . '-' . time() . '.pdf';
            $pdf_path = $this->generate_order_pdf($order, $template_data, $pdf_filename);

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

                wp_send_json_success(array(
                    'message' => 'PDF généré avec succès',
                    'url' => $pdf_url,
                    'filename' => $pdf_filename
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF - fichier non créé');
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Prévisualiser PDF pour une commande
     */
    public function ajax_preview_order_pdf() {
        // Désactiver l'affichage des erreurs PHP pour éviter les réponses HTML
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        // Vérifier les permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if (!$order_id) {
            wp_send_json_error('ID commande manquant');
        }

        // Vérifier que WooCommerce est actif
        if (!class_exists('WooCommerce')) {
            wp_send_json_error('WooCommerce n\'est pas installé ou activé');
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            wp_send_json_error('Commande non trouvée');
        }

        try {
            // Charger le template
            if ($template_id > 0) {
                $template_data = $this->load_template_robust($template_id);
            } else {
                // Utiliser le template par défaut ou détecté automatiquement
                $order_status = $order->get_status();
                $status_templates = get_option('pdf_builder_order_status_templates', []);
                $status_key = 'wc-' . $order_status;

                if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                    $template_data = $this->load_template_robust($status_templates[$status_key]);
                } else {
                    $template_data = $this->get_default_invoice_template();
                }
            }

            if (!$template_data) {
                wp_send_json_error('Template non trouvé');
            }

            // Générer l'HTML d'aperçu avec les données de la commande
            $html_content = $this->generate_unified_html($template_data, $order);

            $response = array(
                'html' => $html_content,
                'width' => $template_data['canvas']['width'] ?? 595,
                'height' => $template_data['canvas']['height'] ?? 842
            );

            wp_send_json_success($response);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Générer PDF pour une commande
     */
    private function generate_order_pdf($order, $template_data, $filename) {
        // Générer le HTML de la commande
        $html = $this->generate_order_html($order, $template_data);

        // Créer le répertoire uploads s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_path = $pdf_dir . '/' . $filename;

        // Générer le PDF avec TCPDF
        require_once plugin_dir_path(dirname(__FILE__)) . '../../lib/tcpdf/tcpdf_autoload.php';

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configuration PDF
        $pdf->SetCreator('PDF Builder Pro');
        $pdf->SetAuthor('PDF Builder Pro');
        $pdf->SetTitle('Facture Commande #' . $order->get_order_number());
        $pdf->SetSubject('Facture générée par PDF Builder Pro');

        // Supprimer les headers par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        // Écrire le HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Sauvegarder le PDF
        $pdf->Output($pdf_path, 'F');

        return $pdf_path;
    }

    /**
     * Générer HTML pour une commande
     */
    private function generate_order_html($order, $template_data) {
        $html = $this->generate_unified_html($template_data, $order);
        return $html;
    }

    /**
     * Générer HTML unifié (méthode commune avec PDF Generator)
     */
    private function generate_unified_html($template, $order = null) {
        $html = '<div style="font-family: Arial, sans-serif; padding: 20px;">';

        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            $elements = $firstPage['elements'] ?? [];
        } elseif (isset($template['elements']) && is_array($template['elements'])) {
            $elements = $template['elements'];
        } else {
            $elements = [];
        }

        if (is_array($elements)) {
            foreach ($elements as $element) {
                // Gérer les deux formats de structure des éléments
                if (isset($element['position']) && isset($element['size'])) {
                    $x = $element['position']['x'] ?? 0;
                    $y = $element['position']['y'] ?? 0;
                    $width = $element['size']['width'] ?? 100;
                    $height = $element['size']['height'] ?? 50;
                } else {
                    $x = $element['x'] ?? 0;
                    $y = $element['y'] ?? 0;
                    $width = $element['width'] ?? 100;
                    $height = $element['height'] ?? 50;
                }

                $style = sprintf(
                    'position: absolute; left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                    $x, $y, $width, $height
                );

                if (isset($element['style'])) {
                    if (isset($element['style']['color'])) {
                        $style .= ' color: ' . $element['style']['color'] . ';';
                    }
                    if (isset($element['style']['fontSize'])) {
                        $style .= ' font-size: ' . $element['style']['fontSize'] . 'px;';
                    }
                    if (isset($element['style']['fontWeight'])) {
                        $style .= ' font-weight: ' . $element['style']['fontWeight'] . ';';
                    }
                    if (isset($element['style']['fillColor'])) {
                        $style .= ' background-color: ' . $element['style']['fillColor'] . ';';
                    }
                }

                $content = $element['content'] ?? '';

                // Remplacer les variables si on a une commande WooCommerce
                if ($order) {
                    $content = $this->replace_order_variables($content, $order);
                }

                switch ($element['type']) {
                    case 'text':
                        $final_content = $order ? $this->replace_order_variables($content, $order) : $content;
                        $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($final_content));
                        break;

                    case 'invoice_number':
                        if ($order) {
                            $invoice_number = $order->get_id() . '-' . time();
                            $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($invoice_number));
                        }
                        break;

                    default:
                        $html .= sprintf('<div style="%s">%s</div>', $style, esc_html($content));
                        break;
                }
            }
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Remplacer les variables de commande
     */
    private function replace_order_variables($content, $order) {
        // Variables simples
        $content = str_replace('{{order_id}}', $order->get_id(), $content);
        $content = str_replace('{{order_number}}', $order->get_order_number(), $content);
        $content = str_replace('{{order_date}}', $order->get_date_created()->date('d/m/Y'), $content);
        $content = str_replace('{{order_total}}', $order->get_formatted_order_total(), $content);

        // Informations client
        $content = str_replace('{{customer_name}}', $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(), $content);
        $content = str_replace('{{customer_email}}', $order->get_billing_email(), $content);

        // Informations société (si configurées)
        $company_info = $this->format_complete_company_info();
        $content = str_replace('{{company_info}}', $company_info, $content);

        // Informations client complètes
        $customer_info = $this->format_complete_customer_info($order);
        $content = str_replace('{{customer_info}}', $customer_info, $content);

        // Tableau des produits
        $products_table = $this->generate_order_products_table($order);
        $content = str_replace('{{products_table}}', $products_table, $content);

        return $content;
    }

    /**
     * Formatter les informations complètes de la société
     */
    private function format_complete_company_info() {
        $company_name = get_option('pdf_builder_company_name', '');
        $company_address = get_option('pdf_builder_company_address', '');
        $company_phone = get_option('pdf_builder_company_phone', '');
        $company_email = get_option('pdf_builder_company_email', '');

        $info = '';
        if ($company_name) $info .= $company_name . "\n";
        if ($company_address) $info .= $company_address . "\n";
        if ($company_phone) $info .= "Tel: " . $company_phone . "\n";
        if ($company_email) $info .= "Email: " . $company_email . "\n";

        return nl2br($info);
    }

    /**
     * Formatter les informations complètes du client
     */
    private function format_complete_customer_info($order) {
        $info = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . "\n";
        $info .= $order->get_billing_address_1() . "\n";
        if ($order->get_billing_address_2()) {
            $info .= $order->get_billing_address_2() . "\n";
        }
        $info .= $order->get_billing_postcode() . ' ' . $order->get_billing_city() . "\n";
        $info .= $order->get_billing_country() . "\n";
        $info .= "Email: " . $order->get_billing_email() . "\n";
        if ($order->get_billing_phone()) {
            $info .= "Tel: " . $order->get_billing_phone() . "\n";
        }

        return nl2br($info);
    }

    /**
     * Générer le tableau des produits de la commande
     */
    private function generate_order_products_table($order) {
        $html = '<table style="width: 100%; border-collapse: collapse; margin: 10px 0;">';
        $html .= '<thead><tr style="background-color: #f5f5f5;">';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: left;">Produit</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: center;">Qté</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Prix</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 8px; text-align: right;">Total</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $product_name = $item->get_name();
            $quantity = $item->get_quantity();
            $price = $item->get_total() / $quantity;
            $total = $item->get_total();

            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px;">' . esc_html($product_name) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">' . $quantity . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . wc_price($price) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 8px; text-align: right;">' . wc_price($total) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Obtenir le template de facture par défaut
     */
    private function get_default_invoice_template() {
        return array(
            'pages' => array(
                array(
                    'elements' => array(
                        array(
                            'type' => 'text',
                            'content' => 'FACTURE',
                            'x' => 50,
                            'y' => 50,
                            'width' => 200,
                            'height' => 40,
                            'style' => array(
                                'fontSize' => 24,
                                'fontWeight' => 'bold'
                            )
                        ),
                        array(
                            'type' => 'text',
                            'content' => 'Commande #{{order_number}}',
                            'x' => 50,
                            'y' => 100,
                            'width' => 200,
                            'height' => 30
                        ),
                        array(
                            'type' => 'text',
                            'content' => 'Date: {{order_date}}',
                            'x' => 400,
                            'y' => 100,
                            'width' => 150,
                            'height' => 30
                        )
                    )
                )
            )
        );
    }

    /**
     * Charger un template de manière robuste
     */
    private function load_template_robust($template_id) {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            return false;
        }

        $template_data_raw = $template['template_data'];

        // Vérifier si les données contiennent des backslashes (échappement PHP)
        if (strpos($template_data_raw, '\\') !== false) {
            $template_data_raw = stripslashes($template_data_raw);
        }

        $template_data = json_decode($template_data_raw, true);
        if ($template_data === null && json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $template_data;
    }
}
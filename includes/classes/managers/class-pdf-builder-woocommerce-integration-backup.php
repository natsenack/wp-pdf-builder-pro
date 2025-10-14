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
        // Enregistrer les hooks AJAX via l'action init pour s'assurer qu'ils sont disponibles
        add_action('init', [$this, 'register_ajax_hooks']);
    }

    /**
     * Enregistrer les hooks AJAX
     */
    public function register_ajax_hooks() {
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf']);
        add_action('wp_ajax_pdf_builder_pro_preview_order_pdf', [$this, 'ajax_preview_order_pdf']);
        add_action('wp_ajax_pdf_builder_save_order_canvas', [$this, 'ajax_save_order_canvas']);
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
     * Rend la meta box dans les commandes WooCommerce - VERSION CLEAN & PROFESSIONAL
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
        /* Modern PDF Modal Styles */
        .pdf-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            z-index: 100000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            visibility: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .pdf-modal-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .pdf-modal {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25),
                        0 0 0 1px rgba(255, 255, 255, 0.05),
                        0 0 60px rgba(102, 126, 234, 0.15);
            max-width: 95vw;
            max-height: 95vh;
            width: 1200px;
            height: 800px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transform: scale(0.9) translateY(20px);
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .pdf-modal.show {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        /* Modal Header */
        .pdf-modal-header {
            padding: 24px 32px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }

        .pdf-modal-header-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .pdf-modal-icon {
            font-size: 32px;
            opacity: 0.9;
        }

        .pdf-modal-info h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: white;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }

        .pdf-modal-subtitle {
            margin: 4px 0 0 0;
            font-size: 14px;
            opacity: 0.8;
            color: white;
        }

        .pdf-modal-header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Zoom Toolbar */
        .pdf-zoom-toolbar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 8px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .pdf-zoom-btn {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #4a5568;
            transition: all 0.2s ease;
            position: relative;
        }

        .pdf-zoom-btn:hover {
            background: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .pdf-zoom-btn:active {
            transform: translateY(0);
        }

        .pdf-zoom-display {
            font-size: 14px;
            font-weight: 600;
            min-width: 50px;
            text-align: center;
            color: #2d3748;
            background: rgba(255, 255, 255, 0.95);
            padding: 6px 12px;
            border-radius: 6px;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .pdf-zoom-separator {
            width: 1px;
            height: 20px;
            background: rgba(255, 255, 255, 0.3);
            margin: 0 4px;
        }

        /* Action Buttons */
        .pdf-action-buttons {
            display: flex;
            gap: 8px;
        }

        .pdf-action-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: all 0.2s ease;
            position: relative;
        }

        .pdf-action-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .pdf-action-btn:active {
            transform: translateY(0);
        }

        /* Close Button */
        .pdf-close-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: white;
            transition: all 0.2s ease;
            position: relative;
        }

        .pdf-close-btn:hover {
            background: rgba(239, 68, 68, 0.9);
            transform: rotate(90deg);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }

        /* Modal Body */
        .pdf-modal-body {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        /* Preview Container */
        .pdf-preview-container {
            flex: 1;
            background: #f8fafc;
            position: relative;
            overflow: hidden;
            border-radius: 0 0 0 20px;
        }

        /* Loading State */
        .pdf-preview-loading {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            z-index: 10;
        }

        .pdf-loading-spinner {
            position: relative;
            width: 60px;
            height: 60px;
            margin-bottom: 24px;
        }

        .pdf-spinner-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border: 3px solid rgba(102, 126, 234, 0.1);
            border-top: 3px solid #667eea;
            border-radius: 50%;
            animation: pdf-spin 1.5s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        .pdf-spinner-ring:nth-child(2) {
            animation-delay: 0.2s;
            border-top-color: #764ba2;
        }

        .pdf-spinner-ring:nth-child(3) {
            animation-delay: 0.4s;
            border-top-color: #f093fb;
        }

        @keyframes pdf-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .pdf-loading-content h3 {
            margin: 0 0 8px 0;
            font-size: 18px;
            font-weight: 600;
            color: #2d3748;
            text-align: center;
        }

        .pdf-loading-content p {
            margin: 0 0 24px 0;
            color: #718096;
            text-align: center;
        }

        .pdf-loading-progress {
            width: 200px;
            height: 4px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 2px;
            overflow: hidden;
        }

        .pdf-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
            width: 0%;
            transition: width 0.3s ease;
        }

        /* Error State */
        .pdf-preview-error {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: #fef5e7;
            text-align: center;
            padding: 40px;
        }

        .pdf-error-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .pdf-preview-error h3 {
            margin: 0 0 8px 0;
            color: #d97706;
            font-size: 20px;
            font-weight: 600;
        }

        .pdf-preview-error p {
            margin: 0 0 24px 0;
            color: #92400e;
        }

        .pdf-retry-btn {
            background: #d97706;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .pdf-retry-btn:hover {
            background: #b45309;
            transform: translateY(-1px);
        }

        /* Preview Content */
        .pdf-preview-content {
            width: 100%;
            height: 100%;
            position: relative;
        }

        #pdf-preview-iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: white;
            transform-origin: center top;
            transition: transform 0.3s ease;
        }

        /* Info Panel */
        .pdf-info-panel {
            width: 320px;
            background: white;
            border-left: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
        }

        .pdf-info-tabs {
            display: flex;
            border-bottom: 1px solid #e2e8f0;
        }

        .pdf-info-tab {
            flex: 1;
            padding: 16px;
            background: none;
            border: none;
            font-weight: 500;
            color: #718096;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .pdf-info-tab.active {
            color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .pdf-info-tab.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background: #667eea;
        }

        .pdf-info-content {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
        }

        .pdf-info-section {
            display: none;
        }

        .pdf-info-section.active {
            display: block;
        }

        /* Details Section */
        .pdf-info-grid {
            display: grid;
            gap: 16px;
        }

        .pdf-info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }

        .pdf-info-label {
            font-size: 13px;
            color: #718096;
            font-weight: 500;
        }

        .pdf-info-value {
            font-size: 14px;
            color: #2d3748;
            font-weight: 600;
        }

        /* Stats Section */
        .pdf-stats-grid {
            display: grid;
            gap: 16px;
        }

        .pdf-stat-card {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }

        .pdf-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .pdf-stat-icon {
            font-size: 24px;
        }

        .pdf-stat-content {
            flex: 1;
        }

        .pdf-stat-value {
            font-size: 18px;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 2px;
        }

        .pdf-stat-label {
            font-size: 12px;
            color: #718096;
            font-weight: 500;
        }

        /* Actions Section */
        .pdf-actions-grid {
            display: grid;
            gap: 12px;
        }

        .pdf-quick-action {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
        }

        .pdf-quick-action:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .pdf-action-icon {
            font-size: 20px;
        }

        .pdf-action-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 2px;
        }

        .pdf-action-desc {
            font-size: 12px;
            opacity: 0.8;
        }

        /* Modal Footer */
        .pdf-modal-footer {
            padding: 20px 32px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pdf-footer-left {
            display: flex;
            gap: 16px;
        }

        .pdf-keyboard-hints {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .pdf-hint {
            font-size: 12px;
            color: #718096;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        kbd {
            background: #e2e8f0;
            color: #4a5568;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 500;
            border: 1px solid #cbd5e0;
        }

        .pdf-footer-right {
            display: flex;
            gap: 12px;
        }

        .pdf-footer-btn {
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border: 1px solid #e2e8f0;
        }

        .pdf-footer-secondary {
            background: white;
            color: #718096;
        }

        .pdf-footer-secondary:hover {
            background: #f8fafc;
        }

        .pdf-footer-primary {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }

        .pdf-footer-primary:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }

        /* Tooltips */
        [data-tooltip] {
            position: relative;
        }

        [data-tooltip]:hover::before {
            content: attr(data-tooltip);
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #2d3748;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            z-index: 100001;
            margin-bottom: 8px;
            opacity: 0;
            animation: tooltipFade 0.2s ease forwards;
        }

        [data-tooltip]:hover::after {
            content: '';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            border: 5px solid transparent;
            border-top-color: #2d3748;
            margin-bottom: 3px;
            opacity: 0;
            animation: tooltipFade 0.2s ease forwards;
        }

        @keyframes tooltipFade {
            to {
                opacity: 1;
            }
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .pdf-modal {
                width: 95vw;
                height: 90vh;
            }

            .pdf-info-panel {
                width: 280px;
            }
        }

        @media (max-width: 768px) {
            .pdf-modal {
                width: 100vw;
                height: 100vh;
                border-radius: 0;
                max-width: none;
                max-height: none;
            }

            .pdf-modal-header {
                padding: 16px 20px;
                flex-direction: column;
                gap: 16px;
            }

            .pdf-modal-header-left {
                flex-direction: column;
                text-align: center;
                gap: 8px;
            }

            .pdf-modal-header-right {
                width: 100%;
                justify-content: space-between;
            }

            .pdf-zoom-toolbar {
                order: 2;
            }

            .pdf-action-buttons {
                order: 1;
            }

            .pdf-modal-body {
                flex-direction: column;
            }

            .pdf-info-panel {
                width: 100%;
                height: 200px;
                border-left: none;
                border-top: 1px solid #e2e8f0;
            }

            .pdf-modal-footer {
                padding: 16px 20px;
                flex-direction: column;
                gap: 16px;
            }

            .pdf-footer-left {
                justify-content: center;
            }

            .pdf-keyboard-hints {
                display: none;
            }

            .pdf-footer-right {
                width: 100%;
                justify-content: center;
            }
        }

        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .pdf-modal {
                background: #1a202c;
                color: #e2e8f0;
            }

            .pdf-preview-container {
                background: #2d3748;
            }

            .pdf-info-panel {
                background: #1a202c;
                border-left-color: #4a5568;
            }

            .pdf-info-tabs {
                border-bottom-color: #4a5568;
            }

            .pdf-info-item {
                background: #2d3748;
                border-color: #4a5568;
            }

            .pdf-info-value {
                color: #e2e8f0;
            }

            .pdf-stat-card {
                background: linear-gradient(135deg, #2d3748 0%, #4a5568 100%);
                border-color: #4a5568;
            }

            .pdf-quick-action {
                background: #2d3748;
                border-color: #4a5568;
                color: #e2e8f0;
            }

            .pdf-modal-footer {
                background: #2d3748;
                border-top-color: #4a5568;
            }

            .pdf-footer-secondary {
                background: #4a5568;
                color: #e2e8f0;
            }
        }

        /* Animation classes */
        .pdf-fade-in {
            animation: pdfFadeIn 0.3s ease forwards;
        }

        .pdf-slide-up {
            animation: pdfSlideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        @keyframes pdfFadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes pdfSlideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        </style>

        <div id="pdf-builder-metabox">
            <!-- Header Section -->
            <div class="pdf-header">
                <div class="pdf-header-content">
                    <h3 class="pdf-order-title">
                        <span class="order-icon">�</span>
                        Commande #<?php echo esc_html($order->get_order_number()); ?>
                    </h3>
                    <div class="pdf-order-meta">
                        <span><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span>
                        <span>•</span>
                        <span><?php echo esc_html($order->get_date_created()->format('d/m/Y H:i')); ?></span>
                        <span class="pdf-status-badge">
                            <?php echo esc_html($document_type_label); ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Document Type Section -->
            <div class="pdf-doc-type">
                <div class="pdf-doc-type-content">
                    <span class="pdf-doc-icon">📄</span>
                    <div class="pdf-doc-info">
                        <h4><?php echo esc_html($document_type_label); ?> détecté automatiquement</h4>
                        <p>Statut: <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?> • Template intelligent sélectionné</p>
                    </div>
                </div>
            </div>

            <!-- Template Section -->
            <div class="pdf-template-section">
                <div class="pdf-template-header">
                    <h4 class="pdf-template-title">
                        Template sélectionné
                    </h4>
                    <span class="pdf-template-toggle" id="pdf-template-toggle">
                        Changer de template ▼
                    </span>
                </div>

                <div class="pdf-template-display" id="pdf-template-display">
                    <span class="pdf-template-icon">📋</span>
                    <div class="pdf-template-info">
                        <div class="pdf-template-name">
                            <?php echo $selected_template ? esc_html($selected_template['name']) : 'Aucun template disponible'; ?>
                        </div>
                        <div class="pdf-template-meta">
                            <?php if ($selected_template): ?>
                                Template automatiquement détecté • Prêt pour génération
                            <?php else: ?>
                                Aucun template trouvé dans la base de données
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="pdf-template-selector" id="pdf-template-selector">
                    <input type="text"
                           class="pdf-template-search"
                           placeholder="Rechercher un template..."
                           id="pdf-template-search">

                    <div class="pdf-template-list" id="pdf-template-list">
                        <?php if (!empty($all_templates)): ?>
                            <?php foreach ($all_templates as $template): ?>
                                <div class="pdf-template-option <?php echo ($selected_template && $selected_template['id'] == $template['id']) ? 'selected' : ''; ?>"
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
            <div class="pdf-actions">
                <button type="button" class="pdf-btn pdf-btn-preview" id="pdf-preview-btn">
                    <span>👁️</span>
                    Aperçu PDF
                </button>

                <button type="button" class="pdf-btn pdf-btn-generate" id="pdf-generate-btn">
                    <span>⚡</span>
                    Générer PDF
                </button>

                <button type="button" class="pdf-btn pdf-btn-download" id="pdf-download-btn">
                    <span>⬇️</span>
                    Télécharger PDF
                </button>
            </div>

            <!-- Status Messages -->
            <div class="pdf-status" id="pdf-status"></div>
        </div>

        <!-- Modern PDF Preview Modal -->
        <div class="pdf-modal-overlay" id="pdf-modal-overlay">
            <div class="pdf-modal">
                <!-- Modal Header -->
                <div class="pdf-modal-header">
                    <div class="pdf-modal-header-left">
                        <div class="pdf-modal-icon">�</div>
                        <div class="pdf-modal-info">
                            <h2 class="pdf-modal-title">Aperçu PDF</h2>
                            <p class="pdf-modal-subtitle">
                                <?php echo esc_html($document_type_label); ?> • Commande #<?php echo esc_html($order->get_order_number()); ?>
                            </p>
                        </div>
                    </div>

                    <div class="pdf-modal-header-right">
                        <!-- Zoom Controls -->
                        <div class="pdf-zoom-toolbar">
                            <button class="pdf-zoom-btn" id="pdf-zoom-out" data-tooltip="Zoom arrière (Ctrl+-)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <span class="pdf-zoom-display" id="pdf-zoom-level">100%</span>
                            <button class="pdf-zoom-btn" id="pdf-zoom-in" data-tooltip="Zoom avant (Ctrl++)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </button>
                            <div class="pdf-zoom-separator"></div>
                            <button class="pdf-zoom-btn" id="pdf-zoom-fit" data-tooltip="Ajuster à la fenêtre (Ctrl+0)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                                    <polyline points="10,17 15,12 10,7"></polyline>
                                    <line x1="15" y1="12" x2="3" y2="12"></line>
                                </svg>
                            </button>
                            <button class="pdf-zoom-btn" id="pdf-zoom-reset" data-tooltip="Zoom 100%">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="23,4 23,10 17,10"></polyline>
                                    <path d="M20.49,15A9,9,0,1,1,5.64,5.64L23,10"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Action Buttons -->
                        <div class="pdf-action-buttons">
                            <button class="pdf-action-btn pdf-action-print" id="pdf-print-btn" data-tooltip="Imprimer (Ctrl+P)">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="6,9 6,2 18,2 18,9"></polyline>
                                    <path d="M6,18H4a2,2,0,0,1-2-2V11a2,2,0,0,1,2-2H20a2,2,0,0,1,2,2v5a2,2,0,0,1-2,2H6"></path>
                                    <rect x="6" y="14" width="12" height="8"></rect>
                                </svg>
                            </button>
                            <button class="pdf-action-btn pdf-action-download" id="pdf-modal-download-btn" data-tooltip="Télécharger">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21,15v4a2,2,0,0,1-2,2H5a2,2,0,0,1-2-2V15"></path>
                                    <polyline points="7,10 12,15 17,10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                            </button>
                            <button class="pdf-action-btn pdf-action-share" id="pdf-share-btn" data-tooltip="Partager">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="18" cy="5" r="3"></circle>
                                    <circle cx="6" cy="12" r="3"></circle>
                                    <circle cx="18" cy="19" r="3"></circle>
                                    <path d="M8.59,13.51l6.83,3.98"></path>
                                    <path d="M15.41,6.51l-6.82,3.98"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Close Button -->
                        <button class="pdf-close-btn" id="pdf-close-modal" data-tooltip="Fermer (Échap)">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Modal Body -->
                <div class="pdf-modal-body">
                    <!-- Preview Container -->
                    <div class="pdf-preview-container">
                        <!-- Loading State -->
                        <div class="pdf-preview-loading" id="pdf-preview-loading">
                            <div class="pdf-loading-spinner">
                                <div class="pdf-spinner-ring"></div>
                                <div class="pdf-spinner-ring"></div>
                                <div class="pdf-spinner-ring"></div>
                            </div>
                            <div class="pdf-loading-content">
                                <h3>Génération de l'aperçu</h3>
                                <p>Création du document PDF en cours...</p>
                                <div class="pdf-loading-progress">
                                    <div class="pdf-progress-bar" id="pdf-progress-bar"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Error State -->
                        <div class="pdf-preview-error" id="pdf-preview-error" style="display: none;">
                            <div class="pdf-error-icon">⚠️</div>
                            <h3>Erreur de génération</h3>
                            <p id="pdf-error-message">Une erreur inattendue s'est produite.</p>
                            <button class="pdf-retry-btn" id="pdf-retry-btn">Réessayer</button>
                        </div>

                        <!-- Success State -->
                        <div class="pdf-preview-content" id="pdf-preview-content" style="display: none;">
                            <iframe id="pdf-preview-iframe" frameborder="0"></iframe>
                        </div>
                    </div>

                    <!-- Info Panel -->
                    <div class="pdf-info-panel">
                        <div class="pdf-info-tabs">
                            <button class="pdf-info-tab active" data-tab="details">Détails</button>
                            <button class="pdf-info-tab" data-tab="stats">Statistiques</button>
                            <button class="pdf-info-tab" data-tab="actions">Actions</button>
                        </div>

                        <div class="pdf-info-content">
                            <!-- Details Tab -->
                            <div class="pdf-info-section active" data-section="details">
                                <div class="pdf-info-grid">
                                    <div class="pdf-info-item">
                                        <span class="pdf-info-label">Template</span>
                                        <span class="pdf-info-value" id="pdf-info-template">
                                            <?php echo $selected_template ? esc_html($selected_template['name']) : 'Par défaut'; ?>
                                        </span>
                                    </div>
                                    <div class="pdf-info-item">
                                        <span class="pdf-info-label">Format</span>
                                        <span class="pdf-info-value">A4 (210×297mm)</span>
                                    </div>
                                    <div class="pdf-info-item">
                                        <span class="pdf-info-label">Orientation</span>
                                        <span class="pdf-info-value">Portrait</span>
                                    </div>
                                    <div class="pdf-info-item">
                                        <span class="pdf-info-label">Pages</span>
                                        <span class="pdf-info-value" id="pdf-info-pages">1</span>
                                    </div>
                                    <div class="pdf-info-item">
                                        <span class="pdf-info-label">Généré le</span>
                                        <span class="pdf-info-value" id="pdf-info-date">
                                            <?php echo esc_html(current_time('d/m/Y H:i')); ?>
                                        </span>
                                    </div>
                                    <div class="pdf-info-item">
                                        <span class="pdf-info-label">Temps</span>
                                        <span class="pdf-info-value" id="pdf-info-time">--</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Stats Tab -->
                            <div class="pdf-info-section" data-section="stats">
                                <div class="pdf-stats-grid">
                                    <div class="pdf-stat-card">
                                        <div class="pdf-stat-icon">📊</div>
                                        <div class="pdf-stat-content">
                                            <div class="pdf-stat-value" id="pdf-stat-size">--</div>
                                            <div class="pdf-stat-label">Taille estimée</div>
                                        </div>
                                    </div>
                                    <div class="pdf-stat-card">
                                        <div class="pdf-stat-icon">⚡</div>
                                        <div class="pdf-stat-content">
                                            <div class="pdf-stat-value" id="pdf-stat-performance">--</div>
                                            <div class="pdf-stat-label">Performance</div>
                                        </div>
                                    </div>
                                    <div class="pdf-stat-card">
                                        <div class="pdf-stat-icon">🎨</div>
                                        <div class="pdf-stat-content">
                                            <div class="pdf-stat-value" id="pdf-stat-quality">--</div>
                                            <div class="pdf-stat-label">Qualité</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Actions Tab -->
                            <div class="pdf-info-section" data-section="actions">
                                <div class="pdf-actions-grid">
                                    <button class="pdf-quick-action" id="pdf-quick-generate">
                                        <div class="pdf-action-icon">⚡</div>
                                        <div class="pdf-action-content">
                                            <div class="pdf-action-title">Générer PDF</div>
                                            <div class="pdf-action-desc">Créer le fichier PDF final</div>
                                        </div>
                                    </button>
                                    <button class="pdf-quick-action" id="pdf-quick-email">
                                        <div class="pdf-action-icon">📧</div>
                                        <div class="pdf-action-content">
                                            <div class="pdf-action-title">Envoyer par email</div>
                                            <div class="pdf-action-desc">Envoyer directement au client</div>
                                        </div>
                                    </button>
                                    <button class="pdf-quick-action" id="pdf-quick-save">
                                        <div class="pdf-action-icon">💾</div>
                                        <div class="pdf-action-content">
                                            <div class="pdf-action-title">Sauvegarder</div>
                                            <div class="pdf-action-desc">Conserver pour plus tard</div>
                                        </div>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="pdf-modal-footer">
                    <div class="pdf-footer-left">
                        <div class="pdf-keyboard-hints">
                            <span class="pdf-hint">
                                <kbd>Ctrl</kbd> + <kbd>+</kbd> Zoom avant
                            </span>
                            <span class="pdf-hint">
                                <kbd>Ctrl</kbd> + <kbd>-</kbd> Zoom arrière
                            </span>
                            <span class="pdf-hint">
                                <kbd>Ctrl</kbd> + <kbd>0</kbd> Ajuster
                            </span>
                            <span class="pdf-hint">
                                <kbd>Échap</kbd> Fermer
                            </span>
                        </div>
                    </div>
                    <div class="pdf-footer-right">
                        <button class="pdf-footer-btn pdf-footer-secondary" id="pdf-cancel-btn">Annuler</button>
                        <button class="pdf-footer-btn pdf-footer-primary" id="pdf-confirm-generate">Générer PDF</button>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        // Modern PDF Modal JavaScript
        if (typeof ajaxurl === 'undefined') {
            ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        }

        jQuery(document).ready(function($) {
            let currentTemplateId = <?php echo $selected_template ? intval($selected_template['id']) : 0; ?>;
            let currentZoom = 1.0;
            let originalPdfWidth = 0;
            let originalPdfHeight = 0;
            let previewStartTime = 0;
            let currentTab = 'details';

            // Utility Functions
            function showStatus(message, type = 'loading') {
                const $status = $('#pdf-status');
                const classes = {
                    loading: 'pdf-status-loading',
                    success: 'pdf-status-success',
                    error: 'pdf-status-error'
                };
                $status.removeClass('pdf-status-loading pdf-status-success pdf-status-error show')
                       .addClass(classes[type]).html(message).show();
                setTimeout(() => $status.addClass('show'), 10);
                if (type !== 'loading') setTimeout(hideStatus, 5000);
            }

            function hideStatus() {
                $('#pdf-status').removeClass('show');
                setTimeout(() => $('#pdf-status').hide(), 300);
            }

            function setButtonLoading($btn, loading) {
                if (loading) {
                    $btn.addClass('loading').prop('disabled', true);
                } else {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            }

            function updateTemplateDisplay(templateId, templateName) {
                $('#pdf-template-display .pdf-template-name').text(templateName);
                $('#pdf-info-template').text(templateName);
                currentTemplateId = templateId;
            }

            function showNotification(message, type = 'info') {
                const notification = $(`
                    <div class="pdf-notification pdf-notification-${type}">
                        <div class="pdf-notification-content">
                            <span class="pdf-notification-icon">
                                ${type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️'}
                            </span>
                            <span class="pdf-notification-text">${message}</span>
                        </div>
                    </div>
                `);

                $('.pdf-modal').append(notification);
                setTimeout(() => {
                    notification.addClass('show');
                    setTimeout(() => {
                        notification.removeClass('show');
                        setTimeout(() => notification.remove(), 300);
                    }, 2000);
                }, 100);
            }

            // Modal Management
            function openModal() {
                previewStartTime = Date.now();
                $('#pdf-modal-overlay').addClass('show');
                $('body').addClass('pdf-modal-open');
                $(document).on('keydown.pdf-modal', handleKeydown);

                // Initialize progress bar
                let progress = 0;
                const progressInterval = setInterval(() => {
                    progress += Math.random() * 15;
                    if (progress > 90) progress = 90;
                    $('#pdf-progress-bar').css('width', progress + '%');
                }, 200);

                // Store interval for cleanup
                $('#pdf-modal-overlay').data('progressInterval', progressInterval);
            }

            function closeModal() {
                $('#pdf-modal-overlay').removeClass('show');
                $('body').removeClass('pdf-modal-open');
                $(document).off('keydown.pdf-modal');

                // Clear progress interval
                const progressInterval = $('#pdf-modal-overlay').data('progressInterval');
                if (progressInterval) {
                    clearInterval(progressInterval);
                }

                // Reset state
                currentZoom = 1.0;
                $('#pdf-zoom-level').text('100%');
                $('#pdf-preview-loading').show();
                $('#pdf-preview-error').hide();
                $('#pdf-preview-content').hide();
            }

            function handleKeydown(e) {
                // Escape to close
                if (e.keyCode === 27) {
                    e.preventDefault();
                    closeModal();
                    return;
                }

                // Ctrl/Cmd shortcuts
                if (e.ctrlKey || e.metaKey) {
                    switch (e.key) {
                        case '+':
                        case '=':
                            e.preventDefault();
                            applyZoom(currentZoom * 1.2);
                            showNotification('Zoom avant (Ctrl+)', 'info');
                            break;
                        case '-':
                            e.preventDefault();
                            applyZoom(currentZoom / 1.2);
                            showNotification('Zoom arrière (Ctrl-)', 'info');
                            break;
                        case '0':
                            e.preventDefault();
                            applyZoom(1.0);
                            showNotification('Zoom 100% (Ctrl+0)', 'info');
                            break;
                        case 'p':
                            e.preventDefault();
                            printPdf();
                            break;
                        case 's':
                            e.preventDefault();
                            downloadPdf();
                            break;
                    }
                }

                // Arrow keys for zoom
                switch (e.key) {
                    case 'ArrowUp':
                        e.preventDefault();
                        applyZoom(currentZoom * 1.1);
                        break;
                    case 'ArrowDown':
                        e.preventDefault();
                        applyZoom(currentZoom / 1.1);
                        break;
                    case 'Home':
                        e.preventDefault();
                        applyZoom(1.0);
                        break;
                }
            }

            function applyZoom(zoomLevel) {
                currentZoom = Math.max(0.25, Math.min(4.0, zoomLevel));
                $('#pdf-zoom-level').text(Math.round(currentZoom * 100) + '%');

                const $iframe = $('#pdf-preview-iframe');
                if ($iframe.length) {
                    const baseHeight = originalPdfHeight || 842;
                    const zoomedHeight = baseHeight * currentZoom;

                    $iframe.css({
                        'transform': `scale(${currentZoom})`,
                        'transform-origin': 'top center',
                        'width': `${100 / currentZoom}%`,
                        'height': `${zoomedHeight}px`,
                        'max-width': '100%'
                    });

                    const containerHeight = Math.max(zoomedHeight + 60, 700);
                    $('.pdf-preview-container').css({
                        'min-height': `${containerHeight}px`,
                        'height': 'auto'
                    });
                }
            }

            function printPdf() {
                const $iframe = $('#pdf-preview-iframe')[0];
                if ($iframe && $iframe.contentWindow) {
                    $iframe.contentWindow.print();
                    showNotification('Impression lancée', 'success');
                } else {
                    showNotification('Aperçu non disponible pour l\'impression', 'error');
                }
            }

            function downloadPdf() {
                // Trigger the generate button
                $('#pdf-confirm-generate').trigger('click');
            }

            // Tab Management
            function switchTab(tabName) {
                $('.pdf-info-tab').removeClass('active');
                $(`.pdf-info-tab[data-tab="${tabName}"]`).addClass('active');

                $('.pdf-info-section').removeClass('active');
                $(`.pdf-info-section[data-section="${tabName}"]`).addClass('active');

                currentTab = tabName;
            }

            // Event Handlers
            $('#pdf-template-toggle').on('click', function() {
                const $selector = $('#pdf-template-selector');
                const isVisible = $selector.is(':visible');
                if (isVisible) {
                    $selector.slideUp(300);
                    $(this).text('Changer de template ▼');
                } else {
                    $selector.slideDown(300);
                    $(this).text('Masquer ▲');
                    $('#pdf-template-search').focus();
                }
            });

            $('#pdf-template-search').on('input', function() {
                const searchTerm = $(this).val().toLowerCase();
                let visibleCount = 0;
                $('.pdf-template-option').each(function() {
                    const templateName = $(this).text().toLowerCase();
                    const $option = $(this);
                    if (templateName.includes(searchTerm)) {
                        $option.show();
                        visibleCount++;
                    } else {
                        $option.hide();
                    }
                });

                if (visibleCount === 0 && searchTerm.length > 0) {
                    if (!$('.pdf-no-results').length) {
                        $('#pdf-template-list').append(`
                            <div class="pdf-no-results" style="text-align: center; color: #6c757d; padding: 20px; font-style: italic;">
                                Aucun template trouvé pour "${searchTerm}"
                            </div>
                        `);
                    }
                } else {
                    $('.pdf-no-results').remove();
                }
            });

            $(document).on('click', '.pdf-template-option', function() {
                const templateId = $(this).data('template-id');
                const templateName = $(this).find('span:last-child').text();
                $('.pdf-template-option').removeClass('selected');
                $(this).addClass('selected');
                updateTemplateDisplay(templateId, templateName);
                $('#pdf-template-selector').slideUp(300);
                $('#pdf-template-toggle').text('Changer de template ▼');
                showStatus('Template changé: ' + templateName, 'success');
            });

            // Info Tabs
            $('.pdf-info-tab').on('click', function() {
                const tabName = $(this).data('tab');
                switchTab(tabName);
            });

            // Quick Actions
            $('#pdf-quick-generate').on('click', function() {
                $('#pdf-confirm-generate').trigger('click');
            });

            $('#pdf-quick-email').on('click', function() {
                showNotification('Fonctionnalité d\'email à venir', 'info');
            });

            $('#pdf-quick-save').on('click', function() {
                showNotification('Sauvegarde automatique activée', 'success');
            });

            // Preview Button
            $('#pdf-preview-btn').on('click', function() {
                showStatus('Génération de l\'aperçu...', 'loading');
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
                        const progressInterval = $('#pdf-modal-overlay').data('progressInterval');
                        if (progressInterval) {
                            clearInterval(progressInterval);
                        }
                        $('#pdf-progress-bar').css('width', '100%');

                        if (response.success) {
                            const generationTime = Date.now() - previewStartTime;
                            originalPdfWidth = response.data.width;
                            originalPdfHeight = response.data.height;

                            const $iframe = $('#pdf-preview-iframe');
                            $iframe.css({
                                'width': '100%',
                                'height': Math.max(originalPdfHeight, 600) + 'px',
                                'border': 'none',
                                'background': 'white',
                                'transform-origin': 'top center',
                                'border-radius': '8px',
                                'box-shadow': 'inset 0 0 0 1px rgba(0,0,0,0.1)'
                            });

                            // Write content to iframe
                            const iframeDoc = $iframe[0].contentDocument || $iframe[0].contentWindow.document;
                            iframeDoc.open();
                            iframeDoc.write(response.data.html);
                            iframeDoc.close();

                            // Handle iframe load
                            $iframe.on('load', function() {
                                $('#pdf-preview-loading').hide();
                                $('#pdf-preview-content').show();

                                const iframeBody = $iframe[0].contentDocument.body;
                                if (iframeBody) {
                                    iframeBody.style.margin = '0';
                                    iframeBody.style.padding = '20px';
                                    iframeBody.style.boxSizing = 'border-box';

                                    const contentHeight = iframeBody.scrollHeight;
                                    if (contentHeight > originalPdfHeight) {
                                        $iframe.css('height', contentHeight + 'px');
                                        $('.pdf-preview-container').css('min-height', contentHeight + 60 + 'px');
                                    }
                                }

                                applyZoom(1.0);

                                // Update stats
                                $('#pdf-info-time').text(generationTime + 'ms');
                                $('#pdf-info-date').text(new Date().toLocaleString('fr-FR'));
                                $('#pdf-stat-size').text((response.data.html.length / 1024).toFixed(1) + ' KB');
                                $('#pdf-stat-performance').text(generationTime < 1000 ? 'Excellent' : generationTime < 3000 ? 'Bon' : 'À améliorer');
                                $('#pdf-stat-quality').text('HD');

                                showStatus(`Aperçu généré avec succès (${generationTime}ms)`, 'success');
                            });

                        } else {
                            $('#pdf-preview-loading').hide();
                            $('#pdf-preview-error').show();
                            $('#pdf-error-message').text(response.data || 'Erreur inconnue');
                            showStatus('Erreur lors de l\'aperçu', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#pdf-preview-loading').hide();
                        $('#pdf-preview-error').show();
                        $('#pdf-error-message').text(`Erreur de connexion: ${error}`);
                        console.error('PDF Preview Error:', xhr.responseText, status, error);
                        showStatus(`Erreur AJAX: ${error}`, 'error');
                    },
                    complete: function() {
                        setButtonLoading($('#pdf-preview-btn'), false);
                    }
                });
            });

            // Generate Button
            $('#pdf-generate-btn, #pdf-confirm-generate').on('click', function() {
                showStatus('Génération du PDF...', 'loading');
                setButtonLoading($('#pdf-generate-btn'), true);
                setButtonLoading($('#pdf-confirm-generate'), true);

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
                        if (response.success) {
                            $('#pdf-download-btn').attr('href', response.data.url).show();
                            $('#pdf-modal-download-btn').attr('href', response.data.url);
                            showStatus('PDF généré avec succès', 'success');
                            showNotification('PDF généré avec succès !', 'success');

                            setTimeout(function() {
                                window.open(response.data.url, '_blank');
                            }, 500);
                        } else {
                            showStatus('Erreur lors de la génération', 'error');
                            showNotification('Erreur lors de la génération', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        showStatus('Erreur AJAX lors de la génération', 'error');
                        showNotification('Erreur de connexion', 'error');
                    },
                    complete: function() {
                        setButtonLoading($('#pdf-generate-btn'), false);
                        setButtonLoading($('#pdf-confirm-generate'), false);
                    }
                });
            });

            // Modal Controls
            $('#pdf-close-modal, #pdf-cancel-btn').on('click', closeModal);

            $('#pdf-modal-overlay').on('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            // Zoom Controls
            $('#pdf-zoom-in').on('click', () => applyZoom(currentZoom * 1.2));
            $('#pdf-zoom-out').on('click', () => applyZoom(currentZoom / 1.2));
            $('#pdf-zoom-fit').on('click', function() {
                if (originalPdfWidth > 0) {
                    const containerWidth = $('.pdf-preview-container').width() - 60;
                    const fitZoom = containerWidth / originalPdfWidth;
                    applyZoom(fitZoom);
                    showNotification('Zoom ajusté à la fenêtre', 'info');
                }
            });
            $('#pdf-zoom-reset').on('click', () => applyZoom(1.0));

            // Action Buttons
            $('#pdf-print-btn').on('click', printPdf);
            $('#pdf-modal-download-btn').on('click', downloadPdf);
            $('#pdf-download-btn').on('click', function() {
                const pdfUrl = $(this).attr('href');
                if (pdfUrl) {
                    window.open(pdfUrl, '_blank');
                }
            });

            // Retry Button
            $('#pdf-retry-btn').on('click', function() {
                $('#pdf-preview-error').hide();
                $('#pdf-preview-btn').trigger('click');
            });

            // Double-click to fit
            $(document).on('dblclick', '#pdf-preview-iframe', function() {
                if (originalPdfWidth && originalPdfWidth > 0) {
                    const containerWidth = $('.pdf-preview-container').width() - 60;
                    const fitZoom = Math.min(containerWidth / originalPdfWidth, 1.0);
                    applyZoom(fitZoom);
                    showNotification('Zoom ajusté à la fenêtre', 'info');
                } else {
                    applyZoom(1.0);
                    showNotification('Zoom 100%', 'info');
                }
            });

            // Share functionality (placeholder)
            $('#pdf-share-btn').on('click', function() {
                showNotification('Fonctionnalité de partage à venir', 'info');
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
            // Essayer d'abord de récupérer le canvas personnalisé de la commande
            $canvas_data = $this->load_order_canvas($order_id);

            if ($canvas_data) {
                // Utiliser le canvas personnalisé de la commande
                $template_data = $canvas_data;
                error_log('PDF Preview: Using custom canvas for order ' . $order_id);
            } else {
                // Charger le template si aucun canvas personnalisé n'existe
                if ($template_id > 0) {
                    $template_data = $this->load_template_robust($template_id);
                    error_log('PDF Preview: Loading template ID ' . $template_id . ' for order ' . $order_id);
                } else {
                    // Utiliser le template par défaut ou détecté automatiquement
                    $order_status = $order->get_status();
                    $status_templates = get_option('pdf_builder_order_status_templates', []);
                    $status_key = 'wc-' . $order_status;

                    if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                        $template_data = $this->load_template_robust($status_templates[$status_key]);
                        error_log('PDF Preview: Using status template for status ' . $order_status . ' (ID: ' . $status_templates[$status_key] . ')');
                    } else {
                        $template_data = $this->get_default_invoice_template();
                        error_log('PDF Preview: Using default template for order ' . $order_id);
                    }
                }
            }

            if (!$template_data) {
                error_log('PDF Preview: No template data found for order ' . $order_id);
                wp_send_json_error('Template ou canvas non trouvé');
            }

            // Générer l'HTML d'aperçu avec les données de la commande
            $html_content = $this->generate_unified_html($template_data, $order);
            error_log('PDF Preview: Generated HTML length: ' . strlen($html_content) . ' for order ' . $order_id);

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
        $canvas_width = $template['canvas']['width'] ?? 595;
        $canvas_height = $template['canvas']['height'] ?? 842;

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu PDF - PDF Builder Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 20px;
            font-family: \'Inter\', -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pdf-preview-container {
            background: white;
            border-radius: 12px;
            box-shadow:
                0 20px 25px -5px rgba(0, 0, 0, 0.1),
                0 10px 10px -5px rgba(0, 0, 0, 0.04),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            overflow: hidden;
            position: relative;
        }

        .pdf-canvas {
            position: relative;
            width: ' . $canvas_width . 'px;
            height: ' . $canvas_height . 'px;
            background: white;
            margin: 0;
            padding: 0;
            box-shadow: inset 0 0 0 1px rgba(0, 0, 0, 0.06);
        }

        .pdf-element {
            position: absolute;
            box-sizing: border-box;
            line-height: 1.4;
        }

        .pdf-element.text-element {
            font-family: \'Inter\', sans-serif;
            color: #1a202c;
            font-weight: 400;
        }

        .pdf-element.invoice-title {
            font-family: \'Poppins\', sans-serif;
            font-weight: 700;
            font-size: 28px;
            color: #2d3748;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .pdf-element.order-info {
            font-family: \'Inter\', sans-serif;
            font-weight: 500;
            font-size: 14px;
            color: #4a5568;
            background: rgba(66, 153, 225, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            border-left: 3px solid #3182ce;
        }

        .pdf-element.customer-info {
            font-family: \'Inter\', sans-serif;
            font-weight: 500;
            font-size: 13px;
            color: #2d3748;
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 12px 16px;
            border-radius: 8px;
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
        }

        .pdf-element.company-info {
            font-family: \'Poppins\', sans-serif;
            font-weight: 600;
            font-size: 15px;
            color: #1a202c;
            line-height: 1.6;
        }

        .pdf-element.table-header {
            font-family: \'Inter\', sans-serif;
            font-weight: 600;
            font-size: 12px;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 12px;
            border-radius: 6px 6px 0 0;
        }

        .pdf-element.table-row {
            font-family: \'Inter\', sans-serif;
            font-weight: 400;
            font-size: 12px;
            color: #2d3748;
            background: rgba(255, 255, 255, 0.8);
            padding: 8px 12px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            transition: background-color 0.2s ease;
        }

        .pdf-element.table-row:hover {
            background: rgba(66, 153, 225, 0.05);
        }

        .pdf-element.total-row {
            font-family: \'Poppins\', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: #2d3748;
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 12px 16px;
            border-radius: 6px;
            box-shadow: 0 4px 6px rgba(72, 187, 120, 0.2);
        }

        .pdf-element.invoice-number {
            font-family: \'Poppins\', sans-serif;
            font-weight: 700;
            font-size: 16px;
            color: #e53e3e;
            background: linear-gradient(135deg, #fed7d7 0%, #feb2b2 100%);
            padding: 8px 16px;
            border-radius: 20px;
            border: 2px solid #e53e3e;
            box-shadow: 0 2px 4px rgba(229, 62, 62, 0.2);
        }

        /* Animations et effets */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .pdf-element {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive design */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .pdf-preview-container {
                transform: scale(0.8);
                transform-origin: top center;
            }
        }

        /* Styles pour les éléments spéciaux */
        .pdf-element.bold {
            font-weight: 700;
        }

        .pdf-element.italic {
            font-style: italic;
        }

        .pdf-element.underline {
            text-decoration: underline;
        }

        /* Indicateur de page PDF */
        .pdf-page-indicator {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-family: \'Inter\', sans-serif;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="pdf-preview-container">
        <div class="pdf-canvas">';

        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            $elements = $firstPage['elements'] ?? [];
        } elseif (isset($template['elements']) && is_array($template['elements'])) {
            $elements = $template['elements'];
        } else {
            $elements = [];
        }

        if (is_array($elements)) {
            foreach ($elements as $index => $element) {
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
                    'left: %dpx; top: %dpx; width: %dpx; min-height: %dpx;',
                    $x, $y, $width, $height
                );

                // Classes CSS spéciales basées sur le contenu
                $css_classes = ['pdf-element'];
                $content = $element['content'] ?? '';

                if (stripos($content, 'facture') !== false || stripos($content, 'invoice') !== false) {
                    $css_classes[] = 'invoice-title';
                } elseif (stripos($content, 'commande') !== false || stripos($content, 'order') !== false) {
                    $css_classes[] = 'order-info';
                } elseif (stripos($content, 'client') !== false || stripos($content, 'customer') !== false) {
                    $css_classes[] = 'customer-info';
                } elseif (stripos($content, 'société') !== false || stripos($content, 'company') !== false) {
                    $css_classes[] = 'company-info';
                } elseif (stripos($content, 'total') !== false) {
                    $css_classes[] = 'total-row';
                } elseif (preg_match('/^\d+-\d+$/', $content)) {
                    $css_classes[] = 'invoice-number';
                } else {
                    $css_classes[] = 'text-element';
                }

                if (isset($element['style'])) {
                    if (isset($element['style']['fontSize'])) {
                        $style .= ' font-size: ' . $element['style']['fontSize'] . 'px;';
                    }
                    if (isset($element['style']['fontWeight']) && $element['style']['fontWeight'] > 500) {
                        $css_classes[] = 'bold';
                    }
                    if (isset($element['style']['fontStyle']) && $element['style']['fontStyle'] === 'italic') {
                        $css_classes[] = 'italic';
                    }
                    if (isset($element['style']['textDecoration']) && $element['style']['textDecoration'] === 'underline') {
                        $css_classes[] = 'underline';
                    }
                    if (isset($element['style']['fillColor'])) {
                        $style .= ' background-color: ' . $element['style']['fillColor'] . ';';
                    }
                }

                // Remplacer les variables si on a une commande WooCommerce
                if ($order) {
                    $content = $this->replace_order_variables($content, $order);
                }

                switch ($element['type']) {
                    case 'text':
                        $final_content = $order ? $this->replace_order_variables($content, $order) : $content;
                        $html .= sprintf('<div class="%s" style="%s">%s</div>', implode(' ', $css_classes), $style, esc_html($final_content));
                        break;

                    case 'invoice_number':
                        if ($order) {
                            $invoice_number = $order->get_id() . '-' . time();
                            $html .= sprintf('<div class="%s" style="%s">%s</div>', implode(' ', $css_classes), $style, esc_html($invoice_number));
                        }
                        break;

                    default:
                        $html .= sprintf('<div class="%s" style="%s">%s</div>', implode(' ', $css_classes), $style, esc_html($content));
                        break;
                }
            }
        }

        // Ajouter un indicateur de page
        $html .= '<div class="pdf-page-indicator">Page 1/1</div>';

        $html .= '</div></div></body></html>';
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

    /**
     * Charger le canvas personnalisé d'une commande depuis la base de données
     */
    private function load_order_canvas($order_id) {
        global $wpdb;
        $table_order_canvases = $wpdb->prefix . 'pdf_builder_order_canvases';

        $canvas = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_order_canvases WHERE order_id = %d", $order_id),
            ARRAY_A
        );

        if (!$canvas) {
            return false;
        }

        $canvas_data_raw = $canvas['canvas_data'];

        // Vérifier si les données contiennent des backslashes (échappement PHP)
        if (strpos($canvas_data_raw, '\\') !== false) {
            $canvas_data_raw = stripslashes($canvas_data_raw);
        }

        $canvas_data = json_decode($canvas_data_raw, true);
        if ($canvas_data === null && json_last_error() !== JSON_ERROR_NONE) {
            return false;
        }

        return $canvas_data;
    }

    /**
     * Sauvegarder le canvas personnalisé d'une commande
     */
    public function save_order_canvas($order_id, $canvas_data, $template_id = null) {
        global $wpdb;
        $table_order_canvases = $wpdb->prefix . 'pdf_builder_order_canvases';

        // Encoder les données en JSON
        $canvas_data_json = json_encode($canvas_data);
        if ($canvas_data_json === false) {
            return new WP_Error('json_encode_error', 'Erreur lors de l\'encodage JSON du canvas');
        }

        // Vérifier si un canvas existe déjà pour cette commande
        $existing = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM $table_order_canvases WHERE order_id = %d", $order_id)
        );

        if ($existing) {
            // Mettre à jour
            $result = $wpdb->update(
                $table_order_canvases,
                [
                    'canvas_data' => $canvas_data_json,
                    'template_id' => $template_id,
                    'updated_at' => current_time('mysql')
                ],
                ['order_id' => $order_id],
                ['%s', '%d', '%s'],
                ['%d']
            );
        } else {
            // Insérer
            $result = $wpdb->insert(
                $table_order_canvases,
                [
                    'order_id' => $order_id,
                    'canvas_data' => $canvas_data_json,
                    'template_id' => $template_id
                ],
                ['%d', '%s', '%d']
            );
        }

        if ($result === false) {
            return new WP_Error('db_error', 'Erreur lors de la sauvegarde du canvas en base de données');
        }

        return true;
    }

    /**
     * AJAX handler pour sauvegarder le canvas d'une commande
     */
    public function ajax_save_order_canvas() {
        // Désactiver l'affichage des erreurs PHP
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
        $canvas_data = isset($_POST['canvas_data']) ? $_POST['canvas_data'] : null;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;

        if (!$order_id) {
            wp_send_json_error('ID commande manquant');
        }

        if (!$canvas_data || !is_array($canvas_data)) {
            wp_send_json_error('Données canvas manquantes ou invalides');
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
            $result = $this->save_order_canvas($order_id, $canvas_data, $template_id);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            wp_send_json_success('Canvas sauvegardé avec succès');

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }
}
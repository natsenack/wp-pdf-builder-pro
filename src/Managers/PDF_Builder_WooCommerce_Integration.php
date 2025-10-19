<?php
// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}
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
        // Enregistrer les hooks AJAX via l'action init pour s'assurer qu'ils sont disponibles tôt
        add_action('init', [$this, 'register_ajax_hooks']);
    }

    /**
     * Enregistrer les hooks AJAX
     */
    public function register_ajax_hooks() {
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_pdf', [$this, 'ajax_generate_order_pdf'], 1);
        add_action('wp_ajax_pdf_builder_save_order_canvas', [$this, 'ajax_save_order_canvas'], 1);
        add_action('wp_ajax_pdf_builder_preview_mirror', [$this, 'ajax_preview_mirror'], 1);
        add_action('wp_ajax_pdf_builder_preview_print', [$this, 'ajax_preview_print'], 1);
        add_action('wp_ajax_pdf_builder_preview_download', [$this, 'ajax_preview_download'], 1);
    }
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
     * Rend la meta box dans les commandes WooCommerce - VERSION SIMPLE & ROBUSTE
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
            echo '<div style="padding: 20px; text-align: center; color: #dc3545; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px;">
                    <div style="font-size: 48px; margin-bottom: 10px;">❌</div>
                    <strong>Commande invalide</strong><br>
                    <small>ID commande: ' . esc_html($order_id) . '</small>
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
        
        // Récupérer le label du statut WooCommerce
        $order_statuses = wc_get_order_statuses();
        $status_label = isset($order_statuses['wc-' . $order_status]) ? $order_statuses['wc-' . $order_status] : ucfirst($order_status);
        
        // Déterminer si le template a été trouvé via mapping ou fallback
        $template_source = 'par défaut';
        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            $template_source = 'configuré pour ce statut';
        }
        ?>
        <style>
        /* Meta Box Styles */
        .pdf-meta-box {
            padding: 20px;
        }

        .pdf-template-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .pdf-template-title {
            font-size: 16px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
        }

        .pdf-template-display {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: white;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }

        .pdf-template-icon {
            font-size: 24px;
        }

        .pdf-template-info {
            flex: 1;
        }

        .pdf-template-name {
            font-weight: 600;
            color: #212529;
        }

        .pdf-template-meta {
            font-size: 12px;
            color: #6c757d;
            margin-top: 2px;
        }

        .pdf-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        .pdf-btn {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 14px;
            position: relative;
            z-index: 1;
            pointer-events: auto;
        }

        .pdf-btn-generate {
            background: #007bff;
            color: white;
        }

        .pdf-btn-generate:hover {
            background: #0056b3;
        }

        .pdf-btn-download {
            background: #6c757d;
            color: white;
        }

        .pdf-status {
            padding: 10px;
            border-radius: 6px;
            font-size: 14px;
            text-align: center;
            display: none;
        }

        .pdf-status-loading {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .pdf-status-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .pdf-status-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Info Notice Styles */
        .pdf-info-notice {
            margin-top: 15px;
            padding: 12px;
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 6px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
        }

        .pdf-info-icon {
            font-size: 16px;
            flex-shrink: 0;
        }

        .pdf-info-content strong {
            color: #1565c0;
            display: block;
            margin-bottom: 2px;
        }

        .pdf-info-content small {
            color: #424242;
            line-height: 1.3;
        }
        </style>

        <div class="pdf-meta-box">
            <!-- Status Section -->
            <div class="pdf-template-section" style="background: #e3f2fd; border-color: #bbdefb;">
                <div class="pdf-template-title" style="color: #1565c0;">
                    📊 État de la commande
                </div>
                <div class="pdf-template-display" style="background: white; justify-content: space-between;">
                    <div>
                        <div class="pdf-template-name"><?php echo esc_html($status_label); ?></div>
                        <div class="pdf-template-meta">Statut actuel de la commande</div>
                    </div>
                    <div style="font-size: 24px;">
                        <?php
                        $status_icons = [
                            'pending' => '⏳',
                            'processing' => '⚙️',
                            'on-hold' => '⏸️',
                            'completed' => '✅',
                            'cancelled' => '❌',
                            'refunded' => '💰',
                            'failed' => '⚠️'
                        ];
                        echo isset($status_icons[$order_status]) ? $status_icons[$order_status] : '❓';
                        ?>
                    </div>
                </div>
            </div>

            <!-- Template Section -->
            <div class="pdf-template-section">
                <div class="pdf-template-title">
                    📋 Template sélectionné
                </div>

                <div class="pdf-template-display">
                    <span class="pdf-template-icon">�</span>
                    <div class="pdf-template-info">
                        <div class="pdf-template-name">
                            <?php echo $selected_template ? esc_html($selected_template['name']) : 'Aucun template disponible'; ?>
                        </div>
                        <div class="pdf-template-meta">
                            <?php if ($selected_template): ?>
                                <?php echo esc_html($template_source); ?> • Prêt pour génération
                            <?php else: ?>
                                Aucun template trouvé dans la base de données
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="pdf-actions">
                <button type="button" class="pdf-btn pdf-btn-generate" id="pdf-generate-btn" style="flex: 0.7;">
                    <span>⚡</span>
                    Générer PDF
                </button>

                <button type="button" class="pdf-btn" id="pdf-preview-btn" style="flex: 0.6; background: #17a2b8; color: white;">
                    <span>👁️</span>
                    Aperçu
                </button>

                <button type="button" class="pdf-btn" id="pdf-preview-mirror-btn" style="flex: 0.8; background: #28a745; color: white;">
                    <span>🔄</span>
                    Aperçu Miroir
                </button>

                <button type="button" class="pdf-btn pdf-btn-download" id="pdf-download-btn" style="display: none; flex: 0.7;">
                    <span>⬇️</span>
                    Télécharger
                </button>
            </div>

            <!-- Info Section -->
            <div class="pdf-info-notice">
                <div class="pdf-info-icon">✅</div>
                <div class="pdf-info-content">
                    <strong>Template synchronisé</strong><br>
                    <small>Cet aperçu utilise le template actuellement enregistré.<br>
                    <em>Modifiez et sauvegardez votre template dans l'éditeur pour mettre à jour cet aperçu.</em></small>
                </div>
            </div>

            <!-- Status Messages -->
            <div class="pdf-status" id="pdf-status"></div>
        </div>

        <!-- MODALE D'APERÇU PDF -->
        <div id="woo-pdf-preview-modal" class="woo-pdf-preview-modal" style="display: none;">
            <div class="woo-pdf-preview-modal-overlay"></div>
            <div class="woo-pdf-preview-modal-container">
                <div class="woo-pdf-preview-modal-header">
                    <h3>Aperçu PDF - Commande #<?php echo intval($order_id); ?></h3>
                    <button class="woo-pdf-preview-modal-close" title="Fermer">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="woo-pdf-preview-modal-body">
                    <div class="woo-pdf-preview-toolbar">
                        <div class="zoom-controls">
                            <button class="zoom-btn" id="zoom-in-btn" title="Agrandir">+ 125%</button>
                            <button class="zoom-btn" id="zoom-fit-btn" title="Ajuster à la page">Ajuster</button>
                            <button class="zoom-btn" id="zoom-out-btn" title="Réduire">- 75%</button>
                            <span class="zoom-display" id="zoom-display">100%</span>
                        </div>
                    </div>
                    <iframe id="woo-pdf-preview-iframe" 
                            style="width: 100%; height: 100%; border: none; background: white; display: block; flex: 1;"
                            title="Aperçu PDF"
                            allow="fullscreen"></iframe>
                    <div class="woo-pdf-preview-loading" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(255,255,255,0.9); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 100; border-radius: 4px;">
                        <div style="font-size: 3em; margin-bottom: 20px;">📄</div>
                        <p style="margin: 0 0 20px 0; color: #374151; font-weight: 500; font-size: 16px;">Génération de l'aperçu...</p>
                        <div class="woo-pdf-preview-spinner"></div>
                    </div>
                </div>
                <div class="woo-pdf-preview-modal-footer">
                    <button class="woo-pdf-preview-print-btn" title="Imprimer">
                        🖨️ Imprimer
                    </button>
                    <button class="woo-pdf-preview-download-btn" title="Télécharger">
                        &#128190; Télécharger
                    </button>
                    <button class="woo-pdf-preview-modal-close-btn">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

        <!-- STYLES MODALE -->
        <style>
            .woo-pdf-preview-modal {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 9999;
            }

            .woo-pdf-preview-modal-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(3px);
            }

            .woo-pdf-preview-modal-container {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.4), 0 0 0 1px rgba(0, 0, 0, 0.05);
                width: 95%;
                max-width: 1200px;
                height: 90vh;
                max-height: 900px;
                display: flex;
                flex-direction: column;
                animation: wooSlideIn 0.3s ease-out;
                overflow: hidden;
            }

            @keyframes wooSlideIn {
                from {
                    opacity: 0;
                    transform: translate(-50%, -48%) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, -50%) scale(1);
                }
            }

            .woo-pdf-preview-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 24px;
                border-bottom: 1px solid #e5e7eb;
                background: linear-gradient(135deg, #f8fafb 0%, #f3f4f6 100%);
                border-radius: 12px 12px 0 0;
                flex-shrink: 0;
            }

            .woo-pdf-preview-modal-header h3 {
                margin: 0;
                font-size: 18px;
                font-weight: 700;
                color: #111827;
                letter-spacing: -0.3px;
            }

            .woo-pdf-preview-modal-close {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 24px;
                color: #9ca3af;
                padding: 0;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 6px;
                transition: all 0.2s ease;
            }

            .woo-pdf-preview-modal-close:hover {
                background: rgba(0, 0, 0, 0.05);
                color: #374151;
            }

            .woo-pdf-preview-modal-body {
                flex: 1;
                overflow: auto;
                background: #f0f0f0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                padding: 0;
                gap: 0;
                min-height: 0;
                position: relative;
                width: 100%;
                height: 100%;
                padding-top: 0;
            }

            #woo-pdf-preview-iframe {
                width: 100% !important;
                height: 100% !important;
                border: none !important;
                display: block !important;
                background: white !important;
                min-height: 600px !important;
                flex: 1 !important;
                object-fit: contain !important;
            }

            .woo-pdf-preview-toolbar {
                display: flex;
                justify-content: center;
                align-items: center;
                padding: 12px 16px;
                background: #f8fafc;
                border-bottom: 1px solid #e5e7eb;
                flex-shrink: 0;
                gap: 12px;
            }

            .zoom-controls {
                display: flex;
                gap: 8px;
                align-items: center;
                flex-wrap: wrap;
                justify-content: center;
            }

            .zoom-btn {
                padding: 6px 14px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                background: white;
                color: #374151;
                cursor: pointer;
                font-size: 12px;
                font-weight: 500;
                transition: all 0.2s ease;
                white-space: nowrap;
            }

            .zoom-btn:hover {
                background: #f3f4f6;
                border-color: #9ca3af;
                color: #111827;
            }

            .zoom-btn.active {
                background: #3b82f6;
                color: white;
                border-color: #3b82f6;
            }

            .zoom-display {
                font-size: 12px;
                font-weight: 600;
                color: #6b7280;
                min-width: 55px;
                text-align: center;
            }

            .woo-pdf-preview-container {
                display: none;
            }

            .woo-pdf-preview-modal-footer {
                display: flex;
                justify-content: flex-end;
                gap: 12px;
                padding: 16px 24px;
                border-top: 1px solid #e5e7eb;
                background: linear-gradient(135deg, #f8fafb 0%, #f3f4f6 100%);
                border-radius: 0 0 12px 12px;
                flex-shrink: 0;
            }

            .woo-pdf-preview-download-btn,
            .woo-pdf-preview-print-btn,
            .woo-pdf-preview-modal-close-btn {
                padding: 10px 18px;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                background: white;
                color: #374151;
                cursor: pointer;
                font-size: 14px;
                font-weight: 600;
                transition: all 0.2s ease;
            }

            .woo-pdf-preview-download-btn {
                background: #3b82f6;
                color: white;
                border-color: #3b82f6;
            }

            .woo-pdf-preview-download-btn:hover {
                background: #2563eb;
                border-color: #2563eb;
                box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            }

            .woo-pdf-preview-print-btn {
                background: #10b981;
                color: white;
                border-color: #10b981;
            }

            .woo-pdf-preview-print-btn:hover {
                background: #059669;
                border-color: #059669;
                box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            }

            .woo-pdf-preview-modal-close-btn:hover {
                background: #f3f4f6;
                border-color: #9ca3af;
                color: #111827;
            }

            .woo-pdf-preview-spinner {
                display: inline-block;
                width: 40px;
                height: 40px;
                border: 4px solid #f3f4f6;
                border-top: 4px solid #3b82f6;
                border-radius: 50%;
                animation: wooSpin 1s linear infinite;
                margin-top: 20px;
            }

            @keyframes wooSpin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            /* Scrollbar personnalisée pour le PDF */
            .woo-pdf-preview-container::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            .woo-pdf-preview-container::-webkit-scrollbar-track {
                background: #f3f4f6;
                border-radius: 4px;
            }

            .woo-pdf-preview-container::-webkit-scrollbar-thumb {
                background: #d1d5db;
                border-radius: 4px;
            }

            .woo-pdf-preview-container::-webkit-scrollbar-thumb:hover {
                background: #9ca3af;
            }

            @media (max-width: 768px) {
                .woo-pdf-preview-modal-container {
                    width: 98%;
                    height: 90vh;
                    border-radius: 0;
                }

                .woo-pdf-preview-modal-header,
                .woo-pdf-preview-modal-footer {
                    border-radius: 0;
                }

                .zoom-controls {
                    flex-direction: column;
                    gap: 6px;
                }

                .zoom-btn {
                    width: 100%;
                }

                .pdf-page-wrapper {
                    max-width: 100%;
                }
            }
        </style>

        <script type="text/javascript">
        // Simple & Robust PDF JavaScript
        jQuery(document).ready(function($) {
            console.log('METABOXES.JS LOADED - WOO PDF INVOICE DEBUG');
            console.log('MetaBoxes.js jQuery ready - WooCommerce PDF Invoice metabox initializing');

            // Configuration
            var orderId = <?php echo intval($order_id); ?>;
            var templateId = <?php echo $selected_template ? intval($selected_template['id']) : 0; ?>;
            var ajaxUrl = "https://threeaxe.fr/wp-admin/admin-ajax.php";
            var nonce = pdfBuilderAjax.nonce;

            console.log("MetaBoxes.js - Configuration loaded:", {
                orderId: orderId,
                templateId: templateId,
                ajaxUrl: ajaxUrl,
                nonceLength: nonce.length
            });

            // Utility functions
            function showStatus(message, type) {
                console.log("MetaBoxes.js - showStatus called:", message, type);
                var $status = $("#pdf-status");
                $status.removeClass("pdf-status-loading pdf-status-success pdf-status-error")
                       .addClass("pdf-status-" + type)
                       .html(message)
                       .show();

                if (type !== "loading") {
                    setTimeout(function() {
                        $status.fadeOut();
                    }, 5000);
                }
            }

            function setButtonLoading($btn, loading) {
                console.log("MetaBoxes.js - setButtonLoading:", loading ? "loading" : "not loading");
                if (loading) {
                    $btn.prop("disabled", true).css("opacity", "0.6");
                } else {
                    $btn.prop("disabled", false).css("opacity", "1");
                }
            }

            $("#pdf-generate-btn").on("click", function() {
                console.log("PDF BUILDER - Generate button clicked");
                showStatus("Génération du PDF...", "loading");
                setButtonLoading($(this), true);

                console.log('PDF BUILDER - Sending AJAX request for generation:', {
                    action: 'pdf_builder_generate_order_pdf_test',
                    order_id: orderId,
                    template_id: templateId,
                    nonce: nonce.substring(0, 10) + '...'
                });

                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: {
                        action: "pdf_builder_generate_order_pdf_test",
                        order_id: orderId,
                        template_id: templateId,
                        nonce: nonce
                    },
                    success: function(response) {
                        console.log("PDF BUILDER - Generate AJAX success response:", response);
                        if (response.success && response.data && response.data.url) {
                            $('#pdf-download-btn').attr('href', response.data.url).show();
                            showStatus("PDF généré avec succès", "success");

                            // Auto-download after a short delay
                            setTimeout(function() {
                                window.open(response.data.url, "_blank");
                            }, 500);
                        } else {
                            var errorMsg = response.data || "Erreur lors de la génération";
                            showStatus(errorMsg, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        showStatus("Erreur AJAX: " + error, "error");
                    },
                    complete: function() {
                        setButtonLoading($('#pdf-generate-btn'), false);
                    }
                });
            });

            $('#pdf-download-btn').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if (url) {
                    window.open(url, "_blank");
                }
            });

            // Handler pour le bouton APERÇU PDF
            $('#pdf-preview-btn').on('click', function(e) {
                e.preventDefault();
                
                console.log("PDF Preview - Aperçu PDF clicked");
                console.log("Using nonce:", nonce.substring(0, 5) + "...");
                
                // Afficher la modale avec loading
                var $modal = $('#woo-pdf-preview-modal');
                var $loading = $modal.find('.woo-pdf-preview-loading');
                var $iframe = $modal.find('#woo-pdf-preview-iframe');
                var $body = $modal.find('.woo-pdf-preview-modal-body');
                
                $modal.show();
                $loading.show();

                showStatus("Chargement de l'aperçu en cours...", "loading");

                // Charger directement la page d'aperçu Canvas au lieu de générer un PDF TCPDF
                // L'aperçu Canvas est identique à l'éditeur et s'affiche plus rapidement
                var previewUrl = "<?php echo admin_url('admin-ajax.php'); ?>" +
                    "?action=pdf_builder_canvas_preview" +
                    "&order_id=" + encodeURIComponent(orderId) +
                    "&template_id=" + encodeURIComponent(templateId) +
                    "&nonce=" + encodeURIComponent(nonce);
                
                console.log("Loading canvas preview from:", previewUrl);
                
                // Charger l'iframe avec l'aperçu Canvas
                $iframe.attr('src', previewUrl);
                
                // Masquer le loading et afficher l'iframe
                setTimeout(function() {
                    $loading.hide();
                    $iframe.css('display', 'block');
                    // Attendre que l'iframe soit chargé
                    $iframe.on('load', function() {
                        console.log('Canvas preview iframe loaded');
                        showStatus("Aperçu chargé avec succès ✓", "success");
                    });
                }, 300);
            });

            // Gérer la fermeture de la modale
            $('#woo-pdf-preview-modal .woo-pdf-preview-modal-close, #woo-pdf-preview-modal .woo-pdf-preview-modal-close-btn, #woo-pdf-preview-modal .woo-pdf-preview-modal-overlay').on('click', function(e) {
                if ($(this).hasClass('woo-pdf-preview-modal-overlay') || $(this).closest('.woo-pdf-preview-modal-header, .woo-pdf-preview-modal-footer').length) {
                    e.preventDefault();
                    $('#woo-pdf-preview-modal').hide();
                }
            });

            // Gestionnaire pour le bouton Aperçu Miroir
            $('#pdf-preview-mirror-btn').on('click', function(e) {
                e.preventDefault();
                console.log("PDF Preview Mirror - Aperçu Miroir clicked");
                
                showStatus("Génération de l'aperçu miroir...", "loading");
                
                var data = {
                    action: 'pdf_builder_preview_mirror',
                    nonce: nonce,
                    order_id: orderId,
                    template_id: templateId
                };
                
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        console.log("Preview mirror response:", response);
                        
                        if (response.success && response.data.html) {
                            // Ouvrir le miroir PDF dans une nouvelle fenêtre
                            var mirrorWindow = window.open('', 'pdf_preview_mirror', 'width=1200,height=800,scrollbars=yes,resizable=yes');
                            mirrorWindow.document.write(response.data.html);
                            mirrorWindow.document.close();
                            
                            showStatus("Aperçu miroir généré avec succès ✓", "success");
                        } else {
                            var errorMsg = response.data || "Erreur lors de la génération";
                            showStatus("Erreur: " + errorMsg, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("Preview mirror error:", error);
                        showStatus("Erreur: " + error, "error");
                    }
                });
            });

            // Gestion du zoom pour l'aperçu PDF
            // Note: Le zoom avec iframe n'est pas aussi efficace, utilisons plutôt les outils du PDF natif
            var currentZoom = 100;

            function updateZoomDisplay() {
                $('#zoom-display').text(currentZoom + '%');
            }

            $('#zoom-in-btn').on('click', function(e) {
                e.preventDefault();
                currentZoom = Math.min(currentZoom + 25, 200);
                updateZoomDisplay();
            });

            $('#zoom-out-btn').on('click', function(e) {
                e.preventDefault();
                currentZoom = Math.max(currentZoom - 25, 50);
                updateZoomDisplay();
            });

            $('#zoom-fit-btn').on('click', function(e) {
                e.preventDefault();
                currentZoom = 100;
                updateZoomDisplay();
            });

            // Télécharger le PDF depuis la modale
            $('#woo-pdf-preview-modal .woo-pdf-preview-download-btn').on('click', function(e) {
                e.preventDefault();
                showStatus("Génération du PDF en cours...", "loading");
                
                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: {
                        action: "pdf_builder_generate_order_pdf",
                        order_id: orderId,
                        template_id: templateId,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success && response.data && response.data.url) {
                            var link = document.createElement('a');
                            link.href = response.data.url;
                            link.download = 'commande-' + orderId + '.pdf';
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                            showStatus("PDF téléchargé avec succès ✓", "success");
                        } else {
                            var errorMsg = response.data || "Erreur lors de la génération";
                            showStatus(errorMsg, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        showStatus("Erreur: " + error, "error");
                    }
                });
            });

            // Imprimer le PDF depuis la modale
            $('#woo-pdf-preview-modal .woo-pdf-preview-print-btn').on('click', function(e) {
                e.preventDefault();
                showStatus("Génération du PDF pour impression...", "loading");
                
                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: {
                        action: "pdf_builder_generate_order_pdf",
                        order_id: orderId,
                        template_id: templateId,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success && response.data && response.data.url) {
                            // Ouvrir le PDF en nouveau onglet pour permettre l'impression
                            window.open(response.data.url, "_blank");
                            showStatus("PDF ouvert pour impression ✓", "success");
                        } else {
                            var errorMsg = response.data || "Erreur lors de la génération";
                            showStatus(errorMsg, "error");
                        }
                    },
                    error: function(xhr, status, error) {
                        showStatus("Erreur: " + error, "error");
                    }
                });
            });

            console.log('MetaBoxes.js initialization complete - button handler attached');
            console.log('BUTTONS STATUS:', {
                'pdf-generate-btn': $('#pdf-generate-btn').length,
                'pdf-preview-btn': $('#pdf-preview-btn').length,
                'pdf-download-btn': $('#pdf-download-btn').length
            });
        });
        </script>
        
        <!-- DEBUG: Button test script -->
        <script>
        jQuery(function($) {
            console.log('DEBUG: Document ready check');
            console.log('DEBUG: #pdf-generate-btn exists:', $('#pdf-generate-btn').length > 0);
            console.log('DEBUG: #pdf-preview-btn exists:', $('#pdf-preview-btn').length > 0);
            
            // Direct onclick test
            document.addEventListener('click', function(e) {
                if (e.target.id === 'pdf-generate-btn' || e.target.closest('#pdf-generate-btn')) {
                    console.log('DEBUG: Generate btn clicked directly');
                }
                if (e.target.id === 'pdf-preview-btn' || e.target.closest('#pdf-preview-btn')) {
                    console.log('DEBUG: Preview btn clicked directly');
                }
            }, true);
        });
        </script>
        <?php
    }

    /**
     * AJAX handler pour générer le PDF d'une commande
     */
    public function ajax_generate_order_pdf() {
        // Log immédiat pour vérifier si la fonction est appelée
        error_log('PDF BUILDER DEBUG: ajax_generate_order_pdf function STARTED');
        error_log('PDF BUILDER DEBUG: POST data: ' . print_r($_POST, true));
        error_log('PDF BUILDER DEBUG: REQUEST data: ' . print_r($_REQUEST, true));

        // Vérifier les permissions
        if (!current_user_can('manage_woocommerce')) {
            error_log('PDF BUILDER DEBUG: Permission check failed');
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            error_log('PDF BUILDER DEBUG: Nonce verification failed - received: ' . ($_POST['nonce'] ?? 'none'));
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        error_log("PDF BUILDER DEBUG: Parameters - order_id: $order_id, template_id: $template_id");

        if (!$order_id) {
            error_log('PDF BUILDER DEBUG: Order ID missing');
            wp_send_json_error('ID commande manquant');
        }

        try {
            // Charger la commande WooCommerce
            $order = wc_get_order($order_id);
            if (!$order) {
                error_log('PDF BUILDER DEBUG: Order not found');
                wp_send_json_error('Commande introuvable');
            }

            // Charger le template
            if ($template_id > 0) {
                $templates = get_option('pdf_builder_templates', []);
                $template_data = isset($templates[$template_id]) ? $templates[$template_id] : null;
            } else {
                // Détecter automatiquement le template basé sur le statut de la commande
                $order_status = $order->get_status();
                $status_templates = get_option('pdf_builder_order_status_templates', []);
                $status_key = 'wc-' . $order_status;

                if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                    $mapped_template_id = $status_templates[$status_key];
                    $templates = get_option('pdf_builder_templates', []);
                    $template_data = isset($templates[$mapped_template_id]) ? $templates[$mapped_template_id] : null;
                } else {
                    // Template par défaut - prendre le premier template disponible
                    $templates = get_option('pdf_builder_templates', []);
                    $template_data = !empty($templates) ? reset($templates) : null;
                }
            }

            if (!$template_data) {
                error_log('PDF BUILDER DEBUG: Template not found');
                wp_send_json_error('Template non trouvé');
            }

            // Extraire les éléments du template
            $elements = [];
            if (isset($template_data['pages']) && is_array($template_data['pages']) && !empty($template_data['pages'])) {
                $elements = $template_data['pages'][0]['elements'] ?? [];
            } elseif (isset($template_data['elements'])) {
                $elements = $template_data['elements'];
            }

            error_log('PDF BUILDER DEBUG: Elements count: ' . count($elements));

            // Générer le PDF SANS TCPDF - utiliser la nouvelle approche HTML
            error_log('PDF BUILDER DEBUG: About to generate PDF without TCPDF using new HTML approach');

            // Générer le HTML depuis les éléments du template
            $generator = new PDF_Builder_Pro_Generator();
            error_log('PDF BUILDER DEBUG: PDF_Builder_Pro_Generator instantiated');

            $html_content = $generator->generate($elements, ['is_preview' => false, 'order' => $order]);
            error_log('PDF BUILDER DEBUG: HTML generated, length: ' . strlen($html_content));

            // Créer un fichier HTML temporaire pour le téléchargement
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';
            error_log('PDF BUILDER DEBUG: Upload dir: ' . $upload_dir['basedir']);
            error_log('PDF BUILDER DEBUG: Temp dir: ' . $temp_dir);

            // Créer le dossier temporaire s'il n'existe pas
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
                error_log('PDF BUILDER DEBUG: Created temp directory');
            }

            // Générer un nom de fichier unique
            $filename = 'pdf-preview-' . $order_id . '-' . time() . '.html';
            $file_path = $temp_dir . '/' . $filename;
            error_log('PDF BUILDER DEBUG: File path: ' . $file_path);

            // Sauvegarder le HTML dans le fichier
            if (file_put_contents($file_path, $html_content) === false) {
                error_log('PDF BUILDER DEBUG: Failed to write HTML file');
                wp_send_json_error('Erreur lors de la création du fichier HTML');
            }

            error_log('PDF BUILDER DEBUG: HTML file written successfully');

            // Créer l'URL de téléchargement
            $download_url = $upload_dir['baseurl'] . '/pdf-builder-temp/' . $filename;
            error_log('PDF BUILDER DEBUG: Download URL: ' . $download_url);

            wp_send_json_success([
                'message' => 'HTML généré avec succès - TCPDF supprimé complètement',
                'url' => $download_url,
                'html_url' => $download_url,
                'pdf_url' => null
            ]);

        } catch (Exception $e) {
            error_log('PDF BUILDER DEBUG: Exception: ' . $e->getMessage());
            wp_send_json_error('Erreur Exception: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('PDF BUILDER DEBUG: Error: ' . $e->getMessage());
            wp_send_json_error('Erreur PHP: ' . $e->getMessage());
        } catch (Throwable $e) {
            error_log('PDF BUILDER DEBUG: Throwable: ' . $e->getMessage());
            wp_send_json_error('Erreur Throwable: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler pour générer l'aperçu PDF d'une commande
     */
    public function ajax_generate_order_pdf_preview() {
        // S'assurer que les headers JSON sont envoyés en premier
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=UTF-8');
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        }

        // Gestionnaire d'erreur temporaire pour capturer les warnings PHP
        $original_error_handler = set_error_handler(function($errno, $errstr, $errfile, $errline) {
            // Convertir les warnings et erreurs en exceptions
            if ($errno & (E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE)) {
                throw new Exception('PHP Warning: ' . $errstr . ' in ' . $errfile . ':' . $errline);
            }
            return false;
        });

        try {
            // Vérifier les permissions
            if (!current_user_can('manage_woocommerce') && !current_user_can('read')) {
                wp_send_json_error('Permissions insuffisantes');
            }


            // Vérification de sécurité - accepter plusieurs nonces pour flexibilité
            $valid_nonces = ['pdf_builder_order_actions', 'pdf_builder_template_actions'];
            $nonce_valid = false;

            foreach ($valid_nonces as $nonce_action) {
                if (wp_verify_nonce($_POST['nonce'] ?? '', $nonce_action)) {
                    $nonce_valid = true;
                    break;
                }
            }

            if (!$nonce_valid) {
                wp_send_json_error('Sécurité: Nonce invalide');
            }


            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
            $elements = isset($_POST['elements']) ? $_POST['elements'] : null;
            $preview_type = isset($_POST['preview_type']) ? $_POST['preview_type'] : 'html'; // 'pdf' ou 'html' - FORCER HTML POUR ÉVITER TCPDF



            // S'assurer que la classe PDF_Builder_Pro_Generator est chargée
            if (!class_exists('PDF_Builder_Pro_Generator')) {
                $generator_path = plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'includes/pdf-generator.php';
                if (file_exists($generator_path)) {
                    require_once $generator_path;
                } else {
                    wp_send_json_error('Fichier générateur PDF non trouvé');
                }
            }


            $generator = new PDF_Builder_Pro_Generator();

            // Déterminer le type d'aperçu
            if (!empty($elements)) {
                // Priorité aux éléments passés directement (depuis l'éditeur Canvas)

                // Nettoyer les slashes échappés par PHP (correction force)
                $clean_elements = stripslashes($elements);

                // Décoder les éléments
                $decoded_elements = json_decode($clean_elements, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Données du template invalides');
                }


                if ($preview_type === 'html') {
                    // Générer l'aperçu HTML avec les éléments du Canvas - SANS TCPDF
                    $result = $generator->generate($decoded_elements, ['is_preview' => true]);
                } else {
                    // PLUS DE GÉNÉRATION PDF AVEC TCPDF - Forcer HTML
                    $result = $generator->generate($decoded_elements, ['is_preview' => true]);
                }

            } elseif ($order_id && $order_id > 0) {
                // Aperçu de template depuis l'éditeur (éléments JSON)

                // Nettoyer les slashes échappés par PHP (correction force)
                $clean_elements = stripslashes($elements);

                // Décoder les éléments
                $decoded_elements = json_decode($clean_elements, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Données du template invalides');
                }


                if ($preview_type === 'html') {
                    // Générer l'aperçu HTML avec les éléments du template - SANS TCPDF
                    $result = $generator->generate($decoded_elements, ['is_preview' => true]);
                } else {
                    // PLUS DE GÉNÉRATION PDF AVEC TCPDF - Forcer HTML
                    $result = $generator->generate($decoded_elements, ['is_preview' => true]);
                }

            } elseif ($order_id && $order_id > 0) {
                // Aperçu de commande WooCommerce - récupérer depuis la base de données

                // Vérifier que WooCommerce est actif
                if (!class_exists('WooCommerce')) {
                    wp_send_json_error('WooCommerce n\'est pas installé ou activé');
                }

                $order = wc_get_order($order_id);
                if (!$order) {
                    wp_send_json_error('Commande non trouvée');
                }

                // Déterminer le template à utiliser
                if (!$template_id || $template_id <= 0) {
                    $template_id = $this->get_template_for_order($order);
                }

                if ($preview_type === 'html') {
                    // Pour l'aperçu HTML, récupérer les éléments du template depuis la base de données
                    global $wpdb;
                    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
                    $template = $wpdb->get_row($wpdb->prepare("SELECT id, name, template_data FROM $table_templates WHERE id = %d", $template_id), ARRAY_A);

                    if (!$template) {
                        wp_send_json_error('Template non trouvé');
                    }


                    $template_data = json_decode($template['template_data'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        wp_send_json_error('Données du template invalides');
                    }

                    $elements_for_html = isset($template_data['elements']) ? $template_data['elements'] : [];

                    $result = $generator->render_html_preview($elements_for_html, $order_id);
                } else {
                    // Aperçu PDF normal
                    $result = $generator->generate_simple_preview($order_id, $template_id);
                }

            } else {
                wp_send_json_error('Contexte d\'aperçu invalide');
            }

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            // Gérer les différents types d'aperçu
            if ($preview_type === 'html') {
                // Pour l'aperçu HTML, retourner directement le HTML généré
                wp_send_json_success(['html' => $result]);
            } else {
                // Pour l'aperçu PDF, vérifier le fichier et retourner l'URL

                // Vérifier si le fichier existe réellement
                $file_path = str_replace(home_url('/'), ABSPATH, $result);
                if (file_exists($file_path)) {
                }

                wp_send_json_success(['url' => $result]);
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        } catch (Throwable $t) {
            wp_send_json_error('Erreur fatale: ' . $t->getMessage());
        } finally {
            // Restaurer le gestionnaire d'erreur original
            if (isset($original_error_handler)) {
                set_error_handler($original_error_handler);
            }
        }
    }

    /**
     * AJAX handler pour générer l'aperçu miroir du PDF avec les vraies données
     */
    public function ajax_preview_mirror() {
        // Log pour debug
        error_log('PDF Builder: ajax_preview_mirror called');

        // Vérifier le nonce avec l'action globale
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_nonce')) {
            error_log('PDF Builder: Nonce verification failed');
            wp_send_json_error('Nonce invalide');
        }

        if (!current_user_can('manage_woocommerce')) {
            error_log('PDF Builder: User does not have manage_woocommerce capability');
            wp_send_json_error('Permissions insuffisantes');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        error_log("PDF Builder: ajax_preview_mirror - order_id: $order_id, template_id: $template_id");

        if (!$order_id) {
            error_log('PDF Builder: No order_id provided');
            wp_send_json_error('ID commande manquant');
        }

        error_log('PDF Builder: Getting order object');
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log("PDF Builder: Order $order_id not found");
            wp_send_json_error('Commande introuvable');
        }

        error_log('PDF Builder: Order found, processing template');
        try {
            if ($template_id > 0) {
                error_log('PDF Builder: Loading specific template');
                $templates = get_option('pdf_builder_templates', []);
                error_log('PDF Builder: Available templates: ' . json_encode(array_keys($templates)));
                $template_data = isset($templates[$template_id]) ? $templates[$template_id] : null;
                
                // If specific template not found, fall back to auto-detection
                if (!$template_data) {
                    error_log('PDF Builder: Specific template not found, falling back to auto-detection');
                    if ($order) {
                        $order_status = $order->get_status();
                        $status_templates = get_option('pdf_builder_order_status_templates', []);
                        $status_key = 'wc-' . $order_status;

                        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                            $mapped_template_id = $status_templates[$status_key];
                            $templates = get_option('pdf_builder_templates', []);
                            $template_data = isset($templates[$mapped_template_id]) ? $templates[$mapped_template_id] : null;
                            error_log('PDF Builder: Auto-detected template for status ' . $order_status . ': ' . ($template_data ? 'found' : 'not found'));
                        } else {
                            $templates = get_option('pdf_builder_templates', []);
                            $template_data = !empty($templates) ? reset($templates) : null;
                            error_log('PDF Builder: Using first available template: ' . ($template_data ? 'found' : 'not found'));
                        }
                    } else {
                        $template_data = null;
                    }
                }
            } else {
                error_log('PDF Builder: Auto-detecting template based on order status');
                // Détecter automatiquement le template basé sur le statut de la commande
                if ($order) {
                    $order_status = $order->get_status();
                    $status_templates = get_option('pdf_builder_order_status_templates', []);
                    $status_key = 'wc-' . $order_status;

                    if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                        $mapped_template_id = $status_templates[$status_key];
                        $templates = get_option('pdf_builder_templates', []);
                        $template_data = isset($templates[$mapped_template_id]) ? $templates[$mapped_template_id] : null;
                    } else {
                        $templates = get_option('pdf_builder_templates', []);
                        $template_data = !empty($templates) ? reset($templates) : null;
                    }
                } else {
                    $template_data = null;
                }
            }

            error_log('PDF Builder: Template data loaded: ' . ($template_data ? 'found' : 'not found'));
            if (!$template_data) {
                error_log('PDF Builder: No template data found - creating a default template');
                
                // Create a comprehensive default template that looks like a real invoice
                $default_template = [
                    'id' => 1,
                    'name' => 'Facture Standard',
                    'canvasWidth' => 595,
                    'canvasHeight' => 842,
                    'pages' => [
                        [
                            'elements' => [
                                // Header - Company Info
                                [
                                    'id' => 'company_name',
                                    'type' => 'text',
                                    'content' => 'THREEAXE',
                                    'position' => ['x' => 50, 'y' => 30],
                                    'size' => ['width' => 200, 'height' => 25],
                                    'style' => [
                                        'fontSize' => '20px',
                                        'fontWeight' => 'bold',
                                        'color' => '#2563eb',
                                        'textAlign' => 'left'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'company_address',
                                    'type' => 'text',
                                    'content' => '123 Rue de la Tech\n75001 Paris\nFrance',
                                    'position' => ['x' => 50, 'y' => 60],
                                    'size' => ['width' => 200, 'height' => 40],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#666666',
                                        'lineHeight' => '1.4',
                                        'textAlign' => 'left'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'company_contact',
                                    'type' => 'text',
                                    'content' => 'Tél: +33 1 23 45 67 89\nEmail: contact@threeaxe.fr',
                                    'position' => ['x' => 50, 'y' => 105],
                                    'size' => ['width' => 200, 'height' => 30],
                                    'style' => [
                                        'fontSize' => '11px',
                                        'color' => '#666666',
                                        'lineHeight' => '1.3',
                                        'textAlign' => 'left'
                                    ],
                                    'visible' => true
                                ],
                                // Invoice Title
                                [
                                    'id' => 'invoice_title',
                                    'type' => 'text',
                                    'content' => 'FACTURE',
                                    'position' => ['x' => 350, 'y' => 30],
                                    'size' => ['width' => 200, 'height' => 30],
                                    'style' => [
                                        'fontSize' => '24px',
                                        'fontWeight' => 'bold',
                                        'color' => '#dc2626',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'invoice_number',
                                    'type' => 'text',
                                    'content' => 'N° {{order_number}}',
                                    'position' => ['x' => 350, 'y' => 65],
                                    'size' => ['width' => 200, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '14px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'invoice_date',
                                    'type' => 'text',
                                    'content' => 'Date: {{order_date}}',
                                    'position' => ['x' => 350, 'y' => 90],
                                    'size' => ['width' => 200, 'height' => 18],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#666666',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                // Bill To section
                                [
                                    'id' => 'bill_to_title',
                                    'type' => 'text',
                                    'content' => 'FACTURER À:',
                                    'position' => ['x' => 50, 'y' => 160],
                                    'size' => ['width' => 200, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'left'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'bill_to_name',
                                    'type' => 'text',
                                    'content' => '{{customer_name}}',
                                    'position' => ['x' => 50, 'y' => 185],
                                    'size' => ['width' => 200, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '14px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'left'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'bill_to_address',
                                    'type' => 'text',
                                    'content' => '{{billing_address}}',
                                    'position' => ['x' => 50, 'y' => 210],
                                    'size' => ['width' => 200, 'height' => 60],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#666666',
                                        'lineHeight' => '1.4',
                                        'textAlign' => 'left'
                                    ],
                                    'visible' => true
                                ],
                                // Products table header
                                [
                                    'id' => 'table_header_bg',
                                    'type' => 'rectangle',
                                    'position' => ['x' => 50, 'y' => 300],
                                    'size' => ['width' => 495, 'height' => 25],
                                    'style' => [
                                        'backgroundColor' => '#f8f9fa',
                                        'border' => '1px solid #dee2e6'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'product_header',
                                    'type' => 'text',
                                    'content' => 'PRODUIT',
                                    'position' => ['x' => 60, 'y' => 305],
                                    'size' => ['width' => 200, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'left'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'qty_header',
                                    'type' => 'text',
                                    'content' => 'QTÉ',
                                    'position' => ['x' => 350, 'y' => 305],
                                    'size' => ['width' => 50, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'center'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'price_header',
                                    'type' => 'text',
                                    'content' => 'PRIX',
                                    'position' => ['x' => 410, 'y' => 305],
                                    'size' => ['width' => 60, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'total_header',
                                    'type' => 'text',
                                    'content' => 'TOTAL',
                                    'position' => ['x' => 480, 'y' => 305],
                                    'size' => ['width' => 60, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                // Sample product row
                                [
                                    'id' => 'product_row_bg',
                                    'type' => 'rectangle',
                                    'position' => ['x' => 50, 'y' => 325],
                                    'size' => ['width' => 495, 'height' => 25],
                                    'style' => [
                                        'backgroundColor' => '#ffffff',
                                        'border' => '1px solid #dee2e6',
                                        'borderTop' => 'none'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'product_name',
                                    'type' => 'text',
                                    'content' => '{{product_name}}',
                                    'position' => ['x' => 60, 'y' => 330],
                                    'size' => ['width' => 200, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#000000',
                                        'textAlign' => 'left'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'product_qty',
                                    'type' => 'text',
                                    'content' => '{{product_qty}}',
                                    'position' => ['x' => 350, 'y' => 330],
                                    'size' => ['width' => 50, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#000000',
                                        'textAlign' => 'center'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'product_price',
                                    'type' => 'text',
                                    'content' => '{{product_price}}',
                                    'position' => ['x' => 410, 'y' => 330],
                                    'size' => ['width' => 60, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'product_total',
                                    'type' => 'text',
                                    'content' => '{{product_total}}',
                                    'position' => ['x' => 480, 'y' => 330],
                                    'size' => ['width' => 60, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                // Totals section
                                [
                                    'id' => 'subtotal_label',
                                    'type' => 'text',
                                    'content' => 'Sous-total:',
                                    'position' => ['x' => 350, 'y' => 380],
                                    'size' => ['width' => 100, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#666666',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'subtotal_amount',
                                    'type' => 'text',
                                    'content' => '{{subtotal}}',
                                    'position' => ['x' => 460, 'y' => 380],
                                    'size' => ['width' => 80, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'tax_label',
                                    'type' => 'text',
                                    'content' => 'TVA (20%):',
                                    'position' => ['x' => 350, 'y' => 400],
                                    'size' => ['width' => 100, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#666666',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'tax_amount',
                                    'type' => 'text',
                                    'content' => '{{tax_amount}}',
                                    'position' => ['x' => 460, 'y' => 400],
                                    'size' => ['width' => 80, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'total_label',
                                    'type' => 'text',
                                    'content' => 'TOTAL TTC:',
                                    'position' => ['x' => 350, 'y' => 425],
                                    'size' => ['width' => 100, 'height' => 25],
                                    'style' => [
                                        'fontSize' => '14px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'total_amount',
                                    'type' => 'text',
                                    'content' => '{{order_total}}',
                                    'position' => ['x' => 460, 'y' => 425],
                                    'size' => ['width' => 80, 'height' => 25],
                                    'style' => [
                                        'fontSize' => '14px',
                                        'fontWeight' => 'bold',
                                        'color' => '#000000',
                                        'textAlign' => 'right'
                                    ],
                                    'visible' => true
                                ],
                                // Footer
                                [
                                    'id' => 'footer_text',
                                    'type' => 'text',
                                    'content' => 'Merci pour votre confiance !',
                                    'position' => ['x' => 50, 'y' => 780],
                                    'size' => ['width' => 495, 'height' => 20],
                                    'style' => [
                                        'fontSize' => '12px',
                                        'color' => '#666666',
                                        'textAlign' => 'center',
                                        'fontStyle' => 'italic'
                                    ],
                                    'visible' => true
                                ],
                                [
                                    'id' => 'footer_line',
                                    'type' => 'line',
                                    'position' => ['x' => 50, 'y' => 770],
                                    'size' => ['width' => 495, 'height' => 1],
                                    'style' => [
                                        'borderTop' => '1px solid #dee2e6'
                                    ],
                                    'visible' => true
                                ]
                            ]
                        ]
                    ]
                ];
                
                // Save the default template
                $templates = [1 => $default_template];
                update_option('pdf_builder_templates', $templates);
                
                // Set it as the default for completed orders
                $status_templates = get_option('pdf_builder_order_status_templates', []);
                $status_templates['wc-completed'] = 1;
                update_option('pdf_builder_order_status_templates', $status_templates);
                
                $template_data = $default_template;
                error_log('PDF Builder: Default template created successfully');
            }

            error_log('PDF Builder: Extracting elements from template');
            // Extraire les éléments
            $elements = [];
            if (isset($template_data['pages']) && is_array($template_data['pages']) && !empty($template_data['pages'])) {
                $elements = $template_data['pages'][0]['elements'] ?? [];
            } elseif (isset($template_data['elements'])) {
                $elements = $template_data['elements'];
            }

            error_log('PDF Builder: Found ' . count($elements) . ' elements');
            $canvas_width = intval($template_data['canvasWidth'] ?? $template_data['canvas']['width'] ?? 595);
            $canvas_height = intval($template_data['canvasHeight'] ?? $template_data['canvas']['height'] ?? 842);

            error_log("PDF Builder: Canvas size: {$canvas_width}x{$canvas_height}");

            // Générer le HTML avec les vraies données de la commande
            error_log('PDF Builder: Generating preview HTML');
            $preview_html = $this->render_preview_canvas($elements, $order, $canvas_width, $canvas_height);

            error_log('PDF Builder: HTML generated, creating full HTML response');
            // Créer le HTML complet avec styles pour l'impression et téléchargement
            $full_html = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aperçu - Commande <?php echo $order && is_object($order) ? $order->get_order_number() : 'N/A'; ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        html, body {
            width: 100%;
            height: 100%;
        }
        body {
            background: #f5f5f5;
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
        }
        .preview-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .canvas-container {
            background: white;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            position: relative;
            margin: 20px auto;
            page-break-after: always;
        }
        .canvas-element {
            position: absolute;
            overflow: hidden;
            box-sizing: border-box;
        }
        .toolbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: white;
            border-bottom: 1px solid #ddd;
            padding: 10px 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1000;
            display: flex;
            gap: 10px;
        }
        .toolbar button {
            padding: 8px 16px;
            background: #0073aa;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 14px;
        }
        .toolbar button:hover {
            background: #005a87;
        }
        .content {
            margin-top: 60px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table td, table th {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
        }
        table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        img {
            max-width: 100%;
            height: auto;
        }
        @media print {
            .toolbar {
                display: none;
            }
            body {
                padding: 0;
                background: white;
            }
            .content {
                margin-top: 0;
            }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button onclick="window.print()">🖨️ Imprimer</button>
        <button onclick="downloadPDF()">⬇️ Télécharger PDF</button>
        <button onclick="window.close()">✕ Fermer</button>
    </div>
    <div class="content">
        <div class="preview-wrapper">
            <div class="canvas-container" style="width: {$canvas_width}px; height: auto; min-height: {$canvas_height}px;">
                {$preview_html}
            </div>
        </div>
    </div>
    <script>
        function downloadPDF() {
            const data = {
                action: 'pdf_builder_preview_download',
                nonce: '{$this->get_nonce()}',
                order_id: {$order_id},
                template_id: {$template_id}
            };
            
            fetch('{$this->get_ajax_url()}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success && result.data.url) {
                    const link = document.createElement('a');
                    link.href = result.data.url;
                    link.download = 'commande-{$order_id}.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                } else {
                    alert('Erreur: ' + (result.data || 'Impossible de télécharger le PDF'));
                }
            });
        }
    </script>
</body>
</html>
HTML;

            wp_send_json_success([
                'html' => $full_html,
                'order_id' => $order_id,
                'template_id' => $template_id
            ]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler pour télécharger le PDF depuis l'aperçu miroir
     */
    public function ajax_preview_download() {
        check_ajax_referer('pdf_builder_order_actions', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if (!$order_id) {
            wp_send_json_error('ID commande manquant');
        }

        try {
            $order = wc_get_order($order_id);
            if (!$order) {
                wp_send_json_error('Commande introuvable');
            }

            // Charger le template et générer le PDF
            // Pour l'instant, on retourne une URL HTML temporaire
            // TODO: Convertir en vrai PDF avec une bibliothèque
            $upload_dir = wp_upload_dir();
            $temp_dir = $upload_dir['basedir'] . '/pdf-builder-temp';
            
            if (!file_exists($temp_dir)) {
                wp_mkdir_p($temp_dir);
            }

            $filename = 'commande-' . $order_id . '-' . time() . '.pdf';
            $file_path = $temp_dir . '/' . $filename;
            
            // Pour l'instant, générer un fichier HTML
            $html = 'Commande #' . $order_id;
            file_put_contents($file_path, $html);

            $download_url = $upload_dir['baseurl'] . '/pdf-builder-temp/' . $filename;
            
            wp_send_json_success(['url' => $download_url]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler pour imprimer l'aperçu miroir
     */
    public function ajax_preview_print() {
        check_ajax_referer('pdf_builder_order_actions', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Permissions insuffisantes');
        }

        wp_send_json_success(['message' => 'Impression lancée']);
    }

    /**
     * Helper pour obtenir le nonce
     */
    private function get_nonce() {
        return wp_create_nonce('pdf_builder_order_actions');
    }

    /**
     * Helper pour obtenir l'URL AJAX
     */
    private function get_ajax_url() {
        return admin_url('admin-ajax.php');
    }

    /**
     * Récupère l'ID du template approprié pour une commande donnée
     */
    private function get_template_for_order($order) {
        if (!$order) {
            return null;
        }

        // Vérifier si un template spécifique est demandé via POST
        if (isset($_POST['template_id']) && !empty($_POST['template_id'])) {
            return intval($_POST['template_id']);
        }

        // Détecter automatiquement le template basé sur le statut de la commande
        $order_status = $order->get_status();
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            return $status_templates[$status_key];
        }

        // Template par défaut - prendre le premier template disponible
        $templates = get_option('pdf_builder_templates', []);
        if (!empty($templates)) {
            $first_template = reset($templates);
            return $first_template['id'] ?? null;
        }

        return null;
    }

    /**
     * Rend le HTML du canvas d'aperçu avec les vraies données de la commande
     */
    private function render_preview_canvas($elements, $order, $canvas_width, $canvas_height) {
        error_log('PDF Builder: render_preview_canvas called with ' . count($elements) . ' elements');
        $html = '';

        if (!is_array($elements) || empty($elements)) {
            error_log('PDF Builder: No elements to render');
            return $html;
        }

        error_log('PDF Builder: Processing elements loop');
        foreach ($elements as $index => $element) {
            // Vérifier les propriétés essentielles
            $x = floatval($element['position']['x'] ?? $element['x'] ?? 0);
            $y = floatval($element['position']['y'] ?? $element['y'] ?? 0);
            $width = floatval($element['position']['width'] ?? $element['size']['width'] ?? $element['width'] ?? 100);
            $height = floatval($element['position']['height'] ?? $element['size']['height'] ?? $element['height'] ?? 50);
            $type = $element['type'] ?? 'text';
            $visible = isset($element['visible']) ? (bool)$element['visible'] : true;

            if (!$visible) {
                continue;
            }

            // Style de base - positionnement
            $style = "position: absolute; left: {$x}px; top: {$y}px; width: {$width}px; height: {$height}px; ";

            // Ajouter TOUS les styles CSS via build_element_style
            $style = $this->build_element_style($element, $style);

            // Contenu de l'élément
            $content = $this->render_element_content($element, $order);

            // Classes CSS
            $classes = 'canvas-element';
            if (isset($element['className'])) {
                $classes .= ' ' . esc_attr($element['className']);
            }

            // Générer l'élément HTML
            $html .= "<div class=\"{$classes}\" style=\"{$style}\">{$content}</div>";
        }

        return $html;
    }

    /**
     * Construit le style CSS d'un élément
     */
    private function build_element_style($element, $base_style) {
        $style = $base_style;

        // Get styles from element
        $element_styles = $element['style'] ?? $element['styles'] ?? [];

        // Font properties
        if (isset($element_styles['fontSize'])) {
            $style .= "font-size: {$element_styles['fontSize']}; ";
        }
        if (isset($element_styles['fontWeight'])) {
            $style .= "font-weight: {$element_styles['fontWeight']}; ";
        }
        if (isset($element_styles['fontStyle'])) {
            $style .= "font-style: {$element_styles['fontStyle']}; ";
        }
        if (isset($element_styles['fontFamily'])) {
            $style .= "font-family: {$element_styles['fontFamily']}; ";
        }
        if (isset($element_styles['lineHeight'])) {
            $style .= "line-height: {$element_styles['lineHeight']}; ";
        }

        // Text properties
        if (isset($element_styles['color'])) {
            $style .= "color: {$element_styles['color']}; ";
        }
        if (isset($element_styles['textAlign'])) {
            $style .= "text-align: {$element_styles['textAlign']}; ";
        }
        if (isset($element_styles['textDecoration'])) {
            $style .= "text-decoration: {$element_styles['textDecoration']}; ";
        }
        if (isset($element_styles['textTransform'])) {
            $style .= "text-transform: {$element_styles['textTransform']}; ";
        }
        if (isset($element_styles['letterSpacing'])) {
            $style .= "letter-spacing: {$element_styles['letterSpacing']}; ";
        }
        if (isset($element_styles['wordSpacing'])) {
            $style .= "word-spacing: {$element_styles['wordSpacing']}; ";
        }

        // Background properties
        if (isset($element_styles['backgroundColor'])) {
            $style .= "background-color: {$element_styles['backgroundColor']}; ";
        }
        if (isset($element_styles['backgroundImage'])) {
            $style .= "background-image: url('{$element_styles['backgroundImage']}'); ";
        }
        if (isset($element_styles['backgroundSize'])) {
            $style .= "background-size: {$element_styles['backgroundSize']}; ";
        }
        if (isset($element_styles['backgroundPosition'])) {
            $style .= "background-position: {$element_styles['backgroundPosition']}; ";
        }
        if (isset($element_styles['backgroundRepeat'])) {
            $style .= "background-repeat: {$element_styles['backgroundRepeat']}; ";
        }

        // Border properties - comprehensive
        if (isset($element_styles['border'])) {
            $style .= "border: {$element_styles['border']}; ";
        }
        if (isset($element_styles['borderWidth'])) {
            $style .= "border-width: {$element_styles['borderWidth']}; ";
        }
        if (isset($element_styles['borderStyle'])) {
            $style .= "border-style: {$element_styles['borderStyle']}; ";
        }
        if (isset($element_styles['borderColor'])) {
            $style .= "border-color: {$element_styles['borderColor']}; ";
        }
        if (isset($element_styles['borderRadius'])) {
            $style .= "border-radius: {$element_styles['borderRadius']}; ";
        }

        // Individual border sides
        $border_sides = ['Top', 'Right', 'Bottom', 'Left'];
        foreach ($border_sides as $side) {
            $side_lower = strtolower($side);
            if (isset($element_styles['border' . $side])) {
                $style .= "border-{$side_lower}: {$element_styles['border' . $side]}; ";
            }
            if (isset($element_styles['border' . $side . 'Width'])) {
                $style .= "border-{$side_lower}-width: {$element_styles['border' . $side . 'Width']}; ";
            }
            if (isset($element_styles['border' . $side . 'Style'])) {
                $style .= "border-{$side_lower}-style: {$element_styles['border' . $side . 'Style']}; ";
            }
            if (isset($element_styles['border' . $side . 'Color'])) {
                $style .= "border-{$side_lower}-color: {$element_styles['border' . $side . 'Color']}; ";
            }
        }

        // Padding and margins - comprehensive
        $spacing_props = ['padding', 'margin'];
        foreach ($spacing_props as $prop) {
            if (isset($element_styles[$prop])) {
                $style .= "{$prop}: {$element_styles[$prop]}; ";
            }
            $directions = ['Top', 'Right', 'Bottom', 'Left'];
            foreach ($directions as $dir) {
                $dir_lower = strtolower($dir);
                if (isset($element_styles[$prop . $dir])) {
                    $style .= "{$prop}-{$dir_lower}: {$element_styles[$prop . $dir]}; ";
                }
            }
        }

        // Dimensions
        if (isset($element_styles['width'])) {
            $style .= "width: {$element_styles['width']}; ";
        }
        if (isset($element_styles['height'])) {
            $style .= "height: {$element_styles['height']}; ";
        }
        if (isset($element_styles['minWidth'])) {
            $style .= "min-width: {$element_styles['minWidth']}; ";
        }
        if (isset($element_styles['minHeight'])) {
            $style .= "min-height: {$element_styles['minHeight']}; ";
        }
        if (isset($element_styles['maxWidth'])) {
            $style .= "max-width: {$element_styles['maxWidth']}; ";
        }
        if (isset($element_styles['maxHeight'])) {
            $style .= "max-height: {$element_styles['maxHeight']}; ";
        }

        // Positioning
        if (isset($element_styles['position'])) {
            $style .= "position: {$element_styles['position']}; ";
        }
        if (isset($element_styles['top'])) {
            $style .= "top: {$element_styles['top']}; ";
        }
        if (isset($element_styles['left'])) {
            $style .= "left: {$element_styles['left']}; ";
        }
        if (isset($element_styles['right'])) {
            $style .= "right: {$element_styles['right']}; ";
        }
        if (isset($element_styles['bottom'])) {
            $style .= "bottom: {$element_styles['bottom']}; ";
        }

        // Display and visibility
        if (isset($element_styles['display'])) {
            $style .= "display: {$element_styles['display']}; ";
        }
        if (isset($element_styles['visibility'])) {
            $style .= "visibility: {$element_styles['visibility']}; ";
        }
        if (isset($element_styles['opacity'])) {
            $style .= "opacity: {$element_styles['opacity']}; ";
        }

        // Flexbox properties
        if (isset($element_styles['display']) && $element_styles['display'] === 'flex') {
            if (isset($element_styles['flexDirection'])) {
                $style .= "flex-direction: {$element_styles['flexDirection']}; ";
            }
            if (isset($element_styles['justifyContent'])) {
                $style .= "justify-content: {$element_styles['justifyContent']}; ";
            }
            if (isset($element_styles['alignItems'])) {
                $style .= "align-items: {$element_styles['alignItems']}; ";
            }
            if (isset($element_styles['flexWrap'])) {
                $style .= "flex-wrap: {$element_styles['flexWrap']}; ";
            }
        }

        // Box shadow
        if (isset($element_styles['boxShadow'])) {
            $style .= "box-shadow: {$element_styles['boxShadow']}; ";
        }

        // Z-index and overflow
        if (isset($element_styles['zIndex'])) {
            $style .= "z-index: {$element_styles['zIndex']}; ";
        }
        if (isset($element_styles['overflow'])) {
            $style .= "overflow: {$element_styles['overflow']}; ";
        }

        // Special handling for rectangle elements
        if (($element['type'] ?? '') === 'rectangle') {
            if (isset($element_styles['backgroundColor'])) {
                $style .= "background-color: {$element_styles['backgroundColor']}; ";
            }
            if (isset($element_styles['border'])) {
                $style .= "border: {$element_styles['border']}; ";
            }
        }

        // Special handling for line elements
        if (($element['type'] ?? '') === 'line') {
            if (isset($element_styles['borderTop'])) {
                $style .= "border-top: {$element_styles['borderTop']}; ";
            } else {
                $style .= "border-top: 1px solid #000000; ";
            }
            $style .= "height: 1px; ";
        }

        return trim($style);
    }

    /**
     * Rend le contenu d'un élément avec les données de la commande
     */
    private function render_element_content($element, $order) {
        $type = $element['type'] ?? 'text';
        $content = $element['content'] ?? '';

        // Remplacer les variables dynamiques avec les vraies données de la commande
        if ($order) {
            $content = $this->replace_order_variables($content, $order);
        }

        switch ($type) {
            case 'text':
                return esc_html($content);

            case 'textarea':
                return nl2br(esc_html($content));

            case 'image':
                $src = $element['src'] ?? $element['content'] ?? '';
                $alt = $element['alt'] ?? 'Image';
                $width = $element['width'] ?? $element['size']['width'] ?? 'auto';
                $height = $element['height'] ?? $element['size']['height'] ?? 'auto';

                // Handle different width/height formats
                if (is_numeric($width)) {
                    $width .= 'px';
                }
                if (is_numeric($height)) {
                    $height .= 'px';
                }

                if (!empty($src)) {
                    return "<img src=\"{$src}\" alt=\"{$alt}\" style=\"width: {$width}; height: {$height}; object-fit: cover; max-width: 100%; max-height: 100%;\">";
                }
                return '';

            case 'line':
                return ''; // Handled by CSS styling only

            case 'rectangle':
                return ''; // Just a colored/styled div, no content needed

            case 'html':
                // Allow basic HTML but sanitize it
                return wp_kses($content, [
                    'br' => [],
                    'strong' => [],
                    'b' => [],
                    'em' => [],
                    'i' => [],
                    'u' => [],
                    'span' => ['style' => []],
                    'div' => ['style' => []],
                    'p' => ['style' => []],
                    'table' => ['style' => []],
                    'tr' => ['style' => []],
                    'td' => ['style' => []],
                    'th' => ['style' => []],
                    'thead' => ['style' => []],
                    'tbody' => ['style' => []],
                ]);

            default:
                return esc_html($content);
        }
    }

    /**
     * Remplace les variables de commande dans le contenu
     */
    private function replace_order_variables($content, $order) {
        if (!$order) {
            return $content;
        }

        // Basic order information
        $replacements = [
            '{{order_number}}' => $order->get_order_number(),
            '{{order_date}}' => $order->get_date_created() ? $order->get_date_created()->format('d/m/Y') : '',
            '{{order_total}}' => $order->get_formatted_order_total(),
            '{{order_status}}' => wc_get_order_status_name($order->get_status()),
            '{{customer_name}}' => $order->get_formatted_billing_full_name(),
            '{{customer_email}}' => $order->get_billing_email() ?: '',
            '{{customer_phone}}' => $order->get_billing_phone() ?: '',
            '{{billing_address}}' => $order->get_formatted_billing_address() ?: '',
            '{{shipping_address}}' => $order->get_formatted_shipping_address() ?: '',
            '{{payment_method}}' => $order->get_payment_method_title(),
            '{{shipping_method}}' => $order->get_shipping_method(),
        ];

        // Financial information
        $subtotal = $order->get_subtotal();
        $total_tax = $order->get_total_tax();
        $shipping_total = $order->get_shipping_total();
        $discount_total = $order->get_discount_total();

        $replacements = array_merge($replacements, [
            '{{subtotal}}' => wc_price($subtotal),
            '{{tax_amount}}' => wc_price($total_tax),
            '{{shipping_amount}}' => wc_price($shipping_total),
            '{{discount_amount}}' => wc_price($discount_total),
            '{{total_excl_tax}}' => wc_price($order->get_total() - $total_tax),
        ]);

        // Handle product-specific variables (for single product display)
        $items = $order->get_items();
        if (!empty($items)) {
            $first_item = reset($items);

            $replacements = array_merge($replacements, [
                '{{product_name}}' => $first_item->get_name(),
                '{{product_qty}}' => $first_item->get_quantity(),
                '{{product_price}}' => wc_price($first_item->get_total() / $first_item->get_quantity()),
                '{{product_total}}' => wc_price($first_item->get_total()),
                '{{product_sku}}' => $first_item->get_product() ? $first_item->get_product()->get_sku() : '',
            ]);

            // For multiple products, create a summary
            if (count($items) > 1) {
                $product_summary = '';
                foreach ($items as $item) {
                    $product_summary .= $item->get_name() . ' (x' . $item->get_quantity() . ') - ' . wc_price($item->get_total()) . "\n";
                }
                $replacements['{{products_list}}'] = $product_summary;
            } else {
                $replacements['{{products_list}}'] = $first_item->get_name() . ' (x' . $first_item->get_quantity() . ') - ' . wc_price($first_item->get_total());
            }
        }

        // Handle billing address components
        $billing_address = [
            '{{billing_first_name}}' => $order->get_billing_first_name(),
            '{{billing_last_name}}' => $order->get_billing_last_name(),
            '{{billing_company}}' => $order->get_billing_company(),
            '{{billing_address_1}}' => $order->get_billing_address_1(),
            '{{billing_address_2}}' => $order->get_billing_address_2(),
            '{{billing_city}}' => $order->get_billing_city(),
            '{{billing_state}}' => $order->get_billing_state(),
            '{{billing_postcode}}' => $order->get_billing_postcode(),
            '{{billing_country}}' => $order->get_billing_country(),
        ];

        // Handle shipping address components
        $shipping_address = [
            '{{shipping_first_name}}' => $order->get_shipping_first_name(),
            '{{shipping_last_name}}' => $order->get_shipping_last_name(),
            '{{shipping_company}}' => $order->get_shipping_company(),
            '{{shipping_address_1}}' => $order->get_shipping_address_1(),
            '{{shipping_address_2}}' => $order->get_shipping_address_2(),
            '{{shipping_city}}' => $order->get_shipping_city(),
            '{{shipping_state}}' => $order->get_shipping_state(),
            '{{shipping_postcode}}' => $order->get_shipping_postcode(),
            '{{shipping_country}}' => $order->get_shipping_country(),
        ];

        $replacements = array_merge($replacements, $billing_address, $shipping_address);

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }
}

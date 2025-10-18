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
        // Enregistrer les hooks AJAX via l'action plugins_loaded pour s'assurer qu'ils sont disponibles tôt
        add_action('plugins_loaded', [$this, 'register_ajax_hooks']);
    }

    /**
     * Enregistrer les hooks AJAX
     */
    public function register_ajax_hooks() {
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf'], 1);
        add_action('wp_ajax_pdf_builder_save_order_canvas', [$this, 'ajax_save_order_canvas'], 1);
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
            <!-- Template Section -->
            <div class="pdf-template-section">
                <div class="pdf-template-title">
                    Template sélectionné
                </div>

                <div class="pdf-template-display">
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

        <!-- 🎨 MODALE D'APERÇU PDF -->
        <div id="woo-pdf-preview-modal" class="woo-pdf-preview-modal" style="display: none;">
            <div class="woo-pdf-preview-modal-overlay"></div>
            <div class="woo-pdf-preview-modal-container">
                <div class="woo-pdf-preview-modal-header">
                    <h3>Aperçu PDF - Commande #<?php echo intval($order_id); ?></h3>
                    <button class="woo-pdf-preview-modal-close" title="Fermer">
                        <span>✕</span>
                    </button>
                </div>
                <div class="woo-pdf-preview-modal-body">
                    <iframe id="woo-pdf-preview-iframe" 
                            style="width: 100%; height: 100%; border: none; border-radius: 4px;"
                            title="Aperçu PDF"></iframe>
                    <div class="woo-pdf-preview-loading" style="display: none; text-align: center; padding: 40px;">
                        <div style="font-size: 3em; margin-bottom: 20px;">📄</div>
                        <p>Chargement de l'aperçu...</p>
                        <div class="woo-pdf-preview-spinner"></div>
                    </div>
                </div>
                <div class="woo-pdf-preview-modal-footer">
                    <button class="woo-pdf-preview-download-btn" title="Télécharger">
                        💾 Télécharger
                    </button>
                    <button class="woo-pdf-preview-modal-close-btn">
                        Fermer
                    </button>
                </div>
            </div>
        </div>

        <!-- 🎨 STYLES MODALE -->
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
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(2px);
            }

            .woo-pdf-preview-modal-container {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border-radius: 8px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
                width: 90%;
                max-width: 900px;
                height: 80vh;
                display: flex;
                flex-direction: column;
                animation: wooSlideIn 0.3s ease-out;
            }

            @keyframes wooSlideIn {
                from {
                    opacity: 0;
                    transform: translate(-50%, -48%);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, -50%);
                }
            }

            .woo-pdf-preview-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px;
                border-bottom: 1px solid #e2e8f0;
                background: #f8fafc;
                border-radius: 8px 8px 0 0;
            }

            .woo-pdf-preview-modal-header h3 {
                margin: 0;
                font-size: 18px;
                font-weight: 600;
                color: #1e293b;
            }

            .woo-pdf-preview-modal-close {
                background: none;
                border: none;
                cursor: pointer;
                font-size: 24px;
                color: #64748b;
                padding: 0;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 4px;
                transition: all 0.2s;
            }

            .woo-pdf-preview-modal-close:hover {
                background: #e2e8f0;
                color: #1e293b;
            }

            .woo-pdf-preview-modal-body {
                flex: 1;
                overflow: auto;
                padding: 20px;
                background: #ffffff;
            }

            .woo-pdf-preview-modal-footer {
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                padding: 15px 20px;
                border-top: 1px solid #e2e8f0;
                background: #f8fafc;
                border-radius: 0 0 8px 8px;
            }

            .woo-pdf-preview-download-btn,
            .woo-pdf-preview-modal-close-btn {
                padding: 8px 16px;
                border: 1px solid #d1d5db;
                border-radius: 4px;
                background: white;
                color: #1f2937;
                cursor: pointer;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s;
            }

            .woo-pdf-preview-download-btn:hover {
                background: #f3f4f6;
                border-color: #9ca3af;
            }

            .woo-pdf-preview-modal-close-btn:hover {
                background: #f3f4f6;
            }

            .woo-pdf-preview-spinner {
                display: inline-block;
                width: 40px;
                height: 40px;
                border: 4px solid #f3f3f3;
                border-top: 4px solid #2563eb;
                border-radius: 50%;
                animation: wooSpin 1s linear infinite;
                margin-top: 20px;
            }

            @keyframes wooSpin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }

            @media (max-width: 768px) {
                .woo-pdf-preview-modal-container {
                    width: 95%;
                    height: 90vh;
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
            var ajaxUrl = "<?php echo esc_js(admin_url('admin-ajax.php')); ?>";
            var nonce = "<?php echo esc_js(wp_create_nonce('pdf_builder_order_actions')); ?>";

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
                    action: 'pdf_builder_generate_order_pdf',
                    order_id: orderId,
                    template_id: templateId,
                    nonce: nonce.substring(0, 10) + '...'
                });

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
                
                console.log("MetaBoxes.js - Aperçu PDF clicked");
                
                // Afficher la modale avec loading
                var $modal = $('#woo-pdf-preview-modal');
                var $loading = $modal.find('.woo-pdf-preview-loading');
                var $iframe = $modal.find('#woo-pdf-preview-iframe');
                
                $modal.show();
                $loading.show();
                $iframe.hide();

                showStatus("Génération de l'aperçu en cours...", "loading");

                // Effectuer l'AJAX pour générer l'aperçu
                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    dataType: "json",
                    timeout: 30000,
                    data: {
                        action: "pdf_builder_unified_preview",
                        order_id: orderId,
                        template_id: templateId > 0 ? templateId : null,
                        nonce: nonce
                    },
                    success: function(response) {
                        console.log('MetaBoxes.js - Aperçu success:', response);

                        if (response.success && response.data && response.data.url) {
                            // Charger le PDF dans l'iframe
                            $iframe.attr('src', response.data.url);
                            
                            // Stocker l'URL pour le téléchargement
                            $modal.data('pdf-url', response.data.url);
                            
                            // Masquer le loading et afficher l'iframe
                            setTimeout(function() {
                                $loading.hide();
                                $iframe.show();
                            }, 500);
                            
                            showStatus("Aperçu généré avec succès", "success");
                        } else {
                            $loading.hide();
                            $modal.find('.woo-pdf-preview-modal-body').html(
                                "<div style='text-align: center; padding: 40px; color: #dc3545;'><p>Erreur: " + (response.data || 'Erreur inconnue') + "</p></div>"
                            );
                            showStatus("Erreur lors de l'aperçu", "error");
                        }
                    },
                    error: function() {
                        $loading.hide();
                        $modal.find('.woo-pdf-preview-modal-body').html(
                            "<div style='text-align: center; padding: 40px; color: #dc3545;'><p>Erreur AJAX lors de l'aperçu</p></div>"
                        );
                        showStatus("Erreur AJAX", "error");
                    }
                });
            });

            // Gérer la fermeture de la modale
            $('#woo-pdf-preview-modal .woo-pdf-preview-modal-close,
               #woo-pdf-preview-modal .woo-pdf-preview-modal-close-btn,
               #woo-pdf-preview-modal .woo-pdf-preview-modal-overlay').on('click', function(e) {
                if ($(this).hasClass('woo-pdf-preview-modal-overlay') || $(this).closest('.woo-pdf-preview-modal-header, .woo-pdf-preview-modal-footer').length) {
                    e.preventDefault();
                    $('#woo-pdf-preview-modal').hide();
                }
            });

            // Télécharger le PDF depuis la modale
            $('#woo-pdf-preview-modal .woo-pdf-preview-download-btn').on('click', function(e) {
                e.preventDefault();
                var $modal = $('#woo-pdf-preview-modal');
                var pdfUrl = $modal.data('pdf-url');
                
                if (pdfUrl) {
                    var link = document.createElement('a');
                    link.href = pdfUrl;
                    link.download = 'apercu-commande-' + orderId + '.pdf';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
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


        // Définir les constantes TCPDF nécessaires AVANT de charger la bibliothèque
        $this->define_tcpdf_constants();

        // Définir K_TCPDF_VERSION si pas déjà défini
        if (!defined('K_TCPDF_VERSION')) {
            define('K_TCPDF_VERSION', '6.6.2');
        }


        // S'assurer que TCPDF est chargé après la définition des constantes
        if (!class_exists('TCPDF')) {

            // Essayer de charger TCPDF depuis les chemins possibles
            $tcpdf_paths = [
                plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'lib/tcpdf/tcpdf_autoload.php',
                plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'lib/tcpdf/tcpdf.php',
                plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'vendor/tecnickcom/tcpdf/tcpdf.php'
            ];

            $tcpdf_loaded = false;
            foreach ($tcpdf_paths as $path) {
                if (file_exists($path)) {

                    // Vérifier les permissions du fichier
                    if (!is_readable($path)) {
                        continue;
                    }

                    // Vérifier les chemins des constantes TCPDF avant le chargement
                    $this->check_tcpdf_paths();

                    try {
                        $start_time = microtime(true);
                        require_once $path;
                        $end_time = microtime(true);
                        $load_time = round(($end_time - $start_time) * 1000, 2);

                        if (class_exists('TCPDF')) {
                            $tcpdf_loaded = true;
                            break;
                        } else {
                        }
                    } catch (Exception $e) {
                    } catch (Error $e) {
                    }
                } else {
                }
            }

            if (!$tcpdf_loaded) {
                wp_send_json_error('Impossible de charger TCPDF');
            }
        } else {
        }

        try {
            // Générer le PDF
            $result = $this->main->generate_order_pdf($order_id, $template_id);

            if (is_wp_error($result)) {
                wp_send_json_error($result->get_error_message());
            }

            wp_send_json_success(['url' => $result]);

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
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
            $preview_type = isset($_POST['preview_type']) ? $_POST['preview_type'] : 'pdf'; // 'pdf' ou 'html'



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
                    // Générer l'aperçu HTML avec les éléments du Canvas
                    $result = $generator->render_html_preview($decoded_elements, $order_id ?: 0);
                } else {
                    // Générer l'aperçu PDF avec les éléments du Canvas
                    $result = $generator->generate($decoded_elements, ['title' => 'Aperçu Template - ' . date('Y-m-d H:i:s')]);
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
                    // Générer l'aperçu HTML avec les éléments du template
                    $result = $generator->render_html_preview($decoded_elements, $order_id ?: 0);
                } else {
                    // Générer l'aperçu PDF avec les éléments du template
                    $result = $generator->generate($decoded_elements, ['title' => 'Aperçu Template - ' . date('Y-m-d H:i:s')]);
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
     * Détermine le template approprié pour une commande
     */
    private function get_template_for_order($order) {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $order_status = $order->get_status();

        // Vérifier s'il y a un mapping spécifique pour ce statut de commande
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            $mapped_template = $wpdb->get_row($wpdb->prepare(
                "SELECT id, name FROM $table_templates WHERE id = %d",
                $status_templates[$status_key]
            ), ARRAY_A);

            if ($mapped_template) {
                return $mapped_template['id'];
            }
        }

        // Logique de détection automatique basée sur le statut
        $keywords = [];
        switch ($order_status) {
            case 'pending':
                $keywords = ['devis', 'quote', 'estimation'];
                break;
            case 'processing':
            case 'on-hold':
                $keywords = ['facture', 'invoice', 'commande'];
                break;
            case 'completed':
                $keywords = ['facture', 'invoice', 'reçu', 'receipt'];
                break;
            case 'cancelled':
            case 'refunded':
                $keywords = ['avoir', 'credit', 'refund'];
                break;
            case 'failed':
                $keywords = ['erreur', 'failed', 'échoué'];
                break;
            default:
                $keywords = ['facture', 'invoice'];
                break;
        }


        if (!empty($keywords)) {
            // Chercher un template par défaut dont le nom contient un mot-clé
            $placeholders = str_repeat('%s,', count($keywords) - 1) . '%s';
            $sql = $wpdb->prepare(
                "SELECT id, name FROM $table_templates WHERE is_default = 1 AND (" .
                implode(' OR ', array_fill(0, count($keywords), 'LOWER(name) LIKE LOWER(%s)')) .
                ") LIMIT 1",
                array_map(function($keyword) { return '%' . $keyword . '%'; }, $keywords)
            );
            $keyword_template = $wpdb->get_row($sql, ARRAY_A);

            if ($keyword_template) {
                return $keyword_template['id'];
            }
        }

        // Si aucun template spécifique trouvé, prendre n'importe quel template par défaut
        $default_template = $wpdb->get_row("SELECT id, name FROM $table_templates WHERE is_default = 1 LIMIT 1", ARRAY_A);
        if ($default_template) {
            return $default_template['id'];
        }

        // Si toujours pas de template, prendre le premier template disponible
        $any_template = $wpdb->get_row("SELECT id, name FROM $table_templates ORDER BY id LIMIT 1", ARRAY_A);
        if ($any_template) {
            return $any_template['id'];
        }

        return null;
    }

    /**
     * AJAX handler pour sauvegarder le canvas d'une commande
     */
    public function ajax_save_order_canvas() {
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

    /**
     * Sauvegarde le canvas d'une commande
     */
    private function save_order_canvas($order_id, $canvas_data, $template_id = null) {
        // Cette méthode peut être implémentée selon les besoins
        return true;
    }

    /**
     * Définit les constantes TCPDF nécessaires
     */
    private function define_tcpdf_constants() {

        // Utiliser des chemins absolus au lieu de chemins relatifs
        $plugin_dir = plugin_dir_path(dirname(dirname(dirname(__FILE__))));

        $constants = [
            'PDF_PAGE_ORIENTATION' => 'P',
            'PDF_UNIT' => 'mm',
            'PDF_PAGE_FORMAT' => 'A4',
            'K_PATH_FONTS' => $plugin_dir . 'lib/tcpdf/fonts/',
            'K_PATH_CACHE' => $plugin_dir . 'uploads/pdf-builder-cache/',
            'K_PATH_IMAGES' => $plugin_dir . 'lib/tcpdf/images/',
            'K_PATH_URL' => $plugin_dir . 'lib/tcpdf/'
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            } else {
            }
        }

    }

    /**
     * Vérifie que les chemins définis dans les constantes TCPDF sont accessibles
     */
    private function check_tcpdf_paths() {

        $paths_to_check = [
            'K_PATH_FONTS' => defined('K_PATH_FONTS') ? K_PATH_FONTS : null,
            'K_PATH_CACHE' => defined('K_PATH_CACHE') ? K_PATH_CACHE : null,
            'K_PATH_IMAGES' => defined('K_PATH_IMAGES') ? K_PATH_IMAGES : null,
            'K_PATH_URL' => defined('K_PATH_URL') ? K_PATH_URL : null
        ];

        foreach ($paths_to_check as $const_name => $path) {
            if ($path === null) {
                continue;
            }


            if (!file_exists($path)) {
                // Tenter de créer le répertoire s'il n'existe pas
                if (!mkdir($path, 0755, true)) {
                } else {
                }
            } elseif (!is_dir($path)) {
            } elseif (!is_readable($path)) {
            } elseif (!is_writable($path)) {
            } else {
            }
        }

    }
}


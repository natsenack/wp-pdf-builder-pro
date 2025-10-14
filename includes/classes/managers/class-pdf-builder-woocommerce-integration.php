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
        error_log('PDF BUILDER - Registering AJAX hooks in WooCommerce integration');
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf'], 1);
        add_action('wp_ajax_pdf_builder_pro_preview_order_pdf', [$this, 'ajax_preview_order_pdf'], 1);
        add_action('wp_ajax_pdf_builder_save_order_canvas', [$this, 'ajax_save_order_canvas'], 1);
        error_log('PDF BUILDER - AJAX hooks registered: pdf_builder_generate_order_pdf, pdf_builder_pro_preview_order_pdf, pdf_builder_save_order_canvas');

        // Ajouter un log global pour déboguer
        add_action('wp_ajax_pdf_builder_pro_preview_order_pdf', function() {
            error_log('PDF BUILDER - Global AJAX hook triggered for pdf_builder_pro_preview_order_pdf');
        }, 1);

        // Hook global pour toutes les actions AJAX
        add_action('wp_ajax_nopriv_pdf_builder_pro_preview_order_pdf', function() {
            error_log('PDF BUILDER - NOPRIV AJAX hook triggered for pdf_builder_pro_preview_order_pdf');
        }, 1);

        // Hook pour intercepter toutes les requêtes AJAX
        add_action('admin_init', function() {
            if (isset($_REQUEST['action']) && $_REQUEST['action'] === 'pdf_builder_pro_preview_order_pdf') {
                error_log('PDF BUILDER - Action pdf_builder_pro_preview_order_pdf detected in admin_init');
                if (has_action('wp_ajax_pdf_builder_pro_preview_order_pdf')) {
                    error_log('PDF BUILDER - Hook wp_ajax_pdf_builder_pro_preview_order_pdf exists');
                } else {
                    error_log('PDF BUILDER - Hook wp_ajax_pdf_builder_pro_preview_order_pdf does NOT exist');
                }
            }
        });
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
        /* Simple & Clean PDF Preview Styles */
        .pdf-preview-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            z-index: 100000;
            padding: 20px;
            box-sizing: border-box;
        }

        .pdf-preview-modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .pdf-preview-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 1200px;
            height: 90%;
            max-height: 800px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .pdf-preview-header {
            padding: 20px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .pdf-preview-title {
            font-size: 24px;
            font-weight: 600;
            margin: 0;
        }

        .pdf-preview-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin: 4px 0 0 0;
        }

        .pdf-preview-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }

        .pdf-preview-close:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .pdf-preview-body {
            flex: 1;
            display: flex;
            overflow: hidden;
        }

        .pdf-preview-iframe {
            flex: 1;
            border: none;
            background: #f5f5f5;
        }

        .pdf-preview-loading {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: white;
        }

        .pdf-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .pdf-loading-text {
            color: #666;
            font-size: 16px;
            text-align: center;
        }

        .pdf-error-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
            background: white;
            text-align: center;
            padding: 40px;
        }

        .pdf-error-icon {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .pdf-error-title {
            font-size: 24px;
            color: #dc3545;
            margin-bottom: 10px;
        }

        .pdf-error-message {
            color: #666;
            margin-bottom: 20px;
        }

        .pdf-retry-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.2s;
        }

        .pdf-retry-btn:hover {
            background: #5a67d8;
        }

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
        }

        .pdf-btn-preview {
            background: #28a745;
            color: white;
        }

        .pdf-btn-preview:hover {
            background: #218838;
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

        .pdf-btn-download:hover {
            background: #545b62;
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
                <button type="button" class="pdf-btn pdf-btn-preview" id="pdf-preview-btn">
                    <span>👁️</span>
                    Aperçu PDF
                </button>

                <button type="button" class="pdf-btn pdf-btn-generate" id="pdf-generate-btn">
                    <span>⚡</span>
                    Générer PDF
                </button>

                <button type="button" class="pdf-btn pdf-btn-download" id="pdf-download-btn" style="display: none;">
                    <span>⬇️</span>
                    Télécharger PDF
                </button>
            </div>

            <!-- Status Messages -->
            <div class="pdf-status" id="pdf-status"></div>
        </div>

        <!-- Simple PDF Preview Modal -->
        <div class="pdf-preview-modal" id="pdf-preview-modal">
            <div class="pdf-preview-content">
                <div class="pdf-preview-header">
                    <div>
                        <h2 class="pdf-preview-title">Aperçu PDF</h2>
                        <p class="pdf-preview-subtitle">
                            <?php echo esc_html($document_type_label); ?> • Commande #<?php echo esc_html($order->get_order_number()); ?>
                        </p>
                    </div>
                    <button class="pdf-preview-close" id="pdf-preview-close">&times;</button>
                </div>

                <div class="pdf-preview-body">
                    <div class="pdf-preview-loading" id="pdf-preview-loading">
                        <div class="pdf-spinner"></div>
                        <div class="pdf-loading-text">
                            <strong>Génération de l'aperçu en cours...</strong><br>
                            <small>Veuillez patienter</small>
                        </div>
                    </div>

                    <div class="pdf-error-state" id="pdf-preview-error" style="display: none;">
                        <div class="pdf-error-icon">⚠️</div>
                        <h3 class="pdf-error-title">Erreur de génération</h3>
                        <p class="pdf-error-message" id="pdf-error-message">Une erreur inattendue s'est produite.</p>
                        <button class="pdf-retry-btn" id="pdf-retry-btn">Réessayer</button>
                    </div>

                    <iframe class="pdf-preview-iframe" id="pdf-preview-iframe" style="display: none;"></iframe>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        // Simple & Robust PDF Preview JavaScript
        (function($) {
            // Configuration
            var orderId = <?php echo intval($order_id); ?>;
            var templateId = <?php echo $selected_template ? intval($selected_template['id']) : 0; ?>;
            var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var nonce = '<?php echo wp_create_nonce('pdf_builder_order_actions'); ?>';

            // Utility functions
            function showStatus(message, type) {
                var $status = $('#pdf-status');
                $status.removeClass('pdf-status-loading pdf-status-success pdf-status-error')
                       .addClass('pdf-status-' + type)
                       .html(message)
                       .show();

                if (type !== 'loading') {
                    setTimeout(function() {
                        $status.fadeOut();
                    }, 5000);
                }
            }

            function setButtonLoading($btn, loading) {
                if (loading) {
                    $btn.prop('disabled', true).css('opacity', '0.6');
                } else {
                    $btn.prop('disabled', false).css('opacity', '1');
                }
            }

            function showModal() {
                $('#pdf-preview-modal').addClass('show');
                $('body').addClass('pdf-modal-open');
            }

            function hideModal() {
                $('#pdf-preview-modal').removeClass('show');
                $('body').removeClass('pdf-modal-open');
            }

            function showLoading() {
                $('#pdf-preview-loading').show();
                $('#pdf-preview-iframe').hide();
                $('#pdf-preview-error').hide();
            }

            function showError(message) {
                $('#pdf-preview-loading').hide();
                $('#pdf-preview-iframe').hide();
                $('#pdf-preview-error').show();
                $('#pdf-error-message').text(message);
            }

            function showPreview(pdfUrl) {
                $('#pdf-preview-loading').hide();
                $('#pdf-preview-error').hide();
                $('#pdf-preview-iframe').attr('src', pdfUrl).show();
            }

            // Event handlers
            $('#pdf-preview-btn').on('click', function() {
                console.log('PDF BUILDER - Preview button clicked');
                showModal();
                showLoading();
                showStatus('Génération de l\'aperçu...', 'loading');
                setButtonLoading($(this), true);

                console.log('PDF BUILDER - Sending AJAX request for preview:', {
                    action: 'pdf_builder_pro_preview_order_pdf',
                    order_id: orderId,
                    template_id: templateId,
                    nonce: nonce.substring(0, 10) + '...'
                });

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_pro_preview_order_pdf',
                        order_id: orderId,
                        template_id: templateId,
                        nonce: nonce
                    },
                    success: function(response) {
                        console.log('PDF BUILDER - AJAX success response:', response);
                        if (response.success && response.data && response.data.url) {
                            showPreview(response.data.url);
                            showStatus('Aperçu généré avec succès', 'success');
                        } else {
                            var errorMsg = response.data || 'Erreur inconnue lors de la génération';
                            console.error('PDF BUILDER - Preview error:', errorMsg);
                            showError(errorMsg);
                            showStatus('Erreur lors de l\'aperçu', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF BUILDER - AJAX error:', xhr, status, error);
                        var errorMsg = 'Erreur de connexion: ' + error;
                        if (xhr.responseJSON && xhr.responseJSON.data) {
                            errorMsg = xhr.responseJSON.data;
                        }
                        showError(errorMsg);
                        showStatus('Erreur AJAX: ' + error, 'error');
                    },
                    complete: function() {
                        setButtonLoading($('#pdf-preview-btn'), false);
                    }
                });
            });

            $('#pdf-generate-btn').on('click', function() {
                console.log('PDF BUILDER - Generate button clicked');
                showStatus('Génération du PDF...', 'loading');
                setButtonLoading($(this), true);

                console.log('PDF BUILDER - Sending AJAX request for generation:', {
                    action: 'pdf_builder_generate_order_pdf',
                    order_id: orderId,
                    template_id: templateId,
                    nonce: nonce.substring(0, 10) + '...'
                });

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_generate_order_pdf',
                        order_id: orderId,
                        template_id: templateId,
                        nonce: nonce
                    },
                    success: function(response) {
                        console.log('PDF BUILDER - Generate AJAX success response:', response);
                        if (response.success && response.data && response.data.url) {
                            $('#pdf-download-btn').attr('href', response.data.url).show();
                            showStatus('PDF généré avec succès', 'success');

                            // Auto-download after a short delay
                            setTimeout(function() {
                                window.open(response.data.url, '_blank');
                            }, 500);
                        } else {
                            var errorMsg = response.data || 'Erreur lors de la génération';
                            showStatus(errorMsg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        showStatus('Erreur AJAX: ' + error, 'error');
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
                    window.open(url, '_blank');
                }
            });

            // Modal controls
            $('#pdf-preview-close').on('click', hideModal);

            $('#pdf-preview-modal').on('click', function(e) {
                if (e.target === this) {
                    hideModal();
                }
            });

            // Retry button
            $('#pdf-retry-btn').on('click', function() {
                $('#pdf-preview-btn').trigger('click');
            });

            // Keyboard shortcuts
            $(document).on('keydown.pdf-preview', function(e) {
                if (!$('#pdf-preview-modal').hasClass('show')) return;

                if (e.keyCode === 27) { // Escape
                    hideModal();
                }
            });

        })(jQuery);
        </script>
        <?php
    }

    /**
     * AJAX handler pour générer l'aperçu PDF d'une commande
     */
    public function ajax_preview_order_pdf() {
        error_log('🚨 PDF BUILDER - ajax_preview_order_pdf METHOD CALLED - STARTING EXECUTION');

        // Vérifier les permissions
        error_log('🔐 PDF BUILDER - ajax_preview_order_pdf: Vérification permissions');
        if (!current_user_can('manage_woocommerce')) {
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Permissions insuffisantes');
            wp_send_json_error('Permissions insuffisantes');
        }
        error_log('✅ PDF BUILDER - ajax_preview_order_pdf: Permissions OK');

        // Vérification de sécurité
        error_log('🔒 PDF BUILDER - ajax_preview_order_pdf: Vérification nonce');
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Nonce invalide');
            wp_send_json_error('Sécurité: Nonce invalide');
        }
        error_log('✅ PDF BUILDER - ajax_preview_order_pdf: Nonce OK');

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        error_log('🟡 PDF BUILDER - ajax_preview_order_pdf: order_id=' . $order_id . ', template_id=' . $template_id);

        if (!$order_id) {
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: ID commande manquant');
            wp_send_json_error('ID commande manquant');
        }
        error_log('✅ PDF BUILDER - ajax_preview_order_pdf: order_id valide');

        // Vérifier que WooCommerce est actif
        error_log('🛒 PDF BUILDER - ajax_preview_order_pdf: Vérification WooCommerce');
        if (!class_exists('WooCommerce')) {
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: WooCommerce non actif');
            wp_send_json_error('WooCommerce n\'est pas installé ou activé');
        }
        error_log('✅ PDF BUILDER - ajax_preview_order_pdf: WooCommerce OK');

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Commande non trouvée: ' . $order_id);
            wp_send_json_error('Commande non trouvée');
        }

        error_log('✅ PDF BUILDER - ajax_preview_order_pdf: Commande trouvée');

        // S'assurer que TCPDF est chargé avant la génération
        if (!class_exists('TCPDF')) {
            error_log('🟡 PDF BUILDER - ajax_preview_order_pdf: TCPDF non chargé, définition des constantes d\'abord');

            // Définir les constantes TCPDF AVANT de charger la bibliothèque
            $this->define_tcpdf_constants();

            error_log('🟡 PDF BUILDER - ajax_preview_order_pdf: Constantes TCPDF définies, tentative de chargement TCPDF');

            // Essayer de charger TCPDF depuis les chemins possibles
            $tcpdf_paths = [
                plugin_dir_path(__FILE__) . '../../../lib/tcpdf/tcpdf.php',
                plugin_dir_path(__FILE__) . '../../../lib/tcpdf/tcpdf_autoload.php',
                plugin_dir_path(__FILE__) . '../../../vendor/tecnickcom/tcpdf/tcpdf.php'
            ];

            $tcpdf_loaded = false;
            foreach ($tcpdf_paths as $path) {
                error_log('🔍 PDF BUILDER - ajax_preview_order_pdf: Test chemin TCPDF: ' . $path);
                if (file_exists($path)) {
                    error_log('✅ PDF BUILDER - ajax_preview_order_pdf: Fichier existe: ' . $path);

                    // Vérifier les permissions du fichier
                    if (!is_readable($path)) {
                        error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Fichier TCPDF non lisible: ' . $path);
                        continue;
                    }

                    // Vérifier les chemins des constantes TCPDF avant le chargement
                    error_log('🔧 PDF BUILDER - ajax_preview_order_pdf: Vérification des chemins TCPDF constants');
                    $this->check_tcpdf_paths();

                    try {
                        error_log('📦 PDF BUILDER - ajax_preview_order_pdf: Tentative require_once de: ' . $path);
                        $start_time = microtime(true);
                        require_once $path;
                        $end_time = microtime(true);
                        $load_time = round(($end_time - $start_time) * 1000, 2);
                        error_log('📦 PDF BUILDER - ajax_preview_order_pdf: require_once réussi en ' . $load_time . 'ms pour: ' . $path);

                        if (class_exists('TCPDF')) {
                            error_log('✅ PDF BUILDER - ajax_preview_order_pdf: TCPDF chargé avec succès depuis: ' . $path);
                            $tcpdf_loaded = true;
                            break;
                        } else {
                            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Échec chargement TCPDF depuis: ' . $path . ' (classe TCPDF non trouvée)');
                        }
                    } catch (Exception $e) {
                        error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Exception lors du require_once: ' . $e->getMessage());
                        error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Stack trace: ' . $e->getTraceAsString());
                    } catch (Error $e) {
                        error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Error fatale lors du require_once: ' . $e->getMessage());
                        error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Stack trace: ' . $e->getTraceAsString());
                    }
                } else {
                    error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Fichier n\'existe pas: ' . $path);
                }
            }

            if (!$tcpdf_loaded) {
                error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Impossible de charger TCPDF depuis tous les chemins');
                wp_send_json_error('Impossible de charger TCPDF');
            }
        } else {
            error_log('✅ PDF BUILDER - ajax_preview_order_pdf: TCPDF déjà chargé');
        }

        // Les constantes TCPDF sont déjà définies plus haut

        try {
            error_log('🟡 PDF BUILDER - ajax_preview_order_pdf: Génération PDF en cours - appel de main->generate_order_pdf');
            // Générer l'aperçu PDF
            $result = $this->main->generate_order_pdf($order_id, $template_id, true);
            error_log('✅ PDF BUILDER - ajax_preview_order_pdf: Retour de main->generate_order_pdf: ' . (is_wp_error($result) ? 'WP_Error' : 'string'));

            if (is_wp_error($result)) {
                error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Erreur génération PDF: ' . $result->get_error_message());
                wp_send_json_error($result->get_error_message());
            }

            error_log('✅ PDF BUILDER - ajax_preview_order_pdf: PDF généré avec succès: ' . $result);
            wp_send_json_success([
                'url' => $result,
                'width' => 595, // A4 width in points
                'height' => 842 // A4 height in points
            ]);

        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Exception capturée: ' . $e->getMessage());
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur inconnue lors de la génération: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Error fatale capturée: ' . $e->getMessage());
            error_log('❌ PDF BUILDER - ajax_preview_order_pdf: Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur fatale lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * AJAX handler pour générer le PDF d'une commande
     */
    public function ajax_generate_order_pdf() {
        error_log('🚨 PDF BUILDER - ajax_generate_order_pdf STARTED');

        // Vérifier les permissions
        if (!current_user_can('manage_woocommerce')) {
            error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Permissions insuffisantes');
            wp_send_json_error('Permissions insuffisantes');
        }

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Nonce invalide');
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        error_log('🟡 PDF BUILDER - ajax_generate_order_pdf: order_id=' . $order_id . ', template_id=' . $template_id);

        if (!$order_id) {
            error_log('❌ PDF BUILDER - ajax_generate_order_pdf: ID commande manquant');
            wp_send_json_error('ID commande manquant');
        }

        // Vérifier que WooCommerce est actif
        if (!class_exists('WooCommerce')) {
            error_log('❌ PDF BUILDER - ajax_generate_order_pdf: WooCommerce non actif');
            wp_send_json_error('WooCommerce n\'est pas installé ou activé');
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Commande non trouvée: ' . $order_id);
            wp_send_json_error('Commande non trouvée');
        }

        error_log('✅ PDF BUILDER - ajax_generate_order_pdf: Commande trouvée');

        // Définir les constantes TCPDF nécessaires AVANT de charger la bibliothèque
        error_log('🟡 PDF BUILDER - ajax_generate_order_pdf: Définition des constantes TCPDF avant chargement');
        $this->define_tcpdf_constants();
        error_log('✅ PDF BUILDER - ajax_generate_order_pdf: Constantes TCPDF définies');

        // S'assurer que TCPDF est chargé après la définition des constantes
        if (!class_exists('TCPDF')) {
            error_log('🟡 PDF BUILDER - ajax_generate_order_pdf: TCPDF non chargé, tentative de chargement');

            // Essayer de charger TCPDF depuis les chemins possibles
            $tcpdf_paths = [
                plugin_dir_path(__FILE__) . '../../../lib/tcpdf/tcpdf.php',
                plugin_dir_path(__FILE__) . '../../../lib/tcpdf/tcpdf_autoload.php',
                plugin_dir_path(__FILE__) . '../../../vendor/tecnickcom/tcpdf/tcpdf.php'
            ];

            $tcpdf_loaded = false;
            foreach ($tcpdf_paths as $path) {
                error_log('🔍 PDF BUILDER - ajax_generate_order_pdf: Test chemin TCPDF: ' . $path);
                if (file_exists($path)) {
                    error_log('✅ PDF BUILDER - ajax_generate_order_pdf: Fichier existe: ' . $path);

                    // Vérifier les permissions du fichier
                    if (!is_readable($path)) {
                        error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Fichier TCPDF non lisible: ' . $path);
                        continue;
                    }

                    // Vérifier les chemins des constantes TCPDF avant le chargement
                    error_log('🔧 PDF BUILDER - ajax_generate_order_pdf: Vérification des chemins TCPDF constants');
                    $this->check_tcpdf_paths();

                    try {
                        error_log('📦 PDF BUILDER - ajax_generate_order_pdf: Tentative require_once de: ' . $path);
                        $start_time = microtime(true);
                        require_once $path;
                        $end_time = microtime(true);
                        $load_time = round(($end_time - $start_time) * 1000, 2);
                        error_log('📦 PDF BUILDER - ajax_generate_order_pdf: require_once réussi en ' . $load_time . 'ms pour: ' . $path);

                        if (class_exists('TCPDF')) {
                            error_log('✅ PDF BUILDER - ajax_generate_order_pdf: TCPDF chargé avec succès depuis: ' . $path);
                            $tcpdf_loaded = true;
                            break;
                        } else {
                            error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Échec chargement TCPDF depuis: ' . $path . ' (classe TCPDF non trouvée)');
                        }
                    } catch (Exception $e) {
                        error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Exception lors du require_once: ' . $e->getMessage());
                        error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Stack trace: ' . $e->getTraceAsString());
                    } catch (Error $e) {
                        error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Error fatale lors du require_once: ' . $e->getMessage());
                        error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Stack trace: ' . $e->getTraceAsString());
                    }
                } else {
                    error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Fichier n\'existe pas: ' . $path);
                }
            }

            if (!$tcpdf_loaded) {
                error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Impossible de charger TCPDF depuis tous les chemins');
                wp_send_json_error('Impossible de charger TCPDF');
            }
        } else {
            error_log('✅ PDF BUILDER - ajax_generate_order_pdf: TCPDF déjà chargé');
        }

        try {
            error_log('🟡 PDF BUILDER - ajax_generate_order_pdf: Génération PDF en cours');
            // Générer le PDF
            $result = $this->main->generate_order_pdf($order_id, $template_id, false);

            if (is_wp_error($result)) {
                error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Erreur génération PDF: ' . $result->get_error_message());
                wp_send_json_error($result->get_error_message());
            }

            error_log('✅ PDF BUILDER - ajax_generate_order_pdf: PDF généré avec succès: ' . $result);
            wp_send_json_success(['url' => $result]);

        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - ajax_generate_order_pdf: Exception: ' . $e->getMessage());
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
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
        error_log('🟡 PDF BUILDER - define_tcpdf_constants: Début définition constantes');

        $plugin_dir = plugin_dir_path(__FILE__) . '../../';
        error_log('🟡 PDF BUILDER - define_tcpdf_constants: plugin_dir = ' . $plugin_dir);

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
            error_log('🟡 PDF BUILDER - define_tcpdf_constants: Définition ' . $name . ' = ' . $value);
            if (!defined($name)) {
                define($name, $value);
                error_log('✅ PDF BUILDER - define_tcpdf_constants: ' . $name . ' défini');
            } else {
                error_log('ℹ️ PDF BUILDER - define_tcpdf_constants: ' . $name . ' déjà défini (valeur: ' . constant($name) . ')');
            }
        }

        error_log('✅ PDF BUILDER - define_tcpdf_constants: Toutes les constantes traitées');
    }

    /**
     * Vérifie que les chemins définis dans les constantes TCPDF sont accessibles
     */
    private function check_tcpdf_paths() {
        error_log('🔧 PDF BUILDER - check_tcpdf_paths: Vérification des chemins TCPDF');

        $paths_to_check = [
            'K_PATH_FONTS' => defined('K_PATH_FONTS') ? K_PATH_FONTS : null,
            'K_PATH_CACHE' => defined('K_PATH_CACHE') ? K_PATH_CACHE : null,
            'K_PATH_IMAGES' => defined('K_PATH_IMAGES') ? K_PATH_IMAGES : null,
            'K_PATH_URL' => defined('K_PATH_URL') ? K_PATH_URL : null
        ];

        foreach ($paths_to_check as $const_name => $path) {
            if ($path === null) {
                error_log('❌ PDF BUILDER - check_tcpdf_paths: Constante ' . $const_name . ' non définie');
                continue;
            }

            error_log('🔍 PDF BUILDER - check_tcpdf_paths: Vérification ' . $const_name . ' = ' . $path);

            if (!file_exists($path)) {
                error_log('❌ PDF BUILDER - check_tcpdf_paths: Chemin n\'existe pas: ' . $path);
                // Tenter de créer le répertoire s'il n'existe pas
                if (!mkdir($path, 0755, true)) {
                    error_log('❌ PDF BUILDER - check_tcpdf_paths: Impossible de créer le répertoire: ' . $path);
                } else {
                    error_log('✅ PDF BUILDER - check_tcpdf_paths: Répertoire créé: ' . $path);
                }
            } elseif (!is_dir($path)) {
                error_log('❌ PDF BUILDER - check_tcpdf_paths: Chemin n\'est pas un répertoire: ' . $path);
            } elseif (!is_readable($path)) {
                error_log('❌ PDF BUILDER - check_tcpdf_paths: Répertoire non lisible: ' . $path);
            } elseif (!is_writable($path)) {
                error_log('⚠️ PDF BUILDER - check_tcpdf_paths: Répertoire non accessible en écriture: ' . $path . ' (peut causer des problèmes)');
            } else {
                error_log('✅ PDF BUILDER - check_tcpdf_paths: Chemin OK: ' . $path);
            }
        }

        error_log('✅ PDF BUILDER - check_tcpdf_paths: Vérification terminée');
    }
}
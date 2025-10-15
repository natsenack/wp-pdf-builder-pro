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
        error_log('PDF BUILDER - Registering AJAX hooks in WooCommerce integration');
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf'], 1);
        add_action('wp_ajax_pdf_builder_unified_preview', [$this, 'ajax_unified_preview'], 1);
        add_action('wp_ajax_pdf_builder_save_order_canvas', [$this, 'ajax_save_order_canvas'], 1);
        error_log('PDF BUILDER - AJAX hooks registered: pdf_builder_generate_order_pdf, pdf_builder_unified_preview, pdf_builder_save_order_canvas');
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

        .pdf-btn-preview {
            background: #28a745;
            color: white;
        }

        .pdf-btn-preview:hover {
            background: #218838;
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
                <button type="button" class="pdf-btn pdf-btn-preview" id="pdf-preview-btn">
                    <span>�</span>
                    Aperçu PDF final
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

            <!-- Info Section -->
            <div class="pdf-info-notice">
                <div class="pdf-info-icon">ℹ️</div>
                <div class="pdf-info-content">
                    <strong>Aperçu du template sauvegardé</strong><br>
                    <small>Cet aperçu utilise le dernier template enregistré en base de données, pas les modifications en cours dans l'éditeur.</small>
                </div>
            </div>

            <!-- Status Messages -->
            <div class="pdf-status" id="pdf-status"></div>
        </div>

        <script type="text/javascript">
        // Simple & Robust PDF JavaScript
        (function($) {
            console.log('🚀🚀 METABOXES.JS LOADED - WOO PDF INVOICE DEBUG 🚀🚀🚀');
            console.log('MetaBoxes.js jQuery ready - WooCommerce PDF Invoice metabox initializing');

            // Configuration
            var orderId = <?php echo intval($order_id); ?>;
            var templateId = <?php echo $selected_template ? intval($selected_template['id']) : 0; ?>;
            var ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            var nonce = '<?php echo wp_create_nonce('pdf_builder_order_actions'); ?>';

            console.log('MetaBoxes.js - Configuration loaded:', {
                orderId: orderId,
                templateId: templateId,
                ajaxUrl: ajaxUrl,
                nonceLength: nonce.length
            });

            // Utility functions
            function showStatus(message, type) {
                console.log('MetaBoxes.js - showStatus called:', message, type);
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
                console.log('MetaBoxes.js - setButtonLoading:', loading ? 'loading' : 'not loading');
                if (loading) {
                    $btn.prop('disabled', true).css('opacity', '0.6');
                } else {
                    $btn.prop('disabled', false).css('opacity', '1');
                }
            }

            // Fonction pour ouvrir la modale PDF
            function openPdfModal(pdfUrl) {
                console.log('MetaBoxes.js - openPdfModal called with URL:', pdfUrl);

                // Créer la modale si elle n'existe pas
                if (!$('#pdf-preview-modal').length) {
                    $('body').append(`
                        <div id="pdf-preview-modal" style="
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0,0,0,0.8);
                            z-index: 999999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            padding: 20px;
                            box-sizing: border-box;
                        ">
                            <div style="
                                background: white;
                                border-radius: 8px;
                                width: 100%;
                                height: 100%;
                                max-width: 1200px;
                                max-height: 800px;
                                position: relative;
                                display: flex;
                                flex-direction: column;
                                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
                                transform: scale(0.95);
                                transition: transform 0.2s ease-out;
                            ">
                                <div style="
                                    padding: 15px 20px;
                                    border-bottom: 1px solid #dee2e6;
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    background: #f8f9fa;
                                    border-radius: 8px 8px 0 0;
                                ">
                                    <h3 style="margin: 0; color: #495057; font-size: 18px;">
                                        👁️ Aperçu PDF - Commande #${orderId}
                                    </h3>
                                    <button id="pdf-modal-close" style="
                                        background: #dc3545;
                                        color: white;
                                        border: none;
                                        border-radius: 4px;
                                        padding: 8px 12px;
                                        cursor: pointer;
                                        font-size: 16px;
                                        line-height: 1;
                                    ">✕ Fermer</button>
                                </div>
                                <div style="
                                    flex: 1;
                                    padding: 0;
                                    overflow: hidden;
                                ">
                                    <iframe id="pdf-preview-iframe" style="
                                        width: 100%;
                                        height: 100%;
                                        border: none;
                                        border-radius: 0 0 8px 8px;
                                    "></iframe>
                                </div>
                            </div>
                        </div>
                    `);

                    // Gestionnaire pour fermer la modale
                    $(document).on('click', '#pdf-modal-close', function() {
                        closePdfModal();
                    });

                    // Fermer en cliquant sur le fond
                    $(document).on('click', '#pdf-preview-modal', function(e) {
                        if (e.target === this) {
                            closePdfModal();
                        }
                    });

                    // Fermer avec Échap
                    $(document).on('keydown', function(e) {
                        if (e.keyCode === 27) { // Échap
                            closePdfModal();
                        }
                    });
                }

                // Fonction pour fermer la modale
                function closePdfModal() {
                    $('#pdf-preview-modal > div').css('transform', 'scale(0.95)');
                    setTimeout(function() {
                        $('#pdf-preview-modal').fadeOut(function() {
                            $(this).css('display', 'none');
                        });
                        $('#pdf-preview-iframe').attr('src', '');
                    }, 200);
                }

                // Ouvrir la modale et charger le PDF
                $('#pdf-preview-iframe').attr('src', pdfUrl);
                $('#pdf-preview-modal').css('display', 'flex').hide().fadeIn(function() {
                    // Animation d'ouverture
                    $('#pdf-preview-modal > div').css('transform', 'scale(1)');
                });
            }

            // Event handlers
            $('#pdf-preview-btn').on('click', function() {
                console.log('PDF BUILDER - Preview button clicked');
                console.log('MetaBoxes.js - Preview button element:', this);
                console.log('MetaBoxes.js - Order ID for preview:', orderId);

                showStatus('Génération de l\'aperçu...', 'loading');
                setButtonLoading($(this), true);

                var ajaxData = {
                    action: 'pdf_builder_unified_preview',
                    order_id: orderId,
                    template_id: templateId,
                    nonce: nonce
                };

                console.log('PDF BUILDER - Sending AJAX request for preview:', {
                    action: ajaxData.action,
                    order_id: ajaxData.order_id,
                    nonce: ajaxData.nonce.substring(0, 10) + '...',
                    ajaxUrl: ajaxUrl
                });

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    data: ajaxData,
                    success: function(response) {
                        console.log('PDF BUILDER - Preview AJAX success response:', response);
                        console.log('MetaBoxes.js - Full response object:', JSON.stringify(response, null, 2));

                        if (response.success && response.data && response.data.url) {
                            console.log('MetaBoxes.js - Opening preview URL in modal:', response.data.url);
                            // Ouvrir l'aperçu dans une modale
                            openPdfModal(response.data.url);
                            showStatus('Aperçu généré avec succès', 'success');
                        } else {
                            var errorMsg = response.data || 'Erreur lors de la génération de l\'aperçu';
                            console.log('MetaBoxes.js - Preview error:', errorMsg);
                            showStatus(errorMsg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log('MetaBoxes.js - Preview AJAX error:', {
                            xhr: xhr,
                            status: status,
                            error: error,
                            responseText: xhr.responseText
                        });
                        showStatus('Erreur AJAX: ' + error, 'error');
                    },
                    complete: function() {
                        console.log('MetaBoxes.js - Preview AJAX complete');
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

            console.log('MetaBoxes.js initialization complete - button handler attached');
        })(jQuery);
        </script>
        <?php
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

        // Définir K_TCPDF_VERSION si pas déjà défini
        if (!defined('K_TCPDF_VERSION')) {
            define('K_TCPDF_VERSION', '6.6.2');
        }

        error_log('✅ PDF BUILDER - ajax_generate_order_pdf: Constantes TCPDF définies');

        // S'assurer que TCPDF est chargé après la définition des constantes
        if (!class_exists('TCPDF')) {
            error_log('🟡 PDF BUILDER - ajax_generate_order_pdf: TCPDF non chargé, tentative de chargement');

            // Essayer de charger TCPDF depuis les chemins possibles
            $tcpdf_paths = [
                plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'lib/tcpdf/tcpdf_autoload.php',
                plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'lib/tcpdf/tcpdf.php',
                plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'vendor/tecnickcom/tcpdf/tcpdf.php'
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
            $result = $this->main->generate_order_pdf($order_id, $template_id);

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
     * AJAX handler pour générer l'aperçu PDF d'une commande
    /**
     * AJAX handler unifié pour tous les aperçus PDF
     * Gère à la fois les aperçus de template (éditeur) et les aperçus de commande (metabox)
     */
    public function ajax_unified_preview() {
        error_log('🎯 PDF BUILDER - ajax_unified_preview: UNIFIED PREVIEW FUNCTION CALLED - START');

        // Vérifier les permissions
        if (!current_user_can('manage_woocommerce') && !current_user_can('read')) {
            error_log('❌ PDF BUILDER - ajax_unified_preview: Permissions insuffisantes');
            wp_send_json_error('Permissions insuffisantes');
        }

        error_log('✅ PDF BUILDER - ajax_unified_preview: Permissions OK');

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
            error_log('❌ PDF BUILDER - ajax_unified_preview: Nonce invalide');
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        error_log('✅ PDF BUILDER - ajax_unified_preview: Nonce OK');

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : null;
        $elements = isset($_POST['elements']) ? $_POST['elements'] : null;

        error_log('🟡 PDF BUILDER - ajax_unified_preview: Params - order_id=' . ($order_id ?: 'null') . ', template_id=' . ($template_id ?: 'null') . ', has_elements=' . (!empty($elements) ? 'yes' : 'no'));

        try {
            error_log('🟡 PDF BUILDER - ajax_unified_preview: Loading PDF generator...');

            // S'assurer que la classe PDF_Builder_Pro_Generator est chargée
            if (!class_exists('PDF_Builder_Pro_Generator')) {
                $generator_path = plugin_dir_path(dirname(dirname(dirname(__FILE__)))) . 'includes/pdf-generator.php';
                error_log('🟡 PDF BUILDER - ajax_unified_preview: Generator path: ' . $generator_path);
                if (file_exists($generator_path)) {
                    error_log('🟡 PDF BUILDER - ajax_unified_preview: Including generator file...');
                    require_once $generator_path;
                } else {
                    error_log('❌ PDF BUILDER - ajax_unified_preview: Fichier générateur PDF non trouvé: ' . $generator_path);
                    wp_send_json_error('Fichier générateur PDF non trouvé');
                }
            }

            error_log('✅ PDF BUILDER - ajax_unified_preview: Generator class loaded');

            $generator = new PDF_Builder_Pro_Generator();
            error_log('✅ PDF BUILDER - ajax_unified_preview: Generator instance created');

            // Déterminer le type d'aperçu
            if ($order_id && $order_id > 0) {
                // Aperçu de commande WooCommerce
                error_log('📋 PDF BUILDER - ajax_unified_preview: Mode commande WooCommerce');

                // Vérifier que WooCommerce est actif
                if (!class_exists('WooCommerce')) {
                    error_log('❌ PDF BUILDER - ajax_unified_preview: WooCommerce non actif');
                    wp_send_json_error('WooCommerce n\'est pas installé ou activé');
                }

                $order = wc_get_order($order_id);
                if (!$order) {
                    error_log('❌ PDF BUILDER - ajax_unified_preview: Commande non trouvée: ' . $order_id);
                    wp_send_json_error('Commande non trouvée');
                }

                // Déterminer le template à utiliser
                if (!$template_id || $template_id <= 0) {
                    $template_id = $this->get_template_for_order($order);
                    error_log('✅ PDF BUILDER - ajax_unified_preview: Template déterminé automatiquement: ' . $template_id);
                }

                $result = $generator->generate_simple_preview($order_id, $template_id);
                error_log('✅ PDF BUILDER - ajax_unified_preview: Aperçu commande généré: ' . (is_wp_error($result) ? 'WP_Error: ' . $result->get_error_message() : 'URL: ' . $result));

            } elseif (!empty($elements)) {
                // Aperçu de template depuis l'éditeur (éléments JSON)
                error_log('🎨 PDF BUILDER - ajax_unified_preview: Mode template éditeur');
                error_log('🎨 PDF BUILDER - ajax_unified_preview: Raw elements: ' . substr($elements, 0, 200) . '...');

                // Nettoyer les slashes échappés par PHP (correction force)
                $clean_elements = stripslashes($elements);
                error_log('🎨 PDF BUILDER - ajax_unified_preview: Elements after stripslashes (force): ' . substr($clean_elements, 0, 200) . '...');

                // Décoder les éléments
                $decoded_elements = json_decode($clean_elements, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    error_log('❌ PDF BUILDER - ajax_unified_preview: JSON éléments invalide: ' . json_last_error_msg());
                    error_log('❌ PDF BUILDER - ajax_unified_preview: JSON error code: ' . json_last_error());
                    wp_send_json_error('Données du template invalides');
                }

                error_log('✅ PDF BUILDER - ajax_unified_preview: ' . count($decoded_elements) . ' éléments décodés');
                error_log('✅ PDF BUILDER - ajax_unified_preview: Premier élément: ' . json_encode($decoded_elements[0] ?? 'N/A'));

                // Générer l'aperçu avec les éléments du template
                $result = $generator->generate($decoded_elements, ['title' => 'Aperçu Template - ' . date('Y-m-d H:i:s')]);
                error_log('✅ PDF BUILDER - ajax_unified_preview: Aperçu template généré: ' . (is_wp_error($result) ? 'WP_Error: ' . $result->get_error_message() : 'URL: ' . $result));

            } else {
                error_log('❌ PDF BUILDER - ajax_unified_preview: Aucun contexte valide (ni order_id ni elements)');
                wp_send_json_error('Contexte d\'aperçu invalide');
            }

            if (is_wp_error($result)) {
                error_log('❌ PDF BUILDER - ajax_unified_preview: Erreur génération: ' . $result->get_error_message());
                wp_send_json_error($result->get_error_message());
            }

            // Vérifier si le fichier existe réellement
            $file_path = str_replace(home_url('/'), ABSPATH, $result);
            error_log('🔍 PDF BUILDER - ajax_unified_preview: Vérification fichier - URL: ' . $result);
            error_log('🔍 PDF BUILDER - ajax_unified_preview: Vérification fichier - Chemin local: ' . $file_path);
            if (file_exists($file_path)) {
                error_log('🔍 PDF BUILDER - ajax_unified_preview: Taille fichier: ' . filesize($file_path) . ' bytes');
            }

            error_log('✅ PDF BUILDER - ajax_unified_preview: Aperçu généré avec succès: ' . $result);
            wp_send_json_success(['url' => $result]);

        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - ajax_unified_preview: Exception: ' . $e->getMessage());
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Détermine le template approprié pour une commande
     */
    private function get_template_for_order($order) {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $order_status = $order->get_status();
        error_log('🔍 PDF BUILDER - get_template_for_order: Statut commande: ' . $order_status);

        // Vérifier s'il y a un mapping spécifique pour ce statut de commande
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            $mapped_template = $wpdb->get_row($wpdb->prepare(
                "SELECT id, name FROM $table_templates WHERE id = %d",
                $status_templates[$status_key]
            ), ARRAY_A);

            if ($mapped_template) {
                error_log('✅ PDF BUILDER - get_template_for_order: Template mappé trouvé: ' . $mapped_template['name'] . ' (ID: ' . $mapped_template['id'] . ')');
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

        error_log('🟡 PDF BUILDER - get_template_for_order: Recherche par mots-clés: ' . implode(', ', $keywords));

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
                error_log('✅ PDF BUILDER - get_template_for_order: Template par mot-clé trouvé: ' . $keyword_template['name'] . ' (ID: ' . $keyword_template['id'] . ')');
                return $keyword_template['id'];
            }
        }

        // Si aucun template spécifique trouvé, prendre n'importe quel template par défaut
        $default_template = $wpdb->get_row("SELECT id, name FROM $table_templates WHERE is_default = 1 LIMIT 1", ARRAY_A);
        if ($default_template) {
            error_log('✅ PDF BUILDER - get_template_for_order: Template par défaut trouvé: ' . $default_template['name'] . ' (ID: ' . $default_template['id'] . ')');
            return $default_template['id'];
        }

        // Si toujours pas de template, prendre le premier template disponible
        $any_template = $wpdb->get_row("SELECT id, name FROM $table_templates ORDER BY id LIMIT 1", ARRAY_A);
        if ($any_template) {
            error_log('🔄 PDF BUILDER - get_template_for_order: Premier template disponible: ' . $any_template['name'] . ' (ID: ' . $any_template['id'] . ')');
            return $any_template['id'];
        }

        error_log('❌ PDF BUILDER - get_template_for_order: Aucun template trouvé');
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
        error_log('🟡 PDF BUILDER - define_tcpdf_constants: Début définition constantes');

        // Utiliser des chemins absolus au lieu de chemins relatifs
        $plugin_dir = plugin_dir_path(dirname(dirname(dirname(__FILE__))));
        error_log('🟡 PDF BUILDER - define_tcpdf_constants: plugin_dir absolu = ' . $plugin_dir);

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


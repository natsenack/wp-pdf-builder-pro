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
        <style>#pdf-builder-metabox{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.4;color:#333}.pdf-header{background:#f8f9fa;padding:15px 20px;border-bottom:1px solid #dee2e6;margin:-12px -12px 20px -12px}.pdf-order-title{font-size:16px;font-weight:600;margin:0 0 8px 0;display:flex;align-items:center;gap:8px}.pdf-order-meta{font-size:13px;color:#6c757d;display:flex;align-items:center;gap:12px}.pdf-status-badge{background:#e9ecef;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:500}.pdf-doc-type{background:#fff;border:1px solid #dee2e6;padding:12px 16px;margin-bottom:16px;border-radius:4px}.pdf-doc-type-content{display:flex;align-items:center;gap:10px}.pdf-doc-info h4{margin:0 0 2px 0;font-size:14px;font-weight:600}.pdf-doc-info p{margin:0;font-size:12px;color:#6c757d}.pdf-template-section{margin-bottom:16px}.pdf-template-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}.pdf-template-title{font-size:13px;font-weight:600;color:#333}.pdf-template-toggle{font-size:12px;color:#007cba;cursor:pointer;padding:4px 8px;border-radius:3px;transition:background-color 0.2s}.pdf-template-toggle:hover{background:#f0f0f0}.pdf-template-display{background:#fff;border:1px solid #dee2e6;padding:12px 16px;border-radius:4px;cursor:pointer;transition:border-color 0.2s}.pdf-template-display:hover{border-color:#007cba}.pdf-template-info{display:flex;align-items:center;gap:10px}.pdf-template-name{font-weight:500;font-size:14px}.pdf-template-meta{font-size:12px;color:#6c757d}.pdf-template-selector{display:none;margin-top:8px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:12px}.pdf-template-search{width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:3px;margin-bottom:8px;font-size:13px}.pdf-template-list{max-height:200px;overflow-y:auto}.pdf-template-option{padding:8px 12px;cursor:pointer;border-radius:3px;transition:background-color 0.2s;margin-bottom:2px;display:flex;align-items:center;gap:8px}.pdf-template-option:hover{background:#e9ecef}.pdf-template-option.selected{background:#007cba;color:#fff}.pdf-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}.pdf-btn{padding:10px 16px;border:1px solid #ccc;border-radius:4px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:6px;text-decoration:none;background:#fff}.pdf-btn:hover{border-color:#007cba;background:#f0f8ff}.pdf-btn:disabled{opacity:0.6;cursor:not-allowed}.pdf-btn.loading{color:transparent}.pdf-btn.loading::after{content:'';position:absolute;width:16px;height:16px;border:2px solid #007cba;border-top:2px solid transparent;border-radius:50%;animation:spin 1s linear infinite}.pdf-btn-preview{border-color:#007cba;color:#007cba}.pdf-btn-preview:hover{background:#007cba;color:#fff}.pdf-btn-generate{background:#28a745;color:#fff;border-color:#28a745}.pdf-btn-generate:hover{background:#218838}.pdf-btn-download{background:#ffc107;color:#212529;border-color:#ffc107;display:none}.pdf-btn-download:hover{background:#e0a800}.pdf-status{padding:10px 14px;border-radius:4px;font-size:13px;font-weight:500;text-align:center;margin-top:12px;opacity:0;transition:opacity 0.3s;display:none}.pdf-status.show{opacity:1}.pdf-status-loading{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb}.pdf-status-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}.pdf-status-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}.pdf-modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:100000;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:opacity 0.3s}.pdf-modal-overlay.show{opacity:1;visibility:visible}.pdf-modal{background:#fff;border-radius:6px;max-width:95vw;max-height:95vh;overflow:hidden;display:flex;flex-direction:column;transform:scale(0.9);transition:transform 0.3s}.pdf-modal.show{transform:scale(1)}.pdf-modal-header{padding:20px 24px;background:#f8f9fa;border-bottom:1px solid #dee2e6;display:flex;justify-content:space-between;align-items:center}.pdf-modal-title{display:flex;align-items:center;gap:12px;margin:0;font-size:18px;font-weight:600}.pdf-modal-controls{display:flex;align-items:center;gap:12px}.pdf-zoom-controls{display:flex;align-items:center;gap:6px;background:#e9ecef;border-radius:4px;padding:6px}.pdf-zoom-btn{background:#fff;border:1px solid #ccc;border-radius:3px;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:background-color 0.2s}.pdf-zoom-btn:hover{background:#f8f9fa}.pdf-zoom-level{font-size:13px;font-weight:500;min-width:50px;text-align:center;color:#333}.pdf-close-btn{background:#6c757d;border:none;border-radius:50%;width:32px;height:32px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:18px;color:#fff;transition:background-color 0.2s}.pdf-close-btn:hover{background:#5a6268}.pdf-modal-body{flex:1;padding:24px;overflow:auto}.pdf-preview-container{background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;display:flex;align-items:center;justify-content:center;min-height:600px;position:relative;overflow:hidden}.pdf-preview-loading{display:flex;flex-direction:column;align-items:center;gap:16px;color:#6c757d}.pdf-spinner{width:40px;height:40px;border:3px solid #dee2e6;border-top:3px solid #007cba;border-radius:50%;animation:spin 1s linear infinite}.pdf-preview-info{margin-top:20px;padding:16px;background:#fff;border:1px solid #dee2e6;border-radius:4px}.pdf-preview-info-header{display:flex;align-items:center;gap:8px;margin-bottom:12px;font-weight:600}.pdf-preview-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}.pdf-preview-stat{display:flex;justify-content:space-between;align-items:center;padding:6px 0;border-bottom:1px solid #f1f1f1}.pdf-preview-stat:last-child{border-bottom:none}.pdf-preview-stat-label{color:#6c757d;font-weight:500}.pdf-preview-stat-value{color:#333;font-weight:600;background:#e9ecef;padding:2px 8px;border-radius:3px;font-size:12px}@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}@media(max-width:768px){.pdf-actions{grid-template-columns:1fr}.pdf-modal{max-width:95vw;max-height:95vh;border-radius:4px}.pdf-modal-header{padding:16px 20px;flex-direction:column;gap:12px;text-align:center}.pdf-modal-title{font-size:16px;justify-content:center}.pdf-zoom-controls{display:none}.pdf-modal-controls{width:100%;justify-content:center}.pdf-btn{padding:12px 16px;font-size:14px}}</style>

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

        <!-- Clean PDF Preview Modal -->
        <div class="pdf-modal-overlay" id="pdf-modal-overlay">
            <div class="pdf-modal">
                <div class="pdf-modal-header">
                    <h3 class="pdf-modal-title">
                        <span class="modal-icon">🔍</span>
                        Aperçu PDF - <?php echo esc_html($document_type_label); ?>
                    </h3>

                    <div class="pdf-modal-controls">
                        <div class="pdf-zoom-controls">
                            <button class="pdf-zoom-btn" id="pdf-zoom-out" title="Zoom arrière (Ctrl+-)">−</button>
                            <span class="pdf-zoom-level" id="pdf-zoom-level">100%</span>
                            <button class="pdf-zoom-btn" id="pdf-zoom-in" title="Zoom avant (Ctrl++)">+</button>
                            <button class="pdf-zoom-btn" id="pdf-zoom-fit" title="Ajuster à la fenêtre (Ctrl+0)">🔍</button>
                        </div>

                        <button class="pdf-close-btn" id="pdf-close-modal" title="Fermer (Échap)">
                            ×
                        </button>
                    </div>
                </div>

                <div class="pdf-modal-body">
                    <div class="pdf-preview-container">
                        <div class="pdf-preview-loading" id="pdf-preview-loading">
                            <div class="pdf-spinner"></div>
                            <div>
                                <div style="font-weight: 600; margin-bottom: 8px;">Génération de l'aperçu...</div>
                                <div style="font-size: 14px; opacity: 0.8;">Veuillez patienter</div>
                            </div>
                        </div>
                    </div>

                    <div class="pdf-preview-info">
                        <div class="pdf-preview-info-header">
                            <span>📊</span>
                            Informations de l'aperçu
                        </div>

                        <div class="pdf-preview-stats">
                            <div class="pdf-preview-stat">
                                <span class="pdf-preview-stat-label">Template actif:</span>
                                <span class="pdf-preview-stat-value" id="pdf-info-template">
                                    <?php echo $selected_template ? esc_html($selected_template['name']) : 'Par défaut'; ?>
                                </span>
                            </div>
                            <div class="pdf-preview-stat">
                                <span class="pdf-preview-stat-label">Format du document:</span>
                                <span class="pdf-preview-stat-value">A4 (210×297mm)</span>
                            </div>
                            <div class="pdf-preview-stat">
                                <span class="pdf-preview-stat-label">Orientation:</span>
                                <span class="pdf-preview-stat-value">Portrait</span>
                            </div>
                            <div class="pdf-preview-stat">
                                <span class="pdf-preview-stat-label">Résolution:</span>
                                <span class="pdf-preview-stat-value">HD</span>
                            </div>
                            <div class="pdf-preview-stat">
                                <span class="pdf-preview-stat-label">Généré le:</span>
                                <span class="pdf-preview-stat-value" id="pdf-info-date">
                                    <?php echo esc_html(current_time('d/m/Y H:i')); ?>
                                </span>
                            </div>
                            <div class="pdf-preview-stat">
                                <span class="pdf-preview-stat-label">Temps de génération:</span>
                                <span class="pdf-preview-stat-value" id="pdf-info-time">--</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            let currentTemplateId = <?php echo $selected_template ? intval($selected_template['id']) : 0; ?>;
            let currentZoom = 1.0;
            let originalPdfWidth = 0;
            let originalPdfHeight = 0;
            let previewStartTime = 0;

            function showStatus(message, type) {
                if (type === undefined) type = 'loading';
                const $status = $('#pdf-status');
                const classes = {loading:'pdf-status-loading',success:'pdf-status-success',error:'pdf-status-error'};
                $status.removeClass('pdf-status-loading pdf-status-success pdf-status-error show').addClass(classes[type]).html(message).show();
                setTimeout(function(){$status.addClass('show')},10);
                if (type !== 'loading') setTimeout(function(){hideStatus()},5000);
            }

            function hideStatus() {
                $('#pdf-status').removeClass('show');
                setTimeout(function(){$('#pdf-status').hide()},300);
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
                        $('#pdf-template-list').append('<div class="pdf-no-results" style="text-align: center; color: #6c757d; padding: 20px; font-style: italic;">Aucun template trouvé pour "' + searchTerm + '"</div>');
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

            function openModal() {
                previewStartTime = Date.now();
                $('#pdf-modal-overlay').addClass('show');
                $('body').addClass('pdf-modal-open');
                $(document).on('keydown.pdf-modal', handleKeydown);
            }

            function closeModal() {
                $('#pdf-modal-overlay').removeClass('show');
                $('body').removeClass('pdf-modal-open');
                $(document).off('keydown.pdf-modal');
                currentZoom = 1.0;
                $('#pdf-zoom-level').text('100%');
            }

            function handleKeydown(e) {
                if (e.keyCode === 27) {
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

            function applyZoom(zoomLevel) {
                currentZoom = Math.max(0.25, Math.min(4.0, zoomLevel));
                $('#pdf-zoom-level').text(Math.round(currentZoom * 100) + '%');
                const $iframe = $('.pdf-preview-container iframe');
                if ($iframe.length) {
                    $iframe.css({
                        'transform': 'scale(' + currentZoom + ')',
                        'transform-origin': 'top center',
                        'width': (100 / currentZoom) + '%',
                        'height': (originalPdfHeight / currentZoom) + 'px'
                    });
                    $('.pdf-preview-container').css({
                        'min-height': (originalPdfHeight * currentZoom + 60) + 'px'
                    });
                }
            }

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
                            iframe.onload = function() {
                                $('#pdf-preview-loading').hide();
                            };
                            $('.pdf-preview-container').html(iframe);
                            applyZoom(1.0);
                            $('#pdf-info-time').text(generationTime + 'ms');
                            $('#pdf-info-date').text(new Date().toLocaleString('fr-FR'));
                            showStatus('Aperçu généré avec succès (' + generationTime + 'ms)', 'success');
                        } else {
                            $('.pdf-preview-container').html('<div style="color: #dc3545; padding: 40px; text-align: center;"><div style="font-size: 48px; margin-bottom: 16px;">❌</div><strong>Erreur de génération</strong><br><small>' + response.data + '</small></div>');
                            showStatus('Erreur lors de l\'aperçu', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        $('.pdf-preview-container').html('<div style="color: #dc3545; padding: 40px; text-align: center;"><div style="font-size: 48px; margin-bottom: 16px;">🔌</div><strong>Erreur de connexion</strong><br><small>Impossible de contacter le serveur</small></div>');
                        showStatus('Erreur AJAX lors de l\'aperçu', 'error');
                    },
                    complete: function() {
                        setButtonLoading($('#pdf-preview-btn'), false);
                    }
                });
            });

            $('#pdf-generate-btn').on('click', function() {
                showStatus('Génération du PDF...', 'loading');
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
                        if (response.success) {
                            $('#pdf-download-btn').attr('href', response.data.url).show();
                            showStatus('PDF généré avec succès', 'success');
                            setTimeout(function() {
                                window.open(response.data.url, '_blank');
                            }, 500);
                        } else {
                            showStatus('Erreur lors de la génération', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        showStatus('Erreur AJAX lors de la génération', 'error');
                    },
                    complete: function() {
                        setButtonLoading($('#pdf-generate-btn'), false);
                    }
                });
            });

            $('#pdf-download-btn').on('click', function() {
                const pdfUrl = $(this).attr('href');
                if (pdfUrl) {
                    window.open(pdfUrl, '_blank');
                }
            });

            $('#pdf-close-modal').on('click', function() {
                closeModal();
            });

            $('#pdf-modal-overlay').on('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });

            $('#pdf-zoom-in').on('click', function() {
                applyZoom(currentZoom * 1.2);
            });

            $('#pdf-zoom-out').on('click', function() {
                applyZoom(currentZoom / 1.2);
            });

            $('#pdf-zoom-fit').on('click', function() {
                if (originalPdfWidth > 0) {
                    const containerWidth = $('.pdf-preview-container').width() - 60;
                    const fitZoom = containerWidth / originalPdfWidth;
                    applyZoom(fitZoom);
                }
            });

            $(document).on('dblclick', '.pdf-preview-container iframe', function() {
                if (originalPdfWidth > 0) {
                    const containerWidth = $('.pdf-preview-container').width() - 60;
                    const fitZoom = containerWidth / originalPdfWidth;
                    applyZoom(fitZoom);
                }
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
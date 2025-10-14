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
        <style>#pdf-builder-metabox{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.4;color:#333}.pdf-header{background:#f8f9fa;padding:15px 20px;border-bottom:1px solid #dee2e6;margin:-12px -12px 20px -12px}.pdf-order-title{font-size:16px;font-weight:600;margin:0 0 8px 0;display:flex;align-items:center;gap:8px}.pdf-order-meta{font-size:13px;color:#6c757d;display:flex;align-items:center;gap:12px}.pdf-status-badge{background:#e9ecef;padding:2px 8px;border-radius:3px;font-size:11px;font-weight:500}.pdf-doc-type{background:#fff;border:1px solid #dee2e6;padding:12px 16px;margin-bottom:16px;border-radius:4px}.pdf-doc-type-content{display:flex;align-items:center;gap:10px}.pdf-doc-info h4{margin:0 0 2px 0;font-size:14px;font-weight:600}.pdf-doc-info p{margin:0;font-size:12px;color:#6c757d}.pdf-template-section{margin-bottom:16px}.pdf-template-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}.pdf-template-title{font-size:13px;font-weight:600;color:#333}.pdf-template-toggle{font-size:12px;color:#007cba;cursor:pointer;padding:4px 8px;border-radius:3px;transition:background-color 0.2s}.pdf-template-toggle:hover{background:#f0f0f0}.pdf-template-display{background:#fff;border:1px solid #dee2e6;padding:12px 16px;border-radius:4px;cursor:pointer;transition:border-color 0.2s}.pdf-template-display:hover{border-color:#007cba}.pdf-template-info{display:flex;align-items:center;gap:10px}.pdf-template-name{font-weight:500;font-size:14px}.pdf-template-meta{font-size:12px;color:#6c757d}.pdf-template-selector{display:none;margin-top:8px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;padding:12px}.pdf-template-search{width:100%;padding:8px 12px;border:1px solid #ccc;border-radius:3px;margin-bottom:8px;font-size:13px}.pdf-template-list{max-height:200px;overflow-y:auto}.pdf-template-option{padding:8px 12px;cursor:pointer;border-radius:3px;transition:background-color 0.2s;margin-bottom:2px;display:flex;align-items:center;gap:8px}.pdf-template-option:hover{background:#e9ecef}.pdf-template-option.selected{background:#007cba;color:#fff}.pdf-actions{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}.pdf-btn{padding:10px 16px;border:1px solid #ccc;border-radius:4px;font-size:13px;font-weight:500;cursor:pointer;transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:6px;text-decoration:none;background:#fff}.pdf-btn:hover{border-color:#007cba;background:#f0f8ff}.pdf-btn:disabled{opacity:0.6;cursor:not-allowed}.pdf-btn.loading{color:transparent}.pdf-btn.loading::after{content:'';position:absolute;width:16px;height:16px;border:2px solid #007cba;border-top:2px solid transparent;border-radius:50%;animation:spin 1s linear infinite}.pdf-btn-preview{border-color:#007cba;color:#007cba}.pdf-btn-preview:hover{background:#007cba;color:#fff}.pdf-btn-generate{background:#28a745;color:#fff;border-color:#28a745}.pdf-btn-generate:hover{background:#218838}.pdf-btn-download{background:#ffc107;color:#212529;border-color:#ffc107;display:none}.pdf-btn-download:hover{background:#e0a800}.pdf-status{padding:10px 14px;border-radius:4px;font-size:13px;font-weight:500;text-align:center;margin-top:12px;opacity:0;transition:opacity 0.3s;display:none}.pdf-status.show{opacity:1}.pdf-status-loading{background:#d1ecf1;color:#0c5460;border:1px solid #bee5eb}.pdf-status-success{background:#d4edda;color:#155724;border:1px solid #c3e6cb}.pdf-status-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}.pdf-modal-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.75);backdrop-filter:blur(8px);z-index:100000;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:all 0.4s cubic-bezier(0.4, 0, 0.2, 1)}.pdf-modal-overlay.show{opacity:1;visibility:visible}.pdf-modal{background:#fff;border-radius:16px;max-width:95vw;max-height:95vh;overflow:hidden;display:flex;flex-direction:column;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25),0 0 0 1px rgba(255,255,255,0.05),0 0 60px rgba(102, 126, 234, 0.15);border:1px solid rgba(0,0,0,0.1);transform:scale(0.85) translateY(20px);opacity:0;transition:all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)}.pdf-modal.show{transform:scale(1) translateY(0);opacity:1}.pdf-modal-header{padding:24px 32px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-bottom:1px solid rgba(255,255,255,0.1);display:flex;justify-content:space-between;align-items:center}.pdf-modal-title{display:flex;align-items:center;gap:12px;margin:0;font-size:20px;font-weight:600;color:#fff;text-shadow:0 1px 2px rgba(0,0,0,0.2)}.pdf-modal-controls{display:flex;align-items:center;gap:12px}.pdf-zoom-controls{display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.2);border-radius:8px;padding:8px;border:1px solid rgba(255,255,255,0.3)}.pdf-zoom-btn{background:rgba(255,255,255,0.9);border:1px solid rgba(255,255,255,0.3);border-radius:6px;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:18px;font-weight:600;color:#4a5568;transition:all 0.2s;position:relative}.pdf-zoom-btn:hover{background:#fff;transform:translateY(-1px);box-shadow:0 4px 8px rgba(0,0,0,0.15)}.pdf-zoom-btn:active{transform:translateY(0)}.pdf-zoom-btn:focus{outline:none;box-shadow:0 0 0 3px rgba(102, 126, 234, 0.4)}.pdf-zoom-level{font-size:13px;font-weight:500;min-width:50px;text-align:center;color:#333}.pdf-close-btn{background:rgba(255,255,255,0.9);border:1px solid rgba(255,255,255,0.3);border-radius:50%;width:36px;height:36px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:20px;font-weight:600;color:#4a5568;transition:all 0.2s;position:relative}.pdf-close-btn:hover{background:#fff;transform:translateY(-1px) rotate(90deg);box-shadow:0 4px 8px rgba(0,0,0,0.15);color:#e53e3e}.pdf-close-btn:active{transform:translateY(0) rotate(90deg)}.pdf-close-btn:focus{outline:none;box-shadow:0 0 0 3px rgba(229, 62, 62, 0.4)}.pdf-modal-body{flex:1;padding:32px;overflow:auto;background:linear-gradient(135deg,#f8fafc 0%,#e2e8f0 100%)}.pdf-preview-container{background:#fff;border:2px solid rgba(0,0,0,0.08);border-radius:12px;display:flex;align-items:center;justify-content:center;min-height:700px;position:relative;overflow:hidden;box-shadow:0 10px 25px rgba(0,0,0,0.1),inset 0 0 0 1px rgba(255,255,255,0.6)}.pdf-preview-loading{display:flex;flex-direction:column;align-items:center;gap:20px;color:#6c757d;padding:40px}.pdf-spinner{width:50px;height:50px;border:4px solid rgba(102, 126, 234, 0.2);border-top:4px solid #667eea;border-radius:50%;animation:spin 1s linear infinite;filter:drop-shadow(0 2px 4px rgba(102, 126, 234, 0.3))}.pdf-loading-text{font-size:16px;font-weight:500;color:#4a5568;text-align:center}.pdf-loading-subtext{font-size:14px;color:#a0aec0;margin-top:8px;text-align:center}.pdf-preview-info{margin-top:24px;padding:24px;background:linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);border:1px solid rgba(226, 232, 240, 0.8);border-radius:12px;box-shadow:0 4px 6px rgba(0, 0, 0, 0.05);backdrop-filter:blur(10px)}.pdf-preview-info-header{display:flex;align-items:center;gap:10px;margin-bottom:16px;font-weight:600;font-size:16px;color:#2d3748}.pdf-preview-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px}.pdf-preview-stat{display:flex;justify-content:space-between;align-items:center;padding:12px 16px;border-radius:8px;background:#fff;border:1px solid rgba(226, 232, 240, 0.6);transition:all 0.2s}.pdf-preview-stat:hover{transform:translateY(-1px);box-shadow:0 4px 8px rgba(0, 0, 0, 0.1)}.pdf-preview-stat:last-child{border-bottom:none}.pdf-preview-stat-label{color:#718096;font-weight:500;font-size:13px}.pdf-preview-stat-value{color:#2d3748;font-weight:600;background:linear-gradient(135deg, #e6fffa 0%, #b2f5ea 100%);padding:4px 12px;border-radius:20px;font-size:12px;border:1px solid rgba(72, 187, 120, 0.2)}@keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}@media(max-width:768px){.pdf-actions{grid-template-columns:1fr}.pdf-modal{max-width:95vw;max-height:96vh;border-radius:4px}.pdf-modal-header{padding:16px 20px;flex-direction:column;gap:12px;text-align:center}.pdf-modal-title{font-size:16px;justify-content:center}.pdf-zoom-controls{display:none}.pdf-modal-controls{width:100%;justify-content:center}.pdf-btn{padding:12px 16px;font-size:14px}}

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
            font-family: 'Inter', sans-serif;
            font-weight: 500;
        }

        /* Notification de zoom */
        .pdf-zoom-notification {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 8px 16px rgba(102, 126, 234, 0.3);
            z-index: 100001;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateX(-50%) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(-50%) translateY(0);
            }
        }</style>

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
        // Définir ajaxurl si ce n'est pas déjà fait
        if (typeof ajaxurl === 'undefined') {
            ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        }

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
                // Échap pour fermer
                if (e.keyCode === 27) {
                    e.preventDefault();
                    closeModal();
                    return;
                }

                // Ctrl/Cmd + raccourcis
                if (e.ctrlKey || e.metaKey) {
                    switch (e.key) {
                        case '+':
                        case '=':
                            e.preventDefault();
                            applyZoom(currentZoom * 1.2);
                            showZoomNotification('Zoom avant (Ctrl+)');
                            break;
                        case '-':
                            e.preventDefault();
                            applyZoom(currentZoom / 1.2);
                            showZoomNotification('Zoom arrière (Ctrl-)');
                            break;
                        case '0':
                            e.preventDefault();
                            applyZoom(1.0);
                            showZoomNotification('Zoom 100% (Ctrl+0)');
                            break;
                        case 'r':
                            e.preventDefault();
                            // Recharger l'aperçu
                            $('#pdf-preview-btn').trigger('click');
                            break;
                    }
                }

                // Raccourcis sans modificateur
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

            function showZoomNotification(message) {
                // Créer une notification temporaire
                const notification = $('<div class="pdf-zoom-notification">' + message + '</div>');
                $('.pdf-modal').append(notification);

                setTimeout(function() {
                    notification.fadeOut(function() {
                        $(this).remove();
                    });
                }, 1500);
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
                            iframe.style.minHeight = '600px';
                            iframe.onload = function() {
                                $('#pdf-preview-loading').hide();
                                // S'assurer que l'iframe a la bonne taille
                                const iframeBody = iframe.contentDocument || iframe.contentWindow.document;
                                if (iframeBody) {
                                    iframeBody.body.style.margin = '0';
                                    iframeBody.body.style.padding = '0';
                                }
                            };
                            $('.pdf-preview-container').html(iframe);
                            
                            // Écrire le contenu HTML dans l'iframe
                            const iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                            iframeDoc.open();
                            iframeDoc.write(response.data.html);
                            iframeDoc.close();
                            
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
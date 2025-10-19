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

        <!-- NOUVEAU SYSTÈME D'APERÇU PDF COMPLET -->
        <div id="pdf-preview-system-container">
            <div id="pdf-preview-system" class="pdf-preview-system" data-order-id="<?php echo intval($order_id); ?>" data-template-id="<?php echo $selected_template ? intval($selected_template['id']) : 0; ?>">

            <!-- État du système d'aperçu -->
            <div id="pdf-preview-state" class="pdf-preview-state" style="display: none;">
                <div class="preview-state-indicator">
                    <span class="state-icon">⏳</span>
                    <span class="state-text">Initialisation...</span>
                </div>
            </div>

            <!-- Conteneur principal de l'aperçu -->
            <div id="pdf-preview-container" class="pdf-preview-container" style="display: none;">

                <!-- Barre d'outils supérieure -->
                <div class="pdf-preview-header">
                    <div class="preview-info">
                        <h4>Aperçu PDF - Commande #<?php echo intval($order_id); ?></h4>
                        <div class="preview-meta">
                            <span class="template-info">
                                Template: <?php echo $selected_template ? esc_html($selected_template['name']) : 'Aucun'; ?>
                            </span>
                            <span class="preview-timestamp" id="preview-timestamp"></span>
                        </div>
                    </div>

                    <div class="preview-actions">
                        <button class="preview-action-btn" id="preview-refresh-btn" title="Actualiser l'aperçu">
                            <span class="btn-icon">🔄</span>
                            <span class="btn-text">Actualiser</span>
                        </button>
                        <button class="preview-action-btn" id="preview-fullscreen-btn" title="Plein écran">
                            <span class="btn-icon">⛶</span>
                            <span class="btn-text">Plein écran</span>
                        </button>
                        <button class="preview-action-btn danger" id="preview-reset-btn" title="Redémarrer complètement">
                            <span class="btn-icon">⚡</span>
                            <span class="btn-text">Reset</span>
                        </button>
                    </div>
                </div>

                <!-- Zone de contenu principal -->
                <div class="pdf-preview-content">

                    <!-- Panneau latéral (optionnel) -->
                    <div class="pdf-preview-sidebar" id="pdf-preview-sidebar" style="display: none;">
                        <div class="sidebar-header">
                            <h5>Outils d'aperçu</h5>
                            <button class="sidebar-toggle" id="sidebar-toggle">×</button>
                        </div>
                        <div class="sidebar-content">
                            <div class="sidebar-section">
                                <h6>Navigation</h6>
                                <div class="page-navigation">
                                    <button class="nav-btn" id="prev-page-btn" disabled>⬅️ Précédent</button>
                                    <span class="page-info">
                                        Page <span id="current-page">1</span> sur <span id="total-pages">1</span>
                                    </span>
                                    <button class="nav-btn" id="next-page-btn" disabled>Suivant ➡️</button>
                                </div>
                            </div>

                            <div class="sidebar-section">
                                <h6>Zoom</h6>
                                <div class="zoom-controls">
                                    <button class="zoom-btn" data-zoom="0.5">50%</button>
                                    <button class="zoom-btn" data-zoom="0.75">75%</button>
                                    <button class="zoom-btn active" data-zoom="1">100%</button>
                                    <button class="zoom-btn" data-zoom="1.25">125%</button>
                                    <button class="zoom-btn" data-zoom="1.5">150%</button>
                                    <button class="zoom-btn" data-zoom="2">200%</button>
                                    <button class="zoom-btn" id="zoom-fit-btn">Ajuster</button>
                                </div>
                                <div class="zoom-slider">
                                    <input type="range" id="zoom-slider" min="50" max="300" value="100" step="10">
                                    <span id="zoom-value">100%</span>
                                </div>
                            </div>

                            <div class="sidebar-section">
                                <h6>Actions</h6>
                                <div class="action-buttons">
                                    <button class="action-btn primary" id="sidebar-print-btn">
                                        <span class="btn-icon">🖨️</span>
                                        Imprimer
                                    </button>
                                    <button class="action-btn secondary" id="sidebar-download-btn">
                                        <span class="btn-icon">⬇️</span>
                                        Télécharger
                                    </button>
                                    <button class="action-btn info" id="sidebar-share-btn">
                                        <span class="btn-icon">📤</span>
                                        Partager
                                    </button>
                                </div>
                            </div>

                            <div class="sidebar-section">
                                <h6>Informations</h6>
                                <div class="preview-info">
                                    <div class="info-item">
                                        <span class="info-label">Taille:</span>
                                        <span class="info-value" id="preview-size">-</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Temps de génération:</span>
                                        <span class="info-value" id="generation-time">-</span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">État:</span>
                                        <span class="info-value" id="preview-status">Prêt</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Zone d'affichage principal -->
                    <div class="pdf-preview-main">
                        <div class="preview-canvas-container">
                            <div class="preview-canvas" id="preview-canvas">
                                <!-- L'aperçu sera injecté ici -->
                                <div class="preview-placeholder">
                                    <div class="placeholder-icon">📄</div>
                                    <div class="placeholder-text">Cliquez sur "Aperçu" pour générer l'aperçu PDF</div>
                                    <div class="placeholder-hint">L'aperçu sera affiché ici</div>
                                </div>
                            </div>

                            <!-- Indicateur de chargement -->
                            <div class="preview-loading" id="preview-loading" style="display: none;">
                                <div class="loading-container">
                                    <div class="loading-spinner"></div>
                                    <div class="loading-text" id="loading-text">Génération de l'aperçu...</div>
                                    <div class="loading-progress">
                                        <div class="progress-bar" id="loading-progress-bar"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Message d'erreur -->
                            <div class="preview-error" id="preview-error" style="display: none;">
                                <div class="error-container">
                                    <div class="error-icon">⚠️</div>
                                    <div class="error-title">Erreur de génération</div>
                                    <div class="error-message" id="error-message">Une erreur s'est produite lors de la génération de l'aperçu.</div>
                                    <button class="error-retry-btn" id="error-retry-btn">Réessayer</button>
                                </div>
                            </div>
                        </div>

                        <!-- Barre d'outils flottante -->
                        <div class="floating-toolbar" id="floating-toolbar">
                            <button class="floating-btn" id="floating-sidebar-toggle" title="Afficher les outils">
                                <span class="btn-icon">⚙️</span>
                            </button>
                            <button class="floating-btn" id="floating-zoom-in" title="Zoom avant">
                                <span class="btn-icon">+</span>
                            </button>
                            <button class="floating-btn" id="floating-zoom-out" title="Zoom arrière">
                                <span class="btn-icon">−</span>
                            </button>
                            <button class="floating-btn" id="floating-fit" title="Ajuster">
                                <span class="btn-icon">⛶</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Barre de statut -->
                <div class="pdf-preview-footer">
                    <div class="footer-left">
                        <span class="footer-status" id="footer-status">Prêt</span>
                    </div>
                    <div class="footer-center">
                        <span class="footer-zoom" id="footer-zoom">100%</span>
                    </div>
                    <div class="footer-right">
                        <button class="footer-btn" id="footer-close-btn">Fermer</button>
                        <button class="footer-btn primary" id="footer-generate-btn">Générer PDF</button>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <!-- NOUVEAU SYSTÈME D'APERÇU PDF COMPLET - STYLES -->
        <style>
        /* === MODALE D'APERÇU PDF === */
        .woo-pdf-preview-modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 9999;
            display: none;
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

        /* === NOUVEAU SYSTÈME D'APERÇU PDF - STYLES COMPLETS === */

        /* === CONTENEUR PRINCIPAL === */
        .pdf-preview-system {
            position: relative;
            width: 100%;
            margin-top: 20px;
        }

        /* === ÉTAT DU SYSTÈME === */
        .pdf-preview-state {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }

        .preview-state-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .state-icon {
            font-size: 18px;
            animation: pulse 2s infinite;
        }

        .state-text {
            font-weight: 500;
            font-size: 14px;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* === CONTENEUR D'APERÇU === */
        .pdf-preview-container {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            position: relative;
        }

        /* === EN-TÊTE === */
        .pdf-preview-header {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 1px solid #e2e8f0;
            padding: 20px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .preview-info h4 {
            margin: 0 0 4px 0;
            color: #1e293b;
            font-size: 18px;
            font-weight: 600;
        }

        .preview-meta {
            display: flex;
            gap: 16px;
            font-size: 13px;
            color: #64748b;
        }

        .template-info {
            color: #059669;
            font-weight: 500;
        }

        .preview-timestamp {
            color: #6b7280;
        }

        .preview-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .preview-action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            color: #374151;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .preview-action-btn:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .preview-action-btn.danger {
            background: #dc3545;
            color: white;
            border-color: #dc3545;
        }

        .preview-action-btn.danger:hover {
            background: #c82333;
            border-color: #bd2130;
        }

        .btn-icon {
            font-size: 14px;
        }

        .btn-text {
            font-size: 13px;
        }

        /* === CONTENU PRINCIPAL === */
        .pdf-preview-content {
            display: flex;
            height: 600px;
            position: relative;
        }

        /* === PANNEAU LATÉRAL === */
        .pdf-preview-sidebar {
            width: 280px;
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }

        .sidebar-header {
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
        }

        .sidebar-header h5 {
            margin: 0;
            font-size: 14px;
            font-weight: 600;
            color: #374151;
        }

        .sidebar-toggle {
            background: none;
            border: none;
            font-size: 18px;
            color: #6b7280;
            cursor: pointer;
            padding: 0;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            transition: all 0.2s ease;
        }

        .sidebar-toggle:hover {
            background: #f1f5f9;
            color: #374151;
        }

        .sidebar-content {
            flex: 1;
            overflow-y: auto;
            padding: 16px 0;
        }

        .sidebar-section {
            padding: 0 20px 24px 20px;
            border-bottom: 1px solid #e2e8f0;
        }

        .sidebar-section:last-child {
            border-bottom: none;
        }

        .sidebar-section h6 {
            margin: 0 0 12px 0;
            font-size: 12px;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Navigation */
        .page-navigation {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-btn {
            padding: 6px 12px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: white;
            color: #374151;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.2s ease;
        }

        .nav-btn:hover:not(:disabled) {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .nav-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .page-info {
            font-size: 12px;
            color: #6b7280;
            white-space: nowrap;
        }

        /* Zoom */
        .zoom-controls {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            margin-bottom: 12px;
        }

        .zoom-btn {
            padding: 4px 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: white;
            color: #374151;
            cursor: pointer;
            font-size: 11px;
            transition: all 0.2s ease;
        }

        .zoom-btn:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .zoom-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .zoom-slider {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .zoom-slider input[type="range"] {
            flex: 1;
            height: 4px;
            border-radius: 2px;
            background: #e5e7eb;
            outline: none;
            appearance: none;
            -webkit-appearance: none;
        }

        .zoom-slider input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #3b82f6;
            cursor: pointer;
        }

        .zoom-slider input[type="range"]::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #3b82f6;
            cursor: pointer;
            border: none;
        }

        #zoom-value {
            font-size: 12px;
            color: #374151;
            font-weight: 500;
            min-width: 40px;
        }

        /* Actions */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            color: #374151;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-align: left;
        }

        .action-btn:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .action-btn.primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .action-btn.primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        .action-btn.secondary {
            background: #10b981;
            color: white;
            border-color: #10b981;
        }

        .action-btn.secondary:hover {
            background: #059669;
            border-color: #059669;
        }

        .action-btn.info {
            background: #f59e0b;
            color: white;
            border-color: #f59e0b;
        }

        .action-btn.info:hover {
            background: #d97706;
            border-color: #d97706;
        }

        /* Informations */
        .preview-info {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
        }

        .info-label {
            color: #6b7280;
        }

        .info-value {
            color: #374151;
            font-weight: 500;
        }

        /* === ZONE D'AFFICHAGE PRINCIPAL === */
        .pdf-preview-main {
            flex: 1;
            position: relative;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
        }

        .preview-canvas-container {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .preview-canvas {
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: auto;
        }

        /* Placeholder */
        .preview-placeholder {
            text-align: center;
            color: #6b7280;
            max-width: 400px;
            padding: 40px;
        }

        .placeholder-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .placeholder-text {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #374151;
        }

        .placeholder-hint {
            font-size: 14px;
            color: #9ca3af;
        }

        /* Indicateur de chargement */
        .preview-loading {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .loading-container {
            text-align: center;
            max-width: 300px;
        }

        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 16px;
            font-weight: 500;
            color: #374151;
            margin-bottom: 16px;
        }

        .loading-progress {
            width: 100%;
            height: 4px;
            background: #e5e7eb;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #06b6d4);
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 2px;
        }

        /* Message d'erreur */
        .preview-error {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(2px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
        }

        .error-container {
            text-align: center;
            max-width: 400px;
            padding: 32px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .error-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .error-title {
            font-size: 20px;
            font-weight: 600;
            color: #dc3545;
            margin-bottom: 8px;
        }

        .error-message {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 20px;
            line-height: 1.5;
        }

        .error-retry-btn {
            padding: 10px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .error-retry-btn:hover {
            background: #c82333;
        }

        /* Barre d'outils flottante */
        .floating-toolbar {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
            z-index: 50;
        }

        .floating-btn {
            width: 44px;
            height: 44px;
            border: none;
            border-radius: 8px;
            background: white;
            color: #374151;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .floating-btn:hover {
            background: #f9fafb;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* === PIED DE PAGE === */
        .pdf-preview-footer {
            background: white;
            border-top: 1px solid #e5e7eb;
            padding: 16px 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-left,
        .footer-center,
        .footer-right {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .footer-status {
            font-size: 13px;
            color: #059669;
            font-weight: 500;
            padding: 4px 8px;
            background: #ecfdf5;
            border-radius: 4px;
        }

        .footer-zoom {
            font-size: 13px;
            color: #374151;
            font-weight: 500;
            padding: 4px 8px;
            background: #f3f4f6;
            border-radius: 4px;
        }

        .footer-btn {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            background: white;
            color: #374151;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .footer-btn:hover {
            background: #f9fafb;
            border-color: #9ca3af;
        }

        .footer-btn.primary {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .footer-btn.primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }

        /* === RESPONSIVE === */
        @media (max-width: 1024px) {
            .pdf-preview-sidebar {
                width: 240px;
            }
        }

        @media (max-width: 768px) {
            .pdf-preview-content {
                flex-direction: column;
                height: 500px;
            }

            .pdf-preview-sidebar {
                width: 100%;
                height: 200px;
                border-right: none;
                border-bottom: 1px solid #e2e8f0;
            }

            .pdf-preview-header {
                flex-direction: column;
                align-items: stretch;
            }

            .preview-actions {
                justify-content: center;
            }

            .pdf-preview-footer {
                flex-direction: column;
                gap: 12px;
            }

            .footer-left,
            .footer-center,
            .footer-right {
                justify-content: center;
            }

            .floating-toolbar {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .pdf-preview-container {
                border-radius: 0;
            }

            .pdf-preview-header {
                padding: 16px 20px;
            }

            .preview-info h4 {
                font-size: 16px;
            }

            .preview-meta {
                flex-direction: column;
                gap: 4px;
                align-items: flex-start;
            }

            .preview-actions {
                flex-direction: column;
                gap: 6px;
            }

            .preview-action-btn {
                justify-content: center;
            }

            .zoom-controls {
                justify-content: center;
            }

            .action-buttons {
                flex-direction: row;
                flex-wrap: wrap;
            }

            .action-btn {
                flex: 1;
                min-width: 120px;
                justify-content: center;
            }
        }

        /* === ANIMATIONS === */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }

        .slide-in {
            animation: slideIn 0.3s ease-out;
        }

        .bounce-in {
            animation: bounceIn 0.4s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
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
                transform: scale(0.3);
            }
            50% {
                opacity: 1;
                transform: scale(1.05);
            }
            70% {
                transform: scale(0.9);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* === ÉTATS SPÉCIAUX === */
        .pdf-preview-container.loading {
            pointer-events: none;
        }

        .pdf-preview-container.error {
            border-color: #dc3545;
        }

        .pdf-preview-container.success {
            border-color: #28a745;
        }

        .sidebar-content::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-content::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }

        .sidebar-content::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .sidebar-content::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .preview-canvas::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .preview-canvas::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .preview-canvas::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .preview-canvas::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* === ACCESSIBILITÉ === */
        .preview-action-btn:focus,
        .zoom-btn:focus,
        .action-btn:focus,
        .nav-btn:focus,
        .footer-btn:focus,
        .floating-btn:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }

        /* High contrast mode */
        @media (prefers-contrast: high) {
            .pdf-preview-container {
                border: 2px solid #000;
            }

            .preview-action-btn,
            .zoom-btn,
            .action-btn,
            .nav-btn,
            .footer-btn,
            .floating-btn {
                border: 2px solid #000;
            }
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            .loading-spinner,
            .state-icon,
            .floating-btn,
            .preview-action-btn {
                animation: none;
            }

            .floating-btn:hover {
                transform: none;
            }
        }
        </style>

        <!-- NOUVEAU SYSTÈME D'APERÇU PDF COMPLET - JAVASCRIPT -->
        <script type="text/javascript">
        // === NOUVEAU SYSTÈME D'APERÇU PDF - JAVASCRIPT OBJET-ORIENTÉ ===

        jQuery(document).ready(function($) {
            console.log('=== NOUVEAU SYSTÈME D\'APERÇU PDF - INITIALISATION ===');

            // Configuration globale
            var config = {
                orderId: <?php echo intval($order_id); ?>,
                templateId: <?php echo $selected_template ? intval($selected_template['id']) : 0; ?>,
                ajaxUrl: "https://threeaxe.fr/wp-admin/admin-ajax.php",
                nonce: pdfBuilderAjax.nonce,
                debug: true
            };

            console.log('Configuration chargée:', {
                orderId: config.orderId,
                templateId: config.templateId,
                ajaxUrl: config.ajaxUrl,
                nonceLength: config.nonce.length
            });

            // === CLASSE PRINCIPALE DU SYSTÈME D'APERÇU ===
            class PDFPreviewSystem {
                constructor(config) {
                    this.config = config;
                    this.state = {
                        isInitialized: false,
                        isLoading: false,
                        currentZoom: 1.0,
                        currentPage: 1,
                        totalPages: 1,
                        previewContent: null,
                        lastUpdate: null,
                        errorCount: 0
                    };

                    this.elements = {};
                    this.eventListeners = {};

                    this.init();
                }

                // Initialisation du système
                init() {
                    console.log('PDFPreviewSystem: Initialisation...');

                    // Sélectionner les éléments DOM
                    this.selectElements();

                    // Attacher les événements
                    this.attachEvents();

                    // Initialiser l'état
                    this.updateStateDisplay('Système initialisé', 'ready');

                    this.state.isInitialized = true;
                    console.log('PDFPreviewSystem: Initialisation terminée');
                }

                // Sélection des éléments DOM
                selectElements() {
                    this.elements = {
                        // Conteneurs principaux
                        system: $('#pdf-preview-system'),
                        state: $('#pdf-preview-state'),
                        container: $('#pdf-preview-container'),
                        canvas: $('#preview-canvas'),

                        // État du système
                        stateIndicator: $('.preview-state-indicator'),
                        stateIcon: $('.state-icon'),
                        stateText: $('.state-text'),

                        // En-tête
                        header: $('.pdf-preview-header'),
                        refreshBtn: $('#preview-refresh-btn'),
                        fullscreenBtn: $('#preview-fullscreen-btn'),
                        resetBtn: $('#preview-reset-btn'),

                        // Panneau latéral
                        sidebar: $('#pdf-preview-sidebar'),
                        sidebarToggle: $('#sidebar-toggle'),
                        sidebarContent: $('.sidebar-content'),

                        // Navigation
                        prevPageBtn: $('#prev-page-btn'),
                        nextPageBtn: $('#next-page-btn'),
                        pageInfo: $('#page-info'),
                        currentPage: $('#current-page'),
                        totalPages: $('#total-pages'),

                        // Zoom
                        zoomBtns: $('.zoom-btn'),
                        zoomSlider: $('#zoom-slider'),
                        zoomValue: $('#zoom-value'),

                        // Actions
                        printBtn: $('#sidebar-print-btn'),
                        downloadBtn: $('#sidebar-download-btn'),
                        shareBtn: $('#sidebar-share-btn'),

                        // Informations
                        previewSize: $('#preview-size'),
                        generationTime: $('#generation-time'),
                        previewStatus: $('#preview-status'),

                        // Chargement et erreurs
                        loading: $('#preview-loading'),
                        loadingText: $('#loading-text'),
                        loadingProgress: $('#loading-progress-bar'),
                        error: $('#preview-error'),
                        errorMessage: $('#error-message'),
                        errorRetryBtn: $('#error-retry-btn'),

                        // Barre d'outils flottante
                        floatingToolbar: $('#floating-toolbar'),
                        floatingSidebarToggle: $('#floating-sidebar-toggle'),
                        floatingZoomIn: $('#floating-zoom-in'),
                        floatingZoomOut: $('#floating-zoom-out'),
                        floatingFit: $('#floating-fit'),

                        // Pied de page
                        footer: $('.pdf-preview-footer'),
                        footerStatus: $('#footer-status'),
                        footerZoom: $('#footer-zoom'),
                        footerCloseBtn: $('#footer-close-btn'),
                        footerGenerateBtn: $('#footer-generate-btn'),

                        // Métabox
                        generateBtn: $('#pdf-generate-btn'),
                        previewBtn: $('#pdf-preview-btn'),
                        downloadBtn: $('#pdf-download-btn'),
                        status: $('#pdf-status')
                    };

                    console.log('Éléments DOM sélectionnés:', Object.keys(this.elements).length);
                }

                // Attacher les événements
                attachEvents() {
                    var self = this;

                    // Boutons principaux de la métabox
                    this.elements.previewBtn.on('click', function(e) {
                        e.preventDefault();
                        self.showPreview();
                    });

                    this.elements.generateBtn.on('click', function(e) {
                        e.preventDefault();
                        self.generatePDF();
                    });

                    // Boutons d'en-tête
                    this.elements.refreshBtn.on('click', function(e) {
                        e.preventDefault();
                        self.refreshPreview();
                    });

                    this.elements.resetBtn.on('click', function(e) {
                        e.preventDefault();
                        self.resetSystem();
                    });

                    // Navigation
                    this.elements.prevPageBtn.on('click', function(e) {
                        e.preventDefault();
                        self.navigatePage('prev');
                    });

                    this.elements.nextPageBtn.on('click', function(e) {
                        e.preventDefault();
                        self.navigatePage('next');
                    });

                    // Zoom
                    this.elements.zoomBtns.on('click', function(e) {
                        e.preventDefault();
                        var zoom = $(this).data('zoom');
                        if (zoom === 'fit') {
                            self.fitToScreen();
                        } else {
                            self.setZoom(parseFloat(zoom));
                        }
                    });

                    this.elements.zoomSlider.on('input', function() {
                        var zoom = $(this).val() / 100;
                        self.setZoom(zoom);
                    });

                    // Actions
                    this.elements.printBtn.on('click', function(e) {
                        e.preventDefault();
                        self.printPDF();
                    });

                    this.elements.downloadBtn.on('click', function(e) {
                        e.preventDefault();
                        self.downloadPDF();
                    });

                    // Barre d'outils flottante
                    this.elements.floatingZoomIn.on('click', function(e) {
                        e.preventDefault();
                        self.zoomIn();
                    });

                    this.elements.floatingZoomOut.on('click', function(e) {
                        e.preventDefault();
                        self.zoomOut();
                    });

                    this.elements.floatingFit.on('click', function(e) {
                        e.preventDefault();
                        self.fitToScreen();
                    });

                    // Gestion des erreurs
                    this.elements.errorRetryBtn.on('click', function(e) {
                        e.preventDefault();
                        self.retryPreview();
                    });

                    // Pied de page
                    this.elements.footerCloseBtn.on('click', function(e) {
                        e.preventDefault();
                        self.hidePreview();
                    });

                    this.elements.footerGenerateBtn.on('click', function(e) {
                        e.preventDefault();
                        self.generatePDF();
                    });

                    console.log('Événements attachés');
                }

                // === MÉTHODES PRINCIPALES ===

                // Afficher l'aperçu dans une modale
                showPreview() {
                    console.log('Affichage de l\'aperçu en modale...');

                    // Créer la modale si elle n'existe pas
                    if (!this.elements.modal) {
                        this.createModal();
                    }

                    // Afficher la modale
                    this.elements.modal.show();
                    this.elements.modalOverlay.show();

                    // Initialiser l'état
                    this.updateStateDisplay('Chargement de l\'aperçu...', 'loading');
                    this.showLoading('Génération de l\'aperçu PDF...');

                    // Masquer l'ancien contenu et afficher le nouveau système
                    this.elements.modal.find('.woo-pdf-preview-modal-body').html('');
                    this.elements.modal.find('.woo-pdf-preview-modal-body').append(this.elements.system);

                    this.elements.container.show();
                    this.elements.state.hide();

                    this.loadPreview();
                }

                // Créer la modale
                createModal() {
                    if (this.elements.modal) return;

                    const modalHTML = `
                        <div id="woo-pdf-preview-modal" class="woo-pdf-preview-modal" style="display: none;">
                            <div class="woo-pdf-preview-modal-overlay"></div>
                            <div class="woo-pdf-preview-modal-container">
                                <div class="woo-pdf-preview-modal-header">
                                    <h3>Aperçu PDF - Commande #${this.config.orderId}</h3>
                                    <button class="woo-pdf-preview-modal-close">×</button>
                                </div>
                                <div class="woo-pdf-preview-modal-body">
                                    <!-- Le système d'aperçu sera inséré ici -->
                                </div>
                                <div class="woo-pdf-preview-modal-footer">
                                    <button class="woo-pdf-preview-modal-close-btn">Fermer</button>
                                    <button class="woo-pdf-preview-download-btn">Télécharger PDF</button>
                                    <button class="woo-pdf-preview-print-btn">Imprimer</button>
                                </div>
                            </div>
                        </div>
                    `;

                    $('body').append(modalHTML);

                    this.elements.modal = $('#woo-pdf-preview-modal');
                    this.elements.modalOverlay = $('.woo-pdf-preview-modal-overlay');

                    // Événements de fermeture
                    var self = this;
                    this.elements.modal.find('.woo-pdf-preview-modal-close, .woo-pdf-preview-modal-close-btn, .woo-pdf-preview-modal-overlay').on('click', function(e) {
                        if ($(this).hasClass('woo-pdf-preview-modal-overlay') ||
                            $(this).hasClass('woo-pdf-preview-modal-close') ||
                            $(this).hasClass('woo-pdf-preview-modal-close-btn')) {
                            e.preventDefault();
                            self.hidePreview();
                        }
                    });

                    // Boutons d'action
                    this.elements.modal.find('.woo-pdf-preview-download-btn').on('click', function(e) {
                        e.preventDefault();
                        self.downloadPDF();
                    });

                    this.elements.modal.find('.woo-pdf-preview-print-btn').on('click', function(e) {
                        e.preventDefault();
                        self.printPDF();
                    });
                }

                // Masquer l'aperçu (fermer la modale)
                hidePreview() {
                    console.log('Fermeture de la modale d\'aperçu');
                    if (this.elements.modal) {
                        this.elements.modal.hide();
                        this.elements.modalOverlay.hide();

                        // Remettre le système dans la métabox
                        $('#pdf-preview-system-container').append(this.elements.system);
                        this.elements.container.hide();
                        this.elements.state.show();
                        this.updateStateDisplay('Aperçu fermé', 'ready');
                    }
                }

                // Charger l'aperçu
                loadPreview() {
                    var self = this;
                    var startTime = Date.now();

                    this.state.isLoading = true;

                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'pdf_builder_unified_pdf_preview',
                            nonce: this.config.nonce,
                            order_id: this.config.orderId
                        },
                        dataType: 'json',
                        timeout: 30000,
                        success: function(response) {
                            var loadTime = Date.now() - startTime;
                            console.log('Aperçu chargé en', loadTime, 'ms');

                            if (response.success && response.data && response.data.html) {
                                self.displayPreview(response.data.html, loadTime);
                            } else {
                                var errorMsg = response.data || 'Erreur lors de la génération de l\'aperçu';
                                self.showError(errorMsg);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('Erreur AJAX aperçu:', error);
                            self.showError('Erreur de communication: ' + error);
                        },
                        complete: function() {
                            self.state.isLoading = false;
                        }
                    });
                }

                // Afficher l'aperçu
                displayPreview(html, loadTime) {
                    console.log('Affichage du contenu HTML, longueur:', html.length);

                    this.elements.canvas.html(html);
                    this.hideLoading();

                    // Mettre à jour les informations
                    this.updatePreviewInfo(loadTime);
                    this.updateStateDisplay('Aperçu chargé', 'success');

                    // Animation d'entrée
                    this.elements.canvas.addClass('fade-in');

                    // Mettre à jour le timestamp
                    $('#preview-timestamp').text(new Date().toLocaleTimeString());

                    console.log('Aperçu affiché avec succès');
                }

                // Rafraîchir l'aperçu
                refreshPreview() {
                    console.log('Rafraîchissement de l\'aperçu');
                    this.showLoading('Actualisation de l\'aperçu...');
                    this.loadPreview();
                }

                // Réinitialiser le système
                resetSystem() {
                    console.log('=== RÉINITIALISATION COMPLÈTE DU SYSTÈME ===');

                    this.state = {
                        isInitialized: true,
                        isLoading: false,
                        currentZoom: 1.0,
                        currentPage: 1,
                        totalPages: 1,
                        previewContent: null,
                        lastUpdate: null,
                        errorCount: 0
                    };

                    // Vider le canvas
                    this.elements.canvas.html('');

                    // Masquer les overlays
                    this.hideLoading();
                    this.hideError();

                    // Réinitialiser le zoom
                    this.setZoom(1.0);

                    // Réinitialiser la navigation
                    this.updateNavigation();

                    // Mettre à jour l'état
                    this.updateStateDisplay('Système réinitialisé', 'ready');

                    console.log('=== RÉINITIALISATION TERMINÉE ===');
                }

                // === MÉTHODES DE NAVIGATION ===

                navigatePage(direction) {
                    if (direction === 'prev' && this.state.currentPage > 1) {
                        this.state.currentPage--;
                    } else if (direction === 'next' && this.state.currentPage < this.state.totalPages) {
                        this.state.currentPage++;
                    }

                    this.updateNavigation();
                    console.log('Navigation vers la page', this.state.currentPage);
                }

                updateNavigation() {
                    this.elements.currentPage.text(this.state.currentPage);
                    this.elements.totalPages.text(this.state.totalPages);

                    this.elements.prevPageBtn.prop('disabled', this.state.currentPage <= 1);
                    this.elements.nextPageBtn.prop('disabled', this.state.currentPage >= this.state.totalPages);
                }

                // === MÉTHODES DE ZOOM ===

                setZoom(zoom) {
                    this.state.currentZoom = Math.max(0.25, Math.min(3.0, zoom));
                    this.applyZoom();
                    this.updateZoomDisplay();
                    console.log('Zoom défini à', this.state.currentZoom);
                }

                zoomIn() {
                    this.setZoom(this.state.currentZoom * 1.2);
                }

                zoomOut() {
                    this.setZoom(this.state.currentZoom / 1.2);
                }

                fitToScreen() {
                    // Calculer le zoom pour s'adapter à l'écran
                    var containerWidth = this.elements.canvas.width();
                    var contentWidth = this.elements.canvas.find('.pdf-content').width() || 800;
                    var fitZoom = containerWidth / contentWidth;
                    this.setZoom(Math.min(fitZoom, 1.0));
                }

                applyZoom() {
                    var transform = 'scale(' + this.state.currentZoom + ')';
                    this.elements.canvas.css({
                        'transform': transform,
                        'transform-origin': 'top center'
                    });
                }

                updateZoomDisplay() {
                    var zoomPercent = Math.round(this.state.currentZoom * 100);
                    this.elements.zoomValue.text(zoomPercent + '%');
                    this.elements.footerZoom.text(zoomPercent + '%');

                    // Mettre à jour les boutons actifs
                    var self = this;
                    this.elements.zoomBtns.removeClass('active');
                    this.elements.zoomBtns.each(function() {
                        var btnZoom = $(this).data('zoom');
                        if (parseFloat(btnZoom) === self.state.currentZoom) {
                            $(this).addClass('active');
                        }
                    });

                    // Mettre à jour le slider
                    this.elements.zoomSlider.val(zoomPercent);
                }

                // === MÉTHODES D'ACTIONS ===

                generatePDF() {
                    var self = this;
                    this.showStatus('Génération du PDF...', 'loading');
                    this.setButtonLoading(this.elements.generateBtn, true);

                    $.ajax({
                        url: this.config.ajaxUrl,
                        type: 'POST',
                        data: {
                            action: 'pdf_builder_generate_order_pdf',
                            order_id: this.config.orderId,
                            template_id: this.config.templateId,
                            nonce: this.config.nonce
                        },
                        success: function(response) {
                            if (response.success && response.data && response.data.url) {
                                self.elements.downloadBtn.attr('href', response.data.url).show();
                                self.showStatus('PDF généré avec succès', 'success');

                                // Auto-téléchargement
                                setTimeout(function() {
                                    window.open(response.data.url, '_blank');
                                }, 500);
                            } else {
                                var errorMsg = response.data || 'Erreur lors de la génération';
                                self.showStatus(errorMsg, 'error');
                            }
                        },
                        error: function(xhr, status, error) {
                            self.showStatus('Erreur AJAX: ' + error, 'error');
                        },
                        complete: function() {
                            self.setButtonLoading(self.elements.generateBtn, false);
                        }
                    });
                }

                downloadPDF() {
                    if (this.elements.downloadBtn.attr('href')) {
                        window.open(this.elements.downloadBtn.attr('href'), '_blank');
                    } else {
                        this.generatePDF();
                    }
                }

                printPDF() {
                    this.showStatus('Préparation de l\'impression...', 'loading');

                    if (this.elements.downloadBtn.attr('href')) {
                        window.open(this.elements.downloadBtn.attr('href'), '_blank');
                        this.showStatus('PDF ouvert pour impression', 'success');
                    } else {
                        this.generatePDF();
                    }
                }

                // === MÉTHODES D'AFFICHAGE ===

                showLoading(message) {
                    this.elements.loadingText.text(message || 'Chargement...');
                    this.elements.loading.show();
                    this.elements.error.hide();
                    this.elements.container.addClass('loading');
                }

                hideLoading() {
                    this.elements.loading.hide();
                    this.elements.container.removeClass('loading');
                }

                showError(message) {
                    this.elements.errorMessage.text(message);
                    this.elements.error.show();
                    this.elements.loading.hide();
                    this.elements.container.addClass('error');
                    this.updateStateDisplay('Erreur', 'error');
                    this.state.errorCount++;
                }

                hideError() {
                    this.elements.error.hide();
                    this.elements.container.removeClass('error');
                }

                retryPreview() {
                    console.log('Nouvelle tentative de chargement de l\'aperçu');
                    this.hideError();
                    this.showPreview();
                }

                updateStateDisplay(message, type) {
                    var icons = {
                        ready: '✅',
                        loading: '⏳',
                        success: '✅',
                        error: '❌',
                        warning: '⚠️'
                    };

                    this.elements.stateIcon.html(icons[type] || '❓');
                    this.elements.stateText.text(message);

                    // Classes CSS pour l'état
                    this.elements.state.removeClass('ready loading success error warning')
                                      .addClass(type);

                    if (type === 'loading') {
                        this.elements.state.show();
                    } else if (!this.elements.container.is(':visible')) {
                        this.elements.state.show();
                    }
                }

                updatePreviewInfo(loadTime) {
                    this.elements.generationTime.text(loadTime + 'ms');
                    this.elements.previewStatus.text('Prêt');
                    this.elements.previewSize.text(this.elements.canvas.html().length + ' octets');
                }

                showStatus(message, type) {
                    var $status = this.elements.status;
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

                setButtonLoading($btn, loading) {
                    if (loading) {
                        $btn.prop('disabled', true).css('opacity', '0.6');
                    } else {
                        $btn.prop('disabled', false).css('opacity', '1');
                    }
                }
            }

            // === INITIALISATION DU SYSTÈME ===
            try {
                var previewSystem = new PDFPreviewSystem(config);
                console.log('=== SYSTÈME D\'APERÇU PDF INITIALISÉ ===');

                // Exposer le système globalement pour le debug
                window.pdfPreviewSystem = previewSystem;

            } catch (error) {
                console.error('Erreur lors de l\'initialisation du système d\'aperçu:', error);
                $('#pdf-preview-state .state-text').text('Erreur d\'initialisation');
                $('#pdf-preview-state .state-icon').html('❌');
            }

            // === FONCTIONS UTILITAIRES GLOBALES ===
            function showStatus(message, type) {
                if (window.pdfPreviewSystem) {
                    window.pdfPreviewSystem.showStatus(message, type);
                }
            }

            function setButtonLoading($btn, loading) {
                if (window.pdfPreviewSystem) {
                    window.pdfPreviewSystem.setButtonLoading($btn, loading);
                }
            }

            // Gestion du bouton de téléchargement existant
            $('#pdf-download-btn').on('click', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if (url) {
                    window.open(url, '_blank');
                }
            });

            console.log('=== INITIALISATION JAVASCRIPT TERMINÉE ===');
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
                return;
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

            // LOG DES ÉLÉMENTS REÇUS - DEBUG
            error_log('PDF PREVIEW DEBUG - Order ID: ' . $order_id);
            error_log('PDF PREVIEW DEBUG - Template ID: ' . $template_id);
            error_log('PDF PREVIEW DEBUG - Preview Type: ' . $preview_type);
            error_log('PDF PREVIEW DEBUG - Elements received: ' . substr($elements, 0, 500) . '...');
            error_log('PDF PREVIEW DEBUG - Elements length: ' . strlen($elements));



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

                // Récupérer l'order pour les variables dynamiques si order_id est fourni
                $order = null;
                if ($order_id && $order_id > 0) {
                    if (!class_exists('WooCommerce')) {
                        wp_send_json_error('WooCommerce n\'est pas installé ou activé');
                    }
                    $order = wc_get_order($order_id);
                    if (!$order) {
                        wp_send_json_error('Commande non trouvée');
                    }
                }

                // Nettoyer les slashes échappés par PHP (correction force)
                $clean_elements = stripslashes($elements);

                // Décoder les éléments
                $decoded_elements = json_decode($clean_elements, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Données du template invalides');
                }

                // LOG DES ÉLÉMENTS DÉCODÉS
                error_log('PDF PREVIEW DEBUG - Decoded elements count: ' . count($decoded_elements));
                error_log('PDF PREVIEW DEBUG - Decoded elements: ' . json_encode($decoded_elements, JSON_PRETTY_PRINT));
                
                // Log des éléments individuels
                foreach ($decoded_elements as $index => $element) {
                    error_log('PDF PREVIEW DEBUG - Element ' . $index . ': type=' . ($element['type'] ?? 'unknown') . ', content=' . substr($element['content'] ?? '', 0, 100));
                }


                if ($preview_type === 'html') {
                    // Générer l'aperçu HTML avec les éléments du Canvas - SANS TCPDF
                    $options = ['is_preview' => true];
                    if ($order) {
                        $options['order'] = $order;
                        error_log('PDF PREVIEW DEBUG - Passing order to generator (Canvas elements), order ID: ' . $order->get_id());
                    } else {
                        error_log('PDF PREVIEW DEBUG - No order passed to generator (Canvas elements)');
                    }
                    $result = $generator->generate($decoded_elements, $options);
                } else {
                    // PLUS DE GÉNÉRATION PDF AVEC TCPDF - Forcer HTML
                    $options = ['is_preview' => true];
                    if ($order) {
                        $options['order'] = $order;
                        error_log('PDF PREVIEW DEBUG - Passing order to generator (Canvas elements, PDF mode), order ID: ' . $order->get_id());
                    } else {
                        error_log('PDF PREVIEW DEBUG - No order passed to generator (Canvas elements, PDF mode)');
                    }
                    $result = $generator->generate($decoded_elements, $options);
                }

                // LOG DU RÉSULTAT GÉNÉRÉ
                error_log('PDF PREVIEW DEBUG - Generation result: ' . substr($result, 0, 500) . '...');
                error_log('PDF PREVIEW DEBUG - Result length: ' . strlen($result));

            } elseif ($order_id && $order_id > 0) {
                // Aperçu de template depuis l'éditeur (éléments JSON)

                // Récupérer l'order pour les variables dynamiques
                if (!class_exists('WooCommerce')) {
                    wp_send_json_error('WooCommerce n\'est pas installé ou activé');
                }
                $order = wc_get_order($order_id);
                if (!$order) {
                    wp_send_json_error('Commande non trouvée');
                }

                // Nettoyer les slashes échappés par PHP (correction force)
                $clean_elements = stripslashes($elements);

                // Décoder les éléments
                $decoded_elements = json_decode($clean_elements, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_send_json_error('Données du template invalides');
                }


                if ($preview_type === 'html') {
                    // Générer l'aperçu HTML avec les éléments du template - SANS TCPDF
                    error_log('PDF PREVIEW DEBUG - Generating HTML preview for template elements with order ID: ' . ($order ? $order->get_id() : 'NULL'));
                    $result = $generator->generate($decoded_elements, ['is_preview' => true, 'order' => $order]);
                } else {
                    // PLUS DE GÉNÉRATION PDF AVEC TCPDF - Forcer HTML
                    error_log('PDF PREVIEW DEBUG - Generating PDF preview for template elements with order ID: ' . ($order ? $order->get_id() : 'NULL'));
                    $result = $generator->generate($decoded_elements, ['is_preview' => true, 'order' => $order]);
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

                // Pour l'aperçu, récupérer les éléments du template depuis la base de données
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

                $elements_for_pdf = isset($template_data['elements']) ? $template_data['elements'] : [];

                error_log('PDF PREVIEW DEBUG - Elements for PDF generation: ' . json_encode($elements_for_pdf));
                error_log('PDF PREVIEW DEBUG - Order object for generation: ' . ($order ? 'EXISTS (ID: ' . $order->get_id() . ')' : 'NULL'));

                // Générer l'aperçu PDF
                $result = $generator->generate($elements_for_pdf, ['is_preview' => true, 'order' => $order]);

                error_log('PDF PREVIEW DEBUG - Generator result type: ' . gettype($result));
                error_log('PDF PREVIEW DEBUG - Generator result length: ' . (is_string($result) ? strlen($result) : 'N/A'));

            } else {
                wp_send_json_error('Contexte d\'aperçu invalide');
            }

            if (!$result) {
                wp_send_json_error('Erreur lors de la génération de l\'aperçu');
            }

            // Gérer les différents types d'aperçu
            if ($preview_type === 'html') {
                // Pour l'aperçu HTML, retourner directement le HTML généré
                error_log('PDF PREVIEW DEBUG - Sending HTML response, length: ' . strlen($result));
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
        error_log('REPLACE VARIABLES - Input content: ' . substr($content, 0, 200) . '...');
        error_log('REPLACE VARIABLES - Order object exists: ' . ($order ? 'YES' : 'NO'));
        
        if (!$order) {
            error_log('REPLACE VARIABLES - No order object, returning original content');
            return $content;
        }

        error_log('REPLACE VARIABLES - Order ID: ' . $order->get_id());
        error_log('REPLACE VARIABLES - Order status: ' . $order->get_status());
        error_log('REPLACE VARIABLES - Order total: ' . $order->get_total());
        error_log('REPLACE VARIABLES - Order date: ' . ($order->get_date_created() ? $order->get_date_created()->format('Y-m-d H:i:s') : 'null'));

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

        error_log('REPLACE VARIABLES - Total replacements available: ' . count($replacements));
        error_log('REPLACE VARIABLES - Replacement keys: ' . json_encode(array_keys($replacements)));
        error_log('REPLACE VARIABLES - Sample values: ' . json_encode(array_slice($replacements, 0, 10, true)));

        $result = str_replace(array_keys($replacements), array_values($replacements), $content);
        
        error_log('REPLACE VARIABLES - Final result: ' . substr($result, 0, 200) . '...');
        error_log('REPLACE VARIABLES - Content was modified: ' . ($content !== $result ? 'YES' : 'NO'));
        
        return $result;
    }
}

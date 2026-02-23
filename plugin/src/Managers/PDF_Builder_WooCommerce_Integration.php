<?php

namespace PDF_Builder\Managers;

// Déclarations des fonctions WordPress et constantes pour l'IDE
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($path) { return mkdir($path, 0755, true); }
}
if (!function_exists('wp_kses')) {
    function wp_kses($string, $allowed_html = []) { return $string; }
}
if (!function_exists('wp_unslash')) {
    function wp_unslash($value) { return $value; }
}
if (!function_exists('update_post_meta')) {
    function update_post_meta($post_id, $meta_key, $meta_value, $prev_value = '') { return true; }
}
if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key = '', $single = false) { return $single ? '' : []; }
}
if (!function_exists('get_post')) {
    function get_post($post = null, $output = OBJECT, $filter = 'raw') { return null; }
}
if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url, $protocols = null) { return $url; }
}

if (!defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

// Déclarations des classes pour utilisation dans ce namespace
// Note: Les classes Exception, Error, Throwable sont natives et accessibles globalement
// Pour les utiliser, référencez-les avec le namespace global (e.g., \Exception)

// Fonction globale WC()
if (!function_exists('WC')) {
    function WC() { return null; }
}

/**
 * @global function \wp_mkdir_p
 * @global function \wp_kses
 * @global function \update_post_meta
 * @global function \get_post_meta
 * @global function \get_post
 * @global function \esc_url_raw
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * PDF Builder Pro - WooCommerce Integration Manager
 * Gestion de l'intégration WooCommerce
 */

use PDF_Builder\Controllers\PdfBuilderProGenerator;
use PDF_Builder\Core\PDF_Builder_Security_Validator;

/**
 * Stub class for PDF_Builder_Rate_Limiter
 */
class PDF_Builder_Rate_Limiter {
    public static function check_rate_limit($action) {
        return true; // Always allow for now
    }
    
    public static function get_reset_time($action) {
        return 0;
    }
}

/**
 * Stub class for PDF_Builder_Path_Validator
 */
class PDF_Builder_Path_Validator {
    public static function validate_file_path($path) {
        return true; // Always allow for now
    }
}

/**
 * Déclarations de fonctions WordPress pour Intelephense
 */
if (!function_exists('get_current_screen')) {
    function get_current_screen() { return null; }
}
if (!function_exists('add_meta_box')) {
    function add_meta_box($id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null) {}
}
if (!function_exists('absint')) {
    function absint($maybeint) { return (int) $maybeint; }
}
if (!function_exists('esc_html')) {
    function esc_html($text) { return $text; }
}
if (!defined('ARRAY_A')) {
    define('ARRAY_A', 2);
}
if (!function_exists('apply_filters')) {
    function apply_filters($tag, $value) { return $value; }
}
if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field($action = -1, $name = '_wpnonce', $referer = true, $echo = true) {}
}
if (!function_exists('wp_mkdir_p')) {
    function wp_mkdir_p($dir) { return mkdir($dir, 0755, true); }
}
if (!function_exists('wp_kses')) {
    function wp_kses($string, $allowed_html = []) { return $string; }
}
if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) { return true; }
}
if (!function_exists('wp_unslash')) {
    function wp_unslash($value) { return $value; }
}
if (!function_exists('update_post_meta')) {
    function update_post_meta($post_id, $meta_key, $meta_value) { return true; }
}
if (!function_exists('esc_url_raw')) {
    function esc_url_raw($url) { return $url; }
}
if (!function_exists('get_post_meta')) {
    function get_post_meta($post_id, $key = '', $single = false) { return ''; }
}
if (!function_exists('get_post')) {
    function get_post($post = null, $output = OBJECT, $filter = 'raw') { return null; }
}
if (!function_exists('set_transient')) {
    function set_transient($transient, $value, $expiration = 0) { return true; }
}
if (!defined('MINUTE_IN_SECONDS')) {
    define('MINUTE_IN_SECONDS', 60);
}
if (!function_exists('get_terms')) {
    function get_terms($args = []) { return []; }
}
if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = '') { return ''; }
}

class PDF_Builder_WooCommerce_Integration
{
    /**
     * Instance du main plugin
     */
    private $main;

    /**
     * Constructeur
     */
    public function __construct($main_instance)
    {
        $this->main = $main_instance;
        $this->initHooks();
    }

    /**
     * Initialiser les hooks
     */
    private function initHooks()
    {
        // Enregistrer les hooks AJAX via l'action init pour s'assurer qu'ils sont disponibles tôt
        add_action('init', [$this, 'registerAjaxHooks']);
    }

    /**
     * Enregistrer les hooks AJAX
     */
    public function registerAjaxHooks()
    {
        // AJAX handlers pour WooCommerce - gérés par le manager
        add_action('wp_ajax_pdf_builder_generate_order_pdf',  [$this, 'ajaxGenerateOrderPdf'],  1);
        add_action('wp_ajax_pdf_builder_send_order_email',     [$this, 'ajaxSendOrderEmail'],     1);
        add_action('wp_ajax_pdf_builder_save_order_canvas',    [$this, 'ajax_save_order_canvas'], 1);
        add_action('wp_ajax_pdf_builder_load_order_canvas', [$this, 'ajax_load_order_canvas'], 1);
        add_action('wp_ajax_pdf_builder_get_canvas_elements', [$this, 'ajax_get_canvas_elements'], 1);
        add_action('wp_ajax_pdf_builder_get_order_data', [$this, 'ajax_get_order_data'], 1);
        add_action('wp_ajax_pdf_builder_get_company_data', [$this, 'ajax_get_company_data'], 1);
        add_action('wp_ajax_pdf_builder_validate_order_access', [$this, 'ajax_validate_order_access'], 1);
        // Queue PDF pour utilisateurs gratuits
        add_action('wp_ajax_pdf_builder_pdf_queue_join',   [$this, 'ajaxPdfQueueJoin'],   1);
        add_action('wp_ajax_pdf_builder_pdf_queue_poll',   [$this, 'ajaxPdfQueuePoll'],   1);
        add_action('wp_ajax_pdf_builder_pdf_queue_leave',  [$this, 'ajaxPdfQueueLeave'],  1);
        // Streaming PDF en blob (fetch depuis JS, tous utilisateurs)
        add_action('wp_ajax_pdf_builder_stream_pdf',       [$this, 'ajaxStreamPdf'],      1);
    }
    private function detectDocumentType($order_status)
    {
        $status_mapping = [
            'pending' => 'devis',
            'devis' => 'devis',
            'quote' => 'devis',
            'estimate' => 'devis',
            'quotation' => 'devis',
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
    private function getDocumentTypeLabel($document_type)
    {
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
    public function addWoocommerceOrderMetaBox()
    {
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
     * Alias pour la compatibilité - méthode appelée par WooCommerce
     */
    public function render_woocommerce_order_meta_box($post_or_order)
    {
        return $this->renderWoocommerceOrderMetaBox($post_or_order);
    }

    /**
     * Rend la meta box dans les commandes WooCommerce - VERSION SIMPLE & ROBUSTE
     */
    public function renderWoocommerceOrderMetaBox($post_or_order)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // --- Résolution de la commande (legacy WP_Post ou HPOS WC_Order) ---
        if (is_a($post_or_order, 'WC_Order')) {
            $order    = $post_or_order;
            $order_id = $order->get_id();
        } elseif (is_a($post_or_order, 'WP_Post')) {
            $order_id = $post_or_order->ID;
            $order    = function_exists('wc_get_order') ? \wc_get_order($order_id) : null;
        } else {
            $order_id = isset($_GET['id']) ? absint($_GET['id']) : 0;
            $order    = function_exists('wc_get_order') ? \wc_get_order($order_id) : null;
        }

        if (!$order) {
            echo '<p style="color:#dc3545;">❌ ' . __('Commande introuvable.', 'pdf-builder-pro') . '</p>';
            return;
        }

        // --- Licence ---
        $is_premium = false;
        if (class_exists('PDF_Builder\Managers\PDF_Builder_License_Manager')) {
            $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
            $is_premium = $license_manager->isPremium();
        }

        // --- Statut de la commande ---
        $order_status = $order->get_status(); // sans préfixe wc-
        $status_key   = 'wc-' . $order_status;

        $order_statuses = function_exists('wc_get_order_statuses') ? \wc_get_order_statuses() : [];
        $status_label   = $order_statuses[$status_key] ?? ucfirst($order_status);

        // --- Mode GRATUIT : uniquement les commandes terminées ---
        if (!$is_premium && $order_status !== 'completed') {
            ?>
            <div style="font-size:13px;">
                <div style="margin-bottom:8px;">
                    <?php _e('Statut:', 'pdf-builder-pro'); ?> <strong><?php echo esc_html($status_label); ?></strong>
                </div>
                <div style="padding:10px;background:#fff3cd;border:1px solid #ffc107;border-radius:4px;color:#856404;font-size:12px;">
                    🔒 <?php _e('La génération PDF automatique par statut est disponible en version Premium. En version gratuite, seules les commandes <strong>Terminées</strong> sont supportées.', 'pdf-builder-pro'); ?>
                </div>
            </div>
            <?php
            return;
        }

        // --- Résolution du template ---
        // Paramètres enregistrés dans l'onglet "Template" des paramètres
        $settings         = pdf_builder_get_option('pdf_builder_settings', []);
        $status_templates = $settings['pdf_builder_order_status_templates'] ?? [];

        $selected_template = null;

        if ($is_premium) {
            // Premium : chercher le template mappé au statut courant
            if (!empty($status_templates[$status_key])) {
                $selected_template = $wpdb->get_row(
                    $wpdb->prepare("SELECT id, name FROM $table_templates WHERE id = %d", $status_templates[$status_key]),
                    ARRAY_A
                );
            }
        } else {
            // Gratuit : utiliser le template assigné à wc-completed
            if (!empty($status_templates['wc-completed'])) {
                $selected_template = $wpdb->get_row(
                    $wpdb->prepare("SELECT id, name FROM $table_templates WHERE id = %d", $status_templates['wc-completed']),
                    ARRAY_A
                );
            }
        }

        // Fallback : filtre StatusManager puis premier template disponible
        if (!$selected_template) {
            $mapped_id = apply_filters('pdf_builder_get_template_for_status', null, $status_key);
            if ($mapped_id) {
                $selected_template = $wpdb->get_row(
                    $wpdb->prepare("SELECT id, name FROM $table_templates WHERE id = %d", $mapped_id),
                    ARRAY_A
                );
            }
        }

        if (!$selected_template) {
            $selected_template = $wpdb->get_row("SELECT id, name FROM $table_templates ORDER BY id ASC LIMIT 1", ARRAY_A);
        }

        // --- Affichage ---
        $nonce    = wp_create_nonce('pdf_builder_order_actions');
        $ajax_url = admin_url('admin-ajax.php');
        ?>
        <div style="font-size:13px;">

            <!-- Template résolu -->
            <div style="margin-bottom:10px;">
                <div style="color:#6c757d;margin-bottom:4px;">
                    <?php _e('Statut:', 'pdf-builder-pro'); ?> <strong><?php echo esc_html($status_label); ?></strong>
                    <?php if (!$is_premium): ?>
                        <span style="margin-left:6px;background:#e9ecef;color:#495057;border-radius:3px;padding:1px 5px;font-size:11px;">Gratuit</span>
                    <?php else: ?>
                        <span style="margin-left:6px;background:#d4edda;color:#155724;border-radius:3px;padding:1px 5px;font-size:11px;">Premium</span>
                    <?php endif; ?>
                </div>
                <div style="padding:8px 10px;background:#f8f9fa;border:1px solid #dee2e6;border-radius:4px;">
                    📄 <?php if ($selected_template): ?>
                        <strong><?php echo esc_html($selected_template['name']); ?></strong>
                    <?php else: ?>
                        <em style="color:#dc3545;"><?php _e('Aucun template assigné pour ce statut.', 'pdf-builder-pro'); ?></em>
                        <br><small style="color:#6c757d;">
                            <a href="<?php echo admin_url('admin.php?page=pdf-builder-settings&tab=templates'); ?>">
                                <?php _e('Configurer dans les paramètres →', 'pdf-builder-pro'); ?>
                            </a>
                        </small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Boutons d'action -->
            <?php if ($selected_template): ?>
            <div style="display:flex;flex-direction:column;gap:6px;margin-top:2px;">

                <!-- Ligne 1 : PDF + Mail -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                    <button type="button"
                            class="button button-primary pdf-builder-action-btn"
                            data-action-type="pdf"
                            data-order-id="<?php echo esc_attr($order_id); ?>"
                            data-template-id="<?php echo esc_attr($selected_template['id']); ?>"
                            data-nonce="<?php echo esc_attr($nonce); ?>"
                            data-ajax="<?php echo esc_attr($ajax_url); ?>"
                            data-is-premium="<?php echo $is_premium ? '1' : '0'; ?>"
                            style="font-size:12px;padding:5px 8px;">
                        📥 <?php _e('PDF', 'pdf-builder-pro'); ?>
                    </button>
                    <button type="button"
                            id="pdf-builder-mail-btn"
                            class="button button-secondary"
                            data-order-id="<?php echo esc_attr($order_id); ?>"
                            data-template-id="<?php echo esc_attr($selected_template['id']); ?>"
                            data-nonce="<?php echo esc_attr($nonce); ?>"
                            data-ajax="<?php echo esc_attr($ajax_url); ?>"
                            data-order-email="<?php echo esc_attr($order->get_billing_email()); ?>"
                            data-order-number="<?php echo esc_attr($order->get_order_number()); ?>"
                            style="font-size:12px;padding:5px 8px;">
                        ✉️ <?php _e('Mail', 'pdf-builder-pro'); ?>
                    </button>
                </div>

                <?php if ($is_premium): ?>
                <!-- Ligne 2 : PNG + JPG (premium seulement) -->
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px;">
                    <button type="button"
                            class="button button-secondary pdf-builder-action-btn"
                            data-action-type="png"
                            data-order-id="<?php echo esc_attr($order_id); ?>"
                            data-template-id="<?php echo esc_attr($selected_template['id']); ?>"
                            data-nonce="<?php echo esc_attr($nonce); ?>"
                            data-ajax="<?php echo esc_attr($ajax_url); ?>"
                            style="font-size:12px;padding:5px 8px;">
                        🖼️ PNG
                    </button>
                    <button type="button"
                            class="button button-secondary pdf-builder-action-btn"
                            data-action-type="jpg"
                            data-order-id="<?php echo esc_attr($order_id); ?>"
                            data-template-id="<?php echo esc_attr($selected_template['id']); ?>"
                            data-nonce="<?php echo esc_attr($nonce); ?>"
                            data-ajax="<?php echo esc_attr($ajax_url); ?>"
                            style="font-size:12px;padding:5px 8px;">
                        🖼️ JPG
                    </button>
                </div>
                <?php endif; ?>

            </div>

            <!-- Message de retour -->
            <div id="pdf-builder-meta-status" style="display:none;margin-top:8px;padding:8px;border-radius:4px;font-size:12px;"></div>

            <!-- Modal Mail -->
            <div id="pdf-builder-mail-modal" style="display:none;position:fixed;inset:0;z-index:100000;background:rgba(0,0,0,.55);" role="dialog" aria-modal="true">
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:8px;width:400px;max-width:95vw;box-shadow:0 10px 40px rgba(0,0,0,.35);">
                    <div style="padding:14px 18px;border-bottom:1px solid #dee2e6;display:flex;justify-content:space-between;align-items:center;background:#f8f9fa;border-radius:8px 8px 0 0;">
                        <strong style="font-size:13px;">✉️ <?php _e('Envoyer par e-mail', 'pdf-builder-pro'); ?></strong>
                        <button type="button" id="pdf-builder-mail-close" style="background:none;border:none;font-size:20px;cursor:pointer;color:#6c757d;line-height:1;padding:0 4px;" aria-label="Fermer">&times;</button>
                    </div>
                    <div style="padding:18px;">
                        <div style="margin-bottom:12px;">
                            <label style="display:block;margin-bottom:4px;font-size:12px;font-weight:600;color:#495057;">
                                <?php _e('Destinataire', 'pdf-builder-pro'); ?> <span style="color:#dc3545;">*</span>
                            </label>
                            <input type="email" id="pdf-builder-mail-to" class="widefat" style="font-size:13px;" placeholder="email@exemple.com">
                        </div>
                        <div style="margin-bottom:12px;">
                            <label style="display:block;margin-bottom:4px;font-size:12px;font-weight:600;color:#495057;">
                                <?php _e('Sujet', 'pdf-builder-pro'); ?> <span style="color:#dc3545;">*</span>
                            </label>
                            <input type="text" id="pdf-builder-mail-subject" class="widefat" style="font-size:13px;">
                        </div>
                        <div style="margin-bottom:16px;">
                            <label style="display:block;margin-bottom:4px;font-size:12px;font-weight:600;color:#495057;">
                                <?php _e('Message', 'pdf-builder-pro'); ?>
                            </label>
                            <textarea id="pdf-builder-mail-message" class="widefat" rows="4" style="font-size:13px;resize:vertical;"></textarea>
                        </div>
                        <div id="pdf-builder-mail-status" style="display:none;margin-bottom:12px;padding:8px 10px;border-radius:4px;font-size:12px;"></div>
                        <div style="display:flex;gap:8px;justify-content:flex-end;">
                            <button type="button" id="pdf-builder-mail-cancel" class="button button-secondary">
                                <?php _e('Annuler', 'pdf-builder-pro'); ?>
                            </button>
                            <button type="button" id="pdf-builder-mail-send" class="button button-primary">
                                ✉️ <?php _e('Envoyer', 'pdf-builder-pro'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal file d'attente PDF (utilisateurs gratuits) -->
            <div id="pdfb-queue-modal" style="display:none;position:fixed;inset:0;z-index:100001;background:rgba(0,0,0,.6);" role="dialog" aria-modal="true" aria-labelledby="pdfb-queue-modal-title">
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#fff;border-radius:10px;width:340px;max-width:95vw;box-shadow:0 12px 48px rgba(0,0,0,.40);overflow:hidden;">
                    <!-- En-tête -->
                    <div style="padding:16px 20px;border-bottom:1px solid #e0e0e0;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:10px 10px 0 0;">
                        <div id="pdfb-queue-modal-title" style="font-size:15px;font-weight:700;margin-bottom:2px;">⏳ <?php _e('Génération du PDF', 'pdf-builder-pro'); ?></div>
                        <div style="font-size:12px;opacity:.85;"><?php _e('Fichier gratuit · File d\'attente active', 'pdf-builder-pro'); ?></div>
                    </div>
                    <!-- Corps -->
                    <div style="padding:24px 20px;text-align:center;">
                        <!-- Spinner animé -->
                        <div style="margin-bottom:18px;">
                            <svg width="56" height="56" viewBox="0 0 56 56" fill="none" xmlns="http://www.w3.org/2000/svg" style="animation:pdfb-spin 1.2s linear infinite">
                                <circle cx="28" cy="28" r="22" stroke="#e9ecef" stroke-width="6"/>
                                <path d="M28 6a22 22 0 0 1 22 22" stroke="#667eea" stroke-width="6" stroke-linecap="round"/>
                            </svg>
                            <style>@keyframes pdfb-spin{to{transform:rotate(360deg)}}</style>
                        </div>
                        <!-- Position -->
                        <div id="pdfb-queue-pos-text" style="font-size:28px;font-weight:800;color:#667eea;line-height:1;margin-bottom:6px;">#<span id="pdfb-queue-pos-num">…</span></div>
                        <div style="font-size:13px;color:#6c757d;margin-bottom:16px;"><?php _e('dans la file d\'attente', 'pdf-builder-pro'); ?></div>
                        <!-- Barre de progression -->
                        <div style="background:#e9ecef;border-radius:6px;height:8px;overflow:hidden;margin-bottom:14px;">
                            <div id="pdfb-queue-progress-bar" style="height:100%;background:linear-gradient(90deg,#667eea,#764ba2);width:0%;transition:width 0.5s ease;border-radius:6px;"></div>
                        </div>
                        <div id="pdfb-queue-status-text" style="font-size:12px;color:#868e96;"><?php _e('Patience, votre PDF sera généré automatiquement…', 'pdf-builder-pro'); ?></div>
                    </div>
                    <!-- Pied -->
                    <div style="padding:12px 20px;border-top:1px solid #e0e0e0;text-align:center;">
                        <button type="button" id="pdfb-queue-cancel" class="button button-secondary" style="font-size:12px;">
                            <?php _e('Annuler', 'pdf-builder-pro'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <?php endif; ?>
        </div>

        <script type="text/javascript">
        (function($) {

            // ---------------------------------------------------------------
            //  Génération PDF : fetch → Blob → nouvel onglet
            //  Pour les utilisateurs gratuits : file d'attente avec modal
            // ---------------------------------------------------------------

            /** Ouvre le PDF (blob) dans un nouvel onglet avec barre d'outils */
            function openPdfBlob(blob, orderNum) {
                var pdfUrl = URL.createObjectURL(blob);
                var htmlPage =
                    '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
                    '<title>PDF Commande #' + orderNum + '</title>' +
                    '<style>' +
                    'body{margin:0;padding:0;background:#525659;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;}' +
                    '.toolbar{position:fixed;top:0;left:0;right:0;height:48px;background:#323639;display:flex;align-items:center;gap:10px;padding:0 16px;z-index:1000;box-shadow:0 2px 8px rgba(0,0,0,.4);}' +
                    '.btn{padding:8px 18px;border:none;border-radius:5px;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:6px;transition:background .2s;}' +
                    '.btn-dl{background:#2271b1;color:#fff;}.btn-dl:hover{background:#135e96;}' +
                    '.btn-print{background:#10b981;color:#fff;}.btn-print:hover{background:#059669;}' +
                    '.title{color:#ccc;font-size:13px;flex:1;}' +
                    'iframe{position:fixed;top:48px;left:0;right:0;bottom:0;width:100%;height:calc(100% - 48px);border:none;}' +
                    '@media print{.toolbar{display:none!important;} iframe{top:0;height:100%;}}' +
                    '</style></head><body>' +
                    '<div class="toolbar">' +
                    '<span class="title">📄 Commande #' + orderNum + '.pdf</span>' +
                    '<button class="btn btn-dl" onclick="dl()">📥 Télécharger</button>' +
                    '<button class="btn btn-print" onclick="ifr.contentWindow.print()">🖨️ Imprimer</button>' +
                    '</div>' +
                    '<iframe id="ifr" src="' + pdfUrl + '"></iframe>' +
                    '<script>var ifr=document.getElementById("ifr");function dl(){var a=document.createElement("a");a.href="' + pdfUrl + '";a.download="commande-' + orderNum + '.pdf";a.click();}<\/script>' +
                    '</body></html>';
                var pageBlob = new Blob([htmlPage], {type:'text/html;charset=utf-8'});
                window.open(URL.createObjectURL(pageBlob), '_blank');
                setTimeout(function(){URL.revokeObjectURL(pdfUrl);}, 120000);
            }

            /** Fetch le PDF et l'ouvre en blob */
            function doGeneratePdf(ajaxUrl, orderId, templateId, nonce, btn, orig, callback) {
                var fd = new FormData();
                fd.append('action',      'pdf_builder_stream_pdf');
                fd.append('order_id',    orderId);
                fd.append('template_id', templateId);
                fd.append('nonce',       nonce);

                fetch(ajaxUrl, {method:'POST', body:fd})
                    .then(function(res) {
                        var ct = res.headers.get('Content-Type') || '';
                        if (!res.ok || ct.indexOf('application/pdf') === -1) {
                            return res.text().then(function(t) { throw new Error(t || 'Erreur ' + res.status); });
                        }
                        return res.blob();
                    })
                    .then(function(blob) {
                        btn.prop('disabled', false).html(orig);
                        openPdfBlob(blob, orderId);
                        if (callback) callback(null);
                    })
                    .catch(function(err) {
                        btn.prop('disabled', false).html(orig);
                        alert('Erreur génération PDF :\n' + err.message);
                        if (callback) callback(err);
                    });
            }

            // ----- File d'attente (utilisateurs gratuits) -----
            var _queuePollTimer = null;
            var _queueCancelled = false;

            function showQueueModal(position) {
                _queueCancelled = false;
                $('#pdfb-queue-pos-num').text(position + 1);
                $('#pdfb-queue-progress-bar').css('width', '0%');
                $('#pdfb-queue-status-text').text('<?php echo esc_js(__('Patience, votre PDF sera généré automatiquement…', 'pdf-builder-pro')); ?>');
                $('#pdfb-queue-modal').fadeIn(200);
            }

            function updateQueueModal(position, queueSize) {
                $('#pdfb-queue-pos-num').text(position + 1);
                var pct = queueSize > 1 ? Math.max(5, Math.round((1 - position / queueSize) * 100)) : 80;
                $('#pdfb-queue-progress-bar').css('width', pct + '%');
                if (position === 0) {
                    $('#pdfb-queue-status-text').text('<?php echo esc_js(__('C\'est votre tour ! Génération en cours…', 'pdf-builder-pro')); ?>');
                }
            }

            function hideQueueModal() {
                $('#pdfb-queue-modal').fadeOut(150);
                if (_queuePollTimer) { clearTimeout(_queuePollTimer); _queuePollTimer = null; }
            }

            function pollQueue(ajaxUrl, nonce, onReady) {
                if (_queueCancelled) return;
                var fd = new FormData();
                fd.append('action', 'pdf_builder_pdf_queue_poll');
                fd.append('nonce',  nonce);
                fetch(ajaxUrl, {method:'POST', body:fd})
                    .then(function(r){ return r.json(); })
                    .then(function(data) {
                        if (_queueCancelled) return;
                        if (data.success) {
                            var pos  = data.data.position;
                            var size = data.data.queue_size;
                            updateQueueModal(pos, size);
                            if (pos === 0) {
                                onReady();
                            } else {
                                _queuePollTimer = setTimeout(function(){ pollQueue(ajaxUrl, nonce, onReady); }, 3000);
                            }
                        } else {
                            _queuePollTimer = setTimeout(function(){ pollQueue(ajaxUrl, nonce, onReady); }, 3000);
                        }
                    })
                    .catch(function() {
                        if (!_queueCancelled) {
                            _queuePollTimer = setTimeout(function(){ pollQueue(ajaxUrl, nonce, onReady); }, 5000);
                        }
                    });
            }

            function leaveQueue(ajaxUrl, nonce) {
                var fd = new FormData();
                fd.append('action', 'pdf_builder_pdf_queue_leave');
                fd.append('nonce',  nonce);
                fetch(ajaxUrl, {method:'POST', body:fd});
            }

            // Bouton Annuler du modal
            $('#pdfb-queue-cancel').on('click', function() {
                _queueCancelled = true;
                hideQueueModal();
            });

            // ---------------------------------------------------------------

            $('.pdf-builder-action-btn').on('click', function() {
                var btn        = $(this);
                var type       = btn.data('action-type'); // pdf | png | jpg
                var orderId    = btn.data('order-id');
                var templateId = btn.data('template-id');
                var nonce      = btn.data('nonce');
                var ajaxUrl    = btn.data('ajax');
                var isPremium  = btn.data('is-premium') === 1 || btn.data('is-premium') === '1';
                var orig       = btn.html();

                btn.prop('disabled', true).html('⏳');

                if (type === 'pdf') {
                    if (isPremium) {
                        // ── Premium : fetch direct → blob → onglet ──
                        doGeneratePdf(ajaxUrl, orderId, templateId, nonce, btn, orig);
                    } else {
                        // ── Gratuit : rejoindre la file → modal → générer → onglet ──
                        var fd = new FormData();
                        fd.append('action', 'pdf_builder_pdf_queue_join');
                        fd.append('nonce',  nonce);
                        fetch(ajaxUrl, {method:'POST', body:fd})
                            .then(function(r){ return r.json(); })
                            .then(function(data) {
                                if (!data.success) {
                                    btn.prop('disabled', false).html(orig);
                                    alert('<?php echo esc_js(__('Impossible de rejoindre la file : ', 'pdf-builder-pro')); ?>' + (data.data && data.data.message ? data.data.message : ''));
                                    return;
                                }
                                var pos = data.data.position;
                                if (pos === 0) {
                                    // Slot disponible immédiatement
                                    showQueueModal(0);
                                    updateQueueModal(0, 1);
                                    doGeneratePdf(ajaxUrl, orderId, templateId, nonce, btn, orig, function() {
                                        leaveQueue(ajaxUrl, nonce);
                                        hideQueueModal();
                                    });
                                } else {
                                    // En attente
                                    showQueueModal(pos);
                                    pollQueue(ajaxUrl, nonce, function() {
                                        // C'est notre tour
                                        doGeneratePdf(ajaxUrl, orderId, templateId, nonce, btn, orig, function() {
                                            leaveQueue(ajaxUrl, nonce);
                                            hideQueueModal();
                                        });
                                    });
                                }
                            })
                            .catch(function(err) {
                                btn.prop('disabled', false).html(orig);
                                alert('<?php echo esc_js(__('Erreur réseau : ', 'pdf-builder-pro')); ?>' + err.message);
                            });
                    }
                } else {
                    // PNG / JPG : génération via Puppeteer (screenshot natif haute qualité)
                    var formData = new FormData();
                    formData.append('action',      'pdf_builder_generate_image');
                    formData.append('template_id', templateId);
                    formData.append('order_id',    orderId);
                    formData.append('format',      type);
                    formData.append('nonce',       nonce);

                    fetch(ajaxUrl, { method: 'POST', body: formData })
                        .then(function(response) {
                            if (!response.ok) {
                                return response.json().then(function(err) {
                                    throw new Error(
                                        (err.data && (err.data.details || err.data.message)) ||
                                        ('Erreur ' + response.status)
                                    );
                                });
                            }
                            return response.blob();
                        })
                        .then(function(blob) {
                            btn.prop('disabled', false).html(orig);

                            // Blob URL directe — pas de conversion base64 (beaucoup plus rapide)
                            var imageBlobUrl = URL.createObjectURL(blob);
                            var mimeType     = (type === 'jpg') ? 'image/jpeg' : 'image/png';
                            var orderNum     = orderId;
                            var fileName     = 'facture-' + orderNum + '.' + type;

                            var htmlPage =
                                '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
                                '<title>Facture ' + orderNum + '</title>' +
                                '<style>' +
                                'body{margin:0;padding:20px;background:#f5f5f5;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;display:flex;flex-direction:column;align-items:center;}' +
                                '.toolbar{position:fixed;top:20px;right:20px;display:flex;gap:12px;z-index:1000;}' +
                                '.btn{padding:12px 24px;border:none;border-radius:6px;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;transition:all 0.2s;box-shadow:0 2px 8px rgba(0,0,0,0.15);}' +
                                '.btn-download{background:#2271b1;color:white;}.btn-download:hover{background:#135e96;transform:translateY(-1px);}' +
                                '.btn-print{background:#10b981;color:white;}.btn-print:hover{background:#059669;transform:translateY(-1px);}' +
                                '.btn-zoom{background:#6b7280;color:white;padding:12px 16px;}.btn-zoom:hover{background:#4b5563;transform:translateY(-1px);}' +
                                '.zoom-level{background:#f3f4f6;color:#374151;padding:12px 16px;font-weight:bold;border-radius:6px;min-width:70px;text-align:center;}' +
                                '.image-container{margin-top:60px;background:white;padding:20px;border-radius:8px;box-shadow:0 2px 16px rgba(0,0,0,0.1);max-width:50%;transition:transform 0.2s;transform-origin:center top;}' +
                                'img{max-width:100%;height:auto;display:block;}' +
                                '@media print{body{background:white;padding:0;}.toolbar{display:none!important;}.image-container{margin:0;padding:0;box-shadow:none;}}' +
                                '</style></head><body>' +
                                '<div class="toolbar">' +
                                '<button class="btn btn-zoom" onclick="zoomOut()" title="Zoom arrière"><span>🔍➖</span></button>' +
                                '<div class="zoom-level" id="zoomLevel">100%</div>' +
                                '<button class="btn btn-zoom" onclick="zoomIn()" title="Zoom avant"><span>🔍➕</span></button>' +
                                '<button class="btn btn-download" onclick="downloadImage()"><span>📥</span><span>Télécharger</span></button>' +
                                '<button class="btn btn-print" onclick="window.print()"><span>🖨️</span><span>Imprimer</span></button>' +
                                '</div>' +
                                '<div class="image-container" id="imageContainer"><img id="facImg" src="' + imageBlobUrl + '" alt="Facture ' + orderNum + '" crossorigin="anonymous" onload="onImageLoaded()" /></div>' +
                                '<script>' +
                                'var downloadHref="' + imageBlobUrl + '";' +
                                'function onImageLoaded(){try{var img=document.getElementById("facImg");var c=document.createElement("canvas");c.width=img.naturalWidth;c.height=img.naturalHeight;c.getContext("2d").drawImage(img,0,0);downloadHref=c.toDataURL("' + mimeType + '");}catch(e){}}' +
                                'var zoomScale=1.0,container=document.getElementById("imageContainer"),zoomLevelDisplay=document.getElementById("zoomLevel");' +
                                'function updateZoom(){container.style.transform="scale("+zoomScale+")";zoomLevelDisplay.textContent=Math.round(zoomScale*100)+"%";}' +
                                'function zoomIn(){if(zoomScale<3.0){zoomScale+=0.25;updateZoom();}}' +
                                'function zoomOut(){if(zoomScale>0.25){zoomScale-=0.25;updateZoom();}}' +
                                'function downloadImage(){var l=document.createElement("a");l.href=downloadHref;l.download="' + fileName + '";document.body.appendChild(l);l.click();document.body.removeChild(l);}' +
                                '<\/script></body></html>';

                            var pageBlob = new Blob([htmlPage], { type: 'text/html; charset=utf-8' });
                            window.open(URL.createObjectURL(pageBlob), '_blank');
                            // Révoquer la blob URL image après 60s (déjà chargée par le nouvel onglet)
                            setTimeout(function() { URL.revokeObjectURL(imageBlobUrl); }, 60000);
                        })
                        .catch(function(err) {
                            btn.prop('disabled', false).html(orig);
                            alert('Erreur lors de la génération ' + type.toUpperCase() + '\n\n' + err.message);
                        });
                }
            });

            // --- Modal Mail ---
            var $modal  = $('#pdf-builder-mail-modal');
            var $mstatus = $('#pdf-builder-mail-status');

            $('#pdf-builder-mail-btn').on('click', function() {
                var btn   = $(this);
                var num   = btn.data('order-number');
                var email = btn.data('order-email');
                $('#pdf-builder-mail-to').val(email);
                $('#pdf-builder-mail-subject').val('<?php echo esc_js(__('Votre document - Commande', 'pdf-builder-pro')); ?> #' + num);
                $('#pdf-builder-mail-message').val('<?php echo esc_js(__('Bonjour,\n\nVeuillez trouver ci-joint votre document relatif à la commande #', 'pdf-builder-pro')); ?>' + num + '.\n\n<?php echo esc_js(__('Cordialement.', 'pdf-builder-pro')); ?>');
                $mstatus.hide();
                $modal.fadeIn(200);
            });

            function closeModal() { $modal.fadeOut(150); }
            $('#pdf-builder-mail-close, #pdf-builder-mail-cancel').on('click', closeModal);
            $modal.on('click', function(e) { if ($(e.target).is($modal)) closeModal(); });
            $(document).on('keydown.pdfBuilderMail', function(e) { if (e.key === 'Escape') closeModal(); });

            $('#pdf-builder-mail-send').on('click', function() {
                var sendBtn    = $(this);
                var mailBtn    = $('#pdf-builder-mail-btn');
                var to         = $('#pdf-builder-mail-to').val().trim();
                var subject    = $('#pdf-builder-mail-subject').val().trim();
                var message    = $('#pdf-builder-mail-message').val().trim();
                var orderId    = mailBtn.data('order-id');
                var templateId = mailBtn.data('template-id');
                var nonce      = mailBtn.data('nonce');
                var ajaxUrl    = mailBtn.data('ajax');

                if (!to || !subject) {
                    $mstatus.css({display:'block', background:'#f8d7da', color:'#721c24', border:'1px solid #f5c6cb'})
                            .text('<?php echo esc_js(__('Le destinataire et le sujet sont obligatoires.', 'pdf-builder-pro')); ?>');
                    return;
                }

                $mstatus.css({display:'block', background:'#d1ecf1', color:'#0c5460', border:'1px solid #bee5eb'})
                        .text('⏳ <?php echo esc_js(__('Génération du PDF et envoi en cours…', 'pdf-builder-pro')); ?>');
                sendBtn.prop('disabled', true).text('⏳');

                $.post(ajaxUrl, {
                    action:      'pdf_builder_send_order_email',
                    order_id:    orderId,
                    template_id: templateId,
                    nonce:       nonce,
                    to:          to,
                    subject:     subject,
                    message:     message
                }, function(response) {
                    sendBtn.prop('disabled', false).html('✉️ <?php echo esc_js(__('Envoyer', 'pdf-builder-pro')); ?>');
                    if (response.success) {
                        $mstatus.css({background:'#d4edda', color:'#155724', border:'1px solid #c3e6cb'})
                                .text('✅ ' + (response.data.message || '<?php echo esc_js(__('E-mail envoyé !', 'pdf-builder-pro')); ?>'));
                        setTimeout(closeModal, 2500);
                    } else {
                        var msg = (response.data && response.data.message)
                            ? response.data.message
                            : '<?php echo esc_js(__('Erreur lors de l\'envoi.', 'pdf-builder-pro')); ?>';
                        $mstatus.css({background:'#f8d7da', color:'#721c24', border:'1px solid #f5c6cb'}).text('❌ ' + msg);
                    }
                }).fail(function() {
                    sendBtn.prop('disabled', false).html('✉️ <?php echo esc_js(__('Envoyer', 'pdf-builder-pro')); ?>');
                    $mstatus.css({display:'block', background:'#f8d7da', color:'#721c24', border:'1px solid #f5c6cb'})
                            .text('❌ <?php echo esc_js(__('Erreur réseau.', 'pdf-builder-pro')); ?>');
                });
            });

        })(jQuery);
        </script>
        <?php
    }

    // -----------------------------------------------------------------------
    //  FILE D'ATTENTE PDF (utilisateurs gratuits)
    //  Stockage : option WP  pdfb_free_pdf_slots  = [user_id => timestamp, …]
    //  Durée de vie max d'un slot actif : 3 min (sécurité contre les crashs)
    // -----------------------------------------------------------------------

    /** Retourne et nettoie les slots actifs */
    private function _pdfQueueGetSlots(): array
    {
        $slots = get_option('pdfb_free_pdf_slots', []);
        $now   = time();
        $slots = array_filter($slots, fn($ts) => ($now - $ts) < 180);
        return $slots;
    }

    /** Sauvegarde les slots */
    private function _pdfQueueSaveSlots(array $slots): void
    {
        update_option('pdfb_free_pdf_slots', $slots, false);
    }

    /** Vérifie si l'utilisateur courant est premium */
    private function _checkIsPremium(): bool
    {
        if (!class_exists('PDF_Builder\Managers\PDF_Builder_License_Manager')) {
            return false;
        }
        return \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance()->isPremium();
    }

    /**
     * Rejoindre la file / obtenir sa position
     * Réponse : { is_premium, position, slot_available, queue_size }
     */
    public function ajaxPdfQueueJoin()
    {
        if (!current_user_can('edit_shop_orders') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission refusée'], 403);
            return;
        }
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
            wp_send_json_error(['message' => 'Nonce invalide'], 403);
            return;
        }

        if ($this->_checkIsPremium()) {
            wp_send_json_success(['is_premium' => true, 'position' => 0, 'slot_available' => true, 'queue_size' => 0]);
            return;
        }

        $slots   = $this->_pdfQueueGetSlots();
        $user_id = get_current_user_id();

        // Ajouter l'utilisateur s'il n'est pas encore dans la file
        if (!isset($slots[$user_id])) {
            $slots[$user_id] = time();
            $this->_pdfQueueSaveSlots($slots);
        }

        $keys     = array_keys($slots);
        $position = (int) array_search($user_id, $keys, true);

        wp_send_json_success([
            'is_premium'     => false,
            'position'       => $position,      // 0 = premier (peut générer)
            'slot_available' => ($position === 0),
            'queue_size'     => count($slots),
        ]);
    }

    /**
     * Sonder la file (polling) pour connaître la position actuelle
     * Réponse : { position, slot_available, queue_size }
     */
    public function ajaxPdfQueuePoll()
    {
        if (!current_user_can('edit_shop_orders') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission refusée'], 403);
            return;
        }
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
            wp_send_json_error(['message' => 'Nonce invalide'], 403);
            return;
        }

        $slots   = $this->_pdfQueueGetSlots();
        $user_id = get_current_user_id();

        if (!isset($slots[$user_id])) {
            // L'utilisateur n'est plus dans la file (expiration) – le remettre en premier
            $slots = [$user_id => time()] + $slots;
            $this->_pdfQueueSaveSlots($slots);
        } else {
            // Rafraîchir le timestamp pour éviter l'expiration
            $slots[$user_id] = time();
            $this->_pdfQueueSaveSlots($slots);
        }

        $keys     = array_keys($slots);
        $position = (int) array_search($user_id, $keys, true);

        wp_send_json_success([
            'position'       => $position,
            'slot_available' => ($position === 0),
            'queue_size'     => count($slots),
        ]);
    }

    /**
     * Quitter la file après la génération (ou en cas d'erreur)
     */
    public function ajaxPdfQueueLeave()
    {
        if (!current_user_can('edit_shop_orders') && !current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permission refusée'], 403);
            return;
        }
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
            wp_send_json_error(['message' => 'Nonce invalide'], 403);
            return;
        }

        $slots   = $this->_pdfQueueGetSlots();
        $user_id = get_current_user_id();
        unset($slots[$user_id]);
        $this->_pdfQueueSaveSlots($slots);

        wp_send_json_success(['message' => 'ok', 'queue_size' => count($slots)]);
    }

    // -----------------------------------------------------------------------

    /**
     * Stream le PDF en binaire pour un fetch() depuis le navigateur (blob URL).
     * Endpoint dédié, distinct de handle_generate_pdf(), pour éviter les
     * conflits liés à $_REQUEST / form-POST vs AJAX fetch.
     */
    public function ajaxStreamPdf()
    {
        // Permissions
        if (!current_user_can('edit_shop_orders') && !current_user_can('manage_options')) {
            status_header(403);
            echo 'Permission refusée';
            exit;
        }

        // Nonce
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        if (!wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
            status_header(403);
            echo 'Nonce invalide';
            exit;
        }

        // Paramètres
        $order_id    = intval($_POST['order_id'] ?? 0);
        $template_id = sanitize_text_field($_POST['template_id'] ?? '');

        if (!$order_id || !$template_id) {
            status_header(422);
            echo 'Paramètres manquants (order_id=' . $order_id . ' template_id=' . $template_id . ')';
            exit;
        }

        if (!class_exists('PDF_Builder_Unified_Ajax_Handler')) {
            status_header(500);
            echo 'Handler PDF indisponible';
            exit;
        }

        set_time_limit(120);

        $handler     = \PDF_Builder_Unified_Ajax_Handler::get_instance();
        $pdf_content = $handler->get_pdf_buffer($template_id, $order_id);

        if ($pdf_content === false || strlen($pdf_content) === 0) {
            status_header(500);
            echo 'Échec génération PDF';
            exit;
        }

        // Stream le PDF
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header_remove();
        header('Content-Type: application/pdf');

        // Récupérer le numéro de commande pour le filename
        $order_number = $order_id;
        if (function_exists('wc_get_order')) {
            $order = \wc_get_order($order_id);
            if ($order) {
                $order_number = $order->get_order_number();
            }
        }

        header('Content-Disposition: inline; filename="commande-' . $order_number . '.pdf"');
        header('Content-Length: ' . strlen($pdf_content));
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('X-Content-Type-Options: nosniff');

        echo $pdf_content;
        exit;
    }

    // -----------------------------------------------------------------------

    /**
     * AJAX handler pour générer le PDF d'une commande
     */
    public function ajaxGenerateOrderPdf()
    {
        // Vérification des permissions
        if (!current_user_can('manage_options') && !current_user_can('edit_shop_orders')) {
            \wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Validation du nonce (POST pour génération, GET pour aperçu)
        $nonce = \sanitize_text_field($_POST['nonce'] ?? $_GET['nonce'] ?? '');
        if (!\wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
            \wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Paramètres
        $order_id    = intval($_POST['order_id'] ?? $_GET['order_id'] ?? 0);
        $template_id = sanitize_text_field($_POST['template_id'] ?? $_GET['template_id'] ?? '');

        if (!$order_id || !$template_id) {
            \wp_send_json_error(['message' => 'Paramètres manquants']);
            return;
        }

        // Déléguer au handler unifié — toujours via PuppeteerEngine (service threeaxe.fr)
        $_POST['template_id'] = $template_id;
        $_POST['order_id']    = $order_id;

        if (class_exists('PDF_Builder_Unified_Ajax_Handler')) {
            $handler = \PDF_Builder_Unified_Ajax_Handler::get_instance();
            $handler->handle_generate_pdf(); // Streame le PDF et appelle exit()
        }

        \wp_send_json_error(['message' => 'Handler PDF indisponible']);
    }

    /**
     * AJAX handler pour envoyer le PDF d'une commande par e-mail
     */
    public function ajaxSendOrderEmail()
    {
        if (!current_user_can('manage_options') && !current_user_can('edit_shop_orders')) {
            \wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        $nonce = \sanitize_text_field($_POST['nonce'] ?? '');
        if (!\wp_verify_nonce($nonce, 'pdf_builder_order_actions')) {
            \wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        $order_id    = intval($_POST['order_id'] ?? 0);
        $template_id = \sanitize_text_field($_POST['template_id'] ?? '');
        $to          = \sanitize_email($_POST['to'] ?? '');
        $subject     = \sanitize_text_field($_POST['subject'] ?? '');
        $message     = \sanitize_textarea_field($_POST['message'] ?? '');

        if (!$order_id || !$template_id || !$to || !$subject) {
            \wp_send_json_error(['message' => 'Paramètres manquants (order_id, template_id, to, subject)']);
            return;
        }

        if (!\is_email($to)) {
            \wp_send_json_error(['message' => 'Adresse e-mail invalide']);
            return;
        }

        if (!function_exists('wc_get_order')) {
            \wp_send_json_error(['message' => 'WooCommerce non disponible']);
            return;
        }

        $order = \wc_get_order($order_id);
        if (!$order) {
            \wp_send_json_error(['message' => 'Commande #' . $order_id . ' introuvable']);
            return;
        }

        if (!class_exists('PDF_Builder_Unified_Ajax_Handler')) {
            \wp_send_json_error(['message' => 'Handler PDF indisponible']);
            return;
        }

        // Générer le PDF en mémoire
        $handler     = \PDF_Builder_Unified_Ajax_Handler::get_instance();
        $pdf_content = $handler->get_pdf_buffer($template_id, $order_id);

        if ($pdf_content === false) {
            \wp_send_json_error(['message' => 'Erreur lors de la génération du PDF. Vérifiez qu\'un moteur PDF est configuré.']);
            return;
        }

        // Sauvegarder dans un fichier temporaire
        $upload    = \wp_upload_dir();
        $tmp_dir   = $upload['basedir'] . '/pdf-builder-tmp/';
        if (!file_exists($tmp_dir)) {
            \wp_mkdir_p($tmp_dir);
        }
        $tmp_file = $tmp_dir . 'order-' . $order_id . '-' . uniqid() . '.pdf';
        file_put_contents($tmp_file, $pdf_content);

        // Envoi e-mail
        $filename    = 'document-commande-' . $order->get_order_number() . '.pdf';
        $from_name   = get_bloginfo('name');
        $from_email  = get_option('admin_email');
        $headers     = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
        ];
        $body        = $message ? nl2br(\esc_html($message)) : '';
        $attachments = [$tmp_file];

        $sent = \wp_mail($to, $subject, $body, $headers, $attachments);

        // Supprimer le fichier temporaire
        @unlink($tmp_file);

        if ($sent) {
            // translators: %s: recipient email address
            \wp_send_json_success(['message' => sprintf(__('E-mail envoyé avec succès à %s', 'pdf-builder-pro'), $to)]);
        } else {
            \wp_send_json_error(['message' => __('Échec de l\'envoi. Vérifiez la configuration SMTP de WordPress.', 'pdf-builder-pro')]);
        }
    }

    /**
     * Helper pour obtenir le nonce
     */
    private function getNonce()
    {
        return \wp_create_nonce('pdf_builder_order_actions');
    }

    /**
     * Helper pour obtenir l'URL AJAX
     */
    private function getAjaxUrl()
    {
        return \admin_url('admin-ajax.php');
    }

    /**
     * Récupère l'ID du template approprié pour une commande donnée
     */
    private function getTemplateForOrder($order)
    {
        if (!$order) {
            return null;
        }

        // Vérifier si un template spécifique est demandé via POST
        if (isset($_POST['template_id']) && !empty($_POST['template_id'])) {
            return intval($_POST['template_id']);
        }

        // Détecter automatiquement le template basé sur le statut de la commande
        $order_status = $order->get_status();
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $status_templates = $settings['pdf_builder_order_status_templates'] ?? [];
        $status_key = 'wc-' . $order_status;

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            return $status_templates[$status_key];
        }

        // Template par défaut - prendre le premier template disponible
        $templates = pdf_builder_get_option('pdf_builder_templates', []);
        if (!empty($templates)) {
            $first_template = reset($templates);
            return $first_template['id'] ?? null;
        }

        return null;
    }

    /**
     * Construit le style CSS d'un élément
     */
    private function buildElementStyle($element, $base_style)
    {
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

        // `lineHeight` deprecated — ignore incoming values to keep spacing consistent with React canvas

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
    private function renderElementContent($element, $order)
    {
        $type = $element['type'] ?? 'text';
        $content = $element['content'] ?? '';

        // Remplacer les variables dynamiques avec les vraies données de la commande
        if ($order) {
            $content = $this->replaceOrderVariables($content, $order);
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
                return \wp_kses(
                    $content,
                    [
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
                    ]
                );

            default:
                return esc_html($content);
        }
    }

    /**
     * Remplace les variables de commande dans le contenu
     */
    private function replaceOrderVariables($content, $order)
    {
        if (!$order) {
            return $content;
        }

        // Basic order information
        $replacements = [
            '{{order_number}}' => $order->get_order_number(),
            '{{order_date}}' => $order->get_date_created() ? $order->get_date_created()->format('d/m/Y') : '',
            '{{order_date_time}}' => $order->get_date_created() ? $order->get_date_created()->format('d/m/Y H:i:s') : '',
            '{{order_date_modified}}' => $order->get_date_modified() ? $order->get_date_modified()->format('d/m/Y') : '',
            '{{order_total}}' => function_exists('wc_price') ? \wc_price($order->get_total()) : number_format($order->get_total(), 2),
            '{{order_status}}' => function_exists('wc_get_order_status_name') ? \wc_get_order_status_name($order->get_status()) : $order->get_status(),
            '{{customer_name}}' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            '{{customer_email}}' => $order->get_billing_email() ?: '',
            '{{customer_phone}}' => $order->get_billing_phone() ?: '',
            '{{customer_note}}' => $order->get_customer_note() ?: '',
            '{{billing_address}}' => $this->formatAddress($order, 'billing') ?: '',
            '{{shipping_address}}' => $this->formatAddress($order, 'shipping') ?: '',
            '{{payment_method}}' => $order->get_payment_method_title(),
            '{{payment_method_code}}' => $order->get_payment_method(),
            '{{transaction_id}}' => $order->get_transaction_id() ?: '',
            '{{shipping_method}}' => $order->get_shipping_method(),
        ];

        // Financial information
        $subtotal = $order->get_subtotal();
        $total_tax = $order->get_total_tax();
        $shipping_total = $order->get_shipping_total();
        $discount_total = $order->get_discount_total();

        $replacements = array_merge(
            $replacements,
            [
            '{{subtotal}}' => function_exists('wc_price') ? \wc_price($subtotal) : number_format($subtotal, 2),
            '{{tax_amount}}' => function_exists('wc_price') ? \wc_price($total_tax) : number_format($total_tax, 2),
            '{{shipping_amount}}' => function_exists('wc_price') ? \wc_price($shipping_total) : number_format($shipping_total, 2),
            '{{discount_amount}}' => function_exists('wc_price') ? \wc_price($discount_total) : number_format($discount_total, 2),
            '{{total_excl_tax}}' => function_exists('wc_price') ? \wc_price($order->get_total() - $total_tax) : number_format($order->get_total() - $total_tax, 2),
            ]
        );

        // Handle product-specific variables (for single product display)
        $items = $order->get_items();
        if (!empty($items)) {
            $first_item = reset($items);

            $replacements = array_merge(
                $replacements,
                [
                '{{product_name}}' => $first_item->get_name(),
                '{{product_qty}}' => $first_item->get_quantity(),
                '{{product_price}}' => function_exists('wc_price') ? \wc_price($first_item->get_total() / $first_item->get_quantity()) : number_format($first_item->get_total() / $first_item->get_quantity(), 2),
                '{{product_total}}' => function_exists('wc_price') ? \wc_price($first_item->get_total()) : number_format($first_item->get_total(), 2),
                '{{product_sku}}' => $first_item->get_product() ? $first_item->get_product()->get_sku() : '',
                ]
            );

            // For multiple products, create a summary
            if (count($items) > 1) {
                $product_summary = '';
                foreach ($items as $item) {
                    $product_summary .= $item->get_name() . ' (x' . $item->get_quantity() . ') - ' . (function_exists('wc_price') ? \wc_price($item->get_total()) : number_format($item->get_total(), 2)) . "\n";
                }
                $replacements['{{products_list}}'] = $product_summary;
            } else {
                $replacements['{{products_list}}'] = $first_item->get_name() . ' (x' . $first_item->get_quantity() . ') - ' . (function_exists('wc_price') ? \wc_price($first_item->get_total()) : number_format($first_item->get_total(), 2));
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

        $result = str_replace(array_keys($replacements), array_values($replacements), $content);

        return $result;
    }

    /**
     * AJAX handler pour sauvegarder le canvas d'une commande
     */
    public function ajaxSaveOrderCanvas()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!\current_user_can('manage_woocommerce') && !\current_user_can('edit_shop_orders')) {
                \wp_send_json_error('Permissions insuffisantes pour sauvegarder le canvas');
                return;
            }

            // Vérifier le nonce et permissions
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                \wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser les données d'entrée
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
            $canvas_data = isset($_POST['canvas_data']) ? \wp_unslash($_POST['canvas_data']) : '';

            if (!$order_id || empty($canvas_data)) {
                \wp_send_json_error('Données manquantes: order_id et canvas_data requis');
                return;
            }

            // Valider que la commande existe
            $order = function_exists('wc_get_order') ? \wc_get_order($order_id) : null;
            if (!$order) {
                \wp_send_json_error('Commande introuvable');
                return;
            }

            // Valider le format JSON des données canvas
            $canvas_elements = json_decode($canvas_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                \wp_send_json_error('Format JSON invalide pour les données canvas');
                return;
            }

            // Sanitiser les données canvas (validation basique)
            $sanitized_elements = $this->sanitizeCanvasElements($canvas_elements);

            // Sauvegarder les données dans les meta de la commande
            $meta_key = '_pdf_builder_canvas_data';
            $save_result = \update_post_meta($order_id, $meta_key, $sanitized_elements);

            if ($save_result === false) {
                \wp_send_json_error('Erreur lors de la sauvegarde des données canvas');
                return;
            }

            wp_send_json_success(
                [
                'message' => 'Canvas sauvegardé avec succès',
                'order_id' => $order_id,
                'elements_count' => count($sanitized_elements)
                ]
            );
        } catch (\Exception $e) {
            \wp_send_json_error('Erreur interne lors de la sauvegarde');
        }
    }

    /**
     * Sanitise les éléments du canvas
     */
    private function sanitizeCanvasElements($elements)
    {
        if (!is_array($elements)) {
            return [];
        }

        $sanitized = [];
        foreach ($elements as $element) {
            if (!is_array($element)) {
                continue;
            }

            $sanitized_element = [];

            // Sanitiser les champs de base
            $sanitized_element['id'] = isset($element['id']) ? \sanitize_text_field($element['id']) : '';
            // NE PAS sanitizer le type avec sanitize_text_field car il enlève les underscores
            // Simplement le valider
            $sanitized_element['type'] = isset($element['type']) && is_string($element['type']) ? $element['type'] : '';
            $sanitized_element['x'] = isset($element['x']) ? floatval($element['x']) : 0;
            $sanitized_element['y'] = isset($element['y']) ? floatval($element['y']) : 0;
            $sanitized_element['width'] = isset($element['width']) ? floatval($element['width']) : 0;
            $sanitized_element['height'] = isset($element['height']) ? floatval($element['height']) : 0;

            // Sanitiser le contenu selon le type
            if (isset($element['content'])) {
                $sanitized_element['content'] = $this->sanitizeElementContent($element['content'], $element['type'] ?? '');
            }

            // Sanitiser les styles
            if (isset($element['style']) && is_array($element['style'])) {
                $sanitized_element['style'] = $this->sanitizeElementStyles($element['style']);
            }

            $sanitized[] = $sanitized_element;
        }

        return $sanitized;
    }

    /**
     * Sanitise le contenu d'un élément selon son type
     */
    private function sanitizeElementContent($content, $type)
    {
        switch ($type) {
            case 'text':
            case 'dynamic_text':
                return \wp_kses(
                    $content,
                    [
                    'br' => [],
                    'strong' => [],
                    'em' => [],
                    'u' => []
                    ]
                );
            case 'image':
                return \esc_url_raw($content);
            default:
                return \sanitize_text_field($content);
        }
    }

    /**
     * Sanitise les styles d'un élément
     */
    private function sanitizeElementStyles($styles)
    {
        $allowed_styles = [
            'fontSize', 'fontFamily', 'color', 'backgroundColor',
            'textAlign', 'fontWeight', 'fontStyle', 'textDecoration',
            'borderWidth', 'borderColor', 'borderStyle'
        ];

        $sanitized = [];
        foreach ($styles as $key => $value) {
            if (in_array($key, $allowed_styles)) {
                if (strpos($key, 'color') !== false) {
                    $sanitized[$key] = sanitize_hex_color($value) ?: '#000000';
                } elseif (strpos($key, 'fontSize') !== false || strpos($key, 'borderWidth') !== false) {
                    $sanitized[$key] = floatval($value);
                } else {
                    $sanitized[$key] = \sanitize_text_field($value);
                }
            }
        }

        return $sanitized;
    }

    /**
     * AJAX handler pour charger le canvas d'une commande
     */
    public function ajaxLoadOrderCanvas()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!\current_user_can('manage_woocommerce') && !\current_user_can('edit_shop_orders')) {
                \wp_send_json_error('Permissions insuffisantes pour charger le canvas');
                return;
            }

            // Vérifier le nonce et permissions
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                \wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser les données d'entrée
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

            if (!$order_id) {
                \wp_send_json_error('ID commande manquant');
                return;
            }

            // Valider que la commande existe
            $order = function_exists('wc_get_order') ? \wc_get_order($order_id) : null;
            if (!$order) {
                \wp_send_json_error('Commande introuvable');
                return;
            }

            // Récupérer les données canvas depuis les meta
            $meta_key = '_pdf_builder_canvas_data';
            $canvas_data = \get_post_meta($order_id, $meta_key, true);

            // Si aucune donnée canvas n'existe, retourner un tableau vide
            if (empty($canvas_data)) {
                wp_send_json_success(
                    [
                    'canvas_data' => [],
                    'message' => 'Aucune donnée canvas trouvée pour cette commande'
                    ]
                );
                return;
            }

            // Valider que les données sont un tableau
            if (!is_array($canvas_data)) {
                \wp_send_json_error('Format de données canvas invalide');
                return;
            }

            wp_send_json_success(
                [
                'canvas_data' => $canvas_data,
                'order_id' => $order_id,
                'elements_count' => count($canvas_data)
                ]
            );
        } catch (\Exception $e) {
            \wp_send_json_error('Erreur interne lors du chargement');
        }
    }

    /**
     * AJAX handler pour récupérer les éléments canvas d'un template
     */
    public function ajaxGetCanvasElements()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!\current_user_can('manage_woocommerce') && !\current_user_can('edit_shop_orders')) {
                \wp_send_json_error('Permissions insuffisantes pour accéder aux éléments canvas');
                return;
            }

            // Vérifier le nonce et permissions
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                \wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser le template_id
            $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

            if (!$template_id || $template_id <= 0) {
                \wp_send_json_error('ID template invalide ou manquant');
                return;
            }

            // Vérifier que le template existe (soit comme post WordPress, soit dans la table personnalisée)
            $template_exists = false;
            $template_data = null;

            // D'abord vérifier si c'est un post WordPress
            if (\get_post($template_id)) {
                $template_exists = true;
            } else {
                // Essayer de récupérer depuis la table pdf_builder_templates
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';
                $template = $wpdb->get_row(
                    $wpdb->prepare("SELECT id, name, template_data FROM $table_templates WHERE id = %d", $template_id),
                    ARRAY_A
                );

                if ($template && !empty($template['template_data'])) {
                    $template_exists = true;
                    $template_data = $template['template_data'];
                } else {
                }
            }

            if (!$template_exists) {
                \wp_send_json_error('Template introuvable');
                return;
            }

            // Récupérer les éléments depuis le cache ou la base de données
            // ✅ RESPECT DU SETTING CACHE: Only use transient if cache is enabled in settings
            $cache_key = 'pdf_builder_canvas_elements_' . $template_id;
            $cache_enabled = !empty(pdf_builder_get_option('pdf_builder_settings', array())['cache_enabled']);
            $canvas_elements = false;
            
            if ($cache_enabled) {
                $canvas_elements = get_transient($cache_key);
            }

            if ($canvas_elements === false) {
                // Si on a déjà les données du template depuis la table personnalisée, les utiliser
                if ($template_data !== null) {
                    $decoded_data = json_decode($template_data, true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded_data)) {
                        // Extraire les éléments depuis la structure du template
                        if (isset($decoded_data['elements']) && is_array($decoded_data['elements'])) {
                            $canvas_elements = $decoded_data['elements'];
                        } elseif (isset($decoded_data['pages']) && is_array($decoded_data['pages']) && !empty($decoded_data['pages'])) {
                            // Fallback pour l'ancienne structure
                            $first_page = $decoded_data['pages'][0];
                            if (isset($first_page['elements']) && is_array($first_page['elements'])) {
                                $canvas_elements = $first_page['elements'];
                            } else {
                                $canvas_elements = [];
                            }
                        } else {
                            $canvas_elements = [];
                        }
                    } else {
                        $canvas_elements = [];
                    }
                } else {
                    // Template WordPress classique - récupérer depuis les métadonnées
                    $canvas_elements = \get_post_meta($template_id, 'pdf_builder_elements', true);
                }

                // Validation et nettoyage des données
                $canvas_elements = $this->validateAndCleanCanvasElements($canvas_elements);

                // ✅ RESPECT DU SETTING CACHE: Only cache if cache is enabled in settings
                if ($cache_enabled) {
                    \set_transient($cache_key, $canvas_elements, 5 * \MINUTE_IN_SECONDS);
                }
            }

            wp_send_json_success(
                [
                'elements' => $canvas_elements,
                'template_id' => $template_id,
                'cached' => ($canvas_elements !== false),
                'element_count' => is_array($canvas_elements) ? count($canvas_elements) : 0
                ]
            );
        } catch (\Exception $e) {
            \wp_send_json_error('Erreur interne lors de la récupération des éléments');
        }
    }

    /**
     * Valide et nettoie les éléments canvas récupérés
     */
    private function validateAndCleanCanvasElements($elements)
    {
        if (!is_array($elements)) {
            // Essayer de décoder si c'est du JSON
            if (is_string($elements)) {
                $decoded = json_decode($elements, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $elements = $decoded;
                } else {
                    return []; // Retourner tableau vide si JSON invalide
                }
            } else {
                return []; // Retourner tableau vide si format invalide
            }
        }

        // Nettoyer et valider chaque élément
        $cleaned_elements = [];
        foreach ($elements as $element) {
            if (is_array($element) && isset($element['type'])) {
                $cleaned_element = $this->cleanCanvasElement($element);
                if ($cleaned_element) {
                    $cleaned_elements[] = $cleaned_element;
                }
            }
        }

        return $cleaned_elements;
    }

    /**
     * Nettoie un élément canvas individuel
     */
    private function cleanCanvasElement($element)
    {
        $cleaned = [];

        // Champs obligatoires
        $required_fields = ['id', 'type', 'x', 'y', 'width', 'height'];
        foreach ($required_fields as $field) {
            if (!isset($element[$field])) {
                return false; // Élément invalide
            }
            $cleaned[$field] = $this->sanitizeElementField($field, $element[$field]);
        }

        // Champs optionnels
        $optional_fields = ['content', 'style', 'rotation', 'opacity', 'zIndex'];
        foreach ($optional_fields as $field) {
            if (isset($element[$field])) {
                $cleaned[$field] = $this->sanitizeElementField($field, $element[$field]);
            }
        }

        return $cleaned;
    }

    /**
     * Sanitise un champ d'élément selon son type
     */
    private function sanitizeElementField($field, $value)
    {
        switch ($field) {
            case 'id':
            case 'type':
                return \sanitize_text_field($value);
            case 'x':
            case 'y':
            case 'width':
            case 'height':
            case 'rotation':
            case 'opacity':
            case 'zIndex':
                return floatval($value);
            case 'content':
                return $this->sanitizeElementContent($value, 'text'); // Type par défaut
            case 'style':
                return is_array($value) ? $this->sanitizeElementStyles($value) : [];
            default:
                return \sanitize_text_field($value);
        }
    }

    /**
     * Récupère et valide complètement une commande WooCommerce
     */
    private function getAndValidateOrder($order_id)
    {
        // Vérifier que WooCommerce est actif
        if (!function_exists('wc_get_order')) {
            return new \WP_Error('woocommerce_not_active', 'WooCommerce n\'est pas actif');
        }

        // Récupérer la commande
        $order = \wc_get_order($order_id);
        if (!$order) {
            return new \WP_Error('order_not_found', 'Commande introuvable');
        }

        // Vérifier que c'est bien une commande WooCommerce
        if (!function_exists('pdf_builder_is_woocommerce_active') || !pdf_builder_is_woocommerce_active() || !function_exists('is_a') || !is_a($order, 'WC_Order')) {
            return new \WP_Error('invalid_order_type', 'Type d\'objet invalide');
        }

        // Vérifier les permissions d'accès à cette commande spécifique
        if (!\current_user_can('manage_woocommerce')) {
            // Pour les utilisateurs non-admin, vérifier qu'ils ont accès à cette commande
            $user_id = get_current_user_id();
            $order_user_id = $order->get_customer_id();

            // Si l'utilisateur n'est pas le propriétaire et n'a pas les droits d'édition
            if ($user_id !== $order_user_id && !\current_user_can('edit_shop_orders')) {
                return new \WP_Error('access_denied', 'Accès non autorisé à cette commande');
            }
        }

        // Vérifier le statut de la commande - gérer les statuts avec/sans préfixe wc-
        $current_status = $order->get_status();

        // Normaliser les statuts pour la comparaison
        $normalized_current = $current_status;
        if (strpos($current_status, 'wc-') !== 0) {
            // Si pas de préfixe, l'ajouter
            $normalized_current = 'wc-' . $current_status;
        }

        $valid_statuses = function_exists('wc_get_order_statuses') ? array_keys(\wc_get_order_statuses()) : [];

        // Ajouter les statuts configurés dans les mappings du plugin (même s'ils ne sont pas encore détectés par WooCommerce)
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $status_templates = $settings['pdf_builder_order_status_templates'] ?? [];
        $configured_statuses = array_keys($status_templates);
        $valid_statuses = array_merge($valid_statuses, $configured_statuses);

        if (!in_array($normalized_current, $valid_statuses) && !in_array($current_status, $valid_statuses)) {
            return new \WP_Error('invalid_order_status', 'Statut de commande non valide pour le traitement');
        }

        return $order;
    }

    /**
     * Récupère les données complètes des articles de commande
     */
    private function getOrderItemsCompleteData($order)
    {
        $items_data = [];

        foreach ($order->get_items() as $item_id => $item) {
            $product = $item->get_product();
            $item_data = [
                'id' => $item_id,
                'name' => $item->get_name(),
                'quantity' => (float) $item->get_quantity(),
                'price' => (float) ($item->get_total() / max(1, $item->get_quantity())), // Prix unitaire
                'regular_price' => $product ? (float) $product->get_regular_price() : null,
                'sale_price' => $product ? (float) $product->get_sale_price() : null,
                'total' => (float) $item->get_total(),
                'total_tax' => (float) $item->get_total_tax(),
                'subtotal' => (float) $item->get_subtotal(),
                'subtotal_tax' => method_exists($item, 'get_subtotal_tax') ? (float) $item->get_subtotal_tax() : 0.0,
                'tax_class' => $item->get_tax_class(),
                'sku' => $product ? $product->get_sku() : '',
                'product_id' => $item->get_product_id(),
                'variation_id' => $item->get_variation_id(),
                'type' => $product ? $product->get_type() : 'simple',
            ];

            // Gestion des variations
            if ($item->get_variation_id()) {
                $variation = function_exists('wc_get_product') ? \wc_get_product($item->get_variation_id()) : null;
                if ($variation) {
                    $item_data['variation_attributes'] = [];
                    $variation_attributes = $variation->get_variation_attributes();

                    foreach ($variation_attributes as $attribute_name => $attribute_value) {
                        $attribute_name_clean = str_replace('attribute_', '', $attribute_name);
                        $attribute_name_clean = str_replace('pa_', '', $attribute_name_clean);

                        // Récupérer le nom d'attribut lisible
                        $attribute_taxonomy = 'pa_' . $attribute_name_clean;
                        $attribute_terms = get_terms(
                            [
                            'taxonomy' => $attribute_taxonomy,
                            'slug' => $attribute_value,
                            'hide_empty' => false
                            ]
                        );

                        $attribute_label = $attribute_terms && !\is_wp_error($attribute_terms) && !empty($attribute_terms)
                            ? $attribute_terms[0]->name
                            : ucfirst($attribute_name_clean);

                        $item_data['variation_attributes'][$attribute_name_clean] = [
                            'label' => $attribute_label,
                            'value' => $attribute_value,
                            'taxonomy' => $attribute_taxonomy
                        ];
                    }
                }
            }

            // Métadonnées de l'article
            $item_data['meta_data'] = [];
            foreach ($item->get_meta_data() as $meta) {
                $item_data['meta_data'][$meta->key] = $meta->value;
            }

            $items_data[] = $item_data;
        }

        return $items_data;
    }

    /**
     * AJAX: Récupère les données complètes d'une commande pour le mode Metabox
     */
    public function ajaxGetOrderData()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!\current_user_can('manage_woocommerce') && !\current_user_can('edit_shop_orders')) {
                \wp_send_json_error('Permissions insuffisantes pour accéder aux données de commande');
                return;
            }

            // Vérifier le nonce et permissions
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                \wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser les données d'entrée
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

            if (!$order_id) {
                \wp_send_json_error('ID commande manquant');
                return;
            }

            // Récupération et validation complète de la commande
            /** @var \WC_Order $order */
            $order = wc_get_order($order_id);
            if (!$order) {
                \wp_send_json_error('Commande introuvable');
                return;
            }

            // Récupérer les données de commande formatées
            $order_data = [
                'order' => [
                    'id' => $order->get_id(),
                    'number' => $order->get_order_number(),
                    'status' => $order->get_status(),
                    'total' => $order->get_total(),
                ],
                'items' => $this->getOrderItemsCompleteData($order),
            ];

            wp_send_json_success(
                [
                'order' => $order_data,
                'order_id' => $order_id
                ]
            );
        } catch (\Exception $e) {
            \wp_send_json_error('Erreur interne lors de la récupération des données de commande');
        }
    }

    /**
     * AJAX: Valide l'accès à une commande
     */
    public function ajaxValidateOrderAccess()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!\current_user_can('manage_woocommerce') && !\current_user_can('edit_shop_orders')) {
                \wp_send_json_error('Permissions insuffisantes pour accéder à cette commande');
                return;
            }

            // Vérifier le nonce et permissions
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                \wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Valider et sanitiser les données d'entrée
            $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

            if (!$order_id) {
                \wp_send_json_error('ID commande manquant');
                return;
            }

            // Récupération et validation de la commande
            $order = $this->getAndValidateOrder($order_id);
            if (\is_wp_error($order)) {
                \wp_send_json_error($order->get_error_message());
                return;
            }

            wp_send_json_success(
                [
                'order_id' => $order_id,
                'accessible' => true
                ]
            );
        } catch (\Exception $e) {
            \wp_send_json_error('Erreur interne lors de la validation d\'accès');
        }
    }

    /**
     * AJAX: Récupère les données entreprise depuis WooCommerce/WordPress
     */
    public function ajaxGetCompanyData()
    {
        try {
            // Vérifier les permissions utilisateur
            if (!\current_user_can('manage_woocommerce') && !\current_user_can('edit_shop_orders')) {
                \wp_send_json_error('Permissions insuffisantes pour accéder aux données entreprise');
                return;
            }

            // Vérifier le nonce et permissions
            if (!pdf_builder_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_order_actions')) {
                \wp_send_json_error('Sécurité: Nonce invalide');
                return;
            }

            // Récupérer les données entreprise depuis les options WordPress/WooCommerce
            $company_data = [
                'name' => get_bloginfo('name'),
                'address' => trim(
                    get_option('woocommerce_store_address') . ' ' .
                    get_option('woocommerce_store_address_2') . ' ' .
                    get_option('woocommerce_store_postcode') . ' ' .
                    get_option('woocommerce_store_city')
                ),
                'phone' => get_option('woocommerce_phone') ?: pdf_builder_get_option('pdf_builder_company_phone'),
                'email' => get_option('woocommerce_email_from_address'),
                'website' => get_option('siteurl'), // URL du site WordPress
                'vat' => pdf_builder_get_option('pdf_builder_company_vat'),
                'rcs' => pdf_builder_get_option('pdf_builder_company_rcs'),
                'siret' => pdf_builder_get_option('pdf_builder_company_siret')
            ];

            // Nettoyer les données vides
            foreach ($company_data as $key => $value) {
                if (empty($value)) {
                    $company_data[$key] = '';
                }
            }

            wp_send_json_success([
                'company' => $company_data
            ]);
        } catch (\Exception $e) {
            \wp_send_json_error('Erreur interne lors de la récupération des données entreprise');
        }
    }

    /**
     * Formate une adresse manuellement pour éviter l'autoloading WooCommerce
     */
    private function formatAddress($order, $type = 'billing')
    {
        $address_parts = array();

        $company = $type === 'billing' ? $order->get_billing_company() : $order->get_shipping_company();
        if (!empty($company)) {
            $address_parts[] = $company;
        }

        $first_name = $type === 'billing' ? $order->get_billing_first_name() : $order->get_shipping_first_name();
        $last_name = $type === 'billing' ? $order->get_billing_last_name() : $order->get_shipping_last_name();
        if (!empty($first_name) || !empty($last_name)) {
            $address_parts[] = trim($first_name . ' ' . $last_name);
        }

        $address_1 = $type === 'billing' ? $order->get_billing_address_1() : $order->get_shipping_address_1();
        if (!empty($address_1)) {
            $address_parts[] = $address_1;
        }

        $address_2 = $type === 'billing' ? $order->get_billing_address_2() : $order->get_shipping_address_2();
        if (!empty($address_2)) {
            $address_parts[] = $address_2;
        }

        $city = $type === 'billing' ? $order->get_billing_city() : $order->get_shipping_city();
        $postcode = $type === 'billing' ? $order->get_billing_postcode() : $order->get_shipping_postcode();
        $city_line = trim($city . ' ' . $postcode);
        if (!empty($city_line)) {
            $address_parts[] = $city_line;
        }

        $country = $type === 'billing' ? $this->getCountryName($order->get_billing_country()) : $this->getCountryName($order->get_shipping_country());
        if (!empty($country)) {
            $address_parts[] = $country;
        }

        return implode("\n", $address_parts);
    }

    /**
     * Get country name from country code
     */
    private function getCountryName($country_code) {
        if (empty($country_code)) {
            return '';
        }
        
        // Use WooCommerce countries if available
        $wc_instance = WC();
        if (function_exists('WC') && $wc_instance !== null && is_object($wc_instance) && isset($wc_instance->countries) && is_object($wc_instance->countries) && isset($wc_instance->countries->countries) && is_array($wc_instance->countries->countries)) {
            $countries = $wc_instance->countries->countries;
            return isset($countries[$country_code]) ? $countries[$country_code] : $country_code;
        }
        
        return $country_code;
    }
}








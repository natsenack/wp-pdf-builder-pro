<?php
/**
 * PDF Builder Pro - GDPR Compliance Manager
 * Gestionnaire de conformité RGPD
 *
 * @package PDF_Builder_Pro
 * @since 1.6.11
 */

namespace PDF_Builder\Utilities;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe pour gérer la conformité RGPD
 */
class PDF_Builder_GDPR_Manager {

    /**
     * Instance unique (Singleton)
     */
    private static $instance = null;

    /**
     * Options RGPD
     */
    private $gdpr_options = [];

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        $this->init_hooks();
        $this->load_gdpr_options();
    }

    /**
     * Obtenir l'instance unique
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // Hooks d'administration - intégré dans la page settings
        \add_action('admin_enqueue_scripts', [$this, 'enqueue_gdpr_scripts']);

        // Hooks AJAX pour la gestion des consentements
        \add_action('wp_ajax_pdf_builder_save_consent', [$this, 'ajax_save_consent']);
        \add_action('wp_ajax_pdf_builder_revoke_consent', [$this, 'ajax_revoke_consent']);
        \add_action('wp_ajax_pdf_builder_load_gdpr_preferences', [$this, 'ajax_load_gdpr_preferences']);
        \add_action('wp_ajax_pdf_builder_save_gdpr_preferences', [$this, 'ajax_save_gdpr_preferences']);
        \add_action('wp_ajax_pdf_builder_export_user_data', [$this, 'ajax_export_user_data']);
        \add_action('wp_ajax_pdf_builder_delete_user_data', [$this, 'ajax_delete_user_data']);

        // Hooks pour les données utilisateur
        \add_action('wp_ajax_pdf_builder_request_data_portability', [$this, 'ajax_request_data_portability']);
        \add_action('wp_ajax_pdf_builder_get_consent_status', [$this, 'ajax_get_consent_status']);
        \add_action('wp_ajax_pdf_builder_save_gdpr_settings', [$this, 'ajax_save_gdpr_settings']);
        \add_action('wp_ajax_pdf_builder_view_consent_status', [$this, 'ajax_view_consent_status']);
        \add_action('wp_ajax_pdf_builder_refresh_audit_log', [$this, 'ajax_refresh_audit_log']);
        \add_action('wp_ajax_pdf_builder_export_audit_log', [$this, 'ajax_export_audit_log']);

        // Hooks de nettoyage automatique
        \add_action('wp_scheduled_delete', [$this, 'cleanup_expired_data']);

        // Hooks pour les logs d'audit
        \add_action('init', [$this, 'init_audit_logging']);
    }

    /**
     * Charger les options RGPD
     */
    private function load_gdpr_options() {
        $this->gdpr_options = pdf_builder_get_option('pdf_builder_gdpr', [
            'consent_required' => true,
            'consent_types' => [
                'analytics' => true,
                'templates' => true,
                'marketing' => false
            ],
            'data_retention_days' => 2555, // 7 ans en jours (durée légale RGPD)
            'audit_enabled' => true,
            'encryption_enabled' => true
        ]);
    }

    /**
     * Sauvegarder les options RGPD
     */
    private function save_gdpr_options() {
        pdf_builder_update_option('pdf_builder_gdpr', $this->gdpr_options);
    }

    /**
     * Enqueue les scripts et styles RGPD
     */
    public function enqueue_gdpr_scripts($hook) {
        // Charger seulement sur la page des paramètres
        if ($hook !== 'toplevel_page_pdf-builder-pro' && strpos($hook, 'pdf-builder') === false) {
            return;
        }

        // Enqueue les scripts et styles RGPD seulement si les fichiers existent
        $gdpr_js = plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/js/gdpr.js';
        if (file_exists($gdpr_js)) {
            wp_enqueue_script('pdf-builder-gdpr', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/gdpr.js', ['jquery'], PDF_BUILDER_PRO_VERSION, true);
        }
        
        $gdpr_css = plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/css/gdpr.css';
        if (file_exists($gdpr_css)) {
            wp_enqueue_style('pdf-builder-gdpr', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/gdpr.css', [], PDF_BUILDER_PRO_VERSION);
        }

        wp_localize_script('pdf-builder-gdpr', 'pdfBuilderGDPR', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_gdpr'),
            'strings' => [
                'loading' => __('Chargement...', 'pdf-builder-pro'),
                'error' => __('Erreur', 'pdf-builder-pro'),
                'success' => __('Succès', 'pdf-builder-pro'),
                'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer toutes vos données ? Cette action est irréversible.', 'pdf-builder-pro'),
                'confirm_revoke' => __('Êtes-vous sûr de vouloir révoquer ce consentement ?', 'pdf-builder-pro')
            ]
        ]);
    }


    /**
     * AJAX - Sauvegarder un consentement
     */
    public function ajax_save_consent() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();
        $consent_type = sanitize_text_field($_POST['consent_type']);
        $granted = (bool) $_POST['granted'];

        // Sauvegarder le consentement
        $this->save_user_consent($user_id, $consent_type, $granted);

        // Logger l'action
        $this->log_audit_action($user_id, $granted ? 'consent_granted' : 'consent_revoked', 'consent', $consent_type);

        \wp_send_json_success(['message' => __('Consentement sauvegardé.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Révoquer un consentement
     */
    public function ajax_revoke_consent() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();
        $consent_type = sanitize_text_field($_POST['consent_type']);

        // Révoquer le consentement
        $this->revoke_user_consent($user_id, $consent_type);

        // Logger l'action
        $this->log_audit_action($user_id, 'consent_revoked', 'consent', $consent_type);

        \wp_send_json_success(['message' => __('Consentement révoqué.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Charger les préférences GDPR
     */
    public function ajax_load_gdpr_preferences() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();

        // Récupérer tous les types de consentements disponibles
        $consent_types = array_keys($this->gdpr_options['consent_types']);
        $preferences = [];

        // Récupérer le statut de chaque consentement
        foreach ($consent_types as $consent_type) {
            $preferences[$consent_type] = $this->is_consent_granted($user_id, $consent_type);
        }

        \wp_send_json_success(['preferences' => $preferences]);
    }

    /**
     * AJAX - Sauvegarder les préférences GDPR
     */
    public function ajax_save_gdpr_preferences() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();
        $preferences = $_POST['preferences'] ?? [];

        if (!is_array($preferences)) {
            wp_send_json_error(['message' => __('Données invalides.', 'pdf-builder-pro')]);
            return;
        }

        // Sauvegarder chaque préférence
        foreach ($preferences as $consent_type => $granted) {
            $this->save_user_consent($user_id, sanitize_text_field($consent_type), (bool) $granted);
        }

        // Logger l'action
        $this->log_audit_action($user_id, 'preferences_saved', 'gdpr_preferences', count($preferences) . ' preferences');

        \wp_send_json_success(['message' => __('Préférences sauvegardées.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Exporter les données utilisateur
     */
    public function ajax_export_user_data() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();
        $format = sanitize_text_field($_POST['format'] ?? 'json');

        // Récupérer toutes les données utilisateur
        $user_data = $this->get_user_data($user_id);

        // Créer le fichier selon le format demandé
        $export_result = $this->create_user_data_export($user_data, $user_id, $format);

        if (\is_wp_error($export_result)) {
            
            wp_send_json_error(['message' => $export_result->get_error_message()]);
            return;
        }

        // Logger l'action avec plus de détails
        $this->log_audit_action($user_id, 'data_exported', 'user_data', $format . ' (' . $export_result['mime_type'] . ')');

        

        \wp_send_json_success([
            // translators: %s: export file format (e.g. CSV, JSON)
            'message' => sprintf(__('Données exportées avec succès au format %s.', 'pdf-builder-pro'), strtoupper($format)),
            'download_url' => $export_result['download_url'],
            'filename' => $export_result['filename'],
            'mime_type' => $export_result['mime_type']
        ]);
    }

    /**
     * Créer un export des données utilisateur dans le format spécifié
     */
    private function create_user_data_export($user_data, $user_id, $format = 'json') {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/pdf-builder-exports';

        // Créer le dossier s'il n'existe pas
        wp_mkdir_p($export_dir);

        $timestamp = date('Y-m-d-H-i-s');
        $filename = "pdf-builder-user-data-{$user_id}-{$timestamp}.{$format}";
        $file_path = $export_dir . '/' . $filename;

        $content = '';
        $mime_type = 'text/plain';

        switch ($format) {
            case 'html':
                $content = $this->convert_to_html($user_data);
                $mime_type = 'text/html';
                break;

            default:
                return new WP_Error('invalid_format', __('Format d\'export non supporté.', 'pdf-builder-pro'));
        }

        // Écrire le fichier
        if (file_put_contents($file_path, $content) === false) {
            return new WP_Error('file_write_error', __('Erreur lors de l\'écriture du fichier d\'export.', 'pdf-builder-pro'));
        }

        return [
            'filename' => $filename,
            'file_path' => $file_path,
            'download_url' => $upload_dir['baseurl'] . '/pdf-builder-exports/' . $filename,
            'mime_type' => $mime_type,
            'format' => $format
        ];
    }





    /**
     * Convertir les données utilisateur en HTML (format lisible pour le grand public)
     */
    private function convert_to_html($user_data) {
        $user_info = wp_get_current_user();
        $export_date = date_i18n('d/m/Y H:i:s', current_time('timestamp'));

        $html = '<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Données Personnelles - Export RGPD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background: #f8f9fa;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .header {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            color: #333;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 2em;
            margin-bottom: 10px;
            font-weight: 600;
            color: #2c3e50;
        }

        .header .subtitle {
            font-size: 1em;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .header .actions {
            margin-top: 20px;
            text-align: center;
        }

        .print-button {
            background: #007cba;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .print-button:hover {
            background: #005a87;
        }

        .print-button:active {
            background: #004c6d;
        }

        .user-info {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .user-info-item {
            text-align: center;
        }

        .user-info-label {
            font-size: 0.9em;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .user-info-value {
            font-weight: 600;
            color: #495057;
        }

        .privacy-notice {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 20px 30px;
            margin: 0;
        }

        .privacy-notice h3 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .content {
            padding: 0 30px 30px;
        }

        .section {
            margin-bottom: 25px;
            background: #f8f9fa;
            border-radius: 6px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        .section h2 {
            background: #fff;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
            margin: 0;
            padding: 15px 20px;
            font-size: 1.2em;
            font-weight: 600;
        }

        .section-content {
            padding: 20px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }

        .data-item {
            background: white;
            border-radius: 4px;
            padding: 15px;
            border: 1px solid #e9ecef;
        }

        .data-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9em;
        }

        .data-value {
            background: #f8f9fa;
            padding: 10px 12px;
            border-radius: 3px;
            border-left: 3px solid #007cba;
            word-wrap: break-word;
            font-family: "SF Mono", Monaco, "Cascadia Code", monospace;
            font-size: 0.85em;
            line-height: 1.4;
        }

        .array-value {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 3px;
            border-left: 3px solid #28a745;
        }

        .array-item {
            margin-bottom: 8px;
            padding: 8px 10px;
            background: white;
            border-radius: 3px;
            border: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .array-item:last-child {
            margin-bottom: 0;
        }

        .array-key {
            font-weight: 600;
            color: #495057;
            font-size: 0.85em;
        }

        .array-value {
            color: #6c757d;
            font-family: "SF Mono", Monaco, "Cascadia Code", monospace;
            font-size: 0.8em;
        }

        .empty-notice {
            text-align: center;
            color: #6c757d;
            font-style: italic;
            padding: 15px;
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 25px 30px;
            text-align: center;
            margin-top: 30px;
        }

        .footer h3 {
            margin-bottom: 10px;
            font-weight: 400;
            font-size: 1.1em;
        }

        .footer p {
            margin-bottom: 8px;
            opacity: 0.8;
            font-size: 0.9em;
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .footer-link {
            color: #3498db;
            text-decoration: none;
            padding: 6px 12px;
            border-radius: 4px;
            background: rgba(255,255,255,0.1);
            font-size: 0.85em;
            transition: background 0.2s ease;
        }

        .footer-link:hover {
            background: rgba(255,255,255,0.2);
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .header h1 {
                font-size: 1.8em;
            }

            .user-info {
                flex-direction: column;
                gap: 15px;
            }

            .data-grid {
                grid-template-columns: 1fr;
            }

            .footer-links {
                flex-direction: column;
                align-items: center;
            }
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }

            .container {
                box-shadow: none;
                border-radius: 0;
                max-width: none;
            }

            .header .actions {
                display: none;
            }

            .footer-links {
                display: none;
            }

            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
        <div class="pdfb-container">
        <div class="header">
            <h1>Mes Données Personnelles</h1>
            <div class="subtitle">Export RGPD - Document officiel</div>
            <div class="date">Généré le ' . $export_date . '</div>
            <div class="actions">
                <button onclick="window.print()" class="print-button">
                    🖨️ Imprimer le document
                </button>
            </div>
        </div>            <div class="user-info">
                <div class="user-info-item">
                    <div class="user-info-label">Utilisateur</div>
                    <div class="user-info-value">' . esc_html($user_info->display_name) . '</div>
                </div>
                <div class="user-info-item">
                    <div class="user-info-label">Email</div>
                    <div class="user-info-value">' . esc_html($user_info->user_email) . '</div>
                </div>
                <div class="user-info-item">
                    <div class="user-info-label">ID Utilisateur</div>
                    <div class="user-info-value">#' . esc_html($user_info->ID) . '</div>
                </div>
            </div>

            <div class="privacy-notice">
                <h3>Protection de vos données</h3>
                <p>Ce document contient toutes les données personnelles que nous détenons à votre sujet, conformément au Règlement Général sur la Protection des Données (RGPD). Vous avez le droit de consulter, rectifier ou supprimer ces données à tout moment.</p>
            </div>

            <div class="content">';        // Fonction récursive pour formater les données
        $format_data = function($data, $prefix = '') use (&$format_data) {
            $result = '';

            foreach ($data as $key => $value) {
                $full_key = $prefix ? $prefix . ' → ' . $key : $key;
                $display_key = ucfirst(str_replace(['_', '-'], ' ', $key));

                if (is_array($value) || is_object($value)) {
                    if (empty($value)) {
                        $result .= '<div class="data-item">
                            <div class="data-label">' . esc_html($display_key) . '</div>
                            <div class="data-value empty-notice">(Aucune donnée)</div>
                        </div>';
                    } else {
                        $result .= '<div class="data-item">
                            <div class="data-label">' . esc_html($display_key) . '</div>
                            <div class="array-value">';

                        if (is_object($value)) {
                            $value = (array) $value;
                        }

                        foreach ($value as $sub_key => $sub_value) {
                            $sub_display_key = ucfirst(str_replace(['_', '-'], ' ', $sub_key));
                            $result .= '<div class="array-item">
                                <span class="array-key">' . esc_html($sub_display_key) . '</span>
                                <span class="array-value">' . (is_array($sub_value) || is_object($sub_value) ? '(Données complexes)' : esc_html($sub_value)) . '</span>
                            </div>';
                        }

                        $result .= '</div></div>';
                    }
                } else {
                    $result .= '<div class="data-item">
                        <div class="data-label">' . esc_html($display_key) . '</div>
                        <div class="data-value">' . esc_html($value) . '</div>
                    </div>';
                }
            }

            return $result;
        };

        // Grouper les données par catégories
        $sections = [
            'Informations de base' => [],
            'Métadonnées utilisateur' => [],
            'Préférences et paramètres' => [],
            'Historique et activité' => [],
            'Données RGPD' => [],
            'Autre' => []
        ];

        foreach ($user_data as $key => $value) {
            if (strpos($key, 'user_') === 0 || in_array($key, ['ID', 'user_login', 'user_email', 'display_name'])) {
                $sections['Informations de base'][$key] = $value;
            } elseif (strpos($key, 'meta_') === 0 || strpos($key, 'wp_') === 0) {
                $sections['Métadonnées utilisateur'][$key] = $value;
            } elseif (strpos($key, 'pref') === 0 || strpos($key, 'setting') === 0 || strpos($key, 'option') === 0) {
                $sections['Préférences et paramètres'][$key] = $value;
            } elseif (strpos($key, 'consent') !== false || strpos($key, 'gdpr') !== false) {
                $sections['Données RGPD'][$key] = $value;
            } elseif (strpos($key, 'last_') === 0 || strpos($key, 'date') !== false || strpos($key, 'time') !== false) {
                $sections['Historique et activité'][$key] = $value;
            } else {
                $sections['Autre'][$key] = $value;
            }
        }

        // Générer les sections HTML
        foreach ($sections as $section_title => $section_data) {
            if (!empty($section_data)) {
                $html .= '<div class="section">
                    <h2>' . esc_html($section_title) . '</h2>
                    <div class="pdfb-section-content">
                        <div class="data-grid">
                            ' . $format_data($section_data) . '
                        </div>
                    </div>
                </div>';
            }
        }

        $html .= '</div>

        <div class="footer">
            <h3>PDF Builder Pro - Protection RGPD</h3>
            <p>Conformément au Règlement UE 2016/679 - Règlement Général sur la Protection des Données</p>
            <p>Pour toute question concernant vos données, contactez l\'administrateur du site</p>
            <div class="pdfb-footer-links">
                <a href="#" class="pdfb-footer-link">Demander rectification</a>
                <a href="#" class="pdfb-footer-link">Demander suppression</a>
                <a href="#" class="pdfb-footer-link">Contacter le DPO</a>
            </div>
        </div>
    </div>
</body>
</html>';

        return $html;
    }



    /**
     * AJAX - Supprimer les données utilisateur
     */
    public function ajax_delete_user_data() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();

        // Supprimer toutes les données utilisateur
        $this->delete_user_data($user_id);

        // Logger l'action
        $this->log_audit_action($user_id, 'data_deleted', 'user_data', 'all');

        \wp_send_json_success(['message' => __('Toutes vos données ont été supprimées.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Demander la portabilité des données
     */
    public function ajax_request_data_portability() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();
        $format = sanitize_text_field($_POST['format'] ?? 'json');

        // Créer un export dans le format demandé
        $export_data = $this->get_user_data_portable($user_id, $format);

        if (\is_wp_error($export_data)) {
            wp_send_json_error(['message' => $export_data->get_error_message()]);
            return;
        }

        // Logger l'action
        $this->log_audit_action($user_id, 'data_portability_requested', 'user_data', $format);

        \wp_send_json_success([
            'message' => __('Demande de portabilité traitée.', 'pdf-builder-pro'),
            'data' => $export_data
        ]);
    }

    /**
     * Sauvegarder le consentement d'un utilisateur
     */
    private function save_user_consent($user_id, $consent_type, $granted) {
        $consent_data = [
            'granted' => $granted,
            'timestamp' => current_time('timestamp'),
            'ip_address' => $this->get_client_ip()
        ];

        // Chiffrer les données sensibles si activé
        $data_to_store = $this->gdpr_options['encryption_enabled'] ?
            $this->encrypt_data(json_encode($consent_data)) :
            $consent_data;

        $consent_key = 'pdf_builder_consent_' . $consent_type;
        update_user_meta($user_id, $consent_key, $data_to_store);
    }

    /**
     * Révoquer le consentement d'un utilisateur
     */
    private function revoke_user_consent($user_id, $consent_type) {
        $consent_key = 'pdf_builder_consent_' . $consent_type;
        delete_user_meta($user_id, $consent_key);
    }

    /**
     * Récupérer les données d'un utilisateur
     */
    private function get_user_data($user_id) {
        $user = \get_userdata($user_id);
        $consent_data = [];

        // Récupérer tous les consentements
        foreach (['analytics', 'templates', 'marketing'] as $type) {
            $consent_key = 'pdf_builder_consent_' . $type;
            $consent_data[$type] = get_user_meta($user_id, $consent_key, true);
        }

        // Récupérer les templates utilisateur
        global $wpdb;
        $templates = $wpdb->get_results($wpdb->prepare("
            SELECT ID, post_title, post_modified, post_content
            FROM {$wpdb->posts}
            WHERE post_author = %d AND post_type = 'pdf_template'
        ", $user_id), ARRAY_A);

        // Récupérer les métadonnées des templates
        $template_meta = [];
        foreach ($templates as $template) {
            $meta = \get_post_meta($template['ID']);
            $template_meta[$template['ID']] = $meta;
        }

        // Récupérer les logs d'audit de l'utilisateur (anonymisés)
        $table_audit = $wpdb->prefix . 'pdf_builder_audit_log';
        $audit_logs = $wpdb->get_results($wpdb->prepare("
            SELECT action, data_type, created_at
            FROM {$table_audit}
            WHERE user_id = %d
            ORDER BY created_at DESC
        ", $user_id), ARRAY_A);

        // Récupérer les préférences utilisateur
        $user_preferences = get_user_meta($user_id, 'pdf_builder_user_preferences', true);
        $last_activity = get_user_meta($user_id, 'pdf_builder_last_activity', true);

        return [
            'user_info' => [
                'id' => $user->ID,
                'login' => $user->user_login,
                'email' => $user->user_email,
                'display_name' => $user->display_name,
                'registered' => $user->user_registered,
                'roles' => $user->roles
            ],
            'consents' => $consent_data,
            'templates' => $templates,
            'template_metadata' => $template_meta,
            'audit_logs' => $audit_logs,
            'user_preferences' => $user_preferences,
            'last_activity' => $last_activity,
            'export_date' => current_time('mysql'),
            'data_portability_notice' => 'Ces données sont fournies au format RGPD pour portabilité.'
        ];
    }

    /**
     * Obtenir le statut d'un consentement pour un utilisateur
     */
    public function get_user_consent_status($user_id, $consent_type) {
        $consent_key = 'pdf_builder_consent_' . $consent_type;
        $stored_data = get_user_meta($user_id, $consent_key, true);

        if (empty($stored_data)) {
            return false;
        }

        // Déchiffrer si nécessaire
        if ($this->gdpr_options['encryption_enabled'] && is_string($stored_data)) {
            $decrypted = $this->decrypt_data($stored_data);
            if ($decrypted) {
                $consent_data = json_decode($decrypted, true);
                return $consent_data ? $consent_data['granted'] : false;
            }
            return false;
        }

        // Données non chiffrées (ancien format)
        return isset($stored_data['granted']) ? $stored_data['granted'] : false;
    }

    /**
     * Vérifier si un consentement est requis et accordé
     */
    public function is_consent_granted($user_id, $consent_type) {
        // Si le consentement n'est pas requis globalement, considérer comme accordé
        if (!($this->gdpr_options['consent_types'][$consent_type] ?? false)) {
            return true;
        }

        return $this->get_user_consent_status($user_id, $consent_type);
    }

    /**
     * Obtenir la clé de chiffrement
     */
    private function get_encryption_key() {
        if (!defined('PDF_BUILDER_ENCRYPTION_KEY')) {
            // Générer une clé basée sur les salts WordPress pour la sécurité
            $salt = wp_salt('auth') . wp_salt('secure_auth') . wp_salt('logged_in') . wp_salt('nonce');
            define('PDF_BUILDER_ENCRYPTION_KEY', substr(hash('sha256', $salt), 0, 32));
        }
        return PDF_BUILDER_ENCRYPTION_KEY;
    }

    /**
     * Chiffrer des données sensibles
     */
    public function encrypt_data($data) {
        if (!$this->gdpr_options['encryption_enabled'] || empty($data)) {
            return $data;
        }

        $key = $this->get_encryption_key();
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);

        // Stocker l'IV avec les données chiffrées
        return base64_encode($iv . $encrypted);
    }

    /**
     * Déchiffrer des données sensibles
     */
    public function decrypt_data($encrypted_data) {
        if (!$this->gdpr_options['encryption_enabled'] || empty($encrypted_data)) {
            return $encrypted_data;
        }

        $key = $this->get_encryption_key();
        $data = base64_decode($encrypted_data);

        if (strlen($data) < 16) {
            return $encrypted_data; // Données corrompues
        }

        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);

        return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
    }

    /**
     * Vérifier si le chiffrement est disponible
     */
    public function is_encryption_available() {
        return function_exists('openssl_encrypt') && function_exists('openssl_decrypt');
    }

    /**
     * Supprimer les données d'un utilisateur (RGPD - Droit à l'oubli)
     */
    private function delete_user_data($user_id) {
        global $wpdb;

        // 1. Supprimer les consentements utilisateur
        foreach (['analytics', 'templates', 'marketing'] as $type) {
            $consent_key = 'pdf_builder_consent_' . $type;
            delete_user_meta($user_id, $consent_key);
        }

        // 2. Supprimer les templates créés par l'utilisateur
        $wpdb->delete($wpdb->posts, [
            'post_author' => $user_id,
            'post_type' => 'pdf_template'
        ]);

        // 3. Supprimer toutes les métadonnées liées à l'utilisateur
        $wpdb->delete($wpdb->postmeta, [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key,WordPress.DB.SlowDBQuery.slow_db_query_meta_value
            'meta_key' => '_pdf_template_author',
            'meta_value' => $user_id
        ]);

        // 4. Supprimer les logs d'audit de cet utilisateur (RGPD compliance)
        $table_audit = $wpdb->prefix . 'pdf_builder_audit_log';
        $wpdb->delete($table_audit, ['user_id' => $user_id]);

        // 5. Supprimer les données de sauvegarde utilisateur si elles existent
        $backup_dir = WP_CONTENT_DIR . '/pdf-builder-backups/' . $user_id;
        if (is_dir($backup_dir)) {
            $this->delete_directory_recursive($backup_dir);
        }

        // 6. Supprimer les fichiers temporaires de l'utilisateur
        $temp_files = glob(WP_CONTENT_DIR . '/pdf-builder-temp/*' . $user_id . '*');
        foreach ($temp_files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        // 7. Supprimer les préférences utilisateur personnalisées
        delete_user_meta($user_id, 'pdf_builder_user_preferences');
        delete_user_meta($user_id, 'pdf_builder_last_activity');
        delete_user_meta($user_id, 'pdf_builder_session_data');
    }

    /**
     * Récupérer les données utilisateur pour portabilité
     */
    private function get_user_data_portable($user_id, $format = 'json') {
        $data = $this->get_user_data($user_id);

        if ($format === 'xml') {
            // Convertir en XML si demandé
            return $this->array_to_xml($data);
        }

        return $data;
    }

    /**
     * Logger une action d'audit
     */
    private function log_audit_action($user_id, $action, $data_type, $details = '') {
        if (!$this->gdpr_options['audit_enabled']) {
            return;
        }

        global $wpdb;
        $table_audit = $wpdb->prefix . 'pdf_builder_audit_log';

        $wpdb->insert($table_audit, [
            'user_id' => $user_id,
            'action' => $action,
            'data_type' => $data_type,
            'details' => $details,
            'ip_address' => $this->get_client_ip(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'created_at' => current_time('mysql')
        ]);
    }

    /**
     * Nettoyer les données expirées
     */
    public function cleanup_expired_data() {
        $retention_days = $this->gdpr_options['data_retention_days'];
        $cutoff_date = date('Y-m-d H:i:s', strtotime("-{$retention_days} days"));

        global $wpdb;
        $table_audit = $wpdb->prefix . 'pdf_builder_audit_log';

        // Supprimer les anciens logs d'audit
        $deleted_audit = $wpdb->delete($table_audit, [
            'created_at <' => $cutoff_date
        ]);

        // Supprimer les anciens consentements expirés (optionnel - à configurer)
        // Note: Les consentements sont conservés selon la politique de l'utilisateur

        // Log de nettoyage
        if ($this->gdpr_options['audit_enabled']) {
            $this->log_audit_action(0, 'data_cleanup', 'system', "Audit logs deleted: {$deleted_audit}");
        }
    }

    /**
     * Programmer le nettoyage automatique
     */
    public function schedule_data_cleanup() {
        if (!\wp_next_scheduled('pdf_builder_gdpr_cleanup')) {
            \wp_schedule_event(time(), 'daily', 'pdf_builder_gdpr_cleanup');
        }
    }

    /**
     * Anonymiser les anciennes données utilisateur
     */
    private function anonymize_old_user_data($cutoff_date) {
        global $wpdb;

        // Anonymiser les templates anciens
        $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->posts}
            SET post_title = CONCAT('Anonymized Template ', ID),
                post_content = ''
            WHERE post_type = 'pdf_template'
            AND post_modified < %s
        ", $cutoff_date));
    }

    /**
     * Initialiser le logging d'audit
     */
    public function init_audit_logging() {
        // Créer la table d'audit si elle n'existe pas
        $this->create_audit_table();
    }

    /**
     * Créer la table d'audit
     */
    private function create_audit_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pdf_builder_audit_log';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            action varchar(100) NOT NULL,
            data_type varchar(100) NOT NULL,
            details text,
            ip_address varchar(45),
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Obtenir l'adresse IP du client
     */
    private function get_client_ip() {
        $ip_headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ip_headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Prendre la première IP si plusieurs sont présentes
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                // Valider l'IP
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Convertir un array en XML
     */
    private function array_to_xml($data, $root_element = 'data') {
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\"?><$root_element></$root_element>");

        $this->array_to_xml_recursive($data, $xml);

        return $xml->asXML();
    }

    /**
     * Fonction récursive pour convertir array en XML
     */
    private function array_to_xml_recursive($data, &$xml) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key;
                }
                $subnode = $xml->addChild($key);
                $this->array_to_xml_recursive($value, $subnode);
            } else {
                $xml->addChild($key, htmlspecialchars($value));
            }
        }
    }

    /**
     * AJAX - Obtenir le statut des consentements
     */
    public function ajax_get_consent_status() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();
        $consents = [];

        foreach (['analytics', 'templates', 'marketing'] as $type) {
            $consents[$type] = [
                'granted' => $this->get_user_consent_status($user_id, $type),
                'timestamp' => null,
                'encrypted' => $this->gdpr_options['encryption_enabled']
            ];
        }

        \wp_send_json_success(['consents' => $consents]);
    }

    /**
     * AJAX - Voir le statut des consentements (HTML formaté)
     */
    public function ajax_view_consent_status() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();

        ob_start();
        ?>
        <table class="widefat striped" style="margin-top: 10px;">
            <thead>
                <tr>
                    <th>Type de consentement</th>
                    <th>Statut</th>
                    <th>Date de consentement</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $consent_types = [
                    'analytics' => 'Analytics & Suivi',
                    'templates' => 'Sauvegarde des Templates',
                    'marketing' => 'Communications Marketing'
                ];

                foreach ($consent_types as $type => $label) {
                    $status = $this->get_user_consent_status($user_id, $type);
                    $consent_data = get_user_meta($user_id, 'pdf_builder_consent_' . $type, true);

                    $status_text = $status ? '✅ Accordé' : '❌ Refusé';
                    $status_class = $status ? 'text-success' : 'text-danger';
                    $date_text = 'Non défini';

                    if (is_array($consent_data) && isset($consent_data['timestamp'])) {
                        $date_text = date_i18n('d/m/Y H:i', $consent_data['timestamp']);
                    }
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($label); ?></strong></td>
                        <td class="<?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_text); ?></td>
                        <td><?php echo esc_html($date_text); ?></td>
                        <td>
                            <?php if ($status): ?>
                                <button type="button" class="button button-small button-secondary revoke-consent"
                                        data-consent-type="<?php echo esc_attr($type); ?>">
                                    Révoquer
                                </button>
                            <?php else: ?>
                                <button type="button" class="button button-small button-primary grant-consent"
                                        data-consent-type="<?php echo esc_attr($type); ?>">
                                    Accorder
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <p style="margin-top: 15px; color: #666; font-size: 12px;">
            <em>💡 Vous pouvez modifier vos consentements à tout moment. Ces informations sont stockées de manière sécurisée et conforme au RGPD.</em>
        </p>
        <?php

        $html = ob_get_clean();
        \wp_send_json_success(['consent_html' => $html]);
    }

    /**
     * AJAX - Sauvegarder les paramètres RGPD
     */
    public function ajax_save_gdpr_settings() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        if (!\current_user_can('manage_options')) {
            \wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        // Sauvegarder les paramètres de consentement
        $this->gdpr_options['consent_required'] = isset($_POST['consent_required']);
        $this->gdpr_options['consent_types'] = [
            'analytics' => isset($_POST['consent_types']['analytics']),
            'templates' => isset($_POST['consent_types']['templates']),
            'marketing' => isset($_POST['consent_types']['marketing'])
        ];

        $this->save_gdpr_options();

        \wp_send_json_success(['message' => __('Paramètres RGPD sauvegardés.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Sauvegarder les paramètres de sécurité
     */
    public function ajax_save_gdpr_security() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        if (!\current_user_can('manage_options')) {
            \wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        // Sauvegarder les paramètres de sécurité
        $this->gdpr_options['encryption_enabled'] = isset($_POST['encryption_enabled']);
        $this->gdpr_options['data_retention_days'] = \intval($_POST['data_retention_days'] ?? 2555);
        $this->gdpr_options['audit_enabled'] = isset($_POST['audit_enabled']);

        $this->save_gdpr_options();

        \wp_send_json_success(['message' => __('Paramètres de sécurité sauvegardés.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Actualiser le journal d'audit
     */
    public function ajax_refresh_audit_log() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        if (!\current_user_can('manage_options')) {
            \wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        global $wpdb;
        $table_audit = $wpdb->prefix . 'pdf_builder_audit_log';

        $audit_logs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_audit
            ORDER BY created_at DESC
            LIMIT 50
        "), ARRAY_A);

        ob_start();
        if (empty($audit_logs)) {
            echo '<tr><td colspan="5">' . __('Aucun journal d\'audit disponible.', 'pdf-builder-pro') . '</td></tr>';
        } else {
            foreach ($audit_logs as $log) {
                echo '<tr>';
                echo '<td>' . esc_html(date_i18n('d/m/Y H:i', strtotime($log['created_at']))) . '</td>';
                echo '<td>' . esc_html($log['user_id'] ? \get_userdata($log['user_id'])->display_name : 'Système') . '</td>';
                echo '<td>' . esc_html($log['action']) . '</td>';
                echo '<td>' . esc_html($log['data_type']) . '</td>';
                echo '<td>' . esc_html($log['ip_address']) . '</td>';
                echo '</tr>';
            }
        }
        $html = ob_get_clean();

        \wp_send_json_success(['html' => $html]);
    }

    /**
     * AJAX - Exporter le journal d'audit
     */
    public function ajax_export_audit_log() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        if (!\current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes', 'pdf-builder-pro')]);
            return;
        }

        $start_date = sanitize_text_field($_GET['start_date'] ?? '');
        $end_date = sanitize_text_field($_GET['end_date'] ?? '');

        global $wpdb;
        $table_audit = $wpdb->prefix . 'pdf_builder_audit_log';

        $where = '';
        $params = [];

        if ($start_date) {
            $where .= ' AND created_at >= %s';
            $params[] = $start_date . ' 00:00:00';
        }

        if ($end_date) {
            $where .= ' AND created_at <= %s';
            $params[] = $end_date . ' 23:59:59';
        }

        $audit_logs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_audit
            WHERE 1=1 $where
            ORDER BY created_at DESC
        ", $params), ARRAY_A);

        // Créer le répertoire d'export s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/pdf-builder-exports';
        wp_mkdir_p($export_dir);

        // Créer un fichier CSV
        $timestamp = date('Y-m-d-H-i-s');
        $filename = "audit-log-{$timestamp}.csv";
        $file_path = $export_dir . '/' . $filename;

        $csv_content = "Date,Utilisateur,Action,Données concernées,IP\n";

        foreach ($audit_logs as $log) {
            $csv_content .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $log['created_at'],
                $log['user_id'] ? \get_userdata($log['user_id'])->display_name : 'Système',
                $log['action'],
                $log['data_type'],
                $log['ip_address']
            );
        }

        // Écrire le fichier
        if (file_put_contents($file_path, $csv_content) === false) {
            wp_send_json_error(['message' => __('Erreur lors de la création du fichier d\'export.', 'pdf-builder-pro')]);
            return;
        }

        // Logger l'action
        $this->log_audit_action(get_current_user_id(), 'audit_log_exported', 'audit_logs', 'csv');

        \wp_send_json_success([
            'message' => __('Logs d\'audit exportés avec succès.', 'pdf-builder-pro'),
            'download_url' => $upload_dir['baseurl'] . '/pdf-builder-exports/' . $filename,
            'filename' => $filename
        ]);
    }

    /**
     * Supprimer récursivement un répertoire
     */
    private function delete_directory_recursive($dir) {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                $this->delete_directory_recursive($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }
}






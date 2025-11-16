<?php
/**
 * PDF Builder Pro - GDPR Compliance Manager
 * Gestionnaire de conformité RGPD
 *
 * @package PDF_Builder_Pro
 * @since 1.6.11
 */

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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_gdpr_scripts']);

        // Hooks AJAX pour la gestion des consentements
        add_action('wp_ajax_pdf_builder_save_consent', [$this, 'ajax_save_consent']);
        add_action('wp_ajax_pdf_builder_revoke_consent', [$this, 'ajax_revoke_consent']);
        add_action('wp_ajax_pdf_builder_export_user_data', [$this, 'ajax_export_user_data']);
        add_action('wp_ajax_pdf_builder_delete_user_data', [$this, 'ajax_delete_user_data']);

        // Hooks pour les données utilisateur
        add_action('wp_ajax_pdf_builder_request_data_portability', [$this, 'ajax_request_data_portability']);
        add_action('wp_ajax_pdf_builder_get_consent_status', [$this, 'ajax_get_consent_status']);
        add_action('wp_ajax_pdf_builder_save_gdpr_settings', [$this, 'ajax_save_gdpr_settings']);
        add_action('wp_ajax_pdf_builder_save_gdpr_security', [$this, 'ajax_save_gdpr_security']);
        add_action('wp_ajax_pdf_builder_refresh_audit_log', [$this, 'ajax_refresh_audit_log']);
        add_action('wp_ajax_pdf_builder_export_audit_log', [$this, 'ajax_export_audit_log']);

        // Hooks de nettoyage automatique
        add_action('wp_scheduled_delete', [$this, 'cleanup_expired_data']);

        // Hooks pour les logs d'audit
        add_action('init', [$this, 'init_audit_logging']);
    }

    /**
     * Charger les options RGPD
     */
    private function load_gdpr_options() {
        $this->gdpr_options = get_option('pdf_builder_gdpr', [
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
        update_option('pdf_builder_gdpr', $this->gdpr_options);
    }

    /**
     * Enqueue les scripts et styles RGPD
     */
    public function enqueue_gdpr_scripts($hook) {
        // Charger seulement sur la page des paramètres
        if ($hook !== 'pdf-builder_page_pdf-builder-settings') {
            return;
        }

        wp_enqueue_script('pdf-builder-gdpr', PDF_BUILDER_PRO_ASSETS_URL . 'js/gdpr.js', ['jquery'], PDF_BUILDER_PRO_VERSION, true);
        wp_enqueue_style('pdf-builder-gdpr', PDF_BUILDER_PRO_ASSETS_URL . 'css/gdpr.css', [], PDF_BUILDER_PRO_VERSION);

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

        wp_send_json_success(['message' => __('Consentement sauvegardé.', 'pdf-builder-pro')]);
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

        wp_send_json_success(['message' => __('Consentement révoqué.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Exporter les données utilisateur
     */
    public function ajax_export_user_data() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        $user_id = get_current_user_id();

        // Récupérer toutes les données utilisateur
        $user_data = $this->get_user_data($user_id);

        // Créer un fichier JSON
        $filename = 'pdf-builder-user-data-' . $user_id . '-' . date('Y-m-d') . '.json';
        $file_path = wp_upload_dir()['basedir'] . '/pdf-builder-exports/' . $filename;

        // Créer le dossier s'il n'existe pas
        wp_mkdir_p(dirname($file_path));

        // Écrire le fichier
        file_put_contents($file_path, json_encode($user_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        // Logger l'action
        $this->log_audit_action($user_id, 'data_exported', 'user_data', 'all');

        wp_send_json_success([
            'message' => __('Données exportées avec succès.', 'pdf-builder-pro'),
            'download_url' => wp_upload_dir()['baseurl'] . '/pdf-builder-exports/' . $filename
        ]);
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

        wp_send_json_success(['message' => __('Toutes vos données ont été supprimées.', 'pdf-builder-pro')]);
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

        // Logger l'action
        $this->log_audit_action($user_id, 'data_portability_requested', 'user_data', $format);

        wp_send_json_success([
            'message' => __('Demande de portabilité traitée.', 'pdf-builder-pro'),
            'data' => $export_data
        ]);
    }

    /**
     * Sauvegarder le consentement d'un utilisateur
     */
    private function save_user_consent($user_id, $consent_type, $granted) {
        $consent_key = 'pdf_builder_consent_' . $consent_type;
        update_user_meta($user_id, $consent_key, [
            'granted' => $granted,
            'timestamp' => current_time('timestamp'),
            'ip_address' => $this->get_client_ip()
        ]);
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
        $user = get_userdata($user_id);
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
            $meta = get_post_meta($template['ID']);
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
        $consent_data = get_user_meta($user_id, $consent_key, true);

        return $consent_data ? $consent_data['granted'] : false;
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
        $wpdb->delete($wpdb->postmeta, [
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

        // Supprimer les anciens logs d'audit
        $table_audit = $wpdb->prefix . 'pdf_builder_audit_log';
        $wpdb->query($wpdb->prepare("
            DELETE FROM $table_audit
            WHERE created_at < %s
        ", $cutoff_date));

        // Anonymiser les anciennes données utilisateur
        $this->anonymize_old_user_data($cutoff_date);
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
            $consent_key = 'pdf_builder_consent_' . $type;
            $consents[$type] = get_user_meta($user_id, $consent_key, true) ?: ['granted' => false, 'timestamp' => null];
        }

        wp_send_json_success(['consents' => $consents]);
    }

    /**
     * AJAX - Sauvegarder les paramètres RGPD
     */
    public function ajax_save_gdpr_settings() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        // Sauvegarder les paramètres de consentement
        $this->gdpr_options['consent_required'] = isset($_POST['consent_required']);
        $this->gdpr_options['consent_types'] = [
            'analytics' => isset($_POST['consent_types']['analytics']),
            'templates' => isset($_POST['consent_types']['templates']),
            'marketing' => isset($_POST['consent_types']['marketing'])
        ];

        $this->save_gdpr_options();

        wp_send_json_success(['message' => __('Paramètres RGPD sauvegardés.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Sauvegarder les paramètres de sécurité
     */
    public function ajax_save_gdpr_security() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        // Sauvegarder les paramètres de sécurité
        $this->gdpr_options['encryption_enabled'] = isset($_POST['encryption_enabled']);
        $this->gdpr_options['data_retention_days'] = intval($_POST['data_retention_days'] ?? 2555);
        $this->gdpr_options['audit_enabled'] = isset($_POST['audit_enabled']);

        $this->save_gdpr_options();

        wp_send_json_success(['message' => __('Paramètres de sécurité sauvegardés.', 'pdf-builder-pro')]);
    }

    /**
     * AJAX - Actualiser le journal d'audit
     */
    public function ajax_refresh_audit_log() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
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
                echo '<td>' . esc_html($log['user_id'] ? get_userdata($log['user_id'])->display_name : 'Système') . '</td>';
                echo '<td>' . esc_html($log['action']) . '</td>';
                echo '<td>' . esc_html($log['data_type']) . '</td>';
                echo '<td>' . esc_html($log['ip_address']) . '</td>';
                echo '</tr>';
            }
        }
        $html = ob_get_clean();

        wp_send_json_success(['html' => $html]);
    }

    /**
     * AJAX - Exporter le journal d'audit
     */
    public function ajax_export_audit_log() {
        check_ajax_referer('pdf_builder_gdpr', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
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

        // Créer un fichier CSV
        $filename = 'audit-log-' . date('Y-m-d') . '.csv';
        $csv_content = "Date,Utilisateur,Action,Données concernées,IP\n";

        foreach ($audit_logs as $log) {
            $csv_content .= sprintf(
                "%s,%s,%s,%s,%s\n",
                $log['created_at'],
                $log['user_id'] ? get_userdata($log['user_id'])->display_name : 'Système',
                $log['action'],
                $log['data_type'],
                $log['ip_address']
            );
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csv_content));

        echo $csv_content;
        exit;
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
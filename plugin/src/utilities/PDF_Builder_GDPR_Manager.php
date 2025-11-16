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
        // Hooks d'administration
        add_action('admin_menu', [$this, 'add_gdpr_menu']);
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
     * Ajouter le menu RGPD
     */
    public function add_gdpr_menu() {
        add_submenu_page(
            'pdf-builder-pro',
            __('RGPD - PDF Builder Pro', 'pdf-builder-pro'),
            __('🔒 RGPD', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-gdpr',
            [$this, 'render_gdpr_page']
        );
    }

    /**
     * Enqueue les scripts et styles RGPD
     */
    public function enqueue_gdpr_scripts($hook) {
        if ($hook !== 'pdf-builder_page_pdf-builder-gdpr') {
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
     * Rendre la page RGPD
     */
    public function render_gdpr_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'pdf-builder-pro'));
        }

        ?>
        <div class="wrap">
            <h1><?php _e('Conformité RGPD - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

            <div class="gdpr-tabs">
                <div class="gdpr-tab active" data-tab="consent"><?php _e('Gestion Consentements', 'pdf-builder-pro'); ?></div>
                <div class="gdpr-tab" data-tab="rights"><?php _e('Droits Utilisateur', 'pdf-builder-pro'); ?></div>
                <div class="gdpr-tab" data-tab="audit"><?php _e('Audit & Traçabilité', 'pdf-builder-pro'); ?></div>
                <div class="gdpr-tab" data-tab="security"><?php _e('Sécurité Données', 'pdf-builder-pro'); ?></div>
            </div>

            <div class="gdpr-content">
                <!-- Onglet Gestion Consentements -->
                <div class="gdpr-tab-content active" id="consent-tab">
                    <?php $this->render_consent_management(); ?>
                </div>

                <!-- Onglet Droits Utilisateur -->
                <div class="gdpr-tab-content" id="rights-tab">
                    <?php $this->render_user_rights(); ?>
                </div>

                <!-- Onglet Audit & Traçabilité -->
                <div class="gdpr-tab-content" id="audit-tab">
                    <?php $this->render_audit_trail(); ?>
                </div>

                <!-- Onglet Sécurité Données -->
                <div class="gdpr-tab-content" id="security-tab">
                    <?php $this->render_security_settings(); ?>
                </div>
            </div>
        </div>

        <script>
        jQuery(document).ready(function($) {
            $('.gdpr-tab').on('click', function() {
                var tab = $(this).data('tab');
                $('.gdpr-tab').removeClass('active');
                $('.gdpr-tab-content').removeClass('active');
                $(this).addClass('active');
                $('#' + tab + '-tab').addClass('active');
            });
        });
        </script>
        <?php
    }

    /**
     * Rendre la gestion des consentements
     */
    private function render_consent_management() {
        $consent_types = $this->gdpr_options['consent_types'];
        ?>
        <div class="gdpr-section">
            <h2><?php _e('Gestion des Consentements', 'pdf-builder-pro'); ?></h2>
            <p><?php _e('Configurez les types de consentements requis pour l\'utilisation du plugin.', 'pdf-builder-pro'); ?></p>

            <form method="post" id="gdpr-consent-form">
                <?php wp_nonce_field('pdf_builder_gdpr_settings', 'gdpr_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Consentement obligatoire', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="consent_required"
                                       value="1" <?php checked($this->gdpr_options['consent_required']); ?> />
                                <?php _e('Exiger un consentement explicite avant utilisation', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Types de consentements', 'pdf-builder-pro'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="checkbox" name="consent_types[analytics]"
                                           value="1" <?php checked($consent_types['analytics']); ?> />
                                    <?php _e('Analytics et statistiques d\'usage', 'pdf-builder-pro'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="consent_types[templates]"
                                           value="1" <?php checked($consent_types['templates']); ?> />
                                    <?php _e('Sauvegarde des templates personnalisés', 'pdf-builder-pro'); ?>
                                </label><br>

                                <label>
                                    <input type="checkbox" name="consent_types[marketing]"
                                           value="1" <?php checked($consent_types['marketing']); ?> />
                                    <?php _e('Communications marketing (newsletters, offres)', 'pdf-builder-pro'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php _e('Sauvegarder les paramètres', 'pdf-builder-pro'); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
    }

    /**
     * Rendre les droits utilisateur
     */
    private function render_user_rights() {
        $current_user = wp_get_current_user();
        ?>
        <div class="gdpr-section">
            <h2><?php _e('Droits RGPD de l\'Utilisateur', 'pdf-builder-pro'); ?></h2>
            <p><?php _e('Exercez vos droits concernant vos données personnelles.', 'pdf-builder-pro'); ?></p>

            <div class="gdpr-rights-grid">
                <div class="gdpr-right-card">
                    <h3><?php _e('Droit d\'accès', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Demandez une copie de toutes vos données personnelles.', 'pdf-builder-pro'); ?></p>
                    <button class="button button-secondary" id="export-data-btn">
                        <?php _e('Exporter mes données', 'pdf-builder-pro'); ?>
                    </button>
                </div>

                <div class="gdpr-right-card">
                    <h3><?php _e('Droit de rectification', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Modifiez vos données personnelles si elles sont inexactes.', 'pdf-builder-pro'); ?></p>
                    <a href="<?php echo admin_url('profile.php'); ?>" class="button button-secondary">
                        <?php _e('Modifier mon profil', 'pdf-builder-pro'); ?>
                    </a>
                </div>

                <div class="gdpr-right-card">
                    <h3><?php _e('Droit à l\'oubli', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Demandez la suppression de toutes vos données.', 'pdf-builder-pro'); ?></p>
                    <button class="button button-danger" id="delete-data-btn">
                        <?php _e('Supprimer mes données', 'pdf-builder-pro'); ?>
                    </button>
                </div>

                <div class="gdpr-right-card">
                    <h3><?php _e('Portabilité des données', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Demandez vos données dans un format structuré.', 'pdf-builder-pro'); ?></p>
                    <button class="button button-secondary" id="portability-btn">
                        <?php _e('Demander portabilité', 'pdf-builder-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Rendre l'audit trail
     */
    private function render_audit_trail() {
        global $wpdb;
        $table_audit = $wpdb->prefix . 'pdf_builder_audit_log';

        // Récupérer les 50 dernières entrées d'audit
        $audit_logs = $wpdb->get_results($wpdb->prepare("
            SELECT * FROM $table_audit
            ORDER BY created_at DESC
            LIMIT 50
        "), ARRAY_A);

        ?>
        <div class="gdpr-section">
            <h2><?php _e('Audit & Traçabilité', 'pdf-builder-pro'); ?></h2>
            <p><?php _e('Historique des opérations sur les données personnelles.', 'pdf-builder-pro'); ?></p>

            <div class="audit-controls">
                <button class="button button-secondary" id="refresh-audit">
                    <?php _e('Actualiser', 'pdf-builder-pro'); ?>
                </button>
                <button class="button button-secondary" id="export-audit">
                    <?php _e('Exporter le journal', 'pdf-builder-pro'); ?>
                </button>
            </div>

            <div class="audit-table-container">
                <table class="widefat fixed audit-table">
                    <thead>
                        <tr>
                            <th><?php _e('Date', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('Utilisateur', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('Action', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('Données concernées', 'pdf-builder-pro'); ?></th>
                            <th><?php _e('IP', 'pdf-builder-pro'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($audit_logs)) : ?>
                            <tr>
                                <td colspan="5"><?php _e('Aucun journal d\'audit disponible.', 'pdf-builder-pro'); ?></td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($audit_logs as $log) : ?>
                                <tr>
                                    <td><?php echo esc_html(date_i18n('d/m/Y H:i', strtotime($log['created_at']))); ?></td>
                                    <td><?php echo esc_html($log['user_id'] ? get_userdata($log['user_id'])->display_name : 'Système'); ?></td>
                                    <td><?php echo esc_html($log['action']); ?></td>
                                    <td><?php echo esc_html($log['data_type']); ?></td>
                                    <td><?php echo esc_html($log['ip_address']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Rendre les paramètres de sécurité
     */
    private function render_security_settings() {
        ?>
        <div class="gdpr-section">
            <h2><?php _e('Sécurité des Données', 'pdf-builder-pro'); ?></h2>
            <p><?php _e('Configuration de la sécurité et du chiffrement des données.', 'pdf-builder-pro'); ?></p>

            <form method="post" id="gdpr-security-form">
                <?php wp_nonce_field('pdf_builder_gdpr_security', 'security_nonce'); ?>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Chiffrement activé', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="encryption_enabled"
                                       value="1" <?php checked($this->gdpr_options['encryption_enabled']); ?> />
                                <?php _e('Chiffrer les données sensibles stockées', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Durée de rétention (jours)', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="data_retention_days" min="1" max="9999"
                                   value="<?php echo esc_attr($this->gdpr_options['data_retention_days']); ?>" />
                            <p class="description">
                                <?php _e('Nombre de jours avant suppression automatique des données (conformément RGPD).', 'pdf-builder-pro'); ?>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Audit activé', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="audit_enabled"
                                       value="1" <?php checked($this->gdpr_options['audit_enabled']); ?> />
                                <?php _e('Enregistrer toutes les opérations sur les données personnelles', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <p class="submit">
                    <button type="submit" class="button button-primary">
                        <?php _e('Sauvegarder la sécurité', 'pdf-builder-pro'); ?>
                    </button>
                </p>
            </form>
        </div>
        <?php
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
            SELECT ID, post_title, post_modified
            FROM {$wpdb->posts}
            WHERE post_author = %d AND post_type = 'pdf_template'
        ", $user_id), ARRAY_A);

        return [
            'user_info' => [
                'id' => $user->ID,
                'login' => $user->user_login,
                'email' => $user->user_email,
                'display_name' => $user->display_name,
                'registered' => $user->user_registered
            ],
            'consents' => $consent_data,
            'templates' => $templates,
            'export_date' => current_time('mysql')
        ];
    }

    /**
     * Supprimer les données d'un utilisateur
     */
    private function delete_user_data($user_id) {
        // Supprimer les consentements
        foreach (['analytics', 'templates', 'marketing'] as $type) {
            $consent_key = 'pdf_builder_consent_' . $type;
            delete_user_meta($user_id, $consent_key);
        }

        // Supprimer les templates utilisateur
        global $wpdb;
        $wpdb->delete($wpdb->posts, [
            'post_author' => $user_id,
            'post_type' => 'pdf_template'
        ]);

        // Supprimer les métadonnées des templates
        $wpdb->delete($wpdb->postmeta, [
            'meta_key' => '_pdf_template_author',
            'meta_value' => $user_id
        ]);
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
}
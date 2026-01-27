<?php
/**
 * Gestionnaire AJAX avancé pour PDF Builder Pro
 *
 * Ce système remplace tous les anciens handlers AJAX procéduraux
 * par un système orienté objet moderne et maintenable.
 *
 * @package PDF_Builder
 * @subpackage Core
 * @since 1.1.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe principale pour la gestion des requêtes AJAX
 */
class PDF_Builder_Ajax_Handler {

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Systèmes de gestion utilisés
     */
    private $systems = array();

    /**
     * Constructeur privé pour le pattern Singleton
     */
    private function __construct() {
        $this->init_systems();
        $this->register_hooks();
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
     * Initialiser les systèmes de gestion
     */
    private function init_systems() {
        $this->systems = array(
            'logger' => PDF_Builder_Core_Logger::get_instance(),
            'security' => PDF_Builder_Security_Validator::get_instance(),
            'diagnostic' => PDF_Builder_Diagnostic_Tool::get_instance(),
            'backup' => PDF_Builder_Backup_Recovery_System::get_instance(),
            'license' => \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance(),
            'user' => PDF_Builder_User_Manager::get_instance(),
            'reporting' => PDF_Builder_Advanced_Reporting::get_instance(),
        );
    }

    /**
     * Enregistrer les hooks WordPress
     */
    private function register_hooks() {
        // Hook pour les requêtes AJAX authentifiées
        add_action('wp_ajax_pdf_builder_ajax_dispatch', array($this, 'handle_authenticated_request'));

        // Hook pour les requêtes AJAX publiques (si nécessaire)
        add_action('wp_ajax_nopriv_pdf_builder_ajax_dispatch', array($this, 'handle_public_request'));
    }

    /**
     * Dispatcher principal pour toutes les actions AJAX
     */
    public function dispatch($action) {
        try {
            // Validation de sécurité de base
            $this->validate_request();

            // Router vers le gestionnaire approprié
            $response = $this->route_action($action);

            // Envoyer la réponse
            wp_send_json_success($response);

        } catch (Exception $e) {
            $this->handle_error($e);
        }
    }

    /**
     * Valider la requête AJAX
     */
    private function validate_request() {
        // Vérifier le nonce
        if (!isset($_REQUEST['nonce']) || !wp_verify_nonce($_REQUEST['nonce'], 'pdf_builder_ajax')) {
            throw new Exception('Nonce de sécurité invalide');
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            throw new Exception('Permissions insuffisantes');
        }

        // Validation supplémentaire via le système de sécurité
        if (isset($this->systems['security'])) {
            $this->systems['security']->validate_ajax_request();
        }
    }

    /**
     * Router les actions vers les gestionnaires appropriés
     */
    private function route_action($action) {
        $method = $this->get_method_from_action($action);

        if (!method_exists($this, $method)) {
            throw new Exception('Action non reconnue: ' . $action);
        }

        return $this->$method();
    }

    /**
     * Convertir une action en nom de méthode
     */
    private function get_method_from_action($action) {
        // Supprimer le préfixe pdf_builder_
        $clean_action = str_replace('pdf_builder_', '', $action);

        // Convertir en camelCase
        $parts = explode('_', $clean_action);
        $method = 'handle_' . $parts[0];
        for ($i = 1; $i < count($parts); $i++) {
            $method .= ucfirst($parts[$i]);
        }

        return $method;
    }

    /**
     * Gestionnaire pour les paramètres
     */
    private function handle_save_settings() {
        $current_tab = sanitize_text_field($_POST['current_tab'] ?? 'all');
        $saved_count = 0;

        if ($current_tab === 'all') {
            $saved_count = $this->save_all_settings();
        } else {
            $saved_count = $this->save_tab_settings($current_tab);
        }

        return array(
            'message' => 'Paramètres sauvegardés avec succès',
            'saved_count' => $saved_count,
            'new_nonce' => wp_create_nonce('pdf_builder_ajax')
        );
    }

    /**
     * Sauvegarder tous les paramètres
     */
    private function save_all_settings() {
        $settings_map = array(
            // Cache
            'cache_enabled' => 'pdf_builder_cache_enabled',
            'cache_ttl' => 'pdf_builder_cache_ttl',
            'cache_compression' => 'pdf_builder_cache_compression',
            'cache_auto_cleanup' => 'pdf_builder_cache_auto_cleanup',
            'cache_max_size' => 'pdf_builder_cache_max_size',

            // Maintenance
            'auto_maintenance' => 'pdf_builder_auto_maintenance',

            // Sauvegarde
            'auto_backup' => 'pdf_builder_auto_backup',
            'auto_backup_frequency' => 'pdf_builder_auto_backup_frequency',
            'backup_retention' => 'pdf_builder_backup_retention',

            // Sécurité
            'security_level' => 'pdf_builder_security_level',
            'enable_logging' => 'pdf_builder_enable_logging',
        );

        $saved_count = 0;
        foreach ($settings_map as $post_key => $option_key) {
            if (isset($_POST[$post_key])) {
                $value = $this->sanitize_setting($post_key, $_POST[$post_key]);
                update_option($option_key, $value);
                $saved_count++;
            }
        }

        return $saved_count;
    }

    /**
     * Sauvegarder les paramètres d'un onglet spécifique
     */
    private function save_tab_settings($tab) {
        $handlers = array(
            'cache' => 'save_cache_settings',
            'maintenance' => 'save_maintenance_settings',
            'sauvegarde' => 'save_backup_settings',
            'securite' => 'save_security_settings',
        );

        if (!isset($handlers[$tab])) {
            return 0;
        }

        return $this->{$handlers[$tab]}();
    }

    /**
     * Sanitiser une valeur de paramètre
     */
    private function sanitize_setting($key, $value) {
        $sanitizers = array(
            'cache_enabled' => 'intval',
            'cache_ttl' => 'intval',
            'cache_compression' => 'intval',
            'cache_auto_cleanup' => 'intval',
            'cache_max_size' => 'intval',
            'auto_maintenance' => 'intval',
            'auto_backup' => 'intval',
            'backup_retention' => 'intval',
            'security_level' => 'sanitize_text_field',
            'enable_logging' => 'intval',
            'auto_backup_frequency' => 'sanitize_text_field',
        );

        if (isset($sanitizers[$key])) {
            $sanitizer = $sanitizers[$key];
            return $sanitizer($value);
        }

        return sanitize_text_field($value);
    }

    /**
     * Gestionnaire pour sauvegarder tous les paramètres
     */
    private function handle_save_all_settings() {
        return $this->handle_save_settings();
    }

    /**
     * Gestionnaire pour obtenir un nouveau nonce
     */
    private function handle_get_fresh_nonce() {
        return array(
            'nonce' => wp_create_nonce('pdf_builder_ajax'),
            'generated_at' => current_time('timestamp')
        );
    }

    /**
     * Gestionnaire pour l'état du cache
     */
    private function handle_get_cache_status() {
        if (!isset($this->systems['cache'])) {
            throw new Exception('Système de cache non disponible');
        }

        return $this->systems['cache']->get_status();
    }

    /**
     * Gestionnaire pour tester le cache
     */
    private function handle_test_cache() {
        if (!isset($this->systems['cache'])) {
            throw new Exception('Système de cache non disponible');
        }

        return $this->systems['cache']->run_tests();
    }

    /**
     * Gestionnaire pour tester l'intégration du cache
     */
    private function handle_test_cache_integration() {
        if (!isset($this->systems['cache'])) {
            throw new Exception('Système de cache non disponible');
        }

        return $this->systems['cache']->test_integration();
    }

    /**
     * Gestionnaire pour vider tout le cache
     */
    private function handle_clear_all_cache() {
        if (!isset($this->systems['cache'])) {
            throw new Exception('Système de cache non disponible');
        }

        return $this->systems['cache']->clear_all();
    }

    /**
     * Gestionnaire pour obtenir les métriques du cache
     */
    private function handle_get_cache_metrics() {
        if (!isset($this->systems['cache'])) {
            throw new Exception('Système de cache non disponible');
        }

        return $this->systems['cache']->get_metrics();
    }

    /**
     * Gestionnaire pour mettre à jour les métriques du cache
     */
    private function handle_update_cache_metrics() {
        if (!isset($this->systems['cache'])) {
            throw new Exception('Système de cache non disponible');
        }

        return $this->systems['cache']->update_metrics();
    }

    /**
     * Gestionnaire pour optimiser la base de données
     */
    private function handle_optimize_database() {
        if (!isset($this->systems['diagnostic'])) {
            throw new Exception('Système de diagnostic non disponible');
        }

        return $this->systems['diagnostic']->optimize_database();
    }

    /**
     * Gestionnaire pour réparer les templates
     */
    private function handle_repair_templates() {
        if (!isset($this->systems['diagnostic'])) {
            throw new Exception('Système de diagnostic non disponible');
        }

        return $this->systems['diagnostic']->repair_templates();
    }

    /**
     * Gestionnaire pour supprimer les fichiers temporaires
     */
    private function handle_remove_temp_files() {
        if (!isset($this->systems['diagnostic'])) {
            throw new Exception('Système de diagnostic non disponible');
        }

        return $this->systems['diagnostic']->remove_temp_files();
    }

    /**
     * Gestionnaire pour créer une sauvegarde
     */
    private function handle_create_backup() {
        if (!isset($this->systems['backup'])) {
            throw new Exception('Système de sauvegarde non disponible');
        }

        return $this->systems['backup']->create_backup();
    }

    /**
     * Gestionnaire pour lister les sauvegardes
     */
    private function handle_list_backups() {
        if (!isset($this->systems['backup'])) {
            throw new Exception('Système de sauvegarde non disponible');
        }

        return $this->systems['backup']->list_backups();
    }

    /**
     * Gestionnaire pour restaurer une sauvegarde
     */
    private function handle_restore_backup() {
        if (!isset($this->systems['backup'])) {
            throw new Exception('Système de sauvegarde non disponible');
        }

        $filename = sanitize_file_name($_POST['filename'] ?? '');
        return $this->systems['backup']->restore_backup($filename);
    }

    /**
     * Gestionnaire pour supprimer une sauvegarde
     */
    private function handle_delete_backup() {
        if (!isset($this->systems['backup'])) {
            throw new Exception('Système de sauvegarde non disponible');
        }

        $filename = sanitize_file_name($_POST['filename'] ?? '');
        return $this->systems['backup']->delete_backup($filename);
    }

    /**
     * Gestionnaire pour tester la licence
     */
    private function handle_test_license() {
        if (!isset($this->systems['license'])) {
            throw new Exception('Système de licence non disponible');
        }

        return $this->systems['license']->test_license();
    }

    /**
     * Gestionnaire pour tester les routes
     */
    private function handle_test_routes() {
        if (!isset($this->systems['diagnostic'])) {
            throw new Exception('Système de diagnostic non disponible');
        }

        return $this->systems['diagnostic']->test_routes();
    }

    /**
     * Gestionnaire pour exporter le diagnostic
     */
    private function handle_export_diagnostic() {
        if (!isset($this->systems['diagnostic'])) {
            throw new Exception('Système de diagnostic non disponible');
        }

        return $this->systems['diagnostic']->export_diagnostic();
    }

    /**
     * Gestionnaire pour voir les logs
     */
    private function handle_view_logs() {
        if (!isset($this->systems['logger'])) {
            throw new Exception('Système de logging non disponible');
        }

        return $this->systems['logger']->get_logs();
    }

    /**
     * Gestionnaire pour sauvegarder un template
     */
    private function handle_save_template() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_save_template_handler();
    }

    /**
     * Gestionnaire pour charger un template
     */
    private function handle_load_template() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_load_template_handler();
    }

    /**
     * Gestionnaire pour la sauvegarde automatique de template
     */
    private function handle_auto_save_template() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_auto_save_template_handler();
    }

    /**
     * Gestionnaire pour charger les paramètres de template
     */
    private function handle_load_template_settings() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_load_template_settings_handler();
    }

    /**
     * Gestionnaire pour sauvegarder les paramètres de template
     */
    private function handle_save_template_settings() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_save_template_settings_handler();
    }

    /**
     * Gestionnaire pour supprimer un template
     */
    private function handle_delete_template() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_delete_template_handler();
    }

    /**
     * Gestionnaire pour définir un template par défaut
     */
    private function handle_set_default_template() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_set_default_template_handler();
    }

    /**
     * Gestionnaire pour dupliquer un template
     */
    private function handle_duplicate_template() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_duplicate_template_handler();
    }

    /**
     * Gestionnaire pour charger un modèle prédéfini
     */
    private function handle_load_predefined_into_editor() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_load_predefined_into_editor_handler();
    }

    /**
     * Gestionnaire pour vérifier la limite de templates
     */
    private function handle_check_template_limit() {
        // Utiliser la logique existante pour la compatibilité
        return pdf_builder_check_template_limit_handler();
    }

    /**
     * Gestionnaire d'erreurs
     */
    private function handle_error(Exception $e) {
        // Logger l'erreur
        if (isset($this->systems['logger'])) {
            $this->systems['logger']->log_error('AJAX Error: ' . $e->getMessage());
        }

        // Envoyer une réponse d'erreur
        wp_send_json_error(array(
            'message' => $e->getMessage(),
            'code' => $e->getCode()
        ));
    }

    /**
     * Gestionnaire pour les requêtes authentifiées
     */
    public function handle_authenticated_request() {
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';
        $this->dispatch($action);
    }

    /**
     * Gestionnaire pour les requêtes publiques
     */
    public function handle_public_request() {
        // Pour les requêtes publiques, implémenter une logique spécifique si nécessaire
        $action = isset($_REQUEST['action']) ? sanitize_text_field($_REQUEST['action']) : '';

        // Seules certaines actions sont autorisées pour les utilisateurs non connectés
        $allowed_public_actions = array(
            'pdf_builder_test_ajax'
        );

        if (!in_array($action, $allowed_public_actions)) {
            wp_send_json_error('Action non autorisée pour les utilisateurs non connectés');
            return;
        }

        $this->dispatch($action);
    }

    /**
     * Méthodes privées pour les sauvegardes de paramètres par onglet
     */
    private function save_cache_settings() {
        $settings = array(
            'cache_enabled' => isset($_POST['cache_enabled']) ? '1' : '0',
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'cache_compression' => isset($_POST['cache_compression']) ? '1' : '0',
            'cache_auto_cleanup' => isset($_POST['cache_auto_cleanup']) ? '1' : '0',
            'cache_max_size' => intval($_POST['cache_max_size'] ?? 100),
        );

        foreach ($settings as $key => $value) {
            pdf_builder_update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    private function save_maintenance_settings() {
        $settings = array(
            'auto_maintenance' => isset($_POST['auto_maintenance']) ? '1' : '0',
        );

        foreach ($settings as $key => $value) {
            pdf_builder_update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    private function save_backup_settings() {
        $settings = array(
            'auto_backup' => isset($_POST['auto_backup']) ? '1' : '0',
            'auto_backup_frequency' => sanitize_text_field($_POST['auto_backup_frequency'] ?? 'daily'),
            'backup_retention' => intval($_POST['backup_retention'] ?? 30),
        );

        foreach ($settings as $key => $value) {
            pdf_builder_update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }

    private function save_security_settings() {
        $settings = array(
            'security_level' => sanitize_text_field($_POST['security_level'] ?? 'medium'),
            'enable_logging' => isset($_POST['enable_logging']) ? '1' : '0',
        );

        foreach ($settings as $key => $value) {
            pdf_builder_update_option('pdf_builder_' . $key, $value);
        }

        return count($settings);
    }
}



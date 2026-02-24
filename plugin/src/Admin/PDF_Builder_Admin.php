<?php
// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter

/**
 * PDF Builder Pro - Interface d'administration simplifiée
 * Version 5.1.0 - Éditeur React uniquement
 * REFACORISÉ : Architecture modulaire avec séparation des responsabilités
 */

namespace PDF_Builder\Admin;

if ( ! defined( 'ABSPATH' ) ) exit;

// Prevent multiple inclusions
if (defined('PDF_BUILDER_ADMIN_LOADED')) {
    return;
}
define('PDF_BUILDER_ADMIN_LOADED', true);

// Importer les classes spécialisées
use PDF_Builder\Admin\Managers\SettingsManager;
use PDF_Builder\Admin\Handlers\AjaxHandler;
use PDF_Builder\Admin\Utils\Permissions;
use PDF_Builder\Admin\Utils\Validation;
use PDF_Builder\Admin\Utils\Helpers;
use PDF_Builder\Admin\Data\DataUtils;
use PDF_Builder\Admin\Utils\Utils;

// Import des fonctions WordPress globales (implicite - appellées avec \__)
// Les fonctions comme            \__(), _e(), add_action, etc. sont des fonction globales WordPress
// et seront accessibles via l'opérateur \ lorsqu'elles ne sont pas trouvées dans le namespace

/**
 * Classe principale d'administration du PDF Builder Pro
 * RESPONSABILITÉS : Orchestration des managers, interface principale
 * Version: 2.0.3 - Optimisée avec lazy loading et méthodes groupées
 */
class PdfBuilderAdminNew
{
    // Constantes pour optimiser les chemins
    const PLUGIN_ROOT = __DIR__ . '/../../..';
    const SRC_DIR = self::PLUGIN_ROOT . '/src';
    const TEMPLATES_DIR = self::PLUGIN_ROOT . '/templates/admin';

    /**
     * Instance unique de la classe
     */
    private static $instance = null;

    /**
     * Référence vers le core
     */
    private $core = null;

    /**
     * Managers spécialisés
     */
    private $template_manager = null;
    private $settings_manager = null;
    private $ajax_handler = null;

    /**
     * Intégration WooCommerce
     */
    private $woocommerce_integration = null;

    /**
     * Flag pour éviter l'ajout multiple du menu
     */
    private static $menu_added = false;

    /**
     * Manager de templates prédéfinis
     */
    private $predefined_templates_manager = null;

    /**
     * Renderers et processors
     */
    private $html_renderer = null;
    private $template_processor = null;
    private $data_utils = null;
    private $utils = null;
    private $thumbnail_manager = null;
    private $script_loader = null;
    private $style_builder = null;
    private $table_renderer = null;
    private $react_transformer = null;
    private $filesystem_helper = null;
    private $maintenance_manager = null;
    private $logger_service = null;
    private $parameter_validator = null;

    /**
     * Handlers et providers spécialisés
     */
    private $maintenance_action_handler = null;
    private $dashboard_data_provider = null;
    private $pdf_html_generator = null;
    private $admin_page_renderer = null;

    /**
     * Obtenir l'instance unique de la classe (Singleton)
     */
    public static function getInstance($core = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($core);
        }
        return self::$instance;
    }

    /**
     * Constructeur privé - Optimisé avec initialisations groupées
     */
    private function __construct($core = null)
    {
        $this->core = $core;

        $this->initCoreManagers();
        $this->initSpecializedModules();
        $this->initServicesAndLoaders();
        $this->initConditionalModules();

        // ✅ ENREGISTRER LES HOOKS WOOCOMMERCE TRÈS TÔT (AVANT is_admin check)
        $this->registerWooCommerceHooks();

        $this->initHooks();
    }

    /**
     * Initialisation des managers de base
     */
    private function initCoreManagers()
    {
        // Initialiser les managers spécialisés avec autoloader PSR-4
        $this->settings_manager = new \PDF_Builder\Managers\PDF_Builder_Settings_Manager($this);

        // Initialiser le template manager
        if (class_exists('PDF_Builder\Managers\PDF_Builder_Template_Manager')) {
            $this->template_manager = new \PDF_Builder\Managers\PDF_Builder_Template_Manager($this);
        } else {
            
        }
    }

    /**
     * Initialisation des modules spécialisés
     */
    private function initSpecializedModules()
    {
        $this->html_renderer = new \PDF_Builder\Admin\Renderers\HTMLRenderer($this);
        $this->data_utils = new DataUtils($this);

        // Try-catch pour la création du template_processor
        try {
            $this->template_processor = new \PDF_Builder\Admin\Processors\TemplateProcessor($this);
        } catch (\Exception $e) {
            $this->template_processor = null;
        }

        // Initialiser AjaxHandler APRÈS template_processor
        $this->ajax_handler = new \PDF_Builder\Admin\Handlers\AjaxHandler($this);

        $this->utils = new \PDF_Builder\Admin\Utils\Utils($this);
    }

    /**
     * Initialisation des services et loaders
     */
    private function initServicesAndLoaders()
    {
        $this->script_loader = new \PDF_Builder\Admin\Loaders\AdminScriptLoader($this);
        $this->style_builder = new \PDF_Builder\Admin\Builders\StyleBuilder();
        $this->table_renderer = new \PDF_Builder\Admin\Renderers\TableRenderer();
        $this->react_transformer = new \PDF_Builder\Admin\Transformers\ReactDataTransformer();
        $this->filesystem_helper = new \PDF_Builder\Admin\Helpers\FileSystemHelper();
        $this->maintenance_manager = new \PDF_Builder\Admin\Managers\MaintenanceManager();
        $this->logger_service = new \PDF_Builder\Admin\Services\LoggerService();
        $this->parameter_validator = new \PDF_Builder\Admin\Validators\ParameterValidator();
        $this->maintenance_action_handler = new \PDF_Builder\Admin\Handlers\MaintenanceActionHandler();

        // Initialiser le AdminPageRenderer
        $this->admin_page_renderer = new \PDF_Builder\Admin\Renderers\AdminPageRenderer($this);
    }

    /**
     * Initialisation des modules conditionnels - Optimisée avec lazy loading
     */
    private function initConditionalModules()
    {
        // Ces modules seront initialisés à la demande via les getters
        // $this->thumbnail_manager = lazy loaded via getThumbnailManager()
        // $this->dashboard_data_provider = lazy loaded via getDashboardDataProvider()
        // $this->woocommerce_integration = lazy loaded via getWooCommerceIntegration()

        // Initialiser seulement le manager de templates prédéfinis s'il existe (réservé aux développeurs)
        if (function_exists('pdf_builder_is_dev_access') && pdf_builder_is_dev_access()) {
            $predefined_manager_file = PDF_BUILDER_PLUGIN_DIR . 'templates/admin/predefined-templates-manager.php';
            if (file_exists($predefined_manager_file)) {
                require_once $predefined_manager_file;
            }
        }
        if (class_exists('PDF_Builder\Admin\PDF_Builder_Predefined_Templates_Manager')) {
            $this->predefined_templates_manager = new \PDF_Builder\Admin\PDF_Builder_Predefined_Templates_Manager();
        }
    }

    /**
     * Initialisation du Thumbnail Manager avec gestion d'erreurs
     */
    private function initThumbnailManager()
    {
        if (!class_exists('PDF_Builder\Managers\PDF_Builder_Thumbnail_Manager')) {
            $thumbnail_manager_file = self::SRC_DIR . '/Managers/PDF_Builder_Thumbnail_Manager.php';
            if (file_exists($thumbnail_manager_file)) {
                require_once $thumbnail_manager_file;
            }
        }

        $this->thumbnail_manager = \PDF_Builder\Managers\PDF_Builder_Thumbnail_Manager::getInstance();
    }

    /**
     * Initialisation du Dashboard Data Provider
     */
    private function initDashboardProvider()
    {
        if (!class_exists('\PDF_Builder\Admin\Providers\DashboardDataProvider')) {
            require_once self::SRC_DIR . '/Admin/Providers/DashboardDataProvider.php';
        }

        $this->dashboard_data_provider = new \PDF_Builder\Admin\Providers\DashboardDataProvider();
    }

    /**
     * Récupère les statistiques du tableau de bord
     */
    public function getDashboardStats()
    {
        $provider = $this->getDashboardDataProvider();
        if ($provider) {
            return $provider->getDashboardStats();
        }
        return [
            'templates' => 0,
            'documents' => 0,
            'today' => 0
        ];
    }

    /**
     * Enregistrer les paramètres WordPress
     */
    public function register_settings()
    {

        // Section Général
        \add_settings_section(
            'pdf_builder_general',
            \__('Paramètres Généraux', 'pdf-builder-pro'),
            array($this, 'general_section_callback'),
            'pdf_builder_general'
        );

        \add_settings_field(
            'company_name',
            \__('Nom de l\'entreprise', 'pdf-builder-pro'),
            array($this, 'company_name_field_callback'),
            'pdf_builder_general',
            'pdf_builder_general'
        );

        \add_settings_field(
            'company_address',
            \__('Adresse', 'pdf-builder-pro'),
            array($this, 'company_address_field_callback'),
            'pdf_builder_general',
            'pdf_builder_general'
        );

        // Section Licence
        \add_settings_section(
            'pdf_builder_licence',
            \__('Paramètres de Licence', 'pdf-builder-pro'),
            array($this, 'licence_section_callback'),
            'pdf_builder_licence'
        );

        // Section Système
        \add_settings_section(
            'pdf_builder_systeme',
            \__('Paramètres Système', 'pdf-builder-pro'),
            array($this, 'systeme_section_callback'),
            'pdf_builder_systeme'
        );

        \add_settings_field(
            'system_memory_limit',
            \__('Limite mémoire PHP', 'pdf-builder-pro'),
            array($this, 'system_memory_limit_field_callback'),
            'pdf_builder_systeme',
            'pdf_builder_systeme'
        );

        \add_settings_field(
            'system_max_execution_time',
            \__('Temps d\'exécution maximum', 'pdf-builder-pro'),
            array($this, 'system_max_execution_time_field_callback'),
            'pdf_builder_systeme',
            'pdf_builder_systeme'
        );

        // Section Sécurité
        \add_settings_section(
            'pdf_builder_securite',
            \__('Paramètres de Sécurité', 'pdf-builder-pro'),
            array($this, 'securite_section_callback'),
            'pdf_builder_securite'
        );

        \add_settings_field(
            'security_file_validation',
            \__('Validation des fichiers', 'pdf-builder-pro'),
            array($this, 'security_file_validation_field_callback'),
            'pdf_builder_securite',
            'pdf_builder_securite'
        );

        // Section Configuration PDF
        \add_settings_section(
            'pdf_builder_pdf',
            \__('Configuration PDF', 'pdf-builder-pro'),
            array($this, 'pdf_section_callback'),
            'pdf_builder_pdf'
        );

        \add_settings_field(
            'pdf_quality',
            \__('Qualité PDF', 'pdf-builder-pro'),
            array($this, 'pdf_quality_field_callback'),
            'pdf_builder_pdf',
            'pdf_builder_pdf'
        );

        \add_settings_field(
            'pdf_compression',
            \__('Compression PDF', 'pdf-builder-pro'),
            array($this, 'pdf_compression_field_callback'),
            'pdf_builder_pdf',
            'pdf_builder_pdf'
        );

        // Section Canvas & Design
        \add_settings_section(
            'pdf_builder_contenu',
            \__('Canvas & Design', 'pdf-builder-pro'),
            array($this, 'contenu_section_callback'),
            'pdf_builder_contenu'
        );

        \add_settings_field(
            'canvas_default_width',
            \__('Largeur par défaut du canvas', 'pdf-builder-pro'),
            array($this, 'canvas_default_width_field_callback'),
            'pdf_builder_contenu',
            'pdf_builder_contenu'
        );

        // Section Templates
        \add_settings_section(
            'pdf_builder_templates',
            \__('Paramètres Templates', 'pdf-builder-pro'),
            array($this, 'templates_section_callback'),
            'pdf_builder_templates'
        );

        \add_settings_field(
            'template_cache_enabled',
            \__('Cache des templates activé', 'pdf-builder-pro'),
            array($this, 'template_cache_enabled_field_callback'),
            'pdf_builder_templates',
            'pdf_builder_templates'
        );

        // Section Développeur
        \add_settings_section(
            'pdf_builder_developpeur',
            \__('Paramètres Développeur', 'pdf-builder-pro'),
            array($this, 'developpeur_section_callback'),
            'pdf_builder_developpeur'
        );

        \add_settings_field(
            'developer_debug_mode',
            \__('Mode debug', 'pdf-builder-pro'),
            array($this, 'developer_debug_mode_field_callback'),
            'pdf_builder_developpeur',
            'pdf_builder_developpeur'
        );

        // Ajouter d'autres sections et champs selon les besoins
    }

    /**
     * Fonction de nettoyage des paramètres
     */
    public function sanitize_settings($input)
    {
        $sanitized = array();

        // Champs généraux
        if (isset($input['company_name'])) {
            $sanitized['company_name'] = \sanitize_text_field($input['company_name']);
        }

        if (isset($input['company_address'])) {
            $sanitized['company_address'] = \sanitize_textarea_field($input['company_address']);
        }

        // Champs système
        if (isset($input['system_memory_limit'])) {
            $sanitized['system_memory_limit'] = \sanitize_text_field($input['system_memory_limit']);
        }

        if (isset($input['system_max_execution_time'])) {
            $sanitized['system_max_execution_time'] = \absint($input['system_max_execution_time']);
        }

        // Champs sécurité
        if (isset($input['security_file_validation'])) {
            $sanitized['security_file_validation'] = $input['security_file_validation'] ? '1' : '0';
        }

        // Champs PDF
        if (isset($input['pdf_quality'])) {
            $allowed_qualities = array('low', 'medium', 'high');
            $sanitized['pdf_quality'] = in_array($input['pdf_quality'], $allowed_qualities) ? $input['pdf_quality'] : 'high';
        }

        if (isset($input['pdf_compression'])) {
            $sanitized['pdf_compression'] = $input['pdf_compression'] ? '1' : '0';
        }

        // Champs contenu/canvas
        if (isset($input['canvas_default_width'])) {
            $width = \absint($input['canvas_default_width']);
            $sanitized['canvas_default_width'] = max(400, min(2000, $width)); // Entre 400 et 2000
        }

        // Champs templates
        if (isset($input['template_cache_enabled'])) {
            $sanitized['template_cache_enabled'] = $input['template_cache_enabled'] ? '1' : '0';
        }

        // Champs développeur
        if (isset($input['developer_debug_mode'])) {
            $sanitized['developer_debug_mode'] = $input['developer_debug_mode'] ? '1' : '0';
        }

        return $sanitized;
    }

    /**
     * Callback pour la section général
     */
    public function general_section_callback()
    {
        echo '<p>' . esc_html__('Configuration générale du générateur de PDF.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour le champ nom de l'entreprise
     */
    public function company_name_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['company_name']) ? $settings['company_name'] : '';
        echo '<input type="text" name="pdf_builder_settings[company_name]" value="' . \esc_attr($value) . '" class="regular-text" />';
    }

    /**
     * Callback pour le champ adresse
     */
    public function company_address_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['company_address']) ? $settings['company_address'] : '';
        echo '<textarea name="pdf_builder_settings[company_address]" rows="3" class="large-text">' . \esc_textarea($value) . '</textarea>';
    }

    /**
     * Callback pour la section licence
     */
    public function licence_section_callback()
    {
        echo '<p>' . esc_html__('Configuration de la licence du plugin.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section système
     */
    public function systeme_section_callback()
    {
        echo '<p>' . esc_html__('Configuration des paramètres système pour optimiser les performances.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section sécurité
     */
    public function securite_section_callback()
    {
        echo '<p>' . esc_html__('Paramètres de sécurité pour protéger vos documents PDF.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section PDF
     */
    public function pdf_section_callback()
    {
        echo '<p>' . esc_html__('Configuration de la génération et de la qualité des fichiers PDF.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section contenu
     */
    public function contenu_section_callback()
    {
        echo '<p>' . esc_html__('Paramètres du canvas et options de design pour vos documents.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section templates
     */
    public function templates_section_callback()
    {
        echo '<p>' . esc_html__('Configuration des templates et options de mise en cache.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section développeur
     */
    public function developpeur_section_callback()
    {
        echo '<p>' .            esc_html__('Outils et options pour les développeurs.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour le champ limite mémoire système
     */
    public function system_memory_limit_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['system_memory_limit']) ? $settings['system_memory_limit'] : '256M';
        echo '<input type="text" name="pdf_builder_settings[system_memory_limit]" value="' . esc_attr($value) . '" class="regular-text" placeholder="256M" />';
        echo '<p class="description">Limite mémoire PHP recommandée (ex: 256M, 512M).</p>';
    }

    /**
     * Callback pour le champ temps d'exécution maximum
     */
    public function system_max_execution_time_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['system_max_execution_time']) ? $settings['system_max_execution_time'] : '30';
        echo '<input type="number" name="pdf_builder_settings[system_max_execution_time]" value="' . esc_attr($value) . '" class="small-text" min="10" max="300" />';
        echo '<p class="description">Temps maximum d\'exécution en secondes (10-300).</p>';
    }

    /**
     * Callback pour le champ validation des fichiers
     */
    public function security_file_validation_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['security_file_validation']) ? $settings['security_file_validation'] : '1';
        echo '<input type="checkbox" name="pdf_builder_settings[security_file_validation]" value="1" ' . \checked($value, '1', false) . ' />';
        echo '<label>Activer la validation des fichiers uploadés</label>';
        echo '<p class="description">Vérifie les types et tailles des fichiers pour la sécurité.</p>';
    }

    /**
     * Callback pour le champ qualité PDF
     */
    public function pdf_quality_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['pdf_quality']) ? $settings['pdf_quality'] : 'high';
        echo '<select name="pdf_builder_settings[pdf_quality]">';
        echo '<option value="low" ' . \selected($value, 'low', false) . '>Faible (72 DPI)</option>';
        echo '<option value="medium" ' . \selected($value, 'medium', false) . '>Moyenne (150 DPI)</option>';
        echo '<option value="high" ' . \selected($value, 'high', false) . '>Haute (300 DPI)</option>';
        echo '</select>';
        echo '<p class="description">Qualité d\'export des images dans le PDF.</p>';
    }

    /**
     * Callback pour le champ compression PDF
     */
    public function pdf_compression_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['pdf_compression']) ? $settings['pdf_compression'] : '1';
        echo '<input type="checkbox" name="pdf_builder_settings[pdf_compression]" value="1" ' . \checked($value, '1', false) . ' />';
        echo '<label>Activer la compression des images</label>';
        echo '<p class="description">Réduit la taille du fichier PDF en compressant les images.</p>';
    }

    /**
     * Callback pour le champ largeur par défaut du canvas
     */
    public function canvas_default_width_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['canvas_default_width']) ? $settings['canvas_default_width'] : '800';
        echo '<input type="number" name="pdf_builder_settings[canvas_default_width]" value="' . esc_attr($value) . '" class="small-text" min="400" max="2000" /> px';
        echo '<p class="description">Largeur par défaut du canvas en pixels (400-2000).</p>';
    }

    /**
     * Callback pour le champ cache des templates
     */
    public function template_cache_enabled_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['template_cache_enabled']) ? $settings['template_cache_enabled'] : '1';
        echo '<input type="checkbox" name="pdf_builder_settings[template_cache_enabled]" value="1" ' . \checked($value, '1', false) . ' />';
        echo '<label>Activer le cache des templates</label>';
        echo '<p class="description">Améliore les performances en mettant en cache les templates compilés.</p>';
    }

    /**
     * Callback pour le champ mode debug développeur
     */
    public function developer_debug_mode_field_callback()
    {
        $settings = pdf_builder_get_option('pdf_builder_settings', array());
        $value = isset($settings['developer_debug_mode']) ? $settings['developer_debug_mode'] : '0';
        echo '<input type="checkbox" name="pdf_builder_settings[developer_debug_mode]" value="1" ' . \checked($value, '1', false) . ' />';
        echo '<label>Activer le mode debug développeur</label>';
        echo '<p class="description">Affiche des informations de debug pour le développement.</p>';
    }

    /**
     * Récupère la version du plugin
     */
    public function getPluginVersion()
    {
        if ($this->dashboard_data_provider) {
            return $this->dashboard_data_provider->getPluginVersion();
        }
        return '1.0.0';
    }

    /**
     * Récupère l'instance du template manager
     */
    public function getTemplateManager()
    {
        return $this->template_manager;
    }

    /**
     * Vérifie les permissions d'administration sans mise en cache
     */
    private function checkAdminPermissions()
    {
        // Vérifier les rôles autorisés par défaut
        $allowed_roles = ['administrator', 'editor', 'shop_manager'];

        $user = \wp_get_current_user();
        $user_roles = $user ? $user->roles : [];

        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Vérifie si l'utilisateur peut créer un nouveau template (limitation freemium)
     *
     * @return bool
     */
    public static function can_create_template() {
        // Vérifier si utilisateur premium
        if (self::is_premium_user()) {
            return true; // Pas de limitation pour premium
        }

        // Compter templates existants de l'utilisateur
        $user_id = \get_current_user_id();
        $templates_count = self::count_user_templates($user_id);

        // Limite : 1 template gratuit
        return $templates_count < 1;
    }

    /**
     * Traite les soumissions de formulaires personnalisés des templates de paramètres
     */
    private function handle_settings_form_submission()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        // Vérifier les permissions
        if (!\current_user_can('manage_options')) {
            \wp_die(esc_html__('Permissions insuffisantes.', 'pdf-builder-pro'));
        }

        // Récupérer l'onglet actuel depuis le formulaire
        $current_tab = $_POST['current_tab'] ?? '';

        if (empty($current_tab)) {
            return;
        }

        // Vérifier le nonce selon l'onglet
        $nonce_name = 'pdf_builder_save_settings';
        if (!isset($_POST[$nonce_name]) || !\pdf_builder_verify_nonce($_POST[$nonce_name], $nonce_name)) {
            \wp_die(esc_html__('Nonce de sécurité invalide.', 'pdf-builder-pro'));
        }

        // Récupérer les paramètres existants
        $settings = pdf_builder_get_option('pdf_builder_settings', array());

        // Traiter selon l'onglet
        switch ($current_tab) {
            case 'general':
                // Paramètres généraux - traiter le array pdf_builder_settings
                if (isset($_POST['pdf_builder_settings']) && is_array($_POST['pdf_builder_settings'])) {
                    $settings['pdf_builder_company_name'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_company_name'] ?? '');
                    $settings['pdf_builder_company_address'] = \sanitize_textarea_field($_POST['pdf_builder_settings']['pdf_builder_company_address'] ?? '');
                    $settings['pdf_builder_company_phone'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_company_phone'] ?? '');
                    $settings['pdf_builder_company_email'] = \sanitize_email($_POST['pdf_builder_settings']['pdf_builder_company_email'] ?? '');
                    $settings['pdf_builder_company_phone_manual'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_company_phone_manual'] ?? '');
                    $settings['pdf_builder_company_siret'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_company_siret'] ?? '');
                    $settings['pdf_builder_company_vat'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_company_vat'] ?? '');
                    $settings['pdf_builder_company_rcs'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_company_rcs'] ?? '');
                    $settings['pdf_builder_company_capital'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_company_capital'] ?? '');
                }
                break;

            case 'pdf':
                // Paramètres PDF
                if (isset($_POST['pdf_builder_settings']) && is_array($_POST['pdf_builder_settings'])) {
                    $settings['pdf_builder_pdf_quality'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_quality'] ?? 'high');
                    $settings['pdf_builder_default_format'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_default_format'] ?? 'A4');
                    $settings['pdf_builder_default_orientation'] = sanitize_text_field($_POST['pdf_builder_settings']['pdf_builder_default_orientation'] ?? 'portrait');
                }
                break;

            case 'systeme':
                // Paramètres système
                if (isset($_POST['pdf_builder_settings']) && is_array($_POST['pdf_builder_settings'])) {
                    $settings['pdf_builder_cache_enabled'] = isset($_POST['pdf_builder_settings']['pdf_builder_cache_enabled']) ? '1' : '0';
                    $settings['pdf_builder_cache_compression'] = isset($_POST['pdf_builder_settings']['pdf_builder_cache_compression']) ? '1' : '0';
                    $settings['pdf_builder_cache_auto_cleanup'] = isset($_POST['pdf_builder_settings']['pdf_builder_cache_auto_cleanup']) ? '1' : '0';
                    $settings['pdf_builder_cache_max_size'] = intval($_POST['pdf_builder_settings']['pdf_builder_cache_max_size'] ?? 100);
                    $settings['pdf_builder_cache_ttl'] = intval($_POST['pdf_builder_settings']['pdf_builder_cache_ttl'] ?? 3600);
                    $settings['pdf_builder_performance_auto_optimization'] = isset($_POST['pdf_builder_settings']['pdf_builder_performance_auto_optimization']) ? '1' : '0';
                    $settings['pdf_builder_systeme_auto_maintenance'] = isset($_POST['pdf_builder_settings']['pdf_builder_systeme_auto_maintenance']) ? '1' : '0';
                }
                break;

            // Ajouter d'autres onglets selon les besoins
            default:
                // Onglet non traité
                break;
        }

        // Sauvegarder les paramètres
        pdf_builder_update_option('pdf_builder_settings', $settings);

        // Message de succès
        \add_settings_error(
            'pdf_builder_settings',
            'settings_updated',
            \__('Paramètres sauvegardés avec succès.', 'pdf-builder-pro'),
            'updated'
        );

        // Rediriger pour éviter la resoumission du formulaire
        \wp_safe_redirect(add_query_arg(['page' => 'pdf-builder-settings', 'tab' => $current_tab, 'updated' => 'true']));
        exit;
    }

    /**
     * Compte le nombre de templates créés par un utilisateur
     *
     * @param int $user_id
     * @return int
     */
    public static function count_user_templates($user_id) {
        global $wpdb;

        // Compter depuis la table custom pdf_builder_templates
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Récupérer le nombre de templates pour cet utilisateur (tous, pas seulement les personnalisés)
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_templates WHERE user_id = %d",
            $user_id
        ));

        return (int)$count;
    }

    /**
     * Vérifie si l'utilisateur est premium
     *
     * @return bool
     */
    public static function is_premium_user() {
        $license_manager = \PDF_Builder\Managers\PDF_Builder_License_Manager::getInstance();
        return $license_manager->is_premium();
    }

    /**
     * Enregistre le custom post type pour les templates PDF
     */
    public function registerTemplatePostType()
    {
        \register_post_type('pdf_template', [
            'labels' => [
                'name' =>            \__('Templates PDF', 'pdf-builder-pro'),
                'singular_name' =>            \__('Template PDF', 'pdf-builder-pro'),
                'add_new' =>            \__('Nouveau Template', 'pdf-builder-pro'),
                'add_new_item' =>            \__('Ajouter un Nouveau Template', 'pdf-builder-pro'),
                'edit_item' =>            \__('Éditer le Template', 'pdf-builder-pro'),
                'new_item' =>            \__('Nouveau Template', 'pdf-builder-pro'),
                'view_item' =>            \__('Voir le Template', 'pdf-builder-pro'),
                'search_items' =>            \__('Rechercher Templates', 'pdf-builder-pro'),
                'not_found' =>            \__('Aucun template trouvé', 'pdf-builder-pro'),
                'not_found_in_trash' =>            \__('Aucun template dans la corbeille', 'pdf-builder-pro'),
            ],
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Masqué du menu principal
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => ['title'],
            'has_archive' => false,
            'rewrite' => false,
        ]);
    }

    /**
     * Initialise les hooks WordPress
     */
    /**
     * Initialisation des hooks WordPress - Optimisée
     */
    private function initHooks()
    {
        // Hooks toujours nécessaires
        \add_action('admin_menu', [$this, 'addAdminMenu']);

        // Vérifier si on est dans le contexte admin
        if (!$this->isAdminContext()) {
            return;
        }

        // Hooks d'administration
        $this->initAdminHooks();

        // Hooks conditionnels
        $this->initConditionalHooks();
    }

    /**
     * Vérifie si on est dans le contexte d'administration
     */
    private function isAdminContext()
    {
        return is_admin() || (isset($_REQUEST['action']) && strpos($_REQUEST['action'], 'pdf_builder') !== false);
    }

    /**
     * Enregistre les hooks WooCommerce (fait très tôt, avant is_admin check)
     */
    private function registerWooCommerceHooks()
    {
        // AJAX handlers WooCommerce — enregistrés tôt pour être disponibles même hors page admin
        // On lazy-load l'intégration uniquement quand la requête AJAX arrive
        // html2canvas sur les pages commandes (pour export PNG/JPG côté client dans la metabox)
        \add_action('admin_enqueue_scripts', function($hook) {
            $is_order = false;
            if ($hook === 'post.php' && isset($_GET['post'])) {
                $is_order = get_post_type(intval($_GET['post'])) === 'shop_order';
            }
            if (!$is_order && (
                strpos($hook, 'wc-orders') !== false ||
                (isset($_GET['page']) && $_GET['page'] === 'wc-orders' && isset($_GET['id']))
            )) {
                $is_order = true;
            }
            if (!$is_order) return;

            wp_enqueue_script(
                'html2canvas',
                'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js',
                [], '1.4.1', true
            );
        });

        \add_action('wp_ajax_pdf_builder_generate_order_pdf', function() {
            $integration = $this->getWooCommerceIntegration();
            if ($integration) {
                $integration->ajaxGenerateOrderPdf();
            } else {
                \wp_send_json_error(['message' => 'WooCommerce integration unavailable']);
            }
        }, 1);

        \add_action('wp_ajax_pdf_builder_send_order_email', function() {
            $integration = $this->getWooCommerceIntegration();
            if ($integration) {
                $integration->ajaxSendOrderEmail();
            } else {
                \wp_send_json_error(['message' => 'WooCommerce integration unavailable']);
            }
        }, 1);

        // Streaming PDF blob (fetch depuis JS - tous utilisateurs)
        \add_action('wp_ajax_pdf_builder_stream_pdf', function() {
            $integration = $this->getWooCommerceIntegration();
            if ($integration) {
                $integration->ajaxStreamPdf();
            } else {
                status_header(503);
                echo 'WooCommerce integration unavailable';
                exit;
            }
        }, 1);

        // File d'attente PDF (utilisateurs gratuits)
        \add_action('wp_ajax_pdf_builder_pdf_queue_join', function() {
            $integration = $this->getWooCommerceIntegration();
            if ($integration) {
                $integration->ajaxPdfQueueJoin();
            } else {
                \wp_send_json_error(['message' => 'WooCommerce integration unavailable']);
            }
        }, 1);

        \add_action('wp_ajax_pdf_builder_pdf_queue_poll', function() {
            $integration = $this->getWooCommerceIntegration();
            if ($integration) {
                $integration->ajaxPdfQueuePoll();
            } else {
                \wp_send_json_error(['message' => 'WooCommerce integration unavailable']);
            }
        }, 1);

        \add_action('wp_ajax_pdf_builder_pdf_queue_leave', function() {
            $integration = $this->getWooCommerceIntegration();
            if ($integration) {
                $integration->ajaxPdfQueueLeave();
            } else {
                \wp_send_json_error(['message' => 'ok']);
            }
        }, 1);

        // Enregistrer directement pour le hook shop_order (version legacy)
        \add_action('add_meta_boxes_shop_order', function() {
            // Vérifier que WooCommerce est activé
            if (!defined('WC_VERSION')) {
                return;
            }

            // Obtenir l'intégration WooCommerce
            $woo_integration = $this->getWooCommerceIntegration();
            if ($woo_integration === null) {
                return;
            }

            // Appeler la méthode pour ajouter la meta box
            $woo_integration->addWoocommerceOrderMetaBox();
        }, 10);

        // Enregistrer pour HPOS (version WooCommerce 7.1+)
        if (defined('WC_VERSION') && version_compare(WC_VERSION, '7.1', '>=')) {
            \add_action('add_meta_boxes_woocommerce_page_wc-orders', function() {
                // Vérifier que WooCommerce est activé
                if (!defined('WC_VERSION')) {
                    return;
                }

                // Obtenir l'intégration WooCommerce
                $woo_integration = $this->getWooCommerceIntegration();
                if ($woo_integration === null) {
                    return;
                }

                // Appeler la méthode pour ajouter la meta box
                $woo_integration->addWoocommerceOrderMetaBox();
            }, 10);
        }
    }

    /**
     * Hooks d'administration de base
     */
    private function initAdminHooks()
    {
        \add_action('admin_init', array($this, 'register_settings'));
        \add_action('init', [$this, 'register_template_post_type']);
        \add_action('admin_enqueue_scripts', [$this, 'disable_problematic_preferences'], 1);
        \add_action('admin_init', [$this, 'handleLegacyTemplateLinks']);
    }

    /**
     * Hooks conditionnels (autres que WooCommerce)
     */
    private function initConditionalHooks()
    {
        // Le gestionnaire de modèles prédéfinis est déjà chargé dans bootstrap.php
        // include_once self::TEMPLATES_DIR . '/predefined-templates-manager.php';
    }

    /**
     * Enregistre le custom post type pour les templates PDF
     */
    public function register_template_post_type()
    {
        $labels = array(
            'name' =>            \__('Templates PDF', 'pdf-builder-pro'),
            'singular_name' =>            \__('Template PDF', 'pdf-builder-pro'),
            'menu_name' =>            \__('Templates', 'pdf-builder-pro'),
            'add_new' =>            \__('Ajouter', 'pdf-builder-pro'),
            'add_new_item' =>            \__('Ajouter un template PDF', 'pdf-builder-pro'),
            'edit_item' =>            \__('Modifier le template', 'pdf-builder-pro'),
            'new_item' =>            \__('Nouveau template', 'pdf-builder-pro'),
            'view_item' =>            \__('Voir le template', 'pdf-builder-pro'),
            'search_items' =>            \__('Rechercher des templates', 'pdf-builder-pro'),
            'not_found' =>            \__('Aucun template trouvé', 'pdf-builder-pro'),
            'not_found_in_trash' =>            \__('Aucun template dans la corbeille', 'pdf-builder-pro'),
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Masqué du menu principal
            'capability_type' => 'post',
            'hierarchical' => false,
            'supports' => array('title', 'editor', 'custom-fields'),
            'has_archive' => false,
            'rewrite' => false,
            'query_var' => false,
        );

        \register_post_type('pdf_template', $args);
    }

    /**
     * Compatibilité avec les anciens liens template_id - méthode vide (système supprimé)
     */
    public function handleLegacyTemplateLinks()
    {
        // Méthode vide - plus de redirection nécessaire car éditeur unique
    }

    /**
     * Désactive les préférences WordPress problématiques qui causent des erreurs API REST
     */
    public function disable_problematic_preferences()
    {
        global $pagenow;
        $page = isset($_GET['page']) ? $_GET['page'] : '';

        // Ne désactiver que sur notre page d'éditeur
        if ($page !== 'pdf-builder-react-editor') {
            return;
        }

        // Désactiver les préférences utilisateur qui causent des erreurs API REST
        echo "<script>
            // Désactiver les appels API REST problématiques
            if (typeof wp !== 'undefined' && wp.apiFetch) {
                // Override apiFetch to prevent problematic calls
                var originalApiFetch = wp.apiFetch;
                wp.apiFetch = function(options) {
                    // Block calls to users/me endpoint that cause 404
                    if (options.path && options.path.includes('/wp/v2/users/me')) {
                        return Promise.reject({
                            code: 'blocked_endpoint',
                            message: 'Endpoint blocked to prevent errors'
                        });
                    }
                    return originalApiFetch(options);
                };
            }

            // Désactiver les préférences qui causent des erreurs JSON
            if (typeof wp !== 'undefined' && wp.data && wp.data.dispatch) {
                try {
                    // Désactiver les préférences utilisateur si elles existent
                    if (wp.data.dispatch('core/preferences')) {
                        // Override pour éviter les erreurs
                        wp.data.dispatch('core/preferences').set = function() {
                            // Silent fail
                        };
                    }
                } catch (e) {
                    // Ignore errors
                }
            }

            // Corriger les appels AJAX qui retournent du HTML au lieu de JSON
            if (typeof jQuery !== 'undefined') {
                // Override jQuery.ajax pour gérer les réponses non-JSON
                var originalAjax = jQuery.ajax;
                jQuery.ajax = function(options) {
                    if (options.dataType === 'json' || (options.url && options.url.includes('admin-ajax.php'))) {
                        var originalSuccess = options.success;
                        var originalError = options.error;

                        options.success = function(data, textStatus, jqXHR) {
                            // Vérifier si la réponse est du JSON valide
                            if (typeof data === 'string' && data.trim().charAt(0) !== '{') {
                                // Réponse HTML au lieu de JSON - traiter comme erreur
                                if (originalError) {
                                    originalError(jqXHR, 'parsererror', {
                                        code: 'invalid_json',
                                        message: 'La réponse n\'est pas une réponse JSON valide.'
                                    });
                                }
                                return;
                            }
                            if (originalSuccess) {
                                originalSuccess(data, textStatus, jqXHR);
                            }
                        };

                        options.error = function(jqXHR, textStatus, errorThrown) {
                            // Log pour debug mais ne pas afficher d'erreur bloquante
                            console.warn('PDF Builder: AJAX error handled:', textStatus, errorThrown);
                            if (originalError) {
                                originalError(jqXHR, textStatus, errorThrown);
                            }
                        };
                    }
                    return originalAjax.call(this, options);
                };
            }
        </script>";
    }

    /**
     * Ajoute le menu d'administration
     */
    public function addAdminMenu()
    {
        // Vérifier les permissions de l'utilisateur actuel
        $current_user = \wp_get_current_user();
        $user_id = $current_user ? $current_user->ID : 'null';
        $user_roles = $current_user ? implode(',', $current_user->roles) : 'none';

        // Vérifier la capacité manage_options
        $has_manage_options = \current_user_can('manage_options');

        if (!$has_manage_options) {
            return;
        }

        // Menu principal PDF Builder Pro
        \add_menu_page(
            \__('PDF Builder Pro', 'pdf-builder-pro'),
            \__('PDF Builder', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-pro',
            [$this, 'adminPage'],
            'dashicons-pdf',
            30
        );

        // Page d'accueil (sous-menu principal masqué)
        \add_submenu_page(
            'pdf-builder-pro',
            \__('Accueil - PDF Builder Pro', 'pdf-builder-pro'),
            \__('🏠 Accueil', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-pro', // Même slug que le menu principal
            [$this, 'adminPage']
        );

        // Éditeur React unique (accessible via lien direct, masqué du menu)
        \add_submenu_page('pdf-builder-pro',            \__('Éditeur PDF', 'pdf-builder-pro'),            \__('🎨 Éditeur PDF', 'pdf-builder-pro'), 'manage_options', 'pdf-builder-react-editor', [$this, 'reactEditorPage']);

        // Le menu de l'éditeur React est maintenant visible
        // Ancien code commenté :
        // \add_action('admin_enqueue_scripts', function() {
        //     echo '<style>
        //         li a[href*="page=pdf-builder-react-editor"] {
        //             display: none !important;
        //         }
        //         li a[href*="page=pdf-builder-react-editor"] + ul {
        //             display: none !important;
        //         }
        //     </style>';
        // });

        // Gestion des templates
        \add_submenu_page('pdf-builder-pro',            \__('Templates PDF - PDF Builder Pro', 'pdf-builder-pro'),            \__('📋 Templates', 'pdf-builder-pro'), 'manage_options', 'pdf-builder-templates', [$this, 'templatesPage']);

        // Paramètres et configuration
        \add_submenu_page('pdf-builder-pro',            \__('Paramètres - PDF Builder Pro', 'pdf-builder-pro'),            \__('⚙️ Paramètres', 'pdf-builder-pro'), 'manage_options', 'pdf-builder-settings', [$this, 'settings_page']);

        // Galerie de modèles (mode développeur uniquement — token + BDD requis + manager instancié)
        if (
            function_exists('pdf_builder_is_developer_mode_active') &&
            pdf_builder_is_developer_mode_active() &&
            $this->predefined_templates_manager !== null
        ) {
            \add_submenu_page(
                'pdf-builder-pro',
                \__('Galerie de Modèles - PDF Builder Pro', 'pdf-builder-pro'),
                \__('🖼️ Galerie', 'pdf-builder-pro'),
                'manage_options',
                'pdf-builder-predefined-templates',
                [$this->predefined_templates_manager, 'renderAdminPage']
            );
        }

    }



    /**
     * Page principale d'administration - Tableau de bord
     */
    public function adminPage()
    {
        if (!$this->checkAdminPermissions()) {
            \wp_die(esc_html__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Utiliser le renderer pour afficher la page d'administration
        if ($this->admin_page_renderer) {
            echo $this->admin_page_renderer->renderAdminPage(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- HTML généré en interne
        } else {
            // Fallback si le renderer n'est pas disponible
            echo '<div class="wrap"><h1>PDF Builder Pro</h1><p>Erreur: Renderer non disponible.</p></div>';
        }
    }

    /**
     * Page de l'éditeur React
     */
    /**
     * Page de l'éditeur React unifié
     */
    public function reactEditorPage()
    {
        // En mode preview, autoriser les utilisateurs avec droits WooCommerce
        $is_preview_mode = isset($_GET['preview']) && $_GET['preview'] === '1';
        $has_permission = $this->checkAdminPermissions() || 
                          ($is_preview_mode && \current_user_can('edit_shop_orders'));

        if (!$has_permission) {
            \wp_die(esc_html__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Récupération des paramètres
        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 1;
        $template_type = isset($_GET['template_type']) ? sanitize_text_field($_GET['template_type']) : 'custom';

        ?>
        <div class="wrap pdf-builder-editor-page">
            <div id="pdf-builder-react-root"></div>
        </div>
        <?php
    }

    /**
     * Page de gestion des templates
     */
    public function templatesPage()
    {
        if (!$this->checkAdminPermissions()) {
            \wp_die(esc_html__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Inclure la page dédiée de gestion des templates
        $templates_file = \plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/templates-page.php';
        if (file_exists($templates_file)) {
            include $templates_file;
        } else {
            // Fallback si le fichier n'existe pas
            echo '<div class="wrap">';
            echo '<h1>Gestion des Templates PDF</h1>';
            echo '<p>Erreur: Fichier de templates introuvable.</p>';
            echo '</div>';
        }
    }

    /**
     * Page des paramètres - CENTRALISÉE dans settings-main.php
     */
    public function settings_page()
    {
        if (!$this->checkAdminPermissions()) {
            \wp_die(esc_html__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Inclure le fichier centralisé des paramètres
        include \plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-main.php';
    }

    /**
     * Getter pour le Thumbnail Manager avec lazy loading
     */
    public function getThumbnailManager()
    {
        if ($this->thumbnail_manager === null) {
            $this->initThumbnailManager();
        }
        return $this->thumbnail_manager;
    }

    /**
     * Getter pour le Dashboard Data Provider avec lazy loading
     */
    public function getDashboardDataProvider()
    {
        if ($this->dashboard_data_provider === null) {
            $this->initDashboardProvider();
        }
        return $this->dashboard_data_provider;
    }

    /**
     * Getter pour l'intégration WooCommerce avec lazy loading
     */
    public function getWooCommerceIntegration()
    {
        if ($this->woocommerce_integration === null && \did_action('plugins_loaded') && defined('WC_VERSION')) {
            if (class_exists('PDF_Builder\Managers\PDF_Builder_WooCommerce_Integration')) {
                $this->woocommerce_integration = new \PDF_Builder\Managers\PDF_Builder_WooCommerce_Integration($this->core);
            }
        }
        return $this->woocommerce_integration;
    }

    /**
     * Get template processor instance
     */
    public function getTemplateProcessor()
    {
        if ($this->template_processor === null) {
            
            try {
                $this->template_processor = new \PDF_Builder\Admin\Processors\TemplateProcessor($this);
                
            } catch (\Exception $e) {
                
                $this->template_processor = null;
            }
        }
        return $this->template_processor;
    }
}







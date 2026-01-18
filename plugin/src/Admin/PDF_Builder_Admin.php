<?php

/**
 * PDF Builder Pro - Interface d'administration simplifiée
 * Version 5.1.0 - Éditeur React uniquement
 * REFACORISÉ : Architecture modulaire avec séparation des responsabilités
 */

namespace PDF_Builder\Admin;

// Importer les classes spécialisées
use PDF_Builder\Admin\Managers\SettingsManager;
use PDF_Builder\Admin\Handlers\AjaxHandler;
use PDF_Builder\Admin\Utils\Permissions;
use PDF_Builder\Admin\Utils\Validation;
use Exception;
use PDF_Builder\Admin\Utils\Helpers;
use PDF_Builder\Admin\Data\DataUtils;
use PDF_Builder\Admin\Utils\Utils;

/**
 * Classe principale d'administration du PDF Builder Pro
 * RESPONSABILITÉS : Orchestration des managers, interface principale
 */
class PdfBuilderAdmin
{
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
    private $pdf_generator = null;
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
     * Constructeur privé
     */
    private function __construct($core = null)
    {
        $this->core = $core;

        // Charger manuellement les classes nécessaires si l'autoloader est désactivé
        if (!class_exists('PDF_Builder\Admin\Managers\SettingsManager')) {
            $settings_manager_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Managers/SettingsManager.php';
            if (file_exists($settings_manager_file)) {
                require_once $settings_manager_file;
            }
        }

        if (!class_exists('PDF_Builder\Admin\Handlers\AjaxHandler')) {
            $ajax_handler_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Handlers/AjaxHandler.php';
            if (file_exists($ajax_handler_file)) {
                require_once $ajax_handler_file;
            }
        }

        if (!class_exists('PDF_Builder\Admin\Renderers\HTMLRenderer')) {
            $html_renderer_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Renderers/HTMLRenderer.php';
            if (file_exists($html_renderer_file)) {
                require_once $html_renderer_file;
            }
        }

        if (!class_exists('PDF_Builder\Admin\Processors\TemplateProcessor')) {
            $template_processor_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Processors/TemplateProcessor.php';
            if (file_exists($template_processor_file)) {
                require_once $template_processor_file;
            }
        }

        // Charger manuellement PDFGenerator et Utils si nécessaire
        if (!class_exists('PDF_Builder\Admin\Generators\PDFGenerator')) {
            $pdf_generator_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Generators/PDFGenerator.php';
            if (file_exists($pdf_generator_file)) {
                require_once $pdf_generator_file;
            }
        }

        if (!class_exists('PDF_Builder\Admin\Utils\Utils')) {
            $utils_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Utils/Utils.php';
            if (file_exists($utils_file)) {
                require_once $utils_file;
            }
        }

        // Initialiser les managers spécialisés
        $this->settings_manager = new SettingsManager($this);

        // Initialiser le template manager
        if (class_exists('PDF_Builder\Managers\PdfBuilderTemplateManager')) {
            $this->template_manager = new \PDF_Builder\Managers\PdfBuilderTemplateManager($this);
        }

        // Initialiser les nouveaux modules spécialisés
        $this->html_renderer = new \PDF_Builder\Admin\Renderers\HTMLRenderer($this);

        // Charger manuellement la classe DataUtils si nécessaire
        if (!class_exists('PDF_Builder\Admin\Data\DataUtils')) {
            $data_utils_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Data/DataUtils.php';
            if (file_exists($data_utils_file)) {
                require_once $data_utils_file;
            }
        }

        $this->data_utils = new DataUtils($this);
        
        // Try-catch pour la création du template_processor
        try {
            $this->template_processor = new \PDF_Builder\Admin\Processors\TemplateProcessor($this);
        } catch (Exception $e) {
            $this->template_processor = null;
        }

        // Initialiser AjaxHandler APRÈS template_processor
        $this->ajax_handler = new AjaxHandler($this);
        
        $this->pdf_generator = new \PDF_Builder\Admin\Generators\PDFGenerator($this);
        $this->utils = new Utils($this);

        // Initialiser l'intégration WooCommerce si disponible
        if (did_action('plugins_loaded') && defined('WC_VERSION') && class_exists('PDF_Builder\Managers\PDF_Builder_WooCommerce_Integration')) {
            $this->woocommerce_integration = new \PDF_Builder\Managers\PDF_Builder_WooCommerce_Integration($this->core);
        }

        // Initialiser le manager de templates prédéfinis
        if (class_exists('PDF_Builder\Admin\PDF_Builder_Predefined_Templates_Manager')) {
            $this->predefined_templates_manager = new \PDF_Builder\Admin\PDF_Builder_Predefined_Templates_Manager();
        }

        // Charger manuellement le Thumbnail Manager si nécessaire
        if (!class_exists('PDF_Builder\Managers\PdfBuilderThumbnailManager')) {
            // Chemin absolu depuis ce fichier
            $plugin_root = dirname(dirname(dirname(__DIR__)));
            $thumbnail_manager_file = $plugin_root . '/src/Managers/PDF_Builder_Thumbnail_Manager.php';
            if (file_exists($thumbnail_manager_file)) {
                require_once $thumbnail_manager_file;
            }
        }

        // Initialiser le manager de thumbnails
        $this->thumbnail_manager = \PDF_Builder\Managers\PdfBuilderThumbnailManager::getInstance();

        // Charger manuellement AdminScriptLoader si nécessaire
        if (!class_exists('PDF_Builder\Admin\Loaders\AdminScriptLoader')) {
            $admin_script_loader_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Loaders/AdminScriptLoader.php';
            if (file_exists($admin_script_loader_file)) {
                require_once $admin_script_loader_file;
            }
        }

        // Initialiser les nouveaux services et loaders
        $this->script_loader = new \PDF_Builder\Admin\Loaders\AdminScriptLoader($this);
        $this->style_builder = new \PDF_Builder\Admin\Builders\StyleBuilder();
        $this->table_renderer = new \PDF_Builder\Admin\Renderers\TableRenderer();
        $this->react_transformer = new \PDF_Builder\Admin\Transformers\ReactDataTransformer();
        $this->filesystem_helper = new \PDF_Builder\Admin\Helpers\FileSystemHelper();
        $this->maintenance_manager = new \PDF_Builder\Admin\Managers\MaintenanceManager();
        $this->logger_service = new \PDF_Builder\Admin\Services\LoggerService();
        $this->parameter_validator = new \PDF_Builder\Admin\Validators\ParameterValidator();
        $this->maintenance_action_handler = new \PDF_Builder\Admin\Handlers\MaintenanceActionHandler();

        // Charger le DashboardDataProvider si nécessaire
        if (!class_exists('\PDF_Builder\Admin\Providers\DashboardDataProvider')) {
            require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'plugin/src/Admin/Providers/DashboardDataProvider.php';
        }

        $this->dashboard_data_provider = new \PDF_Builder\Admin\Providers\DashboardDataProvider();

        // Initialiser le AdminPageRenderer
        if (!class_exists('\PDF_Builder\Admin\Renderers\AdminPageRenderer')) {
            require_once plugin_dir_path(dirname(dirname(__FILE__))) . 'src/Admin/Renderers/AdminPageRenderer.php';
        }

        $this->admin_page_renderer = new \PDF_Builder\Admin\Renderers\AdminPageRenderer($this);

        $this->initHooks();
    }

    /**
     * Récupère les statistiques du tableau de bord
     */
    public function getDashboardStats()
    {
        if ($this->dashboard_data_provider) {
            return $this->dashboard_data_provider->getDashboardStats();
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
        // REMOVED: register_setting déplacé vers SettingsManager.php pour éviter les conflits
        // register_setting('pdf_builder_settings', 'pdf_builder_settings', array($this, 'sanitize_settings'));

        // Section Général
        add_settings_section(
            'pdf_builder_general',
            __('Paramètres Généraux', 'pdf-builder-pro'),
            array($this, 'general_section_callback'),
            'pdf_builder_general'
        );

        add_settings_field(
            'company_name',
            __('Nom de l\'entreprise', 'pdf-builder-pro'),
            array($this, 'company_name_field_callback'),
            'pdf_builder_general',
            'pdf_builder_general'
        );

        add_settings_field(
            'company_address',
            __('Adresse', 'pdf-builder-pro'),
            array($this, 'company_address_field_callback'),
            'pdf_builder_general',
            'pdf_builder_general'
        );

        // Section Licence
        add_settings_section(
            'pdf_builder_licence',
            __('Paramètres de Licence', 'pdf-builder-pro'),
            array($this, 'licence_section_callback'),
            'pdf_builder_licence'
        );

        // Section Système
        add_settings_section(
            'pdf_builder_systeme',
            __('Paramètres Système', 'pdf-builder-pro'),
            array($this, 'systeme_section_callback'),
            'pdf_builder_systeme'
        );

        add_settings_field(
            'system_memory_limit',
            __('Limite mémoire PHP', 'pdf-builder-pro'),
            array($this, 'system_memory_limit_field_callback'),
            'pdf_builder_systeme',
            'pdf_builder_systeme'
        );

        add_settings_field(
            'system_max_execution_time',
            __('Temps d\'exécution maximum', 'pdf-builder-pro'),
            array($this, 'system_max_execution_time_field_callback'),
            'pdf_builder_systeme',
            'pdf_builder_systeme'
        );

        // Section Sécurité
        add_settings_section(
            'pdf_builder_securite',
            __('Paramètres de Sécurité', 'pdf-builder-pro'),
            array($this, 'securite_section_callback'),
            'pdf_builder_securite'
        );

        add_settings_field(
            'security_file_validation',
            __('Validation des fichiers', 'pdf-builder-pro'),
            array($this, 'security_file_validation_field_callback'),
            'pdf_builder_securite',
            'pdf_builder_securite'
        );

        // Section Configuration PDF
        add_settings_section(
            'pdf_builder_pdf',
            __('Configuration PDF', 'pdf-builder-pro'),
            array($this, 'pdf_section_callback'),
            'pdf_builder_pdf'
        );

        add_settings_field(
            'pdf_quality',
            __('Qualité PDF', 'pdf-builder-pro'),
            array($this, 'pdf_quality_field_callback'),
            'pdf_builder_pdf',
            'pdf_builder_pdf'
        );

        add_settings_field(
            'pdf_compression',
            __('Compression PDF', 'pdf-builder-pro'),
            array($this, 'pdf_compression_field_callback'),
            'pdf_builder_pdf',
            'pdf_builder_pdf'
        );

        // Section Canvas & Design
        add_settings_section(
            'pdf_builder_contenu',
            __('Canvas & Design', 'pdf-builder-pro'),
            array($this, 'contenu_section_callback'),
            'pdf_builder_contenu'
        );

        add_settings_field(
            'canvas_default_width',
            __('Largeur par défaut du canvas', 'pdf-builder-pro'),
            array($this, 'canvas_default_width_field_callback'),
            'pdf_builder_contenu',
            'pdf_builder_contenu'
        );

        // Section Templates
        add_settings_section(
            'pdf_builder_templates',
            __('Paramètres Templates', 'pdf-builder-pro'),
            array($this, 'templates_section_callback'),
            'pdf_builder_templates'
        );

        add_settings_field(
            'template_cache_enabled',
            __('Cache des templates activé', 'pdf-builder-pro'),
            array($this, 'template_cache_enabled_field_callback'),
            'pdf_builder_templates',
            'pdf_builder_templates'
        );

        // Section Développeur
        add_settings_section(
            'pdf_builder_developpeur',
            __('Paramètres Développeur', 'pdf-builder-pro'),
            array($this, 'developpeur_section_callback'),
            'pdf_builder_developpeur'
        );

        add_settings_field(
            'developer_debug_mode',
            __('Mode debug', 'pdf-builder-pro'),
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
            $sanitized['company_name'] = sanitize_text_field($input['company_name']);
        }

        if (isset($input['company_address'])) {
            $sanitized['company_address'] = sanitize_textarea_field($input['company_address']);
        }

        // Champs système
        if (isset($input['system_memory_limit'])) {
            $sanitized['system_memory_limit'] = sanitize_text_field($input['system_memory_limit']);
        }

        if (isset($input['system_max_execution_time'])) {
            $sanitized['system_max_execution_time'] = absint($input['system_max_execution_time']);
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
            $width = absint($input['canvas_default_width']);
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
        echo '<p>' . __('Configuration générale du générateur de PDF.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour le champ nom de l'entreprise
     */
    public function company_name_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['company_name']) ? $settings['company_name'] : '';
        echo '<input type="text" name="pdf_builder_settings[company_name]" value="' . esc_attr($value) . '" class="regular-text" />';
    }

    /**
     * Callback pour le champ adresse
     */
    public function company_address_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['company_address']) ? $settings['company_address'] : '';
        echo '<textarea name="pdf_builder_settings[company_address]" rows="3" class="large-text">' . esc_textarea($value) . '</textarea>';
    }

    /**
     * Callback pour la section licence
     */
    public function licence_section_callback()
    {
        echo '<p>' . __('Configuration de la licence du plugin.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section système
     */
    public function systeme_section_callback()
    {
        echo '<p>' . __('Configuration des paramètres système pour optimiser les performances.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section sécurité
     */
    public function securite_section_callback()
    {
        echo '<p>' . __('Paramètres de sécurité pour protéger vos documents PDF.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section PDF
     */
    public function pdf_section_callback()
    {
        echo '<p>' . __('Configuration de la génération et de la qualité des fichiers PDF.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section contenu
     */
    public function contenu_section_callback()
    {
        echo '<p>' . __('Paramètres du canvas et options de design pour vos documents.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section templates
     */
    public function templates_section_callback()
    {
        echo '<p>' . __('Configuration des templates et options de mise en cache.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour la section développeur
     */
    public function developpeur_section_callback()
    {
        echo '<p>' . __('Outils et options pour les développeurs.', 'pdf-builder-pro') . '</p>';
    }

    /**
     * Callback pour le champ limite mémoire système
     */
    public function system_memory_limit_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['system_memory_limit']) ? $settings['system_memory_limit'] : '256M';
        echo '<input type="text" name="pdf_builder_settings[system_memory_limit]" value="' . esc_attr($value) . '" class="regular-text" placeholder="256M" />';
        echo '<p class="description">Limite mémoire PHP recommandée (ex: 256M, 512M).</p>';
    }

    /**
     * Callback pour le champ temps d'exécution maximum
     */
    public function system_max_execution_time_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['system_max_execution_time']) ? $settings['system_max_execution_time'] : '30';
        echo '<input type="number" name="pdf_builder_settings[system_max_execution_time]" value="' . esc_attr($value) . '" class="small-text" min="10" max="300" />';
        echo '<p class="description">Temps maximum d\'exécution en secondes (10-300).</p>';
    }

    /**
     * Callback pour le champ validation des fichiers
     */
    public function security_file_validation_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['security_file_validation']) ? $settings['security_file_validation'] : '1';
        echo '<input type="checkbox" name="pdf_builder_settings[security_file_validation]" value="1" ' . checked($value, '1', false) . ' />';
        echo '<label>Activer la validation des fichiers uploadés</label>';
        echo '<p class="description">Vérifie les types et tailles des fichiers pour la sécurité.</p>';
    }

    /**
     * Callback pour le champ qualité PDF
     */
    public function pdf_quality_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['pdf_quality']) ? $settings['pdf_quality'] : 'high';
        echo '<select name="pdf_builder_settings[pdf_quality]">';
        echo '<option value="low" ' . selected($value, 'low', false) . '>Faible (72 DPI)</option>';
        echo '<option value="medium" ' . selected($value, 'medium', false) . '>Moyenne (150 DPI)</option>';
        echo '<option value="high" ' . selected($value, 'high', false) . '>Haute (300 DPI)</option>';
        echo '</select>';
        echo '<p class="description">Qualité d\'export des images dans le PDF.</p>';
    }

    /**
     * Callback pour le champ compression PDF
     */
    public function pdf_compression_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['pdf_compression']) ? $settings['pdf_compression'] : '1';
        echo '<input type="checkbox" name="pdf_builder_settings[pdf_compression]" value="1" ' . checked($value, '1', false) . ' />';
        echo '<label>Activer la compression des images</label>';
        echo '<p class="description">Réduit la taille du fichier PDF en compressant les images.</p>';
    }

    /**
     * Callback pour le champ largeur par défaut du canvas
     */
    public function canvas_default_width_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['canvas_default_width']) ? $settings['canvas_default_width'] : '800';
        echo '<input type="number" name="pdf_builder_settings[canvas_default_width]" value="' . esc_attr($value) . '" class="small-text" min="400" max="2000" /> px';
        echo '<p class="description">Largeur par défaut du canvas en pixels (400-2000).</p>';
    }

    /**
     * Callback pour le champ cache des templates
     */
    public function template_cache_enabled_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['template_cache_enabled']) ? $settings['template_cache_enabled'] : '1';
        echo '<input type="checkbox" name="pdf_builder_settings[template_cache_enabled]" value="1" ' . checked($value, '1', false) . ' />';
        echo '<label>Activer le cache des templates</label>';
        echo '<p class="description">Améliore les performances en mettant en cache les templates compilés.</p>';
    }

    /**
     * Callback pour le champ mode debug développeur
     */
    public function developer_debug_mode_field_callback()
    {
        $settings = get_option('pdf_builder_settings', array());
        $value = isset($settings['developer_debug_mode']) ? $settings['developer_debug_mode'] : '0';
        echo '<input type="checkbox" name="pdf_builder_settings[developer_debug_mode]" value="1" ' . checked($value, '1', false) . ' />';
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

        $user = wp_get_current_user();
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
        $user_id = get_current_user_id();
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
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.', 'pdf-builder-pro'));
        }

        // Récupérer l'onglet actuel depuis le formulaire
        $current_tab = $_POST['current_tab'] ?? '';

        if (empty($current_tab)) {
            return;
        }

        // Vérifier le nonce selon l'onglet
        $nonce_name = 'pdf_builder_save_settings';
        if (!isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_name)) {
            wp_die(__('Nonce de sécurité invalide.', 'pdf-builder-pro'));
        }

        // Récupérer les paramètres existants
        $settings = get_option('pdf_builder_settings', array());

        // Traiter selon l'onglet
        switch ($current_tab) {
            case 'general':
                // Paramètres généraux
                $settings['pdf_builder_company_name'] = sanitize_text_field($_POST['pdf_builder_company_name'] ?? '');
                $settings['pdf_builder_company_address'] = sanitize_textarea_field($_POST['pdf_builder_company_address'] ?? '');
                $settings['pdf_builder_company_phone'] = sanitize_text_field($_POST['pdf_builder_company_phone'] ?? '');
                $settings['pdf_builder_company_email'] = sanitize_email($_POST['pdf_builder_company_email'] ?? '');
                $settings['pdf_builder_company_phone_manual'] = sanitize_text_field($_POST['pdf_builder_company_phone_manual'] ?? '');
                $settings['pdf_builder_company_siret'] = sanitize_text_field($_POST['pdf_builder_company_siret'] ?? '');
                $settings['pdf_builder_company_vat'] = sanitize_text_field($_POST['pdf_builder_company_vat'] ?? '');
                $settings['pdf_builder_company_rcs'] = sanitize_text_field($_POST['pdf_builder_company_rcs'] ?? '');
                $settings['pdf_builder_company_capital'] = sanitize_text_field($_POST['pdf_builder_company_capital'] ?? '');
                break;

            case 'pdf':
                // Paramètres PDF
                $settings['pdf_builder_pdf_quality'] = sanitize_text_field($_POST['pdf_builder_pdf_quality'] ?? 'high');
                $settings['pdf_builder_default_format'] = sanitize_text_field($_POST['pdf_builder_default_format'] ?? 'A4');
                $settings['pdf_builder_default_orientation'] = sanitize_text_field($_POST['pdf_builder_default_orientation'] ?? 'portrait');
                break;

            case 'systeme':
                // Paramètres système
                $settings['pdf_builder_cache_enabled'] = isset($_POST['pdf_builder_cache_enabled']) ? '1' : '0';
                $settings['pdf_builder_cache_compression'] = isset($_POST['pdf_builder_cache_compression']) ? '1' : '0';
                $settings['pdf_builder_cache_auto_cleanup'] = isset($_POST['pdf_builder_cache_auto_cleanup']) ? '1' : '0';
                $settings['pdf_builder_cache_max_size'] = intval($_POST['pdf_builder_cache_max_size'] ?? 100);
                $settings['pdf_builder_cache_ttl'] = intval($_POST['pdf_builder_cache_ttl'] ?? 3600);
                $settings['pdf_builder_performance_auto_optimization'] = isset($_POST['pdf_builder_performance_auto_optimization']) ? '1' : '0';
                $settings['pdf_builder_systeme_auto_maintenance'] = isset($_POST['pdf_builder_systeme_auto_maintenance']) ? '1' : '0';
                break;

            // Ajouter d'autres onglets selon les besoins
            default:
                // Onglet non traité
                break;
        }

        // Sauvegarder les paramètres
        update_option('pdf_builder_settings', $settings);

        // Message de succès
        add_settings_error(
            'pdf_builder_settings',
            'settings_updated',
            __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro'),
            'updated'
        );

        // Rediriger pour éviter la resoumission du formulaire
        wp_redirect(add_query_arg(['page' => 'pdf-builder-settings', 'tab' => $current_tab, 'updated' => 'true']));
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
        
        // Récupérer le nombre de templates pour cet utilisateur
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_templates WHERE user_id = %d AND is_default = 0",
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
        register_post_type('pdf_template', [
            'labels' => [
                'name' => __('Templates PDF', 'pdf-builder-pro'),
                'singular_name' => __('Template PDF', 'pdf-builder-pro'),
                'add_new' => __('Nouveau Template', 'pdf-builder-pro'),
                'add_new_item' => __('Ajouter un Nouveau Template', 'pdf-builder-pro'),
                'edit_item' => __('Éditer le Template', 'pdf-builder-pro'),
                'new_item' => __('Nouveau Template', 'pdf-builder-pro'),
                'view_item' => __('Voir le Template', 'pdf-builder-pro'),
                'search_items' => __('Rechercher Templates', 'pdf-builder-pro'),
                'not_found' => __('Aucun template trouvé', 'pdf-builder-pro'),
                'not_found_in_trash' => __('Aucun template dans la corbeille', 'pdf-builder-pro'),
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
    private function initHooks()
    {
        // Enregistrer les paramètres
        add_action('admin_init', array($this, 'register_settings'));

        // 🔧 MIGRATION BASE DE DONNÉES - Gérée automatiquement par PDF_Builder_Migration_System
        // add_action('admin_init', [$this, 'run_database_migrations']);

        // 🔧 MISE À JOUR DES NOMS DE TEMPLATES (TEMPORAIRE)
        // Désactiver temporairement la mise à jour automatique des noms
        // add_action('admin_init', [$this, 'update_template_names']);

        // Enregistrer le custom post type pour les templates
        add_action('init', [$this, 'register_template_post_type']);

        // Hooks de base de l'admin (restent dans cette classe)
        add_action('admin_menu', [$this, 'addAdminMenu']);
        // Script loading is handled by AdminScriptLoader
        // add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 20);

        // Inclure le gestionnaire de modèles prédéfinis
        include_once plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/predefined-templates-manager.php';

        // Instancier le gestionnaire de modèles prédéfinis
        // new PDF_Builder_Predefined_Templates_Manager();

        // Enregistrer les hooks AJAX du Template_Manager avant même son instantiation
        // Cela garantit que les handlers AJAX seront disponibles immédiatement
        // ✅ Note: Seulement pdf_builder_save_template - React utilise celui-ci

// REMOVED: Conflit avec bootstrap.php - le handler de bootstrap.php gère maintenant le chargement
// Hook pdf_builder_get_template déjà enregistré plus haut

        // Hooks WooCommerce - Délégation vers le manager
        // Différer l'enregistrement jusqu'à ce que WooCommerce soit complètement chargé
        add_action('init', function() {
            if (did_action('plugins_loaded') && defined('WC_VERSION') && $this->woocommerce_integration !== null) {
                add_action('add_meta_boxes_shop_order', [$this->woocommerce_integration, 'addWoocommerceOrderMetaBox']);
                // Le hook HPOS peut ne pas exister dans toutes les versions, on l'enregistre seulement si WC_VERSION est défini et >= 7.1
                if (defined('WC_VERSION') && version_compare(WC_VERSION, '7.1', '>=')) {
                    add_action('add_meta_boxes_woocommerce_page_wc-orders', [$this->woocommerce_integration, 'addWoocommerceOrderMetaBox']);
                }
            }
        });

        // Hook pour la compatibilité avec les anciens liens template_id
        add_action('admin_init', [$this, 'handle_legacy_template_links']);
    }

    /**
     * Enregistre le custom post type pour les templates PDF
     */
    public function register_template_post_type()
    {
        $labels = array(
            'name' => __('Templates PDF', 'pdf-builder-pro'),
            'singular_name' => __('Template PDF', 'pdf-builder-pro'),
            'menu_name' => __('Templates', 'pdf-builder-pro'),
            'add_new' => __('Ajouter', 'pdf-builder-pro'),
            'add_new_item' => __('Ajouter un template PDF', 'pdf-builder-pro'),
            'edit_item' => __('Modifier le template', 'pdf-builder-pro'),
            'new_item' => __('Nouveau template', 'pdf-builder-pro'),
            'view_item' => __('Voir le template', 'pdf-builder-pro'),
            'search_items' => __('Rechercher des templates', 'pdf-builder-pro'),
            'not_found' => __('Aucun template trouvé', 'pdf-builder-pro'),
            'not_found_in_trash' => __('Aucun template dans la corbeille', 'pdf-builder-pro'),
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

        register_post_type('pdf_template', $args);
    }

    /**
     * Compatibilité avec les anciens liens template_id - méthode vide (système supprimé)
     */
    public function handleLegacyTemplateLinks()
    {
        // Méthode vide - plus de redirection nécessaire car éditeur unique
    }

    /**
     * Alias pour handleLegacyTemplateLinks() - compatibilité
     */
    public function handle_legacy_template_links()
    {
        return $this->handleLegacyTemplateLinks();
    }

    /**
     * Ajoute le menu d'administration
     */
    public function addAdminMenu()
    {
        // Éviter l'ajout multiple du menu
        if (self::$menu_added) {
            return;
        }
        self::$menu_added = true;

        // Menu principal avec icône distinctive - position remontée
        add_menu_page(__('PDF Builder Pro - Gestionnaire de PDF', 'pdf-builder-pro'), __('PDF Builder', 'pdf-builder-pro'), 'manage_options', 'pdf-builder-pro', [$this, 'adminPage'], 'dashicons-pdf', 25);

        // Page d'accueil (sous-menu principal masqué)
        add_submenu_page(
            'pdf-builder-pro',
            __('Accueil - PDF Builder Pro', 'pdf-builder-pro'),
            __('🏠 Accueil', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-pro', // Même slug que le menu principal
            [$this, 'adminPage']
        );

        // Éditeur React unique (accessible via lien direct, masqué du menu)
        add_submenu_page('pdf-builder-pro', __('Éditeur PDF', 'pdf-builder-pro'), __('🎨 Éditeur PDF', 'pdf-builder-pro'), 'manage_options', 'pdf-builder-react-editor', [$this, 'reactEditorPage']);

        // Masquer le menu de l'éditeur React globalement avec CSS
        add_action('admin_enqueue_scripts', function() {
            echo '<style>
                li a[href*="page=pdf-builder-react-editor"] {
                    display: none !important;
                }
                li a[href*="page=pdf-builder-react-editor"] + ul {
                    display: none !important;
                }
            </style>';
        });

        // Gestion des templates
        add_submenu_page('pdf-builder-pro', __('Templates PDF - PDF Builder Pro', 'pdf-builder-pro'), __('📋 Templates', 'pdf-builder-pro'), 'manage_options', 'pdf-builder-templates', [$this, 'templatesPage']);

        // Paramètres et configuration
        add_submenu_page('pdf-builder-pro', __('Paramètres - PDF Builder Pro', 'pdf-builder-pro'), __('⚙️ Paramètres', 'pdf-builder-pro'), 'manage_options', 'pdf-builder-settings', [$this, 'settings_page']);

        // Galerie de modèles (mode développeur uniquement)
        if (!empty(get_option('pdf_builder_settings')['pdf_builder_developer_enabled'])) {
            add_submenu_page(
                'pdf-builder-pro',
                __('Galerie de Modèles - PDF Builder Pro', 'pdf-builder-pro'),
                __('🖼️ Galerie', 'pdf-builder-pro'),
                'manage_options',
                'pdf-builder-predefined-templates',
                [$this->predefined_templates_manager ?? null, 'renderAdminPage']
            );
        }

    }



    /**
     * Page principale d'administration - Tableau de bord
     */
    public function adminPage()
    {
        if (!$this->checkAdminPermissions()) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Utiliser le renderer pour afficher la page d'administration
        if ($this->admin_page_renderer) {
            echo $this->admin_page_renderer->renderAdminPage();
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
        if (!$this->checkAdminPermissions()) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Get template ID and type from URL parameters
        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 1;
        $template_type = isset($_GET['template_type']) ? sanitize_text_field($_GET['template_type']) : 'custom';

        // Validate template type
        $valid_types = ['custom', 'predefined', 'system'];
        if (!in_array($template_type, $valid_types)) {
            $template_type = 'custom';
        }

        // Enqueue React scripts are now handled in enqueueAdminScripts()

        ?>
        <div class="wrap">
            <!-- PDF Builder Loading Screen -->
            <div id="pdf-builder-loader" style="
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(255, 255, 255, 0.95);
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                z-index: 999999;
                text-align: center;
            ">
                <div style="
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 20px;
                ">
                    <!-- Custom Spinner - no global classes -->
                    <div id="pdf-builder-custom-spinner" style="
                        width: 40px;
                        height: 40px;
                        border: 4px solid #f3f3f3;
                        border-top: 4px solid #007cba;
                        border-radius: 50%;
                        animation: pdfBuilderSpin 1s linear infinite;
                    "></div>
                    <p style="
                        color: #666;
                        font-size: 16px;
                        margin: 0;
                        font-weight: 500;
                        animation: none;
                        transform: none;
                    "><?php esc_html_e('Chargement de l\'éditeur PDF...', 'pdf-builder-pro'); ?> <span id="pdf-builder-timeout-counter">(10s)</span></p>
                </div>
            </div>

            <!-- Main React Editor Container -->
            <div id="pdf-builder-editor-container" style="
                display: block;
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 8px;
                min-height: 600px;
            ">
                <div id="pdf-builder-react-root"></div>
            </div>
        </div>

        <style>
        /* Custom spinner animation - completely isolated */
        @keyframes pdfBuilderSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Ensure no interference from global styles */
        #pdf-builder-custom-spinner {
            box-sizing: border-box !important;
        }
        </style>

        <script>
        (function() {
            'use strict';

            // Vérifier si on est sur la page de l'éditeur React
            const urlParams = new URLSearchParams(window.location.search);
            const currentPage = urlParams.get('page');

            

            // Ne charger React que sur la page appropriée
            if (currentPage !== 'pdf-builder-react-editor') {
                
                return;
            }

            

            // Simple loader management
            const loader = {
                element: null,
                editor: null,

                // Helper functions defined first
                isReactReady: function() {
                    return typeof window.initPDFBuilderReact === 'function';
                },

                isContainerReady: function() {
                    const container = document.getElementById('pdf-builder-react-root');
                    return !!container;
                },

                init: function() {
                    this.element = document.getElementById('pdf-builder-loader');
                    this.editor = document.getElementById('pdf-builder-editor-container');


                    if (!this.element || !this.editor) {
                        const allElements = document.querySelectorAll('[id*="pdf-builder"]');
                        allElements.forEach(el => 
                        return;
                    }

                    this.startChecking();
                },

                hide: function() {
                    if (this.element && this.editor) {
                        this.element.style.display = 'none';
                        this.editor.style.display = 'block';
                    }
                },

                startChecking: function() {
                    let attempts = 0;
                    const maxAttempts = 20; // 10 secondes à 500ms
                    let countdown = 10;

                    // Start countdown display
                    const counterElement = document.getElementById('pdf-builder-timeout-counter');
                    const countdownInterval = setInterval(() => {
                        countdown--;
                        if (counterElement && countdown >= 0) {
                            counterElement.textContent = `(${countdown}s)`;
                        }
                        if (countdown <= 0) {
                            clearInterval(countdownInterval);
                        }
                    }, 1000);

                    const checkInterval = setInterval(() => {
                        attempts++;
                        

                        if (this.isReactReady() && this.isContainerReady()) {
                            clearInterval(checkInterval);
                            clearInterval(countdownInterval);
                            
                            this.initializeReact();
                            return;
                        }

                        if (attempts >= maxAttempts) {
                            clearInterval(checkInterval);
                            clearInterval(countdownInterval);
                            
                            this.showLoadingError();
                        }
                    }, 500);
                },

                showLoadingError: function() {
                    // Hide loader
                    if (this.element) {
                        this.element.style.display = 'none';
                    }

                    // Show editor container with error message
                    if (this.editor) {
                        this.editor.style.display = 'block';
                        this.editor.innerHTML = `
                            <div style="
                                padding: 40px;
                                text-align: center;
                                background: #fff;
                                border: 2px solid #dc3232;
                                border-radius: 8px;
                                margin: 20px;
                                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                            ">
                                <h2 style="color: #dc3232; margin-top: 0;">❌ Erreur de chargement de l'éditeur PDF</h2>
                                <p style="color: #666; font-size: 16px; margin-bottom: 20px;">
                                    L'éditeur React n'a pas pu se charger dans les 10 secondes imparties.
                                </p>

                                <div style="
                                    background: #f8f9fa;
                                    border: 1px solid #e1e1e1;
                                    border-radius: 4px;
                                    padding: 15px;
                                    margin: 20px 0;
                                    text-align: left;
                                    font-family: monospace;
                                    font-size: 12px;
                                    color: #333;
                                ">
                                    <strong>Informations de débogage :</strong><br>
                                    • React disponible: ${typeof window.pdfBuilderReact !== 'undefined'}<br>
                                    • Fonction initPDFBuilderReact: ${typeof window.initPDFBuilderReact === 'function'}<br>
                                    • Container #pdf-builder-react-root: ${!!document.getElementById('pdf-builder-react-root')}<br>
                                    • Script React chargé: ${!!window.REACT_SCRIPT_LOADED}<br>
                                    • Heure de chargement: ${window.REACT_LOAD_TIME || 'Non défini'}<br>
                                    • Timestamp: ${new Date().toISOString()}
                                </div>

                                <div style="margin-top: 20px;">
                                    <button onclick="location.reload()" style="
                                        background: #007cba;
                                        color: white;
                                        border: none;
                                        padding: 10px 20px;
                                        border-radius: 4px;
                                        cursor: pointer;
                                        font-size: 14px;
                                        margin-right: 10px;
                                    ">🔄 Recharger la page</button>
                                </div>

                                <p style="color: #666; font-size: 12px; margin-top: 20px;">
                                    Vérifiez la console du navigateur (F12) pour plus de détails sur l'erreur.
                                </p>
                            </div>
                        `;
                    }

                    // Log detailed error information
                    
                    
                    
                    
                    
                    

                    // Check for script loading issues
                    const reactScripts = Array.from(document.querySelectorAll('script')).filter(s =>
                        s.src.includes('pdf-builder-react')
                    );
                    
                    reactScripts.forEach((script, index) => {
                        
                    });
                },

                initializeReact: function() {
                    
                    if (this.isReactReady()) {
                        

                        // Additional check: ensure container exists in DOM
                        const container = document.getElementById('pdf-builder-react-root');
                        if (!container) {
                            
                            
                            const allElements = document.querySelectorAll('[id*="pdf-builder"]');
                            allElements.forEach(el => 
                            return false;
                        }

                        
                        try {
                            const result = window.initPDFBuilderReact();
                            
                            
                            // Hide loader after successful init
                            if (result) {
                                this.hide();
                            }
                            return true;
                        } catch (error) {
                            
                            return false;
                        }
                    }
                    
                    return false;
                }
            };

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', () => {
                    loader.init();
                });
            } else {
                loader.init();
            }

            // Listen for React ready event
            document.addEventListener('pdfBuilderReactLoaded', function() {
                loader.initializeReact();
            });

        })();
        </script>
        <?php
    }

    /**
     * Page de gestion des templates
     */
    public function templatesPage()
    {
        if (!$this->checkAdminPermissions()) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Inclure la page dédiée de gestion des templates
        $templates_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/templates-page.php';
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
     * Page des paramètres
     */
    public function settings_page()
    {
        if (!$this->checkAdminPermissions()) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Traitement des formulaires personnalisés des templates
        $this->handle_settings_form_submission();

        // Récupération des paramètres généraux
        $settings = get_option('pdf_builder_settings', array());
        $current_user = wp_get_current_user();

        // Gestion des onglets via URL
        $current_tab = $_GET['tab'] ?? 'general';
        $valid_tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
        if (!in_array($current_tab, $valid_tabs)) {
            $current_tab = 'general';
        }

        ?>
        <div class="wrap">
            <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
            <p><?php _e('Configurez les paramètres de génération de vos documents PDF.', 'pdf-builder-pro'); ?></p>

            <form method="post" action="options.php">
                <?php settings_fields('pdf_builder_settings'); ?>

                <!-- Navigation par onglets moderne -->
                <h2 class="nav-tab-wrapper">
                    <div class="tabs-container">
                        <a href="?page=pdf-builder-settings&tab=general" class="nav-tab<?php echo $current_tab === 'general' ? ' nav-tab-active' : ''; ?>">
                            <span class="tab-icon">⚙️</span>
                            <span class="tab-text"><?php _e('Général', 'pdf-builder-pro'); ?></span>
                        </a>

                        <a href="?page=pdf-builder-settings&tab=licence" class="nav-tab<?php echo $current_tab === 'licence' ? ' nav-tab-active' : ''; ?>">
                            <span class="tab-icon">🔑</span>
                            <span class="tab-text"><?php _e('Licence', 'pdf-builder-pro'); ?></span>
                        </a>

                        <a href="?page=pdf-builder-settings&tab=systeme" class="nav-tab<?php echo $current_tab === 'systeme' ? ' nav-tab-active' : ''; ?>">
                            <span class="tab-icon">🖥️</span>
                            <span class="tab-text"><?php _e('Système', 'pdf-builder-pro'); ?></span>
                        </a>

                        <a href="?page=pdf-builder-settings&tab=securite" class="nav-tab<?php echo $current_tab === 'securite' ? ' nav-tab-active' : ''; ?>">
                            <span class="tab-icon">🔒</span>
                            <span class="tab-text"><?php _e('Sécurité', 'pdf-builder-pro'); ?></span>
                        </a>

                        <a href="?page=pdf-builder-settings&tab=pdf" class="nav-tab<?php echo $current_tab === 'pdf' ? ' nav-tab-active' : ''; ?>">
                            <span class="tab-icon">📄</span>
                            <span class="tab-text"><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></span>
                        </a>

                        <a href="?page=pdf-builder-settings&tab=contenu" class="nav-tab<?php echo $current_tab === 'contenu' ? ' nav-tab-active' : ''; ?>">
                            <span class="tab-icon">🎨</span>
                            <span class="tab-text"><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></span>
                        </a>

                        <a href="?page=pdf-builder-settings&tab=templates" class="nav-tab<?php echo $current_tab === 'templates' ? ' nav-tab-active' : ''; ?>">
                            <span class="tab-icon">📋</span>
                            <span class="tab-text"><?php _e('Templates', 'pdf-builder-pro'); ?></span>
                        </a>

                        <a href="?page=pdf-builder-settings&tab=developpeur" class="nav-tab<?php echo $current_tab === 'developpeur' ? ' nav-tab-active' : ''; ?>">
                            <span class="tab-icon">👨‍💻</span>
                            <span class="tab-text"><?php _e('Développeur', 'pdf-builder-pro'); ?></span>
                        </a>
                    </div>
                </h2>

                <!-- contenu des onglets moderne -->
                <div class="settings-content-wrapper">
                    <?php
                    switch ($current_tab) {
                        case 'general':
                            echo '<div id="pdf-builder-tab-general">';
                            $general_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-general.php';
                            if (file_exists($general_file)) {
                                include $general_file;
                            } else {
                                echo '<p>' . __('Fichier de paramètres général manquant.', 'pdf-builder-pro') . '</p>';
                            }
                            echo '</div>';
                            break;

                        case 'licence':
                            echo '<div id="pdf-builder-tab-licence">';
                            $licence_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-licence.php';
                            if (file_exists($licence_file)) {
                                include $licence_file;
                            } else {
                                echo '<p>' . __('Fichier de paramètres licence manquant.', 'pdf-builder-pro') . '</p>';
                            }
                            echo '</div>';
                            break;

                        case 'systeme':
                            echo '<div id="pdf-builder-tab-systeme">';
                            $systeme_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-systeme.php';
                            if (file_exists($systeme_file)) {
                                include $systeme_file;
                            } else {
                                echo '<p>' . __('Fichier de paramètres système manquant.', 'pdf-builder-pro') . '</p>';
                            }
                            echo '</div>';
                            break;

                        case 'securite':
                            echo '<div id="pdf-builder-tab-securite">';
                            $securite_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-securite.php';
                            if (file_exists($securite_file)) {
                                include $securite_file;
                            } else {
                                echo '<p>' . __('Fichier de paramètres sécurité manquant.', 'pdf-builder-pro') . '</p>';
                            }
                            echo '</div>';
                            break;

                        case 'pdf':
                            echo '<div id="pdf-builder-tab-pdf">';
                            $pdf_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-pdf.php';
                            if (file_exists($pdf_file)) {
                                include $pdf_file;
                            } else {
                                echo '<p>' . __('Fichier de paramètres PDF manquant.', 'pdf-builder-pro') . '</p>';
                            }
                            echo '</div>';
                            break;

                        case 'contenu':
                            echo '<div id="pdf-builder-tab-contenu">';
                            $contenu_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-contenu.php';
                            if (file_exists($contenu_file)) {
                                include $contenu_file;
                            } else {
                                echo '<p>' . __('Fichier de paramètres canvas manquant.', 'pdf-builder-pro') . '</p>';
                            }
                            echo '</div>';
                            break;

                        case 'templates':
                            echo '<div id="pdf-builder-tab-templates">';
                            $templates_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-templates.php';
                            if (file_exists($templates_file)) {
                                include $templates_file;
                            } else {
                                echo '<p>' . __('Fichier de paramètres templates manquant.', 'pdf-builder-pro') . '</p>';
                            }
                            echo '</div>';
                            break;

                        case 'developpeur':
                            echo '<div id="pdf-builder-tab-developpeur">';
                            $developpeur_file = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/settings-parts/settings-developpeur.php';
                            if (file_exists($developpeur_file)) {
                                include $developpeur_file;
                            } else {
                                echo '<p>' . __('Fichier de paramètres développeur manquant.', 'pdf-builder-pro') . '</p>';
                            }
                            echo '</div>';
                            break;

                        default:
                            echo '<p>' . __('Onglet non valide.', 'pdf-builder-pro') . '</p>';
                            break;
                    }
                    ?>

                    <!-- Bouton flottant personnalisé -->
                    <button type="submit" name="submit" id="pdf-builder-floating-save" class="pdf-builder-floating-save">
                        <?php _e('Enregistrer', 'pdf-builder-pro'); ?>
                    </button>
                </div>
            </form>

            <!-- Containers fictifs pour éviter les erreurs JS -->
            <div id="pdf-builder-tabs" style="display: none;"></div>
            <div id="pdf-builder-tab-content" style="display: none;"></div>

        </div> <!-- Fin du .wrap -->
        <?php
    }
}




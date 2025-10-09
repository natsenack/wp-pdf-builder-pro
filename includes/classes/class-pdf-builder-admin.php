<?php
/**
 * PDF Builder Pro - Interface d'administration simplifiée
 * Version 5.1.0 - Canvas uniquement
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

/**
 * Classe d'administration PDF Builder Pro - Version Simplifiée
 */
class PDF_Builder_Admin {

    /**
     * Instance singleton
     */
    private static $instance = null;

    /**
     * Instance de la classe principale
     */
    private $main;

    /**
     * Flag pour éviter les doublons de menu
     */
    private static $menu_added = false;

    /**
     * Flag pour éviter le rendu multiple de la page éditeur
     */
    private static $editor_page_rendered = false;

    /**
     * Flag pour éviter le rendu multiple de la page admin
     */
    private static $admin_page_rendered = false;

    /**
     * Constructeur privé pour singleton
     */
    private function __construct($main_instance) {
        error_log('PDF_Builder_Admin constructor called');
        $this->main = $main_instance;
        $this->init_hooks();
    }

    /**
     * Obtenir l'instance singleton
     */
    public static function getInstance($main_instance = null) {
        error_log('PDF_Builder_Admin getInstance called');
        if (self::$instance === null) {
            error_log('PDF_Builder_Admin creating new instance');
            self::$instance = new self($main_instance);
        }
        return self::$instance;
    }

    /**
     * Vérifie les permissions d'administration
     */
    private function check_admin_permissions() {
        // Si le mode debug est activé, pas de vérification
        if (defined('PDF_BUILDER_DEBUG_MODE') && PDF_BUILDER_DEBUG_MODE) {
            return;
        }

        if (!is_user_logged_in() || !current_user_can('read')) {
            wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
        }
    }

    /**
     * Initialise les hooks WordPress
     */
    private function init_hooks() {
        error_log('PDF Builder Admin: init_hooks appelée');
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 20);
        add_action('wp_ajax_pdf_builder_pro_generate_pdf', [$this, 'ajax_generate_pdf_from_canvas']);
        add_action('wp_ajax_pdf_builder_preview_test', 'pdf_builder_handle_preview_ajax');
        add_action('wp_ajax_nopriv_pdf_builder_preview_test', 'pdf_builder_handle_preview_ajax');
        error_log('PDF Builder Admin: AJAX actions registered - using global function');
        // Test if the action is registered
        global $wp_filter;
        if (isset($wp_filter['wp_ajax_pdf_builder_preview_test'])) {
            error_log('PDF Builder Admin: wp_ajax_pdf_builder_preview_test action is registered in wp_filter');
        } else {
            error_log('PDF Builder Admin: wp_ajax_pdf_builder_preview_test action is NOT registered in wp_filter');
        }
        add_action('wp_ajax_pdf_builder_pro_download_pdf', [$this, 'ajax_download_pdf']);
        add_action('wp_ajax_pdf_builder_pro_save_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_pdf_builder_pro_load_template', [$this, 'ajax_load_template']);
        add_action('wp_ajax_pdf_builder_get_templates', [$this, 'ajax_get_templates']);
        add_action('wp_ajax_pdf_builder_delete_template', [$this, 'ajax_delete_template']);
        add_action('wp_ajax_pdf_builder_duplicate_template', [$this, 'ajax_duplicate_template']);
        add_action('wp_ajax_pdf_builder_set_default_template', [$this, 'ajax_set_default_template']);
        add_action('wp_ajax_pdf_builder_get_template_data', [$this, 'ajax_get_template_data']);
        add_action('wp_ajax_pdf_builder_update_template_params', [$this, 'ajax_update_template_params']);
        add_action('wp_ajax_pdf_builder_get_authors', [$this, 'ajax_get_authors']);
        add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajax_flush_rest_cache']);

        // Actions de maintenance
        add_action('wp_ajax_pdf_builder_check_database', [$this, 'ajax_check_database']);
        add_action('wp_ajax_pdf_builder_repair_database', [$this, 'ajax_repair_database']);
        add_action('wp_ajax_pdf_builder_execute_sql_repair', [$this, 'ajax_execute_sql_repair']);
        add_action('wp_ajax_pdf_builder_clear_cache', [$this, 'ajax_clear_cache']);
        add_action('wp_ajax_pdf_builder_optimize_database', [$this, 'ajax_optimize_database']);
        add_action('wp_ajax_pdf_builder_view_logs', [$this, 'ajax_view_logs']);
        add_action('wp_ajax_pdf_builder_clear_logs', [$this, 'ajax_clear_logs']);

        // Diagnostic action
        add_action('wp_ajax_pdf_builder_diagnose_template', [$this, 'ajax_diagnose_template']);

        // Actions de gestion des rôles
        add_action('wp_ajax_pdf_builder_reset_role_permissions', [$this, 'ajax_reset_role_permissions']);
        add_action('wp_ajax_pdf_builder_bulk_assign_permissions', [$this, 'ajax_bulk_assign_permissions']);

        // WooCommerce integration hooks
        if (class_exists('WooCommerce')) {
            // Support for both legacy and HPOS order systems
            add_action('add_meta_boxes_shop_order', [$this, 'add_woocommerce_order_meta_box']);
            add_action('add_meta_boxes_woocommerce_page_wc-orders', [$this, 'add_woocommerce_order_meta_box']);
            add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf']);
            add_action('wp_ajax_pdf_builder_preview_order_pdf', [$this, 'ajax_preview_order_pdf']);
        }
    }

    /**
     * Ajoute le menu d'administration
     */
    public function add_admin_menu() {
        // Éviter les doublons de menu
        if (self::$menu_added) {
            return;
        }
        self::$menu_added = true;
        // Menu principal avec icône distinctive
        add_menu_page(
            __('PDF Builder Pro - Gestionnaire de PDF', 'pdf-builder-pro'),
            __('📄 PDF Builder', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-pro',
            [$this, 'admin_page'],
            'dashicons-pdf',
            30
        );

        // Page d'accueil (sous-menu principal masqué)
        add_submenu_page(
            'pdf-builder-pro',
            __('Accueil - PDF Builder Pro', 'pdf-builder-pro'),
            __('🏠 Accueil', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-pro', // Même slug que le menu principal
            [$this, 'admin_page']
        );

        // Éditeur Canvas (outil principal)
        add_submenu_page(
            'pdf-builder-pro',
            __('Éditeur Canvas - PDF Builder Pro', 'pdf-builder-pro'),
            __('🎨 Éditeur Canvas', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-editor',
            [$this, 'template_editor_page']
        );

        // Gestion des templates
        add_submenu_page(
            'pdf-builder-pro',
            __('Templates PDF - PDF Builder Pro', 'pdf-builder-pro'),
            __('📋 Templates', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-templates',
            [$this, 'templates_page']
        );

        // Paramètres et configuration
        add_submenu_page(
            'pdf-builder-pro',
            __('Paramètres - PDF Builder Pro', 'pdf-builder-pro'),
            __('⚙️ Paramètres', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-settings',
            [$this, 'settings_page']
        );

        // Outils de diagnostic
        add_submenu_page(
            'pdf-builder-pro',
            __('Diagnostic - PDF Builder Pro', 'pdf-builder-pro'),
            __('🔧 Diagnostic', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-diagnostic',
            [$this, 'diagnostic_page']
        );

        // Tests WooCommerce
        add_submenu_page(
            'pdf-builder-pro',
            __('Tests WooCommerce - PDF Builder Pro', 'pdf-builder-pro'),
            __('🛒 Tests WC', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-woocommerce-test',
            [$this, 'woocommerce_test_page']
        );
    }

    /**
     * Page principale d'administration - Tableau de bord
     */
    public function admin_page() {
        // Éviter le rendu multiple de la page
        if (self::$admin_page_rendered) {
            return;
        }
        self::$admin_page_rendered = true;

        $this->check_admin_permissions();

        // Statistiques de base (simulées pour l'instant)
        $stats = [
            'templates' => 5, // À remplacer par une vraie requête
            'documents' => 23,
            'today' => 3
        ];
        ?>
        <div class="wrap">
            <div class="pdf-builder-dashboard">
                <div class="dashboard-header">
                    <h1>📄 PDF Builder Pro</h1>
                    <p class="dashboard-subtitle">Constructeur de PDF professionnel avec éditeur visuel avancé</p>
                </div>

                <!-- Statistiques rapides -->
                <div class="dashboard-stats">
                    <div class="stat-card">
                        <div class="stat-icon">📋</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['templates']; ?></div>
                            <div class="stat-label">Templates</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📄</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['documents']; ?></div>
                            <div class="stat-label">Documents générés</div>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon">📈</div>
                        <div class="stat-content">
                            <div class="stat-number"><?php echo $stats['today']; ?></div>
                            <div class="stat-label">Aujourd'hui</div>
                        </div>
                    </div>
                </div>

                <!-- Actions principales -->
                <div class="dashboard-actions">
                    <div class="action-card primary">
                        <h3>🎨 Créer un nouveau PDF</h3>
                        <p>Utilisez notre éditeur visuel intuitif pour concevoir vos documents</p>
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor'); ?>" class="button button-primary">
                            Ouvrir l'Éditeur Canvas
                        </a>
                    </div>

                    <div class="action-card">
                        <h3>📋 Gérer les Templates</h3>
                        <p>Créez, modifiez et organisez vos modèles de documents</p>
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>" class="button button-secondary">
                            Voir les Templates
                        </a>
                    </div>

                    <div class="action-card">
                        <h3>📄 Documents Récents</h3>
                        <p>Consultez et téléchargez vos PDF générés récemment</p>
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-documents'); ?>" class="button button-secondary">
                            Voir les Documents
                        </a>
                    </div>
                </div>

                <!-- Guide rapide -->
                <div class="dashboard-guide">
                    <h3>🚀 Guide de démarrage rapide</h3>
                    <div class="guide-steps">
                        <div class="step">
                            <span class="step-number">1</span>
                            <div class="step-content">
                                <h4>Créez votre premier template</h4>
                                <p>Utilisez l'éditeur canvas pour concevoir votre modèle PDF</p>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">2</span>
                            <div class="step-content">
                                <h4>Ajoutez vos données</h4>
                                <p>Importez vos informations depuis WooCommerce ou saisissez-les manuellement</p>
                            </div>
                        </div>
                        <div class="step">
                            <span class="step-number">3</span>
                            <div class="step-content">
                                <h4>Exportez votre PDF</h4>
                                <p>Générez et téléchargez votre document professionnel</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .pdf-builder-dashboard {
                    max-width: 1200px;
                }

                .dashboard-header {
                    text-align: center;
                    margin-bottom: 30px;
                }

                .dashboard-subtitle {
                    color: #666;
                    font-size: 16px;
                    margin-top: 10px;
                }

                .dashboard-stats {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }

                .stat-card {
                    background: #fff;
                    border: 1px solid #e1e1e1;
                    border-radius: 8px;
                    padding: 20px;
                    display: flex;
                    align-items: center;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }

                .stat-icon {
                    font-size: 32px;
                    margin-right: 15px;
                }

                .stat-number {
                    font-size: 28px;
                    font-weight: bold;
                    color: #2271b1;
                }

                .stat-label {
                    color: #666;
                    font-size: 14px;
                }

                .dashboard-actions {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 20px;
                    margin-bottom: 30px;
                }

                .action-card {
                    background: #fff;
                    border: 1px solid #e1e1e1;
                    border-radius: 8px;
                    padding: 25px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }

                .action-card.primary {
                    border-color: #2271b1;
                    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
                }

                .action-card h3 {
                    margin-top: 0;
                    color: #1d2327;
                }

                .action-card p {
                    color: #666;
                    margin-bottom: 15px;
                }

                .dashboard-guide {
                    background: #fff;
                    border: 1px solid #e1e1e1;
                    border-radius: 8px;
                    padding: 25px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                }

                .guide-steps {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 20px;
                    margin-top: 20px;
                }

                .step {
                    display: flex;
                    align-items: flex-start;
                }

                .step-number {
                    background: #3b82f6;
                    color: white;
                    width: 30px;
                    height: 30px;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: bold;
                    margin-right: 15px;
                    flex-shrink: 0;
                }

                .step-content h4 {
                    margin: 0 0 5px 0;
                    color: #1d2327;
                }

                .step-content p {
                    margin: 0;
                    color: #666;
                    font-size: 14px;
                }
            </style>
        </div>
        <?php
    }

    /**
     * Templates page
     */
    public function templates_page() {
        $this->check_admin_permissions();
        include plugin_dir_path(dirname(__FILE__)) . 'templates-page.php';
    }

    /**
     * Settings page
     */
    public function settings_page() {
        $this->check_admin_permissions();
        include plugin_dir_path(dirname(__FILE__)) . 'settings-page.php';
    }

    /**
     * Diagnostic page
     */
    public function diagnostic_page() {
        $this->check_admin_permissions();

        // Handle form submission
        $diagnostic_output = '';
        if (isset($_POST['diagnose_template']) && wp_verify_nonce($_POST['diagnostic_nonce'], 'pdf_builder_diagnostic')) {
            $template_id = intval($_POST['template_id']);
            if ($template_id > 0) {
                $diagnostic_output = $this->diagnose_template_json($template_id);
            }
        }

        ?>
        <div class="wrap">
            <h1><?php _e('🔧 Outil de Diagnostic - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

            <div class="pdf-builder-diagnostic">
                <div class="diagnostic-header">
                    <p><?php _e('Utilisez cet outil pour diagnostiquer les problèmes avec les templates PDF.', 'pdf-builder-pro'); ?></p>
                </div>

                <div class="diagnostic-form">
                    <h2><?php _e('Diagnostiquer un Template', 'pdf-builder-pro'); ?></h2>
                    <form method="post" action="">
                        <?php wp_nonce_field('pdf_builder_diagnostic', 'diagnostic_nonce'); ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="template_id"><?php _e('ID du Template', 'pdf-builder-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="number" id="template_id" name="template_id" value="<?php echo isset($_POST['template_id']) ? intval($_POST['template_id']) : '131'; ?>" min="1" required>
                                    <p class="description"><?php _e('Entrez l\'ID du template à diagnostiquer.', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="diagnose_template" class="button button-primary" value="<?php _e('Diagnostiquer', 'pdf-builder-pro'); ?>">
                        </p>
                    </form>
                </div>

                <?php if (!empty($diagnostic_output)): ?>
                <div class="diagnostic-results">
                    <h2><?php _e('Résultats du Diagnostic', 'pdf-builder-pro'); ?></h2>
                    <div class="diagnostic-output">
                        <?php echo $diagnostic_output; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <style>
        .pdf-builder-diagnostic {
            max-width: 1200px;
        }
        .diagnostic-header {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .diagnostic-form {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .diagnostic-results {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .diagnostic-output {
            background: #f9f9f9;
            padding: 15px;
            border: 1px solid #e1e1e1;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 600px;
            overflow-y: auto;
        }
        </style>
        <?php
    }

    /**
     * Template Editor page (React/TypeScript)
     */
    public function template_editor_page() {
        // Éviter le rendu multiple de la page
        if (self::$editor_page_rendered) {
            return;
        }
        self::$editor_page_rendered = true;

        $this->check_admin_permissions();
        include PDF_BUILDER_PLUGIN_DIR . 'includes/template-editor.php';
    }

    /**
     * Charge les scripts et styles d'administration
     */
    public function enqueue_admin_scripts($hook) {
        // Liste des pages de notre plugin
        $our_pages = [
            'toplevel_page_pdf-builder-pro',
            'pdf-builder_page_pdf-builder-editor',
            'pdf-builder_page_pdf-builder-templates',
            'pdf-builder_page_pdf-builder-settings'
        ];
        
        // Le hook peut contenir des emojis encodés, on va nettoyer pour la comparaison
        $clean_hook = urldecode($hook);
        $clean_hook = preg_replace('/^[^\w]*/', '', $clean_hook); // Retire les emojis du début

        // Load React/TypeScript on ALL PDF Builder pages for debugging
        if (in_array($clean_hook, $our_pages) || strpos($clean_hook, 'pdf-builder') !== false) {
            $this->enqueue_react_scripts();

            // Pour la page éditeur, NE PAS charger les scripts canvas - React gère tout
            if ($clean_hook === 'pdf-builder_page_pdf-builder-editor') {
                return; // Ne pas charger les scripts canvas pour éviter les conflits
            }
            // Pour les autres pages (y compris settings), continuer avec les scripts canvas
        }

        // Charger seulement sur nos pages principales
        if (!in_array($clean_hook, $our_pages)) {
            return;
        }

        // jQuery et jQuery UI pour toutes les pages admin de notre plugin
        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-ui-core');

        // Charger jQuery UI Dialog pour la page des paramètres
        if ($hook === 'pdf-builder_page_pdf-builder-settings') {
            // Plus besoin de jQuery UI Dialog, on utilise une modale simple
        }

        // Scripts supplémentaires seulement pour les pages qui en ont besoin
        if ($clean_hook === 'pdf-builder_page_pdf-builder-templates') {
            // Pour la page templates, on a besoin que de jQuery et jQuery UI
            // Le JavaScript inline dans templates-page.php gère le reste
        }
        
        // Scripts du canvas pour TOUTES les pages PDF Builder (y compris l'éditeur)
        wp_enqueue_script('jquery-ui-draggable');
        wp_enqueue_script('jquery-ui-resizable');

        wp_enqueue_script(
            'pdf-builder-canvas',
            PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-canvas.js',
            array('jquery', 'jquery-ui-draggable', 'jquery-ui-resizable'),
            PDF_BUILDER_PRO_VERSION,
            true
        );

        wp_enqueue_script(
            'pdf-builder-unified-config',
            PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-unified-config.js',
            array('jquery'),
            PDF_BUILDER_PRO_VERSION,
            true
        );

        wp_enqueue_script(
            'pdf-builder-utils',
            PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-utils.js',
            array('jquery'),
            PDF_BUILDER_PRO_VERSION,
            true
        );

        // Styles
        wp_enqueue_style(
            'pdf-builder-canvas',
            PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-canvas.css',
            array(),
            PDF_BUILDER_PRO_VERSION
        );

        // wp_enqueue_style(
        //     'pdf-builder-preview-advanced',
        //     PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-preview-advanced.css',
        //     array(),
        //     PDF_BUILDER_PRO_VERSION
        // );

        // Styles personnalisés pour la page d'accueil
        wp_add_inline_style('pdf-builder-canvas', '
            .pdf-builder-welcome {
                background: #fff;
                border: 1px solid #e1e1e1;
                border-radius: 8px;
                padding: 30px;
                margin: 20px 0;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }

            .pdf-builder-hero {
                text-align: center;
                margin-bottom: 40px;
            }

            .pdf-builder-hero h2 {
                color: #1d2327;
                font-size: 2em;
                margin-bottom: 10px;
            }

            .pdf-builder-hero p {
                color: #646970;
                font-size: 1.1em;
                margin-bottom: 30px;
            }

            .pdf-builder-actions {
                display: flex;
                gap: 15px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .button-hero {
                padding: 12px 24px;
                font-size: 1.1em;
                border-radius: 6px;
                text-decoration: none;
                display: inline-block;
                transition: all 0.3s ease;
            }

            .button-hero:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            }

            .pdf-builder-features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-top: 40px;
            }

            .feature-card {
                background: #f8f9fa;
                border: 1px solid #e1e1e1;
                border-radius: 6px;
                padding: 20px;
                text-align: center;
                transition: transform 0.3s ease;
            }

            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            }

            .feature-card h3 {
                color: #1d2327;
                margin-bottom: 10px;
            }

            .feature-card p {
                color: #646970;
                margin: 0;
            }

            @media (max-width: 768px) {
                .pdf-builder-actions {
                    flex-direction: column;
                    align-items: center;
                }

                .pdf-builder-features {
                    grid-template-columns: 1fr;
                }
            }
        ');

        // Variables JavaScript
        wp_localize_script('pdf-builder-canvas', 'pdf_builder_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_canvas_nonce'),
            'strings' => array(
                'generating_pdf' => 'Génération du PDF en cours...',
                'pdf_generated' => 'PDF généré avec succès!',
                'error' => 'Une erreur est survenue.'
            )
        ));

        // Initialisation du canvas avec vérifications de sécurité
        wp_add_inline_script('pdf-builder-canvas', '
            // Fonction utilitaire pour attendre que wp soit disponible
            function waitForWp(callback, maxAttempts = 50) {
                let attempts = 0;
                const checkWp = function() {
                    attempts++;
                    if (typeof wp !== "undefined") {
                        callback();
                    } else if (attempts < maxAttempts) {
                        setTimeout(checkWp, 100); // Attendre 100ms et réessayer
                    } else {
                        console.warn("PDF Builder Pro: wp n\'est toujours pas disponible après", maxAttempts * 100, "ms");
                    }
                };
                checkWp();
            }

            jQuery(document).ready(function($) {
                // Attendre que wp soit disponible avant d\'initialiser
                waitForWp(function() {
                    // DISABLED: Auto-initialization now handled by React component
                    // Vérifier que toutes les dépendances sont disponibles ET qu\'il y a un élément canvas
                    /*
                    if (typeof PDF_BUILDER_CANVAS !== "undefined" &&
                        typeof PDF_BUILDER_CANVAS.init === "function" &&
                        typeof pdf_builder_ajax !== "undefined" &&
                        jQuery("#pdf-canvas").length > 0) {
                        try {
                            PDF_BUILDER_CANVAS.init();
                        } catch (error) {
                            console.warn("PDF Builder Pro: Erreur lors de l\'initialisation du canvas:", error);
                        }
                    } else if (jQuery("#pdf-canvas").length === 0) {
                    } else {
                        console.warn("PDF Builder Pro: Dépendances manquantes pour l\'initialisation du canvas");
                    }
                    */
                
                });
            });
        ');
    }

    /**
     * Enfile les scripts React pour les pages d'administration
     */
    private function enqueue_react_scripts() {
        // Charger React depuis CDN (plus fiable que les versions locales)
        wp_enqueue_script('react', 'https://unpkg.com/react@18/umd/react.production.min.js', [], '18.2.0', true);
        wp_enqueue_script('react-dom', 'https://unpkg.com/react-dom@18/umd/react-dom.production.min.js', ['react'], '18.2.0', true);

        // Charger le script principal React du plugin
        $script_path = PDF_BUILDER_PRO_ASSETS_URL . 'js/pdf-builder-pro.js';
        $script_version = PDF_BUILDER_PRO_VERSION;
        if (defined('PDF_BUILDER_PRO_ASSETS_PATH') && file_exists(PDF_BUILDER_PRO_ASSETS_PATH . 'js/pdf-builder-pro.js')) {
            $script_version = filemtime(PDF_BUILDER_PRO_ASSETS_PATH . 'js/pdf-builder-pro.js');
        }

        wp_enqueue_script(
            'pdf-builder-pro-react',
            $script_path,
            ['react', 'react-dom', 'jquery'],
            $script_version,
            true
        );

        // Localiser le script avec les données nécessaires
        wp_localize_script('pdf-builder-pro-react', 'pdfBuilderAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_nonce'),
            'strings' => [
                'loading' => __('Chargement...', 'pdf-builder-pro'),
                'error' => __('Erreur', 'pdf-builder-pro'),
                'success' => __('Succès', 'pdf-builder-pro'),
                'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer cet élément ?', 'pdf-builder-pro')
            ]
        ]);

        // Charger le script admin compilé (avec les dépendances)
        $admin_script_path = PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js';
        $admin_script_version = PDF_BUILDER_PRO_VERSION;
        if (defined('PDF_BUILDER_PRO_ASSETS_PATH') && file_exists(PDF_BUILDER_PRO_ASSETS_PATH . 'js/dist/pdf-builder-admin.js')) {
            $admin_script_version = filemtime(PDF_BUILDER_PRO_ASSETS_PATH . 'js/dist/pdf-builder-admin.js');
        }

        wp_enqueue_script(
            'pdf-builder-admin',
            $admin_script_path,
            ['react', 'react-dom', 'jquery', 'pdf-builder-pro-react'],
            $admin_script_version,
            true
        );

        // Styles CSS
        $css_path = PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css';
        $css_version = PDF_BUILDER_PRO_VERSION;
        if (defined('PDF_BUILDER_PRO_ASSETS_PATH') && file_exists(PDF_BUILDER_PRO_ASSETS_PATH . 'css/pdf-builder-admin.css')) {
            $css_version = filemtime(PDF_BUILDER_PRO_ASSETS_PATH . 'css/pdf-builder-admin.css');
        }

        wp_enqueue_style(
            'pdf-builder-admin',
            $css_path,
            [],
            $css_version
        );

        // Debug logging
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('PDF Builder Admin: pdf-builder-pro.js enqueued from: ' . $script_path);
            error_log('PDF Builder Admin: pdf-builder-admin.js enqueued from: ' . $admin_script_path);
        }
    }

    /**
     * AJAX - Génère un PDF depuis le canvas
     */
    public function ajax_generate_pdf_from_canvas() {
        $this->check_admin_permissions();

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        // Récupérer les données du template
        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';

        if (empty($template_data)) {
            wp_send_json_error('Aucune donnée template reçue');
        }

        try {
            // Décoder les données JSON
            $template = json_decode($template_data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error('Données template invalides');
                return;
            }

            // Générer le PDF
            $pdf_filename = 'pdf-builder-' . time() . '.pdf';
            $pdf_path = $this->generate_pdf_from_template_data($template, $pdf_filename);

            if ($pdf_path && file_exists($pdf_path)) {
                wp_send_json_success(array(
                    'message' => 'PDF généré avec succès',
                    'filename' => $pdf_filename,
                    'url' => wp_upload_dir()['baseurl'] . '/pdf-builder/' . $pdf_filename
                ));
            } else {
                wp_send_json_error('Erreur lors de la génération du PDF');
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Aperçu du PDF
     */
    public function ajax_preview_pdf() {
        // This method is now handled by the global function
        // Keeping it for compatibility
    }

    /**
     * AJAX - Téléchargement du PDF
     */
    public function ajax_download_pdf() {
        $this->check_admin_permissions();

        $template_id = isset($_GET['template_id']) ? sanitize_text_field($_GET['template_id']) : 'preview';

        // Créer le répertoire de téléchargement s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_filename = 'pdf-builder-' . $template_id . '.pdf';
        $pdf_path = $pdf_dir . '/' . $pdf_filename;

        if (file_exists($pdf_path)) {
            // Envoyer le fichier PDF
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $pdf_filename . '"');
            header('Content-Length: ' . filesize($pdf_path));
            header('Cache-Control: private, max-age=0, must-revalidate');
            header('Pragma: public');

            readfile($pdf_path);
            exit;
        } else {
            wp_die('Fichier PDF non trouvé');
        }
    }

    /**
     * AJAX - Sauvegarder le template
     */
    public function ajax_save_template() {
        $this->check_admin_permissions();

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if (empty($template_data) || empty($template_name)) {
            wp_send_json_error('Données template ou nom manquant');
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $data = array(
            'name' => $template_name,
            'template_data' => $template_data,
            'updated_at' => current_time('mysql')
        );

        if ($template_id > 0) {
            // Update existing template
            $result = $wpdb->update($table_templates, $data, array('id' => $template_id));
        } else {
            // Create new template
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_templates, $data);
            $template_id = $wpdb->insert_id;
        }

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => 'Template sauvegardé avec succès',
                'template_id' => $template_id
            ));
        } else {
            wp_send_json_error('Erreur lors de la sauvegarde du template');
        }
    }

    /**
     * AJAX - Charger un template
     */
    public function ajax_load_template() {
        $this->check_admin_permissions();

        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        if ($template_id <= 0) {
            wp_send_json_error('ID template invalide');
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            PDF_Builder_Debug_Helper::safe_wpdb_prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if ($template) {
            $template_data = json_decode($template['template_data'], true);
            if ($template_data === null && json_last_error() !== JSON_ERROR_NONE) {
                $json_error = json_last_error_msg();
                $raw_data_preview = substr($template['template_data'], 0, 200) . (strlen($template['template_data']) > 200 ? '...' : '');
                error_log('PDF Builder Pro: Invalid JSON in template_data for template ID ' . $template_id . '. JSON error: ' . $json_error . '. Raw data preview: ' . $raw_data_preview);
                wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error . ' - Aperçu: ' . $raw_data_preview);
            }
            wp_send_json_success(array(
                'template' => $template_data,
                'name' => $template['name']
            ));
        } else {
            wp_send_json_error('Template non trouvé');
        }
    }

    /**
     * Vide le cache des routes REST
     */
    public function ajax_flush_rest_cache() {
        $this->check_admin_permissions();

        $results = array();

        // Vider le cache des routes REST
        if (function_exists('rest_get_server')) {
            $server = rest_get_server();
            if (method_exists($server, 'get_routes')) {
                $routes = $server->get_routes();
                $results['routes_count'] = count($routes);

                // Forcer le rechargement des routes
                if (function_exists('rest_api_init')) {
                    rest_api_init();
                    $results['rest_cache'] = 'flushed';
                }
            }
        }

        // Vider le cache objet si disponible
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
            $results['object_cache'] = 'flushed';
        }

        // Vider les transients liés aux routes
        global $wpdb;
        if ($wpdb) {
            $deleted_transients = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wp_rest%'");
            $deleted_timeouts = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_wp_rest%'");
            $results['transients_deleted'] = $deleted_transients;
            $results['timeouts_deleted'] = $deleted_timeouts;
        }

        // Vider le cache des rewrite rules
        global $wp_rewrite;
        if ($wp_rewrite) {
            $wp_rewrite->flush_rules();
            $results['rewrite_rules'] = 'flushed';
        }

        wp_send_json_success(array(
            'message' => 'Cache REST vidé avec succès',
            'results' => $results
        ));
    }

    /**
     * Génère un PDF depuis les données du template
     */
    private function generate_pdf_from_template_data($template, $filename) {
        // Créer le répertoire de stockage s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_path = $pdf_dir . '/' . $filename;

        // Pour l'instant, créer un fichier PDF basique avec HTML2PDF ou TCPDF
        // Ici nous simulons la génération - à remplacer par une vraie bibliothèque PDF

        // Générer le HTML d'abord
        $html_content = $this->generate_html_from_template_data($template);

        // Utiliser une bibliothèque PDF si disponible
        if (class_exists('TCPDF')) {
            // Utiliser TCPDF si disponible
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator('PDF Builder Pro');
            $pdf->SetAuthor('PDF Builder Pro');
            $pdf->SetTitle('Generated PDF');

            // Appliquer les marges d'impression depuis le template
            $pdf_margins = ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20];
            if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
                $firstPage = $template['pages'][0];
                if (isset($firstPage['margins'])) {
                    $pdf_margins = $firstPage['margins'];
                }
            }
            // Convertir les marges en unités TCPDF (points par défaut)
            $pdf->SetMargins($pdf_margins['left'], $pdf_margins['top'], $pdf_margins['right']);
            $pdf->SetAutoPageBreak(true, $pdf_margins['bottom']);

            $pdf->AddPage();
            $pdf->writeHTML($html_content, true, false, true, false, '');
            $pdf->Output($pdf_path, 'F');

            return $pdf_path;
        } elseif (function_exists('wkhtmltopdf')) {
            // Utiliser wkhtmltopdf si disponible
            // Simulation pour l'instant
            file_put_contents($pdf_path, 'PDF Content Placeholder');
            return $pdf_path;
        } else {
            // Erreur: aucune bibliothèque PDF disponible
            error_log('PDF Builder Pro: Aucune bibliothèque PDF disponible (TCPDF ou wkhtmltopdf requis)');
            throw new Exception('Bibliothèque PDF non disponible. Veuillez installer TCPDF via Composer ou wkhtmltopdf.');
        }
    }

    /**
     * Génère du HTML depuis les données du template
     */
    private function generate_html_from_template_data($template, $order_id = null) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>PDF Preview</title>';

        // Gestion des marges d'impression - utiliser la première page
        $margins = ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20];
        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            if (isset($firstPage['margins'])) {
                $margins = $firstPage['margins'];
            }
        }
        $margin_css = sprintf('margin: 0; padding: %dpx %dpx %dpx %dpx;', $margins['top'], $margins['right'], $margins['bottom'], $margins['left']);

        $html .= '<style>body { font-family: Arial, sans-serif; ' . $margin_css . ' } .pdf-element { position: absolute; }</style>';
        $html .= '</head><body>';

        // Utiliser les éléments de la première page
        $elements = [];
        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            $elements = $firstPage['elements'] ?? [];
        } elseif (isset($template['elements']) && is_array($template['elements'])) {
            // Fallback pour l'ancienne structure
            $elements = $template['elements'];
        }

        if (is_array($elements)) {
            foreach ($elements as $element) {
                $style = sprintf(
                    'left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                    $element['position']['x'],
                    $element['position']['y'],
                    $element['size']['width'],
                    $element['size']['height']
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

                // Remplacer les variables dynamiques
                $content = $this->replace_dynamic_variables($content, $order_id);

                switch ($element['type']) {
                    case 'text':
                    case 'invoice_number':
                    case 'invoice_date':
                    case 'customer_name':
                    case 'customer_address':
                    case 'subtotal':
                    case 'tax':
                    case 'total':
                    case 'company_info':
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content));
                        break;

                    case 'rectangle':
                        $html .= sprintf('<div class="pdf-element" style="%s"></div>', $style);
                        break;

                    case 'image':
                    case 'company_logo':
                        if ($content) {
                            $html .= sprintf('<img class="pdf-element" src="%s" style="%s" alt="Image" />', esc_url($content), $style);
                        }
                        break;

                    case 'product_table':
                        $html .= sprintf('<div class="pdf-element" style="%s">Product Table Placeholder</div>', $style);
                        break;

                    case 'layout-header':
                        $html .= sprintf('<div class="pdf-element layout-header" style="%s"><strong>EN-TÊTE</strong></div>', $style);
                        break;

                    case 'layout-footer':
                        $html .= sprintf('<div class="pdf-element layout-footer" style="%s"><em>PIED DE PAGE</em></div>', $style);
                        break;

                    case 'layout-sidebar':
                        $html .= sprintf('<div class="pdf-element layout-sidebar" style="%s"><strong>BARRE LATÉRALE</strong></div>', $style);
                        break;

                    case 'layout-section':
                        $html .= sprintf('<div class="pdf-element layout-section" style="%s"><strong>SECTION</strong></div>', $style);
                        break;

                    case 'layout-container':
                        $html .= sprintf('<div class="pdf-element layout-container" style="%s"><em>CONTENEUR</em></div>', $style);
                        break;

                    default:
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: $element['type']));
                        break;
                }
            }
        }

        $html .= '</body></html>';
        return $html;
    }

    /**
     * Remplace les variables dynamiques dans le contenu
     */
    private function replace_dynamic_variables($content, $order_id = null) {
        if (empty($content)) {
            return $content;
        }

        // Protection contre les boucles infinies - limiter à 10 remplacements maximum
        $max_replacements = 10;
        $replacement_count = 0;

        // Variables générales (date, etc.)
        $replacements = [
            '{{date}}' => date('d/m/Y'),
            '{{date|format:Y-m-d}}' => date('Y-m-d'),
            '{{date|format:d/m/Y}}' => date('d/m/Y'),
            '{{time}}' => date('H:i:s'),
            '{{datetime}}' => date('d/m/Y H:i:s'),
        ];

        // Variables WooCommerce si order_id est fourni
        if ($order_id && function_exists('wc_get_order')) {
            $order = wc_get_order($order_id);
            if ($order) {
                $replacements = array_merge($replacements, [
                    // Informations de commande
                    '{{order_number}}' => $order->get_order_number(),
                    '{{order_date}}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : '',
                    '{{order_status}}' => wc_get_order_status_name($order->get_status()),
                    '{{order_total}}' => $order->get_formatted_order_total(),
                    '{{order_subtotal}}' => wc_price($order->get_subtotal()),
                    '{{order_tax}}' => wc_price($order->get_total_tax()),
                    '{{order_shipping}}' => wc_price($order->get_shipping_total()),
                    '{{order_discount}}' => wc_price($order->get_discount_total()),
                    '{{order_refund}}' => wc_price($order->get_total_refunded()),

                    // Informations client
                    '{{customer_name}}' => $order->get_formatted_billing_full_name(),
                    '{{customer_email}}' => $order->get_billing_email(),
                    '{{customer_phone}}' => $order->get_billing_phone(),

                    // Adresses
                    '{{billing_address}}' => $order->get_formatted_billing_address(),
                    '{{shipping_address}}' => $order->get_formatted_shipping_address(),

                    // Méthode de paiement
                    '{{payment_method}}' => $order->get_payment_method_title(),

                    // Numéros de facture/devis (si extension disponible)
                    '{{invoice_number}}' => $this->get_invoice_number($order),
                    '{{quote_number}}' => $this->get_quote_number($order),
                ]);
            }
        }

        // Variables dynamiques avec expressions (formules) - approche sécurisée
        while ($replacement_count < $max_replacements && preg_match('/\{\{([^}]+)\}\}/', $content, $matches)) {
            $expression = $matches[1];
            $replacement_count++;

            // Éviter les remplacements récursifs - si l'expression contient déjà des accolades, ignorer
            if (strpos($expression, '{{') !== false || strpos($expression, '}}') !== false) {
                // Remplacer par une chaîne vide pour éviter la boucle
                $content = str_replace($matches[0], '', $content);
                continue;
            }

            $result = $this->evaluate_dynamic_expression($expression, $replacements);
            $content = str_replace($matches[0], $result, $content);
        }

        return $content;
    }

    /**
     * Évalue une expression dynamique de manière sécurisée
     */
    private function evaluate_dynamic_expression($expression, $replacements) {
        // Si c'est une expression mathématique
        if (preg_match('/^(.+?)\s*\*\s*(.+)$/', $expression, $math_matches)) {
            $left = $this->evaluate_expression($math_matches[1], $replacements);
            $right = $this->evaluate_expression($math_matches[2], $replacements);
            if (is_numeric($left) && is_numeric($right)) {
                return $left * $right;
            }
        }

        // Si c'est une expression avec |
        if (strpos($expression, '|') !== false) {
            $parts = explode('|', $expression, 2);
            $value = trim($parts[0]);
            $filter = trim($parts[1]);

            // Filtres disponibles
            if ($filter === 'currency:EUR') {
                $numeric_value = $this->extract_numeric_value($value, $replacements);
                return $numeric_value ? '€' . number_format($numeric_value, 2, ',', ' ') : $value;
            }
            if ($filter === 'currency:USD') {
                $numeric_value = $this->extract_numeric_value($value, $replacements);
                return $numeric_value ? '$' . number_format($numeric_value, 2, '.', ',') : $value;
            }
            if (preg_match('/^format:([YmdHis\/\-:]+)$/', $filter, $format_matches)) {
                $format = $format_matches[1];
                if ($value === 'date') {
                    return date($format);
                }
            }
        }

        // Si c'est une variable simple
        if (isset($replacements['{{' . $expression . '}}'])) {
            return $replacements['{{' . $expression . '}}'];
        }

        // Si c'est une expression conditionnelle
        if (preg_match('/^(.+?)\s*\?\s*(.+?)\s*:\s*(.+)$/', $expression, $cond_matches)) {
            $condition = trim($cond_matches[1]);
            $true_val = trim($cond_matches[2]);
            $false_val = trim($cond_matches[3]);

            // Évaluer la condition (simple pour l'instant)
            $condition_result = $this->evaluate_condition($condition, $replacements);
            return $condition_result ? $true_val : $false_val;
        }

        // Retourner l'expression originale si non reconnue
        return '{{' . $expression . '}}';
    }

    /**
     * Nettoie le contenu pour éviter l'affichage de CSS ou de code
     */
    private function sanitize_content($content) {
        // Supprimer les variables CSS qui commencent par --
        if (strpos($content, '--') === 0) {
            return '';
        }

        // Supprimer les déclarations CSS var()
        if (strpos($content, 'var(') === 0) {
            return '';
        }

        // Supprimer les lignes qui ressemblent à du CSS (propriété: valeur;)
        if (preg_match('/^[a-z-]+:\s*[^;]+;/', $content)) {
            return '';
        }

        return $content;
    }

    /**
     * Évalue une expression simple
     */
    private function evaluate_expression($expr, $replacements) {
        // Remplacer les variables dans l'expression
        foreach ($replacements as $key => $value) {
            $expr = str_replace($key, $value, $expr);
        }

        // Essayer d'évaluer comme nombre
        if (is_numeric($expr)) {
            return floatval($expr);
        }

        // Extraire les valeurs numériques des prix
        if (preg_match('/[\d.,\s]+/', $expr, $matches)) {
            $cleaned = preg_replace('/[^\d.,]/', '', $matches[0]);
            $cleaned = str_replace([' ', ','], ['', '.'], $cleaned);
            return floatval($cleaned);
        }

        return 0;
    }

    /**
     * Évalue une condition simple
     */
    private function evaluate_condition($condition, $replacements) {
        // Remplacer les variables
        foreach ($replacements as $key => $value) {
            $condition = str_replace($key, '"' . $value . '"', $condition);
        }

        // Conditions simples
        if (strpos($condition, '===') !== false) {
            list($left, $right) = explode('===', $condition, 2);
            return trim($left, '"\'') === trim($right, '"\'');
        }

        return false;
    }

    /**
     * Extrait une valeur numérique d'une expression
     */
    private function extract_numeric_value($expr, $replacements) {
        // Si c'est une variable
        if (isset($replacements['{{' . $expr . '}}'])) {
            $value = $replacements['{{' . $expr . '}}'];
            return $this->evaluate_expression($value, []);
        }

        return $this->evaluate_expression($expr, $replacements);
    }

    /**
     * Obtient le numéro de facture (extension WooCommerce PDF Invoices)
     */
    private function get_invoice_number($order) {
        // Essayer différentes extensions de facturation
        if (function_exists('wcpdf_get_invoice_number')) {
            return wcpdf_get_invoice_number($order->get_id());
        }

        // Fallback: numéro de commande
        return $order->get_order_number();
    }

    /**
     * Obtient le numéro de devis
     */
    private function get_quote_number($order) {
        // Essayer différentes extensions de devis
        if (function_exists('get_quote_number')) {
            return get_quote_number($order->get_id());
        }

        // Fallback: numéro de commande avec préfixe
        return 'DEVIS-' . $order->get_order_number();
    }

    /**
     * Génère un PDF depuis les données du canvas (legacy)
     * Méthode simplifiée - à remplacer par une vraie bibliothèque PDF
     */
    private function generate_pdf_from_canvas_data($canvas_data) {
        // Pour l'instant, retourner true pour simuler
        // À remplacer par une vraie génération PDF avec TCPDF, FPDF, etc.
        return true;
    }

    /**
     * Ajoute la meta box PDF Builder dans les commandes WooCommerce
     */
    public function add_woocommerce_order_meta_box() {
        error_log('PDF Builder: add_woocommerce_order_meta_box called');

        // Vérifier que nous sommes sur la bonne page
        if (!function_exists('get_current_screen')) {
            error_log('PDF Builder: get_current_screen not available');
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
    public function render_woocommerce_order_meta_box($post_or_order) {
        error_log('PDF Builder: render_woocommerce_order_meta_box called');

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
            echo '<p>' . __('Commande invalide', 'pdf-builder-pro') . '</p>';
            return;
        }

        // Récupérer les templates par défaut uniquement
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $templates = $wpdb->get_results("SELECT id, name FROM $table_templates WHERE is_default = 1 ORDER BY name ASC", ARRAY_A);

        wp_nonce_field('pdf_builder_order_actions', 'pdf_builder_order_nonce');
        ?>
        <div id="pdf-builder-order-meta-box" style="margin: -6px -12px -12px -12px;">
            <div style="padding: 12px; background: #f8f9fa; border-bottom: 1px solid #e1e1e1;">
                <strong><?php _e('Actions PDF', 'pdf-builder-pro'); ?></strong>
            </div>

            <div style="padding: 12px;">
                <?php if (!empty($templates)): ?>
                    <div style="margin-bottom: 12px;">
                        <label for="pdf_template_select" style="display: block; margin-bottom: 5px; font-weight: 500;">
                            <?php _e('Template PDF:', 'pdf-builder-pro'); ?>
                        </label>
                        <select id="pdf_template_select" style="width: 100%; padding: 5px; border: 1px solid #ddd; border-radius: 3px;">
                            <?php foreach ($templates as $template): ?>
                                <option value="<?php echo esc_attr($template['id']); ?>">
                                    <?php echo esc_html($template['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <div style="margin-bottom: 12px;">
                    <label for="pdf_document_type" style="display: block; margin-bottom: 5px; font-weight: 500;">
                        <?php _e('Type de document:', 'pdf-builder-pro'); ?>
                    </label>
                    <select id="pdf_document_type" style="width: 100%; padding: 5px; border: 1px solid #ddd; border-radius: 3px;">
                        <option value="invoice"><?php _e('Facture', 'pdf-builder-pro'); ?></option>
                        <option value="quote"><?php _e('Devis', 'pdf-builder-pro'); ?></option>
                    </select>
                </div>

                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <button type="button"
                            id="pdf-builder-preview-btn"
                            class="button button-secondary"
                            style="width: 100%; justify-content: center;"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                        👁️ <?php _e('Aperçu PDF', 'pdf-builder-pro'); ?>
                    </button>

                    <button type="button"
                            id="pdf-builder-generate-btn"
                            class="button button-primary"
                            style="width: 100%; justify-content: center;"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                        📄 <?php _e('Générer PDF', 'pdf-builder-pro'); ?>
                    </button>

                    <button type="button"
                            id="pdf-builder-download-btn"
                            class="button button-secondary"
                            style="width: 100%; justify-content: center; display: none;"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                        ⬇️ <?php _e('Télécharger PDF', 'pdf-builder-pro'); ?>
                    </button>
                </div>

                <div id="pdf-builder-status" style="margin-top: 10px; font-size: 12px; color: #666;"></div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $previewBtn = $('#pdf-builder-preview-btn');
            var $generateBtn = $('#pdf-builder-generate-btn');
            var $downloadBtn = $('#pdf-builder-download-btn');
            var $status = $('#pdf-builder-status');
            var $templateSelect = $('#pdf_template_select');
            var $documentTypeSelect = $('#pdf_document_type');
            var nonce = $('#pdf_builder_order_nonce').val();

            // Aperçu PDF
            $previewBtn.on('click', function() {
                var orderId = $(this).data('order-id');
                var templateId = $templateSelect.val() || 0;
                var documentType = $documentTypeSelect.val() || 'invoice';

                $status.html('<?php echo esc_js(__('Génération de l\'aperçu...', 'pdf-builder-pro')); ?>');
                $previewBtn.prop('disabled', true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_preview_order_pdf',
                        order_id: orderId,
                        template_id: templateId,
                        document_type: documentType,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            // Ouvrir l'aperçu dans une nouvelle fenêtre
                            var previewWindow = window.open('', '_blank', 'width=800,height=600');
                            if (previewWindow) {
                                previewWindow.document.write(response.data.html);
                                previewWindow.document.close();
                                $status.html('<?php echo esc_js(__('Aperçu ouvert', 'pdf-builder-pro')); ?>');
                            } else {
                                $status.html('<span style="color: #d63638;"><?php echo esc_js(__('Activez les popups pour voir l\'aperçu', 'pdf-builder-pro')); ?></span>');
                            }
                        } else {
                            $status.html('<span style="color: #d63638;">' + (response.data || '<?php echo esc_js(__('Erreur lors de l\'aperçu', 'pdf-builder-pro')); ?>') + '</span>');
                        }
                    },
                    error: function() {
                        $status.html('<span style="color: #d63638;"><?php echo esc_js(__('Erreur AJAX', 'pdf-builder-pro')); ?></span>');
                    },
                    complete: function() {
                        $previewBtn.prop('disabled', false);
                    }
                });
            });

            // Générer PDF
            $generateBtn.on('click', function() {
                var orderId = $(this).data('order-id');
                var templateId = $templateSelect.val() || 0;
                var documentType = $documentTypeSelect.val() || 'invoice';

                $status.html('<?php echo esc_js(__('Génération du PDF...', 'pdf-builder-pro')); ?>');
                $generateBtn.prop('disabled', true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_generate_order_pdf',
                        order_id: orderId,
                        template_id: templateId,
                        document_type: documentType,
                        nonce: nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $status.html('<?php echo esc_js(__('PDF généré avec succès', 'pdf-builder-pro')); ?>');
                            $downloadBtn.show();
                            $downloadBtn.data('pdf-url', response.data.url);
                        } else {
                            $status.html('<span style="color: #d63638;">' + (response.data || '<?php echo esc_js(__('Erreur lors de la génération', 'pdf-builder-pro')); ?>') + '</span>');
                        }
                    },
                    error: function() {
                        $status.html('<span style="color: #d63638;"><?php echo esc_js(__('Erreur AJAX', 'pdf-builder-pro')); ?></span>');
                    },
                    complete: function() {
                        $generateBtn.prop('disabled', false);
                    }
                });
            });

            // Télécharger PDF
            $downloadBtn.on('click', function() {
                var pdfUrl = $(this).data('pdf-url');
                if (pdfUrl) {
                    window.open(pdfUrl, '_blank');
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

        $this->check_admin_permissions();

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        $document_type = isset($_POST['document_type']) ? sanitize_text_field($_POST['document_type']) : 'invoice';

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
            $template_data = null;
            if ($template_id > 0) {
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';

                // Vérifier que la table existe
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
                    wp_send_json_error('Table des templates non trouvée - veuillez réinstaller le plugin');
                }

                $template = $wpdb->get_row(
                    PDF_Builder_Debug_Helper::safe_wpdb_prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id),
                    ARRAY_A
                );
                if ($template) {
                    $template_data = json_decode($template['template_data'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Log détaillé pour le débogage
                        error_log('PDF Builder: JSON decode error for template ID ' . $template_id . ' in generate_order_pdf');
                        error_log('PDF Builder: Raw JSON data: ' . substr($template['template_data'], 0, 500) . '...');
                        error_log('PDF Builder: JSON error: ' . json_last_error_msg());

                        // Essayer de nettoyer le JSON si c'est un problème d'encodage
                        $clean_json = $this->clean_json_data($template['template_data']);
                        if ($clean_json !== $template['template_data']) {
                            $template_data = json_decode($clean_json, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                error_log('PDF Builder: JSON cleaned successfully for template ID ' . $template_id);
                            } else {
                                wp_send_json_error('Erreur lors du décodage du template JSON: ' . json_last_error_msg() . ' (Template ID: ' . $template_id . ')');
                            }
                        } else {
                            wp_send_json_error('Erreur lors du décodage du template JSON: ' . json_last_error_msg() . ' (Template ID: ' . $template_id . ')');
                        }
                    }
                } else {
                    wp_send_json_error('Template non trouvé');
                }
            }

            // Si pas de template, utiliser un template par défaut
            if (!$template_data) {
                if ($document_type === 'quote') {
                    $template_data = $this->get_default_quote_template();
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
        } catch (Error $e) {
            wp_send_json_error('Erreur fatale: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Aperçu PDF pour une commande WooCommerce
     */
    public function ajax_preview_order_pdf() {
        // Désactiver l'affichage des erreurs PHP pour éviter les réponses HTML
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        $this->check_admin_permissions();

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
            $template_data = null;
            if ($template_id > 0) {
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';

                // Vérifier que la table existe
                if ($wpdb->get_var("SHOW TABLES LIKE '$table_templates'") != $table_templates) {
                    wp_send_json_error('Table des templates non trouvée - veuillez réinstaller le plugin');
                }

                $template = $wpdb->get_row(
                    PDF_Builder_Debug_Helper::safe_wpdb_prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id),
                    ARRAY_A
                );
                if ($template) {
                    $template_data = json_decode($template['template_data'], true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // Log détaillé pour le débogage
                        error_log('PDF Builder: JSON decode error for template ID ' . $template_id . ' in preview_order_pdf');
                        error_log('PDF Builder: Raw JSON data: ' . substr($template['template_data'], 0, 500) . '...');
                        error_log('PDF Builder: JSON error: ' . json_last_error_msg());

                        // Essayer de nettoyer le JSON si c'est un problème d'encodage
                        $clean_json = $this->clean_json_data($template['template_data']);
                        if ($clean_json !== $template['template_data']) {
                            $template_data = json_decode($clean_json, true);
                            if (json_last_error() === JSON_ERROR_NONE) {
                                error_log('PDF Builder: JSON cleaned successfully for template ID ' . $template_id);
                            } else {
                                wp_send_json_error('Erreur lors du décodage du template JSON: ' . json_last_error_msg() . ' (Template ID: ' . $template_id . ')');
                            }
                        } else {
                            wp_send_json_error('Erreur lors du décodage du template JSON: ' . json_last_error_msg() . ' (Template ID: ' . $template_id . ')');
                        }
                    }
                } else {
                    wp_send_json_error('Template non trouvé');
                }
            }

            // Si pas de template, utiliser un template par défaut
            if (!$template_data) {
                if ($document_type === 'quote') {
                    $template_data = $this->get_default_quote_template();
                } else {
                    $template_data = $this->get_default_invoice_template();
                }
            }

            // Générer l'HTML d'aperçu avec les données de la commande
            $html_content = $this->generate_order_html($order, $template_data);

            // Debug: vérifier que le HTML est correct
            if (strpos($html_content, '<!DOCTYPE html>') === false) {
                error_log('PDF Builder: HTML content does not start with DOCTYPE: ' . substr($html_content, 0, 200));
            }

        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        } catch (Error $e) {
            wp_send_json_error('Erreur fatale: ' . $e->getMessage());
        }
    }

    /**
     * Génère un PDF pour une commande WooCommerce
     */
    private function generate_order_pdf($order, $template_data, $filename) {
        // Créer le répertoire de stockage s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder/orders';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_path = $pdf_dir . '/' . $filename;

        // Générer le HTML d'abord
        $html_content = $this->generate_order_html($order, $template_data);

        // Utiliser une bibliothèque PDF si disponible
        if (class_exists('TCPDF')) {
            // Utiliser TCPDF si disponible
            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            $pdf->SetCreator('PDF Builder Pro');
            $pdf->SetAuthor('PDF Builder Pro');
            $pdf->SetTitle('Order #' . $order->get_id());

            $pdf->AddPage();
            $pdf->writeHTML($html_content, true, false, true, false, '');
            $pdf->Output($pdf_path, 'F');

            return $pdf_path;
        } else {
            // Fallback: créer un fichier HTML pour simulation
            file_put_contents($pdf_path, $html_content);
            return $pdf_path;
        }
    }

    /**
     * Génère du HTML pour une commande WooCommerce
     */
    private function generate_order_html($order, $template_data) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Order #' . $order->get_id() . '</title>';
        $html .= '<style>body { font-family: Arial, sans-serif; margin: 0; padding: 20px; } .pdf-element { position: absolute; }</style>';
        $html .= '</head><body>';

        if (isset($template_data['elements']) && is_array($template_data['elements'])) {
            foreach ($template_data['elements'] as $element) {
                $style = sprintf(
                    'left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                    $element['position']['x'],
                    $element['position']['y'],
                    $element['size']['width'],
                    $element['size']['height']
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

                // Vérifier que le contenu n'est pas du CSS ou du code malformé
                if (strpos($content, '--') === 0 || strpos($content, 'var(') === 0) {
                    $content = 'Contenu invalide';
                }

                // Remplacer les variables dynamiques de la commande
                $content = $this->replace_dynamic_variables($content, $order->get_id());

                // Nettoyer le contenu pour éviter l'affichage de CSS
                $content = $this->sanitize_content($content);

                switch ($element['type']) {
                    case 'text':
                    case 'title':
                    case 'subtitle':
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, nl2br(esc_html($content)));
                        break;

                    case 'invoice_number':
                        if (empty($content)) $content = '{{order_number}}';
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content));
                        break;

                    case 'invoice_date':
                        if (empty($content)) $content = '{{order_date}}';
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content));
                        break;

                    case 'customer_name':
                        if (empty($content)) $content = '{{customer_name}}';
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content));
                        break;

                    case 'customer_address':
                        if (empty($content)) $content = '{{billing_address}}';
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, nl2br(esc_html($content)));
                        break;

                    case 'subtotal':
                        if (empty($content)) $content = '{{order_subtotal}}';
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content));
                        break;

                    case 'tax':
                        if (empty($content)) $content = '{{order_tax}}';
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content));
                        break;

                    case 'total':
                        if (empty($content)) $content = '{{order_total}}';
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content));
                        break;

                    case 'rectangle':
                        $html .= sprintf('<div class="pdf-element" style="%s"></div>', $style);
                        break;

                    case 'image':
                    case 'company_logo':
                        if ($content) {
                            $html .= sprintf('<img class="pdf-element" src="%s" style="%s" alt="Image" />', esc_url($content), $style);
                        }
                        break;

                    case 'product_table':
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, $this->generate_order_products_table($order));
                        break;

                    default:
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: $element['type']));
                        break;
                }
            }
        }

        $html .= '</body></html>';
        return $html;
    }

    /**
     * Remplace les variables dans le contenu
     */
    private function replace_order_variables($content, $order) {
        // Préparer les données de la commande
        $billing_address = $order->get_formatted_billing_address();
        $shipping_address = $order->get_formatted_shipping_address();

        // Variables avec doubles accolades {{variable}}
        $double_brace_replacements = array(
            '{{order_id}}' => $order->get_id(),
            '{{order_number}}' => $order->get_order_number(),
            '{{order_date}}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : date('d/m/Y'),
            '{{order_date_time}}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y H:i:s') : date('d/m/Y H:i:s'),
            '{{customer_name}}' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            '{{customer_first_name}}' => $order->get_billing_first_name(),
            '{{customer_last_name}}' => $order->get_billing_last_name(),
            '{{customer_email}}' => $order->get_billing_email(),
            '{{customer_phone}}' => $order->get_billing_phone(),
            '{{billing_company}}' => $order->get_billing_company(),
            '{{billing_address_1}}' => $order->get_billing_address_1(),
            '{{billing_address_2}}' => $order->get_billing_address_2(),
            '{{billing_city}}' => $order->get_billing_city(),
            '{{billing_state}}' => $order->get_billing_state(),
            '{{billing_postcode}}' => $order->get_billing_postcode(),
            '{{billing_country}}' => $order->get_billing_country(),
            '{{billing_address}}' => $billing_address ?: 'Adresse de facturation non disponible',
            '{{complete_customer_info}}' => $this->format_complete_customer_info($order),
            '{{complete_billing_address}}' => $billing_address ?: 'Adresse de facturation non disponible',
            '{{shipping_first_name}}' => $order->get_shipping_first_name(),
            '{{shipping_last_name}}' => $order->get_shipping_last_name(),
            '{{shipping_company}}' => $order->get_shipping_company(),
            '{{shipping_address_1}}' => $order->get_shipping_address_1(),
            '{{shipping_address_2}}' => $order->get_shipping_address_2(),
            '{{shipping_city}}' => $order->get_shipping_city(),
            '{{shipping_state}}' => $order->get_shipping_state(),
            '{{shipping_postcode}}' => $order->get_shipping_postcode(),
            '{{shipping_country}}' => $order->get_shipping_country(),
            '{{shipping_address}}' => $shipping_address ?: 'Adresse de livraison non disponible',
            '{{total}}' => wc_price($order->get_total()),
            '{{subtotal}}' => wc_price($order->get_subtotal()),
            '{{tax}}' => wc_price($order->get_total_tax()),
            '{{shipping_total}}' => wc_price($order->get_shipping_total()),
            '{{discount_total}}' => wc_price($order->get_discount_total()),
            '{{payment_method}}' => $order->get_payment_method_title(),
            '{{order_status}}' => wc_get_order_status_name($order->get_status()),
            '{{currency}}' => $order->get_currency(),
        );

        // Variables avec crochets [variable]
        $bracket_replacements = array(
            '[order_id]' => $order->get_id(),
            '[order_number]' => $order->get_order_number(),
            '[order_date]' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : date('d/m/Y'),
            '[order_date_time]' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y H:i:s') : date('d/m/Y H:i:s'),
            '[customer_name]' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            '[billing_first_name]' => $order->get_billing_first_name(),
            '[billing_last_name]' => $order->get_billing_last_name(),
            '[billing_company]' => $order->get_billing_company(),
            '[billing_address_1]' => $order->get_billing_address_1(),
            '[billing_address_2]' => $order->get_billing_address_2(),
            '[billing_city]' => $order->get_billing_city(),
            '[billing_state]' => $order->get_billing_state(),
            '[billing_postcode]' => $order->get_billing_postcode(),
            '[billing_country]' => $order->get_billing_country(),
            '[billing_address]' => $billing_address ?: 'Adresse de facturation non disponible',
            '[complete_customer_info]' => $this->format_complete_customer_info($order),
            '[complete_billing_address]' => $billing_address ?: 'Adresse de facturation non disponible',
            '[shipping_first_name]' => $order->get_shipping_first_name(),
            '[shipping_last_name]' => $order->get_shipping_last_name(),
            '[shipping_company]' => $order->get_shipping_company(),
            '[shipping_address_1]' => $order->get_shipping_address_1(),
            '[shipping_address_2]' => $order->get_shipping_address_2(),
            '[shipping_city]' => $order->get_shipping_city(),
            '[shipping_state]' => $order->get_shipping_state(),
            '[shipping_postcode]' => $order->get_shipping_postcode(),
            '[shipping_country]' => $order->get_shipping_country(),
            '[shipping_address]' => $shipping_address ?: 'Adresse de livraison non disponible',
            '[customer_email]' => $order->get_billing_email(),
            '[customer_phone]' => $order->get_billing_phone(),
            '[total]' => wc_price($order->get_total()),
            '[subtotal]' => wc_price($order->get_subtotal()),
            '[tax]' => wc_price($order->get_total_tax()),
            '[shipping_total]' => wc_price($order->get_shipping_total()),
            '[discount_total]' => wc_price($order->get_discount_total()),
            '[payment_method]' => $order->get_payment_method_title(),
            '[order_status]' => wc_get_order_status_name($order->get_status()),
            '[currency]' => $order->get_currency(),
        );

        // Variables avec accolades simples {variable}
        $single_brace_replacements = array(
            '{order_id}' => $order->get_id(),
            '{order_number}' => $order->get_order_number(),
            '{order_date}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : date('d/m/Y'),
            '{order_date_time}' => $order->get_date_created() ? $order->get_date_created()->date('d/m/Y H:i:s') : date('d/m/Y H:i:s'),
            '{customer_name}' => trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
            '{billing_first_name}' => $order->get_billing_first_name(),
            '{billing_last_name}' => $order->get_billing_last_name(),
            '{billing_company}' => $order->get_billing_company(),
            '{billing_address_1}' => $order->get_billing_address_1(),
            '{billing_address_2}' => $order->get_billing_address_2(),
            '{billing_city}' => $order->get_billing_city(),
            '{billing_state}' => $order->get_billing_state(),
            '{billing_postcode}' => $order->get_billing_postcode(),
            '{billing_country}' => $order->get_billing_country(),
            '{billing_address}' => $billing_address ?: 'Adresse de facturation non disponible',
            '{complete_customer_info}' => $this->format_complete_customer_info($order),
            '{complete_billing_address}' => $billing_address ?: 'Adresse de facturation non disponible',
            '{shipping_first_name}' => $order->get_shipping_first_name(),
            '{shipping_last_name}' => $order->get_shipping_last_name(),
            '{shipping_company}' => $order->get_shipping_company(),
            '{shipping_address_1}' => $order->get_shipping_address_1(),
            '{shipping_address_2}' => $order->get_shipping_address_2(),
            '{shipping_city}' => $order->get_shipping_city(),
            '{shipping_state}' => $order->get_shipping_state(),
            '{shipping_postcode}' => $order->get_shipping_postcode(),
            '{shipping_country}' => $order->get_shipping_country(),
            '{shipping_address}' => $shipping_address ?: 'Adresse de livraison non disponible',
            '{customer_email}' => $order->get_billing_email(),
            '{customer_phone}' => $order->get_billing_phone(),
            '{total}' => wc_price($order->get_total()),
            '{subtotal}' => wc_price($order->get_subtotal()),
            '{tax}' => wc_price($order->get_total_tax()),
            '{shipping_total}' => wc_price($order->get_shipping_total()),
            '{discount_total}' => wc_price($order->get_discount_total()),
            '{payment_method}' => $order->get_payment_method_title(),
            '{order_status}' => wc_get_order_status_name($order->get_status()),
            '{currency}' => $order->get_currency(),
            '{order_items_table}' => $this->generate_order_products_table($order),
        );

        // Appliquer les remplacements dans l'ordre : simples, doubles, crochets
        $content = str_replace(array_keys($single_brace_replacements), array_values($single_brace_replacements), $content);
        $content = str_replace(array_keys($double_brace_replacements), array_values($double_brace_replacements), $content);
        $content = str_replace(array_keys($bracket_replacements), array_values($bracket_replacements), $content);

        return $content;
    }

    /**
     * Formate les informations complètes du client
     */
    private function format_complete_customer_info($order) {
        $info = [];

        // Nom complet
        $full_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
        if (!empty($full_name)) {
            $info[] = $full_name;
        }

        // Société
        $company = $order->get_billing_company();
        if (!empty($company)) {
            $info[] = $company;
        }

        // Adresse complète
        $billing_address = $order->get_formatted_billing_address();
        if (!empty($billing_address)) {
            $info[] = $billing_address;
        }

        // Email
        $email = $order->get_billing_email();
        if (!empty($email)) {
            $info[] = 'Email: ' . $email;
        }

        // Téléphone
        $phone = $order->get_billing_phone();
        if (!empty($phone)) {
            $info[] = 'Téléphone: ' . $phone;
        }

        return implode("\n", $info);
    }

    /**
     * Génère le tableau des produits de la commande
     */
    private function generate_order_products_table($order) {
        $html = '<table style="width: 100%; border-collapse: collapse;">';
        $html .= '<thead><tr>';
        $html .= '<th style="border: 1px solid #ddd; padding: 5px;">Produit</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 5px;">Qté</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 5px;">Prix</th>';
        $html .= '<th style="border: 1px solid #ddd; padding: 5px;">Total</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();
            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 5px;">' . esc_html($item->get_name()) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 5px;">' . $item->get_quantity() . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 5px;">' . wc_price($item->get_total() / $item->get_quantity()) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 5px;">' . wc_price($item->get_total()) . '</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        return $html;
    }

    /**
     * Retourne un template de facture par défaut
     */
    private function get_default_invoice_template() {
        return array(
            'canvas' => array(
                'width' => 595,
                'height' => 842,
                'zoom' => 1,
                'pan' => array('x' => 0, 'y' => 0)
            ),
            'elements' => array(
                array(
                    'id' => 'company_name',
                    'type' => 'text',
                    'position' => array('x' => 50, 'y' => 50),
                    'size' => array('width' => 200, 'height' => 30),
                    'style' => array('fontSize' => 18, 'fontWeight' => 'bold', 'color' => '#000000'),
                    'content' => 'Ma Société'
                ),
                array(
                    'id' => 'invoice_title',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 50),
                    'size' => array('width' => 150, 'height' => 30),
                    'style' => array('fontSize' => 20, 'fontWeight' => 'bold', 'color' => '#000000'),
                    'content' => 'FACTURE'
                ),
                array(
                    'id' => 'invoice_number',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 90),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'N° {{order_number}}'
                ),
                array(
                    'id' => 'invoice_date',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 120),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'Date: {{order_date}}'
                ),
                array(
                    'id' => 'due_date',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 150),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 12, 'color' => '#666666'),
                    'content' => 'Échéance: {{order_date|+30 days|format:d/m/Y}}'
                ),
                array(
                    'id' => 'customer_info',
                    'type' => 'customer_name',
                    'position' => array('x' => 50, 'y' => 150),
                    'size' => array('width' => 250, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'Client'
                ),
                array(
                    'id' => 'customer_address',
                    'type' => 'customer_address',
                    'position' => array('x' => 50, 'y' => 180),
                    'size' => array('width' => 250, 'height' => 60),
                    'style' => array('fontSize' => 12, 'color' => '#000000'),
                    'content' => 'Adresse'
                ),
                array(
                    'id' => 'products_table',
                    'type' => 'product_table',
                    'position' => array('x' => 50, 'y' => 260),
                    'size' => array('width' => 500, 'height' => 200),
                    'style' => array('fontSize' => 12, 'color' => '#000000'),
                    'content' => 'Tableau produits'
                ),
                array(
                    'id' => 'subtotal',
                    'type' => 'subtotal',
                    'position' => array('x' => 400, 'y' => 490),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'Sous-total'
                ),
                array(
                    'id' => 'tax',
                    'type' => 'tax',
                    'position' => array('x' => 400, 'y' => 520),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'TVA'
                ),
                array(
                    'id' => 'total',
                    'type' => 'total',
                    'position' => array('x' => 400, 'y' => 550),
                    'size' => array('width' => 150, 'height' => 30),
                    'style' => array('fontSize' => 16, 'fontWeight' => 'bold', 'color' => '#000000'),
                    'content' => 'Total TTC'
                ),
                array(
                    'id' => 'payment_info',
                    'type' => 'text',
                    'position' => array('x' => 50, 'y' => 480),
                    'size' => array('width' => 300, 'height' => 40),
                    'style' => array('fontSize' => 10, 'color' => '#666666'),
                    'content' => 'Mode de paiement: {{payment_method}} - Échéance: {{order_date|+30 days|format:d/m/Y}}'
                ),
                array(
                    'id' => 'invoice_footer',
                    'type' => 'text',
                    'position' => array('x' => 50, 'y' => 750),
                    'size' => array('width' => 500, 'height' => 40),
                    'style' => array('fontSize' => 10, 'color' => '#666666'),
                    'content' => 'TVA non applicable, art. 293 B du CGI - Merci pour votre confiance'
                )
            )
        );
    }

    /**
     * Template par défaut pour les devis
     */
    private function get_default_quote_template() {
        return array(
            'canvas' => array(
                'width' => 595,
                'height' => 842,
                'zoom' => 1,
                'pan' => array('x' => 0, 'y' => 0)
            ),
            'elements' => array(
                array(
                    'id' => 'company_name',
                    'type' => 'text',
                    'position' => array('x' => 50, 'y' => 50),
                    'size' => array('width' => 200, 'height' => 30),
                    'style' => array('fontSize' => 18, 'fontWeight' => 'bold', 'color' => '#000000'),
                    'content' => 'Ma Société'
                ),
                array(
                    'id' => 'quote_title',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 50),
                    'size' => array('width' => 150, 'height' => 30),
                    'style' => array('fontSize' => 20, 'fontWeight' => 'bold', 'color' => '#000000'),
                    'content' => 'DEVIS'
                ),
                array(
                    'id' => 'quote_number',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 90),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'DEVIS-{{order_number}}'
                ),
                array(
                    'id' => 'quote_date',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 120),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'Date: {{order_date}}'
                ),
                array(
                    'id' => 'validity_date',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 150),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 12, 'color' => '#666666'),
                    'content' => 'Valable jusqu\'au: {{order_date|+30 days|format:d/m/Y}}'
                ),
                array(
                    'id' => 'customer_info',
                    'type' => 'customer_name',
                    'position' => array('x' => 50, 'y' => 150),
                    'size' => array('width' => 250, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'Client'
                ),
                array(
                    'id' => 'customer_address',
                    'type' => 'customer_address',
                    'position' => array('x' => 50, 'y' => 180),
                    'size' => array('width' => 250, 'height' => 60),
                    'style' => array('fontSize' => 12, 'color' => '#000000'),
                    'content' => 'Adresse'
                ),
                array(
                    'id' => 'quote_intro',
                    'type' => 'text',
                    'position' => array('x' => 50, 'y' => 260),
                    'size' => array('width' => 500, 'height' => 40),
                    'style' => array('fontSize' => 12, 'color' => '#000000'),
                    'content' => 'Nous avons le plaisir de vous soumettre notre devis pour les prestations suivantes :'
                ),
                array(
                    'id' => 'products_table',
                    'type' => 'product_table',
                    'position' => array('x' => 50, 'y' => 320),
                    'size' => array('width' => 500, 'height' => 200),
                    'style' => array('fontSize' => 12, 'color' => '#000000'),
                    'content' => 'Tableau produits'
                ),
                array(
                    'id' => 'subtotal',
                    'type' => 'subtotal',
                    'position' => array('x' => 400, 'y' => 550),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'Sous-total'
                ),
                array(
                    'id' => 'tax',
                    'type' => 'tax',
                    'position' => array('x' => 400, 'y' => 580),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'TVA'
                ),
                array(
                    'id' => 'total',
                    'type' => 'total',
                    'position' => array('x' => 400, 'y' => 610),
                    'size' => array('width' => 150, 'height' => 30),
                    'style' => array('fontSize' => 16, 'fontWeight' => 'bold', 'color' => '#000000'),
                    'content' => 'Total TTC'
                ),
                array(
                    'id' => 'quote_conditions',
                    'type' => 'text',
                    'position' => array('x' => 50, 'y' => 660),
                    'size' => array('width' => 500, 'height' => 60),
                    'style' => array('fontSize' => 10, 'color' => '#666666'),
                    'content' => 'Conditions: Ce devis est valable 30 jours. Paiement à 30 jours. Toute commande implique l\'acceptation de nos conditions générales de vente.'
                ),
                array(
                    'id' => 'signature',
                    'type' => 'text',
                    'position' => array('x' => 400, 'y' => 750),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 12, 'color' => '#000000'),
                    'content' => 'Signature'
                )
            )
        );
    }

    /**
     * AJAX - Vérifier l'état de la base de données
     */
    public function ajax_check_database() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            global $wpdb;
            $table_prefix = $wpdb->prefix . 'pdf_builder_';

            $tables_to_check = [
                'templates',
                'documents',
                'categories',
                'logs',
                'cache',
                'template_versions'
            ];

            $results = [];
            foreach ($tables_to_check as $table) {
                $table_name = $table_prefix . $table;

                // Vérifier si la table existe
                $exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));

                // Compter les enregistrements si la table existe
                $count = 0;
                if ($exists) {
                    $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
                }

                $results[$table] = [
                    'exists' => !empty($exists),
                    'count' => (int) $count
                ];
            }

            wp_send_json_success([
                'message' => __('Base de données vérifiée avec succès.', 'pdf-builder-pro'),
                'tables' => $results
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Réparer la base de données
     */
    public function ajax_repair_database() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // Inclure la fonction de création des tables
            if (function_exists('pdf_builder_create_database_tables')) {
                pdf_builder_create_database_tables();
                wp_send_json_success([
                    'message' => __('Tables de base de données créées/réparées avec succès.', 'pdf-builder-pro')
                ]);
            } else {
                wp_send_json_error(['message' => __('Fonction de création des tables non trouvée.', 'pdf-builder-pro')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Exécuter la réparation SQL depuis le fichier
     */
    public function ajax_execute_sql_repair() {
        // Démarrer un buffer de sortie propre pour éviter tout caractère parasite
        ob_start();

        // Headers pour s'assurer d'une réponse JSON propre
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');

        try {
            error_log('PDF Builder Pro: ajax_execute_sql_repair appelée');

            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
                ob_end_clean();
                echo json_encode(['success' => false, 'data' => ['message' => __('Nonce invalide.', 'pdf-builder-pro')]]);
                exit;
            }

            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                ob_end_clean();
                echo json_encode(['success' => false, 'data' => ['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]]);
                exit;
            }

            global $wpdb;

            // Chemin vers le fichier SQL
            $sql_file = plugin_dir_path(__FILE__) . '../repair-database.sql';

            if (!file_exists($sql_file)) {
                ob_end_clean();
                echo json_encode(['success' => false, 'data' => ['message' => __('Fichier repair-database.sql introuvable.', 'pdf-builder-pro')]]);
                exit;
            }

            // Lire le contenu du fichier SQL
            $sql_content = file_get_contents($sql_file);
            if ($sql_content === false) {
                ob_end_clean();
                echo json_encode(['success' => false, 'data' => ['message' => __('Impossible de lire le fichier SQL.', 'pdf-builder-pro')]]);
                exit;
            }

            // Parser les instructions SQL de manière robuste
            $statements = $this->parse_sql_statements($sql_content);

            $results = [];
            $success_count = 0;
            $error_count = 0;

            foreach ($statements as $statement) {
                if (empty(trim($statement))) {
                    continue; // Ignorer les instructions vides
                }

                // Remplacer le préfixe wp_ par le préfixe WordPress réel
                $statement = str_replace('`wp_pdf_builder_', '`' . $wpdb->prefix . 'pdf_builder_', $statement);

                // Extraire le nom de la table de l'instruction CREATE TABLE
                $table_name = '';
                if (preg_match('/CREATE TABLE(?: IF NOT EXISTS)? `?(\w+)`?/i', $statement, $matches)) {
                    $table_name = $matches[1];
                } elseif (preg_match('/INSERT INTO `?(\w+)`?/i', $statement, $matches)) {
                    $table_name = $matches[1] . ' (insertion)';
                }

                try {
                    // Exécuter l'instruction SQL
                    $result = $wpdb->query($statement);

                    if ($result !== false) {
                        $results[] = [
                            'table' => $table_name ?: 'Instruction SQL',
                            'success' => true,
                            'message' => 'Exécutée avec succès'
                        ];
                        $success_count++;
                    } else {
                        $results[] = [
                            'table' => $table_name ?: 'Instruction SQL',
                            'success' => false,
                            'message' => 'Erreur: ' . $wpdb->last_error
                        ];
                        $error_count++;
                    }
                } catch (Exception $e) {
                    $results[] = [
                        'table' => $table_name ?: 'Instruction SQL',
                        'success' => false,
                        'message' => 'Exception: ' . $e->getMessage()
                    ];
                    $error_count++;
                }
            }

            if ($error_count === 0) {
                // Nettoyer le buffer et envoyer la réponse JSON propre
                ob_end_clean();
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'message' => sprintf(__('Script SQL exécuté avec succès ! %d instructions réussies.', 'pdf-builder-pro'), $success_count),
                        'results' => $results
                    ]
                ]);
                exit;
            } else {
                // Nettoyer le buffer et envoyer la réponse JSON propre
                ob_end_clean();
                echo json_encode([
                    'success' => false,
                    'data' => [
                        'message' => sprintf(__('Script SQL partiellement exécuté. %d réussites, %d erreurs.', 'pdf-builder-pro'), $success_count, $error_count),
                        'results' => $results
                    ]
                ]);
                exit;
            }

        } catch (Exception $e) {
            // Nettoyer le buffer et envoyer la réponse JSON propre
            ob_end_clean();
            echo json_encode(['success' => false, 'data' => ['message' => $e->getMessage()]]);
            exit;
        }
    }

    /**
     * AJAX - Vider le cache
     */
    public function ajax_clear_cache() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // S'assurer que la classe Cache Manager est chargée
            if (!class_exists('PDF_Builder_Cache_Manager')) {
                $cache_manager_path = dirname(__FILE__) . '/managers/PDF_Builder_Cache_Manager.php';
                if (file_exists($cache_manager_path)) {
                    require_once $cache_manager_path;
                }
            }

            $cache = PDF_Builder_Cache_Manager::getInstance();
            $cache->flush();

            wp_send_json_success([
                'message' => __('Cache vidé avec succès.', 'pdf-builder-pro')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Obtenir la liste des templates
     */
    public function ajax_get_templates() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            error_log('PDF Builder Pro: Nonce invalide dans ajax_get_templates');
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            error_log('PDF Builder Pro: Permissions insuffisantes dans ajax_get_templates');
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            error_log('PDF Builder Pro: Début de ajax_get_templates');

            // Récupérer les paramètres de pagination
            $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
            $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 12;
            $view = isset($_POST['view']) ? sanitize_text_field($_POST['view']) : 'grid';

            // S'assurer que les valeurs sont dans des limites raisonnables
            $page = max(1, $page);
            $per_page = min(50, max(1, $per_page)); // Maximum 50 par page

            // Utiliser l'instance globale si elle existe, sinon créer une nouvelle
            global $pdf_builder_core;
            if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
                $core = $pdf_builder_core;
                error_log('PDF Builder Pro: Utilisation de l\'instance globale du core');
            } else {
                $core = PDF_Builder_Core::getInstance();
                if (!$core->is_initialized()) {
                    $core->init();
                    error_log('PDF Builder Pro: Core initialisé');
                } else {
                    error_log('PDF Builder Pro: Core déjà initialisé');
                }
            }

            $template_manager = $core->get_template_manager();
            error_log('PDF Builder Pro: Template manager obtenu');

            // Vérifier si la table existe
            global $wpdb;
            $table_name = $wpdb->prefix . 'pdf_builder_templates';
            if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
                error_log('PDF Builder Pro: Table des templates n\'existe pas: ' . $table_name);
                wp_send_json_error(['message' => __('Table des templates introuvable.', 'pdf-builder-pro')]);
                return;
            }
            error_log('PDF Builder Pro: Table des templates vérifiée: ' . $table_name);

            // Récupérer les templates avec pagination
            $result = $template_manager->get_templates_paginated([], $page, $per_page);
            error_log('PDF Builder Pro: Templates récupérés: ' . count($result['templates']) . ' templates sur ' . $result['total'] . ' total');
            error_log('PDF Builder Pro: Résultat complet: ' . print_r($result, true));

            wp_send_json_success($result);

        } catch (Exception $e) {
            error_log('PDF Builder Pro: Exception dans ajax_get_templates: ' . $e->getMessage());
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Supprimer un template
     */
    public function ajax_delete_template() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        $template_id = intval($_POST['template_id'] ?? 0);
        if (!$template_id) {
            wp_send_json_error(['message' => __('ID de template invalide.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // Utiliser l'instance globale si elle existe, sinon créer une nouvelle
            global $pdf_builder_core;
            if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
                $core = $pdf_builder_core;
            } else {
                $core = PDF_Builder_Core::getInstance();
                if (!$core->is_initialized()) {
                    $core->init();
                }
            }

            $template_manager = $core->get_template_manager();
            $result = $template_manager->delete_template($template_id);

            if ($result) {
                wp_send_json_success(['message' => __('Template supprimé avec succès.', 'pdf-builder-pro')]);
            } else {
                wp_send_json_error(['message' => __('Erreur lors de la suppression du template.', 'pdf-builder-pro')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Dupliquer un template
     */
    public function ajax_duplicate_template() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        $template_id = intval($_POST['template_id'] ?? 0);
        if (!$template_id) {
            wp_send_json_error(['message' => __('ID de template invalide.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // Utiliser l'instance globale si elle existe, sinon créer une nouvelle
            global $pdf_builder_core;
            if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
                $core = $pdf_builder_core;
            } else {
                $core = PDF_Builder_Core::getInstance();
                if (!$core->is_initialized()) {
                    $core->init();
                }
            }

            $template_manager = $core->get_template_manager();
            $result = $template_manager->duplicate_template($template_id);

            if ($result) {
                wp_send_json_success(['message' => __('Template dupliqué avec succès.', 'pdf-builder-pro')]);
            } else {
                wp_send_json_error(['message' => __('Erreur lors de la duplication du template.', 'pdf-builder-pro')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Définir un template comme défaut
     */
    public function ajax_set_default_template() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        $template_id = intval($_POST['template_id'] ?? 0);
        $is_default = intval($_POST['is_default'] ?? 0);

        if (!$template_id) {
            wp_send_json_error(['message' => __('ID de template invalide.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // Utiliser l'instance globale si elle existe, sinon créer une nouvelle
            global $pdf_builder_core;
            if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
                $core = $pdf_builder_core;
            } else {
                $core = PDF_Builder_Core::getInstance();
                if (!$core->is_initialized()) {
                    $core->init();
                }
            }

            $template_manager = $core->get_template_manager();
            $result = $template_manager->set_default_template($template_id, $is_default);

            if ($result) {
                $message = $is_default ? __('Template défini comme défaut.', 'pdf-builder-pro') : __('Statut par défaut retiré.', 'pdf-builder-pro');
                wp_send_json_success(['message' => $message]);
            } else {
                wp_send_json_error(['message' => __('Erreur lors de la modification du statut par défaut.', 'pdf-builder-pro')]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Optimiser la base de données
     */
    public function ajax_optimize_database() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // S'assurer que la classe Database Manager est chargée
            if (!class_exists('PDF_Builder_Database_Manager')) {
                $db_manager_path = dirname(__FILE__) . '/managers/PDF_Builder_Database_Manager.php';
                if (file_exists($db_manager_path)) {
                    require_once $db_manager_path;
                }
            }

            $database = PDF_Builder_Database_Manager::getInstance();
            $database->optimize_tables();

            wp_send_json_success([
                'message' => __('Base de données optimisée avec succès.', 'pdf-builder-pro')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Voir les logs
     */
    public function ajax_view_logs() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // S'assurer que la classe Database Manager est chargée
            if (!class_exists('PDF_Builder_Database_Manager')) {
                $db_manager_path = dirname(__FILE__) . '/managers/PDF_Builder_Database_Manager.php';
                if (file_exists($db_manager_path)) {
                    require_once $db_manager_path;
                }
            }

            $database = PDF_Builder_Database_Manager::getInstance();
            $logs_table = $database->get_table_name('logs');

            // Récupérer les 50 derniers logs
            $logs = $database->wpdb->get_results(
                $database->wpdb->prepare(
                    "SELECT level, message, context, created_at, user_id 
                     FROM {$logs_table} 
                     ORDER BY created_at DESC 
                     LIMIT %d",
                    50
                )
            );

            $logs_text = '';
            if (!empty($logs)) {
                foreach ($logs as $log) {
                    $level = strtoupper($log->level);
                    $timestamp = date('Y-m-d H:i:s', strtotime($log->created_at));
                    $user_info = $log->user_id ? " (User: {$log->user_id})" : '';
                    
                    $logs_text .= "[{$timestamp}] {$level}{$user_info}: {$log->message}\n";
                    
                    // Ajouter le contexte si disponible
                    if (!empty($log->context)) {
                        $context = json_decode($log->context, true);
                        if ($context) {
                            $logs_text .= "  Context: " . json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                        }
                    }
                    $logs_text .= "\n";
                }
            } else {
                $logs_text = __('Aucun log trouvé.', 'pdf-builder-pro');
            }

            wp_send_json_success([
                'logs' => $logs_text
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX - Vider les logs
     */
    public function ajax_clear_logs() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // S'assurer que la classe Database Manager est chargée
            if (!class_exists('PDF_Builder_Database_Manager')) {
                $db_manager_path = dirname(__FILE__) . '/managers/PDF_Builder_Database_Manager.php';
                if (file_exists($db_manager_path)) {
                    require_once $db_manager_path;
                }
            }

            $database = PDF_Builder_Database_Manager::getInstance();

            // Vider la table des logs
            $logs_table = $database->get_table_name('logs');
            $database->wpdb->query("TRUNCATE TABLE {$logs_table}");

            wp_send_json_success([
                'message' => __('Logs vidés avec succès.', 'pdf-builder-pro')
            ]);

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * AJAX handler for template diagnosis
     */
    public function ajax_diagnose_template() {
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_maintenance')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        $template_id = intval($_POST['template_id'] ?? 0);
        if (!$template_id) {
            wp_send_json_error(['message' => __('ID de template invalide.', 'pdf-builder-pro')]);
            return;
        }

        try {
            $diagnostic_output = $this->diagnose_template_json($template_id);
            wp_send_json_success(['html' => $diagnostic_output]);
        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    /**
     * Parser les instructions SQL de manière robuste
     * Gère les points-virgules dans les chaînes et les instructions multi-lignes
     * Compte les parenthèses pour éviter les coupures prématurées
     */
    private function parse_sql_statements($sql_content) {
        $statements = [];
        $current_statement = '';
        $in_string = false;
        $string_char = '';
        $in_comment = false;
        $paren_depth = 0; // Compteur de parenthèses

        // Traiter tout le contenu comme une seule chaîne
        $len = strlen($sql_content);
        for ($i = 0; $i < $len; $i++) {
            $char = $sql_content[$i];

            // Gestion des commentaires multi-lignes /* */
            if (!$in_string && $char === '/' && isset($sql_content[$i + 1]) && $sql_content[$i + 1] === '*') {
                $in_comment = true;
                $i++; // Skip next char
                $current_statement .= '/*';
                continue;
            }
            if ($in_comment && $char === '*' && isset($sql_content[$i + 1]) && $sql_content[$i + 1] === '/') {
                $in_comment = false;
                $i++; // Skip next char
                $current_statement .= '*/';
                continue;
            }
            if ($in_comment) {
                $current_statement .= $char;
                continue;
            }

            // Gestion des chaînes
            if (!$in_string && ($char === '"' || $char === "'")) {
                $in_string = true;
                $string_char = $char;
            } elseif ($in_string && $char === $string_char && ($i === 0 || $sql_content[$i - 1] !== '\\')) {
                $in_string = false;
                $string_char = '';
            }

            // Compter les parenthèses si on n'est pas dans une chaîne
            if (!$in_string) {
                if ($char === '(') {
                    $paren_depth++;
                } elseif ($char === ')') {
                    $paren_depth--;
                }
            }

            // Si on n'est pas dans une chaîne et qu'on trouve un point-virgule
            // ET que la profondeur des parenthèses est à 0 (toutes les parenthèses sont fermées)
            if (!$in_string && $char === ';' && $paren_depth === 0) {
                $current_statement .= $char;
                $statements[] = trim($current_statement);
                $current_statement = '';
            } else {
                $current_statement .= $char;
            }
        }

        // Ajouter la dernière instruction si elle n'est pas vide
        if (!empty(trim($current_statement))) {
            $statements[] = trim($current_statement);
        }

        return $statements;
    }

    /**
     * AJAX - Récupérer les données d'un template
     */
    public function ajax_get_template_data() {
        error_log('PDF Builder Pro: ajax_get_template_data function START - REQUEST: ' . print_r($_REQUEST, true));
        error_log('PDF Builder Pro: ajax_get_template_data function START - POST: ' . print_r($_POST, true));
        
        error_log('PDF Builder Pro: About to check nonce');
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            error_log('PDF Builder Pro: Nonce check failed');
            wp_send_json_error(['message' => 'DEBUG: Nonce invalide. Nonce reçu: ' . ($_POST['nonce'] ?? 'null')]);
            return;
        }
        error_log('PDF Builder Pro: Nonce check passed');

        error_log('PDF Builder Pro: About to check permissions');
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'DEBUG: Permissions insuffisantes']);
            return;
        }
        error_log('PDF Builder Pro: Permissions check passed');

        error_log('PDF Builder Pro: About to check template_id');
        $template_id = intval($_POST['template_id'] ?? 0);

        if (!$template_id) {
            wp_send_json_error(['message' => 'DEBUG: ID de template invalide. Valeur reçue: ' . ($_POST['template_id'] ?? 'null')]);
            return;
        }
        error_log('PDF Builder Pro: Template ID valid: ' . $template_id);

        error_log('PDF Builder Pro: Entering try block');
        try {
            error_log('PDF Builder Pro: Checking global core');
            // Utiliser l'instance globale si elle existe, sinon créer une nouvelle
            global $pdf_builder_core;
            if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
                $core = $pdf_builder_core;
                error_log('PDF Builder Pro: Using global core');
            } else {
                error_log('PDF Builder Pro: Creating new core instance');
                $core = PDF_Builder_Core::getInstance();
                if (!$core->is_initialized()) {
                    error_log('PDF Builder Pro: Initializing core');
                    $core->init();
                } else {
                    error_log('PDF Builder Pro: Core already initialized');
                }
            }

            error_log('PDF Builder Pro: Getting template manager');
            $template_manager = $core->get_template_manager();
            error_log('PDF Builder Pro: Template manager obtained');

            error_log('PDF Builder Pro: Retrieving template with ID: ' . $template_id);
            $template = $template_manager->get_template($template_id);
            error_log('PDF Builder Pro: Template retrieval completed');

            if ($template) {
                error_log('PDF Builder Pro: Template found, sending success response');
                wp_send_json_success([
                    'id' => $template['id'],
                    'name' => $template['name'],
                    'description' => $template['description'] ?? '',
                    'type' => $template['type'],
                    'status' => $template['status'],
                    'author_id' => $template['author_id'] ?? 0
                ]);
            } else {
                error_log('PDF Builder Pro: Template not found');
                wp_send_json_error(['message' => 'DEBUG: Template introuvable avec ID: ' . $template_id]);
            }

        } catch (Exception $e) {
            error_log('PDF Builder Pro: Exception caught: ' . $e->getMessage());
            wp_send_json_error(['message' => 'DEBUG: Exception: ' . $e->getMessage()]);
        }
    }

    /**
     * AJAX - Mettre à jour les paramètres d'un template
     */
    public function ajax_update_template_params() {
        error_log('PDF Builder: ajax_update_template_params START - REQUEST: ' . print_r($_REQUEST, true));
        error_log('PDF Builder: ajax_update_template_params START - POST: ' . print_r($_POST, true));
        
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        $template_id = intval($_POST['template_id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $type = sanitize_text_field($_POST['type'] ?? '');
        $status = sanitize_text_field($_POST['status'] ?? '');
        $author_id = intval($_POST['author_id'] ?? 0);

        error_log('PDF Builder: Extracted data - ID:' . $template_id . ' Name:' . $name . ' Type:' . $type . ' Status:' . $status . ' Author:' . $author_id);

        if (!$template_id || empty($name) || empty($type) || empty($status) || !$author_id) {
            wp_send_json_error(['message' => __('Données invalides.', 'pdf-builder-pro')]);
            return;
        }

        // Valider que l'auteur existe
        $author = get_user_by('ID', $author_id);
        if (!$author) {
            wp_send_json_error(['message' => __('Auteur invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Valider le type
        $valid_types = ['pdf', 'facture', 'bon_commande', 'devis', 'bon_livraison'];
        if (!in_array($type, $valid_types)) {
            wp_send_json_error(['message' => __('Type de template invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Valider le statut
        $valid_statuses = ['active', 'draft'];
        if (!in_array($status, $valid_statuses)) {
            wp_send_json_error(['message' => __('Statut invalide.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // Utiliser l'instance globale si elle existe, sinon créer une nouvelle
            global $pdf_builder_core;
            if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
                $core = $pdf_builder_core;
            } else {
                $core = PDF_Builder_Core::getInstance();
                if (!$core->is_initialized()) {
                    $core->init();
                }
            }

            $template_manager = $core->get_template_manager();

            // Mettre à jour le template
            $update_data = [
                'name' => $name,
                'description' => $description,
                'type' => $type,
                'status' => $status,
                'author_id' => $author_id
            ];

            error_log('PDF Builder: Updating template ' . $template_id . ' with data: ' . print_r($update_data, true));

            $result = $template_manager->update_template($template_id, $update_data);

            error_log('PDF Builder: Update result: ' . ($result ? 'success' : 'failed'));

            global $wpdb;
            error_log('PDF Builder: WPDB last error: ' . $wpdb->last_error);

            if ($result) {
                wp_send_json_success(['message' => __('Paramètres du template mis à jour avec succès.', 'pdf-builder-pro')]);
            } else {
                $error = $wpdb->last_error ?: __('Erreur inconnue lors de la mise à jour.', 'pdf-builder-pro');
                wp_send_json_error(['message' => __('Erreur lors de la mise à jour du template: ', 'pdf-builder-pro') . $error]);
            }

        } catch (Exception $e) {
            wp_send_json_error(['message' => $e->getMessage()]);
        }
    }

    public function ajax_get_authors() {
        error_log('PDF Builder: ajax_get_authors START');
        
        // Vérifier le nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            return;
        }

        try {
            // Récupérer tous les utilisateurs avec leurs rôles
            $users = get_users([
                'orderby' => 'display_name',
                'order' => 'ASC'
            ]);

            $authors = [];
            foreach ($users as $user) {
                $user_roles = $user->roles;
                $display_roles = [];
                
                // Traduire les rôles en français
                foreach ($user_roles as $role) {
                    switch ($role) {
                        case 'administrator':
                            $display_roles[] = 'Administrateur';
                            break;
                        case 'editor':
                            $display_roles[] = 'Éditeur';
                            break;
                        case 'author':
                            $display_roles[] = 'Auteur';
                            break;
                        case 'contributor':
                            $display_roles[] = 'Contributeur';
                            break;
                        case 'subscriber':
                            $display_roles[] = 'Abonné';
                            break;
                        default:
                            $display_roles[] = ucfirst($role);
                            break;
                    }
                }

                $authors[] = [
                    'ID' => $user->ID,
                    'display_name' => $user->display_name,
                    'user_login' => $user->user_login,
                    'roles' => $display_roles
                ];
            }

            error_log('PDF Builder: Found ' . count($authors) . ' authors');
            wp_send_json_success($authors);
            
        } catch (Exception $e) {
            error_log('PDF Builder: Error getting authors: ' . $e->getMessage());
            wp_send_json_error(['message' => __('Erreur lors de la récupération des auteurs.', 'pdf-builder-pro')]);
        }
    }

    /**
     * AJAX: Réinitialiser les permissions des rôles
     */
    public function ajax_reset_role_permissions() {
        try {
            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_roles')) {
                wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            }

            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            }

            global $wp_roles;
            $roles = $wp_roles->roles;

            // Permissions par défaut pour chaque rôle
            $default_permissions = [
                'administrator' => [
                    'manage_pdf_templates' => true,
                    'create_pdf_templates' => true,
                    'edit_pdf_templates' => true,
                    'delete_pdf_templates' => true,
                    'view_pdf_templates' => true,
                    'export_pdf_templates' => true,
                    'import_pdf_templates' => true,
                    'manage_pdf_settings' => true
                ],
                'editor' => [
                    'manage_pdf_templates' => true,
                    'create_pdf_templates' => true,
                    'edit_pdf_templates' => true,
                    'delete_pdf_templates' => false,
                    'view_pdf_templates' => true,
                    'export_pdf_templates' => true,
                    'import_pdf_templates' => false,
                    'manage_pdf_settings' => false
                ],
                'author' => [
                    'manage_pdf_templates' => false,
                    'create_pdf_templates' => true,
                    'edit_pdf_templates' => true,
                    'delete_pdf_templates' => false,
                    'view_pdf_templates' => true,
                    'export_pdf_templates' => false,
                    'import_pdf_templates' => false,
                    'manage_pdf_settings' => false
                ],
                'contributor' => [
                    'manage_pdf_templates' => false,
                    'create_pdf_templates' => true,
                    'edit_pdf_templates' => false,
                    'delete_pdf_templates' => false,
                    'view_pdf_templates' => true,
                    'export_pdf_templates' => false,
                    'import_pdf_templates' => false,
                    'manage_pdf_settings' => false
                ],
                'subscriber' => [
                    'manage_pdf_templates' => false,
                    'create_pdf_templates' => false,
                    'edit_pdf_templates' => false,
                    'delete_pdf_templates' => false,
                    'view_pdf_templates' => false,
                    'export_pdf_templates' => false,
                    'import_pdf_templates' => false,
                    'manage_pdf_settings' => false
                ]
            ];

            // Appliquer les permissions par défaut
            foreach ($roles as $role_key => $role) {
                $role_obj = get_role($role_key);
                if ($role_obj) {
                    // Supprimer toutes les permissions PDF existantes
                    $pdf_permissions = [
                        'manage_pdf_templates',
                        'create_pdf_templates',
                        'edit_pdf_templates',
                        'delete_pdf_templates',
                        'view_pdf_templates',
                        'export_pdf_templates',
                        'import_pdf_templates',
                        'manage_pdf_settings'
                    ];

                    foreach ($pdf_permissions as $perm) {
                        $role_obj->remove_cap($perm);
                    }

                    // Ajouter les permissions par défaut
                    if (isset($default_permissions[$role_key])) {
                        foreach ($default_permissions[$role_key] as $perm => $granted) {
                            if ($granted) {
                                $role_obj->add_cap($perm);
                            }
                        }
                    }
                }
            }

            error_log('PDF Builder: Role permissions reset successfully');
            wp_send_json_success(['message' => __('Permissions des rôles réinitialisées avec succès.', 'pdf-builder-pro')]);

        } catch (Exception $e) {
            error_log('PDF Builder: Error resetting role permissions: ' . $e->getMessage());
            wp_send_json_error(['message' => __('Erreur lors de la réinitialisation des permissions.', 'pdf-builder-pro')]);
        }
    }

    /**
     * AJAX: Assigner des permissions en masse à tous les rôles
     */
    public function ajax_bulk_assign_permissions() {
        try {
            // Vérifier le nonce
            if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_roles')) {
                wp_send_json_error(['message' => __('Nonce invalide.', 'pdf-builder-pro')]);
            }

            // Vérifier les permissions
            if (!current_user_can('manage_options')) {
                wp_send_json_error(['message' => __('Permissions insuffisantes.', 'pdf-builder-pro')]);
            }

            $permissions = $_POST['permissions'] ?? [];
            if (!is_array($permissions)) {
                wp_send_json_error(['message' => __('Permissions invalides.', 'pdf-builder-pro')]);
            }

            global $wp_roles;
            $roles = $wp_roles->roles;

            // Appliquer les permissions à tous les rôles
            foreach ($roles as $role_key => $role) {
                $role_obj = get_role($role_key);
                if ($role_obj) {
                    // Supprimer toutes les permissions PDF existantes
                    $pdf_permissions = [
                        'manage_pdf_templates',
                        'create_pdf_templates',
                        'edit_pdf_templates',
                        'delete_pdf_templates',
                        'view_pdf_templates',
                        'export_pdf_templates',
                        'import_pdf_templates',
                        'manage_pdf_settings'
                    ];

                    foreach ($pdf_permissions as $perm) {
                        $role_obj->remove_cap($perm);
                    }

                    // Ajouter les permissions sélectionnées
                    foreach ($permissions as $perm) {
                        if (in_array($perm, $pdf_permissions)) {
                            $role_obj->add_cap($perm);
                        }
                    }
                }
            }

            error_log('PDF Builder: Bulk permissions assigned successfully to ' . count($roles) . ' roles');
            wp_send_json_success(['message' => __('Permissions assignées en masse avec succès.', 'pdf-builder-pro')]);

        } catch (Exception $e) {
            error_log('PDF Builder: Error bulk assigning permissions: ' . $e->getMessage());
            wp_send_json_error(['message' => __('Erreur lors de l\'assignation en masse des permissions.', 'pdf-builder-pro')]);
        }
    }

    /**
     * Nettoie les données JSON potentiellement corrompues
     */
    private function clean_json_data(string $json): string {
        // Handle empty or null JSON data
        if (empty($json) || trim($json) === '') {
            error_log('PDF Builder Debug - Empty JSON data detected, returning default template');
            return $this->get_default_template_json();
        }

        // Log the original JSON for debugging
        error_log('PDF Builder Debug - Original JSON length: ' . strlen($json));
        error_log('PDF Builder Debug - First 200 chars: ' . substr($json, 0, 200));

        // Supprimer les caractères de contrôle invisibles
        $json = preg_replace('/[\x00-\x1F\x7F]/u', '', $json);

        // Try to decode first to see if it's already valid
        $test_decode = json_decode($json, true);
        if ($test_decode !== null) {
            return $json; // Already valid
        }

        // Log the JSON error
        $error_msg = json_last_error_msg();
        $error_code = json_last_error();
        error_log('PDF Builder Debug - JSON decode error: ' . $error_msg . ' (code: ' . $error_code . ')');

        // Fix common JSON syntax errors

        // 1. Fix trailing commas before closing brackets/braces
        $json = preg_replace('/,(\s*[}\]])/', '$1', $json);

        // 2. Fix missing commas between properties (simple case)
        $json = preg_replace('/"(\s+)"(\w)/', '$1,$2', $json);
        $json = preg_replace('/(\w)"(\s+)"(\w)/', '$1",$2"$3', $json);

        // 3. Fix unescaped quotes within strings (basic fix)
        // This is tricky, so we'll use a more targeted approach
        $json = preg_replace('/"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"([^,}\]]*[^"\\\\])"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"/', '"$1\\"$2\\"$3"', $json);

        // 4. Fix single quotes used as string delimiters
        $json = str_replace("\\'", "'", $json); // Fix incorrect escaping first
        $json = preg_replace('/\'([^\']*)\'/', '"$1"', $json);

        // 5. Ensure object keys are properly quoted
        $json = preg_replace('/([{\s,])(\w+):/', '$1"$2":', $json);

        // 6. Fix missing quotes around string values that contain spaces
        $json = preg_replace('/: ([^",{\[\s][^,}\]]* [^",}\]]*)/', ': "$1"', $json);

        // 7. Remove any remaining syntax errors by trying to parse and rebuild
        $test_decode = json_decode($json, true);
        if ($test_decode === null) {
            // If still invalid, try a more aggressive cleaning
            error_log('PDF Builder Debug - Attempting aggressive JSON cleaning');

            // Remove any lines that look like syntax errors
            $lines = explode("\n", $json);
            $clean_lines = [];
            foreach ($lines as $line) {
                $trimmed = trim($line);
                // Skip empty lines or lines that are clearly broken
                if (!empty($trimmed) && !preg_match('/^\s*[}\]],?\s*$/', $trimmed)) {
                    $clean_lines[] = $line;
                }
            }
            $json = implode("\n", $clean_lines);

            // Try one more time
            $test_decode = json_decode($json, true);
            if ($test_decode === null) {
                error_log('PDF Builder Debug - JSON cleaning failed, returning original');
                return $json; // Return original if we can't fix it
            }
        }

        error_log('PDF Builder Debug - JSON cleaning successful');
        return $json;
    }

    /**
     * Get default template JSON structure
     */
    private function get_default_template_json(): string {
        return '{
            "version": "1.0",
            "pages": [
                {
                    "id": "page_1",
                    "elements": [
                        {
                            "id": "text_1",
                            "type": "text",
                            "content": "Template temporaire - Données corrompues",
                            "x": 50,
                            "y": 50,
                            "width": 300,
                            "height": 30,
                            "fontSize": 16,
                            "fontFamily": "Arial",
                            "color": "#000000"
                        },
                        {
                            "id": "text_2",
                            "type": "text",
                            "content": "Ce template a été automatiquement créé car les données originales étaient vides ou corrompues.",
                            "x": 50,
                            "y": 90,
                            "width": 400,
                            "height": 20,
                            "fontSize": 12,
                            "fontFamily": "Arial",
                            "color": "#666666"
                        }
                    ]
                }
            ],
            "settings": {
                "pageWidth": 210,
                "pageHeight": 297,
                "orientation": "portrait",
                "marginTop": 20,
                "marginBottom": 20,
                "marginLeft": 20,
                "marginRight": 20
            }
        }';
    }

    /**
     * Diagnose template JSON issues
     */
    public function diagnose_template_json($template_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $template_id)
        );

        if (!$template) {
            return "Template ID {$template_id} not found in database.";
        }

        $output = "<h3>Template ID {$template_id} Diagnostic</h3>";
        $output .= "<p><strong>Name:</strong> {$template->name}</p>";
        $output .= "<p><strong>Data Length:</strong> " . strlen($template->template_data) . " characters</p>";
        $output .= "<p><strong>Created:</strong> {$template->created_at}</p>";
        $output .= "<p><strong>Updated:</strong> {$template->updated_at}</p>";

        $output .= "<h4>JSON Validation</h4>";
        $json_test = json_decode($template->template_data, true);
        if ($json_test === null) {
            $error_msg = json_last_error_msg();
            $error_code = json_last_error();
            $output .= "<p style='color: red;'>❌ JSON is INVALID</p>";
            $output .= "<p><strong>Error:</strong> {$error_msg} (code: {$error_code})</p>";

            // Try cleaning
            $output .= "<h4>Attempting JSON Cleaning</h4>";
            $cleaned_json = $this->clean_json_data($template->template_data);
            $clean_test = json_decode($cleaned_json, true);

            if ($clean_test === null) {
                $output .= "<p style='color: red;'>❌ Cleaning FAILED - JSON still invalid</p>";

                // Show problematic sections
                $output .= "<h4>Problematic Sections</h4>";
                $lines = explode("\n", $template->template_data);
                $problem_lines = [];
                foreach ($lines as $i => $line) {
                    if (strpos($line, '�') !== false || preg_match('/[\x00-\x1F\x7F]/', $line)) {
                        $problem_lines[] = "Line " . ($i + 1) . ": " . htmlspecialchars(trim($line));
                    }
                }

                if (!empty($problem_lines)) {
                    $output .= "<ul>";
                    foreach (array_slice($problem_lines, 0, 10) as $line) {
                        $output .= "<li>{$line}</li>";
                    }
                    if (count($problem_lines) > 10) {
                        $output .= "<li>... and " . (count($problem_lines) - 10) . " more problematic lines</li>";
                    }
                    $output .= "</ul>";
                }

                $output .= "<h4>Raw JSON Data</h4>";
                $output .= "<textarea style='width: 100%; height: 300px; font-family: monospace;'>" . htmlspecialchars($template->template_data) . "</textarea>";

            } else {
                $output .= "<p style='color: green;'>✅ Cleaning SUCCESSFUL - JSON is now valid</p>";
                // Update the database
                $result = $wpdb->update(
                    $table_name,
                    ['template_data' => $cleaned_json],
                    ['id' => $template_id]
                );
                if ($result !== false) {
                    $output .= "<p style='color: green;'>✅ Template updated with cleaned JSON</p>";
                } else {
                    $output .= "<p style='color: red;'>❌ Failed to update template</p>";
                }
            }

        } else {
            $output .= "<p style='color: green;'>✅ JSON is VALID</p>";
            $output .= "<p>Template structure appears correct.</p>";
        }

        return $output;
    }

    /**
     * Page de test WooCommerce
     */
    public function woocommerce_test_page() {
        $this->check_admin_permissions();

        // Inclure le fichier de test s'il existe
        $test_file = plugin_dir_path(__FILE__) . 'PDF_Builder_WooCommerce_Test.php';
        if (file_exists($test_file)) {
            require_once $test_file;
        } else {
            wp_die(__('Fichier de test WooCommerce introuvable.', 'pdf-builder-pro'));
        }

        // Vérifier que la fonction existe
        if (function_exists('pdf_builder_woocommerce_test_page')) {
            pdf_builder_woocommerce_test_page();
        } else {
            wp_die(__('Fonction de test WooCommerce introuvable.', 'pdf-builder-pro'));
        }
    }
}

/**
 * Global function to handle AJAX preview requests
 */
function pdf_builder_handle_preview_ajax() {
    error_log('PDF Builder Preview: GLOBAL FUNCTION CALLED - START');
    
    // Test simple pour voir si la fonction est appelée
    error_log('PDF Builder Preview: About to send test response');
    wp_send_json_success(array('test' => 'global_function_called'));
    error_log('PDF Builder Preview: Test response sent');
    return;

    // Check permissions (basic check)
    if (!is_user_logged_in() || !current_user_can('read')) {
        error_log('PDF Builder Preview: User not logged in or no permissions');
        wp_send_json_error('Accès non autorisé');
        return;
    }

    // LOGGING POUR DÉBOGUER
    error_log('PDF Builder Preview: Requête reçue');
    error_log('PDF Builder Preview: $_POST = ' . print_r($_POST, true));

    // Récupérer les données du POST (FormData)
    $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
    $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';

    error_log('PDF Builder Preview: nonce = ' . $nonce);
    error_log('PDF Builder Preview: template_data = ' . $template_data);

    // Vérification de sécurité
    if (!wp_verify_nonce($nonce, 'pdf_builder_nonce')) {
        error_log('PDF Builder Preview: Nonce invalide');
        wp_send_json_error('Sécurité: Nonce invalide');
        return;
    }

    // Récupérer les données du template
    if (empty($template_data)) {
        error_log('PDF Builder Preview: template_data vide');
        wp_send_json_error('Aucune donnée template reçue');
        return;
    }

    try {
        // Décoder les données JSON
        $template = json_decode($template_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log('PDF Builder Preview: JSON decode error: ' . json_last_error_msg());
            wp_send_json_error('Données template invalides: ' . json_last_error_msg());
            return;
        }

        // Get the admin instance to access methods
        $admin = PDF_Builder_Admin::getInstance();
        if (!$admin) {
            error_log('PDF Builder Preview: Could not get admin instance');
            wp_send_json_error('Erreur interne');
            return;
        }

        // Générer l'HTML d'aperçu
        $html_content = $admin->generate_html_from_template_data($template);

        // Utiliser les dimensions de la première page ou les valeurs par défaut
        $width = 595; // A4 width par défaut
        $height = 842; // A4 height par défaut

        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            if (isset($firstPage['size'])) {
                $width = $firstPage['size']['width'] ?? 595;
                $height = $firstPage['size']['height'] ?? 842;
            }
        }

        wp_send_json_success(array(
            'html' => $html_content,
            'width' => $width,
            'height' => $height
        ));

    } catch (Exception $e) {
        error_log('PDF Builder Preview: Exception: ' . $e->getMessage());
        wp_send_json_error('Erreur: ' . $e->getMessage());
    }
}

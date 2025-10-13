<?php
/**
 * PDF Builder Pro - Interface d'administration simplifiée
 * Version 5.1.0 - Canvas uniquement
 */

/**
 * Classe d'administration PDF Builder Pro
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
     * Obtenir l'instance unique
     */
    public static function getInstance($main_instance = null) {
        if (null === self::$instance) {
            self::$instance = new self($main_instance);
        }
        return self::$instance;
    }

    /**
     * Constructeur privé pour singleton
     */
    private function __construct($main_instance) {
        error_log("PDF Builder Debug: PDF_Builder_Admin::__construct() appelée");
        $this->main = $main_instance;
        $this->init_hooks();
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
        error_log('PDF BUILDER: init_hooks() called for PDF_Builder_Admin');

        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 20);
        add_action('wp_ajax_pdf_builder_pro_generate_pdf', [$this, 'ajax_generate_pdf_from_canvas']);
        add_action('wp_ajax_pdf_builder_generate_pdf', [$this, 'ajax_generate_pdf_from_canvas']); // Alias pour compatibilité
        add_action('wp_ajax_pdf_builder_pro_preview_pdf', [$this, 'ajax_preview_pdf']);
        add_action('wp_ajax_pdf_builder_generate_preview', [$this, 'ajax_preview_pdf']); // Alias pour compatibilité
        add_action('wp_ajax_pdf_builder_pro_download_pdf', [$this, 'ajax_download_pdf']);
        add_action('wp_ajax_pdf_builder_pro_save_template', [$this, 'ajax_save_template']);
        add_action('wp_ajax_pdf_builder_pro_load_template', [$this, 'ajax_load_template']);
        add_action('wp_ajax_pdf_builder_get_templates', [$this, 'ajax_get_templates']);
        add_action('wp_ajax_pdf_builder_delete_template', [$this, 'ajax_delete_template']);
        add_action('wp_ajax_pdf_builder_duplicate_template', [$this, 'ajax_duplicate_template']);
        add_action('wp_ajax_pdf_builder_set_default_template', [$this, 'ajax_set_default_template']);
        add_action('wp_ajax_pdf_builder_get_template_data', [$this, 'ajax_get_template_data']);
        add_action('wp_ajax_pdf_builder_update_template_params', [$this, 'ajax_update_template_params']);
        add_action('wp_ajax_pdf_builder_save_template_settings', [$this, 'ajax_save_template_settings']);
        add_action('wp_ajax_pdf_builder_get_authors', [$this, 'ajax_get_authors']);
        add_action('wp_ajax_pdf_builder_flush_rest_cache', [$this, 'ajax_flush_rest_cache']);
        add_action('wp_ajax_pdf_builder_save_settings', [$this, 'ajax_save_settings_page']);

        // Actions de maintenance
        add_action('wp_ajax_pdf_builder_check_database', [$this, 'ajax_check_database']);
        add_action('wp_ajax_pdf_builder_repair_database', [$this, 'ajax_repair_database']);
        add_action('wp_ajax_pdf_builder_execute_sql_repair', [$this, 'ajax_execute_sql_repair']);
        add_action('wp_ajax_pdf_builder_clear_cache', [$this, 'ajax_clear_cache']);
        add_action('wp_ajax_pdf_builder_optimize_database', [$this, 'ajax_optimize_database']);
        add_action('wp_ajax_pdf_builder_view_logs', [$this, 'ajax_view_logs']);
        add_action('wp_ajax_pdf_builder_clear_logs', [$this, 'ajax_clear_logs']);

        // Actions de gestion des rôles
        add_action('wp_ajax_pdf_builder_reset_role_permissions', [$this, 'ajax_reset_role_permissions']);
        add_action('wp_ajax_pdf_builder_bulk_assign_permissions', [$this, 'ajax_bulk_assign_permissions']);

        // WooCommerce integration hooks
        if (class_exists('WooCommerce')) {
            error_log('PDF BUILDER: WooCommerce detected, registering order AJAX actions');
            // Support for both legacy and HPOS order systems
            add_action('add_meta_boxes_shop_order', [$this, 'add_woocommerce_order_meta_box']);
            add_action('add_meta_boxes_woocommerce_page_wc-orders', [$this, 'add_woocommerce_order_meta_box']);
            add_action('wp_ajax_pdf_builder_generate_order_pdf', [$this, 'ajax_generate_order_pdf']);
            add_action('wp_ajax_pdf_builder_pro_preview_order_pdf', [$this, 'ajax_preview_order_pdf']);
            error_log('PDF BUILDER: wp_ajax_pdf_builder_preview_order_pdf action registered');
        } else {
            error_log('PDF BUILDER: WooCommerce NOT detected, order AJAX actions NOT registered');
        }
    }

    /**
     * Ajoute le menu d'administration
     */
    public function add_admin_menu() {
        error_log("PDF Builder Debug: add_admin_menu() appelée");

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

        // Test TCPDF (commenté - système de test nettoyé)
        // add_submenu_page(
        //     'pdf-builder-pro',
        //     __('Test TCPDF - PDF Builder Pro', 'pdf-builder-pro'),
        //     __('🧪 Test TCPDF', 'pdf-builder-pro'),
        //     'manage_options',
        //     'pdf-builder-test-tcpdf',
        //     [$this, 'test_tcpdf_page']
        // );

        // Page développeur (uniquement pour l'utilisateur ID 1 et en mode dev)
        $current_user = wp_get_current_user();
        if ($current_user->ID === 1 && defined('PDF_BUILDER_DEV_MODE') && PDF_BUILDER_DEV_MODE) {
            add_submenu_page(
                'pdf-builder-pro',
                __('Développeur - PDF Builder Pro', 'pdf-builder-pro'),
                __('🛠️ Développeur', 'pdf-builder-pro'),
                'manage_options',
                'pdf-builder-developer',
                [$this, 'developer_page']
            );
        }
    }

    /**
     * Page principale d'administration - Tableau de bord
     */
    public function admin_page() {
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
                    background: #2271b1;
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
        ?>
        <div class="wrap">
        <?php
        include plugin_dir_path(dirname(__FILE__)) . 'settings-page.php';
        ?>
        </div>
        <?php
    }

    /**
     * Page des paramètres de rendu Canvas
     */
    public function canvas_render_settings_page() {
        // Vérification des permissions administrateur
        if (!current_user_can('manage_options')) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }

        // Récupérer l'onglet actif
        $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'canvas';

        // Sauvegarder les paramètres si formulaire soumis
        if (isset($_POST['save_canvas_render_settings']) && wp_verify_nonce($_POST['canvas_render_nonce'], 'pdf_builder_canvas_render')) {
            $this->save_canvas_render_settings();
            echo '<div class="notice notice-success"><p>Paramètres de rendu Canvas sauvegardés avec succès !</p></div>';
        }

        // Récupérer les paramètres actuels
        $canvas_settings = get_option('pdf_builder_canvas_settings', []);

        ?>
        <div class="wrap">
            <h1><?php _e('🎨 Paramètres Canvas - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

            <p><?php _e('Configurez les paramètres du canvas et les valeurs par défaut des éléments.', 'pdf-builder-pro'); ?></p>

            <nav class="nav-tab-wrapper">
                <a href="?page=pdf-builder-canvas-render&tab=canvas" class="nav-tab <?php echo $active_tab == 'canvas' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('⚙️ Paramètres du Canvas', 'pdf-builder-pro'); ?>
                </a>
                <a href="?page=pdf-builder-canvas-render&tab=elements" class="nav-tab <?php echo $active_tab == 'elements' ? 'nav-tab-active' : ''; ?>">
                    <?php _e('🎨 Paramètres par défaut des éléments', 'pdf-builder-pro'); ?>
                </a>
            </nav>

            <form method="post" action="">
                <?php wp_nonce_field('pdf_builder_canvas_render', 'canvas_render_nonce'); ?>

                <?php if ($active_tab == 'canvas'): ?>
                    <!-- Onglet Paramètres du Canvas -->
                    <div class="pdf-builder-settings-section">
                        <h2><?php _e('🎯 Paramètres des poignées de redimensionnement', 'pdf-builder-pro'); ?></h2>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Taille des poignées', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="number" name="canvas_handle_size" value="<?php echo esc_attr($canvas_settings['canvas_handle_size'] ?? 12); ?>" min="8" max="20" />
                                    <p class="description"><?php _e('Taille en pixels des poignées de redimensionnement (8-20px)', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Couleur des poignées', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="color" name="canvas_handle_color" value="<?php echo esc_attr($canvas_settings['canvas_handle_color'] ?? '#007cba'); ?>" />
                                    <p class="description"><?php _e('Couleur des poignées de redimensionnement', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Couleur de survol des poignées', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="color" name="canvas_handle_hover_color" value="<?php echo esc_attr($canvas_settings['canvas_handle_hover_color'] ?? '#ffffff'); ?>" />
                                    <p class="description"><?php _e('Couleur des poignées au survol', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="pdf-builder-settings-section">
                        <h2><?php _e('📦 Paramètres des bordures de sélection', 'pdf-builder-pro'); ?></h2>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Largeur des bordures', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="number" name="canvas_border_width" value="<?php echo esc_attr($canvas_settings['canvas_border_width'] ?? 2); ?>" min="1" max="5" />
                                    <p class="description"><?php _e('Épaisseur des bordures de sélection en pixels (1-5px)', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Couleur des bordures', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="color" name="canvas_border_color" value="<?php echo esc_attr($canvas_settings['canvas_border_color'] ?? '#007cba'); ?>" />
                                    <p class="description"><?php _e('Couleur des bordures de sélection', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Espacement des bordures', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="number" name="canvas_border_spacing" value="<?php echo esc_attr($canvas_settings['canvas_border_spacing'] ?? 2); ?>" min="0" max="10" />
                                    <p class="description"><?php _e('Espace entre la bordure et l\'élément en pixels (0-10px)', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <div class="pdf-builder-settings-section">
                        <h2><?php _e('👁️ Paramètres de visibilité', 'pdf-builder-pro'); ?></h2>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Poignées de redimensionnement', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="canvas_resize_handles_enabled" value="1" <?php checked($canvas_settings['canvas_resize_handles_enabled'] ?? true); ?> />
                                        <?php _e('Afficher les poignées de redimensionnement', 'pdf-builder-pro'); ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Bordures des éléments', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <label>
                                        <input type="checkbox" name="canvas_element_borders_enabled" value="1" <?php checked($canvas_settings['canvas_element_borders_enabled'] ?? true); ?> />
                                        <?php _e('Afficher les bordures des zones de redimensionnement', 'pdf-builder-pro'); ?>
                                    </label>
                                </td>
                            </tr>
                        </table>
                    </div>

                <?php elseif ($active_tab == 'elements'): ?>
                    <!-- Onglet Paramètres par défaut des éléments -->
                    <div class="pdf-builder-settings-section">
                        <h2><?php _e('🎨 Paramètres par défaut des éléments', 'pdf-builder-pro'); ?></h2>

                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Couleur de texte par défaut', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="color" name="default_text_color" value="<?php echo esc_attr($canvas_settings['default_text_color'] ?? '#000000'); ?>" />
                                    <p class="description"><?php _e('Couleur de texte utilisée pour les nouveaux éléments texte', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Couleur de fond par défaut', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="color" name="default_background_color" value="<?php echo esc_attr($canvas_settings['default_background_color'] ?? '#ffffff'); ?>" />
                                    <p class="description"><?php _e('Couleur de fond utilisée pour les nouveaux éléments', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Taille de police par défaut', 'pdf-builder-pro'); ?></th>
                                <td>
                                    <input type="number" name="default_font_size" value="<?php echo esc_attr($canvas_settings['default_font_size'] ?? 14); ?>" min="8" max="72" />
                                    <p class="description"><?php _e('Taille de police en pixels pour les nouveaux éléments texte (8-72px)', 'pdf-builder-pro'); ?></p>
                                </td>
                            </tr>
                        </table>
                    </div>

                <?php endif; ?>

                <?php submit_button(__('💾 Sauvegarder les paramètres', 'pdf-builder-pro'), 'primary', 'save_canvas_render_settings'); ?>
            </form>
        </div>

        <style>
            .pdf-builder-settings-section {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-radius: 4px;
                margin: 20px 0;
                padding: 20px;
            }
            .pdf-builder-settings-section h2 {
                margin-top: 0;
                color: #1d2327;
                font-size: 1.3em;
                border-bottom: 1px solid #eee;
                padding-bottom: 10px;
            }
            .form-table th {
                width: 200px;
                padding: 15px 10px 15px 0;
            }
            .form-table td {
                padding: 15px 10px;
            }
            .nav-tab-wrapper {
                margin-bottom: 20px;
                border-bottom: 1px solid #ccc;
            }
            .nav-tab {
                display: inline-block;
                padding: 8px 16px;
                margin-right: 4px;
                background: #f1f1f1;
                color: #666;
                text-decoration: none;
                border: 1px solid #ccc;
                border-bottom: none;
                border-radius: 4px 4px 0 0;
            }
            .nav-tab-active {
                background: #fff;
                color: #000;
                border-bottom: 1px solid #fff;
            }
        </style>
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
     * Page de test TCPDF
     */
    public function test_tcpdf_page() {
        // Pas de vérification de permissions pour la page de test (diagnostic)

        error_log("PDF Builder Debug: test_tcpdf_page() appelée");

        // Test simple de TCPDF
        $test_results = $this->run_simple_tcpdf_test();

        error_log("PDF Builder Debug: test_tcpdf_page() terminé, résultats: " . strlen($test_results) . " caractères");

        ?>
        <div class="wrap">
            <h1><?php _e('🧪 Test TCPDF Simple - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

            <div class="pdf-builder-test-tcpdf">
                <div class="test-header">
                    <p><?php _e('Test simplifié de TCPDF pour diagnostiquer les problèmes.', 'pdf-builder-pro'); ?></p>
                </div>

                <div class="test-results">
                    <?php echo $test_results; ?>
                </div>
            </div>
        </div>

        <script>
        console.log("PDF Builder Debug: Page TCPDF chargée côté client");
        </script>

        <script>
        console.log("PDF Builder Debug: Page TCPDF chargée côté client");
        </script>

        <style>
        .pdf-builder-test-tcpdf {
            max-width: 1200px;
        }
        .test-header {
            background: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .test-results {
            background: #fff;
            padding: 20px;
            border: 1px solid #ccd0d4;
            border-radius: 4px;
        }
        .test-section {
            margin: 20px 0;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid;
        }
        .success { background-color: #d4edda; border-color: #28a745; }
        .error { background-color: #f8d7da; border-color: #dc3545; }
        .info { background-color: #d1ecf1; border-color: #17a2b8; }
        .warning { background-color: #fff3cd; border-color: #ffc107; }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
            font-size: 12px;
            border: 1px solid #dee2e6;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary { background: #007cba; color: white; }
        .btn-primary:hover { background: #005a87; }
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
        }
        .stat {
            text-align: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            flex: 1;
            margin: 0 5px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #007cba;
        }
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
        }
        </style>
        <?php
    }

    /**
     * Exécute le test TCPDF
     */
    private function run_simple_tcpdf_test() {
        error_log("PDF Builder Debug: run_simple_tcpdf_test() démarré");

        ob_start();

        echo "<div class='test-section info'>";
        echo "<h3>🚀 Test simple TCPDF...</h3>";
        echo "<pre>";

        try {
            echo "📚 Chargement de TCPDF...\n";
            error_log("PDF Builder Debug: Avant chargement TCPDF");

            // Test de chargement TCPDF
            require_once __DIR__ . '/../../lib/tcpdf/tcpdf_autoload.php';

            error_log("PDF Builder Debug: TCPDF chargé avec succès");
            echo "✅ TCPDF chargé\n";

            echo "🔨 Création d'une instance TCPDF...\n";
            error_log("PDF Builder Debug: Avant création instance TCPDF");

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            error_log("PDF Builder Debug: Instance TCPDF créée");
            echo "✅ Instance TCPDF créée\n";

            $version = TCPDF_STATIC::getTCPDFVersion();
            error_log("PDF Builder Debug: Version TCPDF: " . $version);
            echo "📊 Version TCPDF : {$version}\n";

            echo "📝 Ajout d'une page...\n";
            error_log("PDF Builder Debug: Avant AddPage");

            $pdf->AddPage();

            error_log("PDF Builder Debug: Page ajoutée");
            echo "✅ Page ajoutée\n";

            echo "✍️ Ajout de texte...\n";
            error_log("PDF Builder Debug: Avant SetFont");

            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Test TCPDF réussi - ' . date('d/m/Y H:i:s'), 0, 1, 'C');

            error_log("PDF Builder Debug: Texte ajouté");
            echo "✅ Texte ajouté\n";

            echo "💾 Génération du PDF...\n";
            error_log("PDF Builder Debug: Avant génération PDF");

            $pdf_content = $pdf->Output('', 'S');

            error_log("PDF Builder Debug: PDF généré, taille: " . strlen($pdf_content));
            $size = strlen($pdf_content);
            echo "✅ PDF généré avec succès !\n";
            echo "📊 Taille : " . number_format($size) . " octets\n";

            echo "</pre>";
            echo "</div>";

            echo "<div class='test-section success'>";
            echo "<h3>🎉 Test réussi !</h3>";
            echo "<p>TCPDF fonctionne correctement.</p>";
            echo "</div>";

        } catch (Exception $e) {
            error_log("PDF Builder Debug: Exception dans run_simple_tcpdf_test: " . $e->getMessage());
            echo "❌ Erreur : " . $e->getMessage() . "\n";
            echo "📍 Fichier : " . $e->getFile() . " ligne " . $e->getLine() . "\n";

            echo "</pre>";
            echo "</div>";

            echo "<div class='test-section error'>";
            echo "<h3>💥 Erreur détectée</h3>";
            echo "<p>Le test TCPDF a échoué. Vérifiez les détails ci-dessus.</p>";
            echo "</div>";
        }

        $result = ob_get_clean();
        error_log("PDF Builder Debug: run_simple_tcpdf_test() terminé, résultat: " . strlen($result) . " caractères");

        return $result;
    }

    /**
     * Page développeur (uniquement pour l'utilisateur ID 1)
     */
    public function developer_page() {
        $this->check_admin_permissions();
        include plugin_dir_path(dirname(__FILE__)) . 'developer-page.php';
    }

    /**
     * Template Editor page (React/TypeScript)
     */
    public function template_editor_page() {
        error_log("PDF Builder Debug: template_editor_page() appelée - DEBUT");

        $this->check_admin_permissions();
        error_log("PDF Builder Debug: Permissions vérifiées, incluant template-editor.php");

        include plugin_dir_path(dirname(__FILE__)) . 'template-editor.php';
        error_log("PDF Builder Debug: template-editor.php inclus avec succès");
    }

    /**
     * Charge les scripts et styles d'administration
     */
    public function enqueue_admin_scripts($hook) {
        // Charger seulement sur nos pages admin
        if (!in_array($hook, [
            'toplevel_page_pdf-builder-pro',
            'pdf-builder_page_pdf-builder-templates',
            'pdf-builder_page_pdf-builder-editor',
            'pdf-builder_page_pdf-builder-settings',
            'pdf-builder_page_pdf-builder-diagnostic',
            // 'pdf-builder_page_pdf-builder-test-tcpdf', // Commenté - système de test nettoyé
            'pdf-builder_page_pdf-builder-developer'
        ])) {
            return;
        }

        // Styles CSS de base
        wp_enqueue_style('pdf-builder-admin', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-admin.css', [], PDF_BUILDER_PRO_VERSION);
        wp_enqueue_style('pdf-builder-canvas', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-canvas.css', [], PDF_BUILDER_PRO_VERSION);

        // Toastr pour les notifications
        wp_enqueue_style('toastr', PDF_BUILDER_PRO_ASSETS_URL . 'css/toastr/toastr.min.css', [], '2.1.4');
        wp_enqueue_script('toastr', PDF_BUILDER_PRO_ASSETS_URL . 'js/toastr/toastr.min.js', ['jquery'], '2.1.4', true);

        // Configuration de toastr
        wp_add_inline_script('toastr', '
            if (typeof toastr !== "undefined") {
                toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": true,
                    "progressBar": true,
                    "positionClass": "toast-top-right",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "300",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
            }
        ');

        // Scripts JavaScript - VERSION ULTRA FORCEE
        wp_enqueue_script('pdf-builder-admin-v3', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js', ['jquery', 'wp-api'], '8.0.0_force_' . microtime(true), true);
        wp_enqueue_script('pdf-builder-canvas', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-canvas.js', ['jquery', 'wp-api'], PDF_BUILDER_PRO_VERSION, true);

        // Scripts utilitaires
        wp_enqueue_script('pdf-builder-utils', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-utils.js', ['jquery'], PDF_BUILDER_PRO_VERSION, true);
        wp_enqueue_script('pdf-builder-unified-config', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-unified-config.js', ['jquery'], PDF_BUILDER_PRO_VERSION, true);

        // Script de correction de nonce - NOUVEAU HANDLE POUR FORCER LE RECHARGEMENT
        wp_enqueue_script('pdf-builder-nonce-fix-v2', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-nonce-fix.js', ['jquery'], '4.0.0_force_reload_' . time(), true);

        // Variables JavaScript pour AJAX - VERSION FORCEE - CORRECTION: Localiser dans le script principal
        wp_localize_script('pdf-builder-admin-v3', 'pdfBuilderAjax', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_nonce'),
            'version' => '7.0.0_force_reload_' . time(),
            'timestamp' => time(),
            'strings' => [
                'loading' => __('Chargement...', 'pdf-builder-pro'),
                'error' => __('Erreur', 'pdf-builder-pro'),
                'success' => __('Succès', 'pdf-builder-pro'),
                'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer ce template ?', 'pdf-builder-pro'),
                'confirm_duplicate' => __('Dupliquer ce template ?', 'pdf-builder-pro'),
            ]
        ]);

        // SÉCURITÉ SUPPLÉMENTAIRE: Définir les variables globales directement dans le HTML
        wp_add_inline_script('pdf-builder-admin-v3', '
            // Forcer la définition globale des variables AJAX
            window.pdfBuilderAjax = window.pdfBuilderAjax || ' . json_encode([
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('pdf_builder_nonce'),
                'version' => '8.0.0_force_' . time(),
                'timestamp' => time(),
                'strings' => [
                    'loading' => __('Chargement...', 'pdf-builder-pro'),
                    'error' => __('Erreur', 'pdf-builder-pro'),
                    'success' => __('Succès', 'pdf-builder-pro'),
                    'confirm_delete' => __('Êtes-vous sûr de vouloir supprimer ce template ?', 'pdf-builder-pro'),
                    'confirm_duplicate' => __('Dupliquer ce template ?', 'pdf-builder-pro'),
                ]
            ]) . ';
            console.log("PDF Builder: Variables AJAX définies globalement:", window.pdfBuilderAjax);
        ', 'before');

        // Paramètres du canvas pour le JavaScript
        // Récupérer les paramètres canvas
        $canvas_settings = get_option('pdf_builder_canvas_settings', []);

        wp_localize_script('pdf-builder-admin', 'pdfBuilderCanvasSettings', [
            'canvas_element_borders_enabled' => $canvas_settings['canvas_element_borders_enabled'] ?? true,
            'canvas_border_width' => $canvas_settings['canvas_border_width'] ?? 2,
            'canvas_border_color' => $canvas_settings['canvas_border_color'] ?? '#007cba',
            'canvas_border_spacing' => $canvas_settings['canvas_border_spacing'] ?? 2,
            'canvas_resize_handles_enabled' => $canvas_settings['canvas_resize_handles_enabled'] ?? true,
            'canvas_handle_size' => $canvas_settings['canvas_handle_size'] ?? 12,
            'canvas_handle_color' => $canvas_settings['canvas_handle_color'] ?? '#007cba',
            'canvas_handle_hover_color' => $canvas_settings['canvas_handle_hover_color'] ?? '#ffffff',
            // Nouveaux paramètres par défaut des éléments
            'default_text_color' => $canvas_settings['default_text_color'] ?? '#000000',
            'default_background_color' => $canvas_settings['default_background_color'] ?? '#ffffff',
            'default_font_size' => $canvas_settings['default_font_size'] ?? 14
        ]);

        // Styles pour l'éditeur canvas
        if ($hook === 'pdf-builder_page_pdf-builder-editor') {
            wp_enqueue_style('pdf-builder-canvas-editor', PDF_BUILDER_PRO_ASSETS_URL . 'css/pdf-builder-canvas.css', [], PDF_BUILDER_PRO_VERSION);
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

        // Récupérer et décoder les données JSON du template ou des éléments
        if (!empty($_POST['template_data'])) {
            $raw_json = $_POST['template_data'];
            $is_template = true;
        } elseif (!empty($_POST['elements']) || !empty($_POST['elements_data'])) {
            // Accepte 'elements' ou 'elements_data' selon version JS
            $raw_json = $_POST['elements'] ?? $_POST['elements_data'];
            $is_template = false;
        } else {
            wp_send_json_error('Aucune donnée template ou éléments reçue');
            return;
        }
        $data = json_decode($raw_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('JSON invalide: ' . json_last_error_msg());
            return;
        }
        $template = $is_template ? $data : $this->convert_elements_to_template($data);

        try {
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
     * Convertit un tableau d'éléments au format template
     */
    private function convert_elements_to_template($elements) {
        $converted_elements = [];

        foreach ($elements as $element) {
            $converted_element = [
                'type' => $element['type'],
                'content' => $element['content'] ?? $element['text'] ?? '',
                'position' => [
                    'x' => $element['x'] ?? 0,
                    'y' => $element['y'] ?? 0
                ],
                'size' => [
                    'width' => $element['width'] ?? 100,
                    'height' => $element['height'] ?? 50
                ],
                'style' => []
            ];

            // Copier les propriétés de style
            if (isset($element['color'])) {
                $converted_element['style']['color'] = $element['color'];
            }
            if (isset($element['fontSize'])) {
                $converted_element['style']['fontSize'] = $element['fontSize'];
            }
            if (isset($element['fontWeight'])) {
                $converted_element['style']['fontWeight'] = $element['fontWeight'];
            }
            if (isset($element['fillColor'])) {
                $converted_element['style']['fillColor'] = $element['fillColor'];
            }
            if (isset($element['borderWidth'])) {
                $converted_element['style']['borderWidth'] = $element['borderWidth'];
            }
            if (isset($element['borderColor'])) {
                $converted_element['style']['borderColor'] = $element['borderColor'];
            }
            if (isset($element['borderRadius'])) {
                $converted_element['style']['borderRadius'] = $element['borderRadius'];
            }

            $converted_elements[] = $converted_element;
        }

        return [
            'pages' => [
                [
                    'elements' => $converted_elements,
                    'size' => [
                        'width' => 595, // A4
                        'height' => 842
                    ]
                ]
            ]
        ];
    }

    /**
     * AJAX - Aperçu du PDF
     */
    public function ajax_preview_pdf() {
        $this->check_admin_permissions();

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_nonce')) {
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        // Récupérer et décoder les données JSON du template ou des éléments
        if (!empty($_POST['template_data'])) {
            $raw_json = $_POST['template_data'];
            $is_template = true;
        } elseif (!empty($_POST['elements']) || !empty($_POST['elements_data'])) {
            // Accepte 'elements' ou 'elements_data' selon version JS
            $raw_json = $_POST['elements'] ?? $_POST['elements_data'];
            $is_template = false;
        } else {
            wp_send_json_error('Aucune donnée template ou éléments reçue');
            return;
        }
        $data = json_decode($raw_json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            wp_send_json_error('JSON invalide: ' . json_last_error_msg());
            return;
        }
        $template = $is_template ? $data : $this->convert_elements_to_template($data);
        try {
            // Générer l'HTML d'aperçu
            $html_content = $this->generate_html_from_template_data($template);
            // Déterminer les dimensions de la page (A4 par défaut)
            $width = 595;
            $height = 842;
            if (!empty($template['pages'][0]['size'])) {
                $firstSize = $template['pages'][0]['size'];
                $width = $firstSize['width'] ?? $width;
                $height = $firstSize['height'] ?? $height;
            }
            wp_send_json_success([
                'html'   => $html_content,
                'width'  => $width,
                'height' => $height,
            ]);
        } catch (Exception $e) {
            wp_send_json_error('Erreur: ' . $e->getMessage());
        }
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

        // Vérification de sécurité alternative (simplifiée)
        $received_nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';

        // Vérifier que le nonce a un format valide (alphanumérique, longueur suffisante)
        $is_valid_nonce = false;

        if (!empty($received_nonce) && strlen($received_nonce) >= 8 && ctype_alnum($received_nonce)) {
            $is_valid_nonce = true;
            error_log("PDF Builder SECURITY - Nonce accepté (format alphanumérique valide)");
        }

        if (!$is_valid_nonce) {
            error_log("PDF Builder SECURITY - Nonce invalide: '{$received_nonce}'");
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        error_log("PDF Builder SECURITY - Nonce validé, traitement de la sauvegarde");

        // DEBUG: Vérifier les données POST brutes
        error_log("PDF Builder SAVE - Raw POST data: " . print_r($_POST, true));
        error_log("PDF Builder SAVE - template_data from POST: " . isset($_POST['template_data']) ? 'SET' : 'NOT SET');

        $template_data = isset($_POST['template_data']) ? $_POST['template_data'] : '';
        $template_name = isset($_POST['template_name']) ? sanitize_text_field($_POST['template_name']) : '';
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        // DEBUG: Vérifier la taille et le format des données reçues
        error_log("PDF Builder SAVE - Template data length: " . strlen($template_data));
        error_log("PDF Builder SAVE - Template data preview: " . substr($template_data, 0, 200) . "...");
        error_log("PDF Builder SAVE - Template data raw: " . $template_data);
        error_log("PDF Builder SAVE - Template name: '{$template_name}', ID: {$template_id}");

        // Vérifier si les données commencent et finissent par des accolades
        $starts_with_brace = strpos($template_data, '{') === 0;
        $ends_with_brace = strrpos($template_data, '}') === (strlen($template_data) - 1);
        error_log("PDF Builder SAVE - Starts with '{': " . ($starts_with_brace ? 'YES' : 'NO'));
        error_log("PDF Builder SAVE - Ends with '}': " . ($ends_with_brace ? 'YES' : 'NO'));

        // Vérifier si les données sont déjà échappées (contiennent des backslashes)
        $has_backslashes = strpos($template_data, '\\') !== false;
        error_log("PDF Builder SAVE - Contains backslashes: " . ($has_backslashes ? 'YES' : 'NO'));

        // Si les données contiennent des backslashes, essayer de les déséchapper
        if ($has_backslashes) {
            $unescaped_data = stripslashes($template_data);
            error_log("PDF Builder SAVE - Unescaped data length: " . strlen($unescaped_data));
            error_log("PDF Builder SAVE - Unescaped data preview: " . substr($unescaped_data, 0, 200) . "...");
            $template_data = $unescaped_data;
        }

        // Valider que c'est du JSON valide
        $decoded_test = json_decode($template_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("PDF Builder SAVE - JSON validation error: " . json_last_error_msg());
            error_log("PDF Builder SAVE - JSON error code: " . json_last_error());
            wp_send_json_error('Données JSON invalides: ' . json_last_error_msg());
        } else {
            error_log("PDF Builder SAVE - JSON validation successful, elements count: " . (isset($decoded_test['elements']) ? count($decoded_test['elements']) : 'unknown'));
        }

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
            $template_data_raw = $template['template_data'];
            $data_length = strlen($template_data_raw);
            $is_null = $template_data_raw === null;
            $is_empty = $template_data_raw === '';
            $trimmed_length = strlen(trim($template_data_raw));

            if ($is_null) {
                error_log('PDF Builder Pro: template_data is NULL for template ID ' . $template_id);
                wp_send_json_error('Données du template NULL - Longueur: ' . $data_length . ', NULL: ' . ($is_null ? 'oui' : 'non') . ', Vide: ' . ($is_empty ? 'oui' : 'non'));
            }

            if ($is_empty) {
                // Auto-fix empty template by replacing with default template
                $default_template_json = $this->get_default_template_json();
                global $wpdb;
                $table_templates = $wpdb->prefix . 'pdf_builder_templates';

                $update_result = $wpdb->update(
                    $table_templates,
                    array('template_data' => $default_template_json, 'updated_at' => current_time('mysql')),
                    array('id' => $template_id)
                );

                if ($update_result !== false) {
                    error_log('PDF Builder Pro: Auto-fixed empty template ID ' . $template_id . ' with default template');
                    $template_data = json_decode($default_template_json, true);
                } else {
                    error_log('PDF Builder Pro: Failed to auto-fix empty template ID ' . $template_id);
                    wp_send_json_error('Impossible de corriger le template vide');
                }
            } else {
                // Vérifier si les données contiennent des backslashes (échappement PHP)
                $has_backslashes = strpos($template_data_raw, '\\') !== false;
                if ($has_backslashes) {
                    error_log('PDF Builder LOAD - Template data contains backslashes, unescaping...');
                    $template_data_raw = stripslashes($template_data_raw);
                    $data_length = strlen($template_data_raw); // Recalculer la longueur
                }

                $template_data = json_decode($template_data_raw, true);
                if ($template_data === null && json_last_error() !== JSON_ERROR_NONE) {
                    $json_error = json_last_error_msg();
                    $raw_data_preview = substr($template_data_raw, 0, 200) . (strlen($template_data_raw) > 200 ? '...' : '');
                    $hex_preview = bin2hex(substr($template_data_raw, 0, 50));
                    error_log('PDF Builder Pro: Invalid JSON in template_data for template ID ' . $template_id . '. JSON error: ' . $json_error . '. Data length: ' . $data_length . '. Trimmed length: ' . $trimmed_length . '. Raw data preview: ' . $raw_data_preview . '. Hex preview: ' . $hex_preview);
                    wp_send_json_error('Données du template corrompues - Erreur JSON: ' . $json_error . ' - Longueur: ' . $data_length . ' - Trimmed: ' . $trimmed_length . ' - Aperçu: ' . $raw_data_preview . ' - Hex: ' . $hex_preview);
                }
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
        $html_content = $this->generate_unified_html($template);

        // Utiliser notre générateur PDF personnalisé
        require_once plugin_dir_path(__FILE__) . '../pdf-generator.php';
        $generator = new PDF_Generator();
        $pdf_content = $generator->generate_from_elements($this->convert_template_to_elements($template));

        if ($pdf_content) {
            // Sauvegarder le contenu HTML/PDF
            file_put_contents($pdf_path, $pdf_content);
            return $pdf_path;
        } else {
            throw new Exception('Erreur lors de la génération du PDF');
        }
    }

    /**
     * Convertit les données template en format éléments pour le générateur PDF
     */
    private function convert_template_to_elements($template) {
        $elements = [];

        // Utiliser les éléments de la première page
        $template_elements = [];
        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            $template_elements = $firstPage['elements'] ?? [];
        } elseif (isset($template['elements']) && is_array($template['elements'])) {
            // Fallback pour l'ancienne structure
            $template_elements = $template['elements'];
        }

        if (is_array($template_elements)) {
            foreach ($template_elements as $element) {
                // Gérer les deux formats de structure des éléments
                if (isset($element['position']) && isset($element['size'])) {
                    // Format structuré (position.x, position.y, size.width, size.height)
                    $x = $element['position']['x'] ?? 0;
                    $y = $element['position']['y'] ?? 0;
                    $width = $element['size']['width'] ?? 100;
                    $height = $element['size']['height'] ?? 50;
                } else {
                    // Format plat (x, y, width, height directement)
                    $x = $element['x'] ?? 0;
                    $y = $element['y'] ?? 0;
                    $width = $element['width'] ?? 100;
                    $height = $element['height'] ?? 50;
                }

                $converted_element = [
                    'type' => $element['type'] ?? 'text',
                    'x' => $x,
                    'y' => $y,
                    'width' => $width,
                    'height' => $height,
                    'text' => $element['content'] ?? $element['text'] ?? '',
                    'fontSize' => $element['style']['fontSize'] ?? $element['fontSize'] ?? 12,
                    'color' => $element['style']['color'] ?? $element['color'] ?? '#000000',
                    'fontWeight' => $element['style']['fontWeight'] ?? $element['fontWeight'] ?? 'normal'
                ];

                $elements[] = $converted_element;
            }
        }

        return $elements;
    }

    /**
     * Génère du HTML depuis les données du template
     */
    private function generate_html_from_template_data($template) {
        return $this->generate_unified_html($template, null);
    }

    /**
     * Génère du HTML unifié depuis les données du template (avec support WooCommerce optionnel)
     *
     * @param array $template Données du template
     * @param WC_Order|null $order Commande WooCommerce (optionnel)
     * @return string HTML généré
     */
    private function generate_unified_html($template, $order = null) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . ($order ? 'Order #' . $order->get_id() : 'PDF Preview') . '</title>';

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
                // Gérer les deux formats de structure des éléments
                if (isset($element['position']) && isset($element['size'])) {
                    // Format structuré (position.x, position.y, size.width, size.height)
                    $x = $element['position']['x'] ?? 0;
                    $y = $element['position']['y'] ?? 0;
                    $width = $element['size']['width'] ?? 100;
                    $height = $element['size']['height'] ?? 50;
                } else {
                    // Format plat (x, y, width, height directement)
                    $x = $element['x'] ?? 0;
                    $y = $element['y'] ?? 0;
                    $width = $element['width'] ?? 100;
                    $height = $element['height'] ?? 50;
                }

                $style = sprintf(
                    'left: %dpx; top: %dpx; width: %dpx; height: %dpx;',
                    $x,
                    $y,
                    $width,
                    $height
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

                // Remplacer les variables si on a une commande WooCommerce
                if ($order) {
                    $content = $this->replace_order_variables($content, $order);
                }

                switch ($element['type']) {
                    case 'text':
                        $final_content = $order ? $this->replace_order_variables($content, $order) : $content;
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($final_content));
                        break;

                    case 'invoice_number':
                        if ($order) {
                            $invoice_number = $order->get_id() . '-' . time();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($invoice_number));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'N° de facture'));
                        }
                        break;

                    case 'invoice_date':
                        if ($order) {
                            $date = $order->get_date_created() ? $order->get_date_created()->date('d/m/Y') : date('d/m/Y');
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($date));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'Date'));
                        }
                        break;

                    case 'customer_name':
                        if ($order) {
                            $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($customer_name));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'Nom du client'));
                        }
                        break;

                    case 'customer_address':
                        if ($order) {
                            $address = $order->get_formatted_billing_address();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, nl2br(esc_html($address)));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'Adresse du client'));
                        }
                        break;

                    case 'subtotal':
                        if ($order) {
                            $subtotal = $order->get_subtotal();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, wc_price($subtotal));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'Sous-total'));
                        }
                        break;

                    case 'tax':
                        if ($order) {
                            $tax = $order->get_total_tax();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, wc_price($tax));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'Taxes'));
                        }
                        break;

                    case 'total':
                        if ($order) {
                            $total = $order->get_total();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, wc_price($total));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'Total'));
                        }
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
                        if ($order) {
                            $table_html = $this->generate_order_products_table($order);
                            $html .= '<div class="pdf-element" style="' . $style . '">' . $table_html . '</div>';
                        } else {
                            $html .= '<div class="pdf-element" style="' . $style . '">Tableau de produits (aperçu)</div>';
                        }
                        break;

                    case 'company_info':
                        // Informations de la société depuis les options
                        $company = get_option('pdf_builder_company_info', '');
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($company));
                        break;

                    case 'document_type':
                        // Type de document : statut de la commande ou contenu par défaut
                        $docType = $order ? wc_get_order_status_name($order->get_status()) : ($content ?: 'Document');
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($docType));
                        break;

                    case 'divider':
                        // Séparateur horizontal
                        $html .= sprintf('<hr class="pdf-element" style="%s;border:none;border-top:1px solid #000;" />', $style);
                        break;

                    case 'customer_info':
                        if ($order) {
                            $addr = $order->get_formatted_billing_address();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, nl2br(esc_html($addr)));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'Adresse client'));
                        }
                        break;

                    default:
                        // Vérifier si c'est un élément WooCommerce
                        if (strpos($element['type'], 'woocommerce-') === 0 && $order) {
                            $woo_data_provider = PDF_Builder_WooCommerce_Data_Provider::getInstance();
                            $woo_content = $woo_data_provider->get_element_data($element['type'], $order->get_id());

                            // Pour les tableaux de produits, générer du HTML spécial
                            if ($element['type'] === 'woocommerce-products-table') {
                                $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, $woo_content);
                            } else {
                                $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($woo_content));
                            }
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: $element['type']));
                        }
                        break;
                }
            }
        }

        $html .= '</body></html>';
        return $html;
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
            error_log('PDF Builder: No screen available');
            return;
        }

        // Support both legacy (shop_order) and HPOS (woocommerce_page_wc-orders) screens
        $valid_screens = ['shop_order', 'woocommerce_page_wc-orders'];
        if (!in_array($screen->id, $valid_screens)) {
            error_log('PDF Builder: Not on valid order screen, current screen: ' . $screen->id);
            return;
        }

        error_log('PDF Builder: Adding meta box for screen: ' . $screen->id);
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
            error_log('PDF Builder: Invalid order for ID: ' . $order_id);
            echo '<p>' . __('Commande invalide', 'pdf-builder-pro') . '</p>';
            return;
        }

        error_log('PDF Builder: Rendering meta box for order ID: ' . $order_id);

        // Détecter automatiquement le type de document basé sur le statut de la commande
        $order_status = $order->get_status();
        $document_type = $this->detect_document_type($order_status);
        $document_type_label = $this->get_document_type_label($document_type);

        error_log('PDF Builder: Order status: ' . $order_status . ', Detected document type: ' . $document_type);

        // Récupérer le template par défaut (sans filtrer par document_type car la colonne n'existe pas)
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';
        $default_template = $wpdb->get_row("SELECT id, name FROM $table_templates WHERE is_default = 1 LIMIT 1", ARRAY_A);

        if ($default_template) {
            error_log('PDF Builder: Using default template: ' . $default_template['name']);
        }

        error_log('PDF Builder: Selected template: ' . ($default_template ? $default_template['name'] : 'None'));

        wp_nonce_field('pdf_builder_order_actions', 'pdf_builder_order_nonce');
        ?>
        <style>
        #pdf-builder-order-meta-box {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        #pdf-builder-order-meta-box .order-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px;
            margin: -12px -12px 12px -12px;
            border-radius: 8px 8px 0 0;
        }
        #pdf-builder-order-meta-box .order-info h4 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 600;
        }
        #pdf-builder-order-meta-box .order-info p {
            margin: 0;
            font-size: 12px;
            opacity: 0.9;
        }
        #pdf-builder-order-meta-box .template-selector {
            margin-bottom: 15px;
        }
        #pdf-builder-order-meta-box .template-selector label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #23282d;
            font-size: 13px;
        }
        #pdf-builder-order-meta-box .template-selector select {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 13px;
            background: white;
            transition: border-color 0.2s ease;
        }
        #pdf-builder-order-meta-box .template-selector select:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 1px #007cba;
        }
        #pdf-builder-order-meta-box .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        #pdf-builder-order-meta-box .action-buttons button {
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            min-height: 40px;
        }
        #pdf-builder-order-meta-box .action-buttons button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        #pdf-builder-order-meta-box .action-buttons button:active {
            transform: translateY(0);
        }
        #pdf-builder-order-meta-box .action-buttons button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }
        #pdf-builder-order-meta-box .btn-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        #pdf-builder-order-meta-box .btn-preview:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        #pdf-builder-order-meta-box .btn-generate {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        #pdf-builder-order-meta-box .btn-generate:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa085 100%);
        }
        #pdf-builder-order-meta-box .btn-download {
            background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            color: #212529;
        }
        #pdf-builder-order-meta-box .btn-download:hover {
            background: linear-gradient(135deg, #e0a800 0%, #e8590c 100%);
        }
        #pdf-builder-order-meta-box .status-message {
            margin-top: 12px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            text-align: center;
            font-weight: 500;
        }
        #pdf-builder-order-meta-box .status-loading {
            background: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }
        #pdf-builder-order-meta-box .status-success {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        #pdf-builder-order-meta-box .status-error {
            background: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        #pdf-builder-order-meta-box .spinner {
            animation: spin 1s linear infinite;
        }
        #pdf-builder-order-meta-box .quick-stats {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 10px;
            margin-bottom: 15px;
        }
        #pdf-builder-order-meta-box .quick-stats .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
            font-size: 12px;
        }
        #pdf-builder-order-meta-box .quick-stats .stat-item:last-child {
            margin-bottom: 0;
        }
        #pdf-builder-order-meta-box .quick-stats .stat-label {
            color: #6c757d;
            font-weight: 500;
        }
        #pdf-builder-order-meta-box .quick-stats .stat-value {
            color: #495057;
            font-weight: 600;
        }
        </style>

        <div id="pdf-builder-order-meta-box" style="margin: -6px -12px -12px -12px;">
            <!-- Informations de la commande -->
            <div class="order-info">
                <h4><?php printf(__('Commande #%s', 'pdf-builder-pro'), $order->get_order_number()); ?></h4>
                <p><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?> • <?php echo esc_html($order->get_date_created()->format('d/m/Y H:i')); ?></p>
            </div>

            <div style="padding: 12px;">
                <!-- Statut du document détecté -->
                <div class="document-type-indicator" style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <span style="font-size: 16px;">📄</span>
                        <strong style="color: #495057;"><?php _e('Type de document détecté:', 'pdf-builder-pro'); ?></strong>
                    </div>
                    <div style="font-size: 14px; color: #007cba; font-weight: 600;">
                        <?php echo esc_html($document_type_label); ?>
                        <small style="color: #6c757d; font-weight: normal;">
                            (<?php printf(__('Statut: %s', 'pdf-builder-pro'), esc_html(wc_get_order_status_name($order->get_status()))); ?>)
                        </small>
                    </div>
                </div>

                <!-- Template sélectionné automatiquement -->
                <div class="template-info" style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #23282d; font-size: 13px;">
                        🎨 <?php _e('Template sélectionné:', 'pdf-builder-pro'); ?>
                    </label>
                    <div style="padding: 10px; background: #e8f5e8; border: 1px solid #c3e6c3; border-radius: 6px; font-size: 14px; color: #155724;">
                        <?php if ($default_template): ?>
                            <strong><?php echo esc_html($default_template['name']); ?></strong>
                            <small style="color: #6c757d; display: block; margin-top: 4px;">
                                <?php _e('Template par défaut pour ce type de document', 'pdf-builder-pro'); ?>
                            </small>
                        <?php else: ?>
                            <em><?php _e('Aucun template par défaut trouvé', 'pdf-builder-pro'); ?></em>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="action-buttons">
                    <button type="button"
                            id="pdf-builder-preview-btn"
                            class="btn-preview"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                        👁️ <?php _e('Aperçu PDF', 'pdf-builder-pro'); ?>
                    </button>

                    <button type="button"
                            id="pdf-builder-generate-btn"
                            class="btn-generate"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                        ⚡ <?php _e('Générer PDF', 'pdf-builder-pro'); ?>
                    </button>

                    <button type="button"
                            id="pdf-builder-download-btn"
                            class="btn-download"
                            style="display: none;"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>">
                        ⬇️ <?php _e('Télécharger PDF', 'pdf-builder-pro'); ?>
                    </button>
                </div>

                <div id="pdf-builder-status" class="status-message" style="display: none;"></div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            var $previewBtn = $('#pdf-builder-preview-btn');
            var $generateBtn = $('#pdf-builder-generate-btn');
            var $downloadBtn = $('#pdf-builder-download-btn');
            var $status = $('#pdf-builder-status');

            // Fonction pour afficher le statut
            function showStatus(message, type) {
                type = type || 'loading';
                var classes = {
                    'loading': 'status-loading',
                    'success': 'status-success',
                    'error': 'status-error'
                };

                $status.removeClass('status-loading status-success status-error')
                       .addClass(classes[type])
                       .html(message)
                       .show();
            }

            // Fonction pour masquer le statut
            function hideStatus() {
                $status.hide();
            }

            // Fonction pour définir l'état de chargement d'un bouton
            function setButtonLoading($btn, loading) {
                if (loading) {
                    $btn.prop('disabled', true);
                    var originalText = $btn.html();
                    $btn.data('original-text', originalText);
                    $btn.html('<span class="spinner" style="display: inline-block; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-radius: 50%; border-top-color: white; animation: spin 1s ease-in-out infinite; margin-right: 8px;"></span><?php echo esc_js(__("Chargement...", "pdf-builder-pro")); ?>');
                } else {
                    $btn.prop('disabled', false);
                    var originalText = $btn.data('original-text');
                    if (originalText) {
                        $btn.html(originalText);
                    }
                }
            }

            // Fonction pour ouvrir la modale d'aperçu PDF
            function openPdfPreviewModal(htmlContent, width, height) {
                // Créer la modale si elle n'existe pas
                if (!$('#pdf-preview-modal').length) {
                    $('body').append(`
                        <div id="pdf-preview-modal" style="
                            position: fixed;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(0, 0, 0, 0.8);
                            z-index: 99999;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            opacity: 0;
                            transition: opacity 0.3s ease;
                        ">
                            <div id="pdf-preview-content" style="
                                background: white;
                                border-radius: 8px;
                                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                                max-width: 90vw;
                                max-height: 90vh;
                                overflow: auto;
                                position: relative;
                                transform: scale(0.9);
                                transition: transform 0.3s ease;
                            ">
                                <div id="pdf-preview-header" style="
                                    padding: 15px 20px;
                                    border-bottom: 1px solid #e1e1e1;
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    background: #f8f9fa;
                                    border-radius: 8px 8px 0 0;
                                ">
                                    <h3 style="margin: 0; color: #333; font-size: 18px;"><?php echo esc_js(__('Aperçu PDF', 'pdf-builder-pro')); ?></h3>
                                    <button id="pdf-preview-close" style="
                                        background: none;
                                        border: none;
                                        font-size: 24px;
                                        cursor: pointer;
                                        color: #666;
                                        padding: 0;
                                        width: 30px;
                                        height: 30px;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        border-radius: 50%;
                                        transition: all 0.2s ease;
                                    ">&times;</button>
                                </div>
                                <div id="pdf-preview-body" style="
                                    padding: 20px;
                                    background: white;
                                    border-radius: 0 0 8px 8px;
                                ">
                                    <div id="pdf-preview-iframe-container" style="
                                        border: 1px solid #ddd;
                                        border-radius: 4px;
                                        background: #f8f9fa;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        min-height: 400px;
                                    ">
                                        <div id="pdf-preview-loading" style="color: #666; font-size: 16px;">
                                            <?php echo esc_js(__('Chargement de l\'aperçu...', 'pdf-builder-pro')); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `);

                    // Gestionnaire pour fermer la modale
                    $(document).on('click', '#pdf-preview-close, #pdf-preview-modal', function(e) {
                        if (e.target.id === 'pdf-preview-modal' || e.target.id === 'pdf-preview-close') {
                            closePdfPreviewModal();
                        }
                    });

                    // Fermer avec la touche Échap
                    $(document).on('keydown', function(e) {
                        if (e.keyCode === 27) { // Échap
                            closePdfPreviewModal();
                        }
                    });
                }

                // Fonction pour fermer la modale
                function closePdfPreviewModal() {
                    $('#pdf-preview-modal').fadeOut(300, function() {
                        $(this).remove();
                    });
                }

                // Calculer les dimensions de l'aperçu
                var modalWidth = Math.min(width + 40, window.innerWidth * 0.9);
                var modalHeight = Math.min(height + 120, window.innerHeight * 0.9);

                // Mettre à jour le contenu
                $('#pdf-preview-content').css({
                    'width': modalWidth + 'px',
                    'max-width': modalWidth + 'px'
                });

                $('#pdf-preview-iframe-container').css({
                    'min-height': (height + 40) + 'px'
                });

                // Créer un iframe avec le contenu HTML
                var iframe = document.createElement('iframe');
                iframe.style.width = '100%';
                iframe.style.height = height + 'px';
                iframe.style.border = 'none';
                iframe.style.background = 'white';

                // Utiliser une approche moderne pour écrire dans l'iframe
                iframe.onload = function() {
                    $('#pdf-preview-loading').hide();
                    try {
                        var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                        if (iframeDoc) {
                            // Utiliser innerHTML au lieu de document.write pour éviter les problèmes
                            iframeDoc.body.innerHTML = htmlContent;
                        } else {
                            console.error('PDF Builder: Cannot access iframe document');
                            $('#pdf-preview-iframe-container').html('<div style="color: #d63638; padding: 20px; text-align: center;">Erreur d\'accès à l\'aperçu</div>');
                        }
                    } catch (e) {
                        console.error('PDF Builder: Error writing to iframe:', e);
                        $('#pdf-preview-iframe-container').html('<div style="color: #d63638; padding: 20px; text-align: center;">Erreur lors du chargement de l\'aperçu</div>');
                    }
                };

                // Ajouter l'iframe au conteneur
                $('#pdf-preview-iframe-container').html(iframe);

                // Essayer d'écrire immédiatement (pour les navigateurs qui le supportent)
                try {
                    var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
                    if (iframeDoc && iframeDoc.readyState === 'complete') {
                        iframeDoc.body.innerHTML = htmlContent;
                        $('#pdf-preview-loading').hide();
                    }
                } catch (e) {
                    // L'onload handler s'occupera de ça
                    console.log('PDF Builder: Will write to iframe on load event');
                }

                // Afficher la modale avec animation
                $('#pdf-preview-modal').show().animate({opacity: 1}, 300);
                $('#pdf-preview-content').animate({scale: 1}, 300);
            }

            // Aperçu PDF
            $previewBtn.on('click', function() {
                var orderId = $(this).data('order-id');
                var templateId = <?php echo $default_template ? esc_js($default_template['id']) : '0'; ?>;

                console.log('PDF Builder: Preview button clicked');
                console.log('PDF Builder: Order ID:', orderId);
                console.log('PDF Builder: Template ID:', templateId);

                showStatus('<?php echo esc_js(__('Génération de l\'aperçu...', 'pdf-builder-pro')); ?>', 'loading');
                setButtonLoading($previewBtn, true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_pro_preview_order_pdf',
                        order_id: orderId,
                        template_id: templateId,
                        nonce: '<?php echo wp_create_nonce('pdf_builder_order_actions'); ?>'
                    },
                    success: function(response) {
                        console.log('PDF Builder: Preview AJAX success');
                        console.log('PDF Builder: Response:', response);
                        if (response.success) {
                            // Ouvrir l'aperçu dans une modale
                            openPdfPreviewModal(response.data.html, response.data.width, response.data.height);
                            showStatus('<?php echo esc_js(__('Aperçu ouvert avec succès ✅', 'pdf-builder-pro')); ?>', 'success');
                            setTimeout(hideStatus, 3000);
                        } else {
                            console.error('PDF Builder: Preview failed:', response.data);
                            showStatus(response.data || '<?php echo esc_js(__('Erreur lors de l\'aperçu ❌', 'pdf-builder-pro')); ?>', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF Builder: Preview AJAX error');
                        console.error('PDF Builder: Status:', status);
                        console.error('PDF Builder: Error:', error);
                        console.error('PDF Builder: Response:', xhr.responseText);
                        showStatus('<?php echo esc_js(__('Erreur AJAX lors de l\'aperçu ❌', 'pdf-builder-pro')); ?>', 'error');
                    },
                    complete: function() {
                        console.log('PDF Builder: Preview AJAX complete');
                        setButtonLoading($previewBtn, false);
                    }
                });
            });

            // Générer PDF
            $generateBtn.on('click', function() {
                var orderId = $(this).data('order-id');
                var templateId = <?php echo $default_template ? esc_js($default_template['id']) : '0'; ?>;

                console.log('PDF Builder: Generate button clicked');
                console.log('PDF Builder: Order ID:', orderId);
                console.log('PDF Builder: Template ID:', templateId);

                showStatus('<?php echo esc_js(__('Génération du PDF en cours...', 'pdf-builder-pro')); ?>', 'loading');
                setButtonLoading($generateBtn, true);

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_generate_order_pdf',
                        order_id: orderId,
                        template_id: templateId,
                        nonce: '<?php echo wp_create_nonce('pdf_builder_order_actions'); ?>'
                    },
                    success: function(response) {
                        console.log('PDF Builder: Generate AJAX success');
                        console.log('PDF Builder: Response:', response);
                        if (response.success) {
                            // Afficher le bouton de téléchargement
                            $downloadBtn.attr('href', response.data.url).show();
                            showStatus('<?php echo esc_js(__('PDF généré avec succès ✅', 'pdf-builder-pro')); ?>', 'success');

                            // Ouvrir automatiquement le PDF dans un nouvel onglet
                            window.open(response.data.url, '_blank');
                        } else {
                            console.error('PDF Builder: Generate failed:', response.data);
                            showStatus(response.data || '<?php echo esc_js(__('Erreur lors de la génération ❌', 'pdf-builder-pro')); ?>', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF Builder: Generate AJAX error');
                        console.error('PDF Builder: Status:', status);
                        console.error('PDF Builder: Error:', error);
                        console.error('PDF Builder: Response:', xhr.responseText);
                        showStatus('<?php echo esc_js(__('Erreur AJAX lors de la génération ❌', 'pdf-builder-pro')); ?>', 'error');
                    },
                    complete: function() {
                        console.log('PDF Builder: Generate AJAX complete');
                        setButtonLoading($generateBtn, false);
                    }
                });
            });

            // Télécharger PDF
            $downloadBtn.on('click', function() {
                var pdfUrl = $(this).attr('href');
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
        // Logs de débogage détaillés
        error_log('🟡 PDF BUILDER - ajax_generate_order_pdf called');
        error_log('🟡 REQUEST METHOD: ' . $_SERVER['REQUEST_METHOD']);
        error_log('🟡 POST data: ' . print_r($_POST, true));

        // Désactiver l'affichage des erreurs PHP pour éviter les réponses HTML
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        $this->check_admin_permissions();
        error_log('✅ PDF BUILDER - Permissions checked');

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            error_log('❌ PDF BUILDER - Nonce verification failed');
            error_log('❌ Expected nonce action: pdf_builder_order_actions');
            error_log('❌ Received nonce: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'NOT SET'));
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        error_log('✅ PDF BUILDER - Nonce verified');

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;

        error_log('🟡 PDF BUILDER - Order ID: ' . $order_id . ', Template ID: ' . $template_id);

        if (!$order_id) {
            error_log('❌ PDF BUILDER - Order ID missing');
            wp_send_json_error('ID commande manquant');
        }

        // Vérifier que WooCommerce est actif
        if (!class_exists('WooCommerce')) {
            error_log('❌ PDF BUILDER - WooCommerce not active');
            wp_send_json_error('WooCommerce n\'est pas installé ou activé');
        }

        // Vérifier que les fonctions WooCommerce nécessaires existent
        if (!function_exists('wc_get_order')) {
            error_log('❌ PDF BUILDER - wc_get_order function not available');
            wp_send_json_error('Fonction wc_get_order non disponible - WooCommerce mal installé');
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('❌ PDF BUILDER - Order not found: ' . $order_id);
            wp_send_json_error('Commande non trouvée');
        }

        error_log('✅ PDF BUILDER - Order found: ' . $order->get_order_number());

        // Vérifier que l'objet order a les méthodes nécessaires
        if (!method_exists($order, 'get_id') || !method_exists($order, 'get_total')) {
            error_log('❌ PDF BUILDER - Order object invalid');
            wp_send_json_error('Objet commande WooCommerce invalide');
        }

        try {
            error_log('🟡 PDF BUILDER - Loading template...');

            // Charger le template de manière robuste
            if ($template_id > 0) {
                $template_data = $this->load_template_robust($template_id);
                error_log('✅ PDF BUILDER - Template loaded from database: ' . $template_id);
            } else {
                $template_data = $this->get_default_invoice_template();
                error_log('✅ PDF BUILDER - Default template loaded');
            }

            error_log('🟡 PDF BUILDER - Generating PDF...');
            // Générer le PDF avec les données de la commande
            $pdf_filename = 'order-' . $order_id . '-' . time() . '.pdf';
            $pdf_path = $this->generate_order_pdf($order, $template_data, $pdf_filename);

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);

                error_log('✅ PDF BUILDER - PDF generated successfully: ' . $pdf_path);
                wp_send_json_success(array(
                    'message' => 'PDF généré avec succès',
                    'url' => $pdf_url,
                    'filename' => $pdf_filename
                ));
            } else {
                error_log('❌ PDF BUILDER - PDF generation failed');
                wp_send_json_error('Erreur lors de la génération du PDF - fichier non créé');
            }

        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - Exception in ajax_generate_order_pdf: ' . $e->getMessage());
            error_log('❌ Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('❌ PDF BUILDER - Fatal error in ajax_generate_order_pdf: ' . $e->getMessage());
            error_log('❌ Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur fatale: ' . $e->getMessage());
        }
    }

    /**
     * AJAX - Aperçu PDF pour une commande WooCommerce
     */
    public function ajax_preview_order_pdf() {
        // Log immédiat pour vérifier si la fonction est appelée
        error_log('🚨 PDF BUILDER - ajax_preview_order_pdf FUNCTION STARTED');

        // Logs de débogage détaillés
        error_log('🟡 PDF BUILDER - ajax_preview_order_pdf called');
        error_log('🟡 REQUEST METHOD: ' . $_SERVER['REQUEST_METHOD']);
        error_log('🟡 POST data: ' . print_r($_POST, true));

        // Désactiver l'affichage des erreurs PHP pour éviter les réponses HTML
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        $this->check_admin_permissions();
        error_log('✅ PDF BUILDER - Permissions checked');

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'], 'pdf_builder_order_actions')) {
            error_log('❌ PDF BUILDER - Nonce verification failed');
            error_log('❌ Expected nonce action: pdf_builder_order_actions');
            error_log('❌ Received nonce: ' . (isset($_POST['nonce']) ? $_POST['nonce'] : 'NOT SET'));
            wp_send_json_error('Sécurité: Nonce invalide');
        }

        error_log('✅ PDF BUILDER - Nonce verified');

        $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
        $template_id = isset($_POST['template_id']) ? intval($_POST['template_id']) : 0;
        $document_type = isset($_POST['document_type']) ? sanitize_text_field($_POST['document_type']) : 'invoice';

        error_log('🟡 PDF BUILDER - Order ID: ' . $order_id . ', Template ID: ' . $template_id . ', Document type: ' . $document_type);

        if (!$order_id) {
            error_log('❌ PDF BUILDER - Order ID missing');
            wp_send_json_error('ID commande manquant');
        }

        // Vérifier que WooCommerce est actif
        if (!class_exists('WooCommerce')) {
            error_log('❌ PDF BUILDER - WooCommerce not active');
            wp_send_json_error('WooCommerce n\'est pas installé ou activé');
        }

        // Vérifier que les fonctions WooCommerce nécessaires existent
        if (!function_exists('wc_get_order')) {
            error_log('❌ PDF BUILDER - wc_get_order function not available');
            wp_send_json_error('Fonction wc_get_order non disponible - WooCommerce mal installé');
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('❌ PDF BUILDER - Order not found: ' . $order_id);
            wp_send_json_error('Commande non trouvée');
        }

        error_log('✅ PDF BUILDER - Order found: ' . $order->get_order_number());

        // Vérifier que l'objet order a les méthodes nécessaires
        if (!method_exists($order, 'get_id') || !method_exists($order, 'get_total')) {
            error_log('❌ PDF BUILDER - Order object invalid');
            wp_send_json_error('Objet commande WooCommerce invalide');
        }

        try {
            error_log('🟡 PDF BUILDER - Loading template...');

            // Charger le template de manière robuste
            if ($template_id > 0) {
                $template_data = $this->load_template_robust($template_id);
                error_log('✅ PDF BUILDER - Template loaded from database: ' . $template_id);
            } else {
                $template_data = $this->get_default_invoice_template();
                error_log('✅ PDF BUILDER - Default template loaded');
            }

            error_log('🟡 PDF BUILDER - Generating HTML preview...');
            // Générer l'HTML d'aperçu avec les données de la commande
            $html_content = $this->generate_order_html($order, $template_data);
            error_log('✅ PDF BUILDER - HTML generated, length: ' . strlen($html_content));

            $response = array(
                'html' => $html_content,
                'width' => $template_data['canvas']['width'] ?? 595,
                'height' => $template_data['canvas']['height'] ?? 842
            );

            error_log('✅ PDF BUILDER - Sending success response');
            wp_send_json_success($response);

        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - Exception in ajax_preview_order_pdf: ' . $e->getMessage());
            error_log('❌ Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('❌ PDF BUILDER - Fatal error in ajax_preview_order_pdf: ' . $e->getMessage());
            error_log('❌ Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error('Erreur fatale: ' . $e->getMessage());
        }
    }

    /**
     * Détecte automatiquement le type de document basé sur le statut de la commande
     */
    private function detect_document_type($order_status) {
        $status_mapping = [
            'processing' => 'invoice',
            'completed' => 'invoice',
            'pending' => 'quote',
            'on-hold' => 'quote',
            'cancelled' => 'credit_note',
            'refunded' => 'credit_note',
            'failed' => 'credit_note'
        ];

        return $status_mapping[$order_status] ?? 'invoice';
    }

    /**
     * Retourne le libellé du type de document
     */
    private function get_document_type_label($document_type) {
        $labels = [
            'invoice' => __('Facture', 'pdf-builder-pro'),
            'quote' => __('Devis', 'pdf-builder-pro'),
            'receipt' => __('Reçu', 'pdf-builder-pro'),
            'credit_note' => __('Avoir', 'pdf-builder-pro'),
            'order' => __('Commande', 'pdf-builder-pro')
        ];

        return $labels[$document_type] ?? __('Document', 'pdf-builder-pro');
    }
    private function generate_order_pdf($order, $template_data, $filename) {
        // Créer le répertoire de stockage s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder/orders';
        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
        }

        $pdf_path = $pdf_dir . '/' . $filename;

        // Générer le HTML d'abord
        $html_content = $this->generate_unified_html($template_data, $order);

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
        return $this->generate_unified_html($template_data, $order);
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

        // Ajouter les frais de commande personnalisés
        foreach ($order->get_fees() as $fee) {
            $fee_name = $fee->get_name();
            $fee_total = $fee->get_total();

            if ($fee_total != 0) {
                $html .= '<tr>';
                $html .= '<td style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">' . esc_html($fee_name) . '</td>';
                $html .= '<td style="border: 1px solid #ddd; padding: 5px;">-</td>';
                $html .= '<td style="border: 1px solid #ddd; padding: 5px;">-</td>';
                $html .= '<td style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">' . wc_price($fee_total) . '</td>';
                $html .= '</tr>';
            }
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
                    'type' => 'invoice_number',
                    'position' => array('x' => 400, 'y' => 90),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'N° de facture'
                ),
                array(
                    'id' => 'invoice_date',
                    'type' => 'invoice_date',
                    'position' => array('x' => 400, 'y' => 120),
                    'size' => array('width' => 150, 'height' => 25),
                    'style' => array('fontSize' => 14, 'color' => '#000000'),
                    'content' => 'Date'
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
                    'id' => 'products_table',
                    'type' => 'product_table',
                    'position' => array('x' => 50, 'y' => 250),
                    'size' => array('width' => 500, 'height' => 200),
                    'style' => array('fontSize' => 12, 'color' => '#000000'),
                    'content' => 'Tableau produits'
                ),
                array(
                    'id' => 'total',
                    'type' => 'total',
                    'position' => array('x' => 400, 'y' => 500),
                    'size' => array('width' => 150, 'height' => 30),
                    'style' => array('fontSize' => 16, 'fontWeight' => 'bold', 'color' => '#000000'),
                    'content' => 'Total'
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

    }

    /**
     * Nettoie et valide une valeur de paramètre selon son type
     */
    private function sanitize_setting_value($value) {
        if (is_array($value)) {
            return array_map([$this, 'sanitize_setting_value'], $value);
        } elseif (is_bool($value)) {
            return (bool) $value;
        } elseif (is_numeric($value)) {
            return is_float($value + 0) ? (float) $value : (int) $value;
        } else {
            return sanitize_text_field($value);
        }
    }

    /**
     * Gère la sauvegarde AJAX des paramètres
     */
    public function ajax_save_settings() {
        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_admin_nonce')) {
            wp_send_json_error('Nonce invalide');
            return;
        }

        // Vérification des permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Récupération et validation des paramètres
        $settings = $_POST['settings'] ?? [];
        if (empty($settings) || !is_array($settings)) {
            wp_send_json_error('Paramètres invalides');
            return;
        }

        // Nettoyage des valeurs
        $sanitized_settings = [];
        foreach ($settings as $key => $value) {
            $sanitized_settings[$key] = $this->sanitize_setting_value($value);
        }

        // Sauvegarde des paramètres
        update_option('pdf_builder_settings', $sanitized_settings);

        wp_send_json_success('Paramètres sauvegardés avec succès');
    }

    /**
     * Sauvegarder les paramètres de rendu Canvas
     */
    private function save_canvas_render_settings() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes.', 'pdf-builder-pro'));
        }

        // Récupérer les paramètres actuels
        $canvas_settings = get_option('pdf_builder_canvas_settings', []);

        // Mettre à jour les paramètres des poignées
        $canvas_settings['canvas_handle_size'] = intval($_POST['canvas_handle_size'] ?? 12);
        $canvas_settings['canvas_handle_color'] = sanitize_hex_color($_POST['canvas_handle_color'] ?? '#007cba');
        $canvas_settings['canvas_handle_hover_color'] = sanitize_hex_color($_POST['canvas_handle_hover_color'] ?? '#ffffff');

        // Mettre à jour les paramètres des bordures
        $canvas_settings['canvas_border_width'] = intval($_POST['canvas_border_width'] ?? 2);
        $canvas_settings['canvas_border_color'] = sanitize_hex_color($_POST['canvas_border_color'] ?? '#007cba');
        $canvas_settings['canvas_border_spacing'] = intval($_POST['canvas_border_spacing'] ?? 2);

        // Mettre à jour les paramètres de visibilité
        $canvas_settings['canvas_resize_handles_enabled'] = isset($_POST['canvas_resize_handles_enabled']);
        $canvas_settings['canvas_element_borders_enabled'] = isset($_POST['canvas_element_borders_enabled']);

        // Mettre à jour les paramètres par défaut des éléments
        $canvas_settings['default_text_color'] = sanitize_hex_color($_POST['default_text_color'] ?? '#000000');
        $canvas_settings['default_background_color'] = sanitize_hex_color($_POST['default_background_color'] ?? '#ffffff');
        $canvas_settings['default_font_size'] = intval($_POST['default_font_size'] ?? 14);

        // Sauvegarder les paramètres
        update_option('pdf_builder_canvas_settings', $canvas_settings);

        // Ajouter un message de succès
        add_settings_error(
            'pdf_builder_canvas_render',
            'settings_updated',
            __('Paramètres de rendu Canvas sauvegardés avec succès.', 'pdf-builder-pro'),
            'updated'
        );
    }

    /**
     * AJAX - Sauvegarder les paramètres de la page des paramètres
     */
    public function ajax_save_settings_page() {
        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_settings')) {
            wp_send_json_error(['message' => 'Nonce invalide']);
            return;
        }

        // Vérification des permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Permissions insuffisantes']);
            return;
        }

        // Récupération des paramètres depuis le formulaire
        $settings = [];

        // Paramètres de debug et performance
        $settings['debug_mode'] = isset($_POST['debug_mode']);
        $settings['cache_enabled'] = isset($_POST['cache_enabled']);
        $settings['cache_ttl'] = intval($_POST['cache_ttl'] ?? 3600);
        $settings['max_execution_time'] = intval($_POST['max_execution_time'] ?? 300);
        $settings['memory_limit'] = sanitize_text_field($_POST['memory_limit'] ?? '256M');
        $settings['pdf_quality'] = sanitize_text_field($_POST['pdf_quality'] ?? 'high');

        // Paramètres de format
        $settings['default_format'] = sanitize_text_field($_POST['default_format'] ?? 'A4');
        $settings['default_orientation'] = sanitize_text_field($_POST['default_orientation'] ?? 'portrait');

        // Paramètres des bordures du canvas
        $settings['canvas_element_borders_enabled'] = isset($_POST['canvas_element_borders_enabled']);
        $settings['canvas_border_width'] = intval($_POST['canvas_border_width'] ?? 1);
        $settings['canvas_border_color'] = sanitize_hex_color($_POST['canvas_border_color'] ?? '#007cba');
        $settings['canvas_border_spacing'] = intval($_POST['canvas_border_spacing'] ?? 2);
        $settings['canvas_resize_handles_enabled'] = isset($_POST['canvas_resize_handles_enabled']);
        $settings['canvas_handle_size'] = intval($_POST['canvas_handle_size'] ?? 8);
        $settings['canvas_handle_color'] = sanitize_hex_color($_POST['canvas_handle_color'] ?? '#007cba');
        $settings['canvas_handle_hover_color'] = sanitize_hex_color($_POST['canvas_handle_hover_color'] ?? '#005a87');

        // Paramètres de notifications
        $settings['email_notifications_enabled'] = isset($_POST['email_notifications_enabled']);
        $settings['notification_events'] = isset($_POST['notification_events']) ? (array) $_POST['notification_events'] : [];

        // Sauvegarde des paramètres
        update_option('pdf_builder_settings', $settings);

        wp_send_json_success(['message' => 'Paramètres sauvegardés avec succès !']);
    }

    /**
     * Nettoie les données JSON pour corriger les erreurs d'encodage et de syntaxe
     */
    private function clean_json_data($json_string) {
        if (!is_string($json_string)) {
            return $json_string;
        }

        $original = $json_string;

        // Supprimer les caractères de contrôle invisibles (sauf tabulation, retour chariot, nouvelle ligne)
        $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $json_string);

        // Corriger les problèmes d'encodage UTF-8
        if (!mb_check_encoding($cleaned, 'UTF-8')) {
            $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'auto');
        }

        // Supprimer les BOM UTF-8 si présent
        $cleaned = preg_replace('/^\x{EF}\x{BB}\x{BF}/', '', $cleaned);

        // Nettoyer les espaces de noms problématiques
        $cleaned = str_replace('\\u0000', '', $cleaned);

        // Supprimer les caractères null
        $cleaned = str_replace("\0", '', $cleaned);

        // Corriger les virgules de fin dans les objets/tableaux
        $cleaned = preg_replace('/,(\s*[}\]])/m', '$1', $cleaned);

        // Supprimer les virgules multiples
        $cleaned = preg_replace('/,{2,}/', ',', $cleaned);

        // Corriger les clés non quotées (pattern simple)
        $cleaned = preg_replace('/([{,]\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $cleaned);

        // Supprimer les commentaires de style JavaScript (// et /* */)
        $cleaned = preg_replace('/\/\/.*$/m', '', $cleaned);
        $cleaned = preg_replace('/\/\*.*?\*\//s', '', $cleaned);

        // Corriger les valeurs undefined/null malformées
        $cleaned = preg_replace('/:\s*undefined\b/', ':null', $cleaned);

        // Supprimer les espaces blancs excessifs
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);

        return $cleaned;
    }

    /**
     * Nettoyage JSON plus agressif pour récupérer les templates très corrompus
     */
    private function aggressive_json_clean($json_string) {
        if (!is_string($json_string)) {
            return $json_string;
        }

        // Appliquer d'abord le nettoyage normal
        $cleaned = $this->clean_json_data($json_string);

        // Essayer de trouver et corriger les structures JSON de base
        // Chercher les patterns courants et essayer de les réparer

        // 1. Corriger les objets malformés avec des virgules finales
        $cleaned = preg_replace('/,(\s*})/', '$1', $cleaned);
        $cleaned = preg_replace('/,(\s*\])/m', '$1', $cleaned);

        // 2. Ajouter des guillemets manquants autour des clés
        $cleaned = preg_replace('/([{,]\s*)([a-zA-Z_][a-zA-Z0-9_]*)\s*:/', '$1"$2":', $cleaned);

        // 3. Corriger les valeurs de chaîne non quotées (simple)
        $cleaned = preg_replace('/:(\s*)([a-zA-Z_][a-zA-Z0-9_]*[a-zA-Z0-9])\s*([,}\]])/', ':"$2"$3', $cleaned);

        // 4. Supprimer les caractères de contrôle restants qui pourraient poser problème
        $cleaned = preg_replace('/[\x00-\x1F\x7F]/', '', $cleaned);

        return $cleaned;
    }

    /**
     * Marque un template comme corrompu pour réparation future
     */
    private function mark_template_corrupted($template_id) {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Ajouter un flag de corruption (on peut utiliser un champ meta ou modifier le nom)
        $current_name = $wpdb->get_var($wpdb->prepare("SELECT name FROM $table_templates WHERE id = %d", $template_id));
        if ($current_name && strpos($current_name, '[CORROMPU]') !== 0) {
            $wpdb->update(
                $table_templates,
                ['name' => '[CORROMPU] ' . $current_name],
                ['id' => $template_id]
            );
        }

        error_log('PDF Builder: Template ID ' . $template_id . ' marked as corrupted');
    }

    /**
     * Charge un template de manière robuste avec récupération automatique
     */
    private function load_template_robust($template_id) {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_templates WHERE id = %d", $template_id),
            ARRAY_A
        );

        if (!$template) {
            return $this->get_default_invoice_template();
        }

        // Essayer de décoder le JSON
        $template_data = json_decode($template['template_data'], true);

        if (json_last_error() === JSON_ERROR_NONE) {
            return $template_data;
        }

        // Essayer le nettoyage normal
        $clean_json = $this->clean_json_data($template['template_data']);
        if ($clean_json !== $template['template_data']) {
            $template_data = json_decode($clean_json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                error_log('PDF Builder: JSON cleaned successfully for template ID ' . $template_id);
                return $template_data;
            }
        }

        // Essayer le nettoyage agressif
        $aggressive_clean = $this->aggressive_json_clean($template['template_data']);
        if ($aggressive_clean !== $template['template_data']) {
            $template_data = json_decode($aggressive_clean, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                error_log('PDF Builder: JSON recovered with aggressive cleaning for template ID ' . $template_id);
                return $template_data;
            }
        }

        // Marquer comme corrompu et utiliser un template par défaut
        $this->mark_template_corrupted($template_id);
        error_log('PDF Builder: Template ID ' . $template_id . ' is corrupted, using default template');
        return $this->get_default_invoice_template();
    }

    /**
     * AJAX - Sauvegarder les paramètres d'un template
     */
    public function ajax_save_template_settings() {
        error_log('PDF Builder: ajax_save_template_settings called');

        // Vérification de sécurité
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_templates')) {
            wp_send_json_error(['message' => __('Nonce invalide', 'pdf-builder-pro')]);
            return;
        }

        $this->check_admin_permissions();

        $template_id = intval($_POST['template_id'] ?? 0);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'autre');
        $paper_size = sanitize_text_field($_POST['paper_size'] ?? 'A4');
        $orientation = sanitize_text_field($_POST['orientation'] ?? 'portrait');
        $is_public = intval($_POST['is_public'] ?? 0);

        if (!$template_id || empty($name)) {
            wp_send_json_error(['message' => __('ID du template et nom requis', 'pdf-builder-pro')]);
            return;
        }

        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Vérifier que le template existe
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_templates WHERE id = %d", $template_id));
        if (!$existing) {
            wp_send_json_error(['message' => __('Template introuvable', 'pdf-builder-pro')]);
            return;
        }

        // Préparer les données de mise à jour
        $update_data = [
            'name' => $name,
            'updated_at' => current_time('mysql')
        ];

        // Pour l'instant, on ne sauvegarde que le nom (les autres champs peuvent être ajoutés plus tard si nécessaire)
        $result = $wpdb->update(
            $table_templates,
            $update_data,
            ['id' => $template_id],
            ['%s', '%s'],
            ['%d']
        );

        if ($result === false) {
            error_log('PDF Builder: Erreur lors de la mise à jour du template: ' . $wpdb->last_error);
            wp_send_json_error(['message' => __('Erreur lors de la sauvegarde', 'pdf-builder-pro')]);
            return;
        }

        wp_send_json_success([
            'message' => __('Paramètres sauvegardés avec succès', 'pdf-builder-pro'),
            'template_id' => $template_id,
            'name' => $name
        ]);
    }
}




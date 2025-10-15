<?php
/**
 * PDF Builder Pro - Interface d'administration simplifiée
 * Version 5.1.0 - Canvas uniquement
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

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
     * Gestionnaire de templates
     */
    private $template_manager;

    /**
     * Générateur de PDF
     */
    private $pdf_generator;

    /**
     * Intégration WooCommerce
     */
    private $woocommerce_integration;

    /**
     * Gestionnaire de paramètres
     */
    private $settings_manager;

    /**
     * Gestionnaire de diagnostics
     */
    private $diagnostic_manager;

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
        $this->main = $main_instance;

        // Inclure les classes des managers
        $this->include_managers();

        // Instancier les managers
        $this->init_managers();

        $this->init_hooks();
    }

    /**
     * Inclure les fichiers des managers
     */
    private function include_managers() {
        $managers_path = plugin_dir_path(__FILE__) . 'managers/';

        require_once $managers_path . 'class-pdf-builder-template-manager.php';
        require_once $managers_path . 'class-pdf-builder-pdf-generator.php';
        require_once $managers_path . 'class-pdf-builder-woocommerce-integration.php';
        require_once $managers_path . 'class-pdf-builder-settings-manager.php';
        require_once $managers_path . 'class-pdf-builder-diagnostic-manager.php';
    }

    /**
     * Instancier les managers
     */
    private function init_managers() {
        $this->template_manager = new PDF_Builder_Template_Manager($this->main);
        $this->pdf_generator = new PDF_Builder_PDF_Generator($this->main);
        $this->woocommerce_integration = new PDF_Builder_WooCommerce_Integration($this->main);
        $this->settings_manager = new PDF_Builder_Settings_Manager($this->main);
        $this->diagnostic_manager = new PDF_Builder_Diagnostic_Manager($this->main);
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

        // Vérifier si l'utilisateur a accès basé sur les rôles autorisés
        if (!$this->user_has_pdf_access()) {
            wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
        }
    }

    /**
     * Vérifie si l'utilisateur actuel a accès au PDF Builder basé sur les rôles autorisés
     */
    private function user_has_pdf_access() {
        // Les administrateurs ont toujours accès
        if (current_user_can('administrator')) {
            return true;
        }

        $user_id = get_current_user_id();

        // Vérifier le cache (valide pour 5 minutes)
        $cache_key = 'pdf_builder_user_access_' . $user_id;
        $cached_result = get_transient($cache_key);

        if ($cached_result !== false) {
            return $cached_result === 'allowed';
        }

        // Récupérer les rôles autorisés depuis les options
        $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator']);

        // S'assurer que c'est un tableau
        if (!is_array($allowed_roles)) {
            $allowed_roles = ['administrator'];
        }

        // Vérifier si l'utilisateur a un des rôles autorisés
        $user = wp_get_current_user();
        $user_roles = $user->roles;
        $has_access = false;

        foreach ($user_roles as $role) {
            if (in_array($role, $allowed_roles)) {
                $has_access = true;
                break;
            }
        }

        // Mettre en cache le résultat (5 minutes)
        set_transient($cache_key, $has_access ? 'allowed' : 'denied', 5 * MINUTE_IN_SECONDS);

        return $has_access;
    }

    /**
     * Initialise les hooks WordPress
     */
    /**
     * Initialise les hooks WordPress
     */
    private function init_hooks() {
        // Hooks de base de l'admin (restent dans cette classe)
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 20);

        // Hooks WooCommerce - Délégation vers le manager
        if (class_exists('WooCommerce')) {
            add_action('add_meta_boxes_shop_order', [$this->woocommerce_integration, 'add_woocommerce_order_meta_box']);
            add_action('add_meta_boxes_woocommerce_page_wc-orders', [$this->woocommerce_integration, 'add_woocommerce_order_meta_box']);
        }

        // Les managers s'occupent de leurs propres hooks AJAX
        // Les hooks AJAX sont maintenant gérés par les managers respectifs

        // Hook AJAX pour le debug PDF metabox
        add_action('wp_ajax_pdf_debug_metabox', [$this, 'ajax_debug_pdf_metabox']);

        // Hook AJAX pour sauvegarder les paramètres
        add_action('wp_ajax_pdf_builder_save_settings', [$this, 'ajax_save_settings']);
        add_action('wp_ajax_pdf_builder_save_settings_page', [$this, 'ajax_save_settings_page']);

        // Endpoint pour le debug direct (accessible via URL)
        add_action('init', [$this, 'add_debug_endpoint']);
        add_action('template_redirect', [$this, 'handle_debug_endpoint']);
        add_filter('query_vars', [$this, 'add_debug_query_vars']);
    }

    /**
     * Ajoute le menu d'administration
     */
    public function add_admin_menu() {
        // Menu principal avec icône distinctive
        add_menu_page(
            __('PDF Builder Pro - Gestionnaire de PDF', 'pdf-builder-pro'),
            __('PDF Builder', 'pdf-builder-pro'),
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

        // Page de test pour la sélection de templates
        add_submenu_page(
            'pdf-builder-pro',
            __('Test Templates - PDF Builder Pro', 'pdf-builder-pro'),
            __('🔧 Test Templates', 'pdf-builder-pro'),
            'manage_options',
            'pdf-builder-test-templates',
            [$this->main, 'test_template_selection_page']
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

                <!-- Fonctionnalités complètes -->
                <div class="dashboard-features">
                    <h3>✨ Fonctionnalités de PDF Builder Pro</h3>
                    <div class="features-grid">
                        <!-- Éditeur Canvas -->
                        <div class="feature-category">
                            <h4>🎨 Éditeur Canvas</h4>
                            <ul>
                                <li>Interface drag & drop intuitive</li>
                                <li>Éditeur visuel en temps réel</li>
                                <li>Grille d'aimantation magnétique</li>
                                <li>Zoom fluide (10% à 500%)</li>
                                <li>Navigation panoramique</li>
                                <li>Compteur FPS intégré</li>
                                <li>Aperçu en direct</li>
                            </ul>
                        </div>

                        <!-- Éléments de Design -->
                        <div class="feature-category">
                            <h4>📐 Éléments de Design</h4>
                            <ul>
                                <li>Textes avec formatage riche</li>
                                <li>Images et logos</li>
                                <li>Formes géométriques</li>
                                <li>Lignes et bordures</li>
                                <li>Code-barres et QR codes</li>
                                <li>Éléments WooCommerce</li>
                                <li>Variables dynamiques</li>
                            </ul>
                        </div>

                        <!-- Gestion des Templates -->
                        <div class="feature-category">
                            <h4>📋 Gestion des Templates</h4>
                            <ul>
                                <li>Création de modèles personnalisés</li>
                                <li>Import/Export de templates</li>
                                <li>Catégorisation avancée</li>
                                <li>Templates prédéfinis</li>
                                <li>Historique des versions</li>
                                <li>Partage d'équipe</li>
                                <li>Sauvegarde automatique</li>
                            </ul>
                        </div>

                        <!-- Intégration WooCommerce -->
                        <div class="feature-category">
                            <h4>🛒 WooCommerce</h4>
                            <ul>
                                <li>Factures automatiques</li>
                                <li>Bon de livraison</li>
                                <li>Étiquettes de produits</li>
                                <li>Intégration commandes</li>
                                <li>Variables dynamiques</li>
                                <li>Support HPOS</li>
                                <li>Gestion des statuts</li>
                            </ul>
                        </div>

                        <!-- Export et Qualité -->
                        <div class="feature-category">
                            <h4>📤 Export & Qualité</h4>
                            <ul>
                                <li>PDF haute qualité</li>
                                <li>Compression intelligente</li>
                                <li>Polices embarquées</li>
                                <li>Métadonnées PDF</li>
                                <li>Formats multiples</li>
                                <li>Optimisation web</li>
                                <li>Signature numérique</li>
                            </ul>
                        </div>

                        <!-- Paramètres Avancés -->
                        <div class="feature-category">
                            <h4>⚙️ Paramètres Avancés</h4>
                            <ul>
                                <li>Configuration canvas</li>
                                <li>Paramètres de performance</li>
                                <li>Gestion des rôles</li>
                                <li>Notifications email</li>
                                <li>Cache intelligent</li>
                                <li>Logs détaillés</li>
                                <li>Actions de maintenance</li>
                            </ul>
                        </div>

                        <!-- API et Intégrations -->
                        <div class="feature-category">
                            <h4>🔗 API & Intégrations</h4>
                            <ul>
                                <li>API REST complète</li>
                                <li>Webhooks personnalisés</li>
                                <li>Intégration Zapier</li>
                                <li>Support JSON</li>
                                <li>Import CSV/Excel</li>
                                <li>Connexions externes</li>
                                <li>Callbacks JavaScript</li>
                            </ul>
                        </div>

                        <!-- Sécurité et Performance -->
                        <div class="feature-category">
                            <h4>🔒 Sécurité & Performance</h4>
                            <ul>
                                <li>Validation des données</li>
                                <li>Protection CSRF</li>
                                <li>Sanitisation automatique</li>
                                <li>Cache optimisé</li>
                                <li>Compression GZIP</li>
                                <li>Monitoring des ressources</li>
                                <li>Logs de sécurité</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Nouvelles fonctionnalités -->
                    <div class="new-features">
                        <h4>🆕 Nouvelles fonctionnalités (v1.0.2)</h4>
                        <div class="new-features-list">
                            <div class="new-feature-item">
                                <span class="feature-badge">NOUVEAU</span>
                                <strong>Compteur FPS</strong> - Surveillez les performances de l'éditeur canvas
                            </div>
                            <div class="new-feature-item">
                                <span class="feature-badge">AMÉLIORÉ</span>
                                <strong>Aperçu côté serveur</strong> - Validation des éléments avant génération
                            </div>
                            <div class="new-feature-item">
                                <span class="feature-badge">CORRIGÉ</span>
                                <strong>Paramètres Canvas</strong> - Sauvegarde et chargement corrects de tous les paramètres
                            </div>
                            <div class="new-feature-item">
                                <span class="feature-badge">OPTIMISÉ</span>
                                <strong>Performance AJAX</strong> - Requêtes plus rapides et fiables
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

                /* Styles pour la section fonctionnalités */
                .dashboard-features {
                    background: #fff;
                    border: 1px solid #e1e1e1;
                    border-radius: 8px;
                    padding: 25px;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    margin-top: 30px;
                }

                .dashboard-features h3 {
                    margin-top: 0;
                    color: #1d2327;
                    border-bottom: 2px solid #2271b1;
                    padding-bottom: 10px;
                }

                .features-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 25px;
                    margin-top: 25px;
                }

                .feature-category {
                    background: #f8f9fa;
                    border: 1px solid #e9ecef;
                    border-radius: 6px;
                    padding: 20px;
                }

                .feature-category h4 {
                    margin: 0 0 15px 0;
                    color: #2271b1;
                    font-size: 16px;
                    border-bottom: 1px solid #dee2e6;
                    padding-bottom: 8px;
                }

                .feature-category ul {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }

                .feature-category li {
                    padding: 4px 0;
                    color: #495057;
                    font-size: 14px;
                    position: relative;
                    padding-left: 20px;
                }

                .feature-category li:before {
                    content: "✓";
                    color: #28a745;
                    font-weight: bold;
                    position: absolute;
                    left: 0;
                }

                /* Styles pour les nouvelles fonctionnalités */
                .new-features {
                    margin-top: 30px;
                    padding-top: 25px;
                    border-top: 1px solid #e9ecef;
                }

                .new-features h4 {
                    color: #1d2327;
                    margin-bottom: 15px;
                }

                .new-features-list {
                    display: grid;
                    gap: 10px;
                }

                .new-feature-item {
                    display: flex;
                    align-items: center;
                    padding: 10px 15px;
                    background: #fff3cd;
                    border: 1px solid #ffeaa7;
                    border-radius: 4px;
                    font-size: 14px;
                }

                .feature-badge {
                    background: #fd7e14;
                    color: white;
                    padding: 2px 8px;
                    border-radius: 12px;
                    font-size: 11px;
                    font-weight: bold;
                    margin-right: 10px;
                    flex-shrink: 0;
                }

                .new-feature-item strong {
                    color: #856404;
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

        // Charger le fichier de configuration si nécessaire
        if (!defined('PDF_BUILDER_VERSION')) {
            $config_file = plugin_dir_path(dirname(__FILE__)) . 'config.php';
            if (file_exists($config_file)) {
                require_once $config_file;
            }
        }

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

        // Test simple de TCPDF
        $test_results = $this->run_simple_tcpdf_test();

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

        ob_start();

        echo "<div class='test-section info'>";
        echo "<h3>🚀 Test simple TCPDF...</h3>";
        echo "<pre>";

        try {
            echo "📚 Définition des constantes TCPDF...\n";
            $this->define_tcpdf_constants();
            echo "✅ Constantes TCPDF définies\n";

            echo "📚 Chargement de TCPDF...\n";

            // Test de chargement TCPDF
            require_once __DIR__ . '/../../lib/tcpdf/tcpdf_autoload.php';

            echo "✅ TCPDF chargé\n";

            echo "🔨 Création d'une instance TCPDF...\n";

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            echo "✅ Instance TCPDF créée\n";

            $version = TCPDF_STATIC::getTCPDFVersion();
            echo "📊 Version TCPDF : {$version}\n";

            echo "📝 Ajout d'une page...\n";

            $pdf->AddPage();

            echo "✅ Page ajoutée\n";

            echo "✍️ Ajout de texte...\n";

            $pdf->SetFont('helvetica', '', 12);

            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'Test TCPDF réussi - ' . date('d/m/Y H:i:s'), 0, 1, 'C');

            echo "✅ Texte ajouté\n";

            echo "💾 Génération du PDF...\n";

            $pdf_content = $pdf->Output('', 'S');

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

        $this->check_admin_permissions();

        include plugin_dir_path(dirname(__FILE__)) . 'template-editor.php';
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
        // Récupérer les paramètres canvas individuels depuis les options WordPress
        wp_localize_script('pdf-builder-admin-v3', 'pdfBuilderCanvasSettings', [
            // Paramètres généraux du canvas
            'default_canvas_width' => get_option('pdf_builder_default_canvas_width', 210),
            'default_canvas_height' => get_option('pdf_builder_default_canvas_height', 297),
            'default_canvas_unit' => get_option('pdf_builder_default_canvas_unit', 'mm'),
            'default_orientation' => get_option('pdf_builder_default_orientation', 'portrait'),
            'canvas_background_color' => get_option('pdf_builder_canvas_background_color', '#ffffff'),
            'canvas_show_transparency' => get_option('pdf_builder_canvas_show_transparency', false),

            // Marges de sécurité
            'margin_top' => get_option('pdf_builder_margin_top', 10),
            'margin_right' => get_option('pdf_builder_margin_right', 10),
            'margin_bottom' => get_option('pdf_builder_margin_bottom', 10),
            'margin_left' => get_option('pdf_builder_margin_left', 10),
            'show_margins' => get_option('pdf_builder_show_margins', true),

            // Paramètres de grille
            'show_grid' => get_option('pdf_builder_show_grid', true),
            'grid_size' => get_option('pdf_builder_grid_size', 10),
            'grid_color' => get_option('pdf_builder_grid_color', '#e0e0e0'),
            'grid_opacity' => get_option('pdf_builder_grid_opacity', 30),

            // Aimantation
            'snap_to_grid' => get_option('pdf_builder_snap_to_grid', true),
            'snap_to_elements' => get_option('pdf_builder_snap_to_elements', true),
            'snap_to_margins' => get_option('pdf_builder_snap_to_margins', true),
            'snap_tolerance' => get_option('pdf_builder_snap_tolerance', 5),

            // Lignes guides
            'show_guides' => get_option('pdf_builder_show_guides', true),
            'lock_guides' => get_option('pdf_builder_lock_guides', false),

            // Paramètres de zoom et navigation
            'default_zoom' => get_option('pdf_builder_default_zoom', '100'),
            'min_zoom' => get_option('pdf_builder_min_zoom', 10),
            'max_zoom' => get_option('pdf_builder_max_zoom', 500),
            'zoom_step' => get_option('pdf_builder_zoom_step', 25),
            'pan_with_mouse' => get_option('pdf_builder_pan_with_mouse', true),
            'smooth_zoom' => get_option('pdf_builder_smooth_zoom', true),
            'show_zoom_indicator' => get_option('pdf_builder_show_zoom_indicator', true),
            'zoom_with_wheel' => get_option('pdf_builder_zoom_with_wheel', true),
            'zoom_to_selection' => get_option('pdf_builder_zoom_to_selection', true),

            // Paramètres de sélection et manipulation
            'show_resize_handles' => get_option('pdf_builder_show_resize_handles', true),
            'handle_size' => get_option('pdf_builder_handle_size', 8),
            'handle_color' => get_option('pdf_builder_handle_color', '#007cba'),
            'enable_rotation' => get_option('pdf_builder_enable_rotation', true),
            'rotation_step' => get_option('pdf_builder_rotation_step', 15),
            'rotation_snap' => get_option('pdf_builder_rotation_snap', true),
            'multi_select' => get_option('pdf_builder_multi_select', true),
            'select_all_shortcut' => get_option('pdf_builder_select_all_shortcut', true),
            'show_selection_bounds' => get_option('pdf_builder_show_selection_bounds', true),
            'copy_paste_enabled' => get_option('pdf_builder_copy_paste_enabled', true),
            'duplicate_on_drag' => get_option('pdf_builder_duplicate_on_drag', false),

            // Paramètres d'export et qualité
            'export_quality' => get_option('pdf_builder_export_quality', 'print'),
            'export_format' => get_option('pdf_builder_export_format', 'pdf'),
            'compress_images' => get_option('pdf_builder_compress_images', true),
            'image_quality' => get_option('pdf_builder_image_quality', 85),
            'max_image_size' => get_option('pdf_builder_max_image_size', 2048),
            'include_metadata' => get_option('pdf_builder_include_metadata', true),
            'pdf_author' => get_option('pdf_builder_pdf_author', get_bloginfo('name')),
            'pdf_subject' => get_option('pdf_builder_pdf_subject', ''),
            'auto_crop' => get_option('pdf_builder_auto_crop', false),
            'embed_fonts' => get_option('pdf_builder_embed_fonts', true),
            'optimize_for_web' => get_option('pdf_builder_optimize_for_web', true),

            // Paramètres avancés
            'enable_hardware_acceleration' => get_option('pdf_builder_enable_hardware_acceleration', true),
            'limit_fps' => get_option('pdf_builder_limit_fps', true),
            'max_fps' => get_option('pdf_builder_max_fps', 60),
            'auto_save_enabled' => get_option('pdf_builder_auto_save_enabled', true),
            'auto_save_interval' => get_option('pdf_builder_auto_save_interval', 30),
            'auto_save_versions' => get_option('pdf_builder_auto_save_versions', 10),
            'undo_levels' => get_option('pdf_builder_undo_levels', 50),
            'redo_levels' => get_option('pdf_builder_redo_levels', 50),
            'enable_keyboard_shortcuts' => get_option('pdf_builder_enable_keyboard_shortcuts', true),
            'debug_mode' => get_option('pdf_builder_debug_mode', false),
            'show_fps' => get_option('pdf_builder_show_fps', false),

            // Anciens paramètres (pour compatibilité)
            'canvas_element_borders_enabled' => get_option('pdf_builder_canvas_element_borders_enabled', true),
            'canvas_border_width' => get_option('pdf_builder_canvas_border_width', 2),
            'canvas_border_color' => get_option('pdf_builder_canvas_border_color', '#007cba'),
            'canvas_border_spacing' => get_option('pdf_builder_canvas_border_spacing', 2),
            'canvas_resize_handles_enabled' => get_option('pdf_builder_show_resize_handles', true),
            'canvas_handle_size' => get_option('pdf_builder_handle_size', 8),
            'canvas_handle_color' => get_option('pdf_builder_handle_color', '#007cba'),
            'canvas_handle_hover_color' => get_option('pdf_builder_canvas_handle_hover_color', '#ffffff'),
            'default_text_color' => get_option('pdf_builder_default_text_color', '#000000'),
            'default_background_color' => get_option('pdf_builder_default_background_color', '#ffffff'),
            'default_font_size' => get_option('pdf_builder_default_font_size', 14)
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
        return $this->template_manager->ajax_save_template();
    }

    /**
     * AJAX - Charger un template
     */
    public function ajax_load_template() {
        return $this->template_manager->ajax_load_template();
    }

    /**
     * Vide le cache des routes REST
     */
    public function ajax_flush_rest_cache() {
        return $this->template_manager->ajax_flush_rest_cache();
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
        $generator->set_order($order); // Passer l'ordre au générateur
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
    public function generate_unified_html($template, $order = null) {
        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>' . ($order ? 'Order #' . $order->get_id() : 'PDF') . '</title>';

        // Gestion des marges d'impression - utiliser la première page
        $margins = ['top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20];
        if (isset($template['pages']) && is_array($template['pages']) && !empty($template['pages'])) {
            $firstPage = $template['pages'][0];
            if (isset($firstPage['margins'])) {
                $margins = $firstPage['margins'];
            }
        }
        $margin_css = sprintf('margin: 0; padding: %dpx %dpx %dpx %dpx;', $margins['top'], $margins['right'], $margins['bottom'], $margins['left']);

        $html .= '<style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            color: #333;
            line-height: 1.4;
            font-size: 14px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .pdf-container {
            position: relative;
            width: 100%;
            height: 100%;
            background: white;
            margin: 0;
            border: none;
            box-shadow: none;
        }
        .pdf-element {
            position: absolute;
            box-sizing: border-box;
            z-index: 1;
        }
        .pdf-element.text-element {
            white-space: pre-wrap;
            word-wrap: break-word;
            z-index: 2;
        }
        .pdf-element.image-element img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .pdf-element.table-element {
            border-collapse: collapse;
        }
        .pdf-element.table-element table {
            width: 100%;
            border-collapse: collapse;
        }
        .pdf-element.table-element th,
        .pdf-element.table-element td {
            border: 1px solid #ddd;
            padding: 4px 8px;
            text-align: left;
        }
        .pdf-element.table-element th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .pdf-element.barcode,
        .pdf-element.qrcode {
            font-family: monospace;
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pdf-element.progress-bar {
            background: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
        }
        .pdf-element.progress-bar div {
            height: 100%;
            background: linear-gradient(90deg, #007cba 0%, #005a87 100%);
            border-radius: 8px;
        }
        .pdf-element.watermark {
            opacity: 0.1;
            pointer-events: none;
            z-index: -1;
        }
        .pdf-element.divider {
            background-color: #cccccc;
            height: 2px;
        }
        @media print {
            body {
                margin: 0;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
        }
        </style>';
        $html .= '</head><body>';
        $html .= '<div class="pdf-container" style="position: relative; width: 595px; height: 842px; background: white;">';

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
            // Trier les éléments par position Y puis X pour un meilleur rendu
            usort($elements, function($a, $b) {
                $a_y = $a['position']['y'] ?? $a['y'] ?? 0;
                $b_y = $b['position']['y'] ?? $b['y'] ?? 0;
                if ($a_y === $b_y) {
                    $a_x = $a['position']['x'] ?? $a['x'] ?? 0;
                    $b_x = $b['position']['x'] ?? $b['x'] ?? 0;
                    return $a_x <=> $b_x;
                }
                return $a_y <=> $b_y;
            });

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

                // Convertir les coordonnées pour TCPDF (de pixels à points, approx 1px = 0.75pt)
                $x_pt = round($x * 0.75);
                $y_pt = round($y * 0.75);
                $width_pt = round($width * 0.75);
                $height_pt = round($height * 0.75);

                // TCPDF ne supporte pas bien position:absolute, utiliser une approche alternative
                // Créer un élément avec des coordonnées TCPDF spéciales
                $base_style = sprintf(
                    'position: absolute; left: %dpt; top: %dpt; width: %dpt; height: %dpt;',
                    $x_pt,
                        $y_pt,
                        $width_pt,
                        $height_pt
                    );

                // Ajouter les styles CSS supplémentaires
                if (isset($element['style'])) {
                    if (isset($element['style']['color'])) {
                        $base_style .= ' color: ' . $element['style']['color'] . ';';
                    }
                    if (isset($element['style']['fontSize'])) {
                        $font_size_pt = round($element['style']['fontSize'] * 0.75);
                        $base_style .= ' font-size: ' . $font_size_pt . 'pt;';
                    }
                    if (isset($element['style']['fontWeight'])) {
                        $base_style .= ' font-weight: ' . $element['style']['fontWeight'] . ';';
                    }
                    if (isset($element['style']['fillColor'])) {
                        $base_style .= ' background-color: ' . $element['style']['fillColor'] . ';';
                    }
                }

                $style = $base_style;

                $content = $element['content'] ?? '';

                // Remplacer les variables si on a une commande WooCommerce
                if ($order) {
                    $content = $this->replace_order_variables($content, $order);
                }

                switch ($element['type']) {
                    case 'text':
                        $final_content = $order ? $this->replace_order_variables($content, $order) : $content;
                        $html .= sprintf('<div class="pdf-element text-element" style="%s">%s</div>', $style, esc_html($final_content));
                        break;

                    case 'invoice_number':
                        if ($order) {
                            $invoice_number = $order->get_id() . '-' . time();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($invoice_number));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'N° de facture'));
                        }
                        break;

                    case 'order_number':
                        if ($order) {
                            $order_number = $order->get_order_number();
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($order_number));
                        } else {
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($content ?: 'N° de commande'));
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
                        // Utiliser l'URL de l'image depuis l'élément du canvas en priorité
                        $logo_url = $element['imageUrl'] ?? $content;

                        // Si pas d'URL spécifique dans l'élément, essayer le logo du site WordPress
                        if (!$logo_url) {
                            $custom_logo_id = get_theme_mod('custom_logo');
                            if ($custom_logo_id) {
                                $logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');
                            }
                        }

                        // Si pas de logo WordPress, essayer l'option site_logo
                        if (!$logo_url) {
                            $site_logo_id = get_option('site_logo');
                            if ($site_logo_id) {
                                $logo_url = wp_get_attachment_image_url($site_logo_id, 'full');
                            }
                        }

                        if ($logo_url) {
                            $html .= sprintf('<div class="pdf-element image-element" style="%s"><img src="%s" style="width: 100%%; height: 100%%; object-fit: contain;" alt="Logo entreprise" /></div>', $style, esc_url($logo_url));
                        } else {
                            // Afficher un placeholder pour le logo de l'entreprise
                            $html .= sprintf('<div class="pdf-element image-element" style="%s"><div style="width: 100%%; height: 100%%; background-color: #f0f0f0; border: 2px dashed #ccc; display: flex; align-items: center; justify-content: center; color: #666; font-size: 12px;">🏢 Logo</div></div>', $style);
                        }
                        break;

                    case 'product_table':
                        if ($order) {
                            $table_html = $this->generate_order_products_table($order);
                            $html .= '<div class="pdf-element table-element" style="' . $style . '">' . $table_html . '</div>';
                        } else {
                            // Aperçu fictif du tableau de produits avec un meilleur style
                            $table_html = '<table style="width: 100%; border-collapse: collapse; font-size: 11px;">';
                            $table_html .= '<thead>';
                            $table_html .= '<tr style="background-color: #f8f9fa;">';
                            $table_html .= '<th style="border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-weight: bold;">Produit</th>';
                            $table_html .= '<th style="border: 1px solid #ddd; padding: 6px 8px; text-align: center; font-weight: bold; width: 60px;">Qté</th>';
                            $table_html .= '<th style="border: 1px solid #ddd; padding: 6px 8px; text-align: right; font-weight: bold; width: 80px;">Prix</th>';
                            $table_html .= '<th style="border: 1px solid #ddd; padding: 6px 8px; text-align: right; font-weight: bold; width: 80px;">Total</th>';
                            $table_html .= '</tr>';
                            $table_html .= '</thead>';
                            $table_html .= '<tbody>';
                            $table_html .= '<tr>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px;">Produit A - Description détaillée</td>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: center;">2</td>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: right;">19.99€</td>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: right;">39.98€</td>';
                            $table_html .= '</tr>';
                            $table_html .= '<tr>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px;">Produit B - Autre article</td>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: center;">1</td>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: right;">29.99€</td>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: right;">29.99€</td>';
                            $table_html .= '</tr>';
                            $table_html .= '</tbody>';
                            $table_html .= '<tfoot>';
                            $table_html .= '<tr style="background-color: #f8f9fa; font-weight: bold;">';
                            $table_html .= '<td colspan="3" style="border: 1px solid #ddd; padding: 6px 8px; text-align: right;">Total:</td>';
                            $table_html .= '<td style="border: 1px solid #ddd; padding: 6px 8px; text-align: right;">69.97€</td>';
                            $table_html .= '</tr>';
                            $table_html .= '</tfoot>';
                            $table_html .= '</table>';
                            $html .= '<div class="pdf-element table-element" style="' . $style . '">' . $table_html . '</div>';
                        }
                        break;

                    case 'company_info':
                        // Informations complètes de la société
                        $company_info = $this->format_complete_company_info();
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, nl2br(esc_html($company_info)));
                        break;

                    case 'document_type':
                        // Type de document basé sur le statut de la commande
                        if ($order) {
                            $order_status = $order->get_status();
                            $document_type = $this->detect_document_type($order_status);
                            $docType = $this->get_document_type_label($document_type);
                        } else {
                            $docType = $content ?: 'Document';
                        }
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($docType));
                        break;

                    case 'divider':
                        // Ligne de séparation horizontale
                        $thickness = $element['thickness'] ?? 2;
                        $color = $element['color'] ?? '#cccccc';
                        $margin = $element['margin'] ?? 10;
                        $divider_style = $style . sprintf('height: %dpx; background-color: %s; margin: %dpx 0;', $thickness, $color, $margin);
                        $html .= sprintf('<div class="pdf-element divider" style="%s"></div>', $divider_style);
                        break;

                    case 'watermark':
                        $watermark_text = $element['content'] ?? 'CONFIDENTIEL';
                        $opacity = isset($element['opacity']) ? $element['opacity'] / 100 : 0.1;
                        $style .= sprintf('opacity: %s; color: rgba(0,0,0,%s); font-size: 48px; text-align: center; transform: rotate(-45deg); pointer-events: none;', $opacity, $opacity);
                        $html .= sprintf('<div class="pdf-element watermark" style="%s">%s</div>', $style, esc_html($watermark_text));
                        break;

                    case 'progress-bar':
                        $progress = $element['progress'] ?? 50;
                        $progress_style = $style . sprintf('background: #f0f0f0; border: 1px solid #ccc; border-radius: 10px; overflow: hidden;');
                        $bar_style = sprintf('width: %d%%; height: 100%%; background: #007cba; border-radius: 8px;', $progress);
                        $html .= sprintf('<div class="pdf-element progress-bar" style="%s"><div style="%s"></div></div>', $progress_style, $bar_style);
                        break;

                    case 'barcode':
                        if ($order) {
                            $barcode_data = $order->get_order_number();
                            $html .= sprintf('<div class="pdf-element barcode" style="%s">*%s*</div>', $style, esc_html($barcode_data));
                        } else {
                            $html .= sprintf('<div class="pdf-element barcode" style="%s">*BARCODE*</div>', $style);
                        }
                        break;

                    case 'qrcode':
                        if ($order) {
                            $qr_data = 'Order: ' . $order->get_order_number();
                            $html .= sprintf('<div class="pdf-element qrcode" style="%s">[QR:%s]</div>', $style, esc_html($qr_data));
                        } else {
                            $html .= sprintf('<div class="pdf-element qrcode" style="%s">[QR:CODE]</div>', $style);
                        }
                        break;

                    case 'icon':
                        $icon_name = $element['icon'] ?? 'star';
                        $html .= sprintf('<div class="pdf-element icon" style="%s">📄</div>', $style);
                        break;

                    case 'line':
                        $line_style = $style . 'border-top: 2px solid #000; height: 0;';
                        $html .= sprintf('<div class="pdf-element line" style="%s"></div>', $line_style);
                        break;

                    case 'customer_info':
                        if ($order) {
                            // Formater comme dans l'aperçu du builder avec les vraies données
                            $customer_html = '<div style="padding: 8px; font-size: 12px; line-height: 1.4;">';
                            $customer_html .= '<div style="font-weight: bold; margin-bottom: 4px;">' . esc_html($order->get_billing_first_name() . ' ' . $order->get_billing_last_name()) . '</div>';

                            // Adresse de facturation
                            $billing_address = $order->get_formatted_billing_address();
                            if ($billing_address) {
                                $address_lines = explode("\n", $billing_address);
                                foreach ($address_lines as $line) {
                                    if (!empty(trim($line))) {
                                        $customer_html .= '<div>' . esc_html(trim($line)) . '</div>';
                                    }
                                }
                            }

                            // Email
                            $email = $order->get_billing_email();
                            if (!empty($email)) {
                                $customer_html .= '<div>' . esc_html($email) . '</div>';
                            }

                            // Téléphone
                            $phone = $order->get_billing_phone();
                            if (!empty($phone)) {
                                $customer_html .= '<div>' . esc_html($phone) . '</div>';
                            }

                            $customer_html .= '</div>';
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, $customer_html);
                        } else {
                            // Aperçu fictif comme dans le builder
                            $customer_html = '<div style="padding: 8px; font-size: 12px; line-height: 1.4;">';
                            $customer_html .= '<div style="font-weight: bold; margin-bottom: 4px;">Client</div>';
                            $customer_html .= '<div>Jean Dupont</div>';
                            $customer_html .= '<div>123 Rue de la Paix</div>';
                            $customer_html .= '<div>75001 Paris</div>';
                            $customer_html .= '<div>France</div>';
                            $customer_html .= '</div>';
                            $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, $customer_html);
                        }
                        break;

                    default:
                        // Élément par défaut - afficher le contenu tel quel
                        $final_content = $order ? $this->replace_order_variables($content, $order) : $content;
                        $html .= sprintf('<div class="pdf-element" style="%s">%s</div>', $style, esc_html($final_content ?: $element['type']));
                        break;
                }
            }
        }

        $html .= '</div>'; // Fermer le pdf-container
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
     * Rend la meta box dans les commandes WooCommerce
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
            echo '<p>' . __('Commande invalide', 'pdf-builder-pro') . '</p>';
            return;
        }

        // Détecter automatiquement le type de document basé sur le statut de la commande
        $order_status = $order->get_status();
        $document_type = $this->detect_document_type($order_status);
        $document_type_label = $this->get_document_type_label($document_type);

        // Vérifier d'abord s'il y a un mapping spécifique pour ce statut de commande
        $status_templates = get_option('pdf_builder_order_status_templates', []);
        $status_key = 'wc-' . $order_status;
        $mapped_template = null;

        if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
            // Il y a un mapping spécifique pour ce statut
            $mapped_template = $wpdb->get_row($wpdb->prepare(
                "SELECT id, name FROM $table_templates WHERE id = %d",
                $status_templates[$status_key]
            ), ARRAY_A);
        }

        // Si pas de mapping spécifique, utiliser la logique de détection automatique
        $default_template = $mapped_template;
        if (!$default_template) {
            $keywords = $this->get_document_keywords($document_type);

            if (!empty($keywords)) {
                // D'abord chercher un template par défaut dont le nom contient un mot-clé du type
                $placeholders = str_repeat('%s,', count($keywords) - 1) . '%s';
                $sql = $wpdb->prepare(
                    "SELECT id, name FROM $table_templates WHERE is_default = 1 AND (" .
                    implode(' OR ', array_fill(0, count($keywords), 'LOWER(name) LIKE LOWER(%s)')) .
                    ") LIMIT 1",
                    array_map(function($keyword) { return '%' . $keyword . '%'; }, $keywords)
                );
                $default_template = $wpdb->get_row($sql, ARRAY_A);
            }

            // Si aucun template spécifique trouvé, prendre n'importe quel template par défaut
            if (!$default_template) {
                $default_template = $wpdb->get_row("SELECT id, name FROM $table_templates WHERE is_default = 1 LIMIT 1", ARRAY_A);
            }
        }

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
        #pdf-builder-order-meta-box .template-info {
            margin-bottom: 15px;
        }
        #pdf-builder-order-meta-box .template-info label {
            display: block;
            margin-bottom: 6px;
            font-weight: 500;
            color: #23282d;
            font-size: 13px;
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
        #pdf-builder-order-meta-box .btn-preview {
            background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
            color: white;
        }
        #pdf-builder-order-meta-box .btn-preview:hover {
            background: linear-gradient(135deg, #0056b3 0%, #5a0fc8 100%);
        }
        #pdf-builder-order-meta-box .action-buttons button.loading {
            position: relative;
            color: transparent !important;
        }
        #pdf-builder-order-meta-box .action-buttons button.loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top: 2px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        #pdf-builder-order-meta-box .status-message {
            margin-top: 12px;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 12px;
            text-align: center;
            font-weight: 500;
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(-10px);
        }
        #pdf-builder-order-meta-box .status-message.show {
            opacity: 1;
            transform: translateY(0);
        }
        #pdf-builder-order-meta-box .status-loading {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            color: #1976d2;
            border: 1px solid #90caf9;
            animation: pulse 2s infinite;
        }
        #pdf-builder-order-meta-box .status-success {
            background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%);
            color: #2e7d32;
            border: 1px solid #81c784;
        }
        #pdf-builder-order-meta-box .status-error {
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            color: #c62828;
            border: 1px solid #e57373;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
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
                    <div style="padding: 10px; background: <?php echo $mapped_template ? '#e8f0ff' : '#e8f5e8'; ?>; border: 1px solid <?php echo $mapped_template ? '#b3d4ff' : '#c3e6c3'; ?>; border-radius: 6px; font-size: 14px; color: <?php echo $mapped_template ? '#0d47a1' : '#155724'; ?>;">
                        <?php if ($default_template): ?>
                            <strong><?php echo esc_html($default_template['name']); ?></strong>
                            <small style="color: #6c757d; display: block; margin-top: 4px;">
                                <?php if ($mapped_template): ?>
                                    <?php _e('Template assigné spécifiquement pour le statut "', 'pdf-builder-pro'); ?><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?><?php _e('"', 'pdf-builder-pro'); ?>
                                <?php else: ?>
                                    <?php _e('Template automatiquement sélectionné pour ce type de document', 'pdf-builder-pro'); ?>
                                <?php endif; ?>
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
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>"
                            title="<?php echo esc_attr(__('Générer un aperçu du PDF', 'pdf-builder-pro')); ?>">
                        👁️ <?php _e('Aperçu PDF', 'pdf-builder-pro'); ?>
                    </button>

                    <button type="button"
                            id="pdf-builder-generate-btn"
                            class="btn-generate"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>"
                            title="<?php echo esc_attr(__('Générer le PDF définitif', 'pdf-builder-pro')); ?>">
                        ⚡ <?php _e('Générer PDF', 'pdf-builder-pro'); ?>
                    </button>

                    <button type="button"
                            id="pdf-builder-download-btn"
                            class="btn-download"
                            style="display: none;"
                            data-order-id="<?php echo esc_attr($order->get_id()); ?>"
                            title="<?php echo esc_attr(__('Télécharger le PDF généré', 'pdf-builder-pro')); ?>">
                        ⬇️ <?php _e('Télécharger PDF', 'pdf-builder-pro'); ?>
                    </button>
                </div>

                <div id="pdf-builder-status" class="status-message" style="display: none;"></div>
            </div>
        </div>
<script>
        jQuery(document).ready(function($) {
            // Vérifier que WordPress et jQuery sont chargés
            if (typeof wp === 'undefined' || typeof $ === 'undefined') {
                console.error('PDF Builder: WordPress ou jQuery non disponible');
                return;
            }

            // Définir ajaxurl si nécessaire
            if (typeof ajaxurl === 'undefined') {
                ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
            }

            // Vérifier que les éléments du DOM existent
            var $generateBtn = $('#pdf-builder-generate-btn');
            var $downloadBtn = $('#pdf-builder-download-btn');
            var $previewBtn = $('#pdf-builder-preview-btn');
            var $status = $('#pdf-builder-status');

            if ($generateBtn.length === 0 || $previewBtn.length === 0) {
                console.error('PDF Builder: Éléments du metabox non trouvés');
                return;
            }

            // Fonction pour afficher le statut avec validation
            function showStatus(message, type) {
                if (typeof message !== 'string' || message.length === 0) {
                    console.error('PDF Builder: Message de statut invalide');
                    return;
                }

                type = type || 'loading';
                var validTypes = ['loading', 'success', 'error'];
                if (validTypes.indexOf(type) === -1) {
                    type = 'loading';
                }

                var classes = {
                    'loading': 'status-loading',
                    'success': 'status-success',
                    'error': 'status-error'
                };

                $status.removeClass('status-loading status-success status-error show')
                       .addClass(classes[type])
                       .html(message)
                       .addClass('show');
            }

            // Fonction pour masquer le statut
            function hideStatus() {
                $status.removeClass('show');
                setTimeout(function() {
                    $status.hide();
                }, 300);
            }

            // Fonction pour définir l'état de chargement d'un bouton
            function setButtonLoading($btn, loading) {
                if (!$btn || $btn.length === 0) return;

                if (loading) {
                    $btn.addClass('loading').prop('disabled', true);
                } else {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            }

            // Générer PDF avec gestion d'erreurs améliorée
            $generateBtn.on('click', function(e) {
                e.preventDefault();

                var orderId = $(this).data('order-id');
                var templateId = <?php echo isset($default_template) && $default_template ? esc_js($default_template['id']) : '0'; ?>;

                // Validation des données
                if (!orderId || orderId <= 0) {
                    showStatus('<?php echo esc_js(__('ID de commande invalide', 'pdf-builder-pro')); ?>', 'error');
                    return;
                }

                if (typeof orderId !== 'number' && typeof orderId !== 'string') {
                    showStatus('<?php echo esc_js(__('Type de données invalide', 'pdf-builder-pro')); ?>', 'error');
                    return;
                }

                console.log('PDF Builder: Generate button clicked');
                console.log('PDF Builder: Order ID:', orderId);
                console.log('PDF Builder: Template ID:', templateId);

                showStatus('<?php echo esc_js(__('Génération du PDF en cours...', 'pdf-builder-pro')); ?>', 'loading');
                setButtonLoading($generateBtn, true);

                // Préparer les données AJAX
                var ajaxData = {
                    action: 'pdf_builder_generate_order_pdf',
                    order_id: parseInt(orderId, 10),
                    template_id: parseInt(templateId, 10),
                    nonce: '<?php echo esc_js(wp_create_nonce('pdf_builder_order_actions')); ?>'
                };

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    timeout: 30000,
                    data: ajaxData,
                    success: function(response, textStatus, xhr) {
                        console.log('PDF Builder: Generate AJAX success');
                        console.log('PDF Builder: Response:', response);

                        // Validation de la réponse
                        if (typeof response !== 'object' || response === null) {
                            showStatus('<?php echo esc_js(__('Réponse serveur invalide', 'pdf-builder-pro')); ?>', 'error');
                            return;
                        }

                        if (response.success) {
                            // Validation de l'URL
                            if (response.data && typeof response.data.url === 'string' && response.data.url.length > 0) {
                                // Afficher le bouton de téléchargement
                                $downloadBtn.attr('href', response.data.url).show();
                                showStatus('<?php echo esc_js(__('PDF généré avec succès ✅', 'pdf-builder-pro')); ?>', 'success');

                                // Ouvrir automatiquement le PDF dans un nouvel onglet
                                setTimeout(function() {
                                    window.open(response.data.url, '_blank');
                                }, 500);
                            } else {
                                showStatus('<?php echo esc_js(__('URL du PDF manquante', 'pdf-builder-pro')); ?>', 'error');
                            }
                        } else {
                            var errorMsg = (response.data && typeof response.data === 'string') ? response.data : '<?php echo esc_js(__('Erreur lors de la génération ❌', 'pdf-builder-pro')); ?>';
                            console.error('PDF Builder: Generate failed:', errorMsg);
                            showStatus(errorMsg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF Builder: Generate AJAX error');
                        console.error('PDF Builder: Status:', status);
                        console.error('PDF Builder: Error:', error);
                        console.error('PDF Builder: Response:', xhr.responseText);

                        var errorMsg = '<?php echo esc_js(__('Erreur AJAX lors de la génération ❌', 'pdf-builder-pro')); ?>';
                        if (status === 'timeout') {
                            errorMsg = '<?php echo esc_js(__('Timeout - génération trop longue', 'pdf-builder-pro')); ?>';
                        } else if (status === 'parsererror') {
                            errorMsg = '<?php echo esc_js(__('Erreur de parsing JSON', 'pdf-builder-pro')); ?>';
                        }
                        showStatus(errorMsg, 'error');
                    },
                    complete: function(xhr, status) {
                        console.log('PDF Builder: Generate AJAX complete');
                        setButtonLoading($generateBtn, false);
                    }
                });
            });

            // Télécharger PDF
            $downloadBtn.on('click', function(e) {
                e.preventDefault();
                var pdfUrl = $(this).attr('href');
                if (pdfUrl) {
                    window.open(pdfUrl, '_blank');
                }
            });

            // Aperçu PDF avec gestion d'erreurs améliorée
            $previewBtn.on('click', function(e) {
                e.preventDefault();

                var orderId = $(this).data('order-id');

                // Validation des données
                if (!orderId || orderId <= 0) {
                    showStatus('<?php echo esc_js(__('ID de commande invalide', 'pdf-builder-pro')); ?>', 'error');
                    return;
                }

                if (typeof orderId !== 'number' && typeof orderId !== 'string') {
                    showStatus('<?php echo esc_js(__('Type de données invalide', 'pdf-builder-pro')); ?>', 'error');
                    return;
                }

                console.log('PDF Builder: Preview button clicked');
                console.log('PDF Builder: Order ID:', orderId);

                showStatus('<?php echo esc_js(__('Génération de l\'aperçu en cours...', 'pdf-builder-pro')); ?>', 'loading');
                setButtonLoading($previewBtn, true);

                // Préparer les données AJAX
                var ajaxData = {
                    action: 'pdf_builder_preview_order_pdf',
                    order_id: parseInt(orderId, 10),
                    nonce: '<?php echo esc_js(wp_create_nonce('pdf_builder_order_actions')); ?>'
                };

                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    dataType: 'json',
                    timeout: 30000,
                    data: ajaxData,
                    success: function(response, textStatus, xhr) {
                        console.log('PDF Builder: Preview AJAX success');
                        console.log('PDF Builder: Response:', response);

                        // Validation de la réponse
                        if (typeof response !== 'object' || response === null) {
                            showStatus('<?php echo esc_js(__('Réponse serveur invalide', 'pdf-builder-pro')); ?>', 'error');
                            return;
                        }

                        if (response.success) {
                            // Validation de l'URL
                            if (response.data && typeof response.data.url === 'string' && response.data.url.length > 0) {
                                // Ouvrir l'aperçu dans un nouvel onglet
                                setTimeout(function() {
                                    window.open(response.data.url, '_blank');
                                }, 500);
                                showStatus('<?php echo esc_js(__('Aperçu généré avec succès ✅', 'pdf-builder-pro')); ?>', 'success');
                            } else {
                                showStatus('<?php echo esc_js(__('URL de l\'aperçu manquante', 'pdf-builder-pro')); ?>', 'error');
                            }
                        } else {
                            var errorMsg = (response.data && typeof response.data === 'string') ? response.data : '<?php echo esc_js(__('Erreur lors de l\'aperçu ❌', 'pdf-builder-pro')); ?>';
                            console.error('PDF Builder: Preview failed:', errorMsg);
                            showStatus(errorMsg, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF Builder: Preview AJAX error');
                        console.error('PDF Builder: Status:', status);
                        console.error('PDF Builder: Error:', error);
                        console.error('PDF Builder: Response:', xhr.responseText);

                        var errorMsg = '<?php echo esc_js(__('Erreur AJAX lors de l\'aperçu ❌', 'pdf-builder-pro')); ?>';
                        if (status === 'timeout') {
                            errorMsg = '<?php echo esc_js(__('Timeout - aperçu trop long', 'pdf-builder-pro')); ?>';
                        } else if (status === 'parsererror') {
                            errorMsg = '<?php echo esc_js(__('Erreur de parsing JSON', 'pdf-builder-pro')); ?>';
                        }
                        showStatus(errorMsg, 'error');
                    },
                    complete: function(xhr, status) {
                        console.log('PDF Builder: Preview AJAX complete');
                        setButtonLoading($previewBtn, false);
                    }
                });
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
            // Charger le template de manière robuste
            if ($template_id > 0) {
                $template_data = $this->load_template_robust($template_id);
            } else {
                // Vérifier s'il y a un template spécifique pour le statut de la commande
                $order_status = $order->get_status();
                $status_templates = get_option('pdf_builder_order_status_templates', []);
                $status_key = 'wc-' . $order_status;

                if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                    $mapped_template_id = $status_templates[$status_key];
                    $template_data = $this->load_template_robust($mapped_template_id);
                    error_log('✅ PDF BUILDER - Template loaded from status mapping: ' . $mapped_template_id . ' for status: ' . $order_status);
                } else {
                    $template_data = $this->get_default_invoice_template();
                    error_log('✅ PDF BUILDER - Default template loaded (no status mapping found)');
                }
            }

            // Générer le PDF avec les données de la commande
            $pdf_filename = 'order-' . $order_id . '-' . time() . '.pdf';
            $pdf_path = $this->generate_order_pdf_private($order, $template_data, $pdf_filename);

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
     * Retourne le libellé du type de document
     */
    private function generate_order_pdf_private($order, $template_data, $filename) {
        error_log('🟡 PDF BUILDER - generate_order_pdf_private: Début génération PDF pour commande ' . $order->get_id());

        // Créer le répertoire de stockage s'il n'existe pas
        $upload_dir = wp_upload_dir();
        $pdf_dir = $upload_dir['basedir'] . '/pdf-builder/orders';
        error_log('🟡 PDF BUILDER - generate_order_pdf_private: Répertoire PDF: ' . $pdf_dir);

        if (!file_exists($pdf_dir)) {
            wp_mkdir_p($pdf_dir);
            error_log('✅ PDF BUILDER - generate_order_pdf_private: Répertoire créé');
        }

        $pdf_path = $pdf_dir . '/' . $filename;
        error_log('🟡 PDF BUILDER - generate_order_pdf_private: Chemin PDF: ' . $pdf_path);

        try {
            // Générer le HTML d'abord
            error_log('🟡 PDF BUILDER - generate_order_pdf_private: Génération HTML...');
            $html_content = $this->generate_unified_html($template_data, $order);
            error_log('✅ PDF BUILDER - generate_order_pdf_private: HTML généré, longueur: ' . strlen($html_content) . ' caractères');

            // Charger TCPDF si nécessaire
            if (!class_exists('TCPDF')) {
                error_log('🟡 PDF BUILDER - generate_order_pdf_private: Chargement TCPDF...');
                $this->load_tcpdf_library();
                error_log('✅ PDF BUILDER - generate_order_pdf_private: TCPDF chargé');
            } else {
                error_log('✅ PDF BUILDER - generate_order_pdf_private: TCPDF déjà chargé');
            }

            // Utiliser une bibliothèque PDF si disponible
            if (class_exists('TCPDF')) {
                error_log('🟡 PDF BUILDER - generate_order_pdf_private: Création instance TCPDF...');
                // Utiliser TCPDF si disponible
                $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $pdf->SetCreator('PDF Builder Pro');
                $pdf->SetAuthor('PDF Builder Pro');
                $pdf->SetTitle('Order #' . $order->get_id());

                error_log('🟡 PDF BUILDER - generate_order_pdf_private: Ajout de page...');
                $pdf->AddPage();

                error_log('🟡 PDF BUILDER - generate_order_pdf_private: Écriture HTML...');
                $pdf->writeHTML($html_content, true, false, true, false, '');

                error_log('🟡 PDF BUILDER - generate_order_pdf_private: Génération fichier PDF...');
                $pdf->Output($pdf_path, 'F');

                error_log('✅ PDF BUILDER - generate_order_pdf_private: PDF généré avec succès: ' . $pdf_path);
                return $pdf_path;
            } else {
                error_log('❌ PDF BUILDER - generate_order_pdf_private: TCPDF non disponible');
                // Fallback: créer un fichier HTML pour simulation
                file_put_contents($pdf_path, $html_content);
                return $pdf_path;
            }
        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - generate_order_pdf_private: Exception: ' . $e->getMessage());
            error_log('❌ PDF BUILDER - generate_order_pdf_private: Stack trace: ' . $e->getTraceAsString());
            throw $e;
        } catch (Error $e) {
            error_log('❌ PDF BUILDER - generate_order_pdf_private: Error fatale: ' . $e->getMessage());
            error_log('❌ PDF BUILDER - generate_order_pdf_private: Stack trace: ' . $e->getTraceAsString());
            throw $e;
        }
    }

    /**
     * Charge la bibliothèque TCPDF depuis différents chemins possibles
     */
    private function load_tcpdf_library() {
        // Définir les constantes TCPDF AVANT de charger la bibliothèque
        $this->define_tcpdf_constants();

        // Définir K_TCPDF_VERSION si pas déjà défini
        if (!defined('K_TCPDF_VERSION')) {
            define('K_TCPDF_VERSION', '6.6.2');
        }

        $tcpdf_paths = [
            __DIR__ . '/../../lib/tcpdf/tcpdf_autoload.php',  // Essayer tcpdf_autoload.php d'abord
            __DIR__ . '/../../lib/tcpdf/tcpdf.php',
            __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php',
            plugin_dir_path(__FILE__) . '../../lib/tcpdf/tcpdf_autoload.php',
            plugin_dir_path(__FILE__) . '../../lib/tcpdf/tcpdf.php',
            plugin_dir_path(__FILE__) . '../../vendor/tecnickcom/tcpdf/tcpdf.php'
        ];

        foreach ($tcpdf_paths as $path) {
            if (file_exists($path)) {
                require_once $path;
                if (class_exists('TCPDF')) {
                    // TCPDF chargé avec succès (constantes déjà définies)
                    return true;
                }
            }
        }

        error_log('PDF Builder Pro: Impossible de charger TCPDF depuis tous les chemins testés');
        return false;
    }

    /**
     * Définit les constantes TCPDF nécessaires
     */
    private function define_tcpdf_constants() {
        $plugin_dir = plugin_dir_path(__FILE__) . '../../';

        $constants = [
            'PDF_PAGE_ORIENTATION' => 'P',
            'PDF_UNIT' => 'mm',
            'PDF_PAGE_FORMAT' => 'A4',
            'K_PATH_FONTS' => $plugin_dir . 'lib/tcpdf/fonts/',
            'K_PATH_CACHE' => $plugin_dir . 'uploads/pdf-builder-cache/',
            'K_PATH_IMAGES' => $plugin_dir . 'lib/tcpdf/images/',
            'K_PATH_URL' => $plugin_dir . 'lib/tcpdf/'
        ];

        foreach ($constants as $name => $value) {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }

    /**
     * Génère du HTML pour une commande WooCommerce
     */
    private function generate_order_html($order, $template_data) {
        return $this->generate_unified_html($template_data, $order);
    }

    /**
     * Formate les informations complètes de la société
     */
    private function format_complete_company_info() {
        // Essayer d'abord de récupérer depuis l'option personnalisée
        $company_info = get_option('pdf_builder_company_info', '');

        // Si les informations sont configurées manuellement, les utiliser
        if (!empty($company_info)) {
            return $company_info;
        }

        // Sinon, récupérer automatiquement depuis WooCommerce/WordPress
        $company_parts = [];

        // Nom de la société (nom du site WordPress)
        $company_name = get_bloginfo('name');
        if (!empty($company_name)) {
            $company_parts[] = $company_name;
        }

        // Adresse depuis WooCommerce
        $address_parts = [];
        $address1 = get_option('woocommerce_store_address');
        $address2 = get_option('woocommerce_store_address_2');
        $city = get_option('woocommerce_store_city');
        $postcode = get_option('woocommerce_store_postcode');
        $country = get_option('woocommerce_store_country');

        if (!empty($address1)) $address_parts[] = $address1;
        if (!empty($address2)) $address_parts[] = $address2;

        $city_line = [];
        if (!empty($postcode)) $city_line[] = $postcode;
        if (!empty($city)) $city_line[] = $city;
        if (!empty($city_line)) $address_parts[] = implode(' ', $city_line);

        if (!empty($country)) {
            // Convertir le code pays en nom complet si possible
            $countries = WC()->countries->get_countries();
            $country_name = isset($countries[$country]) ? $countries[$country] : $country;
            $address_parts[] = $country_name;
        }

        if (!empty($address_parts)) {
            $company_parts = array_merge($company_parts, $address_parts);
        }

        // Email depuis WordPress
        $email = get_bloginfo('admin_email');
        if (!empty($email)) {
            $company_parts[] = 'Email: ' . $email;
        }

        // Si on a au moins le nom, retourner les infos récupérées
        if (!empty($company_parts)) {
            return implode("\n", $company_parts);
        }

        // Sinon, données d'exemple par défaut
        return "Votre Société SARL\n123 Rue de l'Entreprise\n75001 Paris\nFrance\nTél: 01 23 45 67 89\nEmail: contact@votresociete.com";
    }
    private function replace_order_variables($content, $order) {
        // Préparer les données de la commande
        $billing_address = $order->get_formatted_billing_address();
        $shipping_address = $order->get_formatted_shipping_address();

        // Détecter le type de document
        $order_status = $order->get_status();
        $document_type = $this->detect_document_type($order_status);
        $document_type_label = $this->get_document_type_label($document_type);

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
            '{{document_type}}' => $document_type,
            '{{document_type_label}}' => $document_type_label,
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
            '[document_type]' => $document_type,
            '[document_type_label]' => $document_type_label,
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
            '{document_type}' => $document_type,
            '{document_type_label}' => $document_type_label,
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

            $html .= '<tr>';
            $html .= '<td style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">' . esc_html($fee_name) . '</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 5px;">-</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 5px;">-</td>';
            $html .= '<td style="border: 1px solid #ddd; padding: 5px; font-weight: bold;">' . wc_price($fee_total) . '</td>';
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
            'pages' => array(
                array(
                    'margins' => array('top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20),
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
                            'type' => 'customer_info',
                            'position' => array('x' => 50, 'y' => 150),
                            'size' => array('width' => 250, 'height' => 80),
                            'style' => array('fontSize' => 12, 'color' => '#000000'),
                            'content' => 'Informations client'
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
                )
            )
        );
    }

    /**
     * AJAX - Debug PDF metabox generation
     */
    public function ajax_debug_pdf_metabox() {
        // Vérifier les permissions
        if (!current_user_can('manage_woocommerce')) {
            wp_die('Permissions insuffisantes');
        }

        $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
        $template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

        if (!$order_id) {
            wp_die('Order ID manquant');
        }

        echo "<h1>Debug PDF Generation - Order #$order_id</h1>";
        echo "<style>body { font-family: Arial, sans-serif; margin: 20px; } table { border-collapse: collapse; width: 100%; } th, td { border: 1px solid #ddd; padding: 8px; text-align: left; } th { background-color: #f2f2f2; }</style>";

        // Charger la commande
        $order = wc_get_order($order_id);
        if (!$order) {
            wp_die('Commande non trouvée');
        }

        echo "<h2>Informations de la commande</h2>";
        echo "<p><strong>ID:</strong> " . $order->get_id() . "</p>";
        echo "<p><strong>Numéro:</strong> " . $order->get_order_number() . "</p>";
        echo "<p><strong>Statut:</strong> " . $order->get_status() . "</p>";
        echo "<p><strong>Client:</strong> " . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . "</p>";
        echo "<p><strong>Total:</strong> " . wc_price($order->get_total()) . "</p>";

        // Produits de la commande
        echo "<h2>Produits de la commande</h2>";
        echo "<table>";
        echo "<tr><th>Produit</th><th>Quantité</th><th>Prix</th><th>Total</th></tr>";
        foreach ($order->get_items() as $item) {
            echo "<tr>";
            echo "<td>" . esc_html($item->get_name()) . "</td>";
            echo "<td>" . $item->get_quantity() . "</td>";
            echo "<td>" . wc_price($item->get_total() / $item->get_quantity()) . "</td>";
            echo "<td>" . wc_price($item->get_total()) . "</td>";
            echo "</tr>";
        }
        echo "</table>";

        // Charger le template
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        if ($template_id > 0) {
            $template_data = $wpdb->get_var($wpdb->prepare(
                "SELECT template_data FROM $table_templates WHERE id = %d",
                $template_id
            ));
            if ($template_data) {
                $template = json_decode($template_data, true);
                echo "<h2>Template chargé (ID: $template_id)</h2>";
            }
        } else {
            // Détection automatique du template
            $order_status = $order->get_status();
            $status_templates = get_option('pdf_builder_order_status_templates', []);

            $selected_template_id = null;
            $status_key = 'wc-' . $order_status;

            if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                $selected_template_id = $status_templates[$status_key];
                echo "<h2>Template détecté automatiquement (statut: $order_status)</h2>";
            } else {
                // Chercher un template par nom
                $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name ASC", ARRAY_A);
                foreach ($all_templates as $tpl) {
                    if (stripos($tpl['name'], 'facture') !== false) {
                        $selected_template_id = $tpl['id'];
                        echo "<h2>Template trouvé par nom (contient 'facture')</h2>";
                        break;
                    }
                }
            }

            if ($selected_template_id) {
                $template_data = $wpdb->get_var($wpdb->prepare(
                    "SELECT template_data FROM $table_templates WHERE id = %d",
                    $selected_template_id
                ));
                if ($template_data) {
                    $template = json_decode($template_data, true);
                    $template_id = $selected_template_id;
                }
            }
        }

        if (!isset($template) || !$template) {
            echo "<h2 style='color: red;'>ERREUR: Aucun template trouvé !</h2>";
            echo "<p>Templates disponibles:</p>";
            $all_templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name ASC", ARRAY_A);
            echo "<ul>";
            foreach ($all_templates as $tpl) {
                echo "<li>" . $tpl['id'] . ": " . esc_html($tpl['name']) . "</li>";
            }
            echo "</ul>";
            wp_die();
        }

        echo "<p><strong>Template ID:</strong> $template_id</p>";

        // Analyser la structure du template
        echo "<h2>Structure du template</h2>";
        echo "<pre style='background: #f5f5f5; padding: 10px; overflow: auto; max-height: 200px;'>";
        print_r(array_keys($template));
        echo "</pre>";

        if (isset($template['pages']) && is_array($template['pages'])) {
            echo "<h3>Pages du template (" . count($template['pages']) . " pages)</h3>";

            foreach ($template['pages'] as $page_index => $page) {
                echo "<h4>Page " . ($page_index + 1) . "</h4>";

                if (isset($page['elements']) && is_array($page['elements'])) {
                    echo "<p><strong>Éléments (" . count($page['elements']) . "):</strong></p>";
                    echo "<ul>";
                    foreach ($page['elements'] as $element) {
                        $type = $element['type'] ?? 'unknown';
                        $content = isset($element['content']) ? substr($element['content'], 0, 50) . (strlen($element['content']) > 50 ? '...' : '') : '';
                        echo "<li><strong>$type</strong>: " . esc_html($content) . "</li>";
                    }
                    echo "</ul>";
                } else {
                    echo "<p style='color: red;'>Aucun élément dans cette page !</p>";
                }
            }
        } else {
            echo "<p style='color: red;'>Structure de template invalide - pas de pages !</p>";
        }

        // Générer l'HTML
        echo "<h2>Génération de l'HTML</h2>";

        try {
            $html_content = $this->generate_unified_html($template, $order);

            echo "<p><strong>Longueur HTML généré:</strong> " . strlen($html_content) . " caractères</p>";

            echo "<h3>Aperçu HTML (tronqué)</h3>";
            echo "<div style='border: 1px solid #ccc; padding: 10px; max-height: 400px; overflow: auto; background: #f9f9f9;'>";
            echo substr($html_content, 0, 2000) . (strlen($html_content) > 2000 ? '<p><em>... [tronqué]</em></p>' : '');
            echo "</div>";

            // Vérifier si la table des produits est présente
            if (strpos($html_content, '<table') !== false) {
                echo "<p style='color: green;'>✅ Table des produits détectée dans le HTML</p>";
            } else {
                echo "<p style='color: red;'>❌ Aucune table détectée dans le HTML</p>";
            }

        } catch (Exception $e) {
            echo "<p style='color: red;'>Erreur lors de la génération HTML: " . $e->getMessage() . "</p>";
        }

        wp_die();
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
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'pdf_builder_settings')) {
            wp_send_json_error('Nonce invalide');
            return;
        }

        // Vérification des permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permissions insuffisantes');
            return;
        }

        // Traitement des paramètres comme dans la logique non-AJAX
        $settings = [
            'debug_mode' => isset($_POST['debug_mode']),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
            'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
            'email_notifications_enabled' => isset($_POST['email_notifications_enabled']),
            'notification_events' => isset($_POST['notification_events']) ? array_map('sanitize_text_field', $_POST['notification_events']) : [],
            // Paramètres Canvas - anciens
            'canvas_element_borders_enabled' => isset($_POST['canvas_element_borders_enabled']),
            'canvas_border_width' => isset($_POST['canvas_border_width']) ? floatval($_POST['canvas_border_width']) : 1,
            'canvas_border_color' => isset($_POST['canvas_border_color']) ? sanitize_text_field($_POST['canvas_border_color']) : '#007cba',
            'canvas_border_spacing' => isset($_POST['canvas_border_spacing']) ? intval($_POST['canvas_border_spacing']) : 2,
            'canvas_resize_handles_enabled' => isset($_POST['canvas_resize_handles_enabled']),
            'canvas_handle_size' => isset($_POST['canvas_handle_size']) ? intval($_POST['canvas_handle_size']) : 8,
            'canvas_handle_color' => isset($_POST['canvas_handle_color']) ? sanitize_text_field($_POST['canvas_handle_color']) : '#007cba',
            'canvas_handle_hover_color' => isset($_POST['canvas_handle_hover_color']) ? sanitize_text_field($_POST['canvas_handle_hover_color']) : '#005a87',
            // Paramètres Canvas - nouveaux sous-onglets
            'default_canvas_width' => isset($_POST['default_canvas_width']) ? intval($_POST['default_canvas_width']) : 210,
            'default_canvas_height' => isset($_POST['default_canvas_height']) ? intval($_POST['default_canvas_height']) : 297,
            'default_canvas_unit' => isset($_POST['default_canvas_unit']) ? sanitize_text_field($_POST['default_canvas_unit']) : 'mm',
            'canvas_background_color' => isset($_POST['canvas_background_color']) ? sanitize_text_field($_POST['canvas_background_color']) : '#ffffff',
            'canvas_show_transparency' => isset($_POST['canvas_show_transparency']),
            'show_margins' => isset($_POST['show_margins']),
            'margin_top' => isset($_POST['margin_top']) ? intval($_POST['margin_top']) : 10,
            'margin_right' => isset($_POST['margin_right']) ? intval($_POST['margin_right']) : 10,
            'margin_bottom' => isset($_POST['margin_bottom']) ? intval($_POST['margin_bottom']) : 10,
            'margin_left' => isset($_POST['margin_left']) ? intval($_POST['margin_left']) : 10,
            'email_notifications' => isset($_POST['email_notifications']),
            'admin_email' => sanitize_email($_POST['admin_email'] ?? ''),
            'notification_log_level' => sanitize_text_field($_POST['notification_log_level'] ?? 'info')
        ];

        // Sauvegarde des informations entreprise
        if (isset($_POST['company_vat'])) {
            update_option('pdf_builder_company_vat', sanitize_text_field($_POST['company_vat']));
        }
        if (isset($_POST['company_rcs'])) {
            update_option('pdf_builder_company_rcs', sanitize_text_field($_POST['company_rcs']));
        }
        if (isset($_POST['company_siret'])) {
            update_option('pdf_builder_company_siret', sanitize_text_field($_POST['company_siret']));
        }
        if (isset($_POST['company_phone'])) {
            update_option('pdf_builder_company_phone', sanitize_text_field($_POST['company_phone']));
        }

        // Sauvegarde des paramètres
        foreach ($settings as $key => $value) {
            update_option('pdf_builder_' . $key, $value);
        }

        // Traitement spécifique des rôles autorisés
        if (isset($_POST['pdf_builder_allowed_roles'])) {
            $allowed_roles = array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles']);
            // S'assurer qu'au moins un rôle est sélectionné
            if (empty($allowed_roles)) {
                $allowed_roles = ['administrator']; // Rôle par défaut
            }
            update_option('pdf_builder_allowed_roles', $allowed_roles);
        }

        // Traitement des mappings template par statut de commande
        if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
            $template_mappings = [];
            foreach ($_POST['order_status_templates'] as $status => $template_id) {
                $template_id = intval($template_id);
                if ($template_id > 0) {
                    $template_mappings[sanitize_text_field($status)] = $template_id;
                }
            }
            update_option('pdf_builder_order_status_templates', $template_mappings);
        }

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

        // Paramètres Canvas complets (tous les paramètres des sous-onglets)
        // Général
        $settings['default_canvas_width'] = intval($_POST['default_canvas_width'] ?? 210);
        $settings['default_canvas_height'] = intval($_POST['default_canvas_height'] ?? 297);
        $settings['default_canvas_unit'] = sanitize_text_field($_POST['default_canvas_unit'] ?? 'mm');
        $settings['default_orientation'] = sanitize_text_field($_POST['default_orientation'] ?? 'portrait');
        $settings['canvas_background_color'] = sanitize_hex_color($_POST['canvas_background_color'] ?? '#ffffff');
        $settings['canvas_show_transparency'] = isset($_POST['canvas_show_transparency']);

        // Marges de sécurité
        $settings['margin_top'] = intval($_POST['margin_top'] ?? 10);
        $settings['margin_right'] = intval($_POST['margin_right'] ?? 10);
        $settings['margin_bottom'] = intval($_POST['margin_bottom'] ?? 10);
        $settings['margin_left'] = intval($_POST['margin_left'] ?? 10);
        $settings['show_margins'] = isset($_POST['show_margins']);

        // Grille & Aimants
        $settings['show_grid'] = isset($_POST['show_grid']);
        $settings['grid_size'] = intval($_POST['grid_size'] ?? 10);
        $settings['grid_color'] = sanitize_hex_color($_POST['grid_color'] ?? '#e0e0e0');
        $settings['grid_opacity'] = intval($_POST['grid_opacity'] ?? 30);
        $settings['snap_to_grid'] = isset($_POST['snap_to_grid']);
        $settings['snap_to_elements'] = isset($_POST['snap_to_elements']);
        $settings['snap_to_margins'] = isset($_POST['snap_to_margins']);
        $settings['snap_tolerance'] = intval($_POST['snap_tolerance'] ?? 5);
        $settings['show_guides'] = isset($_POST['show_guides']);
        $settings['lock_guides'] = isset($_POST['lock_guides']);

        // Zoom & Navigation
        $settings['default_zoom'] = sanitize_text_field($_POST['default_zoom'] ?? '100');
        $settings['min_zoom'] = intval($_POST['min_zoom'] ?? 10);
        $settings['max_zoom'] = intval($_POST['max_zoom'] ?? 500);
        $settings['zoom_step'] = intval($_POST['zoom_step'] ?? 25);
        $settings['pan_with_mouse'] = isset($_POST['pan_with_mouse']);
        $settings['smooth_zoom'] = isset($_POST['smooth_zoom']);
        $settings['show_zoom_indicator'] = isset($_POST['show_zoom_indicator']);
        $settings['zoom_with_wheel'] = isset($_POST['zoom_with_wheel']);
        $settings['zoom_to_selection'] = isset($_POST['zoom_to_selection']);

        // Sélection & Manipulation
        $settings['show_resize_handles'] = isset($_POST['show_resize_handles']);
        $settings['handle_size'] = intval($_POST['handle_size'] ?? 8);
        $settings['handle_color'] = sanitize_hex_color($_POST['handle_color'] ?? '#007cba');
        $settings['enable_rotation'] = isset($_POST['enable_rotation']);
        $settings['rotation_step'] = intval($_POST['rotation_step'] ?? 15);
        $settings['rotation_snap'] = isset($_POST['rotation_snap']);
        $settings['multi_select'] = isset($_POST['multi_select']);
        $settings['select_all_shortcut'] = isset($_POST['select_all_shortcut']);
        $settings['show_selection_bounds'] = isset($_POST['show_selection_bounds']);
        $settings['copy_paste_enabled'] = isset($_POST['copy_paste_enabled']);
        $settings['duplicate_on_drag'] = isset($_POST['duplicate_on_drag']);

        // Export & Qualité
        $settings['export_quality'] = sanitize_text_field($_POST['export_quality'] ?? 'print');
        $settings['export_format'] = sanitize_text_field($_POST['export_format'] ?? 'pdf');
        $settings['compress_images'] = isset($_POST['compress_images']);
        $settings['image_quality'] = intval($_POST['image_quality'] ?? 85);
        $settings['max_image_size'] = intval($_POST['max_image_size'] ?? 2048);
        $settings['include_metadata'] = isset($_POST['include_metadata']);
        $settings['pdf_author'] = sanitize_text_field($_POST['pdf_author'] ?? '');
        $settings['pdf_subject'] = sanitize_text_field($_POST['pdf_subject'] ?? '');
        $settings['auto_crop'] = isset($_POST['auto_crop']);
        $settings['embed_fonts'] = isset($_POST['embed_fonts']);
        $settings['optimize_for_web'] = isset($_POST['optimize_for_web']);

        // Avancé
        $settings['enable_hardware_acceleration'] = isset($_POST['enable_hardware_acceleration']);
        $settings['limit_fps'] = isset($_POST['limit_fps']);
        $settings['max_fps'] = intval($_POST['max_fps'] ?? 60);
        $settings['auto_save_enabled'] = isset($_POST['auto_save_enabled']);
        $settings['auto_save_interval'] = intval($_POST['auto_save_interval'] ?? 30);
        $settings['auto_save_versions'] = intval($_POST['auto_save_versions'] ?? 10);
        $settings['undo_levels'] = intval($_POST['undo_levels'] ?? 50);
        $settings['redo_levels'] = intval($_POST['redo_levels'] ?? 50);
        $settings['enable_keyboard_shortcuts'] = isset($_POST['enable_keyboard_shortcuts']);
        $settings['debug_mode'] = isset($_POST['debug_mode']);
        $settings['show_fps'] = isset($_POST['show_fps']);

        // Paramètres de notifications
        $settings['email_notifications_enabled'] = isset($_POST['email_notifications_enabled']);
        $settings['notification_events'] = isset($_POST['notification_events']) ? (array) $_POST['notification_events'] : [];

        // Paramètres des rôles autorisés avec validation améliorée
        $new_allowed_roles = isset($_POST['pdf_builder_allowed_roles']) ? array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles']) : [];

        // Validation : s'assurer qu'au moins un rôle est sélectionné
        if (empty($new_allowed_roles)) {
            wp_send_json_error(['message' => __('Erreur: Vous devez sélectionner au moins un rôle pour éviter de bloquer complètement l\'accès à PDF Builder Pro.', 'pdf-builder-pro')]);
            return;
        }

        // Validation : s'assurer que seuls des rôles valides sont sélectionnés
        global $wp_roles;
        $valid_roles = array_keys($wp_roles->roles);
        $invalid_roles = array_diff($new_allowed_roles, $valid_roles);

        if (!empty($invalid_roles)) {
            wp_send_json_error(['message' => sprintf(__('Erreur: Les rôles suivants ne sont pas valides: %s', 'pdf-builder-pro'), implode(', ', $invalid_roles))]);
            return;
        }

        $settings['allowed_roles'] = $new_allowed_roles;

        // Récupérer les anciens rôles pour le logging
        $old_allowed_roles = get_option('pdf_builder_allowed_roles', []);

        // Sauvegarde des paramètres
        update_option('pdf_builder_settings', $settings);

        // Sauvegarde individuelle de tous les paramètres Canvas pour la compatibilité
        foreach ($settings as $key => $value) {
            if ($key !== 'allowed_roles') { // Les rôles sont sauvegardés séparément
                update_option('pdf_builder_' . $key, $value);
            }
        }

        // Sauvegarde séparée pour la compatibilité avec l'ancien système
        update_option('pdf_builder_allowed_roles', $settings['allowed_roles']);

        // Logging des changements de permissions
        if ($old_allowed_roles !== $new_allowed_roles) {
            $this->log_role_permissions_change($old_allowed_roles, $new_allowed_roles);
            // Invalider le cache des permissions pour tous les utilisateurs
            $this->clear_permissions_cache();
        }

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

        // Supprimer les espaces blancs excessifs - ATTENTION: NE PAS utiliser car ça casse le JSON !
        // $cleaned = preg_replace('/\s+/', ' ', $cleaned);

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

    /**
     * Enregistre les changements de permissions des rôles dans les logs
     */
    private function log_role_permissions_change($old_roles, $new_roles) {
        $current_user = wp_get_current_user();
        $user_name = $current_user->display_name;
        $user_id = $current_user->ID;

        // Calculer les différences
        $added_roles = array_diff($new_roles, $old_roles);
        $removed_roles = array_diff($old_roles, $new_roles);

        $log_message = sprintf(
            'PDF Builder: Changement des permissions par %s (ID: %d) - Anciens rôles: [%s], Nouveaux rôles: [%s]',
            $user_name,
            $user_id,
            implode(', ', $old_roles),
            implode(', ', $new_roles)
        );

        if (!empty($added_roles)) {
            $log_message .= sprintf(' - Rôles ajoutés: [%s]', implode(', ', $added_roles));
        }

        if (!empty($removed_roles)) {
            $log_message .= sprintf(' - Rôles supprimés: [%s]', implode(', ', $removed_roles));
        }

        // Log dans le fichier de debug WordPress
        error_log($log_message);

        // Log dans le système de logging du plugin si disponible
        if (method_exists($this, 'get_logger')) {
            $logger = $this->get_logger();
            if ($logger) {
                $logger->info('Role permissions changed', [
                    'user_id' => $user_id,
                    'user_name' => $user_name,
                    'old_roles' => $old_roles,
                    'new_roles' => $new_roles,
                    'added_roles' => array_values($added_roles),
                    'removed_roles' => array_values($removed_roles)
                ]);
            }
        }
    }

    /**
     * Invalide le cache des permissions pour tous les utilisateurs
     * Utile quand les rôles autorisés changent
     */
    private function clear_permissions_cache() {
        global $wpdb;

        // Supprimer tous les transients liés aux permissions PDF Builder
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_pdf_builder_user_access_') . '%'
        ));

        // Supprimer aussi les timeouts
        $wpdb->query($wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like('_transient_timeout_pdf_builder_user_access_') . '%'
        ));

        // Log de l'invalidation du cache
        error_log('PDF Builder: Cache des permissions invalidé suite à un changement des rôles autorisés');
    }

    /**
     * Calcule l'espace disque utilisé par le plugin
     *
     * @return int Espace disque utilisé en octets
     */
    public function get_disk_usage() {
        $total_size = 0;

        // Répertoires à analyser
        $dirs_to_check = [
            PDF_BUILDER_PRO_UPLOADS_DIR,
            PDF_BUILDER_PLUGIN_DIR . 'cache/',
            PDF_BUILDER_PLUGIN_DIR . 'logs/',
        ];

        foreach ($dirs_to_check as $dir) {
            if (is_dir($dir)) {
                $total_size += $this->get_directory_size($dir);
            }
        }

        return $total_size;
    }

    /**
     * Calcule la taille d'un répertoire récursivement
     *
     * @param string $directory Chemin du répertoire
     * @return int Taille en octets
     */
    private function get_directory_size($directory) {
        $size = 0;

        if (!is_dir($directory)) {
            return $size;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $size += $file->getSize();
            }
        }

        return $size;
    }

    /**
     * Compte le nombre de templates PDF
     *
     * @return int Nombre de templates
     */
    public function get_template_count() {
        // Pour l'instant, retourner un compte approximatif basé sur les options
        // TODO: Implémenter un vrai comptage des templates quand le système de templates sera en place
        $templates = get_option('pdf_builder_templates', []);
        return is_array($templates) ? count($templates) : 0;
    }

    /**
     * Gère les actions de maintenance via AJAX
     */
    public function handle_maintenance_action() {
        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Permissions insuffisantes.', 'pdf-builder-pro')));
            return;
        }

        // Vérifier le nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'pdf_builder_maintenance')) {
            wp_send_json_error(array('message' => __('Nonce invalide.', 'pdf-builder-pro')));
            return;
        }

        // Récupérer l'action de maintenance
        $action = isset($_POST['maintenance_action']) ? sanitize_text_field($_POST['maintenance_action']) : '';

        try {
            switch ($action) {
                case 'clear_cache':
                    $result = $this->perform_clear_cache();
                    break;

                case 'clear_temp_files':
                    $result = $this->perform_clear_temp_files();
                    break;

                case 'repair_templates':
                    $result = $this->perform_repair_templates();
                    break;

                case 'reset_settings':
                    $result = $this->perform_reset_settings();
                    break;

                default:
                    wp_send_json_error(array('message' => __('Action de maintenance inconnue.', 'pdf-builder-pro')));
                    return;
            }

            if ($result['success']) {
                wp_send_json_success(array('message' => $result['message']));
            } else {
                wp_send_json_error(array('message' => $result['message']));
            }

        } catch (Exception $e) {
            error_log('PDF Builder Maintenance Error: ' . $e->getMessage());
            wp_send_json_error(array('message' => __('Erreur lors de l\'exécution de l\'action de maintenance.', 'pdf-builder-pro')));
        }
    }

    /**
     * Vide le cache du plugin
     */
    private function perform_clear_cache() {
        $cache_dir = PDF_BUILDER_PLUGIN_DIR . 'cache/';
        $cleared_files = 0;
        $total_size = 0;

        if (is_dir($cache_dir)) {
            $files = glob($cache_dir . '*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $size = filesize($file);
                    if (unlink($file)) {
                        $cleared_files++;
                        $total_size += $size;
                    }
                }
            }
        }

        // Invalider aussi le cache WordPress si nécessaire
        wp_cache_flush();

        return array(
            'success' => true,
            'message' => sprintf(__('Cache vidé avec succès. %d fichiers supprimés, %s libérés.', 'pdf-builder-pro'), $cleared_files, size_format($total_size))
        );
    }

    /**
     * Supprime les fichiers temporaires
     */
    private function perform_clear_temp_files() {
        $temp_dir = sys_get_temp_dir() . '/pdf-builder/';
        $cleared_files = 0;
        $total_size = 0;

        if (is_dir($temp_dir)) {
            $files = glob($temp_dir . '*');
            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < time() - 86400) { // Fichiers de plus de 24h
                    $size = filesize($file);
                    if (unlink($file)) {
                        $cleared_files++;
                        $total_size += $size;
                    }
                }
            }
        }

        return array(
            'success' => true,
            'message' => sprintf(__('Fichiers temporaires nettoyés. %d fichiers supprimés, %s libérés.', 'pdf-builder-pro'), $cleared_files, size_format($total_size))
        );
    }

    /**
     * Répare les templates corrompus
     */
    private function perform_repair_templates() {
        $templates = get_option('pdf_builder_templates', []);
        $repaired_count = 0;

        if (is_array($templates)) {
            foreach ($templates as $key => $template) {
                // Vérifier et réparer la structure des templates
                if (!isset($template['name']) || !isset($template['data'])) {
                    unset($templates[$key]);
                    $repaired_count++;
                }
            }
        }

        update_option('pdf_builder_templates', $templates);

        return array(
            'success' => true,
            'message' => sprintf(__('Templates réparés. %d templates corrompus supprimés.', 'pdf-builder-pro'), $repaired_count)
        );
    }

    /**
     * Réinitialise tous les paramètres aux valeurs par défaut
     */
    private function perform_reset_settings() {
        // Liste des options à réinitialiser
        $options_to_reset = [
            'pdf_builder_settings',
            'pdf_builder_allowed_roles',
            'pdf_builder_templates',
            'pdf_builder_admin_email',
            'pdf_builder_notification_log_level',
            'pdf_builder_default_canvas_width',
            'pdf_builder_default_canvas_height',
            'pdf_builder_show_grid',
            'pdf_builder_snap_to_grid',
            'pdf_builder_snap_to_elements'
        ];

        $reset_count = 0;
        foreach ($options_to_reset as $option) {
            if (delete_option($option)) {
                $reset_count++;
            }
        }

        // Vider le cache
        wp_cache_flush();

        return array(
            'success' => true,
            'message' => sprintf(__('Paramètres réinitialisés avec succès. %d options supprimées.', 'pdf-builder-pro'), $reset_count)
        );
    }

    /**
     * Détecte le type de document basé sur le statut de la commande
     */
    private function detect_document_type($order_status) {
        // Log pour debug
        error_log('PDF Builder: Détection type document pour statut: ' . $order_status);

        // Mapping des statuts WooCommerce vers les types de document
        $status_mapping = [
            'wc-quote' => 'devis',           // Devis
            'wc-quotation' => 'devis',      // Devis (variante)
            'quote' => 'devis',             // Devis (sans préfixe)
            'quotation' => 'devis',         // Devis (sans préfixe)
            'wc-pending' => 'commande',     // En attente
            'wc-processing' => 'commande',  // En cours
            'wc-on-hold' => 'commande',     // En attente
            'wc-completed' => 'facture',    // Terminée -> Facture
            'wc-cancelled' => 'commande',   // Annulée
            'wc-refunded' => 'facture',     // Remboursée -> Facture
            'wc-failed' => 'commande',      // Échec
        ];

        // Retourner le type mappé ou 'commande' par défaut
        $document_type = isset($status_mapping[$order_status]) ? $status_mapping[$order_status] : 'commande';
        error_log('PDF Builder: Type document détecté: ' . $document_type . ' pour statut: ' . $order_status);

        return $document_type;
    }

    /**
     * Retourne le libellé du type de document
     */
    private function get_document_type_label($document_type) {
        $labels = [
            'facture' => __('Facture', 'pdf-builder-pro'),
            'devis' => __('Devis', 'pdf-builder-pro'),
            'commande' => __('Commande', 'pdf-builder-pro'),
            'contrat' => __('Contrat', 'pdf-builder-pro'),
            'bon_livraison' => __('Bon de livraison', 'pdf-builder-pro'),
        ];

        return isset($labels[$document_type]) ? $labels[$document_type] : ucfirst($document_type);
    }

    /**
     * Retourne les mots-clés pour rechercher des templates par type de document
     */
    private function get_document_keywords($document_type) {
        $keywords_mapping = [
            'facture' => ['facture', 'invoice', 'factura'],
            'devis' => ['devis', 'quote', 'quotation', 'cotización', 'presupuesto'],
            'commande' => ['commande', 'order', 'pedido', 'orden'],
            'contrat' => ['contrat', 'contract', 'contrato'],
            'bon_livraison' => ['livraison', 'delivery', 'entrega', 'bon'],
        ];

        return isset($keywords_mapping[$document_type]) ? $keywords_mapping[$document_type] : [$document_type];
    }

    /**
     * Méthodes publiques pour accéder aux modules (pour compatibilité)
     */
    public function get_template_manager() {
        return $this->template_manager;
    }

    public function get_pdf_generator() {
        return $this->pdf_generator;
    }

    public function get_woocommerce_integration() {
        return $this->woocommerce_integration;
    }

    public function get_settings_manager() {
        return $this->settings_manager;
    }

    public function get_diagnostic_manager() {
        return $this->diagnostic_manager;
    }

    /**
     * Page de diagnostic (délégation au manager)
     */
    public function diagnostic_page() {
        return $this->diagnostic_manager->diagnostic_page();
    }

    /**
     * Génère un PDF pour une commande WooCommerce (méthode publique pour l'intégration)
     *
     * @param int $order_id ID de la commande
     * @param int $template_id ID du template (0 pour auto-détection)
     * @return string|WP_Error URL du PDF généré ou erreur
     */
    public function generate_order_pdf($order_id, $template_id = 0) {
        // Désactiver l'affichage des erreurs PHP pour éviter les réponses HTML
        if (!defined('WP_DEBUG') || !WP_DEBUG) {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        error_log('🟡 PDF BUILDER - generate_order_pdf appelée: order_id=' . $order_id . ', template_id=' . $template_id);

        // Vérifier que WooCommerce est actif
        if (!class_exists('WooCommerce')) {
            error_log('❌ PDF BUILDER - WooCommerce non actif');
            return new WP_Error('woocommerce_missing', 'WooCommerce n\'est pas installé ou activé');
        }

        // Vérifier que les fonctions WooCommerce nécessaires existent
        if (!function_exists('wc_get_order')) {
            error_log('❌ PDF BUILDER - Fonction wc_get_order non disponible');
            return new WP_Error('woocommerce_functions_missing', 'Fonction wc_get_order non disponible - WooCommerce mal installé');
        }

        $order = wc_get_order($order_id);
        if (!$order) {
            error_log('❌ PDF BUILDER - Commande non trouvée: ' . $order_id);
            return new WP_Error('order_not_found', 'Commande non trouvée');
        }

        error_log('✅ PDF BUILDER - Commande trouvée: ' . $order->get_order_number());

        // Vérifier que l'objet order a les méthodes nécessaires
        if (!method_exists($order, 'get_id') || !method_exists($order, 'get_total')) {
            error_log('❌ PDF BUILDER - Objet commande invalide');
            return new WP_Error('invalid_order_object', 'Objet commande WooCommerce invalide');
        }

        try {
            error_log('🟡 PDF BUILDER - Chargement du template...');

            // Charger le template de manière robuste
            if ($template_id > 0) {
                $template_data = $this->load_template_robust($template_id);
                error_log('✅ PDF BUILDER - Template chargé depuis database: ' . $template_id);
            } else {
                // Vérifier s'il y a un template spécifique pour le statut de la commande
                $order_status = $order->get_status();
                $status_templates = get_option('pdf_builder_order_status_templates', []);
                $status_key = 'wc-' . $order_status;

                if (isset($status_templates[$status_key]) && $status_templates[$status_key] > 0) {
                    $mapped_template_id = $status_templates[$status_key];
                    $template_data = $this->load_template_robust($mapped_template_id);
                    error_log('✅ PDF BUILDER - Template chargé depuis mapping statut: ' . $mapped_template_id . ' pour statut: ' . $order_status);
                } else {
                    $template_data = $this->get_default_invoice_template();
                    error_log('✅ PDF BUILDER - Template par défaut chargé (pas de mapping trouvé)');
                }
            }

            if (!$template_data) {
                error_log('❌ PDF BUILDER - Échec chargement template');
                return new WP_Error('template_load_failed', 'Impossible de charger le template');
            }

            // Générer le PDF avec les données de la commande
            $pdf_filename = 'order-' . $order_id . '-' . time() . '.pdf';
            error_log('🟡 PDF BUILDER - Génération PDF: ' . $pdf_filename);

            $pdf_path = $this->generate_order_pdf_private($order, $template_data, $pdf_filename);

            if ($pdf_path && file_exists($pdf_path)) {
                $upload_dir = wp_upload_dir();
                $pdf_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $pdf_path);
                error_log('✅ PDF BUILDER - PDF généré avec succès: ' . $pdf_url);
                return $pdf_url;
            } else {
                error_log('❌ PDF BUILDER - Échec génération PDF - fichier non créé: ' . $pdf_path);
                return new WP_Error('pdf_generation_failed', 'Erreur lors de la génération du PDF - fichier non créé');
            }

        } catch (Exception $e) {
            error_log('❌ PDF BUILDER - Exception dans generate_order_pdf: ' . $e->getMessage());
            error_log('❌ PDF BUILDER - Stack trace: ' . $e->getTraceAsString());
            return new WP_Error('generation_exception', 'Erreur inconnue lors de la génération: ' . $e->getMessage());
        } catch (Error $e) {
            error_log('❌ PDF BUILDER - Erreur fatale dans generate_order_pdf: ' . $e->getMessage());
            error_log('❌ PDF BUILDER - Stack trace: ' . $e->getTraceAsString());
            return new WP_Error('generation_error', 'Erreur fatale lors de la génération: ' . $e->getMessage());
        }
    }

    /**
     * Remplacer les variables WooCommerce dans les données du template
     *
     * @param array $template_data Données du template
     * @param array $woocommerce_data Données WooCommerce
     * @return array Template avec variables remplacées
     */
    private function replace_woocommerce_variables($template_data, $woocommerce_data) {
        $processed_data = $template_data;

        // Fonction récursive pour remplacer les variables dans toutes les profondeurs
        $replace_vars = function($data) use ($woocommerce_data, &$replace_vars) {
            if (is_array($data)) {
                $result = [];
                foreach ($data as $key => $value) {
                    $result[$key] = $replace_vars($value);
                }
                return $result;
            } elseif (is_string($data)) {
                // Remplacer les variables du type {order_number}, {customer_name}, etc.
                $replaced = $data;
                foreach ($woocommerce_data as $var => $value) {
                    $replaced = str_replace('{' . $var . '}', $value, $replaced);
                }
                return $replaced;
            } else {
                return $data;
            }
        };

        return $replace_vars($processed_data);
    }

    /**
     * Ajouter l'endpoint de debug
     */
    public function add_debug_endpoint() {
        add_rewrite_rule('^pdf-builder-debug/?$', 'index.php?pdf_builder_debug=1', 'top');
        add_rewrite_rule('^pdf-builder-debug/([^/]*)/?$', 'index.php?pdf_builder_debug=1&debug_action=$matches[1]', 'top');
    }

    /**
     * Gérer l'endpoint de debug
     */
    public function handle_debug_endpoint() {
        if (get_query_var('pdf_builder_debug') == '1') {
            // Vérifier les permissions
            if (!current_user_can('manage_woocommerce')) {
                wp_die('Permissions insuffisantes pour accéder au debug PDF.');
            }

            $debug_action = get_query_var('debug_action', 'metabox');

            if ($debug_action === 'metabox') {
                $this->ajax_debug_pdf_metabox();
            } else {
                wp_die('Action de debug inconnue.');
            }

            exit;
        }
    }

    /**
     * Ajouter les variables de requête pour le debug
     */
    public function add_debug_query_vars($vars) {
        $vars[] = 'pdf_builder_debug';
        $vars[] = 'debug_action';
        return $vars;
    }
}







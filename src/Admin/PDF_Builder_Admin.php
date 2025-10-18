<?php

namespace PDF_Builder\Admin;

// Importer les constantes WordPress globales
use const ABSPATH;
use const ARRAY_A;
use const MINUTE_IN_SECONDS;
use const PDF_BUILDER_DEV_MODE;
use const WP_DEBUG;
use const PDF_PAGE_ORIENTATION;
use const PDF_UNIT;
use const PDF_PAGE_FORMAT;

// Importer les fonctions WordPress globales
use function is_user_logged_in;
use function current_user_can;
use function wp_die;
use function __;
use function get_transient;
use function set_transient;
use function get_option;
use function get_current_user_id;
use function wp_get_current_user;
use function add_action;
use function add_filter;
use function add_menu_page;
use function add_submenu_page;
use function admin_url;
use function wp_enqueue_script;
use function wp_enqueue_style;
use function wp_localize_script;
use function wp_create_nonce;
use function wp_verify_nonce;
use function wp_nonce_field;
use function sanitize_text_field;
use function sanitize_textarea_field;
use function sanitize_hex_color;
use function sanitize_email;
use function update_option;
use function delete_option;
use function add_settings_error;
use function esc_attr;
use function esc_html;
use function esc_url;
use function esc_js;
use function wp_upload_dir;
use function wp_mkdir_p;
use function _e;
use function add_meta_box;
use function get_current_screen;
use function absint;
use function size_format;
use function wp_send_json_success;
use function wp_send_json_error;
use function wp_send_json;
use function current_time;
use function delete_transient;
use function wp_cache_flush;
use function plugin_dir_path;
use function plugin_dir_url;
use function apply_filters;
use function do_action;
use function checked;
use function selected;
use function submit_button;
use function is_admin;
use function wp_add_inline_script;
use function get_query_var;
use function add_rewrite_rule;
use function add_rewrite_tag;
use function home_url;

// Importer les types/classes
use TCPDF;
use TCPDF_STATIC;
use Exception;
use Error;
use WP_Error;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use PDF_Generator;
use PDF_Builder_Cache_Manager;
use PDF_Builder_Pro_Generator;

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

        // Instancier les managers (autoloader gère les inclusions)
        $this->init_managers();

        $this->init_hooks();
    }

    /**
     * Instancier les managers
     */
    private function init_managers() {
        $this->template_manager = new \PDF_Builder_Template_Manager($this->main);
        $this->pdf_generator = new \PDF_Builder_PDF_Generator($this->main);
        $this->woocommerce_integration = new \PDF_Builder_WooCommerce_Integration($this->main);
        $this->settings_manager = new \PDF_Builder_Settings_Manager($this->main);
        $this->diagnostic_manager = new \PDF_Builder_Diagnostic_Manager($this->main);
    }

    /**
     * Vérifie les permissions d'administration
     */
    private function check_admin_permissions() {
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
        $user_roles = $user ? $user->roles : [];
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

        // Hook supplémentaire pour les pages qui chargent du contenu dynamiquement
        add_action('wp_enqueue_scripts', [$this, 'enqueue_admin_scripts_late'], 20);

        // Hooks WooCommerce - Délégation vers le manager
        if (class_exists('WooCommerce')) {
            add_action('add_meta_boxes_shop_order', [$this->woocommerce_integration, 'add_woocommerce_order_meta_box']);
            add_action('add_meta_boxes_woocommerce_page_wc-orders', [$this->woocommerce_integration, 'add_woocommerce_order_meta_box']);
        }

        // Les managers s'occupent de leurs propres hooks AJAX
        // Les hooks AJAX sont maintenant gérés par les managers respectifs

        // Hook AJAX pour le debug PDF metabox
        add_action('wp_ajax_pdf_debug_metabox', [$this, 'ajax_debug_pdf_metabox']);

        // Hook AJAX pour l'aperçu PDF unifié
        add_action('wp_ajax_pdf_builder_unified_preview', [$this, 'ajax_unified_pdf_preview']);

        // Hook AJAX pour servir les aperçus PDF en cache
        add_action('wp_ajax_pdf_builder_serve_preview', [$this, 'ajax_serve_preview']);
        add_action('wp_ajax_nopriv_pdf_builder_serve_preview', [$this, 'ajax_serve_preview']);

        // Hook AJAX pour flush les rewrite rules
        add_action('wp_ajax_pdf_builder_flush_rewrites', [$this, 'ajax_flush_rewrites']);

        // Endpoint public pour servir les aperçus PDF (accessible via GET)
        add_action('init', [$this, 'register_pdf_preview_endpoint']);
        add_action('template_redirect', [$this, 'handle_pdf_preview_request']);

        // Hook AJAX pour sauvegarder les paramètres
        add_action('wp_ajax_pdf_builder_save_settings', [$this, 'ajax_save_settings']);
        add_action('wp_ajax_pdf_builder_save_settings_page', [$this, 'ajax_save_settings_page']);

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
        if ($current_user && $current_user->ID === 1 && defined('PDF_BUILDER_DEV_MODE') && PDF_BUILDER_DEV_MODE) {
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
        include plugin_dir_path(dirname(__FILE__)) . '../templates/admin/templates-page.php';
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
        include plugin_dir_path(dirname(__FILE__)) . '../templates/admin/settings-page.php';
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
        include plugin_dir_path(dirname(__FILE__)) . '../templates/admin/developer-page.php';
    }

    /**
     * Template Editor page (React/TypeScript)
     */
    public function template_editor_page() {

        $this->check_admin_permissions();

        include plugin_dir_path(dirname(__FILE__)) . '../templates/admin/template-editor.php';
    }

    /**
     * Charge les scripts d'administration en retard (pour les pages qui chargent du contenu dynamiquement)
     */
    public function enqueue_admin_scripts_late() {
        // Vérifier si on est dans l'admin et sur la page de l'éditeur
        if (!is_admin() || !isset($_GET['page']) || $_GET['page'] !== 'pdf-builder-editor') {
            return;
        }


        // Charger les scripts comme dans enqueue_admin_scripts
        $this->load_admin_scripts('pdf-builder_page_pdf-builder-editor');
    }

    /**
     * Charge les scripts et styles d'administration
     */
    public function enqueue_admin_scripts($hook) {
        // DEBUG: Log pour vérifier que la méthode est appelée

        // Charger seulement sur nos pages admin
        $allowed_hooks = [
            'toplevel_page_pdf-builder-pro',
            'pdf-builder-pro_page_pdf-builder-templates',
            'pdf-builder-pro_page_pdf-builder-editor',
            'pdf-builder-pro_page_pdf-builder-settings',
            'pdf-builder-pro_page_pdf-builder-diagnostic',
            // 'pdf-builder-pro_page_pdf-builder-test-tcpdf', // Commenté - système de test nettoyé
            'pdf-builder-pro_page_pdf-builder-developer'
        ];


        // Temporairement désactiver la vérification des hooks pour debug
        // if (!in_array($hook, $allowed_hooks)) {
        //     return;
        // }


        $this->load_admin_scripts($hook);
    }

    /**
     * Méthode commune pour charger les scripts admin
     */
    private function load_admin_scripts($hook = null) {

        // DEBUG: Vérifier que les constantes sont définies

        // Vérifier que les fichiers existent
        $admin_js_path = PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js';
        $nonce_fix_path = PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-nonce-fix.js';

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
        // React et ReactDOM sont maintenant bundlés dans le script principal
        // wp_enqueue_script('react');
        // wp_enqueue_script('react-dom', ['react']);

        // Charger d'abord les vendors (React, etc.)
        wp_enqueue_script('pdf-builder-vendors', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/vendors.js', [], '8.0.0_force_' . microtime(true), false);

        wp_enqueue_script('pdf-builder-admin-v3', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-admin.js', ['jquery', 'pdf-builder-vendors'], '8.0.0_force_' . microtime(true), false);

        // DEBUG: Vérifier que le script est enqueued

        // Script de correction de nonce - NOUVEAU HANDLE POUR FORCER LE RECHARGEMENT
        wp_enqueue_script('pdf-builder-nonce-fix-v2', PDF_BUILDER_PRO_ASSETS_URL . 'js/dist/pdf-builder-nonce-fix.js', ['jquery'], '4.0.0_force_reload_' . time(), false);

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
        );

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
        // Récupérer les paramètres canvas depuis le tableau pdf_builder_settings
        $canvas_settings = get_option('pdf_builder_settings', []);
        wp_localize_script('pdf-builder-admin-v3', 'pdfBuilderCanvasSettings', [
            // Paramètres généraux du canvas
            'default_canvas_width' => $canvas_settings['default_canvas_width'] ?? 210,
            'default_canvas_height' => $canvas_settings['default_canvas_height'] ?? 297,
            'default_canvas_unit' => $canvas_settings['default_canvas_unit'] ?? 'mm',
            'default_orientation' => $canvas_settings['default_orientation'] ?? 'portrait',
            'canvas_background_color' => $canvas_settings['canvas_background_color'] ?? '#ffffff',
            'canvas_show_transparency' => $canvas_settings['canvas_show_transparency'] ?? false,
            'container_background_color' => $canvas_settings['container_background_color'] ?? '#f8f9fa',
            'container_show_transparency' => $canvas_settings['container_show_transparency'] ?? false,

            // Marges de sécurité
            'margin_top' => $canvas_settings['margin_top'] ?? 10,
            'margin_right' => $canvas_settings['margin_right'] ?? 10,
            'margin_bottom' => $canvas_settings['margin_bottom'] ?? 10,
            'margin_left' => $canvas_settings['margin_left'] ?? 10,
            'show_margins' => $canvas_settings['show_margins'] ?? true,

            // Paramètres de grille
            'show_grid' => $canvas_settings['show_grid'] ?? true,
            'grid_size' => $canvas_settings['grid_size'] ?? 10,
            'grid_color' => $canvas_settings['grid_color'] ?? '#e0e0e0',
            'grid_opacity' => $canvas_settings['grid_opacity'] ?? 30,

            // Aimantation
            'snap_to_grid' => $canvas_settings['snap_to_grid'] ?? true,
            'snap_to_elements' => $canvas_settings['snap_to_elements'] ?? true,
            'snap_to_margins' => $canvas_settings['snap_to_margins'] ?? true,
            'snap_tolerance' => $canvas_settings['snap_tolerance'] ?? 5,

            // Lignes guides
            'show_guides' => $canvas_settings['show_guides'] ?? true,
            'lock_guides' => $canvas_settings['lock_guides'] ?? false,

            // Paramètres de zoom et navigation
            'default_zoom' => $canvas_settings['default_zoom'] ?? '100',
            'min_zoom' => $canvas_settings['min_zoom'] ?? 10,
            'max_zoom' => $canvas_settings['max_zoom'] ?? 500,
            'zoom_step' => $canvas_settings['zoom_step'] ?? 25,
            'pan_with_mouse' => $canvas_settings['pan_with_mouse'] ?? true,
            'smooth_zoom' => $canvas_settings['smooth_zoom'] ?? true,
            'show_zoom_indicator' => $canvas_settings['show_zoom_indicator'] ?? true,
            'zoom_with_wheel' => $canvas_settings['zoom_with_wheel'] ?? true,
            'zoom_to_selection' => $canvas_settings['zoom_to_selection'] ?? true,

            // Paramètres de sélection et manipulation
            'show_resize_handles' => $canvas_settings['show_resize_handles'] ?? true,
            'handle_size' => $canvas_settings['handle_size'] ?? 8,
            'handle_color' => $canvas_settings['handle_color'] ?? '#007cba',
            'enable_rotation' => $canvas_settings['enable_rotation'] ?? true,
            'rotation_step' => $canvas_settings['rotation_step'] ?? 15,
            'rotation_snap' => $canvas_settings['rotation_snap'] ?? true,
            'multi_select' => $canvas_settings['multi_select'] ?? true,
            'select_all_shortcut' => $canvas_settings['select_all_shortcut'] ?? true,
            'show_selection_bounds' => $canvas_settings['show_selection_bounds'] ?? true,
            'copy_paste_enabled' => $canvas_settings['copy_paste_enabled'] ?? true,
            'duplicate_on_drag' => $canvas_settings['duplicate_on_drag'] ?? false,

            // Paramètres d'export et qualité
            'export_quality' => $canvas_settings['export_quality'] ?? 'print',
            'export_format' => $canvas_settings['export_format'] ?? 'pdf',
            'compress_images' => $canvas_settings['compress_images'] ?? true,
            'image_quality' => $canvas_settings['image_quality'] ?? 85,
            'max_image_size' => $canvas_settings['max_image_size'] ?? 2048,
            'include_metadata' => $canvas_settings['include_metadata'] ?? true,
            'pdf_author' => $canvas_settings['pdf_author'] ?? get_bloginfo('name'),
            'pdf_subject' => $canvas_settings['pdf_subject'] ?? '',
            'auto_crop' => $canvas_settings['auto_crop'] ?? false,
            'embed_fonts' => $canvas_settings['embed_fonts'] ?? true,
            'optimize_for_web' => $canvas_settings['optimize_for_web'] ?? true,

            // Paramètres avancés
            'enable_hardware_acceleration' => $canvas_settings['enable_hardware_acceleration'] ?? true,
            'limit_fps' => $canvas_settings['limit_fps'] ?? true,
            'max_fps' => $canvas_settings['max_fps'] ?? 60,
            'auto_save_enabled' => $canvas_settings['auto_save_enabled'] ?? true,
            'auto_save_interval' => $canvas_settings['auto_save_interval'] ?? 30,
            'auto_save_versions' => $canvas_settings['auto_save_versions'] ?? 10,
            'undo_levels' => $canvas_settings['undo_levels'] ?? 50,
            'redo_levels' => $canvas_settings['redo_levels'] ?? 50,
            'enable_keyboard_shortcuts' => $canvas_settings['enable_keyboard_shortcuts'] ?? true,
            'debug_mode' => $canvas_settings['debug_mode'] ?? false,
            'show_fps' => $canvas_settings['show_fps'] ?? false,

            // Anciens paramètres (pour compatibilité)
            'canvas_element_borders_enabled' => $canvas_settings['canvas_element_borders_enabled'] ?? true,
            'canvas_border_width' => $canvas_settings['canvas_border_width'] ?? 2,
            'canvas_border_color' => $canvas_settings['canvas_border_color'] ?? '#007cba',
            'canvas_border_spacing' => $canvas_settings['canvas_border_spacing'] ?? 2,
            'canvas_resize_handles_enabled' => $canvas_settings['canvas_resize_handles_enabled'] ?? true,
            'canvas_handle_size' => $canvas_settings['canvas_handle_size'] ?? 8,
            'canvas_handle_color' => $canvas_settings['canvas_handle_color'] ?? '#007cba',
            'canvas_handle_hover_color' => $canvas_settings['canvas_handle_hover_color'] ?? '#ffffff',
            'default_text_color' => $canvas_settings['default_text_color'] ?? '#000000',
            'default_background_color' => $canvas_settings['default_background_color'] ?? '#ffffff',
            'default_font_size' => $canvas_settings['default_font_size'] ?? 14
        ]);

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
        $settings['zoom_to_selection'







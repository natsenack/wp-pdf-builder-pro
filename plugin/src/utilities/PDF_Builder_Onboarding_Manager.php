<?php
namespace PDF_Builder\Utilities;
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PDF Builder Pro - Onboarding Manager
 * Gestionnaire d'onboarding et tutoriels
 *
 * @package PDF_Builder
 * @since 1.0.0
 */
if (!defined('ABSPATH')) {
    exit;
}
// Import the Security Manager from global namespace
use \PDF_Builder_Security_Manager;
use Exception;
/**
 * Classe pour gérer l'onboarding et les tutoriels
 */
class PDF_Builder_Onboarding_Manager {
    /**
     * Instance unique (Singleton)
     */
    private static $instance = null;
    /**
     * Options d'onboarding
     */
    private $onboarding_options = [];
    /**
     * Constructeur privé (Singleton)
     */
    private function __construct() {
        // Vérification de sécurité - s'assurer que WordPress est chargé
        if (!defined('ABSPATH')) {
            PDF_Builder_Security_Manager::debug_log('php_errors', 'ABSPATH non défini - WordPress pas chargé');
            return;
        }
        $this->init_hooks();
        $this->load_onboarding_options();
    }
    /**
     * Obtenir l'instance unique avec gestion d'erreur
     */
    public static function get_instance() {
        if (self::$instance === null) {
            try {
                self::$instance = new self();
            } catch (Exception $e) {
                PDF_Builder_Security_Manager::debug_log('php_errors', 'Erreur lors de l\'instanciation: ' . $e->getMessage());
                return null;
            }
        }
        return self::$instance;
    }
    /**
     * Initialiser les hooks
     */
    private function init_hooks() {
        // Vérifier que WordPress est chargé avant d'ajouter les hooks
        if (!function_exists('add_action')) {
            return;
        }
        // Utiliser admin_enqueue_scripts au lieu de admin_init pour une meilleure synchronisation
        add_action('admin_enqueue_scripts', [$this, 'check_onboarding_status']);
        add_action('wp_ajax_pdf_builder_complete_onboarding_step', [$this, 'ajax_complete_onboarding_step']);
        add_action('wp_ajax_pdf_builder_skip_onboarding', [$this, 'ajax_skip_onboarding']);
        add_action('wp_ajax_pdf_builder_reset_onboarding', [$this, 'ajax_reset_onboarding']);
        add_action('wp_ajax_pdf_builder_load_onboarding_step', [$this, 'ajax_load_onboarding_step']);
        add_action('wp_ajax_pdf_builder_save_template_selection', [$this, 'ajax_save_template_selection']);
        add_action('wp_ajax_pdf_builder_save_freemium_mode', [$this, 'ajax_save_freemium_mode']);
        add_action('wp_ajax_pdf_builder_update_onboarding_step', [$this, 'ajax_update_onboarding_step']);
        add_action('wp_ajax_pdf_builder_save_template_assignment', [$this, 'ajax_save_template_assignment']);
        add_action('wp_ajax_pdf_builder_mark_onboarding_complete', [$this, 'ajax_mark_onboarding_complete']);
    }
    /**
     * Charger les options d'onboarding
     */
    private function load_onboarding_options() {
        $this->onboarding_options = pdf_builder_get_option('pdf_builder_onboarding', [
            'completed' => false,
            'current_step' => 0,
            'steps_completed' => [],
            'skipped' => false,
            'first_login' => \current_time('timestamp'),
            'last_activity' => \current_time('timestamp')
        ]);
    }
    /**
     * Sauvegarder les options d'onboarding
     */
    private function save_onboarding_options() {
        pdf_builder_update_option('pdf_builder_onboarding', $this->onboarding_options);
    }


    /**
     * Vérifier le statut d'onboarding (appelé via admin_enqueue_scripts)
     */
    public function check_onboarding_status($hook) {
        // Afficher seulement sur les pages PDF Builder
        if (!in_array($hook, [
            'toplevel_page_pdf-builder-pro',
            'pdf-builder_page_pdf-builder-templates',
            'pdf-builder_page_pdf-builder-settings'
        ])) {
            return;
        }
        // Enqueue les scripts et styles d'onboarding seulement si les fichiers existent
        // TEMPORAIREMENT DÉSACTIVÉ - À RÉIMPLÉMENTER PLUS TARD
        /*
        $onboarding_js = plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/js/onboarding.min.js';
        if (file_exists($onboarding_js)) {
            wp_enqueue_script('pdf-builder-onboarding', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/onboarding.min.js', ['jquery'], PDF_BUILDER_PRO_VERSION, true);
        }
        
        $onboarding_css = plugin_dir_path(dirname(dirname(__FILE__))) . 'assets/css/onboarding-css.min.css';
        if (file_exists($onboarding_css)) {
            wp_enqueue_style('pdf-builder-onboarding', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/css/onboarding-css.min.css', [], PDF_BUILDER_PRO_VERSION);
        }
        */
        
        // TEMPORAIREMENT DÉSACTIVÉ - À RÉIMPLÉMENTER PLUS TARD
        /*
        // Localize le script avec les données AJAX
        wp_localize_script('pdf-builder-onboarding', 'pdfBuilderOnboarding', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_onboarding'),
            'current_step' => $this->get_current_step(),
            'selected_template' => $this->onboarding_options['selected_template'] ?? null,
            'selected_mode' => $this->onboarding_options['selected_mode'] ?? null,
            'total_steps' => count($this->get_onboarding_steps()),
            'strings' => [
                'loading' => __('Chargement...', 'pdf-builder-pro'),
                'error' => __('Erreur', 'pdf-builder-pro'),
                'success' => __('Succès', 'pdf-builder-pro')
            ]
        ]);
        */
        
        // TEMPORAIREMENT DÉSACTIVÉ - À RÉIMPLÉMENTER PLUS TARD
        /*
        // Vérifier le statut d'onboarding
        $completed = $this->is_onboarding_completed();
        $skipped = $this->is_onboarding_skipped();
        // Afficher le wizard seulement si ce n'est ni terminé ni ignoré
        if (!$completed && !$skipped) {
            add_action('admin_footer', [$this, 'render_onboarding_wizard']);
        }
        */
    }
    /**
     * Vérifier si l'onboarding est terminé
     */
    public function is_onboarding_completed() {
        return $this->onboarding_options['completed'];
    }
    /**
     * Vérifier si l'onboarding a été ignoré
     */
    public function is_onboarding_skipped() {
        return $this->onboarding_options['skipped'];
    }
    /**
     * Obtenir l'étape actuelle
     */
    public function get_current_step() {
        $current_step = $this->onboarding_options['current_step'] ?? 1;
        $all_steps = $this->get_onboarding_steps();
        // S'assurer que l'étape actuelle existe dans les étapes disponibles
        if (!isset($all_steps[$current_step])) {
            // Si l'étape n'existe pas, prendre la première étape disponible
            $current_step = min(array_keys($all_steps));
            // Mettre à jour les options pour éviter ce problème à l'avenir
            $this->onboarding_options['current_step'] = $current_step;
            $this->save_onboarding_options();
        }
        return $current_step;
    }
    /**
     * Obtenir toutes les étapes d'onboarding
     */
    public function get_onboarding_steps() {
        $steps = [
            1 => [
                'id' => 'welcome',
                'title' => __('Bienvenue dans PDF Builder Pro', 'pdf-builder-pro'),
                'description' => __('Découvrez le constructeur de PDF professionnel le plus avancé pour WordPress.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('welcome'),
                'action' => __('Suivant', 'pdf-builder-pro'),
                'action_type' => 'next', // Type d'action pour la logique JavaScript
                'can_skip' => false // Ne peut pas être ignorée
            ],
            2 => [
                'id' => 'freemium_mode',
                'title' => __('Choisissez votre mode d\'utilisation', 'pdf-builder-pro'),
                'description' => __('Sélectionnez la version qui correspond à vos besoins.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('freemium_mode'),
                'action' => __('Suivant', 'pdf-builder-pro'),
                'action_type' => 'next',
                'can_skip' => false, // Ne peut pas être ignorée - choix important
                'requires_selection' => true // Nécessite une sélection
            ],
            3 => [
                'id' => 'first_template',
                'title' => __('Choisissez votre template de départ', 'pdf-builder-pro'),
                'description' => __('Sélectionnez un template professionnel pour commencer.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('first_template'),
                'action' => __('Suivant', 'pdf-builder-pro'),
                'action_type' => 'next',
                'can_skip' => true, // Peut être ignorée - passer à l'étape suivante sans sélection
                'skip_text' => __('Ignorer l\'étape', 'pdf-builder-pro'),
                'requires_selection' => true // Nécessite une sélection avant activation du bouton principal
            ],
            4 => [
                'id' => 'assign_template',
                'title' => __('Configurez votre template', 'pdf-builder-pro'),
                'description' => __('Assignez et personnalisez votre template sélectionné.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('assign_template'),
                'action' => __('Suivant', 'pdf-builder-pro'),
                'action_type' => 'next',
                'can_skip' => true, // Peut être configuré plus tard
                'skip_text' => __('Configurer plus tard', 'pdf-builder-pro'),
                'requires_selection' => true // Template doit être sélectionné pour continuer
            ]
        ];
        // Ajouter l'étape WooCommerce seulement si WooCommerce est installé
        // La vérification est différée pour éviter tout chargement prématuré
        // L'étape sera ajoutée dynamiquement plus tard si WooCommerce est disponible
        // Pour l'instant, on termine avec l'étape 'completed' à la position 5

        // Étape finale - toujours présente
        $steps[5] = [
            'id' => 'completed',
            'title' => __('Configuration terminée !', 'pdf-builder-pro'),
            'description' => __('Votre PDF Builder Pro est prêt à être utilisé.', 'pdf-builder-pro'),
            'content' => $this->get_step_content('completed'),
            'action' => __('Commencer à créer', 'pdf-builder-pro'),
            'action_type' => 'finish',
            'can_skip' => false // Dernière étape, ne peut pas être ignorée
        ];

        return $steps;
    }
    /**
     * Obtenir le contenu d'une étape
     */
    private function get_step_content($step_id) {
        switch ($step_id) {
            case 'welcome':
                return '
                    <div class="onboarding-welcome">
                        <div class="welcome-features">
                            <div class="feature-item">
                                <span class="feature-icon">🎨</span>
                                <h4>' . __('Éditeur Visuel Avancé', 'pdf-builder-pro') . '</h4>
                                <p>' . __('Interface drag & drop intuitive avec canvas interactif', 'pdf-builder-pro') . '</p>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">🛒</span>
                                <h4>' . __('Intégration WooCommerce', 'pdf-builder-pro') . '</h4>
                                <p>' . __('Génération automatique de factures, devis et bons de livraison', 'pdf-builder-pro') . '</p>
                            </div>
                            <div class="feature-item">
                                <span class="feature-icon">⚡</span>
                                <h4>' . __('Performance Optimisée', 'pdf-builder-pro') . '</h4>
                                <p>' . __('Rendu rapide et fiable avec aperçu temps réel', 'pdf-builder-pro') . '</p>
                            </div>
                        </div>
                    </div>
                ';
            case 'environment_check':
                $checks = $this->perform_environment_checks();
                $content = '<div class="environment-checks">';
                foreach ($checks as $check) {
                    $status_class = $check['status'] ? 'success' : 'warning';
                    $status_icon = $check['status'] ? '✅' : '⚠️';
                    $content .= '
                        <div class="check-item ' . $status_class . '">
                            <span class="check-icon">' . $status_icon . '</span>
                            <div class="check-content">
                                <h5>' . $check['title'] . '</h5>
                                <p>' . $check['description'] . '</p>
                            </div>
                        </div>
                    ';
                }
                $content .= '</div>';
                return $content;
            case 'freemium_mode':
                return '
                    <div class="freemium-mode-selection">
                        <p>' . __('Choisissez le mode d\'utilisation qui correspond à vos besoins :', 'pdf-builder-pro') . '</p>
                        <div class="mode-options">
                            <div class="mode-card" data-mode="free" data-tooltip="Version gratuite parfaite pour découvrir et tester les fonctionnalités de base">
                                <div class="mode-header">
                                    <span class="mode-icon">🆓</span>
                                    <h4>' . __('Version Gratuite', 'pdf-builder-pro') . '</h4>
                                </div>
                                <div class="mode-features">
                                    <ul>
                                        <li>✅ ' . __('1 template PDF inclus', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('15 variables dynamiques de base', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Export PDF (avec watermark)', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Éditeur de base fonctionnel', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Support communautaire', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Qualité 72 DPI', 'pdf-builder-pro') . '</li>
                                    </ul>
                                </div>
                                <div class="mode-limitations">
                                    <div class="limitation-notice">
                                        <span class="limitation-icon">⚠️</span>
                                        <small>' . __('Limitations : 1 template, watermark sur PDFs, support de base', 'pdf-builder-pro') . '</small>
                                    </div>
                                </div>
                                <div class="mode-price">
                                    <span class="price">0€</span>
                                    <span class="period">' . __('/ mois', 'pdf-builder-pro') . '</span>
                                </div>
                            </div>
                            <div class="mode-card premium" data-mode="premium" data-tooltip="Version complète avec toutes les fonctionnalités avancées et support prioritaire">
                                <div class="mode-header">
                                    <span class="mode-icon">⭐</span>
                                    <h4>' . __('Version Premium', 'pdf-builder-pro') . '</h4>
                                    <span class="popular-badge">' . __('Recommandé', 'pdf-builder-pro') . '</span>
                                </div>
                                <div class="mode-features">
                                    <ul>
                                        <li>✅ ' . __('Templates PDF illimités', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Variables dynamiques avancées', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Exports haute qualité (300 DPI)', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Éditeur React professionnel', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Intégration WooCommerce complète', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Support prioritaire 24/7', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Métriques et analytics détaillés', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Variables conditionnelles', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Exports PNG/JPG', 'pdf-builder-pro') . '</li>
                                        <li>✅ ' . __('Pas de watermark', 'pdf-builder-pro') . '</li>
                                    </ul>
                                </div>
                                <div class="mode-benefits">
                                    <div class="benefit-highlight">
                                        <span class="benefit-icon">🚀</span>
                                        <strong>' . __('Tout débloqué, aucune limitation !', 'pdf-builder-pro') . '</strong>
                                    </div>
                                </div>
                                <div class="mode-price">
                                    <span class="price">79€</span>
                                    <span class="period">' . __('à vie', 'pdf-builder-pro') . '</span>
                                </div>
                            </div>
                        </div>
                        <div class="freemium-notice" style="margin-top:16px;padding:12px;background:#fef3c7;border-left:4px solid #f59e0b;border-radius:4px;">
                            <strong>💡 Note :</strong> Vous pourrez changer de version à tout moment depuis les paramètres du plugin.
                        </div>
                    </div>
                ';
            case 'first_template':
                // Scanner les templates prédéfinis disponibles
                $predefined_templates = $this->get_predefined_templates();
                $template_cards = '';
                foreach ($predefined_templates as $template) {
                    $template_cards .= '
                        <div class="template-card" data-template="' . esc_attr($template['id']) . '" data-tooltip="' . esc_attr($template['description']) . '">
                            <div class="pdfb-template-preview">
                                <span class="template-icon">' . esc_html($template['icon']) . '</span>
                            </div>
                            <h4>' . esc_html($template['name']) . '</h4>
                            <p>' . esc_html($template['short_description']) . '</p>
                        </div>
                    ';
                }
                // Ajouter l'option template vierge
                $template_cards .= '
                    <div class="template-card" data-template="blank" data-tooltip="Canvas vierge pour créer votre propre design personnalisé">
                        <div class="pdfb-template-preview">
                            <span class="template-icon">✨</span>
                        </div>
                        <h4>' . __('Template Vierge', 'pdf-builder-pro') . '</h4>
                        <p>' . __('Commencez depuis zéro', 'pdf-builder-pro') . '</p>
                    </div>
                ';
                return '
                    <div class="first-template-setup">
                        <p>' . __('Choisissez un template de départ pour commencer votre premier PDF :', 'pdf-builder-pro') . '</p>
                        <div class="template-suggestions">
                            ' . $template_cards . '
                        </div>
                        <div class="template-tip" style="margin-top:16px;padding:12px;background:#f0f9ff;border-left:4px solid #3b82f6;border-radius:4px;">
                            <strong>💡 Conseil :</strong> Vous pourrez personnaliser complètement ce template plus tard dans l\'éditeur.
                        </div>
                    </div>
                ';
            case 'assign_template':
                // Récupérer les statuts WooCommerce si disponibles
                $order_statuses = [];
                if (function_exists('\wc_get_order_statuses')) {
                    // @phpstan-ignore-next-line
                    $order_statuses = \wc_get_order_statuses();
                }
                $status_options = '';
                foreach ($order_statuses as $status_key => $status_label) {
                    $status_options .= '<label class="status-option">
                        <input type="checkbox" name="assigned_statuses" value="' . esc_attr($status_key) . '">
                        <span class="status-badge status-' . esc_attr(str_replace('wc-', '', $status_key)) . '">' . esc_html($status_label) . '</span>
                    </label>';
                }
                return '
                    <div class="assign-template-setup">
                        <!-- Aperçu du template sélectionné -->
                        <div class="selected-template-preview">
                            <div class="template-header">
                                <div class="template-icon-large">
                                    <span id="selected-template-icon">📄</span>
                                </div>
                                <div class="template-info">
                                    <h3 id="selected-template-title">Template sélectionné</h3>
                                    <p id="selected-template-description">Aucun template sélectionné</p>
                                </div>
                            </div>
                        </div>
                        <!-- Personnalisation basique -->
                        <div class="template-customization">
                            <h4>✨ Personnalisez votre template</h4>
                            <div class="customization-fields">
                                <div class="field-group">
                                    <label for="template_custom_name">Nom du template</label>
                                    <input type="text" id="template_custom_name" placeholder="Ex: Facture Pro 2025" maxlength="100">
                                </div>
                                <div class="field-group">
                                    <label for="template_custom_description">Description (optionnel)</label>
                                    <textarea id="template_custom_description" placeholder="Décrivez l\'usage de ce template..." maxlength="255" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <!-- Assignation WooCommerce -->
                        <div class="woocommerce-assignment">
                            <h4>🛒 Assignation WooCommerce</h4>
                            <p class="assignment-description">Sélectionnez les statuts de commande pour lesquels ce template sera automatiquement généré :</p>
                            <div class="status-selection">
                                ' . $status_options . '
                            </div>
                            <div class="assignment-notice">
                                <div class="notice-icon">💡</div>
                                <div class="notice-content">
                                    <strong>Configuration automatique :</strong> Le template sera généré automatiquement pour les commandes atteignant ces statuts.
                                </div>
                            </div>
                        </div>
                        <!-- Actions disponibles -->
                        <div class="template-actions">
                            <h4>🎯 Actions disponibles</h4>
                            <div class="action-options">
                                <label class="action-option">
                                    <input type="checkbox" name="template_actions" value="auto_generate" checked>
                                    <div class="option-content">
                                        <strong>Génération automatique</strong>
                                        <span>Créer le PDF automatiquement lors des changements de statut</span>
                                    </div>
                                </label>
                                <label class="action-option">
                                    <input type="checkbox" name="template_actions" value="email_attach" checked>
                                    <div class="option-content">
                                        <strong>Pièce jointe email</strong>
                                        <span>Joindre automatiquement le PDF aux emails WooCommerce</span>
                                    </div>
                                </label>
                                <label class="action-option">
                                    <input type="checkbox" name="template_actions" value="download_link">
                                    <div class="option-content">
                                        <strong>Lien de téléchargement</strong>
                                        <span>Ajouter un lien de téléchargement dans la commande client</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                        <div class="setup-complete-notice">
                            <div class="notice-icon">✅</div>
                            <div class="notice-content">
                                <strong>Configuration terminée !</strong> Votre template est prêt à être utilisé. Vous pourrez le modifier à tout moment depuis l\'éditeur.
                            </div>
                        </div>
                    </div>
                ';
            case 'woocommerce_setup':
                if (\did_action('init') && defined('WC_VERSION')) {
                    return '
                        <div class="woocommerce-setup">
                            <div class="setup-notice success">
                                <span class="notice-icon">✅</span>
                                <div class="notice-content">
                                    <h4>' . __('WooCommerce détecté', 'pdf-builder-pro') . '</h4>
                                    <p>' . __('Votre boutique WooCommerce est prête pour l\'intégration PDF.', 'pdf-builder-pro') . '</p>
                                </div>
                            </div>
                            <div class="setup-section">
                                <h5>' . __('Options d\'intégration', 'pdf-builder-pro') . '</h5>
                                <p class="pdfb-section-description">' . __('Configurez comment PDF Builder Pro s\'intègre avec votre boutique WooCommerce.', 'pdf-builder-pro') . '</p>
                                <div class="integration-options">
                                    <div class="option-card" data-tooltip="Les clients recevront automatiquement leurs PDFs (factures, bons de commande...) joints aux emails de confirmation de commande WooCommerce">
                                        <div class="option-header">
                                            <input type="checkbox" name="woocommerce_emails" checked id="woocommerce_emails">
                                            <label for="woocommerce_emails" class="option-toggle"></label>
                                        </div>
                                        <div class="option-content">
                                            <div class="option-icon">📧</div>
                                            <div class="option-text">
                                                <h6>' . __('PDFs dans les emails de commande', 'pdf-builder-pro') . '</h6>
                                                <p>' . __('Vos clients recevront automatiquement leurs documents PDF (factures, bons de livraison...) directement dans leurs emails de confirmation de commande.', 'pdf-builder-pro') . '</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="option-card" data-tooltip="Aperçu rapide des PDFs générés directement dans l\'interface d\'administration WooCommerce pour chaque commande">
                                        <div class="option-header">
                                            <input type="checkbox" name="admin_preview" checked id="admin_preview">
                                            <label for="admin_preview" class="option-toggle"></label>
                                        </div>
                                        <div class="option-content">
                                            <div class="option-icon">👁️</div>
                                            <div class="option-text">
                                                <h6>' . __('Aperçu PDF dans l\'admin', 'pdf-builder-pro') . '</h6>
                                                <p>' . __('Affichez un bouton d\'aperçu rapide dans l\'interface d\'administration pour visualiser les PDFs générés sans quitter la page de commande.', 'pdf-builder-pro') . '</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="option-card" data-tooltip="Utiliser automatiquement les données de commande WooCommerce (prix, produits, informations client...) dans vos templates PDF">
                                        <div class="option-header">
                                            <input type="checkbox" name="variables" checked id="variables">
                                            <label for="variables" class="option-toggle"></label>
                                        </div>
                                        <div class="option-content">
                                            <div class="option-icon">🔧</div>
                                            <div class="option-text">
                                                <h6>' . __('Variables WooCommerce', 'pdf-builder-pro') . '</h6>
                                                <p>' . __('Activez l\'utilisation automatique des données WooCommerce (prix, produits, adresse client, numéro de commande...) dans vos templates PDF.', 'pdf-builder-pro') . '</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="setup-benefits">
                                <div class="benefit-item">
                                    <span class="benefit-icon">🚀</span>
                                    <span class="benefit-text">' . __('Automatisation complète des documents', 'pdf-builder-pro') . '</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="benefit-icon">⚡</span>
                                    <span class="benefit-text">' . __('Génération instantanée', 'pdf-builder-pro') . '</span>
                                </div>
                                <div class="benefit-item">
                                    <span class="benefit-icon">🎯</span>
                                    <span class="benefit-text">' . __('Expérience client améliorée', 'pdf-builder-pro') . '</span>
                                </div>
                            </div>
                        </div>
                    ';
                } else {
                    return '
                        <div class="woocommerce-setup">
                            <div class="setup-notice info">
                                <span class="notice-icon">ℹ️</span>
                                <div class="notice-content">
                                    <h4>' . __('WooCommerce non détecté', 'pdf-builder-pro') . '</h4>
                                    <p>' . __('Installez WooCommerce pour bénéficier de l\'intégration complète.', 'pdf-builder-pro') . '</p>
                                </div>
                            </div>
                            <div class="setup-actions">
                                <a href="' . admin_url('plugin-install.php?s=woocommerce&tab=search&type=term') . '" class="button button-primary" target="_blank">
                                    ' . __('Installer WooCommerce', 'pdf-builder-pro') . '
                                </a>
                                <button class="button button-secondary skip-woocommerce">
                                    ' . __('Ignorer pour le moment', 'pdf-builder-pro') . '
                                </button>
                            </div>
                        </div>
                    ';
                }
            case 'completed':
                // Récupérer les informations de configuration
                $has_woocommerce = function_exists('pdf_builder_is_woocommerce_active') && pdf_builder_is_woocommerce_active();
                $template_count = count(glob(plugin_dir_path(dirname(__FILE__)) . '../resources/templates/predefined/*.json'));
                $current_user = wp_get_current_user();
                return '
                    <div class="onboarding-completed">
                        <!-- Célébration -->
                        <div class="celebration-header">
                            <div class="celebration-icon">🎉</div>
                            <h3>' . __('Félicitations !', 'pdf-builder-pro') . '</h3>
                            <p class="celebration-subtitle">' . __('Votre PDF Builder Pro est maintenant configuré et prêt à l\'emploi', 'pdf-builder-pro') . '</p>
                        </div>
                        <!-- Résumé de la configuration -->
                        <div class="configuration-summary">
                            <h4>' . __('📋 Résumé de votre configuration', 'pdf-builder-pro') . '</h4>
                            <div class="summary-grid">
                                <div class="summary-card">
                                    <div class="card-icon">📄</div>
                                    <div class="card-content">
                                        <h5>' .
                                        // translators: %d: number of available templates
                                        sprintf(__('%d Templates disponibles', 'pdf-builder-pro'), $template_count) . '</h5>
                                        <p>' . __('Templates professionnels prêts à utiliser', 'pdf-builder-pro') . '</p>
                                    </div>
                                </div>
                                <div class="summary-card ' . ($has_woocommerce ? 'active' : 'inactive') . '">
                                    <div class="card-icon">' . ($has_woocommerce ? '🛒' : '❌') . '</div>
                                    <div class="card-content">
                                        <h5>' . ($has_woocommerce ? __('WooCommerce intégré', 'pdf-builder-pro') : __('WooCommerce non détecté', 'pdf-builder-pro')) . '</h5>
                                        <p>' . ($has_woocommerce ? __('Vos PDFs seront automatiquement joints aux emails', 'pdf-builder-pro') : __('Installez WooCommerce pour une intégration automatique', 'pdf-builder-pro')) . '</p>
                                    </div>
                                </div>
                                <div class="summary-card active">
                                    <div class="card-icon">⚡</div>
                                    <div class="card-content">
                                        <h5>' . __('Éditeur React activé', 'pdf-builder-pro') . '</h5>
                                        <p>' . __('Interface moderne et intuitive pour créer vos PDFs', 'pdf-builder-pro') . '</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Actions rapides -->
                        <div class="quick-actions">
                            <h4>' . __('🚀 Commencez dès maintenant', 'pdf-builder-pro') . '</h4>
                            <div class="actions-grid">
                                <a href="' . admin_url('admin.php?page=pdf-builder-templates') . '" class="action-card primary">
                                    <div class="action-icon">🎨</div>
                                    <div class="action-content">
                                        <h5>' . __('Créer un nouveau PDF', 'pdf-builder-pro') . '</h5>
                                        <p>' . __('Utilisez l\'éditeur visuel pour concevoir votre document', 'pdf-builder-pro') . '</p>
                                    </div>
                                    <div class="action-arrow">→</div>
                                </a>
                                <a href="' . admin_url('admin.php?page=pdf-builder-settings') . '" class="action-card secondary">
                                    <div class="action-icon">⚙️</div>
                                    <div class="action-content">
                                        <h5>' . __('Configurer les paramètres', 'pdf-builder-pro') . '</h5>
                                        <p>' . __('Ajustez les options générales et les intégrations', 'pdf-builder-pro') . '</p>
                                    </div>
                                    <div class="action-arrow">→</div>
                                </a>
                                <a href="' . admin_url('edit.php?post_type=pdf_template') . '" class="action-card secondary">
                                    <div class="action-icon">📁</div>
                                    <div class="action-content">
                                        <h5>' . __('Gérer les templates', 'pdf-builder-pro') . '</h5>
                                        <p>' . __('Modifiez ou dupliquez vos templates existants', 'pdf-builder-pro') . '</p>
                                    </div>
                                    <div class="action-arrow">→</div>
                                </a>
                            </div>
                        </div>
                        <!-- Ressources et support -->
                        <div class="resources-section">
                            <h4>' . __('📚 Ressources et support', 'pdf-builder-pro') . '</h4>
                            <div class="resources-grid">
                                <div class="resource-item">
                                    <div class="resource-icon">📖</div>
                                    <div class="resource-content">
                                        <h6>' . __('Documentation complète', 'pdf-builder-pro') . '</h6>
                                        <p>' . __('Guides détaillés et tutoriels vidéo', 'pdf-builder-pro') . '</p>
                                        <a href="#" class="resource-link">' . __('Consulter la doc', 'pdf-builder-pro') . ' →</a>
                                    </div>
                                </div>
                                <div class="resource-item">
                                    <div class="resource-icon">💬</div>
                                    <div class="resource-content">
                                        <h6>' . __('Support technique', 'pdf-builder-pro') . '</h6>
                                        <p>' . __('Notre équipe est là pour vous aider', 'pdf-builder-pro') . '</p>
                                        <a href="#" class="resource-link">' . __('Contacter le support', 'pdf-builder-pro') . ' →</a>
                                    </div>
                                </div>
                                <div class="resource-item">
                                    <div class="resource-icon">🎓</div>
                                    <div class="resource-content">
                                        <h6>' . __('Webinaires gratuits', 'pdf-builder-pro') . '</h6>
                                        <p>' . __('Apprenez les meilleures pratiques', 'pdf-builder-pro') . '</p>
                                        <a href="#" class="resource-link">' . __('Voir le planning', 'pdf-builder-pro') . ' →</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Feedback -->
                        <div class="feedback-section">
                            <div class="feedback-content">
                                <div class="feedback-icon">👍</div>
                                <div class="feedback-text">
                                    <h5>' . __('Votre avis compte !', 'pdf-builder-pro') . '</h5>
                                    <p>' . __('Aidez-nous à améliorer PDF Builder Pro en partageant votre expérience.', 'pdf-builder-pro') . '</p>
                                    <div class="feedback-actions">
                                        <button class="feedback-btn positive" onclick="this.innerHTML=\'Merci pour votre retour ! ⭐\'">
                                            ' . __('J\'adore !', 'pdf-builder-pro') . '
                                        </button>
                                        <button class="feedback-btn suggestion" onclick="this.innerHTML=\'Suggestion notée ! 💡\'">
                                            ' . __('Une suggestion ?', 'pdf-builder-pro') . '
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Footer avec bienvenue personnalisée -->
                        <div class="welcome-footer">
                            <p class="welcome-message">
                                ' . /* translators: %s: user display name */ sprintf(__('Bienvenue dans la communauté PDF Builder Pro, %s !', 'pdf-builder-pro'), $current_user->display_name) . '
                            </p>
                            <p class="welcome-tip">
                                💡 ' . __('Astuce : Utilisez Ctrl+S (Cmd+S sur Mac) pour sauvegarder automatiquement vos modifications.', 'pdf-builder-pro') . '
                            </p>
                        </div>
                    </div>
                ';
            default:
                return '';
        }
    }
    /**
     * Effectuer les vérifications d'environnement
     */
    private function perform_environment_checks() {
        $checks = [];
        // Vérification PHP
        $checks[] = [
            'title' => __('Version PHP', 'pdf-builder-pro'),
            // translators: %s: current PHP version number
            'description' => sprintf(__('Version actuelle : %s (Minimum requis : 7.4)', 'pdf-builder-pro'), PHP_VERSION),
            'status' => version_compare(PHP_VERSION, '7.4', '>=')
        ];
        // Vérification WordPress
        global $wp_version;
        $checks[] = [
            'title' => __('Version WordPress', 'pdf-builder-pro'),
            // translators: %s: current WordPress version number
            'description' => sprintf(__('Version actuelle : %s (Minimum requis : 5.0)', 'pdf-builder-pro'), $wp_version),
            'status' => version_compare($wp_version, '5.0', '>=')
        ];
        // Vérification WooCommerce
        $checks[] = [
            'title' => __('WooCommerce', 'pdf-builder-pro'),
            'description' => function_exists('pdf_builder_is_woocommerce_active') && pdf_builder_is_woocommerce_active() ?
                __('WooCommerce détecté et compatible', 'pdf-builder-pro') :
                __('WooCommerce non détecté - Installation recommandée', 'pdf-builder-pro'),
            'status' => function_exists('pdf_builder_is_woocommerce_active') && pdf_builder_is_woocommerce_active()
        ];
        // Vérification mémoire
        $memory_limit = ini_get('memory_limit');
        $memory_bytes = wp_convert_hr_to_bytes($memory_limit);
        $checks[] = [
            'title' => __('Mémoire PHP', 'pdf-builder-pro'),
            // translators: %s: PHP memory limit value (e.g. 128M)
            'description' => sprintf(__('Limite actuelle : %s (Recommandé : 128M)', 'pdf-builder-pro'), $memory_limit),
            'status' => $memory_bytes >= 134217728 // 128M
        ];
        // Vérification permissions écriture
        $upload_dir = wp_upload_dir();
        $writable = is_writable($upload_dir['basedir']); // phpcs:ignore WordPress.WP.AlternativeFunctions
        $checks[] = [
            'title' => __('Permissions d\'écriture', 'pdf-builder-pro'),
            'description' => $writable ?
                __('Le dossier uploads est accessible en écriture', 'pdf-builder-pro') :
                __('Problème de permissions sur le dossier uploads', 'pdf-builder-pro'),
            'status' => $writable
        ];
        return $checks;
    }
    /**
     * Rendre le wizard d'onboarding
     */
    public function render_onboarding_wizard() {
        $steps = $this->get_onboarding_steps();
        // Vérifier si une étape spécifique est demandée via URL
        $forced_step = isset($_GET['pdf_onboarding_step']) ? \intval($_GET['pdf_onboarding_step']) : null;
        if ($forced_step && $forced_step >= 1 && $forced_step <= count($steps)) {
            // Forcer l'étape et sauvegarder
            $this->onboarding_options['current_step'] = $forced_step;
            $this->save_onboarding_options();
            $current_step = $forced_step;
        } else {
            $current_step = $this->get_current_step() ?: 1;
        }
        $current_step_data = isset($steps[$current_step]) ? $steps[$current_step] : $steps[1];
        ?>
        <div id="pdf-builder-onboarding-modal" class="pdf-builder-onboarding-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <?php if ($current_step > 1) : ?>
                    <button class="button button-previous">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                    </button>
                    <?php endif; ?>
                    <div class="progress-indicator">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo intval(($current_step / count($steps)) * 100); ?>%"></div>
                        </div>
                        <div class="progress-text">Étape <?php echo intval($current_step); ?> sur <?php echo intval(count($steps)); ?></div>
                        <div class="progress-steps">
                            <?php for ($i = 1; $i <= count($steps); $i++): ?>
                                <div class="progress-step <?php echo $i < $current_step ? 'completed' : ($i === $current_step ? 'active' : ''); ?>"
                                     data-step="<?php echo intval($i); ?>"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <button class="modal-close" data-action="skip-onboarding" data-tooltip="Quitter l'assistant">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="step-content">
                        <?php echo $this->render_step_content($current_step_data, $current_step); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Internal HTML method ?>
                    </div>
                </div>
                <div class="modal-footer">
                        <?php if ($current_step_data['can_skip']): ?>
                        <button class="button button-secondary" data-action="skip-step">
                            <?php echo esc_html($current_step_data['skip_text'] ?? __('Ignorer', 'pdf-builder-pro')); ?>
                        </button>
                        <?php else: ?>
                        <button class="button button-secondary" data-action="skip-onboarding">
                            <?php esc_html_e('Ignorer l\'assistant', 'pdf-builder-pro'); ?>
                        </button>
                        <?php endif; ?>
                        <?php if ($current_step_data['action']): ?>
                        <button class="button button-primary complete-step" 
                                data-step="<?php echo intval($current_step); ?>" 
                                data-action-type="<?php echo esc_attr($current_step_data['action_type']); ?>"
                                <?php echo ($current_step_data['requires_selection'] ?? false) ? 'disabled' : ''; ?>>
                            <?php echo esc_html($current_step_data['action']); ?>
                        </button>
                        <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    /**
     * Charger les scripts d'onboarding
     */
    public function enqueue_onboarding_scripts($hook) {
        // Charger seulement sur les pages pertinentes
        if (!in_array($hook, [
            'toplevel_page_pdf-builder-pro',
            'pdf-builder_page_pdf-builder-templates',
            'pdf-builder_page_pdf-builder-settings'
        ])) {
            return;
        }
        // Charger le CSS d'onboarding
        wp_enqueue_style(
            'pdf-builder-onboarding',
            PDF_BUILDER_PRO_ASSETS_URL . 'css/onboarding.css',
            [],
            PDF_BUILDER_PRO_VERSION
        );
        wp_enqueue_script(
            'pdf-builder-onboarding',
            PDF_BUILDER_PRO_ASSETS_URL . 'js/onboarding.js',
            ['jquery'],
            PDF_BUILDER_PRO_VERSION,
            true
        );
        wp_localize_script('pdf-builder-onboarding', 'pdfBuilderOnboarding', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_onboarding'),
            'strings' => [
                'confirm_skip' => __('Êtes-vous sûr de vouloir ignorer l\'assistant de configuration ?', 'pdf-builder-pro'),
                'step_completed' => __('Étape terminée !', 'pdf-builder-pro'),
                'onboarding_completed' => __('Configuration terminée !', 'pdf-builder-pro')
            ]
        ]);
    }
    /**
     * AJAX - Compléter une étape d'onboarding
     */
    public function ajax_complete_onboarding_step() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $step = \intval($_POST['step']);
        $action = sanitize_text_field($_POST['step_action'] ?? '');
        $selected_template = sanitize_text_field($_POST['selected_template'] ?? '');
        // Validation des étapes avant de passer à la suivante
        $validation_error = $this->validate_step_completion($step, $action);
        if ($validation_error) {
            wp_send_json_error(['message' => $validation_error]);
            return;
        }
        $this->onboarding_options['steps_completed'][] = $step;
        // Calculer la prochaine étape en fonction des étapes disponibles
        $all_steps = $this->get_onboarding_steps();
        $max_step = max(array_keys($all_steps));
        // Ne pas incrémenter au-delà de la dernière étape disponible
        if ($step < $max_step) {
            $this->onboarding_options['current_step'] = $step + 1;
        }
        $this->onboarding_options['last_activity'] = \current_time('timestamp');
        // Nettoyer la redirection précédente
        unset($this->onboarding_options['redirect_to']);
        // Actions spécifiques selon l'étape
        $all_steps = $this->get_onboarding_steps();
        $current_step_data = $all_steps[$step] ?? null;
        if ($current_step_data) {
            switch ($current_step_data['id']) {
                case 'freemium_mode':
                    if (!empty($_POST['selected_mode'])) {
                        $this->onboarding_options['selected_mode'] = sanitize_text_field($_POST['selected_mode']);
                    } else {
                    }
                    break;
                case 'first_template':
                    if (!empty($_POST['selected_template'])) {
                        $this->onboarding_options['selected_template'] = sanitize_text_field($_POST['selected_template']);
                    } else {
                    }
                    break;
                case 'assign_template':
                    // Sauvegarder les préférences d'assignation si fournies
                    if (isset($_POST['template_usage'])) {
                        $this->onboarding_options['template_usage'] = sanitize_text_field($_POST['template_usage']);
                    }
                    break;
                case 'woocommerce_setup':
                    // Sauvegarder les préférences WooCommerce
                    if (isset($_POST['woocommerce_options'])) {
                        $woocommerce_options = $this->sanitizeWooCommerceOptions($_POST['woocommerce_options']);
                        pdf_builder_update_option('pdf_builder_woocommerce_integration', $woocommerce_options);
                    }
                    break;
                case 'completed':
                    $this->onboarding_options['completed'] = true;
                    $this->onboarding_options['completed_at'] = \current_time('timestamp');
                    // Rediriger vers l'éditeur après completion de l'onboarding
                    $this->onboarding_options['redirect_to'] = admin_url('admin.php?page=pdf-builder-react-editor');
                    break;
                default:
                    break;
            }
        }
        $this->save_onboarding_options();
        $response = [
            'next_step' => $this->onboarding_options['current_step'],
            'completed' => $this->onboarding_options['completed'],
            'redirect_to' => $this->onboarding_options['redirect_to'] ?? null
        ];
        wp_send_json_success($response);
    }
    /**
     * AJAX - Sauvegarder la sélection de template
     */
    public function ajax_save_template_selection() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $selected_template = sanitize_text_field($_POST['selected_template'] ?? '');
        $this->onboarding_options['selected_template'] = $selected_template;
        $this->onboarding_options['last_activity'] = \current_time('timestamp');
        $this->save_onboarding_options();
        wp_send_json_success();
    }
    /**
     * AJAX - Sauvegarder le mode freemium sélectionné
     */
    public function ajax_save_freemium_mode() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $selected_mode = sanitize_text_field($_POST['selected_mode'] ?? '');
        // Valider que le mode est valide
        if (!in_array($selected_mode, ['free', 'premium'])) {
            wp_send_json_error(__('Mode invalide', 'pdf-builder-pro'));
            return;
        }
        $this->onboarding_options['selected_mode'] = $selected_mode;
        $this->onboarding_options['last_activity'] = \current_time('timestamp');
        $this->save_onboarding_options();
        wp_send_json_success();
    }
    /**
     * AJAX - Mettre à jour l'étape actuelle
     */
    public function ajax_update_onboarding_step() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $step = \intval($_POST['step']);
        $this->onboarding_options['current_step'] = $step;
        $this->onboarding_options['last_activity'] = \current_time('timestamp');
        $this->save_onboarding_options();
        wp_send_json_success();
    }
    /**
     * AJAX - Marquer l'onboarding comme terminé
     */
    public function ajax_mark_onboarding_complete() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $this->onboarding_options['completed'] = true;
        $this->onboarding_options['completed_at'] = \current_time('timestamp');
        $this->save_onboarding_options();
        wp_send_json_success();
    }
    /**
     * Valider la completion d'une étape
     */
    private function validate_step_completion($step, $action) {
        $all_steps = $this->get_onboarding_steps();
        // Vérifier si l'étape existe
        if (!isset($all_steps[$step])) {
            return __('Étape inconnue.', 'pdf-builder-pro');
        }
        $step_data = $all_steps[$step];
        switch ($step_data['id']) {
            case 'welcome': // Welcome - toujours valide
                return null;
            case 'freemium_mode': // Freemium mode - doit avoir sélectionné un mode
                if (empty($_POST['selected_mode'])) {
                    return __('Veuillez sélectionner un mode d\'utilisation.', 'pdf-builder-pro');
                }
                return null;
            case 'first_template': // First template - doit avoir sélectionné un template
                if (empty($_POST['selected_template'])) {
                    return __('Veuillez sélectionner un template.', 'pdf-builder-pro');
                }
                return null;
            case 'assign_template': // Template assignment - toujours valide
                return null;
            case 'woocommerce_setup': // WooCommerce setup - toujours valide (optionnel)
                return null;
            case 'completed': // Completed - toujours valide
                return null;
            default:
                return __('Étape inconnue.', 'pdf-builder-pro');
        }
    }
    /**
     * AJAX - Ignorer l'onboarding
     */
    public function ajax_skip_onboarding() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $this->onboarding_options['skipped'] = true;
        $this->onboarding_options['skipped_at'] = \current_time('timestamp');
        $this->save_onboarding_options();
        wp_send_json_success();
    }
    /**
     * AJAX - Réinitialiser l'onboarding
     */
    public function ajax_reset_onboarding() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $this->onboarding_options = [
            'completed' => false,
            'current_step' => 0,
            'steps_completed' => [],
            'skipped' => false,
            'first_login' => \current_time('timestamp'),
            'last_activity' => \current_time('timestamp'),
            'reset_at' => \current_time('timestamp')
        ];
        $this->save_onboarding_options();
        wp_send_json_success();
    }
    /**
     * Générer le contenu HTML d'une étape
     */
    private function render_step_content($step_data, $step_number) {
        ob_start();
        ?>
        <div class="onboarding-step-content" data-step-id="<?php echo esc_attr($step_data['id']); ?>">
            <div class="step-header">
                <h2><?php echo esc_html($step_data['title']); ?></h2>
                <p class="step-description"><?php echo esc_html($step_data['description']); ?></p>
            </div>
            <div class="step-body">
                <?php echo wp_kses_post($step_data['content']); ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    /**
     * AJAX - Charger le contenu d'une étape d'onboarding
     */
    public function ajax_load_onboarding_step() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $step = \intval($_POST['step']);
        $steps = $this->get_onboarding_steps();
        if (!isset($steps[$step])) {
            wp_send_json_error(__('Étape non trouvée', 'pdf-builder-pro'));
        }
        $step_data = $steps[$step];
        // Générer le contenu HTML de l'étape
        $html = $this->render_step_content($step_data, $step);
        wp_send_json_success([
            'step' => $step,
            'title' => $step_data['title'],
            'description' => $step_data['description'],
            'content' => $html,
            'action' => $step_data['action'],
            'action_type' => $step_data['action_type'] ?? 'next',
            'can_skip' => $step_data['can_skip'] ?? false,
            'skip_text' => $step_data['skip_text'] ?? __('Ignorer', 'pdf-builder-pro'),
            'requires_selection' => $step_data['requires_selection'] ?? false,
            'auto_advance' => $step_data['auto_advance'] ?? false,
            'auto_advance_delay' => $step_data['auto_advance_delay'] ?? 3000
        ]);
    }
    /**
     * Reset l'onboarding (méthode publique pour usage externe)
     */
    public function reset_onboarding() {
        if (!current_user_can('manage_options')) {
            return false;
        }
        $this->onboarding_options = [
            'completed' => false,
            'current_step' => 0,
            'steps_completed' => [],
            'skipped' => false,
            'first_login' => \current_time('timestamp'),
            'last_activity' => \current_time('timestamp'),
            'reset_at' => \current_time('timestamp')
        ];
        $this->save_onboarding_options();
        return true;
    }
    /**
     * Obtenir les statistiques d'onboarding
     */
    public function get_onboarding_stats() {
        return [
            'completed' => $this->is_onboarding_completed(),
            'skipped' => $this->is_onboarding_skipped(),
            'current_step' => $this->get_current_step(),
            'steps_completed' => count($this->onboarding_options['steps_completed']),
            'total_steps' => count($this->get_onboarding_steps()),
            'first_login' => $this->onboarding_options['first_login'],
            'last_activity' => $this->onboarding_options['last_activity']
        ];
    }
    /**
     * AJAX handler pour sauvegarder l'assignation de template
     */
    public function ajax_save_template_assignment() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }
        $assignment_data = json_decode(stripslashes($_POST['assignment_data']), true);
        if (!$assignment_data || !isset($assignment_data['template_id'])) {
            wp_send_json_error(__('Données d\'assignation invalides', 'pdf-builder-pro'));
        }
        // Sauvegarder les données d'assignation
        $this->onboarding_options['template_assignment'] = [
            'template_id' => sanitize_text_field($assignment_data['template_id']),
            'custom_name' => sanitize_text_field($assignment_data['custom_name'] ?? ''),
            'custom_description' => sanitize_textarea_field($assignment_data['custom_description'] ?? ''),
            'assigned_statuses' => array_map('sanitize_text_field', $assignment_data['assigned_statuses'] ?? []),
            'template_actions' => array_map('sanitize_text_field', $assignment_data['template_actions'] ?? []),
            'assigned_at' => \current_time('mysql')
        ];
        // Marquer l'étape 4 comme complétée
        if (!in_array(4, $this->onboarding_options['steps_completed'])) {
            $this->onboarding_options['steps_completed'][] = 4;
        }
        // Sauvegarder les options
        pdf_builder_update_option('pdf_builder_onboarding', $this->onboarding_options);
        // Créer une configuration WooCommerce si nécessaire
        if (!empty($assignment_data['assigned_statuses'])) {
            $this->create_woocommerce_template_config($assignment_data);
        }
        wp_send_json_success([
            'message' => __('Configuration de template sauvegardée avec succès', 'pdf-builder-pro'),
            'assignment' => $this->onboarding_options['template_assignment']
        ]);
    }
    /**
     * Créer la configuration WooCommerce pour le template
     */
    private function create_woocommerce_template_config($assignment_data) {
        if (!function_exists('pdf_builder_is_woocommerce_active') || !pdf_builder_is_woocommerce_active()) {
            return;
        }
        // Récupérer ou créer les options WooCommerce
        $wc_options = pdf_builder_get_option('pdf_builder_woocommerce', []);
        // Configuration pour les statuts assignés
        foreach ($assignment_data['assigned_statuses'] as $status) {
            $clean_status = str_replace('wc-', '', $status);
            if (!isset($wc_options[$clean_status])) {
                $wc_options[$clean_status] = [
                    'enabled' => true,
                    'template_id' => $assignment_data['template_id'],
                    'custom_name' => $assignment_data['custom_name'] ?: $assignment_data['template_id'],
                    'auto_generate' => in_array('auto_generate', $assignment_data['template_actions']),
                    'email_attach' => in_array('email_attach', $assignment_data['template_actions']),
                    'download_link' => in_array('download_link', $assignment_data['template_actions']),
                    'created_by_onboarding' => true,
                    'created_at' => \current_time('mysql')
                ];
            }
        }
        pdf_builder_update_option('pdf_builder_woocommerce', $wc_options);
    }
    /**
     * Obtenir la liste des templates prédéfinis disponibles
     */
    private function get_predefined_templates() {
        $templates = [];
        $template_dir = plugin_dir_path(dirname(__FILE__)) . '../resources/templates/predefined/';
        // Scanner les fichiers .json dans le dossier predefined
        $template_files = glob($template_dir . '*.json');
        foreach ($template_files as $file_path) {
            $filename = basename($file_path, '.json');
            // Essayer de lire le fichier JSON pour extraire les métadonnées
            $template_data = json_decode(file_get_contents($file_path), true);
            if ($template_data && isset($template_data['metadata'])) {
                $metadata = $template_data['metadata'];
                $templates[] = [
                    'id' => $filename,
                    'name' => $metadata['name'] ?? $this->format_template_name($filename),
                    'description' => $metadata['description'] ?? __('Template professionnel prêt à l\'emploi', 'pdf-builder-pro'),
                    'short_description' => $metadata['short_description'] ?? __('Template prédéfini', 'pdf-builder-pro'),
                    'icon' => $metadata['icon'] ?? '📄',
                    'category' => $metadata['category'] ?? 'general'
                ];
            } else {
                // Fallback si pas de métadonnées
                $templates[] = [
                    'id' => $filename,
                    'name' => $this->format_template_name($filename),
                    'description' => __('Template professionnel prêt à l\'emploi', 'pdf-builder-pro'),
                    'short_description' => __('Template prédéfini', 'pdf-builder-pro'),
                    'icon' => '📄',
                    'category' => 'general'
                ];
            }
        }
        return $templates;
    }
    /**
     * Formater le nom d'un template depuis son filename
     */
    private function format_template_name($filename) {
        // Convertir les tirets et underscores en espaces, puis capitaliser
        $name = str_replace(['-', '_'], ' ', $filename);
        $name = ucwords($name);
        return $name;
    }

    /**
     * Sanitize WooCommerce options array
     */
    private function sanitizeWooCommerceOptions($options)
    {
        if (!is_array($options)) {
            return array();
        }

        $sanitized = array();
        foreach ($options as $key => $value) {
            $sanitized_key = sanitize_key($key);
            if (is_array($value)) {
                $sanitized[$sanitized_key] = $this->sanitizeWooCommerceOptions($value);
            } elseif (is_string($value)) {
                $sanitized[$sanitized_key] = sanitize_text_field($value);
            } elseif (is_numeric($value)) {
                $sanitized[$sanitized_key] = $value;
            } elseif (is_bool($value)) {
                $sanitized[$sanitized_key] = $value;
            }
        }
        return $sanitized;
    }
}







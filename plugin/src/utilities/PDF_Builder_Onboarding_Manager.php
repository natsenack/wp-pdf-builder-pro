<?php
/**
 * PDF Builder Pro - Onboarding Manager
 * Gestionnaire d'onboarding et tutoriels
 *
 * @package PDF_Builder_Pro
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

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
        $this->init_hooks();
        $this->load_onboarding_options();
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
        // Utiliser admin_enqueue_scripts au lieu de admin_init pour une meilleure synchronisation
        add_action('admin_enqueue_scripts', [$this, 'check_onboarding_status']);
        add_action('wp_ajax_pdf_builder_complete_onboarding_step', [$this, 'ajax_complete_onboarding_step']);
        add_action('wp_ajax_pdf_builder_skip_onboarding', [$this, 'ajax_skip_onboarding']);
        add_action('wp_ajax_pdf_builder_reset_onboarding', [$this, 'ajax_reset_onboarding']);
    }

    /**
     * Charger les options d'onboarding
     */
    private function load_onboarding_options() {
        $this->onboarding_options = get_option('pdf_builder_onboarding', [
            'completed' => false,
            'current_step' => 0,
            'steps_completed' => [],
            'skipped' => false,
            'first_login' => current_time('timestamp'),
            'last_activity' => current_time('timestamp')
        ]);
    }

    /**
     * Sauvegarder les options d'onboarding
     */
    private function save_onboarding_options() {
        update_option('pdf_builder_onboarding', $this->onboarding_options);
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

        // Enqueue les scripts et styles d'onboarding
        wp_enqueue_script('pdf-builder-onboarding', PDF_BUILDER_PRO_ASSETS_URL . 'js/onboarding.js', ['jquery'], PDF_BUILDER_PRO_VERSION, true);
        wp_enqueue_style('pdf-builder-onboarding', PDF_BUILDER_PRO_ASSETS_URL . 'css/onboarding.css', [], PDF_BUILDER_PRO_VERSION);

        // Localize le script avec les données AJAX
        wp_localize_script('pdf-builder-onboarding', 'pdfBuilderOnboarding', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_onboarding'),
            'current_step' => $this->get_current_step(),
            'strings' => [
                'loading' => __('Chargement...', 'pdf-builder-pro'),
                'error' => __('Erreur', 'pdf-builder-pro'),
                'success' => __('Succès', 'pdf-builder-pro')
            ]
        ]);

        // Afficher le wizard seulement si ce n'est ni terminé ni ignoré
        if (!$this->is_onboarding_completed() && !$this->is_onboarding_skipped()) {
            add_action('admin_footer', [$this, 'render_onboarding_wizard']);
        }
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
        return $this->onboarding_options['current_step'];
    }

    /**
     * Obtenir toutes les étapes d'onboarding
     */
    public function get_onboarding_steps() {
        return [
            1 => [
                'id' => 'welcome',
                'title' => __('Bienvenue dans PDF Builder Pro', 'pdf-builder-pro'),
                'description' => __('Découvrez le constructeur de PDF professionnel le plus avancé pour WordPress.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('welcome'),
                'action' => __('Commencer', 'pdf-builder-pro')
            ],
            2 => [
                'id' => 'environment_check',
                'title' => __('Vérification de l\'environnement', 'pdf-builder-pro'),
                'description' => __('Nous analysons votre installation pour optimiser l\'expérience.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('environment_check'),
                'action' => __('Continuer', 'pdf-builder-pro')
            ],
            3 => [
                'id' => 'first_template',
                'title' => __('Créez votre premier template', 'pdf-builder-pro'),
                'description' => __('Lancez-vous avec un template professionnel prêt à l\'emploi.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('first_template'),
                'action' => __('Créer un template', 'pdf-builder-pro')
            ],
            4 => [
                'id' => 'woocommerce_setup',
                'title' => __('Configuration WooCommerce', 'pdf-builder-pro'),
                'description' => __('Intégrez vos PDFs dans vos commandes WooCommerce.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('woocommerce_setup'),
                'action' => __('Configurer', 'pdf-builder-pro')
            ],
            5 => [
                'id' => 'completed',
                'title' => __('Configuration terminée !', 'pdf-builder-pro'),
                'description' => __('Votre PDF Builder Pro est prêt à être utilisé.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('completed'),
                'action' => __('Commencer à créer', 'pdf-builder-pro')
            ]
        ];
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

            case 'first_template':
                return '
                    <div class="first-template-setup">
                        <p>' . __('Choisissez un template de départ pour commencer votre premier PDF :', 'pdf-builder-pro') . '</p>
                        <div class="template-suggestions">
                            <div class="template-card" data-template="invoice" data-tooltip="Template professionnel avec en-têtes, tableau des articles et calculs automatiques">
                                <div class="template-preview">
                                    <span class="template-icon">📄</span>
                                </div>
                                <h4>' . __('Facture', 'pdf-builder-pro') . '</h4>
                                <p>' . __('Template professionnel pour factures', 'pdf-builder-pro') . '</p>
                            </div>
                            <div class="template-card" data-template="quote" data-tooltip="Template élégant avec conditions, validité et signature électronique">
                                <div class="template-preview">
                                    <span class="template-icon">📋</span>
                                </div>
                                <h4>' . __('Devis', 'pdf-builder-pro') . '</h4>
                                <p>' . __('Template élégant pour devis', 'pdf-builder-pro') . '</p>
                            </div>
                            <div class="template-card" data-template="blank" data-tooltip="Canvas vierge pour créer votre propre design personnalisé">
                                <div class="template-preview">
                                    <span class="template-icon">✨</span>
                                </div>
                                <h4>' . __('Template Vierge', 'pdf-builder-pro') . '</h4>
                                <p>' . __('Commencez depuis zéro', 'pdf-builder-pro') . '</p>
                            </div>
                        </div>
                        <div class="template-tip" style="margin-top:16px;padding:12px;background:#f0f9ff;border-left:4px solid #3b82f6;border-radius:4px;">
                            <strong>💡 Conseil :</strong> Vous pourrez personnaliser complètement ce template plus tard dans l\'éditeur.
                        </div>
                    </div>
                ';

            case 'woocommerce_setup':
                if (class_exists('WooCommerce')) {
                    return '
                        <div class="woocommerce-setup">
                            <div class="setup-notice success">
                                <span class="notice-icon">✅</span>
                                <div class="notice-content">
                                    <h4>' . __('WooCommerce détecté', 'pdf-builder-pro') . '</h4>
                                    <p>' . __('Votre boutique WooCommerce est prête pour l\'intégration PDF.', 'pdf-builder-pro') . '</p>
                                </div>
                            </div>
                            <div class="setup-options">
                                <h5>' . __('Options d\'intégration :', 'pdf-builder-pro') . '</h5>
                                <label class="option-item" data-tooltip="Les clients recevront automatiquement leurs PDFs avec leurs emails de confirmation">
                                    <input type="checkbox" name="woocommerce_emails" checked> ' . __('Ajouter les PDFs aux emails de commande', 'pdf-builder-pro') . '
                                </label>
                                <label class="option-item" data-tooltip="Aperçu rapide des PDFs générés directement dans l\'interface admin">
                                    <input type="checkbox" name="admin_preview" checked> ' . __('Afficher l\'aperçu PDF dans l\'admin', 'pdf-builder-pro') . '
                                </label>
                                <label class="option-item" data-tooltip="Utiliser automatiquement les données de commande (prix, produits, client...)">
                                    <input type="checkbox" name="variables" checked> ' . __('Activer les variables WooCommerce', 'pdf-builder-pro') . '
                                </label>
                            </div>
                            <div class="woocommerce-tip" style="margin-top:16px;padding:12px;background:#f0fdf4;border-left:4px solid #10b981;border-radius:4px;">
                                <strong>🚀 Avantage :</strong> Vos clients recevront leurs factures automatiquement avec chaque commande !
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
                return '
                    <div class="onboarding-completed">
                        <div class="completion-summary">
                            <div class="summary-item">
                                <span class="summary-icon">🎯</span>
                                <h4>' . __('Étape suivante', 'pdf-builder-pro') . '</h4>
                                <p>' . __('Allez dans Templates PDF pour créer votre premier document', 'pdf-builder-pro') . '</p>
                                <a href="' . admin_url('admin.php?page=pdf-builder-templates') . '" class="button button-primary">
                                    ' . __('Voir les Templates', 'pdf-builder-pro') . '
                                </a>
                            </div>
                            <div class="summary-item">
                                <span class="summary-icon">📚</span>
                                <h4>' . __('Documentation', 'pdf-builder-pro') . '</h4>
                                <p>' . __('Consultez notre guide d\'utilisation complet', 'pdf-builder-pro') . '</p>
                                <a href="' . admin_url('admin.php?page=pdf-builder-settings') . '" class="button button-secondary">
                                    ' . __('Voir les Paramètres', 'pdf-builder-pro') . '
                                </a>
                            </div>
                        </div>
                        <div class="completion-tips">
                            <h5>💡 Conseils pour bien démarrer :</h5>
                            <ul>
                                <li>' . __('Utilisez l\'éditeur React pour une expérience optimale', 'pdf-builder-pro') . '</li>
                                <li>' . __('Les templates prédéfinis vous feront gagner du temps', 'pdf-builder-pro') . '</li>
                                <li>' . __('N\'hésitez pas à sauvegarder régulièrement votre travail', 'pdf-builder-pro') . '</li>
                            </ul>
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
            'description' => sprintf(__('Version actuelle : %s (Minimum requis : 7.4)', 'pdf-builder-pro'), PHP_VERSION),
            'status' => version_compare(PHP_VERSION, '7.4', '>=')
        ];

        // Vérification WordPress
        global $wp_version;
        $checks[] = [
            'title' => __('Version WordPress', 'pdf-builder-pro'),
            'description' => sprintf(__('Version actuelle : %s (Minimum requis : 5.0)', 'pdf-builder-pro'), $wp_version),
            'status' => version_compare($wp_version, '5.0', '>=')
        ];

        // Vérification WooCommerce
        $checks[] = [
            'title' => __('WooCommerce', 'pdf-builder-pro'),
            'description' => class_exists('WooCommerce') ?
                __('WooCommerce détecté et compatible', 'pdf-builder-pro') :
                __('WooCommerce non détecté - Installation recommandée', 'pdf-builder-pro'),
            'status' => class_exists('WooCommerce')
        ];

        // Vérification mémoire
        $memory_limit = ini_get('memory_limit');
        $memory_bytes = wp_convert_hr_to_bytes($memory_limit);
        $checks[] = [
            'title' => __('Mémoire PHP', 'pdf-builder-pro'),
            'description' => sprintf(__('Limite actuelle : %s (Recommandé : 128M)', 'pdf-builder-pro'), $memory_limit),
            'status' => $memory_bytes >= 134217728 // 128M
        ];

        // Vérification permissions écriture
        $upload_dir = wp_upload_dir();
        $writable = is_writable($upload_dir['basedir']);
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
        $current_step = $this->get_current_step() ?: 1;
        $current_step_data = isset($steps[$current_step]) ? $steps[$current_step] : $steps[1];

        ?>
        <div id="pdf-builder-onboarding-modal" class="pdf-builder-modal-overlay" style="display: block;">
            <div class="pdf-builder-modal pdf-builder-onboarding-modal">
                <div class="modal-header">
                    <div class="progress-container">
                        <div class="step-indicators">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <div class="step-indicator <?php echo $i < $current_step ? 'completed' : ($i === $current_step ? 'active' : ''); ?>"
                                     data-step="<?php echo $i; ?>"
                                     data-tooltip="Étape <?php echo $i; ?>">
                                </div>
                            <?php endfor; ?>
                        </div>
                        <div class="onboarding-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo (min($current_step, 5) / 5) * 100; ?>%"></div>
                            </div>
                            <span class="progress-text">
                                <?php printf(__('Étape %d sur %d', 'pdf-builder-pro'), $current_step, count($steps)); ?>
                            </span>
                        </div>
                    </div>

                    <button class="onboarding-help-btn" data-tooltip="Aide et raccourcis clavier (Ctrl+H)">
                        <span class="dashicons dashicons-editor-help"></span>
                    </button>

                    <button class="modal-close" data-action="skip-onboarding" data-tooltip="Quitter l'assistant">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="onboarding-step" data-step="<?php echo $current_step; ?>">
                        <div class="step-header">
                            <h2><?php echo esc_html($current_step_data['title']); ?></h2>
                            <p><?php echo esc_html($current_step_data['description']); ?></p>
                        </div>

                        <div class="step-content">
                            <?php echo $current_step_data['content']; ?>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="button button-secondary" data-action="skip-onboarding" data-tooltip="Ignorer l'assistant et aller directement à PDF Builder">
                        <?php _e('Ignorer', 'pdf-builder-pro'); ?>
                    </button>
                    <button class="button button-primary" data-action="next-step" data-step="<?php echo $current_step; ?>" data-tooltip="<?php echo esc_attr($current_step_data['action']); ?>">
                        <?php echo esc_html($current_step_data['action']); ?>
                    </button>
                </div>
            </div>
        </div>

        <style>
            .pdf-builder-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.7);
                z-index: 100000;
                display: none;
                animation: fadeIn 0.3s ease;
            }

            .pdf-builder-modal {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: white;
                border-radius: 12px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow: hidden;
                animation: slideIn 0.3s ease;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translate(-50%, -60%);
                }
                to {
                    opacity: 1;
                    transform: translate(-50%, -50%);
                }
            }

            .modal-header {
                padding: 20px 24px;
                border-bottom: 1px solid #e1e1e1;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .onboarding-progress {
                flex: 1;
            }

            .progress-bar {
                height: 4px;
                background: #e1e1e1;
                border-radius: 2px;
                margin-bottom: 8px;
                overflow: hidden;
            }

            .progress-fill {
                height: 100%;
                background: #2271b1;
                transition: width 0.3s ease;
            }

            .progress-text {
                font-size: 12px;
                color: #666;
            }

            .modal-close {
                background: none;
                border: none;
                cursor: pointer;
                padding: 4px;
                border-radius: 4px;
                color: #666;
                transition: all 0.2s;
            }

            .modal-close:hover {
                background: #f0f0f0;
                color: #333;
            }

            .modal-body {
                padding: 24px;
                max-height: 400px;
                overflow-y: auto;
            }

            .step-header h2 {
                margin: 0 0 8px 0;
                font-size: 24px;
                font-weight: 600;
                color: #1d2327;
            }

            .step-header p {
                margin: 0 0 20px 0;
                color: #666;
                font-size: 16px;
            }

            .modal-footer {
                padding: 20px 24px;
                border-top: 1px solid #e1e1e1;
                display: flex;
                justify-content: space-between;
                gap: 12px;
            }

            /* Styles spécifiques aux étapes */
            .welcome-features {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-top: 20px;
            }

            .feature-item {
                text-align: center;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
            }

            .feature-icon {
                font-size: 32px;
                display: block;
                margin-bottom: 12px;
            }

            .environment-checks {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .check-item {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 12px;
                border-radius: 8px;
                background: #f8f9fa;
            }

            .check-item.success {
                background: #d1edff;
                border-left: 4px solid #2271b1;
            }

            .check-item.warning {
                background: #fff3cd;
                border-left: 4px solid #f39c12;
            }

            .template-suggestions {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 16px;
                margin-top: 20px;
            }

            .template-card {
                border: 2px solid #e1e1e1;
                border-radius: 8px;
                padding: 16px;
                text-align: center;
                cursor: pointer;
                transition: all 0.2s;
            }

            .template-card:hover {
                border-color: #2271b1;
                background: #f8f9fa;
            }

            .template-card.selected {
                border-color: #2271b1;
                background: #e6f3ff;
            }

            .template-preview {
                width: 60px;
                height: 60px;
                background: #f0f0f0;
                border-radius: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin: 0 auto 12px;
                font-size: 24px;
            }

            .completion-summary {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 24px;
            }

            .summary-item {
                text-align: center;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
            }

            .summary-icon {
                font-size: 32px;
                display: block;
                margin-bottom: 12px;
            }

            .completion-tips {
                background: #e6f3ff;
                padding: 16px;
                border-radius: 8px;
                border-left: 4px solid #2271b1;
            }

            .completion-tips ul {
                margin: 8px 0 0 0;
                padding-left: 20px;
            }

            .completion-tips li {
                margin-bottom: 4px;
            }
        </style>
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
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        $step = intval($_POST['step']);
        $action = sanitize_text_field($_POST['step_action'] ?? '');

        $this->onboarding_options['steps_completed'][] = $step;
        $this->onboarding_options['current_step'] = $step + 1;
        $this->onboarding_options['last_activity'] = current_time('timestamp');

        // Actions spécifiques selon l'étape
        switch ($step) {
            case 3: // First template
                if ($action === 'create_template') {
                    // Rediriger vers l'éditeur
                    $this->onboarding_options['redirect_to'] = admin_url('admin.php?page=pdf-builder-react-editor');
                }
                break;

            case 4: // WooCommerce setup
                // Sauvegarder les préférences WooCommerce
                if (isset($_POST['woocommerce_options'])) {
                    update_option('pdf_builder_woocommerce_integration', $_POST['woocommerce_options']);
                }
                break;

            case 5: // Completed
                $this->onboarding_options['completed'] = true;
                $this->onboarding_options['completed_at'] = current_time('timestamp');
                break;
        }

        $this->save_onboarding_options();

        wp_send_json_success([
            'next_step' => $this->onboarding_options['current_step'],
            'completed' => $this->onboarding_options['completed'],
            'redirect_to' => $this->onboarding_options['redirect_to'] ?? null
        ]);
    }

    /**
     * AJAX - Ignorer l'onboarding
     */
    public function ajax_skip_onboarding() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        $this->onboarding_options['skipped'] = true;
        $this->onboarding_options['skipped_at'] = current_time('timestamp');
        $this->save_onboarding_options();

        wp_send_json_success();
    }

    /**
     * AJAX - Réinitialiser l'onboarding
     */
    public function ajax_reset_onboarding() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        $this->onboarding_options = [
            'completed' => false,
            'current_step' => 0,
            'steps_completed' => [],
            'skipped' => false,
            'first_login' => current_time('timestamp'),
            'last_activity' => current_time('timestamp'),
            'reset_at' => current_time('timestamp')
        ];
        $this->save_onboarding_options();

        wp_send_json_success();
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
}
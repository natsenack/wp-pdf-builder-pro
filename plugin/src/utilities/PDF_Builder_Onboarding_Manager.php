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
        add_action('wp_ajax_pdf_builder_load_onboarding_step', [$this, 'ajax_load_onboarding_step']);
        add_action('wp_ajax_pdf_builder_save_template_selection', [$this, 'ajax_save_template_selection']);
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
            'selected_template' => $this->onboarding_options['selected_template'] ?? null,
            'strings' => [
                'loading' => __('Chargement...', 'pdf-builder-pro'),
                'error' => __('Erreur', 'pdf-builder-pro'),
                'success' => __('Succès', 'pdf-builder-pro')
            ]
        ]);

        // Vérifier le statut d'onboarding
        $completed = $this->is_onboarding_completed();
        $skipped = $this->is_onboarding_skipped();

        // Afficher le wizard seulement si ce n'est ni terminé ni ignoré
        if (!$completed && !$skipped) {
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
                'id' => 'first_template',
                'title' => __('Créez votre premier template', 'pdf-builder-pro'),
                'description' => __('Lancez-vous avec un template professionnel prêt à l\'emploi.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('first_template'),
                'action' => __('Continuer', 'pdf-builder-pro')
            ],
            3 => [
                'id' => 'woocommerce_setup',
                'title' => __('Configuration WooCommerce', 'pdf-builder-pro'),
                'description' => __('Intégrez vos PDFs dans vos commandes WooCommerce.', 'pdf-builder-pro'),
                'content' => $this->get_step_content('woocommerce_setup'),
                'action' => __('Configurer', 'pdf-builder-pro')
            ],
            4 => [
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
        error_log('PDF_Builder_Onboarding_Manager::render_onboarding_wizard: Starting render');
        $steps = $this->get_onboarding_steps();

        // Vérifier si une étape spécifique est demandée via URL
        $forced_step = isset($_GET['pdf_onboarding_step']) ? intval($_GET['pdf_onboarding_step']) : null;
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
                    <button class="button button-previous" data-tooltip="Étape précédente">
                        <span class="dashicons dashicons-arrow-left-alt"></span>
                    </button>
                    <?php endif; ?>

                    <div class="progress-indicator">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo (($current_step / 4) * 100); ?>%"></div>
                        </div>
                        <div class="progress-text">Étape <?php echo $current_step; ?> sur 4</div>
                    </div>

                    <button class="modal-close" data-action="skip-onboarding" data-tooltip="Quitter l'assistant">
                        <span class="dashicons dashicons-no"></span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="step-content">
                        <?php echo $this->render_step_content($current_step_data, $current_step); ?>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="button button-secondary" data-action="skip-onboarding">
                        <?php _e('Ignorer', 'pdf-builder-pro'); ?>
                    </button>
                    <?php if ($current_step_data['action']): ?>
                    <button class="button button-primary complete-step" data-step="<?php echo $current_step; ?>">
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
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        $step = intval($_POST['step']);
        $action = sanitize_text_field($_POST['step_action'] ?? '');

        // Validation des étapes avant de passer à la suivante
        $validation_error = $this->validate_step_completion($step, $action);
        if ($validation_error) {
            wp_send_json_error(['message' => $validation_error]);
            return;
        }

        $this->onboarding_options['steps_completed'][] = $step;
        $this->onboarding_options['current_step'] = $step + 1;
        $this->onboarding_options['last_activity'] = current_time('timestamp');

        // Actions spécifiques selon l'étape
        switch ($step) {
            case 2: // First template
                if ($action === 'create_template') {
                    // Sauvegarder le template sélectionné
                    $this->onboarding_options['selected_template'] = sanitize_text_field($_POST['selected_template'] ?? '');
                    // Rediriger vers l'éditeur
                    $this->onboarding_options['redirect_to'] = admin_url('admin.php?page=pdf-builder-react-editor');
                }
                break;

            case 3: // WooCommerce setup
                // Sauvegarder les préférences WooCommerce
                if (isset($_POST['woocommerce_options'])) {
                    update_option('pdf_builder_woocommerce_integration', $_POST['woocommerce_options']);
                }
                break;

            case 4: // Completed
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
     * AJAX - Sauvegarder la sélection de template
     */
    public function ajax_save_template_selection() {
        check_ajax_referer('pdf_builder_onboarding', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        $selected_template = sanitize_text_field($_POST['selected_template'] ?? '');

        $this->onboarding_options['selected_template'] = $selected_template;
        $this->onboarding_options['last_activity'] = current_time('timestamp');
        $this->save_onboarding_options();

        wp_send_json_success();
    }

    /**
     * Valider la completion d'une étape
     */
    private function validate_step_completion($step, $action) {
        switch ($step) {
            case 1: // Welcome - toujours valide
                return null;

            case 2: // First template - doit avoir sélectionné un template
                if ($action !== 'create_template') {
                    return __('Veuillez sélectionner un template avant de continuer.', 'pdf-builder-pro');
                }
                if (empty($_POST['selected_template'])) {
                    return __('Aucun template sélectionné.', 'pdf-builder-pro');
                }
                return null;

            case 3: // WooCommerce setup - toujours valide (optionnel)
                return null;

            case 4: // Completed - toujours valide
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
                <?php echo $step_data['content']; ?>
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
            wp_die(__('Permissions insuffisantes', 'pdf-builder-pro'));
        }

        $step = intval($_POST['step']);
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
            'first_login' => current_time('timestamp'),
            'last_activity' => current_time('timestamp'),
            'reset_at' => current_time('timestamp')
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
}
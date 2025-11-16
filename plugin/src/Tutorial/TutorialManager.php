<?php

namespace WP_PDF_Builder_Pro\Tutorial;

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

/**
 * PDF Builder Tutorial Manager
 * Système de tutoriels intégré
 */
class TutorialManager
{
    private static $instance = null;

    /**
     * Instance unique
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialisation
     */
    public function init()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueueTutorialAssets']);
        add_action('wp_ajax_pdf_builder_tutorial_progress', [$this, 'handleTutorialProgress']);
        add_action('wp_ajax_pdf_builder_skip_tutorial', [$this, 'handleSkipTutorial']);

        // Hooks pour afficher les tooltips
        add_action('pdf_builder_editor_loaded', [$this, 'addEditorTooltips']);
        add_action('pdf_builder_settings_loaded', [$this, 'addSettingsTooltips']);

        // Hook pour afficher le wizard de bienvenue sur les pages du plugin
        add_action('admin_footer', [$this, 'maybeShowWelcomeWizard']);

        // Ajouter le bouton d'aide dans la barre d'admin
        add_action('admin_bar_menu', [$this, 'addTutorialHelpButton'], 100);
    }

    /**
     * Ajouter un bouton d'aide dans la barre d'admin WordPress
     */
    public function addTutorialHelpButton($wp_admin_bar)
    {
        // Vérifier si on est sur une page du plugin
        $current_screen = get_current_screen();
        if (!$current_screen || (strpos($current_screen->id, 'pdf-builder') === false && strpos($current_screen->id, 'woocommerce') === false)) {
            return;
        }

        $wp_admin_bar->add_node([
            'id' => 'pdf-builder-tutorial-help',
            'title' => '<span class="ab-icon dashicons dashicons-editor-help"></span> ' . __('Aide PDF Builder', 'pdf-builder-pro'),
            'href' => '#',
            'meta' => [
                'onclick' => 'window.pdfBuilderTutorialManager.showWelcomeWizard(); return false;',
                'title' => __('Relancer le tutoriel d\'introduction', 'pdf-builder-pro')
            ]
        ]);
    }

    /**
     * Charger les assets des tutoriels
     */
    public function enqueueTutorialAssets($hook)
    {
        // Charger seulement sur les pages du plugin
        if (strpos($hook, 'pdf-builder') === false && strpos($hook, 'woocommerce') === false) {
            return;
        }

        error_log('PDF Builder Tutorial: Loading tutorial assets for hook: ' . $hook);

        wp_enqueue_style(
            'pdf-builder-tutorial',
            plugin_dir_url(__FILE__) . '../../assets/css/tutorial.css',
            [],
            '1.0.0'
        );

        wp_enqueue_script(
            'pdf-builder-tutorial',
            plugin_dir_url(__FILE__) . '../../assets/js/tutorial.js',
            ['jquery'],
            '1.0.0',
            true
        );

        wp_localize_script('pdf-builder-tutorial', 'pdfBuilderTutorial', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_tutorial_nonce'),
            'currentUserId' => get_current_user_id(),
            'tutorials' => $this->getTutorialsData()
        ]);

        error_log('PDF Builder Tutorial: Assets enqueued successfully');
    }

    /**
     * Données des tutoriels
     */
    public function getTutorialsData()
    {
        return [
            'welcome' => [
                'title' => __('Bienvenue dans PDF Builder Pro', 'pdf-builder-pro'),
                'description' => __('Découvrez comment créer vos premiers PDFs personnalisés', 'pdf-builder-pro'),
                'steps' => [
                    [
                        'target' => '.pdf-builder-editor',
                        'title' => __('L\'éditeur PDF', 'pdf-builder-pro'),
                        'content' => __('Ceci est votre éditeur PDF. Vous pouvez y ajouter du texte, des images et des éléments.', 'pdf-builder-pro'),
                        'position' => 'bottom'
                    ],
                    [
                        'target' => '.element-palette',
                        'title' => __('Palette d\'éléments', 'pdf-builder-pro'),
                        'content' => __('Utilisez ces éléments pour construire votre PDF : texte, images, formes, etc.', 'pdf-builder-pro'),
                        'position' => 'right'
                    ],
                    [
                        'target' => '.preview-button',
                        'title' => __('Aperçu', 'pdf-builder-pro'),
                        'content' => __('Cliquez ici pour voir un aperçu de votre PDF avant de le générer.', 'pdf-builder-pro'),
                        'position' => 'top'
                    ]
                ]
            ],
            'premium_features' => [
                'title' => __('Fonctionnalités Premium', 'pdf-builder-pro'),
                'description' => __('Découvrez les avantages de la version premium', 'pdf-builder-pro'),
                'steps' => [
                    [
                        'target' => '.premium-badge',
                        'title' => __('Badge Premium', 'pdf-builder-pro'),
                        'content' => __('Cette fonctionnalité est disponible uniquement en version premium.', 'pdf-builder-pro'),
                        'position' => 'bottom'
                    ]
                ]
            ]
        ];
    }

    /**
     * Ajouter les tooltips dans l'éditeur
     */
    public function addEditorTooltips()
    {
        if (!$this->shouldShowTutorial('editor_basics')) {
            return;
        }

        ?>
        <div class="tutorial-tooltip" data-tutorial="editor_basics" data-step="0" style="display: none;">
            <div class="tutorial-tooltip-header">
                <h4><?php _e('Bienvenue dans l\'éditeur PDF', 'pdf-builder-pro'); ?></h4>
                <button class="tutorial-close">&times;</button>
            </div>
            <div class="tutorial-tooltip-content">
                <p><?php _e('L\'éditeur PDF vous permet de créer des documents personnalisés avec du texte, des images et des éléments WooCommerce.', 'pdf-builder-pro'); ?></p>
            </div>
            <div class="tutorial-tooltip-footer">
                <button class="tutorial-prev" disabled><?php _e('Précédent', 'pdf-builder-pro'); ?></button>
                <span class="tutorial-progress">1 / 3</span>
                <button class="tutorial-next"><?php _e('Suivant', 'pdf-builder-pro'); ?></button>
            </div>
        </div>
        <?php
    }

    /**
     * Ajouter les tooltips dans les paramètres
     */
    public function addSettingsTooltips()
    {
        if (!$this->shouldShowTutorial('settings_overview')) {
            return;
        }

        ?>
        <div class="tutorial-tooltip" data-tutorial="settings_overview" data-step="0" style="display: none;">
            <div class="tutorial-tooltip-header">
                <h4><?php _e('Configuration PDF Builder', 'pdf-builder-pro'); ?></h4>
                <button class="tutorial-close">&times;</button>
            </div>
            <div class="tutorial-tooltip-content">
                <p><?php _e('Configurez les paramètres généraux de génération PDF, la qualité d\'image et les options d\'intégration.', 'pdf-builder-pro'); ?></p>
            </div>
            <div class="tutorial-tooltip-footer">
                <button class="tutorial-skip"><?php _e('Passer le tutoriel', 'pdf-builder-pro'); ?></button>
                <button class="tutorial-start"><?php _e('Commencer', 'pdf-builder-pro'); ?></button>
            </div>
        </div>
        <?php
    }

    /**
     * Vérifier si un tutoriel doit être affiché
     */
    private function shouldShowTutorial($tutorial_id)
    {
        $user_id = get_current_user_id();
        $completed_tutorials = get_user_meta($user_id, 'pdf_builder_completed_tutorials', true);
        $skipped_tutorials = get_user_meta($user_id, 'pdf_builder_skipped_tutorials', true);

        if (in_array($tutorial_id, (array) $completed_tutorials)) {
            return false;
        }

        if (in_array($tutorial_id, (array) $skipped_tutorials)) {
            return false;
        }

        return true;
    }

    /**
     * Gérer la progression des tutoriels
     */
    public function handleTutorialProgress()
    {
        check_ajax_referer('pdf_builder_tutorial_nonce', 'nonce');

        $tutorial_id = sanitize_text_field($_POST['tutorial_id']);
        $step = intval($_POST['step']);
        $completed = isset($_POST['completed']) ? (bool) $_POST['completed'] : false;

        $user_id = get_current_user_id();

        if ($completed) {
            $completed_tutorials = get_user_meta($user_id, 'pdf_builder_completed_tutorials', true);
            if (!is_array($completed_tutorials)) {
                $completed_tutorials = [];
            }
            $completed_tutorials[] = $tutorial_id;
            update_user_meta($user_id, 'pdf_builder_completed_tutorials', array_unique($completed_tutorials));
        }

        wp_send_json_success([
            'message' => __('Progression sauvegardée', 'pdf-builder-pro')
        ]);
    }

    /**
     * Gérer le skip des tutoriels
     */
    public function handleSkipTutorial()
    {
        check_ajax_referer('pdf_builder_tutorial_nonce', 'nonce');

        $tutorial_id = sanitize_text_field($_POST['tutorial_id']);

        $user_id = get_current_user_id();
        $skipped_tutorials = get_user_meta($user_id, 'pdf_builder_skipped_tutorials', true);
        if (!is_array($skipped_tutorials)) {
            $skipped_tutorials = [];
        }
        $skipped_tutorials[] = $tutorial_id;
        update_user_meta($user_id, 'pdf_builder_skipped_tutorials', array_unique($skipped_tutorials));

        wp_send_json_success([
            'message' => __('Tutoriel ignoré', 'pdf-builder-pro')
        ]);
    }

    /**
     * Afficher le wizard de première utilisation
     */
    public function showWelcomeWizard()
    {
        // DEBUG: Forcer l'affichage pour les tests
        error_log('PDF Builder Tutorial: showWelcomeWizard called');

        // Temporairement désactiver la vérification pour forcer l'affichage
        // if (!$this->shouldShowTutorial('welcome_wizard')) {
        //     error_log('PDF Builder Tutorial: User already saw wizard, skipping');
        //     return;
        // }

        error_log('PDF Builder Tutorial: Displaying welcome wizard');

        ?>
        <div id="pdf-builder-welcome-wizard" class="pdf-builder-modal" style="display: block;">
            <div class="pdf-builder-modal-backdrop"></div>
            <div class="pdf-builder-modal-content welcome-wizard">
                <div class="wizard-header">
                    <h2><?php _e('🎉 Bienvenue dans PDF Builder Pro !', 'pdf-builder-pro'); ?></h2>
                    <p><?php _e('Créons ensemble vos premiers PDFs personnalisés', 'pdf-builder-pro'); ?></p>
                </div>

                <div class="wizard-steps">
                    <div class="wizard-step active" data-step="1">
                        <div class="step-icon">📋</div>
                        <h3><?php _e('Étape 1 : Configuration', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Configurez les paramètres de base pour WooCommerce', 'pdf-builder-pro'); ?></p>
                    </div>

                    <div class="wizard-step" data-step="2">
                        <div class="step-icon">🎨</div>
                        <h3><?php _e('Étape 2 : Premier template', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Créez votre premier modèle de facture', 'pdf-builder-pro'); ?></p>
                    </div>

                    <div class="wizard-step" data-step="3">
                        <div class="step-icon">🚀</div>
                        <h3><?php _e('Étape 3 : Test', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Testez la génération PDF avec une commande', 'pdf-builder-pro'); ?></p>
                    </div>
                </div>

                <div class="wizard-actions">
                    <button class="button-secondary" id="skip-wizard">
                        <?php _e('Passer le wizard', 'pdf-builder-pro'); ?>
                    </button>
                    <button class="button-primary" id="start-wizard">
                        <?php _e('Commencer', 'pdf-builder-pro'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Vérifier et afficher le wizard si nécessaire
     */
    public function maybeShowWelcomeWizard()
    {
        // DEBUG: Forcer l'affichage pour les tests
        error_log('PDF Builder Tutorial: maybeShowWelcomeWizard called');

        // Vérifier si on est sur une page du plugin
        $pdf_builder_pages = [
            'pdf-builder-pro',
            'pdf-builder-templates',
            'pdf-builder-react-editor',
            'pdf-builder-settings',
            'pdf-builder-developer',
            'pdf-builder-predefined-templates'
        ];

        $current_page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        error_log('PDF Builder Tutorial: Current page = ' . $current_page);

        if (in_array($current_page, $pdf_builder_pages)) {
            error_log('PDF Builder Tutorial: On PDF Builder page, showing wizard');
            $this->showWelcomeWizard();
        } else {
            error_log('PDF Builder Tutorial: Not on PDF Builder page');
        }
    }
}
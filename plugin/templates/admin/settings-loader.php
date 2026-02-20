<?php
/**
 * PDF Builder Pro - Settings Loader
 * Charge les styles et scripts pour la page de paramètres
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

/**
 * Charger les assets pour la page de paramètres
 */
function pdf_builder_load_settings_assets($hook) {
    // DEBUG: Fonction appelée
    error_log('PDF Builder - pdf_builder_load_settings_assets appelée pour hook: ' . $hook . ' - DÉBUT FONCTION');

    // DEBUG: Log du hook actuel
    error_log('PDF Builder - Hook actuel: ' . $hook);

    // TEMPORAIREMENT : Charger sur TOUTES les pages admin pour debug
    // if ($hook !== 'pdf-builder-pro_page_pdf-builder-settings') {
    //     return;
    // }

    // Charger la médiathèque WordPress si nécessaire
    wp_enqueue_media();

    // Enqueue WordPress core scripts needed for the settings page
    wp_enqueue_script('wp-date'); // Provides moment.js
    wp_enqueue_script('wp-element'); // Provides React
    wp_enqueue_script('wp-components'); // Provides React components
    wp_enqueue_script('wp-api'); // Provides WordPress API
    wp_enqueue_script('wp-data'); // Provides Redux store
    wp_enqueue_script('wp-hooks'); // Provides hooks
    wp_enqueue_script('wp-i18n'); // Provides internationalization
    wp_enqueue_script('wp-url'); // Provides URL utilities
    wp_enqueue_script('wp-keycodes'); // Provides keycodes
    wp_enqueue_script('wp-compose'); // Provides compose utilities
    wp_enqueue_script('wp-html-entities'); // Provides HTML entities
    wp_enqueue_script('wp-primitives'); // Provides primitives
    wp_enqueue_script('wp-warning'); // Provides warning system
    wp_enqueue_script('wp-token-list'); // Provides token list
    wp_enqueue_script('wp-core-data'); // Provides core data
    wp_enqueue_script('wp-core-commands'); // Provides core commands
    wp_enqueue_script('wp-block-editor'); // Provides block editor
    wp_enqueue_script('wp-rich-text'); // Provides rich text
    wp_enqueue_script('wp-commands'); // Provides commands
    wp_enqueue_script('wp-blob'); // Provides blob utilities
    wp_enqueue_script('wp-shortcode'); // Provides shortcode
    wp_enqueue_script('wp-media-utils'); // Provides media utilities
    wp_enqueue_script('wp-notices'); // Provides notices
    wp_enqueue_script('wp-preferences'); // Provides preferences
    wp_enqueue_script('wp-preferences-persistence'); // Provides preferences persistence
    wp_enqueue_script('wp-editor'); // Provides editor
    wp_enqueue_script('wp-plugins'); // Provides plugins
    wp_enqueue_script('wp-edit-post'); // Provides edit post
    wp_enqueue_script('wp-viewport'); // Provides viewport
    wp_enqueue_script('wp-interface'); // Provides interface
    wp_enqueue_script('wp-redux-routine'); // Provides redux routine
    wp_enqueue_script('wp-priority-queue'); // Provides priority queue
    wp_enqueue_script('wp-server-side-render'); // Provides server side render
    wp_enqueue_script('wp-autop'); // Provides autop
    wp_enqueue_script('wp-wordcount'); // Provides wordcount
    wp_enqueue_script('wp-annotations'); // Provides annotations
    wp_enqueue_script('wp-dom'); // Provides DOM utilities
    wp_enqueue_script('wp-a11y'); // Provides accessibility
    wp_enqueue_script('wp-dom-ready'); // Provides DOM ready
    wp_enqueue_script('wp-polyfill'); // Provides polyfills

    // S'assurer que l'objet wp global est disponible pour tous les scripts admin
    add_action('admin_enqueue_scripts', function() {
        ?>
        <script type="text/javascript">
        // S'assurer que l'objet wp est défini avant que d'autres scripts ne s'exécutent
        if (typeof window.wp === 'undefined') {
            window.wp = window.wp || {};
            
        }
        // Initialiser les propriétés communes de wp si elles n'existent pas
        window.wp = window.wp || {};
        window.wp.media = window.wp.media || null;
        window.wp.ajax = window.wp.ajax || { settings: {} };
        </script>
        <?php
    }, 1); // Priorité 1 pour s'exécuter très tôt

    // ACTIVATION DU CSS UNIFIÉ ULTIME
    // Un SEUL fichier CSS contenant TOUT pour une performance maximale

    // Charger le CSS unifié ultime (remplace TOUS les fichiers individuels)
    wp_enqueue_style(
        'pdf-builder-unified',
        PDF_BUILDER_PLUGIN_URL . 'assets/css/pdf-builder-unified.css',
        array(),
        PDF_BUILDER_VERSION . '-' . time(),
        'all'
    );

    // DEBUG: Avant enqueue du script
    error_log('PDF Builder - Avant wp_enqueue_script');

    // Charger le JavaScript pour la navigation par onglets - seulement si le fichier existe
    $settings_tabs_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/settings-tabs.min.js';
    if (file_exists($settings_tabs_js)) {
        wp_enqueue_script(
            'pdf-builder-settings-tabs',
            PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-tabs.min.js',
            array('jquery', 'wp-element', 'wp-components', 'wp-data', 'wp-hooks'), // Updated dependencies
            PDF_BUILDER_VERSION . '-' . time() . '-' . rand(1000, 9999), // Cache busting très agressif
            false // Chargé dans le header pour une exécution précoce
        );

        // Localiser le script avec les données AJAX - seulement si chargé
        if (class_exists('PDF_Builder_Nonce_Manager')) {
            $nonce_manager = PDF_Builder_Nonce_Manager::get_instance();
            $nonce = $nonce_manager->generate_nonce();
        } else {
            $nonce = wp_create_nonce('pdf_builder_ajax');
        }
        wp_localize_script('pdf-builder-settings-tabs', 'pdfBuilderAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => $nonce
        ));

        // DEBUG: Après localization
        error_log('PDF Builder - Après wp_localize_script');
    } else {
        error_log('PDF Builder - settings-tabs.js non trouvé, script ignoré');
    }

    // Force the settings-tabs script to not be deferred or async for early API availability
    add_action('wp_enqueue_scripts', function() {
        global $wp_scripts;
        if (isset($wp_scripts->registered['pdf-builder-settings-tabs'])) {
            $wp_scripts->registered['pdf-builder-settings-tabs']->extra['defer'] = false;
            $wp_scripts->registered['pdf-builder-settings-tabs']->extra['async'] = false;
        }
    }, 1);

    // DEBUG: Après enqueue du script
    error_log('PDF Builder - Après wp_enqueue_script');

    // Charger le script principal des paramètres - seulement si le fichier existe
    $settings_main_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/settings-main.min.js';
    if (file_exists($settings_main_js)) {
        wp_enqueue_script(
            'pdf-builder-settings-main',
            PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-main.min.js',
            array('jquery', 'wp-element', 'wp-components', 'wp-data', 'wp-hooks'),
            PDF_BUILDER_VERSION . '-' . time(),
            false // Chargé dans le header pour disponibilité immédiate
        );

        // Charger le script des paramètres canvas - seulement si le fichier existe et que settings-main est chargé
        $canvas_settings_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/canvas-settings.min.js';
        if (file_exists($canvas_settings_js)) {
            wp_enqueue_script(
                'pdf-builder-canvas-settings',
                PDF_BUILDER_PLUGIN_URL . 'assets/js/canvas-settings.min.js',
                array('jquery', 'pdf-builder-settings-main', 'wp-element', 'wp-components'),
                PDF_BUILDER_VERSION . '-' . time(),
                true // Chargé dans le footer
            );

            // Localiser le script canvas-settings avec le nonce approprié et l'URL AJAX
            if (class_exists('PDF_Builder_Nonce_Manager')) {
                $nonce_manager = PDF_Builder_Nonce_Manager::get_instance();
                $canvas_nonce = $nonce_manager->generate_nonce();
            } else {
                $canvas_nonce = wp_create_nonce('pdf_builder_canvas_settings');
            }
            wp_localize_script('pdf-builder-canvas-settings', 'pdf_builder_canvas_settings', array(
                'nonce' => $canvas_nonce,
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
    }

    // Localiser le script principal avec les données AJAX
    if (class_exists('PDF_Builder_Nonce_Manager')) {
        $nonce_manager = PDF_Builder_Nonce_Manager::get_instance();
        $main_nonce = $nonce_manager->generate_nonce();
    } else {
        $main_nonce = wp_create_nonce('pdf_builder_ajax');
    }
    wp_localize_script('pdf-builder-settings-main', 'pdf_builder_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => $main_nonce
    ));

    // Rendre la variable globale pour tous les scripts inline
    add_action('admin_footer', function() use ($main_nonce) {
        ?>
        <script type="text/javascript">
        // Rendre pdf_builder_ajax disponible globalement pour les scripts inline
        if (typeof window.pdf_builder_ajax === 'undefined') {
            window.pdf_builder_ajax = {
                ajax_url: '<?php echo admin_url('admin-ajax.php'); ?>',
                nonce: '<?php echo $main_nonce; ?>'
            };
        }
        </script>
        <?php
    });

    // Charger le script du bouton flottant de sauvegarde - seulement si le fichier existe
    $floating_save_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/floating-save-button.js';
    if (file_exists($floating_save_js)) {
        wp_enqueue_script(
            'pdf-builder-floating-save',
            PDF_BUILDER_PLUGIN_URL . 'assets/js/floating-save-button.js',
            array('jquery', 'pdf-builder-settings-main', 'wp-element', 'wp-components'),
            PDF_BUILDER_VERSION . '-' . time(),
            true // Chargé dans le footer
        );
    }

    error_log('PDF Builder - pdf_builder_load_settings_assets TERMINÉE pour hook: ' . $hook);
}

// Enregistrer le hook pour charger les assets
add_action('admin_enqueue_scripts', 'pdf_builder_load_settings_assets');

/**
 * Charger les assets pour toutes les pages admin (styles globaux)
 */
function pdf_builder_load_global_admin_assets($hook) {
    // Les styles globaux sont maintenant inclus dans le fichier consolidé
    // Cette fonction peut être supprimée ou simplifiée selon les besoins
}

// Enregistrer le hook pour les assets globaux
add_action('admin_enqueue_scripts', 'pdf_builder_load_global_admin_assets');






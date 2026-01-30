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
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - pdf_builder_load_settings_assets appelée pour hook: ' . $hook . ' - DÉBUT FONCTION'); }

    // DEBUG: Log du hook actuel
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Hook actuel: ' . $hook); }

    // TEMPORAIREMENT : Charger sur TOUTES les pages admin pour debug
    // if ($hook !== 'pdf-builder-pro_page_pdf-builder-settings') {
    //     return;
    // }

    // Charger la médiathèque WordPress si nécessaire
    wp_enqueue_media();

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
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Avant wp_enqueue_script'); }

    // Charger le JavaScript pour la navigation par onglets - seulement si le fichier existe
    $settings_tabs_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/settings-tabs.min.js';
    if (file_exists($settings_tabs_js)) {
        wp_enqueue_script(
            'pdf-builder-settings-tabs',
            PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-tabs.min.js',
            array('jquery'), // Removed wp-util and wp-api to avoid async loading
            PDF_BUILDER_VERSION . '-' . time() . '-' . rand(1000, 9999), // Cache busting très agressif
            false // Chargé dans le header pour une exécution précoce
        );

        // Localiser le script avec les données AJAX - seulement si chargé
        wp_localize_script('pdf-builder-settings-tabs', 'pdfBuilderAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_ajax')
        ));

        // DEBUG: Après localization
        if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Après wp_localize_script'); }
    } else {
        if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - settings-tabs.js non trouvé, script ignoré'); }
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
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - Après wp_enqueue_script'); }

    // Charger le script principal des paramètres - seulement si le fichier existe
    $settings_main_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/settings-main.min.js';
    if (file_exists($settings_main_js)) {
        wp_enqueue_script(
            'pdf-builder-settings-main',
            PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-main.min.js',
            array('jquery'),
            PDF_BUILDER_VERSION . '-' . time(),
            false // Chargé dans le header pour disponibilité immédiate
        );

        // Charger le script des paramètres canvas - seulement si le fichier existe et que settings-main est chargé
        $canvas_settings_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/canvas-settings.min.js';
        if (file_exists($canvas_settings_js)) {
            wp_enqueue_script(
                'pdf-builder-canvas-settings',
                PDF_BUILDER_PLUGIN_URL . 'assets/js/canvas-settings.min.js',
                array('jquery', 'pdf-builder-settings-main'),
                PDF_BUILDER_VERSION . '-' . time(),
                true // Chargé dans le footer
            );

            // Localiser le script canvas-settings avec le nonce approprié et l'URL AJAX
            wp_localize_script('pdf-builder-canvas-settings', 'pdf_builder_canvas_settings', array(
                'nonce' => wp_create_nonce('pdf_builder_canvas_settings'),
                'ajax_url' => admin_url('admin-ajax.php')
            ));
        }
    }

    // Localiser le script principal avec les données AJAX
    wp_localize_script('pdf-builder-settings-main', 'pdf_builder_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_ajax')
    ));

    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('PDF Builder - pdf_builder_load_settings_assets TERMINÉE pour hook: ' . $hook); }
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




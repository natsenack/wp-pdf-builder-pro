<?php
/**
 * PDF Builder Pro - Settings Loader
 * Charge les styles et scripts pour la page de paramètres
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
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

    // S'assurer que l'objet wp global est disponible pour tous les scripts admin
    add_action('admin_enqueue_scripts', function() {
        ?>
        <script type="text/javascript">
        // S'assurer que l'objet wp est défini avant que d'autres scripts ne s'exécutent
        if (typeof window.wp === 'undefined') {
            window.wp = window.wp || {};
            console.log('[PDF Builder Settings] Initialized wp object');
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
    $settings_tabs_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/settings-tabs.js';
    if (file_exists($settings_tabs_js)) {
        wp_enqueue_script(
            'pdf-builder-settings-tabs',
            PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-tabs.js',
            array('jquery', 'wp-util', 'wp-api'),
            PDF_BUILDER_VERSION . '-' . time() . '-' . rand(1000, 9999), // Cache busting très agressif
            false // Chargé dans le header pour une exécution précoce
        );

        // Localiser le script avec les données AJAX - seulement si chargé
        wp_localize_script('pdf-builder-settings-tabs', 'pdfBuilderAjax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('pdf_builder_ajax')
        ));

        // DEBUG: Après localization
        error_log('PDF Builder - Après wp_localize_script');
    } else {
        error_log('PDF Builder - settings-tabs.js non trouvé, script ignoré');
    }

    // DEBUG: Après enqueue du script
    error_log('PDF Builder - Après wp_enqueue_script');

    // Charger le script principal des paramètres - seulement si le fichier existe
    $settings_main_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/settings-main.js';
    if (file_exists($settings_main_js)) {
        wp_enqueue_script(
            'pdf-builder-settings-main',
            PDF_BUILDER_PLUGIN_URL . 'assets/js/settings-main.js',
            array('jquery'),
            PDF_BUILDER_VERSION . '-' . time(),
            true // Chargé dans le footer
        );

        // Charger le script des paramètres canvas - seulement si le fichier existe et que settings-main est chargé
        $canvas_settings_js = PDF_BUILDER_PRO_ASSETS_PATH . 'js/canvas-settings.js';
        if (file_exists($canvas_settings_js)) {
            wp_enqueue_script(
                'pdf-builder-canvas-settings',
                PDF_BUILDER_PLUGIN_URL . 'assets/js/canvas-settings.js',
                array('jquery', 'pdf-builder-settings-main'),
                PDF_BUILDER_VERSION . '-' . time(),
                true // Chargé dans le footer
            );
        }
    }

    // Localiser le script principal avec les données AJAX
    wp_localize_script('pdf-builder-settings-main', 'pdf_builder_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('pdf_builder_ajax')
    ));

    // AJOUTER L'API PDFBuilderTabsAPI DIRECTEMENT - SOLUTION DE SECOURS
    $pdf_builder_api_script = "
    console.log('PDFBuilderTabsAPI script starting via inline script...');
    
    try {
        window.PDFBuilderTabsAPI = {
            switchToTab: function(tabName) {
                console.log('switchToTab called with:', tabName);
                var tabLink = document.querySelector('a[href*=\"tab=' + tabName + '\"]');
                if (tabLink) {
                    tabLink.click();
                } else {
                    var currentUrl = window.location.href;
                    var newUrl = currentUrl.replace(/tab=[^&]*/, 'tab=' + tabName);
                    if (newUrl === currentUrl) {
                        newUrl = currentUrl + (currentUrl.indexOf('?') > -1 ? '&' : '?') + 'tab=' + tabName;
                    }
                    window.location.href = newUrl;
                }
            },

            toggleAdvancedSection: function() {
                console.log('toggleAdvancedSection called');
                var advancedSection = document.getElementById('advanced-section');
                var toggleIcon = document.getElementById('advanced-toggle');

                console.log('Elements found:', { advancedSection: advancedSection, toggleIcon: toggleIcon });

                if (advancedSection && toggleIcon) {
                    if (advancedSection.classList.contains('hidden-element')) {
                        advancedSection.classList.remove('hidden-element');
                        toggleIcon.textContent = '▲';
                        console.log('Section shown');
                    } else {
                        advancedSection.classList.add('hidden-element');
                        toggleIcon.textContent = '▼';
                        console.log('Section hidden');
                    }
                } else {
                    console.error('Required elements not found');
                }
            },

            resetTemplatesStatus: function() {
                if (confirm('Êtes-vous sûr de vouloir réinitialiser tous les templates par statut de commande ? Cette action ne peut pas être annulée.')) {
                    var selects = document.querySelectorAll('.template-select');
                    selects.forEach(function(select) {
                        select.value = '';
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                    alert('Les paramètres des templates ont été réinitialisés.');
                }
            }
        };

        console.log('PDFBuilderTabsAPI defined successfully via inline script:', window.PDFBuilderTabsAPI);
    } catch (error) {
        console.error('Error in PDFBuilderTabsAPI definition via inline script:', error);
    }
    ";

    wp_add_inline_script('pdf-builder-settings-tabs', $pdf_builder_api_script, 'after');

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

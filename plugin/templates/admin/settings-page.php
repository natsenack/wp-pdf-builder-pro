<?php
/**
 * Page des Paramètres - PDF Builder Pro
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// Vérifier les permissions - permettre à tous les utilisateurs connectés
if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
}

// Utiliser l'instance globale si elle existe, sinon créer une nouvelle
global $pdf_builder_core;
if (isset($pdf_builder_core) && $pdf_builder_core instanceof \PDF_Builder\Core\PDF_Builder_Core) {
    $core = $pdf_builder_core;
} else {
    $core = \PDF_Builder\Core\PDF_Builder_Core::getInstance();
}

$config = null;

// Variable pour stocker les messages de notification
$admin_notices = array();

// Classe temporaire pour gérer la configuration avec les options WordPress
class TempConfig {
    private $option_name = 'pdf_builder_settings';

    public function get($key, $default = '') {
        $settings = get_option($this->option_name, []);

        // Valeurs par défaut complètes
        $defaults = [
            'debug_mode' => false,
            'log_level' => 'info',
            'max_template_size' => 52428800, // 50MB
            'cache_enabled' => true,
            'cache_ttl' => 3600,
            'max_execution_time' => 300,
            'memory_limit' => '256M',
            'pdf_quality' => 'high',
            'default_format' => 'A4',
            'default_orientation' => 'portrait',
            'email_notifications_enabled' => false,
            'notification_events' => [],
            // Paramètres Canvas - anciens
            'canvas_element_borders_enabled' => true,
            'canvas_border_width' => 1,
            'canvas_border_color' => '#007cba',
            'canvas_border_spacing' => 2,
            'canvas_resize_handles_enabled' => true,
            'canvas_handle_size' => 8,
            'canvas_handle_color' => '#007cba',
            'canvas_handle_hover_color' => '#005a87',
            // Paramètres Canvas - nouveaux (Général)
            'default_canvas_width' => 794,
            'default_canvas_height' => 1123,
            'default_canvas_unit' => 'px',
            'canvas_background_color' => '#ffffff',
            'canvas_show_transparency' => false,
            'container_background_color' => '#f8f9fa',
            'container_show_transparency' => false,
            'show_margins' => true,
            'margin_top' => 28,
            'margin_right' => 28,
            'margin_bottom' => 28,
            'margin_left' => 10,
            // Paramètres Canvas - Grille & Aimants
            'show_grid' => true,
            'grid_size' => 10,
            'grid_color' => '#e0e0e0',
            'grid_opacity' => 30,
            'snap_to_grid' => true,
            'snap_to_elements' => true,
            'snap_to_margins' => true,
            'snap_tolerance' => 5,
            'show_guides' => true,
            'lock_guides' => false,
            // Paramètres Canvas - Zoom & Navigation
            'default_zoom' => '100',
            'zoom_step' => 25,
            'min_zoom' => 10,
            'max_zoom' => 500,
            'pan_with_mouse' => true,
            'zoom_with_wheel' => true,
            'smooth_zoom' => true,
            'show_zoom_indicator' => true,
            // Paramètres Canvas - Sélection & Manipulation
            'show_resize_handles' => true,
            'handle_size' => 8,
            'handle_color' => '#007cba',
            'enable_rotation' => true,
            'rotation_step' => 15,
            'rotation_snap' => true,
            'multi_select' => true,
            'select_all_shortcut' => true,
            'show_selection_bounds' => true,
            'copy_paste_enabled' => true,
            'duplicate_on_drag' => false,
            // Paramètres Canvas - Export & Qualité
            'export_quality' => 'print',
            'export_format' => 'pdf',
            'compress_images' => true,
            'image_quality' => 85,
            'max_image_size' => 2048,
            'include_metadata' => true,
            'pdf_author' => get_bloginfo('name'),
            'pdf_subject' => '',
            'auto_crop' => false,
            'embed_fonts' => true,
            'optimize_for_web' => false,
            // Paramètres Canvas - Avancé
            'enable_hardware_acceleration' => true,
            'limit_fps' => false,
            'max_fps' => 60,
            'auto_save_enabled' => true,
            'auto_save_interval' => 30,
            'auto_save_versions' => 10,
            'undo_levels' => 50,
            'redo_levels' => 50,
            'enable_keyboard_shortcuts' => true,
            'show_fps' => false,
            'email_notifications' => false,
            'admin_email' => get_option('admin_email'),
            // Paramètres Développeur
            'developer_enabled' => false,
            'developer_password' => ''
        ];

        return isset($settings[$key]) ? $settings[$key] : ($defaults[$key] ?? $default);
    }

    public function set_multiple($settings) {
        $current_settings = get_option($this->option_name, []);
        $updated_settings = array_merge($current_settings, $settings);
        update_option($this->option_name, $updated_settings);
    }

    public function set($key, $value) {
        $settings = get_option($this->option_name, []);
        $settings[$key] = $value;
        update_option($this->option_name, $settings);
    }

    /**
     * Retourne une description pour un rôle WordPress
     */
    public function get_role_description($role_key) {
        $descriptions = [
            'administrator' => __('Accès complet à toutes les fonctionnalités de WordPress, y compris PDF Builder Pro.', 'pdf-builder-pro'),
            'editor' => __('Peut publier et gérer ses propres articles et ceux des autres. Accès à PDF Builder Pro.', 'pdf-builder-pro'),
            'author' => __('Peut publier et gérer ses propres articles. Accès limité à PDF Builder Pro.', 'pdf-builder-pro'),
            'contributor' => __('Peut écrire ses propres articles mais ne peut pas les publier. Accès limité à PDF Builder Pro.', 'pdf-builder-pro'),
            'subscriber' => __('Peut uniquement lire les articles. Pas d\'accès à PDF Builder Pro.', 'pdf-builder-pro'),
            'shop_manager' => __('Gestionnaire de boutique WooCommerce. Accès à PDF Builder Pro pour les commandes.', 'pdf-builder-pro'),
            'customer' => __('Client WooCommerce. Pas d\'accès à PDF Builder Pro.', 'pdf-builder-pro'),
        ];

        return isset($descriptions[$role_key]) ? $descriptions[$role_key] : __('Rôle personnalisé ajouté par un plugin.', 'pdf-builder-pro');
    }
}

$config = new TempConfig();

/*
// Le traitement des paramètres est maintenant géré par AJAX via ajax_save_settings()
// Code commenté pour référence
// ...*/
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ((isset($_POST['submit']) || isset($_POST['submit_roles']) || isset($_POST['submit_notifications']) || isset($_POST['submit_templates'])) && isset($_POST['pdf_builder_settings_nonce'])) {

    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {

        $settings = [
            'debug_mode' => isset($_POST['debug_mode']),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'cache_ttl' => intval($_POST['cache_ttl']),
            'max_execution_time' => intval($_POST['max_execution_time']),
            'memory_limit' => sanitize_text_field($_POST['memory_limit']),
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality']),
            'default_format' => sanitize_text_field($_POST['default_format']),
            'default_orientation' => sanitize_text_field($_POST['default_orientation']),
            'log_level' => sanitize_text_field($_POST['log_level']),
            'max_template_size' => intval($_POST['max_template_size']),
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
            'default_canvas_width' => isset($_POST['default_canvas_width']) ? intval($_POST['default_canvas_width']) : 794,
            'default_canvas_height' => isset($_POST['default_canvas_height']) ? intval($_POST['default_canvas_height']) : 1123,
            'default_canvas_unit' => isset($_POST['default_canvas_unit']) ? sanitize_text_field($_POST['default_canvas_unit']) : 'px',
            'canvas_background_color' => isset($_POST['canvas_background_color']) ? sanitize_text_field($_POST['canvas_background_color']) : '#ffffff',
            'canvas_show_transparency' => isset($_POST['canvas_show_transparency']),
            'container_background_color' => isset($_POST['container_background_color']) ? sanitize_text_field($_POST['container_background_color']) : '#f8f9fa',
            'container_show_transparency' => isset($_POST['container_show_transparency']),
            'show_margins' => isset($_POST['show_margins']),
            'margin_top' => isset($_POST['margin_top']) ? intval($_POST['margin_top']) : 28,
            'margin_right' => isset($_POST['margin_right']) ? intval($_POST['margin_right']) : 28,
            'margin_bottom' => isset($_POST['margin_bottom']) ? intval($_POST['margin_bottom']) : 28,
            'margin_left' => isset($_POST['margin_left']) ? intval($_POST['margin_left']) : 28,
            'show_grid' => isset($_POST['show_grid']),
            'grid_size' => isset($_POST['grid_size']) ? intval($_POST['grid_size']) : 10,
            'grid_color' => isset($_POST['grid_color']) ? sanitize_text_field($_POST['grid_color']) : '#e0e0e0',
            'grid_opacity' => isset($_POST['grid_opacity']) ? intval($_POST['grid_opacity']) : 30,
            'snap_to_grid' => isset($_POST['snap_to_grid']),
            'snap_to_elements' => isset($_POST['snap_to_elements']),
            'snap_to_margins' => isset($_POST['snap_to_margins']),
            'snap_tolerance' => isset($_POST['snap_tolerance']) ? intval($_POST['snap_tolerance']) : 5,
            'show_guides' => isset($_POST['show_guides']),
            'lock_guides' => isset($_POST['lock_guides']),
            'default_zoom' => isset($_POST['default_zoom']) ? sanitize_text_field($_POST['default_zoom']) : '100',
            'zoom_step' => isset($_POST['zoom_step']) ? intval($_POST['zoom_step']) : 25,
            'min_zoom' => isset($_POST['min_zoom']) ? intval($_POST['min_zoom']) : 10,
            'max_zoom' => isset($_POST['max_zoom']) ? intval($_POST['max_zoom']) : 500,
            'pan_with_mouse' => isset($_POST['pan_with_mouse']),
            'smooth_zoom' => isset($_POST['smooth_zoom']),
            'show_zoom_indicator' => isset($_POST['show_zoom_indicator']),
            'zoom_with_wheel' => isset($_POST['zoom_with_wheel']),
            'zoom_to_selection' => isset($_POST['zoom_to_selection']),
            'show_resize_handles' => isset($_POST['show_resize_handles']),
            'handle_size' => isset($_POST['handle_size']) ? intval($_POST['handle_size']) : 8,
            'handle_color' => isset($_POST['handle_color']) ? sanitize_text_field($_POST['handle_color']) : '#007cba',
            'enable_rotation' => isset($_POST['enable_rotation']),
            'rotation_step' => isset($_POST['rotation_step']) ? intval($_POST['rotation_step']) : 15,
            'rotation_snap' => isset($_POST['rotation_snap']),
            'multi_select' => isset($_POST['multi_select']),
            'select_all_shortcut' => isset($_POST['select_all_shortcut']),
            'show_selection_bounds' => isset($_POST['show_selection_bounds']),
            'copy_paste_enabled' => isset($_POST['copy_paste_enabled']),
            'duplicate_on_drag' => isset($_POST['duplicate_on_drag']),
            'export_quality' => isset($_POST['export_quality']) ? sanitize_text_field($_POST['export_quality']) : 'print',
            'export_format' => isset($_POST['export_format']) ? sanitize_text_field($_POST['export_format']) : 'pdf',
            'compress_images' => isset($_POST['compress_images']),
            'image_quality' => isset($_POST['image_quality']) ? intval($_POST['image_quality']) : 85,
            'max_image_size' => isset($_POST['max_image_size']) ? intval($_POST['max_image_size']) : 2048,
            'include_metadata' => isset($_POST['include_metadata']),
            'pdf_author' => isset($_POST['pdf_author']) ? sanitize_text_field($_POST['pdf_author']) : get_bloginfo('name'),
            'pdf_subject' => isset($_POST['pdf_subject']) ? sanitize_text_field($_POST['pdf_subject']) : '',
            'auto_crop' => isset($_POST['auto_crop']),
            'embed_fonts' => isset($_POST['embed_fonts']),
            'optimize_for_web' => isset($_POST['optimize_for_web']),
            'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
            'limit_fps' => isset($_POST['limit_fps']),
            'max_fps' => isset($_POST['max_fps']) ? intval($_POST['max_fps']) : 60,
            'auto_save_enabled' => isset($_POST['auto_save_enabled']),
            'auto_save_interval' => isset($_POST['auto_save_interval']) ? intval($_POST['auto_save_interval']) : 30,
            'auto_save_versions' => isset($_POST['auto_save_versions']) ? intval($_POST['auto_save_versions']) : 10,
            'undo_levels' => isset($_POST['undo_levels']) ? intval($_POST['undo_levels']) : 50,
            'redo_levels' => isset($_POST['redo_levels']) ? intval($_POST['redo_levels']) : 50,
            'enable_keyboard_shortcuts' => isset($_POST['enable_keyboard_shortcuts']),
            'debug_mode' => isset($_POST['debug_mode']),
            'show_fps' => isset($_POST['show_fps']),
            'email_notifications' => isset($_POST['email_notifications']),
            'admin_email' => sanitize_email($_POST['admin_email']),
            'notification_log_level' => sanitize_text_field($_POST['notification_log_level'])
        ];

        $config->set_multiple($settings);

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

        // Vérification que les options sont bien sauvegardées

        if ($isAjax) {
            // Réponse AJAX - sortir immédiatement
            wp_send_json_success(array(
                'message' => __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro'),
                'spacing' => $settings['canvas_border_spacing']
            ));
            exit; // Important : arrêter l'exécution ici pour les requêtes AJAX
        } else {
            // Réponse normale - stocker le message pour affichage dans le HTML
            $admin_notices[] = '<div class="notice notice-success"><p>' . __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro') . ' (espacement: ' . $settings['canvas_border_spacing'] . ')</p></div>';
        }
    } else {
        if ($isAjax) {
            wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
            exit; // Important : arrêter l'exécution ici pour les requêtes AJAX
        } else {
            $admin_notices[] = '<div class="notice notice-error"><p>' . __('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro') . '</p></div>';
        }
    }
} elseif (isset($_POST['submit'])) {
    if ($isAjax) {
        wp_send_json_error(__('Erreur : nonce manquant.', 'pdf-builder-pro'));
        exit; // Important : arrêter l'exécution ici pour les requêtes AJAX
    } else {
        $admin_notices[] = '<div class="notice notice-error"><p>' . __('Erreur : nonce manquant.', 'pdf-builder-pro') . '</p></div>';
    }
}

// Si c'est une requête AJAX, ne pas afficher le HTML
if ($isAjax) {
    exit; // Sortir immédiatement pour les requêtes AJAX qui n'ont pas de données POST
}

// Charger les paramètres pour l'affichage
$settings = get_option('pdf_builder_settings', []);
?>

<!-- Debug script to check React availability -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    
    
    
    
});

// Check again after a longer timeout to ensure all scripts are loaded
window.addEventListener('load', function() {
    setTimeout(function() {
        
        
        
        
    }, 1000);
});
</script>

    <h1><?php _e('⚙️ Paramètres - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <script type="text/javascript">
    // Définir les variables globales nécessaires
    window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    window.pdfBuilderSettingsNonce = '<?php echo wp_create_nonce('pdf_builder_settings'); ?>';
    window.pdfBuilderMaintenanceNonce = '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>';
    
    // Vérification de sécurité pour éviter les erreurs JavaScript de plugins tiers
    if (typeof wp === 'undefined') {
        // Définir un objet wp complet pour éviter les erreurs de plugins tiers
        window.wp = window.wp || {
            api: {
                models: {},
                collections: {},
                views: {},
                loadPromise: Promise.resolve()
            },
            ajax: {
                settings: {
                    url: ajaxurl || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    timeout: 30000
                },
                send: function(action, data) {
                    return jQuery.ajax({
                        url: this.settings.url,
                        type: this.settings.type,
                        data: jQuery.extend({ action: action }, data),
                        timeout: this.settings.timeout
                    });
                }
            },
            hooks: {
                addAction: function(hook, callback) { /* stub */ },
                addFilter: function(hook, callback) { return callback; },
                doAction: function(hook) { /* stub */ },
                applyFilters: function(hook, value) { return value; }
            },
            i18n: {
                __: function(text) { return text; },
                _x: function(text, context) { return text; },
                _n: function(single, plural, number) { return number === 1 ? single : plural; }
            },
            media: {
                controller: {
                    Library: function() { /* stub */ }
                }
            },
            blocks: {},
            components: {},
            compose: {},
            data: {
                dispatch: function(store) { return {}; },
                select: function(store) { return {}; },
                subscribe: function(callback) { /* stub */ }
            },
            editPost: {},
            editor: {},
            plugins: {},
            richText: {},
            url: {
                addQueryArgs: function(url, args) {
                    var separator = url.indexOf('?') !== -1 ? '&' : '?';
                    var query = [];
                    for (var key in args) {
                        if (args.hasOwnProperty(key)) {
                            query.push(encodeURIComponent(key) + '=' + encodeURIComponent(args[key]));
                        }
                    }
                    return url + separator + query.join('&');
                }
            }
        };
    }

    // Message immédiat pour confirmer que le script s'exécute

    // Simple tab functionality without jQuery wrapper
    function simpleActivateTab(tabHref) {
        // Find the target element
        var targetElement = document.getElementById(tabHref.substring(1)); // Remove #
        if (!targetElement) {
            return;
        }

        // Hide all tab contents by finding elements that match nav tab hrefs
        var navTabs = document.querySelectorAll('.nav-tab');
        
        // Collect all tab content IDs from nav tabs
        var tabContentIds = [];
        for (var i = 0; i < navTabs.length; i++) {
            var href = navTabs[i].getAttribute('href');
            if (href && href.startsWith('#')) {
                tabContentIds.push(href.substring(1));
            }
        }
        
        // Hide all tab contents that correspond to nav tabs
        var tabContents = [];
        for (var i = 0; i < tabContentIds.length; i++) {
            var element = document.getElementById(tabContentIds[i]);
            if (element) {
                tabContents.push(element);
            }
        }

        // Simple approach: hide all tab contents first, then show only the target
        for (var i = 0; i < tabContents.length; i++) {
            if (tabContents[i].id === targetElement.id) {
                // This is the target - show it
                tabContents[i].classList.add('active');
                tabContents[i].style.cssText = 'display: block !important;';
                
                // Special handling for Canvas tab
                if (tabContents[i].id === 'canvas') {
                    // Modern tabs are handled by the jQuery code below
                    // Scroll to make tabs visible
                    var tabsContainer = document.querySelector('.modern-tabs-container');
                    if (tabsContainer) {
                        tabsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            } else {
                // This is not the target - hide it
                tabContents[i].classList.remove('active');
                tabContents[i].style.cssText = 'display: none !important;';
            }
        }

        // Handle nav tabs
        for (var i = 0; i < navTabs.length; i++) {
            navTabs[i].classList.remove('nav-tab-active');
        }

        var activeNavTab = document.querySelector('.nav-tab[href="' + tabHref + '"]');
        if (activeNavTab) {
            activeNavTab.classList.add('nav-tab-active');
        }
    }
    
    // Attach click handlers when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        var navTabs = document.querySelectorAll('.nav-tab');

        for (var i = 0; i < navTabs.length; i++) {
            navTabs[i].addEventListener('click', function(e) {
                e.preventDefault();
                var tabHref = this.getAttribute('href');
                simpleActivateTab(tabHref);
            });
        }

        // Initialize first tab
        var firstTab = document.querySelector('.nav-tab');
        if (firstTab) {
            var firstHref = firstTab.getAttribute('href');
            simpleActivateTab(firstHref);
        }

        // Handle range inputs with value display
        var rangeInputs = document.querySelectorAll('input[type="range"]');
        for (var i = 0; i < rangeInputs.length; i++) {
            var rangeInput = rangeInputs[i];
            var valueDisplay = document.getElementById(rangeInput.id + '_value');
            if (valueDisplay) {
                rangeInput.addEventListener('input', function() {
                    var display = document.getElementById(this.id + '_value');
                    if (display) {
                        var suffix = '';
                        if (this.id.includes('opacity') || this.id.includes('quality')) {
                            suffix = '%';
                        } else if (this.id.includes('size')) {
                            suffix = ' px';
                        }
                        display.textContent = this.value + suffix;
                    }
                });
                // Initialize display
                var initialSuffix = '';
                if (rangeInput.id.includes('opacity') || rangeInput.id.includes('quality')) {
                    initialSuffix = '%';
                } else if (rangeInput.id.includes('size')) {
                    initialSuffix = ' px';
                }
                valueDisplay.textContent = rangeInput.value + initialSuffix;
            }
        }
    });
    </script>

    <?php
    // Afficher les messages de notification stockés
    if (!empty($admin_notices)) {
        foreach ($admin_notices as $notice) {
            echo $notice;
        }
    }
    ?>

    <div id="pdf-builder-settings-tabs" class="pdf-builder-settings">

            <style>
            /* Styles pour la page des paramètres PDF Builder Pro */
            .pdf-builder-settings {
                max-width: 100%;
                margin-top: 20px;
            }

            .pdf-builder-settings .nav-tab-wrapper {
                margin: 0 0 20px 0;
                padding: 0;
                border-bottom: 1px solid #ccc;
                background: transparent;
            }

            .pdf-builder-settings .nav-tab {
                display: inline-block;
                padding: 8px 16px;
                margin: 0 4px -1px 0;
                border: 1px solid #ccc;
                border-bottom: none;
                background: #f1f1f1;
                color: #666;
                text-decoration: none;
                border-radius: 4px 4px 0 0;
                cursor: pointer;
                font-size: 14px;
                line-height: 1.4;
                position: relative;
                top: 1px;
            }

            .pdf-builder-settings .nav-tab.nav-tab-active,
            .pdf-builder-settings .nav-tab:hover {
                background: #fff;
                color: #000;
                border-bottom: 1px solid #fff;
            }

            .pdf-builder-settings .tab-content {
                display: none;
                padding: 20px;
                background: #fff;
                border: 1px solid #ccc;
                border-radius: 0 4px 4px 4px;
                margin-bottom: 20px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }

            .pdf-builder-settings .tab-content.active {
                display: block;
            }

            .pdf-builder-settings .form-table {
                background: transparent;
                margin: 0;
                width: 100%;
            }

            .pdf-builder-settings .form-table th {
                width: 200px;
                padding: 20px 10px 20px 0;
                vertical-align: top;
                font-weight: 600;
                color: #23282d;
            }

            .pdf-builder-settings .form-table td {
                padding: 15px 10px;
                vertical-align: top;
            }

            .pdf-builder-settings .form-table tr {
                border-bottom: 1px solid #f0f0f0;
            }

            .pdf-builder-settings .form-table tr:last-child {
                border-bottom: none;
            }

            .pdf-builder-settings .description {
                color: #666;
                font-style: italic;
                margin-top: 5px;
                font-size: 13px;
            }

            .pdf-builder-settings .submit {
                padding: 20px 0;
                border-top: 1px solid #ccc;
                margin-top: 20px;
                text-align: left;
            }

            /* Responsive */
            @media screen and (max-width: 782px) {
                .pdf-builder-settings .form-table th {
                    width: 100%;
                    padding-bottom: 10px;
                }

                .pdf-builder-settings .form-table td {
                    width: 100%;
                    padding-top: 0;
                }
            }

            /* Fix for footer positioning - ensure content appears above footer */
            .wrap {
                position: relative;
                z-index: 1;
                clear: both;
            }

            .wrap::after {
                content: "";
                display: block;
                clear: both;
            }

            /* Ensure notifications don't interfere with footer */
            .notice, .updated, .error {
                position: relative !important;
                z-index: 10 !important;
            }
            </style>

            <!-- Onglets -->
            <div class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active"><?php _e('Général', 'pdf-builder-pro'); ?></a>
                <a href="#license" class="nav-tab"><?php _e('Licence', 'pdf-builder-pro'); ?></a>
                <a href="#performance" class="nav-tab"><?php _e('Performance', 'pdf-builder-pro'); ?></a>
                <a href="#pdf" class="nav-tab"><?php _e('PDF', 'pdf-builder-pro'); ?></a>
                <a href="#security" class="nav-tab"><?php _e('Sécurité', 'pdf-builder-pro'); ?></a>
                <a href="#roles" class="nav-tab"><?php _e('Rôles', 'pdf-builder-pro'); ?></a>
                <a href="#notifications" class="nav-tab"><?php _e('Notifications', 'pdf-builder-pro'); ?></a>
                <a href="#canvas" class="nav-tab"><?php _e('Canvas', 'pdf-builder-pro'); ?></a>
                <a href="#templates" class="nav-tab"><?php _e('Templates', 'pdf-builder-pro'); ?></a>
                <a href="#maintenance" class="nav-tab"><?php _e('Maintenance', 'pdf-builder-pro'); ?></a>
                <a href="#developer" class="nav-tab"><?php _e('🔧 Développeur', 'pdf-builder-pro'); ?></a>
            </div>

            <!-- Message pour les utilisateurs sans JavaScript -->
            <noscript>
                <div class="notice notice-warning">
                    <p><?php _e('JavaScript est requis pour la navigation par onglets. Certains onglets peuvent ne pas s\'afficher correctement.', 'pdf-builder-pro'); ?></p>
                </div>
            </noscript>

            <!-- Onglet Général -->
            <div id="general" class="tab-content active">
                <h2><?php _e('Paramètres Généraux', 'pdf-builder-pro'); ?></h2>

                <h3><?php _e('Informations Entreprise', 'pdf-builder-pro'); ?></h3>
                <p class="description">
                    <?php _e('Les informations de base (nom, adresse, téléphone, email) sont automatiquement récupérées depuis les réglages WooCommerce et WordPress.', 'pdf-builder-pro'); ?><br>
                    <?php _e('Configurez ici uniquement les informations légales spécifiques qui ne sont pas disponibles dans WooCommerce.', 'pdf-builder-pro'); ?>
                </p>

                <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
                    <h4 style="margin-top: 0; color: #495057;"><?php _e('Informations récupérées automatiquement', 'pdf-builder-pro'); ?></h4>
                    <ul style="margin: 0; padding-left: 20px; color: #6c757d;">
                        <li><strong><?php _e('Nom de l\'entreprise', 'pdf-builder-pro'); ?>:</strong> <?php echo esc_html(get_bloginfo('name')); ?> <em>(<?php _e('Réglages WordPress > Général', 'pdf-builder-pro'); ?>)</em></li>
                        <li><strong><?php _e('Adresse', 'pdf-builder-pro'); ?>:</strong> <?php
                            $address = trim(get_option('woocommerce_store_address') . ' ' . get_option('woocommerce_store_address_2') . ' ' . get_option('woocommerce_store_postcode') . ' ' . get_option('woocommerce_store_city'));
                            echo esc_html($address ?: __('Non configurée', 'pdf-builder-pro'));
                        ?> <em>(<?php _e('WooCommerce > Réglages > Général', 'pdf-builder-pro'); ?>)</em></li>
                        <li><strong><?php _e('Téléphone', 'pdf-builder-pro'); ?>:</strong> <?php
                            $phone = get_option('woocommerce_phone') ?: get_option('pdf_builder_company_phone');
                            echo esc_html($phone ?: __('Non configuré', 'pdf-builder-pro'));
                        ?> <em>(<?php _e('WooCommerce > Réglages > Général ou paramètres du plugin', 'pdf-builder-pro'); ?>)</em></li>
                        <li><strong><?php _e('Email', 'pdf-builder-pro'); ?>:</strong> <?php echo esc_html(get_option('woocommerce_email_from_address') ?: __('Non configuré', 'pdf-builder-pro')); ?> <em>(<?php _e('WooCommerce > Réglages > Emails', 'pdf-builder-pro'); ?>)</em></li>
                    </ul>
                </div>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Numéro TVA', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="text" name="company_vat" value="<?php echo esc_attr(get_option('pdf_builder_company_vat', '')); ?>" class="regular-text" placeholder="FR12345678901">
                            <p class="description"><?php _e('Numéro de TVA intracommunautaire (ex: FR12345678901)', 'pdf-builder-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('RCS', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="text" name="company_rcs" value="<?php echo esc_attr(get_option('pdf_builder_company_rcs', '')); ?>" class="regular-text" placeholder="Paris B 123 456 789">
                            <p class="description"><?php _e('Numéro d\'immatriculation au Registre du Commerce et des Sociétés', 'pdf-builder-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('SIRET', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="text" name="company_siret" value="<?php echo esc_attr(get_option('pdf_builder_company_siret', '')); ?>" class="regular-text" placeholder="123 456 789 00012">
                            <p class="description"><?php _e('Numéro SIRET de l\'entreprise (14 chiffres)', 'pdf-builder-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Téléphone', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="text" name="company_phone" value="<?php echo esc_attr(get_option('pdf_builder_company_phone', '')); ?>" class="regular-text" placeholder="+33 1 23 45 67 89">
                            <p class="description"><?php _e('Numéro de téléphone de l\'entreprise (si non configuré dans WooCommerce)', 'pdf-builder-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Onglet Licence -->
            <div id="license" class="tab-content">
                <h2><?php _e('Gestion de la Licence', 'pdf-builder-pro'); ?></h2>

                <?php
                // Classes chargées automatiquement par l'autoloader
                $license_manager = PDF_Builder_License_Manager::getInstance();
                $feature_manager = new PDF_Builder_Feature_Manager();
                $license_info = $license_manager->get_license_info();
                $is_premium = $license_manager->is_premium();

                // Vérifier si le gestionnaire de licence est fonctionnel
                $license_available = method_exists($license_manager, 'activate_license') && method_exists($license_manager, 'get_license_info');

                if ($license_available) {
                    // Traitement de l'activation de licence
                    if (isset($_POST['activate_license']) && check_admin_referer('activate_license', 'license_nonce')) {
                        $license_key = sanitize_text_field($_POST['license_key']);
                        $result = $license_manager->activate_license($license_key);

                        if ($result['success']) {
                            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
                            $license_info = $license_manager->get_license_info(); // Refresh
                            $is_premium = $license_manager->is_premium();
                        } else {
                            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
                        }
                    }

                    // Traitement de la désactivation
                    if (isset($_POST['deactivate_license']) && check_admin_referer('deactivate_license', 'deactivate_nonce')) {
                        $result = $license_manager->deactivate_license();
                        if ($result['success']) {
                            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
                            $license_info = $license_manager->get_license_info(); // Refresh
                            $is_premium = $license_manager->is_premium();
                        } else {
                            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($result['message']) . '</p></div>';
                        }
                    }
                } else {
                    echo '<div class="notice notice-warning"><p>Le système de licence n\'est pas encore disponible. Il sera activé dans la prochaine mise à jour.</p></div>';
                    $is_premium = false;
                    $license_info = ['status' => 'free', 'tier' => 'free'];
                }
                ?>

                <!-- Status de la licence -->
                <div class="license-status-card" style="background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                    <h3 style="margin-top: 0; color: #23282d;"><?php _e('Statut de la Licence', 'pdf-builder-pro'); ?></h3>

                    <div class="license-status-indicator <?php echo esc_attr($license_info['status']); ?>" style="display: inline-block; padding: 8px 16px; border-radius: 4px; font-weight: bold; margin-bottom: 15px;">
                        <?php
                        $status_labels = [
                            'free' => 'Gratuit',
                            'active' => 'Premium Activé',
                            'expired' => 'Expiré',
                            'invalid' => 'Invalide'
                        ];
                        echo esc_html($status_labels[$license_info['status']] ?? ucfirst($license_info['status']));
                        ?>
                    </div>

                    <?php if ($license_info['tier'] !== 'free'): ?>
                        <div style="margin-bottom: 15px;">
                            <strong><?php _e('Niveau :', 'pdf-builder-pro'); ?></strong>
                            <?php echo esc_html(ucfirst($license_info['tier'])); ?>
                            <?php if ($license_info['expires']): ?>
                                <br><strong><?php _e('Expire le :', 'pdf-builder-pro'); ?></strong>
                                <?php echo esc_html($license_info['expires']); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$is_premium): ?>
                        <div class="upgrade-prompt" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-top: 20px;">
                            <h4 style="margin: 0 0 10px 0; color: white;">🔓 Passez à la version Premium</h4>
                            <p style="margin: 0 0 15px 0;">Débloquez toutes les fonctionnalités avancées et créez des PDFs professionnels sans limites !</p>
                            <a href="https://pdfbuilderpro.com/pricing" class="button button-primary" target="_blank" style="background: white; color: #667eea; border: none; font-weight: bold;">
                                <?php _e('Voir les tarifs', 'pdf-builder-pro'); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Activation/Désactivation de licence -->
                <?php if (!$is_premium): ?>
                <div class="license-activation-form" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h3><?php _e('Activer une Licence Premium', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Entrez votre clé de licence pour débloquer toutes les fonctionnalités premium.', 'pdf-builder-pro'); ?></p>

                    <form method="post">
                        <?php wp_nonce_field('activate_license', 'license_nonce'); ?>
                        <table class="form-table" style="background: transparent; margin: 0;">
                            <tr>
                                <th scope="row">
                                    <label for="license_key"><?php _e('Clé de licence', 'pdf-builder-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="text" name="license_key" id="license_key" class="regular-text" placeholder="XXXX-XXXX-XXXX-XXXX" style="min-width: 300px;">
                                    <p class="description">
                                        <?php _e('Vous pouvez trouver votre clé de licence dans votre compte client.', 'pdf-builder-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <p>
                            <input type="submit" name="activate_license" class="button button-primary" value="<?php esc_attr_e('Activer la licence', 'pdf-builder-pro'); ?>">
                        </p>
                    </form>

                    <script type="text/javascript">
                    document.addEventListener('DOMContentLoaded', function() {
                        var licenseForm = document.querySelector('form[action*="activate_license"]');
                        if (licenseForm) {
                            licenseForm.addEventListener('submit', function(e) {
                                var licenseKey = document.getElementById('license_key');
                                if (licenseKey && licenseKey.value.trim() === '') {
                                    alert('<?php _e('Veuillez entrer une clé de licence valide.', 'pdf-builder-pro'); ?>');
                                    licenseKey.focus();
                                    e.preventDefault();
                                    return false;
                                }
                            });
                        }
                    });
                    </script>
                </div>
                <?php else: ?>
                <div class="license-management" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px;">
                    <h3><?php _e('Gestion de la Licence', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Votre licence premium est active. Vous pouvez la désactiver si vous souhaitez la transférer vers un autre site.', 'pdf-builder-pro'); ?></p>

                    <form method="post" onsubmit="return confirm('<?php _e('Êtes-vous sûr de vouloir désactiver cette licence ? Elle pourra être réactivée ultérieurement.', 'pdf-builder-pro'); ?>');">
                        <?php wp_nonce_field('deactivate_license', 'deactivate_nonce'); ?>
                        <p>
                            <input type="submit" name="deactivate_license" class="button button-secondary" value="<?php esc_attr_e('Désactiver la licence', 'pdf-builder-pro'); ?>">
                        </p>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Comparaison des fonctionnalités -->
                <div class="feature-comparison" style="margin-top: 30px;">
                    <h3><?php _e('Comparaison des Fonctionnalités', 'pdf-builder-pro'); ?></h3>

                    <div style="overflow-x: auto;">
                        <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                            <thead>
                                <tr>
                                    <th style="width: 40%;"><?php _e('Fonctionnalité', 'pdf-builder-pro'); ?></th>
                                    <th style="width: 15%; text-align: center;"><?php _e('Gratuit', 'pdf-builder-pro'); ?></th>
                                    <th style="width: 15%; text-align: center;"><?php _e('Premium', 'pdf-builder-pro'); ?></th>
                                    <th style="width: 30%;"><?php _e('Description', 'pdf-builder-pro'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $features = [
                                    ['name' => 'Templates de base', 'free' => true, 'premium' => true, 'desc' => '4 templates prédéfinis'],
                                    ['name' => 'Éléments standards', 'free' => true, 'premium' => true, 'desc' => 'Texte, image, ligne, rectangle'],
                                    ['name' => 'Intégration WooCommerce', 'free' => true, 'premium' => true, 'desc' => 'Variables de commande'],
                                    ['name' => 'Génération PDF', 'free' => '50/mois', 'premium' => 'Illimitée', 'desc' => 'Création de documents'],
                                    ['name' => 'Templates avancés', 'free' => false, 'premium' => true, 'desc' => 'Bibliothèque complète'],
                                    ['name' => 'Éléments premium', 'free' => false, 'premium' => true, 'desc' => 'Codes-barres, QR codes, graphiques'],
                                    ['name' => 'Génération en masse', 'free' => false, 'premium' => true, 'desc' => 'Création multiple'],
                                    ['name' => 'API développeur', 'free' => false, 'premium' => true, 'desc' => 'Accès complet à l\'API'],
                                    ['name' => 'White-label', 'free' => false, 'premium' => true, 'desc' => 'Rebranding complet'],
                                    ['name' => 'Support prioritaire', 'free' => false, 'premium' => true, 'desc' => '24/7 avec SLA']
                                ];

                                foreach ($features as $feature):
                                ?>
                                <tr>
                                    <td><strong><?php echo esc_html($feature['name']); ?></strong></td>
                                    <td style="text-align: center;">
                                        <?php if ($feature['free'] === true): ?>
                                            <span style="color: #46b450;">✓</span>
                                        <?php elseif ($feature['free'] === false): ?>
                                            <span style="color: #dc3232;">✗</span>
                                        <?php else: ?>
                                            <span style="color: #ffb900;"><?php echo esc_html($feature['free']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($feature['premium']): ?>
                                            <span style="color: #46b450;">✓</span>
                                        <?php else: ?>
                                            <span style="color: #dc3232;">✗</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo esc_html($feature['desc']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Onglet Performance -->
            <div id="performance" class="tab-content">
                <h2><?php _e('Paramètres de Performance', 'pdf-builder-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Cache Activé', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="cache_enabled" value="1" <?php checked($config->get('cache_enabled'), true); ?>>
                                <?php _e('Activer la mise en cache pour améliorer les performances', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('TTL Cache', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="cache_ttl" value="<?php echo esc_attr($config->get('cache_ttl')); ?>" class="small-text">
                            <span class="description"><?php _e('Durée de vie du cache en secondes (3600 = 1 heure)', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Temps Max Exécution', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="max_execution_time" value="<?php echo esc_attr($config->get('max_execution_time')); ?>" class="small-text">
                            <span class="description"><?php _e('Temps maximum d\'exécution en secondes', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Limite Mémoire', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="text" name="memory_limit" value="<?php echo esc_attr($config->get('memory_limit')); ?>" class="small-text">
                            <span class="description"><?php _e('Limite mémoire PHP (ex: 256M, 512M)', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Onglet PDF -->
            <div id="pdf" class="tab-content">
                <h2><?php _e('Paramètres PDF', 'pdf-builder-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Qualité PDF', 'pdf-builder-pro'); ?></th>
                        <td>
                            <select name="pdf_quality">
                                <option value="low" <?php selected($config->get('pdf_quality'), 'low'); ?>><?php _e('Basse', 'pdf-builder-pro'); ?></option>
                                <option value="medium" <?php selected($config->get('pdf_quality'), 'medium'); ?>><?php _e('Moyenne', 'pdf-builder-pro'); ?></option>
                                <option value="high" <?php selected($config->get('pdf_quality'), 'high'); ?>><?php _e('Haute', 'pdf-builder-pro'); ?></option>
                                <option value="ultra" <?php selected($config->get('pdf_quality'), 'ultra'); ?>><?php _e('Ultra', 'pdf-builder-pro'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Format par Défaut', 'pdf-builder-pro'); ?></th>
                        <td>
                            <select name="default_format">
                                <option value="A3" <?php selected($config->get('default_format'), 'A3'); ?>>A3</option>
                                <option value="A4" <?php selected($config->get('default_format'), 'A4'); ?>>A4</option>
                                <option value="A5" <?php selected($config->get('default_format'), 'A5'); ?>>A5</option>
                                <option value="Letter" <?php selected($config->get('default_format'), 'Letter'); ?>>Letter</option>
                                <option value="Legal" <?php selected($config->get('default_format'), 'Legal'); ?>>Legal</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Orientation par Défaut', 'pdf-builder-pro'); ?></th>
                        <td>
                            <select name="default_orientation">
                                <option value="portrait" <?php selected($config->get('default_orientation'), 'portrait'); ?>><?php _e('Portrait', 'pdf-builder-pro'); ?></option>
                                <option value="landscape" <?php selected($config->get('default_orientation'), 'landscape'); ?>><?php _e('Paysage', 'pdf-builder-pro'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Onglet Sécurité -->
            <div id="security" class="tab-content">
                <h2><?php _e('Paramètres de Sécurité', 'pdf-builder-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Mode Debug', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label style="display: inline-block; margin-right: 10px;">
                                <input type="checkbox" name="debug_mode" value="1" <?php checked($config->get('debug_mode'), true); ?>>
                                <?php _e('Activer le mode debug pour les logs détaillés', 'pdf-builder-pro'); ?>
                            </label>
                            <button type="button" id="toggle-debug-mode-btn" class="button button-secondary">
                                <?php echo $config->get('debug_mode') ? __('Désactiver Debug', 'pdf-builder-pro') : __('Activer Debug', 'pdf-builder-pro'); ?>
                            </button>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Niveau de Log', 'pdf-builder-pro'); ?></th>
                        <td>
                            <select name="log_level">
                                <option value="debug" <?php selected($config->get('log_level'), 'debug'); ?>><?php _e('Debug', 'pdf-builder-pro'); ?></option>
                                <option value="info" <?php selected($config->get('log_level'), 'info'); ?>><?php _e('Info', 'pdf-builder-pro'); ?></option>
                                <option value="warning" <?php selected($config->get('log_level'), 'warning'); ?>><?php _e('Warning', 'pdf-builder-pro'); ?></option>
                                <option value="error" <?php selected($config->get('log_level'), 'error'); ?>><?php _e('Error', 'pdf-builder-pro'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Taille Max Template', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="max_template_size" value="<?php echo esc_attr($config->get('max_template_size')); ?>" class="small-text">
                            <span class="description"><?php _e('Taille maximale en octets (50MB par défaut)', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Rate Limiting', 'pdf-builder-pro'); ?></th>
                        <td>
                            <p><?php _e('Le rate limiting est automatiquement activé pour prévenir les abus.', 'pdf-builder-pro'); ?></p>
                            <p><strong><?php _e('Limite actuelle:', 'pdf-builder-pro'); ?></strong> <?php echo $config->get('max_requests_per_minute'); ?> <?php _e('requêtes par minute', 'pdf-builder-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Durée du Nonce', 'pdf-builder-pro'); ?></th>
                        <td>
                            <p><?php _e('Les nonces expirent après 24 heures pour plus de sécurité.', 'pdf-builder-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Onglet Rôles -->
            <div id="roles" class="tab-content">
                <h2><?php _e('Gestion des Rôles et Permissions', 'pdf-builder-pro'); ?></h2>

                <div class="roles-management">
                    <p><?php _e('Sélectionnez les rôles WordPress qui auront accès aux fonctionnalités PDF Builder Pro.', 'pdf-builder-pro'); ?></p>

                    <div class="roles-section">
                        <h3><?php _e('Rôles Autorises', 'pdf-builder-pro'); ?></h3>

                        <?php
                        // Récupérer tous les rôles WordPress
                        global $wp_roles;
                        $all_roles = $wp_roles->roles;

                        // Rôles actuellement autorisés (récupérés depuis les options)
                        $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
                        if (!is_array($allowed_roles)) {
                            $allowed_roles = ['administrator', 'editor', 'shop_manager'];
                        }
                        ?>

                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="pdf_builder_allowed_roles"><?php _e('Rôles avec Accès', 'pdf-builder-pro'); ?></label>
                                </th>
                                <td>
                                    <!-- Boutons de sélection rapide -->
                                    <div class="role-selection-controls" style="margin-bottom: 10px;">
                                        <button type="button" id="select-all-roles" class="button button-secondary" style="margin-right: 5px;">
                                            <?php _e('Sélectionner Tout', 'pdf-builder-pro'); ?>
                                        </button>
                                        <button type="button" id="select-none-roles" class="button button-secondary" style="margin-right: 5px;">
                                            <?php _e('Désélectionner Tout', 'pdf-builder-pro'); ?>
                                        </button>
                                        <button type="button" id="select-common-roles" class="button button-secondary" style="margin-right: 5px;">
                                            <?php _e('Rôles Courants', 'pdf-builder-pro'); ?>
                                        </button>
                                        <span class="description" style="margin-left: 10px;">
                                            <?php printf(__('Sélectionnés: <strong id="selected-count">%d</strong> rôle(s)', 'pdf-builder-pro'), count($allowed_roles)); ?>
                                        </span>
                                    </div>

                                    <select name="pdf_builder_allowed_roles[]" id="pdf_builder_allowed_roles" multiple="multiple" class="widefat" style="height: 200px;" required>
                                        <?php foreach ($all_roles as $role_key => $role):
                                            $role_name = translate_user_role($role['name']);
                                            $is_selected = in_array($role_key, $allowed_roles);
                                            $role_description = $config->get_role_description($role_key);
                                        ?>
                                            <option value="<?php echo esc_attr($role_key); ?>"
                                                    <?php selected($is_selected); ?>
                                                    title="<?php echo esc_attr($role_description); ?>">
                                                <?php echo esc_html($role_name); ?>
                                                <?php if ($role_key === 'administrator'): ?>
                                                    <em>(<?php _e('Accès complet', 'pdf-builder-pro'); ?>)</em>
                                                <?php else: ?>
                                                    <em>(<?php echo esc_html($role_key); ?>)</em>
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>

                                    <div class="role-validation-message" style="display: none; color: #dc3232; margin-top: 5px;" id="role-validation-error">
                                        <?php _e('⚠️ Vous devez sélectionner au moins un rôle pour éviter de bloquer l\'accès à PDF Builder Pro.', 'pdf-builder-pro'); ?>
                                    </div>

                                    <p class="description">
                                        <?php _e('Sélectionnez les rôles qui auront accès à PDF Builder Pro.', 'pdf-builder-pro'); ?><br>
                                        <?php _e('💡 Conseil: Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs rôles à la fois.', 'pdf-builder-pro'); ?><br>
                                        <?php _e('📝 Note: Les rôles personnalisés ajoutés par d\'autres plugins apparaîtront automatiquement dans cette liste.', 'pdf-builder-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        <div class="roles-info">
                            <div class="notice notice-info inline">
                                <p>
                                    <strong><?php _e('🔐 Permissions Incluses:', 'pdf-builder-pro'); ?></strong><br>
                                    <?php _e('Les rôles sélectionnés auront un accès complet à :', 'pdf-builder-pro'); ?>
                                </p>
                                <ul style="margin-left: 20px; margin-top: 5px;">
                                    <li><?php _e('✅ Création, édition et suppression de templates PDF', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('✅ Génération et téléchargement de PDF', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('✅ Accès aux paramètres et à la configuration', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('✅ Prévisualisation des PDF avant génération', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('✅ Gestion des commandes WooCommerce (si applicable)', 'pdf-builder-pro'); ?></li>
                                </ul>
                            </div>

                            <div class="notice notice-warning inline" style="margin-top: 10px;">
                                <p>
                                    <strong><?php _e('⚠️ Important:', 'pdf-builder-pro'); ?></strong><br>
                                    <?php _e('Les rôles non sélectionnés n\'auront aucun accès à PDF Builder Pro.', 'pdf-builder-pro'); ?><br>
                                    <?php _e('Le rôle "Administrator" a toujours accès complet, même s\'il n\'est pas sélectionné.', 'pdf-builder-pro'); ?>
                                </p>
                            </div>

                            <div class="notice notice-success inline" style="margin-top: 10px;">
                                <p>
                                    <strong><?php _e('💡 Conseils d\'utilisation:', 'pdf-builder-pro'); ?></strong>
                                </p>
                                <ul style="margin-left: 20px; margin-top: 5px;">
                                    <li><?php _e('Pour une utilisation basique: sélectionnez "Administrator" et "Editor"', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('Pour les boutiques WooCommerce: ajoutez "Shop Manager"', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('Utilisez le bouton "Rôles Courants" pour une configuration rapide', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('Survolez les rôles avec la souris pour voir leur description détaillée', 'pdf-builder-pro'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="roles-save-section" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                        <h3><?php _e('Sauvegarder les Paramètres', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Cliquez sur le bouton ci-dessous pour sauvegarder les modifications apportées aux rôles et permissions.', 'pdf-builder-pro'); ?></p>

                        <p style="margin-top: 15px;">
                            <button type="button" class="button button-primary ajax-save-btn" data-section="roles" style="cursor: pointer;">
                                <?php _e('Enregistrer les Rôles et Permissions', 'pdf-builder-pro'); ?>
                            </button>
                            <span style="margin-left: 10px; color: #666;">
                                <?php _e('💡 Vous pouvez aussi utiliser le bouton principal en bas de la page.', 'pdf-builder-pro'); ?>
                            </span>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Onglet Notifications -->
            <div id="notifications" class="tab-content">
                <h2><?php _e('Paramètres de Notifications', 'pdf-builder-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Notifications par Email', 'pdf-builder-pro'); ?></th>
                        <td>
                            <fieldset>
                                <label for="email_notifications">
                                    <input name="email_notifications" type="checkbox" id="email_notifications" value="1" <?php checked(get_option('pdf_builder_email_notifications', true)); ?>>
                                    <?php _e('Activer les notifications par email pour les erreurs et avertissements', 'pdf-builder-pro'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Email Administrateur', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input name="admin_email" type="email" id="admin_email" value="<?php echo esc_attr(get_option('pdf_builder_admin_email', get_option('admin_email'))); ?>" class="regular-text">
                            <p class="description"><?php _e('Adresse email pour recevoir les notifications système.', 'pdf-builder-pro'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Niveau de Log pour Notifications', 'pdf-builder-pro'); ?></th>
                        <td>
                            <select name="notification_log_level">
                                <option value="error" <?php selected(get_option('pdf_builder_notification_log_level'), 'error'); ?>><?php _e('Erreurs uniquement', 'pdf-builder-pro'); ?></option>
                                <option value="warning" <?php selected(get_option('pdf_builder_notification_log_level'), 'warning'); ?>><?php _e('Erreurs et avertissements', 'pdf-builder-pro'); ?></option>
                                <option value="info" <?php selected(get_option('pdf_builder_notification_log_level'), 'info'); ?>><?php _e('Tous les événements importants', 'pdf-builder-pro'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>

                <div class="notifications-save-section" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                    <h3><?php _e('Sauvegarder les Paramètres', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Cliquez sur le bouton ci-dessous pour sauvegarder les modifications apportées aux paramètres de notifications.', 'pdf-builder-pro'); ?></p>

                    <p style="margin-top: 15px;">
                        <button type="button" class="button button-primary ajax-save-btn" data-section="notifications" style="cursor: pointer;">
                            <?php _e('Enregistrer les Paramètres de Notifications', 'pdf-builder-pro'); ?>
                        </button>
                        <span style="margin-left: 10px; color: #666;">
                            <?php _e('💡 Vous pouvez aussi utiliser le bouton principal en bas de la page.', 'pdf-builder-pro'); ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Onglet Canvas -->
            <div id="canvas" class="tab-content">
                <h2><?php _e('Paramètres Canvas', 'pdf-builder-pro'); ?></h2>

                <!-- SYSTÈME D'ONGLETS MODERNE ET ROBUSTE -->
                <div class="modern-tabs-container">
                    <div class="modern-tabs-header">
                        <button type="button" class="modern-tab-button active" data-tab="general">
                            <?php _e('Général', 'pdf-builder-pro'); ?>
                        </button>
                        <button type="button" class="modern-tab-button" data-tab="grid">
                            <?php _e('Grille & Aimants', 'pdf-builder-pro'); ?>
                        </button>
                        <button type="button" class="modern-tab-button" data-tab="zoom">
                            <?php _e('Zoom & Navigation', 'pdf-builder-pro'); ?>
                        </button>
                        <button type="button" class="modern-tab-button" data-tab="selection">
                            <?php _e('Sélection & Manipulation', 'pdf-builder-pro'); ?>
                        </button>
                        <button type="button" class="modern-tab-button" data-tab="export">
                            <?php _e('Export & Qualité', 'pdf-builder-pro'); ?>
                        </button>
                        <button type="button" class="modern-tab-button" data-tab="advanced">
                            <?php _e('Avancé', 'pdf-builder-pro'); ?>
                        </button>
                    </div>

                    <div class="modern-tabs-content">
                        <!-- Onglet Général -->
                        <div class="modern-tab-panel active" data-tab="general">
                            <h3><?php _e('Paramètres Généraux du Canvas', 'pdf-builder-pro'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Dimensions par Défaut', 'pdf-builder-pro'); ?></th>
                            <td>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="default_canvas_width"><?php _e('Largeur:', 'pdf-builder-pro'); ?></label>
                                        <input name="default_canvas_width" type="number" id="default_canvas_width" value="<?php echo esc_attr($config->get('default_canvas_width', 794)); ?>" class="small-text" min="50" max="2000"> px
                                    </div>
                                    <div>
                                        <label for="default_canvas_height"><?php _e('Hauteur:', 'pdf-builder-pro'); ?></label>
                                        <input name="default_canvas_height" type="number" id="default_canvas_height" value="<?php echo esc_attr($config->get('default_canvas_height', 1123)); ?>" class="small-text" min="50" max="2000"> px
                                    </div>
                                    <div>
                                        <label for="default_canvas_unit"><?php _e('Unité:', 'pdf-builder-pro'); ?></label>
                                        <select name="default_canvas_unit" id="default_canvas_unit">
                                            <option value="mm" <?php selected($config->get('default_canvas_unit', 'mm'), 'mm'); ?>>mm</option>
                                            <option value="cm" <?php selected($config->get('default_canvas_unit', 'mm'), 'cm'); ?>>cm</option>
                                            <option value="in" <?php selected($config->get('default_canvas_unit', 'mm'), 'in'); ?>>inches</option>
                                            <option value="px" <?php selected($config->get('default_canvas_unit', 'mm'), 'px'); ?>>pixels</option>
                                        </select>
                                    </div>
                                </div>
                                <p class="description"><?php _e('Dimensions par défaut pour les nouveaux documents PDF.', 'pdf-builder-pro'); ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Orientation par Défaut', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="default_orientation_portrait">
                                        <input name="default_orientation" type="radio" id="default_orientation_portrait" value="portrait" <?php checked($config->get('default_orientation', 'portrait'), 'portrait'); ?>>
                                        <?php _e('Portrait', 'pdf-builder-pro'); ?>
                                    </label>
                                    <br>
                                    <label for="default_orientation_landscape">
                                        <input name="default_orientation" type="radio" id="default_orientation_landscape" value="landscape" <?php checked($config->get('default_orientation', 'portrait'), 'landscape'); ?>>
                                        <?php _e('Paysage', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Fond du Canvas', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="canvas_background_color">
                                        <input name="canvas_background_color" type="color" id="canvas_background_color" value="<?php echo esc_attr($config->get('canvas_background_color', '#ffffff')); ?>">
                                        <?php _e('Couleur de fond', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="canvas_show_transparency">
                                        <input name="canvas_show_transparency" type="checkbox" id="canvas_show_transparency" value="1" <?php checked($config->get('canvas_show_transparency', false)); ?>>
                                        <?php _e('Afficher la transparence (motif de damier)', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Arrière-plan du Canvas', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="container_background_color">
                                        <input name="container_background_color" type="color" id="container_background_color" value="<?php echo esc_attr($config->get('container_background_color', '#f8f9fa')); ?>">
                                        <?php _e('Couleur de fond du container', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="container_show_transparency">
                                        <input name="container_show_transparency" type="checkbox" id="container_show_transparency" value="1" <?php checked($config->get('container_show_transparency', false)); ?>>
                                        <?php _e('Afficher la transparence (motif de damier)', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Marges de Sécurité', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="show_margins">
                                        <input name="show_margins" type="checkbox" id="show_margins" value="1" <?php checked($config->get('show_margins', true)); ?>>
                                        <?php _e('Afficher les marges de sécurité dans l\'éditeur', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="display: flex; gap: 10px; align-items: center;">
                                    <label for="margin_top"><?php _e('Haut:', 'pdf-builder-pro'); ?></label>
                                    <input name="margin_top" type="number" id="margin_top" value="<?php echo esc_attr($config->get('margin_top', 10)); ?>" class="tiny-text" min="0" max="50"> mm

                                    <label for="margin_right"><?php _e('Droite:', 'pdf-builder-pro'); ?></label>
                                    <input name="margin_right" type="number" id="margin_right" value="<?php echo esc_attr($config->get('margin_right', 10)); ?>" class="tiny-text" min="0" max="50"> mm

                                    <label for="margin_bottom"><?php _e('Bas:', 'pdf-builder-pro'); ?></label>
                                    <input name="margin_bottom" type="number" id="margin_bottom" value="<?php echo esc_attr($config->get('margin_bottom', 10)); ?>" class="tiny-text" min="0" max="50"> mm

                                    <label for="margin_left"><?php _e('Gauche:', 'pdf-builder-pro'); ?></label>
                                    <input name="margin_left" type="number" id="margin_left" value="<?php echo esc_attr($config->get('margin_left', 10)); ?>" class="tiny-text" min="0" max="50"> mm
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="canvas-settings-notice" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                        <h4 style="margin-top: 0; color: #495057;"><?php _e('💡 Conseils d\'utilisation', 'pdf-builder-pro'); ?></h4>
                        <ul style="margin: 10px 0; padding-left: 20px; color: #6c757d;">
                            <li><?php _e('Les dimensions A4 (794x1123px) sont standard pour les documents de format portrait', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('L\'unité "px" (pixels) est utilisée pour le rendu canvas', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Activez l\'affichage des marges pour un meilleur contrôle de la mise en page', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Le motif de damier facilite la visualisation des zones transparentes', 'pdf-builder-pro'); ?></li>
                        </ul>
                    </div>

                    <div class="canvas-save-section" style="margin-top: 20px; padding: 15px; background: #e8f5e8; border: 1px solid #c3e6c3; border-radius: 6px;">
                        <p style="margin: 0; color: #2d5a2d;">
                            <strong><?php _e('💾 Sauvegarder les paramètres généraux', 'pdf-builder-pro'); ?></strong><br>
                            <?php _e('Cliquez sur le bouton principal "Enregistrer les paramètres" en bas de la page.', 'pdf-builder-pro'); ?>
                        </p>
                    </div>
                        </div>

                        <!-- Onglet Grille & Aimants -->
                        <div class="modern-tab-panel" data-tab="grid">
                            <h3><?php _e('Paramètres de Grille et Aimantation', 'pdf-builder-pro'); ?></h3>

                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Grille d\'Alignement', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="show_grid">
                                                <input name="show_grid" type="checkbox" id="show_grid" value="1" <?php checked($config->get('show_grid', true)); ?>>
                                                <?php _e('Afficher la grille d\'alignement dans l\'éditeur', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <div style="display: flex; gap: 20px; align-items: center;">
                                            <div>
                                                <label for="grid_size"><?php _e('Taille de la grille:', 'pdf-builder-pro'); ?></label>
                                                <input name="grid_size" type="number" id="grid_size" value="<?php echo esc_attr($config->get('grid_size', 10)); ?>" class="small-text" min="5" max="50" step="5"> px
                                            </div>
                                            <div>
                                                <label for="grid_color"><?php _e('Couleur:', 'pdf-builder-pro'); ?></label>
                                                <input name="grid_color" type="color" id="grid_color" value="<?php echo esc_attr($config->get('grid_color', '#e0e0e0')); ?>">
                                            </div>
                                            <div>
                                                <label for="grid_opacity"><?php _e('Opacité:', 'pdf-builder-pro'); ?></label>
                                                <input name="grid_opacity" type="range" id="grid_opacity" min="10" max="100" value="<?php echo esc_attr($config->get('grid_opacity', 30)); ?>" style="width: 80px;">
                                                <span id="grid_opacity_value"><?php echo esc_attr($config->get('grid_opacity', 30)); ?>%</span>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Aimantation', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="snap_to_grid">
                                                <input name="snap_to_grid" type="checkbox" id="snap_to_grid" value="1" <?php checked($config->get('snap_to_grid', true)); ?>>
                                                <?php _e('Activer l\'aimantation à la grille', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="snap_to_elements">
                                                <input name="snap_to_elements" type="checkbox" id="snap_to_elements" value="1" <?php checked($config->get('snap_to_elements', true)); ?>>
                                                <?php _e('Activer l\'aimantation aux autres éléments', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="snap_to_margins">
                                                <input name="snap_to_margins" type="checkbox" id="snap_to_margins" value="1" <?php checked($config->get('snap_to_margins', true)); ?>>
                                                <?php _e('Activer l\'aimantation aux marges de sécurité', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <div style="margin-top: 10px;">
                                            <label for="snap_tolerance"><?php _e('Tolérance d\'aimantation:', 'pdf-builder-pro'); ?></label>
                                            <input name="snap_tolerance" type="number" id="snap_tolerance" value="<?php echo esc_attr($config->get('snap_tolerance', 5)); ?>" class="small-text" min="1" max="20"> px
                                            <p class="description"><?php _e('Distance maximale pour l\'aimantation automatique.', 'pdf-builder-pro'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Guides', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="show_guides">
                                                <input name="show_guides" type="checkbox" id="show_guides" value="1" <?php checked($config->get('show_guides', true)); ?>>
                                                <?php _e('Afficher les lignes guides personnalisables', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="lock_guides">
                                                <input name="lock_guides" type="checkbox" id="lock_guides" value="1" <?php checked($config->get('lock_guides', false)); ?>>
                                                <?php _e('Verrouiller les guides (empêcher le déplacement accidentel)', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>

                            <div class="canvas-settings-notice" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                                <h4 style="margin-top: 0; color: #495057;"><?php _e('💡 Conseils d\'utilisation', 'pdf-builder-pro'); ?></h4>
                                <ul style="margin: 10px 0; padding-left: 20px; color: #6c757d;">
                                    <li><?php _e('Une grille de 10px est idéale pour l\'alignement précis des éléments', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('Activez l\'aimantation aux éléments pour un alignement automatique', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('Les guides peuvent être déplacés et verrouillés pour des références permanentes', 'pdf-builder-pro'); ?></li>
                                    <li><?php _e('Une tolérance de 5px offre un bon équilibre entre précision et facilité d\'usage', 'pdf-builder-pro'); ?></li>
                                </ul>
                            </div>

                            <div class="canvas-save-section" style="margin-top: 20px; padding: 15px; background: #e8f5e8; border: 1px solid #c3e6c3; border-radius: 6px;">
                                <p style="margin: 0; color: #2d5a2d;">
                                    <strong><?php _e('💾 Sauvegarder les paramètres de grille', 'pdf-builder-pro'); ?></strong><br>
                                    <?php _e('Cliquez sur le bouton principal "Enregistrer les paramètres" en bas de la page.', 'pdf-builder-pro'); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Onglet Zoom & Navigation -->
                        <div class="modern-tab-panel" data-tab="zoom">
                            <h3><?php _e('Paramètres de Zoom et Navigation', 'pdf-builder-pro'); ?></h3>

                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Zoom par Défaut', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <select name="default_zoom" id="default_zoom">
                                            <option value="25" <?php selected($config->get('default_zoom', '100'), '25'); ?>>25%</option>
                                            <option value="50" <?php selected($config->get('default_zoom', '100'), '50'); ?>>50%</option>
                                            <option value="75" <?php selected($config->get('default_zoom', '100'), '75'); ?>>75%</option>
                                            <option value="100" <?php selected($config->get('default_zoom', '100'), '100'); ?>>100%</option>
                                            <option value="125" <?php selected($config->get('default_zoom', '100'), '125'); ?>>125%</option>
                                            <option value="150" <?php selected($config->get('default_zoom', '100'), '150'); ?>>150%</option>
                                            <option value="200" <?php selected($config->get('default_zoom', '100'), '200'); ?>>200%</option>
                                        </select>
                                        <p class="description"><?php _e('Niveau de zoom affiché lors de l\'ouverture d\'un document.', 'pdf-builder-pro'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Limites de Zoom', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <div style="display: flex; gap: 20px; align-items: center;">
                                            <div>
                                                <label for="min_zoom"><?php _e('Minimum:', 'pdf-builder-pro'); ?></label>
                                                <input name="min_zoom" type="number" id="min_zoom" value="<?php echo esc_attr($config->get('min_zoom', 10)); ?>" class="small-text" min="5" max="50"> %
                                            </div>
                                            <div>
                                                <label for="max_zoom"><?php _e('Maximum:', 'pdf-builder-pro'); ?></label>
                                                <input name="max_zoom" type="number" id="max_zoom" value="<?php echo esc_attr($config->get('max_zoom', 500)); ?>" class="small-text" min="100" max="1000"> %
                                            </div>
                                            <div>
                                                <label for="zoom_step"><?php _e('Pas:', 'pdf-builder-pro'); ?></label>
                                                <input name="zoom_step" type="number" id="zoom_step" value="<?php echo esc_attr($config->get('zoom_step', 25)); ?>" class="small-text" min="5" max="50"> %
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Contrôles de Navigation', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="pan_with_mouse">
                                                <input name="pan_with_mouse" type="checkbox" id="pan_with_mouse" value="1" <?php checked($config->get('pan_with_mouse', true)); ?>>
                                                <?php _e('Déplacer la vue avec le bouton central de la souris', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="zoom_with_wheel">
                                                <input name="zoom_with_wheel" type="checkbox" id="zoom_with_wheel" value="1" <?php checked($config->get('zoom_with_wheel', true)); ?>>
                                                <?php _e('Zoomer avec la molette de la souris', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="smooth_zoom">
                                                <input name="smooth_zoom" type="checkbox" id="smooth_zoom" value="1" <?php checked($config->get('smooth_zoom', true)); ?>>
                                                <?php _e('Animation fluide lors du zoom', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="show_zoom_indicator">
                                                <input name="show_zoom_indicator" type="checkbox" id="show_zoom_indicator" value="1" <?php checked($config->get('show_zoom_indicator', true)); ?>>
                                                <?php _e('Afficher l\'indicateur de niveau de zoom', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Onglet Sélection & Manipulation -->
                        <div class="modern-tab-panel" data-tab="selection">
                            <h3><?php _e('Paramètres de Sélection et Manipulation', 'pdf-builder-pro'); ?></h3>

                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Poignées de Redimensionnement', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="show_resize_handles">
                                                <input name="show_resize_handles" type="checkbox" id="show_resize_handles" value="1" <?php checked($config->get('show_resize_handles', true)); ?>>
                                                <?php _e('Afficher les poignées de redimensionnement', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <div style="display: flex; gap: 20px; align-items: center; margin-top: 10px;">
                                            <div>
                                                <label for="handle_size"><?php _e('Taille des poignées:', 'pdf-builder-pro'); ?></label>
                                                <input name="handle_size" type="number" id="handle_size" value="<?php echo esc_attr($config->get('handle_size', 8)); ?>" class="small-text" min="4" max="20"> px
                                            </div>
                                            <div>
                                                <label for="handle_color"><?php _e('Couleur:', 'pdf-builder-pro'); ?></label>
                                                <input name="handle_color" type="color" id="handle_color" value="<?php echo esc_attr($config->get('handle_color', '#007cba')); ?>">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Rotation', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="enable_rotation">
                                                <input name="enable_rotation" type="checkbox" id="enable_rotation" value="1" <?php checked($config->get('enable_rotation', true)); ?>>
                                                <?php _e('Autoriser la rotation des éléments', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <div style="display: flex; gap: 20px; align-items: center; margin-top: 10px;">
                                            <div>
                                                <label for="rotation_step"><?php _e('Pas de rotation:', 'pdf-builder-pro'); ?></label>
                                                <input name="rotation_step" type="number" id="rotation_step" value="<?php echo esc_attr($config->get('rotation_step', 15)); ?>" class="small-text" min="1" max="45"> °
                                            </div>
                                            <fieldset style="margin: 0;">
                                                <label for="rotation_snap">
                                                    <input name="rotation_snap" type="checkbox" id="rotation_snap" value="1" <?php checked($config->get('rotation_snap', true)); ?>>
                                                    <?php _e('Aimantation angulaire', 'pdf-builder-pro'); ?>
                                                </label>
                                            </fieldset>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Sélection Multiple', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="multi_select">
                                                <input name="multi_select" type="checkbox" id="multi_select" value="1" <?php checked($config->get('multi_select', true)); ?>>
                                                <?php _e('Autoriser la sélection de plusieurs éléments', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="select_all_shortcut">
                                                <input name="select_all_shortcut" type="checkbox" id="select_all_shortcut" value="1" <?php checked($config->get('select_all_shortcut', true)); ?>>
                                                <?php _e('Raccourci Ctrl+A pour tout sélectionner', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="show_selection_bounds">
                                                <input name="show_selection_bounds" type="checkbox" id="show_selection_bounds" value="1" <?php checked($config->get('show_selection_bounds', true)); ?>>
                                                <?php _e('Afficher les limites de la sélection multiple', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Copier-Coller', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="copy_paste_enabled">
                                                <input name="copy_paste_enabled" type="checkbox" id="copy_paste_enabled" value="1" <?php checked($config->get('copy_paste_enabled', true)); ?>>
                                                <?php _e('Activer les fonctions copier-coller', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <fieldset>
                                            <label for="duplicate_on_drag">
                                                <input name="duplicate_on_drag" type="checkbox" id="duplicate_on_drag" value="1" <?php checked($config->get('duplicate_on_drag', false)); ?>>
                                                <?php _e('Dupliquer l\'élément lors du glisser avec Ctrl', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Onglet Export & Qualité -->
                        <div class="modern-tab-panel" data-tab="export">
                            <h3><?php _e('Paramètres d\'Export et Qualité', 'pdf-builder-pro'); ?></h3>

                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Qualité d\'Export', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <select name="export_quality" id="export_quality">
                                            <option value="draft" <?php selected($config->get('export_quality', 'print'), 'draft'); ?>>Brouillon (rapide)</option>
                                            <option value="standard" <?php selected($config->get('export_quality', 'print'), 'standard'); ?>>Standard</option>
                                            <option value="print" <?php selected($config->get('export_quality', 'print'), 'print'); ?>>Impression (recommandé)</option>
                                            <option value="high" <?php selected($config->get('export_quality', 'print'), 'high'); ?>>Haute qualité</option>
                                        </select>
                                        <p class="description"><?php _e('Qualité du PDF généré. Une qualité plus élevée produit des fichiers plus volumineux.', 'pdf-builder-pro'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Format d\'Export', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <select name="export_format" id="export_format">
                                            <option value="pdf" <?php selected($config->get('export_format', 'pdf'), 'pdf'); ?>>PDF</option>
                                            <option value="png" <?php selected($config->get('export_format', 'pdf'), 'png'); ?>>PNG (image)</option>
                                            <option value="jpg" <?php selected($config->get('export_format', 'pdf'), 'jpg'); ?>>JPG (image)</option>
                                        </select>
                                        <p class="description"><?php _e('Format de fichier pour l\'export.', 'pdf-builder-pro'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Compression d\'Images', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="compress_images">
                                                <input name="compress_images" type="checkbox" id="compress_images" value="1" <?php checked($config->get('compress_images', true)); ?>>
                                                <?php _e('Compresser les images pour réduire la taille du fichier', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <div style="margin-top: 10px;">
                                            <label for="image_quality"><?php _e('Qualité des images:', 'pdf-builder-pro'); ?></label>
                                            <input name="image_quality" type="range" id="image_quality" min="50" max="100" value="<?php echo esc_attr($config->get('image_quality', 85)); ?>" style="width: 150px;">
                                            <span id="image_quality_value"><?php echo esc_attr($config->get('image_quality', 85)); ?>%</span>
                                            <p class="description"><?php _e('Qualité de compression des images (plus élevé = meilleure qualité, fichier plus volumineux).', 'pdf-builder-pro'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Métadonnées PDF', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <div style="display: flex; gap: 20px; align-items: center;">
                                            <div>
                                                <label for="pdf_author"><?php _e('Auteur:', 'pdf-builder-pro'); ?></label>
                                                <input name="pdf_author" type="text" id="pdf_author" value="<?php echo esc_attr($config->get('pdf_author', get_bloginfo('name'))); ?>" style="width: 200px;">
                                            </div>
                                            <div>
                                                <label for="pdf_subject"><?php _e('Sujet:', 'pdf-builder-pro'); ?></label>
                                                <input name="pdf_subject" type="text" id="pdf_subject" value="<?php echo esc_attr($config->get('pdf_subject', '')); ?>" style="width: 200px;">
                                            </div>
                                        </div>
                                        <br>
                                        <fieldset>
                                            <label for="include_metadata">
                                                <input name="include_metadata" type="checkbox" id="include_metadata" value="1" <?php checked($config->get('include_metadata', true)); ?>>
                                                <?php _e('Inclure les métadonnées dans le PDF', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>
                            </table>
                        </div>

                        <!-- Onglet Avancé -->
                        <div class="modern-tab-panel" data-tab="advanced">
                            <h3><?php _e('Paramètres Avancés', 'pdf-builder-pro'); ?></h3>

                            <table class="form-table">
                                <tr>
                                    <th scope="row"><?php _e('Performance', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="enable_hardware_acceleration">
                                                <input name="enable_hardware_acceleration" type="checkbox" id="enable_hardware_acceleration" value="1" <?php checked($config->get('enable_hardware_acceleration', true)); ?>>
                                                <?php _e('Activer l\'accélération matérielle (GPU)', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <div style="margin-top: 10px;">
                                            <label for="max_fps"><?php _e('FPS maximum:', 'pdf-builder-pro'); ?></label>
                                            <input name="max_fps" type="number" id="max_fps" value="<?php echo esc_attr($config->get('max_fps', 60)); ?>" class="small-text" min="30" max="120">
                                            <span><?php _e('images par seconde', 'pdf-builder-pro'); ?></span>
                                            <p class="description"><?php _e('Limite la fréquence d\'images pour économiser les ressources.', 'pdf-builder-pro'); ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Sauvegarde Automatique', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="auto_save_enabled">
                                                <input name="auto_save_enabled" type="checkbox" id="auto_save_enabled" value="1" <?php checked($config->get('auto_save_enabled', true)); ?>>
                                                <?php _e('Activer la sauvegarde automatique', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <br>
                                        <div style="display: flex; gap: 20px; align-items: center; margin-top: 10px;">
                                            <div>
                                                <label for="auto_save_interval"><?php _e('Intervalle:', 'pdf-builder-pro'); ?></label>
                                                <input name="auto_save_interval" type="number" id="auto_save_interval" value="<?php echo esc_attr($config->get('auto_save_interval', 30)); ?>" class="small-text" min="5" max="300"> sec
                                            </div>
                                            <div>
                                                <label for="auto_save_versions"><?php _e('Versions max:', 'pdf-builder-pro'); ?></label>
                                                <input name="auto_save_versions" type="number" id="auto_save_versions" value="<?php echo esc_attr($config->get('auto_save_versions', 10)); ?>" class="small-text" min="1" max="50">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Historique d\'Actions', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <div style="display: flex; gap: 20px; align-items: center;">
                                            <div>
                                                <label for="undo_levels"><?php _e('Niveaux d\'annulation:', 'pdf-builder-pro'); ?></label>
                                                <input name="undo_levels" type="number" id="undo_levels" value="<?php echo esc_attr($config->get('undo_levels', 50)); ?>" class="small-text" min="10" max="200">
                                            </div>
                                            <div>
                                                <label for="redo_levels"><?php _e('Niveaux de rétablissement:', 'pdf-builder-pro'); ?></label>
                                                <input name="redo_levels" type="number" id="redo_levels" value="<?php echo esc_attr($config->get('redo_levels', 50)); ?>" class="small-text" min="10" max="200">
                                            </div>
                                        </div>
                                        <p class="description"><?php _e('Nombre maximum d\'actions annulables/rétablissables.', 'pdf-builder-pro'); ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row"><?php _e('Raccourcis Clavier', 'pdf-builder-pro'); ?></th>
                                    <td>
                                        <fieldset>
                                            <label for="enable_keyboard_shortcuts">
                                                <input name="enable_keyboard_shortcuts" type="checkbox" id="enable_keyboard_shortcuts" value="1" <?php checked($config->get('enable_keyboard_shortcuts', true)); ?>>
                                                <?php _e('Activer les raccourcis clavier personnalisés', 'pdf-builder-pro'); ?>
                                            </label>
                                        </fieldset>
                                        <p class="description"><?php _e('Permet d\'utiliser des raccourcis clavier pour accélérer le travail.', 'pdf-builder-pro'); ?></p>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- JAVASCRIPT ULTRA SIMPLE POUR LES ONGLES -->
                <script>
                jQuery(document).ready(function($) {

                    // Cacher tous les panneaux sauf le premier
                    $('.modern-tab-panel').not(':first').hide();
                    $('.modern-tab-panel:first').show();

                    // Gérer les clics sur les onglets
                    $('.modern-tab-button').on('click', function(e) {
                        e.preventDefault();

                        var tabName = $(this).data('tab');

                        // Retirer active de tous les onglets
                        $('.modern-tab-button').removeClass('active');
                        // Ajouter active à l'onglet cliqué
                        $(this).addClass('active');

                        // Cacher tous les panneaux
                        $('.modern-tab-panel').hide();
                        // Montrer le panneau correspondant
                        $('.modern-tab-panel[data-tab="' + tabName + '"]').show();

                    });

                });
                </script>
            </div>

            <!-- Onglet Templates -->
            <div id="templates" class="tab-content">
                <h2><?php _e('Templates par Statut de Commande', 'pdf-builder-pro'); ?></h2>

                <p><?php _e('Configurez les templates PDF à utiliser automatiquement selon le statut des commandes WooCommerce.', 'pdf-builder-pro'); ?></p>

                <div class="notice notice-info inline" style="margin-bottom: 20px;">
                    <p><strong><?php _e('ℹ️ Détection automatique :', 'pdf-builder-pro'); ?></strong> <?php _e('Le système détecte automatiquement tous les statuts de commande WooCommerce. Les nouveaux statuts sont ajoutés automatiquement avec le template par défaut assigné.', 'pdf-builder-pro'); ?></p>
                </div>

                <?php
                // Utiliser le StatusManager pour étendre les paramètres
                global $pdf_builder_admin;
                if (isset($pdf_builder_admin) && method_exists($pdf_builder_admin, 'get_status_manager')) {
                    $status_manager = $pdf_builder_admin->get_status_manager();
                    $status_info = $status_manager->get_status_info_for_admin();

                    // Afficher les informations sur les statuts détectés
                    if (!empty($status_info['newly_detected'])) {
                        echo '<div class="notice notice-success inline" style="margin-bottom: 20px;">';
                        echo '<p><strong>' . __('✅ Nouveaux statuts détectés :', 'pdf-builder-pro') . '</strong></p>';
                        echo '<ul style="margin: 10px 0;">';
                        foreach ($status_info['newly_detected'] as $new_status) {
                            $assigned_text = $new_status['auto_assigned'] ?
                                __('Template par défaut assigné', 'pdf-builder-pro') :
                                __('Aucun template par défaut disponible', 'pdf-builder-pro');
                            echo '<li><code>' . esc_html($new_status['key']) . '</code> - ' . esc_html($new_status['name']) . ' (' . $assigned_text . ')</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                }
                ?>

                <table class="form-table">
                    <?php
                    // Utiliser le StatusManager pour récupérer les statuts
                    $order_statuses = [];
                    if (isset($status_manager)) {
                        $order_statuses = $status_manager->detect_woocommerce_statuses();
                    } else {
                        // Fallback vers l'ancienne méthode
                        if (class_exists('WooCommerce') && function_exists('wc_get_order_statuses')) {
                            try {
                                $order_statuses = call_user_func('wc_get_order_statuses');
                            } catch (Exception $e) {
                                $order_statuses = [];
                            }
                        } else {
                            $order_statuses = [
                                'wc-pending' => __('En attente', 'pdf-builder-pro'),
                                'wc-processing' => __('En cours', 'pdf-builder-pro'),
                                'wc-on-hold' => __('En attente', 'pdf-builder-pro'),
                                'wc-completed' => __('Terminée', 'pdf-builder-pro'),
                                'wc-cancelled' => __('Annulée', 'pdf-builder-pro'),
                                'wc-refunded' => __('Remboursée', 'pdf-builder-pro'),
                                'wc-failed' => __('Échec', 'pdf-builder-pro')
                            ];
                        }
                    }

                    // Appliquer le filtre d'extension des paramètres
                    $order_statuses = apply_filters('pdf_builder_order_status_settings', $order_statuses);

                    // Récupérer les mappings actuels
                    $current_mappings = get_option('pdf_builder_order_status_templates', []);

                    // Récupérer la liste des templates disponibles
                    global $wpdb;
                    $table_templates = $wpdb->prefix . 'pdf_builder_templates';
                    $templates = $wpdb->get_results("SELECT id, name FROM $table_templates ORDER BY name", ARRAY_A);

                    foreach ($order_statuses as $status_key => $status_name) {
                        // Enlever le préfixe 'wc-' pour l'affichage
                        $display_status = str_replace('wc-', '', $status_key);
                        $selected_template = isset($current_mappings[$status_key]) ? $current_mappings[$status_key] : '';

                        // Marquer les statuts récemment détectés
                        $is_newly_detected = isset($status_info) && in_array($status_key, array_column($status_info['newly_detected'], 'key'));
                        $row_class = $is_newly_detected ? 'style="background-color: #e8f5e8;"' : '';
                        ?>
                        <tr <?php echo $row_class; ?>>
                            <th scope="row">
                                <label for="template_<?php echo esc_attr($status_key); ?>">
                                    <?php echo esc_html($status_name); ?>
                                    <code>(<?php echo esc_html($display_status); ?>)</code>
                                    <?php if ($is_newly_detected): ?>
                                        <span class="dashicons dashicons-plus" style="color: #46b450;" title="<?php esc_attr_e('Nouveau statut détecté automatiquement', 'pdf-builder-pro'); ?>"></span>
                                    <?php endif; ?>
                                </label>
                            </th>
                            <td>
                                <select name="order_status_templates[<?php echo esc_attr($status_key); ?>]" id="template_<?php echo esc_attr($status_key); ?>" class="regular-text">
                                    <option value=""><?php _e('-- Utiliser le template par défaut --', 'pdf-builder-pro'); ?></option>
                                    <?php foreach ($templates as $template) { ?>
                                        <option value="<?php echo esc_attr($template['id']); ?>" <?php selected($selected_template, $template['id']); ?>>
                                            <?php echo esc_html($template['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <p class="description">
                                    <?php printf(__('Template à utiliser pour les commandes avec le statut "%s".', 'pdf-builder-pro'), esc_html($status_name)); ?>
                                    <?php if ($is_newly_detected): ?>
                                        <br><em style="color: #46b450;"><?php _e('Nouveau statut détecté - Template par défaut assigné automatiquement.', 'pdf-builder-pro'); ?></em>
                                    <?php endif; ?>
                                </p>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>

                <p class="submit">
                    <button type="button" class="button button-primary ajax-save-btn" data-section="templates" style="cursor: pointer;">
                        <?php _e('Enregistrer les Templates', 'pdf-builder-pro'); ?>
                    </button>
                </p>
            </div>

            <!-- Onglet Maintenance -->
            <div id="maintenance" class="tab-content">
                <h2><?php _e('Actions de Maintenance', 'pdf-builder-pro'); ?></h2>

                <div class="maintenance-actions">
                    <!-- Zone de statut pour les actions de maintenance -->
                    <div id="maintenance-status" class="maintenance-status" style="margin-bottom: 20px; display: none;">
                        <!-- Les messages de statut seront affichés ici -->
                    </div>

                    <div class="maintenance-section" style="margin-bottom: 30px;">
                        <h3><?php _e('Nettoyage des Données', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Supprimez les données temporaires et les fichiers obsolètes pour optimiser les performances.', 'pdf-builder-pro'); ?></p>

                        <form method="post" style="display: inline;" id="clear-cache-form">
                            <?php wp_nonce_field('clear_cache', 'clear_cache_nonce'); ?>
                            <input type="submit" name="clear_cache" id="clear-cache-btn" class="button button-secondary" value="<?php esc_attr_e('Vider le Cache', 'pdf-builder-pro'); ?>">
                        </form>

                        <form method="post" style="display: inline; margin-left: 10px;" id="clear-temp-files-form">
                            <?php wp_nonce_field('clear_temp_files', 'clear_temp_files_nonce'); ?>
                            <input type="submit" name="clear_temp_files" id="clear-temp-files-btn" class="button button-secondary" value="<?php esc_attr_e('Supprimer Fichiers Temporaires', 'pdf-builder-pro'); ?>">
                        </form>
                    </div>

                    <div class="maintenance-section" style="margin-bottom: 30px;">
                        <h3><?php _e('Réparation de Données', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Réparez les templates corrompus et les paramètres invalides.', 'pdf-builder-pro'); ?></p>

                        <form method="post" style="display: inline;" id="repair-templates-form">
                            <?php wp_nonce_field('repair_templates', 'repair_templates_nonce'); ?>
                            <input type="submit" name="repair_templates" id="repair-templates-btn" class="button button-secondary" value="<?php esc_attr_e('Réparer Templates', 'pdf-builder-pro'); ?>">
                        </form>

                        <form method="post" style="display: inline; margin-left: 10px;" id="reset-settings-form">
                            <?php wp_nonce_field('reset_settings', 'reset_settings_nonce'); ?>
                            <input type="submit" name="reset_settings" id="reset-settings-btn" class="button button-warning" value="<?php esc_attr_e('Réinitialiser Paramètres', 'pdf-builder-pro'); ?>"
                                   onclick="return confirm('<?php _e('Attention: Cette action va réinitialiser tous les paramètres aux valeurs par défaut. Continuer ?', 'pdf-builder-pro'); ?>');">
                        </form>
                    </div>

                    <div class="maintenance-section" style="margin-bottom: 30px;">
                        <h3><?php _e('Outils de Développement', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Outils pour les développeurs et le débogage.', 'pdf-builder-pro'); ?></p>

                        <div class="debug-controls" style="margin-bottom: 15px;">
                            <?php 
                            $debug_mode = get_option('pdf_builder_debug_mode', false);
                            echo "<script>window.pdfBuilderDebug = " . ($debug_mode ? 'true' : 'false') . ";</script>";
                            ?>
                            <button type="button" id="toggle-debug-btn" class="button button-secondary">
                                <?php echo $debug_mode ? __('Désactiver Debug', 'pdf-builder-pro') : __('Activer Debug', 'pdf-builder-pro'); ?>
                            </button>
                            <span style="margin-left: 10px; font-style: italic; color: #666;">
                                <?php echo $debug_mode ? __('Logs de debug JavaScript activés', 'pdf-builder-pro') : __('Logs de debug JavaScript désactivés', 'pdf-builder-pro'); ?>
                            </span>
                        </div>

                        <div class="debug-info" style="background: #f9f9f9; padding: 10px; border-left: 4px solid #007cba; margin-bottom: 15px;">
                            <p><strong><?php _e('Comment utiliser les logs de debug :', 'pdf-builder-pro'); ?></strong></p>
                            <ul style="margin: 0; padding-left: 20px;">
                                <li><?php _e('Activez les logs ci-dessus', 'pdf-builder-pro'); ?></li>
                                <li><?php _e('Allez dans l\'éditeur React', 'pdf-builder-pro'); ?></li>
                                <li><?php _e('Ouvrez la console du navigateur (F12)', 'pdf-builder-pro'); ?></li>
                                <li><?php _e('Les logs apparaîtront avec des emojis (🚀, ✅, ❌, etc.)', 'pdf-builder-pro'); ?></li>
                            </ul>
                        </div>

                        <form method="post" style="display: inline;" id="clear-debug-logs-form">
                            <?php wp_nonce_field('clear_debug_logs', 'clear_debug_logs_nonce'); ?>
                            <input type="submit" name="clear_debug_logs" id="clear-debug-logs-btn" class="button button-secondary" value="<?php esc_attr_e('Vider Logs Debug', 'pdf-builder-pro'); ?>">
                        </form>
                    </div>

                    <div class="maintenance-section">
                        <h3><?php _e('Informations Système', 'pdf-builder-pro'); ?></h3>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php _e('Version du Plugin', 'pdf-builder-pro'); ?></th>
                                <td><?php echo esc_html(PDF_BUILDER_VERSION); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Espace Disque Utilisé', 'pdf-builder-pro'); ?></th>
                                <td><?php echo size_format($this->get_disk_usage()); ?></td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Nombre de Templates', 'pdf-builder-pro'); ?></th>
                                <td><?php echo $this->get_template_count(); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Onglet Développeur -->
            <div id="developer" class="tab-content">
                <h2><?php _e('🔧 Mode Développeur', 'pdf-builder-pro'); ?></h2>

                <div class="developer-settings">
                    <div class="notice notice-info" style="margin-bottom: 20px;">
                        <p><?php _e('⚠️ <strong>Zone réservée aux développeurs</strong> - Ces paramètres contrôlent l\'accès aux outils de développement avancés.', 'pdf-builder-pro'); ?></p>
                    </div>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Activer le mode développeur', 'pdf-builder-pro'); ?></th>
                            <td>
                                <label for="developer_enabled">
                                    <input type="checkbox" name="pdf_builder_settings[developer_enabled]" id="developer_enabled" value="1"
                                           <?php checked(isset($settings['developer_enabled']) && $settings['developer_enabled']); ?> />
                                    <?php _e('Activer l\'accès aux outils de développement', 'pdf-builder-pro'); ?>
                                </label>
                                <p class="description">
                                    <?php _e('Permet l\'accès à la page "📝 Gestion des Modèles Prédéfinis" et autres outils développeur.', 'pdf-builder-pro'); ?>
                                </p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php _e('Mot de passe développeur', 'pdf-builder-pro'); ?></th>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <input type="password" name="pdf_builder_settings[developer_password]" id="developer_password"
                                           value="<?php echo esc_attr($settings['developer_password'] ?? ''); ?>" class="regular-text" />
                                    <button type="button" id="toggle-password-btn" class="button button-small" style="min-width: auto;">👁️</button>
                                </div>
                                <p class="description">
                                    <?php _e('Mot de passe requis pour accéder aux outils de développement. Laissez vide pour désactiver.', 'pdf-builder-pro'); ?>
                                </p>
                                <?php if (!empty($settings['developer_password'])): ?>
                                <p class="description" style="color: #28a745;">
                                    ✅ Mot de passe configuré (<?php echo strlen($settings['developer_password']); ?> caractères)
                                </p>
                                <?php else: ?>
                                <p class="description" style="color: #dc3545;">
                                    ❌ Aucun mot de passe configuré
                                </p>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><?php _e('État actuel', 'pdf-builder-pro'); ?></th>
                            <td>
                                <?php
                                $is_enabled = isset($settings['developer_enabled']) && $settings['developer_enabled'];
                                $has_password = !empty($settings['developer_password']);
                                ?>
                                <div class="developer-status" id="developer-status-display">
                                    <span class="status-indicator <?php echo $is_enabled ? 'enabled' : 'disabled'; ?>" id="status-enabled">
                                        <?php echo $is_enabled ? '✅' : '❌'; ?> Mode développeur <?php echo $is_enabled ? 'activé' : 'désactivé'; ?>
                                    </span>
                                    <?php if ($is_enabled): ?>
                                        <br>
                                        <span class="status-indicator <?php echo $has_password ? 'enabled' : 'disabled'; ?>" id="status-password">
                                            <?php echo $has_password ? '🔒' : '🔓'; ?> Authentification <?php echo $has_password ? 'activée' : 'désactivée'; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <style>
                                .status-indicator.enabled { color: #28a745; font-weight: bold; }
                                .status-indicator.disabled { color: #dc3545; font-weight: bold; }
                                </style>
                            </td>
                        </tr>
                    </table>

                    <div class="developer-info" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border-left: 4px solid #007cba;">
                        <h3><?php _e('Informations pour les développeurs', 'pdf-builder-pro'); ?></h3>
                        <ul style="margin: 0; padding-left: 20px;">
                            <li><?php _e('La page "📝 Gestion des Modèles Prédéfinis" permet de créer et modifier les modèles prédéfinis.', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Utilisez un mot de passe fort pour sécuriser l\'accès.', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('L\'authentification est liée à la session PHP et expire automatiquement.', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Les développeurs peuvent se déconnecter manuellement via le bouton en haut à droite.', 'pdf-builder-pro'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Enregistrer les paramètres', 'pdf-builder-pro'); ?>">
        </p>
    </form>

    </div> <!-- #pdf-builder-settings-tabs -->

<!-- Script pour la sauvegarde dynamique des paramètres (AJAX) -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Définir les variables globales nécessaires
    window.pdfBuilderSettingsNonce = '<?php echo wp_create_nonce('pdf_builder_settings'); ?>';
    window.pdfBuilderMaintenanceNonce = '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>';
    
    // Fonction pour mettre à jour l'indicateur d'état du mode développeur
    window.updateDeveloperStatus = function() {
        var developerEnabledCheckbox = document.getElementById('developer_enabled');
        var passwordField = document.getElementById('developer_password');
        var statusDisplay = document.getElementById('developer-status-display');
        
        if (!statusDisplay || !developerEnabledCheckbox || !passwordField) {
            return;
        }
        
        var isEnabled = developerEnabledCheckbox.checked;
        var hasPassword = passwordField.value.trim().length > 0;
        
        // Construire le nouvel HTML
        var statusHTML = '';
        statusHTML += '<span class="status-indicator ' + (isEnabled ? 'enabled' : 'disabled') + '" id="status-enabled">';
        statusHTML += (isEnabled ? '✅' : '❌') + ' Mode développeur ' + (isEnabled ? 'activé' : 'désactivé');
        statusHTML += '</span>';
        
        if (isEnabled) {
            statusHTML += '<br>';
            statusHTML += '<span class="status-indicator ' + (hasPassword ? 'enabled' : 'disabled') + '" id="status-password">';
            statusHTML += (hasPassword ? '🔒' : '🔓') + ' Authentification ' + (hasPassword ? 'activée' : 'désactivée');
            statusHTML += '</span>';
        }
        
        // Mettre à jour le DOM
        statusDisplay.innerHTML = statusHTML;
    };
    
    // Récupérer les éléments
    var submitBtn = document.getElementById('submit');
    var form = document.querySelector('form[method="post"]');
    var originalButtonValue = submitBtn ? submitBtn.value : '';
    
    // Créer un conteneur de notification
    var notificationContainer = document.createElement('div');
    notificationContainer.id = 'pdf-builder-notification';
    notificationContainer.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 9999; max-width: 400px; min-width: 300px; padding: 0;';
    document.body.appendChild(notificationContainer);
    
    // Fonction pour afficher une notification
    function showNotification(message, type) {
        var icon = type === 'success' ? '✓' : '✗';
        var bgColor = type === 'success' ? '#d4edda' : '#f8d7da';
        var borderColor = type === 'success' ? '#c3e6cb' : '#f5c6cb';
        var textColor = type === 'success' ? '#155724' : '#721c24';
        
        var notification = document.createElement('div');
        notification.style.cssText = 'background: ' + bgColor + '; border: 1px solid ' + borderColor + '; border-radius: 4px; padding: 12px 16px; margin-bottom: 10px; color: ' + textColor + '; box-shadow: 0 2px 4px rgba(0,0,0,0.1); animation: slideIn 0.3s ease-out;';
        notification.innerHTML = '<strong>' + icon + ' ' + message + '</strong>';
        
        notificationContainer.insertBefore(notification, notificationContainer.firstChild);
        
        // Supprimer la notification après 5 secondes
        setTimeout(function() {
            notification.style.animation = 'slideOut 0.3s ease-out';
            setTimeout(function() {
                notification.remove();
            }, 300);
        }, 5000);
    }
    
    // Ajouter les styles d'animation
    var style = document.createElement('style');
    style.textContent = `
        @keyframes slideIn {
            from { transform: translateX(400px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOut {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(400px); opacity: 0; }
        }
    `;
    document.head.appendChild(style);
    
    // Gérer le clic du bouton submit
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();
            submitFormAjax();
        });
    }
    
    // Fonction pour soumettre le formulaire en AJAX
    function submitFormAjax() {
        if (!form) {
            showNotification('Erreur: Formulaire non trouvé', 'error');
            return;
        }
        
        // Collecter les données du formulaire
        var formData = new FormData(form);
        
        // Afficher l'état de chargement
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.value = '⏳ Enregistrement...';
        }
        
        // Convertir FormData en URLSearchParams
        var params = new URLSearchParams();
        for (var pair of formData.entries()) {
            params.append(pair[0], pair[1]);
        }
        params.append('action', 'pdf_builder_save_settings');
        params.append('nonce', window.pdfBuilderSettingsNonce);
        
        // DEBUG: Log des données envoyées
        console.log('Données envoyées:');
        for (var pair of formData.entries()) {
            console.log(pair[0] + ': ' + pair[1]);
        }
        
        // Faire la requête AJAX
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params.toString()
        })
        .then(function(response) {
            return response.json().catch(function() {
                return response.text();
            });
        })
        .then(function(data) {
            // Réactiver le bouton
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.value = originalButtonValue;
            }
            
            // Traiter la réponse
            if (typeof data === 'object') {
                if (data.success) {
                    showNotification(data.data || 'Paramètres sauvegardés avec succès !', 'success');
                    // Mettre à jour l'indicateur d'état du mode développeur
                    updateDeveloperStatus();
                } else {
                    showNotification(data.data || 'Erreur lors de la sauvegarde', 'error');
                }
            } else {
                // Réponse textuelle
                if (data.includes('success')) {
                    showNotification('Paramètres sauvegardés avec succès !', 'success');
                    updateDeveloperStatus();
                } else {
                    showNotification('Paramètres sauvegardés.', 'success');
                    updateDeveloperStatus();
                }
            }
        })
        .catch(function(error) {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.value = originalButtonValue;
            }
            showNotification('Erreur de connexion: ' + error.message, 'error');
        });
    }
    
    // Gestionnaire pour le bouton toggle du mot de passe développeur
    var togglePasswordBtn = document.getElementById('toggle-password-btn');
    var passwordField = document.getElementById('developer_password');
    
    if (togglePasswordBtn && passwordField) {
        togglePasswordBtn.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                this.textContent = '🙈';
                this.title = 'Masquer le mot de passe';
            } else {
                passwordField.type = 'password';
                this.textContent = '👁️';
                this.title = 'Afficher le mot de passe';
            }
        });
    }
    
    // Ajouter des listeners pour mettre à jour l'état en temps réel
    var developerEnabledCheckbox = document.getElementById('developer_enabled');
    var passwordField = document.getElementById('developer_password');
    
    if (developerEnabledCheckbox) {
        developerEnabledCheckbox.addEventListener('change', function() {
            updateDeveloperStatus();
        });
    }
    
    if (passwordField) {
        passwordField.addEventListener('input', function() {
            updateDeveloperStatus();
        });
    }
});
</script>

<script type="text/javascript">
(function($) {
    'use strict';

    $(document).ready(function() {
        // Définir le nonce pour les actions de maintenance (global)
        window.pdfBuilderMaintenanceNonce = '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>';
        
        // Gestion du bouton "Vider le Cache"
        $('#clear-cache').on('click', function() {
            if (!confirm('<?php echo esc_js(__('Êtes-vous sûr de vouloir vider le cache ?', 'pdf-builder-pro')); ?>')) {
                return;
            }

            var $button = $(this);
            var $status = $('#cache-status');

            $button.prop('disabled', true).text('<?php echo esc_js(__('Nettoyage...', 'pdf-builder-pro')); ?>');
            $status.html('<div class="notice notice-info"><p><?php echo esc_js(__('Nettoyage du cache en cours...', 'pdf-builder-pro')); ?></p></div>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_clear_cache',
                    nonce: pdfBuilderMaintenanceNonce
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<div class="notice notice-success"><p><?php echo esc_js(__('Cache vidé avec succès !', 'pdf-builder-pro')); ?></p></div>');
                    } else {
                        $status.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? $('<div>').text(response.data.message).html() : 'Erreur inconnue') + '</p></div>');
                    }
                },
                error: function() {
                    $status.html('<div class="notice notice-error"><p><?php echo esc_js(__('Erreur lors du nettoyage du cache.', 'pdf-builder-pro')); ?></p></div>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('<?php echo esc_js(__('Vider le Cache', 'pdf-builder-pro')); ?>');
                }
            });
        });
    });

    // Gestion des rôles - boutons de sélection rapide
    jQuery(document).ready(function($) {
        var $rolesSelect = $('#pdf_builder_allowed_roles');
        var $selectedCount = $('#selected-count');
        var $validationError = $('#role-validation-error');

        // Fonction pour mettre à jour le compteur
        function updateSelectedCount() {
            var selectedCount = $rolesSelect.find('option:selected').length;
            $selectedCount.text(selectedCount);

            // Validation : au moins un rôle doit être sélectionné
            if (selectedCount === 0) {
                $validationError.show();
                $rolesSelect.addClass('error');
            } else {
                $validationError.hide();
                $rolesSelect.removeClass('error');
            }
        }

        // Bouton "Sélectionner Tout"
        $('#select-all-roles').on('click', function(e) {
            e.preventDefault();
            $rolesSelect.find('option').prop('selected', true);
            updateSelectedCount();
        });

        // Bouton "Désélectionner Tout"
        $('#select-none-roles').on('click', function(e) {
            e.preventDefault();
            $rolesSelect.find('option').prop('selected', false);
            updateSelectedCount();
        });

        // Bouton "Rôles Courants" - sélectionne les rôles les plus utilisés
        $('#select-common-roles').on('click', function(e) {
            e.preventDefault();
            var commonRoles = ['administrator', 'editor', 'shop_manager'];
            $rolesSelect.find('option').prop('selected', false);
            commonRoles.forEach(function(role) {
                $rolesSelect.find('option[value="' + role + '"]').prop('selected', true);
            });
            updateSelectedCount();
        });

        // Mettre à jour le compteur lors des changements
        $rolesSelect.on('change', function() {
            updateSelectedCount();
        });

        // Validation avant soumission du formulaire
        $('form#pdf-builder-settings-form').on('submit', function(e) {
            var selectedCount = $rolesSelect.find('option:selected').length;
            if (selectedCount === 0) {
                e.preventDefault();
                $validationError.show();
                $rolesSelect.addClass('error').focus();

                // Scroll vers la section des rôles
                $('html, body').animate({
                    scrollTop: $rolesSelect.offset().top - 50
                }, 500);

                return false;
            }
        });

        // Initialiser le compteur
        updateSelectedCount();
    });

    // Gestion des actions de maintenance en AJAX
    $(document).ready(function() {
        // Définir le nonce pour les actions de maintenance (global)
        window.pdfBuilderMaintenanceNonce = '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>';
        
        // Fonction utilitaire pour afficher les messages de statut
        function showMaintenanceStatus(message, type) {
            var $status = $('#maintenance-status');
            var statusClass = type === 'success' ? 'notice-success' : (type === 'error' ? 'notice-error' : 'notice-info');

            $status.html('<div class="notice ' + statusClass + ' is-dismissible"><p>' + message + '</p></div>');
            $status.show();

            // Masquer automatiquement après 5 secondes pour les succès
            if (type === 'success') {
                setTimeout(function() {
                    $status.fadeOut();
                }, 5000);
            }
        }

        // Fonction utilitaire pour gérer l'état des boutons
        function setButtonState(buttonId, loading, text) {
            var $button = $('#' + buttonId);
            $button.prop('disabled', loading);
            if (loading) {
                $button.data('original-text', $button.val());
                $button.val(text || '<?php _e('Traitement...', 'pdf-builder-pro'); ?>');
            } else {
                $button.val($button.data('original-text') || $button.val());
            }
        }

        // Action: Vider le Cache
        $('#clear-cache-btn').on('click', function(e) {
            e.preventDefault();

            if (!confirm('<?php _e('Êtes-vous sûr de vouloir vider le cache ?', 'pdf-builder-pro'); ?>')) {
                return;
            }

            setButtonState('clear-cache-btn', true, '<?php _e('Nettoyage...', 'pdf-builder-pro'); ?>');
            showMaintenanceStatus('<?php _e('Nettoyage du cache en cours...', 'pdf-builder-pro'); ?>', 'info');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_maintenance',
                    maintenance_action: 'clear_cache',
                    nonce: pdfBuilderMaintenanceNonce
                },
                success: function(response) {
                    if (response.success) {
                        showMaintenanceStatus('<?php _e('Cache vidé avec succès !', 'pdf-builder-pro'); ?>', 'success');
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Erreur lors du nettoyage du cache.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(errorMsg, 'error');
                    }
                },
                error: function() {
                    showMaintenanceStatus('<?php _e('Erreur de connexion lors du nettoyage du cache.', 'pdf-builder-pro'); ?>', 'error');
                },
                complete: function() {
                    setButtonState('clear-cache-btn', false);
                }
            });
        });

        // Action: Supprimer Fichiers Temporaires
        $('#clear-temp-files-btn').on('click', function(e) {
            e.preventDefault();

            if (!confirm('<?php _e('Êtes-vous sûr de vouloir supprimer les fichiers temporaires ?', 'pdf-builder-pro'); ?>')) {
                return;
            }

            setButtonState('clear-temp-files-btn', true, '<?php _e('Suppression...', 'pdf-builder-pro'); ?>');
            showMaintenanceStatus('<?php _e('Suppression des fichiers temporaires en cours...', 'pdf-builder-pro'); ?>', 'info');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_maintenance',
                    maintenance_action: 'clear_temp_files',
                    nonce: pdfBuilderMaintenanceNonce
                },
                success: function(response) {
                    if (response.success) {
                        showMaintenanceStatus('<?php _e('Fichiers temporaires supprimés avec succès !', 'pdf-builder-pro'); ?>', 'success');
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Erreur lors de la suppression des fichiers temporaires.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(errorMsg, 'error');
                    }
                },
                error: function() {
                    showMaintenanceStatus('<?php _e('Erreur de connexion lors de la suppression des fichiers temporaires.', 'pdf-builder-pro'); ?>', 'error');
                },
                complete: function() {
                    setButtonState('clear-temp-files-btn', false);
                }
            });
        });

        // Action: Réparer Templates
        $('#repair-templates-btn').on('click', function(e) {
            e.preventDefault();

            if (!confirm('<?php _e('Êtes-vous sûr de vouloir réparer les templates ?', 'pdf-builder-pro'); ?>')) {
                return;
            }

            setButtonState('repair-templates-btn', true, '<?php _e('Réparation...', 'pdf-builder-pro'); ?>');
            showMaintenanceStatus('<?php _e('Réparation des templates en cours...', 'pdf-builder-pro'); ?>', 'info');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_maintenance',
                    maintenance_action: 'repair_templates',
                    nonce: pdfBuilderMaintenanceNonce
                },
                success: function(response) {
                    if (response.success) {
                        showMaintenanceStatus('<?php _e('Templates réparés avec succès !', 'pdf-builder-pro'); ?>', 'success');
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Erreur lors de la réparation des templates.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(errorMsg, 'error');
                    }
                },
                error: function() {
                    showMaintenanceStatus('<?php _e('Erreur de connexion lors de la réparation des templates.', 'pdf-builder-pro'); ?>', 'error');
                },
                complete: function() {
                    setButtonState('repair-templates-btn', false);
                }
            });
        });

        // Action: Réinitialiser Paramètres
        $('#reset-settings-btn').on('click', function(e) {
            e.preventDefault();

            // Le confirm est déjà géré par l'attribut onclick du bouton
            // Si l'utilisateur annule, on ne fait rien
            if (!confirm('<?php _e('Attention: Cette action va réinitialiser tous les paramètres aux valeurs par défaut. Continuer ?', 'pdf-builder-pro'); ?>')) {
                return;
            }

            setButtonState('reset-settings-btn', true, '<?php _e('Réinitialisation...', 'pdf-builder-pro'); ?>');
            showMaintenanceStatus('<?php _e('Réinitialisation des paramètres en cours...', 'pdf-builder-pro'); ?>', 'info');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_maintenance',
                    maintenance_action: 'reset_settings',
                    nonce: pdfBuilderMaintenanceNonce
                },
                success: function(response) {
                    if (response.success) {
                        showMaintenanceStatus('<?php _e('Paramètres réinitialisés avec succès ! Rechargez la page pour voir les changements.', 'pdf-builder-pro'); ?>', 'success');
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Erreur lors de la réinitialisation des paramètres.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(errorMsg, 'error');
                    }
                },
                error: function() {
                    showMaintenanceStatus('<?php _e('Erreur de connexion lors de la réinitialisation des paramètres.', 'pdf-builder-pro'); ?>', 'error');
                },
                complete: function() {
                    setButtonState('reset-settings-btn', false);
                }
            });
        });

        // Action: Toggle Debug Mode
        $('#toggle-debug-btn').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var currentText = $button.text().trim();
            var isCurrentlyEnabled = currentText === '<?php _e('Désactiver Debug', 'pdf-builder-pro'); ?>';
            var newState = !isCurrentlyEnabled;



            $button.prop('disabled', true);
            $button.text('<?php _e('Mise à jour...', 'pdf-builder-pro'); ?>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_toggle_debug',
                    debug_enabled: newState ? 1 : 0,
                    nonce: pdfBuilderMaintenanceNonce
                },
                success: function(response) {



                    if (response.success) {

                        $button.text(newState ? '<?php _e('Désactiver Debug', 'pdf-builder-pro'); ?>' : '<?php _e('Activer Debug', 'pdf-builder-pro'); ?>');
                        $button.siblings('span').text(newState ? '<?php _e('Logs de debug JavaScript activés', 'pdf-builder-pro'); ?>' : '<?php _e('Logs de debug JavaScript désactivés', 'pdf-builder-pro'); ?>');

                        var message = newState ?
                            '<?php _e('Mode debug activé ! Les logs JavaScript seront maintenant visibles dans la console.', 'pdf-builder-pro'); ?>' :
                            '<?php _e('Mode debug désactivé ! Les logs JavaScript sont maintenant masqués.', 'pdf-builder-pro'); ?>';

                        // Mettre à jour la variable globale si elle existe
                        if (typeof window !== 'undefined') {
                            window.pdfBuilderDebug = newState;

                        }
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Erreur lors de la mise à jour du mode debug.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(errorMsg, 'error');
                    }
                },
                error: function() {
                    showMaintenanceStatus('<?php _e('Erreur de connexion lors de la mise à jour du mode debug.', 'pdf-builder-pro'); ?>', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        // Action: Toggle Debug Mode Principal
        $('#toggle-debug-mode-btn').on('click', function(e) {
            e.preventDefault();

            var $button = $(this);
            var $checkbox = $('input[name="debug_mode"]');
            var isCurrentlyEnabled = $checkbox.is(':checked');
            var newState = !isCurrentlyEnabled;

            $button.prop('disabled', true);
            $button.text('<?php _e('Mise à jour...', 'pdf-builder-pro'); ?>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_toggle_debug_mode',
                    debug_enabled: newState,
                    nonce: pdfBuilderMaintenanceNonce
                },
                success: function(response) {
                    if (response.success) {
                        $checkbox.prop('checked', newState);
                        $button.text(newState ? '<?php _e('Désactiver Debug', 'pdf-builder-pro'); ?>' : '<?php _e('Activer Debug', 'pdf-builder-pro'); ?>');

                        var message = newState ?
                            '<?php _e('Mode debug principal activé ! Les logs détaillés seront maintenant enregistrés.', 'pdf-builder-pro'); ?>' :
                            '<?php _e('Mode debug principal désactivé ! Les logs détaillés sont maintenant masqués.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(message, 'success');
                    } else {
                            '<?php _e('Mode debug principal désactivé ! Les logs détaillés sont maintenant masqués.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(message, 'success');
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Erreur lors de la mise à jour du mode debug.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(errorMsg, 'error');
                    }
                },
                error: function() {
                    showMaintenanceStatus('<?php _e('Erreur de connexion lors de la mise à jour du mode debug.', 'pdf-builder-pro'); ?>', 'error');
                },
                complete: function() {
                    $button.prop('disabled', false);
                }
            });
        });

        // Action: Clear Debug Logs
        $('#clear-debug-logs-btn').on('click', function(e) {
            e.preventDefault();

            if (!confirm('<?php _e('Êtes-vous sûr de vouloir vider les logs de debug ?', 'pdf-builder-pro'); ?>')) {
                return;
            }

            setButtonState('clear-debug-logs-btn', true, '<?php _e('Nettoyage...', 'pdf-builder-pro'); ?>');
            showMaintenanceStatus('<?php _e('Nettoyage des logs de debug en cours...', 'pdf-builder-pro'); ?>', 'info');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_maintenance',
                    maintenance_action: 'clear_debug_logs',
                    nonce: pdfBuilderMaintenanceNonce
                },
                success: function(response) {
                    if (response.success) {
                        showMaintenanceStatus('<?php _e('Logs de debug vidés avec succès !', 'pdf-builder-pro'); ?>', 'success');
                    } else {
                        var errorMsg = response.data && response.data.message ? response.data.message : '<?php _e('Erreur lors du nettoyage des logs de debug.', 'pdf-builder-pro'); ?>';
                        showMaintenanceStatus(errorMsg, 'error');
                    }
                },
                error: function() {
                    showMaintenanceStatus('<?php _e('Erreur de connexion lors du nettoyage des logs de debug.', 'pdf-builder-pro'); ?>', 'error');
                },
                complete: function() {
                    setButtonState('clear-debug-logs-btn', false);
                }
            });
        });
    });

})(jQuery);
</script>



<?php
// Fin du fichier
?>


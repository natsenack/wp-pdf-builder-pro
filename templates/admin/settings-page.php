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
            'canvas_element_borders_enabled' => true,
            'canvas_border_width' => 1,
            'canvas_border_color' => '#007cba',
            'canvas_border_spacing' => 2,
            'canvas_resize_handles_enabled' => true,
            'canvas_handle_size' => 8,
            'canvas_handle_color' => '#007cba',
            'canvas_handle_hover_color' => '#005a87'
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
            'default_canvas_width' => isset($_POST['default_canvas_width']) ? intval($_POST['default_canvas_width']) : 210,
            'default_canvas_height' => isset($_POST['default_canvas_height']) ? intval($_POST['default_canvas_height']) : 297,
            'default_canvas_unit' => isset($_POST['default_canvas_unit']) ? sanitize_text_field($_POST['default_canvas_unit']) : 'mm',
            'canvas_background_color' => isset($_POST['canvas_background_color']) ? sanitize_text_field($_POST['canvas_background_color']) : '#ffffff',
            'canvas_show_transparency' => isset($_POST['canvas_show_transparency']),
            'container_background_color' => isset($_POST['container_background_color']) ? sanitize_text_field($_POST['container_background_color']) : '#f8f9fa',
            'container_show_transparency' => isset($_POST['container_show_transparency']),
            'show_margins' => isset($_POST['show_margins']),
            'margin_top' => isset($_POST['margin_top']) ? intval($_POST['margin_top']) : 10,
            'margin_right' => isset($_POST['margin_right']) ? intval($_POST['margin_right']) : 10,
            'margin_bottom' => isset($_POST['margin_bottom']) ? intval($_POST['margin_bottom']) : 10,
            'margin_left' => isset($_POST['margin_left']) ? intval($_POST['margin_left']) : 10,
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

    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

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
                
                // Special handling for Canvas tab - initialize sub-tabs
                if (tabContents[i].id === 'canvas') {
                    console.log('Canvas tab activated, initializing sub-tabs...');
                    initializeCanvasSubTabs();
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
    
    // Function to initialize Canvas sub-tabs
    function initializeCanvasSubTabs() {
        console.log('Initializing Canvas sub-tabs...');

        // Always attach click handlers for canvas sub-tabs, even if already initialized
        attachCanvasSubTabHandlers();

        // Check if already initialized
        var activeSubTab = document.querySelector('#canvas .sub-nav-tab-active');
        if (activeSubTab) {
            console.log('Canvas sub-tabs already initialized');
            return; // Already initialized, but handlers are attached
        }

        console.log('Setting up Canvas sub-tabs...');

        // Hide all canvas sub-tab contents
        var canvasSubTabContents = document.querySelectorAll('#canvas .sub-tab-content');
        console.log('Found', canvasSubTabContents.length, 'canvas sub-tab contents');
        for (var j = 0; j < canvasSubTabContents.length; j++) {
            canvasSubTabContents[j].classList.remove('sub-tab-active');
        }

        // Remove active class from all canvas sub-nav tabs
        var canvasSubNavTabs = document.querySelectorAll('#canvas .sub-nav-tab');
        console.log('Found', canvasSubNavTabs.length, 'canvas sub-nav tabs');
        for (var j = 0; j < canvasSubNavTabs.length; j++) {
            canvasSubNavTabs[j].classList.remove('sub-nav-tab-active');
        }

        // Show first sub-tab content
        var firstSubTabContent = document.querySelector('#canvas .sub-tab-content');
        if (firstSubTabContent) {
            console.log('Activating first sub-tab content:', firstSubTabContent.id);
            firstSubTabContent.classList.add('sub-tab-active');
        } else {
            console.log('No first sub-tab content found');
        }

        // Activate first sub-nav tab
        var firstSubNavTab = document.querySelector('#canvas .sub-nav-tab');
        if (firstSubNavTab) {
            console.log('Activating first sub-nav tab:', firstSubNavTab.textContent);
            firstSubNavTab.classList.add('sub-nav-tab-active');
        } else {
            console.log('No first sub-nav tab found');
        }
    }

    // Function to attach click handlers for canvas sub-tabs
    function attachCanvasSubTabHandlers() {
        console.log('Attaching canvas sub-tab handlers...');

        // Check if canvas tab is visible
        var canvasTab = document.getElementById('canvas');
        if (canvasTab) {
            console.log('Canvas tab element found, display:', getComputedStyle(canvasTab).display);
            console.log('Canvas tab classes:', canvasTab.className);
        } else {
            console.log('Canvas tab element NOT found!');
            return;
        }

        var subNavTabs = document.querySelectorAll('#canvas .sub-nav-tab');
        console.log('Found', subNavTabs.length, 'sub-nav tabs to attach handlers to');

        for (var i = 0; i < subNavTabs.length; i++) {
            // Remove existing listeners to avoid duplicates
            subNavTabs[i].removeEventListener('click', handleSubTabClick);
            subNavTabs[i].addEventListener('click', handleSubTabClick);
            console.log('Attached handler to:', subNavTabs[i].textContent.trim());
        }
    }

    // Handler function for sub-tab clicks
    function handleSubTabClick(e) {
        e.preventDefault();
        var targetId = this.getAttribute('href');
        console.log('Sub-tab clicked:', this.textContent, 'target:', targetId);

        // Hide all canvas sub-tab contents
        var canvasSubTabContents = document.querySelectorAll('#canvas .sub-tab-content');
        for (var j = 0; j < canvasSubTabContents.length; j++) {
            canvasSubTabContents[j].classList.remove('sub-tab-active');
        }

        // Remove active class from all canvas sub-nav tabs
        var canvasSubNavTabs = document.querySelectorAll('#canvas .sub-nav-tab');
        for (var j = 0; j < canvasSubNavTabs.length; j++) {
            canvasSubNavTabs[j].classList.remove('sub-nav-tab-active');
        }

        // Show target sub-tab content
        var targetContent = document.querySelector(targetId);
        if (targetContent) {
            console.log('Activating target content:', targetId);
            targetContent.classList.add('sub-tab-active');
        } else {
            console.log('Target content not found:', targetId);
        }

        // Add active class to clicked sub-nav tab
        this.classList.add('sub-nav-tab-active');
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

        // Initialize first sub-tab for Canvas when Canvas tab is active
        var canvasTab = document.querySelector('.nav-tab[href="#canvas"]');
        if (canvasTab && canvasTab.classList.contains('nav-tab-active')) {
            initializeCanvasSubTabs();
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

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-settings')); ?>" onsubmit="return true;">
        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>

        <div class="pdf-builder-settings">

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
                            <label>
                                <input type="checkbox" name="debug_mode" value="1" <?php checked($config->get('debug_mode'), true); ?>>
                                <?php _e('Activer le mode debug pour les logs détaillés', 'pdf-builder-pro'); ?>
                            </label>
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
                            <input type="submit" name="submit_roles" class="button button-primary" value="<?php esc_attr_e('Enregistrer les Rôles et Permissions', 'pdf-builder-pro'); ?>">
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
                        <input type="submit" name="submit_notifications" class="button button-primary" value="<?php esc_attr_e('Enregistrer les Paramètres de Notifications', 'pdf-builder-pro'); ?>">
                        <span style="margin-left: 10px; color: #666;">
                            <?php _e('💡 Vous pouvez aussi utiliser le bouton principal en bas de la page.', 'pdf-builder-pro'); ?>
                        </span>
                    </p>
                </div>
            </div>

            <!-- Onglet Canvas -->
            <div id="canvas" class="tab-content">
                <h2><?php _e('Paramètres Canvas', 'pdf-builder-pro'); ?></h2>

                <!-- Sous-onglets pour l'organisation -->
                <div class="sub-nav-tab-wrapper">
                    <a href="#canvas-general" class="sub-nav-tab sub-nav-tab-active"><?php _e('Général', 'pdf-builder-pro'); ?></a>
                    <a href="#canvas-grid" class="sub-nav-tab"><?php _e('Grille & Aimants', 'pdf-builder-pro'); ?></a>
                    <a href="#canvas-zoom" class="sub-nav-tab"><?php _e('Zoom & Navigation', 'pdf-builder-pro'); ?></a>
                    <a href="#canvas-selection" class="sub-nav-tab"><?php _e('Sélection & Manipulation', 'pdf-builder-pro'); ?></a>
                    <a href="#canvas-export" class="sub-nav-tab"><?php _e('Export & Qualité', 'pdf-builder-pro'); ?></a>
                    <a href="#canvas-advanced" class="sub-nav-tab"><?php _e('Avancé', 'pdf-builder-pro'); ?></a>
                </div>

                <!-- Sous-onglet Général -->
                <div id="canvas-general" class="sub-tab-content sub-tab-active">
                    <h3><?php _e('Paramètres Généraux du Canvas', 'pdf-builder-pro'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Dimensions par Défaut', 'pdf-builder-pro'); ?></th>
                            <td>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="default_canvas_width"><?php _e('Largeur:', 'pdf-builder-pro'); ?></label>
                                        <input name="default_canvas_width" type="number" id="default_canvas_width" value="<?php echo esc_attr($config->get('default_canvas_width', 210)); ?>" class="small-text" min="50" max="1000"> mm
                                    </div>
                                    <div>
                                        <label for="default_canvas_height"><?php _e('Hauteur:', 'pdf-builder-pro'); ?></label>
                                        <input name="default_canvas_height" type="number" id="default_canvas_height" value="<?php echo esc_attr($config->get('default_canvas_height', 297)); ?>" class="small-text" min="50" max="1000"> mm
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
                            <li><?php _e('Les dimensions A4 (210x297mm) sont recommandées pour la plupart des documents', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('L\'unité "mm" est idéale pour l\'impression professionnelle', 'pdf-builder-pro'); ?></li>
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

                <!-- Sous-onglet Grille & Aimants -->
                <?php $canvas_settings = get_option('pdf_builder_settings', []); ?>
                <div id="canvas-grid" class="sub-tab-content">
                    <h3><?php _e('Paramètres de Grille et Aimantation', 'pdf-builder-pro'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Grille d\'Alignement', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="show_grid">
                                        <input name="show_grid" type="checkbox" id="show_grid" value="1" <?php checked($canvas_settings['show_grid'] ?? true); ?>>
                                        <?php _e('Afficher la grille d\'alignement dans l\'éditeur', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="grid_size"><?php _e('Taille de la grille:', 'pdf-builder-pro'); ?></label>
                                        <input name="grid_size" type="number" id="grid_size" value="<?php echo esc_attr($canvas_settings['grid_size'] ?? 10); ?>" class="small-text" min="5" max="50" step="5"> px
                                    </div>
                                    <div>
                                        <label for="grid_color"><?php _e('Couleur:', 'pdf-builder-pro'); ?></label>
                                        <input name="grid_color" type="color" id="grid_color" value="<?php echo esc_attr($canvas_settings['grid_color'] ?? '#e0e0e0'); ?>">
                                    </div>
                                    <div>
                                        <label for="grid_opacity"><?php _e('Opacité:', 'pdf-builder-pro'); ?></label>
                                        <input name="grid_opacity" type="range" id="grid_opacity" min="10" max="100" value="<?php echo esc_attr($canvas_settings['grid_opacity'] ?? 30); ?>" style="width: 80px;">
                                        <span id="grid_opacity_value"><?php echo esc_attr($canvas_settings['grid_opacity'] ?? 30); ?>%</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Aimantation', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="snap_to_grid">
                                        <input name="snap_to_grid" type="checkbox" id="snap_to_grid" value="1" <?php checked(get_option('pdf_builder_snap_to_grid', true)); ?>>
                                        <?php _e('Activer l\'aimantation à la grille', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="snap_to_elements">
                                        <input name="snap_to_elements" type="checkbox" id="snap_to_elements" value="1" <?php checked(get_option('pdf_builder_snap_to_elements', true)); ?>>
                                        <?php _e('Activer l\'aimantation aux autres éléments', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="snap_to_margins">
                                        <input name="snap_to_margins" type="checkbox" id="snap_to_margins" value="1" <?php checked(get_option('pdf_builder_snap_to_margins', true)); ?>>
                                        <?php _e('Activer l\'aimantation aux marges de sécurité', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="margin-top: 10px;">
                                    <label for="snap_tolerance"><?php _e('Tolérance d\'aimantation:', 'pdf-builder-pro'); ?></label>
                                    <input name="snap_tolerance" type="number" id="snap_tolerance" value="<?php echo esc_attr(get_option('pdf_builder_snap_tolerance', 5)); ?>" class="small-text" min="1" max="20"> px
                                    <p class="description"><?php _e('Distance maximale pour l\'aimantation automatique.', 'pdf-builder-pro'); ?></p>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Lignes Guides', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="show_guides">
                                        <input name="show_guides" type="checkbox" id="show_guides" value="1" <?php checked(get_option('pdf_builder_show_guides', true)); ?>>
                                        <?php _e('Afficher les lignes guides personnalisables', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="lock_guides">
                                        <input name="lock_guides" type="checkbox" id="lock_guides" value="1" <?php checked(get_option('pdf_builder_lock_guides', false)); ?>>
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

                <!-- Sous-onglet Zoom & Navigation -->
                <div id="canvas-zoom" class="sub-tab-content">
                    <h3><?php _e('Paramètres de Zoom et Navigation', 'pdf-builder-pro'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Zoom par Défaut', 'pdf-builder-pro'); ?></th>
                            <td>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="default_zoom"><?php _e('Niveau de zoom initial:', 'pdf-builder-pro'); ?></label>
                                        <select name="default_zoom" id="default_zoom">
                                            <option value="25" <?php selected(get_option('pdf_builder_default_zoom', 'fit'), '25'); ?>>25%</option>
                                            <option value="50" <?php selected(get_option('pdf_builder_default_zoom', 'fit'), '50'); ?>>50%</option>
                                            <option value="75" <?php selected(get_option('pdf_builder_default_zoom', 'fit'), '75'); ?>>75%</option>
                                            <option value="100" <?php selected(get_option('pdf_builder_default_zoom', 'fit'), '100'); ?>>100%</option>
                                            <option value="125" <?php selected(get_option('pdf_builder_default_zoom', 'fit'), '125'); ?>>125%</option>
                                            <option value="150" <?php selected(get_option('pdf_builder_default_zoom', 'fit'), '150'); ?>>150%</option>
                                            <option value="200" <?php selected(get_option('pdf_builder_default_zoom', 'fit'), '200'); ?>>200%</option>
                                            <option value="fit" <?php selected(get_option('pdf_builder_default_zoom', 'fit'), 'fit'); ?>>Ajuster à la page</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="zoom_step"><?php _e('Pas de zoom:', 'pdf-builder-pro'); ?></label>
                                        <input name="zoom_step" type="number" id="zoom_step" value="<?php echo esc_attr(get_option('pdf_builder_zoom_step', 25)); ?>" class="small-text" min="5" max="50" step="5"> %
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Limites de Zoom', 'pdf-builder-pro'); ?></th>
                            <td>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="min_zoom"><?php _e('Zoom minimum:', 'pdf-builder-pro'); ?></label>
                                        <input name="min_zoom" type="number" id="min_zoom" value="<?php echo esc_attr(get_option('pdf_builder_min_zoom', 10)); ?>" class="small-text" min="5" max="50"> %
                                    </div>
                                    <div>
                                        <label for="max_zoom"><?php _e('Zoom maximum:', 'pdf-builder-pro'); ?></label>
                                        <input name="max_zoom" type="number" id="max_zoom" value="<?php echo esc_attr(get_option('pdf_builder_max_zoom', 500)); ?>" class="small-text" min="100" max="1000"> %
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Navigation', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="pan_with_mouse">
                                        <input name="pan_with_mouse" type="checkbox" id="pan_with_mouse" value="1" <?php checked($settings['pan_with_mouse'] ?? true); ?>>
                                        <?php _e('Activer le panoramique avec le bouton central de la souris', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="smooth_zoom">
                                        <input name="smooth_zoom" type="checkbox" id="smooth_zoom" value="1" <?php checked($settings['smooth_zoom'] ?? true); ?>>
                                        <?php _e('Activer le zoom fluide (animation)', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="show_zoom_indicator">
                                        <input name="show_zoom_indicator" type="checkbox" id="show_zoom_indicator" value="1" <?php checked($settings['show_zoom_indicator'] ?? true); ?>>
                                        <?php _e('Afficher l\'indicateur de niveau de zoom', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Raccourcis Zoom', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="zoom_with_wheel">
                                        <input name="zoom_with_wheel" type="checkbox" id="zoom_with_wheel" value="1" <?php checked($settings['zoom_with_wheel'] ?? true); ?>>
                                        <?php _e('Zoom avec la molette de la souris (Ctrl+molette)', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="zoom_to_selection">
                                        <input name="zoom_to_selection" type="checkbox" id="zoom_to_selection" value="1" <?php checked($settings['zoom_to_selection'] ?? true); ?>>
                                        <?php _e('Double-clic pour zoomer sur la sélection', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>

                    <div class="canvas-settings-notice" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                        <h4 style="margin-top: 0; color: #495057;"><?php _e('💡 Conseils d\'utilisation', 'pdf-builder-pro'); ?></h4>
                        <ul style="margin: 10px 0; padding-left: 20px; color: #6c757d;">
                            <li><?php _e('"Ajuster à la page" offre la meilleure vue d\'ensemble lors de l\'ouverture', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Le panoramique avec le bouton central facilite la navigation dans les grands documents', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Ctrl+molette permet un zoom rapide et intuitif', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Le double-clic pour zoomer sur la sélection améliore la productivité', 'pdf-builder-pro'); ?></li>
                        </ul>
                    </div>

                    <div class="canvas-save-section" style="margin-top: 20px; padding: 15px; background: #e8f5e8; border: 1px solid #c3e6c3; border-radius: 6px;">
                        <p style="margin: 0; color: #2d5a2d;">
                            <strong><?php _e('💾 Sauvegarder les paramètres de zoom', 'pdf-builder-pro'); ?></strong><br>
                            <?php _e('Cliquez sur le bouton principal "Enregistrer les paramètres" en bas de la page.', 'pdf-builder-pro'); ?>
                        </p>
                    </div>
                </div>

                <!-- Sous-onglet Sélection & Manipulation -->
                <div id="canvas-selection" class="sub-tab-content">
                    <h3><?php _e('Paramètres de Sélection et Manipulation', 'pdf-builder-pro'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Poignées de Redimensionnement', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="show_resize_handles">
                                        <input name="show_resize_handles" type="checkbox" id="show_resize_handles" value="1" <?php checked(get_option('pdf_builder_show_resize_handles', true)); ?>>
                                        <?php _e('Afficher les poignées de redimensionnement', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="handle_size"><?php _e('Taille des poignées:', 'pdf-builder-pro'); ?></label>
                                        <input name="handle_size" type="number" id="handle_size" value="<?php echo esc_attr(get_option('pdf_builder_handle_size', 8)); ?>" class="small-text" min="4" max="20"> px
                                    </div>
                                    <div>
                                        <label for="handle_color"><?php _e('Couleur:', 'pdf-builder-pro'); ?></label>
                                        <input name="handle_color" type="color" id="handle_color" value="<?php echo esc_attr(get_option('pdf_builder_handle_color', '#007cba')); ?>">
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Rotation', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="enable_rotation">
                                        <input name="enable_rotation" type="checkbox" id="enable_rotation" value="1" <?php checked($settings['enable_rotation'] ?? true); ?>>
                                        <?php _e('Activer la rotation des éléments', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="rotation_step"><?php _e('Pas de rotation:', 'pdf-builder-pro'); ?></label>
                                        <input name="rotation_step" type="number" id="rotation_step" value="<?php echo esc_attr($settings['rotation_step'] ?? 15); ?>" class="small-text" min="1" max="45"> °
                                    </div>
                                    <div>
                                        <label for="rotation_snap"><?php _e('Aimantation angulaire:', 'pdf-builder-pro'); ?></label>
                                        <input name="rotation_snap" type="checkbox" id="rotation_snap" value="1" <?php checked($settings['rotation_snap'] ?? true); ?>>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Sélection Multiple', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="multi_select">
                                        <input name="multi_select" type="checkbox" id="multi_select" value="1" <?php checked(get_option('pdf_builder_multi_select', true)); ?>>
                                        <?php _e('Activer la sélection multiple (Ctrl+Clic)', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="select_all_shortcut">
                                        <input name="select_all_shortcut" type="checkbox" id="select_all_shortcut" value="1" <?php checked(get_option('pdf_builder_select_all_shortcut', true)); ?>>
                                        <?php _e('Activer Ctrl+A pour tout sélectionner', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="show_selection_bounds">
                                        <input name="show_selection_bounds" type="checkbox" id="show_selection_bounds" value="1" <?php checked(get_option('pdf_builder_show_selection_bounds', true)); ?>>
                                        <?php _e('Afficher le cadre de sélection pour les groupes', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Copier-Coller', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="copy_paste_enabled">
                                        <input name="copy_paste_enabled" type="checkbox" id="copy_paste_enabled" value="1" <?php checked(get_option('pdf_builder_copy_paste_enabled', true)); ?>>
                                        <?php _e('Activer les fonctions copier-coller', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="duplicate_on_drag">
                                        <input name="duplicate_on_drag" type="checkbox" id="duplicate_on_drag" value="1" <?php checked(get_option('pdf_builder_duplicate_on_drag', false)); ?>>
                                        <?php _e('Dupliquer l\'élément lors du glisser avec Alt', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>

                    <div class="canvas-settings-notice" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                        <h4 style="margin-top: 0; color: #495057;"><?php _e('💡 Conseils d\'utilisation', 'pdf-builder-pro'); ?></h4>
                        <ul style="margin: 10px 0; padding-left: 20px; color: #6c757d;">
                            <li><?php _e('Les poignées de 8px offrent un bon équilibre entre visibilité et précision', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('La rotation par pas de 15° permet un contrôle précis des orientations', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Ctrl+Clic permet de sélectionner plusieurs éléments simultanément', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Alt+glisser duplique automatiquement l\'élément sélectionné', 'pdf-builder-pro'); ?></li>
                        </ul>
                    </div>

                    <div class="canvas-save-section" style="margin-top: 20px; padding: 15px; background: #e8f5e8; border: 1px solid #c3e6c3; border-radius: 6px;">
                        <p style="margin: 0; color: #2d5a2d;">
                            <strong><?php _e('💾 Sauvegarder les paramètres de sélection', 'pdf-builder-pro'); ?></strong><br>
                            <?php _e('Cliquez sur le bouton principal "Enregistrer les paramètres" en bas de la page.', 'pdf-builder-pro'); ?>
                        </p>
                    </div>
                </div>

                <!-- Sous-onglet Export & Qualité -->
                <div id="canvas-export" class="sub-tab-content">
                    <h3><?php _e('Paramètres d\'Export et Qualité', 'pdf-builder-pro'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Qualité d\'Export', 'pdf-builder-pro'); ?></th>
                            <td>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="export_quality"><?php _e('Qualité PDF:', 'pdf-builder-pro'); ?></label>
                                        <select name="export_quality" id="export_quality">
                                            <option value="screen" <?php selected(get_option('pdf_builder_export_quality', 'print'), 'screen'); ?>>Écran (72 DPI)</option>
                                            <option value="ebook" <?php selected(get_option('pdf_builder_export_quality', 'print'), 'ebook'); ?>>E-book (150 DPI)</option>
                                            <option value="printer" <?php selected(get_option('pdf_builder_export_quality', 'print'), 'printer'); ?>>Imprimante (300 DPI)</option>
                                            <option value="print" <?php selected(get_option('pdf_builder_export_quality', 'print'), 'print'); ?>>Haute qualité (600 DPI)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="export_format"><?php _e('Format d\'export:', 'pdf-builder-pro'); ?></label>
                                        <select name="export_format" id="export_format">
                                            <option value="pdf" <?php selected(get_option('pdf_builder_export_format', 'pdf'), 'pdf'); ?>>PDF</option>
                                            <option value="png" <?php selected(get_option('pdf_builder_export_format', 'pdf'), 'png'); ?>>PNG</option>
                                            <option value="jpg" <?php selected(get_option('pdf_builder_export_format', 'pdf'), 'jpg'); ?>>JPEG</option>
                                            <option value="svg" <?php selected(get_option('pdf_builder_export_format', 'pdf'), 'svg'); ?>>SVG</option>
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Compression', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="compress_images">
                                        <input name="compress_images" type="checkbox" id="compress_images" value="1" <?php checked(get_option('pdf_builder_compress_images', true)); ?>>
                                        <?php _e('Compresser automatiquement les images', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="image_quality"><?php _e('Qualité des images:', 'pdf-builder-pro'); ?></label>
                                        <input name="image_quality" type="range" id="image_quality" min="10" max="100" value="<?php echo esc_attr(get_option('pdf_builder_image_quality', 85)); ?>" style="width: 100px;">
                                        <span id="image_quality_value"><?php echo esc_attr(get_option('pdf_builder_image_quality', 85)); ?>%</span>
                                    </div>
                                    <div>
                                        <label for="max_image_size"><?php _e('Taille max des images:', 'pdf-builder-pro'); ?></label>
                                        <input name="max_image_size" type="number" id="max_image_size" value="<?php echo esc_attr(get_option('pdf_builder_max_image_size', 2048)); ?>" class="small-text" min="512" max="8192" step="512"> px
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Métadonnées PDF', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="include_metadata">
                                        <input name="include_metadata" type="checkbox" id="include_metadata" value="1" <?php checked(get_option('pdf_builder_include_metadata', true)); ?>>
                                        <?php _e('Inclure les métadonnées dans le PDF', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="margin-top: 10px;">
                                    <label for="pdf_author"><?php _e('Auteur par défaut:', 'pdf-builder-pro'); ?></label>
                                    <input name="pdf_author" type="text" id="pdf_author" value="<?php echo esc_attr(get_option('pdf_builder_pdf_author', get_bloginfo('name'))); ?>" class="regular-text">
                                    <br>
                                    <label for="pdf_subject"><?php _e('Sujet par défaut:', 'pdf-builder-pro'); ?></label>
                                    <input name="pdf_subject" type="text" id="pdf_subject" value="<?php echo esc_attr(get_option('pdf_builder_pdf_subject', '')); ?>" class="regular-text">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Options d\'Export', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="auto_crop">
                                        <input name="auto_crop" type="checkbox" id="auto_crop" value="1" <?php checked(get_option('pdf_builder_auto_crop', false)); ?>>
                                        <?php _e('Rogner automatiquement les espaces vides', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="embed_fonts">
                                        <input name="embed_fonts" type="checkbox" id="embed_fonts" value="1" <?php checked(get_option('pdf_builder_embed_fonts', true)); ?>>
                                        <?php _e('Intégrer les polices dans le PDF', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="optimize_for_web">
                                        <input name="optimize_for_web" type="checkbox" id="optimize_for_web" value="1" <?php checked(get_option('pdf_builder_optimize_for_web', true)); ?>>
                                        <?php _e('Optimiser pour l\'affichage web', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                            </td>
                        </tr>
                    </table>

                    <div class="canvas-settings-notice" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                        <h4 style="margin-top: 0; color: #495057;"><?php _e('💡 Conseils d\'utilisation', 'pdf-builder-pro'); ?></h4>
                        <ul style="margin: 10px 0; padding-left: 20px; color: #6c757d;">
                            <li><?php _e('Haute qualité (600 DPI) est recommandée pour l\'impression professionnelle', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Une qualité d\'image de 85% offre le meilleur équilibre taille/qualité', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('L\'intégration des polices garantit l\'affichage correct sur tous les appareils', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('L\'optimisation web réduit la taille du fichier pour un chargement plus rapide', 'pdf-builder-pro'); ?></li>
                        </ul>
                    </div>

                    <div class="canvas-save-section" style="margin-top: 20px; padding: 15px; background: #e8f5e8; border: 1px solid #c3e6c3; border-radius: 6px;">
                        <p style="margin: 0; color: #2d5a2d;">
                            <strong><?php _e('💾 Sauvegarder les paramètres d\'export', 'pdf-builder-pro'); ?></strong><br>
                            <?php _e('Cliquez sur le bouton principal "Enregistrer les paramètres" en bas de la page.', 'pdf-builder-pro'); ?>
                        </p>
                    </div>
                </div>

                <!-- Sous-onglet Avancé -->
                <div id="canvas-advanced" class="sub-tab-content">
                    <h3><?php _e('Paramètres Avancés du Canvas', 'pdf-builder-pro'); ?></h3>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Performance', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="enable_hardware_acceleration">
                                        <input name="enable_hardware_acceleration" type="checkbox" id="enable_hardware_acceleration" value="1" <?php checked(get_option('pdf_builder_enable_hardware_acceleration', true)); ?>>
                                        <?php _e('Activer l\'accélération matérielle (GPU)', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <fieldset>
                                    <label for="limit_fps">
                                        <input name="limit_fps" type="checkbox" id="limit_fps" value="1" <?php checked(get_option('pdf_builder_limit_fps', true)); ?>>
                                        <?php _e('Limiter les FPS pour économiser les ressources', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="margin-top: 10px;">
                                    <label for="max_fps"><?php _e('FPS maximum:', 'pdf-builder-pro'); ?></label>
                                    <input name="max_fps" type="number" id="max_fps" value="<?php echo esc_attr(get_option('pdf_builder_max_fps', 60)); ?>" class="small-text" min="15" max="120"> FPS
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php _e('Sauvegarde Automatique', 'pdf-builder-pro'); ?></th>
                            <td>
                                <fieldset>
                                    <label for="auto_save_enabled">
                                        <input name="auto_save_enabled" type="checkbox" id="auto_save_enabled" value="1" <?php checked(get_option('pdf_builder_auto_save_enabled', true)); ?>>
                                        <?php _e('Activer la sauvegarde automatique', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="display: flex; gap: 20px; align-items: center;">
                                    <div>
                                        <label for="auto_save_interval"><?php _e('Intervalle:', 'pdf-builder-pro'); ?></label>
                                        <input name="auto_save_interval" type="number" id="auto_save_interval" value="<?php echo esc_attr(get_option('pdf_builder_auto_save_interval', 30)); ?>" class="small-text" min="10" max="300" step="10"> secondes
                                    </div>
                                    <div>
                                        <label for="auto_save_versions"><?php _e('Versions à conserver:', 'pdf-builder-pro'); ?></label>
                                        <input name="auto_save_versions" type="number" id="auto_save_versions" value="<?php echo esc_attr(get_option('pdf_builder_auto_save_versions', 10)); ?>" class="small-text" min="1" max="50">
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
                                        <input name="undo_levels" type="number" id="undo_levels" value="<?php echo esc_attr(get_option('pdf_builder_undo_levels', 50)); ?>" class="small-text" min="10" max="200" step="10">
                                    </div>
                                    <div>
                                        <label for="redo_levels"><?php _e('Niveaux de rétablissement:', 'pdf-builder-pro'); ?></label>
                                        <input name="redo_levels" type="number" id="redo_levels" value="<?php echo esc_attr(get_option('pdf_builder_redo_levels', 50)); ?>" class="small-text" min="10" max="200" step="10">
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
                                        <input name="enable_keyboard_shortcuts" type="checkbox" id="enable_keyboard_shortcuts" value="1" <?php checked(get_option('pdf_builder_enable_keyboard_shortcuts', true)); ?>>
                                        <?php _e('Activer les raccourcis clavier personnalisables', 'pdf-builder-pro'); ?>
                                    </label>
                                </fieldset>
                                <br>
                                <div style="margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                                    <p style="margin: 0; font-weight: bold;"><?php _e('Raccourcis par défaut:', 'pdf-builder-pro'); ?></p>
                                    <ul style="margin: 5px 0; padding-left: 20px;">
                                        <li>Ctrl+Z: Annuler</li>
                                        <li>Ctrl+Y: Rétablir</li>
                                        <li>Ctrl+A: Tout sélectionner</li>
                                        <li>Ctrl+C: Copier</li>
                                        <li>Ctrl+V: Coller</li>
                                        <li>Ctrl+D: Dupliquer</li>
                                        <li>Suppr: Supprimer</li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </table>

                    <div class="canvas-settings-notice" style="margin-top: 30px; padding: 20px; background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px;">
                        <h4 style="margin-top: 0; color: #495057;"><?php _e('💡 Conseils d\'utilisation', 'pdf-builder-pro'); ?></h4>
                        <ul style="margin: 10px 0; padding-left: 20px; color: #6c757d;">
                            <li><?php _e('L\'accélération GPU améliore considérablement les performances sur les machines récentes', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('La sauvegarde automatique toutes les 30 secondes protège contre la perte de travail', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('50 niveaux d\'annulation offrent une grande flexibilité dans l\'édition', 'pdf-builder-pro'); ?></li>
                            <li><?php _e('Le mode débogage aide au développement mais peut ralentir l\'interface', 'pdf-builder-pro'); ?></li>
                        </ul>
                    </div>

                    <div class="canvas-save-section" style="margin-top: 20px; padding: 15px; background: #e8f5e8; border: 1px solid #c3e6c3; border-radius: 6px;">
                        <p style="margin: 0; color: #2d5a2d;">
                            <strong><?php _e('💾 Sauvegarder les paramètres avancés', 'pdf-builder-pro'); ?></strong><br>
                            <?php _e('Cliquez sur le bouton principal "Enregistrer les paramètres" en bas de la page.', 'pdf-builder-pro'); ?>
                        </p>
                    </div>
                </div>
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
                        $woocommerce_available = function_exists('wc_get_order_statuses');
                        if ($woocommerce_available) {
                            $order_statuses = wc_get_order_statuses();
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
                    <input type="submit" name="submit_templates" class="button button-primary" value="<?php esc_attr_e('Enregistrer les Templates', 'pdf-builder-pro'); ?>">
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
        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Enregistrer les paramètres', 'pdf-builder-pro'); ?>">
        </p>
    </form>
</div>

<!-- Debug script to check form submission -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    // Définir les variables globales nécessaires
    window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
    window.pdfBuilderSettingsNonce = '<?php echo wp_create_nonce('pdf_builder_settings'); ?>';
    window.pdfBuilderMaintenanceNonce = '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>';
    // Définir les nonces en variables JavaScript (globales)
    window.pdfBuilderSettingsNonce = '<?php echo wp_create_nonce('pdf_builder_settings'); ?>';
    window.pdfBuilderMaintenanceNonce = '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>';
    
    // Écouter les clics sur le bouton submit
    var submitBtn = document.getElementById('submit');
    var form = document.querySelector('form[method="post"]');
    
    if (submitBtn) {
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Empêcher la soumission normale
            
            // Soumission AJAX
            submitFormAjax();
        });
    } else {
        // Bouton submit non trouvé - ne rien faire
    }
    
    // Fonction pour soumettre le formulaire en AJAX
    function submitFormAjax() {
        if (!form) {
            return;
        }
        
        // Collecter les données du formulaire
        var formData = new FormData(form);
        
        // Afficher un indicateur de chargement
        submitBtn.disabled = true;
        submitBtn.value = 'Enregistrement...';
        
        // Faire la requête AJAX vers l'endpoint WordPress
        fetch(ajaxurl + '?action=pdf_builder_save_settings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                ...Object.fromEntries(formData),
                nonce: pdfBuilderSettingsNonce
            })
        })
        .then(function(response) {
            return response.text();
        })
        .then(function(data) {
            // Réactiver le bouton
            submitBtn.disabled = false;
            submitBtn.value = 'Enregistrer les paramètres';
            
            try {
                // Essayer de parser comme JSON
                var jsonResponse = JSON.parse(data);
                if (jsonResponse.success) {
                    showNotification(jsonResponse.data || 'Paramètres sauvegardés avec succès !', 'success');
                    // Au lieu de recharger la page, rafraîchir les paramètres en temps réel
                    refreshGlobalSettings();
                } else {
                    showNotification(jsonResponse.data || 'Erreur lors de la sauvegarde.', 'error');
                }
            } catch (e) {
                // Si ce n'est pas du JSON, vérifier le contenu HTML
                if (data.includes('Paramètres sauvegardés avec succès') || data.includes('notice-success')) {
                    showNotification('Paramètres sauvegardés avec succès !', 'success');
                    // Au lieu de recharger la page, rafraîchir les paramètres en temps réel
                    refreshGlobalSettings();
                } else if (data.includes('notice-error') || data.includes('Erreur')) {
                    showNotification('Erreur lors de la sauvegarde.', 'error');
                } else {
                    showNotification('Paramètres sauvegardés.', 'success');
                    // Au lieu de recharger la page, rafraîchir les paramètres en temps réel
                    refreshGlobalSettings();
                }
            }
        })
        .catch(function(error) {
            submitBtn.disabled = false;
            submitBtn.value = 'Enregistrer les paramètres';
            showNotification('Erreur de connexion.', 'error');
        });
    }
    
    // Fonction pour rafraîchir les paramètres globaux en temps réel
    function refreshGlobalSettings() {
        // Faire un appel AJAX pour récupérer les paramètres mis à jour
        fetch(ajaxurl + '?action=pdf_builder_get_settings&nonce=' + pdfBuilderSettingsNonce, {
            method: 'GET',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            if (data.success && data.data) {
                // Mettre à jour la variable globale JavaScript
                window.pdfBuilderCanvasSettings = data.data;
                
                // Déclencher un événement personnalisé pour notifier les composants React
                var event = new CustomEvent('pdfBuilderSettingsUpdated', {
                    detail: { settings: data.data }
                });
                window.dispatchEvent(event);
                
                showNotification('Paramètres appliqués en temps réel !', 'success');
            } else {
                showNotification('Paramètres sauvegardés, mais rafraîchissement échoué.', 'error');
            }
        })
        .catch(function(error) {
            showNotification('Paramètres sauvegardés, mais rafraîchissement échoué.', 'error');
        });
    }
    
    // Fonction pour afficher les notifications
    function showNotification(message, type) {
        // Supprimer les notifications existantes
        var existingNotifications = document.querySelectorAll('.pdf-builder-notification');
        existingNotifications.forEach(function(notif) {
            notif.remove();
        });
        
        // Créer la notification
        var notification = document.createElement('div');
        notification.className = 'pdf-builder-notification ' + (type === 'success' ? 'success' : 'error');
        notification.innerHTML = '<span>' + message + '</span>';
        notification.style.cssText = `
            position: fixed;
            top: 40px;
            right: 20px;
            background: ${type === 'success' ? '#4CAF50' : '#f44336'};
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            z-index: 100;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            font-size: 14px;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
        `;
        
        document.body.appendChild(notification);
        
        // Animer l'apparition
        setTimeout(function() {
            notification.style.opacity = '1';
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        // Masquer automatiquement après 3 secondes
        setTimeout(function() {
            notification.style.opacity = '0';
            notification.style.transform = 'translateX(100%)';
            setTimeout(function() {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 300);
        }, 3000);
    }
});
</script>

</div>

</div>

<?php
// CSS pour la page des paramètres
echo '<style>
.pdf-builder-settings {
    margin-top: 20px;
}

.nav-tab-wrapper {
    margin-bottom: 20px;
    border-bottom: 1px solid #ccc;
}

.nav-tab {
    display: inline-block;
    padding: 8px 16px;
    margin-right: 4px;
    border: 1px solid #ccc;
    border-bottom: none;
    background: #f1f1f1;
    color: #555;
    text-decoration: none;
    border-radius: 4px 4px 0 0;
    cursor: pointer;
    transition: all 0.2s ease;
}

.nav-tab:hover {
    background: #e9e9e9;
    color: #333;
}

.nav-tab-active {
    background: #fff !important;
    border-bottom: 1px solid #fff !important;
    color: #000 !important;
    position: relative;
    top: 1px;
}

.tab-content {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 0 8px 8px 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-top: -1px;
    display: none;
}

.tab-content.active {
    display: block;
}

.tab-content h2 {
    margin: 0 0 20px 0;
    color: #23282d;
    border-bottom: 1px solid #e5e5e5;
    padding-bottom: 10px;
}

.pdf-builder-maintenance {
    margin-top: 40px;
    padding: 20px;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pdf-builder-maintenance h2 {
    margin: 0 0 15px 0;
    color: #23282d;
}

.maintenance-actions {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.maintenance-actions .button {
    padding: 8px 16px;
}

/* Styles pour les sections Canvas */
.pdf-builder-settings-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin: 20px 0;
    padding: 20px;
}

.pdf-builder-settings-section h3 {
    margin-top: 0;
    color: #1d2327;
    font-size: 1.3em;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.pdf-builder-settings-section .form-table th {
    width: 200px;
    padding: 15px 10px 15px 0;
}

.pdf-builder-settings-section .form-table td {
    padding: 15px 10px;
}

/* Styles pour l\'onglet Rôles */
.roles-management {
    max-width: none;
}

.roles-section {
    margin-bottom: 30px;
}

.roles-section h3 {
    margin: 0 0 15px 0;
    color: #23282d;
    font-size: 16px;
}

.roles-section table {
    margin-bottom: 15px;
}

.roles-section table th,
.roles-section table td {
    padding: 8px 12px;
    text-align: left;
    vertical-align: middle;
}

.roles-section table th {
    background: #f8f9fa;
    font-weight: 600;
    border-bottom: 2px solid #e5e5e5;
}

.roles-section table td {
    border-bottom: 1px solid #e5e5e5;
}

.roles-section table td input[type="checkbox"] {
    margin: 0;
}

.roles-section table td strong {
    color: #23282d;
}

.roles-section table td small {
    color: #666;
    font-size: 12px;
}

.roles-actions {
    margin-bottom: 30px;
    padding: 20px;
    background: #f8f9fa;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
}

/* Styles pour la gestion des roles */
.role-selection-controls {
    margin-bottom: 15px;
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #e5e5e5;
    border-radius: 6px;
}

.role-selection-controls .button {
    margin-right: 8px;
    margin-bottom: 5px;
}

.role-selection-controls .description {
    font-style: italic;
    color: #666;
}

#pdf_builder_allowed_roles {
    border: 2px solid #ddd;
    border-radius: 4px;
    transition: border-color 0.3s ease;
}

#pdf_builder_allowed_roles:focus {
    border-color: #007cba;
    box-shadow: 0 0 0 1px #007cba;
}

#pdf_builder_allowed_roles.error {
    border-color: #dc3232 !important;
    box-shadow: 0 0 0 1px #dc3232 !important;
}

.role-validation-message {
    color: #dc3232;
    font-weight: bold;
    padding: 8px 12px;
    background: #ffeaea;
    border: 1px solid #facfd2;
    border-radius: 4px;
    margin-top: 8px;
}

.roles-info .notice {
    margin-bottom: 15px;
}

.roles-info .notice ul {
    list-style-type: none;
    padding-left: 0;
}

.roles-info .notice ul li {
    margin-bottom: 5px;
}

.roles-info .notice ul li:before {
    content: "•";
    color: #007cba;
    font-weight: bold;
    margin-right: 8px;
}

#pdf_builder_allowed_roles option:hover {
    background-color: #f0f8ff;
}

.roles-actions h3 {
    margin: 0 0 15px 0;
    color: #23282d;
    font-size: 16px;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-bottom: 15px;
}

.action-buttons .button {
    padding: 8px 16px;
}

.action-buttons .button .dashicons {
    margin-left: 5px;
}

.roles-info {
    margin-top: 20px;
}

.roles-info h3 {
    margin: 0 0 15px 0;
    color: #23282d;
    font-size: 16px;
}

#roles-status {
    margin-top: 15px;
}

/* Styles for Maintenance tab */
.maintenance-status {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
}

.maintenance-status .notice {
    margin: 0;
    padding: 10px 12px;
}

.maintenance-status .notice-success {
    border-left-color: #46b450;
    background-color: #f0f9f0;
}

.maintenance-status .notice-error {
    border-left-color: #dc3232;
    background-color: #fef2f2;
}

.maintenance-status .notice-info {
    border-left-color: #00a0d2;
    background-color: #f0f8ff;
}

.maintenance-actions {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.maintenance-section {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 20px;
}

.maintenance-section h3 {
    margin: 0 0 15px 0;
    color: #23282d;
    font-size: 16px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.maintenance-section p {
    margin: 0 0 15px 0;
    color: #666;
}

.maintenance-section .button {
    margin-right: 10px;
    margin-bottom: 5px;
}

.maintenance-section .button-warning {
    background-color: #d63638;
    border-color: #d63638;
    color: #fff;
}

.maintenance-section .button-warning:hover {
    background-color: #b32d2e;
    border-color: #b32d2e;
}

/* Styles for sub-tabs in Canvas tab */
.sub-nav-tab-wrapper {
    margin: 20px 0 30px 0;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.sub-nav-tab {
    display: inline-block;
    padding: 6px 12px;
    margin-right: 4px;
    background: #f7f7f7;
    color: #666;
    text-decoration: none;
    border: 1px solid #ddd;
    border-bottom: none;
    border-radius: 4px 4px 0 0;
    cursor: pointer;
    transition: all 0.2s ease;
}

.sub-nav-tab:hover {
    background: #e9e9e9;
    color: #333;
}

.sub-nav-tab-active {
    background: #fff !important;
    border-bottom: 1px solid #fff !important;
    color: #000 !important;
    position: relative;
    top: 1px;
}

.sub-tab-content {
    display: none;
}

.sub-tab-active {
    display: block;
}

/* Fix footer positioning */
#wpfooter {
    position: static !important;
    clear: both !important;
    margin-top: 50px !important;
}

/* Ensure proper page layout */
.wrap {
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
    min-height: auto !important;
}

/* Ensure content stays within bounds */
.pdf-builder-settings {
    margin-bottom: 100px !important;
    padding-bottom: 50px !important;
}
</style>';
?>

    // Tab functionality moved to inline script above
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
    });

})(jQuery);
</script>



<?php
// Fin du fichier
?>


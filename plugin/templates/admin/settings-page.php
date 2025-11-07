<?php
/**
 * Page des Paramètres - PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

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
            'max_template_size' => 52428800,
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
            'canvas_handle_hover_color' => '#005a87',
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
            'default_zoom' => '100',
            'zoom_step' => 25,
            'min_zoom' => 10,
            'max_zoom' => 500,
            'pan_with_mouse' => true,
            'zoom_with_wheel' => true,
            'smooth_zoom' => true,
            'show_zoom_indicator' => true,
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
            'canvas_element_borders_enabled' => isset($_POST['canvas_element_borders_enabled']),
            'canvas_border_width' => isset($_POST['canvas_border_width']) ? floatval($_POST['canvas_border_width']) : 1,
            'canvas_border_color' => isset($_POST['canvas_border_color']) ? sanitize_text_field($_POST['canvas_border_color']) : '#007cba',
            'canvas_border_spacing' => isset($_POST['canvas_border_spacing']) ? intval($_POST['canvas_border_spacing']) : 2,
            'canvas_resize_handles_enabled' => isset($_POST['canvas_resize_handles_enabled']),
            'canvas_handle_size' => isset($_POST['canvas_handle_size']) ? intval($_POST['canvas_handle_size']) : 8,
            'canvas_handle_color' => isset($_POST['canvas_handle_color']) ? sanitize_text_field($_POST['canvas_handle_color']) : '#007cba',
            'canvas_handle_hover_color' => isset($_POST['canvas_handle_hover_color']) ? sanitize_text_field($_POST['canvas_handle_hover_color']) : '#005a87',
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
            'show_fps' => isset($_POST['show_fps']),
            'email_notifications' => isset($_POST['email_notifications']),
            'admin_email' => sanitize_email($_POST['admin_email']),
            'notification_log_level' => sanitize_text_field($_POST['notification_log_level'])
        ];

        $config->set_multiple($settings);

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

        if (isset($_POST['pdf_builder_allowed_roles'])) {
            $allowed_roles = array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles']);
            if (empty($allowed_roles)) {
                $allowed_roles = ['administrator'];
            }
            update_option('pdf_builder_allowed_roles', $allowed_roles);
        }

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

        if ($isAjax) {
            wp_send_json_success(array(
                'message' => __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro'),
                'spacing' => $settings['canvas_border_spacing']
            ));
            exit;
        } else {
            $admin_notices[] = '<div class="notice notice-success"><p>' . __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro') . ' (espacement: ' . $settings['canvas_border_spacing'] . ')</p></div>';
        }
    } else {
        if ($isAjax) {
            wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
            exit;
        } else {
            $admin_notices[] = '<div class="notice notice-error"><p>' . __('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro') . '</p></div>';
        }
    }
} elseif (isset($_POST['submit'])) {
    if ($isAjax) {
        wp_send_json_error(__('Erreur : nonce manquant.', 'pdf-builder-pro'));
        exit;
    } else {
        $admin_notices[] = '<div class="notice notice-error"><p>' . __('Erreur : nonce manquant.', 'pdf-builder-pro') . '</p></div>';
    }
}

if ($isAjax) {
    exit;
}

$settings = get_option('pdf_builder_settings', []);
?>

<div class="wrap">
    <h1><?php _e('⚙️ Paramètres - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
    
    <?php
    foreach ($admin_notices as $notice) {
        echo $notice;
    }
    ?>
    
    <div class="nav-tab-wrapper wp-clearfix">
        <a href="#general" class="nav-tab nav-tab-active">Général</a>
        <a href="#licence" class="nav-tab">Licence</a>
        <a href="#performance" class="nav-tab">Performance</a>
        <a href="#pdf" class="nav-tab">PDF</a>
        <a href="#securite" class="nav-tab">Sécurité</a>
        <a href="#roles" class="nav-tab">Rôles</a>
        <a href="#notifications" class="nav-tab">Notifications</a>
        <a href="#canvas" class="nav-tab">Canvas</a>
        <a href="#templates" class="nav-tab">Templates</a>
        <a href="#maintenance" class="nav-tab">Maintenance</a>
        <a href="#developpeur" class="nav-tab">Développeur</a>
    </div>
    
    <form method="post" class="settings-form">
        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
        
        <!-- Onglet Général -->
        <div id="general" class="tab-content" style="display: block;">
            <h2>Paramètres Généraux</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="pdf_quality">Qualité PDF</label></th>
                    <td>
                        <select id="pdf_quality" name="pdf_quality">
                            <option value="low" <?php selected($settings['pdf_quality'] ?? 'high', 'low'); ?>>Faible</option>
                            <option value="medium" <?php selected($settings['pdf_quality'] ?? 'high', 'medium'); ?>>Moyen</option>
                            <option value="high" <?php selected($settings['pdf_quality'] ?? 'high', 'high'); ?>>Élevée</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_format">Format par défaut</label></th>
                    <td>
                        <input type="text" id="default_format" name="default_format" value="<?php echo esc_attr($settings['default_format'] ?? 'A4'); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_orientation">Orientation par défaut</label></th>
                    <td>
                        <select id="default_orientation" name="default_orientation">
                            <option value="portrait" <?php selected($settings['default_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                            <option value="landscape" <?php selected($settings['default_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_mode">Mode Debug</label></th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="debug_mode" <?php checked($settings['debug_mode'] ?? false); ?> />
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Licence -->
        <div id="licence" class="tab-content" style="display: none;">
            <h2>Licence</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Clé de licence</th>
                    <td>
                        <input type="text" name="licence_key" value="" placeholder="Votre clé de licence">
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Performance -->
        <div id="performance" class="tab-content" style="display: none;">
            <h2>Performance</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                    <td>
                        <input type="checkbox" id="cache_enabled" name="cache_enabled" <?php checked($settings['cache_enabled'] ?? true); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cache_ttl">TTL Cache (secondes)</label></th>
                    <td>
                        <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo esc_attr($settings['cache_ttl'] ?? 3600); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_execution_time">Temps max d'exécution (s)</label></th>
                    <td>
                        <input type="number" id="max_execution_time" name="max_execution_time" value="<?php echo esc_attr($settings['max_execution_time'] ?? 300); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="memory_limit">Limite mémoire</label></th>
                    <td>
                        <input type="text" id="memory_limit" name="memory_limit" value="<?php echo esc_attr($settings['memory_limit'] ?? '256M'); ?>">
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet PDF -->
        <div id="pdf" class="tab-content" style="display: none;">
            <h2>Paramètres PDF</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="pdf_author">Auteur PDF</label></th>
                    <td>
                        <input type="text" id="pdf_author" name="pdf_author" value="<?php echo esc_attr($settings['pdf_author'] ?? get_bloginfo('name')); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pdf_subject">Sujet</label></th>
                    <td>
                        <input type="text" id="pdf_subject" name="pdf_subject" value="<?php echo esc_attr($settings['pdf_subject'] ?? ''); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="embed_fonts">Inclure les polices</label></th>
                    <td>
                        <input type="checkbox" id="embed_fonts" name="embed_fonts" <?php checked($settings['embed_fonts'] ?? true); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="compress_images">Compresser les images</label></th>
                    <td>
                        <input type="checkbox" id="compress_images" name="compress_images" <?php checked($settings['compress_images'] ?? true); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="image_quality">Qualité image (1-100)</label></th>
                    <td>
                        <input type="number" id="image_quality" name="image_quality" min="1" max="100" value="<?php echo esc_attr($settings['image_quality'] ?? 85); ?>">
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Sécurité -->
        <div id="securite" class="tab-content" style="display: none;">
            <h2>Sécurité</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="log_level">Niveau de log</label></th>
                    <td>
                        <select id="log_level" name="log_level">
                            <option value="debug" <?php selected($settings['log_level'] ?? 'info', 'debug'); ?>>Debug</option>
                            <option value="info" <?php selected($settings['log_level'] ?? 'info', 'info'); ?>>Info</option>
                            <option value="warning" <?php selected($settings['log_level'] ?? 'info', 'warning'); ?>>Warning</option>
                            <option value="error" <?php selected($settings['log_level'] ?? 'info', 'error'); ?>>Error</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Rôles -->
        <div id="roles" class="tab-content" style="display: none;">
            <h2>Rôles autorisés</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Rôles WordPress</th>
                    <td>
                        <?php
                        $wp_roles = wp_roles();
                        $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator']);
                        foreach ($wp_roles->get_names() as $role => $name) {
                            $checked = in_array($role, (array) $allowed_roles) ? 'checked' : '';
                            echo '<label><input type="checkbox" name="pdf_builder_allowed_roles[]" value="' . $role . '" ' . $checked . '> ' . $name . '</label><br>';
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Notifications -->
        <div id="notifications" class="tab-content" style="display: none;">
            <h2>Notifications</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="email_notifications">Notifications email activées</label></th>
                    <td>
                        <input type="checkbox" id="email_notifications" name="email_notifications" <?php checked($settings['email_notifications'] ?? false); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="admin_email">Email administrateur</label></th>
                    <td>
                        <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($settings['admin_email'] ?? get_option('admin_email')); ?>">
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Canvas -->
        <div id="canvas" class="tab-content" style="display: none;">
            <h2>Paramètres Canvas</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="default_canvas_width">Largeur (px)</label></th>
                    <td>
                        <input type="number" id="default_canvas_width" name="default_canvas_width" value="<?php echo esc_attr($settings['default_canvas_width'] ?? 794); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_canvas_height">Hauteur (px)</label></th>
                    <td>
                        <input type="number" id="default_canvas_height" name="default_canvas_height" value="<?php echo esc_attr($settings['default_canvas_height'] ?? 1123); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="show_grid">Afficher la grille</label></th>
                    <td>
                        <input type="checkbox" id="show_grid" name="show_grid" <?php checked($settings['show_grid'] ?? true); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="show_guides">Afficher les guides</label></th>
                    <td>
                        <input type="checkbox" id="show_guides" name="show_guides" <?php checked($settings['show_guides'] ?? true); ?> />
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Templates -->
        <div id="templates" class="tab-content" style="display: none;">
            <h2>Templates</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="max_template_size">Taille max template (bytes)</label></th>
                    <td>
                        <input type="number" id="max_template_size" name="max_template_size" value="<?php echo esc_attr($settings['max_template_size'] ?? 52428800); ?>">
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Maintenance -->
        <div id="maintenance" class="tab-content" style="display: none;">
            <h2>Maintenance</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">Options de maintenance</th>
                    <td>
                        <p><button type="button" class="button">Vider le cache</button></p>
                        <p><button type="button" class="button">Réinitialiser les paramètres</button></p>
                    </td>
                </tr>
            </table>
        </div>
        
        <!-- Onglet Développeur -->
        <div id="developpeur" class="tab-content" style="display: none;">
            <h2>Mode Développeur</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_fps">Afficher les FPS</label></th>
                    <td>
                        <input type="checkbox" id="show_fps" name="show_fps" <?php checked($settings['show_fps'] ?? false); ?> />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="enable_keyboard_shortcuts">Raccourcis clavier</label></th>
                    <td>
                        <input type="checkbox" id="enable_keyboard_shortcuts" name="enable_keyboard_shortcuts" <?php checked($settings['enable_keyboard_shortcuts'] ?? true); ?> />
                    </td>
                </tr>
            </table>
        </div>
        
        <p class="submit">
            <button type="submit" name="submit" class="button button-primary">Enregistrer les paramètres</button>
        </p>
    </form>
</div>

<style>
    .nav-tab-wrapper {
        border-bottom: 1px solid #ccc;
        margin: 20px 0;
        white-space: nowrap;
        overflow-x: auto;
    }
    
    .nav-tab {
        background: #f5f5f5;
        border: 1px solid #ccc;
        border-bottom: none;
        color: #0073aa;
        cursor: pointer;
        margin-right: 2px;
        padding: 10px 15px;
        text-decoration: none;
        display: inline-block;
        transition: all 0.2s;
        white-space: nowrap;
    }
    
    .nav-tab:hover {
        background: #e9e9e9;
    }
    
    .nav-tab-active {
        background: #fff;
        border-bottom: 1px solid #fff;
        color: #000;
        font-weight: bold;
    }
    
    .tab-content {
        background: #fff;
        padding: 20px;
        border: 1px solid #ccc;
        border-top: none;
        margin-top: -1px;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            contents.forEach(function(content) {
                content.style.display = 'none';
            });
            
            tabs.forEach(function(t) {
                t.classList.remove('nav-tab-active');
            });
            
            const tabId = this.getAttribute('href').substring(1);
            const selectedContent = document.getElementById(tabId);
            if (selectedContent) {
                selectedContent.style.display = 'block';
            }
            
            this.classList.add('nav-tab-active');
        });
    });
});
</script>

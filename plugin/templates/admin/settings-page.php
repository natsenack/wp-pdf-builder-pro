<?php
/**
 * PDF Builder Pro - Settings Page
 * Complete settings with all tabs
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die(__('You must be logged in', 'pdf-builder-pro'));
}

// Debug: Page loaded
error_log('PDF Builder: Settings page loaded at ' . date('Y-m-d H:i:s'));

// Initialize
$notices = [];
$settings = get_option('pdf_builder_settings', []);
error_log('DEBUG: Settings page loaded, POST data: ' . json_encode($_POST));

// Process form
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    error_log('DEBUG: Form submission detected');
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        error_log('DEBUG: Nonce verified successfully');
        // Check for max_input_vars limit
        $max_input_vars = ini_get('max_input_vars');
        if ($max_input_vars && count($_POST) >= $max_input_vars) {
            $notices[] = '<div class="notice notice-error"><p><strong>⚠️</strong> Trop de paramètres soumis (' . count($_POST) . '). Limite PHP max_input_vars: ' . $max_input_vars . '. Certains paramètres n\'ont pas été sauvegardés.</p></div>';
        }
        $to_save = [
            'debug_mode' => isset($_POST['debug_mode']),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'cache_enabled' => isset($_POST['cache_enabled']),
            'cache_ttl' => intval($_POST['cache_ttl'] ?? 3600),
            'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
            'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
            'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
            'pdf_quality' => sanitize_text_field($_POST['pdf_quality'] ?? 'high'),
            'default_format' => sanitize_text_field($_POST['default_format'] ?? 'A4'),
            'default_orientation' => sanitize_text_field($_POST['default_orientation'] ?? 'portrait'),
            'auto_save_enabled' => isset($_POST['auto_save_enabled']),
            'auto_save_interval' => intval($_POST['auto_save_interval'] ?? 30),
            'compress_images' => isset($_POST['compress_images']),
            'image_quality' => intval($_POST['image_quality'] ?? 85),
            'optimize_for_web' => isset($_POST['optimize_for_web']),
            'enable_hardware_acceleration' => isset($_POST['enable_hardware_acceleration']),
            'limit_fps' => isset($_POST['limit_fps']),
            'max_fps' => intval($_POST['max_fps'] ?? 60),
            'export_quality' => sanitize_text_field($_POST['export_quality'] ?? 'print'),
            'export_format' => sanitize_text_field($_POST['export_format'] ?? 'pdf'),
            'pdf_author' => sanitize_text_field($_POST['pdf_author'] ?? get_bloginfo('name')),
            'pdf_subject' => sanitize_text_field($_POST['pdf_subject'] ?? ''),
            'include_metadata' => isset($_POST['include_metadata']),
            'embed_fonts' => isset($_POST['embed_fonts']),
            'auto_crop' => isset($_POST['auto_crop']),
            'max_image_size' => intval($_POST['max_image_size'] ?? 2048),
            // Canvas
            'default_canvas_width' => intval($_POST['default_canvas_width'] ?? 794),
            'default_canvas_height' => intval($_POST['default_canvas_height'] ?? 1123),
            'canvas_background_color' => sanitize_text_field($_POST['canvas_background_color'] ?? '#ffffff'),
            'container_background_color' => sanitize_text_field($_POST['container_background_color'] ?? '#f8f9fa'),
            'show_margins' => isset($_POST['show_margins']),
            'margin_top' => intval($_POST['margin_top'] ?? 28),
            'margin_right' => intval($_POST['margin_right'] ?? 28),
            'margin_bottom' => intval($_POST['margin_bottom'] ?? 28),
            'margin_left' => intval($_POST['margin_left'] ?? 10),
            'show_grid' => isset($_POST['show_grid']),
            'grid_size' => intval($_POST['grid_size'] ?? 10),
            'grid_color' => sanitize_text_field($_POST['grid_color'] ?? '#e0e0e0'),
            'snap_to_grid' => isset($_POST['snap_to_grid']),
            'snap_to_elements' => isset($_POST['snap_to_elements']),
            'snap_tolerance' => intval($_POST['snap_tolerance'] ?? 5),
            'show_guides' => isset($_POST['show_guides']),
            'default_zoom' => intval($_POST['default_zoom'] ?? 100),
            'zoom_step' => intval($_POST['zoom_step'] ?? 25),
            'min_zoom' => intval($_POST['min_zoom'] ?? 10),
            'max_zoom' => intval($_POST['max_zoom'] ?? 500),
            'zoom_with_wheel' => isset($_POST['zoom_with_wheel']),
            'pan_with_mouse' => isset($_POST['pan_with_mouse']),
            'show_resize_handles' => isset($_POST['show_resize_handles']),
            'handle_size' => intval($_POST['handle_size'] ?? 8),
            'enable_rotation' => isset($_POST['enable_rotation']),
            'rotation_step' => intval($_POST['rotation_step'] ?? 15),
            'multi_select' => isset($_POST['multi_select']),
            'copy_paste_enabled' => isset($_POST['copy_paste_enabled']),
            'undo_levels' => intval($_POST['undo_levels'] ?? 50),
            'redo_levels' => intval($_POST['redo_levels'] ?? 50),
            'auto_save_versions' => intval($_POST['auto_save_versions'] ?? 10),
            // Développeur
            'developer_enabled' => isset($_POST['developer_enabled']),
            'developer_password' => sanitize_text_field($_POST['developer_password'] ?? ''),
            'debug_php_errors' => isset($_POST['debug_php_errors']),
            'debug_javascript' => isset($_POST['debug_javascript']),
            'debug_ajax' => isset($_POST['debug_ajax']),
            'debug_performance' => isset($_POST['debug_performance']),
            'debug_database' => isset($_POST['debug_database']),
            'log_file_size' => intval($_POST['log_file_size'] ?? 10),
            'log_retention' => intval($_POST['log_retention'] ?? 30),
            'disable_hooks' => sanitize_text_field($_POST['disable_hooks'] ?? ''),
            'enable_profiling' => isset($_POST['enable_profiling']),
            'force_https' => isset($_POST['force_https']),
        ];
        error_log('DEBUG: About to save settings: ' . json_encode($to_save));
        $result = update_option('pdf_builder_settings', array_merge($settings, $to_save));
        error_log('DEBUG: Settings saved, result: ' . ($result ? 'success' : 'failed'));
        if ($result) {
            $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres enregistrés avec succès.</p></div>';
        } else {
            $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur lors de la sauvegarde des paramètres.</p></div>';
        }
        $settings = get_option('pdf_builder_settings', []);
    } else {
        $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
    }
}

// Handle cache clear
if (isset($_POST['clear_cache']) &&
    (isset($_POST['pdf_builder_clear_cache_nonce_performance']) ||
     isset($_POST['pdf_builder_clear_cache_nonce_maintenance']))) {

    $nonce_verified = false;
    if (isset($_POST['pdf_builder_clear_cache_nonce_performance'])) {
        $nonce_verified = wp_verify_nonce($_POST['pdf_builder_clear_cache_nonce_performance'], 'pdf_builder_clear_cache_performance');
    } elseif (isset($_POST['pdf_builder_clear_cache_nonce_maintenance'])) {
        $nonce_verified = wp_verify_nonce($_POST['pdf_builder_clear_cache_nonce_maintenance'], 'pdf_builder_clear_cache_maintenance');
    }

    if ($nonce_verified) {
        // Clear transients and cache
        delete_transient('pdf_builder_cache');
        delete_transient('pdf_builder_templates');
        delete_transient('pdf_builder_elements');
        
        // Clear WP object cache if available
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
        }
        
        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Cache vidé avec succès.</p></div>';
    }
}

// Handle individual tab submissions
if (isset($_POST['submit_pdf']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $pdf_settings = [
            'export_quality' => sanitize_text_field($_POST['export_quality'] ?? 'print'),
            'export_format' => sanitize_text_field($_POST['export_format'] ?? 'pdf'),
            'pdf_author' => sanitize_text_field($_POST['pdf_author'] ?? get_bloginfo('name')),
            'pdf_subject' => sanitize_text_field($_POST['pdf_subject'] ?? ''),
            'include_metadata' => isset($_POST['include_metadata']),
            'embed_fonts' => isset($_POST['embed_fonts']),
            'auto_crop' => isset($_POST['auto_crop']),
            'max_image_size' => intval($_POST['max_image_size'] ?? 2048),
        ];
        update_option('pdf_builder_settings', array_merge($settings, $pdf_settings));
        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres PDF enregistrés avec succès.</p></div>';
        $settings = get_option('pdf_builder_settings', []);
    }
}

if (isset($_POST['submit_security']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $security_settings = [
            'max_template_size' => intval($_POST['max_template_size'] ?? 52428800),
            'max_execution_time' => intval($_POST['max_execution_time'] ?? 300),
            'memory_limit' => sanitize_text_field($_POST['memory_limit'] ?? '256M'),
        ];
        update_option('pdf_builder_settings', array_merge($settings, $security_settings));
        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de sécurité enregistrés avec succès.</p></div>';
        $settings = get_option('pdf_builder_settings', []);
    }
}

if (isset($_POST['submit_canvas']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $canvas_settings = [
            'default_canvas_width' => intval($_POST['default_canvas_width'] ?? 794),
            'default_canvas_height' => intval($_POST['default_canvas_height'] ?? 1123),
            'canvas_background_color' => sanitize_text_field($_POST['canvas_background_color'] ?? '#ffffff'),
            'container_background_color' => sanitize_text_field($_POST['container_background_color'] ?? '#f8f9fa'),
            'show_margins' => isset($_POST['show_margins']),
            'margin_top' => intval($_POST['margin_top'] ?? 28),
            'margin_right' => intval($_POST['margin_right'] ?? 28),
            'margin_bottom' => intval($_POST['margin_bottom'] ?? 28),
            'margin_left' => intval($_POST['margin_left'] ?? 10),
            'show_grid' => isset($_POST['show_grid']),
            'grid_size' => intval($_POST['grid_size'] ?? 10),
            'grid_color' => sanitize_text_field($_POST['grid_color'] ?? '#e0e0e0'),
            'snap_to_grid' => isset($_POST['snap_to_grid']),
            'snap_to_elements' => isset($_POST['snap_to_elements']),
            'snap_tolerance' => intval($_POST['snap_tolerance'] ?? 5),
            'show_guides' => isset($_POST['show_guides']),
            'default_zoom' => intval($_POST['default_zoom'] ?? 100),
            'zoom_step' => intval($_POST['zoom_step'] ?? 25),
            'min_zoom' => intval($_POST['min_zoom'] ?? 10),
            'max_zoom' => intval($_POST['max_zoom'] ?? 500),
            'enable_zoom_wheel' => isset($_POST['enable_zoom_wheel']),
            'enable_pan_drag' => isset($_POST['enable_pan_drag']),
            'auto_save_enabled' => isset($_POST['auto_save_enabled']),
            'auto_save_interval' => intval($_POST['auto_save_interval'] ?? 30),
            'auto_save_versions' => intval($_POST['auto_save_versions'] ?? 10),
        ];
        update_option('pdf_builder_settings', array_merge($settings, $canvas_settings));
        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres Canvas enregistrés avec succès.</p></div>';
        $settings = get_option('pdf_builder_settings', []);
    }
}

if (isset($_POST['submit_developpeur']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        $dev_settings = [
            'debug_mode' => isset($_POST['debug_mode']),
            'log_level' => sanitize_text_field($_POST['log_level'] ?? 'info'),
            'enable_debug_js' => isset($_POST['enable_debug_js']),
            'enable_debug_ajax' => isset($_POST['enable_debug_ajax']),
            'enable_debug_performance' => isset($_POST['enable_debug_performance']),
            'log_file_size' => intval($_POST['log_file_size'] ?? 10),
            'log_retention' => intval($_POST['log_retention'] ?? 30),
            'disable_hooks' => sanitize_text_field($_POST['disable_hooks'] ?? ''),
            'enable_profiling' => isset($_POST['enable_profiling']),
            'force_https' => isset($_POST['force_https']),
        ];
        update_option('pdf_builder_settings', array_merge($settings, $dev_settings));
        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres développeur enregistrés avec succès.</p></div>';
        $settings = get_option('pdf_builder_settings', []);
    }
}
?>  
<div class="wrap">
    <h1><?php _e('⚙️ PDF Builder Pro Settings', 'pdf-builder-pro'); ?></h1>
    
    <?php foreach ($notices as $notice) echo $notice; ?>
    
    <div class="nav-tab-wrapper wp-clearfix">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general">
            <span class="tab-icon">⚙️</span>
            <span class="tab-text">Général</span>
        </a>
        <a href="#licence" class="nav-tab" data-tab="licence">
            <span class="tab-icon">🔑</span>
            <span class="tab-text">Licence</span>
        </a>
        <a href="#performance" class="nav-tab" data-tab="performance">
            <span class="tab-icon">🚀</span>
            <span class="tab-text">Performance</span>
        </a>
        <a href="#pdf" class="nav-tab" data-tab="pdf">
            <span class="tab-icon">📄</span>
            <span class="tab-text">PDF</span>
        </a>
        <a href="#securite" class="nav-tab" data-tab="securite">
            <span class="tab-icon">🔒</span>
            <span class="tab-text">Sécurité</span>
        </a>
        <a href="#roles" class="nav-tab" data-tab="roles">
            <span class="tab-icon">👥</span>
            <span class="tab-text">Rôles</span>
        </a>
        <a href="#notifications" class="nav-tab" data-tab="notifications">
            <span class="tab-icon">🔔</span>
            <span class="tab-text">Notifications</span>
        </a>
        <a href="#canvas" class="nav-tab" data-tab="canvas">
            <span class="tab-icon">🎨</span>
            <span class="tab-text">Canvas</span>
        </a>
        <a href="#templates" class="nav-tab" data-tab="templates">
            <span class="tab-icon">📋</span>
            <span class="tab-text">Templates</span>
        </a>
        <a href="#maintenance" class="nav-tab" data-tab="maintenance">
            <span class="tab-icon">🔧</span>
            <span class="tab-text">Maintenance</span>
        </a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur">
            <span class="tab-icon">👨‍💻</span>
            <span class="tab-text">Développeur</span>
        </a>
    </div>
    
    <form method="post" class="settings-form" id="settings-form">
        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>
        
        <div id="general" class="tab-content" style="display: block;">
            <h2>Paramètres Généraux</h2>
            <p style="color: #666;">Paramètres de base pour la génération PDF. Pour le cache et la sécurité, voir les onglets Performance et Sécurité.</p>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📋 Cache</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="cache_enabled" name="cache_enabled" value="1" <?php checked($settings['cache_enabled'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le cache</span>
                        </div>
                        <div class="toggle-description">Améliore les performances en mettant en cache les données</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cache_ttl">TTL du cache (secondes)</label></th>
                    <td>
                        <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo intval($settings['cache_ttl'] ?? 3600); ?>" min="0" max="86400" />
                        <p class="description">Durée de vie du cache en secondes (défaut: 3600)</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📄 Paramètres PDF</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="pdf_quality">Qualité PDF</label></th>
                    <td>
                        <select id="pdf_quality" name="pdf_quality">
                            <option value="low" <?php selected($settings['pdf_quality'] ?? 'high', 'low'); ?>>Faible (fichiers plus petits)</option>
                            <option value="medium" <?php selected($settings['pdf_quality'] ?? 'high', 'medium'); ?>>Moyen</option>
                            <option value="high" <?php selected($settings['pdf_quality'] ?? 'high', 'high'); ?>>Élevée (meilleure qualité)</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_format">Format PDF par défaut</label></th>
                    <td>
                        <select id="default_format" name="default_format">
                            <option value="A4" <?php selected($settings['default_format'] ?? 'A4', 'A4'); ?>>A4</option>
                            <option value="A3" <?php selected($settings['default_format'] ?? 'A4', 'A3'); ?>>A3</option>
                            <option value="Letter" <?php selected($settings['default_format'] ?? 'A4', 'Letter'); ?>>Letter</option>
                            <option value="Legal" <?php selected($settings['default_format'] ?? 'A4', 'Legal'); ?>>Legal</option>
                        </select>
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
            </table>
            
            <p class="submit">
                <button type="submit" name="submit" class="button button-primary">Enregistrer les paramètres</button>
                <button type="button" id="debug-btn" class="button">Debug Form</button>
            </p>
        </div>
    </form>

    <script>
        console.log('DEBUG SCRIPT LOADED');
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DEBUG DOMContentLoaded fired');
            document.getElementById('debug-btn').addEventListener('click', function() {
                console.log('=== FORM DEBUG START ===');
                const form = document.getElementById('settings-form');
                console.log('Form found:', !!form);

                if (form) {
                    console.log('Form elements count:', form.elements.length);
                    console.log('Form method:', form.method);
                    console.log('Form action:', form.action);

                    for(let i = 0; i < form.elements.length; i++) {
                        const el = form.elements[i];
                        console.log(`Element ${i}: name="${el.name}" type="${el.type}" value="${el.value}" checked="${el.checked}"`);
                    }

                    const formData = new FormData(form);
                    console.log('FormData entries count:', [...formData.entries()].length);
                    for (let [key, value] of formData.entries()) {
                        console.log(`FormData: ${key} = ${value}`);
                    }
                } else {
                    console.log('Form not found!');
                }
                console.log('=== FORM DEBUG END ===');
            });
        });
    </script>
        
        <div id="licence" class="tab-content" style="display: none;">
            <h2>Gestion de la Licence</h2>
            
            <?php
            $license_status = get_option('pdf_builder_license_status', 'free');
            $license_key = get_option('pdf_builder_license_key', '');
            $license_expires = get_option('pdf_builder_license_expires', '');
            $is_premium = $license_status !== 'free' && $license_status !== 'expired';
            
            // Traitement activation licence
            if (isset($_POST['activate_license']) && isset($_POST['pdf_builder_license_nonce'])) {
                if (wp_verify_nonce($_POST['pdf_builder_license_nonce'], 'pdf_builder_license')) {
                    $new_key = sanitize_text_field($_POST['license_key'] ?? '');
                    if (!empty($new_key)) {
                        update_option('pdf_builder_license_key', $new_key);
                        update_option('pdf_builder_license_status', 'active');
                        update_option('pdf_builder_license_expires', date('Y-m-d', strtotime('+1 year')));
                        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence activée avec succès !</p></div>';
                        $is_premium = true;
                        $license_key = $new_key;
                        $license_status = 'active';
                    }
                }
            }
            
            // Traitement désactivation licence
            if (isset($_POST['deactivate_license']) && isset($_POST['pdf_builder_deactivate_nonce'])) {
                if (wp_verify_nonce($_POST['pdf_builder_deactivate_nonce'], 'pdf_builder_deactivate')) {
                    delete_option('pdf_builder_license_key');
                    delete_option('pdf_builder_license_expires');
                    update_option('pdf_builder_license_status', 'free');
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Licence désactivée.</p></div>';
                    $is_premium = false;
                    $license_key = '';
                    $license_status = 'free';
                }
            }
            ?>
            
            <!-- Statut de la licence -->
            <div style="background: #fff; border: 1px solid #e5e5e5; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3 style="margin-top: 0;">Statut de la Licence</h3>
                
                <div style="display: inline-block; padding: 12px 20px; border-radius: 4px; font-weight: bold; margin-bottom: 15px; color: white;
                            background: <?php echo $is_premium ? '#28a745' : '#6c757d'; ?>;">
                    <?php echo $is_premium ? '✓ Premium Activé' : '○ Gratuit'; ?>
                </div>
                
                <?php if ($is_premium): ?>
                    <div style="margin-bottom: 15px;">
                        <p><strong>Clé de licence :</strong> <?php echo substr($license_key, 0, 4) . '****' . substr($license_key, -4); ?></p>
                        <?php if ($license_expires): ?>
                            <p><strong>Expire le :</strong> <?php echo esc_html($license_expires); ?></p>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; margin-top: 20px;">
                        <h4 style="margin: 0 0 10px 0; color: white;">🔓 Passez à la version Premium</h4>
                        <p style="margin: 0 0 15px 0;">Débloquez toutes les fonctionnalités avancées et créez des PDFs professionnels sans limites !</p>
                        <a href="https://pdfbuilderpro.com/pricing" class="button button-primary" target="_blank" 
                           style="background: white; color: #667eea; border: none; font-weight: bold;">
                            Voir les tarifs →
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Activation/Désactivation -->
            <?php if (!$is_premium): ?>
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3>Activer une Licence Premium</h3>
                <p>Entrez votre clé de licence pour débloquer toutes les fonctionnalités premium.</p>
                
                <form method="post">
                    <?php wp_nonce_field('pdf_builder_license', 'pdf_builder_license_nonce'); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="license_key">Clé de licence</label></th>
                            <td>
                                <input type="text" id="license_key" name="license_key" class="regular-text" 
                                       placeholder="XXXX-XXXX-XXXX-XXXX" style="min-width: 300px;">
                                <p class="description">Vous pouvez trouver votre clé dans votre compte client.</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" name="activate_license" class="button button-primary">
                            Activer la licence
                        </button>
                    </p>
                </form>
            </div>
            <?php else: ?>
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-bottom: 20px;">
                <h3>Gestion de la Licence</h3>
                <p>Votre licence premium est active. Vous pouvez la désactiver pour la transférer vers un autre site.</p>
                
                <form method="post">
                    <?php wp_nonce_field('pdf_builder_deactivate', 'pdf_builder_deactivate_nonce'); ?>
                    <p class="submit">
                        <button type="submit" name="deactivate_license" class="button button-secondary"
                                onclick="return confirm('Êtes-vous sûr de vouloir désactiver cette licence ?');">
                            Désactiver la licence
                        </button>
                    </p>
                </form>
            </div>
            <?php endif; ?>
            
            <!-- Comparaison des fonctionnalités -->
            <div style="margin-top: 30px;">
                <h3>Comparaison des Fonctionnalités</h3>
                <table class="wp-list-table widefat fixed striped" style="margin-top: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 40%;">Fonctionnalité</th>
                            <th style="width: 15%; text-align: center;">Gratuit</th>
                            <th style="width: 15%; text-align: center;">Premium</th>
                            <th style="width: 30%;">Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Templates de base</strong></td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>4 templates prédéfinis</td>
                        </tr>
                        <tr>
                            <td><strong>Éléments standards</strong></td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>Texte, image, ligne, rectangle</td>
                        </tr>
                        <tr>
                            <td><strong>Intégration WooCommerce</strong></td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>Variables de commande</td>
                        </tr>
                        <tr>
                            <td><strong>Génération PDF</strong></td>
                            <td style="text-align: center; color: #ffb900;">50/mois</td>
                            <td style="text-align: center; color: #46b450;">✓ Illimitée</td>
                            <td>Création de documents</td>
                        </tr>
                        <tr>
                            <td><strong>Templates avancés</strong></td>
                            <td style="text-align: center; color: #dc3232;">✗</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>Bibliothèque complète</td>
                        </tr>
                        <tr>
                            <td><strong>Éléments premium</strong></td>
                            <td style="text-align: center; color: #dc3232;">✗</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>Codes-barres, QR codes, graphiques</td>
                        </tr>
                        <tr>
                            <td><strong>Génération en masse</strong></td>
                            <td style="text-align: center; color: #dc3232;">✗</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>Création multiple de documents</td>
                        </tr>
                        <tr>
                            <td><strong>API développeur</strong></td>
                            <td style="text-align: center; color: #dc3232;">✗</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>Accès complet à l'API REST</td>
                        </tr>
                        <tr>
                            <td><strong>White-label</strong></td>
                            <td style="text-align: center; color: #dc3232;">✗</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>Rebranding complet</td>
                        </tr>
                        <tr>
                            <td><strong>Support prioritaire</strong></td>
                            <td style="text-align: center; color: #dc3232;">✗</td>
                            <td style="text-align: center; color: #46b450;">✓</td>
                            <td>24/7 avec SLA garanti</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="performance" class="tab-content" style="display: none;">
            <h2>Paramètres de Performance</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="auto_save_enabled">Sauvegarde Auto</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="auto_save_enabled" name="auto_save_enabled" value="1" 
                                       <?php checked($settings['auto_save_enabled'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Sauvegarde automatique</span>
                        </div>
                        <div class="toggle-description">Sauvegarde automatique pendant l'édition</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="auto_save_interval">Intervalle Auto-save (secondes)</label></th>
                    <td>
                        <input type="number" id="auto_save_interval" name="auto_save_interval" value="<?php echo intval($settings['auto_save_interval'] ?? 30); ?>" 
                               min="10" max="300" step="10" />
                        <p class="description">Intervalle entre chaque sauvegarde automatique</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="compress_images">Compresser les Images</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="compress_images" name="compress_images" value="1" 
                                       <?php checked($settings['compress_images'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Compression d'images</span>
                        </div>
                        <div class="toggle-description">Compresse les images pour réduire la taille des PDFs</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="image_quality">Qualité des Images (%)</label></th>
                    <td>
                        <input type="range" id="image_quality" name="image_quality" value="<?php echo intval($settings['image_quality'] ?? 85); ?>" 
                               min="30" max="100" step="5" style="width: 300px;" />
                        <span id="image_quality_value" style="margin-left: 10px; font-weight: bold;">
                            <?php echo intval($settings['image_quality'] ?? 85); ?>%
                        </span>
                        <p class="description">Plus faible = fichiers plus petits mais moins de détails</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="optimize_for_web">Optimiser pour le Web</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="optimize_for_web" name="optimize_for_web" value="1" 
                                       <?php checked($settings['optimize_for_web'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Optimisation web</span>
                        </div>
                        <div class="toggle-description">Réduit la taille du fichier pour une meilleure distribution web</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="enable_hardware_acceleration">Accélération Matérielle</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="enable_hardware_acceleration" name="enable_hardware_acceleration" value="1" 
                                       <?php checked($settings['enable_hardware_acceleration'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">GPU activé</span>
                        </div>
                        <div class="toggle-description">Utilise les ressources GPU si disponibles</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="limit_fps">Limiter les FPS</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="limit_fps" name="limit_fps" value="1" 
                                       <?php checked($settings['limit_fps'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Limitation FPS</span>
                        </div>
                        <div class="toggle-description">Limite le rendu pour économiser les ressources</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_fps">FPS Maximum</label></th>
                    <td>
                        <input type="number" id="max_fps" name="max_fps" value="<?php echo intval($settings['max_fps'] ?? 60); ?>" 
                               min="15" max="240" />
                        <p class="description">Images par seconde maximales (15-240 FPS)</p>
                    </td>
                </tr>
            </table>
            
            <!-- Section Nettoyage -->
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 30px;">
                <h3>Nettoyage & Maintenance</h3>
                <p>Supprimez les données temporaires et les fichiers obsolètes pour optimiser les performances.</p>
                
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('pdf_builder_clear_cache_performance', 'pdf_builder_clear_cache_nonce_performance'); ?>
                    <button type="submit" name="clear_cache" class="button button-secondary">
                        🗑️ Vider le Cache
                    </button>
                </form>
                
                <div style="margin-top: 20px; padding: 15px; background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px;">
                    <p style="margin: 0;"><strong>💡 Conseil :</strong> Videz le cache si vous rencontrez des problèmes de génération PDF ou si les changements n'apparaissent pas.</p>
                </div>
            </div>
        </div>
        
        <div id="pdf" class="tab-content" style="display: none;">
            <h2>Paramètres PDF</h2>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Qualité & Export</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="export_quality">Qualité d'Export</label></th>
                    <td>
                        <select id="export_quality" name="export_quality">
                            <option value="screen" <?php selected($settings['export_quality'] ?? 'print', 'screen'); ?>>Écran (72 DPI)</option>
                            <option value="print" <?php selected($settings['export_quality'] ?? 'print', 'print'); ?>>Impression (300 DPI)</option>
                            <option value="prepress" <?php selected($settings['export_quality'] ?? 'print', 'prepress'); ?>>Pré-presse (600 DPI)</option>
                        </select>
                        <p class="description">Définit la résolution de sortie du PDF</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="export_format">Format d'Export</label></th>
                    <td>
                        <select id="export_format" name="export_format">
                            <option value="pdf" <?php selected($settings['export_format'] ?? 'pdf', 'pdf'); ?>>PDF</option>
                            <option value="png" <?php selected($settings['export_format'] ?? 'pdf', 'png'); ?>>PNG</option>
                            <option value="jpg" <?php selected($settings['export_format'] ?? 'pdf', 'jpg'); ?>>JPEG</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Métadonnées & Contenu</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="pdf_author">Auteur du PDF</label></th>
                    <td>
                        <input type="text" id="pdf_author" name="pdf_author" value="<?php echo esc_attr($settings['pdf_author'] ?? get_bloginfo('name')); ?>" 
                               class="regular-text" />
                        <p class="description">Sera inclus dans les propriétés du PDF</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pdf_subject">Sujet du PDF</label></th>
                    <td>
                        <input type="text" id="pdf_subject" name="pdf_subject" value="<?php echo esc_attr($settings['pdf_subject'] ?? ''); ?>" 
                               class="regular-text" placeholder="Ex: Facture, Devis, etc." />
                        <p class="description">Sujet dans les propriétés du PDF</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="include_metadata">Inclure les Métadonnées</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="include_metadata" name="include_metadata" value="1" 
                                       <?php checked($settings['include_metadata'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Métadonnées PDF</span>
                        </div>
                        <div class="toggle-description">Ajoute les données de titre, auteur, date, etc.</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Optimisation & Compression</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="embed_fonts">Intégrer les Polices</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="embed_fonts" name="embed_fonts" value="1" 
                                       <?php checked($settings['embed_fonts'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Polices intégrées</span>
                        </div>
                        <div class="toggle-description">Inclut les polices personnalisées dans le PDF (fichiers plus gros)</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="auto_crop">Recadrage Automatique</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="auto_crop" name="auto_crop" value="1" 
                                       <?php checked($settings['auto_crop'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Recadrage auto</span>
                        </div>
                        <div class="toggle-description">Supprime les marges blanches automatiquement</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_image_size">Taille Max des Images (px)</label></th>
                    <td>
                        <input type="number" id="max_image_size" name="max_image_size" value="<?php echo intval($settings['max_image_size'] ?? 2048); ?>" 
                               min="512" max="8192" step="256" />
                        <p class="description">Les images plus grandes seront redimensionnées</p>
                    </td>
                </tr>
            </table>
            
            <!-- Aide & Conseils -->
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 30px;">
                <h3>💡 Conseils d'Optimisation</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Pour impression :</strong> Utilisez la qualité "Haute" + Pré-presse + Polices intégrées</li>
                    <li><strong>Pour web :</strong> Utilisez la qualité "Moyenne" + Écran + Compression images</li>
                    <li><strong>Pour email :</strong> Utilisez la qualité "Basse" + Optimiser pour le web + Recadrage auto</li>
                </ul>
            </div>
            
            <p class="submit">
                <button type="submit" name="submit_pdf" class="button button-primary">Enregistrer les paramètres PDF</button>
            </p>
        </div>
        
        <div id="securite" class="tab-content" style="display: none;">
            <h2>Paramètres de Sécurité</h2>
            <p style="color: #666;">Configurations de sécurité et limites système. Pour le debug et logging, voir l'onglet Développeur.</p>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">⚙️ Limites & Protections Système</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="max_template_size">Taille Max Template (octets)</label></th>
                    <td>
                        <input type="number" id="max_template_size" name="max_template_size" 
                               value="<?php echo intval($settings['max_template_size'] ?? 52428800); ?>" min="1048576" step="1048576" />
                        <p class="description">Maximum: ~<?php echo number_format(intval($settings['max_template_size'] ?? 52428800) / 1048576); ?> MB (défaut: 50 MB)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_execution_time">Temps Max d'Exécution (secondes)</label></th>
                    <td>
                        <input type="number" id="max_execution_time" name="max_execution_time" 
                               value="<?php echo intval($settings['max_execution_time'] ?? 300); ?>" min="1" max="3600" />
                        <p class="description">Temps avant timeout pour la génération PDF (défaut: 300 secondes)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="memory_limit">Limite Mémoire</label></th>
                    <td>
                        <input type="text" id="memory_limit" name="memory_limit" 
                               value="<?php echo esc_attr($settings['memory_limit'] ?? '256M'); ?>" 
                               placeholder="256M" />
                        <p class="description">Format: 256M, 512M, 1G. Doit être ≥ taille max template (défaut: 256M)</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🔐 Protections</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label>Nonces WordPress</label></th>
                    <td>
                        <p style="margin: 0;">✓ Les nonces expirent après <strong>24 heures</strong> pour plus de sécurité</p>
                        <p style="margin: 0; margin-top: 10px;">✓ Tous les formulaires sont protégés par des nonces WordPress</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Rate Limiting</label></th>
                    <td>
                        <p style="margin: 0;">✓ Le rate limiting est automatiquement activé pour prévenir les abus</p>
                        <p style="margin: 0; margin-top: 10px;">Limite: <strong>100 requêtes par minute</strong> par IP</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Permissions</label></th>
                    <td>
                        <p style="margin: 0;">✓ Accès à PDF Builder Pro limité aux rôles autorisés</p>
                        <p style="margin: 0; margin-top: 10px;">Voir l'onglet "Rôles" pour configurer les accès</p>
                    </td>
                </tr>
            </table>
            
            <!-- Section Sécurité avancée -->
            <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 20px; margin-top: 30px;">
                <h3>🔒 Sécurité Avancée</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li>✓ Sanitization de toutes les entrées utilisateur</li>
                    <li>✓ Validation des fichiers uploadés</li>
                    <li>✓ Protection XSS et CSRF</li>
                    <li>✓ Permissions WordPress vérifiées</li>
                    <li>✓ Logs sécurisés des actions critiques</li>
                </ul>
            </div>
            
            <!-- Conseils de sécurité -->
            <div style="background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #856404;">💡 Conseils Sécurité</h3>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li><strong>Production :</strong> Désactivez le mode debug et mettez "Error" en log level</li>
                    <li><strong>Memory limit :</strong> Doit être suffisant pour vos plus gros PDFs</li>
                    <li><strong>Mises à jour :</strong> Gardez WordPress et les plugins à jour</li>
                    <li><strong>Sauvegardes :</strong> Effectuez des sauvegardes régulières</li>
                </ul>
            </div>
            
            <p class="submit">
                <button type="submit" name="submit_security" class="button button-primary">Enregistrer les paramètres de sécurité</button>
            </p>
        </div>
        
        <div id="roles" class="tab-content" style="display: none;">
            <h2>Gestion des Rôles et Permissions</h2>
            
            <?php
            // Traitement de la sauvegarde des rôles autorisés
            if (isset($_POST['submit_roles']) && isset($_POST['pdf_builder_roles_nonce'])) {
                if (wp_verify_nonce($_POST['pdf_builder_roles_nonce'], 'pdf_builder_roles')) {
                    $allowed_roles = isset($_POST['pdf_builder_allowed_roles']) 
                        ? array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles'])
                        : [];
                    
                    if (empty($allowed_roles)) {
                        $allowed_roles = ['administrator']; // Au minimum l'admin
                    }
                    
                    update_option('pdf_builder_allowed_roles', $allowed_roles);
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Rôles autorisés mis à jour avec succès.</p></div>';
                }
            }
            
            global $wp_roles;
            $all_roles = $wp_roles->roles;
            $allowed_roles = get_option('pdf_builder_allowed_roles', ['administrator', 'editor', 'shop_manager']);
            if (!is_array($allowed_roles)) {
                $allowed_roles = ['administrator', 'editor', 'shop_manager'];
            }
            
            $role_descriptions = [
                'administrator' => 'Accès complet à toutes les fonctionnalités',
                'editor' => 'Peut publier et gérer les articles',
                'author' => 'Peut publier ses propres articles',
                'contributor' => 'Peut soumettre des articles pour révision',
                'subscriber' => 'Peut uniquement lire les articles',
                'shop_manager' => 'Gestionnaire de boutique WooCommerce',
                'customer' => 'Client WooCommerce',
            ];
            ?>
            
            <p style="margin-bottom: 20px;">Sélectionnez les rôles WordPress qui auront accès à PDF Builder Pro.</p>
            
            <form method="post">
                <?php wp_nonce_field('pdf_builder_roles', 'pdf_builder_roles_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="pdf_builder_allowed_roles">Rôles avec Accès</label></th>
                        <td>
                            <!-- Boutons de sélection rapide -->
                            <div style="margin-bottom: 15px;">
                                <button type="button" id="select-all-roles" class="button button-secondary" style="margin-right: 5px;">
                                    Sélectionner Tout
                                </button>
                                <button type="button" id="select-common-roles" class="button button-secondary" style="margin-right: 5px;">
                                    Rôles Courants
                                </button>
                                <span class="description" style="margin-left: 10px;">
                                    Sélectionnés: <strong id="selected-count"><?php echo count($allowed_roles); ?></strong> rôle(s)
                                </span>
                            </div>
                            
                            <select name="pdf_builder_allowed_roles[]" id="pdf_builder_allowed_roles" multiple="multiple" 
                                    style="height: 250px; width: 100%; max-width: 500px;">
                                <?php foreach ($all_roles as $role_key => $role):
                                    $role_name = translate_user_role($role['name']);
                                    $is_selected = in_array($role_key, $allowed_roles);
                                    $description = $role_descriptions[$role_key] ?? 'Rôle personnalisé';
                                ?>
                                    <option value="<?php echo esc_attr($role_key); ?>" 
                                            <?php selected($is_selected); ?>
                                            title="<?php echo esc_attr($description); ?>">
                                        <?php echo esc_html($role_name); ?> 
                                        <em style="color: #666;">(<?php echo esc_html($role_key); ?>)</em>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <p class="description">
                                💡 Maintenez Ctrl (ou Cmd sur Mac) pour sélectionner plusieurs rôles<br>
                                📝 Survolez les rôles pour voir leur description
                            </p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="submit_roles" class="button button-primary">
                        Sauvegarder les Rôles
                    </button>
                </p>
            </form>
            
            <!-- Permissions incluses -->
            <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #003d66;">🔐 Permissions Incluses</h3>
                <p style="margin: 10px 0; color: #003d66;">Les rôles sélectionnés auront accès à :</p>
                <ul style="margin: 0; padding-left: 20px; color: #003d66;">
                    <li>✅ Création, édition et suppression de templates PDF</li>
                    <li>✅ Génération et téléchargement de PDF</li>
                    <li>✅ Accès aux paramètres et configuration</li>
                    <li>✅ Prévisualisation avant génération</li>
                    <li>✅ Gestion des commandes WooCommerce (si applicable)</li>
                </ul>
            </div>
            
            <!-- Avertissement important -->
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #856404;">⚠️ Informations Importantes</h3>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li>Les rôles non sélectionnés n'auront aucun accès à PDF Builder Pro</li>
                    <li>Le rôle "Administrator" a toujours accès complet, indépendamment</li>
                    <li>Minimum requis : au moins un rôle sélectionné</li>
                </ul>
            </div>
            
            <!-- Conseils d'utilisation -->
            <div style="background: #f0f0f0; border-left: 4px solid #666; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0;">💡 Conseils d'Utilisation</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Basique :</strong> Sélectionnez "Administrator" et "Editor"</li>
                    <li><strong>WooCommerce :</strong> Ajoutez "Shop Manager"</li>
                    <li><strong>Multi-utilisateurs :</strong> Utilisez "Rôles Courants" pour configuration rapide</li>
                    <li><strong>Sécurité :</strong> Limitez l'accès aux rôles les moins permissifs nécessaires</li>
                </ul>
            </div>
            
            <!-- Tableau de référence des rôles -->
            <div style="margin-top: 30px;">
                <h3>📋 Référence des Rôles WordPress</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Rôle</th>
                            <th style="width: 50%;">Description</th>
                            <th style="width: 30%; text-align: center;">Recommandé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Administrator</strong></td>
                            <td>Accès complet à toutes les fonctionnalités WordPress et PDF Builder Pro</td>
                            <td style="text-align: center; color: #46b450;">✓ Oui</td>
                        </tr>
                        <tr>
                            <td><strong>Editor</strong></td>
                            <td>Peut publier et gérer tous les articles, y compris les PDFs</td>
                            <td style="text-align: center; color: #46b450;">✓ Oui</td>
                        </tr>
                        <tr>
                            <td><strong>Author</strong></td>
                            <td>Peut publier ses propres articles avec générateur PDF</td>
                            <td style="text-align: center;">○ Optionnel</td>
                        </tr>
                        <tr>
                            <td><strong>Contributor</strong></td>
                            <td>Peut soumettre des brouillons mais n'a accès qu'à la prévisualisation</td>
                            <td style="text-align: center;">○ Optionnel</td>
                        </tr>
                        <tr>
                            <td><strong>Shop Manager</strong></td>
                            <td>Gestionnaire WooCommerce, accès aux factures et devis PDF</td>
                            <td style="text-align: center; color: #46b450;">✓ Pour boutiques</td>
                        </tr>
                        <tr>
                            <td><strong>Customer</strong></td>
                            <td>Client WooCommerce, accès à ses commandes</td>
                            <td style="text-align: center; color: #dc3232;">✗ Non</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="notifications" class="tab-content" style="display: none;">
            <h2>Paramètres de Notifications</h2>
            
            <?php
            // Traitement de la sauvegarde des notifications
            if (isset($_POST['submit_notifications']) && isset($_POST['pdf_builder_notifications_nonce'])) {
                if (wp_verify_nonce($_POST['pdf_builder_notifications_nonce'], 'pdf_builder_notifications')) {
                    $notification_settings = [
                        'email_notifications_enabled' => isset($_POST['email_notifications_enabled']),
                        'admin_email' => sanitize_email($_POST['admin_email'] ?? get_option('admin_email')),
                        'notification_log_level' => sanitize_text_field($_POST['notification_log_level'] ?? 'error'),
                        'notification_on_generation' => isset($_POST['notification_on_generation']),
                        'notification_on_error' => isset($_POST['notification_on_error']),
                        'notification_on_deletion' => isset($_POST['notification_on_deletion']),
                    ];
                    
                    foreach ($notification_settings as $key => $value) {
                        update_option('pdf_builder_' . $key, $value);
                    }
                    
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres de notifications sauvegardés.</p></div>';
                }
            }
            
            $email_notifications = get_option('pdf_builder_email_notifications_enabled', false);
            $admin_email = get_option('pdf_builder_admin_email', get_option('admin_email'));
            $notification_level = get_option('pdf_builder_notification_log_level', 'error');
            ?>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Notifications par Email</h3>
            
            <form method="post">
                <?php wp_nonce_field('pdf_builder_notifications', 'pdf_builder_notifications_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="email_notifications_enabled">Notifications Email</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="email_notifications_enabled" name="email_notifications_enabled" value="1" 
                                           <?php checked($email_notifications); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Notifications email</span>
                            </div>
                            <div class="toggle-description">Active les notifications par email pour les erreurs et événements importants</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="admin_email">Email Administrateur</label></th>
                        <td>
                            <input type="email" id="admin_email" name="admin_email" value="<?php echo esc_attr($admin_email); ?>" 
                                   class="regular-text" />
                            <p class="description">Adresse email pour recevoir les notifications système</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notification_log_level">Niveau de Notification</label></th>
                        <td>
                            <select id="notification_log_level" name="notification_log_level">
                                <option value="error" <?php selected($notification_level, 'error'); ?>>Erreurs uniquement</option>
                                <option value="warning" <?php selected($notification_level, 'warning'); ?>>Erreurs et avertissements</option>
                                <option value="info" <?php selected($notification_level, 'info'); ?>>Tous les événements importants</option>
                            </select>
                            <p class="description">Détermine quels événements déclencheront une notification email</p>
                        </td>
                    </tr>
                </table>
                
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Événements de Notification</h3>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="notification_on_generation">Génération PDF</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notification_on_generation" name="notification_on_generation" value="1" 
                                           <?php checked(get_option('pdf_builder_notification_on_generation')); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Génération réussie</span>
                            </div>
                            <div class="toggle-description">Notifier à chaque génération de PDF réussie</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notification_on_error">Erreurs</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notification_on_error" name="notification_on_error" value="1" 
                                           <?php checked(get_option('pdf_builder_notification_on_error')); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Erreurs de génération</span>
                            </div>
                            <div class="toggle-description">Notifier en cas d'erreur lors de la génération</div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notification_on_deletion">Suppression</label></th>
                        <td>
                            <div class="toggle-container">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="notification_on_deletion" name="notification_on_deletion" value="1" 
                                           <?php checked(get_option('pdf_builder_notification_on_deletion')); ?> />
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="toggle-label">Suppression templates</span>
                            </div>
                            <div class="toggle-description">Notifier lors de la suppression de templates</div>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="submit_notifications" class="button button-primary">
                        Sauvegarder les Notifications
                    </button>
                    <button type="button" id="test-notifications" class="button button-secondary" style="margin-left: 10px;">
                        🧪 Tester les Notifications
                    </button>
                </p>
            </form>
            
            <!-- Informations sur les notifications -->
            <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #003d66;">📧 Informations sur les Notifications</h3>
                <ul style="margin: 0; padding-left: 20px; color: #003d66;">
                    <li><strong>Email actuel :</strong> <?php echo esc_html($admin_email); ?></li>
                    <li>Les notifications sont envoyées aux administrateurs autorisés</li>
                    <li>Les emails peuvent être personnalisés via des filtres WordPress</li>
                    <li>Les logs de notification sont conservés pendant 30 jours</li>
                </ul>
            </div>
            
            <!-- Exemples de notifications -->
            <div style="background: #f8f9fa; border-left: 4px solid #666; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0;">💡 Exemples de Notifications</h3>
                <p><strong>Erreur :</strong> "PDF generation failed for order #1234: Memory limit exceeded"</p>
                <p><strong>Avertissement :</strong> "Large template detected: file size 45MB, consider optimizing"</p>
                <p><strong>Info :</strong> "Successfully generated 150 PDFs in batch process (12.5s)"</p>
            </div>
            
            <!-- Tableau des types de notifications -->
            <div style="margin-top: 30px;">
                <h3>📋 Types de Notifications</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Type</th>
                            <th style="width: 35%;">Description</th>
                            <th style="width: 20%; text-align: center;">Niveau</th>
                            <th style="width: 20%; text-align: center;">Activé</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Génération Réussie</strong></td>
                            <td>Un PDF a été généré avec succès</td>
                            <td style="text-align: center;">Info</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled <?php checked(get_option('pdf_builder_notification_on_generation')); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Erreur</strong></td>
                            <td>Une erreur s'est produite lors de la génération</td>
                            <td style="text-align: center; color: #dc3232;">Erreur</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled <?php checked(get_option('pdf_builder_notification_on_error')); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Avertissement</strong></td>
                            <td>Dépassement de limite de ressources</td>
                            <td style="text-align: center; color: #ffb900;">Avertissement</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled checked />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Suppression</strong></td>
                            <td>Un template a été supprimé</td>
                            <td style="text-align: center;">Info</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled <?php checked(get_option('pdf_builder_notification_on_deletion')); ?> />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Maintenance</strong></td>
                            <td>Mises à jour et maintenance du système</td>
                            <td style="text-align: center;">Info</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled checked />
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Activation License</strong></td>
                            <td>Licence activée ou expirée</td>
                            <td style="text-align: center;">Info</td>
                            <td style="text-align: center;">
                                <input type="checkbox" disabled checked />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div id="canvas" class="tab-content" style="display: none;">
            <h2>Paramètres Canvas</h2>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Dimensions par Défaut</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="default_canvas_width">Largeur</label></th>
                    <td>
                        <input type="number" id="default_canvas_width" name="default_canvas_width" 
                               value="<?php echo intval($settings['default_canvas_width'] ?? 794); ?>" 
                               min="50" max="2000" />
                        <span>px</span>
                        <p class="description">Largeur par défaut du canvas (794px = A4)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_canvas_height">Hauteur</label></th>
                    <td>
                        <input type="number" id="default_canvas_height" name="default_canvas_height" 
                               value="<?php echo intval($settings['default_canvas_height'] ?? 1123); ?>" 
                               min="50" max="2000" />
                        <span>px</span>
                        <p class="description">Hauteur par défaut du canvas (1123px = A4)</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Fond & Couleurs</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="canvas_background_color">Couleur Fond Canvas</label></th>
                    <td>
                        <input type="color" id="canvas_background_color" name="canvas_background_color" 
                               value="<?php echo esc_attr($settings['canvas_background_color'] ?? '#ffffff'); ?>" />
                        <p class="description">Couleur de fond du canvas</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="container_background_color">Couleur Fond Conteneur</label></th>
                    <td>
                        <input type="color" id="container_background_color" name="container_background_color" 
                               value="<?php echo esc_attr($settings['container_background_color'] ?? '#f8f9fa'); ?>" />
                        <p class="description">Couleur de fond autour du canvas</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Marges</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_margins">Afficher les Marges</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_margins" name="show_margins" value="1" 
                                       <?php checked($settings['show_margins'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Marges visibles</span>
                        </div>
                        <div class="toggle-description">Affiche les lignes de marge sur le canvas</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Marges (mm)</label></th>
                    <td>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                            <div>
                                <label for="margin_top">Haut :</label>
                                <input type="number" id="margin_top" name="margin_top" 
                                       value="<?php echo intval($settings['margin_top'] ?? 28); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_right">Droite :</label>
                                <input type="number" id="margin_right" name="margin_right" 
                                       value="<?php echo intval($settings['margin_right'] ?? 28); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_bottom">Bas :</label>
                                <input type="number" id="margin_bottom" name="margin_bottom" 
                                       value="<?php echo intval($settings['margin_bottom'] ?? 28); ?>" min="0" />
                            </div>
                            <div>
                                <label for="margin_left">Gauche :</label>
                                <input type="number" id="margin_left" name="margin_left" 
                                       value="<?php echo intval($settings['margin_left'] ?? 10); ?>" min="0" />
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Grille & Aimants</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_grid">Afficher Grille</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_grid" name="show_grid" value="1" 
                                       <?php checked($settings['show_grid'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Grille visible</span>
                        </div>
                        <div class="toggle-description">Affiche une grille de référence</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="grid_size">Taille Grille (px)</label></th>
                    <td>
                        <input type="number" id="grid_size" name="grid_size" 
                               value="<?php echo intval($settings['grid_size'] ?? 10); ?>" min="5" max="100" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="grid_color">Couleur Grille</label></th>
                    <td>
                        <input type="color" id="grid_color" name="grid_color" 
                               value="<?php echo esc_attr($settings['grid_color'] ?? '#e0e0e0'); ?>" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="snap_to_grid">Magnétisme Grille</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="snap_to_grid" name="snap_to_grid" value="1" 
                                       <?php checked($settings['snap_to_grid'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer magnétisme</span>
                        </div>
                        <div class="toggle-description">Les éléments s'accrochent à la grille</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="snap_to_elements">Magnétisme Éléments</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="snap_to_elements" name="snap_to_elements" value="1" 
                                       <?php checked($settings['snap_to_elements'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer magnétisme</span>
                        </div>
                        <div class="toggle-description">Les éléments s'accrochent les uns aux autres</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="snap_tolerance">Tolérance Aimantation (px)</label></th>
                    <td>
                        <input type="number" id="snap_tolerance" name="snap_tolerance" 
                               value="<?php echo intval($settings['snap_tolerance'] ?? 5); ?>" min="1" max="50" />
                        <p class="description">Distance avant accrochage magnétique</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="show_guides">Afficher Guides</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_guides" name="show_guides" value="1" 
                                       <?php checked($settings['show_guides'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Guides visibles</span>
                        </div>
                        <div class="toggle-description">Affiche les guides de positionnement</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Zoom & Navigation</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="default_zoom">Zoom par Défaut (%)</label></th>
                    <td>
                        <input type="number" id="default_zoom" name="default_zoom" 
                               value="<?php echo intval($settings['default_zoom'] ?? 100); ?>" min="10" max="500" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="zoom_step">Pas du Zoom (%)</label></th>
                    <td>
                        <input type="number" id="zoom_step" name="zoom_step" 
                               value="<?php echo intval($settings['zoom_step'] ?? 25); ?>" min="5" max="100" />
                        <p class="description">Incrément lors du zoom avant/arrière</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="min_zoom">Zoom Minimum (%)</label></th>
                    <td>
                        <input type="number" id="min_zoom" name="min_zoom" 
                               value="<?php echo intval($settings['min_zoom'] ?? 10); ?>" min="1" max="100" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_zoom">Zoom Maximum (%)</label></th>
                    <td>
                        <input type="number" id="max_zoom" name="max_zoom" 
                               value="<?php echo intval($settings['max_zoom'] ?? 500); ?>" min="100" max="2000" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="zoom_with_wheel">Zoom à la Molette</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="zoom_with_wheel" name="zoom_with_wheel" value="1" 
                                       <?php checked($settings['zoom_with_wheel'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Zoom molette</span>
                        </div>
                        <div class="toggle-description">Permet de zoomer avec la molette souris</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="pan_with_mouse">Panoramique à la Souris</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="pan_with_mouse" name="pan_with_mouse" value="1" 
                                       <?php checked($settings['pan_with_mouse'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Panoramique souris</span>
                        </div>
                        <div class="toggle-description">Permet de déplacer le canvas en glissant</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Sélection & Manipulation</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="show_resize_handles">Afficher Poignées</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="show_resize_handles" name="show_resize_handles" value="1" 
                                       <?php checked($settings['show_resize_handles'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Poignées visibles</span>
                        </div>
                        <div class="toggle-description">Affiche les poignées de redimensionnement</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="handle_size">Taille Poignée (px)</label></th>
                    <td>
                        <input type="number" id="handle_size" name="handle_size" 
                               value="<?php echo intval($settings['handle_size'] ?? 8); ?>" min="4" max="20" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="enable_rotation">Rotation d'Éléments</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="enable_rotation" name="enable_rotation" value="1" 
                                       <?php checked($settings['enable_rotation'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Rotation activée</span>
                        </div>
                        <div class="toggle-description">Permet la rotation des éléments</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="rotation_step">Pas Rotation (degrés)</label></th>
                    <td>
                        <input type="number" id="rotation_step" name="rotation_step" 
                               value="<?php echo intval($settings['rotation_step'] ?? 15); ?>" min="1" max="90" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="multi_select">Sélection Multiple</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="multi_select" name="multi_select" value="1" 
                                       <?php checked($settings['multi_select'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Multi-sélection</span>
                        </div>
                        <div class="toggle-description">Permet de sélectionner plusieurs éléments</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="copy_paste_enabled">Copier/Coller</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="copy_paste_enabled" name="copy_paste_enabled" value="1" 
                                       <?php checked($settings['copy_paste_enabled'] ?? false); ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Copier/coller</span>
                        </div>
                        <div class="toggle-description">Active les raccourcis Ctrl+C / Ctrl+V</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Undo/Redo & Auto-save</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="undo_levels">Niveaux Undo</label></th>
                    <td>
                        <input type="number" id="undo_levels" name="undo_levels" 
                               value="<?php echo intval($settings['undo_levels'] ?? 50); ?>" min="1" max="500" />
                        <p class="description">Nombre d'actions à mémoriser pour annuler</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="redo_levels">Niveaux Redo</label></th>
                    <td>
                        <input type="number" id="redo_levels" name="redo_levels" 
                               value="<?php echo intval($settings['redo_levels'] ?? 50); ?>" min="1" max="500" />
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="auto_save_versions">Versions Auto-save</label></th>
                    <td>
                        <input type="number" id="auto_save_versions" name="auto_save_versions" 
                               value="<?php echo intval($settings['auto_save_versions'] ?? 10); ?>" min="1" max="100" />
                        <p class="description">Nombre de versions à conserver</p>
                    </td>
                </tr>
            </table>
            
            <!-- Conseils Canvas -->
            <div style="background: #f8f9fa; border-left: 4px solid #666; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0;">💡 Conseils Canvas</h3>
                <ul style="margin: 0; padding-left: 20px;">
                    <li><strong>Performance :</strong> Réduisez la taille grille et les niveaux undo sur machines lentes</li>
                    <li><strong>Précision :</strong> Activez le magnétisme pour alignement automatique</li>
                    <li><strong>Navigation :</strong> Activez zoom molette et panoramique pour meilleure ergonomie</li>
                    <li><strong>Sécurité :</strong> Les versions auto-save permettent de récupérer en cas de crash</li>
                </ul>
            </div>
            
            <p class="submit">
                <button type="submit" name="submit_canvas" class="button button-primary">Enregistrer les paramètres Canvas</button>
            </p>
        </div>
        
        <div id="templates" class="tab-content" style="display: none;">
            <h2>Assignation des Templates</h2>
            
            <p style="margin-bottom: 20px;">Assignez automatiquement des templates aux différents statuts de commande WooCommerce.</p>
            
            <?php
            // Traitement de la sauvegarde
            if (isset($_POST['submit_templates']) && isset($_POST['pdf_builder_templates_nonce'])) {
                if (wp_verify_nonce($_POST['pdf_builder_templates_nonce'], 'pdf_builder_templates')) {
                    $template_mappings = [];
                    if (isset($_POST['order_status_templates']) && is_array($_POST['order_status_templates'])) {
                        foreach ($_POST['order_status_templates'] as $status => $template_id) {
                            $template_id = intval($template_id);
                            if ($template_id > 0) {
                                $template_mappings[sanitize_text_field($status)] = $template_id;
                            }
                        }
                    }
                    update_option('pdf_builder_order_status_templates', $template_mappings);
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Templates assignés avec succès.</p></div>';
                }
            }
            
            // Récupérer les statuts de commande WooCommerce
            $order_statuses = [];
            
            if (function_exists('wc_get_order_statuses')) {
                $order_statuses = wc_get_order_statuses();
            } else {
                // Fallback : statuts standards
                $order_statuses = [
                    'wc-pending' => 'En attente',
                    'wc-processing' => 'En cours',
                    'wc-on-hold' => 'En attente de paiement',
                    'wc-completed' => 'Terminée',
                    'wc-cancelled' => 'Annulée',
                    'wc-refunded' => 'Remboursée',
                    'wc-failed' => 'Échec du paiement'
                ];
            }
            
            // Récupérer les mappings actuels
            $current_mappings = get_option('pdf_builder_order_status_templates', []);
            
            // Récupérer les templates disponibles
            $templates = get_posts([
                'post_type' => 'pdf_template',
                'posts_per_page' => -1,
                'orderby' => 'title',
                'order' => 'ASC'
            ]);
            ?>
            
            <form method="post">
                <?php wp_nonce_field('pdf_builder_templates', 'pdf_builder_templates_nonce'); ?>
                
                <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Mappage des Statuts aux Templates</h3>
                
                <table class="form-table">
                    <?php foreach ($order_statuses as $status_key => $status_name):
                        $display_status = str_replace('wc-', '', $status_key);
                        $selected_template = isset($current_mappings[$status_key]) ? $current_mappings[$status_key] : '';
                    ?>
                        <tr>
                            <th scope="row">
                                <label for="template_<?php echo esc_attr($display_status); ?>">
                                    <strong><?php echo esc_html($status_name); ?></strong><br>
                                    <code style="color: #666;"><?php echo esc_html($display_status); ?></code>
                                </label>
                            </th>
                            <td>
                                <select name="order_status_templates[<?php echo esc_attr($status_key); ?>]" 
                                        id="template_<?php echo esc_attr($display_status); ?>" class="regular-text">
                                    <option value="">-- Utiliser le template par défaut --</option>
                                    <?php foreach ($templates as $template): ?>
                                        <option value="<?php echo intval($template->ID); ?>" 
                                                <?php selected($selected_template, $template->ID); ?>>
                                            <?php echo esc_html($template->post_title ?: '(Sans titre)'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">
                                    Template automatique pour les commandes avec ce statut
                                </p>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                
                <p class="submit">
                    <button type="submit" name="submit_templates" class="button button-primary">
                        Sauvegarder les Assignations
                    </button>
                </p>
            </form>
            
            <!-- Info WooCommerce -->
            <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #003d66;">📦 Intégration WooCommerce</h3>
                <ul style="margin: 0; padding-left: 20px; color: #003d66;">
                    <li><strong>Statuts disponibles :</strong> <?php echo count($order_statuses); ?> statuts détectés</li>
                    <li><strong>Templates disponibles :</strong> <?php echo count($templates); ?> templates</li>
                    <li>Chaque statut de commande peut avoir son propre template</li>
                    <li>Les commandes utiliseront automatiquement le template assigné à leur statut</li>
                    <li>Laissez vide pour utiliser le template par défaut</li>
                </ul>
            </div>
            
            <!-- Tableau récapitulatif -->
            <div style="margin-top: 30px;">
                <h3>📋 Vue d'ensemble des Assignations</h3>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th style="width: 30%;">Statut</th>
                            <th style="width: 50%;">Template Assigné</th>
                            <th style="width: 20%; text-align: center;">Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_statuses as $status_key => $status_name):
                            $template_id = isset($current_mappings[$status_key]) ? $current_mappings[$status_key] : false;
                            $template_name = $template_id ? get_the_title($template_id) : '(Défaut)';
                            $template_type = $template_id ? 'Personnalisé' : 'Défaut';
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($status_name); ?></strong></td>
                                <td><?php echo esc_html($template_name); ?></td>
                                <td style="text-align: center;">
                                    <span style="display: inline-block; padding: 3px 10px; border-radius: 3px; background: <?php echo $template_id ? '#d4edda' : '#e9ecef'; ?>; color: <?php echo $template_id ? '#155724' : '#666'; ?>;">
                                        <?php echo $template_type; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Conseils d'utilisation -->
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #856404;">💡 Conseils d'Utilisation</h3>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li><strong>Factures :</strong> Assignez un template "Facture" au statut "Terminée"</li>
                    <li><strong>Confirmations :</strong> Utilisez un template "Confirmation" pour le statut "En attente"</li>
                    <li><strong>Avis d'expédition :</strong> Assignez au statut "En cours"</li>
                    <li><strong>Avoirs :</strong> Créez un template "Avoir" pour les remboursements</li>
                    <li>Les templates peuvent inclure des variables dynamiques (numéro de commande, client, articles, etc.)</li>
                </ul>
            </div>
        </div>
        
        <div id="maintenance" class="tab-content" style="display: none;">
            <h2>Actions de Maintenance</h2>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🧹 Nettoyage des Données</h3>
            <p>Supprimez les données temporaires et les fichiers obsolètes pour optimiser les performances.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <form method="post" style="display: inline;">
                    <?php wp_nonce_field('pdf_builder_clear_cache_maintenance', 'pdf_builder_clear_cache_nonce_maintenance'); ?>
                    <button type="submit" name="clear_cache" class="button button-secondary" style="width: 100%;">
                        🗑️ Vider le Cache
                    </button>
                </form>
                
                <button type="button" class="button button-secondary" onclick="alert('Suppression de fichiers temporaires...');" style="width: 100%;">
                    📁 Supprimer Fichiers Temp
                </button>
                
                <button type="button" class="button button-secondary" onclick="alert('Optimisation base de données...');" style="width: 100%;">
                    ⚡ Optimiser BD
                </button>
            </div>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🔧 Réparation & Réinitialisation</h3>
            <p>Réparez les templates corrompus et les paramètres invalides.</p>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-bottom: 20px;">
                <button type="button" class="button button-secondary" onclick="alert('Réparation des templates en cours...');" style="width: 100%;">
                    ✅ Réparer Templates
                </button>
                
                <button type="button" class="button button-warning" 
                        onclick="if(confirm('Réinitialiser tous les paramètres ? Cette action est irréversible.')) { alert('Réinitialisation...'); }" 
                        style="width: 100%;">
                    ⚠️ Réinitialiser Paramètres
                </button>
                
                <button type="button" class="button button-secondary" onclick="alert('Validation de l\'intégrité en cours...');" style="width: 100%;">
                    🔍 Vérifier Intégrité
                </button>
            </div>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🐛 Outils de Développement</h3>
            <p>Outils pour les développeurs et le débogage avancé.</p>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label>Console de Debug</label></th>
                    <td>
                        <button type="button" class="button button-secondary" onclick="alert('Ouverture de la console...');">
                            🖥️ Ouvrir Console
                        </button>
                        <p class="description">Affiche les logs JavaScript avec emojis (🚀, ✅, ❌, ⚠️)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label>Logs Debug</label></th>
                    <td>
                        <button type="button" class="button button-secondary" onclick="alert('Vider les logs debug...');">
                            🗑️ Vider Logs
                        </button>
                        <p class="description">Supprime tous les logs de débogation accumulés</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📊 Informations Système</h3>
            <table class="form-table">
                <tr>
                    <th scope="row">Version du Plugin</th>
                    <td>
                        <code><?php echo defined('PDF_BUILDER_VERSION') ? PDF_BUILDER_VERSION : '1.0.0'; ?></code>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Statut WordPress</th>
                    <td>
                        <span style="color: #46b450;">✓ WordPress <?php echo get_bloginfo('version'); ?></span>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Mémoire Disponible</th>
                    <td>
                        <?php
                        $memory_limit = ini_get('memory_limit');
                        $color = (intval($memory_limit) >= 256) ? '#46b450' : '#ffb900';
                        ?>
                        <span style="color: <?php echo $color; ?>;"><?php echo esc_html($memory_limit); ?></span>
                        <p class="description">Minimum recommandé: 256MB</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Temps Max Exécution</th>
                    <td>
                        <?php
                        $max_exec = ini_get('max_execution_time');
                        $color = ($max_exec >= 300) ? '#46b450' : '#ffb900';
                        ?>
                        <span style="color: <?php echo $color; ?>;"><?php echo esc_html($max_exec); ?>s</span>
                        <p class="description">Minimum recommandé: 300s</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">WooCommerce</th>
                    <td>
                        <?php
                        if (class_exists('WooCommerce')) {
                            echo '<span style="color: #46b450;">✓ Installé</span>';
                        } else {
                            echo '<span style="color: #666;">○ Non détecté</span>';
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Nombre de Templates</th>
                    <td>
                        <?php
                        $template_count = count(get_posts([
                            'post_type' => 'pdf_template',
                            'posts_per_page' => -1
                        ]));
                        echo intval($template_count);
                        ?>
                    </td>
                </tr>
            </table>
            
            <!-- Section Logs & Diagnostics -->
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📋 Logs & Diagnostics</h3>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 25%;">Type</th>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 25%; text-align: center;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Erreurs PHP</strong></td>
                        <td>Errors et Warnings PHP du plugin</td>
                        <td style="text-align: center;">
                            <button type="button" class="button button-small" onclick="alert('Affichage des logs...');">Voir</button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Génération PDF</strong></td>
                        <td>Logs des opérations de génération PDF</td>
                        <td style="text-align: center;">
                            <button type="button" class="button button-small" onclick="alert('Affichage des logs...');">Voir</button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Événements</strong></td>
                        <td>Événements système importants</td>
                        <td style="text-align: center;">
                            <button type="button" class="button button-small" onclick="alert('Affichage des logs...');">Voir</button>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Détails Requis</strong></td>
                        <td>Toutes les requêtes traitées</td>
                        <td style="text-align: center;">
                            <button type="button" class="button button-small" onclick="alert('Affichage des logs...');">Voir</button>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Avertissements de maintenance -->
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #856404;">⚠️ Avant la Maintenance</h3>
                <ul style="margin: 0; padding-left: 20px; color: #856404;">
                    <li>✓ Faites toujours une <strong>sauvegarde</strong> avant les opérations de maintenance</li>
                    <li>✓ Testez en mode de débogage d'abord</li>
                    <li>✓ Vérifiez les logs après l'opération</li>
                    <li>✓ N'utilisez pas "Réinitialiser" sans raison importante</li>
                </ul>
            </div>
            
            <!-- Conseils performance -->
            <div style="background: #e7f3ff; border-left: 4px solid #2271b1; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #003d66;">💡 Conseils Performance</h3>
                <ul style="margin: 0; padding-left: 20px; color: #003d66;">
                    <li>Videz régulièrement le cache (hebdomadaire en production)</li>
                    <li>Supprimez les fichiers temporaires tous les mois</li>
                    <li>Vérifiez l'intégrité du système mensuellement</li>
                    <li>Consultez les logs en cas de problème</li>
                    <li>Maintenez WordPress à jour</li>
                </ul>
            </div>
        </div>
        
        <div id="developpeur" class="tab-content" style="display: none;">
            <h2>Paramètres Développeur</h2>
            <p style="color: #666;">⚠️ Cette section est réservée aux développeurs. Les modifications ici peuvent affecter le fonctionnement du plugin.</p>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🔐 Contrôle d'Accès</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="developer_enabled">Mode Développeur</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="developer_enabled" name="developer_enabled" value="1" <?php echo isset($settings['developer_enabled']) && $settings['developer_enabled'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Activer le mode développeur</span>
                        </div>
                        <div class="toggle-description">Active le mode développeur avec logs détaillés</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="developer_password">Mot de Passe Dev</label></th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="password" id="developer_password" name="developer_password" placeholder="Laisser vide pour aucun mot de passe" style="width: 250px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
                            <button type="button" id="toggle_password" class="button button-secondary" style="padding: 8px 12px; height: auto;">
                                👁️ Afficher
                            </button>
                        </div>
                        <p class="description">Protège les outils développeur avec un mot de passe (optionnel)</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🔍 Paramètres de Debug</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="debug_php_errors">Errors PHP</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_php_errors" name="debug_php_errors" value="1" <?php echo isset($settings['debug_php_errors']) && $settings['debug_php_errors'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug PHP</span>
                        </div>
                        <div class="toggle-description">Affiche les erreurs/warnings PHP du plugin</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_javascript">Debug JavaScript</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_javascript" name="debug_javascript" value="1" <?php echo isset($settings['debug_javascript']) && $settings['debug_javascript'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug JS</span>
                        </div>
                        <div class="toggle-description">Active les logs détaillés en console (emojis: 🚀 start, ✅ success, ❌ error, ⚠️ warn)</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_ajax">Debug AJAX</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_ajax" name="debug_ajax" value="1" <?php echo isset($settings['debug_ajax']) && $settings['debug_ajax'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug AJAX</span>
                        </div>
                        <div class="toggle-description">Enregistre toutes les requêtes AJAX avec requête/réponse</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_performance">Métriques Performance</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_performance" name="debug_performance" value="1" <?php echo isset($settings['debug_performance']) && $settings['debug_performance'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug perf.</span>
                        </div>
                        <div class="toggle-description">Affiche le temps d'exécution et l'utilisation mémoire des opérations</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_database">Requêtes BD</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="debug_database" name="debug_database" value="1" <?php echo isset($settings['debug_database']) && $settings['debug_database'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Debug DB</span>
                        </div>
                        <div class="toggle-description">Enregistre les requêtes SQL exécutées par le plugin</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">📝 Fichiers Logs</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="log_level">Niveau de Log</label></th>
                    <td>
                        <select id="log_level" name="log_level" style="width: 200px;">
                            <option value="0" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 0) ? 'selected' : ''; ?>>Aucun log</option>
                            <option value="1" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 1) ? 'selected' : ''; ?>>Erreurs uniquement</option>
                            <option value="2" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 2) ? 'selected' : ''; ?>>Erreurs + Avertissements</option>
                            <option value="3" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 3) ? 'selected' : ''; ?>>Info complète</option>
                            <option value="4" <?php echo (isset($settings['log_level']) && $settings['log_level'] == 4) ? 'selected' : ''; ?>>Détails (Développement)</option>
                        </select>
                        <p class="description">0=Aucun, 1=Erreurs, 2=Warn, 3=Info, 4=Détails</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="log_file_size">Taille Max Log</label></th>
                    <td>
                        <input type="number" id="log_file_size" name="log_file_size" value="<?php echo isset($settings['log_file_size']) ? intval($settings['log_file_size']) : '10'; ?>" min="1" max="100" /> MB
                        <p class="description">Rotation automatique quand le log dépasse cette taille</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="log_retention">Retention Logs</label></th>
                    <td>
                        <input type="number" id="log_retention" name="log_retention" value="<?php echo isset($settings['log_retention']) ? intval($settings['log_retention']) : '30'; ?>" min="1" max="365" /> jours
                        <p class="description">Supprime automatiquement les logs plus vieux que ce délai</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🚀 Optimisations Avancées</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="disable_hooks">Désactiver Hooks</label></th>
                    <td>
                        <input type="text" id="disable_hooks" name="disable_hooks" placeholder="hook1,hook2,hook3" style="width: 100%; max-width: 400px;" />
                        <p class="description">Hooks WordPress à désactiver (séparés par virgule). Utile pour déboguer les conflits</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="enable_profiling">Profiling PHP</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="enable_profiling" name="enable_profiling" value="1" <?php echo isset($settings['enable_profiling']) && $settings['enable_profiling'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">Profiling actif</span>
                        </div>
                        <div class="toggle-description">Active le profiling PHP (impact sur les performances). Générer des rapports xdebug</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="force_https">Forcer HTTPS API</label></th>
                    <td>
                        <div class="toggle-container">
                            <label class="toggle-switch">
                                <input type="checkbox" id="force_https" name="force_https" value="1" <?php echo isset($settings['force_https']) && $settings['force_https'] ? 'checked' : ''; ?> />
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label">HTTPS forcé</span>
                        </div>
                        <div class="toggle-description">Force les appels API externes en HTTPS (sécurité renforcée)</div>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🧪 Outils de Développement</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <button type="button" class="button button-secondary" onclick="alert('Rechargement du code en cache...');">
                    🔄 Recharger Cache
                </button>
                <button type="button" class="button button-secondary" onclick="alert('Vidage des données temporaires...');">
                    🗑️ Vider Temp
                </button>
                <button type="button" class="button button-secondary" onclick="alert('Vérification des routes API...');">
                    🛣️ Tester Routes
                </button>
                <button type="button" class="button button-secondary" onclick="alert('Extraction de diagnostic...');">
                    💾 Exporter Diagnostic
                </button>
            </div>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🎨 Console Code</h3>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="test_code">Code Test</label></th>
                    <td>
                        <textarea id="test_code" style="width: 100%; height: 150px; font-family: monospace; padding: 10px;">// Exemple: var result = pdf_builder.checkHealth();</textarea>
                        <p class="description">Zone d'essai pour du code PHP (exécution en contexte du plugin)</p>
                        <button type="button" class="button button-secondary" style="margin-top: 10px;" onclick="alert('Code exécuté. Voir les logs pour résultat.');">▶️ Exécuter Code</button>
                    </td>
                </tr>
            </table>
            
            <!-- Tableau de références des hooks disponibles -->
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">🎣 Hooks Disponibles</h3>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 25%;">Hook</th>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 25%;">Typage</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>pdf_builder_before_generate</code></td>
                        <td>Avant la génération PDF</td>
                        <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_after_generate</code></td>
                        <td>Après la génération PDF réussie</td>
                        <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_template_data</code></td>
                        <td>Filtre les données de template</td>
                        <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_element_render</code></td>
                        <td>Rendu d'un élément du canvas</td>
                        <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_security_check</code></td>
                        <td>Vérifications de sécurité personnalisées</td>
                        <td><span style="background: #e8f5e9; padding: 2px 6px; border-radius: 3px;">filter</span></td>
                    </tr>
                    <tr>
                        <td><code>pdf_builder_before_save</code></td>
                        <td>Avant sauvegarde des paramètres</td>
                        <td><span style="background: #f0f0f0; padding: 2px 6px; border-radius: 3px;">action</span></td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Avertissement production -->
            <div style="background: #ffebee; border-left: 4px solid #d32f2f; border-radius: 4px; padding: 20px; margin-top: 30px;">
                <h3 style="margin-top: 0; color: #c62828;">🚨 Avertissement Production</h3>
                <ul style="margin: 0; padding-left: 20px; color: #c62828;">
                    <li>❌ Ne jamais laisser le mode développeur ACTIVÉ en production</li>
                    <li>❌ Ne jamais afficher les logs détaillés aux utilisateurs</li>
                    <li>❌ Désactivez le profiling et les hooks de debug après débogage</li>
                    <li>❌ N'exécutez pas de code arbitraire en production</li>
                    <li>✓ Utilisez des mots de passe forts pour protéger les outils dev</li>
                </ul>
            </div>
            
            <!-- Conseils développement -->
            <div style="background: #f3e5f5; border-left: 4px solid #7b1fa2; border-radius: 4px; padding: 20px; margin-top: 20px;">
                <h3 style="margin-top: 0; color: #4a148c;">💻 Conseils Développement</h3>
                <ul style="margin: 0; padding-left: 20px; color: #4a148c;">
                    <li>Activez Debug JavaScript pour déboguer les interactions client</li>
                    <li>Utilisez Debug AJAX pour vérifier les requêtes serveur</li>
                    <li>Consultez Debug Performance pour optimiser les opérations lentes</li>
                    <li>Lisez les logs détaillés (niveau 4) pour comprendre le flux</li>
                    <li>Testez avec les différents niveaux de log</li>
                </ul>
            </div>
            
            <p class="submit">
                <button type="submit" name="submit_developpeur" class="button button-primary">Enregistrer les paramètres développeur</button>
            </p>
        </div>
    </form>

<style>
    .toggle-switch {
        position: relative;
        display: inline-block;
        width: 50px;
        height: 24px;
    }

    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .toggle-slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        transition: .4s;
        border-radius: 24px;
    }

    .toggle-slider:before {
        position: absolute;
        content: "";
        height: 18px;
        width: 18px;
        left: 3px;
        bottom: 3px;
        background-color: white;
        transition: .4s;
        border-radius: 50%;
    }

    input:checked + .toggle-slider {
        background-color: #2196F3;
    }

    input:checked + .toggle-slider:before {
        transform: translateX(26px);
    }

    .toggle-container {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .toggle-label {
        font-weight: 500;
        color: #333;
    }

    .toggle-description {
        font-size: 12px;
        color: #666;
        margin: 0;
        padding-left: 60px;
    }

    .toggle-switch input:disabled ~ .toggle-slider {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .toggle-switch input:disabled ~ .toggle-label {
        opacity: 0.5;
        cursor: not-allowed;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        const tabs = document.querySelectorAll('.nav-tab');
        const contents = document.querySelectorAll('.tab-content');

        // Fonction pour changer d'onglet
        function switchTab(targetId, clickedTab) {
            // Masquer tous les contenus
            contents.forEach(function(content) {
                content.style.display = 'none';
                content.setAttribute('aria-hidden', 'true');
            });
            
            // Désactiver tous les onglets
            tabs.forEach(function(tab) {
                tab.classList.remove('nav-tab-active');
                tab.setAttribute('aria-selected', 'false');
            });
            
            // Afficher l'onglet cible
            const targetContent = document.getElementById(targetId);
            if (targetContent) {
                targetContent.style.display = 'block';
                targetContent.setAttribute('aria-hidden', 'false');
            }
            
            // Activer l'onglet cliqué
            clickedTab.classList.add('nav-tab-active');
            clickedTab.setAttribute('aria-selected', 'true');
            
            // Mettre à jour l'URL hash
            if (history.pushState) {
                history.pushState(null, null, '#' + targetId);
            } else {
                window.location.hash = '#' + targetId;
            }
        }
        
        // Gestionnaire d'événement pour les onglets
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-tab');
                switchTab(targetId, this);
            });
        });
        
        // Gestion du hash dans l'URL au chargement
        const hash = window.location.hash.substring(1);
        if (hash) {
            const targetTab = document.querySelector('.nav-tab[data-tab="' + hash + '"]');
            if (targetTab) {
                switchTab(hash, targetTab);
            }
        }
        
        // Gestion des boutons de soumission par onglet
        const submitButtons = document.querySelectorAll('button[name="submit"]');
        submitButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                // Trouver l'onglet actif
                const activeTab = document.querySelector('.nav-tab-active');
                if (activeTab) {
                    const tabId = activeTab.getAttribute('data-tab');
                    console.log('Submitting form for tab:', tabId);
                }
            });
        });
        
        // Toggle switches
        const toggleSwitches = document.querySelectorAll('.toggle-switch input[type="checkbox"]');
        toggleSwitches.forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const label = this.parentElement.nextElementSibling;
                if (label && label.classList.contains('toggle-label')) {
                    if (this.checked) {
                        label.style.fontWeight = 'bold';
                        label.style.color = '#2196F3';
                    } else {
                        label.style.fontWeight = 'normal';
                        label.style.color = '#333';
                    }
                }
            });
            
            // Initial state
            const label = toggle.parentElement.nextElementSibling;
            if (label && label.classList.contains('toggle-label')) {
                if (toggle.checked) {
                    label.style.fontWeight = 'bold';
                    label.style.color = '#2196F3';
                }
            }
        });
        
        // Range sliders with value display
        const rangeInputs = document.querySelectorAll('input[type="range"]');
        rangeInputs.forEach(function(range) {
            const valueDisplay = document.getElementById(range.id + '_value');
            if (valueDisplay) {
                range.addEventListener('input', function() {
                    valueDisplay.textContent = this.value + '%';
                });
            }
        });
        
        // Test notifications button
        const testNotificationsBtn = document.getElementById('test-notifications');
        if (testNotificationsBtn) {
            testNotificationsBtn.addEventListener('click', function() {
                this.disabled = true;
                this.textContent = '🧪 Test en cours...';
                
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        action: 'test_notifications',
                        nonce: document.querySelector('#pdf_builder_notifications_nonce')?.value || ''
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Test des notifications réussi ! Vérifiez vos emails.');
                    } else {
                        alert('❌ Erreur lors du test : ' + (data.data || 'Erreur inconnue'));
                    }
                })
                .catch(error => {
                    alert('❌ Erreur réseau : ' + error.message);
                })
                .finally(() => {
                    // Réactiver le bouton
                    this.disabled = false;
                    this.textContent = '🧪 Tester les Notifications';
                });
            });
        }
    });
</script>
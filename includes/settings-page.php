<?php
/**
 * Page des Paramètres - PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Vérifier les permissions - permettre à tous les utilisateurs connectés
if (!defined('PDF_BUILDER_DEBUG_MODE') || !PDF_BUILDER_DEBUG_MODE) {
    if (!is_user_logged_in() || !current_user_can('read')) {
        wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
    }
}

// Utiliser l'instance globale si elle existe, sinon créer une nouvelle
global $pdf_builder_core;
if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
    $core = $pdf_builder_core;
} else {
    $core = PDF_Builder_Core::getInstance();
    // Temporaire : supprimer la vérification d'initialisation qui ne fonctionne pas
    // if (!$core->is_initialized()) {
    //     $core->init();
    // }
}

$config = null; // Temporaire : pas de config manager pour l'instant

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
}

$config = new TempConfig();

// Sauvegarde des paramètres si formulaire soumis
if (isset($_POST['pdf_builder_settings_nonce']) && wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
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
        'canvas_border_width' => floatval($_POST['canvas_border_width']),
        'canvas_border_color' => sanitize_text_field($_POST['canvas_border_color']),
        'canvas_border_spacing' => intval($_POST['canvas_border_spacing']),
        'canvas_resize_handles_enabled' => isset($_POST['canvas_resize_handles_enabled']),
        'canvas_handle_size' => intval($_POST['canvas_handle_size']),
        'canvas_handle_color' => sanitize_text_field($_POST['canvas_handle_color']),
        'canvas_handle_hover_color' => sanitize_text_field($_POST['canvas_handle_hover_color'])
    ];

    $config->set_multiple($settings);

    // Sauvegarde individuelle des paramètres canvas pour la compatibilité avec le JavaScript
    update_option('canvas_element_borders_enabled', $settings['canvas_element_borders_enabled']);
    update_option('canvas_border_width', $settings['canvas_border_width']);
    update_option('canvas_border_color', $settings['canvas_border_color']);
    update_option('canvas_border_spacing', $settings['canvas_border_spacing']);
    update_option('canvas_resize_handles_enabled', $settings['canvas_resize_handles_enabled']);
    update_option('canvas_handle_size', $settings['canvas_handle_size']);
    update_option('canvas_handle_color', $settings['canvas_handle_color']);
    update_option('canvas_handle_hover_color', $settings['canvas_handle_hover_color']);

    // Sauvegarde des autres paramètres importants
    update_option('debug_mode', $settings['debug_mode']);
    update_option('cache_enabled', $settings['cache_enabled']);
    update_option('cache_ttl', $settings['cache_ttl']);
    update_option('max_execution_time', $settings['max_execution_time']);
    update_option('memory_limit', $settings['memory_limit']);
    update_option('pdf_quality', $settings['pdf_quality']);
    update_option('default_format', $settings['default_format']);
    update_option('default_orientation', $settings['default_orientation']);
    update_option('log_level', $settings['log_level']);
    update_option('max_template_size', $settings['max_template_size']);
    update_option('email_notifications_enabled', $settings['email_notifications_enabled']);
    update_option('notification_events', $settings['notification_events']);

    // Sauvegarde des permissions des rôles
    if (isset($_POST['role_permissions']) && is_array($_POST['role_permissions'])) {
        global $wp_roles;
        $roles = $wp_roles->roles;

        $pdf_permissions = [
            'manage_pdf_templates',
            'create_pdf_templates',
            'edit_pdf_templates',
            'delete_pdf_templates',
            'view_pdf_templates',
            'export_pdf_templates',
            'import_pdf_templates',
            'manage_pdf_settings'
        ];

        foreach ($_POST['role_permissions'] as $role_key => $permissions) {
            $role_obj = get_role($role_key);
            if ($role_obj) {
                // Supprimer toutes les permissions PDF existantes
                foreach ($pdf_permissions as $perm) {
                    $role_obj->remove_cap($perm);
                }

                // Ajouter les permissions cochées
                foreach ($permissions as $perm => $value) {
                    if (in_array($perm, $pdf_permissions) && $value == '1') {
                        $role_obj->add_cap($perm);
                    }
                }
            }
        }
    }

    echo '<div class="notice notice-success"><p>' . __('Paramètres sauvegardés avec succès.', 'pdf-builder-pro') . '</p></div>';
}
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

<div class="wrap">
    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <form method="post" action="">
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
                <a href="#maintenance" class="nav-tab"><?php _e('Maintenance', 'pdf-builder-pro'); ?></a>
            </div>

            <!-- Onglet Général -->
            <div id="general" class="tab-content active">
                <h2><?php _e('Paramètres Généraux', 'pdf-builder-pro'); ?></h2>

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
                </table>
            </div>

            <!-- Onglet Licence -->
            <div id="license" class="tab-content">
                <h2><?php _e('Gestion de la Licence', 'pdf-builder-pro'); ?></h2>

                <?php
                // Charger les classes de licence si elles existent
                if (file_exists(plugin_dir_path(__FILE__) . 'classes/PDF_Builder_License_Manager.php')) {
                    require_once plugin_dir_path(__FILE__) . 'classes/PDF_Builder_License_Manager.php';
                    require_once plugin_dir_path(__FILE__) . 'classes/PDF_Builder_Feature_Manager.php';

                    $license_manager = PDF_Builder_License_Manager::getInstance();
                    $feature_manager = new PDF_Builder_Feature_Manager();
                    $license_info = $license_manager->get_license_info();
                    $is_premium = $license_manager->is_premium();

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
                                    <input type="text" name="license_key" id="license_key" class="regular-text" placeholder="XXXX-XXXX-XXXX-XXXX" required style="min-width: 300px;">
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
                    <p><?php _e('Gérez les rôles et permissions pour l\'accès aux fonctionnalités PDF Builder Pro.', 'pdf-builder-pro'); ?></p>

                    <div class="roles-section">
                        <h3><?php _e('Permissions par Rôle', 'pdf-builder-pro'); ?></h3>

                        <?php
                        // Récupérer tous les rôles WordPress
                        global $wp_roles;
                        $roles = $wp_roles->roles;

                        // Permissions disponibles pour PDF Builder
                        $pdf_permissions = [
                            'manage_pdf_templates' => __('Gérer les templates PDF', 'pdf-builder-pro'),
                            'create_pdf_templates' => __('Créer des templates PDF', 'pdf-builder-pro'),
                            'edit_pdf_templates' => __('Modifier les templates PDF', 'pdf-builder-pro'),
                            'delete_pdf_templates' => __('Supprimer les templates PDF', 'pdf-builder-pro'),
                            'view_pdf_templates' => __('Voir les templates PDF', 'pdf-builder-pro'),
                            'export_pdf_templates' => __('Exporter les templates PDF', 'pdf-builder-pro'),
                            'import_pdf_templates' => __('Importer les templates PDF', 'pdf-builder-pro'),
                            'manage_pdf_settings' => __('Gérer les paramètres PDF', 'pdf-builder-pro')
                        ];
                        ?>

                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Rôle', 'pdf-builder-pro'); ?></th>
                                    <?php foreach ($pdf_permissions as $perm => $label): ?>
                                        <th><?php echo $label; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($roles as $role_key => $role): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo translate_user_role($role['name']); ?></strong>
                                            <br><small><?php echo $role_key; ?></small>
                                        </td>
                                        <?php foreach ($pdf_permissions as $perm => $label): ?>
                                            <td>
                                                <input type="checkbox"
                                                       name="role_permissions[<?php echo $role_key; ?>][<?php echo $perm; ?>]"
                                                       value="1"
                                                       <?php checked($role['capabilities'][$perm] ?? false); ?>>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <p class="description">
                            <?php _e('Cochez les permissions que vous souhaitez accorder à chaque rôle.', 'pdf-builder-pro'); ?>
                        </p>
                    </div>

                    <div class="roles-actions">
                        <h3><?php _e('Actions', 'pdf-builder-pro'); ?></h3>

                        <div class="action-buttons">
                            <a href="<?php echo admin_url('users.php?page=roles'); ?>" class="button button-secondary" target="_blank">
                                <?php _e('Créer un Nouveau Rôle', 'pdf-builder-pro'); ?>
                                <span class="dashicons dashicons-external"></span>
                            </a>

                            <button type="button" id="reset-role-permissions" class="button button-secondary">
                                <?php _e('Réinitialiser les Permissions', 'pdf-builder-pro'); ?>
                            </button>

                            <button type="button" id="bulk-assign-permissions" class="button button-secondary">
                                <?php _e('Assigner en Masse', 'pdf-builder-pro'); ?>
                            </button>
                        </div>

                        <div id="roles-status"></div>
                    </div>

                    <div class="roles-info">
                        <h3><?php _e('Informations', 'pdf-builder-pro'); ?></h3>
                        <div class="notice notice-info inline">
                            <p>
                                <strong><?php _e('Note importante:', 'pdf-builder-pro'); ?></strong><br>
                                <?php _e('Les modifications des permissions prennent effet immédiatement. Les utilisateurs connectés devront se reconnecter pour que les changements soient appliqués.', 'pdf-builder-pro'); ?>
                            </p>
                            <p>
                                <?php _e('Pour créer un nouveau rôle personnalisé, utilisez le lien "Créer un Nouveau Rôle" ci-dessus qui vous redirigera vers la page de gestion des rôles de WordPress.', 'pdf-builder-pro'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglet Notifications -->
            <div id="notifications" class="tab-content">
                <h2><?php _e('Paramètres de Notifications', 'pdf-builder-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Notifications Email', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="email_notifications_enabled" value="1" <?php checked($config->get('email_notifications_enabled'), true); ?>>
                                <?php _e('Activer les notifications par email', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Événements à Notifier', 'pdf-builder-pro'); ?></th>
                        <td>
                            <?php
                            $events = $config->get('notification_events', []);
                            if (!is_array($events)) {
                                $events = [];
                            }
                            $available_events = [
                                'template_created' => __('Template créé', 'pdf-builder-pro'),
                                'template_updated' => __('Template mis à jour', 'pdf-builder-pro'),
                                'document_generated' => __('Document généré', 'pdf-builder-pro'),
                                'error_occurred' => __('Erreur survenue', 'pdf-builder-pro')
                            ];
                            ?>
                            <?php foreach ($available_events as $event => $label): ?>
                                <label style="display: block; margin-bottom: 5px;">
                                    <input type="checkbox" name="notification_events[]" value="<?php echo $event; ?>" <?php checked(true, in_array($event, $events)); ?>>
                                    <?php echo $label; ?>
                                </label>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Onglet Canvas -->
            <div id="canvas" class="tab-content">
                <h2><?php _e('Paramètres du Canvas', 'pdf-builder-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Bordures des Éléments', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="canvas_element_borders_enabled" value="1" <?php checked($config->get('canvas_element_borders_enabled', true), true); ?>>
                                <?php _e('Activer les bordures des éléments sur le canvas', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Épaisseur des Bordures', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="canvas_border_width" value="<?php echo $config->get('canvas_border_width'); ?>" class="small-text" min="0" max="10" step="0.5">
                            <span class="description"><?php _e('Épaisseur en pixels (0.5 à 10)', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Couleur des Bordures', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="color" name="canvas_border_color" value="<?php echo esc_attr($config->get('canvas_border_color')); ?>">
                            <span class="description"><?php _e('Couleur des bordures des éléments', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Écart des Bordures', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="canvas_border_spacing" value="<?php echo $config->get('canvas_border_spacing'); ?>" class="small-text" min="0" max="20" step="1">
                            <span class="description"><?php _e('Espacement autour des bordures en pixels (0 à 20)', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Poignées de Redimensionnement', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="canvas_resize_handles_enabled" value="1" <?php checked($config->get('canvas_resize_handles_enabled'), true); ?>>
                                <?php _e('Afficher les poignées de redimensionnement', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Taille des Poignées', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="canvas_handle_size" value="<?php echo $config->get('canvas_handle_size'); ?>" class="small-text" min="4" max="20">
                            <span class="description"><?php _e('Taille en pixels (4 à 20)', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Couleur des Poignées', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="color" name="canvas_handle_color" value="<?php echo esc_attr($config->get('canvas_handle_color')); ?>">
                            <span class="description"><?php _e('Couleur des poignées de redimensionnement', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Couleur de Survol des Poignées', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="color" name="canvas_handle_hover_color" value="<?php echo esc_attr($config->get('canvas_handle_hover_color')); ?>">
                            <span class="description"><?php _e('Couleur des poignées au survol', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Onglet Maintenance -->
            <div id="maintenance" class="tab-content">
                <h2><?php _e('Actions de Maintenance', 'pdf-builder-pro'); ?></h2>

                <div class="maintenance-section">
                    <h3><?php _e('Base de Données', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Vérifiez et réparez la structure de la base de données si nécessaire.', 'pdf-builder-pro'); ?></p>

                    <div class="maintenance-actions">
                        <a href="<?php echo plugins_url('repair-database.php', dirname(__FILE__) . '/../..'); ?>" class="button button-secondary">
                            <?php _e('Vérifier la Base de Données', 'pdf-builder-pro'); ?>
                        </a>
                        <button type="button" id="execute-sql-repair" class="button button-primary" style="margin-left: 10px;">
                            <?php _e('Réparer la Base de Données', 'pdf-builder-pro'); ?>
                        </button>
                        <a href="<?php echo plugins_url('repair-database.sql', dirname(__FILE__) . '/../..'); ?>" class="button button-secondary" style="margin-left: 10px;" download="repair-database.sql">
                            <?php _e('Télécharger SQL', 'pdf-builder-pro'); ?>
                        </a>
                    </div>

                    <div id="database-status" class="maintenance-status" style="margin-top: 15px;"></div>
                </div>

                <hr>

                <div class="maintenance-section">
                    <h3><?php _e('Cache et Optimisation', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Nettoyez le cache et optimisez les performances.', 'pdf-builder-pro'); ?></p>

                    <div class="maintenance-actions">
                        <button type="button" id="clear-cache" class="button button-secondary">
                            <?php _e('Vider le Cache', 'pdf-builder-pro'); ?>
                        </button>
                        <button type="button" id="optimize-database" class="button button-secondary" style="margin-left: 10px;">
                            <?php _e('Optimiser la Base de Données', 'pdf-builder-pro'); ?>
                        </button>
                    </div>

                    <div id="cache-status" class="maintenance-status" style="margin-top: 15px;"></div>
                </div>

                <hr>

                <div class="maintenance-section">
                    <h3><?php _e('Logs et Diagnostics', 'pdf-builder-pro'); ?></h3>
                    <p><?php _e('Consultez et nettoyez les logs du système.', 'pdf-builder-pro'); ?></p>

                    <div class="maintenance-actions">
                        <button type="button" id="view-logs" class="button button-secondary">
                            <?php _e('Voir les Logs', 'pdf-builder-pro'); ?>
                        </button>
                        <button type="button" id="clear-logs" class="button button-secondary" style="margin-left: 10px;">
                            <?php _e('Vider les Logs', 'pdf-builder-pro'); ?>
                        </button>
                        <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates&action=diagnostic'); ?>" class="button button-secondary" style="margin-left: 10px;">
                            <?php _e('Outil de Diagnostic', 'pdf-builder-pro'); ?>
                        </a>
                    </div>

                    <div id="logs-status" class="maintenance-status" style="margin-top: 15px;"></div>
                </div>
            </div>
        </div>

        <p class="submit">
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Enregistrer les paramètres', 'pdf-builder-pro'); ?>">
        </p>
    </form>
</div>

<style>
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
    display: block !important;
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

/* Styles pour l'onglet Rôles */
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
</style>

<script type="text/javascript">
(function($) {
    'use strict';

    // Attendre que le DOM soit complètement chargé
    $(document).ready(function() {
        

        // Vérifier que les éléments existent
        var navTabs = document.querySelectorAll('.nav-tab');
        var tabContents = document.querySelectorAll('.tab-content');

        

        if (navTabs.length === 0 || tabContents.length === 0) {
            console.error('❌ Tab elements not found');
            return;
        }

        // Fonction pour masquer tous les onglets
        function hideAllTabs() {
            tabContents.forEach(function(tab) {
                tab.classList.remove('active');
                tab.style.display = 'none';
            });
        }

        // Fonction pour afficher un onglet
        function showTab(tabId) {
            var tabElement = document.getElementById(tabId.substring(1)); // Remove the #
            if (tabElement) {
                tabElement.classList.add('active');
                tabElement.style.display = 'block';
            } else {
                console.error('❌ Tab element not found:', tabId);
            }
        }

        // Fonction pour changer d'onglet
        function switchToTab(tabId) {
            // Masquer tous les onglets
            hideAllTabs();

            // Désactiver tous les onglets de navigation
            navTabs.forEach(function(tab) {
                tab.classList.remove('nav-tab-active');
            });

            // Activer l'onglet de navigation cible
            var targetNavTab = document.querySelector('a[href="' + tabId + '"]');
            if (targetNavTab) {
                targetNavTab.classList.add('nav-tab-active');
            }

            // Afficher l'onglet cible
            showTab(tabId);
        }

        // Attacher les gestionnaires de clic
        navTabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('href');
                switchToTab(targetId);

                // Mettre à jour l'URL avec le hash (sans recharger la page)
                if (history.replaceState) {
                    history.replaceState(null, null, targetId);
                }
            });
        });

        // Initialisation : masquer tous les onglets sauf celui actif
        hideAllTabs();

        // Vérifier si on arrive sur la page avec un hash dans l'URL
        var currentHash = window.location.hash;
        var activeTab = document.querySelector('.nav-tab-active');

        if (currentHash && document.querySelector('a[href="' + currentHash + '"]')) {
            // Si un hash est présent dans l'URL, l'utiliser
            
            switchToTab(currentHash);
        } else if (activeTab) {
            // Sinon, utiliser l'onglet actif par défaut
            var activeTabId = activeTab.getAttribute('href');
            
            showTab(activeTabId);
        } else {
            // Fallback : activer le premier onglet
            
            if (navTabs.length > 0) {
                var firstTabId = navTabs[0].getAttribute('href');
                navTabs[0].classList.add('nav-tab-active');
                showTab(firstTabId);
            }
        }

        // Gérer les changements de hash dans l'URL (liens directs, navigation arrière/avant)
        window.addEventListener('hashchange', function() {
            var newHash = window.location.hash;
            if (newHash && document.querySelector('a[href="' + newHash + '"]')) {
                
                switchToTab(newHash);
            }
        });

        // Vérifier périodiquement si les onglets sont bien affichés (fallback au cas où)
        setTimeout(function() {
            var visibleTabs = document.querySelectorAll('.tab-content[style*="display: block"]');
            if (visibleTabs.length === 0) {
                console.warn('⚠️ No tabs visible, forcing default tab');
                var defaultTab = document.querySelector('.nav-tab-active') || navTabs[0];
                if (defaultTab) {
                    var defaultTabId = defaultTab.getAttribute('href');
                    switchToTab(defaultTabId);
                }
            }
        }, 100);
    });

    // Fallback si jQuery n'est pas disponible
    if (typeof jQuery === 'undefined') {
        console.warn('⚠️ jQuery not available, using vanilla JS fallback');

        document.addEventListener('DOMContentLoaded', function() {
            var navTabs = document.querySelectorAll('.nav-tab');
            var tabContents = document.querySelectorAll('.tab-content');

            if (navTabs.length === 0 || tabContents.length === 0) {
                console.error('❌ Tab elements not found in fallback');
                return;
            }

            // Fonction pour masquer tous les onglets
            function hideAllTabs() {
                tabContents.forEach(function(tab) {
                    tab.classList.remove('active');
                    tab.style.display = 'none';
                });
            }

            // Fonction pour afficher un onglet
            function showTab(tabId) {
                var tabElement = document.getElementById(tabId.substring(1));
                if (tabElement) {
                    tabElement.classList.add('active');
                    tabElement.style.display = 'block';
                }
            }

            // Attacher les gestionnaires de clic
            navTabs.forEach(function(tab) {
                tab.addEventListener('click', function(e) {
                    e.preventDefault();
                    var targetId = this.getAttribute('href');

                    // Masquer tous les onglets
                    hideAllTabs();

                    // Désactiver tous les onglets de navigation
                    navTabs.forEach(function(t) {
                        t.classList.remove('nav-tab-active');
                    });

                    // Activer l'onglet de navigation cible
                    this.classList.add('nav-tab-active');

                    // Afficher l'onglet cible
                    showTab(targetId);
                });
            });

            // Initialisation
            hideAllTabs();
            var activeTab = document.querySelector('.nav-tab-active');
            if (activeTab) {
                showTab(activeTab.getAttribute('href'));
            }
        });
    }

})(jQuery);
</script>
<script>
(function($) {
    'use strict';

    // Actions de maintenance
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
                nonce: '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>'
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

    $('#execute-sql-repair').on('click', function() {
        if (!confirm('<?php echo esc_js(__('Êtes-vous sûr de vouloir exécuter la réparation SQL ? Cette action va créer les tables manquantes et insérer les données par défaut.', 'pdf-builder-pro')); ?>')) {
            return;
        }

        var $button = $(this);
        var $status = $('#database-status');

        $button.prop('disabled', true).text('<?php echo esc_js(__('Exécution...', 'pdf-builder-pro')); ?>');
        $status.html('<div class="notice notice-info"><p><?php echo esc_js(__('Exécution du script SQL en cours...', 'pdf-builder-pro')); ?></p></div>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_execute_sql_repair',
                nonce: '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var html = '<div class="notice notice-success"><p><?php echo esc_js(__('Réparation SQL exécutée avec succès !', 'pdf-builder-pro')); ?></p>';
                    if (response.data.results) {
                        html += '<ul>';
                        $.each(response.data.results, function(index, result) {
                            var icon = result.success ? '✅' : '❌';
                            html += '<li>' + icon + ' ' + result.table + ': ' + result.message + '</li>';
                        });
                        html += '</ul>';
                    }
                    html += '</div>';
                    $status.html(html);
                } else {
                    console.error('Erreur dans la réponse:', response.data);
                    $status.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? $('<div>').text(response.data.message).html() : 'Erreur inconnue') + '</p></div>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Erreur AJAX:', {
                    xhr: xhr,
                    status: status,
                    error: error,
                    responseText: xhr.responseText
                });
                $status.html('<div class="notice notice-error"><p><?php echo esc_js(__('Erreur lors de l\'exécution du script SQL.', 'pdf-builder-pro')); ?></p></div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('<?php echo esc_js(__('Réparer la Base de Données', 'pdf-builder-pro')); ?>');
            }
        });
    });

    $('#optimize-database').on('click', function() {
        if (!confirm('<?php echo esc_js(__('Êtes-vous sûr de vouloir optimiser la base de données ?', 'pdf-builder-pro')); ?>')) {
            return;
        }

        var $button = $(this);
        var $status = $('#cache-status');

        $button.prop('disabled', true).text('<?php echo esc_js(__('Optimisation...', 'pdf-builder-pro')); ?>');
        $status.html('<div class="notice notice-info"><p><?php echo esc_js(__('Optimisation de la base de données en cours...', 'pdf-builder-pro')); ?></p></div>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_optimize_database',
                nonce: '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<div class="notice notice-success"><p><?php echo esc_js(__('Base de données optimisée avec succès !', 'pdf-builder-pro')); ?></p></div>');
                } else {
                    $status.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? $('<div>').text(response.data.message).html() : 'Erreur inconnue') + '</p></div>');
                }
            },
            error: function() {
                $status.html('<div class="notice notice-error"><p><?php echo esc_js(__('Erreur lors de l\'optimisation de la base de données.', 'pdf-builder-pro')); ?></p></div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('<?php echo esc_js(__('Optimiser la Base de Données', 'pdf-builder-pro')); ?>');
            }
        });
    });

    $('#view-logs').on('click', function() {
        var $button = $(this);
        var $status = $('#logs-status');

        $button.prop('disabled', true).text('<?php echo esc_js(__('Chargement...', 'pdf-builder-pro')); ?>');
        $status.html('<div class="notice notice-info"><p><?php echo esc_js(__('Chargement des logs en cours...', 'pdf-builder-pro')); ?></p></div>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_view_logs',
                nonce: '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var logsHtml = '<div class="notice notice-info"><p><?php echo esc_js(__('Logs récents :', 'pdf-builder-pro')); ?></p>';
                    logsHtml += '<div style="max-height: 300px; overflow-y: auto; background: #f9f9f9; padding: 10px; border: 1px solid #ddd; margin-top: 10px;">';
                    logsHtml += '<pre style="margin: 0; white-space: pre-wrap; font-family: monospace; font-size: 12px;">';
                    logsHtml += response.data.logs || '<?php echo esc_js(__('Aucun log trouvé.', 'pdf-builder-pro')); ?>';
                    logsHtml += '</pre></div></div>';
                    $status.html(logsHtml);
                } else {
                    $status.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? $('<div>').text(response.data.message).html() : 'Erreur inconnue') + '</p></div>');
                }
            },
            error: function() {
                $status.html('<div class="notice notice-error"><p><?php echo esc_js(__('Erreur lors du chargement des logs.', 'pdf-builder-pro')); ?></p></div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('<?php echo esc_js(__('Voir les Logs', 'pdf-builder-pro')); ?>');
            }
        });
    });

    $('#clear-logs').on('click', function() {
        if (!confirm('<?php echo esc_js(__('Êtes-vous sûr de vouloir vider les logs ?', 'pdf-builder-pro')); ?>')) {
            return;
        }

        var $button = $(this);
        var $status = $('#logs-status');

        $button.prop('disabled', true).text('<?php echo esc_js(__('Nettoyage...', 'pdf-builder-pro')); ?>');
        $status.html('<div class="notice notice-info"><p><?php echo esc_js(__('Nettoyage des logs en cours...', 'pdf-builder-pro')); ?></p></div>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_clear_logs',
                nonce: '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<div class="notice notice-success"><p><?php echo esc_js(__('Logs vidés avec succès !', 'pdf-builder-pro')); ?></p></div>');
                } else {
                    $status.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? $('<div>').text(response.data.message).html() : 'Erreur inconnue') + '</p></div>');
                }
            },
            error: function() {
                $status.html('<div class="notice notice-error"><p><?php echo esc_js(__('Erreur lors du nettoyage des logs.', 'pdf-builder-pro')); ?></p></div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('<?php echo esc_js(__('Vider les Logs', 'pdf-builder-pro')); ?>');
            }
        });
    });

    // Gestion des rôles
    $('#reset-role-permissions').on('click', function() {
        if (!confirm('<?php echo esc_js(__('Êtes-vous sûr de vouloir réinitialiser toutes les permissions des rôles ? Cette action ne peut pas être annulée.', 'pdf-builder-pro')); ?>')) {
            return;
        }

        var $button = $(this);
        var $status = $('#roles-status');

        $button.prop('disabled', true).text('<?php echo esc_js(__('Réinitialisation...', 'pdf-builder-pro')); ?>');
        $status.html('<div class="notice notice-info"><p><?php echo esc_js(__('Réinitialisation des permissions en cours...', 'pdf-builder-pro')); ?></p></div>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_reset_role_permissions',
                nonce: '<?php echo wp_create_nonce('pdf_builder_roles'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $status.html('<div class="notice notice-success"><p><?php echo esc_js(__('Permissions réinitialisées avec succès !', 'pdf-builder-pro')); ?></p></div>');
                    location.reload(); // Recharger la page pour voir les changements
                } else {
                    $status.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? $('<div>').text(response.data.message).html() : 'Erreur inconnue') + '</p></div>');
                }
            },
            error: function() {
                $status.html('<div class="notice notice-error"><p><?php echo esc_js(__('Erreur lors de la réinitialisation des permissions.', 'pdf-builder-pro')); ?></p></div>');
            },
            complete: function() {
                $button.prop('disabled', false).text('<?php echo esc_js(__('Réinitialiser les Permissions', 'pdf-builder-pro')); ?>');
            }
        });
    });

    $('#bulk-assign-permissions').on('click', function() {
        var $button = $(this);
        var $status = $('#roles-status');

        // Créer une boîte de dialogue simple
        var dialogHtml = '<div id="bulk-assign-dialog" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000;">';
        dialogHtml += '<div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); max-width: 500px; width: 90%; max-height: 80vh; overflow-y: auto;">';
        dialogHtml += '<h3 style="margin: 0 0 15px 0; color: #23282d;">Assignation en Masse des Permissions</h3>';
        dialogHtml += '<p>Sélectionnez les permissions à assigner à tous les rôles:</p>';
        dialogHtml += '<div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; margin: 10px 0;">';

        var permissions = [
            {key: 'manage_pdf_templates', label: 'Gérer les templates PDF'},
            {key: 'create_pdf_templates', label: 'Créer des templates PDF'},
            {key: 'edit_pdf_templates', label: 'Modifier les templates PDF'},
            {key: 'delete_pdf_templates', label: 'Supprimer les templates PDF'},
            {key: 'view_pdf_templates', label: 'Voir les templates PDF'},
            {key: 'export_pdf_templates', label: 'Exporter les templates PDF'},
            {key: 'import_pdf_templates', label: 'Importer les templates PDF'},
            {key: 'manage_pdf_settings', label: 'Gérer les paramètres PDF'}
        ];

        permissions.forEach(function(perm) {
            dialogHtml += '<label style="display: block; margin-bottom: 5px;"><input type="checkbox" name="bulk_permissions[]" value="' + perm.key + '"> ' + perm.label + '</label>';
        });

        dialogHtml += '</div>';
        dialogHtml += '<p><small style="color: #666;">Attention: Cette action va écraser toutes les permissions actuelles.</small></p>';
        dialogHtml += '<div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">';
        dialogHtml += '<button id="bulk-assign-cancel" class="button">Annuler</button>';
        dialogHtml += '<button id="bulk-assign-confirm" class="button button-primary">Appliquer</button>';
        dialogHtml += '</div>';
        dialogHtml += '</div>';
        dialogHtml += '</div>';

        $('body').append(dialogHtml);
        $('#bulk-assign-dialog').show();

        // Gestionnaire pour le bouton Annuler
        $('#bulk-assign-cancel').on('click', function() {
            $('#bulk-assign-dialog').remove();
        });

        // Gestionnaire pour le bouton Appliquer
        $('#bulk-assign-confirm').on('click', function() {
            var selectedPermissions = [];
            $('#bulk-assign-dialog input[name="bulk_permissions[]"]:checked').each(function() {
                selectedPermissions.push($(this).val());
            });

            if (selectedPermissions.length === 0) {
                alert('Veuillez sélectionner au moins une permission.');
                return;
            }

            $('#bulk-assign-dialog').remove();

            $button.prop('disabled', true).text('Application...');
            $status.html('<div class="notice notice-info"><p>Application des permissions en cours...</p></div>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_bulk_assign_permissions',
                    permissions: selectedPermissions,
                    nonce: '<?php echo wp_create_nonce('pdf_builder_roles'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        $status.html('<div class="notice notice-success"><p>Permissions appliquées avec succès !</p></div>');
                        location.reload();
                    } else {
                        $status.html('<div class="notice notice-error"><p>' + (response.data && response.data.message ? $('<div>').text(response.data.message).html() : 'Erreur inconnue') + '</p></div>');
                    }
                },
                error: function() {
                    $status.html('<div class="notice notice-error"><p>Erreur lors de l\'application des permissions.</p></div>');
                },
                complete: function() {
                    $button.prop('disabled', false).text('Assigner en Masse');
                }
            });
        });

        // Fermer la boîte de dialogue en cliquant sur le fond
        $('#bulk-assign-dialog').on('click', function(e) {
            if (e.target === this) {
                $(this).remove();
            }
        });
    });

})(jQuery);
</script>


<?php
/**
 * Page des Paramètres - PDF Builder Pro
 */



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

if ((isset($_POST['submit']) || isset($_POST['submit_roles']) || isset($_POST['submit_notifications'])) && isset($_POST['pdf_builder_settings_nonce'])) {
    error_log('PDF Builder: Bouton submit cliqué, nonce présent: ' . $_POST['pdf_builder_settings_nonce']);

    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
        error_log('PDF Builder: Nonce valide, traitement des paramètres');

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
            'canvas_handle_hover_color' => sanitize_text_field($_POST['canvas_handle_hover_color']),
            'email_notifications' => isset($_POST['email_notifications']),
            'admin_email' => sanitize_email($_POST['admin_email']),
            'notification_log_level' => sanitize_text_field($_POST['notification_log_level'])
        ];

        error_log('PDF Builder: Paramètres canvas - borders_enabled: ' . ($settings['canvas_element_borders_enabled'] ? 'true' : 'false'));
        error_log('PDF Builder: Paramètres canvas - border_spacing: ' . $settings['canvas_border_spacing']);

        $config->set_multiple($settings);

        // Traitement spécifique des rôles autorisés
        if (isset($_POST['pdf_builder_allowed_roles'])) {
            $allowed_roles = array_map('sanitize_text_field', (array) $_POST['pdf_builder_allowed_roles']);
            // S'assurer qu'au moins un rôle est sélectionné
            if (empty($allowed_roles)) {
                $allowed_roles = ['administrator']; // Rôle par défaut
            }
            update_option('pdf_builder_allowed_roles', $allowed_roles);
            error_log('PDF Builder: Rôles autorisés sauvegardés: ' . implode(', ', $allowed_roles));
        }

        // Vérification que les options sont bien sauvegardées
        $saved_spacing = get_option('canvas_border_spacing', 'NOT_SET');
        error_log('PDF Builder: Vérification sauvegarde - canvas_border_spacing: ' . $saved_spacing);

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
        error_log('PDF Builder: Nonce invalide');
        if ($isAjax) {
            wp_send_json_error(__('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro'));
            exit; // Important : arrêter l'exécution ici pour les requêtes AJAX
        } else {
            $admin_notices[] = '<div class="notice notice-error"><p>' . __('Erreur de sécurité : nonce invalide.', 'pdf-builder-pro') . '</p></div>';
        }
    }
} elseif (isset($_POST['submit'])) {
    error_log('PDF Builder: Bouton submit cliqué mais pas de nonce');
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
    // Vérification de sécurité pour éviter les erreurs JavaScript de plugins tiers
    if (typeof wp === 'undefined') {
        // Définir un objet wp minimal pour éviter les erreurs
        window.wp = window.wp || {
            api: { models: {}, collections: {}, views: {} },
            ajax: { settings: { url: ajaxurl || '/wp-admin/admin-ajax.php' } }
        };
    }

    // Message immédiat pour confirmer que le script s'exécute
    // Debug elements removed - script is working properly
    
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

    <form method="post" action="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-settings')); ?>" onsubmit="console.log('Form submitted'); console.log('Form action:', this.action); console.log('Form method:', this.method); return true;">
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

            <!-- Message pour les utilisateurs sans JavaScript -->
            <noscript>
                <div class="notice notice-warning">
                    <p><?php _e('JavaScript est requis pour la navigation par onglets. Certains onglets peuvent ne pas s\'afficher correctement.', 'pdf-builder-pro'); ?></p>
                </div>
            </noscript>

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

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Dimensions par Défaut', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label for="default_canvas_width"><?php _e('Largeur:', 'pdf-builder-pro'); ?></label>
                            <input name="default_canvas_width" type="number" id="default_canvas_width" value="<?php echo esc_attr(get_option('pdf_builder_default_canvas_width', 210)); ?>" class="small-text"> mm
                            <br>
                            <label for="default_canvas_height"><?php _e('Hauteur:', 'pdf-builder-pro'); ?></label>
                            <input name="default_canvas_height" type="number" id="default_canvas_height" value="<?php echo esc_attr(get_option('pdf_builder_default_canvas_height', 297)); ?>" class="small-text"> mm
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Grille d\'Alignement', 'pdf-builder-pro'); ?></th>
                        <td>
                            <fieldset>
                                <label for="show_grid">
                                    <input name="show_grid" type="checkbox" id="show_grid" value="1" <?php checked(get_option('pdf_builder_show_grid', true)); ?>>
                                    <?php _e('Afficher la grille d\'alignement dans l\'éditeur', 'pdf-builder-pro'); ?>
                                </label>
                            </fieldset>
                            <br>
                            <label for="grid_size"><?php _e('Taille de la grille:', 'pdf-builder-pro'); ?></label>
                            <input name="grid_size" type="number" id="grid_size" value="<?php echo esc_attr(get_option('pdf_builder_grid_size', 10)); ?>" class="small-text"> px
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Aimants', 'pdf-builder-pro'); ?></th>
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
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Onglet Maintenance -->
            <div id="maintenance" class="tab-content">
                <h2><?php _e('Actions de Maintenance', 'pdf-builder-pro'); ?></h2>

                <div class="maintenance-actions">
                    <div class="maintenance-section" style="margin-bottom: 30px;">
                        <h3><?php _e('Nettoyage des Données', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Supprimez les données temporaires et les fichiers obsolètes pour optimiser les performances.', 'pdf-builder-pro'); ?></p>

                        <form method="post" style="display: inline;">
                            <?php wp_nonce_field('clear_cache', 'clear_cache_nonce'); ?>
                            <input type="submit" name="clear_cache" class="button button-secondary" value="<?php esc_attr_e('Vider le Cache', 'pdf-builder-pro'); ?>">
                        </form>

                        <form method="post" style="display: inline; margin-left: 10px;">
                            <?php wp_nonce_field('clear_temp_files', 'clear_temp_files_nonce'); ?>
                            <input type="submit" name="clear_temp_files" class="button button-secondary" value="<?php esc_attr_e('Supprimer Fichiers Temporaires', 'pdf-builder-pro'); ?>">
                        </form>
                    </div>

                    <div class="maintenance-section" style="margin-bottom: 30px;">
                        <h3><?php _e('Réparation de Données', 'pdf-builder-pro'); ?></h3>
                        <p><?php _e('Réparez les templates corrompus et les paramètres invalides.', 'pdf-builder-pro'); ?></p>

                        <form method="post" style="display: inline;">
                            <?php wp_nonce_field('repair_templates', 'repair_templates_nonce'); ?>
                            <input type="submit" name="repair_templates" class="button button-secondary" value="<?php esc_attr_e('Réparer Templates', 'pdf-builder-pro'); ?>">
                        </form>

                        <form method="post" style="display: inline; margin-left: 10px;">
                            <?php wp_nonce_field('reset_settings', 'reset_settings_nonce'); ?>
                            <input type="submit" name="reset_settings" class="button button-warning" value="<?php esc_attr_e('Réinitialiser Paramètres', 'pdf-builder-pro'); ?>"
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
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Enregistrer les paramètres', 'pdf-builder-pro'); ?>" onclick="console.log('Submit button clicked - type:', this.type, 'name:', this.name);">
        </p>
    </form>
</div>

<!-- Debug script to check form submission -->
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    console.log('Settings page JavaScript loaded');
    
    // Définir les nonces en variables JavaScript
    var pdfBuilderSettingsNonce = '<?php echo wp_create_nonce('pdf_builder_settings'); ?>';
    var pdfBuilderMaintenanceNonce = '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>';
    
    // Écouter tous les événements de soumission de formulaire
    document.addEventListener('submit', function(e) {
        console.log('Form submit event detected on:', e.target);
        console.log('Form action:', e.target.action);
        console.log('Form method:', e.target.method);
    });
    
    // Écouter les clics sur le bouton submit
    var submitBtn = document.getElementById('submit');
    if (submitBtn) {
        console.log('Submit button found:', submitBtn);
        submitBtn.addEventListener('click', function(e) {
            e.preventDefault(); // Empêcher la soumission normale
            console.log('Submit button click event:', e);
            console.log('Default prevented?', e.defaultPrevented);
            
            // Soumission AJAX
            submitFormAjax();
        });
    } else {
        console.error('Submit button not found!');
    }
    
    // Vérifier le formulaire
    var form = document.querySelector('form[method="post"]');
    if (form) {
        console.log('Form found:', form);
        console.log('Form action:', form.action);
        console.log('Form method:', form.method);
        
        // Ajouter un écouteur d'événement submit sur le formulaire
        form.addEventListener('submit', function(e) {
            console.log('FORM SUBMIT EVENT TRIGGERED!');
            console.log('Submit event:', e);
            console.log('Default prevented:', e.defaultPrevented);
        });
    } else {
        console.error('Form not found!');
    }
    
    // Fonction pour soumettre le formulaire en AJAX
    function submitFormAjax() {
        if (!form) {
            console.error('No form found for AJAX submission');
            return;
        }
        
        // Collecter les données du formulaire
        var formData = new FormData(form);
        
        // Afficher un indicateur de chargement
        submitBtn.disabled = true;
        submitBtn.value = 'Enregistrement...';
        
        console.log('Submitting form via AJAX...');
        
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
            console.log('AJAX response received:', response);
            return response.text();
        })
        .then(function(data) {
            console.log('AJAX response data:', data);
            
            // Réactiver le bouton
            submitBtn.disabled = false;
            submitBtn.value = 'Enregistrer les paramètres';
            
            try {
                // Essayer de parser comme JSON
                var jsonResponse = JSON.parse(data);
                if (jsonResponse.success) {
                    showNotification(jsonResponse.data.message || 'Paramètres sauvegardés avec succès !', 'success');
                } else {
                    showNotification(jsonResponse.data || 'Erreur lors de la sauvegarde.', 'error');
                }
            } catch (e) {
                // Si ce n'est pas du JSON, vérifier le contenu HTML
                if (data.includes('Paramètres sauvegardés avec succès') || data.includes('notice-success')) {
                    showNotification('Paramètres sauvegardés avec succès !', 'success');
                } else if (data.includes('notice-error') || data.includes('Erreur')) {
                    showNotification('Erreur lors de la sauvegarde.', 'error');
                } else {
                    showNotification('Paramètres sauvegardés.', 'success');
                }
            }
        })
        .catch(function(error) {
            console.error('AJAX error:', error);
            submitBtn.disabled = false;
            submitBtn.value = 'Enregistrer les paramètres';
            showNotification('Erreur de connexion.', 'error');
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
            z-index: 9999;
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

})(jQuery);
</script>



<?php
// Fin du fichier
?>


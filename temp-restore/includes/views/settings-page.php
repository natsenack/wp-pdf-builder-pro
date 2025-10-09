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
    // S'assurer que c'est initialisé
    if (!$core->is_initialized()) {
        $core->init();
    }
}

$config = $core->get_config_manager();

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
        'email_notifications_enabled' => isset($_POST['email_notifications_enabled'])
    ];

    $config->set_multiple($settings);

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

<div class="wrap">
    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <form method="post" action="">
        <?php wp_nonce_field('pdf_builder_settings', 'pdf_builder_settings_nonce'); ?>

        <div class="pdf-builder-settings">
            <!-- Onglets -->
            <div class="nav-tab-wrapper">
                <a href="#general" class="nav-tab nav-tab-active"><?php _e('Général', 'pdf-builder-pro'); ?></a>
                <a href="#performance" class="nav-tab"><?php _e('Performance', 'pdf-builder-pro'); ?></a>
                <a href="#pdf" class="nav-tab"><?php _e('PDF', 'pdf-builder-pro'); ?></a>
                <a href="#security" class="nav-tab"><?php _e('Sécurité', 'pdf-builder-pro'); ?></a>
                <a href="#roles" class="nav-tab"><?php _e('Rôles', 'pdf-builder-pro'); ?></a>
                <a href="#notifications" class="nav-tab"><?php _e('Notifications', 'pdf-builder-pro'); ?></a>
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
                                <input type="checkbox" name="debug_mode" value="1" <?php checked($config->get('debug_mode')); ?>>
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
                            <input type="number" name="max_template_size" value="<?php echo $config->get('max_template_size'); ?>" class="small-text">
                            <span class="description"><?php _e('Taille maximale en octets (50MB par défaut)', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Onglet Performance -->
            <div id="performance" class="tab-content">
                <h2><?php _e('Paramètres de Performance', 'pdf-builder-pro'); ?></h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Cache Activé', 'pdf-builder-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="cache_enabled" value="1" <?php checked($config->get('cache_enabled')); ?>>
                                <?php _e('Activer la mise en cache pour améliorer les performances', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('TTL Cache', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="cache_ttl" value="<?php echo $config->get('cache_ttl'); ?>" class="small-text">
                            <span class="description"><?php _e('Durée de vie du cache en secondes (3600 = 1 heure)', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Temps Max Exécution', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="number" name="max_execution_time" value="<?php echo $config->get('max_execution_time'); ?>" class="small-text">
                            <span class="description"><?php _e('Temps maximum d\'exécution en secondes', 'pdf-builder-pro'); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Limite Mémoire', 'pdf-builder-pro'); ?></th>
                        <td>
                            <input type="text" name="memory_limit" value="<?php echo $config->get('memory_limit'); ?>" class="small-text">
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
                                <input type="checkbox" name="email_notifications_enabled" value="1" <?php checked($config->get('email_notifications_enabled')); ?>>
                                <?php _e('Activer les notifications par email', 'pdf-builder-pro'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Événements à Notifier', 'pdf-builder-pro'); ?></th>
                        <td>
                            <?php
                            $events = $config->get('notification_events', []);
                            $available_events = [
                                'template_created' => __('Template créé', 'pdf-builder-pro'),
                                'template_updated' => __('Template mis à jour', 'pdf-builder-pro'),
                                'document_generated' => __('Document généré', 'pdf-builder-pro'),
                                'error_occurred' => __('Erreur survenue', 'pdf-builder-pro')
                            ];
                            ?>
                            <?php foreach ($available_events as $event => $label): ?>
                                <label style="display: block; margin-bottom: 5px;">
                                    <input type="checkbox" name="notification_events[]" value="<?php echo $event; ?>" <?php checked(in_array($event, $events)); ?>>
                                    <?php echo $label; ?>
                                </label>
                            <?php endforeach; ?>
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
}

.tab-content {
    display: none;
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

<script>
jQuery(document).ready(function($) {
    // Navigation par onglets
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();

        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');

        $('.tab-content').removeClass('active');
        $($(this).attr('href')).addClass('active');
    });

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
                    $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
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
        console.log('Bouton "Réparer la Base de Données" cliqué');

        if (!confirm('<?php echo esc_js(__('Êtes-vous sûr de vouloir exécuter la réparation SQL ? Cette action va créer les tables manquantes et insérer les données par défaut.', 'pdf-builder-pro')); ?>')) {
            return;
        }

        var $button = $(this);
        var $status = $('#database-status');

        $button.prop('disabled', true).text('<?php echo esc_js(__('Exécution...', 'pdf-builder-pro')); ?>');
        $status.html('<div class="notice notice-info"><p><?php echo esc_js(__('Exécution du script SQL en cours...', 'pdf-builder-pro')); ?></p></div>');

        console.log('Envoi de la requête AJAX...');
        console.log('Action:', 'pdf_builder_execute_sql_repair');
        console.log('AJAX URL:', ajaxurl);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_execute_sql_repair',
                nonce: '<?php echo wp_create_nonce('pdf_builder_maintenance'); ?>'
            },
            success: function(response) {
                console.log('Réponse AJAX reçue:', response);
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
                    $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
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
                    $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
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
                    $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
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
                    $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
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
                    $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
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
                        $status.html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
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
});
</script>
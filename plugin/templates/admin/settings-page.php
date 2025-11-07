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

// Initialize
$notices = [];
$settings = get_option('pdf_builder_settings', []);

// Process form
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings_nonce'])) {
    if (wp_verify_nonce($_POST['pdf_builder_settings_nonce'], 'pdf_builder_settings')) {
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
        ];
        update_option('pdf_builder_settings', array_merge($settings, $to_save));
        $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Paramètres enregistrés avec succès.</p></div>';
        $settings = get_option('pdf_builder_settings', []);
    } else {
        $notices[] = '<div class="notice notice-error"><p><strong>✗</strong> Erreur de sécurité. Veuillez réessayer.</p></div>';
    }
}
?>

<div class="wrap">
    <h1><?php _e('⚙️ PDF Builder Pro Settings', 'pdf-builder-pro'); ?></h1>
    
    <?php foreach ($notices as $notice) echo $notice; ?>
    
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
        
        <div id="general" class="tab-content" style="display: block;">
            <h2>Paramètres Généraux</h2>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="log_level">Niveau de log</label></th>
                    <td>
                        <select id="log_level" name="log_level">
                            <option value="debug" <?php selected($settings['log_level'] ?? 'info', 'debug'); ?>>Debug</option>
                            <option value="info" <?php selected($settings['log_level'] ?? 'info', 'info'); ?>>Info</option>
                            <option value="warning" <?php selected($settings['log_level'] ?? 'info', 'warning'); ?>>Avertissement</option>
                            <option value="error" <?php selected($settings['log_level'] ?? 'info', 'error'); ?>>Erreur</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="debug_mode">Mode Debug</label></th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="debug_mode" value="1" <?php checked($settings['debug_mode'] ?? false); ?> />
                        <p class="description">Active les logs détaillés pour le débogage</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cache_enabled">Cache activé</label></th>
                    <td>
                        <input type="checkbox" id="cache_enabled" name="cache_enabled" value="1" <?php checked($settings['cache_enabled'] ?? false); ?> />
                        <p class="description">Améliore les performances en mettant en cache les données</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cache_ttl">TTL du cache (secondes)</label></th>
                    <td>
                        <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo intval($settings['cache_ttl'] ?? 3600); ?>" min="0" max="86400" />
                        <p class="description">Durée de vie du cache en secondes (défaut: 3600)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_template_size">Taille max template (octets)</label></th>
                    <td>
                        <input type="number" id="max_template_size" name="max_template_size" value="<?php echo intval($settings['max_template_size'] ?? 52428800); ?>" min="1048576" />
                        <p class="description">Taille maximale des fichiers template (défaut: 50MB)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_execution_time">Temps max d'exécution (secondes)</label></th>
                    <td>
                        <input type="number" id="max_execution_time" name="max_execution_time" value="<?php echo intval($settings['max_execution_time'] ?? 300); ?>" min="1" max="3600" />
                        <p class="description">Temps maximum pour générer un PDF (défaut: 300)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="memory_limit">Limite mémoire</label></th>
                    <td>
                        <input type="text" id="memory_limit" name="memory_limit" value="<?php echo esc_attr($settings['memory_limit'] ?? '256M'); ?>" placeholder="256M" />
                        <p class="description">Ex: 256M, 512M, 1G (défaut: 256M)</p>
                    </td>
                </tr>
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
        </div>
        
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
            
            <?php
            // Traitement du vidage du cache
            if (isset($_POST['clear_cache']) && isset($_POST['pdf_builder_clear_cache_nonce'])) {
                if (wp_verify_nonce($_POST['pdf_builder_clear_cache_nonce'], 'pdf_builder_clear_cache')) {
                    delete_transients_by_prefix('pdf_builder_');
                    $notices[] = '<div class="notice notice-success"><p><strong>✓</strong> Cache vidé avec succès !</p></div>';
                }
            }
            
            function delete_transients_by_prefix($prefix) {
                global $wpdb;
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $wpdb->esc_like('_transient_' . $prefix) . '%'
                ));
            }
            ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="cache_enabled">Cache Activé</label></th>
                    <td>
                        <input type="checkbox" id="cache_enabled" name="cache_enabled" value="1" 
                               <?php checked($settings['cache_enabled'] ?? false); ?> />
                        <p class="description">Active la mise en cache pour améliorer les performances</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="cache_ttl">TTL Cache (secondes)</label></th>
                    <td>
                        <input type="number" id="cache_ttl" name="cache_ttl" value="<?php echo intval($settings['cache_ttl'] ?? 3600); ?>" 
                               min="1" max="86400" step="60" />
                        <p class="description">Durée de vie du cache en secondes (3600 = 1 heure, 86400 = 1 jour)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="auto_save_enabled">Sauvegarde Auto</label></th>
                    <td>
                        <input type="checkbox" id="auto_save_enabled" name="auto_save_enabled" value="1" 
                               <?php checked($settings['auto_save_enabled'] ?? false); ?> />
                        <p class="description">Sauvegarde automatique pendant l'édition</p>
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
                        <input type="checkbox" id="compress_images" name="compress_images" value="1" 
                               <?php checked($settings['compress_images'] ?? false); ?> />
                        <p class="description">Compresse les images pour réduire la taille des PDFs</p>
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
                        <input type="checkbox" id="optimize_for_web" name="optimize_for_web" value="1" 
                               <?php checked($settings['optimize_for_web'] ?? false); ?> />
                        <p class="description">Réduit la taille du fichier pour une meilleure distribution web</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="enable_hardware_acceleration">Accélération Matérielle</label></th>
                    <td>
                        <input type="checkbox" id="enable_hardware_acceleration" name="enable_hardware_acceleration" value="1" 
                               <?php checked($settings['enable_hardware_acceleration'] ?? false); ?> />
                        <p class="description">Utilise les ressources GPU si disponibles</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="limit_fps">Limiter les FPS</label></th>
                    <td>
                        <input type="checkbox" id="limit_fps" name="limit_fps" value="1" 
                               <?php checked($settings['limit_fps'] ?? false); ?> />
                        <p class="description">Limite le rendu pour économiser les ressources</p>
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
                    <?php wp_nonce_field('pdf_builder_clear_cache', 'pdf_builder_clear_cache_nonce'); ?>
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
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Format & Orientation</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="default_format">Format par Défaut</label></th>
                    <td>
                        <select id="default_format" name="default_format">
                            <option value="A3" <?php selected($settings['default_format'] ?? 'A4', 'A3'); ?>>A3</option>
                            <option value="A4" <?php selected($settings['default_format'] ?? 'A4', 'A4'); ?>>A4</option>
                            <option value="A5" <?php selected($settings['default_format'] ?? 'A4', 'A5'); ?>>A5</option>
                            <option value="Letter" <?php selected($settings['default_format'] ?? 'A4', 'Letter'); ?>>Letter</option>
                            <option value="Legal" <?php selected($settings['default_format'] ?? 'A4', 'Legal'); ?>>Legal</option>
                        </select>
                        <p class="description">Format de page par défaut pour les nouveaux PDFs</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="default_orientation">Orientation par Défaut</label></th>
                    <td>
                        <select id="default_orientation" name="default_orientation">
                            <option value="portrait" <?php selected($settings['default_orientation'] ?? 'portrait', 'portrait'); ?>>Portrait</option>
                            <option value="landscape" <?php selected($settings['default_orientation'] ?? 'portrait', 'landscape'); ?>>Paysage</option>
                        </select>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Qualité & Export</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="pdf_quality">Qualité PDF</label></th>
                    <td>
                        <select id="pdf_quality" name="pdf_quality">
                            <option value="low" <?php selected($settings['pdf_quality'] ?? 'high', 'low'); ?>>Basse (fichiers plus petits)</option>
                            <option value="medium" <?php selected($settings['pdf_quality'] ?? 'high', 'medium'); ?>>Moyenne</option>
                            <option value="high" <?php selected($settings['pdf_quality'] ?? 'high', 'high'); ?>>Haute (meilleure qualité)</option>
                            <option value="ultra" <?php selected($settings['pdf_quality'] ?? 'high', 'ultra'); ?>>Ultra HD</option>
                        </select>
                        <p class="description">Plus haute = meilleure qualité mais fichiers plus gros</p>
                    </td>
                </tr>
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
                        <input type="checkbox" id="include_metadata" name="include_metadata" value="1" 
                               <?php checked($settings['include_metadata'] ?? false); ?> />
                        <p class="description">Ajoute les données de titre, auteur, date, etc.</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Optimisation & Compression</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="embed_fonts">Intégrer les Polices</label></th>
                    <td>
                        <input type="checkbox" id="embed_fonts" name="embed_fonts" value="1" 
                               <?php checked($settings['embed_fonts'] ?? false); ?> />
                        <p class="description">Inclut les polices personnalisées dans le PDF (fichiers plus gros)</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="auto_crop">Recadrage Automatique</label></th>
                    <td>
                        <input type="checkbox" id="auto_crop" name="auto_crop" value="1" 
                               <?php checked($settings['auto_crop'] ?? false); ?> />
                        <p class="description">Supprime les marges blanches automatiquement</p>
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
        </div>
        
        <div id="securite" class="tab-content" style="display: none;">
            <h2>Paramètres de Sécurité</h2>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Logging & Debugging</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="debug_mode">Mode Debug</label></th>
                    <td>
                        <input type="checkbox" id="debug_mode" name="debug_mode" value="1" 
                               <?php checked($settings['debug_mode'] ?? false); ?> />
                        <p class="description">⚠️ Active les logs détaillés. À désactiver en production !</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="log_level">Niveau de Log</label></th>
                    <td>
                        <select id="log_level" name="log_level">
                            <option value="debug" <?php selected($settings['log_level'] ?? 'info', 'debug'); ?>>Debug (tout enregistre)</option>
                            <option value="info" <?php selected($settings['log_level'] ?? 'info', 'info'); ?>>Info (événements importants)</option>
                            <option value="warning" <?php selected($settings['log_level'] ?? 'info', 'warning'); ?>>Avertissement (avertissements et erreurs)</option>
                            <option value="error" <?php selected($settings['log_level'] ?? 'info', 'error'); ?>>Erreur (erreurs seulement)</option>
                        </select>
                        <p class="description">Détermine quels événements seront enregistrés dans les logs</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Limites & Protections</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="max_template_size">Taille Max Template (octets)</label></th>
                    <td>
                        <input type="number" id="max_template_size" name="max_template_size" 
                               value="<?php echo intval($settings['max_template_size'] ?? 52428800); ?>" min="1048576" step="1048576" />
                        <p class="description">Maximum: ~<?php echo number_format(intval($settings['max_template_size'] ?? 52428800) / 1048576); ?> MB</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="max_execution_time">Temps Max d'Exécution (secondes)</label></th>
                    <td>
                        <input type="number" id="max_execution_time" name="max_execution_time" 
                               value="<?php echo intval($settings['max_execution_time'] ?? 300); ?>" min="1" max="3600" />
                        <p class="description">Temps avant timeout pour la génération PDF</p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="memory_limit">Limite Mémoire</label></th>
                    <td>
                        <input type="text" id="memory_limit" name="memory_limit" 
                               value="<?php echo esc_attr($settings['memory_limit'] ?? '256M'); ?>" 
                               placeholder="256M" />
                        <p class="description">Format: 256M, 512M, 1G. Doit être ≥ max template size</p>
                    </td>
                </tr>
            </table>
            
            <h3 style="margin-top: 30px; border-bottom: 1px solid #e5e5e5; padding-bottom: 10px;">Protection & Validation</h3>
            <table class="form-table">
                <tr>
                    <th scope="row"><label>Nonces</label></th>
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
                            <input type="checkbox" id="email_notifications_enabled" name="email_notifications_enabled" value="1" 
                                   <?php checked($email_notifications); ?> />
                            <p class="description">Active les notifications par email pour les erreurs et événements importants</p>
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
                            <input type="checkbox" id="notification_on_generation" name="notification_on_generation" value="1" />
                            <p class="description">Notifier à chaque génération de PDF réussie</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notification_on_error">Erreurs</label></th>
                        <td>
                            <input type="checkbox" id="notification_on_error" name="notification_on_error" value="1" />
                            <p class="description">Notifier en cas d'erreur lors de la génération</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="notification_on_deletion">Suppression</label></th>
                        <td>
                            <input type="checkbox" id="notification_on_deletion" name="notification_on_deletion" value="1" />
                            <p class="description">Notifier lors de la suppression de templates</p>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <button type="submit" name="submit_notifications" class="button button-primary">
                        Sauvegarder les Notifications
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
            <h2>Canvas</h2>
            <p>Configuration Canvas...</p>
        </div>
        
        <div id="templates" class="tab-content" style="display: none;">
            <h2>Templates</h2>
            <p>Gestion des templates...</p>
        </div>
        
        <div id="maintenance" class="tab-content" style="display: none;">
            <h2>Maintenance</h2>
            <p>Outils de maintenance...</p>
        </div>
        
        <div id="developpeur" class="tab-content" style="display: none;">
            <h2>Mode Développeur</h2>
            <p>Options développeur...</p>
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
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.nav-tab');
    const contents = document.querySelectorAll('.tab-content');
    
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            contents.forEach(function(c) { c.style.display = 'none'; });
            tabs.forEach(function(t) { t.classList.remove('nav-tab-active'); });
            
            const id = this.getAttribute('href').substring(1);
            document.getElementById(id).style.display = 'block';
            this.classList.add('nav-tab-active');
        });
    });
    
    // Slider pour la qualité des images
    const imageQualitySlider = document.getElementById('image_quality');
    const imageQualityValue = document.getElementById('image_quality_value');
    
    if (imageQualitySlider && imageQualityValue) {
        imageQualitySlider.addEventListener('input', function() {
            imageQualityValue.textContent = this.value + '%';
        });
    }
    
    // Gestion des rôles
    const rolesSelect = document.getElementById('pdf_builder_allowed_roles');
    const selectAllBtn = document.getElementById('select-all-roles');
    const selectCommonBtn = document.getElementById('select-common-roles');
    const selectedCountSpan = document.getElementById('selected-count');
    
    function updateCount() {
        if (rolesSelect && selectedCountSpan) {
            const selected = Array.from(rolesSelect.options).filter(opt => opt.selected).length;
            selectedCountSpan.textContent = selected;
        }
    }
    
    if (selectAllBtn && rolesSelect) {
        selectAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            Array.from(rolesSelect.options).forEach(opt => opt.selected = true);
            updateCount();
        });
    }
    
    if (selectCommonBtn && rolesSelect) {
        selectCommonBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const commonRoles = ['administrator', 'editor', 'shop_manager'];
            Array.from(rolesSelect.options).forEach(opt => {
                opt.selected = commonRoles.includes(opt.value);
            });
            updateCount();
        });
    }
    
    if (rolesSelect) {
        rolesSelect.addEventListener('change', updateCount);
    }
});
</script>

<?php wp_footer(); ?>

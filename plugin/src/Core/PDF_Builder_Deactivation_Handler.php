<?php
/**
 * PDF Builder Pro - Gestion de la désactivation du plugin
 * Affiche un modal de feedback avec option de suppression de la base de données
 */

// Éviter les inclusions multiples
if (defined('PDF_BUILDER_DEACTIVATION_HANDLER_LOADED')) {
    return;
}
define('PDF_BUILDER_DEACTIVATION_HANDLER_LOADED', true);

/**
 * Vérifier le paramètre de désactivation TRÈS TÔT, avant les redirects
 */
if (!function_exists('pdf_builder_check_deactivation_action')) {
    function pdf_builder_check_deactivation_action() {
        // Vérifier si c'est une page d'admin et si nous avons le paramètre
        if (!is_admin() || !isset($_GET['pdf_builder_db_action'])) {
            return;
        }

        // Vérifier les permissions
        if (!current_user_can('manage_options')) {
            return;
        }

        $action = sanitize_text_field($_GET['pdf_builder_db_action']);
        
        // Sauvegarder le choix de l'utilisateur
        if ($action === 'delete') {
            update_option('pdf_builder_delete_on_deactivate', true);
        } else if ($action === 'keep') {
            update_option('pdf_builder_delete_on_deactivate', false);
        }
    }
    
    // Hook très tôt, avant les redirects
    add_action('plugins_loaded', 'pdf_builder_check_deactivation_action', 1);
}

/**
 * Ajouter le script de gestion de la désactivation sur la page des plugins
 */
if (!function_exists('pdf_builder_init_deactivation_modal')) {
    function pdf_builder_init_deactivation_modal() {
        // Vérifier qu'on est admin
        if (!is_admin()) {
            return;
        }
        
        // Ne s'afficher que sur la page des plugins
        $current_screen = function_exists('get_current_screen') ? get_current_screen() : null;
        if (!$current_screen || $current_screen->id !== 'plugins') {
            return;
        }
        
        // Ajouter le HTML et le script sur le footer de l'admin
        add_action('admin_footer', 'pdf_builder_add_deactivation_modal');
    }
    
    add_action('admin_init', 'pdf_builder_init_deactivation_modal', 10);
}

/**
 * Afficher le modal HTML et le JavaScript pour la désactivation
 */
if (!function_exists('pdf_builder_add_deactivation_modal')) {
    function pdf_builder_add_deactivation_modal() {
        ?>
        <!-- Modal de feedback PDF Builder Pro -->
        <div id="pdf-builder-deactivation-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 8px; max-width: 500px; width: 90%; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 24px; color: #333;">
                        <?php _e('Avant de désactiver...', 'pdf-builder-pro'); ?>
                    </h2>
                    <button id="pdf-builder-deactivation-close" type="button" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666;">×</button>
                </div>

                <!-- Content -->
                <div style="margin-bottom: 25px; color: #666; line-height: 1.6;">
                    <p><?php _e('Bonjour ! Avant de désactiver PDF Builder Pro, nous aimerions en savoir plus sur votre décision.', 'pdf-builder-pro'); ?></p>
                    
                    <p style="margin-top: 15px;">
                        <strong><?php _e('Souhaitez-vous conserver vos données dans la base de données ?', 'pdf-builder-pro'); ?></strong>
                    </p>

                    <!-- Options de radio -->
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; margin-top: 12px;">
                        <!-- Option 1: Conserver les données -->
                        <label style="display: flex; align-items: flex-start; margin-bottom: 15px; cursor: pointer;">
                            <input type="radio" name="pdf_builder_db_action" id="pdf_builder_keep_data" value="keep" checked style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer; margin-top: 2px; flex-shrink: 0;">
                            <span style="font-size: 14px; color: #333;">
                                <strong><?php _e('Conserver les données', 'pdf-builder-pro'); ?></strong>
                                <br/>
                                <span style="color: #999; font-size: 12px;">
                                    <?php _e('Les templates et paramètres seront sauvegardés. Vous pourrez réactiver le plugin plus tard.', 'pdf-builder-pro'); ?>
                                </span>
                            </span>
                        </label>

                        <!-- Option 2: Supprimer les données -->
                        <label style="display: flex; align-items: flex-start; cursor: pointer;">
                            <input type="radio" name="pdf_builder_db_action" id="pdf_builder_delete_data" value="delete" style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer; margin-top: 2px; flex-shrink: 0;">
                            <span style="font-size: 14px; color: #333;">
                                <strong><?php _e('Supprimer toutes les données', 'pdf-builder-pro'); ?></strong>
                                <br/>
                                <span style="color: #999; font-size: 12px;">
                                    <?php _e('Tous les templates et paramètres du plugin seront supprimés définitivement.', 'pdf-builder-pro'); ?>
                                </span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Actions -->
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button id="pdf-builder-deactivation-cancel" type="button" class="button button-secondary" style="padding: 8px 20px;">
                        <?php _e('Annuler', 'pdf-builder-pro'); ?>
                    </button>
                    <button id="pdf-builder-deactivation-proceed" type="button" class="button button-primary" style="padding: 8px 20px; background: #667eea; border-color: #667eea; color: white; cursor: pointer;">
                        <?php _e('Continuer la désactivation', 'pdf-builder-pro'); ?>
                    </button>
                </div>
            </div>
        </div>

        <script type="text/javascript">
        (function() {
            'use strict';
            
            let deactivationLink = null;
            let selectedAction = 'keep'; // Par défaut: conserver les données

            // Attendre que jQuery soit disponible
            var checkJQuery = setInterval(function() {
                if (typeof jQuery === 'undefined') {
                    return;
                }
                
                clearInterval(checkJQuery);

                jQuery(document).ready(function($) {
                    // Trouver le lien de désactivation pour PDF Builder Pro
                    // Chercher dans la colonne "Description"
                    var foundLink = null;
                    
                    // Stratégie 1: Chercher par aria-label
                    var links = $('tr').filter(function() {
                        var text = $(this).find('strong').text();
                        return text.indexOf('PDF Builder') > -1;
                    }).find('a');
                    
                    links.each(function() {
                        var href = $(this).attr('href');
                        var label = $(this).attr('aria-label') || '';
                        
                        if (href && href.indexOf('action=deactivate') > -1 && 
                            (label.indexOf('PDF Builder') > -1 || label.indexOf('Désactiver') > -1)) {
                            foundLink = $(this);
                            deactivationLink = href;
                            return false; // break
                        }
                    });

                    if (!foundLink || !deactivationLink) {
                        return; // Lien non trouvé
                    }

                    // Intercepter le clic sur le lien de désactivation
                    foundLink.on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        $('#pdf-builder-deactivation-modal').css('display', 'flex');
                    });

                    // Bouton "Annuler"
                    $('#pdf-builder-deactivation-cancel').on('click', function() {
                        $('#pdf-builder-deactivation-modal').css('display', 'none');
                    });

                    // Bouton "Fermer" (X)
                    $('#pdf-builder-deactivation-close').on('click', function() {
                        $('#pdf-builder-deactivation-modal').css('display', 'none');
                    });

                    // Bouton "Continuer la désactivation"
                    $('#pdf-builder-deactivation-proceed').on('click', function() {
                        selectedAction = $('input[name="pdf_builder_db_action"]:checked').val() || 'keep';
                        
                        // Modifier l'URL de désactivation pour inclure notre paramètre
                        var finalUrl = deactivationLink;
                        var separator = (finalUrl.indexOf('?') === -1) ? '?' : '&';
                        finalUrl += separator + 'pdf_builder_db_action=' + encodeURIComponent(selectedAction);
                        
                        // Rediriger vers la désactivation
                        window.location.href = finalUrl;
                    });

                    // Mettre à jour la valeur sélectionnée
                    $('input[name="pdf_builder_db_action"]').on('change', function() {
                        selectedAction = $(this).val();
                    });

                    // Fermer le modal en cliquant sur le fond
                    $('#pdf-builder-deactivation-modal').on('click', function(e) {
                        if (e.target === this) {
                            $(this).css('display', 'none');
                        }
                    });
                });
            }, 50);
        })();
        </script>
        <?php
    }
}

/**
 * Exécuter la suppression des données si l'option est activée
 */
if (!function_exists('pdf_builder_execute_deactivation_action')) {
    function pdf_builder_execute_deactivation_action() {
        if (!get_option('pdf_builder_delete_on_deactivate', false)) {
            return; // Ne pas supprimer
        }

        global $wpdb;

        try {
            // Récupérer les noms des tables PDF Builder
            $prefix = $wpdb->prefix;
            
            // Liste des tables potentielles à supprimer
            $tables = array(
                $prefix . 'pdf_builder_templates',
                $prefix . 'pdf_builder_elements',
                $prefix . 'pdf_builder_settings',
                $prefix . 'pdf_builder_logs',
                $prefix . 'pdf_builder_analytics',
                $prefix . 'pdf_builder_security_logs',
            );

            // Supprimer les tables qui existent
            foreach ($tables as $table) {
                // Vérifier si la table existe
                $table_exists = $wpdb->get_var(
                    $wpdb->prepare("SHOW TABLES LIKE %s", $table)
                );
                
                if ($table_exists === $table) {
                    $wpdb->query("DROP TABLE IF EXISTS `$table`");
                }
            }

            // Supprimer les options du plugin (via SQL pour plus de sécurité)
            $wpdb->query(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_%' OR option_name LIKE 'pdf-builder-%'"
            );

            // Supprimer les user meta du plugin
            $wpdb->query(
                "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'pdf_builder_%' OR meta_key LIKE 'pdf-builder-%'"
            );

            // Supprimer les post meta du plugin
            $wpdb->query(
                "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'pdf_builder_%' OR meta_key LIKE 'pdf-builder-%'"
            );

            error_log('[PDF Builder Pro] Database and options deleted successfully on deactivation');

        } catch (Exception $e) {
            error_log('[PDF Builder Pro] Error deleting database during deactivation: ' . $e->getMessage());
        }

        // Nettoyer l'option temporaire
        delete_option('pdf_builder_delete_on_deactivate');
    }
    
    // Hook à la désactivation
    add_action('pdf_builder_deactivate', 'pdf_builder_execute_deactivation_action', 10);
}
?>


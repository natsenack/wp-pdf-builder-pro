<?php
/**
 * PDF Builder Pro - Deactivation Handler
 */
if (defined('PDF_BUILDER_DEACTIVATION_HANDLER_LOADED')) {
    return;
}
define('PDF_BUILDER_DEACTIVATION_HANDLER_LOADED', true);

add_action('plugins_loaded', function() {
    if (!is_admin() || !isset($_GET['pdf_builder_db_action'])) {
        return;
    }
    if (!current_user_can('manage_options')) {
        return;
    }
    $action = sanitize_text_field($_GET['pdf_builder_db_action']);
    if ($action === 'delete') {
        update_option('pdf_builder_delete_on_deactivate', true);
    } else if ($action === 'keep') {
        update_option('pdf_builder_delete_on_deactivate', false);
    }
}, 1);

add_action('admin_enqueue_scripts', function() {
    $current_screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$current_screen || $current_screen->id !== 'plugins') {
        return;
    }
    add_action('admin_footer', function() {
        ?>
        <!-- PDF Builder Pro Deactivation Modal v2.0 -->
        <div id="pdf-builder-deactivation-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; flex-wrap: wrap; align-content: center; justify-content: center;">
            <div style="background: white; border-radius: 8px; max-width: 600px; width: 90%; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.3); max-height: 90vh; overflow-y: auto;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h2 style="margin: 0; font-size: 24px; color: #333;">Avant de désactiver...</h2>
                    <button id="pdf-builder-deactivation-close" type="button" style="background: none; border: none; font-size: 28px; cursor: pointer; color: #666;">×</button>
                </div>
                <div style="margin-bottom: 25px; color: #666; line-height: 1.6;">
                    <p>Bonjour ! Avant de désactiver PDF Builder Pro, nous aimerions en savoir plus sur votre décision.</p>
                    <p style="margin-top: 15px;"><strong>Souhaitez-vous conserver vos données ?</strong></p>
                    <div style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 6px; padding: 15px; margin-top: 12px;">
                        <label style="display: flex; align-items: flex-start; margin-bottom: 15px; cursor: pointer;">
                            <input type="radio" name="pdf_builder_db_action" value="keep" checked style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer; margin-top: 2px;">
                            <span style="font-size: 14px; color: #333;">
                                <strong>Conserver les données</strong>
                                <br/>
                                <span style="color: #999; font-size: 12px;">Les templates et paramètres seront sauvegardés.</span>
                            </span>
                        </label>
                        <label style="display: flex; align-items: flex-start; cursor: pointer;">
                            <input type="radio" name="pdf_builder_db_action" value="delete" style="margin-right: 10px; width: 18px; height: 18px; cursor: pointer; margin-top: 2px;">
                            <span style="font-size: 14px; color: #333;">
                                <strong>Supprimer toutes les données</strong>
                                <br/>
                                <span style="color: #999; font-size: 12px;">Tous les données seront supprimées.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Section Feedback -->
                <div style="background: #f0f4ff; border: 1px solid #d0e0ff; border-radius: 6px; padding: 15px; margin-bottom: 20px;">
                    <p style="margin: 0 0 15px 0; font-size: 13px; color: #333;"><strong>Si vous avez un moment, veuillez nous indiquer pourquoi vous désactivez :</strong></p>
                    <div style="max-height: 200px; overflow-y: auto;">
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="not_working" style="margin-right: 8px; cursor: pointer;">
                            <span>Le plugin ne fonctionne pas</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="not_as_expected" style="margin-right: 8px; cursor: pointer;">
                            <span>Le plugin n'a pas fonctionné comme prévu</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="stopped_working" style="margin-right: 8px; cursor: pointer;">
                            <span>Le plugin a soudainement cessé de fonctionner</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="broke_site" style="margin-right: 8px; cursor: pointer;">
                            <span>Le plugin a cassé mon site</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="didnt_understand" style="margin-right: 8px; cursor: pointer;">
                            <span>Je n'ai pas compris comment le faire fonctionner</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="better_plugin" style="margin-right: 8px; cursor: pointer;">
                            <span>J'ai trouvé un meilleur plugin</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="missing_feature" style="margin-right: 8px; cursor: pointer;">
                            <span>Le plugin est génial, mais j'ai besoin d'une fonctionnalité spécifique</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="no_longer_need" style="margin-right: 8px; cursor: pointer;">
                            <span>Je n'ai plus besoin du plugin</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="temporary" style="margin-right: 8px; cursor: pointer;">
                            <span>C'est une désactivation temporaire, je débogue un problème</span>
                        </label>
                        <label style="display: flex; align-items: center; margin-bottom: 0; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="other" style="margin-right: 8px; cursor: pointer;">
                            <span>Autre</span>
                        </label>
                    </div>
                    <p style="margin: 12px 0 0 0; font-size: 12px; color: #999;">Vos retours nous aident à améliorer le produit.</p>
                </div>

                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button id="pdf-builder-deactivation-cancel" type="button" class="button button-secondary" style="padding: 8px 20px;">Annuler</button>
                    <button id="pdf-builder-deactivation-proceed" type="button" class="button button-primary" style="padding: 8px 20px; background: #667eea; border-color: #667eea; color: white; cursor: pointer;">Continuer</button>
                </div>
            </div>
        </div>
        <style>
        #pdf-builder-deactivation-modal {
            display: none !important;
        }
        #pdf-builder-deactivation-modal.show {
            display: flex !important;
        }
        </style>
        <script>
        (function() {
            if (typeof jQuery === 'undefined') return;
            var $ = jQuery, link = null;
            $(document).ready(function() {
                $('a[href*="action=deactivate"]').each(function() {
                    if ($(this).attr('href').indexOf('wp-pdf-builder-pro') > -1) {
                        link = $(this).attr('href');
                        $(this).on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            $('#pdf-builder-deactivation-modal').addClass('show');
                            return false;
                        });
                    }
                });
                $('#pdf-builder-deactivation-cancel, #pdf-builder-deactivation-close').on('click', function() {
                    $('#pdf-builder-deactivation-modal').removeClass('show');
                });
                $('#pdf-builder-deactivation-proceed').on('click', function() {
                    var action = $('input[name="pdf_builder_db_action"]:checked').val() || 'keep';
                    if (!link) return;
                    var sep = link.indexOf('?') === -1 ? '?' : '&';
                    window.location.href = link + sep + 'pdf_builder_db_action=' + action;
                });
            });
        })();
        </script>
        <?php
    }, 999);
}, 10);

add_action('pdf_builder_deactivate', function() {
    if (!get_option('pdf_builder_delete_on_deactivate', false)) {
        return;
    }
    global $wpdb;
    $prefix = $wpdb->prefix;
    $tables = array(
        $prefix . 'pdf_builder_templates',
        $prefix . 'pdf_builder_elements',
        $prefix . 'pdf_builder_settings',
        $prefix . 'pdf_builder_logs',
        $prefix . 'pdf_builder_analytics',
        $prefix . 'pdf_builder_security_logs',
    );
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS `$table`");
    }
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_%' OR option_name LIKE 'pdf-builder-%'");
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'pdf_builder_%'");
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'pdf_builder_%'");
    delete_option('pdf_builder_delete_on_deactivate');
}, 10);
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
            let modalShown = false;

            // Fonction pour initialiser le modal
            function initializeModal() {
                if (typeof jQuery === 'undefined') {
                    return false;
                }
                
                var $ = jQuery;

                // Trouver le lien de désactivation pour PDF Builder Pro
                // Chercher tous les liens de déactivation
                var foundLink = null;
                
                // Chercher par le plugin slug dans l'attribut href ou data
                $('a[href*="action=deactivate"]').each(function() {
                    var href = $(this).attr('href');
                    var $row = $(this).closest('tr');
                    var pluginName = $row.find('[data-plugin]').attr('data-plugin') || '';
                    var rowText = $row.text();
                    
                    // Vérifier si c'est notre plugin
                    if (href.indexOf('wp-pdf-builder-pro/pdf-builder-pro.php') > -1 || 
                        pluginName.indexOf('pdf-builder-pro') > -1 ||
                        pluginName.indexOf('wp-pdf-builder-pro') > -1 ||
                        rowText.indexOf('PDF Builder') > -1) {
                        foundLink = $(this);
                        deactivationLink = href;
                        return false; // break
                    }
                });

                if (!foundLink || !deactivationLink) {
                    console.warn('[PDF Builder] Lien de désactivation non trouvé');
                    return false;
                }

                // Intercepter le clic sur le lien de désactivation
                foundLink.on('click', function(e) {
                    if (modalShown) return; // Éviter les clics multiples
                    
                    e.preventDefault();
                    e.stopPropagation();
                    modalShown = true;
                    $('#pdf-builder-deactivation-modal').css('display', 'flex');
                    
                    console.log('[PDF Builder] Modal de désactivation affichée');
                });

                // Bouton "Annuler"
                $('#pdf-builder-deactivation-cancel').on('click', function() {
                    $('#pdf-builder-deactivation-modal').css('display', 'none');
                    modalShown = false;
                });

                // Bouton "Fermer" (X)
                $('#pdf-builder-deactivation-close').on('click', function() {
                    $('#pdf-builder-deactivation-modal').css('display', 'none');
                    modalShown = false;
                });

                // Bouton "Continuer la désactivation"
                $('#pdf-builder-deactivation-proceed').on('click', function() {
                    selectedAction = $('input[name="pdf_builder_db_action"]:checked').val() || 'keep';
                    
                    // Modifier l'URL de désactivation pour inclure notre paramètre
                    var finalUrl = deactivationLink;
                    var separator = (finalUrl.indexOf('?') === -1) ? '?' : '&';
                    finalUrl += separator + 'pdf_builder_db_action=' + encodeURIComponent(selectedAction);
                    
                    console.log('[PDF Builder] Désactivation avec action:', selectedAction);
                    console.log('[PDF Builder] URL de désactivation:', finalUrl);
                    
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
                        modalShown = false;
                    }
                });
                
                return true;
            }

            // Attendre que jQuery soit disponible - avec timeout court
            var attempts = 0;
            var checkJQuery = setInterval(function() {
                attempts++;
                if (typeof jQuery === 'undefined') {
                    if (attempts > 100) { // Timeout après 5 secondes
                        clearInterval(checkJQuery);
                        console.warn('[PDF Builder] jQuery non disponible après timeout');
                    }
                    return;
                }
                
                clearInterval(checkJQuery);

                jQuery(document).ready(function($) {
                    if (!initializeModal()) {
                        // Si la première tentative échoue, réessayer après 500ms
                        setTimeout(function() {
                            initializeModal();
                        }, 500);
                    }
                });

/**
 * Exécuter la suppression des données si l'option est activée
 */
add_action('pdf_builder_deactivate', function() {
    if (!get_option('pdf_builder_delete_on_deactivate', false)) {
        return;
    }

    global $wpdb;

    try {
        $prefix = $wpdb->prefix;
        
        // Tables à supprimer
        $tables = array(
            $prefix . 'pdf_builder_templates',
            $prefix . 'pdf_builder_elements',
            $prefix . 'pdf_builder_settings',
            $prefix . 'pdf_builder_logs',
            $prefix . 'pdf_builder_analytics',
            $prefix . 'pdf_builder_security_logs',
        );

        // Supprimer les tables
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS `$table`");
        }

        // Supprimer les options
        $wpdb->query(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'pdf_builder_%' OR option_name LIKE 'pdf-builder-%'"
        );

        // Supprimer les user meta
        $wpdb->query(
            "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'pdf_builder_%' OR meta_key LIKE 'pdf-builder-%'"
        );

        // Supprimer les post meta
        $wpdb->query(
            "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'pdf_builder_%' OR meta_key LIKE 'pdf-builder-%'"
        );

        error_log('[PDF Builder Pro] Data deleted on deactivation');
    } catch (Exception $e) {
        error_log('[PDF Builder Pro] Error: ' . $e->getMessage());
    }

    delete_option('pdf_builder_delete_on_deactivate');
}, 10);
?>

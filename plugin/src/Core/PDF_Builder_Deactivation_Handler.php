<?php
/**
 * PDF Builder Pro - Deactivation Handler
 */
if (defined('PDF_BUILDER_DEACTIVATION_HANDLER_LOADED')) {
    return;
}
define('PDF_BUILDER_DEACTIVATION_HANDLER_LOADED', true);

// Register deactivation hook
add_action('plugins_loaded', function() {
    if (function_exists('register_deactivation_hook')) {
        register_deactivation_hook(dirname(dirname(dirname(__FILE__))) . '/pdf-builder-pro.php', function() {
            if (get_option('pdf_builder_delete_on_deactivate', false)) {
                do_action('pdf_builder_deactivate');
            }
        });
    }
}, 0);

// Handle deactivation parameters
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
    } else {
        update_option('pdf_builder_delete_on_deactivate', false);
    }
    
    if (isset($_GET['pdf_builder_reason'])) {
        update_option('pdf_builder_deactivation_reason', sanitize_text_field($_GET['pdf_builder_reason']));
    }
}, 1);

// Display modal on plugins page
add_action('admin_enqueue_scripts', function() {
    $screen = get_current_screen();
    if (!$screen || $screen->id !== 'plugins') {
        return;
    }
    
    add_action('admin_footer', function() {
        ?>
        <div id="pdf-builder-deactivation-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
            <div style="background: white; border-radius: 12px; max-width: 550px; width: 95%; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); position: relative; max-height: 85vh; overflow-y: auto;">
                
                <button id="pdf-builder-modal-close" type="button" style="position: absolute; top: 15px; right: 15px; background: none; border: none; font-size: 32px; cursor: pointer; color: #999; line-height: 1;">×</button>
                
                <h2 style="margin: 0 0 10px 0; font-size: 26px; color: #222; font-weight: 600;">Avant de désactiver</h2>
                <p style="margin: 0 0 30px 0; color: #666; font-size: 14px; line-height: 1.6;">Aidez-nous à améliorer le plugin en nous indiquant votre décision.</p>
                
                <!-- Feedback Section -->
                <div style="background: #f0f4ff; border: 1px solid #d0e0ff; border-radius: 8px; padding: 20px; margin-bottom: 25px;">
                    <p style="margin: 0 0 15px 0; font-weight: 600; color: #333; font-size: 14px;">Pourquoi désactivez-vous ? (optionnel)</p>
                    
                    <div style="max-height: 180px; overflow-y: auto; padding-right: 5px;">
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="not_working" style="margin-right: 8px; cursor: pointer;">
                            Le plugin ne fonctionne pas
                        </label>
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="not_as_expected" style="margin-right: 8px; cursor: pointer;">
                            N'a pas fonctionné comme prévu
                        </label>
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="stopped_working" style="margin-right: 8px; cursor: pointer;">
                            A cessé de fonctionner soudainement
                        </label>
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="broke_site" style="margin-right: 8px; cursor: pointer;">
                            A cassé mon site
                        </label>
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="didnt_understand" style="margin-right: 8px; cursor: pointer;">
                            Je n'ai pas compris comment l'utiliser
                        </label>
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="better_plugin" style="margin-right: 8px; cursor: pointer;">
                            J'ai trouvé un meilleur plugin
                        </label>
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="missing_feature" style="margin-right: 8px; cursor: pointer;">
                            Manque de fonctionnalités
                        </label>
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="no_longer_need" style="margin-right: 8px; cursor: pointer;">
                            Je n'en ai plus besoin
                        </label>
                        <label style="display: block; margin-bottom: 10px; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="temporary" style="margin-right: 8px; cursor: pointer;">
                            Désactivation temporaire
                        </label>
                        <label style="display: block; cursor: pointer; font-size: 13px;">
                            <input type="radio" name="pdf_builder_reason" value="other" style="margin-right: 8px; cursor: pointer;">
                            Autre
                        </label>
                    </div>
                    
                    <!-- Textarea for custom reason -->
                    <div id="pdf-builder-other-reason" style="display: none; margin-top: 15px;">
                        <textarea id="pdf-builder-custom-reason" placeholder="Veuillez nous expliquer..." style="width: 100%; min-height: 100px; padding: 10px; border: 1px solid #d0e0ff; border-radius: 4px; font-family: Arial, sans-serif; font-size: 13px; resize: vertical;"></textarea>
                    </div>
                </div>
                
                <!-- Buttons -->
                <div style="display: flex; gap: 10px; justify-content: space-between;">
                    <button id="pdf-builder-btn-skip" type="button" class="button" style="padding: 10px 20px; cursor: pointer; background: transparent; border: 1px solid transparent; color: #aaa; opacity: 0.4; font-size: 12px; font-weight: normal;">Passer & Désactiver</button>
                    <div style="display: flex; gap: 10px;">
                        <button id="pdf-builder-btn-cancel" type="button" class="button button-secondary" style="padding: 10px 20px; cursor: pointer;">Annuler</button>
                        <button id="pdf-builder-btn-proceed" type="button" class="button button-primary" style="padding: 10px 20px; background: #667eea; border-color: #667eea; color: white; cursor: not-allowed; opacity: 0.6;">Désactiver</button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        (function() {
            if (typeof jQuery === 'undefined') return;
            
            var $ = jQuery;
            var deactivateLink = null;
            
            $(document).ready(function() {
                // Find and intercept deactivate link
                $('a[href*="action=deactivate"]').each(function() {
                    var href = $(this).attr('href');
                    if (href.indexOf('wp-pdf-builder-pro') > -1) {
                        deactivateLink = href;
                        
                        $(this).on('click', function(e) {
                            e.preventDefault();
                            e.stopPropagation();
                            $('#pdf-builder-deactivation-modal').css('display', 'flex');
                            return false;
                        });
                    }
                });
                
                // Toggle textarea when "Autre" is selected + Update button style
                $('input[name="pdf_builder_reason"]').on('change', function() {
                    if ($(this).val() === 'other') {
                        $('#pdf-builder-other-reason').show();
                    } else {
                        $('#pdf-builder-other-reason').hide();
                    }
                    
                    // Change button cursor and opacity when reason is selected
                    var reasonSelected = $('input[name="pdf_builder_reason"]:checked').length > 0;
                    if (reasonSelected) {
                        $('#pdf-builder-btn-proceed').css({
                            'cursor': 'pointer',
                            'opacity': '1'
                        });
                    } else {
                        $('#pdf-builder-btn-proceed').css({
                            'cursor': 'default',
                            'opacity': '0.8'
                        });
                    }
                });
                
                // Close handlers
                $('#pdf-builder-modal-close, #pdf-builder-btn-cancel').on('click', function() {
                    $('#pdf-builder-deactivation-modal').css('display', 'none');
                });
                
                // Skip button (directly deactivate without feedback)
                $('#pdf-builder-btn-skip').on('click', function() {
                    if (!deactivateLink) return;
                    window.location.href = deactivateLink;
                });
                
                // Proceed button - SIMPLE ET DIRECT
                $('#pdf-builder-btn-proceed').on('click', function() {
                    // Vérifier qu'une raison est sélectionnée
                    var reasonSelected = $('input[name="pdf_builder_reason"]:checked').length > 0;
                    if (!reasonSelected) {
                        return false;
                    }
                    
                    var reason = $('input[name="pdf_builder_reason"]:checked').val() || 'no_reason';
                    
                    // Si "Autre" est sélectionné, utiliser le texte personnalisé
                    if (reason === 'other') {
                        var customText = $('#pdf-builder-custom-reason').val().trim();
                        reason = customText || 'autre';
                    }
                    
                    // Construire l'URL avec la raison
                    if (!deactivateLink) {
                        return false;
                    }
                    
                    var url = deactivateLink;
                    var separator = url.indexOf('?') === -1 ? '?' : '&';
                    url += separator + 'pdf_builder_reason=' + encodeURIComponent(reason);
                    
                    // Rediriger
                    window.location.href = url;
                });
            });
        })();
        </script>
        <?php
    }, 999);
});

// Handle data deletion
add_action('pdf_builder_deactivate', function() {
    global $wpdb;
    
    $tables = array(
        $wpdb->prefix . 'pdf_builder_templates',
        $wpdb->prefix . 'pdf_builder_elements',
        $wpdb->prefix . 'pdf_builder_settings',
        $wpdb->prefix . 'pdf_builder_logs',
        $wpdb->prefix . 'pdf_builder_analytics',
        $wpdb->prefix . 'pdf_builder_security_logs',
    );
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS `$table`");
    }
    
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%pdf_builder%' OR option_name LIKE '%pdf-builder%'");
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%pdf_builder%'");
    $wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE '%pdf_builder%'");
    
    delete_option('pdf_builder_delete_on_deactivate');
});

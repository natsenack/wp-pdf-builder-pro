<?php
if (!defined('ABSPATH')) exit('No direct access');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');
?>
<div class="pdf-builder-settings-section">
    <h2>Paramètres système</h2>

    <div class="pdf-builder-card">
        <h3>Informations système</h3>
        <p>Cette section contient les paramètres système du plugin PDF Builder Pro.</p>

        <div class="system-info" style="margin-top: 20px;">
            <button type="button" id="system_info_btn" class="button">ℹ️ Infos Système</button>
            <div id="system_info_result" style="margin-top: 10px; display: none; border: 1px solid #ddd; padding: 10px; background: #f9f9f9; font-family: monospace; font-size: 12px;">
                <p>Chargement des informations système...</p>
            </div>
        </div>
    </div>

    <div class="pdf-builder-card">
        <h3>Cache système</h3>
        <p>Gestion du cache du plugin.</p>

        <div class="cache-actions" style="margin-top: 20px;">
            <button type="button" id="clear_cache_btn" class="button">🗑️ Vider le cache</button>
            <div id="cache_result" style="margin-top: 10px;"></div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Informations système
    $('#system_info_btn').on('click', function() {
        var $result = $('#system_info_result');
        $result.show();
        $result.html('<p>Chargement des informations système...</p>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_system_info',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<pre>' + response.data + '</pre>');
                } else {
                    $result.html('<p style="color: red;">Erreur: ' + response.data + '</p>');
                }
            },
            error: function() {
                $result.html('<p style="color: red;">Erreur de communication avec le serveur</p>');
            }
        });
    });

    // Vider le cache
    $('#clear_cache_btn').on('click', function() {
        var $result = $('#cache_result');
        $(this).prop('disabled', true).text('Vider le cache...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_clear_cache',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $result.html('<div class="notice notice-success"><p>✅ Cache vidé avec succès!</p></div>');
                } else {
                    $result.html('<div class="notice notice-error"><p>❌ Erreur: ' + response.data + '</p></div>');
                }
            },
            error: function() {
                $result.html('<div class="notice notice-error"><p>❌ Erreur de communication avec le serveur</p></div>');
            },
            complete: function() {
                $('#clear_cache_btn').prop('disabled', false).text('🗑️ Vider le cache');
            }
        });
    });
});

</script>

<style>
.pdf-builder-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.pdf-builder-card h3 {
    margin-top: 0;
    color: #23282d;
}
</style>

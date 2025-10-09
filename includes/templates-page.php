<?php
/**
 * Templates Page - PDF Builder Pro
 * Gestion des templates PDF
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}
?>

<div class="wrap">
    <h1><?php _e('📄 Gestion des Templates PDF', 'pdf-builder-pro'); ?></h1>

    <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
        <h2><?php _e('Templates Disponibles', 'pdf-builder-pro'); ?></h2>

        <div style="margin: 20px 0;">
            <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor&template_id=0'); ?>" class="button button-primary">
                ➕ <?php _e('Créer un nouveau template', 'pdf-builder-pro'); ?>
            </a>
        </div>

        <div id="templates-list" style="margin-top: 20px;">
            <p><?php _e('Chargement des templates...', 'pdf-builder-pro'); ?></p>
            <!-- Les templates seront chargés dynamiquement ici -->
        </div>

        <div id="no-templates" style="display: none; text-align: center; padding: 40px; color: #666;">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
            <h3><?php _e('Aucun template trouvé', 'pdf-builder-pro'); ?></h3>
            <p><?php _e('Créez votre premier template pour commencer à concevoir des PDF personnalisés.', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<script>
(function($) {
    'use strict';

    // Chargement des templates (temporaire - simulation)
    $(document).ready(function() {
        setTimeout(function() {
            $('#templates-list').html('<p><em>Fonctionnalité en développement - Les templates seront bientôt disponibles.</em></p>');
        }, 500);
    });

})(jQuery);
</script>
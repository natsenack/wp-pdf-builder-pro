<?php
/**
 * Template pour le conteneur de toasts
 *
 * @package PDF_Builder
 * @since 2.0.0
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Conteneur pour les notifications toast -->
<div id="pdf-builder-toast-container" class="pdf-builder-toast-container" style="display: none;">
    <!-- Template pour les toasts individuels -->
    <template id="pdf-builder-toast-template">
        <div class="pdf-builder-notification pdf-builder-notification-toast" role="alert" aria-live="assertive">
            <div class="pdf-builder-notification-icon">
                <span class="pdf-builder-notification-icon-symbol" aria-hidden="true"></span>
            </div>
            <div class="pdf-builder-notification-content">
                <div class="pdf-builder-notification-message"></div>
            </div>
            <button class="pdf-builder-notification-close" type="button" aria-label="<?php esc_attr_e('Fermer la notification', 'pdf-builder-pro'); ?>">
                <span aria-hidden="true">×</span>
            </button>
        </div>
    </template>
</div>
<?php
/**
 * Template pour le conteneur de toasts
 *
 * @package PDF_Builder
 * @since 1.0.0
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}
?>
<!-- Conteneur pour les notifications toast -->
<div id="pdf-builder-toast-container"></div>

<!-- Template pour les toasts (caché) -->
<template id="pdf-builder-toast-template">
    <div class="pdf-builder-notification">
        <span class="pdf-builder-notification-icon"></span>
        <span class="pdf-builder-notification-message"></span>
        <span class="pdf-builder-notification-close"></span>
    </div>
</template>
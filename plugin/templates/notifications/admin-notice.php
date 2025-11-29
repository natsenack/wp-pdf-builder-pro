<?php
/**
 * Template pour les notifications admin WordPress
 *
 * @package PDF_Builder
 * @since 2.0.0
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Variables attendues : $notification (array avec id, message, type, dismissible)
if (!isset($notification) || !is_array($notification)) {
    return;
}

$type_class = 'notice-' . esc_attr($notification['type']);
if ($notification['type'] === 'error') {
    $type_class = 'notice-error';
} elseif ($notification['type'] === 'warning') {
    $type_class = 'notice-warning';
} elseif ($notification['type'] === 'success') {
    $type_class = 'notice-success';
} elseif ($notification['type'] === 'info') {
    $type_class = 'notice-info';
}

$dismissible_class = $notification['dismissible'] ? ' is-dismissible' : '';
?>
<div id="<?php echo esc_attr($notification['id']); ?>" class="notice <?php echo $type_class; ?><?php echo $dismissible_class; ?>">
    <p><?php echo wp_kses_post($notification['message']); ?></p>
    <?php if ($notification['dismissible']): ?>
        <button type="button" class="notice-dismiss" onclick="pdfBuilderNotifications.dismiss('<?php echo esc_attr($notification['id']); ?>')">
            <span class="screen-reader-text"><?php esc_html_e('Fermer cette notification', 'pdf-builder-pro'); ?></span>
        </button>
    <?php endif; ?>
</div>
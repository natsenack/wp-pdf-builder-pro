<?php
/**
 * Template pour une notification admin
 *
 * @package PDF_Builder
 * @since 1.0.0
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Variables attendues : $notification (array avec 'type', 'message', 'dismissible')
if (!isset($notification) || !is_array($notification)) {
    return;
}

$class = 'notice notice-' . esc_attr($notification['type']);
if (!empty($notification['dismissible'])) {
    $class .= ' is-dismissible';
}

$allowed_html = [
    'strong' => [],
    'em' => [],
    'br' => [],
    'a' => [
        'href' => [],
        'target' => [],
        'rel' => []
    ],
    'span' => [
        'class' => []
    ]
];
?>
<!-- Template de notice d'administration WordPress -->
<div class="<?php echo esc_attr($class); ?> pdf-builder-notice">
    <p><?php echo wp_kses($notification['message'], $allowed_html); ?></p>
</div>
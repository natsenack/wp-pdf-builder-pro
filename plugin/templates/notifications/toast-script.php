<?php
/**
 * Template pour l'injection des données de toasts en JavaScript
 *
 * @package PDF_Builder
 * @since 1.0.0
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Variables attendues : $toast_data (JSON des toasts)
if (!isset($toast_data)) {
    return;
}

$json_toasts = wp_json_encode($toast_data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($json_toasts === false) {
    return;
}

// Échapper complètement le JSON pour JavaScript
$escaped_json = str_replace('</script>', '<\/script>', $json_toasts);
$escaped_json = str_replace('<!--', '<\!--', $escaped_json);
$escaped_json = str_replace('-->', '--\>', $escaped_json);
?>
<script>
try {
    window.pdfBuilderToasts = <?php echo $escaped_json; ?>;
} catch(e) {
    console.error('Erreur chargement toasts:', e);
    window.pdfBuilderToasts = [];
}
</script>
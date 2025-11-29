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

// Variables attendues : $toast_data (JSON des toasts), $localization_data (données de localisation)
if (!isset($toast_data)) {
    return;
}

$json_toasts = wp_json_encode($toast_data);
if ($json_toasts === false) {
    return;
}

// Échapper pour éviter les injections XSS
$escaped_json = str_replace('</script>', '<\/script>', $json_toasts);

// Données de localisation si disponibles
$localization_json = '';
if (isset($localization_data) && is_array($localization_data)) {
    $json_localization = wp_json_encode($localization_data);
    if ($json_localization !== false) {
        // Échapper pour éviter les injections XSS
        $localization_json = str_replace('</script>', '<\/script>', $json_localization);
    }
}
?>
<!-- Script d'injection des données toast et de localisation -->
<script>
try {
    window.pdfBuilderToasts = <?php echo $escaped_json; ?>;
    <?php if ($localization_json): ?>
    window.pdfBuilderNotifications = <?php echo $localization_json; ?>;
    <?php endif; ?>
} catch(e) {
    console.error('Erreur chargement toasts:', e);
    window.pdfBuilderToasts = [];
    <?php if ($localization_json): ?>
    window.pdfBuilderNotifications = {};
    <?php endif; ?>
}
</script>
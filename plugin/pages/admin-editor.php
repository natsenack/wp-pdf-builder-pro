<?php
/**
 * PDF Builder Pro V2 - Page d'administration
 * 
 * Cette page affiche l'interface React du PDF Builder
 */

// Vérifier les permissions WordPress
if (!current_user_can('manage_options')) {
    wp_die(__('Accès refusé', 'pdf-builder-pro'));
}

// Inclure les assets React
require_once __DIR__ . '/includes/ReactAssetsV2.php';
?>

<div class="wrap pdf-builder-admin-container">
    <div class="pdf-builder-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p class="description">
            <?php _e('Édition de documents PDF avec PDF Builder Pro V2', 'pdf-builder-pro'); ?>
        </p>
    </div>
    
    <!-- Conteneur React principal -->
    <div id="pdf-builder-react-root" class="pdf-builder-root">
        <div class="pdf-builder-loading">
            <div class="spinner"></div>
            <p><?php _e('Chargement du PDF Builder...', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<style>
.pdf-builder-admin-container {
    margin: 0 -20px -20px -20px;
    padding: 0;
}

.pdf-builder-header {
    background: white;
    padding: 20px;
    border-bottom: 1px solid #e5e5e5;
    margin: 0 0 20px 0;
}

.pdf-builder-header h1 {
    margin: 0 0 10px 0;
}

.pdf-builder-root {
    background: white;
    min-height: 600px;
}

.pdf-builder-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 600px;
    gap: 20px;
}

.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>


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

<div class="wrap">
    <div class="pdf-builder-admin-container">
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
</div>

<style>
.pdf-builder-admin-container {
    margin: 0;
    padding: 0;
    background: #f1f1f1;
    min-height: calc(100vh - 100px); /* Assure que le conteneur prend toute la hauteur disponible */
}

.pdf-builder-header {
    background: white;
    padding: 20px;
    border-bottom: 1px solid #e5e5e5;
    margin: 0 0 20px 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pdf-builder-header h1 {
    margin: 0 0 10px 0;
    font-size: 23px;
    font-weight: 400;
    line-height: 1.3;
}

.pdf-builder-root {
    background: white;
    min-height: 600px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    margin-bottom: 20px; /* Espace avant le footer */
    overflow: hidden; /* Empêche tout débordement */
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

/* Assure que le footer WordPress reste en bas */
#wpfooter {
    clear: both;
    margin-top: 20px;
}
</style>


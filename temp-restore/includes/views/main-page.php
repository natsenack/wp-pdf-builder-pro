<?php
/**
 * Page principale - PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Vérifier les permissions
if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die(__('Vous devez être connecté et avoir les permissions pour accéder à cette page.', 'pdf-builder-pro'));
}

$core = PDF_Builder_Core::getInstance();
?>

<div class="wrap">
    <h1><?php _e('PDF Builder Pro - Accueil', 'pdf-builder-pro'); ?></h1>
    
    <div class="pdf-builder-dashboard">
        <div class="pdf-builder-cards">
            <div class="pdf-builder-card">
                <h3><?php _e('🎨 Éditeur Canvas', 'pdf-builder-pro'); ?></h3>
                <p><?php _e('Créez et personnalisez vos templates PDF avec l\'éditeur visuel avancé.', 'pdf-builder-pro'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>" class="button button-primary">
                    <?php _e('Commencer à créer', 'pdf-builder-pro'); ?>
                </a>
            </div>

            <div class="pdf-builder-card">
                <h3><?php _e('📋 Gestion des Templates', 'pdf-builder-pro'); ?></h3>
                <p><?php _e('Gérez, organisez et configurez tous vos templates PDF.', 'pdf-builder-pro'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>" class="button">
                    <?php _e('Voir les templates', 'pdf-builder-pro'); ?>
                </a>
            </div>

            <div class="pdf-builder-card">
                <h3><?php _e('📄 Documents', 'pdf-builder-pro'); ?></h3>
                <p><?php _e('Consultez et gérez tous vos documents PDF générés.', 'pdf-builder-pro'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-documents'); ?>" class="button">
                    <?php _e('Voir les documents', 'pdf-builder-pro'); ?>
                </a>
            </div>

            <div class="pdf-builder-card">
                <h3><?php _e('⚙️ Paramètres', 'pdf-builder-pro'); ?></h3>
                <p><?php _e('Configurez les paramètres globaux et les options avancées.', 'pdf-builder-pro'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-settings'); ?>" class="button">
                    <?php _e('Paramètres', 'pdf-builder-pro'); ?>
                </a>
            </div>
        </div>

        <div class="pdf-builder-stats">
            <h3><?php _e('Statistiques rapides', 'pdf-builder-pro'); ?></h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <strong>0</strong>
                    <span><?php _e('Templates actifs', 'pdf-builder-pro'); ?></span>
                </div>
                <div class="stat-item">
                    <strong>0</strong>
                    <span><?php _e('Documents générés', 'pdf-builder-pro'); ?></span>
                </div>
                <div class="stat-item">
                    <strong><?php echo PDF_BUILDER_VERSION; ?></strong>
                    <span><?php _e('Version', 'pdf-builder-pro'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.pdf-builder-dashboard {
    margin-top: 20px;
}

.pdf-builder-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.pdf-builder-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pdf-builder-card h3 {
    margin-top: 0;
    color: #23282d;
}

.pdf-builder-card p {
    margin-bottom: 15px;
    color: #646970;
}

.pdf-builder-stats {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 8px;
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-top: 15px;
}

.stat-item {
    text-align: center;
    padding: 10px;
}

.stat-item strong {
    display: block;
    font-size: 24px;
    color: #0073aa;
    margin-bottom: 5px;
}

.stat-item span {
    font-size: 12px;
    color: #646970;
    text-transform: uppercase;
}
</style>
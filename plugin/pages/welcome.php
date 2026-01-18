<?php
/**
 * PDF Builder Pro V2 - Page d'accueil du plugin
 */

if (!current_user_can('manage_options')) {
    wp_die(__('Accès refusé', 'pdf-builder-pro'));
}
?>

<div class="wrap">
    <div class="pdf-builder-welcome">
        <div class="welcome-header">
            <h1><?php _e('Bienvenue dans PDF Builder Pro V2', 'pdf-builder-pro'); ?></h1>
            <p class="subtitle"><?php _e('Refonte complète avec architecture moderne', 'pdf-builder-pro'); ?></p>
        </div>
        
        <div class="welcome-grid">
            <!-- Carte Éditeur -->
            <div class="welcome-card">
                <div class="card-icon">📄</div>
                <h2><?php _e('Éditeur PDF', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Créez et modifiez vos documents PDF facilement', 'pdf-builder-pro'); ?></p>
                <a href="admin.php?page=pdf-builder-react-editor" class="button button-primary">
                    <?php _e('Ouvrir l\'éditeur', 'pdf-builder-pro'); ?>
                </a>
            </div>
            
            <!-- Carte Paramètres -->
            <div class="welcome-card">
                <div class="card-icon">⚙️</div>
                <h2><?php _e('Paramètres', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Configurez les options du PDF Builder', 'pdf-builder-pro'); ?></p>
                <a href="admin.php?page=pdf-builder-settings" class="button button-primary">
                    <?php _e('Accéder aux paramètres', 'pdf-builder-pro'); ?>
                </a>
            </div>
            
            <!-- Carte Documentation -->
            <div class="welcome-card">
                <div class="card-icon">📖</div>
                <h2><?php _e('Documentation', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Consultez les guides et tutoriels', 'pdf-builder-pro'); ?></p>
                <a href="admin.php?page=pdf-builder-docs" class="button button-secondary">
                    <?php _e('Consulter la doc', 'pdf-builder-pro'); ?>
                </a>
            </div>
        </div>
        
        <!-- Section Infos -->
        <div class="welcome-info">
            <div class="info-column">
                <h3><?php _e('Nouveautés V2', 'pdf-builder-pro'); ?></h3>
                <ul>
                    <li>✅ Architecture modulaire et maintenable</li>
                    <li>✅ TypeScript pour plus de sécurité</li>
                    <li>✅ Bundle optimisé (4x plus petit)</li>
                    <li>✅ React 18 avec dernières features</li>
                    <li>✅ Gestion d'erreurs robuste</li>
                    <li>✅ Performance améliorée</li>
                </ul>
            </div>
            
            <div class="info-column">
                <h3><?php _e('En savoir plus', 'pdf-builder-pro'); ?></h3>
                <p><strong><?php _e('Version:', 'pdf-builder-pro'); ?></strong> 2.0.0</p>
                <p><strong><?php _e('React:', 'pdf-builder-pro'); ?></strong> 18.3.1</p>
                <p><strong><?php _e('TypeScript:', 'pdf-builder-pro'); ?></strong> 5.3</p>
                <p><strong><?php _e('Webpack:', 'pdf-builder-pro'); ?></strong> 5.104</p>
            </div>
        </div>
    </div>
</div>

<style>
.pdf-builder-welcome {
    background: white;
    padding: 30px;
    margin: 20px 0;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.welcome-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.welcome-header h1 {
    margin: 0 0 10px 0;
    font-size: 32px;
    color: #333;
}

.subtitle {
    color: #666;
    font-size: 16px;
    margin: 0;
}

.welcome-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 40px;
}

.welcome-card {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #eee;
    text-align: center;
    transition: all 0.3s ease;
}

.welcome-card:hover {
    background: #fff;
    border-color: #ddd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.card-icon {
    font-size: 40px;
    margin-bottom: 15px;
}

.welcome-card h2 {
    margin: 0 0 10px 0;
    font-size: 18px;
}

.welcome-card p {
    color: #666;
    margin: 10px 0 15px 0;
}

.welcome-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    background: #f0f7ff;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #0073aa;
}

.info-column h3 {
    margin-top: 0;
    color: #0073aa;
}

.info-column ul {
    margin: 10px 0;
    padding-left: 20px;
}

.info-column li {
    margin-bottom: 8px;
    color: #666;
}

.info-column p {
    margin: 8px 0;
    color: #666;
}

@media (max-width: 768px) {
    .welcome-info {
        grid-template-columns: 1fr;
    }
    
    .welcome-header h1 {
        font-size: 24px;
    }
}
</style>


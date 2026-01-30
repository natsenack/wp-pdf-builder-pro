<?php
/**
 * PDF Builder Pro V2 - Page de paramètres
 */

if (!current_user_can('manage_options')) {
    wp_die(__('Accès refusé', 'pdf-builder-pro'));
}

$active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'general';
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <nav class="nav-tab-wrapper">
        <a href="?page=pdf-builder-settings&tab=general" class="nav-tab <?php echo $active_tab === 'general' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Général', 'pdf-builder-pro'); ?>
        </a>
        <a href="?page=pdf-builder-settings&tab=advanced" class="nav-tab <?php echo $active_tab === 'advanced' ? 'nav-tab-active' : ''; ?>">
            <?php _e('Avancé', 'pdf-builder-pro'); ?>
        </a>
        <a href="?page=pdf-builder-settings&tab=about" class="nav-tab <?php echo $active_tab === 'about' ? 'nav-tab-active' : ''; ?>">
            <?php _e('À propos', 'pdf-builder-pro'); ?>
        </a>
    </nav>
    
    <div class="tab-content">
        <?php if ($active_tab === 'general'): ?>
            <div class="tab-pane">
                <h2><?php _e('Paramètres généraux', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Configurez les options générales du PDF Builder Pro.', 'pdf-builder-pro'); ?></p>
                
                <form method="post" action="options.php">
                    <?php settings_fields('pdf_builder_general'); ?>
                    <?php do_settings_sections('pdf_builder_general'); ?>
                    <?php submit_button(); ?>
                </form>
            </div>
        <?php elseif ($active_tab === 'advanced'): ?>
            <div class="tab-pane">
                <h2><?php _e('Paramètres avancés', 'pdf-builder-pro'); ?></h2>
                <p><?php _e('Configurer les options avancées du PDF Builder Pro.', 'pdf-builder-pro'); ?></p>
                
                <form method="post" action="options.php">
                    <?php settings_fields('pdf_builder_advanced'); ?>
                    <?php do_settings_sections('pdf_builder_advanced'); ?>
                    <?php submit_button(); ?>
                </form>
            </div>
        <?php else: ?>
            <div class="tab-pane">
                <h2><?php _e('À propos de PDF Builder Pro', 'pdf-builder-pro'); ?></h2>
                <div class="about-box">
                    <h3>Version 2.0.0</h3>
                    <p><strong><?php _e('PDF Builder Pro V2', 'pdf-builder-pro'); ?></strong></p>
                    <p><?php _e('Refonte complète avec architecture moderne et React 18', 'pdf-builder-pro'); ?></p>
                    
                    <h4><?php _e('Améliorations', 'pdf-builder-pro'); ?></h4>
                    <ul>
                        <li>✅ Architecture modulaire</li>
                        <li>✅ TypeScript strict</li>
                        <li>✅ Bundle 4x plus petit</li>
                        <li>✅ Gestion d'erreurs robuste</li>
                        <li>✅ React 18 natif</li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section de débogage temporaire -->
    <div style="margin-top: 30px; padding: 20px; background: #f0f0f0; border: 2px solid #ff6b35; border-radius: 5px;">
        <h3 style="color: #ff6b35; margin-top: 0;">🔍 DÉBOGAGE - Logs PDF Builder</h3>
        <p><strong>Les logs sont maintenant écrits dans wp-content/debug.log</strong></p>
        <p>Pour voir les logs, vérifiez le fichier <code>wp-content/debug.log</code> sur votre serveur.</p>
        <p>Cette section sera supprimée une fois le problème résolu.</p>
    </div>
</div>
    background: white;
    padding: 20px;
    margin-top: 0;
}

.tab-pane {
    display: block;
}

.about-box {
    background: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    margin-top: 20px;
}

.about-box ul {
    margin-left: 20px;
}

.about-box li {
    margin-bottom: 8px;
}
</style>


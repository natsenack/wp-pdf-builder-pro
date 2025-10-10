<?php
/**
 * Page principale d'administration - PDF Builder Pro
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Vérifier les permissions - permettre à tous les utilisateurs connectés
if (!defined('PDF_BUILDER_DEBUG_MODE') || !PDF_BUILDER_DEBUG_MODE) {
    if (!is_user_logged_in() || !current_user_can('read')) {
        wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
    }
}

// Utiliser l'instance globale si elle existe, sinon créer une nouvelle
global $pdf_builder_core;
if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
    $core = $pdf_builder_core;
} else {
    $core = PDF_Builder_Core::getInstance();
    // S'assurer que c'est initialisé
    if (!$core->is_initialized()) {
        $core->init();
    }
}

$cache_stats = $core->get_cache_manager()->get_stats();
$db_stats = $core->get_database_manager()->get_stats();
$template_stats = $core->get_template_manager()->get_stats();
?>

<div class="wrap">
    <h1><?php _e('PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <div class="pdf-builder-dashboard">
        <!-- En-tête avec statistiques rapides -->
        <div class="pdf-builder-stats-grid">
            <div class="pdf-builder-stat-card">
                <h3><?php _e('Templates', 'pdf-builder-pro'); ?></h3>
                <div class="stat-number"><?php echo $template_stats['total']; ?></div>
                <div class="stat-details">
                    <?php foreach ($template_stats['by_status'] as $status => $count): ?>
                        <span class="status-<?php echo esc_attr($status); ?>">
                            <?php echo esc_html(ucfirst($status)); ?>: <?php echo $count; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="pdf-builder-stat-card">
                <h3><?php _e('Cache Performance', 'pdf-builder-pro'); ?></h3>
                <div class="stat-number"><?php echo $cache_stats['hit_rate']; ?>%</div>
                <div class="stat-details">
                    <?php _e('Hit Rate', 'pdf-builder-pro'); ?>
                </div>
            </div>

            <div class="pdf-builder-stat-card">
                <h3><?php _e('Database', 'pdf-builder-pro'); ?></h3>
                <div class="stat-number"><?php echo $db_stats['table_sizes']['templates']['count'] ?? 0; ?></div>
                <div class="stat-details">
                    <?php _e('Records', 'pdf-builder-pro'); ?>
                </div>
            </div>

            <div class="pdf-builder-stat-card">
                <h3><?php _e('Version', 'pdf-builder-pro'); ?></h3>
                <div class="stat-number"><?php echo $core->get_version(); ?></div>
                <div class="stat-details">
                    <?php _e('Plugin Version', 'pdf-builder-pro'); ?>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="pdf-builder-quick-actions">
            <h2><?php _e('Actions Rapides', 'pdf-builder-pro'); ?></h2>
            <div class="action-buttons">
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor'); ?>" class="button button-primary">
                    <?php _e('🎨 Ouvrir l\'Éditeur Canvas', 'pdf-builder-pro'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-templates'); ?>" class="button button-secondary">
                    <?php _e('Gérer les Templates', 'pdf-builder-pro'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-documents'); ?>" class="button button-secondary">
                    <?php _e('Voir les Documents', 'pdf-builder-pro'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=pdf-builder-settings'); ?>" class="button button-secondary">
                    <?php _e('Paramètres', 'pdf-builder-pro'); ?>
                </a>
            </div>
        </div>

        <!-- Informations système -->
        <div class="pdf-builder-system-info">
            <h2><?php _e('Informations Système', 'pdf-builder-pro'); ?></h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td><strong><?php _e('Version PHP', 'pdf-builder-pro'); ?></strong></td>
                        <td><?php echo PHP_VERSION; ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Version WordPress', 'pdf-builder-pro'); ?></strong></td>
                        <td><?php echo get_bloginfo('version'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Mémoire Limite', 'pdf-builder-pro'); ?></strong></td>
                        <td><?php echo ini_get('memory_limit'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Temps d\'exécution max', 'pdf-builder-pro'); ?></strong></td>
                        <td><?php echo ini_get('max_execution_time'); ?>s</td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Cache Activé', 'pdf-builder-pro'); ?></strong></td>
                        <td><?php echo $cache_stats['enabled'] ? __('Oui', 'pdf-builder-pro') : __('Non', 'pdf-builder-pro'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Journal récent -->
        <div class="pdf-builder-recent-logs">
            <h2><?php _e('Activité Récente', 'pdf-builder-pro'); ?></h2>
            <?php
            $logger = $core->get_logger();
            $recent_logs = $logger->get_recent_logs(10);
            ?>
            <div class="log-entries">
                <?php if (empty($recent_logs)): ?>
                    <p><?php _e('Aucune activité récente.', 'pdf-builder-pro'); ?></p>
                <?php else: ?>
                    <?php foreach ($recent_logs as $log): ?>
                        <div class="log-entry">
                            <code><?php echo esc_html($log); ?></code>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
.pdf-builder-dashboard {
    margin-top: 20px;
}

.pdf-builder-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.pdf-builder-stat-card {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pdf-builder-stat-card h3 {
    margin: 0 0 10px 0;
    color: #23282d;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-number {
    font-size: 36px;
    font-weight: bold;
    color: #007cba;
    margin-bottom: 5px;
}

.stat-details {
    font-size: 12px;
    color: #666;
}

.stat-details .status-active { color: #46b450; }
.stat-details .status-draft { color: #ffb900; }
.stat-details .status-inactive { color: #dc3232; }

.pdf-builder-quick-actions,
.pdf-builder-system-info,
.pdf-builder-recent-logs {
    background: #fff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pdf-builder-quick-actions h2,
.pdf-builder-system-info h2,
.pdf-builder-recent-logs h2 {
    margin: 0 0 15px 0;
    color: #23282d;
}

.action-buttons {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.action-buttons .button {
    padding: 8px 16px;
}

.pdf-builder-system-info table {
    border: none;
    margin: 0;
}

.pdf-builder-system-info table td {
    border: none;
    padding: 8px 0;
}

.pdf-builder-system-info table td:first-child {
    font-weight: 600;
    width: 200px;
}

.log-entries {
    max-height: 300px;
    overflow-y: auto;
    background: #f8f9fa;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
    padding: 10px;
}

.log-entry {
    font-family: monospace;
    font-size: 12px;
    margin-bottom: 5px;
    padding: 5px;
    background: #fff;
    border-radius: 3px;
    border-left: 3px solid #007cba;
}

.log-entry:nth-child(even) {
    background: #f8f9fa;
}
</style>


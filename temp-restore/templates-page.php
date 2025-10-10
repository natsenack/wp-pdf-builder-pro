<?php
/**
 * Page des Templates - PDF Builder Pro - Version Améliorée
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit.');
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
}

// Utiliser l'instance globale si elle existe, sinon créer une nouvelle
global $pdf_builder_core;
if (isset($pdf_builder_core) && $pdf_builder_core instanceof PDF_Builder_Core) {
    $core = $pdf_builder_core;
} else {
    $core = PDF_Builder_Core::getInstance();
    if (!$core->is_initialized()) {
        $core->init();
    }
}

$template_manager = $core->get_template_manager();
// $templates = $template_manager->get_templates(); // Chargé via AJAX maintenant

// Vérifier si les tables existent
global $wpdb;
$tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}pdf_builder_templates'") === $wpdb->prefix . 'pdf_builder_templates';
?>

<div class="wrap">
    <div class="pdf-builder-templates-header">
        <h1><?php _e('Gestion des Templates PDF', 'pdf-builder-pro'); ?></h1>
        <p class="description"><?php _e('Créez, modifiez et organisez vos modèles de documents PDF', 'pdf-builder-pro'); ?></p>

        <?php if (!$tables_exist): ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php _e('Tables de base de données non trouvées', 'pdf-builder-pro'); ?></strong><br>
                <?php _e('Les tables PDF Builder Pro n\'ont pas été créées. Pour utiliser pleinement le plugin, vous devez :', 'pdf-builder-pro'); ?>
            </p>
            <ul>
                <li><?php _e('Activer le plugin PDF Builder Pro dans Extensions > Plugins installés', 'pdf-builder-pro'); ?></li>
                <li><?php _e('Ou exécuter le script d\'activation : <code>https://votre-site.com/wp-content/plugins/wp-pdf-builder-pro/activate-plugin.php</code>', 'pdf-builder-pro'); ?></li>
            </ul>
            <p><?php _e('Sans les tables, seules les fonctionnalités de base sont disponibles.', 'pdf-builder-pro'); ?></p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Actions principales -->
    <div class="pdf-builder-templates-actions">
        <a href="<?php echo admin_url('admin.php?page=pdf-builder-editor'); ?>" class="button button-primary button-hero">
            <?php _e('Ouvrir l\'Éditeur Canvas', 'pdf-builder-pro'); ?>
        </a>
        <button type="button" class="button button-secondary" id="create-template">
            <?php _e('Nouveau Template', 'pdf-builder-pro'); ?>
        </button>
        <button type="button" class="button" id="import-template">
            <?php _e('Importer', 'pdf-builder-pro'); ?>
        </button>
    </div>

    <!-- Filtres et vues -->
    <div class="pdf-builder-templates-filters">
        <div class="filter-tabs">
            <button class="tab-button active" data-view="grid">
                <?php _e('Grille', 'pdf-builder-pro'); ?>
            </button>
            <button class="tab-button" data-view="list">
                <?php _e('Liste', 'pdf-builder-pro'); ?>
            </button>
        </div>

        <div class="filter-controls">
            <select id="status-filter">
                <option value=""><?php _e('Tous les statuts', 'pdf-builder-pro'); ?></option>
                <option value="active"><?php _e('Actif', 'pdf-builder-pro'); ?></option>
                <option value="draft"><?php _e('Brouillon', 'pdf-builder-pro'); ?></option>
                <option value="inactive"><?php _e('Inactif', 'pdf-builder-pro'); ?></option>
            </select>

            <select id="type-filter">
                <option value=""><?php _e('Tous les types', 'pdf-builder-pro'); ?></option>
                <option value="pdf"><?php _e('PDF', 'pdf-builder-pro'); ?></option>
                <option value="facture"><?php _e('Facture', 'pdf-builder-pro'); ?></option>
                <option value="devis"><?php _e('Devis', 'pdf-builder-pro'); ?></option>
                <option value="bon_commande"><?php _e('Bon de commande', 'pdf-builder-pro'); ?></option>
                <option value="bon_livraison"><?php _e('Bon de livraison', 'pdf-builder-pro'); ?></option>
            </select>

            <input type="text" id="search-templates" placeholder="<?php _e('Rechercher...', 'pdf-builder-pro'); ?>">
        </div>
    </div>

    <!-- Statistiques -->
    <div class="pdf-builder-templates-stats">
        <div class="stat-item">
            <span class="stat-number" id="total-templates-count">0</span>
            <span class="stat-label"><?php _e('Templates', 'pdf-builder-pro'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-number" id="active-templates-count">0</span>
            <span class="stat-label"><?php _e('Actifs', 'pdf-builder-pro'); ?></span>
        </div>
        <div class="stat-item">
            <span class="stat-number" id="draft-templates-count">0</span>
            <span class="stat-label"><?php _e('Brouillons', 'pdf-builder-pro'); ?></span>
        </div>
    </div>

    <!-- Vue Grille -->
    <div id="templates-grid-view" class="templates-view active">
        <div id="templates-loading" class="templates-loading">
            <div class="loading-spinner">⟳</div>
            <p><?php _e('Chargement des templates...', 'pdf-builder-pro'); ?></p>
        </div>
        <div id="templates-grid-content" class="templates-container">
            <!-- Templates will be loaded here via AJAX -->
        </div>
    </div>

    <!-- Vue Liste (Table) -->
    <div id="templates-list-view" class="templates-view">
        <div id="templates-list-loading" class="templates-loading" style="display: none;">
            <div class="loading-spinner">⟳</div>
            <p><?php _e('Chargement des templates...', 'pdf-builder-pro'); ?></p>
        </div>
        <table id="templates-list-content" class="wp-list-table widefat fixed striped" style="display: none;">
            <thead>
                <tr>
                    <th><?php _e('Nom', 'pdf-builder-pro'); ?></th>
                    <th><?php _e('Type', 'pdf-builder-pro'); ?></th>
                    <th><?php _e('Statut', 'pdf-builder-pro'); ?></th>
                    <th><?php _e('Auteur', 'pdf-builder-pro'); ?></th>
                    <th><?php _e('Date de création', 'pdf-builder-pro'); ?></th>
                    <th><?php _e('Actions', 'pdf-builder-pro'); ?></th>
                </tr>
            </thead>
            <tbody>
                <!-- Templates will be loaded here via AJAX -->
            </tbody>
        </table>
    </div>
</div>

<style data-version="1.2.0">
/* ===========================================
   PDF Builder Pro - Templates Page Styles
   Version: 1.2.0 - <?php echo date('Y-m-d H:i:s'); ?>
   =========================================== */

/* Variables CSS améliorées pour une meilleure cohérence */
:root {
    --pdf-primary: #06b6d4;
    --pdf-primary-dark: #22d3ee;
    --pdf-primary-light: #67e8f9;
    --pdf-primary-gradient: linear-gradient(135deg, #06b6d4 0%, #22d3ee 100%);
    --pdf-secondary: #64748b;
    --pdf-secondary-light: #94a3b8;
    --pdf-success: #059669;
    --pdf-success-light: #10b981;
    --pdf-warning: #d97706;
    --pdf-warning-light: #f59e0b;
    --pdf-error: #dc2626;
    --pdf-error-light: #ef4444;
    --pdf-gray-50: #fefefe;
    --pdf-gray-100: #fafafa;
    --pdf-gray-200: #f5f5f5;
    --pdf-gray-300: #f0f0f0;
    --pdf-gray-400: #d1d5db;
    --pdf-gray-500: #9ca3af;
    --pdf-gray-600: #6b7280;
    --pdf-gray-700: #4b5563;
    --pdf-gray-800: #374151;
    --pdf-gray-900: #111827;
    --pdf-shadow-sm: 0 1px 2px 0 rgba(156, 163, 175, 0.1);
    --pdf-shadow: 0 1px 3px 0 rgba(156, 163, 175, 0.15), 0 1px 2px -1px rgba(156, 163, 175, 0.15);
    --pdf-shadow-md: 0 4px 6px -1px rgba(156, 163, 175, 0.2), 0 2px 4px -2px rgba(156, 163, 175, 0.2);
    --pdf-shadow-lg: 0 10px 15px -3px rgba(156, 163, 175, 0.25), 0 4px 6px -4px rgba(156, 163, 175, 0.25);
    --pdf-shadow-xl: 0 20px 25px -5px rgba(156, 163, 175, 0.3), 0 8px 10px -6px rgba(156, 163, 175, 0.3);
    --pdf-shadow-glow: 0 0 20px rgb(6 182 212 / 0.12);
    --pdf-radius: 16px;
    --pdf-radius-lg: 20px;
    --pdf-radius-xl: 24px;
    --pdf-transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    --pdf-transition-fast: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
    --pdf-transition-slow: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Style ultra-minimal et clair comme l'éditeur canvas */
.pdf-builder-templates-header {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 20px;
}

.pdf-builder-templates-header h1 {
    color: #212529;
    font-size: 24px;
    font-weight: 500;
    margin: 0 0 8px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.pdf-builder-templates-header .description {
    color: #6c757d;
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

/* Actions ultra-simples */
.pdf-builder-templates-actions {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
    flex-wrap: wrap;
    align-items: center;
}

.pdf-builder-templates-actions .button {
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 400;
    font-size: 14px;
    transition: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    border: 1px solid #dee2e6;
    cursor: pointer;
    position: relative;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    background: #ffffff;
    color: #495057;
}

.pdf-builder-templates-actions .button:hover {
    background: #f8f9fa;
    color: #212529;
    border-color: #adb5bd;
}

.pdf-builder-templates-actions .button-primary {
    background: #007cba;
    color: #ffffff;
    border-color: #007cba;
}

.pdf-builder-templates-actions .button-primary:hover {
    background: #005a87;
    border-color: #005a87;
}

.pdf-builder-templates-actions .button-secondary {
    background: #ffffff;
    color: #495057;
    border-color: #dee2e6;
}

.pdf-builder-templates-actions .button-secondary:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
    color: #212529;
}

.pdf-builder-templates-actions .button:hover {
    text-decoration: none;
}

/* Filtres ultra-simples */
.pdf-builder-templates-filters {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.filter-tabs {
    display: flex;
    gap: 5px;
    background: #ffffff;
    padding: 4px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
}

.tab-button {
    padding: 6px 12px;
    border: none;
    background: transparent;
    border-radius: 3px;
    cursor: pointer;
    transition: none;
    font-weight: 400;
    font-size: 14px;
    color: #6c757d;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.tab-button:hover {
    background: #f8f9fa;
    color: #495057;
}

.tab-button.active {
    background: #007cba;
    color: #ffffff;
}

.filter-controls {
    display: flex;
    gap: 10px;
    align-items: center;
}

.filter-controls select,
.filter-controls input {
    padding: 6px 8px;
    border: 1px solid #dee2e6;
    border-radius: 3px;
    font-size: 14px;
    background: #ffffff;
    color: #495057;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.filter-controls select:focus,
.filter-controls input:focus {
    outline: none;
    border-color: #007cba;
    background: #ffffff;
}

.filter-controls input {
    min-width: 150px;
}

/* Statistiques ultra-simples */
.pdf-builder-templates-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 10px;
    margin-bottom: 20px;
}

.stat-item {
    background: #f8f9fa;
    padding: 15px;
    border: 1px solid #e9ecef;
    border-radius: 4px;
    text-align: center;
}

.stat-number {
    display: block;
    font-size: 24px;
    font-weight: 600;
    color: #007cba;
    line-height: 1;
    margin-bottom: 5px;
}

.stat-label {
    color: #6c757d;
    font-size: 12px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Vues des templates */
.templates-view {
    display: none;
}

.templates-view.active {
    display: block;
}

/* Vue grille */
.templates-container {
    width: 100%;
    box-sizing: border-box;
}

.templates-grid, 
#templates-grid-content .templates-grid,
.pdf-builder-templates .templates-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)) !important;
    gap: 15px;
    width: 100%;
    box-sizing: border-box;
    padding: 5px;
}

.template-card {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
    transition: none;
    position: relative;
    width: 100%;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    min-height: 200px;
}

.template-card:hover {
    border-color: #adb5bd;
}

.template-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 16px 12px 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 8px 8px 0 0;
    border-bottom: 1px solid #e9ecef;
    position: relative;
}

.template-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 16px;
    right: 16px;
    height: 1px;
    background: linear-gradient(90deg, transparent 0%, #e9ecef 20%, #e9ecef 80%, transparent 100%);
}

.template-header-badges {
    display: flex;
    gap: 12px;
    align-items: center;
    flex-wrap: wrap;
}

.template-author {
    font-size: 12px;
    color: #495057;
    font-weight: 500;
    opacity: 0.9;
    background: rgba(108, 117, 125, 0.1);
    padding: 4px 8px;
    border-radius: 6px;
    border: 1px solid rgba(108, 117, 125, 0.2);
}

.template-icon {
    font-size: 24px;
    opacity: 0.8;
    color: #06b6d4;
    background: rgba(6, 182, 212, 0.1);
    padding: 8px;
    border-radius: 8px;
    border: 1px solid rgba(6, 182, 212, 0.2);
    transition: all 0.3s ease;
}

.template-card:hover .template-icon {
    opacity: 1;
    color: #0891b2;
    background: rgba(6, 182, 212, 0.15);
    transform: scale(1.05);
}

.template-status {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    backdrop-filter: none;
    border: 1px solid transparent;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 20px;
}

.template-status.status-active {
    background: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.template-status.status-draft {
    background: #fff3cd;
    color: #856404;
    border-color: #ffeaa7;
}

.template-status.status-inactive {
    background: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.template-type-badge {
    padding: 6px 10px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #ffffff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: #6c757d;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    min-height: 24px;
}

.template-type-badge.type-pdf {
    background: #007cba;
}

.template-type-badge.type-invoice {
    background: #28a745;
}

.template-type-badge.type-quote {
    background: #ffc107;
    color: #212529;
}

.template-default-badge {
    padding: 4px;
    border-radius: 3px;
    background: #ffc107;
    color: #212529;
    font-size: 12px;
    font-weight: 400;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #e0a800;
    min-width: 20px;
    min-height: 20px;
}

.template-content {
    padding: 15px;
    background: #ffffff;
    border-radius: 0;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.template-title {
    margin: 0 0 10px 0;
    font-size: 16px;
    font-weight: 500;
    color: #212529;
    line-height: 1.4;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.template-description {
    color: #6c757d;
    font-size: 14px;
    margin: 0 0 15px 0;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.template-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
    color: #6c757d;
    margin-bottom: 10px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    padding: 8px 0;
    border-top: 1px solid #f8f9fa;
    border-bottom: 1px solid #f8f9fa;
    background: #f8f9fa;
    border-radius: 3px;
    margin: 0 -2px 10px -2px;
    padding: 6px 2px;
}

.template-type {
    background: #e9ecef;
    color: #495057;
    padding: 4px 8px;
    border-radius: 3px;
    font-weight: 400;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-size: 11px;
    border: 1px solid #dee2e6;
}

.template-actions {
    padding: 12px 8px;
    display: flex;
    gap: 4px;
    justify-content: space-between;
    flex-wrap: wrap;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
    margin-top: auto;
}

.template-actions .button-icon {
    flex: 1;
    min-width: 0;
    text-align: center;
    padding: 6px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 400;
    transition: none;
    border: 1px solid #dee2e6;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    min-height: 32px;
    background: #ffffff;
    color: #495057;
    width: 32px;
    height: 32px;
}

.template-actions .button-icon:hover {
    background: #f8f9fa;
    color: #212529;
    border-color: #adb5bd;
    transform: scale(1.05);
}

.template-actions .button-small {
    background: #ffffff;
    color: #495057;
    border-color: #dee2e6;
}

.template-actions .button-small:hover {
    background: #f8f9fa;
    color: #212529;
    border-color: #adb5bd;
}

.template-actions .button-secondary {
    background: #007cba;
    color: #ffffff;
    border-color: #007cba;
}

.template-actions .button-secondary:hover {
    background: #005a87;
    border-color: #005a87;
}

.template-actions .button-link-delete {
    background: #dc3545;
    color: #ffffff;
    border-color: #dc3545;
}

.template-actions .button-link-delete:hover {
    background: #c82333;
    border-color: #c82333;
}

.template-actions .button-outline {
    background: transparent;
    border: 1px solid #cbd5e1;
    color: #475569;
}

.template-actions .button-outline:hover {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border-color: #94a3b8;
    color: #334155;
}

/* Vue liste ultra-simple */
#templates-list-content {
    background: #ffffff;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    overflow: hidden;
}

#templates-list-content table {
    width: 100%;
    border-collapse: collapse;
}

#templates-list-content thead {
    background: #f8f9fa;
}

#templates-list-content th {
    padding: 8px 12px;
    text-align: left;
    font-weight: 600;
    color: #495057;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 1px solid #dee2e6;
}

#templates-list-content td {
    padding: 8px 12px;
    border-bottom: 1px solid #f8f9fa;
    vertical-align: top;
    color: #495057;
}

#templates-list-content tbody tr:hover {
    background: #f8f9fa;
}

.template-type {
    background: #e9ecef;
    color: #495057;
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 400;
}

.row-actions {
    display: flex;
    gap: 5px;
    align-items: center;
}

.row-actions a,
.row-actions button {
    color: #007cba;
    text-decoration: none;
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 3px;
    transition: none;
    border: 1px solid #dee2e6;
    background: #ffffff;
    cursor: pointer;
    font-weight: 400;
}

.row-actions a:hover,
.row-actions button:hover {
    background: #007cba;
    color: #ffffff;
    border-color: #007cba;
}

/* Styles pour la vue liste */
.template-list-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.template-list-icon {
    font-size: 24px;
    color: #6c757d;
    flex-shrink: 0;
}

.template-list-info {
    flex: 1;
    min-width: 0;
}

.template-list-title {
    margin: 0 0 4px 0;
    font-size: 16px;
    font-weight: 500;
    color: #212529;
}

.template-list-title a {
    color: inherit;
    text-decoration: none;
}

.template-list-title a:hover {
    color: #007cba;
    text-decoration: underline;
}

.template-list-description {
    margin: 0;
    color: #6c757d;
    font-size: 14px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* États vides équilibrés */
.no-templates {
    text-align: center;
    padding: 2.5rem 2rem;
    background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    margin: 2rem 0;
    box-shadow: none;
}

.no-templates-icon {
    font-size: 2.5rem;
    margin-bottom: 1rem;
    opacity: 0.6;
    color: #cbd5e1;
}

.no-templates h3 {
    margin: 0 0 0.5rem 0;
    color: #334155;
    font-size: 1.25rem;
    font-weight: 600;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.no-templates p {
    color: #64748b;
    font-size: 0.9375rem;
    margin: 0 0 1.5rem 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
    color: var(--pdf-gray-600);
    margin-bottom: 1.5rem;
    font-size: 1rem;
}

.templates-loading {
    text-align: center;
    padding: 4rem 2rem;
    background: white;
    border: 1px solid var(--pdf-gray-200);
    border-radius: var(--pdf-radius-lg);
    box-shadow: var(--pdf-shadow);
    margin: 2rem 0;
}

.loading-spinner {
    font-size: 2.5rem;
    animation: spin 1s linear infinite;
    margin-bottom: 1rem;
    display: inline-block;
}

.loading-indicator {
    text-align: center;
    padding: 2rem;
    grid-column: 1 / -1;
    background: rgba(255, 255, 255, 0.8);
    border-radius: var(--pdf-radius);
    margin: 1rem 0;
    backdrop-filter: blur(10px);
}

.loading-indicator .loading-spinner {
    font-size: 1.5rem;
    animation: spin 1s linear infinite;
    margin-bottom: 0.5rem;
}

.loading-indicator p {
    margin: 0;
    color: var(--pdf-gray-600);
    font-size: 0.9rem;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .pdf-builder-templates-header {
        padding: 1.5rem;
    }

    .pdf-builder-templates-header h1 {
        font-size: 1.5rem;
    }

    .pdf-builder-templates-actions {
        flex-direction: column;
    }

    .pdf-builder-templates-actions .button {
        width: 100%;
        justify-content: center;
    }

    .pdf-builder-templates-filters {
        flex-direction: column;
        align-items: stretch;
    }

    .filter-controls {
        flex-direction: column;
        width: 100%;
    }

    .filter-controls input {
        min-width: auto;
        width: 100%;
    }

    .templates-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)) !important;
    }

    .template-actions {
        flex-direction: column;
    }

    .template-meta {
        flex-direction: column;
        gap: 0.5rem;
        align-items: flex-start;
    }

    #templates-list-content {
        overflow-x: auto;
    }

    #templates-list-content table {
        min-width: 600px;
    }
}

@media (max-width: 480px) {
    .pdf-builder-templates-stats {
        grid-template-columns: 1fr;
    }

    .filter-tabs {
        flex-direction: column;
        width: 100%;
    }

    .tab-button {
        width: 100%;
        justify-content: center;
    }
}

/* Animations d'entrée améliorées */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

.template-card {
    animation: fadeInUp 0.6s ease-out;
}

.template-card:nth-child(1) { animation-delay: 0.1s; }
.template-card:nth-child(2) { animation-delay: 0.2s; }
.template-card:nth-child(3) { animation-delay: 0.3s; }
.template-card:nth-child(4) { animation-delay: 0.4s; }
.template-card:nth-child(5) { animation-delay: 0.5s; }
.template-card:nth-child(6) { animation-delay: 0.6s; }

.stat-item {
    animation: slideInLeft 0.5s ease-out;
}

.stat-item:nth-child(2) { animation: slideInRight 0.5s ease-out 0.1s both; }
.stat-item:nth-child(3) { animation: slideInLeft 0.5s ease-out 0.2s both; }
.stat-item:nth-child(4) { animation: slideInRight 0.5s ease-out 0.3s both; }

/* Accessibilité et focus améliorés */
.tab-button:focus-visible,
.filter-controls select:focus-visible,
.filter-controls input:focus-visible,
.template-actions .button:focus-visible {
    outline: 3px solid var(--pdf-primary);
    outline-offset: 3px;
    box-shadow: 0 0 0 6px rgba(37, 99, 235, 0.1);
}

/* Responsivité améliorée */
@media (max-width: 1200px) {
    .templates-grid {
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)) !important;
    }
}

@media (max-width: 768px) {
    .pdf-builder-templates-header {
        padding: 2rem 1.5rem;
        margin-bottom: 2rem;
    }

    .pdf-builder-templates-header h1 {
        font-size: 2rem;
        flex-direction: column;
        gap: 0.5rem;
        text-align: center;
    }

    .pdf-builder-templates-actions {
        justify-content: center;
    }

    .pdf-builder-templates-filters {
        padding: 1.5rem 1rem;
        flex-direction: column;
        gap: 1rem;
    }

    .filter-controls {
        justify-content: center;
    }

    .templates-grid {
        grid-template-columns: 1fr !important;
        gap: 1rem;
    }

    .stat-item {
        padding: 1.5rem 1rem;
    }

    .stat-number {
        font-size: 2.5rem;
    }
}

@media (max-width: 480px) {
    .pdf-builder-templates-header h1 {
        font-size: 1.75rem;
    }

    .pdf-builder-templates-header .description {
        font-size: 1rem;
    }

    .pdf-builder-templates-actions .button {
        padding: 0.875rem 1.5rem;
        font-size: 0.9rem;
    }

    .template-actions {
        flex-direction: column;
        gap: 0.5rem;
        padding: 12px 8px;
    }

    .template-actions .button {
        width: 100%;
    }
}

@media (max-width: 600px) {
    .template-actions {
        flex-direction: column;
        gap: 4px;
        padding: 12px 8px;
    }

    .template-actions .button-icon {
        width: 100%;
        min-height: 32px;
        padding: 8px;
        font-size: 16px;
    }
}

/* Mode sombre support (si activé) */
@media (prefers-color-scheme: dark) {
    .pdf-builder-templates-header {
        background: #ffffff;
        border-color: #e5e7eb;
        color: #1f2937;
    }

    .pdf-builder-templates-header h1 {
        color: #1f2937;
    }

    .pdf-builder-templates-header .description {
        color: #6b7280;
    }

    .template-card {
        background: #ffffff;
        border-color: #e5e7eb;
        color: #1f2937;
    }

    .template-title {
        color: #1f2937;
    }

    .template-description {
        color: #6b7280;
    }

    .template-meta {
        color: #6b7280;
    }

    .template-type {
        background: #f9fafb;
        color: #6b7280;
        border-color: #e5e7eb;
    }

    .template-actions .button {
        background: #ffffff;
        color: #6b7280;
        border-color: #e5e7eb;
    }

    .template-actions .button:hover {
        background: #f9fafb;
        color: #374151;
        border-color: #d1d5db;
    }

    .template-actions .button-secondary {
        background: #007cba;
        color: #ffffff;
        border-color: #007cba;
    }

    .template-actions .button-link-delete {
        background: #dc3545;
        color: #ffffff;
        border-color: #dc3545;
    }

    .pdf-builder-templates-filters {
        background: #ffffff;
        border-color: #e5e7eb;
    }

    .pdf-builder-templates-stats .stat-item {
        background: #ffffff;
        border-color: #e5e7eb;
    }

    .stat-number {
        color: #007cba;
    }

    .stat-label {
        color: #6b7280;
    }

    #templates-list-content {
        background: #ffffff;
        border-color: #e5e7eb;
    }

    #templates-list-content th {
        background: #f9fafb;
        color: #374151;
        border-color: #e5e7eb;
    }

    #templates-list-content td {
        border-color: #f9fafb;
        color: #6b7280;
    }

    #templates-list-content tbody tr:hover {
        background: #f9fafb;
    }

    .template-type {
        background: #f9fafb;
        color: #6b7280;
        border-color: #e5e7eb;
    }

    .row-actions a,
    .row-actions button {
        color: #007cba;
        background: #ffffff;
        border-color: #e5e7eb;
    }

    .row-actions a:hover,
    .row-actions button:hover {
        background: #007cba;
        color: #ffffff;
        border-color: #007cba;
    }
}

/* Réduction du mouvement pour l'accessibilité */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }

    .pdf-builder-templates-header h1::before {
        animation: none;
    }

    .template-default-badge {
        animation: none;
    }
}
</style>
<script>
jQuery(document).ready(function($) {
    // Définir ajaxurl si pas déjà défini
    if (typeof ajaxurl === 'undefined') {
        ajaxurl = '<?php echo esc_js(admin_url('admin-ajax.php')); ?>';
    }

    // Variables globales pour les URLs
    var editorUrl = '<?php echo esc_js(admin_url('admin.php?page=pdf-builder-editor')); ?>';
    var editorBaseUrl = editorUrl + '&template_id=';
    var ajaxNonce = '<?php echo esc_js(wp_create_nonce('pdf_builder_templates')); ?>';

    // Traductions
    var translations = {
        aucun_template_trouve: "Aucun template trouvé",
        creer_premier_template: "Créer votre premier template",
        commencer_creer_modele: "Commencez par créer votre premier modèle de document PDF",
        editer: "Éditer",
        editer_parametres: "Éditer les paramètres",
        dupliquer: "Dupliquer",
        supprimer: "Supprimer",
        inconnu: "Inconnu",
        template_introuvable: 'Template introuvable. Les tables de la base de données n\'ont peut-être pas été créées.',
        permissions_dupliquer: "Vous n'avez pas les permissions pour dupliquer ce template.",
        definir_defaut: "Voulez-vous définir ce template comme par défaut pour le type",
        ancien_remplace: "L'ancien template par défaut sera remplacé.",
        confirmer_suppression: "Êtes-vous sûr de vouloir supprimer le template",
        confirmer_duplication: "Voulez-vous dupliquer le template",
        suppression_en_cours: "Suppression...",
        duplication_en_cours: "Duplication..."
    };

    // File updated at 2025-10-06 15:00

    // Variables pour la pagination et le cache
    var currentPage = 1;
    var templatesPerPage = 12;
    var allTemplates = [];
    var filteredTemplates = [];
    var isLoadingMore = false;
    var hasMoreTemplates = true;
    var templatesCache = {};
    var cacheExpiry = 5 * 60 * 1000; // 5 minutes

    // Chargement initial optimisé
    loadTemplates();

    var isLoadingTemplates = false;

    function loadTemplates(reset = true, forceRefresh = false) {
        if (isLoadingTemplates) {
            return;
        }

        var view = $('.tab-button.active').data('view') || 'grid';

        if (reset) {
            currentPage = 1;
            allTemplates = [];
            filteredTemplates = [];
            hasMoreTemplates = true;
            $('#templates-grid-content').empty();
            $('#templates-list-content tbody').empty();
            showLoadingForView(view);
        }

        // Déconnecter temporairement l'observer pendant le chargement
        if (window.templateIntersectionObserver) {
            window.templateIntersectionObserver.disconnect();
        }

        // Vérifier le cache seulement si on ne force pas le refresh
        var cacheKey = 'pdf_templates_' + currentPage;
        var cachedData = forceRefresh ? null : getCachedData(cacheKey);

        if (cachedData && !reset) {
            renderTemplates(cachedData, $('.tab-button.active').data('view') || 'grid', false);
            return;
        }

        isLoadingTemplates = true;

        var view = $('.tab-button.active').data('view') || 'grid';
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_get_templates',
                view: view,
                page: currentPage,
                per_page: templatesPerPage,
                nonce: ajaxNonce
            },
            success: function(response) {
                if (response.success && response.data) {
                    var templates = response.data.templates || [];
                    var total = response.data.total || 0;
                    hasMoreTemplates = templates.length === templatesPerPage && (currentPage * templatesPerPage) < total;

                    // Mettre en cache
                    setCachedData(cacheKey, templates);

                    // Ajouter à la collection existante
                    if (reset) {
                        allTemplates = templates;
                    } else {
                        allTemplates = allTemplates.concat(templates);
                    }

                    renderTemplates(templates, view, reset);
                    currentPage++;

                    // Mettre à jour l'observer du scroll infini
                    updateInfiniteScrollObserver();
                } else {
                    console.error('PDF Builder Pro: Erreur dans la réponse AJAX:', response);
                    showError('Erreur lors du chargement des templates');
                }
                isLoadingTemplates = false;
            },
            error: function(xhr, status, error) {
                console.error('PDF Builder Pro: Erreur AJAX:', status, error, xhr.responseText);
                showError('Erreur lors du chargement des templates');
                isLoadingTemplates = false;
            }
        });
    }

    // Fonctions de cache optimisées
    function getCachedData(key) {
        try {
            var cached = localStorage.getItem('pdf_builder_' + key);
            if (cached) {
                var data = JSON.parse(cached);
                if (Date.now() - data.timestamp < cacheExpiry) {
                    return data.templates;
                } else {
                    localStorage.removeItem('pdf_builder_' + key);
                }
            }
        } catch (e) {
            console.warn('Erreur cache localStorage:', e);
        }
        return null;
    }

    function setCachedData(key, templates) {
        try {
            localStorage.setItem('pdf_builder_' + key, JSON.stringify({
                templates: templates,
                timestamp: Date.now()
            }));
        } catch (e) {
            console.warn('Erreur sauvegarde cache:', e);
        }
    }

    function renderTemplates(templates, view, reset = true) {
        // Masquer le chargement
        $('#templates-loading, #templates-list-loading').hide();

        // Vérifier que templates est un array valide
        if (!Array.isArray(templates)) {
            console.error('PDF Builder Pro: templates n\'est pas un array:', templates);
            showError('Format de données invalide pour les templates');
            return;
        }

        if (view === 'grid') {
            renderGridView(templates, reset);
        } else if (view === 'list') {
            renderListView(templates, reset);
        }

        // Réattacher les événements après le rendu seulement si c'est un reset complet
        if (reset) {
            attachTemplateEvents();
            setupInfiniteScroll();
        } else {
            // Pour le scroll infini, mettre à jour l'observer
            updateInfiniteScrollObserver();
        }
    }

    function renderGridView(templates, reset = true) {
        var $container = $('#templates-grid-content');

        // Vider seulement si c'est un reset complet
        if (reset) {
            $container.empty();
        }

        // Vérifier que templates est un array valide
        if (!Array.isArray(templates)) {
            console.error('PDF Builder Pro: templates n\'est pas un array dans renderGridView:', templates);
            if (reset) {
                $container.html('<div class="notice notice-error"><p>Erreur: Impossible de charger les templates</p></div>');
            }
            return;
        }

        if (templates.length === 0 && reset) {
            $container.html('<div class="no-templates"><div class="no-templates-icon">📄</div><h3>' + translations.aucun_template_trouve + '</h3><p>' + translations.commencer_creer_modele + '</p><a href="' + editorUrl + '" class="button button-primary">' + translations.creer_premier_template + '</a></div>');
        } else {
            // Utiliser DocumentFragment pour de meilleures performances
            var fragment = document.createDocumentFragment();
            var gridContainer = reset ? document.createElement('div') : $container.find('.templates-grid')[0];

            if (reset) {
                gridContainer = document.createElement('div');
                gridContainer.className = 'templates-grid';
            }

            templates.forEach(function(template, index) {
                var card = createTemplateCard(template);
                gridContainer.appendChild(card);
            });

            if (reset) {
                fragment.appendChild(gridContainer);
                $container.html('').append(fragment);
            }

            // Ajouter indicateur de chargement si plus de templates disponibles
            if (hasMoreTemplates && !reset) {
                addLoadingIndicator();
            }
        }

        if (reset) {
            $container.show();
            updateStatsFromDOM();
        }
    }

    function renderListView(templates, reset = true) {
        var $table = $('#templates-list-content');
        var $tbody = $table.find('tbody');

        // Vider seulement si c'est un reset complet
        if (reset) {
            $tbody.empty();
        }

        // Vérifier que templates est un array valide
        if (!Array.isArray(templates)) {
            console.error('PDF Builder Pro: templates n\'est pas un array dans renderListView:', templates);
            if (reset) {
                $tbody.html('<tr><td colspan="6" class="notice notice-error">Erreur: Impossible de charger les templates</td></tr>');
            }
            return;
        }

        if (templates.length === 0 && reset) {
            var emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="6"><div class="no-templates"><div class="no-templates-icon">📄</div><h3>' + translations.aucun_template_trouve + '</h3><p>' + translations.commencer_creer_modele + '</p><a href="' + editorUrl + '" class="button button-primary">' + translations.creer_premier_template + '</a></div></td>';
            $tbody.append(emptyRow);
        } else {
            templates.forEach(function(template, index) {
                var row = createTemplateRow(template);
                $tbody.append(row);
            });
        }

        if (reset) {
            $table.show();
            updateStatsFromDOM();
        }
    }

    // Fonction optimisée pour créer une carte de template
    function createTemplateCard(template) {
        var card = document.createElement('div');
        card.className = 'template-card';
        card.setAttribute('data-status', template.status);
        card.setAttribute('data-type', template.type);

        var statusClass = 'status-' + template.status;
        var typeClass = 'type-' + template.type;

        card.innerHTML = '<div class="template-header">' +
            '<div class="template-icon">📄</div>' +
            '<div class="template-header-badges">' +
                '<div class="template-author">' + (template.author_name || 'Inconnu') + '</div>' +
                '<div class="template-status ' + statusClass + '">' + template.status.charAt(0).toUpperCase() + template.status.slice(1) + '</div>' +
                '<div class="template-type-badge ' + typeClass + '">' + getTemplateTypeIcon(template.type) + '</div>' +
                (template.is_default == 1 ? '<div class="template-default-badge">⭐</div>' : '') +
            '</div>' +
        '</div>' +
        '<div class="template-content">' +
            '<h3 class="template-title"><a href="' + editorBaseUrl + template.id + '" class="template-editor-link" title="Cliquer pour éditer avec l\'éditeur canvas">' + escapeHtml(template.name) + '</a></h3>' +
            (template.description ? '<p class="template-description">' + escapeHtml(template.description) + '</p>' : '') +
        '</div>' +
        '<div class="template-actions">' +
            '<a href="#" class="button button-icon edit-template-params" data-id="' + template.id + '" title="' + translations.editer_parametres + '" style="text-decoration:none;">⚙️</a>' +
            '<button type="button" class="button button-icon duplicate-template" data-id="' + template.id + '" title="' + translations.dupliquer + '">🔄</button>' +
            (template.is_default == 1 ?
                '<button type="button" class="button button-icon remove-default-template" data-id="' + template.id + '" data-type="' + template.type + '" title="Retirer défaut">⭐</button>' :
                '<button type="button" class="button button-icon set-default-template" data-id="' + template.id + '" data-type="' + template.type + '" title="Définir comme défaut">☆</button>'
            ) +
            '<button type="button" class="button button-icon button-link-delete delete-template" data-id="' + template.id + '" title="' + translations.supprimer + '">🗑️</button>' +
        '</div>';

        return card;
    }

    // Fonction pour créer une ligne de table pour la vue liste
    function createTemplateRow(template) {
        var statusClass = 'status-' + template.status;
        var typeClass = 'type-' + template.type;

        var row = document.createElement('tr');
        row.setAttribute('data-status', template.status);
        row.setAttribute('data-type', template.type);

        row.innerHTML = '<td>' +
            '<div class="template-list-header">' +
                '<div class="template-list-icon">📄</div>' +
                '<div class="template-list-info">' +
                    '<h4 class="template-list-title"><a href="' + editorBaseUrl + template.id + '" class="template-editor-link" title="Cliquer pour éditer avec l\'éditeur canvas">' + escapeHtml(template.name) + '</a></h4>' +
                    (template.description ? '<p class="template-list-description">' + escapeHtml(template.description) + '</p>' : '') +
                '</div>' +
            '</div>' +
        '</td>' +
        '<td>' +
            '<div class="template-type-badge ' + typeClass + '">' + getTemplateTypeIcon(template.type) + '</div>' +
        '</td>' +
        '<td>' +
            '<div class="template-status ' + statusClass + '">' + template.status.charAt(0).toUpperCase() + template.status.slice(1) + '</div>' +
            (template.is_default == 1 ? '<div class="template-default-badge">⭐</div>' : '') +
        '</td>' +
        '<td>' + (template.author_name || translations.inconnu) + '</td>' +
        '<td>' + (template.created_at || 'N/A') + '</td>' +
        '<td>' +
            '<div class="row-actions">' +
                '<a href="#" class="button button-icon edit-template-params" data-id="' + template.id + '" title="' + translations.editer_parametres + '" style="text-decoration:none;">⚙️</a>' +
                '<button type="button" class="button button-icon duplicate-template" data-id="' + template.id + '" title="' + translations.dupliquer + '">🔄</button>' +
                (template.is_default == 1 ?
                    '<button type="button" class="button button-icon remove-default-template" data-id="' + template.id + '" data-type="' + template.type + '" title="Retirer défaut">⭐</button>' :
                    '<button type="button" class="button button-icon set-default-template" data-id="' + template.id + '" data-type="' + template.type + '" title="Définir comme défaut">☆</button>'
                ) +
                '<button type="button" class="button button-icon button-link-delete delete-template" data-id="' + template.id + '" title="' + translations.supprimer + '">🗑️</button>' +
            '</div>' +
        '</td>';

        return row;
    }

    // Fonction utilitaire pour échapper le HTML
    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Configuration du scroll infini
    function setupInfiniteScroll() {
        if (window.templateIntersectionObserver) {
            window.templateIntersectionObserver.disconnect();
        }

        var options = {
            root: null,
            rootMargin: '100px',
            threshold: 0.1
        };

        window.templateIntersectionObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting && hasMoreTemplates && !isLoadingTemplates) {
                    
                    

                    // Marquer immédiatement comme en chargement pour éviter les appels multiples
                    isLoadingTemplates = true;

                    loadTemplates(false); // Charger plus sans reset
                }
            });
        }, options);

        // Initialiser l'observer
        updateInfiniteScrollObserver();
    }

    // Mettre à jour l'observer du scroll infini
    function updateInfiniteScrollObserver() {
        if (!window.templateIntersectionObserver) return;

        // Déconnecter l'ancien observer
        window.templateIntersectionObserver.disconnect();

        // Si plus de templates à charger, ne pas reconnecter
        if (!hasMoreTemplates) {
            
            // Supprimer le loader s'il existe
            $('.loading-indicator').remove();
            return;
        }

        // Attendre que le DOM soit stabilisé avant de reconnecter
        setTimeout(function() {
            // Vérifier à nouveau si on a encore besoin de charger
            if (!hasMoreTemplates || isLoadingTemplates) {
                
                return;
            }

            var view = $('.tab-button.active').data('view') || 'grid';
            var lastElement;

            if (view === 'grid') {
                lastElement = document.querySelector('.template-card:last-child');
            } else {
                lastElement = document.querySelector('#templates-list-content tbody tr:last-child');
            }

            if (lastElement) {
                
                window.templateIntersectionObserver.observe(lastElement);
            } else {
                
            }
        }, 200); // Augmenter le délai pour laisser le DOM se stabiliser
    }

    // Ajouter un indicateur de chargement
    function addLoadingIndicator() {
        var view = $('.tab-button.active').data('view') || 'grid';
        var container;
        
        if (view === 'grid') {
            container = document.getElementById('templates-grid-content');
        } else {
            // Pour la vue liste, ajouter une ligne de chargement
            var tbody = document.querySelector('#templates-list-content tbody');
            if (tbody) {
                var loadingRow = tbody.querySelector('.loading-indicator');
                if (!loadingRow) {
                    loadingRow = document.createElement('tr');
                    loadingRow.className = 'loading-indicator';
                    loadingRow.innerHTML = '<td colspan="6" style="text-align: center; padding: 20px;"><div class="loading-spinner">⏳</div><p>Chargement de plus de templates...</p></td>';
                    tbody.appendChild(loadingRow);
                }
            }
            return;
        }
        
        var indicator = container.querySelector('.loading-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'loading-indicator';
            indicator.innerHTML = '<div class="loading-spinner">⏳</div><p>Chargement de plus de templates...</p>';
            container.appendChild(indicator);
        }
    }

    function getTemplateTypeIcon(type) {
        var typeName = type || 'other';
        var icon = '';
        var title = '';

        switch(typeName.toLowerCase()) {
            case 'pdf':
                icon = '📄';
                title = 'Document PDF';
                break;
            case 'facture':
            case 'invoice':
                icon = '🧾';
                title = 'Facture';
                break;
            case 'devis':
            case 'quote':
                icon = '📝';
                title = 'Devis';
                break;
            case 'bon_commande':
                icon = '📦';
                title = 'Bon de commande';
                break;
            case 'bon_livraison':
                icon = '🚚';
                title = 'Bon de livraison';
                break;
            default:
                icon = '📋';
                title = 'Autre type';
                break;
        }

        return '<span title="' + title + '">' + icon + '</span>';
    }

    function getTemplateTypeIconOnly(type) {
        var typeName = type || 'other';
        var icon = '';

        switch(typeName.toLowerCase()) {
            case 'pdf':
                icon = '📄';
                break;
            case 'facture':
            case 'invoice':
                icon = '🧾';
                break;
            case 'devis':
            case 'quote':
                icon = '📝';
                break;
            case 'bon_commande':
                icon = '📦';
                break;
            case 'bon_livraison':
                icon = '🚚';
                break;
            default:
                icon = '📋';
                break;
        }

        return icon;
    }

    function updateTemplateCardVisuals(templateId, newData) {
        

        // Mettre à jour les cartes de la vue grille
        $('.template-card .edit-template-params[data-id="' + templateId + '"]').each(function() {
            var $card = $(this).closest('.template-card');

            // Mettre à jour le nom
            $card.find('.template-title a').text(newData.name);

            // Mettre à jour la description
            var $desc = $card.find('.template-description');
            if (newData.description) {
                $desc.text(newData.description).show();
            } else {
                $desc.hide();
            }

            // Mettre à jour le statut
            if (newData.status && typeof newData.status === 'string') {
                $card.attr('data-status', newData.status);
                $card.removeClass('status-active status-draft').addClass('status-' + newData.status);
                $card.find('.template-status').text(newData.status.charAt(0).toUpperCase() + newData.status.slice(1));
            }

            // Mettre à jour l'auteur
            if (newData.author) {
                $card.find('.template-author').text(newData.author);
            }

            // Mettre à jour le type
            $card.attr('data-type', newData.type);
            $card.removeClass('type-pdf type-facture type-devis type-bon_commande type-bon_livraison').addClass('type-' + newData.type);

            // Mettre à jour le badge de type
            $card.find('.template-type-badge').html(getTemplateTypeIcon(newData.type));

            // Mettre à jour le bouton dupliquer
            $card.find('.duplicate-template').html('🔄');

            
        });

        // Mettre à jour les lignes de la vue liste
        $('tr .edit-template-params[data-id="' + templateId + '"]').each(function() {
            var $row = $(this).closest('tr');

            // Mettre à jour le nom
            $row.find('.template-list-title a').text(newData.name);

            // Mettre à jour la description
            var $desc = $row.find('.template-list-description');
            if (newData.description) {
                $desc.text(newData.description).show();
            } else {
                $desc.hide();
            }

            // Mettre à jour le statut
            if (newData.status && typeof newData.status === 'string') {
                $row.attr('data-status', newData.status);
                $row.removeClass('status-active status-draft').addClass('status-' + newData.status);
                $row.find('.template-status').text(newData.status.charAt(0).toUpperCase() + newData.status.slice(1));
            }

            // Mettre à jour l'auteur
            if (newData.author) {
                $row.find('.template-author').text(newData.author);
            }

            // Mettre à jour le type
            $row.attr('data-type', newData.type);
            $row.removeClass('type-pdf type-facture type-devis type-bon_commande type-bon_livraison').addClass('type-' + newData.type);

            // Mettre à jour le badge de type
            $row.find('.template-type-badge').html(getTemplateTypeIcon(newData.type));

            // Mettre à jour le bouton dupliquer
            $row.find('.duplicate-template').html('🔄');

            
        });

        // Mettre à jour les statistiques si elles sont affichées
        updateStatsFromDOM();
        
    }

    function attachTemplateEvents() {
        

        // Boutons Éditer
        $('.template-card .button-icon:not(.duplicate-template):not(.delete-template):not(.set-default-template):not(.remove-default-template):not(.edit-template-params), .edit').off('click').on('click', function(e) {
            e.preventDefault();
            var templateId = $(this).attr('href') ? $(this).attr('href').split('template_id=')[1] : $(this).data('id');
            if (templateId) {
                window.location.href = editorBaseUrl + templateId;
            }
        });

        // Boutons Dupliquer
        $('.duplicate-template').off('click').on('click', function() {
            var templateId = $(this).data('id');
            var templateName = $(this).closest('.template-card').find('.template-title').text().trim();

            if (confirm(translations.confirmer_duplication + ' "' + templateName + '" ?')) {
                // Désactiver le bouton pendant la duplication
                var $button = $(this);
                $button.prop('disabled', true).text(translations.duplication_en_cours);

                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'pdf_builder_duplicate_template',
                        template_id: templateId,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            loadTemplates(true, true); // Forcer le refresh pour afficher le template dupliqué
                        } else {
                            var errorMessage = response.data && response.data.message ? response.data.message : 'Erreur lors de la duplication du template';
                            console.error('PDF Builder Pro: Erreur AJAX duplication:', errorMessage);
                            alert(errorMessage);
                        }
                        $button.prop('disabled', false).text('📋');
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF Builder Pro: Erreur AJAX duplication:', status, error, xhr.responseText);
                        $button.prop('disabled', false).text('📋');
                        showError('Erreur lors de la duplication du template');
                    }
                });
            }
        });

        // Boutons Supprimer
        $('.delete-template').off('click').on('click', function() {
            var templateId = $(this).data('id');
            var templateName = $(this).closest('.template-card').find('.template-title').text().trim();

            if (confirm(translations.confirmer_suppression + ' "' + templateName + '" ?')) {
                // Désactiver le bouton pendant la suppression
                var $button = $(this);
                $button.prop('disabled', true).text(translations.suppression_en_cours);

                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'pdf_builder_delete_template',
                        template_id: templateId,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            loadTemplates(true, true); // Forcer le refresh pour mettre à jour la liste
                        } else {
                            var errorMessage = response.data && response.data.message ? response.data.message : 'Erreur lors de la suppression du template';
                            console.error('PDF Builder Pro: Erreur AJAX suppression:', errorMessage);
                            alert(errorMessage);
                        }
                        $button.prop('disabled', false).text('🗑️');
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF Builder Pro: Erreur AJAX suppression:', status, error, xhr.responseText);
                        $button.prop('disabled', false).text('🗑️');
                        showError('Erreur lors de la suppression du template');
                    }
                });
            }
        });

        // Boutons Définir comme défaut
        $('.set-default-template').off('click').on('click', function() {
            var templateId = $(this).data('id');
            var templateType = $(this).data('type');

            if (confirm(translations.definir_defaut + ' ' + templateType.toUpperCase() + ' ? ' + translations.ancien_remplace)) {
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'pdf_builder_set_default_template',
                        template_id: templateId,
                        is_default: 1,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            loadTemplates(true, true); // Forcer le refresh pour mettre à jour les badges
                        } else {
                            var errorMessage = response.data && response.data.message ? response.data.message : 'Erreur lors de la définition du template par défaut';
                            console.error('PDF Builder Pro: Erreur AJAX définir défaut:', errorMessage);
                            alert(errorMessage);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF Builder Pro: Erreur AJAX définir défaut:', status, error, xhr.responseText);
                        showError('Erreur lors de la définition du template par défaut');
                    }
                });
            }
        });

        // Boutons Retirer défaut
        $('.remove-default-template').off('click').on('click', function() {
            var templateId = $(this).data('id');
            var templateType = $(this).data('type');

            if (confirm('Voulez-vous retirer le statut par défaut de ce template ?')) {
                $.ajax({
                    url: ajaxurl,
                    method: 'POST',
                    data: {
                        action: 'pdf_builder_set_default_template',
                        template_id: templateId,
                        is_default: 0,
                        nonce: ajaxNonce
                    },
                    success: function(response) {
                        if (response.success) {
                            loadTemplates(true, true); // Forcer le refresh pour mettre à jour les badges
                        } else {
                            var errorMessage = response.data && response.data.message ? response.data.message : 'Erreur lors du retrait du statut par défaut';
                            console.error('PDF Builder Pro: Erreur AJAX retirer défaut:', errorMessage);
                            alert(errorMessage);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('PDF Builder Pro: Erreur AJAX retirer défaut:', status, error, xhr.responseText);
                        showError('Erreur lors du retrait du statut par défaut');
                    }
                });
            }
        });

    }

    function showError(message) {
        $('#templates-loading, #templates-list-loading').hide();
        $('#templates-grid-content').html('<div class="notice notice-error"><p>' + message + '</p></div>').show();
        $('#templates-list-content').hide();
    }

    // Changement de vue
    $('.tab-button').on('click', function() {
        var $this = $(this);
        var view = $this.data('view');

        // Ne rien faire si le bouton est déjà actif
        if ($this.hasClass('active')) {
            return;
        }

        $('.tab-button').removeClass('active');
        $this.addClass('active');

        $('.templates-view').removeClass('active');
        $('#templates-' + view + '-view').addClass('active');

        // Réinitialiser et charger avec la nouvelle vue
        loadTemplates(true);
    });

    function showLoadingForView(view) {
        if (view === 'grid') {
            $('#templates-grid-content').html('<div class="loading">Chargement des templates...</div>').show();
            $('#templates-list-content').hide();
            $('#templates-list-loading').hide();
        } else {
            $('#templates-list-content').hide();
            $('#templates-list-loading').show();
            $('#templates-grid-content').hide();
            $('#templates-loading').hide();
        }
    }

    // Boutons Nouveau Template et Importer
    $('#create-template').on('click', function() {
        window.location.href = editorUrl;
    });

    $('#import-template').on('click', function() {
        alert('Fonctionnalité d\'importation à venir...');
    });

    // Filtres
    $('#status-filter, #type-filter').on('change', function() {
        filterTemplates();
    });

    function filterTemplates() {
        var statusFilter = $('#status-filter').val();
        var typeFilter = $('#type-filter').val();

        $('.template-card, #templates-list-content tbody tr').each(function() {
            var $item = $(this);
            var itemStatus = $item.data('status') || '';
            var itemType = $item.data('type') || '';

            var statusMatch = !statusFilter || statusFilter === '' || itemStatus === statusFilter;
            var typeMatch = !typeFilter || typeFilter === '' || itemType === typeFilter;

            if (statusMatch && typeMatch) {
                $item.show();
            } else {
                $item.hide();
            }
        });

        updateStatsFromDOM();
    }

    function updateStatsFromDOM() {
        var view = $('.tab-button.active').data('view') || 'grid';
        var total, active, draft;
        
        if (view === 'grid') {
            total = $('.template-card:visible').length;
            active = $('.template-card[data-status="active"]:visible').length;
            draft = $('.template-card[data-status="draft"]:visible').length;
        } else {
            total = $('#templates-list-content tbody tr:visible').length;
            active = $('#templates-list-content tbody tr[data-status="active"]:visible').length;
            draft = $('#templates-list-content tbody tr[data-status="draft"]:visible').length;
        }

        $('#total-templates-count').text(total);
        $('#active-templates-count').text(active);
        $('#draft-templates-count').text(draft);
    }

    // Confirmation que tous les gestionnaires d'événements sont attachés
    

    // Pré-créer la modale d'édition pour de meilleures performances
    createEditTemplateModal();

    // Modale d'édition des paramètres du template
    function createEditTemplateModal() {
        if ($('#edit-template-modal').length === 0) {
            var modal = document.createElement('div');
            modal.id = 'edit-template-modal';
            modal.className = 'pdf-builder-modal';
            modal.innerHTML = `
                <div class="pdf-builder-modal-backdrop"></div>
                <div class="pdf-builder-modal-content">
                    <div class="pdf-builder-modal-header">
                        <h2>Éditer les paramètres du template</h2>
                        <button type="button" class="pdf-builder-modal-close">&times;</button>
                    </div>
                    <div class="pdf-builder-modal-body">
                        <form id="edit-template-form">
                            <div class="form-group">
                                <label for="template-name">Nom du template</label>
                                <input type="text" id="template-name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="template-description">Description (optionnel)</label>
                                <textarea id="template-description" name="description" rows="3"></textarea>
                            </div>
                            <div class="form-group">
                                <label for="template-type">Type de template</label>
                                <select id="template-type" name="type" required>
                                    <option value="pdf">PDF</option>
                                    <option value="facture">Facture</option>
                                    <option value="bon_commande">Bon de commande</option>
                                    <option value="devis">Devis</option>
                                    <option value="bon_livraison">Bon de livraison</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="template-author">Auteur</label>
                                <select id="template-author" name="author" required>
                                    <option value="">Chargement...</option>
                                </select>
                            </div>
                            <div class="form-group">
                        </form>
                    </div>
                    <div class="pdf-builder-modal-footer">
                        <button type="button" class="button button-secondary" id="cancel-edit-template">Annuler</button>
                        <button type="button" class="button button-primary" id="save-template-params">Enregistrer</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
    }

    // Fonction pour charger la liste des auteurs
    function loadAuthors() {
        
        return $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'pdf_builder_get_authors',
                nonce: ajaxNonce
            }
        });
    }

    // Gestionnaire pour ouvrir la modale d'édition
    $(document).on('click', '.edit-template-params', function(e) {
        e.stopImmediatePropagation();
        e.preventDefault();
        
        var templateId = $(this).data('id');
        

        // Charger les données du template
            url: ajaxurl,
            action: 'pdf_builder_get_template_data',
            template_id: templateId,
            nonce: ajaxNonce
        });
        
        // Charger les auteurs et les données du template en parallèle
        $.when(
            loadAuthors(),
            $.ajax({
                url: ajaxurl,
                method: 'POST',
                data: {
                    action: 'pdf_builder_get_template_data',
                    template_id: templateId,
                    nonce: ajaxNonce
                }
            })
        ).done(function(authorsResponse, templateResponse) {
            
            
            
            // Remplir la liste des auteurs
            if (authorsResponse[0].success) {
                var $authorSelect = $('#template-author');
                $authorSelect.empty();
                $authorSelect.append('<option value="">Sélectionner un auteur</option>');
                
                authorsResponse[0].data.forEach(function(author) {
                    var displayName = author.display_name + ' (' + author.roles.join(', ') + ')';
                    $authorSelect.append('<option value="' + author.ID + '">' + displayName + '</option>');
                });
            }
            
            // Remplir les données du template
            if (templateResponse[0].success) {
                $('#edit-template-modal').data('template-id', templateId);
                $('#template-name').val(templateResponse[0].data.name || '');
                $('#template-description').val(templateResponse[0].data.description || '');
                $('#template-type').val(templateResponse[0].data.type || 'pdf');
                $('#template-status').val(templateResponse[0].data.status || 'draft');
                $('#template-author').val(templateResponse[0].data.author_id || '');
                
                $('#edit-template-modal').show();
            } else {
                alert('Erreur lors du chargement des données du template: ' + (templateResponse[0].data?.message || 'Erreur inconnue'));
            }
        }).fail(function() {
            alert('Erreur lors du chargement des données');
        });
    });

    // Fermer la modale
    $(document).on('click', '.pdf-builder-modal-close, .pdf-builder-modal-backdrop, #cancel-edit-template', function() {
        $('#edit-template-modal').hide();
    });

    // Sauvegarder les paramètres
    $(document).on('click', '#save-template-params', function() {
        var templateId = $('#edit-template-modal').data('template-id');
        var name = $('#template-name').val().trim();
        var description = $('#template-description').val().trim();
        var type = $('#template-type').val();
        var status = $('#template-status').val();
        var authorId = $('#template-author').val();

        
        
        

        // Validation côté client
        if (!name) {
            alert('Le nom du template est obligatoire');
            return;
        }

        // Définir des valeurs par défaut si nécessaire
        if (!type) type = 'pdf';
        if (!status) status = 'draft';

        

        if (!authorId) {
            alert('L\'auteur est obligatoire');
            return;
        }

        var formData = {
            action: 'pdf_builder_update_template_params',
            template_id: templateId,
            nonce: ajaxNonce,
            name: name,
            description: description,
            type: type,
            status: status,
            author_id: authorId
        };

        

        // Désactiver le bouton pendant la sauvegarde
        var $button = $(this);
        var originalText = $button.text();
        $button.prop('disabled', true).text('Sauvegarde...');

        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: formData,
            success: function(response) {
                
                $button.prop('disabled', false).text(originalText);

                if (response.success) {
                    
                    $('#edit-template-modal').hide();

                    // Utiliser les valeurs validées plutôt que de les récupérer à nouveau du DOM
                    var templateId = $('#edit-template-modal').data('template-id');
                    var newName = name; // Utiliser la variable validée
                    var newDescription = description; // Utiliser la variable validée
                    var newType = type; // Utiliser la variable validée
                    var newStatus = status; // Utiliser la variable validée
                    var newAuthorId = authorId; // Utiliser la variable validée
                    var newAuthorName = $('#template-author option:selected').text().split(' (')[0] || 'Inconnu';

                    

                    // Mettre à jour immédiatement l'interface pour ce template
                    updateTemplateCardVisuals(templateId, {
                        name: newName,
                        description: newDescription,
                        type: newType,
                        status: newStatus,
                        author: newAuthorName
                    });

                    // Fermer le modal et afficher le message de succès
                    $('#edit-template-modal').hide();
                    alert('Paramètres du template mis à jour avec succès !');

                    
                } else {
                    console.error('PDF Builder: Save failed:', response.data?.message);
                    alert('Erreur lors de la sauvegarde: ' + (response.data?.message || 'Erreur inconnue'));
                }
            },
            error: function(xhr, status, error) {
                console.error('PDF Builder: AJAX error:', xhr, status, error);
                $button.prop('disabled', false).text(originalText);
                alert('Erreur lors de la sauvegarde des paramètres');
            }
        });
    });
});
</script>

<style>
.pdf-builder-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
}

.pdf-builder-modal-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.pdf-builder-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
}

.pdf-builder-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #ddd;
}

.pdf-builder-modal-header h2 {
    margin: 0;
    font-size: 18px;
}

.pdf-builder-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.pdf-builder-modal-body {
    padding: 20px;
}

.pdf-builder-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group textarea {
    resize: vertical;
}

.template-editor-link {
    color: inherit;
    text-decoration: none;
}

.template-editor-link:hover {
    color: #007cba;
    text-decoration: underline;
}
</style>


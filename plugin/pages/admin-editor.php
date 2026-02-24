<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed, PluginCheck.Security.DirectDB, Squiz.PHP.DiscouragedFunctions, Generic.PHP.DisallowAlternativePHPTags
/**
 * PDF Builder Pro V2 - Page d'administration
 * 
 * Cette page affiche l'interface React du PDF Builder
 */

if (!defined('ABSPATH')) {
    exit;
}

// Vérifier les permissions WordPress
// En mode preview, autoriser les utilisateurs avec droits WooCommerce
$is_preview_mode = isset($_GET['preview']) && $_GET['preview'] === '1';
$has_permission = current_user_can('manage_options') || 
                  ($is_preview_mode && current_user_can('edit_shop_orders'));

if (!$has_permission) {
    wp_die(esc_html__('Accès refusé', 'pdf-builder-pro'));
}

// Inclure les assets React

require_once PDF_BUILDER_PLUGIN_DIR . 'src/Admin/ReactAssetsV2.php';

?>

<div class="wrap pdfb-pdf-builder-admin-container">
    <div class="pdfb-pdf-builder-header">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p class="description">
            <?php esc_html_e('Édition de documents PDF avec PDF Builder Pro V2', 'pdf-builder-pro'); ?>
        </p>
    </div>
    
    <!-- Conteneur React principal -->
    <div id="pdf-builder-react-root" class="pdfb-pdf-builder-root">
        <div class="pdfb-pdf-builder-loading">
            <div class="pdfb-spinner"></div>
            <p><?php esc_html_e('Chargement du PDF Builder...', 'pdf-builder-pro'); ?></p>
        </div>
    </div>
</div>

<?php /* Modaux upgrade injectés via AdminScriptLoader::renderUpgradeModals() dans admin_footer */ ?>


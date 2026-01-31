<?php
/**
 * Page principale des paramètres PDF Builder Pro - VERSION SIMPLIFIÉE
 */

if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die(__('Accès refusé. Vous devez être administrateur pour accéder à cette page.', 'pdf-builder-pro'));
}

// Récupération des paramètres
$settings = pdf_builder_get_option('pdf_builder_settings', array());
$current_tab = sanitize_text_field($_GET['tab'] ?? 'general');
$valid_tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
if (!in_array($current_tab, $valid_tabs)) {
    $current_tab = 'general';
}

// Enregistrer les paramètres - UTILISE LE SYSTÈME PERSONNALISÉ
if (isset($_POST['submit']) && isset($_POST['pdf_builder_settings'])) {
    // Déterminer si c'est une sauvegarde flottante
    $is_floating_save = isset($_POST['pdf_builder_floating_save']) && $_POST['pdf_builder_floating_save'] == '1';
    $save_type = $is_floating_save ? 'FLOATING SAVE BUTTON' : 'REGULAR SAVE';

    // Logs détaillés pour le débogage
    if (class_exists('PDF_Builder_Logger')) {
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Settings save triggered');
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] POST data received: submit=' . (isset($_POST['submit']) ? 'YES' : 'NO') . ', settings_count=' . (isset($_POST['pdf_builder_settings']) ? count($_POST['pdf_builder_settings']) : '0'));
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Current tab: ' . $current_tab);
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] User: ' . wp_get_current_user()->user_login . ' (ID: ' . get_current_user_id() . ')');

        if ($is_floating_save) {
            PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Floating save detected - value: ' . $_POST['pdf_builder_floating_save']);
        }
    }

    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'pdf_builder_settings-options')) {
        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] ERROR: Invalid nonce');
        }
        wp_die('Sécurité: Nonce invalide');
    }

    if (!current_user_can('manage_options')) {
        if (class_exists('PDF_Builder_Logger')) {
            PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] ERROR: Insufficient permissions for user ' . wp_get_current_user()->user_login);
        }
        wp_die('Accès refusé');
    }

    // Sanitize and save settings
    $settings = array_map('sanitize_text_field', $_POST['pdf_builder_settings']);
    $save_result = pdf_builder_update_option('pdf_builder_settings', $settings);

    // Log the save operation avec plus de détails
    if (class_exists('PDF_Builder_Logger')) {
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Settings saved successfully - result: ' . ($save_result ? 'SUCCESS' : 'FAILED'));
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Settings count: ' . count($settings));

        // Log des clés sauvegardées (sans les valeurs sensibles)
        $setting_keys = array_keys($settings);
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Saved setting keys: ' . implode(', ', $setting_keys));

        if ($is_floating_save) {
            PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Floating save completed for tab: ' . $current_tab);
        }
    }

    // Redirection pour éviter la resoumission avec message de succès
    $redirect_url = add_query_arg([
        'page' => 'pdf-builder-settings',
        'tab' => $current_tab,
        'updated' => '1'
    ], admin_url('admin.php'));

    if (class_exists('PDF_Builder_Logger')) {
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Redirecting to: ' . $redirect_url);
        PDF_Builder_Logger::get_instance()->debug_log('[PHP][' . $save_type . '] Save process completed successfully');
    }

    wp_redirect($redirect_url);
    exit;
} else {
    // Log quand aucune soumission de formulaire n'est détectée
    if (class_exists('PDF_Builder_Logger') && isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
        PDF_Builder_Logger::get_instance()->debug_log('[PHP] POST request received but no valid form submission detected');
        PDF_Builder_Logger::get_instance()->debug_log('[PHP] POST keys: ' . implode(', ', array_keys($_POST)));
    }
}

// Afficher le message de succès si la mise à jour a réussi
if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible"><p>Paramètres sauvegardés avec succès !</p></div>';
    });
}

?>

<div class="wrap">
    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <form method="post" action="" id="pdf-builder-settings-form">
        <?php wp_nonce_field('pdf_builder_settings-options'); ?>
        <!-- Champ caché pour la soumission manuelle du formulaire -->
        <input type="hidden" name="submit" value="1">

        <!-- Navigation par onglets -->
        <h2 class="nav-tab-wrapper">
            <div class="tabs-container">
                <a href="?page=pdf-builder-settings&tab=general" class="nav-tab<?php echo $current_tab === 'general' ? ' nav-tab-active' : ''; ?>">
                    <span class="tab-icon">⚙️</span>
                    <span class="tab-text"><?php _e('Général', 'pdf-builder-pro'); ?></span>
                </a>
                <a href="?page=pdf-builder-settings&tab=licence" class="nav-tab<?php echo $current_tab === 'licence' ? ' nav-tab-active' : ''; ?>">
                    <span class="tab-icon">🔑</span>
                    <span class="tab-text"><?php _e('Licence', 'pdf-builder-pro'); ?></span>
                </a>
                <a href="?page=pdf-builder-settings&tab=systeme" class="nav-tab<?php echo $current_tab === 'systeme' ? ' nav-tab-active' : ''; ?>">
                    <span class="tab-icon">🖥️</span>
                    <span class="tab-text"><?php _e('Système', 'pdf-builder-pro'); ?></span>
                </a>
                <a href="?page=pdf-builder-settings&tab=securite" class="nav-tab<?php echo $current_tab === 'securite' ? ' nav-tab-active' : ''; ?>">
                    <span class="tab-icon">🔒</span>
                    <span class="tab-text"><?php _e('Sécurité', 'pdf-builder-pro'); ?></span>
                </a>
                <a href="?page=pdf-builder-settings&tab=pdf" class="nav-tab<?php echo $current_tab === 'pdf' ? ' nav-tab-active' : ''; ?>">
                    <span class="tab-icon">📄</span>
                    <span class="tab-text"><?php _e('Configuration PDF', 'pdf-builder-pro'); ?></span>
                </a>
                <a href="?page=pdf-builder-settings&tab=contenu" class="nav-tab<?php echo $current_tab === 'contenu' ? ' nav-tab-active' : ''; ?>">
                    <span class="tab-icon">🎨</span>
                    <span class="tab-text"><?php _e('Canvas & Design', 'pdf-builder-pro'); ?></span>
                </a>
                <a href="?page=pdf-builder-settings&tab=templates" class="nav-tab<?php echo $current_tab === 'templates' ? ' nav-tab-active' : ''; ?>">
                    <span class="tab-icon">📋</span>
                    <span class="tab-text"><?php _e('Templates', 'pdf-builder-pro'); ?></span>
                </a>
                <a href="?page=pdf-builder-settings&tab=developpeur" class="nav-tab<?php echo $current_tab === 'developpeur' ? ' nav-tab-active' : ''; ?>">
                    <span class="tab-icon">👨‍💻</span>
                    <span class="tab-text"><?php _e('Développeur', 'pdf-builder-pro'); ?></span>
                </a>
            </div>
        </h2>

        <div class="settings-content-wrapper">
            <?php
            switch ($current_tab) {
                case 'general':
                    include __DIR__ . '/settings-general.php';
                    break;
                case 'licence':
                    do_settings_sections('pdf_builder_licence');
                    break;
                case 'systeme':
                    include __DIR__ . '/settings-systeme.php';
                    break;
                case 'securite':
                    include __DIR__ . '/settings-securite.php';
                    break;
                case 'pdf':
                    include __DIR__ . '/settings-pdf.php';
                    break;
                case 'contenu':
                    include __DIR__ . '/settings-contenu.php';
                    break;
                case 'templates':
                    include __DIR__ . '/settings-templates.php';
                    break;
                case 'developpeur':
                    include __DIR__ . '/settings-developpeur.php';
                    break;
                default:
                    echo '<p>' . __('Onglet non valide.', 'pdf-builder-pro') . '</p>';
                    break;
            }
            ?>

            <?php submit_button(); ?>

            <!-- Bouton flottant de sauvegarde - TOUJOURS visible -->
            <div id="pdf-builder-save-floating" class="pdf-builder-save-floating-container">
                <button type="submit" name="submit" id="pdf-builder-save-floating-btn" class="pdf-builder-floating-save">
                    💾 Enregistrer
                </button>
            </div>
        </div>
    </form>
</div>

<?php
// Inclure les modales canvas à la fin pour éviter les conflits de structure
require_once __DIR__ . '/settings-modals.php';
?>

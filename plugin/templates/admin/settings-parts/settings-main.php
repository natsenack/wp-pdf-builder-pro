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
    if (!wp_verify_nonce($_POST['_wpnonce'] ?? '', 'pdf_builder_settings-options')) {
        wp_die('Sécurité: Nonce invalide');
    }

<<<<<<< HEAD
    // LOG AU DÉBUT DU FICHIER
    
    // Afficher les logs persistants s'ils existent (depuis le fichier temporaire)
    $log_file = sys_get_temp_dir() . '/pdf_builder_debug.log';
    $persistent_logs = array();
    if (file_exists($log_file)) {
        $log_content = file_get_contents($log_file);
        if ($log_content) {
            $persistent_logs = array_filter(explode(PHP_EOL, trim($log_content)));
        }
    }
    
    if (!empty($persistent_logs)) {
        echo '<div style="background: #f0f8ff; border: 1px solid #add8e6; padding: 10px; margin: 10px 0; border-radius: 5px;">';
        echo '<h3>📋 Logs de débogage persistants :</h3>';
        echo '<pre style="max-height: 200px; overflow-y: auto; font-size: 12px;">';
        foreach ($persistent_logs as $log) {
            echo htmlspecialchars($log) . "\n";
        }
        echo '</pre>';
        echo '<button onclick="clearPersistentLogs()" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Effacer logs</button>';
        echo '</div>';
        echo '<script>function clearPersistentLogs() { 
            fetch("?page=pdf-builder-settings&clear_logs=1", {method: "POST"}).then(() => location.reload()); 
        }</script>';
    }
    
    if (isset($_GET['clear_logs'])) {
        $log_file = sys_get_temp_dir() . '/pdf_builder_debug.log';
        if (file_exists($log_file)) {
            unlink($log_file);
        }
        wp_redirect(remove_query_arg('clear_logs'));
        exit;
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_die(__('Accès refusé. Vous devez être administrateur pour accéder à cette page.', 'pdf-builder-pro'));
=======
    if (!current_user_can('manage_options')) {
        wp_die('Accès refusé');
>>>>>>> a95dfc1e4c21298f74f2f7fcedd7c49c1dcfa128
    }

    $settings = array_map('sanitize_text_field', $_POST['pdf_builder_settings']);
    pdf_builder_update_option('pdf_builder_settings', $settings);

<<<<<<< HEAD
    // LOG pour déboguer la soumission du formulaire
    
    
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] === SETTINGS PAGE LOADED ==='); }
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Settings page loaded - REQUEST_METHOD: ' . $_SERVER['REQUEST_METHOD']); }
    if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Current tab: ' . $current_tab); }
    
    // Log des données POST si présentes
    if (!empty($_POST)) {
        if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] POST data received: ' . json_encode($_POST)); }
        if (isset($_POST['submit'])) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Submit button clicked: ' . $_POST['submit']); }
        }
        if (isset($_POST['option_page'])) {
            if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Option page: ' . $_POST['option_page']); }
        }
    }
    
    // Gestion des onglets via URL
    $current_tab = sanitize_text_field($_GET['tab'] ?? 'general');
    $valid_tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
    if (!in_array($current_tab, $valid_tabs)) {
        $current_tab = 'general';
    }
=======
    // Message de succès
    add_action('admin_notices', function() {
        echo '<div class="notice notice-success is-dismissible"><p>Paramètres sauvegardés avec succès !</p></div>';
    });
>>>>>>> a95dfc1e4c21298f74f2f7fcedd7c49c1dcfa128

    // Redirection pour éviter la resoumission
    wp_redirect(add_query_arg('updated', '1', wp_get_referer()));
    exit;
}

?>

<div class="wrap">
    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <form method="post" action="" id="pdf-builder-settings-form">
        <?php wp_nonce_field('pdf_builder_settings-options'); ?>

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
<<<<<<< HEAD
    </h2>

    <!-- contenu des onglets moderne -->
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

        <!-- Bouton flottant de sauvegarde -->
        <?php if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] About to render floating save button'); } ?>
        <div id="pdf-builder-save-floating" class="pdf-builder-save-floating-container">
            <button type="submit" name="submit" id="pdf-builder-save-floating-btn" class="pdf-builder-floating-save">
                💾 Enregistrer
            </button>
        </div>
        <?php if (class_exists('PDF_Builder_Logger')) { PDF_Builder_Logger::get_instance()->debug_log('[PDF Builder] Floating save button rendered'); } ?>
    </div>
    </form>

    <!-- Containers fictifs pour éviter les erreurs JS -->
    <div id="pdf-builder-tabs" style="display: none;"></div>
    <div id="pdf-builder-tab-content" style="display: none;"></div>

</div> <!-- Fin du .wrap -->

<?php
// Inclure les modales canvas à la fin pour éviter les conflits de structure
require_once __DIR__ . '/settings-modals.php';
?>

</body>
</html>

=======
    </form>
</div>
>>>>>>> a95dfc1e4c21298f74f2f7fcedd7c49c1dcfa128

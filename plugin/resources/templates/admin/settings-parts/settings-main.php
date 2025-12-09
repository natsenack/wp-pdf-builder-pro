<?php
    /**
     * Page principale des paramètres PDF Builder Pro
     *
     * Interface d'administration principale avec système d'onglets
     * pour la configuration complète du générateur de PDF.
     *
     * @version 2.1.0
     * @since 2025-12-08
     */

    // Sécurité WordPress
    if (!defined('ABSPATH')) {
        exit('Direct access forbidden');
    }

    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_die(__('Accès refusé. Vous devez être administrateur pour accéder à cette page.', 'pdf-builder-pro'));
    }

    // Récupération des paramètres généraux
    $settings = get_option('pdf_builder_settings', array());
    $current_user = wp_get_current_user();

    // Gestion des onglets via URL
    $current_tab = $_GET['tab'] ?? 'general';
    $valid_tabs = ['general', 'licence', 'systeme', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
    if (!in_array($current_tab, $valid_tabs)) {
        $current_tab = 'general';
    }

    // Informations de diagnostic pour le débogage (uniquement en mode debug)
    $debug_info = defined('WP_DEBUG') && WP_DEBUG ? [
        'version' => PDF_BUILDER_PRO_VERSION ?? 'unknown',
        'php' => PHP_VERSION,
        'wordpress' => get_bloginfo('version'),
        'user' => $current_user->display_name,
        'time' => current_time('mysql')
    ] : null;

?>
<div class="wrap" style="padding-bottom: 100px;">
    <h1><?php _e('Paramètres PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
    <p><?php _e('Configurez les paramètres de génération de vos documents PDF.', 'pdf-builder-pro'); ?></p>

    <!-- Navigation par onglets moderne -->
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

    <!-- contenu des onglets moderne -->
    <div class="settings-content-wrapper">
        <?php
        switch ($current_tab) {
            case 'general':
                $general_file = __DIR__ . '/settings-general.php';
                if (file_exists($general_file)) {
                    require_once $general_file;
                } else {
                    echo '<p>' . __('Fichier de paramètres général manquant.', 'pdf-builder-pro') . '</p>';
                }
                break;

            case 'licence':
                $licence_file = __DIR__ . '/settings-licence.php';
                if (file_exists($licence_file)) {
                    require_once $licence_file;
                } else {
                    echo '<p>' . __('Fichier de paramètres licence manquant.', 'pdf-builder-pro') . '</p>';
                }
                break;

            case 'systeme':
                $systeme_file = __DIR__ . '/settings-systeme.php';
                if (file_exists($systeme_file)) {
                    require_once $systeme_file;
                } else {
                    echo '<p>' . __('Fichier de paramètres système manquant.', 'pdf-builder-pro') . '</p>';
                }
                break;

            case 'securite':
                $securite_file = __DIR__ . '/settings-securite.php';
                if (file_exists($securite_file)) {
                    require_once $securite_file;
                } else {
                    echo '<p>' . __('Fichier de paramètres sécurité manquant.', 'pdf-builder-pro') . '</p>';
                }
                break;

            case 'pdf':
                $pdf_file = __DIR__ . '/settings-pdf.php';
                if (file_exists($pdf_file)) {
                    require_once $pdf_file;
                } else {
                    echo '<p>' . __('Fichier de paramètres PDF manquant.', 'pdf-builder-pro') . '</p>';
                }
                break;

            case 'contenu':
                $contenu_file = __DIR__ . '/settings-contenu.php';
                if (file_exists($contenu_file)) {
                    require_once $contenu_file;
                } else {
                    echo '<p>' . __('Fichier de paramètres canvas manquant.', 'pdf-builder-pro') . '</p>';
                }
                break;

            case 'templates':
                $templates_file = __DIR__ . '/settings-templates.php';
                if (file_exists($templates_file)) {
                    require_once $templates_file;
                } else {
                    echo '<p>' . __('Fichier de paramètres templates manquant.', 'pdf-builder-pro') . '</p>';
                }
                break;

            case 'developpeur':
                $developpeur_file = __DIR__ . '/settings-developpeur.php';
                if (file_exists($developpeur_file)) {
                    require_once $developpeur_file;
                } else {
                    echo '<p>' . __('Fichier de paramètres développeur manquant.', 'pdf-builder-pro') . '</p>';
                }
                break;

            default:
                echo '<p>' . __('Onglet non valide.', 'pdf-builder-pro') . '</p>';
                break;
        }
        ?>
    </div>

    <!-- Bouton flottant de sauvegarde -->
    <button id="pdf-builder-save-floating-btn" class="floating-save-btn" style="display: none; position: fixed; bottom: 20px; right: 20px; z-index: 1000; background: #007cba; color: white; border: none; padding: 12px 24px; border-radius: 6px; cursor: pointer; font-size: 14px; font-weight: 500; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);">
        💾 Enregistrer
    </button>

    <!-- Containers fictifs pour éviter les erreurs JS -->
    <div id="pdf-builder-tabs" style="display: none;"></div>
    <div id="pdf-builder-tab-content" style="display: none;"></div>

</div> <!-- Fin du .wrap -->

<!-- Inclusion des modales en dehors du .wrap -->
<?php require_once __DIR__ . '/settings-modals.php'; ?>

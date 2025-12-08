<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());

// require_once __DIR__ . '/settings-helpers.php'; // REMOVED - settings-helpers.php deleted

// Charger les styles CSS pour les paramètres d'administration
$plugin_dir = dirname(dirname(dirname(__FILE__)));
wp_enqueue_style('pdf-builder-admin-settings', plugins_url('assets/css/admin-settings.css', $plugin_dir . '/pdf-builder-pro.php'), array(), '1.0.0');
?>
<!-- Settings page loaded -->
<main class="wrap" id="pdf-builder-settings-wrapper">
    <!-- Bouton de sauvegarde flottant global -->
    <div id="pdf-builder-save-floating" class="pdf-builder-save-floating" style="position: fixed !important; bottom: 20px !important; right: 20px !important; z-index: 9999 !important; display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
        <button type="button" id="pdf-builder-save-floating-btn" class="button button-primary button-hero pdf-builder-save-btn">
            <span class="dashicons dashicons-yes"></span>
            Enregistrer
        </button>
        <div id="save-status-indicator" class="save-status-indicator">
            <span id="save-status-text">Prêt à enregistrer</span>
        </div>
    </div>

    <header class="pdf-builder-header">
        <h1>Paramètres PDF Builder Pro</h1>
    </header>



    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs" role="tablist" aria-label="Onglets des paramètres PDF Builder">
        <a id="tab-general" href="#general" class="nav-tab" data-tab="general" role="tab" aria-selected="false" aria-controls="general">
            <span class="tab-icon">⚙️</span>
            <span class="tab-text">Général</span>
        </a>
        <a id="tab-licence" href="#licence" class="nav-tab" data-tab="licence" role="tab" aria-selected="false" aria-controls="licence">
            <span class="tab-icon">🔑</span>
            <span class="tab-text">Licence</span>
        </a>
        <a id="tab-systeme" href="#systeme" class="nav-tab" data-tab="systeme" role="tab" aria-selected="false" aria-controls="systeme">
            <span class="tab-icon">🖥️</span>
            <span class="tab-text">Système</span>
        </a>
        <a id="tab-securite" href="#securite" class="nav-tab" data-tab="securite" role="tab" aria-selected="false" aria-controls="securite">
            <span class="tab-icon">🔒</span>
            <span class="tab-text">Sécurité</span>
        </a>
        <a id="tab-pdf" href="#pdf" class="nav-tab" data-tab="pdf" role="tab" aria-selected="false" aria-controls="pdf">
            <span class="tab-icon">📄</span>
            <span class="tab-text">Configuration PDF</span>
        </a>
        <a id="tab-contenu" href="#contenu" class="nav-tab" data-tab="contenu" role="tab" aria-selected="false" aria-controls="contenu">
            <span class="tab-icon">🎨</span>
            <span class="tab-text">Canvas & Design</span>
        </a>
        <a id="tab-templates" href="#templates" class="nav-tab" data-tab="templates" role="tab" aria-selected="false" aria-controls="templates">
            <span class="tab-icon">📋</span>
            <span class="tab-text">Templates par Statut</span>
        </a>
        <a id="tab-developpeur" href="#developpeur" class="nav-tab" data-tab="developpeur" role="tab" aria-selected="false" aria-controls="developpeur">
            <span class="tab-icon">👨‍💻</span>
            <span class="tab-text">Développeur</span>
        </a>
    </nav>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper" role="tabpanel" aria-live="polite">
        <div id="general" class="tab-content" role="tabpanel" aria-labelledby="tab-general">
            <?php require_once 'settings-general.php'; ?>
        </div>

        <div id="licence" class="tab-content" role="tabpanel" aria-labelledby="tab-licence">
            <?php require_once 'settings-licence.php'; ?>
        </div>

        <div id="systeme" class="tab-content" role="tabpanel" aria-labelledby="tab-systeme">
            <?php require_once 'settings-systeme.php'; ?>
        </div>

        <div id="securite" class="tab-content" role="tabpanel" aria-labelledby="tab-securite">
            <?php require_once 'settings-securite.php'; ?>
        </div>

        <div id="pdf" class="tab-content" role="tabpanel" aria-labelledby="tab-pdf">
            <?php require_once 'settings-pdf.php'; ?>
        </div>

        <div id="contenu" class="tab-content" role="tabpanel" aria-labelledby="tab-contenu">
            <?php require_once 'settings-contenu.php'; ?>
        </div>

        <div id="templates" class="tab-content" role="tabpanel" aria-labelledby="tab-templates">
            <?php require_once 'settings-templates.php'; ?>
        </div>

        <div id="developpeur" class="tab-content" role="tabpanel" aria-labelledby="tab-developpeur">
            <?php require_once 'settings-developpeur.php'; ?>
        </div>
    </section>

    <!-- Modales de configuration - Chargées après tous les onglets pour éviter les conflits de structure -->
    <?php require_once 'settings-modals.php'; ?>

    <!-- Navigation JavaScript - Gérée par assets/js/settings-tabs.js -->
    <!-- Le fichier settings-tabs.js fournit PDFBuilderTabsAPI avec switchToTab(), getActiveTab() -->

    <!-- Styles inline de secours (au cas où le CSS ne chargerait pas) -->
    <style>
    /* Styles pour la navigation par onglets */
    #pdf-builder-tab-content .tab-content {
        display: none;
        padding: 20px 0;
    }
    #pdf-builder-tab-content .tab-content.active {
        display: block;
    }
    #pdf-builder-tabs .nav-tab {
        cursor: pointer;
    }
    </style>
</main>

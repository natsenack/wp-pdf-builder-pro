<?php
if (!defined('ABSPATH')) exit('Direct access forbidden');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');
$settings = get_option('pdf_builder_settings', array());

// Inclure les fonctions helper nécessaires pour tous les onglets
require_once __DIR__ . '/settings-helpers.php';

// DEBUG: Force visible output to verify file is loaded
echo "<div style='position:fixed;top:10px;left:10px;background:red;color:white;padding:10px;z-index:999999;border:3px solid yellow;font-size:16px;font-weight:bold;'>DEBUG: settings-main.php LOADED! [" . date('H:i:s') . "]</div>";
?>
<!-- Settings page loaded -->
<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1>Paramètres PDF Builder Pro</h1>
    </header>



    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs" role="tablist" aria-label="Onglets des paramètres PDF Builder">
        <a id="tab-general" href="#general" class="nav-tab nav-tab-active" data-tab="general" role="tab" aria-selected="true" aria-controls="general">Général</a>
        <a id="tab-licence" href="#licence" class="nav-tab" data-tab="licence" role="tab" aria-selected="false" aria-controls="licence">Licence</a>
        <a id="tab-systeme" href="#systeme" class="nav-tab" data-tab="systeme" role="tab" aria-selected="false" aria-controls="systeme">Système</a>
        <a id="tab-securite" href="#securite" class="nav-tab" data-tab="securite" role="tab" aria-selected="false" aria-controls="securite">Sécurité</a>
        <a id="tab-pdf" href="#pdf" class="nav-tab" data-tab="pdf" role="tab" aria-selected="false" aria-controls="pdf">PDF</a>
        <a id="tab-contenu" href="#contenu" class="nav-tab" data-tab="contenu" role="tab" aria-selected="false" aria-controls="contenu">Contenu</a>
        <a id="tab-templates" href="#templates" class="nav-tab" data-tab="templates" role="tab" aria-selected="false" aria-controls="templates">Modèles</a>
        <a id="tab-developpeur" href="#developpeur" class="nav-tab" data-tab="developpeur" role="tab" aria-selected="false" aria-controls="developpeur">Développeur</a>
    </nav>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper" role="tabpanel" aria-live="polite">
        <div id="general" class="tab-content active" role="tabpanel" aria-labelledby="tab-general">
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

<?php
/**
 * PDF BUILDER PRO - SETTINGS MAIN (VERSION ULTRA-SIMPLE POUR DIAGNOSTIC)
 * Juste les onglets + navigation jQuery basique
 */

// Vérifications de sécurité
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

if (!is_user_logged_in() || !current_user_can('pdf_builder_access')) {
    wp_die(__('Vous n\'avez pas la permission d\'accéder à cette page.', 'pdf-builder-pro'));
}

// Charger les paramètres
$settings = get_option('pdf_builder_settings', []);
$company_phone_manual = get_option('pdf_builder_company_phone_manual', '');
$company_siret = get_option('pdf_builder_company_siret', '');
$company_vat = get_option('pdf_builder_company_vat', '');
$company_rcs = get_option('pdf_builder_company_rcs', '');
$company_capital = get_option('pdf_builder_company_capital', '');

// Nonce
wp_nonce_field('pdf_builder_settings', '_wpnonce_pdf_builder');
?>

<!-- TEST: LE FICHIER SETTINGS-MAIN.PHP EST BIEN CHARGÉ LE 2 DÉCEMBRE À 23H20 -->

<main class="wrap" id="pdf-builder-settings-wrapper">
    <header class="pdf-builder-header">
        <h1><?php _e('⚙️ Paramètres PDF Builder Pro (VERSION SIMPLE)', 'pdf-builder-pro'); ?></h1>
    </header>

    <nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs">
        <a href="#general" class="nav-tab nav-tab-active" data-tab="general">⚙️ Général</a>
        <a href="#licence" class="nav-tab" data-tab="licence">🔑 Licence</a>
        <a href="#systeme" class="nav-tab" data-tab="systeme">🖥️ Système</a>
        <a href="#acces" class="nav-tab" data-tab="acces">🔐 Accès</a>
        <a href="#securite" class="nav-tab" data-tab="securite">🛡️ Sécurité</a>
        <a href="#pdf" class="nav-tab" data-tab="pdf">📄 PDF</a>
        <a href="#contenu" class="nav-tab" data-tab="contenu">🎨 Contenu</a>
        <a href="#templates" class="nav-tab" data-tab="templates">📋 Modèles</a>
        <a href="#developpeur" class="nav-tab" data-tab="developpeur">🛠️ Développeur</a>
    </nav>

    <section id="pdf-builder-tab-content" class="tab-content-wrapper">
        <div id="general" class="tab-content active">
            <div class="tab-content-inner">
                <?php require_once 'settings-general.php'; ?>
            </div>
        </div>
        <div id="licence" class="tab-content">
            <div class="tab-content-inner">
                <?php require_once 'settings-licence.php'; ?>
            </div>
        </div>
        <div id="systeme" class="tab-content">
            <div class="tab-content-inner">
                <?php require_once 'settings-systeme.php'; ?>
            </div>
        </div>
        <div id="acces" class="tab-content">
            <div class="tab-content-inner">
                <?php require_once 'settings-acces.php'; ?>
            </div>
        </div>
        <div id="securite" class="tab-content">
            <div class="tab-content-inner">
                <?php require_once 'settings-securite.php'; ?>
            </div>
        </div>
        <div id="pdf" class="tab-content">
            <div class="tab-content-inner">
                <?php require_once 'settings-pdf.php'; ?>
            </div>
        </div>
        <div id="contenu" class="tab-content">
            <div class="tab-content-inner">
                <?php require_once 'settings-contenu.php'; ?>
            </div>
        </div>
        <div id="templates" class="tab-content">
            <div class="tab-content-inner">
                <?php require_once 'settings-templates.php'; ?>
            </div>
        </div>
        <div id="developpeur" class="tab-content">
            <div class="tab-content-inner">
                <?php require_once 'settings-developpeur.php'; ?>
            </div>
        </div>
    </section>

<?php
// Enqueuer le script de navigation des onglets
wp_enqueue_script('pdf-builder-tabs', plugin_dir_url(dirname(dirname(__FILE__))) . 'assets/js/settings-tabs.js', ['jquery'], '1.0.0', true);
?>

</main>

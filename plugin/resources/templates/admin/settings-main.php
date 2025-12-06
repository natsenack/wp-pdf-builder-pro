<?php
if (!defined('ABSPATH')) exit('No direct access');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');

$settings = get_option('pdf_builder_settings', array());
$nonce = wp_create_nonce('pdf_builder_ajax');
?>
<div class="wrap">
<h1>PDF Builder Pro Settings</h1>

<nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs">
<a href="#" class="nav-tab nav-tab-active" data-tab="general">General</a>
<a href="#" class="nav-tab" data-tab="licence">License</a>
<a href="#" class="nav-tab" data-tab="systeme">System</a>
<a href="#" class="nav-tab" data-tab="securite">Security</a>
<a href="#" class="nav-tab" data-tab="pdf">PDF</a>
<a href="#" class="nav-tab" data-tab="contenu">Content</a>
<a href="#" class="nav-tab" data-tab="templates">Templates</a>
<a href="#" class="nav-tab" data-tab="developpeur">Developer</a>
</nav>

<div id="pdf-builder-tab-content">
<div id="tab-content-general" class="tab-content active">
<?php include __DIR__ . '/settings-parts/settings-general.php'; ?>
</div>
<div id="tab-content-licence" class="tab-content">
<?php include __DIR__ . '/settings-parts/settings-licence.php'; ?>
</div>
<div id="tab-content-systeme" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-systeme.php'; ?>
</div>
<div id="tab-content-securite" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-securite.php'; ?>
</div>
<div id="tab-content-pdf" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-pdf.php'; ?>
</div>
<div id="tab-content-contenu" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-contenu.php'; ?>
</div>
<div id="tab-content-templates" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-templates.php'; ?>
</div>
<div id="tab-content-developpeur" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-developpeur.php'; ?>
</div>
</div>

<p class="submit"><button type="submit" class="button button-primary">Save Settings</button></p>

<!-- Bouton de sauvegarde flottant -->
<div id="pdf-builder-save-floating" class="pdf-builder-save-floating">
    <button type="button" id="pdf-builder-save-floating-btn" class="button button-primary button-hero pdf-builder-save-btn">
        <span class="dashicons dashicons-yes"></span>
        💾 Enregistrer
    </button>
    <div id="save-status-indicator" class="save-status-indicator">
        <span id="save-status-text">Prêt à enregistrer</span>
    </div>
</div>

<!-- Styles pour le bouton flottant -->
<style>
.pdf-builder-save-floating {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 10px;
}

.pdf-builder-save-btn {
    padding: 12px 24px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    transition: all 0.3s ease !important;
    min-width: 140px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.pdf-builder-save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2) !important;
}

.pdf-builder-save-btn:active {
    transform: translateY(0);
}

.pdf-builder-save-btn.saving {
    opacity: 0.7;
    pointer-events: none;
}

.pdf-builder-save-btn.saving::after {
    content: '';
    width: 16px;
    height: 16px;
    border: 2px solid #fff;
    border-top: 2px solid transparent;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-left: 8px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.save-status-indicator {
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 6px 12px;
    border-radius: 4px;
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    white-space: nowrap;
}

.save-status-indicator.visible {
    opacity: 1;
}

.save-status-indicator.success {
    background: rgba(40, 167, 69, 0.9);
}

.save-status-indicator.error {
    background: rgba(220, 53, 69, 0.9);
}

/* Responsive */
@media (max-width: 782px) {
    .pdf-builder-save-floating {
        bottom: 10px;
        right: 10px;
    }

    .pdf-builder-save-btn {
        padding: 10px 20px !important;
        font-size: 13px !important;
        min-width: 120px;
    }
}
</style>
</div>

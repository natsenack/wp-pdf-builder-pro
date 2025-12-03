<?php
if (!defined('ABSPATH')) exit('No direct access');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');

$settings = get_option('pdf_builder_settings', array());
$nonce = wp_create_nonce('pdf_builder_nonce');
?>
<div class="wrap">
<h1>PDF Builder Pro Settings</h1>

<nav class="nav-tab-wrapper wp-clearfix" id="pdf-builder-tabs">
<a href="#" class="nav-tab nav-tab-active" data-tab="general">General</a>
<a href="#" class="nav-tab" data-tab="licence">License</a>
<a href="#" class="nav-tab" data-tab="systeme">System</a>
<a href="#" class="nav-tab" data-tab="acces">Access</a>
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
<?php include __DIR__ . '/settings-parts/settings-systeme.php'; ?>
</div>
<div id="tab-content-acces" class="tab-content">
<?php include __DIR__ . '/settings-parts/settings-acces.php'; ?>
</div>
<div id="tab-content-securite" class="tab-content">
<?php include __DIR__ . '/settings-parts/settings-securite.php'; ?>
</div>
<div id="tab-content-pdf" class="tab-content">
<?php include __DIR__ . '/settings-parts/settings-pdf.php'; ?>
</div>
<div id="tab-content-contenu" class="tab-content">
<?php include __DIR__ . '/settings-parts/settings-contenu.php'; ?>
</div>
<div id="tab-content-templates" class="tab-content">
<?php include __DIR__ . '/settings-parts/settings-templates.php'; ?>
</div>
<div id="tab-content-developpeur" class="tab-content">
<?php include __DIR__ . '/settings-parts/settings-developpeur.php'; ?>
</div>
</div>

<p class="submit"><button type="submit" class="button button-primary">Save Settings</button></p>
</div>

<!-- JavaScript moved to settings-parts/settings-main.php to avoid conflicts -->

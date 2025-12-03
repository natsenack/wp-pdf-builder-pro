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
<div id="tab-content-general" class="tab-content active"><h2>General</h2><p>Content</p></div>
<div id="tab-content-licence" class="tab-content"><h2>License</h2><p>Content</p></div>
<div id="tab-content-systeme" class="tab-content"><h2>System</h2><p>Content</p></div>
<div id="tab-content-acces" class="tab-content"><h2>Access</h2><p>Content</p></div>
<div id="tab-content-securite" class="tab-content"><h2>Security</h2><p>Content</p></div>
<div id="tab-content-pdf" class="tab-content"><h2>PDF</h2><p>Content</p></div>
<div id="tab-content-contenu" class="tab-content"><h2>Content</h2><p>Content</p></div>
<div id="tab-content-templates" class="tab-content"><h2>Templates</h2><p>Content</p></div>
<div id="tab-content-developpeur" class="tab-content"><h2>Developer</h2><p>Content</p></div>
</div>

<p class="submit"><button type="submit" class="button button-primary">Save Settings</button></p>
</div>

<!-- JavaScript moved to settings-parts/settings-main.php to avoid conflicts -->

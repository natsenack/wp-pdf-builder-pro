<?php
/**
 * PDF Builder Pro - Admin Settings Page
 * Ultra-simple version - no complex includes, no emojis
 */

if (!defined('ABSPATH')) {
    exit('No direct access');
}

if (!is_user_logged_in() || !current_user_can('manage_options')) {
    wp_die('Access denied');
}

$settings = get_option('pdf_builder_settings', array());
$nonce = wp_create_nonce('pdf_builder_nonce');
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>PDF Builder Pro Settings</title>
</head>
<body>
<div class="wrap">
<h1>PDF Builder Pro Settings</h1>

<nav class="nav-tab-wrapper wp-clearfix">
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

<div id="tab-general" class="tab-pane active">
<h2>General Settings</h2>
<p>General settings content here</p>
</div>

<div id="tab-licence" class="tab-pane">
<h2>License Settings</h2>
<p>License content here</p>
</div>

<div id="tab-systeme" class="tab-pane">
<h2>System Settings</h2>
<p>System content here</p>
</div>

<div id="tab-acces" class="tab-pane">
<h2>Access Settings</h2>
<p>Access content here</p>
</div>

<div id="tab-securite" class="tab-pane">
<h2>Security Settings</h2>
<p>Security content here</p>
</div>

<div id="tab-pdf" class="tab-pane">
<h2>PDF Settings</h2>
<p>PDF content here</p>
</div>

<div id="tab-contenu" class="tab-pane">
<h2>Content Settings</h2>
<p>Content here</p>
</div>

<div id="tab-templates" class="tab-pane">
<h2>Templates Settings</h2>
<p>Templates here</p>
</div>

<div id="tab-developpeur" class="tab-pane">
<h2>Developer Settings</h2>
<p>Developer options here</p>
</div>

<form method="post">
<input type="hidden" name="nonce" value="<?php echo esc_attr($nonce); ?>">
<p class="submit">
<button type="submit" class="button button-primary">Save Settings</button>
</p>
</form>

</div>

<style>
.tab-pane { display: none; padding: 20px; }
.tab-pane.active { display: block; }
.nav-tab { cursor: pointer; }
</style>

<!-- JavaScript moved to settings-main.php to avoid conflicts -->

</body>
</html>
<?php

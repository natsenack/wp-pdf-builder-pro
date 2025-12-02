<?php
if (!defined('ABSPATH')) exit('No direct access');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');

$settings = get_option('pdf_builder_settings', array());
$nonce = wp_create_nonce('pdf_builder_nonce');
?>
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

<div id="tab-general" class="tab-pane active"><h2>General</h2><p>Content</p></div>
<div id="tab-licence" class="tab-pane"><h2>License</h2><p>Content</p></div>
<div id="tab-systeme" class="tab-pane"><h2>System</h2><p>Content</p></div>
<div id="tab-acces" class="tab-pane"><h2>Access</h2><p>Content</p></div>
<div id="tab-securite" class="tab-pane"><h2>Security</h2><p>Content</p></div>
<div id="tab-pdf" class="tab-pane"><h2>PDF</h2><p>Content</p></div>
<div id="tab-contenu" class="tab-pane"><h2>Content</h2><p>Content</p></div>
<div id="tab-templates" class="tab-pane"><h2>Templates</h2><p>Content</p></div>
<div id="tab-developpeur" class="tab-pane"><h2>Developer</h2><p>Content</p></div>

<p class="submit"><button type="submit" class="button button-primary">Save Settings</button></p>
</div>

<style>
.tab-pane { display: none; padding: 20px; }
.tab-pane.active { display: block; }
</style>

<script>
var tabs = document.querySelectorAll('.nav-tab');
var panes = document.querySelectorAll('.tab-pane');
tabs.forEach(function(tab) {
  tab.onclick = function(e) {
    e.preventDefault();
    var tabName = this.getAttribute('data-tab');
    tabs.forEach(function(t) { t.classList.remove('nav-tab-active'); });
    panes.forEach(function(p) { p.classList.remove('active'); });
    this.classList.add('nav-tab-active');
    var pane = document.getElementById('tab-' + tabName);
    if (pane) pane.classList.add('active');
  };
});
</script>

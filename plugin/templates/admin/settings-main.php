<?php
if (!defined('ABSPATH')) exit('No direct access');
if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Access denied');

// TEMPORARY: Include cache-busted version
include_once __DIR__ . '/settings-main-cache-bust.php';

// FORCE COMPLETE PAGE RELOAD TO BYPASS CACHE
echo "<script>
if (!sessionStorage.getItem('pdf_builder_cache_busted')) {
    sessionStorage.setItem('pdf_builder_cache_busted', 'true');
    window.location.reload(true);
}
</script>";

// FORCE NO-CACHE HEADERS
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Include helper functions
require_once __DIR__ . '/settings-parts/settings-helpers.php';

$settings = get_option('pdf_builder_settings', array());
$nonce = wp_create_nonce('pdf_builder_nonce');

// DEBUG: Force output to verify file is loaded
echo "<script>console.log('🔥 PDF BUILDER DEBUG: settings-main.php LOADED - " . date('H:i:s') . " - CACHE BUSTER: " . time() . "');</script>";
echo "<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 PDF BUILDER DEBUG: DOM loaded, checking tabs...');
    const tabsContainer = document.getElementById('pdf-builder-tabs');
    const contentContainer = document.getElementById('pdf-builder-tab-content');
    console.log('📍 Tabs container:', tabsContainer);
    console.log('📍 Content container:', contentContainer);
    if (tabsContainer) {
        const tabButtons = tabsContainer.querySelectorAll('.nav-tab');
        console.log('📍 Tab buttons found:', tabButtons.length);
        tabButtons.forEach((btn, i) => {
            console.log('  ' + (i+1) + '. ' + btn.textContent + ' (data-tab: ' + btn.getAttribute('data-tab') + ')');
        });
    }
    if (contentContainer) {
        const tabContents = contentContainer.querySelectorAll('.tab-content');
        console.log('📍 Tab contents found:', tabContents.length);
        tabContents.forEach((content, i) => {
            console.log('  ' + (i+1) + '. #' + content.id + ' - ' + (content.classList.contains('active') ? 'ACTIVE' : 'inactive'));
        });
    }
    console.log('🔧 PDF BUILDER DEBUG: Checking if PDFBuilderTabsAPI exists:', typeof window.PDFBuilderTabsAPI);
});
</script>";
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
<?php require_once __DIR__ . '/settings-parts/settings-systeme.php'; ?>
</div>
<div id="tab-content-acces" class="tab-content">
<?php require_once __DIR__ . '/settings-parts/settings-acces.php'; ?>
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

<!-- Bouton de sauvegarde flottant défini en HTML -->
<div id="pdf-builder-save-floating" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; display: block;">
    <button id="pdf-builder-save-all" class="button button-primary" style="padding: 12px 20px; font-size: 16px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); border-radius: 8px; transition: all 0.3s ease;">
        Enregistrer les paramètres
    </button>
</div>

<!-- Updated: 2025-12-03 23:20:00 - Bouton flottant défini uniquement en HTML - Cache bust: <?php echo time(); ?> -->
</div>

<!-- JavaScript moved to settings-parts/settings-main.php to avoid conflicts -->

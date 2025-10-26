<?php
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}
?>
<div id="pdf-builder-container">
    <div id="pdf-builder-loading">Loading...</div>
</div>
<script>
// PDF Builder Template loaded - initialization handled by wp_localize_script
console.log('PDF Builder template editor loaded');
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM ready');
    // PDFBuilderPro global will be defined by webpack bundle
    // Initialization happens in the bundle's inline script
});
</script>

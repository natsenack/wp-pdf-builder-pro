<?php
/**
 * PDF Builder Pro - Settings Page (Simplified)
 * Main settings dispatcher - includes modular parts
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

// Include main settings logic (contains all navigation and tabs)
require_once plugin_dir_path(__FILE__) . 'settings-parts/settings-main.php';

// Add floating save button and JavaScript here for better execution context
?>
<!-- Bouton de sauvegarde flottant global -->
<div id="pdf-builder-save-floating" class="pdf-builder-save-floating" style="position: fixed; bottom: 20px; right: 20px; z-index: 999999; display: block;">
    <button type="button" id="pdf-builder-save-floating-btn" class="button button-primary button-hero pdf-builder-save-btn" style="background: red; color: white; border: 3px solid yellow; font-size: 18px; padding: 12px 20px;">
        <span class="dashicons dashicons-saved"></span>
        💾 Enregistrer
    </button>
</div>

<script type="text/javascript">
(function() {
    console.log('🚨 PDF BUILDER: Floating button script loaded!');
    
    // Create debug indicator
    var debugDiv = document.createElement('div');
    debugDiv.id = 'pdf-js-test';
    debugDiv.innerHTML = '🚨 JAVASCRIPT EXECUTED! Floating button script working at ' + new Date().toLocaleTimeString();
    debugDiv.style.cssText = 'position:fixed;top:50px;left:10px;background:cyan;color:black;padding:15px;border:3px solid blue;font-size:14px;font-weight:bold;z-index:1000000;border-radius:5px;max-width:300px;';
    
    // Add to body when ready
    function addDebugElement() {
        if (document.body) {
            document.body.appendChild(debugDiv);
            console.log('🚨 PDF BUILDER: Debug element added to body');
            
            // Test floating button
            setTimeout(function() {
                var btn = document.getElementById('pdf-builder-save-floating-btn');
                if (btn) {
                    debugDiv.innerHTML += '<br>✅ Floating button found and visible!';
                    debugDiv.style.background = 'lime';
                    
                    // Add click handler
                    btn.addEventListener('click', function() {
                        alert('🚨 SUCCESS! Floating save button is working!');
                    });
                    
                } else {
                    debugDiv.innerHTML += '<br>❌ Floating button NOT found!';
                    debugDiv.style.background = 'orange';
                }
            }, 500);
            
        } else {
            setTimeout(addDebugElement, 10);
        }
    }
    addDebugElement();
    
})();
</script>

<style>
.pdf-builder-save-floating {
    position: fixed !important;
    bottom: 20px !important;
    right: 20px !important;
    z-index: 999999 !important;
    display: block !important;
}
.pdf-builder-save-floating .button {
    background: red !important;
    color: white !important;
    border: 3px solid yellow !important;
    font-size: 18px !important;
    padding: 12px 20px !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 8px rgba(0,0,0,0.3) !important;
}
</style>
<?php

// All JavaScript moved to settings-main.php to avoid conflicts

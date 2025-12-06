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
// Script ultra-fiable qui s'exécute très tard
window.addEventListener('load', function() {
    setTimeout(function() {
        console.log('🚨 PDF BUILDER: Ultra-late script execution!');
        
        // Créer indicateur de debug ultra-visible
        var debugDiv = document.createElement('div');
        debugDiv.id = 'pdf-js-test';
        debugDiv.innerHTML = '🚨 JAVASCRIPT WORKS! Script executed successfully at ' + new Date().toLocaleTimeString();
        debugDiv.style.cssText = 'position:fixed;top:50px;left:10px;background:cyan;color:black;padding:15px;border:3px solid blue;font-size:14px;font-weight:bold;z-index:1000000;border-radius:5px;max-width:300px;';
        
        document.body.appendChild(debugDiv);
        console.log('🚨 PDF BUILDER: Debug element added to body');
        
        // Forcer le bouton flottant visible
        setTimeout(function() {
            var btn = document.getElementById('pdf-builder-save-floating-btn');
            if (btn) {
                debugDiv.innerHTML += '<br>✅ Floating button found and forced visible!';
                debugDiv.style.background = 'lime';
                
                // S'assurer qu'il est visible
                btn.style.display = 'block !important';
                btn.style.visibility = 'visible !important';
                btn.style.opacity = '1 !important';
                btn.style.background = 'red !important';
                btn.style.color = 'white !important';
                btn.style.border = '3px solid yellow !important';
                btn.style.position = 'fixed !important';
                btn.style.bottom = '20px !important';
                btn.style.right = '20px !important';
                btn.style.zIndex = '999999 !important';
                btn.style.fontSize = '18px !important';
                btn.style.padding = '12px 20px !important';
                
                // Ajouter un gestionnaire de clic
                btn.addEventListener('click', function() {
                    alert('🚨 SUCCESS! Floating save button is working!');
                });
                
            } else {
                debugDiv.innerHTML += '<br>❌ Floating button NOT found!';
                debugDiv.style.background = 'orange';
            }
        }, 1000);
    }, 2000); // Attendre 2 secondes après le load
});
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

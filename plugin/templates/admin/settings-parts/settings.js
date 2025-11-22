/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 */

document.addEventListener("DOMContentLoaded", function() {
    console.log("Settings loaded");
    
    // Exposer les fonctions globalement pour qu elles soient accessibles depuis settings-main.php
    window.updateCanvasPreviews = function(category) {
        console.log("Updating canvas previews for category:", category);
        
        // Mettre à jour le preview FPS si on est dans la catégorie performance
        if (category === "performance") {
            const fpsSelect = document.getElementById("canvas_fps_target");
            const fpsValue = document.getElementById("current_fps_value");
            
            if (fpsSelect && fpsValue) {
                // Déclencher l événement change pour mettre à jour le preview
                const event = new Event("change");
                fpsSelect.dispatchEvent(event);
            }
        }
    };
    
    window.updateZoomPreview = function() {
        window.updateCanvasPreviews("performance");
    };
});

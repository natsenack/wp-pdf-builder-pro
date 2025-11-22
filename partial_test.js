/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 */

document.addEventListener('DOMContentLoaded', function() {
    // Fonction pour mettre à jour les checkboxes du formulaire avec les nouvelles valeurs
    function updateFormCheckboxes(settings) {
        // Mapping des paramètres vers les IDs des checkboxes
        const checkboxMappings = {
            'shadow_enabled': 'canvas_shadow_enabled',
            'show_grid': 'canvas_grid_enabled',
            'show_guides': 'canvas_guides_enabled',
            'snap_to_grid': 'canvas_snap_to_grid',
            'zoom_with_wheel': 'zoom_with_wheel',
            'show_resize_handles': 'canvas_resize_enabled',
            'enable_rotation': 'canvas_rotate_enabled',
            'multi_select': 'canvas_multi_select',
            'enable_keyboard_shortcuts': 'canvas_keyboard_shortcuts',
            'auto_save_enabled': 'canvas_auto_save',
            'debug_enabled': 'canvas_debug_enabled',
            // Paramètres de performance
            'lazy_loading_editor': 'canvas_lazy_loading_editor',
            'preload_critical': 'canvas_preload_critical',
            'lazy_loading_plugin': 'canvas_lazy_loading_plugin'
        };

        // Mettre à jour chaque checkbox
        Object.keys(checkboxMappings).forEach(settingKey => {
            const checkboxId = checkboxMappings[settingKey];
            const checkbox = document.getElementById(checkboxId);

            if (checkbox && settings[settingKey] !== undefined) {
                const shouldBeChecked = settings[settingKey] === true || settings[settingKey] === '1';
                const parentElement = checkbox.parentElement;

                // Mettre à jour l'état de la checkbox
                checkbox.checked = shouldBeChecked;

                // Mettre à jour les attributs et classes
                if (shouldBeChecked) {
                    checkbox.setAttribute('checked', 'checked');
                    parentElement.classList.add('checked');
                } else {
                    checkbox.removeAttribute('checked');
                    parentElement.classList.remove('checked');
                }
            }
        });
    }

    // === GESTION DES CARTES CANVAS (ouverture des modales) ===
    const configureButtons = document.querySelectorAll('.canvas-configure-btn');

    configureButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.stopPropagation(); // Empêcher la propagation du clic

            // Trouver la carte parente pour obtenir la catégorie
            const card = this.closest('.canvas-card');
            const category = card.getAttribute('data-category');
            const modalId = 'canvas-' + category + '-modal';
            const modal = document.getElementById(modalId);

            if (modal) {
                // Créer une modale propre basée sur le contenu existant
                const cleanModal = document.createElement('div');
                cleanModal.id = modalId + '-clean';
                cleanModal.className = 'canvas-modal'; // Ajouter la classe pour que closest() fonctionne
                cleanModal.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0,0,0,0.7);
                    z-index: 999999;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                `;

                // Copier le contenu de la modale existante
                const existingContent = modal.querySelector('.canvas-modal-content');
                if (existingContent) {
                    const contentClone = existingContent.cloneNode(true);
                    contentClone.style.cssText = `
                        background: white;
                        border-radius: 8px;
                        padding: 20px;
                        max-width: 600px;
                        width: 90%;
                        max-height: 85vh;
                        overflow-y: auto;
                        box-shadow: 0 10px 40px rgba(0,0,0,0.3);
                        position: relative;
                    `;
                    cleanModal.appendChild(contentClone);

                    // S'assurer que les boutons de fermeture fonctionnent
});

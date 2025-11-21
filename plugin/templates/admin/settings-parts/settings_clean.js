/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 * Updated: 2025-11-21 22:35:00 - Solution finale modales canvas
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
    const canvasCards = document.querySelectorAll('.canvas-card');
    canvasCards.forEach(function(card) {
        card.addEventListener('click', function() {
            const category = this.getAttribute('data-category');
            const modalId = 'canvas-' + category + '-modal';
            const modal = document.getElementById(modalId);

            if (modal) {
                // SOLUTION : Recréer complètement la modale canvas avec des styles inline
                const newModal = document.createElement('div');
                newModal.id = modalId;
                newModal.className = 'canvas-modal';
                newModal.innerHTML = `
                    <div class="canvas-modal-overlay" style="
                        position: fixed !important;
                        top: 0 !important;
                        left: 0 !important;
                        width: 100% !important;
                        height: 100% !important;
                        background: rgba(0,0,0,0.8) !important;
                        z-index: 1000000 !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                    ">
                        <div class="canvas-modal-content" style="
                            background: white !important;
                            border-radius: 8px !important;
                            padding: 20px !important;
                            max-width: 600px !important;
                            width: 90% !important;
                            max-height: 85vh !important;
                            overflow: hidden !important;
                            box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
                            border: 1px solid #e1e1e1 !important;
                            position: relative !important;
                            z-index: 1000001 !important;
                        ">
                            <div class="canvas-modal-header" style="
                                display: flex !important;
                                justify-content: space-between !important;
                                align-items: center !important;
                                margin-bottom: 25px !important;
                                border-bottom: 1px solid #dee2e6 !important;
                                padding-bottom: 15px !important;
                            ">
                                <h3 style="margin: 0 !important; color: #495057 !important;">📋 Configuration ${category}</h3>
                                <button type="button" class="canvas-modal-close" style="
                                    background: none !important;
                                    border: none !important;
                                    font-size: 24px !important;
                                    cursor: pointer !important;
                                    color: #6c757d !important;
                                ">&times;</button>
                            </div>
                            <div class="canvas-modal-body" style="
                                margin-bottom: 20px !important;
                                padding: 15px !important;
                                background: #f8f9fa !important;
                                border-radius: 8px !important;
                                border-left: 4px solid #007cba !important;
                            ">
                                <p style="margin: 0 !important; font-size: 14px !important; color: #495057 !important; line-height: 1.5 !important;">
                                    <strong>💡 Configuration ${category}</strong><br>
                                    Cette section permet de configurer les paramètres ${category} du canvas.
                                </p>
                            </div>
                            <div class="canvas-modal-footer" style="
                                display: flex !important;
                                justify-content: flex-end !important;
                                gap: 10px !important;
                                padding-top: 15px !important;
                                border-top: 1px solid #dee2e6 !important;
                            ">
                                <button type="button" class="canvas-modal-cancel" style="
                                    padding: 8px 16px !important;
                                    background: #6c757d !important;
                                    color: white !important;
                                    border: none !important;
                                    border-radius: 4px !important;
                                    cursor: pointer !important;
                                ">Annuler</button>
                                <button type="button" class="canvas-modal-save" style="
                                    padding: 8px 16px !important;
                                    background: #007cba !important;
                                    color: white !important;
                                    border: none !important;
                                    border-radius: 4px !important;
                                    cursor: pointer !important;
                                ">Sauvegarder</button>
                            </div>
                        </div>
                    </div>
                `;

                // Remplacer l'ancienne modale
                modal.parentNode.replaceChild(newModal, modal);

                // Ajouter les event listeners
                const closeButtons = newModal.querySelectorAll('.canvas-modal-close, .canvas-modal-cancel');
                closeButtons.forEach(function(button) {
                    button.addEventListener('click', function() {
                        newModal.remove();
                        document.body.style.overflow = '';
                    });
                });

                // Fermeture en cliquant sur l'overlay
                const overlay = newModal.querySelector('.canvas-modal-overlay');
                overlay.addEventListener('click', function(event) {
                    if (event.target === overlay) {
                        newModal.remove();
                        document.body.style.overflow = '';
                    }
                });

                document.body.style.overflow = 'hidden';
            } else {
                console.error('Modal not found for category:', category, 'Expected ID:', modalId);
            }
        });
    });

    // ... reste du code existant ...
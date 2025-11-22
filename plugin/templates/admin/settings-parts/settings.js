/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 */

document.addEventListener("DOMContentLoaded", function() {
    console.log("Settings loaded");
    
    // Exposer les fonctions globalement pour qu elles soient accessibles depuis settings-main.php
    window.updateCanvasPreviews = function(category) {
        console.log("🔄 Updating canvas previews for category:", category);

        // NEW: Get AJAX config and update values from database
        let ajaxConfig = null;
        if (typeof pdf_builder_ajax !== 'undefined') {
            ajaxConfig = pdf_builder_ajax;
        } else if (typeof pdfBuilderAjax !== 'undefined') {
            ajaxConfig = pdfBuilderAjax;
        } else if (typeof ajaxurl !== 'undefined') {
            ajaxConfig = { ajax_url: ajaxurl, nonce: '' };
        }

        if (ajaxConfig && ajaxConfig.ajax_url) {
            console.log('📡 Making AJAX request to get updated values for category:', category);

            // Make AJAX call to get updated values
            fetch(ajaxConfig.ajax_url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action': 'pdf_builder_get_canvas_settings',
                    'category': category,
                    'nonce': ajaxConfig.nonce || ''
                })
            })
            .then(response => {
                console.log('📨 AJAX response received:', response);
                return response.json();
            })
            .then(data => {
                console.log('📊 AJAX data received:', data);
                if (data.success && data.data) {
                    console.log('✅ Updating modal values with:', data.data);
                    updateModalValuesFromAjax(category, data.data);
                } else {
                    console.error('❌ Failed to get updated values:', data);
                }
            })
            .catch(error => {
                console.error('❌ Error updating previews:', error);
            });
        }

        // LEGACY: Keep existing preview functionality
        if (category === "performance") {
            const fpsSelect = document.getElementById("canvas_fps_target");
            const fpsValue = document.getElementById("current_fps_value");
            
            if (fpsSelect && fpsValue) {
                // Déclencher l'événement change pour mettre à jour le preview
                const event = new Event("change");
                fpsSelect.dispatchEvent(event);
            }
        }
        
        // Mettre à jour les contrôles de grille si on est dans la catégorie grille
        if (category === "grille") {
            const gridEnabled = document.getElementById("canvas_grid_enabled");
            const gridSize = document.getElementById("canvas_grid_size");
            const snapToGrid = document.getElementById("canvas_snap_to_grid");
            const snapToGridContainer = snapToGrid ? snapToGrid.closest('.toggle-switch') : null;
            
            if (gridEnabled && gridSize && snapToGrid && snapToGridContainer) {
                const isEnabled = gridEnabled.checked;
                
                // Activer/désactiver les contrôles selon l'état de la grille
                gridSize.disabled = !isEnabled;
                snapToGrid.disabled = !isEnabled;
                
                // Ajouter/supprimer la classe disabled sur le container
                if (isEnabled) {
                    snapToGridContainer.classList.remove('disabled');
                } else {
                    snapToGridContainer.classList.add('disabled');
                }
            }
        }
        
        // Mettre à jour le display des dimensions si on est dans la catégorie dimensions
        if (category === "dimensions") {
            const formatSelect = document.getElementById("canvas_format");
            const orientationSelect = document.getElementById("canvas_orientation");
            const dpiSelect = document.getElementById("canvas_dpi");
            const widthDisplay = document.getElementById("canvas-width-display");
            const heightDisplay = document.getElementById("canvas-height-display");
            const mmDisplay = document.getElementById("canvas-mm-display");
            
            if (formatSelect && orientationSelect && dpiSelect && widthDisplay && heightDisplay && mmDisplay) {
                const format = formatSelect.value;
                const orientation = orientationSelect.value;
                const dpi = parseInt(dpiSelect.value);
                
                // Dimensions standard en mm pour chaque format
                const formatDimensionsMM = {
                    'A4': {width: 210, height: 297},
                    'A3': {width: 297, height: 420},
                    'A5': {width: 148, height: 210},
                    'Letter': {width: 215.9, height: 279.4},
                    'Legal': {width: 215.9, height: 355.6},
                    'Tabloid': {width: 279.4, height: 431.8}
                };
                
                let dimensions = formatDimensionsMM[format] || formatDimensionsMM['A4'];
                
                // Appliquer l'orientation
                if (orientation === 'landscape') {
                    const temp = dimensions.width;
                    dimensions.width = dimensions.height;
                    dimensions.height = temp;
                }
                
                // Convertir mm en pixels (1mm = dpi/25.4 pixels)
                const widthPx = Math.round((dimensions.width / 25.4) * dpi);
                const heightPx = Math.round((dimensions.height / 25.4) * dpi);
                
                // Mettre à jour les displays
                widthDisplay.textContent = widthPx;
                heightDisplay.textContent = heightPx;
                mmDisplay.textContent = dimensions.width.toFixed(1) + '×' + dimensions.height.toFixed(1) + 'mm';
            }
        }
    };

    // Legacy preview updates function (existing functionality)
    function updateLegacyPreviews(category) {
        // Mettre à jour le preview FPS si on est dans la catégorie performance
        if (category === "performance") {
            const fpsSelect = document.getElementById("canvas_fps_target");
            const fpsValue = document.getElementById("current_fps_value");

            if (fpsSelect && fpsValue) {
                // Déclencher l'événement change pour mettre à jour le preview
                const event = new Event("change");
                fpsSelect.dispatchEvent(event);
            }
        }

        // Mettre à jour les contrôles de grille si on est dans la catégorie grille
        if (category === "grille") {
            const gridEnabled = document.getElementById("canvas_grid_enabled");
            const gridSize = document.getElementById("canvas_grid_size");
            const snapToGrid = document.getElementById("canvas_snap_to_grid");
            const snapToGridContainer = snapToGrid ? snapToGrid.closest('.toggle-switch') : null;

            if (gridEnabled && gridSize && snapToGrid && snapToGridContainer) {
                const isEnabled = gridEnabled.checked;

                // Activer/désactiver les contrôles selon l'état de la grille
                gridSize.disabled = !isEnabled;
                snapToGrid.disabled = !isEnabled;

                // Ajouter/supprimer la classe disabled sur le container
                if (isEnabled) {
                    snapToGridContainer.classList.remove('disabled');
                } else {
                    snapToGridContainer.classList.add('disabled');
                }
            }
        }

        // Mettre à jour le display des dimensions si on est dans la catégorie dimensions
        if (category === "dimensions") {
            const formatSelect = document.getElementById("canvas_format");
            const orientationSelect = document.getElementById("canvas_orientation");
            const dpiSelect = document.getElementById("canvas_dpi");
            const widthDisplay = document.getElementById("canvas-width-display");
            const heightDisplay = document.getElementById("canvas-height-display");
            const mmDisplay = document.getElementById("canvas-mm-display");

            if (formatSelect && orientationSelect && dpiSelect && widthDisplay && heightDisplay && mmDisplay) {
                const format = formatSelect.value;
                const orientation = orientationSelect.value;
                const dpi = parseInt(dpiSelect.value);

                // Dimensions standard en mm pour chaque format
                const formatDimensionsMM = {
                    'A4': {width: 210, height: 297},
                    'A3': {width: 297, height: 420},
                    'A5': {width: 148, height: 210},
                    'Letter': {width: 215.9, height: 279.4},
                    'Legal': {width: 215.9, height: 355.6},
                    'Tabloid': {width: 279.4, height: 431.8}
                };

                let dimensions = formatDimensionsMM[format] || formatDimensionsMM['A4'];

                // Appliquer l'orientation
                if (orientation === 'landscape') {
                    const temp = dimensions.width;
                    dimensions.width = dimensions.height;
                    dimensions.height = temp;
                }

                // Calculer les dimensions en pixels
                const widthPx = Math.round((dimensions.width / 25.4) * dpi);
                const heightPx = Math.round((dimensions.height / 25.4) * dpi);

                // Mettre à jour les displays
                widthDisplay.textContent = widthPx;
                heightDisplay.textContent = heightPx;
                mmDisplay.textContent = dimensions.width.toFixed(1) + '×' + dimensions.height.toFixed(1) + 'mm';
            }
        }
    }

    // Function to update modal values from AJAX data
    function updateModalValuesFromAjax(category, values) {
        console.log('🔄 Updating modal values for', category, 'with data:', values);

        const modalId = `canvas-${category}-modal`;
        console.log('🎯 Looking for modal with ID:', modalId);

        const modal = document.getElementById(modalId);
        if (!modal) {
            console.error('❌ Modal not found:', modalId);
            console.log('📋 Available modals:', Array.from(document.querySelectorAll('[id*="canvas-"]')).map(el => el.id));
            return;
        }

        console.log('✅ Modal found, updating values...');

        // Update values based on category
        switch (category) {
            case 'grille':
                console.log('🎯 Calling updateGrilleModal');
                updateGrilleModalFromAjax(modal, values);
                break;
            default:
                console.warn('⚠️ Unknown category:', category);
        }
    }

    // Update grille modal values from AJAX
    function updateGrilleModalFromAjax(modal, values) {
        console.log('🎯 Updating grille modal with values:', values);

        // Guides enabled
        const guidesCheckbox = modal.querySelector('#canvas_guides_enabled');
        if (guidesCheckbox) {
            const newValue = values.guides_enabled === '1' || values.guides_enabled === true;
            console.log('📝 Setting guides_enabled:', guidesCheckbox.checked, '->', newValue);
            guidesCheckbox.checked = newValue;
        } else {
            console.error('❌ guidesCheckbox not found');
        }

        // Grid enabled
        const gridCheckbox = modal.querySelector('#canvas_grid_enabled');
        if (gridCheckbox) {
            const newValue = values.grid_enabled === '1' || values.grid_enabled === true;
            console.log('📝 Setting grid_enabled:', gridCheckbox.checked, '->', newValue);
            gridCheckbox.checked = newValue;
        } else {
            console.error('❌ gridCheckbox not found');
        }

        // Grid size
        const gridSizeInput = modal.querySelector('#canvas_grid_size');
        if (gridSizeInput) {
            const newValue = values.grid_size || 20;
            console.log('📝 Setting grid_size:', gridSizeInput.value, '->', newValue);
            gridSizeInput.value = newValue;
            gridSizeInput.disabled = !(values.grid_enabled === '1' || values.grid_enabled === true);
        } else {
            console.error('❌ gridSizeInput not found');
        }

        // Snap to grid
        const snapCheckbox = modal.querySelector('#canvas_snap_to_grid');
        if (snapCheckbox) {
            const newValue = values.snap_to_grid === '1' || values.snap_to_grid === true;
            console.log('📝 Setting snap_to_grid:', snapCheckbox.checked, '->', newValue);
            snapCheckbox.checked = newValue;
            snapCheckbox.disabled = !(values.grid_enabled === '1' || values.grid_enabled === true);
        } else {
            console.error('❌ snapCheckbox not found');
        }

        // Update toggle switch classes
        const gridToggle = modal.querySelector('#canvas_grid_enabled').closest('.toggle-switch');
        const snapToggle = modal.querySelector('#canvas_snap_to_grid').closest('.toggle-switch');
        if (gridToggle) {
            const isDisabled = !(values.grid_enabled === '1' || values.grid_enabled === true);
            console.log('🎨 Setting grid toggle disabled:', gridToggle.classList.contains('disabled'), '->', isDisabled);
            gridToggle.classList.toggle('disabled', isDisabled);
        }
        if (snapToggle) {
            const isDisabled = !(values.grid_enabled === '1' || values.grid_enabled === true);
            console.log('🎨 Setting snap toggle disabled:', snapToggle.classList.contains('disabled'), '->', isDisabled);
            snapToggle.classList.toggle('disabled', isDisabled);
        }

        console.log('✅ Grille modal update completed');
    }

    window.updateZoomPreview = function() {
        window.updateCanvasPreviews("performance");
    };
    
    // Ajouter un event listener pour la grille dans la modale
    // Removed: causes infinite loop with updateGrilleModal
    // const gridEnabledCheckbox = document.getElementById("canvas_grid_enabled");
    // if (gridEnabledCheckbox) {
    //     gridEnabledCheckbox.addEventListener("change", function() {
    //         // Mettre à jour les contrôles de grille en temps réel
    //         window.updateCanvasPreviews("grille");
    //     });
    // }
    
    // Ajouter des event listeners pour les dimensions en temps réel
    const formatSelect = document.getElementById("canvas_format");
    const orientationSelect = document.getElementById("canvas_orientation");
    const dpiSelect = document.getElementById("canvas_dpi");
    
    if (formatSelect) {
        formatSelect.addEventListener("change", function() {
            window.updateCanvasPreviews("dimensions");
        });
    }
    
    if (orientationSelect) {
        orientationSelect.addEventListener("change", function() {
            window.updateCanvasPreviews("dimensions");
        });
    }
    
    if (dpiSelect) {
        dpiSelect.addEventListener("change", function() {
            window.updateCanvasPreviews("dimensions");
        });
    }
});

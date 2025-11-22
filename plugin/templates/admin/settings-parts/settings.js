/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 */

document.addEventListener("DOMContentLoaded", function() {
    console.log("Settings loaded");

    window.updateZoomPreview = function() {
        window.updateCanvasPreviews("performance");
    };

    // Ajouter des event listeners pour les dimensions en temps réel
    const formatSelect = document.getElementById("canvas_format");
    const orientationSelect = document.getElementById("canvas_orientation");
    const dpiSelect = document.getElementById("canvas_dpi");

    if (formatSelect) {
        formatSelect.addEventListener("change", function() {
            updateLegacyPreviews("dimensions");
        });
    }
    if (orientationSelect) {
        orientationSelect.addEventListener("change", function() {
            updateLegacyPreviews("dimensions");
        });
    }
    if (dpiSelect) {
        dpiSelect.addEventListener("change", function() {
            updateLegacyPreviews("dimensions");
        });
    }

    // Event listener pour la grille dans la modale (mise à jour en temps réel)
    const gridEnabledCheckbox = document.getElementById("canvas_grid_enabled");
    if (gridEnabledCheckbox) {
        gridEnabledCheckbox.addEventListener("change", function() {
            // Mettre à jour les contrôles de grille en temps réel
            updateLegacyPreviews("grille");
        });
    }

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

                // Convertir mm en pixels (1mm = dpi/25.4 pixels)
                const widthPx = Math.round((dimensions.width / 25.4) * dpi);
                const heightPx = Math.round((dimensions.height / 25.4) * dpi);

                // Mettre à jour les displays
                widthDisplay.textContent = widthPx;
                heightDisplay.textContent = heightPx;
                mmDisplay.textContent = dimensions.width.toFixed(1) + '×' + dimensions.height.toFixed(1) + 'mm';

                // Mettre à jour les dimensions du canvas dans l'éditeur React
                if (window.pdfBuilderReact && window.pdfBuilderReact.updateCanvasDimensions) {
                    window.pdfBuilderReact.updateCanvasDimensions(widthPx, heightPx);
                }
            }
        }
    }

    // Fonction pour mettre à jour le preview de la carte Dimensions & Format
    function updateCardPreview() {
        const formatSelect = document.getElementById("canvas_format");
        const orientationSelect = document.getElementById("canvas_orientation");
        const dpiSelect = document.getElementById("canvas_dpi");

        if (formatSelect && orientationSelect && dpiSelect) {
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

            // Mettre à jour les éléments de la carte
            const cardWidth = document.getElementById("card-canvas-width");
            const cardHeight = document.getElementById("card-canvas-height");
            const cardDpi = document.getElementById("card-canvas-dpi");

            if (cardWidth) cardWidth.textContent = widthPx;
            if (cardHeight) cardHeight.textContent = heightPx;
            if (cardDpi) {
                cardDpi.textContent = `${dpi} DPI - ${format} (${dimensions.width.toFixed(1)}×${dimensions.height.toFixed(1)}mm)`;
            }
        }
    }

    // Ajouter les event listeners pour mettre à jour le preview de la carte en temps réel
    const cardFormatSelect = document.getElementById("canvas_format");
    const cardOrientationSelect = document.getElementById("canvas_orientation");
    const cardDpiSelect = document.getElementById("canvas_dpi");

    if (cardFormatSelect) {
        cardFormatSelect.addEventListener("change", updateCardPreview);
    }
    if (cardOrientationSelect) {
        cardOrientationSelect.addEventListener("change", updateCardPreview);
    }
    if (cardDpiSelect) {
        cardDpiSelect.addEventListener("change", updateCardPreview);
    }

});

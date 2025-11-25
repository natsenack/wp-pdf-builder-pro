/**
 * JavaScript pour la page de paramètres PDF Builder Pro
 * Gestion des interactions utilisateur et AJAX
 */

document.addEventListener("DOMContentLoaded", function() {
    

    window.updateZoomPreview = function() {
        if (window.CanvasPreviewManager && typeof window.CanvasPreviewManager.updatePreviews === 'function') {
            window.CanvasPreviewManager.updatePreviews("performance");
        }
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
        cardFormatSelect.addEventListener("change", function() {
            updateCardPreview();
            if (typeof updateDimensionsCardPreview === 'function') {
                updateDimensionsCardPreview();
            }
        });
    }
    if (cardOrientationSelect) {
        cardOrientationSelect.addEventListener("change", function() {
            updateCardPreview();
            if (typeof updateDimensionsCardPreview === 'function') {
                updateDimensionsCardPreview();
            }
        });
    }
    if (cardDpiSelect) {
        cardDpiSelect.addEventListener("change", function() {
            updateCardPreview();
            if (typeof updateDimensionsCardPreview === 'function') {
                updateDimensionsCardPreview();
            }
        });
    }

    // Add real-time updates for performance card
    const fpsTargetSelect = document.getElementById("canvas_fps_target");
    const memoryJsSelect = document.getElementById("canvas_memory_limit_js");
    const memoryPhpSelect = document.getElementById("canvas_memory_limit_php");
    const lazyLoadingEditorCheckbox = document.getElementById("canvas_lazy_loading_editor");
    const lazyLoadingPluginCheckbox = document.getElementById("canvas_lazy_loading_plugin");

    if (fpsTargetSelect) {
        fpsTargetSelect.addEventListener("change", function() {
            if (typeof updatePerformanceCardPreview === 'function') {
                updatePerformanceCardPreview();
            }
        });
    }
    if (memoryJsSelect) {
        memoryJsSelect.addEventListener("change", function() {
            if (typeof updatePerformanceCardPreview === 'function') {
                updatePerformanceCardPreview();
            }
        });
    }
    if (memoryPhpSelect) {
        memoryPhpSelect.addEventListener("change", function() {
            if (typeof updatePerformanceCardPreview === 'function') {
                updatePerformanceCardPreview();
            }
        });
    }
    if (lazyLoadingEditorCheckbox) {
        lazyLoadingEditorCheckbox.addEventListener("change", function() {
            if (typeof updatePerformanceCardPreview === 'function') {
                updatePerformanceCardPreview();
            }
        });
    }
    if (lazyLoadingPluginCheckbox) {
        lazyLoadingPluginCheckbox.addEventListener("change", function() {
            if (typeof updatePerformanceCardPreview === 'function') {
                updatePerformanceCardPreview();
            }
        });
    }

    // Add real-time updates for autosave card
    const autosaveEnabledCheckbox = document.getElementById("canvas_autosave_enabled");
    const autosaveIntervalInput = document.getElementById("canvas_autosave_interval");
    const versionsLimitInput = document.getElementById("canvas_versions_limit");

    if (autosaveEnabledCheckbox) {
        autosaveEnabledCheckbox.addEventListener("change", function() {
            if (typeof window.updateAutosaveCardPreview === 'function') {
                window.updateAutosaveCardPreview();
            }
        });
    }
    if (autosaveIntervalInput) {
        autosaveIntervalInput.addEventListener("input", function() {
            if (typeof window.updateAutosaveCardPreview === 'function') {
                window.updateAutosaveCardPreview();
            }
        });
    }
    if (versionsLimitInput) {
        versionsLimitInput.addEventListener("input", function() {
            if (typeof window.updateAutosaveCardPreview === 'function') {
                window.updateAutosaveCardPreview();
            }
        });
    }

    // Add real-time updates for apparence card
    const bgColorInput = document.getElementById("canvas_bg_color");
    const borderColorInput = document.getElementById("canvas_border_color");
    const borderWidthInput = document.getElementById("canvas_border_width");
    const shadowEnabledCheckbox = document.getElementById("canvas_shadow_enabled");
    const containerBgColorInput = document.getElementById("canvas_container_bg_color");

    if (bgColorInput) {
        bgColorInput.addEventListener("input", function() {
            if (typeof updateApparenceCardPreview === 'function') {
                updateApparenceCardPreview();
            }
        });
    }
    if (borderColorInput) {
        borderColorInput.addEventListener("input", function() {
            if (typeof updateApparenceCardPreview === 'function') {
                updateApparenceCardPreview();
            }
        });
    }
    if (borderWidthInput) {
        borderWidthInput.addEventListener("input", function() {
            if (typeof updateApparenceCardPreview === 'function') {
                updateApparenceCardPreview();
            }
        });
    }
    if (shadowEnabledCheckbox) {
        shadowEnabledCheckbox.addEventListener("change", function() {
            if (typeof updateApparenceCardPreview === 'function') {
                updateApparenceCardPreview();
            }
        });
    }
    if (containerBgColorInput) {
        containerBgColorInput.addEventListener("input", function() {
            if (typeof updateApparenceCardPreview === 'function') {
                updateApparenceCardPreview();
            }
        });
    }

    // Add real-time updates for zoom card
    const zoomMinInput = document.getElementById("zoom_min");
    const zoomMaxInput = document.getElementById("zoom_max");
    const zoomDefaultInput = document.getElementById("zoom_default");
    const zoomStepInput = document.getElementById("zoom_step");

    if (zoomMinInput) {
        zoomMinInput.addEventListener("input", function() {
            if (typeof updateZoomCardPreview === 'function') {
                updateZoomCardPreview();
            }
        });
    }
    if (zoomMaxInput) {
        zoomMaxInput.addEventListener("input", function() {
            if (typeof updateZoomCardPreview === 'function') {
                updateZoomCardPreview();
            }
        });
    }
    if (zoomDefaultInput) {
        zoomDefaultInput.addEventListener("input", function() {
            if (typeof updateZoomCardPreview === 'function') {
                updateZoomCardPreview();
            }
        });
    }
    if (zoomStepInput) {
        zoomStepInput.addEventListener("input", function() {
            if (typeof updateZoomCardPreview === 'function') {
                updateZoomCardPreview();
            }
        });
    }

    // Add real-time updates for export card
    const exportFormatSelect = document.getElementById("canvas_export_format");
    const exportQualityInput = document.getElementById("canvas_export_quality");

    if (exportFormatSelect) {
        exportFormatSelect.addEventListener("change", function() {
            if (typeof updateExportCardPreview === 'function') {
                updateExportCardPreview();
            }
        });
    }
    if (exportQualityInput) {
        exportQualityInput.addEventListener("input", function() {
            if (typeof updateExportCardPreview === 'function') {
                updateExportCardPreview();
            }
        });
    }

    // Add real-time updates for grille card
    // Note: gridEnabledCheckbox is already declared above for legacy updates

    if (gridEnabledCheckbox) {
        // Add additional listener for the new preview update
        gridEnabledCheckbox.addEventListener("change", function() {
            if (typeof updateGrilleCardPreview === 'function') {
                updateGrilleCardPreview();
            }
        });
    }

    // Add real-time updates for interactions card
    const dragEnabledCheckbox = document.getElementById("canvas_drag_enabled");
    const resizeEnabledCheckbox = document.getElementById("canvas_resize_enabled");
    const multiSelectCheckbox = document.getElementById("canvas_multi_select");
    const selectionModeSelect = document.getElementById("canvas_selection_mode");

    if (dragEnabledCheckbox) {
        dragEnabledCheckbox.addEventListener("change", function() {
            if (typeof updateInteractionsCardPreview === 'function') {
                updateInteractionsCardPreview();
            }
        });
    }
    if (resizeEnabledCheckbox) {
        resizeEnabledCheckbox.addEventListener("change", function() {
            if (typeof updateInteractionsCardPreview === 'function') {
                updateInteractionsCardPreview();
            }
        });
    }
    if (multiSelectCheckbox) {
        multiSelectCheckbox.addEventListener("change", function() {
            if (typeof updateInteractionsCardPreview === 'function') {
                updateInteractionsCardPreview();
            }
        });
    }
    if (selectionModeSelect) {
        selectionModeSelect.addEventListener("change", function() {
            if (typeof updateInteractionsCardPreview === 'function') {
                updateInteractionsCardPreview();
            }
        });
    }

    // Gestionnaire pour le bouton Test Settings
    const testSettingsBtn = document.getElementById('test-settings-btn');
    if (testSettingsBtn) {
        testSettingsBtn.addEventListener('click', function() {
            const resultsSpan = document.getElementById('test-settings-results');
            const outputDiv = document.getElementById('test-settings-output');

            // Désactiver le bouton et afficher "Test en cours..."
            testSettingsBtn.disabled = true;
            testSettingsBtn.textContent = '🔄 Test en cours...';
            if (resultsSpan) resultsSpan.textContent = '';

            // Masquer les résultats précédents
            if (outputDiv) outputDiv.style.display = 'none';

            // Récupérer le nonce de sécurité
            const security = pdf_builder_ajax.nonce;
            if (!security) {
                if (resultsSpan) resultsSpan.innerHTML = '<span style="color: #dc3545;">❌ Erreur: Nonce de sécurité introuvable</span>';
                testSettingsBtn.disabled = false;
                testSettingsBtn.innerHTML = '🧪 Test Settings (Jours 5-7)';
                return;
            }

            // Faire la requête AJAX
            const xhr = new XMLHttpRequest();
            xhr.open('POST', pdf_builder_ajax.ajax_url, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4) {
                    testSettingsBtn.disabled = false;
                    testSettingsBtn.innerHTML = '🧪 Test Settings (Jours 5-7)';

                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);

                            if (response.success) {
                                if (resultsSpan) resultsSpan.innerHTML = '<span style="color: #28a745;">✅ Tests terminés</span>';

                                if (outputDiv && response.data && response.data.output) {
                                    outputDiv.textContent = response.data.output;
                                    outputDiv.style.display = 'block';
                                }
                            } else {
                                if (resultsSpan) resultsSpan.innerHTML = '<span style="color: #dc3545;">❌ Erreur: ' + (response.data ? response.data.message : 'Erreur inconnue') + '</span>';
                            }
                        } catch (e) {
                            if (resultsSpan) resultsSpan.innerHTML = '<span style="color: #dc3545;">❌ Erreur de parsing JSON</span>';
                            console.error('JSON parse error:', e);
                        }
                    } else {
                        if (resultsSpan) resultsSpan.innerHTML = '<span style="color: #dc3545;">❌ Erreur HTTP: ' + xhr.status + '</span>';
                    }
                }
            };

            const params = 'action=pdf_builder_run_settings_tests&security=' + encodeURIComponent(security.value);
            xhr.send(params);
        });
    }

});

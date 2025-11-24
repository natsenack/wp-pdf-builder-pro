                     window.PDF_Builder_Notification_Manager.show_toast('Save error: ' + errorMessage, 'error', 6000);
                                }
                            }
                            throw new Error(errorMessage);
                        }
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);
                        console.log('PDF_BUILDER_DEBUG: AJAX error:', error);
                        
                        this.textContent = originalText;
                        this.disabled = false;

                        if (error.name === 'AbortError') {
                            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
                                window.pdfBuilderNotifications.showToast('Erreur: Timeout de la requête (30 secondes)', 'error', 6000);
                            } else if (window.PDF_Builder_Notification_Manager) {
                                window.PDF_Builder_Notification_Manager.show_toast('Erreur: Timeout de la requête (30 secondes)', 'error', 6000);
                            }
                        } else {
                            if (window.pdfBuilderNotifications && window.pdfBuilderNotifications.showToast) {
                                window.pdfBuilderNotifications.showToast('Erreur lors de la sauvegarde: ' + error.message, 'error', 6000);
                            } else if (window.PDF_Builder_Notification_Manager) {
                                window.PDF_Builder_Notification_Manager.show_toast('Erreur lors de la sauvegarde: ' + error.message, 'error', 6000);
                            }
                        }
                    });
                });
            });

            // Initialize zoom preview if function exists
            // Removed automatic updateZoomPreview call to prevent conflicts with manual modal updates
            // if (typeof updateZoomPreview === 'function') {
            //     // Delay initialization to ensure DOM is ready
            //     setTimeout(updateZoomPreview, 1000);
            // }

            isInitialized = true;
            

        } catch (e) {
            
        }
    }

    // Function to update modal values in DOM
    function updateModalValues(category, values) {
        const modalId = `canvas-${category}-modal`;
        const modal = document.getElementById(modalId);
        if (!modal) {
            return;
        }

        // Update values based on category
        switch (category) {
            case 'grille':
                updateGrilleModal(modal, values);
                break;
            case 'dimensions':
                updateDimensionsModal(modal, values);
                break;
            case 'zoom':
                updateZoomModal(modal, values);
                break;
            case 'apparence':
                updateApparenceModal(modal, values);
                break;
            case 'interactions':
                updateInteractionsModal(modal, values);
                break;
            case 'export':
                updateExportModal(modal, values);
                break;
            case 'performance':
                updatePerformanceModal(modal, values);
                break;
            case 'autosave':
                updateAutosaveModal(modal, values);
                break;
            case 'debug':
                updateDebugModal(modal, values);
                break;
            default:
                console.warn('⚠️ Unknown category:', category);
        }
    }

    // Update grille modal values
    function updateGrilleModal(modal, values) {
        const isGridEnabled = values.grid_enabled === '1' || values.grid_enabled === true;

        // Update checkboxes
        const guidesCheckbox = modal.querySelector('#canvas_guides_enabled');
        if (guidesCheckbox) {
            guidesCheckbox.checked = values.guides_enabled === '1' || values.guides_enabled === true;
        }

        const gridCheckbox = modal.querySelector('#canvas_grid_enabled');
        if (gridCheckbox) {
            gridCheckbox.checked = isGridEnabled;
        }

        // Update grid size input
        const gridSizeInput = modal.querySelector('#canvas_grid_size');
        if (gridSizeInput) {
            gridSizeInput.value = values.grid_size || 20;
            gridSizeInput.disabled = !isGridEnabled;
        }

        // Update snap to grid checkbox
        const snapCheckbox = modal.querySelector('#canvas_snap_to_grid');
        if (snapCheckbox) {
            snapCheckbox.checked = values.snap_to_grid === '1' || values.snap_to_grid === true;
            snapCheckbox.disabled = !isGridEnabled;
        }

        // Update toggle switch visual states
        const gridToggle = gridCheckbox?.closest('.toggle-switch');
        const snapToggle = snapCheckbox?.closest('.toggle-switch');

        // Note: gridToggle should NEVER be disabled - it's the main control
        // Only dependent controls (snapToggle) should be disabled when grid is off
        if (snapToggle) {
            snapToggle.classList.toggle('disabled', !isGridEnabled);
        }
    }

    // Update apparence modal values
    function updateApparenceModal(modal, values) {
        

        // Update canvas background color
        const canvasBgColorInput = modal.querySelector('#canvas_bg_color');
        if (canvasBgColorInput && values.canvas_bg_color) {
            canvasBgColorInput.value = values.canvas_bg_color;
            
        }

        // Update container background color
        const containerBgColorInput = modal.querySelector('#canvas_container_bg_color');
        if (containerBgColorInput && values.canvas_container_bg_color) {
            containerBgColorInput.value = values.canvas_container_bg_color;
            
        }

        // Update border color
        const borderColorInput = modal.querySelector('#canvas_border_color');
        if (borderColorInput && values.canvas_border_color) {
            borderColorInput.value = values.canvas_border_color;
            
        }

        // Update border width
        const borderWidthInput = modal.querySelector('#canvas_border_width');
        if (borderWidthInput && values.canvas_border_width !== undefined) {
            borderWidthInput.value = values.canvas_border_width;
            
        }

        // Update shadow enabled checkbox
        const shadowCheckbox = modal.querySelector('#canvas_shadow_enabled');
        if (shadowCheckbox) {
            const isEnabled = values.canvas_shadow_enabled === '1' || values.canvas_shadow_enabled === true;
            shadowCheckbox.checked = isEnabled;
            
        }
    }
    function updateInteractionsModal(modal, values) {
        // Update drag enabled
        const dragCheckbox = modal.querySelector('#canvas_drag_enabled');
        if (dragCheckbox) {
            dragCheckbox.checked = values.drag_enabled === '1' || values.drag_enabled === true;
        }

        // Update resize enabled
        const resizeCheckbox = modal.querySelector('#canvas_resize_enabled');
        if (resizeCheckbox) {
            resizeCheckbox.checked = values.resize_enabled === '1' || values.resize_enabled === true;
        }

        // Update rotate enabled
        const rotateCheckbox = modal.querySelector('#canvas_rotate_enabled');
        if (rotateCheckbox) {
            rotateCheckbox.checked = values.rotate_enabled === '1' || values.rotate_enabled === true;
        }

        // Update multi select
        const multiSelectCheckbox = modal.querySelector('#canvas_multi_select');
        if (multiSelectCheckbox) {
            multiSelectCheckbox.checked = values.multi_select === '1' || values.multi_select === true;
        }

        // Update selection mode
        const selectionModeSelect = modal.querySelector('#canvas_selection_mode');
        if (selectionModeSelect) {
            selectionModeSelect.value = values.selection_mode || 'click';
        }

        // Update keyboard shortcuts
        const keyboardCheckbox = modal.querySelector('#canvas_keyboard_shortcuts');
        if (keyboardCheckbox) {
            keyboardCheckbox.checked = values.keyboard_shortcuts === '1' || values.keyboard_shortcuts === true;
        }

        // Apply dependency logic: disable selection mode when multi-select is disabled
        updateSelectionModeDependency(modal);
    }

    // Function to handle dependency between multi-select and selection mode
    function updateSelectionModeDependency(modal) {
        const multiSelectCheckbox = modal.querySelector('#canvas_multi_select');
        const selectionModeSelect = modal.querySelector('#canvas_selection_mode');
        const selectionModeLabel = modal.querySelector('label[for="canvas_selection_mode"]');

        if (!multiSelectCheckbox || !selectionModeSelect) return;

        const isMultiSelectEnabled = multiSelectCheckbox.checked;

        // Enable/disable selection mode based on multi-select
        selectionModeSelect.disabled = !isMultiSelectEnabled;

        // Update visual appearance
        if (isMultiSelectEnabled) {
            selectionModeSelect.style.opacity = '1';
            if (selectionModeLabel) {
                selectionModeLabel.style.opacity = '1';
            }
        } else {
            selectionModeSelect.style.opacity = '0.5';
            if (selectionModeLabel) {
                selectionModeLabel.style.opacity = '0.5';
            }
        }
    }

    // Function to initialize modal event listeners
    function initializeModalEventListeners(modal) {
        // Handle interactions modal dependencies
        if (modal.id === 'canvas-interactions-modal') {
            const multiSelectCheckbox = modal.querySelector('#canvas_multi_select');
            if (multiSelectCheckbox) {
                multiSelectCheckbox.addEventListener('change', function() {
                    updateSelectionModeDependency(modal);
                });
            }
        }

        // Note: Real-time preview updates have been removed.
        // Previews now only update after successful save operations.
    }
    function updateExportModal(modal, values) {
        // Update export format
        const formatSelect = modal.querySelector('#canvas_export_format');
        if (formatSelect && values.canvas_export_format) {
            formatSelect.value = values.canvas_export_format;
        }

        // Update export quality
        const qualityInput = modal.querySelector('#canvas_export_quality');
        if (qualityInput && values.canvas_export_quality !== undefined) {
            qualityInput.value = values.canvas_export_quality;
        }

        // Update transparent background checkbox
        const transparentCheckbox = modal.querySelector('#canvas_export_transparent');
        if (transparentCheckbox) {
            transparentCheckbox.checked = values.canvas_export_transparent === '1' || values.canvas_export_transparent === true;
        }
    }
    function updatePerformanceModal(modal, values) {
        // Update FPS target
        const fpsSelect = modal.querySelector('#canvas_fps_target');
        if (fpsSelect && values.canvas_fps_target) {
            fpsSelect.value = values.canvas_fps_target;
        }

        // Update memory limits
        const memoryJsSelect = modal.querySelector('#canvas_memory_limit_js');
        if (memoryJsSelect && values.canvas_memory_limit_js) {
            memoryJsSelect.value = values.canvas_memory_limit_js;
        }

        const memoryPhpSelect = modal.querySelector('#canvas_memory_limit_php');
        if (memoryPhpSelect && values.canvas_memory_limit_php) {
            memoryPhpSelect.value = values.canvas_memory_limit_php;
        }

        // Update timeout
        const timeoutSelect = modal.querySelector('#canvas_response_timeout');
        if (timeoutSelect && values.canvas_response_timeout) {
            timeoutSelect.value = values.canvas_response_timeout;
        }

        // Update checkboxes
        const lazyEditorCheckbox = modal.querySelector('#canvas_lazy_loading_editor');
        if (lazyEditorCheckbox) {
            lazyEditorCheckbox.checked = values.canvas_lazy_loading_editor === '1' || values.canvas_lazy_loading_editor === true;
        }

        const preloadCheckbox = modal.querySelector('#canvas_preload_critical');
        if (preloadCheckbox) {
            preloadCheckbox.checked = values.canvas_preload_critical === '1' || values.canvas_preload_critical === true;
        }

        const lazyPluginCheckbox = modal.querySelector('#canvas_lazy_loading_plugin');
        if (lazyPluginCheckbox) {
            lazyPluginCheckbox.checked = values.canvas_lazy_loading_plugin === '1' || values.canvas_lazy_loading_plugin === true;
        }
    }

    function updateZoomModal(modal, values) {
        // Update zoom minimum
        const zoomMinInput = modal.querySelector('#zoom_min');
        if (zoomMinInput && values.canvas_zoom_min !== undefined) {
            zoomMinInput.value = values.canvas_zoom_min;
        }

        // Update zoom maximum
        const zoomMaxInput = modal.querySelector('#zoom_max');
        if (zoomMaxInput && values.canvas_zoom_max !== undefined) {
            zoomMaxInput.value = values.canvas_zoom_max;
        }

        // Update zoom default
        const zoomDefaultInput = modal.querySelector('#zoom_default');
        if (zoomDefaultInput && values.canvas_zoom_default !== undefined) {
            zoomDefaultInput.value = values.canvas_zoom_default;
        }

        // Update zoom step
        const zoomStepInput = modal.querySelector('#zoom_step');
        if (zoomStepInput && values.canvas_zoom_step !== undefined) {
            zoomStepInput.value = values.canvas_zoom_step;
        }
    }

    function updateAutosaveModal(modal, values) {
        // Update autosave enabled
        const autosaveCheckbox = modal.querySelector('#canvas_autosave_enabled');
        if (autosaveCheckbox) {
            autosaveCheckbox.checked = values.canvas_autosave_enabled === '1' || values.canvas_autosave_enabled === true;
        }

        // Update autosave interval
        const intervalInput = modal.querySelector('#canvas_autosave_interval');
        if (intervalInput && values.canvas_autosave_interval !== undefined) {
            intervalInput.value = values.canvas_autosave_interval;
        }

        // Update history enabled
        const historyCheckbox = modal.querySelector('#canvas_history_enabled');
        if (historyCheckbox) {
            historyCheckbox.checked = values.canvas_history_enabled === '1' || values.canvas_history_enabled === true;
        }
    }
    function updateDebugModal(modal, values) {
        // Update debug enabled
        const debugCheckbox = modal.querySelector('#canvas_debug_enabled');
        if (debugCheckbox) {
            debugCheckbox.checked = values.canvas_debug_enabled === '1' || values.canvas_debug_enabled === true;
        }

        // Update performance monitoring
        const perfCheckbox = modal.querySelector('#canvas_performance_monitoring');
        if (perfCheckbox) {
            perfCheckbox.checked = values.canvas_performance_monitoring === '1' || values.canvas_performance_monitoring === true;
        }

        // Update error reporting
        const errorCheckbox = modal.querySelector('#canvas_error_reporting');
        if (errorCheckbox) {
            errorCheckbox.checked = values.canvas_error_reporting === '1' || values.canvas_error_reporting === true;
        }
    }

    // Update dimensions modal values
    function updateDimensionsModal(modal, values) {
        // Update format select
        const formatSelect = modal.querySelector('#canvas_format');
        if (formatSelect && values.format) {
            formatSelect.value = values.format;
        }

        // Update DPI select
        const dpiSelect = modal.querySelector('#canvas_dpi');
        if (dpiSelect && values.dpi) {
            dpiSelect.value = values.dpi;
        }

        // Update calculated dimensions display
        updateCalculatedDimensions(modal, values.format || 'A4', values.dpi || 96);
    }

    // Function to update calculated dimensions display
    function updateCalculatedDimensions(modal, format, dpi) {
        // Utiliser les dimensions standard centralisées
        const formatDimensionsMM = window.pdfBuilderPaperFormats || {
            'A4': { width: 210, height: 297 },
            'A3': { width: 297, height: 420 },
            'A5': { width: 148, height: 210 },
            'Letter': { width: 215.9, height: 279.4 },
            'Legal': { width: 215.9, height: 355.6 },
            'Tabloid': { width: 279.4, height: 431.8 }
        };

        const dimensions = formatDimensionsMM[format] || formatDimensionsMM['A4'];

        // Calculer les dimensions en pixels (1 inch = 25.4mm, 1 inch = dpi pixels)
        const pixelsPerMM = dpi / 25.4;
        const widthPx = Math.round(dimensions.width * pixelsPerMM);
        const heightPx = Math.round(dimensions.height * pixelsPerMM);

        // Update pixel dimensions display
        const widthDisplay = modal.querySelector('#canvas-width-display');
        const heightDisplay = modal.querySelector('#canvas-height-display');
        if (widthDisplay) widthDisplay.textContent = widthPx;
        if (heightDisplay) heightDisplay.textContent = heightPx;

        // Update mm dimensions display
        const mmDisplay = modal.querySelector('#canvas-mm-display');
        if (mmDisplay) {
            mmDisplay.textContent = dimensions.width + '×' + dimensions.height + 'mm';
        }
    }

    // Function to update canvas card previews in real-time
    window.updateCanvasPreviews = function(category) {
        console.log('updateCanvasPreviews called with category:', category);
        updateDimensionsCardPreview();

        // Update apparence card preview
        if (category === 'apparence' || category === 'all') {
            updateApparenceCardPreview();
        }

        // Update grille card preview
        if (category === 'grille' || category === 'all') {
            updateGrilleCardPreview();
        }

        // Update zoom card preview
        if (category === 'zoom' || category === 'all') {
            updateZoomCardPreview();
        }

        // Update interactions card preview
        if (category === 'interactions' || category === 'all') {
            updateInteractionsCardPreview();
        }

        // Update export card preview
        if (category === 'export' || category === 'all') {
            updateExportCardPreview();
        }

        // Update performance card preview
        if (category === 'performance' || category === 'all') {
            updatePerformanceCardPreview();
        }

        // Update autosave card preview
        if (category === 'autosave' || category === 'all') {
            updateAutosaveCardPreview();
        }
    };

    // Update dimensions card preview
    window.updateDimensionsCardPreview = function() {
        console.log('updateDimensionsCardPreview called');
        // Try to get values from modal inputs first (real-time), then from settings
        const formatInput = document.getElementById("canvas_format");
        const dpiInput = document.getElementById("canvas_dpi");

        const format = formatInput ? formatInput.value : (window.pdfBuilderCanvasSettings?.default_canvas_format || 'A4');
        const dpi = dpiInput ? parseInt(dpiInput.value) : (parseInt(window.pdfBuilderCanvasSettings?.default_canvas_dpi) || 96);

        // Orientation is always portrait for now
        const orientation = 'portrait';

        // Utiliser les dimensions standard centralisées
        const formatDimensions = window.pdfBuilderPaperFormats || {
            'A4': { width: 210, height: 297 },
            'A3': { width: 297, height: 420 },
            'A5': { width: 148, height: 210 },
            'Letter': { width: 215.9, height: 279.4 },
            'Legal': { width: 215.9, height: 355.6 },
            'Tabloid': { width: 279.4, height: 431.8 }
        };

        const dimensions = formatDimensions[format] || formatDimensions['A4'];

        // Calculer les dimensions en pixels
        const pixelsPerMM = dpi / 25.4;
        const widthPx = Math.round(dimensions.width * pixelsPerMM);
        const heightPx = Math.round(dimensions.height * pixelsPerMM);

        // Mettre à jour les éléments HTML
        const widthEl = document.getElementById('card-canvas-width');
        const heightEl = document.getElementById('card-canvas-height');
        const dpiEl = document.getElementById('card-canvas-dpi');

        if (widthEl) {
            widthEl.textContent = widthPx;
        }

        if (heightEl) {
            heightEl.textContent = heightPx;
        }

        if (dpiEl) {
            const dpiText = `${dpi} DPI - ${format} (${dimensions.width.toFixed(1)}×${dimensions.height.toFixed(1)}mm)`;
            dpiEl.textContent = dpiText;
        }
    }

    // Update apparence card preview
    window.updateApparenceCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const bgColorInput = document.getElementById("canvas_bg_color");
        const borderColorInput = document.getElementById("canvas_border_color");

        const bgColor = bgColorInput ? bgColorInput.value : (window.pdfBuilderCanvasSettings?.canvas_background_color || '#ffffff');
        const borderColor = borderColorInput ? borderColorInput.value : (window.pdfBuilderCanvasSettings?.border_color || '#cccccc');

        // Update color previews in the card
        const bgPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.bg');
        const borderPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.border');

        if (bgPreview && bgColor) {
            bgPreview.style.backgroundColor = bgColor;
        }
        if (borderPreview && borderColor) {
            borderPreview.style.backgroundColor = borderColor;
        }
    };

    // Update grille card preview
    window.updateGrilleCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const gridEnabledInput = document.getElementById("canvas_grid_enabled");

        const isGridEnabled = gridEnabledInput ? gridEnabledInput.checked : (window.pdfBuilderCanvasSettings?.show_grid === true || window.pdfBuilderCanvasSettings?.show_grid === '1');

        const gridCard = document.querySelector('.canvas-card[data-category="grille"]');
        if (!gridCard) return;

        const gridContainer = gridCard.querySelector('.grid-preview-container');
        if (gridContainer) {
            if (isGridEnabled) {
                gridContainer.classList.add('grid-active');
                gridContainer.classList.remove('grid-inactive');
            } else {
                gridContainer.classList.add('grid-inactive');
                gridContainer.classList.remove('grid-active');
            }
        }
    };

    // Update interactions card preview
    window.updateInteractionsCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const dragEnabledInput = document.getElementById("canvas_drag_enabled");
        const resizeEnabledInput = document.getElementById("canvas_resize_enabled");
        const multiSelectInput = document.getElementById("canvas_multi_select");
        const selectionModeInput = document.getElementById("canvas_selection_mode");

        const dragEnabled = dragEnabledInput ? dragEnabledInput.checked : (window.pdfBuilderCanvasSettings?.drag_enabled === true || window.pdfBuilderCanvasSettings?.drag_enabled === '1');
        const resizeEnabled = resizeEnabledInput ? resizeEnabledInput.checked : (window.pdfBuilderCanvasSettings?.resize_enabled === true || window.pdfBuilderCanvasSettings?.resize_enabled === '1');
        const multiSelect = multiSelectInput ? multiSelectInput.checked : (window.pdfBuilderCanvasSettings?.multi_select === true || window.pdfBuilderCanvasSettings?.multi_select === '1');
        const selectionMode = selectionModeInput ? selectionModeInput.value : (window.pdfBuilderCanvasSettings?.selection_mode || 'rectangle');

        const interactionsCard = document.querySelector('.canvas-card[data-category="interactions"]');
        if (!interactionsCard) return;

        const miniCanvas = interactionsCard.querySelector('.mini-canvas');
        if (miniCanvas) {
            // Update drag state
            if (dragEnabled) {
                miniCanvas.classList.add('drag-enabled');
            } else {
                miniCanvas.classList.remove('drag-enabled');
            }

            // Update resize state
            if (resizeEnabled) {
                miniCanvas.classList.add('resize-enabled');
            } else {
                miniCanvas.classList.remove('resize-enabled');
            }

            // Update multi-select state
            if (multiSelect) {
                miniCanvas.classList.add('multi-select-enabled');
            } else {
                miniCanvas.classList.remove('multi-select-enabled');
            }
        }

        // Update selection mode indicator
        const selectionModeIndicator = interactionsCard.querySelector('.selection-mode-indicator');
        if (selectionModeIndicator) {
            // Remove active class from all mode icons
            const modeIcons = selectionModeIndicator.querySelectorAll('.mode-icon');
            modeIcons.forEach(icon => icon.classList.remove('active'));

            // Add active class to current mode based on selection mode
            if (selectionMode === 'rectangle' && modeIcons[0]) {
                modeIcons[0].classList.add('active');
            } else if (selectionMode === 'lasso' && modeIcons[1]) {
                modeIcons[1].classList.add('active');
            } else if (selectionMode === 'click' && modeIcons[2]) {
                modeIcons[2].classList.add('active');
            }
        }
    };

    // Update export card preview
    window.updateExportCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const exportFormatInput = document.getElementById("canvas_export_format");
        const exportQualityInput = document.getElementById("canvas_export_quality");

        const exportFormat = exportFormatInput ? exportFormatInput.value : (window.pdfBuilderCanvasSettings?.export_format || 'pdf');
        const exportQuality = exportQualityInput ? parseInt(exportQualityInput.value) : (window.pdfBuilderCanvasSettings?.export_quality || 90);

        const exportCard = document.querySelector('.canvas-card[data-category="export"]');
        if (!exportCard) return;

        // Update format badges
        const formatBadges = exportCard.querySelectorAll('.format-badge');
        formatBadges.forEach(badge => badge.classList.remove('active'));

        const activeBadge = exportCard.querySelector(`.format-badge.${exportFormat.toLowerCase()}`);
        if (activeBadge) {
            activeBadge.classList.add('active');
        }

        // Update quality bar
        const qualityFill = exportCard.querySelector('.quality-fill');
        const qualityText = exportCard.querySelector('.quality-text');

        if (qualityFill && qualityText) {
            const quality = parseInt(exportQuality);
            qualityFill.style.width = quality + '%';
            qualityText.textContent = quality + '%';
        }
    };

    // Update performance card preview
    window.updatePerformanceCardPreview = function() {
        console.log('updatePerformanceCardPreview called');
        // Try to get values from modal inputs first (real-time), then from settings
        const fpsTargetInput = document.getElementById("canvas_fps_target");
        const memoryJsInput = document.getElementById("canvas_memory_limit_js");
        const memoryPhpInput = document.getElementById("canvas_memory_limit_php");
        const lazyLoadingEditorInput = document.getElementById("canvas_lazy_loading_editor");
        const lazyLoadingPluginInput = document.getElementById("canvas_lazy_loading_plugin");

        const fpsTarget = fpsTargetInput ? parseInt(fpsTargetInput.value) : (window.pdfBuilderCanvasSettings?.fps_target || 60);
        const memoryJs = memoryJsInput ? parseInt(memoryJsInput.value) : (window.pdfBuilderCanvasSettings?.memory_limit_js || 128);
        const memoryPhp = memoryPhpInput ? parseInt(memoryPhpInput.value) : (window.pdfBuilderCanvasSettings?.memory_limit_php || 256);
        const lazyLoadingEditor = lazyLoadingEditorInput ? lazyLoadingEditorInput.checked : (window.pdfBuilderCanvasSettings?.lazy_loading_editor === true || window.pdfBuilderCanvasSettings?.lazy_loading_editor === '1');
        const lazyLoadingPlugin = lazyLoadingPluginInput ? lazyLoadingPluginInput.checked : (window.pdfBuilderCanvasSettings?.lazy_loading_plugin === true || window.pdfBuilderCanvasSettings?.lazy_loading_plugin === '1');

        // Update FPS metric
        const fpsValue = document.querySelector('.canvas-card[data-category="performance"] .metric-value:first-child');
        if (fpsValue) {
            fpsValue.textContent = fpsTarget;
        }

        // Update memory metrics
        const memoryValues = document.querySelectorAll('.canvas-card[data-category="performance"] .metric-value');
        if (memoryValues[1]) {
            memoryValues[1].textContent = memoryJs + 'MB';
        }
        if (memoryValues[2]) {
            memoryValues[2].textContent = memoryPhp + 'MB';
        }

        // Update lazy loading status
        const statusIndicator = document.querySelector('.canvas-card[data-category="performance"] .status-indicator');
        if (statusIndicator) {
            const isActive = lazyLoadingEditor && lazyLoadingPlugin;
            statusIndicator.classList.toggle('active', isActive);
            statusIndicator.classList.toggle('inactive', !isActive);
        }
    };

    // Update autosave card preview
    window.updateAutosaveCardPreview = function() {
        // Try to get values from modal inputs first (real-time), then from settings
        const autosaveEnabledInput = document.getElementById("canvas_autosave_enabled");
        const autosaveIntervalInput = document.getElementById("canvas_autosave_interval");
        const versionsLimitInput = document.getElementById("canvas_history_max");

        const autosaveInterval = autosaveIntervalInput ? parseInt(autosaveIntervalInput.value) : (window.pdfBuilderCanvasSettings?.autosave_interval || 5);
        const autosaveEnabled = autosaveEnabledInput ? autosaveEnabledInput.checked : (window.pdfBuilderCanvasSettings?.autosave_enabled === true || window.pdfBuilderCanvasSettings?.autosave_enabled === '1');
        const versionsLimit = versionsLimitInput ? parseInt(versionsLimitInput.value) : (window.pdfBuilderCanvasSettings?.versions_limit || 10);

        const autosaveCard = document.querySelector('.canvas-card[data-category="autosave"]');
        if (!autosaveCard) return;

        // Update timer display
        const timerDisplay = autosaveCard.querySelector('.autosave-timer');
        if (timerDisplay) {
            const minutes = autosaveInterval;
            timerDisplay.textContent = minutes + 'min';
        }

        // Update status
        const statusIndicator = autosaveCard.querySelector('.autosave-status');
        if (statusIndicator) {
            if (autosaveEnabled) {
                statusIndicator.classList.add('active');
            } else {
                statusIndicator.classList.remove('active');
            }
        }

        // Update versions dots
        const versionDots = autosaveCard.querySelectorAll('.version-dot');
        if (versionDots.length > 0) {
            const limit = parseInt(versionsLimit);
            versionDots.forEach((dot, index) => {
                if (index < limit) {
                    dot.style.display = 'block';
                } else {
                    dot.style.display = 'none';
                }
            });
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initializeModals();
            initializeAutosaveRealTimePreview();
            // Initialize all canvas previews on page load - wait longer for settings to be loaded
            setTimeout(function() {
                if (typeof updateCanvasPreviews === 'function' && window.pdfBuilderCanvasSettings) {
                    updateCanvasPreviews('all');
                }
            }, 500); // Increased from 100ms to 500ms
        });
    } else {
        // DOM already loaded
        initializeModals();
        initializeAutosaveRealTimePreview();
        // Initialize all canvas previews on page load - wait longer for settings to be loaded
        setTimeout(function() {
            if (typeof updateCanvasPreviews === 'function' && window.pdfBuilderCanvasSettings) {
                updateCanvasPreviews('all');
            }
        }, 500); // Increased from 100ms to 500ms
    }

    // Also try to initialize after a short delay as backup
    setTimeout(function() {
        if (!isInitialized) {
            
            initializeModals();
        }
    }, 2000);

    // Real-time preview updates for dimensions modal
    function initializeDimensionsRealTimePreview() {
        // Listen for changes in dimensions modal fields
        ['change', 'input'].forEach(eventType => {
            document.addEventListener(eventType, function(event) {
                const target = event.target;
                const modal = target.closest('.canvas-modal[data-category="dimensions"]');
                
                if (modal && (target.id === 'canvas_format' || target.id === 'canvas_dpi')) {
                    // Update window.pdfBuilderCanvasSettings temporarily for preview
                    if (window.pdfBuilderCanvasSettings) {
                        if (target.id === 'canvas_format') {
                            window.pdfBuilderCanvasSettings.default_canvas_format = target.value;
                        } else if (target.id === 'canvas_dpi') {
                            window.pdfBuilderCanvasSettings.default_canvas_dpi = parseInt(target.value);
                        }
                        
                        // Update preview immediately
                        if (typeof updateDimensionsCardPreview === 'function') {
                            updateDimensionsCardPreview();
                        }
                    }
                }
            });
        });
    }

    // Real-time preview updates for apparence modal
    function initializeApparenceRealTimePreview() {
        // Listen for changes in apparence modal fields
        document.addEventListener('change', function(event) {
            const target = event.target;
            const modal = target.closest('.canvas-modal[data-category="apparence"]');
            
            if (modal && (target.id === 'canvas_bg_color' || target.id === 'canvas_border_color' || 
                         target.id === 'canvas_border_width' || target.id === 'canvas_shadow_enabled' ||
                         target.id === 'canvas_container_bg_color')) {
                // Update window.pdfBuilderCanvasSettings temporarily for preview
                if (window.pdfBuilderCanvasSettings) {
                    if (target.id === 'canvas_bg_color') {
                        window.pdfBuilderCanvasSettings.canvas_background_color = target.value;
                    } else if (target.id === 'canvas_border_color') {
                        window.pdfBuilderCanvasSettings.border_color = target.value;
                    } else if (target.id === 'canvas_border_width') {
                        window.pdfBuilderCanvasSettings.border_width = parseInt(target.value);
                    } else if (target.id === 'canvas_shadow_enabled') {
                        window.pdfBuilderCanvasSettings.shadow_enabled = target.checked;
                    } else if (target.id === 'canvas_container_bg_color') {
                        window.pdfBuilderCanvasSettings.container_background_color = target.value;
                    }
                    
                    // Update preview immediately
                    if (typeof updateApparenceCardPreview === 'function') {
                        updateApparenceCardPreview();
                    }
                }
            }
        });
    }

    // Initialize real-time preview for dimensions
    initializeDimensionsRealTimePreview();

    // Initialize real-time preview for apparence
    initializeApparenceRealTimePreview();

    // Initialize real-time preview for interactions
    initializeInteractionsRealTimePreview();

    // Synchronize dimensions modal values with current settings
    function synchronizeDimensionsModalValues(modal) {
        if (!modal || !window.pdfBuilderCanvasSettings) return;

        // Store original values for restoration if modal is cancelled
        modal._originalDimensionsSettings = {
            format: window.pdfBuilderCanvasSettings.default_canvas_format,
            dpi: window.pdfBuilderCanvasSettings.default_canvas_dpi
        };

        const formatSelect = modal.querySelector('#canvas_format');
        const dpiSelect = modal.querySelector('#canvas_dpi');

        if (formatSelect) {
            formatSelect.value = window.pdfBuilderCanvasSettings.default_canvas_format || 'A4';
        }

        if (dpiSelect) {
            dpiSelect.value = window.pdfBuilderCanvasSettings.default_canvas_dpi || 96;
        }
    }

    // Synchronize apparence modal values with current settings
    function synchronizeApparenceModalValues(modal) {
        if (!modal || !window.pdfBuilderCanvasSettings) return;

        // Store original values for restoration if modal is cancelled
        modal._originalApparenceSettings = {
            bgColor: window.pdfBuilderCanvasSettings.canvas_background_color,
            borderColor: window.pdfBuilderCanvasSettings.border_color,
            borderWidth: window.pdfBuilderCanvasSettings.border_width,
            shadowEnabled: window.pdfBuilderCanvasSettings.shadow_enabled,
            containerBgColor: window.pdfBuilderCanvasSettings.container_background_color
        };

        const bgColorInput = modal.querySelector('#canvas_bg_color');
        const borderColorInput = modal.querySelector('#canvas_border_color');
        const borderWidthInput = modal.querySelector('#canvas_border_width');
        const shadowEnabledInput = modal.querySelector('#canvas_shadow_enabled');
        const containerBgColorInput = modal.querySelector('#canvas_container_bg_color');

        if (bgColorInput) {
            bgColorInput.value = window.pdfBuilderCanvasSettings.canvas_background_color || '#ffffff';
        }
        if (borderColorInput) {
            borderColorInput.value = window.pdfBuilderCanvasSettings.border_color || '#cccccc';
        }
        if (borderWidthInput) {
            borderWidthInput.value = window.pdfBuilderCanvasSettings.border_width || 1;
        }
        if (shadowEnabledInput) {
            shadowEnabledInput.checked = window.pdfBuilderCanvasSettings.shadow_enabled || false;
        }
        if (containerBgColorInput) {
            containerBgColorInput.value = window.pdfBuilderCanvasSettings.container_background_color || '#f8f9fa';
        }
    }

    // Synchronize interactions modal values with current settings
    function synchronizeInteractionsModalValues(modal) {
        if (!modal || !window.pdfBuilderCanvasSettings) return;

        // Store original values for restoration if modal is cancelled
        modal._originalInteractionsSettings = {
            dragEnabled: window.pdfBuilderCanvasSettings.drag_enabled,
            resizeEnabled: window.pdfBuilderCanvasSettings.resize_enabled,
            rotateEnabled: window.pdfBuilderCanvasSettings.rotate_enabled,
            multiSelect: window.pdfBuilderCanvasSettings.multi_select,
            selectionMode: window.pdfBuilderCanvasSettings.selection_mode,
            keyboardShortcuts: window.pdfBuilderCanvasSettings.keyboard_shortcuts
        };

        const dragCheckbox = modal.querySelector('#canvas_drag_enabled');
        const resizeCheckbox = modal.querySelector('#canvas_resize_enabled');
        const rotateCheckbox = modal.querySelector('#canvas_rotate_enabled');
        const multiSelectCheckbox = modal.querySelector('#canvas_multi_select');
        const selectionModeSelect = modal.querySelector('#canvas_selection_mode');
        const keyboardCheckbox = modal.querySelector('#canvas_keyboard_shortcuts');

        if (dragCheckbox) {
            dragCheckbox.checked = window.pdfBuilderCanvasSettings.drag_enabled ?? true;
        }
        if (resizeCheckbox) {
            resizeCheckbox.checked = window.pdfBuilderCanvasSettings.resize_enabled ?? true;
        }
        if (rotateCheckbox) {
            rotateCheckbox.checked = window.pdfBuilderCanvasSettings.rotate_enabled ?? true;
        }
        if (multiSelectCheckbox) {
            multiSelectCheckbox.checked = window.pdfBuilderCanvasSettings.multi_select ?? true;
        }
        if (selectionModeSelect) {
            selectionModeSelect.value = window.pdfBuilderCanvasSettings.selection_mode || 'bounding_box';
        }
        if (keyboardCheckbox) {
            keyboardCheckbox.checked = window.pdfBuilderCanvasSettings.keyboard_shortcuts ?? true;
        }

        // Apply dependency logic after synchronization
        updateSelectionModeDependency(modal);
    }

    // Synchronize autosave modal values with current settings
    function synchronizeAutosaveModalValues(modal) {
        if (!modal || !window.pdfBuilderCanvasSettings) return;

        // Store original values for restoration if modal is cancelled
        modal._originalAutosaveSettings = {
            autosaveEnabled: window.pdfBuilderCanvasSettings.autosave_enabled,
            autosaveInterval: window.pdfBuilderCanvasSettings.autosave_interval,
            versionsLimit: window.pdfBuilderCanvasSettings.versions_limit
        };

        const autosaveEnabledCheckbox = modal.querySelector('#canvas_autosave_enabled');
        const autosaveIntervalInput = modal.querySelector('#canvas_autosave_interval');
        const versionsLimitInput = modal.querySelector('#canvas_history_max');

        if (autosaveEnabledCheckbox) {
            autosaveEnabledCheckbox.checked = window.pdfBuilderCanvasSettings.autosave_enabled ?? true;
        }
        if (autosaveIntervalInput) {
            autosaveIntervalInput.value = window.pdfBuilderCanvasSettings.autosave_interval ?? 5;
        }
        if (versionsLimitInput) {
            versionsLimitInput.value = window.pdfBuilderCanvasSettings.versions_limit ?? 10;
        }
    }

    // Real-time preview updates for interactions modal
    function initializeInteractionsRealTimePreview() {
        // Listen for changes in interactions modal fields
        document.addEventListener('change', function(event) {
            const target = event.target;
            const modal = target.closest('.canvas-modal[data-category="interactions"]');
            
            if (modal && (target.id === 'canvas_drag_enabled' || target.id === 'canvas_resize_enabled' || 
                         target.id === 'canvas_rotate_enabled' || target.id === 'canvas_multi_select' ||
                         target.id === 'canvas_selection_mode' || target.id === 'canvas_keyboard_shortcuts')) {
                // Update window.pdfBuilderCanvasSettings temporarily for preview
                if (window.pdfBuilderCanvasSettings) {
                    if (target.id === 'canvas_drag_enabled') {
                        window.pdfBuilderCanvasSettings.drag_enabled = target.checked;
                    } else if (target.id === 'canvas_resize_enabled') {
                        window.pdfBuilderCanvasSettings.resize_enabled = target.checked;
                    } else if (target.id === 'canvas_rotate_enabled') {
                        window.pdfBuilderCanvasSettings.rotate_enabled = target.checked;
                    } else if (target.id === 'canvas_multi_select') {
                        window.pdfBuilderCanvasSettings.multi_select = target.checked;
                    } else if (target.id === 'canvas_selection_mode') {
                        window.pdfBuilderCanvasSettings.selection_mode = target.value;
                    } else if (target.id === 'canvas_keyboard_shortcuts') {
                        window.pdfBuilderCanvasSettings.keyboard_shortcuts = target.checked;
                    }
                    
                    // Update preview immediately
                    if (typeof updateInteractionsCardPreview === 'function') {
                        updateInteractionsCardPreview();
                    }
                }
            }
        });
    }

    // Real-time preview updates for autosave modal
    function initializeAutosaveRealTimePreview() {
        // Listen for changes in autosave modal fields
        ['change', 'input'].forEach(eventType => {
            document.addEventListener(eventType, function(event) {
                const target = event.target;
                const modal = target.closest('.canvas-modal[data-category="autosave"]');
                
                if (modal && (target.id === 'canvas_autosave_enabled' || target.id === 'canvas_autosave_interval' || target.id === 'canvas_history_max')) {
                    // Update window.pdfBuilderCanvasSettings temporarily for preview
                    if (window.pdfBuilderCanvasSettings) {
                        if (target.id === 'canvas_autosave_enabled') {
                            window.pdfBuilderCanvasSettings.autosave_enabled = target.checked;
                        } else if (target.id === 'canvas_autosave_interval') {
                            window.pdfBuilderCanvasSettings.autosave_interval = parseInt(target.value);
                        } else if (target.id === 'canvas_history_max') {
                            window.pdfBuilderCanvasSettings.versions_limit = parseInt(target.value);
                        }
                        
                        // Update preview immediately
                        if (typeof updateAutosaveCardPreview === 'function') {
                            updateAutosaveCardPreview();
                        } else {
                            console.warn('updateAutosaveCardPreview function not found');
                        }
                    } else {
                        console.warn('window.pdfBuilderCanvasSettings not available');
                    }
                }
            });
        });
    }


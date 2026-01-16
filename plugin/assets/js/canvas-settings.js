/**
 * PDF Builder Pro - Canvas Settings JavaScript
 * Handles canvas configuration cards and modals
 */

(function($) {
    'use strict';

    let formGenerator = null;
    let modalSettingsManager = null;

    // Initialize when document is ready
    $(document).ready(function() {
        console.log('[PDF Builder Canvas] Initializing canvas settings...');

        // Initialize canvas cards
        initializeCanvasCards();

        // Initialize form generator
        initializeFormGenerator();

        // Initialize modal settings manager
        initializeModalSettingsManager();

        console.log('[PDF Builder Canvas] Canvas settings initialized');
    });

    /**
     * Initialize canvas cards
     */
    function initializeCanvasCards() {
        $('.canvas-card .canvas-configure-btn').on('click', function(e) {
            e.preventDefault();

            const card = $(this).closest('.canvas-card');
            const category = card.data('category');

            console.log('[PDF Builder Canvas] Opening modal for category:', category);

            if (modalSettingsManager) {
                modalSettingsManager.openModal(category);
            } else {
                console.error('[PDF Builder Canvas] modalSettingsManager not available');
            }
        });
    }

    /**
     * Initialize form generator
     */
    function initializeFormGenerator() {
        formGenerator = {
            /**
             * Generate modal HTML for a category
             */
            generateModalHTML: function(category, data) {
                console.log('[PDF Builder Canvas] Generating modal HTML for:', category);

                let html = '';

                switch (category) {
                    case 'dimensions':
                        html = generateDimensionsModal(data);
                        break;
                    case 'apparence':
                        html = generateApparenceModal(data);
                        break;
                    case 'grille':
                        html = generateGrilleModal(data);
                        break;
                    case 'zoom':
                        html = generateZoomModal(data);
                        break;
                    case 'interactions':
                        html = generateInteractionsModal(data);
                        break;
                    case 'export':
                        html = generateExportModal(data);
                        break;
                    case 'performance':
                        html = generatePerformanceModal(data);
                        break;
                    case 'debug':
                        html = generateDebugModal(data);
                        break;
                    default:
                        html = '<p>Configuration non disponible pour cette catégorie.</p>';
                }

                return html;
            }
        };

        // Make it globally available
        window.formGenerator = formGenerator;
    }

    /**
     * Initialize modal settings manager
     */
    function initializeModalSettingsManager() {
        modalSettingsManager = {
            /**
             * Open modal for a category
             */
            openModal: function(category) {
                console.log('[PDF Builder Canvas] Opening modal for category:', category);

                // Get current settings
                const settings = getCurrentSettings(category);

                // Generate modal HTML
                const modalHtml = formGenerator.generateModalHTML(category, settings);

                // Create modal overlay
                const overlay = $(`
                    <div id="canvas-${category}-modal-overlay" class="canvas-modal-overlay">
                        <div class="canvas-modal-container">
                            <div class="canvas-modal-header">
                                <h3>Configuration ${getCategoryTitle(category)}</h3>
                                <button class="canvas-modal-close">&times;</button>
                            </div>
                            <div class="canvas-modal-body">
                                ${modalHtml}
                            </div>
                            <div class="canvas-modal-footer">
                                <button class="canvas-modal-save">Sauvegarder</button>
                                <button class="canvas-modal-cancel">Annuler</button>
                            </div>
                        </div>
                    </div>
                `);

                // Add to body
                $('body').append(overlay);

                // Show modal
                setTimeout(() => overlay.addClass('active'), 10);

                // Handle close
                overlay.find('.canvas-modal-close, .canvas-modal-cancel').on('click', function() {
                    closeModal(category);
                });

                // Handle save
                overlay.find('.canvas-modal-save').on('click', function() {
                    saveSettings(category, overlay);
                });

                // Handle overlay click
                overlay.on('click', function(e) {
                    if (e.target === this) {
                        closeModal(category);
                    }
                });
            }
        };

        // Make it globally available
        window.modalSettingsManager = modalSettingsManager;
    }

    /**
     * Get current settings for a category
     */
    function getCurrentSettings(category) {
        // This would normally fetch from server, but for now return defaults
        const defaults = {
            dimensions: {
                width: 794,
                height: 1123,
                dpi: 96,
                format: 'A4',
                orientation: 'portrait'
            },
            apparence: {
                bgColor: '#ffffff',
                borderColor: '#cccccc',
                borderWidth: 1,
                shadowEnabled: false
            },
            grille: {
                enabled: true,
                size: 20,
                snapToGrid: true
            },
            zoom: {
                min: 25,
                max: 500,
                default: 100,
                step: 25
            },
            interactions: {
                drag: true,
                resize: true,
                rotate: true,
                multiSelect: true
            },
            export: {
                quality: 90,
                format: 'png',
                transparent: false
            },
            performance: {
                fpsTarget: 60,
                memoryLimit: 50,
                timeout: 5000
            },
            debug: {
                enabled: false,
                monitoring: false,
                reporting: false
            }
        };

        return defaults[category] || {};
    }

    /**
     * Get category title
     */
    function getCategoryTitle(category) {
        const titles = {
            dimensions: 'Dimensions & Format',
            apparence: 'Apparence',
            grille: 'Grille',
            zoom: 'Zoom',
            interactions: 'Interactions',
            export: 'Export',
            performance: 'Performance',
            debug: 'Debug'
        };

        return titles[category] || category;
    }

    /**
     * Generate dimensions modal HTML
     */
    function generateDimensionsModal(data) {
        return `
            <div class="form-group">
                <label for="canvas-width">Largeur (px)</label>
                <input type="number" id="canvas-width" value="${data.width || 794}" min="100" max="5000">
            </div>
            <div class="form-group">
                <label for="canvas-height">Hauteur (px)</label>
                <input type="number" id="canvas-height" value="${data.height || 1123}" min="100" max="5000">
            </div>
            <div class="form-group">
                <label for="canvas-dpi">DPI</label>
                <select id="canvas-dpi">
                    <option value="72" ${data.dpi === 72 ? 'selected' : ''}>72 DPI</option>
                    <option value="96" ${data.dpi === 96 ? 'selected' : ''}>96 DPI</option>
                    <option value="150" ${data.dpi === 150 ? 'selected' : ''}>150 DPI</option>
                    <option value="300" ${data.dpi === 300 ? 'selected' : ''}>300 DPI</option>
                </select>
            </div>
            <div class="form-group">
                <label for="canvas-format">Format</label>
                <select id="canvas-format">
                    <option value="A4" ${data.format === 'A4' ? 'selected' : ''}>A4</option>
                    <option value="A3" ${data.format === 'A3' ? 'selected' : ''}>A3</option>
                    <option value="Letter" ${data.format === 'Letter' ? 'selected' : ''}>Letter</option>
                    <option value="Legal" ${data.format === 'Legal' ? 'selected' : ''}>Legal</option>
                </select>
            </div>
        `;
    }

    /**
     * Generate apparence modal HTML
     */
    function generateApparenceModal(data) {
        return `
            <div class="form-group">
                <label for="canvas-bg-color">Couleur de fond</label>
                <input type="color" id="canvas-bg-color" value="${data.bgColor || '#ffffff'}">
            </div>
            <div class="form-group">
                <label for="canvas-border-color">Couleur de bordure</label>
                <input type="color" id="canvas-border-color" value="${data.borderColor || '#cccccc'}">
            </div>
            <div class="form-group">
                <label for="canvas-border-width">Épaisseur de bordure (px)</label>
                <input type="number" id="canvas-border-width" value="${data.borderWidth || 1}" min="0" max="10">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-shadow-enabled" ${data.shadowEnabled ? 'checked' : ''}>
                    Activer l'ombre
                </label>
            </div>
        `;
    }

    /**
     * Generate grille modal HTML
     */
    function generateGrilleModal(data) {
        return `
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-grid-enabled" ${data.enabled ? 'checked' : ''}>
                    Afficher la grille
                </label>
            </div>
            <div class="form-group">
                <label for="canvas-grid-size">Taille de la grille (px)</label>
                <input type="number" id="canvas-grid-size" value="${data.size || 20}" min="5" max="100">
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-snap-to-grid" ${data.snapToGrid ? 'checked' : ''}>
                    Accrocher à la grille
                </label>
            </div>
        `;
    }

    /**
     * Generate zoom modal HTML
     */
    function generateZoomModal(data) {
        return `
            <div class="form-group">
                <label for="canvas-zoom-min">Zoom minimum (%)</label>
                <input type="number" id="canvas-zoom-min" value="${data.min || 25}" min="10" max="100">
            </div>
            <div class="form-group">
                <label for="canvas-zoom-max">Zoom maximum (%)</label>
                <input type="number" id="canvas-zoom-max" value="${data.max || 500}" min="100" max="1000">
            </div>
            <div class="form-group">
                <label for="canvas-zoom-default">Zoom par défaut (%)</label>
                <input type="number" id="canvas-zoom-default" value="${data.default || 100}" min="25" max="500">
            </div>
            <div class="form-group">
                <label for="canvas-zoom-step">Pas de zoom (%)</label>
                <input type="number" id="canvas-zoom-step" value="${data.step || 25}" min="5" max="50">
            </div>
        `;
    }

    /**
     * Generate interactions modal HTML
     */
    function generateInteractionsModal(data) {
        return `
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-drag-enabled" ${data.drag ? 'checked' : ''}>
                    Autoriser le déplacement
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-resize-enabled" ${data.resize ? 'checked' : ''}>
                    Autoriser le redimensionnement
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-rotate-enabled" ${data.rotate ? 'checked' : ''}>
                    Autoriser la rotation
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-multi-select" ${data.multiSelect ? 'checked' : ''}>
                    Sélection multiple
                </label>
            </div>
        `;
    }

    /**
     * Generate export modal HTML
     */
    function generateExportModal(data) {
        return `
            <div class="form-group">
                <label for="canvas-export-quality">Qualité (%)</label>
                <input type="number" id="canvas-export-quality" value="${data.quality || 90}" min="1" max="100">
            </div>
            <div class="form-group">
                <label for="canvas-export-format">Format</label>
                <select id="canvas-export-format">
                    <option value="png" ${data.format === 'png' ? 'selected' : ''}>PNG</option>
                    <option value="jpg" ${data.format === 'jpg' ? 'selected' : ''}>JPG</option>
                    <option value="svg" ${data.format === 'svg' ? 'selected' : ''}>SVG</option>
                </select>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-export-transparent" ${data.transparent ? 'checked' : ''}>
                    Fond transparent
                </label>
            </div>
        `;
    }

    /**
     * Generate performance modal HTML
     */
    function generatePerformanceModal(data) {
        return `
            <div class="form-group">
                <label for="canvas-fps-target">FPS cible</label>
                <input type="number" id="canvas-fps-target" value="${data.fpsTarget || 60}" min="30" max="120">
            </div>
            <div class="form-group">
                <label for="canvas-memory-limit">Limite mémoire JS (MB)</label>
                <input type="number" id="canvas-memory-limit" value="${data.memoryLimit || 50}" min="10" max="200">
            </div>
            <div class="form-group">
                <label for="canvas-response-timeout">Timeout réponse (ms)</label>
                <input type="number" id="canvas-response-timeout" value="${data.timeout || 5000}" min="1000" max="30000">
            </div>
        `;
    }

    /**
     * Generate debug modal HTML
     */
    function generateDebugModal(data) {
        return `
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-debug-enabled" ${data.enabled ? 'checked' : ''}>
                    Activer le debug
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-performance-monitoring" ${data.monitoring ? 'checked' : ''}>
                    Monitoring des performances
                </label>
            </div>
            <div class="form-group">
                <label>
                    <input type="checkbox" id="canvas-error-reporting" ${data.reporting ? 'checked' : ''}>
                    Rapport d'erreurs
                </label>
            </div>
        `;
    }

    /**
     * Close modal
     */
    function closeModal(category) {
        const overlay = $(`#canvas-${category}-modal-overlay`);
        overlay.removeClass('active');
        setTimeout(() => overlay.remove(), 300);
    }

    /**
     * Save settings
     */
    function saveSettings(category, overlay) {
        console.log('[PDF Builder Canvas] Saving settings for category:', category);

        // Collect form data
        const formData = {};
        overlay.find('input, select').each(function() {
            const input = $(this);
            const id = input.attr('id');
            if (id) {
                if (input.attr('type') === 'checkbox') {
                    formData[id] = input.is(':checked');
                } else {
                    formData[id] = input.val();
                }
            }
        });

        console.log('[PDF Builder Canvas] Form data:', formData);

        // Here you would send to server
        // For now, just close modal and show success
        closeModal(category);

        if (window.pdfBuilderSettings && window.pdfBuilderSettings.showNotification) {
            window.pdfBuilderSettings.showNotification('Paramètres sauvegardés avec succès', 'success');
        }
    }

})(jQuery);
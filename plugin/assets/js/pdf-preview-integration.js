/**
 * Système de logging centralisé pour PDF Builder
 */
window.PDFBuilderLogger = {
    levels: {
        DEBUG: 0,
        INFO: 1,
        WARN: 2,
        ERROR: 3
    },

    currentLevel: 1, // INFO par défaut

    setLevel: function(level) {
        if (typeof level === 'string') {
            this.currentLevel = this.levels[level.toUpperCase()] || 1;
        } else {
            this.currentLevel = level;
        }
    },

    debug: function(message, ...args) {
        if (this.currentLevel <= this.levels.DEBUG) {
            PDFBuilderLogger.debug(message, ...args);
        }
    },

    info: function(message, ...args) {
        if (this.currentLevel <= this.levels.INFO) {
            PDFBuilderLogger.info(message, ...args);
        }
    },

    warn: function(message, ...args) {
        if (this.currentLevel <= this.levels.WARN) {
            console.warn(`[PDF Builder WARN] ${message}`, ...args);
        }
    },

    error: function(message, ...args) {
        if (this.currentLevel <= this.levels.ERROR) {
            console.error(`[PDF Builder ERROR] ${message}`, ...args);
        }
    },

    // Méthode pour logger avec contexte
    context: function(context) {
        return {
            debug: (msg, ...args) => this.debug(`[${context}] ${msg}`, ...args),
            info: (msg, ...args) => this.info(`[${context}] ${msg}`, ...args),
            warn: (msg, ...args) => this.warn(`[${context}] ${msg}`, ...args),
            error: (msg, ...args) => this.error(`[${context}] ${msg}`, ...args)
        };
    }
};

// Configuration du niveau de log basé sur les settings WordPress
if (typeof PDF_BUILDER_DEBUG_ENABLED !== 'undefined') {
    window.PDFBuilderLogger.setLevel(PDF_BUILDER_DEBUG_ENABLED ? 'DEBUG' : 'INFO');
}

// Logger spécialisé pour l'auto-save
const autosaveLogger = window.PDFBuilderLogger.context('AutoSave');

// ==========================================
// INTÉGRATION DANS L'ÉDITEUR (Canvas)
// ==========================================

class PDFEditorPreviewIntegration {
    constructor(canvasEditor) {
        this.canvasEditor = canvasEditor;
        this.previewBtn = null;
        this.autosaveTimer = null;
        this.autosaveStatus = null;
        this.autosaveTimerDisplay = null;
        this.init();
    }

    init() {
        this.createPreviewButton();
        this.createAutosaveUI();
        this.bindEvents();
        this.setupAutosave();
    }

    createPreviewButton() {
        // Créer le bouton d'aperçu dans la barre d'outils
        this.previewBtn = document.createElement('button');
        this.previewBtn.id = 'pdf-editor-preview-btn';
        this.previewBtn.innerHTML = '👁️ Aperçu';
        this.previewBtn.title = 'Générer un aperçu PDF';
        this.previewBtn.style.cssText = `
            background: #007cba;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-left: 10px;
        `;

        // L'ajouter à la barre d'outils existante
        const toolbar = document.querySelector('.pdf-editor-toolbar') ||
                       document.querySelector('#pdf-editor-toolbar') ||
                       document.querySelector('.toolbar');

        if (toolbar) {
            toolbar.appendChild(this.previewBtn);
        } else {
            // Fallback: l'ajouter au body avec position fixe
            this.previewBtn.style.position = 'fixed';
            this.previewBtn.style.top = '10px';
            this.previewBtn.style.right = '10px';
            this.previewBtn.style.zIndex = '1000';
            document.body.appendChild(this.previewBtn);
        }
    }

    createAutosaveUI() {
        // Créer les éléments d'auto-sauvegarde
        const autosaveContainer = document.createElement('div');
        autosaveContainer.id = 'pdf-editor-autosave-container';
        autosaveContainer.style.cssText = `
            display: inline-block;
            margin-left: 10px;
            font-size: 12px;
            color: #666;
            vertical-align: middle;
        `;

        autosaveContainer.innerHTML = `
            <span class="autosave-timer">💾 Sauvegarde auto dans 5 min</span>
            <span class="autosave-status" style="margin-left: 10px;">Prêt</span>
        `;

        // L'ajouter à la barre d'outils
        const toolbar = document.querySelector('.pdf-editor-toolbar') ||
                       document.querySelector('#pdf-editor-toolbar') ||
                       document.querySelector('.toolbar');

        if (toolbar) {
            toolbar.appendChild(autosaveContainer);
        }

        this.autosaveTimerDisplay = autosaveContainer.querySelector('.autosave-timer');
        this.autosaveStatus = autosaveContainer.querySelector('.autosave-status');
    }

    bindEvents() {
        if (this.previewBtn) {
            this.previewBtn.addEventListener('click', () => {
                this.generatePreview();
            });
        }

        // Raccourci clavier Ctrl+P (ou Cmd+P sur Mac)
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                this.generatePreview();
            }
        });
    }

    setupAutosave() {
        autosaveLogger.info('Configuration de l\'auto-save');

        const autosaveEnabled = window.pdfBuilderCanvasSettings?.autosave_enabled !== false;
        const autosaveInterval = window.pdfBuilderCanvasSettings?.autosave_interval || 5; // minutes

        autosaveLogger.debug('Paramètres auto-save:', {
            autosaveEnabled,
            autosaveInterval,
            pdfBuilderCanvasSettings: window.pdfBuilderCanvasSettings
        });

        if (!autosaveEnabled) {
            autosaveLogger.info('Auto-save désactivée');
            if (this.autosaveTimerDisplay) {
                this.autosaveTimerDisplay.textContent = '💾 Sauvegarde auto désactivée';
            }
            return;
        }

        PDFBuilderLogger.info('Démarrage de l\'auto-save avec intervalle:', autosaveInterval, 'minutes');
        // Démarrer le timer d'auto-sauvegarde
        this.startAutosaveTimer(autosaveInterval);

        // Sauvegarde avant de quitter la page
        window.addEventListener('beforeunload', () => {
            PDFBuilderLogger.info('Sauvegarde avant de quitter la page');
            this.performAutosave();
        });
    }

    startAutosaveTimer(intervalMinutes) {
        PDFBuilderLogger.info('Démarrage du timer auto-save:', intervalMinutes, 'minutes');

        this.updateAutosaveTimer(intervalMinutes);

        this.autosaveTimer = setInterval(() => {
            PDFBuilderLogger.info('Timer auto-save déclenché - exécution de la sauvegarde');
            this.performAutosave();
            this.updateAutosaveTimer(intervalMinutes);
        }, intervalMinutes * 60 * 1000);

        PDFBuilderLogger.info('Timer auto-save configuré avec ID:', this.autosaveTimer);

        // TEST RAPIDE : Déclencher une sauvegarde dans 10 secondes pour tester
        PDFBuilderLogger.info('⏰ Test rapide : sauvegarde dans 10 secondes...');
        setTimeout(() => {
            PDFBuilderLogger.info('🚀 Déclenchement test manuel de l\'auto-save');
            this.performAutosave();
        }, 10000);
    }

    updateAutosaveTimer(intervalMinutes) {
        if (this.autosaveTimerDisplay) {
            this.autosaveTimerDisplay.textContent = `💾 Sauvegarde auto dans ${intervalMinutes} min`;
        }
    }

    async performAutosave() {
        PDFBuilderLogger.info('Début auto-sauvegarde');

        try {
            PDFBuilderLogger.debug('Vérification des variables AJAX:', {
                ajaxurl: pdfBuilderAjax?.ajaxurl,
                nonce: pdfBuilderAjax?.nonce,
                ajaxurlExists: typeof pdfBuilderAjax !== 'undefined'
            });

            if (this.autosaveStatus) {
                this.autosaveStatus.textContent = 'Sauvegarde en cours...';
                this.autosaveStatus.style.color = '#ffa500';
            }

            const templateData = this.getTemplateData();
            PDFBuilderLogger.debug('Données du template récupérées:', templateData);

            if (!templateData) {
                throw new Error('Aucune donnée de template à sauvegarder');
            }

            PDFBuilderLogger.debug('Préparation de la requête AJAX');
            // Envoyer via AJAX
            const response = await fetch(pdfBuilderAjax.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'pdf_builder_autosave_template',
                    template_data: JSON.stringify(templateData),
                    nonce: pdfBuilderAjax.nonce
                })
            });

            PDFBuilderLogger.debug('Réponse HTTP reçue:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });

            const result = await response.json();
            PDFBuilderLogger.debug('Résultat JSON:', result);

            if (result.success) {
                PDFBuilderLogger.info('Auto-sauvegarde réussie');
                if (this.autosaveStatus) {
                    this.autosaveStatus.textContent = 'Sauvegardé automatiquement';
                    this.autosaveStatus.style.color = '#28a745';
                    setTimeout(() => {
                        this.autosaveStatus.textContent = 'Prêt';
                        this.autosaveStatus.style.color = '#666';
                    }, 3000);
                }
            } else {
                console.error('[PDF Builder] Auto-sauvegarde échouée côté serveur:', result.data);
                throw new Error(result.data || 'Erreur inconnue');
            }

        } catch (error) {
            console.error('[PDF Builder] Erreur auto-sauvegarde:', error);
            console.error('[PDF Builder] Stack trace:', error.stack);

            if (this.autosaveStatus) {
                this.autosaveStatus.textContent = 'Erreur de sauvegarde';
                this.autosaveStatus.style.color = '#dc3545';
                setTimeout(() => {
                    this.autosaveStatus.textContent = 'Prêt';
                    this.autosaveStatus.style.color = '#666';
                }, 5000);
            }
        }
    }

    async generatePreview() {
        try {
            // Récupérer les données du template depuis l'éditeur
            const templateData = this.getTemplateData();

            if (!templateData) {
                alert('Aucune donnée de template trouvée. Veuillez créer un template d\'abord.');
                return;
            }

            // Générer l'aperçu
            const result = await window.generateEditorPreview(templateData, {
                quality: 150,
                format: 'png'
            });

            if (result) {
                // Preview generated successfully
            }

        } catch {

            alert('Erreur lors de la génération de l\'aperçu. Vérifiez la console pour plus de détails.');
        }
    }

    getTemplateData() {
        PDFBuilderLogger.debug('getTemplateData() appelée');
        PDFBuilderLogger.debug('this.canvasEditor:', this.canvasEditor);
        PDFBuilderLogger.debug('window.pdfCanvasEditor:', window.pdfCanvasEditor);

        // Adapter selon votre structure de données d'éditeur
        if (this.canvasEditor && typeof this.canvasEditor.getTemplateData === 'function') {
            PDFBuilderLogger.debug('Utilisation de this.canvasEditor.getTemplateData()');
            const data = this.canvasEditor.getTemplateData();
            PDFBuilderLogger.debug('Données récupérées depuis canvasEditor:', data);
            return data;
        }

        PDFBuilderLogger.debug('canvasEditor non disponible, test des fallbacks');

        // Fallback: Éditeur React
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.getCurrentTemplate === 'function') {
            PDFBuilderLogger.debug('Utilisation de window.pdfBuilderReact.getCurrentTemplate()');
            try {
                const data = window.pdfBuilderReact.getCurrentTemplate();
                PDFBuilderLogger.debug('Données récupérées depuis React editor (getCurrentTemplate):', data);
                if (data) return data;
            } catch (error) {
                console.error('[PDF Builder] Erreur getCurrentTemplate:', error);
            }
        }

        // Fallback: Éditeur React - getEditorState
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.getEditorState === 'function') {
            PDFBuilderLogger.debug('Test de window.pdfBuilderReact.getEditorState()');
            try {
                const data = window.pdfBuilderReact.getEditorState();
                PDFBuilderLogger.debug('Données récupérées depuis React editor (getEditorState):', data);
                if (data) return data;
            } catch (error) {
                console.error('[PDF Builder] Erreur getEditorState:', error);
            }
        }

        // Fallback: chercher dans le localStorage ou les variables globales
        if (window.pdfEditorTemplate) {
            PDFBuilderLogger.debug('Utilisation de window.pdfEditorTemplate');
            return window.pdfEditorTemplate;
        }

        if (localStorage.getItem('pdf-builder-template')) {
            PDFBuilderLogger.debug('Utilisation du localStorage');
            return JSON.parse(localStorage.getItem('pdf-builder-template'));
        }

        // Template par défaut pour les tests
        PDFBuilderLogger.debug('Utilisation du template par défaut (test)');
        return {
            templateId: 'autosave-test-' + Date.now(),
            template: {
                name: 'Auto-save Test Template',
                elements: [
                    {
                        type: 'text',
                        content: 'TEMPLATE DE TEST AUTO-SAVE',
                        x: 50,
                        y: 50,
                        width: 400,
                        height: 40,
                        fontSize: 20,
                        fontFamily: 'Arial',
                        color: '#000000',
                        fontWeight: 'bold'
                    },
                    {
                        type: 'text',
                        content: 'Sauvegardé automatiquement le ' + new Date().toLocaleString(),
                        x: 50,
                        y: 100,
                        width: 400,
                        height: 30,
                        fontSize: 14,
                        fontFamily: 'Arial',
                        color: '#666666'
                    }
                ]
            }
        };
    }
}

// ==========================================
// INTÉGRATION DANS LA METABOX WOOCOMMERCE
// ==========================================

class PDFMetaboxPreviewIntegration {
    constructor(metaboxContainer) {
        this.metaboxContainer = metaboxContainer;
        this.orderId = this.getOrderId();
        this.previewBtn = null;
        this.init();
    }

    init() {
        this.createPreviewButtons();
        this.bindEvents();
    }

    createPreviewButtons() {
        // Créer un conteneur pour les boutons d'aperçu
        const buttonContainer = document.createElement('div');
        buttonContainer.id = 'pdf-metabox-preview-buttons';
        buttonContainer.style.cssText = `
            margin: 15px 0;
            padding: 15px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
        `;

        buttonContainer.innerHTML = `
            <h4 style="margin: 0 0 10px 0; color: #495057;">📄 Aperçu PDF</h4>
            <p style="margin: 0 0 15px 0; color: #6c757d; font-size: 13px;">
                Générez un aperçu du PDF avec les données réelles de cette commande.
            </p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button id="pdf-metabox-preview-btn" class="button button-secondary">
                    👁️ Aperçu Image
                </button>
                <button id="pdf-metabox-generate-btn" class="button button-primary">
                    📄 Générer PDF
                </button>
            </div>
        `;

        // L'insérer dans la metabox
        if (this.metaboxContainer) {
            this.metaboxContainer.appendChild(buttonContainer);
        }

        this.previewBtn = document.getElementById('pdf-metabox-preview-btn');
    }

    bindEvents() {
        if (this.previewBtn) {
            this.previewBtn.addEventListener('click', () => {
                this.generatePreview();
            });
        }

        // Bouton de régénération globale
        window.regenerateOrderPreview = () => {
            this.generatePreview();
        };
    }

    async generatePreview() {
        try {
            if (!this.orderId) {
                alert('ID de commande non trouvé.');
                return;
            }

            // Récupérer les données du template depuis la metabox
            const templateData = this.getTemplateData();

            if (!templateData) {
                alert('Aucune donnée de template trouvée. Veuillez sélectionner un template.');
                return;
            }

            // Générer l'aperçu
            const result = await window.generateOrderPreview(templateData, this.orderId, {
                quality: 150,
                format: 'png'
            });

            if (result) {
                // Preview generated successfully
            }

        } catch {

            alert('Erreur lors de la génération de l\'aperçu. Vérifiez la console pour plus de détails.');
        }
    }

    getOrderId() {
        // Essayer différentes méthodes pour récupérer l'ID de commande

        // Depuis l'URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('post')) {
            return parseInt(urlParams.get('post'));
        }

        // Depuis les variables globales WordPress
        if (window.wpApiSettings && window.wpApiSettings.postId) {
            return window.wpApiSettings.postId;
        }

        // Depuis un élément caché dans la page
        const orderIdElement = document.getElementById('pdf-order-id') ||
                              document.querySelector('[data-order-id]');
        if (orderIdElement) {
            return parseInt(orderIdElement.dataset.orderId || orderIdElement.value);
        }

        // Depuis le titre de la page (parsing du DOM)
        const titleElement = document.querySelector('.wp-heading-inline') ||
                           document.querySelector('h1');
        if (titleElement) {
            const titleMatch = titleElement.textContent.match(/#(\d+)/);
            if (titleMatch) {
                return parseInt(titleMatch[1]);
            }
        }


        return null;
    }

    getTemplateData() {
        // Récupérer les données du template sélectionné

        // Depuis un champ caché
        const templateDataElement = document.getElementById('pdf-template-data') ||
                                   document.querySelector('[data-template-data]');
        if (templateDataElement) {
            try {
                return JSON.parse(templateDataElement.value || templateDataElement.dataset.templateData);
            } catch {
                // Invalid JSON, continue to next method
            }
        }

        // Depuis les variables globales
        if (window.pdfCurrentTemplate) {
            return window.pdfCurrentTemplate;
        }

        // Template par défaut avec variables WooCommerce
        return {
            template: {
                elements: [
                    {
                        type: 'text',
                        content: 'FACTURE',
                        x: 50,
                        y: 30,
                        width: 200,
                        height: 40,
                        fontSize: 24,
                        fontWeight: 'bold',
                        color: '#000000'
                    },
                    {
                        type: 'text',
                        content: 'Commande #{{order_number}}',
                        x: 50,
                        y: 80,
                        width: 200,
                        height: 30,
                        fontSize: 16,
                        color: '#333333'
                    },
                    {
                        type: 'text',
                        content: 'Client: {{customer_name}}',
                        x: 50,
                        y: 120,
                        width: 200,
                        height: 25,
                        fontSize: 14,
                        color: '#666666'
                    },
                    {
                        type: 'text',
                        content: 'Total: {{order_total}} €',
                        x: 50,
                        y: 150,
                        width: 200,
                        height: 25,
                        fontSize: 14,
                        fontWeight: 'bold',
                        color: '#000000'
                    }
                ]
            }
        };
    }
}

// ==========================================
// INITIALISATION AUTOMATIQUE
// ==========================================

/**
 * Met à jour la prévisualisation de la carte performance
 */
window.updatePerformanceCardPreview = function() {
    const fpsSelect = document.getElementById("canvas_fps_target");
    const memoryJsInput = document.getElementById("canvas_memory_limit_js");
    const memoryPhpInput = document.getElementById("canvas_memory_limit_php");

    if (!fpsSelect || !memoryJsInput || !memoryPhpInput) return;

    const fps = parseInt(fpsSelect.value);
    const memoryJs = parseInt(memoryJsInput.value);
    const memoryPhp = parseInt(memoryPhpInput.value);

    // Mettre à jour les valeurs dans la carte performance
    const metricValues = document.querySelectorAll('.canvas-card[data-category="performance"] .metric-value');

    if (metricValues.length >= 3) {
        // FPS
        let fpsStatus = '🔴 Faible';
        if (fps >= 30) fpsStatus = '🟢 Bon';
        else if (fps >= 15) fpsStatus = '🟡 Moyen';
        metricValues[0].innerHTML = `${fps}<br><small>${fpsStatus}</small>`;

        // RAM JS
        let memoryJsStatus = '🔴 Faible';
        if (memoryJs >= 128) memoryJsStatus = '🟢 Bon';
        else if (memoryJs >= 64) memoryJsStatus = '🟡 Moyen';
        metricValues[1].innerHTML = `${memoryJs}MB<br><small>${memoryJsStatus}</small>`;

        // RAM PHP
        let memoryPhpStatus = '🔴 Faible';
        if (memoryPhp >= 256) memoryPhpStatus = '🟢 Bon';
        else if (memoryPhp >= 128) memoryPhpStatus = '🟡 Moyen';
        metricValues[2].innerHTML = `${memoryPhp}MB<br><small>${memoryPhpStatus}</small>`;
    }

    // Mettre à jour l'indicateur de statut
    const statusIndicator = document.querySelector('.canvas-card[data-category="performance"] .status-indicator');
    if (statusIndicator) {
        const lazyLoading = document.getElementById("canvas_lazy_loading_editor")?.checked;
        const statusText = lazyLoading ? 'Lazy Loading Activé' : 'Lazy Loading Désactivé';
        const statusDot = statusIndicator.querySelector('.status-dot');
        if (statusDot) {
            statusDot.style.backgroundColor = lazyLoading ? '#28a745' : '#dc3545';
        }
        const statusTextEl = statusIndicator.querySelector('.status-text');
        if (statusTextEl) {
            statusTextEl.textContent = statusText;
        }
    }
};

/**
 * Met à jour la prévisualisation de la carte apparence
 */
window.updateApparenceCardPreview = function() {
    const bgColorInput = document.getElementById("canvas_background_color");
    const borderColorInput = document.getElementById("canvas_border_color");
    const borderWidthInput = document.getElementById("canvas_border_width");

    // Mettre à jour les previews de couleur
    const bgPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.bg');
    const borderPreview = document.querySelector('.canvas-card[data-category="apparence"] .color-preview.border');

    if (bgPreview && bgColorInput) {
        bgPreview.style.backgroundColor = bgColorInput.value;
    }

    if (borderPreview && borderColorInput && borderWidthInput) {
        borderPreview.style.border = `${borderWidthInput.value}px solid ${borderColorInput.value}`;
    }
};

/**
 * Met à jour la prévisualisation de la carte grille
 */
window.updateGrilleCardPreview = function() {
    const gridEnabled = document.getElementById("canvas_grid_enabled")?.checked;
    const snapToGrid = document.getElementById("canvas_snap_to_grid")?.checked;
    const showGuides = document.getElementById("canvas_show_guides")?.checked;

    const gridContainer = document.querySelector('.canvas-card[data-category="grille"] .grid-preview-container');

    if (!gridContainer) return;

    // Activer/désactiver la grille
    if (gridEnabled) {
        gridContainer.classList.add('grid-enabled');
        gridContainer.classList.remove('grid-disabled');
    } else {
        gridContainer.classList.add('grid-disabled');
        gridContainer.classList.remove('grid-enabled');
    }

    // Afficher/cacher les guides
    const guideLines = gridContainer.querySelectorAll('.guide-line');
    guideLines.forEach(guide => {
        if (showGuides) {
            guide.classList.add('active');
        } else {
            guide.classList.remove('active');
        }
    });

    // Mettre à jour l'indicateur de snap
    const snapIndicator = gridContainer.querySelector('.snap-indicator');
    if (snapIndicator) {
        if (snapToGrid && gridEnabled) {
            snapIndicator.textContent = '🔗 Snap activé';
            snapIndicator.style.color = '#28a745';
        } else {
            snapIndicator.textContent = '🔗 Snap désactivé';
            snapIndicator.style.color = '#6c757d';
        }
    }
};

/**
 * Met à jour la prévisualisation de la carte zoom
 */
window.updateZoomCardPreview = function() {
    // Use saved settings if available, otherwise fall back to input values
    const zoomMin = window.pdfBuilderCanvasSettings?.min_zoom ||
                   (document.getElementById("canvas_zoom_min")?.value || 10);
    const zoomMax = window.pdfBuilderCanvasSettings?.max_zoom ||
                   (document.getElementById("canvas_zoom_max")?.value || 500);
    const zoomStep = window.pdfBuilderCanvasSettings?.zoom_step ||
                    (document.getElementById("canvas_zoom_step")?.value || 25);
    const defaultZoom = window.pdfBuilderCanvasSettings?.default_zoom ||
                       (document.getElementById("canvas_zoom_default")?.value || 100);

    const zoomLevel = document.querySelector('.canvas-card[data-category="zoom"] .zoom-level');
    const zoomInfo = document.querySelector('.canvas-card[data-category="zoom"] .zoom-info');

    if (zoomLevel) {
        zoomLevel.textContent = `${defaultZoom}%`;
    }

    if (zoomInfo) {
        zoomInfo.innerHTML = `<span>${zoomMin}% - ${zoomMax}%</span><span>Pas: ${zoomStep}%</span>`;
    }
};

/**
 * Met à jour la prévisualisation de la carte interactions
 */
window.updateInteractionsCardPreview = function() {
    const selectionMode = document.getElementById("canvas_selection_mode")?.value || 'click';
    const keyboardShortcuts = document.getElementById("canvas_keyboard_shortcuts")?.checked;

    const modeIcons = document.querySelectorAll('.canvas-card[data-category="interactions"] .mode-icon');
    const statusIndicator = document.querySelector('.canvas-card[data-category="interactions"] .status-indicator');

    // Mettre à jour les icônes de mode
    modeIcons.forEach(icon => {
        icon.classList.remove('active');
        if ((selectionMode === 'rectangle' && icon.title === 'Rectangle') ||
            (selectionMode === 'lasso' && icon.title === 'Lasso') ||
            (selectionMode === 'click' && icon.title === 'Clic')) {
            icon.classList.add('active');
        }
    });

    // Mettre à jour l'indicateur de statut
    if (statusIndicator) {
        if (keyboardShortcuts) {
            statusIndicator.textContent = 'Raccourcis activés';
            statusIndicator.className = 'status-indicator enabled';
        } else {
            statusIndicator.textContent = 'Raccourcis désactivés';
            statusIndicator.className = 'status-indicator disabled';
        }
    }
};

/**
 * Met à jour la prévisualisation de la carte export
 */
window.updateExportCardPreview = function() {
    const exportQuality = document.getElementById("canvas_export_quality")?.value || 90;
    const exportFormats = document.querySelectorAll('input[name="canvas_export_formats[]"]:checked');

    const qualityFill = document.querySelector('.canvas-card[data-category="export"] .quality-fill');
    const qualityText = document.querySelector('.canvas-card[data-category="export"] .quality-text');
    const formatBadges = document.querySelectorAll('.canvas-card[data-category="export"] .format-badge');

    // Mettre à jour la barre de qualité
    if (qualityFill) {
        qualityFill.style.width = `${exportQuality}%`;
    }
    if (qualityText) {
        qualityText.textContent = `${exportQuality}%`;
    }

    // Mettre à jour les badges de format
    formatBadges.forEach(badge => {
        badge.classList.remove('active');
    });

    exportFormats.forEach(checkbox => {
        const format = checkbox.value.toLowerCase();
        const badge = document.querySelector(`.canvas-card[data-category="export"] .format-badge.${format}`);
        if (badge) {
            badge.classList.add('active');
        }
    });
};

// ==========================================
// FONCTIONS GLOBALES DE PRÉVISUALISATION DES CARTES
// ==========================================

/**
 * Met à jour la prévisualisation de la carte dimensions
 */
window.updateDimensionsCardPreview = function() {
    // Prioritize modal input values for real-time updates, fall back to saved settings
    const formatInput = document.getElementById("canvas_format");
    const dpiInput = document.getElementById("canvas_dpi");
    const orientationInput = document.getElementById("canvas_orientation");

    const format = formatInput ? formatInput.value : (window.pdfBuilderCanvasSettings?.default_canvas_format || 'A4');
    const dpi = dpiInput ? parseInt(dpiInput.value) : (window.pdfBuilderCanvasSettings?.default_canvas_dpi || 96);
    const orientation = orientationInput ? orientationInput.value : (window.pdfBuilderCanvasSettings?.default_canvas_orientation || 'portrait');

    // Get paper dimensions in mm
    const paperFormats = window.pdfBuilderPaperFormats || {
        'A4': { width: 210, height: 297 },
        'A3': { width: 297, height: 420 },
        'A5': { width: 148, height: 210 },
        'Letter': { width: 215.9, height: 279.4 },
        'Legal': { width: 215.9, height: 355.6 },
        'Tabloid': { width: 279.4, height: 431.8 }
    };

    const dimsMM = paperFormats[format] || paperFormats['A4'];
    let widthMm = dimsMM.width;
    let heightMm = dimsMM.height;

    // Swap if landscape
    if (orientation === 'landscape') {
        [widthMm, heightMm] = [heightMm, widthMm];
    }

    // Convert to pixels with current DPI
    const pixelsPerMM = dpi / 25.4;
    const widthPx = Math.round(widthMm * pixelsPerMM);
    const heightPx = Math.round(heightMm * pixelsPerMM);

    // Mettre à jour les valeurs dans la carte dimensions
    const widthElement = document.getElementById('card-canvas-width');
    const heightElement = document.getElementById('card-canvas-height');
    const dpiElement = document.getElementById('card-canvas-dpi');

    if (widthElement) {
        widthElement.textContent = widthPx;
    } else {
        console.error('PDF_BUILDER_DEBUG: card-canvas-width element not found');
    }

    if (heightElement) {
        heightElement.textContent = heightPx;
    } else {
        console.error('PDF_BUILDER_DEBUG: card-canvas-height element not found');
    }

    if (dpiElement) {
        dpiElement.textContent = `${dpi} DPI - ${format} (${widthMm}×${heightMm}mm)`;
    } else {
        console.error('PDF_BUILDER_DEBUG: card-canvas-dpi element not found');
    }
};/**
 * Met à jour la prévisualisation de la carte auto-save
 */
window.updateAutosaveCardPreview = function() {
    // Use saved settings if available, otherwise fall back to input values
    const interval = window.pdfBuilderCanvasSettings?.autosave_interval ? parseInt(window.pdfBuilderCanvasSettings.autosave_interval) :
                    (document.getElementById("canvas_autosave_interval") ? parseInt(document.getElementById("canvas_autosave_interval").value) : null);
    const enabled = window.pdfBuilderCanvasSettings?.autosave_enabled !== undefined ? window.pdfBuilderCanvasSettings.autosave_enabled :
                   (document.getElementById("canvas_autosave_enabled") ? document.getElementById("canvas_autosave_enabled").checked : false);

    if (interval === null) {
        console.error('PDF_BUILDER_DEBUG: Missing autosave interval');
        return;
    }

    // Mettre à jour l'indicateur de statut
    const timerElement = document.querySelector('.canvas-card[data-category="autosave"] .autosave-timer');
    const statusElement = document.querySelector('.canvas-card[data-category="autosave"] .autosave-status');

    if (timerElement) {
        timerElement.textContent = `${interval}min`;
    } else {
        console.error('PDF_BUILDER_DEBUG: autosave-timer element not found');
    }

    if (statusElement) {
        if (enabled) {
            statusElement.classList.add('active');
        } else {
            statusElement.classList.remove('active');
        }
    } else {
        console.error('PDF_BUILDER_DEBUG: autosave-status element not found');
    }
};

// ==========================================
// INITIALISATION AUTOMATIQUE
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    PDFBuilderLogger.info('Initialisation automatique - URL actuelle:', window.location.href);
    PDFBuilderLogger.debug('Éléments DOM détectés:', {
        pdfEditorCanvas: !!document.querySelector('#pdf-editor-canvas'),
        pdfCanvasEditor: !!document.querySelector('.pdf-canvas-editor'),
        pdfBuilderEditor: window.location.href.includes('pdf-builder-editor'),
        pdfBuilderReactEditor: window.location.href.includes('pdf-builder-react-editor')
    });

    // Initialiser l'intégration éditeur si on est dans l'éditeur
    if (document.querySelector('#pdf-editor-canvas') ||
        document.querySelector('.pdf-canvas-editor') ||
        window.location.href.includes('pdf-builder-editor') ||
        window.location.href.includes('pdf-builder-react-editor')) {

        PDFBuilderLogger.info('Condition éditeur remplie - création de PDFEditorPreviewIntegration');
        window.pdfEditorPreview = new PDFEditorPreviewIntegration(window.pdfCanvasEditor);
    } else {
        PDFBuilderLogger.debug('Condition éditeur NON remplie - pas d\'initialisation');
    }

    // Initialiser l'intégration metabox si on est dans une commande WooCommerce
    if (document.querySelector('.woocommerce-order-data') ||
        document.querySelector('#woocommerce-order-data') ||
        window.location.href.includes('post.php?post=') &&
        window.location.href.includes('action=edit')) {

        // Attendre que la metabox soit chargée
        setTimeout(() => {
            const metabox = document.querySelector('#pdf-builder-metabox') ||
                           document.querySelector('.pdf-builder-metabox') ||
                           document.querySelector('.postbox');

            if (metabox) {

                window.pdfMetaboxPreview = new PDFMetaboxPreviewIntegration(metabox);
            }
        }, 1000);
    }
});

// ==========================================
// FONCTIONS GLOBALES D'AIDE
// ==========================================

/**
 * Système AJAX centralisé pour PDF Builder
 */
window.PDFBuilderAjax = {
    /**
     * Effectue une requête AJAX avec gestion d'erreurs unifiée
     */
    request: async function(action, data = {}, options = {}) {
        const logger = window.PDFBuilderLogger.context('AJAX');

        try {
            // Vérification des variables AJAX
            if (typeof pdfBuilderAjax === 'undefined') {
                throw new Error('Variables AJAX non définies');
            }

            const ajaxData = {
                action: action,
                nonce: pdfBuilderAjax.nonce,
                ...data
            };

            logger.debug('Préparation de la requête AJAX', { action, data: ajaxData });

            const response = await fetch(pdfBuilderAjax.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams(ajaxData),
                ...options
            });

            logger.debug('Réponse HTTP reçue', { status: response.status });

            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}`);
            }

            const result = await response.json();
            logger.debug('Résultat JSON', result);

            if (!result.success) {
                throw new Error(result.data?.message || 'Erreur serveur inconnue');
            }

            return result.data;

        } catch (error) {
            logger.error('Erreur AJAX', error);
            throw error;
        }
    },

    /**
     * Gestionnaire d'erreurs AJAX unifié
     */
    handleError: function(error, context = 'Opération') {
        const logger = window.PDFBuilderLogger.context('ErrorHandler');

        logger.error(`${context} échouée`, error);

        // Afficher un message d'erreur à l'utilisateur
        const message = error.message || 'Une erreur inattendue s\'est produite';

        // Essayer différentes méthodes de notification
        if (window.pdfBuilderNotifications?.showToast) {
            window.pdfBuilderNotifications.showToast(`${context}: ${message}`, 'error', 6000);
        } else if (window.PDF_Builder_Notification_Manager?.show_toast) {
            window.PDF_Builder_Notification_Manager.show_toast(`${context}: ${message}`, 'error', 6000);
        } else {
            alert(`${context}: ${message}`);
        }
    }
};

/**
 * Fonction de génération automatique de preview selon le contexte
 */
window.generateAutoPreview = async function(templateData = null, orderId = null) {
    try {
        // Détection automatique du contexte
        const isEditor = document.querySelector('#pdf-editor-canvas') ||
                        document.querySelector('.pdf-canvas-editor') ||
                        window.location.href.includes('pdf-builder-editor');

        const isMetabox = document.querySelector('.woocommerce-order-data') ||
                         document.querySelector('#woocommerce-order-data') ||
                         (window.location.href.includes('post.php') &&
                          window.location.href.includes('action=edit'));

        if (isEditor) {

            const data = templateData || window.pdfEditorPreview?.getTemplateData();
            return await window.generateEditorPreview(data);
        }

        if (isMetabox) {

            const data = templateData || window.pdfMetaboxPreview?.getTemplateData();
            const id = orderId || window.pdfMetaboxPreview?.getOrderId();
            return await window.generateOrderPreview(data, id);
        }


        return null;

    } catch {

        return null;
    }
};

/**
 * Système centralisé de gestion des previews de cartes canvas
 */
window.CanvasPreviewManager = {
    // Configuration des cartes et leurs mappings
    cardConfigs: {
        dimensions: {
            inputs: ['canvas_format', 'canvas_dpi', 'canvas_orientation'],
            settings: ['default_canvas_format', 'default_canvas_dpi', 'default_canvas_orientation'],
            defaults: ['A4', 96, 'portrait'],
            updateFunction: 'updateDimensionsCardPreview'
        },
        zoom: {
            inputs: ['canvas_zoom'],
            settings: ['default_canvas_zoom'],
            defaults: [100],
            updateFunction: 'updateZoomCardPreview'
        },
        autosave: {
            inputs: ['canvas_autosave'],
            settings: ['default_canvas_autosave'],
            defaults: [30],
            updateFunction: 'updateAutosaveCardPreview'
        }
    },

    /**
     * Récupère une valeur depuis les inputs de modale ou les settings sauvegardés
     */
    getValue: function(inputId, settingKey, defaultValue) {
        const input = document.getElementById(inputId);
        if (input) {
            return input.type === 'number' ? parseInt(input.value) : input.value;
        }
        return window.pdfBuilderCanvasSettings?.[settingKey] || defaultValue;
    },

    /**
     * Met à jour toutes les previews ou une catégorie spécifique
     */
    updatePreviews: function(category = 'all') {
        PDFBuilderLogger.debug('CanvasPreviewManager.updatePreviews called with category:', category);

        Object.keys(this.cardConfigs).forEach(cardCategory => {
            if (category === 'all' || category === cardCategory) {
                const config = this.cardConfigs[cardCategory];
                if (typeof window[config.updateFunction] === 'function') {
                    window[config.updateFunction]();
                }
            }
        });
    },

    /**
     * Initialise les event listeners pour les mises à jour en temps réel
     */
    initializeRealTimeUpdates: function(modal) {
        if (!modal) return;

        const category = modal.getAttribute('data-category');
        const config = this.cardConfigs[category];

        if (!config) return;

        PDFBuilderLogger.debug('Initializing real-time updates for modal category:', category);

        // Supprimer les anciens listeners
        const inputs = modal.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.removeEventListener('input', this.handleInputChange);
            input.removeEventListener('change', this.handleInputChange);
        });

        // Ajouter les nouveaux listeners
        inputs.forEach(input => {
            input.addEventListener('input', (event) => this.handleInputChange(event, category));
            input.addEventListener('change', (event) => this.handleInputChange(event, category));
        });
    },

    /**
     * Gestionnaire d'événement pour les changements d'input
     */
    handleInputChange: function(event, category) {
        const input = event.target;
        PDFBuilderLogger.debug('Input changed:', input.id, 'Value:', input.value, 'Category:', category);

        // Mettre à jour la preview correspondante
        const config = this.cardConfigs[category];
        if (config && typeof window[config.updateFunction] === 'function') {
            window[config.updateFunction]();
        }
    }
};

/**
 * Met à jour la prévisualisation de la carte dimensions
 */
window.updateDimensionsCardPreview = function() {
    const config = window.CanvasPreviewManager.cardConfigs.dimensions;

    const format = window.CanvasPreviewManager.getValue(config.inputs[0], config.settings[0], config.defaults[0]);
    const dpi = window.CanvasPreviewManager.getValue(config.inputs[1], config.settings[1], config.defaults[1]);
    const orientation = window.CanvasPreviewManager.getValue(config.inputs[2], config.settings[2], config.defaults[2]);

    // Get paper dimensions in mm
    const paperFormats = window.pdfBuilderPaperFormats || {
        'A4': { width: 210, height: 297 },
        'A3': { width: 297, height: 420 },
        'A5': { width: 148, height: 210 },
        'Letter': { width: 215.9, height: 279.4 },
        'Legal': { width: 215.9, height: 355.6 },
        'Tabloid': { width: 279.4, height: 431.8 }
    };

    const dimsMM = paperFormats[format] || paperFormats['A4'];
    let widthMm = dimsMM.width;
    let heightMm = dimsMM.height;

    // Swap if landscape
    if (orientation === 'landscape') {
        [widthMm, heightMm] = [heightMm, widthMm];
    }

    // Convert to pixels with current DPI
    const pixelsPerMM = dpi / 25.4;
    const widthPx = Math.round(widthMm * pixelsPerMM);
    const heightPx = Math.round(heightMm * pixelsPerMM);

    // Mettre à jour les valeurs dans la carte dimensions
    const widthElement = document.getElementById('card-canvas-width');
    const heightElement = document.getElementById('card-canvas-height');
    const dpiElement = document.getElementById('card-canvas-dpi');

    if (widthElement) {
        widthElement.textContent = widthPx;
    } else {
        console.error('PDF_BUILDER_DEBUG: card-canvas-width element not found');
    }

    if (heightElement) {
        heightElement.textContent = heightPx;
    } else {
        console.error('PDF_BUILDER_DEBUG: card-canvas-height element not found');
    }

    if (dpiElement) {
        dpiElement.textContent = `${dpi} DPI - ${format} (${widthMm}×${heightMm}mm)`;
    } else {
        console.error('PDF_BUILDER_DEBUG: card-canvas-dpi element not found');
    }
};

/**
 * Met à jour la prévisualisation de la carte zoom
 */
window.updateZoomCardPreview = function() {
    const config = window.CanvasPreviewManager.cardConfigs.zoom;
    const zoom = window.CanvasPreviewManager.getValue(config.inputs[0], config.settings[0], config.defaults[0]);

    const zoomElement = document.querySelector('.zoom-level');
    if (zoomElement) {
        zoomElement.textContent = `${zoom}%`;
    } else {
        console.error('PDF_BUILDER_DEBUG: .zoom-level element not found');
    }
};

/**
 * Met à jour la prévisualisation de la carte sauvegarde automatique
 */
window.updateAutosaveCardPreview = function() {
    const config = window.CanvasPreviewManager.cardConfigs.autosave;
    const autosave = window.CanvasPreviewManager.getValue(config.inputs[0], config.settings[0], config.defaults[0]);

    const autosaveElement = document.querySelector('.autosave-timer');
    if (autosaveElement) {
        autosaveElement.textContent = `${autosave}min`;
    } else {
        console.error('PDF_BUILDER_DEBUG: .autosave-timer element not found');
    }
};






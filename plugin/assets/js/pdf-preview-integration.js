/**
 * Exemple d'intégration de l'API Preview 1.4 dans l'interface
 * À intégrer dans votre éditeur ou metabox WooCommerce
 */

// ==========================================
// LOGS DE DIAGNOSTIC AU CHARGEMENT
// ==========================================

console.log('[PDF Builder] Script pdf-preview-integration.js chargé');
console.log('[PDF Builder] Variables AJAX disponibles:', {
    pdfBuilderAjax: typeof pdfBuilderAjax !== 'undefined' ? pdfBuilderAjax : 'NON DEFINI',
    ajaxurl: typeof pdfBuilderAjax !== 'undefined' ? pdfBuilderAjax.ajaxurl : 'NON DEFINI',
    nonce: typeof pdfBuilderAjax !== 'undefined' ? pdfBuilderAjax.nonce : 'NON DEFINI',
    window_pdfBuilderCanvasSettings: typeof window.pdfBuilderCanvasSettings !== 'undefined' ? 'DEFINI' : 'NON DEFINI'
});

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
        console.log('[PDF Builder] Configuration de l\'auto-save');

        const autosaveEnabled = window.pdfBuilderCanvasSettings?.autosave_enabled !== false;
        const autosaveInterval = window.pdfBuilderCanvasSettings?.autosave_interval || 5; // minutes

        console.log('[PDF Builder] Paramètres auto-save:', {
            autosaveEnabled,
            autosaveInterval,
            pdfBuilderCanvasSettings: window.pdfBuilderCanvasSettings
        });

        if (!autosaveEnabled) {
            console.log('[PDF Builder] Auto-save désactivée');
            if (this.autosaveTimerDisplay) {
                this.autosaveTimerDisplay.textContent = '💾 Sauvegarde auto désactivée';
            }
            return;
        }

        console.log('[PDF Builder] Démarrage de l\'auto-save avec intervalle:', autosaveInterval, 'minutes');
        // Démarrer le timer d'auto-sauvegarde
        this.startAutosaveTimer(autosaveInterval);

        // Sauvegarde avant de quitter la page
        window.addEventListener('beforeunload', () => {
            console.log('[PDF Builder] Sauvegarde avant de quitter la page');
            this.performAutosave();
        });
    }

    startAutosaveTimer(intervalMinutes) {
        console.log('[PDF Builder] Démarrage du timer auto-save:', intervalMinutes, 'minutes');

        this.updateAutosaveTimer(intervalMinutes);

        this.autosaveTimer = setInterval(() => {
            console.log('[PDF Builder] Timer auto-save déclenché - exécution de la sauvegarde');
            this.performAutosave();
            this.updateAutosaveTimer(intervalMinutes);
        }, intervalMinutes * 60 * 1000);

        console.log('[PDF Builder] Timer auto-save configuré avec ID:', this.autosaveTimer);

        // TEST RAPIDE : Déclencher une sauvegarde dans 10 secondes pour tester
        console.log('[PDF Builder] ⏰ Test rapide : sauvegarde dans 10 secondes...');
        setTimeout(() => {
            console.log('[PDF Builder] 🚀 Déclenchement test manuel de l\'auto-save');
            this.performAutosave();
        }, 10000);
    }

    updateAutosaveTimer(intervalMinutes) {
        if (this.autosaveTimerDisplay) {
            this.autosaveTimerDisplay.textContent = `💾 Sauvegarde auto dans ${intervalMinutes} min`;
        }
    }

    async performAutosave() {
        console.log('[PDF Builder] Début auto-sauvegarde');

        try {
            console.log('[PDF Builder] Vérification des variables AJAX:', {
                ajaxurl: pdfBuilderAjax?.ajaxurl,
                nonce: pdfBuilderAjax?.nonce,
                ajaxurlExists: typeof pdfBuilderAjax !== 'undefined'
            });

            if (this.autosaveStatus) {
                this.autosaveStatus.textContent = 'Sauvegarde en cours...';
                this.autosaveStatus.style.color = '#ffa500';
            }

            const templateData = this.getTemplateData();
            console.log('[PDF Builder] Données du template récupérées:', templateData);

            if (!templateData) {
                throw new Error('Aucune donnée de template à sauvegarder');
            }

            console.log('[PDF Builder] Préparation de la requête AJAX');
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

            console.log('[PDF Builder] Réponse HTTP reçue:', {
                status: response.status,
                statusText: response.statusText,
                ok: response.ok
            });

            const result = await response.json();
            console.log('[PDF Builder] Résultat JSON:', result);

            if (result.success) {
                console.log('[PDF Builder] Auto-sauvegarde réussie');
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
        console.log('[PDF Builder] getTemplateData() appelée');
        console.log('[PDF Builder] this.canvasEditor:', this.canvasEditor);
        console.log('[PDF Builder] window.pdfCanvasEditor:', window.pdfCanvasEditor);

        // Adapter selon votre structure de données d'éditeur
        if (this.canvasEditor && typeof this.canvasEditor.getTemplateData === 'function') {
            console.log('[PDF Builder] Utilisation de this.canvasEditor.getTemplateData()');
            const data = this.canvasEditor.getTemplateData();
            console.log('[PDF Builder] Données récupérées depuis canvasEditor:', data);
            return data;
        }

        console.log('[PDF Builder] canvasEditor non disponible, test des fallbacks');

        // Fallback: Éditeur React
        if (window.pdfBuilderReact && typeof window.pdfBuilderReact.getCurrentTemplate === 'function') {
            console.log('[PDF Builder] Utilisation de window.pdfBuilderReact.getCurrentTemplate()');
            try {
                const data = window.pdfBuilderReact.getCurrentTemplate();
                console.log('[PDF Builder] Données récupérées depuis React editor:', data);
                return data;
            } catch (error) {
                console.error('[PDF Builder] Erreur lors de la récupération depuis React editor:', error);
            }
        }

        // Fallback: chercher dans le localStorage ou les variables globales
        if (window.pdfEditorTemplate) {
            console.log('[PDF Builder] Utilisation de window.pdfEditorTemplate');
            return window.pdfEditorTemplate;
        }

        if (localStorage.getItem('pdf-builder-template')) {
            console.log('[PDF Builder] Utilisation du localStorage');
            return JSON.parse(localStorage.getItem('pdf-builder-template'));
        }

        // Template par défaut pour les tests
        return {
            template: {
                elements: [
                    {
                        type: 'text',
                        content: 'APERÇU PDF BUILDER PRO',
                        x: 50,
                        y: 50,
                        width: 300,
                        height: 40,
                        fontSize: 18,
                        fontFamily: 'Arial',
                        color: '#000000',
                        textAlign: 'center'
                    },
                    {
                        type: 'text',
                        content: 'Template de démonstration',
                        x: 50,
                        y: 100,
                        width: 300,
                        height: 30,
                        fontSize: 14,
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
    const zoomMin = document.getElementById("canvas_zoom_min")?.value || 10;
    const zoomMax = document.getElementById("canvas_zoom_max")?.value || 500;
    const zoomStep = document.getElementById("canvas_zoom_step")?.value || 25;
    const defaultZoom = document.getElementById("canvas_zoom_default")?.value || 100;

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
    const widthInput = document.getElementById("canvas_width");
    const heightInput = document.getElementById("canvas_height");
    const unitSelect = document.getElementById("canvas_unit");

    if (!widthInput || !heightInput || !unitSelect) return;

    const width = parseInt(widthInput.value);
    const height = parseInt(heightInput.value);
    const unit = unitSelect.value;

    // Mettre à jour les valeurs dans la carte dimensions
    const dimensionValues = document.querySelectorAll('.canvas-card[data-category="dimensions"] .dimension-value');

    if (dimensionValues.length >= 2) {
        dimensionValues[0].textContent = `${width} ${unit}`;
        dimensionValues[1].textContent = `${height} ${unit}`;
    }

    // Calculer et afficher le ratio
    const ratioValue = document.querySelector('.canvas-card[data-category="dimensions"] .ratio-value');
    if (ratioValue && width > 0 && height > 0) {
        const gcd = (a, b) => b === 0 ? a : gcd(b, a % b);
        const divisor = gcd(width, height);
        const ratio = `${width/divisor}:${height/divisor}`;
        ratioValue.textContent = ratio;
    }
};

/**
 * Met à jour la prévisualisation de la carte auto-save
 */
window.updateAutosaveCardPreview = function() {
    const intervalInput = document.getElementById("canvas_autosave_interval");
    const enabledCheckbox = document.getElementById("canvas_autosave_enabled");

    if (!intervalInput || !enabledCheckbox) return;

    const interval = parseInt(intervalInput.value);
    const enabled = enabledCheckbox.checked;

    // Mettre à jour l'indicateur de statut
    const statusIndicator = document.querySelector('.canvas-card[data-category="autosave"] .status-indicator');
    if (statusIndicator) {
        const statusDot = statusIndicator.querySelector('.status-dot');
        const statusText = statusIndicator.querySelector('.status-text');

        if (enabled) {
            if (statusDot) statusDot.style.backgroundColor = '#28a745';
            if (statusText) statusText.textContent = `Activé (${interval}s)`;
        } else {
            if (statusDot) statusDot.style.backgroundColor = '#dc3545';
            if (statusText) statusText.textContent = 'Désactivé';
        }
    }

    // Mettre à jour la barre de progression simulée
    const progressBar = document.querySelector('.canvas-card[data-category="autosave"] .progress-fill');
    if (progressBar && enabled) {
        // Simuler une progression basée sur le temps écoulé
        const now = Date.now();
        const progress = ((now % (interval * 1000)) / (interval * 1000)) * 100;
        progressBar.style.width = `${progress}%`;
    } else if (progressBar) {
        progressBar.style.width = '0%';
    }
};

// ==========================================
// INITIALISATION AUTOMATIQUE
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    console.log('[PDF Builder] Initialisation automatique - URL actuelle:', window.location.href);
    console.log('[PDF Builder] Éléments DOM détectés:', {
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

        console.log('[PDF Builder] Condition éditeur remplie - création de PDFEditorPreviewIntegration');
        window.pdfEditorPreview = new PDFEditorPreviewIntegration(window.pdfCanvasEditor);
    } else {
        console.log('[PDF Builder] Condition éditeur NON remplie - pas d\'initialisation');
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
 * Génère un aperçu rapide (détection automatique du contexte)
 */
window.generateQuickPreview = async function(templateData = null, orderId = null) {
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






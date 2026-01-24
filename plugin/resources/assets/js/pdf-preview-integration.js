/**
 * Système de logging centralisé pour PDF Builder
 */
window.PDFBuilderLogger = {
    levels: {
        DEBUG: 0,
        INFO: 1,
        WARN: 2,
        ERROR: 3,
        NONE: 4
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
    },

    info: function(message, ...args) {
    },

    warn: function(message, ...args) {
    },

    error: function(message, ...args) {
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
// Désactivé pour éviter les plantages du navigateur dus aux logs excessifs
window.PDFBuilderLogger.setLevel('NONE');

// ==========================================
// INTÉGRATION DANS L'ÉDITEUR (Canvas)
// ==========================================

class PDFEditorPreviewIntegration {
    constructor(canvasEditor) {
        this.canvasEditor = canvasEditor;
        this.previewBtn = null;
        this.init();
    }

    init() {
        this.createPreviewButton();
        this.bindEvents();
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

    async generatePreview() {
        try {
            PDFBuilderLogger.info('🎨 [JS] generatePreview() appelée depuis éditeur');

            // ESSAI 1: Récupérer les données du template depuis l'éditeur
            let templateData = this.getTemplateData();
            PDFBuilderLogger.info('🎨 [JS] Données template récupérées:', templateData);

            // ESSAI 2: Si pas de données, utiliser un template de test simple
            if (!templateData || !templateData.template || !templateData.template.elements || templateData.template.elements.length === 0) {
                PDFBuilderLogger.warn('⚠️ [JS] Aucune donnée template trouvée, utilisation template de test');
                templateData = {
                    templateId: 'test-injection-' + Date.now(),
                    template: {
                        name: 'Test Injection Variables',
                        elements: [
                            {
                                type: 'text',
                                content: 'TEST INJECTION VARIABLES - CANVAS',
                                x: 50,
                                y: 50,
                                width: 400,
                                height: 40,
                                fontSize: 18,
                                color: '#2c3e50'
                            },
                            {
                                type: 'text',
                                content: 'Client: {{customer_name}} ({{customer_email}})',
                                x: 50,
                                y: 100,
                                width: 400,
                                height: 30,
                                fontSize: 14,
                                color: '#34495e'
                            },
                            {
                                type: 'text',
                                content: 'Commande: {{order_number}} - {{order_total}}',
                                x: 50,
                                y: 130,
                                width: 400,
                                height: 30,
                                fontSize: 14,
                                color: '#34495e'
                            },
                            {
                                type: 'text',
                                content: 'Entreprise: {{company_name}}',
                                x: 50,
                                y: 160,
                                width: 400,
                                height: 30,
                                fontSize: 14,
                                color: '#34495e'
                            }
                        ]
                    }
                };
                PDFBuilderLogger.info('🎨 [JS] Template de test créé:', templateData);
            }

            // Générer l'aperçu avec contexte explicite
            PDFBuilderLogger.info('🎨 [JS] Génération aperçu avec templateData et options');
            const result = await window.generateEditorPreview(templateData, {
                quality: 150,
                format: 'png',
                context: 'editor'  // Forcer le contexte editor
            });

            PDFBuilderLogger.info('🎨 [JS] Résultat génération:', result);

            if (result) {
                PDFBuilderLogger.info('✅ [JS] Aperçu généré avec succès');
            } else {
                PDFBuilderLogger.warn('⚠️ [JS] Génération retournée null/vide');
            }

        } catch (error) {
            PDFBuilderLogger.error('❌ [JS] Erreur génération aperçu:', error);
            alert('Erreur lors de la génération de l\'aperçu: ' + error.message + '\nVérifiez la console pour plus de détails.');
        }
    }

    getTemplateData() {
        PDFBuilderLogger.debug('getTemplateData() appelée');
        PDFBuilderLogger.debug('this.canvasEditor:', this.canvasEditor);
        PDFBuilderLogger.debug('window.pdfCanvasEditor:', window.pdfCanvasEditor);
        PDFBuilderLogger.debug('window.pdfBuilderReact:', window.pdfBuilderReact);

        // Priorité 1: Éditeur React - essayer plusieurs méthodes
        if (window.pdfBuilderReact) {
            PDFBuilderLogger.debug('Éditeur React détecté, test des méthodes...');

            // Méthode 1: getCurrentTemplate
            if (typeof window.pdfBuilderReact.getCurrentTemplate === 'function') {
                try {
                    const data = window.pdfBuilderReact.getCurrentTemplate();
                    PDFBuilderLogger.debug('getCurrentTemplate() résultat:', data);
                    if (data && (data.elements || data.template)) {
                        PDFBuilderLogger.info('✅ Données récupérées via getCurrentTemplate');
                        return data;
                    }
                } catch (error) {
                    PDFBuilderLogger.error('Erreur getCurrentTemplate:', error);
                }
            }

            // Méthode 2: getEditorState
            if (typeof window.pdfBuilderReact.getEditorState === 'function') {
                try {
                    const data = window.pdfBuilderReact.getEditorState();
                    PDFBuilderLogger.debug('getEditorState() résultat:', data);
                    if (data && (data.elements || data.template)) {
                        PDFBuilderLogger.info('✅ Données récupérées via getEditorState');
                        return data;
                    }
                } catch (error) {
                    PDFBuilderLogger.error('Erreur getEditorState:', error);
                }
            }

            // Méthode 3: Chercher dans les variables globales React
            if (window.pdfBuilderReact.currentTemplate) {
                PDFBuilderLogger.debug('currentTemplate trouvé:', window.pdfBuilderReact.currentTemplate);
                return window.pdfBuilderReact.currentTemplate;
            }

            if (window.pdfBuilderReact.editorState) {
                PDFBuilderLogger.debug('editorState trouvé:', window.pdfBuilderReact.editorState);
                return window.pdfBuilderReact.editorState;
            }
        }

        // Priorité 2: Éditeur canvas traditionnel
        if (this.canvasEditor && typeof this.canvasEditor.getTemplateData === 'function') {
            PDFBuilderLogger.debug('Utilisation de this.canvasEditor.getTemplateData()');
            const data = this.canvasEditor.getTemplateData();
            PDFBuilderLogger.debug('Données récupérées depuis canvasEditor:', data);
            if (data) return data;
        }

        // Fallback: Variables globales
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
            templateId: 'preview-test-' + Date.now(),
            template: {
                name: 'Template Aperçu Test',
                elements: [
                    {
                        type: 'text',
                        content: 'APERÇU PDF BUILDER PRO - TEST DONNÉES',
                        x: 50,
                        y: 50,
                        width: 400,
                        height: 40,
                        fontSize: 18,
                        color: '#2c3e50'
                    },
                    {
                        type: 'text',
                        content: 'Client: {{customer_name}}',
                        x: 50,
                        y: 100,
                        width: 300,
                        height: 30,
                        fontSize: 14,
                        color: '#34495e'
                    },
                    {
                        type: 'text',
                        content: 'Commande: {{order_number}}',
                        x: 50,
                        y: 130,
                        width: 300,
                        height: 30,
                        fontSize: 14,
                        color: '#34495e'
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
    window.PDFBuilderLogger.info('updatePerformanceCardPreview called');
    try {
        const values = window.CanvasPreviewManager.getCardValues('performance');
        window.PDFBuilderLogger.info('updatePerformanceCardPreview - values:', values);
        const { fps_target: fps, memory_limit_js: memoryJs, lazy_loading_editor: lazyLoading } = values;

        // Fonction helper pour déterminer le statut
        const getStatus = (value, thresholds) => {
            if (value >= thresholds.good) return { text: '🟢 Bon', color: '#28a745' };
            if (value >= thresholds.medium) return { text: '🟡 Moyen', color: '#ffc107' };
            return { text: '🔴 Faible', color: '#dc3545' };
        };

        // Mettre à jour les métriques
        const metricValues = document.querySelectorAll('.canvas-card[data-category="performance"] .metric-value');
        if (metricValues.length >= 3) {
            // FPS
            const fpsStatus = getStatus(fps, { good: 30, medium: 15 });
            metricValues[0].innerHTML = `${fps}<br><small>${fpsStatus.text}</small>`;

            // RAM JS
            const memoryStatus = getStatus(memoryJs, { good: 128, medium: 64 });
            metricValues[1].innerHTML = `${memoryJs}MB<br><small>${memoryStatus.text}</small>`;

            // RAM PHP - valeur fixe
            metricValues[2].innerHTML = `256MB<br><small>🟢 Bon</small>`;
        }

        // Mettre à jour l'indicateur de statut
        const statusIndicator = window.CanvasPreviewManager.getCardElement('performance', '.status-indicator');
        if (statusIndicator) {
            const statusText = lazyLoading ? 'Lazy Loading Activé' : 'Lazy Loading Désactivé';
            const statusDot = statusIndicator.querySelector('.status-dot');
            const statusTextEl = statusIndicator.querySelector('.status-text');

            window.CanvasPreviewManager.updateElement(statusDot, 'style.backgroundColor', lazyLoading ? '#28a745' : '#dc3545');
            window.CanvasPreviewManager.updateElement(statusTextEl, 'textContent', statusText);
        }
        
        window.PDFBuilderLogger.info('updatePerformanceCardPreview completed successfully');
    } catch (error) {
        window.PDFBuilderLogger.error('Error in updatePerformanceCardPreview:', error);
        throw error; // Re-throw to be caught by updatePreviews
    }
};

/**
 * Met à jour la prévisualisation de la carte apparence
 * Fonction legacy - utilise maintenant le système générique
 */
window.updateApparenceCardPreview = function() {
    // Les aperçus sont maintenant gérés par CSS uniquement - pas de modification dynamique
    // pour éviter les changements de couleur lors des rechargements
    window.PDFBuilderLogger.info('Apparence preview managed by CSS only');
};

/**
 * Met à jour la prévisualisation de la carte grille
 */
window.updateGrilleCardPreview = function() {
    const values = window.CanvasPreviewManager.getCardValues('grille');
    const { show_grid: gridEnabled, snap_to_grid: snapToGrid, show_guides: showGuides } = values;

    const gridContainer = window.CanvasPreviewManager.getCardElement('grille', '.grid-preview-container');
    if (!gridContainer) return;

    // Activer/désactiver la grille
    gridContainer.classList.toggle('grid-enabled', gridEnabled);
    gridContainer.classList.toggle('grid-disabled', !gridEnabled);

    // Afficher/cacher les guides
    const guideLines = gridContainer.querySelectorAll('.guide-line');
    guideLines.forEach(guide => guide.classList.toggle('active', showGuides));

    // Mettre à jour l'indicateur de snap
    const snapIndicator = gridContainer.querySelector('.snap-indicator');
    if (snapIndicator) {
        const isActive = snapToGrid && gridEnabled;
        snapIndicator.textContent = isActive ? '🔗 Snap activé' : '🔗 Snap désactivé';
        snapIndicator.style.color = isActive ? '#28a745' : '#6c757d';
    }
};

/**
 * Ancienne fonction updateZoomCardPreview - remplacée par celle utilisant CanvasPreviewManager
 * Gardée pour compatibilité temporaire
 */

/**
 * Met à jour la prévisualisation de la carte interactions
 */
window.updateInteractionsCardPreview = function() {
    const values = window.CanvasPreviewManager.getCardValues('interactions');
    const { selection_mode: selectionMode, keyboard_shortcuts: keyboardShortcuts } = values;

    const modeIcons = document.querySelectorAll('.canvas-card[data-category="interactions"] .mode-icon');
    const statusIndicator = window.CanvasPreviewManager.getCardElement('interactions', '.status-indicator');
    const zoomLevel = window.CanvasPreviewManager.getCardElement('interactions', '.zoom-level');
    const performanceFill = window.CanvasPreviewManager.getCardElement('interactions', '.performance-fill');
    const performanceText = window.CanvasPreviewManager.getCardElement('interactions', '.performance-text');
    const keyboardStatus = window.CanvasPreviewManager.getCardElement('interactions', '.keyboard-status');
    const progressFill = window.CanvasPreviewManager.getCardElement('interactions', '.progress-fill');
    const progressValue = window.CanvasPreviewManager.getCardElement('interactions', '.progress-value');

    // Mettre à jour les icônes de mode
    modeIcons.forEach(icon => {
        icon.classList.remove('active');
        const mode = icon.getAttribute('data-mode');
        if (mode === selectionMode) {
            icon.classList.add('active');
        }
    });

    // Mettre à jour l'indicateur de statut
    if (statusIndicator) {
        const isEnabled = keyboardShortcuts;
        statusIndicator.textContent = isEnabled ? 'Raccourcis activés' : 'Raccourcis désactivés';
        statusIndicator.className = `status-indicator ${isEnabled ? 'enabled' : 'disabled'}`;
    }

    // Mettre à jour le niveau de zoom (simulation)
    if (zoomLevel) {
        const zoomValues = ['75%', '100%', '125%', '150%'];
        const randomZoom = zoomValues[Math.floor(Math.random() * zoomValues.length)];
        zoomLevel.textContent = randomZoom;
    }

    // Mettre à jour l'indicateur de performance
    if (performanceFill && performanceText) {
        const performanceValues = [65, 78, 85, 92, 96];
        const randomPerf = performanceValues[Math.floor(Math.random() * performanceValues.length)];
        performanceFill.style.width = `${randomPerf}%`;
        performanceText.textContent = `${randomPerf}%`;
    }

    // Mettre à jour le statut du clavier
    if (keyboardStatus) {
        keyboardStatus.className = `keyboard-status ${keyboardShortcuts ? '' : 'inactive'}`;
    }

    // Mettre à jour la barre de progression (fluidité)
    if (progressFill && progressValue) {
        const fluidityValues = [78, 85, 92, 96, 98];
        const randomFluidity = fluidityValues[Math.floor(Math.random() * fluidityValues.length)];
        progressFill.style.width = `${randomFluidity}%`;
        progressValue.textContent = `${randomFluidity}%`;
    }
};

/**
 * Met à jour la prévisualisation de la carte export
 */
window.updateExportCardPreview = function() {
    const values = window.CanvasPreviewManager.getCardValues('export');
    const { export_quality: exportQuality } = values;

    const qualityFill = window.CanvasPreviewManager.getCardElement('export', '.quality-fill');
    const qualityText = window.CanvasPreviewManager.getCardElement('export', '.quality-text');

    window.CanvasPreviewManager.updateElement(qualityFill, 'style.width', `${exportQuality}%`);
    window.CanvasPreviewManager.updateElement(qualityText, 'textContent', `${exportQuality}%`);
};

// ==========================================
// FONCTIONS GLOBALES DE PRÉVISUALISATION DES CARTES
// ==========================================

/**
 * Ancienne fonction updateDimensionsCardPreview - remplacée par celle utilisant CanvasPreviewManager
 * Gardée pour compatibilité temporaire
 *//**
 * Ancienne fonction updateAutosaveCardPreview - remplacée par celle utilisant CanvasPreviewManager
 * Gardée pour compatibilité temporaire
 */

// ==========================================
// INITIALISATION AUTOMATIQUE
// ==========================================

document.addEventListener('DOMContentLoaded', function() {
    window.PDFBuilderLogger.info('Initialisation automatique - URL actuelle:', window.location.href);
    window.PDFBuilderLogger.debug('Éléments DOM détectés:', {
        pdfEditorCanvas: !!document.querySelector('#pdf-editor-canvas'),
        pdfCanvasEditor: !!document.querySelector('.pdf-canvas-editor'),
        pdfBuilderEditor: window.location.href.includes('pdf-builder-editor'),
        pdfBuilderReactEditor: window.location.href.includes('pdf-builder-react-editor')
    });

    // Initialiser l'intégration éditeur si on est dans l'éditeur
    if ((document.querySelector('#pdf-editor-canvas') ||
        document.querySelector('.pdf-canvas-editor') ||
        window.location.href.includes('pdf-builder-editor') ||
        window.location.href.includes('pdf-builder-react-editor')) &&
        !window.location.href.includes('pdf-builder-settings')) {

        window.PDFBuilderLogger.info('Condition éditeur remplie - création de PDFEditorPreviewIntegration');
        window.pdfEditorPreview = new PDFEditorPreviewIntegration(window.pdfCanvasEditor);
    } else {
        window.PDFBuilderLogger.debug('Condition éditeur NON remplie - pas d\'initialisation');
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

        // No notification system: log error to console
        logger.error(`${context}: ${message}`);
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
            updateFunction: 'updateDimensionsCardPreview',
            previewRules: [
                {
                    type: 'dimensions_calculation',
                    target: '#card-canvas-width',
                    property: 'textContent',
                    calculate: function(values) {
                        const { default_canvas_format: format, default_canvas_dpi: dpi, default_canvas_orientation: orientation } = values;
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
                        if (orientation === 'landscape') {
                            [widthMm, heightMm] = [heightMm, widthMm];
                        }
                        const pixelsPerMM = dpi / 25.4;
                        return Math.round(widthMm * pixelsPerMM);
                    }
                },
                {
                    type: 'dimensions_calculation',
                    target: '#card-canvas-height',
                    property: 'textContent',
                    calculate: function(values) {
                        const { default_canvas_format: format, default_canvas_dpi: dpi, default_canvas_orientation: orientation } = values;
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
                        if (orientation === 'landscape') {
                            [widthMm, heightMm] = [heightMm, widthMm];
                        }
                        const pixelsPerMM = dpi / 25.4;
                        return Math.round(heightMm * pixelsPerMM);
                    }
                },
                {
                    type: 'dimensions_calculation',
                    target: '#card-canvas-dpi',
                    property: 'textContent',
                    calculate: function(values) {
                        const { default_canvas_format: format, default_canvas_dpi: dpi, default_canvas_orientation: orientation } = values;
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
                        if (orientation === 'landscape') {
                            [widthMm, heightMm] = [heightMm, widthMm];
                        }
                        return `${dpi} DPI - ${format} (${widthMm}×${heightMm}mm)`;
                    }
                }
            ]
        },
        apparence: {
            inputs: ['canvas_bg_color', 'canvas_border_color', 'canvas_border_width', 'canvas_shadow_enabled'],
            settings: ['canvas_background_color', 'border_color', 'border_width', 'shadow_enabled'],
            defaults: ['#ffffff', '#cccccc', 1, false],
            updateFunction: 'updateApparenceCardPreview',
            previewRules: [
                {
                    type: 'style',
                    target: '.apparence-background',
                    property: 'style.backgroundColor',
                    value: '{{canvas_background_color}}'
                },
                {
                    type: 'style',
                    target: '.apparence-border',
                    property: 'style.border',
                    value: '{{border_width}}px solid {{border_color}}'
                },
                {
                    type: 'element_update',
                    target: '.apparence-shadow',
                    property: 'style.display',
                    value: '{{shadow_enabled ? "block" : "none"}}'
                },
                {
                    type: 'element_update',
                    target: '.apparence-legend .legend-item:nth-child(3)',
                    property: 'innerHTML',
                    value: '{{shadow_enabled ? "🌑 Ombre" : "☀️ Ombre"}}'
                }
            ]
        },
        grille: {
            inputs: ['canvas_grid_enabled', 'canvas_snap_to_grid', 'canvas_show_guides'],
            settings: ['show_grid', 'snap_to_grid', 'show_guides'],
            defaults: [true, true, true],
            updateFunction: 'updateGrilleCardPreview',
            previewRules: [
                {
                    type: 'class_toggle',
                    target: '.grid-preview-container',
                    className: 'grid-enabled',
                    condition: '{{show_grid}}'
                },
                {
                    type: 'class_toggle',
                    target: '.grid-preview-container',
                    className: 'grid-disabled',
                    condition: '!{{show_grid}}'
                },
                {
                    type: 'class_toggle_all',
                    target: '.guide-line',
                    className: 'active',
                    condition: '{{show_guides}}'
                },
                {
                    type: 'element_update',
                    target: '.snap-indicator',
                    property: 'textContent',
                    value: '{{show_grid && snap_to_grid ? "🔗 Snap activé" : "🔗 Snap désactivé"}}'
                },
                {
                    type: 'style',
                    target: '.snap-indicator',
                    property: 'style.color',
                    value: '{{show_grid && snap_to_grid ? "#28a745" : "#6c757d"}}'
                }
            ]
        },
        zoom: {
            inputs: ['canvas_zoom'],
            settings: ['default_canvas_zoom'],
            defaults: [100],
            updateFunction: 'updateZoomCardPreview',
            previewRules: [
                {
                    type: 'text',
                    target: '.zoom-level',
                    property: 'textContent',
                    value: '{{default_canvas_zoom}}%'
                }
            ]
        },
        interactions: {
            inputs: ['canvas_selection_mode', 'canvas_keyboard_shortcuts'],
            settings: ['selection_mode', 'keyboard_shortcuts'],
            defaults: ['click', true],
            updateFunction: 'updateInteractionsCardPreview',
            previewRules: [
                {
                    type: 'class_toggle',
                    target: '.mode-icon[title="Rectangle"]',
                    className: 'active',
                    condition: '{{selection_mode === "rectangle"}}'
                },
                {
                    type: 'class_toggle',
                    target: '.mode-icon[title="Lasso"]',
                    className: 'active',
                    condition: '{{selection_mode === "lasso"}}'
                },
                {
                    type: 'class_toggle',
                    target: '.mode-icon[title="Clic"]',
                    className: 'active',
                    condition: '{{selection_mode === "click"}}'
                },
                {
                    type: 'text',
                    target: '.status-indicator',
                    property: 'textContent',
                    value: '{{keyboard_shortcuts ? "Raccourcis activés" : "Raccourcis désactivés"}}'
                },
                {
                    type: 'class',
                    target: '.status-indicator',
                    property: 'className',
                    value: 'status-indicator {{keyboard_shortcuts ? "enabled" : "disabled"}}'
                }
            ]
        },
        export: {
            inputs: ['canvas_export_quality'],
            settings: ['export_quality'],
            defaults: [90],
            updateFunction: 'updateExportCardPreview',
            previewRules: [
                {
                    type: 'style',
                    target: '.quality-fill',
                    property: 'style.width',
                    value: '{{export_quality}}%'
                },
                {
                    type: 'text',
                    target: '.quality-text',
                    property: 'textContent',
                    value: '{{export_quality}}%'
                }
            ]
        },
        performance: {
            inputs: ['canvas_fps_target', 'canvas_memory_limit_js', 'canvas_lazy_loading_editor'],
            settings: ['fps_target', 'memory_limit_js', 'lazy_loading_editor'],
            defaults: [60, 128, true],
            updateFunction: 'updatePerformanceCardPreview',
            previewRules: [
                {
                    type: 'complex_update',
                    updateFunction: function(values) {
                        const { fps_target: fps, memory_limit_js: memoryJs, lazy_loading_editor: lazyLoading } = values;

                        // Fonction helper pour déterminer le statut
                        const getStatus = (value, thresholds) => {
                            if (value >= thresholds.good) return { text: '🟢 Bon', color: '#28a745' };
                            if (value >= thresholds.medium) return { text: '🟡 Moyen', color: '#ffc107' };
                            return { text: '🔴 Faible', color: '#dc3545' };
                        };

                        // Mettre à jour les métriques
                        const metricValues = document.querySelectorAll('.canvas-card[data-category="performance"] .metric-value');
                        if (metricValues.length >= 3) {
                            // FPS
                            const fpsStatus = getStatus(fps, { good: 30, medium: 15 });
                            metricValues[0].innerHTML = `${fps}<br><small>${fpsStatus.text}</small>`;

                            // RAM JS
                            const memoryStatus = getStatus(memoryJs, { good: 128, medium: 64 });
                            metricValues[1].innerHTML = `${memoryJs}MB<br><small>${memoryStatus.text}</small>`;

                            // RAM PHP - valeur fixe
                            metricValues[2].innerHTML = `256MB<br><small>🟢 Bon</small>`;
                        }

                        // Mettre à jour l'indicateur de statut
                        const statusIndicator = document.querySelector('.canvas-card[data-category="performance"] .status-indicator');
                        if (statusIndicator) {
                            const statusText = lazyLoading ? 'Lazy Loading Activé' : 'Lazy Loading Désactivé';
                            const statusDot = statusIndicator.querySelector('.status-dot');
                            const statusTextEl = statusIndicator.querySelector('.status-text');

                            if (statusDot) statusDot.style.backgroundColor = lazyLoading ? '#28a745' : '#dc3545';
                            if (statusTextEl) statusTextEl.textContent = statusText;
                        }
                    }
                }
            ]
        },
        autosave: {
            inputs: ['canvas_autosave_interval'],
            settings: ['autosave_interval'],
            defaults: [5],
            updateFunction: 'updateAutosaveCardPreview',
            previewRules: [
                {
                    type: 'text',
                    target: '.autosave-timer',
                    property: 'textContent',
                    value: '{{autosave_interval}}min'
                },
                {
                    type: 'text',
                    target: '.autosave-status',
                    property: 'textContent',
                    value: '{{autosave_enabled ? "Activé" : "Désactivé"}}'
                }
            ]
        },
        debug: {
            inputs: ['canvas_debug_enabled'],
            settings: ['debug_mode'],
            defaults: [false],
            updateFunction: 'updateDebugCardPreview',
            previewRules: [
                {
                    type: 'text',
                    target: '.status-indicator',
                    property: 'textContent',
                    value: '{{debug_mode ? "Debug Activé" : "Debug Désactivé"}}'
                },
                {
                    type: 'class',
                    target: '.status-indicator',
                    property: 'className',
                    value: 'status-indicator {{debug_mode ? "enabled" : "disabled"}}'
                }
            ]
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
        // Priorité aux settings sauvegardés, puis valeur par défaut
        return window.pdfBuilderCanvasSettings?.[settingKey] !== undefined ? window.pdfBuilderCanvasSettings[settingKey] : defaultValue;
    },

    /**
     * Récupère toutes les valeurs d'une carte
     */
    getCardValues: function(category) {
        const config = this.cardConfigs[category];
        if (!config) return {};

        const values = {};
        config.inputs.forEach((inputId, index) => {
            values[config.settings[index]] = this.getValue(inputId, config.settings[index], config.defaults[index]);
        });
        return values;
    },

    /**
     * Récupère un élément DOM d'une carte avec un sélecteur relatif
     */
    getCardElement: function(category, selector) {
        return document.querySelector(`.canvas-card[data-category="${category}"] ${selector}`);
    },

    /**
     * Met à jour un élément avec gestion d'erreur
     */
    updateElement: function(element, property, value) {
        if (!element) {
            window.PDFBuilderLogger.debug(`Element not found for ${property}`);
            return false;
        }
        try {
            if (property === 'textContent') element.textContent = value;
            else if (property === 'innerHTML') element.innerHTML = value;
            else if (property === 'style') Object.assign(element.style, value);
            else if (property === 'className') element.className = value;
            else if (property.startsWith('style.')) element.style[property.split('.')[1]] = value;
            else element[property] = value;
            return true;
        } catch (e) {
            window.PDFBuilderLogger.error(`Error updating element ${property}:`, e);
            return false;
        }
    },

    /**
     * Met à jour toutes les previews ou une catégorie spécifique
     */
    updatePreviews: function(category = 'all') {
        window.PDFBuilderLogger.info('CanvasPreviewManager.updatePreviews called with category:', category);
        window.PDFBuilderLogger.debug('CanvasPreviewManager.updatePreviews called with category:', category);

        Object.keys(this.cardConfigs).forEach(cardCategory => {
            if (category === 'all' || category === cardCategory) {
                const config = this.cardConfigs[cardCategory];

                // Essayer d'abord la fonction générique, puis la fonction spécifique
                let updateSuccessful = false;

                // 1. Essayer la fonction générique
                try {
                    this.updateCardPreviewGeneric(cardCategory);
                    updateSuccessful = true;
                    window.PDFBuilderLogger.info('Generic update function succeeded for:', cardCategory);
                } catch (error) {
                    window.PDFBuilderLogger.warn('Generic update failed for', cardCategory, '- trying specific function:', error.message);
                }

                // 2. Si la fonction générique échoue, utiliser la fonction spécifique (fallback)
                if (!updateSuccessful && typeof window[config.updateFunction] === 'function') {
                    try {
                        window.PDFBuilderLogger.info('Calling specific update function:', config.updateFunction, 'for category:', cardCategory);
                        window.PDFBuilderLogger.debug('Calling specific update function:', config.updateFunction);
                        window[config.updateFunction]();
                        window.PDFBuilderLogger.info('Specific update function completed successfully:', config.updateFunction);
                        PDFBuilderLogger.debug('Specific update function completed:', config.updateFunction);
                    } catch (error) {
                        window.PDFBuilderLogger.error('Error in specific update function:', config.updateFunction, 'Error:', error);
                        PDFBuilderLogger.error('Error in specific update function:', config.updateFunction, error);
                        // Continue with other functions instead of stopping
                    }
                }
            }
        });
    },

    /**
     * Fonction générique de mise à jour des previews utilisant les règles de configuration
     */
    updateCardPreviewGeneric: function(category) {
        const config = this.cardConfigs[category];
        if (!config || !config.previewRules) {
            throw new Error(`No preview rules defined for category: ${category}`);
        }

        window.PDFBuilderLogger.info(`updateCardPreviewGeneric called for category: ${category}`);

        const values = this.getCardValues(category);
        window.PDFBuilderLogger.debug(`Values for ${category}:`, values);

        // Appliquer chaque règle de preview
        config.previewRules.forEach((rule, index) => {
            try {
                this.applyPreviewRule(category, rule, values);
                window.PDFBuilderLogger.debug(`Applied rule ${index} for ${category}`);
            } catch (error) {
                window.PDFBuilderLogger.error(`Error applying rule ${index} for ${category}:`, error);
                // Continue avec les autres règles même si une échoue
            }
        });

        window.PDFBuilderLogger.info(`updateCardPreviewGeneric completed for category: ${category}`);
    },

    /**
     * Applique une règle de preview spécifique
     */
    applyPreviewRule: function(category, rule, values) {
    let processedValue, element, shouldAdd, toggleElement, shouldAddAll, toggleElements, updateValue, updateElement, calculatedValue, calcElement;

    switch (rule.type) {
        case 'text':
        case 'style':
        case 'class':
            processedValue = this.processTemplateValue(rule.value, values);
            element = this.getCardElement(category, rule.target);
            if (element) {
                this.updateElement(element, rule.property, processedValue);
            }
            break;

        case 'class_toggle':
            shouldAdd = this.evaluateCondition(rule.condition, values);
            toggleElement = this.getCardElement(category, rule.target);
            if (toggleElement) {
                if (shouldAdd) {
                    toggleElement.classList.add(rule.className);
                } else {
                    toggleElement.classList.remove(rule.className);
                }
            }
            break;

        case 'class_toggle_all':
            shouldAddAll = this.evaluateCondition(rule.condition, values);
            toggleElements = document.querySelectorAll(`.canvas-card[data-category="${category}"] ${rule.target}`);
            toggleElements.forEach(function(el) {
                if (shouldAddAll) {
                    el.classList.add(rule.className);
                } else {
                    el.classList.remove(rule.className);
                }
            });
            break;

        case 'element_update':
            updateValue = this.processTemplateValue(rule.value, values);
            updateElement = this.getCardElement(category, rule.target);
            if (updateElement) {
                this.updateElement(updateElement, rule.property, updateValue);
            }
            break;

        case 'dimensions_calculation':
            calculatedValue = rule.calculate(values);
            calcElement = this.getCardElement(category, rule.target);
            if (calcElement) {
                this.updateElement(calcElement, rule.property, calculatedValue);
            }
            break;

        case 'complex_update':
            if (typeof rule.updateFunction === 'function') {
                rule.updateFunction(values);
            }
            break;

        default:
            window.PDFBuilderLogger.warn(`Unknown rule type: ${rule.type}`);
    }
},

    /**
     * Traite les valeurs template avec des variables
     */
    processTemplateValue: function(template, values) {
        if (typeof template !== 'string') {
            return template;
        }

        // Remplacer les variables {{variable}} par leurs valeurs
        let result = template;
        Object.keys(values).forEach(key => {
            const regex = new RegExp(`{{${key}}}`, 'g');
            result = result.replace(regex, values[key]);
        });

        // Évaluer les expressions JavaScript simples
        result = result.replace(/{{([^}]+)}}/g, (match, expression) => {
            try {
                // Fonction d'évaluation sécurisée pour les expressions simples
                const func = new Function('values', `with(values) { return ${expression}; }`);
                return func(values);
            } catch (error) {
                window.PDFBuilderLogger.error('Error evaluating expression:', expression, error);
                return match; // Retourner l'expression originale si elle échoue
            }
        });

        return result;
    },

    /**
     * Évalue une condition simple
     */
    evaluateCondition: function(condition, values) {
        if (typeof condition !== 'string') {
            return Boolean(condition);
        }

        try {
            // Fonction d'évaluation sécurisée pour les conditions
            const func = new Function('values', `with(values) { return ${condition}; }`);
            return Boolean(func(values));
        } catch (error) {
            window.PDFBuilderLogger.error('Error evaluating condition:', condition, error);
            return false;
        }
    },

    /**
     * Initialise les event listeners pour les mises à jour en temps réel
     */
    initializeRealTimeUpdates: function(modal) {
        if (!modal) return;

        const category = modal.getAttribute('data-category');
        const config = this.cardConfigs[category];

        if (!config) return;

        window.PDFBuilderLogger.debug('Initializing real-time updates for modal category:', category);

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
        window.PDFBuilderLogger.debug('Input changed:', input.id, 'Value:', input.value, 'Category:', category);

        // Mettre à jour la preview correspondante
        const config = this.cardConfigs[category];
        if (config && typeof window[config.updateFunction] === 'function') {
            window[config.updateFunction]();
        }
    }
};

/**
 * Met à jour la prévisualisation de la carte dimensions
 * Fonction legacy - utilise maintenant le système générique
 */
window.updateDimensionsCardPreview = function() {
    try {
        window.CanvasPreviewManager.updateCardPreviewGeneric('dimensions');
    } catch (error) {
        // Fallback vers l'ancienne implémentation si la générique échoue
        window.PDFBuilderLogger.warn('Generic dimensions preview failed, using legacy implementation:', error.message);
        const values = window.CanvasPreviewManager.getCardValues('dimensions');
        const { default_canvas_format: format, default_canvas_dpi: dpi, default_canvas_orientation: orientation } = values;

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

        if (orientation === 'landscape') {
            [widthMm, heightMm] = [heightMm, widthMm];
        }

        const pixelsPerMM = dpi / 25.4;
        const widthPx = Math.round(widthMm * pixelsPerMM);
        const heightPx = Math.round(heightMm * pixelsPerMM);

        const widthElement = window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-width');
        const heightElement = window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-height');
        const dpiElement = window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-dpi');

        window.CanvasPreviewManager.updateElement(widthElement, 'textContent', widthPx);
        window.CanvasPreviewManager.updateElement(heightElement, 'textContent', heightPx);
        window.CanvasPreviewManager.updateElement(dpiElement, 'textContent', `${dpi} DPI - ${format} (${widthMm}×${heightMm}mm)`);
    }
};

/**
 * Met à jour la prévisualisation de la carte zoom
 */
window.updateZoomCardPreview = function() {
    const values = window.CanvasPreviewManager.getCardValues('zoom');
    const { default_canvas_zoom: zoom } = values;

    const zoomElement = window.CanvasPreviewManager.getCardElement('zoom', '.zoom-level');
    window.CanvasPreviewManager.updateElement(zoomElement, 'textContent', `${zoom}%`);
};

/**
 * Met à jour la prévisualisation de la carte sauvegarde automatique
 */
window.updateAutosaveCardPreview = function() {
    const values = window.CanvasPreviewManager.getCardValues('autosave');
    const { autosave_interval: autosave } = values;

    // Also get the enabled status
    const enabledInput = document.getElementById('canvas_autosave_enabled');
    const enabled = enabledInput ? enabledInput.checked :
                   (window.pdfBuilderCanvasSettings?.autosave_enabled !== undefined ? window.pdfBuilderCanvasSettings.autosave_enabled : true);

    const autosaveElement = window.CanvasPreviewManager.getCardElement('autosave', '.autosave-timer');
    const statusElement = window.CanvasPreviewManager.getCardElement('autosave', '.autosave-status');

    window.CanvasPreviewManager.updateElement(autosaveElement, 'textContent', `${autosave}min`);
    window.CanvasPreviewManager.updateElement(statusElement, 'textContent', enabled ? 'Activé' : 'Désactivé');
};

/**
 * Met à jour la prévisualisation de la carte debug
 */
/**
 * Met à jour la prévisualisation de la carte debug
 */
window.updateDebugCardPreview = function() {
    const values = window.CanvasPreviewManager.getCardValues('debug');
    const { debug_mode: debugEnabled } = values;

    const statusIndicator = window.CanvasPreviewManager.getCardElement('debug', '.status-indicator');
    if (statusIndicator) {
        const statusText = debugEnabled ? 'Debug Activé' : 'Debug Désactivé';
        statusIndicator.textContent = statusText;
        statusIndicator.className = `status-indicator ${debugEnabled ? 'enabled' : 'disabled'}`;
    }
};



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
 */
window.updateApparenceCardPreview = function() {
    window.PDFBuilderLogger.info('updateApparenceCardPreview called');
    try {
        const values = window.CanvasPreviewManager.getCardValues('apparence');
        window.PDFBuilderLogger.info('updateApparenceCardPreview - values:', values);
        const { canvas_background_color: bgColor, border_color: borderColor, border_width: borderWidth } = values;

        // Mettre à jour les previews de couleur
        const bgPreview = window.CanvasPreviewManager.getCardElement('apparence', '.color-preview.bg');
        const borderPreview = window.CanvasPreviewManager.getCardElement('apparence', '.color-preview.border');

        window.CanvasPreviewManager.updateElement(bgPreview, 'style.backgroundColor', bgColor);
        window.CanvasPreviewManager.updateElement(borderPreview, 'style.border', `${borderWidth}px solid ${borderColor}`);
        
        window.PDFBuilderLogger.info('updateApparenceCardPreview completed successfully');
    } catch (error) {
        window.PDFBuilderLogger.error('Error in updateApparenceCardPreview:', error);
        throw error; // Re-throw to be caught by updatePreviews
    }
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

    // Mettre à jour les icônes de mode
    modeIcons.forEach(icon => {
        icon.classList.remove('active');
        const isActive = (selectionMode === 'rectangle' && icon.title === 'Rectangle') ||
                        (selectionMode === 'lasso' && icon.title === 'Lasso') ||
                        (selectionMode === 'click' && icon.title === 'Clic');
        if (isActive) icon.classList.add('active');
    });

    // Mettre à jour l'indicateur de statut
    if (statusIndicator) {
        const isEnabled = keyboardShortcuts;
        statusIndicator.textContent = isEnabled ? 'Raccourcis activés' : 'Raccourcis désactivés';
        statusIndicator.className = `status-indicator ${isEnabled ? 'enabled' : 'disabled'}`;
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
    PDFBuilderLogger.info('Initialisation automatique - URL actuelle:', window.location.href);
    PDFBuilderLogger.debug('Éléments DOM détectés:', {
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
        apparence: {
            inputs: ['canvas_bg_color', 'canvas_border_color', 'canvas_border_width'],
            settings: ['canvas_background_color', 'border_color', 'border_width'],
            defaults: ['#ffffff', '#cccccc', 1],
            updateFunction: 'updateApparenceCardPreview'
        },
        grille: {
            inputs: ['canvas_grid_enabled', 'canvas_snap_to_grid', 'canvas_show_guides'],
            settings: ['show_grid', 'snap_to_grid', 'show_guides'],
            defaults: [true, true, true],
            updateFunction: 'updateGrilleCardPreview'
        },
        zoom: {
            inputs: ['canvas_zoom'],
            settings: ['default_canvas_zoom'],
            defaults: [100],
            updateFunction: 'updateZoomCardPreview'
        },
        interactions: {
            inputs: ['canvas_selection_mode', 'canvas_keyboard_shortcuts'],
            settings: ['selection_mode', 'keyboard_shortcuts'],
            defaults: ['click', true],
            updateFunction: 'updateInteractionsCardPreview'
        },
        export: {
            inputs: ['canvas_export_quality'],
            settings: ['export_quality'],
            defaults: [90],
            updateFunction: 'updateExportCardPreview'
        },
        performance: {
            inputs: ['canvas_fps_target', 'canvas_memory_limit_js', 'canvas_lazy_loading_editor'],
            settings: ['fps_target', 'memory_limit_js', 'lazy_loading_editor'],
            defaults: [60, 128, true],
            updateFunction: 'updatePerformanceCardPreview'
        },
        autosave: {
            inputs: ['canvas_autosave_interval'],
            settings: ['autosave_interval'],
            defaults: [5],
            updateFunction: 'updateAutosaveCardPreview'
        },
        debug: {
            inputs: ['canvas_debug_enabled'],
            settings: ['debug_mode'],
            defaults: [false],
            updateFunction: 'updateDebugCardPreview'
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
            PDFBuilderLogger.debug(`Element not found for ${property}`);
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
            PDFBuilderLogger.error(`Error updating element ${property}:`, e);
            return false;
        }
    },

    /**
     * Met à jour toutes les previews ou une catégorie spécifique
     */
    updatePreviews: function(category = 'all') {
        window.PDFBuilderLogger.info('CanvasPreviewManager.updatePreviews called with category:', category);
        PDFBuilderLogger.debug('CanvasPreviewManager.updatePreviews called with category:', category);

        Object.keys(this.cardConfigs).forEach(cardCategory => {
            if (category === 'all' || category === cardCategory) {
                const config = this.cardConfigs[cardCategory];
                if (typeof window[config.updateFunction] === 'function') {
                    try {
                        window.PDFBuilderLogger.info('Calling update function:', config.updateFunction, 'for category:', cardCategory);
                        PDFBuilderLogger.debug('Calling update function:', config.updateFunction);
                        window[config.updateFunction]();
                        window.PDFBuilderLogger.info('Update function completed successfully:', config.updateFunction);
                        PDFBuilderLogger.debug('Update function completed:', config.updateFunction);
                    } catch (error) {
                        window.PDFBuilderLogger.error('Error in update function:', config.updateFunction, 'Error:', error);
                        PDFBuilderLogger.error('Error in update function:', config.updateFunction, error);
                        // Continue with other functions instead of stopping
                    }
                } else {
                    window.PDFBuilderLogger.warn('Update function not found:', config.updateFunction);
                    PDFBuilderLogger.warn('Update function not found:', config.updateFunction);
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
    window.PDFBuilderLogger.info('updateDimensionsCardPreview called');
    try {
        const values = window.CanvasPreviewManager.getCardValues('dimensions');
        window.PDFBuilderLogger.info('updateDimensionsCardPreview - values:', values);
        const { default_canvas_format: format, default_canvas_dpi: dpi, default_canvas_orientation: orientation } = values;

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
        const widthElement = window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-width');
        const heightElement = window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-height');
        const dpiElement = window.CanvasPreviewManager.getCardElement('dimensions', '#card-canvas-dpi');

        window.CanvasPreviewManager.updateElement(widthElement, 'textContent', widthPx);
        window.CanvasPreviewManager.updateElement(heightElement, 'textContent', heightPx);
        window.CanvasPreviewManager.updateElement(dpiElement, 'textContent', `${dpi} DPI - ${format} (${widthMm}×${heightMm}mm)`);
        
        window.PDFBuilderLogger.info('updateDimensionsCardPreview completed successfully');
    } catch (error) {
        window.PDFBuilderLogger.error('Error in updateDimensionsCardPreview:', error);
        throw error; // Re-throw to be caught by updatePreviews
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








/* eslint-disable no-undef */
/**
 * Exemple d'intégration de l'API Preview 1.4 dans l'interface
 * À intégrer dans votre éditeur ou metabox WooCommerce
 */

// Fonctions de debug conditionnel - ACTIVÉES pour le système d'aperçu
function isDebugEnabled() {
    // Debug activé si explicitement forcé ou si activé dans les paramètres
    return window.location.search.includes('debug=force') ||
           (typeof window.pdfBuilderDebugSettings !== 'undefined' && window.pdfBuilderDebugSettings?.javascript);
}

function debugLog(...args) {
    // Debug logging disabled for production
}

function debugError(...args) {
    // TEMP: Always log for debugging
    console.error(...args);
}

function debugWarn(...args) {
    // TEMP: Always log for debugging
    console.warn(...args);
}

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
        debugLog('🎨 Initialisation intégration éditeur...');
        this.createPreviewButton();
        this.bindEvents();
        debugLog('✅ Intégration éditeur initialisée');
    }

    createPreviewButton() {
        debugLog('🔘 Création bouton aperçu éditeur...');

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
            debugLog('✅ Bouton aperçu ajouté à la toolbar');
        } else {
            // Fallback: l'ajouter au body avec position fixe
            this.previewBtn.style.position = 'fixed';
            this.previewBtn.style.top = '10px';
            this.previewBtn.style.right = '10px';
            this.previewBtn.style.zIndex = '1000';
            document.body.appendChild(this.previewBtn);
            debugLog('⚠️ Toolbar non trouvée, bouton ajouté en position fixe');
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
        debugLog('🚀 Démarrage génération aperçu éditeur...');

        try {
            // Récupérer les données du template depuis l'éditeur
            const templateData = this.getTemplateData();

            if (!templateData) {
                debugWarn('⚠️ Aucune donnée de template trouvée');
                alert('Aucune donnée de template trouvée. Veuillez créer un template d\'abord.');
                return;
            }

            debugLog('📄 Données template récupérées:', templateData);

            // Générer l'aperçu
            const result = await window.generateEditorPreview(templateData, {
                quality: 150,
                format: 'png'
            });

            if (result) {
                debugLog('✅ Aperçu éditeur généré avec succès');
            }

        } catch (error) {
            debugError('❌ Erreur génération aperçu éditeur:', error);
            alert('Erreur lors de la génération de l\'aperçu. Vérifiez la console pour plus de détails.');
        }
    }

    getTemplateData() {
        debugLog('🔍 Recherche données template...');

        // Adapter selon votre structure de données d'éditeur
        if (this.canvasEditor && typeof this.canvasEditor.getTemplateData === 'function') {
            const data = this.canvasEditor.getTemplateData();
            debugLog('✅ Données récupérées depuis canvasEditor');
            return data;
        }

        // Fallback: chercher dans le localStorage ou les variables globales
        if (window.pdfEditorTemplate) {
            debugLog('✅ Données récupérées depuis window.pdfEditorTemplate');
            return window.pdfEditorTemplate;
        }

        if (localStorage.getItem('pdf-builder-template')) {
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
        debugLog('🛒 Initialisation intégration metabox...');
        this.createPreviewButtons();
        this.bindEvents();
        debugLog('✅ Intégration metabox initialisée');
    }

    createPreviewButtons() {
        debugLog('🔘 Création boutons aperçu metabox...');

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
        debugLog('🚀 Démarrage génération aperçu commande...');

        try {
            if (!this.orderId) {
                debugError('❌ ID de commande non trouvé');
                alert('ID de commande non trouvé.');
                return;
            }

            debugLog('📦 ID commande:', this.orderId);

            // Récupérer les données du template depuis la metabox
            const templateData = this.getTemplateData();

            if (!templateData) {
                debugWarn('⚠️ Aucune donnée de template trouvée');
                alert('Aucune donnée de template trouvée. Veuillez sélectionner un template.');
                return;
            }

            debugLog('📄 Données template récupérées:', templateData);

            // Générer l'aperçu
            const result = await window.generateOrderPreview(templateData, this.orderId, {
                quality: 150,
                format: 'png'
            });

            if (result) {
                debugLog('✅ Aperçu commande généré avec succès');
            }

        } catch (error) {
            debugError('❌ Erreur génération aperçu commande:', error);
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

        debugWarn('⚠️ ID de commande non trouvé automatiquement');
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
            } catch (e) {
                debugWarn('Données template mal formatées:', e);
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

document.addEventListener('DOMContentLoaded', function() {
    // Initialiser l'intégration éditeur si on est dans l'éditeur
    if (document.querySelector('#pdf-editor-canvas') ||
        document.querySelector('.pdf-canvas-editor') ||
        window.location.href.includes('pdf-builder-editor')) {

        debugLog('🎨 Initialisation intégration éditeur...');
        window.pdfEditorPreview = new PDFEditorPreviewIntegration(window.pdfCanvasEditor);
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
                debugLog('🛒 Initialisation intégration metabox...');
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
    debugLog('⚡ Génération aperçu rapide démarrée...');

    try {
        // Détection automatique du contexte
        const isEditor = document.querySelector('#pdf-editor-canvas') ||
                        document.querySelector('.pdf-canvas-editor') ||
                        window.location.href.includes('pdf-builder-editor');

        const isMetabox = document.querySelector('.woocommerce-order-data') ||
                         document.querySelector('#woocommerce-order-data') ||
                         (window.location.href.includes('post.php') &&
                          window.location.href.includes('action=edit'));

        debugLog('🔍 Contexte détecté:', { isEditor, isMetabox });

        if (isEditor) {
            debugLog('🎨 Mode éditeur détecté');
            const data = templateData || window.pdfEditorPreview?.getTemplateData();
            return await window.generateEditorPreview(data);
        }

        if (isMetabox) {
            debugLog('🛒 Mode metabox détecté');
            const data = templateData || window.pdfMetaboxPreview?.getTemplateData();
            const id = orderId || window.pdfMetaboxPreview?.getOrderId();
            return await window.generateOrderPreview(data, id);
        }

        debugWarn('⚠️ Contexte non reconnu pour l\'aperçu');
        return null;

    } catch (error) {
        debugError('❌ Erreur génération aperçu rapide:', error);
        return null;
    }
};

debugLog('🚀 Intégrations API Preview 1.4 chargées !');
debugLog('💡 Raccourcis:');
debugLog('   - Ctrl+P (Cmd+P) : Aperçu rapide');
debugLog('   - generateQuickPreview() : Détection automatique du contexte');

// ==========================================
// CANVAS PREVIEW MANAGER POUR SETTINGS
// ==========================================

/**
 * Gestionnaire centralisé des previews canvas pour la page des paramètres
 */
window.CanvasPreviewManager = {
    /**
     * Met à jour les previews pour une catégorie donnée
     */
    updatePreviews: function(category) {
        debugLog('🔄 Mise à jour previews pour catégorie:', category);

        try {
            switch (category) {
                case 'dimensions':
                    if (typeof updateDimensionsCardPreview === 'function') {
                        updateDimensionsCardPreview();
                    }
                    break;
                case 'apparence':
                    if (typeof updateApparenceCardPreview === 'function') {
                        updateApparenceCardPreview();
                    }
                    break;
                case 'performance':
                    if (typeof updatePerformanceCardPreview === 'function') {
                        updatePerformanceCardPreview();
                    }
                    break;
                case 'autosave':
                    if (typeof updateAutosaveCardPreview === 'function') {
                        updateAutosaveCardPreview();
                    }
                    break;
                case 'zoom':
                    if (typeof updateZoomCardPreview === 'function') {
                        updateZoomCardPreview();
                    }
                    break;
                case 'grille':
                    if (typeof updateGrilleCardPreview === 'function') {
                        updateGrilleCardPreview();
                    }
                    break;
                case 'interactions':
                    if (typeof updateInteractionsCardPreview === 'function') {
                        updateInteractionsCardPreview();
                    }
                    break;
                case 'export':
                    if (typeof updateExportCardPreview === 'function') {
                        updateExportCardPreview();
                    }
                    break;
                case 'all':
                    // Mettre à jour toutes les previews
                    this.updatePreviews('dimensions');
                    this.updatePreviews('apparence');
                    this.updatePreviews('performance');
                    this.updatePreviews('autosave');
                    this.updatePreviews('zoom');
                    this.updatePreviews('grille');
                    this.updatePreviews('interactions');
                    this.updatePreviews('export');
                    break;
                default:
                    debugWarn('⚠️ Catégorie inconnue:', category);
            }
        } catch (error) {
            debugError('❌ Erreur mise à jour preview:', error);
        }
    },

    /**
     * Récupère les valeurs actuelles d'une carte
     */
    getCardValues: function(category) {
        debugLog('📊 Récupération valeurs pour carte:', category);

        try {
            const settings = window.pdfBuilderCanvasSettings || {};

            switch (category) {
                case 'performance':
                    return {
                        fps_target: settings.fps_target || 60,
                        memory_limit_js: settings.memory_limit_js || 128,
                        memory_limit_php: settings.memory_limit_php || 256,
                        lazy_loading_editor: settings.lazy_loading_editor || false,
                        lazy_loading_plugin: settings.lazy_loading_plugin || false
                    };
                case 'apparence':
                    return {
                        canvas_bg_color: settings.canvas_background_color || '#ffffff',
                        canvas_border_color: settings.border_color || '#cccccc',
                        canvas_border_width: settings.border_width || 1,
                        canvas_shadow_enabled: settings.shadow_enabled || false,
                        canvas_container_bg_color: settings.container_background_color || '#f8f9fa'
                    };
                case 'grille':
                    return {
                        grid_enabled: settings.show_grid || false,
                        grid_size: settings.grid_size || 20,
                        snap_to_grid: settings.snap_to_grid || false
                    };
                case 'interactions':
                    return {
                        drag_enabled: settings.drag_enabled !== false,
                        resize_enabled: settings.resize_enabled !== false,
                        rotate_enabled: settings.rotate_enabled !== false,
                        multi_select: settings.multi_select !== false,
                        selection_mode: settings.selection_mode || 'rectangle',
                        keyboard_shortcuts: settings.keyboard_shortcuts !== false
                    };
                case 'export':
                    return {
                        canvas_export_format: settings.export_format || 'pdf',
                        canvas_export_quality: settings.export_quality || 90,
                        canvas_export_transparent: settings.export_transparent || false
                    };
                case 'zoom':
                    return {
                        canvas_zoom_min: settings.min_zoom || 10,
                        canvas_zoom_max: settings.max_zoom || 500,
                        canvas_zoom_default: settings.default_zoom || 100,
                        canvas_zoom_step: settings.zoom_step || 25
                    };
                case 'autosave':
                    return {
                        canvas_autosave_enabled: settings.autosave_enabled !== false,
                        canvas_autosave_interval: settings.autosave_interval || 5,
                        canvas_history_max: settings.versions_limit || 10
                    };
                default:
                    debugWarn('⚠️ Catégorie inconnue pour getCardValues:', category);
                    return {};
            }
        } catch (error) {
            debugError('❌ Erreur récupération valeurs carte:', error);
            return {};
        }
    },

    /**
     * Récupère un élément DOM d'une carte
     */
    getCardElement: function(category, selector) {
        debugLog('🔍 Recherche élément pour carte:', category, 'sélecteur:', selector);

        try {
            const card = document.querySelector(`.canvas-card[data-category="${category}"]`);
            if (!card) {
                debugWarn('⚠️ Carte non trouvée:', category);
                return null;
            }

            return card.querySelector(selector);
        } catch (error) {
            debugError('❌ Erreur recherche élément:', error);
            return null;
        }
    },

    /**
     * Met à jour une propriété d'un élément
     */
    updateElement: function(element, property, value) {
        if (!element) {
            debugWarn('⚠️ Élément null passé à updateElement');
            return;
        }

        try {
            debugLog('🔧 Mise à jour élément:', property, '=', value);

            // Gérer les propriétés imbriquées (ex: style.backgroundColor)
            if (property.includes('.')) {
                const parts = property.split('.');
                let obj = element;
                for (let i = 0; i < parts.length - 1; i++) {
                    obj = obj[parts[i]];
                    if (!obj) {
                        debugWarn('⚠️ Propriété parent non trouvée:', parts.slice(0, i+1).join('.'));
                        return;
                    }
                }
                obj[parts[parts.length - 1]] = value;
            } else {
                element[property] = value;
            }
        } catch (error) {
            debugError('❌ Erreur mise à jour élément:', error);
        }
    },

    /**
     * Met à jour la prévisualisation de la carte export
     */
    updateExportCardPreview: function() {
        try {
            const values = this.getCardValues('export');
            const { export_quality: exportQuality } = values;

            const qualityFill = this.getCardElement('export', '.quality-fill');
            const qualityText = this.getCardElement('export', '.quality-text');

            this.updateElement(qualityFill, 'style.width', `${exportQuality}%`);
            this.updateElement(qualityText, 'textContent', `${exportQuality}%`);

            debugLog('✅ Export preview updated:', exportQuality);
        } catch (error) {
            debugError('❌ Error updating export preview:', error);
        }
    },

    /**
     * Met à jour la prévisualisation de la carte grille
     */
    updateGrilleCardPreview: function() {
        try {
            const values = this.getCardValues('grille');
            const { show_grid: gridEnabled, snap_to_grid: snapToGrid, show_guides: showGuides } = values;

            const gridContainer = this.getCardElement('grille', '.grid-preview-container');
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

            debugLog('✅ Grille preview updated:', { gridEnabled, snapToGrid, showGuides });
        } catch (error) {
            debugError('❌ Error updating grille preview:', error);
        }
    },

    /**
     * Initialise les mises à jour en temps réel pour une catégorie
     */
    initializeRealTimeUpdates: function(modal) {
        if (!modal) return;

        debugLog('⚡ Initialisation mises à jour temps réel pour modal:', modal.getAttribute('data-category'));

        // Les mises à jour temps réel sont gérées dans settings-main.php
        // Cette méthode est appelée pour compatibilité
    }
};

debugLog('✅ CanvasPreviewManager initialisé');

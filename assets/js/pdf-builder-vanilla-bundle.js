// Point d'entrée principal pour le bundle Vanilla JS
// Ce fichier importe tous les modules ES6 et les expose globalement

console.log('🔧 Bundle PDF Builder Vanilla JS chargé et en cours d\'exécution...');

import VanillaCanvas from './pdf-canvas-vanilla.js';
import CanvasRenderer from './pdf-canvas-renderer.js';
import CanvasEvents from './pdf-canvas-events.js';
import CanvasSelection from './pdf-canvas-selection.js';
import CanvasProperties from './pdf-canvas-properties.js';
import CanvasLayers from './pdf-canvas-layers.js';
import CanvasExport from './pdf-canvas-export.js';
import { WooCommerceElementsManager, wooCommerceElementsManager } from './pdf-canvas-woocommerce.js';
import { ElementCustomizationService, elementCustomizationService } from './pdf-canvas-customization.js';
import CanvasOptimizer from './pdf-canvas-optimizer.js';
import CanvasTests from './pdf-canvas-tests.js';
import { ELEMENT_LIBRARY, getAllElements, getElementsByCategory, getElementByType, getAllElementsFlat, searchElements } from './pdf-canvas-element-library.js';

console.log('📚 Tous les modules ES6 importés avec succès');

// Créer un objet global unique qui contient tous les modules
const PDFBuilderPro = {
    VanillaCanvas,
    CanvasRenderer,
    CanvasEvents,
    CanvasSelection,
    CanvasProperties,
    CanvasLayers,
    CanvasExport,
    WooCommerceElementsManager,
    wooCommerceElementsManager,
    ElementCustomizationService,
    elementCustomizationService,
    CanvasOptimizer,
    CanvasTests,
    ELEMENT_LIBRARY,
    getAllElements,
    getElementsByCategory,
    getElementByType,
    getAllElementsFlat,
    searchElements,

    // Alias pour la compatibilité avec le template-editor.php
    PDFCanvasVanilla: VanillaCanvas,
    ElementLibrary: ELEMENT_LIBRARY, // Bibliothèque d'éléments complète
    ToolbarManager: CanvasEvents, // Utiliser CanvasEvents comme ToolbarManager
    PropertiesPanel: CanvasProperties, // Alias pour PropertiesPanel

    // Méthode d'initialisation principale
    init(options = {}) {
        console.log('🚀 Initialisation PDF Builder Vanilla JS complet');

        try {
            // Initialiser le canvas principal
            if (VanillaCanvas && typeof VanillaCanvas.init === 'function') {
                VanillaCanvas.init(options);
            }

            // Initialiser les autres modules si nécessaire
            if (CanvasEvents && typeof CanvasEvents.init === 'function') {
                CanvasEvents.init();
            }

            console.log('✅ PDF Builder Vanilla JS initialisé avec succès');
            return true;
        } catch (error) {
            console.error('❌ Erreur lors de l\'initialisation PDF Builder Vanilla JS:', error);
            return false;
        }
    }
};

// Exposer l'objet global principal
console.log('🌍 Exposition de PDFBuilderPro globalement...');
window.PDFBuilderPro = PDFBuilderPro;

// Pour la compatibilité, exposer aussi les modules individuellement
window.VanillaCanvas = VanillaCanvas;
window.CanvasRenderer = CanvasRenderer;
window.CanvasEvents = CanvasEvents;
window.CanvasSelection = CanvasSelection;
window.CanvasProperties = CanvasProperties;
window.CanvasLayers = CanvasLayers;
window.CanvasExport = CanvasExport;
window.WooCommerceElementsManager = WooCommerceElementsManager;
window.wooCommerceElementsManager = wooCommerceElementsManager;
window.ElementCustomizationService = ElementCustomizationService;
window.elementCustomizationService = elementCustomizationService;
window.CanvasOptimizer = CanvasOptimizer;
window.CanvasTests = CanvasTests;
window.ELEMENT_LIBRARY = ELEMENT_LIBRARY;
window.getAllElements = getAllElements;
window.getElementsByCategory = getElementsByCategory;
window.getElementByType = getElementByType;
window.getAllElementsFlat = getAllElementsFlat;
window.searchElements = searchElements;

// Alias pour la compatibilité avec template-editor.php
window.PDFCanvasVanilla = VanillaCanvas;
window.ElementLibrary = ELEMENT_LIBRARY;
window.ToolbarManager = CanvasEvents;
window.PropertiesPanel = CanvasProperties;

console.log('✅ Modules exposés globalement');

// Export par défaut pour les modules ES6
export default PDFBuilderPro;
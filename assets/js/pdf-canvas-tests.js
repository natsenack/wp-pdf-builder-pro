/**
 * Tests d'intégration pour le système PDF Builder Vanilla JS
 * Validation des fonctionnalités après migration depuis React
 */

import { PDFCanvasVanilla } from './pdf-canvas-vanilla.js';
import { ELEMENT_PROPERTY_RESTRICTIONS } from './pdf-canvas-elements.js';
import { WooCommerceElementsManager } from './pdf-canvas-woocommerce.js';
import { ElementCustomizationService } from './pdf-canvas-customization.js';
import { PDFCanvasRenderer } from './pdf-canvas-renderer.js';
import { PDFCanvasEventManager } from './pdf-canvas-events.js';
import { PDFCanvasRenderUtils } from './pdf-canvas-render-utils.js';
import { PDFCanvasSelectionManager } from './pdf-canvas-selection.js';
import { PDFCanvasPropertiesManager } from './pdf-canvas-properties.js';
import { PDFCanvasLayersManager } from './pdf-canvas-layers.js';
import { PDFCanvasExportManager } from './pdf-canvas-export.js';

class PDFCanvasIntegrationTests {
    constructor() {
        this.results = {
            passed: 0,
            failed: 0,
            tests: []
        };
        this.canvasInstance = null;
    }

    /**
     * Exécute tous les tests
     */
    async runAllTests() {
        console.log('🚀 Démarrage des tests d\'intégration PDF Builder Vanilla JS...');

        try {
            // Tests des modules individuels
            await this.testElementRestrictions();
            await this.testWooCommerceManager();
            await this.testCustomizationService();
            await this.testRenderUtils();
            await this.testPropertiesManager();
            await this.testLayersManager();
            await this.testExportManager();

            // Tests d'intégration
            await this.testCanvasInitialization();
            await this.testElementCreation();
            await this.testSelectionSystem();
            await this.testEventHandling();

            this.printResults();

        } catch (error) {
            console.error('❌ Erreur lors des tests:', error);
            this.logTest('Test Suite', false, error.message);
            this.printResults();
        }
    }

    /**
     * Test des restrictions d'éléments
     */
    async testElementRestrictions() {
        console.log('Testing element restrictions...');

        try {
            // Test des types d'éléments supportés
            const supportedTypes = Object.keys(ELEMENT_PROPERTY_RESTRICTIONS);
            this.assert(supportedTypes.length > 0, 'Types d\'éléments définis');

            // Test des propriétés par défaut
            for (const type of supportedTypes) {
                const defaults = ELEMENT_PROPERTY_RESTRICTIONS[type] && ELEMENT_PROPERTY_RESTRICTIONS[type].defaults;
                this.assert(defaults, `Propriétés par défaut pour ${type}`);
            }

            this.logTest('Element Restrictions', true);
        } catch (error) {
            this.logTest('Element Restrictions', false, error.message);
        }
    }

    /**
     * Test du gestionnaire WooCommerce
     */
    async testWooCommerceManager() {
        console.log('Testing WooCommerce manager...');

        try {
            const manager = new WooCommerceElementsManager();

            // Test des données de test
            const testData = manager.getTestData();
            this.assert(Array.isArray(testData), 'Données de test disponibles');

            // Test du chargement des données
            const loaded = await manager.loadWooCommerceData();
            this.assert(typeof loaded === 'boolean', 'Chargement des données WooCommerce');

            this.logTest('WooCommerce Manager', true);
        } catch (error) {
            this.logTest('WooCommerce Manager', false, error.message);
        }
    }

    /**
     * Test du service de personnalisation
     */
    async testCustomizationService() {
        console.log('Testing customization service...');

        try {
            const service = new ElementCustomizationService();

            // Test de validation de propriété
            const isValid = service.validateProperty('text', 'fontSize', 12);
            this.assert(isValid, 'Validation de propriété basique');

            // Test des propriétés par défaut
            const defaults = service.getDefaultProperties('rectangle');
            this.assert(defaults && typeof defaults === 'object', 'Propriétés par défaut');

            this.logTest('Customization Service', true);
        } catch (error) {
            this.logTest('Customization Service', false, error.message);
        }
    }

    /**
     * Test des utilitaires de rendu
     */
    async testRenderUtils() {
        console.log('Testing render utils...');

        try {
            // Créer un canvas temporaire pour les tests
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            canvas.width = 200;
            canvas.height = 200;

            // Test du rendu de formes
            PDFCanvasRenderUtils.drawShape(ctx, 'rectangle', {
                x: 10, y: 10, width: 50, height: 50,
                fillColor: '#ff0000', strokeColor: '#000000'
            });

            // Test du rendu de texte
            PDFCanvasRenderUtils.drawMultilineText(ctx, 'Test Text', {
                x: 10, y: 80, maxWidth: 100, fontSize: 12
            });

            this.logTest('Render Utils', true);
        } catch (error) {
            this.logTest('Render Utils', false, error.message);
        }
    }

    /**
     * Test du gestionnaire de propriétés
     */
    async testPropertiesManager() {
        console.log('Testing properties manager...');

        try {
            const manager = new PDFCanvasPropertiesManager(null);

            // Test de définition de propriété
            const elementId = 'test-element';
            manager.setProperty(elementId, 'width', 100);
            const value = manager.getProperty(elementId, 'width');
            this.assert(value === 100, 'Définition et récupération de propriété');

            // Test de validation
            const isValid = manager.validatePropertyByType('width', 50);
            this.assert(isValid, 'Validation de propriété par type');

            this.logTest('Properties Manager', true);
        } catch (error) {
            this.logTest('Properties Manager', false, error.message);
        }
    }

    /**
     * Test du gestionnaire de calques
     */
    async testLayersManager() {
        console.log('Testing layers manager...');

        try {
            const manager = new PDFCanvasLayersManager(null);

            // Test de création de calque
            const layer = manager.createLayer('test-layer', { name: 'Test Layer' });
            this.assert(layer && layer.id === 'test-layer', 'Création de calque');

            // Test de récupération de calque
            const retrieved = manager.getLayer('test-layer');
            this.assert(retrieved === layer, 'Récupération de calque');

            this.logTest('Layers Manager', true);
        } catch (error) {
            this.logTest('Layers Manager', false, error.message);
        }
    }

    /**
     * Test du gestionnaire d'export
     */
    async testExportManager() {
        console.log('Testing export manager...');

        try {
            const manager = new PDFCanvasExportManager(null);

            // Test des formats supportés
            const formats = manager.getSupportedFormats();
            this.assert(Array.isArray(formats) && formats.includes('pdf'), 'Formats d\'export supportés');

            // Test de la configuration
            manager.configure({ format: 'a4' });
            const stats = manager.getExportStats();
            this.assert(stats.config.format === 'a4', 'Configuration d\'export');

            this.logTest('Export Manager', true);
        } catch (error) {
            this.logTest('Export Manager', false, error.message);
        }
    }

    /**
     * Test d'initialisation du canvas
     */
    async testCanvasInitialization() {
        console.log('Testing canvas initialization...');

        try {
            // Créer un conteneur de test
            const container = document.createElement('div');
            container.id = 'test-canvas-container';
            container.style.width = '800px';
            container.style.height = '600px';
            document.body.appendChild(container);

            // Initialiser le canvas
            this.canvasInstance = new PDFCanvasVanilla('test-canvas-container', {
                width: 800,
                height: 600
            });

            await this.canvasInstance.init();

            // Vérifier que le canvas a été créé
            this.assert(this.canvasInstance.canvas, 'Canvas créé');
            this.assert(this.canvasInstance.ctx, 'Contexte 2D disponible');
            this.assert(this.canvasInstance.isInitialized, 'Canvas initialisé');

            // Nettoyer
            document.body.removeChild(container);

            this.logTest('Canvas Initialization', true);
        } catch (error) {
            this.logTest('Canvas Initialization', false, error.message);
        }
    }

    /**
     * Test de création d'éléments
     */
    async testElementCreation() {
        console.log('Testing element creation...');

        try {
            // Utiliser l'instance de test créée précédemment
            if (!this.canvasInstance) {
                throw new Error('Canvas instance not available');
            }

            // Créer différents types d'éléments
            const textElement = this.canvasInstance.addElement('text', {
                x: 100, y: 100, text: 'Test Text'
            });
            this.assert(textElement, 'Élément texte créé');

            const rectElement = this.canvasInstance.addElement('rectangle', {
                x: 200, y: 200, width: 100, height: 50
            });
            this.assert(rectElement, 'Élément rectangle créé');

            // Vérifier que les éléments sont dans la collection
            this.assert(this.canvasInstance.elements.has(textElement), 'Élément texte dans la collection');
            this.assert(this.canvasInstance.elements.has(rectElement), 'Élément rectangle dans la collection');

            this.logTest('Element Creation', true);
        } catch (error) {
            this.logTest('Element Creation', false, error.message);
        }
    }

    /**
     * Test du système de sélection
     */
    async testSelectionSystem() {
        console.log('Testing selection system...');

        try {
            if (!this.canvasInstance) {
                throw new Error('Canvas instance not available');
            }

            // Créer quelques éléments pour les tests
            const elem1 = this.canvasInstance.addElement('rectangle', { x: 10, y: 10, width: 50, height: 50 });
            const elem2 = this.canvasInstance.addElement('rectangle', { x: 70, y: 10, width: 50, height: 50 });

            // Test de sélection d'un élément
            this.canvasInstance.selectionManager.selectElement(elem1);
            this.assert(this.canvasInstance.selectionManager.isSelected(elem1), 'Élément sélectionné');

            // Test de sélection multiple
            this.canvasInstance.selectionManager.selectElements([elem1, elem2]);
            this.assert(this.canvasInstance.selectionManager.getSelectedElements().length === 2, 'Sélection multiple');

            this.logTest('Selection System', true);
        } catch (error) {
            this.logTest('Selection System', false, error.message);
        }
    }

    /**
     * Test de la gestion d'événements
     */
    async testEventHandling() {
        console.log('Testing event handling...');

        try {
            if (!this.canvasInstance) {
                throw new Error('Canvas instance not available');
            }

            // Simuler un événement de clic
            const mockEvent = {
                type: 'mousedown',
                clientX: 100,
                clientY: 100,
                preventDefault: () => {},
                stopPropagation: () => {}
            };

            // Le gestionnaire d'événements devrait traiter l'événement sans erreur
            this.canvasInstance.eventManager.handleMouseDown(mockEvent);

            this.logTest('Event Handling', true);
        } catch (error) {
            this.logTest('Event Handling', false, error.message);
        }
    }

    /**
     * Assertion utilitaire
     */
    assert(condition, message) {
        if (!condition) {
            throw new Error(`Assertion failed: ${message}`);
        }
    }

    /**
     * Log un résultat de test
     */
    logTest(name, passed, error = null) {
        const result = {
            name,
            passed,
            error: error || null,
            timestamp: new Date().toISOString()
        };

        this.results.tests.push(result);

        if (passed) {
            this.results.passed++;
            console.log(`✅ ${name}: PASSED`);
        } else {
            this.results.failed++;
            console.log(`❌ ${name}: FAILED - ${error}`);
        }
    }

    /**
     * Affiche les résultats finaux
     */
    printResults() {
        console.log('\n' + '='.repeat(50));
        console.log('📊 RÉSULTATS DES TESTS D\'INTÉGRATION');
        console.log('='.repeat(50));

        console.log(`✅ Tests réussis: ${this.results.passed}`);
        console.log(`❌ Tests échoués: ${this.results.failed}`);
        console.log(`📈 Taux de réussite: ${((this.results.passed / (this.results.passed + this.results.failed)) * 100).toFixed(1)}%`);

        if (this.results.failed > 0) {
            console.log('\n❌ TESTS ÉCHOUÉS:');
            this.results.tests
                .filter(test => !test.passed)
                .forEach(test => {
                    console.log(`  - ${test.name}: ${test.error}`);
                });
        }

        console.log('\n' + '='.repeat(50));

        if (this.results.failed === 0) {
            console.log('🎉 TOUS LES TESTS SONT RÉUSSIS !');
        } else {
            console.log('⚠️  CERTAINS TESTS ONT ÉCHOUÉ - VÉRIFIEZ LES ERREURS CI-DESSUS');
        }
    }
}

// Fonction d'exécution des tests (pour utilisation dans le navigateur)
window.runPDFCanvasTests = async function() {
    const tester = new PDFCanvasIntegrationTests();
    await tester.runAllTests();
    return tester.results;
};

// Exécution automatique si en mode développement
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PDFCanvasIntegrationTests;
}

export default PDFCanvasIntegrationTests;
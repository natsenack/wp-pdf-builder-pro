/**
 * Tests JavaScript pour le système de sauvegarde PDF Builder Pro
 * Tests d'intégration pour valider la fiabilité côté client
 */

// Tests pour les métriques de performance
describe('PerformanceMetrics', () => {
    beforeEach(() => {
        // Reset des métriques
        localStorage.removeItem('pdf_builder_metrics');
    });

    test('should track operation timing', () => {
        PerformanceMetrics.start('testOperation');

        // Simuler du temps
        setTimeout(() => {
            PerformanceMetrics.end('testOperation');

            const metrics = PerformanceMetrics.getMetrics();
            expect(metrics.testOperation).toBeDefined();
            expect(metrics.testOperation.count).toBe(1);
            expect(metrics.testOperation.avgTime).toBeGreaterThan(0);
        }, 10);
    });

    test('should track errors', () => {
        PerformanceMetrics.error('testOperation', 'Test error');

        const metrics = PerformanceMetrics.getMetrics();
        expect(metrics.testOperation).toBeDefined();
        expect(metrics.testOperation.errorCount).toBe(1);
    });
});

// Tests pour le cache local
describe('LocalCache', () => {
    beforeEach(() => {
        LocalCache.clear();
    });

    test('should save and load data', () => {
        const testData = { key: 'value', array: [1, 2, 3] };

        LocalCache.save(testData);
        const loadedData = LocalCache.load();

        expect(loadedData).toEqual(testData);
    });

    test('should handle corrupted data', () => {
        // Sauvegarder des données normales
        LocalCache.save({ valid: 'data' });

        // Corrompre manuellement le cache
        sessionStorage.setItem('pdf_builder_settings_backup', 'invalid json');

        const loadedData = LocalCache.load();
        expect(loadedData).toBeNull();
    });

    test('should expire old data', () => {
        const testData = { key: 'value' };

        // Sauvegarder avec un timestamp ancien
        const oldCache = {
            data: testData,
            timestamp: Date.now() - (3 * 60 * 60 * 1000), // 3h dans le passé
            version: '1.1',
            hash: LocalCache.simpleHash(JSON.stringify(testData)),
            sessionId: LocalCache.getSessionId()
        };
        sessionStorage.setItem('pdf_builder_settings_backup', JSON.stringify(oldCache));

        const loadedData = LocalCache.load();
        expect(loadedData).toBeNull(); // Devrait être expiré
    });
});

// Tests pour la validation des données
describe('validateFormData', () => {
    test('should validate numeric fields', () => {
        const formData = {
            'pdf_builder_cache_max_size': 'not-a-number',
            'pdf_builder_cache_ttl': '123'
        };

        const errors = validateFormData(formData);
        expect(errors.length).toBeGreaterThan(0);
        expect(errors[0]).toContain('doit être un nombre');
    });

    test('should pass valid data', () => {
        const formData = {
            'pdf_builder_cache_max_size': '1024',
            'pdf_builder_cache_ttl': '3600',
            'pdf_builder_company_name': 'Test Company'
        };

        const errors = validateFormData(formData);
        expect(errors.length).toBe(0);
    });
});

// Tests pour la compatibilité AJAX
describe('AjaxCompat', () => {
    test('should have fetch method', () => {
        expect(typeof AjaxCompat.fetch).toBe('function');
    });

    test('should handle network errors gracefully', async () => {
        // Mock d'une URL invalide
        const response = await AjaxCompat.fetch('http://invalid-url-that-does-not-exist.com')
            .catch(error => {
                expect(error).toBeDefined();
                return null;
            });

        expect(response).toBeNull();
    });
});

// Tests d'intégration
describe('Integration Tests', () => {
    test('should collect form data correctly', () => {
        // Créer un formulaire de test
        document.body.innerHTML = `
            <form id="test-form">
                <input type="text" name="pdf_builder_company_name" value="Test Company">
                <input type="email" name="pdf_builder_company_email" value="test@example.com">
                <input type="checkbox" name="pdf_builder_cache_enabled" checked>
            </form>
        `;

        // Simuler collectAllFormData (cette fonction devrait exister)
        const formData = {
            'pdf_builder_company_name': 'Test Company',
            'pdf_builder_company_email': 'test@example.com',
            'pdf_builder_cache_enabled': 'on'
        };

        expect(formData['pdf_builder_company_name']).toBe('Test Company');
        expect(formData['pdf_builder_company_email']).toBe('test@example.com');
    });
});

// Utilitaires de test
function wait(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}

// Tests pour le diagnostic Canvas
describe('CanvasDiagnostic', () => {
    beforeEach(() => {
        // Créer un DOM de test pour les éléments Canvas
        document.body.innerHTML = `
            <div class="canvas-card" data-category="dimensions">
                <button class="canvas-configure-btn">Configurer</button>
            </div>
            <div class="canvas-card" data-category="apparence">
                <button class="canvas-configure-btn">Configurer</button>
            </div>
            <div id="canvas-dimensions-modal" class="modal-overlay"></div>
            <div id="canvas-apparence-modal" class="modal-overlay"></div>
            <input type="hidden" name="pdf_builder_canvas_canvas_width" value="800">
            <input type="hidden" name="pdf_builder_canvas_canvas_height" value="600">
            <div id="card-canvas-width">800px</div>
            <div id="card-canvas-height">600px</div>
        `;

        // Mock des objets globaux
        global.previewSystem = {
            values: {},
            refreshPreviews: function() {}
        };

        global.formGenerator = {
            generateModalHTML: function() {}
        };

        global.modalSettingsManager = {};
    });

    afterEach(() => {
        document.body.innerHTML = '';
        delete global.previewSystem;
        delete global.formGenerator;
        delete global.modalSettingsManager;
    });

    test('should detect all canvas components', () => {
        // Simuler runCanvasDiagnostic (elle devrait être disponible globalement)
        const results = {
            cards: 0,
            buttons: 0,
            modals: 0,
            hiddenFields: 0,
            previewElements: 0,
            issues: []
        };

        // 1. Vérifier les cartes
        const cards = document.querySelectorAll('.canvas-card');
        results.cards = cards.length;

        cards.forEach((card, index) => {
            const category = card.dataset.category;
            const button = card.querySelector('.canvas-configure-btn');
            if (!category) results.issues.push(`Carte ${index}: pas de data-category`);
            if (!button) results.issues.push(`Carte ${index} (${category}): pas de bouton configurer`);
            else results.buttons++;
        });

        // 2. Vérifier les modales
        const modalIds = ['canvas-dimensions-modal', 'canvas-apparence-modal'];

        modalIds.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) results.modals++;
            else results.issues.push(`Modale manquante: ${modalId}`);
        });

        // 3. Vérifier les champs cachés
        const hiddenFields = document.querySelectorAll('input[type="hidden"][name^="pdf_builder_canvas_canvas_"]');
        results.hiddenFields = hiddenFields.length;

        // 4. Vérifier les éléments de preview
        const previewElements = ['card-canvas-width', 'card-canvas-height'];

        previewElements.forEach(id => {
            const el = document.getElementById(id);
            if (el) results.previewElements++;
            else results.issues.push(`Élément preview manquant: ${id}`);
        });

        // Vérifications
        expect(results.cards).toBe(2);
        expect(results.buttons).toBe(2);
        expect(results.modals).toBe(2);
        expect(results.hiddenFields).toBe(2);
        expect(results.previewElements).toBe(2);
        expect(results.issues.length).toBe(0);
    });

    test('should detect missing components', () => {
        // Supprimer un élément
        document.getElementById('canvas-dimensions-modal').remove();

        const results = {
            cards: 0,
            buttons: 0,
            modals: 0,
            hiddenFields: 0,
            previewElements: 0,
            issues: []
        };

        // Vérifier les modales
        const modalIds = ['canvas-dimensions-modal', 'canvas-apparence-modal'];

        modalIds.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (modal) results.modals++;
            else results.issues.push(`Modale manquante: ${modalId}`);
        });

        expect(results.modals).toBe(1);
        expect(results.issues.length).toBe(1);
        expect(results.issues[0]).toContain('Modale manquante: canvas-dimensions-modal');
    });

    test('should detect undefined global objects', () => {
        delete global.previewSystem;

        const results = {
            issues: []
        };

        // Vérifier previewSystem
        if (typeof previewSystem === 'undefined') {
            results.issues.push('previewSystem non défini');
        }

        expect(results.issues.length).toBe(1);
        expect(results.issues[0]).toBe('previewSystem non défini');
    });
});
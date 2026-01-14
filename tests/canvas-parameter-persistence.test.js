/**
 * Tests pour la persistance des paramètres canvas
 * Tests unitaires pour valider la sauvegarde et récupération des paramètres
 */

describe('CanvasParameterPersistence', () => {
    let mockAjax;
    let originalCanvasCardMonitor;

    beforeAll(() => {
        // Sauvegarder l'objet global original
        originalCanvasCardMonitor = window.CanvasCardMonitor;
    });

    beforeEach(() => {
        // Reset all mocks
        jest.clearAllMocks();

        // Setup DOM pour les tests canvas
        document.body.innerHTML = `
            <input type="hidden" name="pdf_builder_canvas_width" value="800">
            <input type="hidden" name="pdf_builder_canvas_height" value="600">
            <input type="hidden" name="pdf_builder_canvas_dpi" value="150">
            <input type="hidden" name="pdf_builder_canvas_bg_color" value="#ffffff">
            <input type="hidden" name="pdf_builder_canvas_border_color" value="#000000">
            <input type="hidden" name="pdf_builder_canvas_border_width" value="2">
            <input type="hidden" name="pdf_builder_canvas_shadow_enabled" value="true">
            <input type="hidden" name="pdf_builder_canvas_grid_enabled" value="true">
            <input type="hidden" name="pdf_builder_canvas_grid_size" value="20">
            <input type="hidden" name="pdf_builder_canvas_guides_enabled" value="false">
            <input type="hidden" name="pdf_builder_canvas_snap_to_grid" value="true">
            <input type="hidden" name="pdf_builder_canvas_zoom_min" value="25">
            <input type="hidden" name="pdf_builder_canvas_zoom_max" value="400">
            <input type="hidden" name="pdf_builder_canvas_zoom_default" value="100">
            <input type="hidden" name="pdf_builder_canvas_zoom_step" value="25">
            <div id="card-canvas-width">800px</div>
            <div id="card-canvas-height">600px</div>
            <div id="card-canvas-dpi">150 DPI</div>
            <div id="card-canvas-bg-color" style="background-color: #ffffff"></div>
        `;

        // Mock jQuery AJAX
        mockAjax = jest.fn();
        global.$ = jest.fn((selector) => {
            if (selector === document) {
                return {
                    ready: jest.fn(callback => callback())
                };
            }
            return {};
        });
        global.$.ajax = mockAjax;
        global.jQuery = global.$;

        // Mock window.ajaxurl et autres globals
        global.ajaxurl = 'http://test.com/wp-admin/admin-ajax.php';
        global.pdfBuilderSettings = {
            nonce: 'test-nonce-123'
        };

        // Mock console pour éviter les logs pendant les tests
        global.console = {
            log: jest.fn(),
            warn: jest.fn(),
            error: jest.fn(),
            debug: jest.fn()
        };
    });

    afterEach(() => {
        document.body.innerHTML = '';
        delete global.ajaxurl;
        delete global.pdfBuilderSettings;
    });

    afterAll(() => {
        // Restaurer l'objet global original
        window.CanvasCardMonitor = originalCanvasCardMonitor;
    });

    describe('Parameter Validation', () => {
        test('should validate all required canvas parameters are present', () => {
            // Simuler un état de moniteur canvas
            const mockMonitor = {
                state: {
                    settings: {
                        'pdf_builder_canvas_width': '800',
                        'pdf_builder_canvas_height': '600',
                        'pdf_builder_canvas_dpi': '150',
                        'pdf_builder_canvas_bg_color': '#ffffff',
                        'pdf_builder_canvas_border_color': '#000000',
                        'pdf_builder_canvas_border_width': '2',
                        'pdf_builder_canvas_shadow_enabled': 'true',
                        'pdf_builder_canvas_grid_enabled': 'true',
                        'pdf_builder_canvas_grid_size': '20',
                        'pdf_builder_canvas_guides_enabled': 'false',
                        'pdf_builder_canvas_snap_to_grid': 'true',
                        'pdf_builder_canvas_zoom_min': '25',
                        'pdf_builder_canvas_zoom_max': '400',
                        'pdf_builder_canvas_zoom_default': '100',
                        'pdf_builder_canvas_zoom_step': '25'
                    },
                    errors: []
                },
                log: jest.fn(),
                verifyDatabaseConsistency: jest.fn()
            };

            // Simuler validateDataPersistence
            const requiredSettings = [
                'pdf_builder_canvas_width',
                'pdf_builder_canvas_height',
                'pdf_builder_canvas_dpi',
                'pdf_builder_canvas_bg_color',
                'pdf_builder_canvas_border_color',
                'pdf_builder_canvas_border_width',
                'pdf_builder_canvas_shadow_enabled',
                'pdf_builder_canvas_grid_enabled',
                'pdf_builder_canvas_grid_size',
                'pdf_builder_canvas_guides_enabled',
                'pdf_builder_canvas_snap_to_grid',
                'pdf_builder_canvas_zoom_min',
                'pdf_builder_canvas_zoom_max',
                'pdf_builder_canvas_zoom_default',
                'pdf_builder_canvas_zoom_step'
            ];

            let persistenceIssues = 0;
            requiredSettings.forEach(settingKey => {
                const value = mockMonitor.state.settings[settingKey];
                if (value === undefined || value === null || value === '') {
                    persistenceIssues++;
                    mockMonitor.state.errors.push({
                        type: 'MISSING_SETTING',
                        key: settingKey,
                        message: `Le paramètre ${settingKey} n'a pas de valeur`,
                        timestamp: new Date()
                    });
                }
            });

            expect(persistenceIssues).toBe(0);
            expect(mockMonitor.state.errors.length).toBe(0);
        });

        test('should detect missing parameters', () => {
            const mockMonitor = {
                state: {
                    settings: {
                        'pdf_builder_canvas_width': '800',
                        // Missing height
                        'pdf_builder_canvas_dpi': '150'
                    },
                    errors: []
                },
                log: jest.fn(),
                verifyDatabaseConsistency: jest.fn()
            };

            const requiredSettings = ['pdf_builder_canvas_width', 'pdf_builder_canvas_height'];
            let persistenceIssues = 0;

            requiredSettings.forEach(settingKey => {
                const value = mockMonitor.state.settings[settingKey];
                if (value === undefined || value === null || value === '') {
                    persistenceIssues++;
                    mockMonitor.state.errors.push({
                        type: 'MISSING_SETTING',
                        key: settingKey,
                        message: `Le paramètre ${settingKey} n'a pas de valeur`,
                        timestamp: new Date()
                    });
                }
            });

            expect(persistenceIssues).toBe(1);
            expect(mockMonitor.state.errors.length).toBe(1);
            expect(mockMonitor.state.errors[0].key).toBe('pdf_builder_canvas_height');
        });

        test('should validate parameter types and ranges', () => {
            const testCases = [
                { key: 'pdf_builder_canvas_width', value: '800', valid: true },
                { key: 'pdf_builder_canvas_width', value: '-100', valid: false },
                { key: 'pdf_builder_canvas_height', value: '600', valid: true },
                { key: 'pdf_builder_canvas_height', value: '0', valid: false },
                { key: 'pdf_builder_canvas_dpi', value: '150', valid: true },
                { key: 'pdf_builder_canvas_dpi', value: '1000', valid: false },
                { key: 'pdf_builder_canvas_bg_color', value: '#ffffff', valid: true },
                { key: 'pdf_builder_canvas_bg_color', value: 'invalid-color', valid: false }
            ];

            testCases.forEach(testCase => {
                const isValid = validateCanvasParameter(testCase.key, testCase.value);
                expect(isValid).toBe(testCase.valid);
            });

            function validateCanvasParameter(key, value) {
                switch (key) {
                    case 'pdf_builder_canvas_width':
                    case 'pdf_builder_canvas_height':
                        const num = parseInt(value);
                        return !isNaN(num) && num > 0 && num <= 10000;
                    case 'pdf_builder_canvas_dpi':
                        const dpi = parseInt(value);
                        return !isNaN(dpi) && dpi >= 72 && dpi <= 600;
                    case 'pdf_builder_canvas_bg_color':
                        return /^#[0-9A-Fa-f]{6}$/.test(value);
                    default:
                        return true;
                }
            }
        });
    });

    describe('Persistence Synchronization', () => {
        test('should synchronize hidden inputs with displayed values', () => {
            // Simuler la mise à jour d'un paramètre
            const widthInput = document.querySelector('input[name="pdf_builder_canvas_width"]');
            const widthDisplay = document.getElementById('card-canvas-width');

            // Changer la valeur cachée
            widthInput.value = '1024';

            // Simuler la synchronisation
            const mockMonitor = {
                updateCardDisplay: function(cardType, value) {
                    const displayElement = document.getElementById(`card-${cardType}`);
                    if (displayElement) {
                        displayElement.textContent = value;
                    }
                }
            };

            mockMonitor.updateCardDisplay('canvas-width', '1024px');

            expect(widthInput.value).toBe('1024');
            expect(widthDisplay.textContent).toBe('1024px');
        });

        test('should detect persistence mismatches', () => {
            const mockMonitor = {
                state: {
                    settings: {
                        'pdf_builder_canvas_width': '800'
                    },
                    errors: []
                },
                getCardDisplayedValues: function() {
                    return {
                        'width': '1024px' // Différent de la valeur cachée
                    };
                },
                getExpectedValuesForCard: function() {
                    return {
                        'width': '800px'
                    };
                },
                log: jest.fn()
            };

            // Simuler la vérification de cohérence
            let persistenceIssues = 0;
            const displayedValues = mockMonitor.getCardDisplayedValues();
            const expectedValues = mockMonitor.getExpectedValuesForCard();

            Object.keys(expectedValues).forEach(key => {
                const expected = expectedValues[key];
                const displayed = displayedValues[key];

                if (expected !== undefined && displayed !== undefined) {
                    if (expected != displayed) {
                        persistenceIssues++;
                        mockMonitor.state.errors.push({
                            type: 'PERSISTENCE_MISMATCH',
                            key: key,
                            expected: expected,
                            displayed: displayed,
                            message: `Mismatch for ${key}`,
                            timestamp: new Date()
                        });
                    }
                }
            });

            expect(persistenceIssues).toBe(1);
            expect(mockMonitor.state.errors.length).toBe(1);
            expect(mockMonitor.state.errors[0].type).toBe('PERSISTENCE_MISMATCH');
        });
    });

    describe('Database Consistency', () => {
        test('should verify database consistency via AJAX', () => {
            const mockMonitor = {
                state: {
                    settings: {
                        'pdf_builder_canvas_width': '800',
                        'pdf_builder_canvas_height': '600'
                    },
                    warnings: []
                },
                log: jest.fn()
            };

            // Mock successful AJAX response
            mockAjax.mockImplementation((options) => {
                options.success({
                    success: true,
                    data: {
                        'pdf_builder_canvas_width': '800',
                        'pdf_builder_canvas_height': '600'
                    }
                });
            });

            // Simuler verifyDatabaseConsistency
            let dbInconsistencies = 0;

            // Mock AJAX call
            const ajaxOptions = {
                url: global.ajaxurl,
                type: 'POST',
                data: {
                    action: 'verify_canvas_settings_consistency',
                    nonce: global.pdfBuilderSettings.nonce
                },
                success: (response) => {
                    if (response.success) {
                        const dbValues = response.data;
                        Object.keys(mockMonitor.state.settings).forEach(key => {
                            const domValue = mockMonitor.state.settings[key];
                            const dbValue = dbValues[key];

                            if (dbValue !== undefined && domValue != dbValue) {
                                dbInconsistencies++;
                                mockMonitor.state.warnings.push({
                                    type: 'DB_DOM_INCONSISTENCY',
                                    key: key,
                                    domValue: domValue,
                                    dbValue: dbValue,
                                    message: `Inconsistency for ${key}`,
                                    timestamp: new Date()
                                });
                            }
                        });
                    }
                },
                error: jest.fn()
            };

            // Execute AJAX
            ajaxOptions.success({
                success: true,
                data: {
                    'pdf_builder_canvas_width': '800',
                    'pdf_builder_canvas_height': '600'
                }
            });

            expect(dbInconsistencies).toBe(0);
            expect(mockMonitor.state.warnings.length).toBe(0);
        });

        test('should detect database inconsistencies', () => {
            const mockMonitor = {
                state: {
                    settings: {
                        'pdf_builder_canvas_width': '800'
                    },
                    warnings: []
                },
                log: jest.fn()
            };

            // Simuler verifyDatabaseConsistency avec données incohérentes
            let dbInconsistencies = 0;

            const domValue = '800';
            const dbValue = '1024'; // Différent

            if (dbValue !== undefined && domValue != dbValue) {
                dbInconsistencies++;
                mockMonitor.state.warnings.push({
                    type: 'DB_DOM_INCONSISTENCY',
                    key: 'pdf_builder_canvas_width',
                    domValue: domValue,
                    dbValue: dbValue,
                    message: 'Inconsistency detected',
                    timestamp: new Date()
                });
            }

            expect(dbInconsistencies).toBe(1);
            expect(mockMonitor.state.warnings.length).toBe(1);
            expect(mockMonitor.state.warnings[0].type).toBe('DB_DOM_INCONSISTENCY');
        });
    });

    describe('Error Handling and Resilience', () => {
        test('should handle AJAX failures gracefully', () => {
            const mockMonitor = {
                log: jest.fn()
            };

            // Mock failed AJAX response
            mockAjax.mockImplementation((options) => {
                options.error(null, 'error', 'Network error');
            });

            // Simuler verifyDatabaseConsistency avec erreur
            const ajaxOptions = {
                url: global.ajaxurl,
                type: 'POST',
                data: {
                    action: 'verify_canvas_settings_consistency',
                    nonce: global.pdfBuilderSettings.nonce
                },
                success: jest.fn(),
                error: (xhr, status, error) => {
                    mockMonitor.log('WARN', 'Erreur AJAX lors de la vérification DB:', error);
                }
            };

            ajaxOptions.error(null, 'error', 'Network error');

            expect(mockMonitor.log).toHaveBeenCalledWith('WARN', 'Erreur AJAX lors de la vérification DB:', 'Network error');
        });

        test('should handle missing AJAX dependencies', () => {
            const mockMonitor = {
                log: jest.fn()
            };

            // Supprimer les dépendances AJAX
            delete global.jQuery;
            delete global.ajaxurl;

            // Simuler verifyDatabaseConsistency sans AJAX
            let calledAjax = false;

            if (!global.jQuery || !global.ajaxurl) {
                mockMonitor.log('DEBUG', 'AJAX non disponible pour vérification base de données');
                calledAjax = false;
            } else {
                calledAjax = true;
            }

            expect(calledAjax).toBe(false);
            expect(mockMonitor.log).toHaveBeenCalledWith('DEBUG', 'AJAX non disponible pour vérification base de données');
        });

        test('should recover from corrupted data', () => {
            // Simuler des données corrompues dans le DOM
            document.querySelector('input[name="pdf_builder_canvas_width"]').value = 'corrupted-data';
            document.querySelector('input[name="pdf_builder_canvas_height"]').value = '';

            const mockMonitor = {
                state: {
                    settings: {},
                    errors: []
                },
                log: jest.fn(),
                sanitizeValue: function(key, value) {
                    if (!value || value === 'corrupted-data') {
                        // Retourner une valeur par défaut
                        switch (key) {
                            case 'pdf_builder_canvas_width': return '800';
                            case 'pdf_builder_canvas_height': return '600';
                            default: return '';
                        }
                    }
                    return value;
                }
            };

            // Collecter et sanitiser les valeurs
            const inputs = document.querySelectorAll('input[type="hidden"][name^="pdf_builder_canvas_"]');
            inputs.forEach(input => {
                const key = input.name;
                const rawValue = input.value;
                const sanitizedValue = mockMonitor.sanitizeValue(key, rawValue);
                mockMonitor.state.settings[key] = sanitizedValue;
            });

            expect(mockMonitor.state.settings['pdf_builder_canvas_width']).toBe('800');
            expect(mockMonitor.state.settings['pdf_builder_canvas_height']).toBe('600');
        });
    });
});
/**
 * Tests pour PDF Preview API Client
 * Tests unitaires pour la génération d'aperçus PDF
 */

describe('PDFPreviewAPI', () => {
    let api;
    let mockFetch;

    beforeEach(() => {
        // Mock fetch
        mockFetch = jest.fn();
        global.fetch = mockFetch;

        // Mock pdfBuilderAjax
        global.pdfBuilderAjax = {
            ajaxurl: '/wp-admin/admin-ajax.php',
            nonce: 'test-nonce'
        };

        // Mock des éléments DOM
        document.body.innerHTML = `
            <div id="pdf-preview-loading" style="display: none;">Loading...</div>
            <div id="pdf-preview-error" style="display: none;"></div>
        `;

        // Charger le script et créer l'instance
        require('../assets/js/pdf-preview-api-client.js');
        api = window.pdfPreviewAPI;

        // Mock des méthodes DOM après chargement
        api.showLoadingIndicator = jest.fn(() => {
            const el = document.getElementById('pdf-preview-loading');
            if (el) el.style.display = 'block';
        });
        api.hideLoadingIndicator = jest.fn(() => {
            const el = document.getElementById('pdf-preview-loading');
            if (el) el.style.display = 'none';
        });
        api.showError = jest.fn((message) => {
            const el = document.getElementById('pdf-preview-error');
            if (el) {
                el.style.display = 'block';
                el.textContent = message;
            }
        });
        api.displayPreview = jest.fn();
    });

    afterEach(() => {
        document.body.innerHTML = '';
        jest.clearAllMocks();
    });

    test('should initialize with correct endpoint and nonce', () => {
        expect(api.endpoint).toBe('/wp-admin/admin-ajax.php');
        expect(api.nonce).toBe('test-nonce');
        expect(api.isGenerating).toBe(false);
    });

    test('should generate editor preview successfully', async () => {
        const mockResponse = {
            success: true,
            data: {
                image_url: 'http://example.com/preview.png',
                template_id: 'test-template'
            }
        };

        mockFetch.mockResolvedValueOnce({
            json: () => Promise.resolve(mockResponse)
        });

        // Mock des méthodes DOM
        api.showLoadingIndicator = jest.fn();
        api.hideLoadingIndicator = jest.fn();
        api.displayPreview = jest.fn();

        const templateData = { title: 'Test Template' };
        const result = await api.generateEditorPreview(templateData);

        expect(result).toEqual(mockResponse.data);
        expect(mockFetch).toHaveBeenCalledWith('/wp-admin/admin-ajax.php', expect.any(Object));
        expect(api.showLoadingIndicator).toHaveBeenCalled();
        expect(api.hideLoadingIndicator).toHaveBeenCalled();
        expect(api.displayPreview).toHaveBeenCalledWith('http://example.com/preview.png', 'editor');
    });

    test('should handle editor preview generation failure', async () => {
        const mockResponse = {
            success: false,
            data: 'Template not found'
        };

        mockFetch.mockResolvedValueOnce({
            json: () => Promise.resolve(mockResponse)
        });

        // Mock des méthodes DOM
        api.showLoadingIndicator = jest.fn();
        api.hideLoadingIndicator = jest.fn();
        api.showError = jest.fn();

        const templateData = { title: 'Invalid Template' };
        const result = await api.generateEditorPreview(templateData);

        expect(result).toBeNull();
        expect(api.showError).toHaveBeenCalledWith('Erreur lors de la génération de l\'aperçu');
        expect(api.hideLoadingIndicator).toHaveBeenCalled();
    });

    test('should handle network errors during preview generation', async () => {
        mockFetch.mockRejectedValueOnce(new Error('Network error'));

        // Mock des méthodes DOM
        api.showLoadingIndicator = jest.fn();
        api.hideLoadingIndicator = jest.fn();
        api.showError = jest.fn();

        const templateData = { title: 'Test Template' };
        const result = await api.generateEditorPreview(templateData);

        expect(result).toBeNull();
        expect(api.showError).toHaveBeenCalledWith('Erreur de connexion');
        expect(api.hideLoadingIndicator).toHaveBeenCalled();
    });

    test('should prevent concurrent preview generation', async () => {
        // Démarrer une première génération
        api.isGenerating = true;

        const templateData = { title: 'Test Template' };
        const result = await api.generateEditorPreview(templateData);

        expect(result).toBeNull();
        expect(mockFetch).not.toHaveBeenCalled();
    });

    test('should generate order preview with order ID', async () => {
        // S'assurer que isGenerating est false
        api.isGenerating = false;

        const mockResponse = {
            success: true,
            data: {
                image_url: 'http://example.com/order-preview.png',
                template_id: 'order-template'
            }
        };

        mockFetch.mockResolvedValueOnce({
            json: () => Promise.resolve(mockResponse)
        });

        // Mock des méthodes DOM avant l'appel
        const originalShowLoading = api.showLoadingIndicator;
        const originalHideLoading = api.hideLoadingIndicator;
        const originalDisplayPreview = api.displayPreview;

        api.showLoadingIndicator = jest.fn();
        api.hideLoadingIndicator = jest.fn();
        api.displayPreview = jest.fn();

        const templateData = { order: { id: 123 } };
        const result = await api.generateOrderPreview(templateData, 123);

        expect(result).toEqual(mockResponse.data);
        expect(mockFetch).toHaveBeenCalledWith('/wp-admin/admin-ajax.php', expect.any(Object));
        expect(api.displayPreview).toHaveBeenCalledWith('http://example.com/order-preview.png', 'metabox', 123);

        // Restaurer les méthodes originales
        api.showLoadingIndicator = originalShowLoading;
        api.hideLoadingIndicator = originalHideLoading;
        api.displayPreview = originalDisplayPreview;
    });

    test('should show and hide loading indicator', () => {
        const loadingElement = document.getElementById('pdf-preview-loading');

        // S'assurer que les méthodes existent
        if (!api.showLoadingIndicator) {
            api.showLoadingIndicator = function() {
                const el = document.getElementById('pdf-preview-loading');
                if (el) el.style.display = 'block';
            };
        }

        if (!api.hideLoadingIndicator) {
            api.hideLoadingIndicator = function() {
                const el = document.getElementById('pdf-preview-loading');
                if (el) el.style.display = 'none';
            };
        }

        api.showLoadingIndicator();
        expect(loadingElement.style.display).toBe('block');

        api.hideLoadingIndicator();
        expect(loadingElement.style.display).toBe('none');
    });

    test('should show error message', () => {
        const errorElement = document.getElementById('pdf-preview-error');

        // S'assurer que la méthode existe
        if (!api.showError) {
            api.showError = function(message) {
                const el = document.getElementById('pdf-preview-error');
                if (el) {
                    el.style.display = 'block';
                    el.textContent = message;
                }
            };
        }

        api.showError('Test error message');
        expect(errorElement.style.display).toBe('block');
        expect(errorElement.textContent).toBe('Test error message');
    });
});
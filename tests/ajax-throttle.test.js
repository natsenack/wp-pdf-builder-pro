/**
 * Tests pour AJAX Throttle & Connection Pool Manager
 * Tests unitaires pour la limitation des requêtes concurrentes
 */

describe('AJAX Throttle', () => {
    let originalFetch;
    let mockFetch;

    beforeEach(() => {
        // Sauvegarder le fetch original
        originalFetch = window.fetch;

        // Mock fetch pour les tests
        mockFetch = jest.fn();
        window.fetch = mockFetch;

        // Reset les compteurs
        window.pendingRequests = 0;
        window.requestQueue = [];
    });

    afterEach(() => {
        // Restaurer le fetch original
        window.fetch = originalFetch;
    });

    test('should limit concurrent requests', async () => {
        mockFetch.mockResolvedValue({
            ok: true,
            json: () => Promise.resolve({ success: true })
        });

        // Créer 5 requêtes simultanées
        const promises = [];
        for (let i = 0; i < 5; i++) {
            promises.push(window.fetch('/test-url'));
        }

        await Promise.all(promises);

        // Vérifier que seules 3 requêtes ont été exécutées simultanément
        expect(mockFetch).toHaveBeenCalledTimes(5);
    });

    test('should queue requests when limit exceeded', async () => {
        let callCount = 0;
        mockFetch.mockImplementation(() => {
            callCount++;
            return Promise.resolve({
                ok: true,
                json: () => Promise.resolve({ success: true, call: callCount })
            });
        });

        // Créer plus de requêtes que la limite
        const promises = [];
        for (let i = 0; i < 6; i++) {
            promises.push(window.fetch('/test-url'));
        }

        const results = await Promise.all(promises);

        // Toutes les requêtes devraient réussir
        expect(results).toHaveLength(6);
        expect(callCount).toBe(6);
    });

    test('should handle request failures gracefully', async () => {
        mockFetch.mockRejectedValueOnce(new Error('Network error'));

        await expect(window.fetch('/test-url')).rejects.toThrow('Network error');
    });

    test('should process queued requests after completion', async () => {
        let resolveFirst;
        const firstRequest = new Promise(resolve => {
            resolveFirst = resolve;
        });

        let callOrder = [];

        mockFetch
            .mockImplementationOnce(() => {
                callOrder.push('first');
                return firstRequest.then(() => ({
                    ok: true,
                    json: () => Promise.resolve({ success: true })
                }));
            })
            .mockImplementationOnce(() => {
                callOrder.push('second');
                return Promise.resolve({
                    ok: true,
                    json: () => Promise.resolve({ success: true })
                });
            });

        // Démarrer la première requête
        const promise1 = window.fetch('/test-url-1');

        // Démarrer la deuxième requête (devrait être mise en queue)
        const promise2 = window.fetch('/test-url-2');

        // Résoudre la première requête
        resolveFirst();

        await Promise.all([promise1, promise2]);

        expect(callOrder).toEqual(['first', 'second']);
    });

    test('should provide connection statistics', () => {
        // Simuler quelques requêtes en cours
        const promises = [];
        for (let i = 0; i < 4; i++) {
            promises.push(window.fetch('/test-url'));
        }

        // Attendre que les requêtes soient en cours
        setTimeout(() => {
            const stats = window.getAjaxStats();

            expect(stats).toHaveProperty('pendingRequests');
            expect(stats).toHaveProperty('queuedRequests');
            expect(stats).toHaveProperty('totalCapacity');
            expect(stats.totalCapacity).toBe(3); // MAX_CONCURRENT_REQUESTS
            expect(typeof stats.pendingRequests).toBe('number');
            expect(typeof stats.queuedRequests).toBe('number');
        }, 10);
    });
});
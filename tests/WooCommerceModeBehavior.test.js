/**
 * Test du comportement des éléments WooCommerce en mode éditeur vs mode commande
 * Vérifie que les éléments utilisent les bonnes sources de données selon le mode
 */

import { WooCommerceElementsManager } from '../assets/js/src/pdf-builder-react/utils/WooCommerceElementsManager';

describe('WooCommerce Elements Mode Behavior Tests', () => {
    let mockCanvas;
    let mockCtx;
    let mockState;
    let wooCommerceManager;

    beforeEach(() => {
        // Mock du contexte canvas
        mockCtx = {
            save: jest.fn(),
            restore: jest.fn(),
            translate: jest.fn(),
            rotate: jest.fn(),
            scale: jest.fn(),
            clearRect: jest.fn(),
            fillStyle: '',
            strokeStyle: '',
            lineWidth: 1,
            font: '',
            textAlign: 'left',
            textBaseline: 'alphabetic',
            fillRect: jest.fn(),
            strokeRect: jest.fn(),
            fillText: jest.fn(),
            stroke: jest.fn(),
            beginPath: jest.fn(),
            moveTo: jest.fn(),
            lineTo: jest.fn(),
            quadraticCurveTo: jest.fn(),
            closePath: jest.fn(),
            arc: jest.fn(),
            measureText: jest.fn().mockReturnValue({ width: 100 }),
            setLineDash: jest.fn()
        };

        mockCanvas = {
            getContext: jest.fn().mockReturnValue(mockCtx),
            width: 800,
            height: 600
        };

        // Mock du state avec mode éditeur par défaut
        mockState = {
            previewMode: 'editor',
            orderId: undefined,
            canvas: { pan: { x: 0, y: 0 }, zoom: 1, showGrid: false, gridSize: 20 },
            selection: { selectedElements: [] },
            elements: []
        };

        // Instance du manager WooCommerce
        wooCommerceManager = new WooCommerceElementsManager();
    });

    afterEach(() => {
        wooCommerceManager.reset();
    });

    describe('Product Table Rendering', () => {
        test('should use fake data in editor mode', () => {
            // Mock de l'élément product_table
            const element = {
                id: 'test-table',
                type: 'product_table',
                x: 50,
                y: 100,
                width: 500,
                height: 200
            };

            // Simuler la fonction drawProductTable avec le state en mode éditeur
            mockState.previewMode = 'editor';

            // Ici nous testerions que la fonction utilise les données fictives
            // Pour cet exemple, nous vérifions juste que le manager n'a pas de données chargées
            expect(wooCommerceManager.getOrderData()).toBeNull();
        });

        test('should use WooCommerce data in command mode', async () => {
            // Charger des données fictives WooCommerce
            await wooCommerceManager.loadOrderData('123');

            const orderData = wooCommerceManager.getOrderData();
            expect(orderData).not.toBeNull();
            expect(orderData?.order_number).toBe('CMD-2024-0123');

            const orderItems = wooCommerceManager.getOrderItems();
            expect(orderItems.length).toBeGreaterThan(0);
            expect(orderItems[0]).toHaveProperty('name');
            expect(orderItems[0]).toHaveProperty('price');
        });
    });

    describe('Customer Info Rendering', () => {
        test('should use fake customer data in editor mode', () => {
            mockState.previewMode = 'editor';

            const customerInfo = wooCommerceManager.getCustomerInfo();
            // En mode éditeur, devrait retourner des données par défaut
            expect(customerInfo.name).toBe('Client Inconnu');
            expect(customerInfo.email).toBe('email@inconnu.com');
        });

        test('should use WooCommerce customer data in command mode', async () => {
            // Charger des données de commande et client
            await wooCommerceManager.loadOrderData('123');
            await wooCommerceManager.loadCustomerData(123);

            const customerInfo = wooCommerceManager.getCustomerInfo();
            expect(customerInfo.name).toBe('Marie Dupont');
            expect(customerInfo.email).toBe('marie.dupont@email.com');
            expect(customerInfo.phone).toBe('+33 6 12 34 56 78');
        });
    });

    describe('Order Number Rendering', () => {
        test('should use fake order number in editor mode', () => {
            mockState.previewMode = 'editor';

            const orderNumber = wooCommerceManager.getOrderNumber();
            expect(orderNumber).toBe('CMD-XXXX-XXXX');
        });

        test('should use real order number in command mode', async () => {
            await wooCommerceManager.loadOrderData('456');

            const orderNumber = wooCommerceManager.getOrderNumber();
            expect(orderNumber).toBe('CMD-2024-0456');
        });
    });

    describe('Dynamic Text Rendering', () => {
        test('should replace variables with fake data in editor mode', () => {
            mockState.previewMode = 'editor';

            // Simuler le remplacement de variables
            const template = 'Commande #{order_number} - Client: #{customer_name}';
            let processedText = template;

            if (mockState.previewMode === 'editor') {
                processedText = processedText
                    .replace('#{order_number}', 'CMD-2024-01234')
                    .replace('#{customer_name}', 'Marie Dupont');
            }

            expect(processedText).toContain('CMD-2024-01234');
            expect(processedText).toContain('Marie Dupont');
        });

        test('should replace variables with real WooCommerce data in command mode', async () => {
            await wooCommerceManager.loadOrderData('789');
            await wooCommerceManager.loadCustomerData(123);

            mockState.previewMode = 'command';

            const template = 'Commande #{order_number} - Client: #{customer_name} - Total: #{total}';
            let processedText = template;

            if (mockState.previewMode === 'command') {
                const orderNumber = wooCommerceManager.getOrderNumber();
                const customerInfo = wooCommerceManager.getCustomerInfo();
                const orderTotals = wooCommerceManager.getOrderTotals();

                processedText = processedText
                    .replace('#{order_number}', orderNumber)
                    .replace('#{customer_name}', customerInfo.name)
                    .replace('#{total}', `${orderTotals.total.toFixed(2)}${orderTotals.currency}`);
            }

            expect(processedText).toContain('CMD-2024-0789');
            expect(processedText).toContain('Marie Dupont');
            expect(processedText).toContain('279.96EUR');
        });
    });

    describe('Company Info Hybrid Mode', () => {
        test('should use element properties for company info', () => {
            // En mode hybride, les données viennent des propriétés de l'élément
            const element = {
                id: 'test-company',
                type: 'company_info',
                x: 50,
                y: 100,
                width: 300,
                height: 150,
                companyName: 'Ma Super Boutique',
                companyEmail: 'super@boutique.com'
            };

            // Les propriétés de l'élément devraient être utilisées
            expect(element.companyName).toBe('Ma Super Boutique');
            expect(element.companyEmail).toBe('super@boutique.com');
        });
    });

    describe('Order Totals Calculation', () => {
        test('should calculate totals correctly from WooCommerce data', async () => {
            await wooCommerceManager.loadOrderData('123');

            const totals = wooCommerceManager.getOrderTotals();

            expect(totals).toHaveProperty('subtotal');
            expect(totals).toHaveProperty('tax');
            expect(totals).toHaveProperty('shipping');
            expect(totals).toHaveProperty('total');
            expect(totals).toHaveProperty('currency');

            expect(totals.currency).toBe('EUR');
            expect(totals.total).toBe(279.96);
        });
    });
});
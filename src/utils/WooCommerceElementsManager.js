/**
 * PDF Builder Pro - WooCommerce Elements Manager
 * Gestion des éléments WooCommerce côté client
 */

export class WooCommerceElementsManager {

    constructor() {
        this.elements = new Map();
        this.testMode = true;
        this.orderId = null;
    }

    /**
     * Activer/désactiver le mode test
     */
    setTestMode(enabled) {
        this.testMode = enabled;
    }

    /**
     * Définir l'ID de commande pour les données réelles
     */
    setOrderId(orderId) {
        this.orderId = orderId;
    }

    /**
     * Enregistrer un élément WooCommerce
     */
    registerElement(elementId, elementType, elementData = {}) {
        this.elements.set(elementId, {
            type: elementType,
            data: elementData,
            lastUpdate: Date.now()
        });
    }

    /**
     * Mettre à jour les données d'un élément
     */
    updateElementData(elementId, newData) {
        if (this.elements.has(elementId)) {
            const element = this.elements.get(elementId);
            element.data = { ...element.data, ...newData };
            element.lastUpdate = Date.now();
            this.elements.set(elementId, element);
        }
    }

    /**
     * Supprimer un élément
     */
    unregisterElement(elementId) {
        this.elements.delete(elementId);
    }

    /**
     * Obtenir les données d'un élément
     */
    getElementData(elementId) {
        const element = this.elements.get(elementId);
        return element ? element.data : null;
    }

    /**
     * Obtenir le texte d'affichage pour un élément
     */
    getElementDisplayText(elementType) {
        const testData = this.getTestData(elementType);
        return testData;
    }

    /**
     * Obtenir les données de test pour un type d'élément
     */
    getTestData(elementType) {
        const testData = {
            'woocommerce-invoice-number': 'INV-001',
            'woocommerce-invoice-date': new Date().toISOString().split('T')[0],
            'woocommerce-order-number': '#1234',
            'woocommerce-order-date': new Date().toLocaleString(),
            'woocommerce-billing-address': 'John Doe\n123 Main Street\nSpringfield, IL 62701\nUnited States',
            'woocommerce-shipping-address': 'John Doe\n456 Shipping Avenue\nSpringfield, IL 62702\nUnited States',
            'woocommerce-customer-name': 'John Doe',
            'woocommerce-customer-email': 'john.doe@example.com',
            'woocommerce-payment-method': 'Carte de crédit (Stripe)',
            'woocommerce-order-status': 'Traitée',
            'woocommerce-products-table': this.formatProductsTable([
                { name: 'Produit Exemple 1', quantity: 1, price: '$10.00', total: '$10.00' },
                { name: 'Produit Exemple 2', quantity: 2, price: '$15.00', total: '$30.00' },
                { name: 'Produit Exemple 3', quantity: 1, price: '$5.00', total: '$5.00' }
            ]),
            'woocommerce-subtotal': '$45.00',
            'woocommerce-discount': '-$5.00',
            'woocommerce-shipping': '$5.00',
            'woocommerce-taxes': '$2.25',
            'woocommerce-total': '$47.25',
            'woocommerce-refund': '$0.00',
            'woocommerce-fees': '$1.50',
            'woocommerce-quote-number': 'QUO-001',
            'woocommerce-quote-date': new Date().toISOString().split('T')[0],
            'woocommerce-quote-validity': '30 jours',
            'woocommerce-quote-notes': 'Conditions spéciales : paiement à 30 jours.'
        };

        return testData[elementType] || '';
    }

    /**
     * Formater le tableau des produits
     */
    formatProductsTable(products) {
        if (!Array.isArray(products)) {
            return '';
        }

        return products.map(product =>
            `- ${product.name} x${product.quantity} ${product.price}`
        ).join('\n');
    }

    /**
     * Charger les données WooCommerce depuis le serveur
     */
    async loadWooCommerceData(orderId = null) {
        if (this.testMode || !orderId) {
            return this.getAllTestData();
        }

        try {
            const response = await fetch(`/wp-json/pdf-builder/v1/woocommerce-data/${orderId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings ? wpApiSettings.nonce : ''
                }
            });

            if (!response.ok) {
                throw new Error(`Erreur HTTP: ${response.status}`);
            }

            const data = await response.json();
            return data;
        } catch (error) {
            return this.getAllTestData();
        }
    }

    /**
     * Obtenir toutes les données de test
     */
    getAllTestData() {
        const allTypes = [
            'woocommerce-invoice-number',
            'woocommerce-invoice-date',
            'woocommerce-order-number',
            'woocommerce-order-date',
            'woocommerce-billing-address',
            'woocommerce-shipping-address',
            'woocommerce-customer-name',
            'woocommerce-customer-email',
            'woocommerce-payment-method',
            'woocommerce-order-status',
            'woocommerce-products-table',
            'woocommerce-subtotal',
            'woocommerce-discount',
            'woocommerce-shipping',
            'woocommerce-taxes',
            'woocommerce-total',
            'woocommerce-refund',
            'woocommerce-fees',
            'woocommerce-quote-number',
            'woocommerce-quote-date',
            'woocommerce-quote-validity',
            'woocommerce-quote-notes'
        ];

        const data = {};
        allTypes.forEach(type => {
            data[type] = this.getTestData(type);
        });

        return data;
    }

    /**
     * Valider un élément WooCommerce
     */
    validateElement(elementType, elementData) {
        const validations = {
            'woocommerce-invoice-number': (data) => typeof data === 'string' && data.length > 0,
            'woocommerce-invoice-date': (data) => this.isValidDate(data),
            'woocommerce-order-number': (data) => typeof data === 'string' && data.length > 0,
            'woocommerce-order-date': (data) => this.isValidDate(data),
            'woocommerce-billing-address': (data) => typeof data === 'string' && data.length > 0,
            'woocommerce-shipping-address': (data) => typeof data === 'string' && data.length > 0,
            'woocommerce-customer-name': (data) => typeof data === 'string' && data.length > 0,
            'woocommerce-customer-email': (data) => this.isValidEmail(data),
            'woocommerce-payment-method': (data) => typeof data === 'string',
            'woocommerce-order-status': (data) => typeof data === 'string',
            'woocommerce-products-table': (data) => typeof data === 'string',
            'woocommerce-subtotal': (data) => typeof data === 'string',
            'woocommerce-discount': (data) => typeof data === 'string',
            'woocommerce-shipping': (data) => typeof data === 'string',
            'woocommerce-taxes': (data) => typeof data === 'string',
            'woocommerce-total': (data) => typeof data === 'string',
            'woocommerce-refund': (data) => typeof data === 'string',
            'woocommerce-fees': (data) => typeof data === 'string',
            'woocommerce-quote-number': (data) => typeof data === 'string' && data.length > 0,
            'woocommerce-quote-date': (data) => this.isValidDate(data),
            'woocommerce-quote-validity': (data) => typeof data === 'string',
            'woocommerce-quote-notes': (data) => typeof data === 'string'
        };

        const validator = validations[elementType];
        return validator ? validator(elementData) : true;
    }

    /**
     * Vérifier si une date est valide
     */
    isValidDate(dateString) {
        if (typeof dateString !== 'string') return false;
        const date = new Date(dateString);
        return !isNaN(date.getTime());
    }

    /**
     * Vérifier si un email est valide
     */
    isValidEmail(email) {
        if (typeof email !== 'string') return false;
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    /**
     * Obtenir les propriétés par défaut pour un type d'élément
     */
    getDefaultProperties(elementType) {
        const defaults = {
            width: 200,
            height: 60,
            fontSize: 14,
            fontFamily: 'Arial, sans-serif',
            color: '#333333',
            backgroundColor: '#ffffff',
            borderColor: '#dddddd',
            borderWidth: 1,
            borderRadius: 4,
            padding: 8
        };

        // Propriétés spécifiques selon le type
        switch (elementType) {
            case 'woocommerce-billing-address':
            case 'woocommerce-shipping-address':
                defaults.height = 100;
                break;
            case 'woocommerce-products-table':
                defaults.width = 400;
                defaults.height = 150;
                break;
            case 'woocommerce-invoice-number':
            case 'woocommerce-order-number':
            case 'woocommerce-quote-number':
                defaults.width = 150;
                defaults.height = 40;
                break;
            default:
                break;
        }

        return defaults;
    }

    /**
     * Nettoyer les ressources
     */
    dispose() {
        this.elements.clear();
    }
}

// Instance globale
export const wooCommerceElementsManager = new WooCommerceElementsManager();

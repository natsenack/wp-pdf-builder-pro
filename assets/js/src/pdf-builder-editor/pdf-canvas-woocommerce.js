/**
 * PDF Builder Pro - WooCommerce Elements Manager - Version Vanilla JS
 * Gestion des éléments WooCommerce côté client
 * Migré depuis resources/js/utils/WooCommerceElementsManager.js
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
            'woocommerce-invoice-number': 'FACT-2025-001',
            'woocommerce-invoice-date': '27/10/2025',
            'woocommerce-order-number': '#WC-12345',
            'woocommerce-order-date': '26/10/2025 à 14:30',
            'woocommerce-billing-address': 'Marie Dupont\n15 Rue de la Paix\n75002 Paris\nFrance',
            'woocommerce-shipping-address': 'Marie Dupont\n25 Avenue des Champs-Élysées\n75008 Paris\nFrance',
            'woocommerce-customer-name': 'Marie Dupont',
            'woocommerce-customer-email': 'marie.dupont@email.fr',
            'woocommerce-payment-method': 'Carte bancaire (Stripe)',
            'woocommerce-order-status': 'Commande expédiée',
            'woocommerce-products-table': this.formatProductsTable([
                { name: 'Ordinateur portable Dell XPS 13', quantity: 1, price: '1 299,00 €', total: '1 299,00 €' },
                { name: 'Souris Logitech MX Master 3', quantity: 1, price: '89,99 €', total: '89,99 €' },
                { name: 'Clavier mécanique Keychron K8', quantity: 1, price: '79,99 €', total: '79,99 €' },
                { name: 'Sacoche ordinateur 15"', quantity: 1, price: '49,99 €', total: '49,99 €' }
            ]),
            'woocommerce-subtotal': '1 518,97 €',
            'woocommerce-discount': '-76,00 €',
            'woocommerce-shipping': '9,99 €',
            'woocommerce-taxes': '304,00 €',
            'woocommerce-total': '1 756,96 €',
            'woocommerce-refund': '0,00 €',
            'woocommerce-fees': '15,00 €',
            'woocommerce-quote-number': 'DEV-2025-001',
            'woocommerce-quote-date': '20/10/2025',
            'woocommerce-quote-validity': '30 jours',
            'woocommerce-quote-notes': 'Devis pour équipement informatique professionnel. Prix valables 30 jours.'
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

        // En-tête du tableau
        let table = 'PRODUIT\t\t\tQTÉ\tPRIX UNIT.\tTOTAL\n';
        table += '─'.repeat(80) + '\n';

        // Lignes de produits
        products.forEach(product => {
            const name = product.name.length > 30 ? product.name.substring(0, 27) + '...' : product.name;
            const line = `${name}\t${product.quantity}\t${product.price}\t${product.total}`;
            table += line + '\n';
        });

        return table;
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
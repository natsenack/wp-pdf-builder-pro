import React from 'react';

/**
 * Sample Data Provider
 *
 * Provides sample data for Canvas mode preview
 */
export class SampleDataProvider {

    /**
     * Get sample data for an element
     *
     * @param {string} elementKey - Element key (type_id)
     * @param {Object} element - Element configuration
     * @returns {Object} Sample data
     */
    static getElementData(elementKey, element) {
        const elementType = element.type;

        switch (elementType) {
            case 'text':
                return this.getTextData(element);

            case 'dynamic-text':
                return this.getDynamicTextData(element);

            case 'conditional-text':
                return this.getConditionalTextData(element);

            case 'product_table':
                return this.getProductTableData(element);

            case 'customer_info':
                return this.getCustomerInfoData(element);

            case 'company_info':
                return this.getCompanyInfoData(element);

            case 'company_logo':
                return this.getCompanyLogoData(element);

            case 'order_number':
                return this.getOrderNumberData(element);

            case 'document_type':
                return this.getDocumentTypeData(element);

            case 'mentions':
                return this.getMentionsData(element);

            case 'image':
                return this.getImageData(element);

            case 'barcode':
            case 'qrcode':
                return this.getBarcodeData(element);

            case 'progress-bar':
                return this.getProgressBarData(element);

            case 'watermark':
                return this.getWatermarkData(element);

            default:
                return {};
        }
    }

    /**
     * Get sample text data
     */
    static getTextData(element) {
        return {
            content: element.text || 'Texte d\'exemple'
        };
    }

    /**
     * Get sample dynamic text data
     */
    static getDynamicTextData(element) {
        const templates = {
            'total_only': '€1,234.56',
            'order_summary': 'Commande #12345 - 15/10/2025',
            'customer_greeting': 'Bonjour Jean Dupont,'
        };

        return {
            content: templates[element.template] || element.customContent || 'Contenu dynamique'
        };
    }

    /**
     * Get sample conditional text data
     */
    static getConditionalTextData(element) {
        return {
            content: element.trueText || 'Condition vraie'
        };
    }

    /**
     * Get sample product table data
     */
    static getProductTableData(element) {
        const sampleProducts = [
            {
                name: 'Produit Exemple 1',
                sku: 'PROD-001',
                quantity: 2,
                price: 25.50,
                total: 51.00
            },
            {
                name: 'Produit Exemple 2',
                sku: 'PROD-002',
                quantity: 1,
                price: 75.25,
                total: 75.25
            }
        ];

        return {
            headers: element.headers || ['Produit', 'Qté', 'Prix', 'Total'],
            products: sampleProducts,
            tableStyleData: this.getTableStyleData(element.tableStyle || 'default'),
            totals: {
                subtotal: 126.25,
                shipping: 5.90,
                tax: 25.25,
                discount: 0.00,
                total: 157.40
            }
        };
    }

    /**
     * Get table style data
     */
    static getTableStyleData(styleName) {
        const styles = {
            default: {
                header_bg: [248, 249, 250],
                header_border: [226, 232, 240],
                row_border: [241, 245, 249],
                alt_row_bg: [250, 251, 252],
                headerTextColor: '#000000',
                rowTextColor: '#000000',
                border_width: 1,
                headerFontWeight: 'bold',
                headerFontSize: '12px',
                rowFontSize: '11px'
            },
            blue: {
                header_bg: [59, 130, 246],
                header_border: [37, 99, 235],
                row_border: [219, 234, 254],
                alt_row_bg: [239, 246, 255],
                headerTextColor: '#ffffff',
                rowTextColor: '#000000',
                border_width: 1,
                headerFontWeight: 'bold',
                headerFontSize: '12px',
                rowFontSize: '11px'
            }
            // Add more styles as needed
        };

        return styles[styleName] || styles.default;
    }

    /**
     * Get sample customer info data
     */
    static getCustomerInfoData(element) {
        return {
            name: 'Jean Dupont',
            email: 'jean.dupont@email.com',
            phone: '+33 1 23 45 67 89',
            company: 'Entreprise Exemple',
            address: '123 Rue de la Paix\n75001 Paris\nFrance'
        };
    }

    /**
     * Get sample company info data
     */
    static getCompanyInfoData(element) {
        return {
            name: 'Ma Société SARL',
            address: '456 Avenue des Champs\n75008 Paris\nFrance',
            phone: '+33 1 98 76 54 32',
            email: 'contact@masociete.com',
            vat: 'FR 12 345 678 901',
            rcs: 'Paris B 123 456 789',
            siret: '123 456 789 01234'
        };
    }

    /**
     * Get sample company logo data
     */
    static getCompanyLogoData(element) {
        return {
            imageUrl: '/wp-content/uploads/2025/01/company-logo.png',
            alt: 'Logo Entreprise'
        };
    }

    /**
     * Get sample order number data
     */
    static getOrderNumberData(element) {
        return {
            formatted: 'Commande #12345 - 15/10/2025'
        };
    }

    /**
     * Get sample document type data
     */
    static getDocumentTypeData(element) {
        return {
            type: 'invoice',
            label: 'Facture'
        };
    }

    /**
     * Get sample mentions data
     */
    static getMentionsData(element) {
        return {
            mentions: [
                'contact@masociete.com',
                '+33 1 98 76 54 32',
                'SIRET: 123 456 789 01234',
                'TVA: FR 12 345 678 901'
            ]
        };
    }

    /**
     * Get sample image data
     */
    static getImageData(element) {
        return {
            imageUrl: '/wp-content/uploads/2025/01/sample-image.jpg',
            alt: 'Image exemple'
        };
    }

    /**
     * Get sample barcode data
     */
    static getBarcodeData(element) {
        return {
            code: element.type === 'qrcode' ? 'QR123456' : '1234567890123',
            format: element.type === 'qrcode' ? 'QR_CODE' : 'CODE128'
        };
    }

    /**
     * Get sample progress bar data
     */
    static getProgressBarData(element) {
        return {
            value: element.progressValue || 75
        };
    }

    /**
     * Get sample watermark data
     */
    static getWatermarkData(element) {
        return {
            text: element.content || 'CONFIDENTIEL'
        };
    }
}
import React from 'react';

/**
 * Real Data Provider
 *
 * Provides real WooCommerce order data for Metabox mode preview
 */
export class RealDataProvider {

    /**
     * Cache for fetched data
     */
    static dataCache = new Map();

    /**
     * Get real data for an element
     *
     * @param {string} elementKey - Element key (type_id)
     * @param {Object} element - Element configuration
     * @param {number} orderId - WooCommerce order ID
     * @returns {Promise<Object>} Real data
     */
    static async getElementData(elementKey, element, orderId) {
        const cacheKey = `${elementKey}_${orderId}`;

        // Check cache first
        if (this.dataCache.has(cacheKey)) {
            return this.dataCache.get(cacheKey);
        }

        try {
            // Fetch real data from server
            const response = await this.fetchOrderData(orderId);
            const data = this.processElementData(elementKey, element, response);

            // Cache the result
            this.dataCache.set(cacheKey, data);

            return data;
        } catch (error) {
            console.error('Error fetching real data:', error);
            // Fallback to sample data
            return SampleDataProvider.getElementData(elementKey, element);
        }
    }

    /**
     * Fetch order data from server
     *
     * @param {number} orderId - WooCommerce order ID
     * @returns {Promise<Object>} Order data
     */
    static async fetchOrderData(orderId) {
        const response = await fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'pdf_builder_get_order_preview_data',
                order_id: orderId,
                nonce: pdfBuilderAjax.nonce
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.data || 'Failed to fetch order data');
        }

        return data.data;
    }

    /**
     * Process element data from server response
     *
     * @param {string} elementKey - Element key
     * @param {Object} element - Element configuration
     * @param {Object} response - Server response
     * @returns {Object} Processed data
     */
    static processElementData(elementKey, element, response) {
        const elementType = element.type;

        switch (elementType) {
            case 'text':
                return this.processTextData(element, response);

            case 'dynamic-text':
                return this.processDynamicTextData(element, response);

            case 'conditional-text':
                return this.processConditionalTextData(element, response);

            case 'product_table':
                return this.processProductTableData(element, response);

            case 'customer_info':
                return this.processCustomerInfoData(element, response);

            case 'company_info':
                return this.processCompanyInfoData(element, response);

            case 'company_logo':
                return this.processCompanyLogoData(element, response);

            case 'order_number':
                return this.processOrderNumberData(element, response);

            case 'document_type':
                return this.processDocumentTypeData(element, response);

            case 'mentions':
                return this.processMentionsData(element, response);

            case 'image':
                return this.processImageData(element, response);

            case 'barcode':
            case 'qrcode':
                return this.processBarcodeData(element, response);

            case 'progress-bar':
                return this.processProgressBarData(element, response);

            case 'watermark':
                return this.processWatermarkData(element, response);

            default:
                return {};
        }
    }

    /**
     * Process text data
     */
    static processTextData(element, response) {
        return {
            content: element.text || ''
        };
    }

    /**
     * Process dynamic text data with variable replacement
     */
    static processDynamicTextData(element, response) {
        let content = element.customContent || '';

        // Replace variables with real data
        if (response.variables) {
            Object.keys(response.variables).forEach(key => {
                const regex = new RegExp(`{{${key}}}`, 'g');
                content = content.replace(regex, response.variables[key] || '');
            });
        }

        // Handle predefined templates
        if (element.template && response.templates && response.templates[element.template]) {
            content = response.templates[element.template];
        }

        return {
            content: content
        };
    }

    /**
     * Process conditional text data
     */
    static processConditionalTextData(element, response) {
        let condition = false;

        // Evaluate condition
        if (element.condition && response.variables) {
            try {
                // Simple condition evaluation (can be enhanced)
                const conditionStr = element.condition.replace(/\{\{(\w+)\}\}/g, (match, key) => {
                    return response.variables[key] || '0';
                });

                // Basic evaluation for common conditions
                if (conditionStr.includes('>')) {
                    const parts = conditionStr.split('>');
                    condition = parseFloat(parts[0].trim()) > parseFloat(parts[1].trim());
                } else if (conditionStr.includes('<')) {
                    const parts = conditionStr.split('<');
                    condition = parseFloat(parts[0].trim()) < parseFloat(parts[1].trim());
                } else if (conditionStr.includes('=')) {
                    const parts = conditionStr.split('=');
                    condition = parts[0].trim() === parts[1].trim();
                }
            } catch (error) {
                console.error('Error evaluating condition:', error);
                condition = false;
            }
        }

        return {
            content: condition ? (element.trueText || '') : (element.falseText || '')
        };
    }

    /**
     * Process product table data
     */
    static processProductTableData(element, response) {
        return {
            headers: element.headers || response.tableHeaders || ['Produit', 'Qté', 'Prix', 'Total'],
            products: response.products || [],
            tableStyleData: this.getTableStyleData(element.tableStyle || 'default'),
            totals: response.totals || {
                subtotal: 0,
                shipping: 0,
                tax: 0,
                discount: 0,
                total: 0
            }
        };
    }

    /**
     * Get table style data (reuse from SampleDataProvider)
     */
    static getTableStyleData(styleName) {
        return SampleDataProvider.getTableStyleData(styleName);
    }

    /**
     * Process customer info data
     */
    static processCustomerInfoData(element, response) {
        return response.customer || {};
    }

    /**
     * Process company info data
     */
    static processCompanyInfoData(element, response) {
        return response.company || {};
    }

    /**
     * Process company logo data
     */
    static processCompanyLogoData(element, response) {
        return {
            imageUrl: response.companyLogo || '',
            alt: 'Logo Entreprise'
        };
    }

    /**
     * Process order number data
     */
    static processOrderNumberData(element, response) {
        return {
            formatted: response.orderNumberFormatted || response.variables?.order_number || ''
        };
    }

    /**
     * Process document type data
     */
    static processDocumentTypeData(element, response) {
        return {
            type: response.documentType || 'invoice',
            label: response.documentTypeLabel || 'Facture'
        };
    }

    /**
     * Process mentions data
     */
    static processMentionsData(element, response) {
        return {
            mentions: response.mentions || []
        };
    }

    /**
     * Process image data
     */
    static processImageData(element, response) {
        return {
            imageUrl: response.imageUrl || '',
            alt: element.alt || 'Image'
        };
    }

    /**
     * Process barcode data
     */
    static processBarcodeData(element, response) {
        return {
            code: response.barcodeCode || (element.type === 'qrcode' ? 'QR123456' : '1234567890123'),
            format: element.type === 'qrcode' ? 'QR_CODE' : 'CODE128'
        };
    }

    /**
     * Process progress bar data
     */
    static processProgressBarData(element, response) {
        return {
            value: response.progressValue || element.progressValue || 75
        };
    }

    /**
     * Process watermark data
     */
    static processWatermarkData(element, response) {
        return {
            text: element.content || response.watermarkText || 'CONFIDENTIEL'
        };
    }

    /**
     * Clear data cache
     */
    static clearCache() {
        this.dataCache.clear();
    }

    /**
     * Get cached data for debugging
     */
    static getCacheStats() {
        return {
            size: this.dataCache.size,
            keys: Array.from(this.dataCache.keys())
        };
    }
}

// Import SampleDataProvider for fallbacks
import { SampleDataProvider } from './SampleDataProvider.jsx';
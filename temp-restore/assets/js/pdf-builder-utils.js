/**
 * PDF Builder Pro - Utilitaires v4.0.0
 */

const PDF_BUILDER_UTILS = {
    
    /**
     * Génère un ID unique
     */
    generateId: function(prefix = 'element') {
        return `${prefix}-${Date.now()}-${Math.random().toString(36).substr(2, 5)}`;
    },

    /**
     * Conversion millimètres vers pixels (96 DPI)
     */
    mmToPx: function(mm) {
        return Math.round(mm * 3.7795275591); // 96 DPI
    },

    /**
     * Conversion pixels vers millimètres (96 DPI)
     */
    pxToMm: function(px) {
        return Math.round(px / 3.7795275591 * 100) / 100;
    },

    /**
     * Validation format A4
     */
    isA4Format: function(width, height) {
        return width === 794 && height === 1123;
    },

    /**
     * Calcul du ratio d'aspect
     */
    calculateAspectRatio: function(width, height) {
        return Math.round((width / height) * 1000) / 1000;
    },

    /**
     * Formatage des prix
     */
    formatPrice: function(price, currency = '€') {
        const num = parseFloat(price);
        if (isNaN(num)) return '0,00 ' + currency;
        return num.toFixed(2).replace('.', ',') + ' ' + currency;
    },

    /**
     * Formatage des dates
     */
    formatDate: function(date, format = 'DD/MM/YYYY') {
        const d = new Date(date);
        const day = String(d.getDate()).padStart(2, '0');
        const month = String(d.getMonth() + 1).padStart(2, '0');
        const year = d.getFullYear();

        switch(format) {
            case 'DD/MM/YYYY': return `${day}/${month}/${year}`;
            case 'YYYY-MM-DD': return `${year}-${month}-${day}`;
            default: return `${day}/${month}/${year}`;
        }
    },

    /**
     * Sanitisation du texte
     */
    sanitizeText: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    /**
     * Throttling des fonctions
     */
    throttle: function(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    },

    /**
     * Debouncing des fonctions
     */
    debounce: function(func, delay) {
        let debounceTimer;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => func.apply(context, args), delay);
        }
    },

    /**
     * Validation de couleur hexadécimale
     */
    isValidHex: function(hex) {
        return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/.test(hex);
    },

    /**
     * Position relative dans le canvas
     */
    getRelativePosition: function(event, container) {
        const rect = container.getBoundingClientRect();
        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top
        };
    },

    /**
     * Contraindre une valeur dans une plage
     */
    clamp: function(value, min, max) {
        return Math.min(Math.max(value, min), max);
    },

    /**
     * Copie profonde d'un objet
     */
    deepClone: function(obj) {
        return JSON.parse(JSON.stringify(obj));
    },

    /**
     * Log avec horodatage
     */
    log: function(message, type = 'info') {
        const timestamp = new Date().toLocaleTimeString();
        const styles = {
            info: 'color: #007cba',
            success: 'color: #28a745', 
            warning: 'color: #ffc107',
            error: 'color: #dc3545'
        };
    },

    /**
     * Conversion d'unités CSS
     */
    convertUnit: function(value, fromUnit, toUnit) {
        const conversions = {
            'px': 1,
            'mm': 3.7795275591,
            'cm': 37.795275591,
            'in': 96,
            'pt': 1.333333
        };
        
        if (!conversions[fromUnit] || !conversions[toUnit]) {
            return value;
        }
        
        return (value / conversions[fromUnit]) * conversions[toUnit];
    }
};

// Exposition globale
window.PDF_BUILDER_UTILS = PDF_BUILDER_UTILS;
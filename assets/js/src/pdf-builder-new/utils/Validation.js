/**
 * Validation - Utilitaires de validation
 * Validation des données et des entrées utilisateur
 */

export class Validation {
    constructor() {
        this.rules = new Map();
        this.messages = new Map();
        this._initDefaultRules();
    }

    /**
     * Initialisation des règles par défaut
     * @private
     */
    _initDefaultRules() {
        // Règles de validation communes
        this.addRule('required', (value) => {
            return value !== null && value !== undefined &&
                   (typeof value !== 'string' || value.trim().length > 0);
        }, 'Ce champ est obligatoire');

        this.addRule('minLength', (value, min) => {
            return typeof value === 'string' && value.length >= min;
        }, 'La longueur minimale est de {min} caractères');

        this.addRule('maxLength', (value, max) => {
            return typeof value === 'string' && value.length <= max;
        }, 'La longueur maximale est de {max} caractères');

        this.addRule('numeric', (value) => {
            return !isNaN(value) && !isNaN(parseFloat(value));
        }, 'Ce champ doit être numérique');

        this.addRule('positive', (value) => {
            return this.validate('numeric', value) && parseFloat(value) > 0;
        }, 'Ce champ doit être positif');

        this.addRule('range', (value, min, max) => {
            const num = parseFloat(value);
            return !isNaN(num) && num >= min && num <= max;
        }, 'La valeur doit être entre {min} et {max}');

        this.addRule('color', (value) => {
            return /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$|^rgb\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3})\)$|^rgba\((\d{1,3}),\s*(\d{1,3}),\s*(\d{1,3}),\s*(0|1|0?\.\d+)\)$|^hsl\((\d{1,3}),\s*(\d{1,3})%,\s*(\d{1,3})%\)$|^hsla\((\d{1,3}),\s*(\d{1,3})%,\s*(\d{1,3})%,\s*(0|1|0?\.\d+)\)$|^[a-zA-Z]+$/.test(value);
        }, 'Couleur invalide');

        this.addRule('email', (value) => {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        }, 'Adresse email invalide');

        this.addRule('url', (value) => {
            try {
                new URL(value);
                return true;
            } catch {
                return false;
            }
        }, 'URL invalide');
    }

    /**
     * Ajout d'une règle de validation personnalisée
     */
    addRule(name, validator, message = '') {
        if (typeof validator !== 'function') {
            throw new Error('Le validateur doit être une fonction');
        }

        this.rules.set(name, validator);
        this.messages.set(name, message);
    }

    /**
     * Validation d'une valeur avec une règle
     */
    validate(rule, value, ...params) {
        const validator = this.rules.get(rule);
        if (!validator) {
            throw new Error(`Règle de validation inconnue: ${rule}`);
        }

        return validator(value, ...params);
    }

    /**
     * Validation d'un objet avec plusieurs règles
     */
    validateObject(data, rules) {
        const errors = {};
        let isValid = true;

        for (const [field, fieldRules] of Object.entries(rules)) {
            const value = data[field];
            const fieldErrors = [];

            for (const rule of fieldRules) {
                let ruleName, params, message;

                if (typeof rule === 'string') {
                    ruleName = rule;
                    params = [];
                } else if (Array.isArray(rule)) {
                    [ruleName, ...params] = rule;
                } else if (typeof rule === 'object') {
                    ruleName = rule.name;
                    params = rule.params || [];
                    message = rule.message;
                }

                if (!this.validate(ruleName, value, ...params)) {
                    const errorMessage = message || this._formatMessage(ruleName, params);
                    fieldErrors.push(errorMessage);
                    isValid = false;
                }
            }

            if (fieldErrors.length > 0) {
                errors[field] = fieldErrors;
            }
        }

        return { isValid, errors };
    }

    /**
     * Validation d'un élément PDF
     */
    validateElement(element) {
        const rules = {
            id: ['required'],
            type: ['required'],
            x: [['numeric'], ['range', 0, 10000]],
            y: [['numeric'], ['range', 0, 10000]],
            width: [['positive'], ['range', 1, 10000]],
            height: [['positive'], ['range', 1, 10000]]
        };

        // Règles spécifiques selon le type
        switch (element.type) {
            case 'text':
                rules.text = ['required'];
                rules.fontSize = [['positive'], ['range', 6, 200]];
                rules.color = ['color'];
                break;

            case 'rectangle':
            case 'circle':
                if (element.fillColor) rules.fillColor = ['color'];
                if (element.strokeColor) rules.strokeColor = ['color'];
                if (element.strokeWidth) rules.strokeWidth = [['positive'], ['range', 0.1, 50]];
                break;

            case 'image':
                if (element.image) rules.image = ['url'];
                break;
        }

        return this.validateObject(element, rules);
    }

    /**
     * Validation d'un template
     */
    validateTemplate(template) {
        const rules = {
            id: ['required'],
            name: ['required', ['minLength', 1], ['maxLength', 100]],
            elements: []
        };

        const result = this.validateObject(template, rules);

        // Validation des éléments individuels
        if (template.elements && Array.isArray(template.elements)) {
            const elementErrors = [];
            template.elements.forEach((element, index) => {
                const elementValidation = this.validateElement(element);
                if (!elementValidation.isValid) {
                    elementErrors.push({
                        index,
                        element: element.id || `element_${index}`,
                        errors: elementValidation.errors
                    });
                }
            });

            if (elementErrors.length > 0) {
                result.isValid = false;
                result.errors.elements = elementErrors;
            }
        }

        return result;
    }

    /**
     * Formatage d'un message d'erreur
     * @private
     */
    _formatMessage(rule, params) {
        let message = this.messages.get(rule) || `Validation ${rule} échouée`;

        // Remplacement des paramètres
        params.forEach((param, index) => {
            message = message.replace(`{${index}}`, param);
            message = message.replace(`{${rule}_${index}}`, param);
        });

        return message;
    }

    /**
     * Sanitisation d'une valeur
     */
    sanitize(value, type = 'string') {
        if (value === null || value === undefined) return value;

        switch (type) {
            case 'string':
                return String(value).trim();
            case 'number':
                const num = parseFloat(value);
                return isNaN(num) ? 0 : num;
            case 'boolean':
                return Boolean(value);
            case 'array':
                return Array.isArray(value) ? value : [value];
            default:
                return value;
        }
    }

    /**
     * Validation et sanitisation combinées
     */
    validateAndSanitize(data, rules, sanitization = {}) {
        const sanitized = { ...data };

        // Sanitisation
        Object.keys(sanitization).forEach(field => {
            if (sanitized[field] !== undefined) {
                sanitized[field] = this.sanitize(sanitized[field], sanitization[field]);
            }
        });

        // Validation
        const validation = this.validateObject(sanitized, rules);

        return {
            data: sanitized,
            ...validation
        };
    }

    /**
     * Récupération des règles disponibles
     */
    getAvailableRules() {
        return Array.from(this.rules.keys());
    }

    /**
     * Statistiques de validation
     */
    getStats() {
        return {
            totalRules: this.rules.size,
            rules: Array.from(this.rules.keys())
        };
    }

    /**
     * Réinitialisation des règles personnalisées
     */
    reset() {
        this.rules.clear();
        this.messages.clear();
        this._initDefaultRules();
    }
}

// Instance globale pour utilisation facile
export const validation = new Validation();
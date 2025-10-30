/**
 * PDF Canvas Properties Manager - Gestionnaire de propriétés d'éléments
 * Système centralisé pour la gestion, validation et liaison des propriétés
 */

import { elementCustomizationService } from './pdf-canvas-customization.js';

export class PDFCanvasPropertiesManager {
    constructor(canvasInstance) {
        this.canvasInstance = canvasInstance;
        this.propertyHistory = new Map(); // Historique par élément
        this.propertyBindings = new Map(); // Liaisons de propriétés
        this.propertyWatchers = new Map(); // Observateurs de propriétés
        this.bulkUpdateQueue = new Map(); // File d'attente pour mises à jour groupées

        // Configuration
        this.config = {
            enableHistory: true,
            maxHistorySize: 50,
            enableValidation: true,
            enableBindings: true,
            enableWatchers: true,
            autoSave: true,
            debounceMs: 100
        };

        // Timers pour le debounce
        this.debounceTimers = new Map();
    }

    /**
     * Définit une propriété d'élément
     */
    setProperty(elementId, propertyName, value, options = {}) {
        const {
            skipValidation = false,
            skipHistory = false,
            skipWatchers = false,
            immediate = false
        } = options;

        const element = this.canvasInstance.elements.get(elementId);
        if (!element) {
            
            return false;
        }

        // Validation
        if (this.config.enableValidation && !skipValidation) {
            const validation = this.validateProperty(element, propertyName, value);
            if (!validation.valid) {
                
                return false;
            }
            value = validation.value; // Utiliser la valeur validée/corrigée
        }

        // Sauvegarder l'ancienne valeur pour l'historique
        const oldValue = element.properties[propertyName];

        // Vérifier si la valeur a changé
        if (oldValue === value) {
            return true; // Pas de changement
        }

        // Appliquer la nouvelle valeur
        element.properties[propertyName] = value;
        element.updatedAt = Date.now();

        // Ajouter à l'historique
        if (this.config.enableHistory && !skipHistory) {
            this.addToHistory(elementId, propertyName, oldValue, value);
        }

        // Traiter les liaisons
        if (this.config.enableBindings) {
            this.processBindings(elementId, propertyName, value);
        }

        // Notifier les observateurs
        if (this.config.enableWatchers && !skipWatchers) {
            this.notifyWatchers(elementId, propertyName, oldValue, value);
        }

        // Mise à jour groupée ou immédiate
        if (immediate) {
            this.canvasInstance.render();
        } else {
            this.scheduleBulkUpdate(elementId);
        }

        return true;
    }

    /**
     * Obtient une propriété d'élément
     */
    getProperty(elementId, propertyName, defaultValue = null) {
        const element = this.canvasInstance.elements.get(elementId);
        if (!element) return defaultValue;

        const value = element.properties[propertyName];
        return value !== undefined ? value : defaultValue;
    }

    /**
     * Définit plusieurs propriétés d'un élément
     */
    setProperties(elementId, properties, options = {}) {
        const results = {};

        for (const [propertyName, value] of Object.entries(properties)) {
            results[propertyName] = this.setProperty(elementId, propertyName, value, {
                ...options,
                skipHistory: true, // L'historique sera géré au niveau du groupe
                immediate: false
            });
        }

        // Ajouter à l'historique pour le groupe
        if (this.config.enableHistory && !options.skipHistory) {
            this.addGroupToHistory(elementId, properties);
        }

        // Mise à jour immédiate si demandée
        if (options.immediate) {
            this.canvasInstance.render();
        } else {
            this.scheduleBulkUpdate(elementId);
        }

        return results;
    }

    /**
     * Obtient toutes les propriétés d'un élément
     */
    getProperties(elementId) {
        const element = this.canvasInstance.elements.get(elementId);
        return element ? { ...element.properties } : {};
    }

    /**
     * Valide une propriété
     */
    validateProperty(element, propertyName, value) {
        // Validation du service de personnalisation
        const validatedValue = elementCustomizationService.validateProperty(propertyName, value);

        // Validation spécifique selon le type
        const typeValidation = this.validatePropertyByType(propertyName, validatedValue, element);

        return {
            valid: typeValidation.valid,
            reason: typeValidation.reason,
            value: typeValidation.value
        };
    }

    /**
     * Validation spécifique selon le type de propriété
     */
    validatePropertyByType(propertyName, value, element) {
        const validations = {
            // Dimensions
            width: (val) => this.validateDimension(val, 1, 2000),
            height: (val) => this.validateDimension(val, 1, 2000),

            // Position
            x: (val) => this.validateCoordinate(val),
            y: (val) => this.validateCoordinate(val),

            // Couleurs
            backgroundColor: (val) => this.validateColor(val),
            borderColor: (val) => this.validateColor(val),
            color: (val) => this.validateColor(val),

            // Typographie
            fontSize: (val) => this.validateRange(val, 8, 72),
            opacity: (val) => this.validateRange(val, 0, 100),

            // Rotation
            rotation: (val) => this.validateAngle(val),

            // Échelle
            scale: (val) => this.validateRange(val, 10, 200)
        };

        const validator = validations[propertyName];
        if (validator) {
            return validator(value);
        }

        // Validation par défaut : accepter
        return { valid: true, value: value };
    }

    /**
     * Valide une dimension
     */
    validateDimension(value, min = 1, max = 2000) {
        if (typeof value !== 'number' || value < min || value > max) {
            return {
                valid: false,
                reason: `Dimension must be a number between ${min} and ${max}`,
                value: Math.max(min, Math.min(max, Number(value) || min))
            };
        }
        return { valid: true, value: value };
    }

    /**
     * Valide une coordonnée
     */
    validateCoordinate(value) {
        if (typeof value !== 'number') {
            return {
                valid: false,
                reason: 'Coordinate must be a number',
                value: Number(value) || 0
            };
        }
        return { valid: true, value: value };
    }

    /**
     * Valide une couleur
     */
    validateColor(value) {
        if (value === 'transparent' || value === '') {
            return { valid: true, value: value };
        }

        // Vérifier si c'est un code hex valide
        if (/^#[0-9A-Fa-f]{6}$/.test(value) || /^#[0-9A-Fa-f]{3}$/.test(value)) {
            return { valid: true, value: value };
        }

        // Vérifier si c'est un nom de couleur CSS valide
        const tempElement = document.createElement('div');
        tempElement.style.color = value;
        const computedColor = tempElement.style.color;

        if (computedColor && computedColor !== '') {
            return { valid: true, value: value };
        }

        return {
            valid: false,
            reason: 'Invalid color format',
            value: '#000000'
        };
    }

    /**
     * Valide une plage de valeurs
     */
    validateRange(value, min, max) {
        if (typeof value !== 'number' || value < min || value > max) {
            return {
                valid: false,
                reason: `Value must be between ${min} and ${max}`,
                value: Math.max(min, Math.min(max, Number(value) || min))
            };
        }
        return { valid: true, value: value };
    }

    /**
     * Valide un angle
     */
    validateAngle(value) {
        if (typeof value !== 'number') {
            return {
                valid: false,
                reason: 'Angle must be a number',
                value: Number(value) || 0
            };
        }

        // Normaliser entre -180 et 180
        const normalized = ((value % 360) + 360) % 360;
        const clamped = normalized > 180 ? normalized - 360 : normalized;

        return { valid: true, value: clamped };
    }

    /**
     * Ajoute une entrée à l'historique
     */
    addToHistory(elementId, propertyName, oldValue, newValue) {
        if (!this.propertyHistory.has(elementId)) {
            this.propertyHistory.set(elementId, []);
        }

        const history = this.propertyHistory.get(elementId);
        history.push({
            property: propertyName,
            oldValue: oldValue,
            newValue: newValue,
            timestamp: Date.now()
        });

        // Limiter la taille de l'historique
        if (history.length > this.config.maxHistorySize) {
            history.shift();
        }
    }

    /**
     * Ajoute un groupe de propriétés à l'historique
     */
    addGroupToHistory(elementId, properties) {
        if (!this.propertyHistory.has(elementId)) {
            this.propertyHistory.set(elementId, []);
        }

        const history = this.propertyHistory.get(elementId);
        history.push({
            properties: { ...properties },
            timestamp: Date.now(),
            type: 'group'
        });

        // Limiter la taille de l'historique
        if (history.length > this.config.maxHistorySize) {
            history.shift();
        }
    }

    /**
     * Annule la dernière modification d'une propriété
     */
    undoProperty(elementId, propertyName = null) {
        const history = this.propertyHistory.get(elementId);
        if (!history || history.length === 0) return false;

        // Trouver la dernière entrée pour cette propriété
        let lastEntry = null;
        if (propertyName) {
            for (let i = history.length - 1; i >= 0; i--) {
                const entry = history[i];
                if (entry.property === propertyName || (entry.type === 'group' && entry.properties[propertyName])) {
                    lastEntry = entry;
                    history.splice(i, 1); // Retirer de l'historique
                    break;
                }
            }
        } else {
            lastEntry = history.pop(); // Dernière entrée
        }

        if (!lastEntry) return false;

        // Restaurer la valeur
        if (lastEntry.type === 'group') {
            // Restaurer toutes les propriétés du groupe
            for (const [prop, value] of Object.entries(lastEntry.properties)) {
                this.setProperty(elementId, prop, value, {
                    skipHistory: true,
                    immediate: false
                });
            }
        } else {
            // Restaurer une propriété individuelle
            this.setProperty(elementId, lastEntry.property, lastEntry.oldValue, {
                skipHistory: true,
                immediate: false
            });
        }

        this.scheduleBulkUpdate(elementId);
        return true;
    }

    /**
     * Crée une liaison entre propriétés
     */
    bindProperties(sourceElementId, sourceProperty, targetElementId, targetProperty, transform = null) {
        const bindingKey = `${sourceElementId}.${sourceProperty}`;

        if (!this.propertyBindings.has(bindingKey)) {
            this.propertyBindings.set(bindingKey, []);
        }

        const bindings = this.propertyBindings.get(bindingKey);
        bindings.push({
            targetElementId,
            targetProperty,
            transform
        });
    }

    /**
     * Supprime une liaison de propriétés
     */
    unbindProperties(sourceElementId, sourceProperty, targetElementId = null, targetProperty = null) {
        const bindingKey = `${sourceElementId}.${sourceProperty}`;
        const bindings = this.propertyBindings.get(bindingKey);

        if (!bindings) return;

        if (targetElementId && targetProperty) {
            // Supprimer une liaison spécifique
            const index = bindings.findIndex(b =>
                b.targetElementId === targetElementId && b.targetProperty === targetProperty
            );
            if (index !== -1) {
                bindings.splice(index, 1);
            }
        } else {
            // Supprimer toutes les liaisons pour cette propriété source
            this.propertyBindings.delete(bindingKey);
        }
    }

    /**
     * Traite les liaisons pour une propriété modifiée
     */
    processBindings(sourceElementId, sourceProperty, value) {
        const bindingKey = `${sourceElementId}.${sourceProperty}`;
        const bindings = this.propertyBindings.get(bindingKey);

        if (!bindings) return;

        bindings.forEach(binding => {
            let targetValue = value;

            // Appliquer la transformation si définie
            if (binding.transform) {
                targetValue = binding.transform(value);
            }

            // Appliquer la valeur à la cible
            this.setProperty(binding.targetElementId, binding.targetProperty, targetValue, {
                skipHistory: true,
                skipWatchers: true,
                immediate: false
            });
        });
    }

    /**
     * Ajoute un observateur de propriété
     */
    watchProperty(elementId, propertyName, callback, options = {}) {
        const watchKey = `${elementId}.${propertyName}`;

        if (!this.propertyWatchers.has(watchKey)) {
            this.propertyWatchers.set(watchKey, []);
        }

        const watchers = this.propertyWatchers.get(watchKey);
        watchers.push({
            callback,
            options: {
                immediate: false,
                ...options
            }
        });

        // Appel immédiat si demandé
        if (options.immediate) {
            const currentValue = this.getProperty(elementId, propertyName);
            callback(currentValue, undefined, { type: 'initial' });
        }
    }

    /**
     * Supprime un observateur de propriété
     */
    unwatchProperty(elementId, propertyName, callback = null) {
        const watchKey = `${elementId}.${propertyName}`;
        const watchers = this.propertyWatchers.get(watchKey);

        if (!watchers) return;

        if (callback) {
            // Supprimer un observateur spécifique
            const index = watchers.findIndex(w => w.callback === callback);
            if (index !== -1) {
                watchers.splice(index, 1);
            }
        } else {
            // Supprimer tous les observateurs pour cette propriété
            this.propertyWatchers.delete(watchKey);
        }
    }

    /**
     * Notifie les observateurs d'une propriété
     */
    notifyWatchers(elementId, propertyName, oldValue, newValue) {
        const watchKey = `${elementId}.${propertyName}`;
        const watchers = this.propertyWatchers.get(watchKey);

        if (!watchers) return;

        watchers.forEach(watcher => {
            try {
                watcher.callback(newValue, oldValue, {
                    elementId,
                    propertyName,
                    type: 'change'
                });
            } catch (error) {
                
            }
        });
    }

    /**
     * Programme une mise à jour groupée
     */
    scheduleBulkUpdate(elementId) {
        if (this.debounceTimers.has(elementId)) {
            clearTimeout(this.debounceTimers.get(elementId));
        }

        const timer = setTimeout(() => {
            this.canvasInstance.render();
            this.debounceTimers.delete(elementId);
        }, this.config.debounceMs);

        this.debounceTimers.set(elementId, timer);
    }

    /**
     * Force une mise à jour immédiate
     */
    forceUpdate(elementId = null) {
        if (elementId) {
            if (this.debounceTimers.has(elementId)) {
                clearTimeout(this.debounceTimers.get(elementId));
                this.debounceTimers.delete(elementId);
            }
            this.canvasInstance.render();
        } else {
            // Mettre à jour tous les timers en attente
            this.debounceTimers.forEach((timer, id) => {
                clearTimeout(timer);
            });
            this.debounceTimers.clear();
            this.canvasInstance.render();
        }
    }

    /**
     * Obtient l'historique d'un élément
     */
    getPropertyHistory(elementId) {
        return this.propertyHistory.get(elementId) || [];
    }

    /**
     * Obtient les liaisons d'une propriété
     */
    getPropertyBindings(elementId, propertyName) {
        const bindingKey = `${elementId}.${propertyName}`;
        return this.propertyBindings.get(bindingKey) || [];
    }

    /**
     * Obtient les observateurs d'une propriété
     */
    getPropertyWatchers(elementId, propertyName) {
        const watchKey = `${elementId}.${propertyName}`;
        return this.propertyWatchers.get(watchKey) || [];
    }

    /**
     * Réinitialise les propriétés d'un élément aux valeurs par défaut
     */
    resetProperties(elementId, propertyNames = null) {
        const element = this.canvasInstance.elements.get(elementId);
        if (!element) return false;

        const defaultProps = elementCustomizationService.getDefaultProperties(element.type, this.canvasInstance.elements);
        const propertiesToReset = propertyNames || Object.keys(defaultProps);

        const resetProps = {};
        propertiesToReset.forEach(prop => {
            if (defaultProps.hasOwnProperty(prop)) {
                resetProps[prop] = defaultProps[prop];
            }
        });

        return this.setProperties(elementId, resetProps);
    }

    /**
     * Copie les propriétés d'un élément à un autre
     */
    copyProperties(sourceElementId, targetElementId, propertyNames = null) {
        const sourceProps = this.getProperties(sourceElementId);
        const targetElement = this.canvasInstance.elements.get(targetElementId);

        if (!targetElement) return false;

        const propertiesToCopy = propertyNames || Object.keys(sourceProps);
        const copiedProps = {};

        propertiesToCopy.forEach(prop => {
            if (sourceProps.hasOwnProperty(prop)) {
                copiedProps[prop] = sourceProps[prop];
            }
        });

        return this.setProperties(targetElementId, copiedProps);
    }

    /**
     * Configure le gestionnaire de propriétés
     */
    configure(options) {
        this.config = { ...this.config, ...options };
    }

    /**
     * Obtient les statistiques d'utilisation
     */
    getStats() {
        const totalElements = this.canvasInstance.elements.size;
        const totalHistoryEntries = Array.from(this.propertyHistory.values())
            .reduce((sum, history) => sum + history.length, 0);
        const totalBindings = Array.from(this.propertyBindings.values())
            .reduce((sum, bindings) => sum + bindings.length, 0);
        const totalWatchers = Array.from(this.propertyWatchers.values())
            .reduce((sum, watchers) => sum + watchers.length, 0);

        return {
            totalElements,
            totalHistoryEntries,
            totalBindings,
            totalWatchers,
            pendingUpdates: this.debounceTimers.size
        };
    }

    /**
     * Nettoie les ressources
     */
    dispose() {
        // Annuler tous les timers
        this.debounceTimers.forEach(timer => clearTimeout(timer));
        this.debounceTimers.clear();

        // Vider les collections
        this.propertyHistory.clear();
        this.propertyBindings.clear();
        this.propertyWatchers.clear();
        this.bulkUpdateQueue.clear();
    }
}

export default PDFCanvasPropertiesManager;

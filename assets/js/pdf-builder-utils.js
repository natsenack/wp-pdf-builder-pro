/**
 * PDF Builder Pro - Utilitaires JavaScript
 * Fonctions utilitaires communes pour le plugin
 */

(function() {
    'use strict';

    // Namespace global
    window.PDFBuilderUtils = window.PDFBuilderUtils || {};

    // Fonction de logging avec fallback
    PDFBuilderUtils.log = function() {
        if (typeof console !== 'undefined' && console.log) {
            console.log.apply(console, arguments);
        }
    };

    // Fonction d'erreur avec fallback
    PDFBuilderUtils.error = function() {
        if (typeof console !== 'undefined' && console.error) {
            console.error.apply(console, arguments);
        }
    };

    // Fonction de warning avec fallback
    PDFBuilderUtils.warn = function() {
        if (typeof console !== 'undefined' && console.warn) {
            console.warn.apply(console, arguments);
        }
    };

    // Vérifier si un élément DOM existe
    PDFBuilderUtils.elementExists = function(selector) {
        return typeof document !== 'undefined' &&
               document.querySelector &&
               document.querySelector(selector) !== null;
    };

    // Attendre qu'un élément DOM soit disponible
    PDFBuilderUtils.waitForElement = function(selector, callback, maxAttempts, interval) {
        maxAttempts = maxAttempts || 50;
        interval = interval || 100;

        var attempts = 0;

        var checkElement = function() {
            attempts++;
            var element = document.querySelector(selector);

            if (element) {
                callback(element);
            } else if (attempts < maxAttempts) {
                setTimeout(checkElement, interval);
            } else {
                PDFBuilderUtils.error('Element not found after', maxAttempts, 'attempts:', selector);
            }
        };

        checkElement();
    };

    // Générer un ID unique
    PDFBuilderUtils.generateId = function(prefix) {
        prefix = prefix || 'pdf-builder';
        var timestamp = Date.now();
        var random = Math.random().toString(36).substr(2, 9);
        return prefix + '-' + timestamp + '-' + random;
    };

    // Échapper les caractères HTML
    PDFBuilderUtils.escapeHtml = function(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    };

    // Vérifier si une valeur est vide
    PDFBuilderUtils.isEmpty = function(value) {
        return value === null ||
               value === undefined ||
               value === '' ||
               (Array.isArray(value) && value.length === 0) ||
               (typeof value === 'object' && Object.keys(value).length === 0);
    };

    // Debounce function
    PDFBuilderUtils.debounce = function(func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };

    // Throttle function
    PDFBuilderUtils.throttle = function(func, limit) {
        var inThrottle;
        return function() {
            var args = arguments;
            var context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(function() { inThrottle = false; }, limit);
            }
        };
    };

})();

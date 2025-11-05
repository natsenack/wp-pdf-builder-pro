/**
 * PDF Builder Pro - Canvas Element Style Injector
 * Injecte les styles des éléments depuis les propriétés du template JSON
 */

/* global document, window, setTimeout, MutationObserver, console */

(function() {
    'use strict';

    // Observer pour surveiller les changements du canvas
    function initializeCanvasStyleInjector() {
        const canvas = document.querySelector('[data-canvas-id], .canvas, [class*="canvas"]');
        if (!canvas) {
            setTimeout(initializeCanvasStyleInjector, 500);
            return;
        }

        // Créer une feuille de style dynamique pour les styles CSS générés
        const styleSheet = document.createElement('style');
        styleSheet.id = 'pdf-builder-element-styles';
        styleSheet.textContent = '';
        document.head.appendChild(styleSheet);

        // Fonction pour appliquer les styles à tous les éléments d'un template
        function applyTemplateStyles(templateData) {
            if (!templateData || !templateData.elements) {
                return;
            }

            console.log('Applying styles to', templateData.elements.length, 'elements');
            templateData.elements.forEach(element => {
                applyElementStyles(null, element);
            });
        }

        // Fonction pour appliquer les styles à un élément
        function applyElementStyles(element, elementData) {
            if (!elementData || !elementData.properties) {
                return;
            }

            const props = elementData.properties;
            const elementId = elementData.id;
            const type = elementData.type;
            const styles = [];

            // Parser les couleurs et styles selon le type d'élément
            if (type === 'rectangle' || type === 'shape') {
                if (props.fillColor) {
                    styles.push(`background-color: ${props.fillColor}`);
                }
                if (props.strokeColor && props.strokeWidth) {
                    styles.push(`border: ${props.strokeWidth}px solid ${props.strokeColor}`);
                }
            }

            if (type === 'circle') {
                if (props.fillColor) {
                    styles.push(`background-color: ${props.fillColor}`);
                }
                if (props.strokeColor && props.strokeWidth) {
                    styles.push(`border: ${props.strokeWidth}px solid ${props.strokeColor}`);
                }
                styles.push('border-radius: 50%');
            }

            if (type === 'line') {
                if (props.strokeColor) {
                    const strokeWidth = props.strokeWidth || 1;
                    styles.push(`border-top: ${strokeWidth}px solid ${props.strokeColor}`);
                    styles.push(`height: 0px`);
                }
            }

            if (type === 'text' || type === 'document_type' || type === 'order_number' || 
                type === 'dynamic-text' || type === 'company_info' || type === 'customer_info') {
                
                if (props.color || props.textColor) {
                    const color = props.color || props.textColor;
                    styles.push(`color: ${color}`);
                }
                if (props.fontSize) {
                    styles.push(`font-size: ${props.fontSize}px`);
                }
                if (props.fontFamily) {
                    styles.push(`font-family: "${props.fontFamily}", sans-serif`);
                }
                if (props.fontWeight) {
                    styles.push(`font-weight: ${props.fontWeight}`);
                }
                if (props.textAlign) {
                    styles.push(`text-align: ${props.textAlign}`);
                }
                if (props.backgroundColor) {
                    styles.push(`background-color: ${props.backgroundColor}`);
                }
            }

            if (type === 'product_table' || type === 'items_table') {
                if (props.backgroundColor) {
                    styles.push(`background-color: ${props.backgroundColor}`);
                }
                if (props.borderColor && props.borderWidth) {
                    styles.push(`border: ${props.borderWidth}px solid ${props.borderColor}`);
                }
            }

            // Appliquer les styles
            if (styles.length > 0) {
                console.log('Applying styles for element', elementId, ':', styles);
                // Ajouter au CSS global avec un sélecteur spécifique
                const cssRule = `[data-element-id="${elementId}"] { ${styles.join('; ')}; }`;
                try {
                    styleSheet.sheet.insertRule(cssRule, styleSheet.sheet.cssRules.length);
                } catch (e) {
                    console.error('Erreur application style:', e);
                }

                // Appliquer aussi directement si l'élément est un DIV
                const domElement = document.querySelector(`[data-element-id="${elementId}"]`);
                if (domElement) {
                    console.log('Found DOM element for', elementId, 'applying inline styles');
                    styles.forEach(style => {
                        const [prop, value] = style.split(':').map(s => s.trim());
                        if (prop && value) {
                            domElement.style[prop.replace(/-([a-z])/g, g => g[1].toUpperCase())] = value;
                        }
                    });
                } else {
                    console.log('DOM element not found for', elementId);
                }
            }
        }

        // Intercepter le chargement du template et appliquer les styles
        const originalFetch = window.fetch;
        window.fetch = function(...args) {
            return originalFetch.apply(this, args).then(response => {
                // Vérifier si c'est un appel AJAX pour charger un template
                if (args[0] && (args[0].includes('load_builtin_template') || args[0].includes('load_template'))) {
                    return response.clone().json().then(data => {
                        if (data.success && data.data && data.data.template && data.data.template.elements) {
                            console.log('Template loaded:', data.data.template);
                            // Appliquer les styles après un délai pour laisser React rendre
                            setTimeout(() => {
                                console.log('Applying styles to template elements...');
                                applyTemplateStyles(data.data.template);
                            }, 500);
                        }
                        return response;
                    }).catch(() => response);
                }
                return response;
            });
        };

        // Surveiller les mutations du DOM pour appliquer les styles aux nouveaux éléments
        const observer = new MutationObserver((mutations) => {
            mutations.forEach(mutation => {
                mutation.addedNodes.forEach(node => {
                    if (node.nodeType === 1 && node.classList && node.classList.contains('canvas-element')) {
                        // C'est un élément du canvas - essayer de récupérer ses données
                        const elementId = node.getAttribute('data-element-id') || node.id;
                        if (elementId) {
                            // Essayer de trouver les données de cet élément dans les données du template
                            if (window.currentTemplateData && window.currentTemplateData.elements) {
                                const element = window.currentTemplateData.elements.find(e => e.id === elementId);
                                if (element) {
                                    applyElementStyles(node, element);
                                }
                            }
                        }
                    }
                });
            });
        });

        observer.observe(canvas, {
            childList: true,
            subtree: true
        });
    }

    // Initialiser quand le DOM est prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeCanvasStyleInjector);
    } else {
        initializeCanvasStyleInjector();
    }
})();

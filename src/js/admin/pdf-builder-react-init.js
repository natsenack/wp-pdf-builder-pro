/**
 * PDF Builder Pro V2 - React Initialization Script
 *
 * Ce script initialise l'éditeur React une fois que tous les bundles sont chargés
 */

(function() {
    'use strict';

    

    // Attendre que les bundles React soient chargés
    function waitForReactBundle(maxRetries = 50) {
        let retries = 0;

        function checkAndInit() {
            retries++;

            if (retries > maxRetries) {
                
                return;
            }

            const container = document.getElementById('pdf-builder-react-root');

            if (!container) {
                
                setTimeout(checkAndInit, 100);
                return;
            }

            // Vérifier que pdfBuilderReact est disponible
            if (typeof window.pdfBuilderReact === 'undefined' || typeof window.pdfBuilderReact.initPDFBuilderReact !== 'function') {
                
                setTimeout(checkAndInit, 100);
                return;
            }

            

            try {
                // Initialiser l'éditeur React
                const success = window.pdfBuilderReact.initPDFBuilderReact('pdf-builder-react-root');

                if (success) {
                    
                    // Appliquer les paramètres de bordure du canvas après l'initialisation
                    setTimeout(function() {
                        applyCanvasBorderSettings();
                    }, 1000);
                } else {
                    
                }
            } catch (error) {
                
            }
        }

        // Commencer à vérifier
        checkAndInit();
    }

    // Fonction pour appliquer les paramètres de bordure du canvas
    function applyCanvasBorderSettings() {
        try {
            // Récupérer les paramètres de bordure depuis les données localisées
            const canvasSettings = window.pdfBuilderCanvasSettings || {};
            const borderColor = canvasSettings.border_color || '#cccccc';
            const borderWidth = canvasSettings.border_width || 1;

            console.log('[PDF Builder] Application des paramètres de bordure:', {
                borderColor: borderColor,
                borderWidth: borderWidth,
                canvasSettings: canvasSettings
            });

            // Chercher directement les éléments canvas par tagName
            const canvasElements = document.getElementsByTagName('canvas');
            
            if (canvasElements.length > 0) {
                console.log(`[PDF Builder] ${canvasElements.length} élément(s) canvas trouvé(s) par tagName`);
                
                // Appliquer les styles au premier élément canvas trouvé
                const canvasElement = canvasElements[0];
                canvasElement.style.borderColor = borderColor;
                canvasElement.style.borderWidth = borderWidth + 'px';
                canvasElement.style.borderStyle = 'solid';
                canvasElement.dataset.borderApplied = 'true';
                
                console.log('[PDF Builder] ✅ Paramètres de bordure appliqués avec succès au canvas:', {
                    tagName: canvasElement.tagName,
                    borderColor: borderColor,
                    borderWidth: borderWidth + 'px'
                });
            } else {
                console.warn('[PDF Builder] Aucun élément canvas trouvé par tagName');
                
                // Fallback: chercher dans tous les éléments avec "canvas" dans le nom
                const allElements = document.querySelectorAll('*');
                const potentialCanvasElements = [];
                
                allElements.forEach(el => {
                    const classList = el.className || '';
                    const id = el.id || '';
                    const tagName = el.tagName || '';
                    
                    if (classList.toLowerCase().includes('canvas') || 
                        id.toLowerCase().includes('canvas') || 
                        tagName.toLowerCase() === 'canvas') {
                        potentialCanvasElements.push({
                            element: el,
                            className: classList,
                            id: id,
                            tagName: tagName
                        });
                    }
                });
                
                if (potentialCanvasElements.length > 0) {
                    console.log('[PDF Builder] Fallback: éléments potentiels trouvés:', potentialCanvasElements.length);
                    
                    // Appliquer au premier élément trouvé
                    const firstElement = potentialCanvasElements[0].element;
                    firstElement.style.borderColor = borderColor;
                    firstElement.style.borderWidth = borderWidth + 'px';
                    firstElement.style.borderStyle = 'solid';
                    firstElement.dataset.borderApplied = 'true';
                    
                    console.log('[PDF Builder] ✅ Paramètres appliqués via fallback');
                } else {
                    console.log('[PDF Builder] Aucun élément canvas trouvé');
                }
            }

        } catch (error) {
            console.error('[PDF Builder] Erreur lors de l\'application des paramètres de bordure:', error);
        }
    }

    // Attendre que le document soit prêt
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            
            waitForReactBundle();
        });
    } else {
        
        waitForReactBundle();
    }

})();


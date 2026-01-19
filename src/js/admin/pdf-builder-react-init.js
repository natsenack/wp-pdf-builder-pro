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
                    // Essayer immédiatement, puis toutes les 500ms pendant 10 secondes
                    applyCanvasBorderSettings();
                    
                    let retryCount = 0;
                    const maxRetries = 20; // 20 * 500ms = 10 secondes
                    
                    const retryInterval = setInterval(function() {
                        retryCount++;
                        console.log(`[PDF Builder] 🔄 Tentative ${retryCount}/${maxRetries} d'application des bordures`);
                        
                        if (applyCanvasBorderSettings() || retryCount >= maxRetries) {
                            clearInterval(retryInterval);
                            console.log('[PDF Builder] ⏹️ Arrêt des tentatives d\'application des bordures');
                        }
                    }, 500);
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
        console.log('[PDF Builder] 🔍 Début de applyCanvasBorderSettings');

        try {
            // Vérifier si les paramètres sont disponibles
            if (typeof window.pdfBuilderCanvasSettings === 'undefined') {
                console.error('[PDF Builder] ❌ pdfBuilderCanvasSettings n\'est pas défini');
                return false;
            }

            // Récupérer les paramètres de bordure depuis les données localisées
            const canvasSettings = window.pdfBuilderCanvasSettings || {};
            const borderColor = canvasSettings.border_color || '#cccccc';
            const borderWidth = canvasSettings.border_width || 1;

            console.log('[PDF Builder] 📋 Paramètres récupérés:', {
                borderColor: borderColor,
                borderWidth: borderWidth,
                canvasSettings: canvasSettings,
                allSettings: window.pdfBuilderCanvasSettings
            });

            // Fonction pour appliquer les styles à un canvas
            function applyStylesToCanvas(canvasElement) {
                console.log('[PDF Builder] 🎨 Application des styles au canvas:', canvasElement);
                canvasElement.style.borderColor = borderColor;
                canvasElement.style.borderWidth = borderWidth + 'px';
                canvasElement.style.borderStyle = 'solid';
                canvasElement.dataset.borderApplied = 'true';
                console.log('[PDF Builder] ✅ Styles appliqués:', {
                    borderColor: canvasElement.style.borderColor,
                    borderWidth: canvasElement.style.borderWidth,
                    borderStyle: canvasElement.style.borderStyle
                });
            }

            // Chercher directement les éléments canvas par tagName
            const canvasElements = document.getElementsByTagName('canvas');
            console.log('[PDF Builder] 🔍 Recherche de canvas par tagName, trouvé:', canvasElements.length);

            if (canvasElements.length > 0) {
                console.log('[PDF Builder] 🎯 Canvas trouvé(s) par tagName:', canvasElements.length);
                // Appliquer les styles à tous les canvas trouvés
                for (let i = 0; i < canvasElements.length; i++) {
                    applyStylesToCanvas(canvasElements[i]);
                }
            } else {
                console.warn('[PDF Builder] ⚠️ Aucun élément canvas trouvé par tagName');

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

                console.log('[PDF Builder] 🔍 Fallback - éléments potentiels trouvés:', potentialCanvasElements.length);

                if (potentialCanvasElements.length > 0) {
                    console.log('[PDF Builder] 📋 Éléments potentiels:', potentialCanvasElements);
                    // Appliquer au premier élément trouvé
                    applyStylesToCanvas(potentialCanvasElements[0].element);
                } else {
                    console.log('[PDF Builder] ❌ Aucun élément canvas trouvé');

                    // Utiliser un MutationObserver pour surveiller l'apparition de canvas
                    console.log('[PDF Builder] 👀 Mise en place du MutationObserver');
                    const observer = new MutationObserver((mutations) => {
                        mutations.forEach((mutation) => {
                            if (mutation.type === 'childList') {
                                const addedNodes = Array.from(mutation.addedNodes);
                                addedNodes.forEach(node => {
                                    if (node.tagName === 'CANVAS' || (node.querySelector && node.querySelector('canvas'))) {
                                        console.log('[PDF Builder] 🎯 Canvas détecté via MutationObserver:', node);
                                        const canvas = node.tagName === 'CANVAS' ? node : node.querySelector('canvas');
                                        if (canvas && !canvas.dataset.borderApplied) {
                                            applyStylesToCanvas(canvas);
                                            observer.disconnect();
                                        }
                                    }
                                });
                            }
                        });
                    });

                    observer.observe(document.body, {
                        childList: true,
                        subtree: true
                    });

                    // Timeout de sécurité
                    setTimeout(() => {
                        observer.disconnect();
                        console.log('[PDF Builder] ⏰ MutationObserver arrêté après timeout');
                    }, 10000);
                }
            }

        } catch (error) {
            console.error('[PDF Builder] 💥 Erreur lors de l\'application des paramètres de bordure:', error);
            return false;
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


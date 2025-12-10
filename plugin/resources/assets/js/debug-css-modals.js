/**
 * PDF Builder Pro - Debug CSS Modals
 * Logs JavaScript pour déboguer les styles CSS des modales
 */

(function() {
    'use strict';

    // Configuration du debug
    const DEBUG_ENABLED = window.location.search.includes('debug=css') ||
                         (typeof window.PDF_BUILDER_CONFIG !== 'undefined' && window.PDF_BUILDER_CONFIG.debug);

    function log(...args) {
        if (DEBUG_ENABLED) {
            console.log('🎨 [CSS MODALS DEBUG]:', ...args);
        }
    }

    function warn(...args) {
        if (DEBUG_ENABLED) {
            console.warn('⚠️ [CSS MODALS DEBUG]:', ...args);
        }
    }

    function error(...args) {
        if (DEBUG_ENABLED) {
            console.error('💥 [CSS MODALS DEBUG]:', ...args);
        }
    }

    // Fonction de diagnostic complet des styles CSS
    function diagnoseCSSIssues() {
        log('🔍 === DIAGNOSTIC COMPLET CSS ===');

        // 1. Vérifier si les fichiers CSS sont chargés
        log('📄 Vérification des fichiers CSS chargés...');
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"]');
        let contenuCssLoaded = false;
        cssLinks.forEach(link => {
            if (link.href && link.href.includes('contenu-settings.css')) {
                contenuCssLoaded = true;
                log('✅ contenu-settings.css trouvé:', link.href);
            }
        });
        if (!contenuCssLoaded) {
            warn('❌ contenu-settings.css NON trouvé dans les liens CSS chargés');
        }

        // 2. Vérifier les éléments des modales
        log('🔍 Vérification des éléments des modales...');
        const modalIds = ['cache-size-modal', 'cache-transients-modal', 'cache-status-modal'];

        modalIds.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal) {
                warn(`❌ Modale ${modalId} non trouvée dans le DOM`);
                return;
            }

            log(`✅ Modale ${modalId} trouvée`);

            // Vérifier le contexte
            const contenuSection = document.querySelector('.contenu-settings');
            const isInContext = contenuSection && contenuSection.contains(modal);
            log(`📍 Contexte .contenu-settings: ${isInContext ? '✅ DANS le contexte' : '❌ HORS du contexte'}`);

            // Vérifier les classes
            log(`🏷️ Classes de la modale: ${modal.className}`);

            // Vérifier l'overlay
            const overlay = modal.querySelector('.cache-modal-overlay');
            if (overlay) {
                log(`✅ Overlay trouvé avec classes: ${overlay.className}`);
                log(`🎨 Styles calculés overlay:`, getComputedStyles(overlay, ['display', 'position', 'background-color', 'z-index']));
            } else {
                warn(`❌ Overlay non trouvé pour ${modalId}`);
            }

            // Vérifier le container
            const container = modal.querySelector('.cache-modal-container');
            if (container) {
                log(`✅ Container trouvé`);
                log(`🎨 Styles calculés container:`, getComputedStyles(container, ['background-color', 'border-radius', 'box-shadow', 'max-width']));
            } else {
                warn(`❌ Container non trouvé pour ${modalId}`);
            }
        });

        // 3. Vérifier les règles CSS dans les stylesheets
        log('📋 Vérification des règles CSS...');
        for (let i = 0; i < document.styleSheets.length; i++) {
            try {
                const sheet = document.styleSheets[i];
                if (sheet.href && sheet.href.includes('contenu-settings.css')) {
                    log('✅ Feuille de style contenu-settings.css accessible');
                    const rules = sheet.cssRules || sheet.rules;
                    let modalRulesCount = 0;
                    for (let j = 0; j < rules.length; j++) {
                        const rule = rules[j];
                        if (rule.selectorText && rule.selectorText.includes('cache-modal')) {
                            modalRulesCount++;
                            log(`📝 Règle trouvée: ${rule.selectorText}`);
                        }
                    }
                    log(`📊 Nombre de règles cache-modal trouvées: ${modalRulesCount}`);
                }
            } catch (e) {
                log('⚠️ Impossible d\'accéder à une feuille de style (CORS ou autre):', e.message);
            }
        }

        log('🔍 === FIN DIAGNOSTIC CSS ===');
    }

    // Fonction pour vérifier si un élément a des styles CSS appliqués
    function checkElementStyles(element, selector, expectedStyles = {}) {
        if (!element) {
            warn(`Élément non trouvé: ${selector}`);
            return false;
        }

        log(`Vérification des styles pour: ${selector}`);
        log(`Élément trouvé:`, element);

        // Vérifier la visibilité et les dimensions
        const rect = element.getBoundingClientRect();
        log(`Dimensions: ${rect.width}x${rect.height}, visible: ${rect.width > 0 && rect.height > 0}`);

        // Obtenir les styles calculés pour tous les propriétés attendues
        const allProperties = Object.keys(expectedStyles);
        const computed = getComputedStyles(element, allProperties);

        log(`Styles calculés:`, computed);

        // Vérifier les styles attendus
        let allStylesCorrect = true;
        Object.entries(expectedStyles).forEach(([prop, expectedValue]) => {
            const actualValue = computed[prop];
            if (actualValue !== expectedValue) {
                warn(`Style incorrect - ${prop}: attendu "${expectedValue}", obtenu "${actualValue}"`);
                allStylesCorrect = false;
            } else {
                log(`✅ Style correct - ${prop}: "${actualValue}"`);
            }
        });

        return allStylesCorrect;
    }

    // Fonction pour déboguer les modales de cache
    function debugCacheModals() {
        log('🔍 Début du débogage des modales de cache');

        const modalIds = ['cache-size-modal', 'cache-transients-modal', 'cache-status-modal'];

        modalIds.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal) {
                warn(`Modale non trouvée: ${modalId}`);
                return;
            }

            log(`\n📋 Analyse de la modale: ${modalId}`);

            // Vérifier la modale principale (doit être masquée par défaut)
            checkElementStyles(modal, `#${modalId}`, {
                'display': 'none' // La modale racine est toujours masquée
            });

            // Vérifier l'overlay
            const overlay = modal.querySelector('.cache-modal-overlay');
            checkElementStyles(overlay, `#${modalId} .cache-modal-overlay`, {
                'position': 'fixed',
                'display': 'none', // Masquée par défaut
                'background-color': 'rgba(0, 0, 0, 0.5)',
                'z-index': '10000'
            });

            // Vérifier le container
            const container = modal.querySelector('.cache-modal-container');
            checkElementStyles(container, `#${modalId} .cache-modal-container`, {
                'background-color': 'rgb(255, 255, 255)',
                'border-radius': '8px',
                'box-shadow': 'rgba(0, 0, 0, 0.3) 0px 4px 20px 0px', // Format retourné par getComputedStyle
                'max-width': '800px'
            });

            // Vérifier le header
            const header = modal.querySelector('.cache-modal-header');
            checkElementStyles(header, `#${modalId} .cache-modal-header`, {
                'background-color': 'rgb(248, 249, 250)',
                'padding': '20px 24px'
            });

            // Vérifier le body
            const body = modal.querySelector('.cache-modal-body');
            checkElementStyles(body, `#${modalId} .cache-modal-body`, {
                'padding': '24px'
            });

            // Vérifier les éléments de contenu spécifiques
            const detailsGrid = modal.querySelector('.cache-details-grid');
            if (detailsGrid) {
                checkElementStyles(detailsGrid, `#${modalId} .cache-details-grid`, {
                    'display': 'grid',
                    'grid-template-columns': '1fr 1fr'
                });
            }

            const folderCards = modal.querySelectorAll('.cache-folder-card');
            folderCards.forEach((card, index) => {
                checkElementStyles(card, `#${modalId} .cache-folder-card:nth-child(${index + 1})`, {
                    'background-color': 'rgb(248, 249, 250)',
                    'padding': '15px',
                    'border-radius': '8px'
                });
            });

            const statsGrid = modal.querySelector('.cache-stats-grid');
            if (statsGrid) {
                checkElementStyles(statsGrid, `#${modalId} .cache-stats-grid`, {
                    'display': 'grid'
                });
            }

            // Vérifier la spécificité CSS - test manuel
            log(`🔍 Vérification de la spécificité CSS pour ${modalId}`);
            checkCSSSpecificity(modalId);
        });

        log('✅ Fin du débogage des modales de cache');
    }

    // Fonction pour vérifier la spécificité CSS
    function checkCSSSpecificity(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        // Tester si nos styles avec .contenu-settings s'appliquent
        const testElement = modal.querySelector('.cache-modal-container');
        if (testElement) {
            const computed = getComputedStyles(testElement, ['background-color', 'border-radius', 'box-shadow']);

            log(`🎨 Styles appliqués sur .cache-modal-container:`, computed);

            // Vérifier si c'est dans le contexte .contenu-settings
            const contenuSection = document.querySelector('.contenu-settings');
            if (contenuSection && contenuSection.contains(modal)) {
                log(`✅ Modale dans le contexte .contenu-settings - styles devraient s'appliquer`);
            } else {
                warn(`❌ Modale HORS du contexte .contenu-settings - styles peuvent ne pas s'appliquer`);
                log(`   Contexte actuel:`, modal.closest('.settings-section')?.className || 'inconnu');
            }
        }
    }

    // Fonction pour surveiller les changements de styles
    function monitorStyleChanges() {
        log('👀 Surveillance des changements de styles activée');

        // Observer les changements sur les overlays des modales
        const overlays = document.querySelectorAll('.cache-modal-overlay');
        overlays.forEach((overlay, index) => {
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const modalId = overlay.closest('.cache-modal')?.id || `modal-${index}`;
                        log(`🔄 Changement de classe sur overlay ${modalId}:`, overlay.className);
                        log(`   - Display:`, getComputedStyles(overlay, ['display']));
                    }
                });
            });

            observer.observe(overlay, {
                attributes: true,
                attributeFilter: ['class']
            });
        });
    }

    // Fonction pour tester l'ouverture/fermeture des modales
    function testModalToggle() {
        log('🧪 Test d\'ouverture/fermeture des modales');

        const modalIds = ['cache-size-modal', 'cache-transients-modal', 'cache-status-modal'];

        modalIds.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal) return;

            const overlay = modal.querySelector('.cache-modal-overlay');
            if (!overlay) return;

            // Tester l'ouverture
            log(`Ouverture de la modale: ${modalId}`);
            overlay.classList.add('active');
            setTimeout(() => {
                checkElementStyles(overlay, `#${modalId} .cache-modal-overlay`, {
                    'display': 'flex'
                });

                // Tester la fermeture
                log(`Fermeture de la modale: ${modalId}`);
                overlay.classList.remove('active');
                setTimeout(() => {
                    checkElementStyles(overlay, `#${modalId} .cache-modal-overlay`, {
                        'display': 'none'
                    });
                }, 100);
            }, 100);
        });
    }

    // Initialisation
    function init() {
        if (!DEBUG_ENABLED) {
            return;
        }

        log('🚀 Initialisation du débogage CSS des modales');

        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    diagnoseCSSIssues(); // Diagnostic complet en premier
                    debugCacheModals();
                    monitorStyleChanges();
                    testModalToggle();
                    checkModalContext();
                }, 2000); // Attendre plus longtemps pour que tous les scripts soient chargés
            });
        } else {
            setTimeout(() => {
                diagnoseCSSIssues(); // Diagnostic complet en premier
                debugCacheModals();
                monitorStyleChanges();
                testModalToggle();
                checkModalContext();
            }, 2000);
        }

        // Ajouter un bouton de debug dans la console
        window.debugCacheModals = debugCacheModals;
        window.testModalToggle = testModalToggle;
        window.checkModalContext = checkModalContext;
        window.diagnoseCSSIssues = diagnoseCSSIssues; // Nouvelle fonction de diagnostic

        log('💡 Commandes disponibles dans la console:');
        log('   - debugCacheModals() : Analyser les styles des modales');
        log('   - testModalToggle() : Tester ouverture/fermeture');
        log('   - checkModalContext() : Vérifier le contexte des modales');
        log('   - diagnoseCSSIssues() : Diagnostic complet CSS (NOUVEAU)'); // Nouvelle commande
    }

    // Fonction pour vérifier le contexte des modales
    function checkModalContext() {
        log('🔍 Vérification du contexte des modales de cache');

        const modalIds = ['cache-size-modal', 'cache-transients-modal', 'cache-status-modal'];

        modalIds.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal) {
                warn(`Modale non trouvée: ${modalId}`);
                return;
            }

            // Vérifier si la modale est dans l'onglet contenu
            const contenuSection = document.querySelector('.contenu-settings');
            const isInContenu = contenuSection && contenuSection.contains(modal);

            log(`📍 Contexte de ${modalId}:`);
            log(`   - Dans .contenu-settings: ${isInContenu ? '✅ OUI' : '❌ NON'}`);
            log(`   - Section parente:`, modal.closest('.settings-section')?.id || 'inconnue');

            if (!isInContenu) {
                warn(`⚠️ La modale ${modalId} n'est pas dans le contexte .contenu-settings`);
                warn(`   Les styles .contenu-settings .cache-modal-* ne s'appliqueront pas`);
            } else {
                log(`✅ La modale ${modalId} est dans le bon contexte - styles applicables`);
            }
        });
    }

    // Démarrer le débogage
    init();

})();
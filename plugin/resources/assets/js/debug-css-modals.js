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

    // Fonction pour vérifier les styles calculés d'un élément
    function getComputedStyles(element, properties) {
        if (!element) return {};

        const computed = window.getComputedStyle(element);
        const result = {};

        properties.forEach(prop => {
            result[prop] = computed.getPropertyValue(prop);
        });

        return result;
    }

    // Fonction de diagnostic complet des styles CSS
    function diagnoseCSSIssues() {
        log('🔍 === DIAGNOSTIC COMPLET CSS ===');

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
                if (sheet.href && (sheet.href.includes('contenu-settings.css') || sheet.href.includes('modals-contenu.css'))) {
                    log(`✅ Feuille de style accessible: ${sheet.href.includes('contenu-settings.css') ? 'contenu-settings.css' : 'modals-contenu.css'}`);
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
                'background-color': 'rgba(0, 0, 0, 0.6)', // Mis à jour pour les nouvelles améliorations
                'z-index': '10000'
            });

            // Vérifier le container
            const container = modal.querySelector('.cache-modal-container');
            checkElementStyles(container, `#${modalId} .cache-modal-container`, {
                'background-color': 'rgb(255, 255, 255)',
                'border-radius': '12px', // Mis à jour pour les nouvelles améliorations
                'box-shadow': 'rgba(0, 0, 0, 0.3) 0px 8px 32px 0px', // Mis à jour pour les nouvelles améliorations
                'max-width': '900px' // Mis à jour pour les nouvelles améliorations
            });

            // Vérifier le header
            const header = modal.querySelector('.cache-modal-header');
            checkElementStyles(header, `#${modalId} .cache-modal-header`, {
                'background-color': 'rgb(248, 249, 250)',
                'padding': '24px 28px' // Mis à jour pour les nouvelles améliorations
            });

            // Vérifier le body
            const body = modal.querySelector('.cache-modal-body');
            checkElementStyles(body, `#${modalId} .cache-modal-body`, {
                'padding': '28px' // Mis à jour pour les nouvelles améliorations
            });

            // Vérifier les éléments de contenu spécifiques
            const detailsGrid = modal.querySelector('.cache-details-grid');
            if (detailsGrid) {
                checkElementStyles(detailsGrid, `#${modalId} .cache-details-grid`, {
                    'display': 'grid',
                    'grid-template-columns': 'repeat(auto-fit, minmax(280px, 1fr))' // Mis à jour pour les nouvelles améliorations
                });
            }

            const folderCards = modal.querySelectorAll('.cache-folder-card');
            folderCards.forEach((card, index) => {
                checkElementStyles(card, `#${modalId} .cache-folder-card:nth-child(${index + 1})`, {
                    'background-color': 'rgba(0, 0, 0, 0)',
                    'padding': '24px', // Mis à jour pour les améliorations modernes (24px au lieu de 20px)
                    'border-radius': '12px' // Mis à jour pour les améliorations modernes (12px au lieu de 10px)
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

    // Fonction pour forcer l'ouverture d'une modale pour test
    function forceOpenModal(modalId) {
        log(`🔧 Forçage de l'ouverture de la modale: ${modalId}`);

        const modal = document.getElementById(modalId);
        if (!modal) {
            error(`❌ Modale ${modalId} non trouvée`);
            return;
        }

        // Ajouter la classe active à l'overlay
        const overlay = modal.querySelector('.cache-modal-overlay');
        if (overlay) {
            overlay.classList.add('active');
            log(`✅ Classe 'active' ajoutée à l'overlay de ${modalId}`);
        } else {
            error(`❌ Overlay non trouvé pour ${modalId}`);
        }

        // Lancer le diagnostic après un court délai
        setTimeout(() => {
            diagnoseCSSIssues();
        }, 100);
    }

    // Fonction pour analyser en profondeur le DOM et les styles
    function deepDOMAnalysis() {
        log('🔬 === ANALYSE PROFONDE DOM & CSS ===');

        // 1. Vérifier la structure DOM complète
        log('📋 Analyse de la structure DOM...');

        const contenuSection = document.querySelector('.contenu-settings');
        if (!contenuSection) {
            error('❌ Section .contenu-settings non trouvée !');
            return;
        }

        log('✅ Section .contenu-settings trouvée');

        // Lister tous les enfants de .contenu-settings
        const children = contenuSection.children;
        log(`📝 Enfants directs de .contenu-settings (${children.length}):`);
        for (let i = 0; i < children.length; i++) {
            const child = children[i];
            log(`  ${i + 1}. ${child.tagName}${child.id ? '#' + child.id : ''}${child.className ? '.' + child.className.replace(/\s+/g, '.') : ''}`);
        }

        // 2. Chercher les modales dans tout le document
        log('🔍 Recherche des modales dans le document...');
        const allModals = document.querySelectorAll('.cache-modal');
        log(`📊 Nombre total de modales trouvées: ${allModals.length}`);

        allModals.forEach((modal, index) => {
            log(`📍 Modale ${index + 1}: #${modal.id}`);
            log(`   - Parent immédiat: ${modal.parentElement?.tagName}${modal.parentElement?.id ? '#' + modal.parentElement.id : ''}${modal.parentElement?.className ? '.' + modal.parentElement.className.replace(/\s+/g, '.') : ''}`);
            log(`   - Dans .contenu-settings: ${contenuSection.contains(modal) ? '✅ OUI' : '❌ NON'}`);

            // Vérifier la hiérarchie complète
            let current = modal.parentElement;
            let depth = 1;
            let hierarchy = [`${modal.tagName}#${modal.id}`];
            while (current && depth < 10) {
                hierarchy.push(`${current.tagName}${current.id ? '#' + current.id : ''}${current.className ? '.' + current.className.replace(/\s+/g, '.') : ''}`);
                if (current === contenuSection) {
                    log(`   - Hiérarchie jusqu'à .contenu-settings (${depth} niveaux): ${hierarchy.reverse().join(' > ')}`);
                    break;
                }
                current = current.parentElement;
                depth++;
            }
        });

        // 3. Analyser les styles CSS calculés
        log('🎨 Analyse des styles CSS calculés...');

        const modalIds = ['cache-size-modal', 'cache-transients-modal', 'cache-status-modal'];
        modalIds.forEach(modalId => {
            const modal = document.getElementById(modalId);
            if (!modal) {
                error(`❌ Modale ${modalId} non trouvée`);
                return;
            }

            const overlay = modal.querySelector('.cache-modal-overlay');
            const container = modal.querySelector('.cache-modal-container');

            log(`📊 Styles pour ${modalId}:`);

            if (overlay) {
                const overlayStyles = getComputedStyles(overlay, ['display', 'position', 'background-color', 'z-index', 'opacity']);
                log(`   Overlay: display=${overlayStyles.display}, position=${overlayStyles.position}, z-index=${overlayStyles.zIndex}`);
            }

            if (container) {
                const containerStyles = getComputedStyles(container, ['background-color', 'border-radius', 'box-shadow', 'width', 'height']);
                log(`   Container: background=${containerStyles['background-color']}, width=${containerStyles.width}`);
            }
        });

        // 4. Vérifier les règles CSS spécifiques
        log('📝 Vérification des règles CSS spécifiques...');
        for (let i = 0; i < document.styleSheets.length; i++) {
            try {
                const sheet = document.styleSheets[i];
                if (sheet.href && sheet.href.includes('contenu-settings.css')) {
                    log('✅ Feuille contenu-settings.css trouvée et accessible');
                    const rules = sheet.cssRules || sheet.rules;

                    const relevantRules = [];
                    for (let j = 0; j < rules.length; j++) {
                        const rule = rules[j];
                        if (rule.selectorText && (
                            rule.selectorText.includes('.cache-modal') ||
                            rule.selectorText.includes('.contenu-settings')
                        )) {
                            relevantRules.push({
                                selector: rule.selectorText,
                                cssText: rule.cssText.substring(0, 100) + (rule.cssText.length > 100 ? '...' : '')
                            });
                        }
                    }

                    log(`📋 Règles CSS pertinentes trouvées (${relevantRules.length}):`);
                    relevantRules.forEach((rule, index) => {
                        log(`   ${index + 1}. ${rule.selector}`);
                        log(`      ${rule.cssText}`);
                    });
                }
            } catch (e) {
                log(`⚠️ Impossible d'accéder à une feuille de style: ${e.message}`);
            }
        }

        log('🔬 === FIN ANALYSE PROFONDE ===');
    }

    // Fonction pour forcer la visibilité des modales pour debug
    function forceModalVisibility(modalId) {
        log(`🔧 Forçage de la visibilité pour: ${modalId}`);

        const modal = document.getElementById(modalId);
        if (!modal) {
            error(`❌ Modale ${modalId} non trouvée`);
            return;
        }

        const overlay = modal.querySelector('.cache-modal-overlay');
        const container = modal.querySelector('.cache-modal-container');

        if (overlay) {
            // Forcer les styles inline pour debug
            overlay.style.display = 'flex !important';
            overlay.style.position = 'fixed !important';
            overlay.style.top = '0 !important';
            overlay.style.left = '0 !important';
            overlay.style.right = '0 !important';
            overlay.style.bottom = '0 !important';
            overlay.style.width = '100vw !important';
            overlay.style.height = '100vh !important';
            overlay.style.background = 'rgba(0, 0, 0, 0.8) !important';
            overlay.style.zIndex = '99999 !important';
            overlay.style.alignItems = 'center !important';
            overlay.style.justifyContent = 'center !important';
            log(`✅ Styles forcés sur overlay de ${modalId}`);
        }

        if (container) {
            container.style.background = 'red !important';
            container.style.border = '5px solid yellow !important';
            container.style.width = '600px !important';
            container.style.height = '400px !important';
            container.style.position = 'relative !important';
            log(`✅ Styles de debug appliqués sur container de ${modalId}`);
        }

        // Vérifier les dimensions après forçage
        setTimeout(() => {
            if (overlay) {
                const rect = overlay.getBoundingClientRect();
                log(`📏 Dimensions overlay après forçage: ${rect.width}x${rect.height}, visible: ${rect.width > 0 && rect.height > 0}`);
            }
            if (container) {
                const rect = container.getBoundingClientRect();
                log(`📏 Dimensions container après forçage: ${rect.width}x${rect.height}, visible: ${rect.width > 0 && rect.height > 0}`);
            }
        }, 100);
    }

    // Fonction pour vérifier si le CSS est chargé dans la page
    function checkCSSLoading() {
        log('🔍 Vérification du chargement des CSS...');

        const cssLinks = document.querySelectorAll('link[rel="stylesheet"]');
        let contenuCssFound = false;
        let contenuCssLoaded = false;

        cssLinks.forEach(link => {
            if (link.href && link.href.includes('contenu-settings.css')) {
                contenuCssFound = true;
                log('✅ contenu-settings.css trouvé dans le DOM:', link.href);

                // Vérifier si la feuille de style est chargée
                try {
                    if (link.sheet) {
                        contenuCssLoaded = true;
                        log('✅ contenu-settings.css chargé et accessible');

                        // Compter les règles CSS
                        const rules = link.sheet.cssRules || link.sheet.rules;
                        log(`📊 Nombre de règles CSS dans contenu-settings.css: ${rules.length}`);

                        // Chercher les règles de modales
                        let modalRules = 0;
                        for (let i = 0; i < rules.length; i++) {
                            const rule = rules[i];
                            if (rule.selectorText && rule.selectorText.includes('cache-modal')) {
                                modalRules++;
                            }
                        }
                        log(`🎯 Règles cache-modal trouvées: ${modalRules}`);

                    } else {
                        log('⚠️ contenu-settings.css trouvé mais feuille de style non accessible (CORS ou chargement en cours)');
                    }
                } catch (e) {
                    log('⚠️ Erreur lors de l\'accès à contenu-settings.css:', e.message);
                }
            }
        });

        if (!contenuCssFound) {
            error('❌ contenu-settings.css NON trouvé dans le DOM !');
            log('📋 Liste de tous les CSS chargés:');
            cssLinks.forEach((link, index) => {
                log(`  ${index + 1}. ${link.href}`);
            });
        }

        return contenuCssLoaded;
    }

    // Exposer la fonction de vérification CSS
    window.checkCSSLoading = checkCSSLoading;

    // Démarrer le débogage
    init();

})();
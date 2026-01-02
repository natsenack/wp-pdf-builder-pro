// PDF Builder - Force Complete Reload
// Script pour vérifier et forcer le rechargement des CSS

(function($) {
    'use strict';

    // Configuration
    const config = {
        checkInterval: 30000, // 30 secondes
        maxChecks: 10,
        excludedFiles: [
            // Réactivé: 'contenu-settings.css',
            // Réactivé: 'modals-contenu.css'
        ]
    };

    // État du système
    let checkCount = 0;
    let isChecking = false;

    // Fonctions utilitaires
    function log(message, type = 'info') {
        const prefix = '🔍 PDF Builder:';
        switch(type) {
            case 'error':
                console.error(`${prefix} ${message}`);
                break;
            case 'warning':
                console.warn(`${prefix} ${message}`);
                break;
            default:
                console.log(`${prefix} ${message}`);
        }
    }

    // Vérifier si un fichier est exclu
    function isExcluded(filename) {
        return config.excludedFiles.some(excluded => filename.includes(excluded));
    }

    // Vérifier le déploiement CSS avec logs détaillés
    function checkCSSDeployment() {
        if (isChecking) return;
        isChecking = true;

        log('Vérification du déploiement CSS...');

        // Trouver tous les liens CSS du plugin
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"][href*="wp-pdf-builder-pro"]');

        log(`🔍 ${cssLinks.length} fichiers CSS PDF Builder trouvés`);

        cssLinks.forEach((link, index) => {
            const href = link.getAttribute('href');
            if (!href) return;

            // Extraire le nom du fichier
            const filename = href.split('/').pop().split('?')[0];

            log(`📄 CSS ${index + 1}/${cssLinks.length}: ${filename}`);

            if (isExcluded(filename)) {
                log(`⏭️ Vérification CSS temporairement désactivée pour: ${filename}`);
                return;
            }

            // Vérifier l'état actuel du lien
            const sheet = link.sheet;
            if (sheet) {
                log(`✅ CSS chargé en mémoire: ${filename} (${sheet.cssRules ? sheet.cssRules.length : 'N/A'} règles)`);
            } else {
                log(`⏳ CSS en cours de chargement: ${filename}`);
            }

            // Vérifier si le fichier est accessible
            fetch(href, { method: 'HEAD' })
                .then(response => {
                    if (!response.ok) {
                        log(`❌ CSS manquant ou inaccessible: ${filename} (status: ${response.status})`, 'error');
                    } else {
                        log(`✅ CSS OK: ${filename} (status: ${response.status}, size: ${response.headers.get('content-length') || 'unknown'})`);

                        // Vérifier les règles CSS si disponibles
                        if (link.sheet && link.sheet.cssRules) {
                            const ruleCount = link.sheet.cssRules.length;
                            log(`📏 ${filename}: ${ruleCount} règles CSS chargées`);

                            // Log des premières règles pour debug
                            if (ruleCount > 0 && ruleCount <= 5) {
                                for (let i = 0; i < Math.min(ruleCount, 3); i++) {
                                    const rule = link.sheet.cssRules[i];
                                    if (rule.selectorText) {
                                        log(`   ↳ Règle ${i + 1}: ${rule.selectorText}`);
                                    }
                                }
                            }
                        }
                    }
                })
                .catch(error => {
                    log(`❌ Erreur vérification CSS ${filename}: ${error.message}`, 'error');
                });
        });

        // Vérifier spécifiquement le CSS React Editor
        const reactCSS = document.querySelector('link[href*="pdf-builder-react.css"]');
        if (reactCSS) {
            log('🎨 CSS React Editor trouvé dans le DOM');
            if (reactCSS.sheet) {
                const ruleCount = reactCSS.sheet.cssRules ? reactCSS.sheet.cssRules.length : 0;
                log(`📊 CSS React: ${ruleCount} règles chargées`);

                // Vérifier quelques règles spécifiques
                if (ruleCount > 0) {
                    const rootVars = Array.from(reactCSS.sheet.cssRules).filter(rule =>
                        rule.selectorText === ':root' && rule.cssText.includes('--breakpoint')
                    );
                    if (rootVars.length > 0) {
                        log('✅ Variables CSS React chargées (--breakpoint*)');
                    }

                    // Vérifier si les styles sont appliqués au container React
                    const reactContainer = document.getElementById('pdf-builder-react-root');
                    if (reactContainer) {
                        const computedStyle = window.getComputedStyle(reactContainer);
                        log(`🎯 Container React trouvé - display: ${computedStyle.display}, visibility: ${computedStyle.visibility}`);
                    } else {
                        log('⚠️ Container React (#pdf-builder-react-root) non trouvé dans le DOM');
                    }
                }
            } else {
                log('⏳ CSS React en cours de chargement...');
            }
        } else {
            log('❌ CSS React Editor NON trouvé dans le DOM', 'error');
        }
    }

    // Forcer le rechargement complet des CSS
    window.pdfBuilderForceReload = function() {
        log('Forçage du rechargement COMPLET des assets...');

        // Supprimer TOUS les liens CSS du plugin (même ceux avec cache busting)
        const cssLinks = document.querySelectorAll('link[rel="stylesheet"][href*="wp-pdf-builder-pro"]');
        cssLinks.forEach(link => {
            const href = link.getAttribute('href');
            if (href) {
                log(`Suppression CSS: ${href.split('/').pop()}`);
                link.remove();
            }
        });

        // Supprimer TOUS les scripts JS du plugin
        const jsScripts = document.querySelectorAll('script[src*="wp-pdf-builder-pro"]');
        jsScripts.forEach(script => {
            const src = script.getAttribute('src');
            if (src) {
                log(`Suppression JS: ${src.split('/').pop()}`);
                script.remove();
            }
        });

        // Forcer le rechargement de la page
        setTimeout(() => {
            window.location.reload(true);
        }, 100);
    };

    // Ajouter les fonctions globales
    window.pdfBuilderCheckCSS = checkCSSDeployment;
    window.pdfBuilderForceReload = window.pdfBuilderForceReload;

    // Démarrer les vérifications automatiques
    $(document).ready(function() {
        log('CSS Debug Tools chargées:');
        log('   - pdfBuilderCheckCSS() : Vérifier déploiement');
        log('   - pdfBuilderForceReload() : Forcer rechargement complet');

        // Ajouter des logs de chargement en temps réel pour les CSS
        setupCSSLoadMonitoring();

        // Vérification initiale
        setTimeout(checkCSSDeployment, 2000);

        // Vérifications périodiques
        setInterval(() => {
            checkCount++;
            if (checkCount <= config.maxChecks) {
                checkCSSDeployment();
            }
        }, config.checkInterval);
    });

    // Surveiller le chargement des CSS en temps réel
    function setupCSSLoadMonitoring() {
        log('🔍 Mise en place de la surveillance CSS en temps réel...');

        // Écouter les nouveaux liens CSS ajoutés dynamiquement
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.tagName === 'LINK' &&
                        node.getAttribute('rel') === 'stylesheet' &&
                        node.getAttribute('href') &&
                        node.getAttribute('href').includes('wp-pdf-builder-pro')) {

                        const filename = node.getAttribute('href').split('/').pop().split('?')[0];
                        log(`🆕 Nouveau CSS détecté: ${filename}`);

                        // Ajouter les event listeners
                        addCSSLoadListeners(node);
                    }
                });
            });
        });

        observer.observe(document.head, { childList: true });

        // Ajouter les listeners aux CSS existants
        const existingCSS = document.querySelectorAll('link[rel="stylesheet"][href*="wp-pdf-builder-pro"]');
        existingCSS.forEach(link => addCSSLoadListeners(link));
    }

    // Ajouter les event listeners de chargement aux liens CSS
    function addCSSLoadListeners(link) {
        const href = link.getAttribute('href');
        if (!href) return;

        const filename = href.split('/').pop().split('?')[0];

        // Événement de chargement réussi
        link.addEventListener('load', function() {
            log(`✅ CSS chargé avec succès: ${filename}`);
            if (link.sheet) {
                const ruleCount = link.sheet.cssRules ? link.sheet.cssRules.length : 0;
                log(`📊 ${filename}: ${ruleCount} règles CSS disponibles`);
            }
        });

        // Événement d'erreur de chargement
        link.addEventListener('error', function() {
            log(`❌ Échec du chargement CSS: ${filename}`, 'error');
        });

        // Vérifier l'état initial
        if (link.sheet) {
            log(`📋 CSS déjà chargé: ${filename}`);
        } else {
            log(`⏳ CSS en attente de chargement: ${filename}`);
        }
    }

})(jQuery);
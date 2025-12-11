/**
 * Paramètres PDF Builder Pro - Navigation des onglets
 * Version: 2.1.0 - Code nettoyé
 * Date: 2025-12-11
 */

// Protection contre les rechargements multiples
if (typeof window.pdfBuilderTabsInitialized === 'undefined') {
    window.pdfBuilderTabsInitialized = true;

// Définition de PDF_BUILDER_CONFIG si elle n'existe pas
if (typeof window.PDF_BUILDER_CONFIG === 'undefined') {
    window.PDF_BUILDER_CONFIG = {
        debug: false,
        ajaxurl: '',
        nonce: ''
    };
}

    // Système de navigation des onglets
    function initTabs() {
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');

        if (!tabsContainer || !contentContainer) {
            return;
        }

        // Gestionnaire de clic pour les onglets
        tabsContainer.addEventListener('click', function(e) {
            const tab = e.target.closest('.nav-tab');
            if (!tab) return;

            e.preventDefault();

            const tabId = tab.getAttribute('data-tab');
            if (!tabId) return;

            // Désactiver tous les onglets
            tabsContainer.querySelectorAll('.nav-tab').forEach(t => {
                t.classList.remove('nav-tab-active');
                t.setAttribute('aria-selected', 'false');
            });

            // Masquer tous les contenus
            contentContainer.querySelectorAll('.tab-content').forEach(content => {
                content.style.display = 'none';
                content.setAttribute('aria-hidden', 'true');
            });

            // Activer l'onglet cliqué
            tab.classList.add('nav-tab-active');
            tab.setAttribute('aria-selected', 'true');

            // Afficher le contenu de l'onglet sélectionné
            const targetContent = document.getElementById(tabId);
            if (targetContent) {
                targetContent.style.display = 'block';
                targetContent.setAttribute('aria-hidden', 'false');
            }

            // Sauvegarder l'onglet actif dans localStorage
            try {
                localStorage.setItem('pdfBuilderActiveTab', tabId);
            } catch (e) {
                // Ignore les erreurs localStorage
            }

            // Mettre à jour l'URL sans recharger la page
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location);
                url.searchParams.set('tab', tabId.replace('tab-', ''));
                window.history.replaceState(null, null, url);
            }
        });

        // Restaurer l'onglet actif depuis localStorage
        try {
            const savedTab = localStorage.getItem('pdfBuilderActiveTab');
            if (savedTab) {
                const savedTabElement = document.querySelector(`[data-tab="${savedTab}"]`);
                if (savedTabElement) {
                    savedTabElement.click();
                }
            }
        } catch (e) {
            // Ignore les erreurs localStorage
        }
    }

    // Initialiser au chargement du DOM
    document.addEventListener('DOMContentLoaded', initTabs);

    // Bouton de sauvegarde flottant
    function initSaveButton() {
        // Vérifier si on est sur la page de paramètres
        if (typeof window !== 'undefined' && window.location && window.location.href.indexOf('page=pdf-builder-settings') === -1) {
            return;
        }

        if (document.querySelector('.pdf-builder-save-initialized')) {
            return;
        }

        const saveBtn = document.getElementById('pdf-builder-save-floating-btn');
        const floatingContainer = document.getElementById('pdf-builder-save-floating');

        if (saveBtn && floatingContainer) {
            saveBtn.addEventListener('click', function(e) {
                e.preventDefault();

                // Trouver le formulaire principal
                let mainForm = document.querySelector('form[action="options.php"]');

                if (!mainForm) {
                    // Essayer de trouver un formulaire contenant les champs de template
                    const templateFields = document.querySelectorAll('select[name^="pdf_builder_order_status_templates"]');
                    if (templateFields.length > 0) {
                        mainForm = templateFields[0].closest('form');
                    }
                }

                if (!mainForm) {
                    // Utiliser le premier formulaire trouvé
                    mainForm = document.querySelector('form');
                }

                if (mainForm) {
                    // Ajouter un indicateur pour éviter les doubles soumissions
                    if (!mainForm.querySelector('.pdf-builder-save-initialized')) {
                        const indicator = document.createElement('input');
                        indicator.type = 'hidden';
                        indicator.name = 'pdf_builder_save_triggered';
                        indicator.value = '1';
                        indicator.className = 'pdf-builder-save-initialized';
                        mainForm.appendChild(indicator);
                    }

                    mainForm.submit();
                }
            });

            // Marquer comme initialisé
            floatingContainer.classList.add('pdf-builder-save-initialized');
        } else {
            // Retry après 1 seconde si les éléments ne sont pas encore disponibles
            setTimeout(initSaveButton, 1000);
        }
    }

    // Initialiser le bouton de sauvegarde
    document.addEventListener('DOMContentLoaded', function() {
        initSaveButton();
    });

// Fin de la protection contre les rechargements multiples
}
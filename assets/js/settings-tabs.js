/**
 * Paramètres PDF Builder Pro - Navigation des onglets (Version Debug)
 */

(function() {
    'use strict';

    console.log('PDF Builder: settings-tabs.js VERSION DEBUG CHARGÉ');

    // Configuration globale
    const PDF_BUILDER_CONFIG = typeof window.PDF_BUILDER_CONFIG !== 'undefined' ? window.PDF_BUILDER_CONFIG : {};

    // Fonction de debug pour vérifier les éléments DOM
    function debugElements() {
        const container = document.getElementById('pdf-builder-settings-wrapper');
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');
        
        console.log('PDF Builder - DIAGNOSTIC DES ÉLÉMENTS DOM:');
        console.log('  - Container principal:', container ? '✅ Trouvé' : '❌ Non trouvé');
        console.log('  - Container onglets:', tabsContainer ? '✅ Trouvé' : '❌ Non trouvé');
        console.log('  - Container contenu:', contentContainer ? '✅ Trouvé' : '❌ Non trouvé');
        
        if (tabsContainer) {
            const tabButtons = tabsContainer.querySelectorAll('.nav-tab');
            console.log('  - Boutons onglets trouvés:', tabButtons.length);
            
            tabButtons.forEach(function(btn, index) {
                console.log('    ' + (index + 1) + '. ' + btn.textContent.trim() + ' (data-tab: ' + btn.getAttribute('data-tab') + ')');
            });
        }
        
        if (contentContainer) {
            const tabContents = contentContainer.querySelectorAll('.tab-content');
            console.log('  - Contenus onglets trouvés:', tabContents.length);
            
            tabContents.forEach(function(content, index) {
                console.log('    ' + (index + 1) + '. #' + content.id + ' - ' + (content.classList.contains('active') ? 'ACTIF' : 'inactif'));
            });
        }
    }

    // Fonction principale de switch d'onglet avec logs détaillés
    function switchTab(tabId) {
        console.log('PDF Builder - SWITCH TAB: Début du changement vers "' + tabId + '"');
        
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
        
        console.log('PDF Builder - Éléments trouvés: ' + tabButtons.length + ' boutons, ' + tabContents.length + ' contenus');
        
        // Désactiver tous les onglets
        console.log('PDF Builder - Désactivation de tous les onglets...');
        tabButtons.forEach(function(btn, index) {
            btn.classList.remove('nav-tab-active');
            console.log('  ' + (index + 1) + '. "' + btn.textContent.trim() + '" désactivé');
        });
        
        tabContents.forEach(function(content, index) {
            content.classList.remove('active');
            console.log('  ' + (index + 1) + '. "#' + content.id + '" désactivé');
        });
        
        // Activer l'onglet cible
        console.log('PDF Builder - Activation de l\'onglet "' + tabId + '"...');
        const targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
        const targetContent = document.getElementById(tabId);
        
        if (targetBtn) {
            targetBtn.classList.add('nav-tab-active');
            console.log('  ✅ Bouton trouvé et activé: "' + targetBtn.textContent.trim() + '"');
        } else {
            console.error('  ❌ ERREUR: Bouton avec data-tab="' + tabId + '" non trouvé!');
        }
        
        if (targetContent) {
            targetContent.classList.add('active');
            console.log('  ✅ Contenu trouvé et activé: "#' + targetContent.id + '"');
        } else {
            console.error('  ❌ ERREUR: Contenu avec id="' + tabId + '" non trouvé!');
        }
        
        // Sauvegarder en localStorage
        try {
            localStorage.setItem('pdf_builder_active_tab', tabId);
            console.log('PDF Builder - Onglet "' + tabId + '" sauvegardé en localStorage');
        } catch(e) {
            console.warn('PDF Builder - Impossible de sauvegarder en localStorage:', e.message);
        }
        
        console.log('PDF Builder - SWITCH TAB: Terminé pour "' + tabId + '"');
    }

    // Gestionnaire d'événements avec logs
    function handleTabClick(event) {
        console.log('PDF Builder - CLIQUE DÉTECTÉ sur:', event.target);
        console.log('PDF Builder - Attributs de l\'élément cliqué:', {
            'data-tab': event.target.getAttribute('data-tab'),
            'href': event.target.getAttribute('href'),
            'class': event.target.className,
            'tagName': event.target.tagName
        });
        
        event.preventDefault();
        event.stopPropagation();
        
        const tabId = event.target.getAttribute('data-tab');
        if (!tabId) {
            console.error('PDF Builder - ERREUR: Aucun attribut data-tab trouvé sur l\'élément cliqué!');
            return;
        }
        
        console.log('PDF Builder - LANCEMENT du switch vers "' + tabId + '"');
        switchTab(tabId);
    }

    // Initialisation principale
    function initializeTabs() {
        console.log('PDF Builder - INITIALISATION DES ONGLETS');
        
        // Vérifier que les éléments DOM existent
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');
        
        if (!tabsContainer) {
            console.error('PDF Builder - ERREUR CRITIQUE: Container #pdf-builder-tabs non trouvé!');
            return false;
        }
        
        if (!contentContainer) {
            console.error('PDF Builder - ERREUR CRITIQUE: Container #pdf-builder-tab-content non trouvé!');
            return false;
        }
        
        // Vérifier les onglets
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
        
        if (tabButtons.length === 0) {
            console.error('PDF Builder - ERREUR CRITIQUE: Aucun bouton d\'onglet trouvé!');
            return false;
        }
        
        if (tabContents.length === 0) {
            console.error('PDF Builder - ERREUR CRITIQUE: Aucun contenu d\'onglet trouvé!');
            return false;
        }
        
        console.log('PDF Builder - ' + tabButtons.length + ' onglets et ' + tabContents.length + ' contenus trouvés');
        
        // Attacher les événements de clic
        console.log('PDF Builder - Attribution des événements de clic...');
        tabButtons.forEach(function(btn, index) {
            // Supprimer les anciens événements pour éviter les doublons
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);
            
            newBtn.addEventListener('click', handleTabClick);
            console.log('  ' + (index + 1) + '. Événement attaché à "' + newBtn.textContent.trim() + '"');
        });
        
        // Restaurer l'onglet sauvegardé
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab && document.getElementById(savedTab)) {
                console.log('PDF Builder - Restauration de l\'onglet sauvegardé: "' + savedTab + '"');
                switchTab(savedTab);
            } else {
                console.log('PDF Builder - Aucun onglet sauvegardé, activation du premier onglet');
                const firstTab = tabButtons[0].getAttribute('data-tab');
                switchTab(firstTab);
            }
        } catch(e) {
            console.warn('PDF Builder - Erreur lors de la restauration de l\'onglet:', e.message);
            // Fallback: activer le premier onglet
            const firstTab = tabButtons[0].getAttribute('data-tab');
            switchTab(firstTab);
        }
        
        console.log('PDF Builder - ONGLETS INITIALISÉS AVEC SUCCÈS');
        return true;
    }

    // Démarrage quand le DOM est prêt
    document.addEventListener('DOMContentLoaded', function() {
        console.log('PDF Builder - DOM CONTENT LOADED - Démarrage de l\'initialisation');
        
        // Attendre un peu pour s'assurer que tous les scripts sont chargés
        setTimeout(function() {
            console.log('PDF Builder - TIMEOUT - Lancement de l\'initialisation différée');
            
            // Logs de diagnostic
            debugElements();
            
            // Initialisation
            const success = initializeTabs();
            
            if (!success) {
                console.error('PDF Builder - ÉCHEC DE L\'INITIALISATION - Vérifiez les éléments DOM');
            }
        }, 100);
    });

    // Logs supplémentaires pour le debugging
    console.log('PDF Builder - Script settings-tabs.js chargé jusqu\'à la fin');
})();

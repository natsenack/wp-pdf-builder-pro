/**
 * Paramètres PDF Builder Pro - Navigation des onglets (Version simplifiée)
 */

(function() {
    'use strict';

    const DEBUG = true; // Forcé à true pour diagnostic

    if (DEBUG) {
        console.log('🔥 PDF Builder: settings-tabs.js chargé en mode DEBUG');
    }

    // Fonction de diagnostic
    function logDiagnostic(message, data = null) {
        if (DEBUG) {
            console.log('📍 ' + message, data || '');
        }
    }

    // Fonction principale de switch d'onglet
    function switchTab(tabId) {
        logDiagnostic('SWITCH TAB: Début vers "' + tabId + '"');
        
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');

        logDiagnostic('Éléments trouvés: ' + tabButtons.length + ' boutons, ' + tabContents.length + ' contenus');
        
        // Désactiver tous les onglets
        tabButtons.forEach(function(btn) {
            btn.classList.remove('nav-tab-active');
            btn.setAttribute('aria-selected', 'false');
        });
        
        tabContents.forEach(function(content) {
            content.classList.remove('active');
        });
        
        // Activer l'onglet cible
        const targetBtn = document.querySelector('[data-tab="' + tabId + '"]');
        let targetContent = document.getElementById(tabId);
        if (!targetContent) {
            targetContent = document.getElementById('tab-' + tabId);
        }
        
        if (targetBtn) {
            targetBtn.classList.add('nav-tab-active');
            targetBtn.setAttribute('aria-selected', 'true');
            logDiagnostic('✅ Bouton activé: "' + targetBtn.textContent.trim() + '"');
        } else {
            logDiagnostic('❌ ERREUR: Bouton non trouvé pour "' + tabId + '"');
        }
        
        if (targetContent) {
            targetContent.classList.add('active');
            logDiagnostic('✅ Contenu activé: "' + targetContent.id + '"');
        } else {
            logDiagnostic('❌ ERREUR: Contenu non trouvé pour "' + tabId + '"');
        }
        
        // Sauvegarder en localStorage
        try {
            localStorage.setItem('pdf_builder_active_tab', tabId);
            logDiagnostic('Onglet sauvegardé en localStorage: "' + tabId + '"');
        } catch(e) {
            logDiagnostic('Impossible de sauvegarder en localStorage:', e.message);
        }
        
        // Déclencher événement personnalisé
        document.dispatchEvent(new CustomEvent('pdfBuilderTabChanged', {
            detail: { tabId: tabId, timestamp: Date.now() }
        }));
        
        logDiagnostic('SWITCH TAB: Terminé pour "' + tabId + '"');
    }

    // Gestionnaire d'événements
    function handleTabClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const tabId = event.currentTarget.getAttribute('data-tab');
        if (!tabId) {
            logDiagnostic('❌ ERREUR: Aucun attribut data-tab trouvé');
            return;
        }
        
        logDiagnostic('CLIC DÉTECTÉ vers "' + tabId + '"');
        switchTab(tabId);
    }

    // Initialisation
    function initializeTabs() {
        logDiagnostic('INITIALISATION DES ONGLETS');
        
        // Vérifier que les éléments DOM existent
        const tabsContainer = document.getElementById('pdf-builder-tabs');
        const contentContainer = document.getElementById('pdf-builder-tab-content');
        
        if (!tabsContainer) {
            logDiagnostic('❌ ERREUR CRITIQUE: Container #pdf-builder-tabs non trouvé!');
            return false;
        }
        
        if (!contentContainer) {
            logDiagnostic('❌ ERREUR CRITIQUE: Container #pdf-builder-tab-content non trouvé!');
            return false;
        }
        
        const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
        const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
        
        if (tabButtons.length === 0) {
            logDiagnostic('❌ ERREUR CRITIQUE: Aucun bouton d\'onglet trouvé!');
            return false;
        }
        
        if (tabContents.length === 0) {
            logDiagnostic('❌ ERREUR CRITIQUE: Aucun contenu d\'onglet trouvé!');
            return false;
        }
        
        logDiagnostic(tabButtons.length + ' onglets et ' + tabContents.length + ' contenus trouvés');
        
        // Attacher les événements de clic
        tabButtons.forEach(function(btn) {
            btn.removeEventListener('click', handleTabClick);
            btn.addEventListener('click', handleTabClick);
            logDiagnostic('Event listener ajouté pour: ' + btn.getAttribute('data-tab'));
        });
        
        // Restaurer l'onglet sauvegardé
        try {
            const savedTab = localStorage.getItem('pdf_builder_active_tab');
            if (savedTab && document.getElementById(savedTab)) {
                logDiagnostic('Restauration de l\'onglet sauvegardé: "' + savedTab + '"');
                setTimeout(function() {
                    switchTab(savedTab);
                }, 100);
            } else {
                logDiagnostic('Activation du premier onglet');
                setTimeout(function() {
                    switchTab(tabButtons[0].getAttribute('data-tab'));
                }, 100);
            }
        } catch(e) {
            logDiagnostic('Erreur lors de la restauration:', e.message);
            setTimeout(function() {
                switchTab(tabButtons[0].getAttribute('data-tab'));
            }, 100);
        }
        
        logDiagnostic('ONGLETS INITIALISÉS AVEC SUCCÈS');
        return true;
    }

    // Démarrage quand le DOM est prêt
    document.addEventListener('DOMContentLoaded', function() {
        logDiagnostic('DOM CONTENT LOADED - Initialisation différée');
        
        setTimeout(function() {
            initializeTabs();
        }, 50);
    });

    logDiagnostic('Script settings-tabs.js chargé');
})();

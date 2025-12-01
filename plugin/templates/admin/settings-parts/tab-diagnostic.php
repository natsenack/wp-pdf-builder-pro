<?php
/**
 * Diagnostic script for tab functionality - VERSION AMÉLIORÉE
 * This will help identify why tabs are not switching properly and debug persistence
 */
echo "<!-- PDF Builder Tab Diagnostic Loaded -->";
?>

<script>
// Diagnostic for tab functionality - VERSION AMÉLIORÉE
if (window.pdfBuilderDebugSettings?.javascript) {
    console.log('=== PDF BUILDER TAB DIAGNOSTIC V2 ===');
}

// Fonctions de diagnostic améliorées
window.tabDiagnostic = {
    logState: function(context = '') {
        if (window.pdfBuilderDebugSettings?.javascript) {
            console.log(`[TAB DIAG] ${context} - État actuel:`);
            console.log(`[TAB DIAG] Hash URL:`, window.location.hash);
            console.log(`[TAB DIAG] localStorage tab:`, localStorage.getItem('pdf-builder-active-tab'));
            console.log(`[TAB DIAG] localStorage time:`, localStorage.getItem('pdf-builder-active-tab-time'));
            console.log(`[TAB DIAG] Active tab element:`, document.querySelector('.nav-tab-active'));
            console.log(`[TAB DIAG] Active content element:`, document.querySelector('.tab-content.active'));
            
            const activeTab = document.querySelector('.nav-tab-active');
            if (activeTab) {
                console.log(`[TAB DIAG] Active tab href:`, activeTab.getAttribute('href'));
                console.log(`[TAB DIAG] Active tab text:`, activeTab.textContent.trim());
            }
            
            console.log(`[TAB DIAG] All tabs:`, Array.from(document.querySelectorAll('.nav-tab')).map(tab => ({
                href: tab.getAttribute('href'),
                isActive: tab.classList.contains('nav-tab-active'),
                visible: tab.offsetWidth > 0 && tab.offsetHeight > 0
            })));
        }
    },

    forceRestoreTab: function() {
        const savedTab = localStorage.getItem('pdf-builder-active-tab');
        if (savedTab && window.setActiveTab) {
            console.log(`[TAB DIAG] Force restoration to tab: ${savedTab}`);
            window.setActiveTab(savedTab, false);
            return true;
        }
        return false;
    },

    testTabSwitching: function() {
        const tabs = ['general', 'licence', 'systeme', 'acces', 'securite', 'pdf', 'contenu', 'templates', 'developpeur'];
        tabs.forEach((tabId, index) => {
            setTimeout(() => {
                if (window.setActiveTab) {
                    console.log(`[TAB DIAG] Testing switch to: ${tabId}`);
                    window.setActiveTab(tabId);
                    this.logState(`After switch to ${tabId}`);
                }
            }, index * 500);
        });
    }
};

// Check if DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (window.pdfBuilderDebugSettings?.javascript) {
        console.log('DOM loaded, running enhanced diagnostic...');
        window.tabDiagnostic.logState('DOMContentLoaded');
    }

    // Vérifier l'état initial des onglets
    const navTabs = document.querySelectorAll('.nav-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (window.pdfBuilderDebugSettings?.javascript) {
        console.log(`[TAB DIAG] Found ${navTabs.length} nav tabs`);
        console.log(`[TAB DIAG] Found ${tabContents.length} tab contents`);
    }

    // Vérifier si initializeTabs existe et a été appelée
    if (window.pdfBuilderDebugSettings?.javascript) {
        console.log('[TAB DIAG] initializeTabs function exists:', typeof window.initializeTabs === 'function');
        console.log('[TAB DIAG] setActiveTab function exists:', typeof window.setActiveTab === 'function');
    }

    // Délai pour laisser le temps à initializeTabs de s'exécuter
    setTimeout(function() {
        if (window.pdfBuilderDebugSettings?.javascript) {
            console.log('[TAB DIAG] State after initializeTabs delay:');
            window.tabDiagnostic.logState('After initializeTabs');
        }

        // Vérifier si l'onglet actif correspond à ce qui est sauvegardé
        const savedTab = localStorage.getItem('pdf-builder-active-tab');
        const activeTab = document.querySelector('.nav-tab-active');
        const activeContent = document.querySelector('.tab-content.active');
        
        if (savedTab && activeTab) {
            const activeTabId = activeTab.getAttribute('href').substring(1);
            if (activeTabId !== savedTab) {
                console.warn(`[TAB DIAG] MISMATCH! Saved tab: ${savedTab}, Active tab: ${activeTabId}`);
                console.log('[TAB DIAG] Attempting to fix the mismatch...');
                window.tabDiagnostic.forceRestoreTab();
            } else {
                console.log(`[TAB DIAG] SUCCESS! Tab persistence working correctly: ${savedTab}`);
            }
        } else if (!savedTab && activeTab) {
            console.log('[TAB DIAG] No saved tab, but active tab found. This is normal on first load.');
        } else if (savedTab && !activeTab) {
            console.error('[TAB DIAG] PROBLEM! Saved tab exists but no active tab found!');
            window.tabDiagnostic.forceRestoreTab();
        }

        // Test des raccourcis clavier pour diagnostic
        if (window.pdfBuilderDebugSettings?.javascript) {
            console.log('[TAB DIAG] Testing keyboard shortcuts...');
            console.log('[TAB DIAG] Tapez Alt+1 à Alt+9 pour tester la navigation');
        }

        // Écouter les raccourcis clavier pour navigation rapide
        document.addEventListener('keydown', function(e) {
            if (e.altKey && e.key >= '1' && e.key <= '9') {
                e.preventDefault();
                const tabIndex = parseInt(e.key) - 1;
                const tabs = Array.from(document.querySelectorAll('.nav-tab'));
                if (tabs[tabIndex]) {
                    const tabId = tabs[tabIndex].getAttribute('href').substring(1);
                    if (window.setActiveTab) {
                        window.setActiveTab(tabId);
                        console.log(`[TAB DIAG] Alt+${e.key} switched to: ${tabId}`);
                    }
                }
            }
        });

        if (window.pdfBuilderDebugSettings?.javascript) {
            console.log('=== DIAGNOSTIC COMPLETE ===');
            console.log('[TAB DIAG] Available diagnostic functions:');
            console.log('- window.tabDiagnostic.logState()');
            console.log('- window.tabDiagnostic.forceRestoreTab()');
            console.log('- window.tabDiagnostic.testTabSwitching()');
        }
    }, 1000); // Délai de 1 seconde pour laisser le temps à tout de s'initialiser

    // Écouter les changements de hash pour diagnostic
    window.addEventListener('hashchange', function() {
        if (window.pdfBuilderDebugSettings?.javascript) {
            console.log('[TAB DIAG] Hash changed to:', window.location.hash);
            window.tabDiagnostic.logState('HashChange');
        }
    });

    // Écouter la fermeture de la page pour diagnostic
    window.addEventListener('beforeunload', function() {
        if (window.pdfBuilderDebugSettings?.javascript) {
            console.log('[TAB DIAG] Page about to unload, saving current state...');
            const activeTab = document.querySelector('.nav-tab-active');
            if (activeTab) {
                const tabId = activeTab.getAttribute('href').substring(1);
                localStorage.setItem('pdf-builder-active-tab', tabId);
                localStorage.setItem('pdf-builder-active-tab-time', Date.now());
                console.log(`[TAB DIAG] Saved tab state before unload: ${tabId}`);
            }
        }
    });
});

// Exposer les fonctions de diagnostic globalement
window.tabDiagnostic = window.tabDiagnostic || {};
</script>
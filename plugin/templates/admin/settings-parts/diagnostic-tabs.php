<?php
/**
 * Diagnostic de navigation des onglets - PDF Builder
 * Ce fichier peut être inclus dans settings-main.php pour diagnostic
 */
if (!defined('ABSPATH')) exit('Direct access forbidden');
?>

<div id="pdf-builder-diagnostic" style="background: #f0f0f0; border: 2px solid #0073aa; padding: 15px; margin: 10px 0; font-family: monospace; font-size: 12px;">
    <h3>🔍 DIAGNOSTIC PDF BUILDER - Navigation des Onglets</h3>
    
    <div style="margin-bottom: 10px;">
        <strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?><br>
        <strong>URL actuelle:</strong> <?php echo esc_url($_SERVER['REQUEST_URI'] ?? ''); ?><br>
        <strong>Debug activé:</strong> <?php echo isset($settings['pdf_builder_debug_javascript']) && $settings['pdf_builder_debug_javascript'] ? 'OUI' : 'NON'; ?>
    </div>
    
    <div style="background: white; padding: 10px; border: 1px solid #ccc; margin: 10px 0;">
        <h4>📋 CHECKLIST DE DIAGNOSTIC</h4>
        <ul id="diagnostic-list" style="list-style: none; padding: 0;">
            <li id="check-1">⏳ Vérification 1: Chargement du script...</li>
            <li id="check-2">⏳ Vérification 2: Éléments DOM...</li>
            <li id="check-3">⏳ Vérification 3: Événements de clic...</li>
            <li id="check-4">⏳ Vérification 4: Navigation fonctionnelle...</li>
        </ul>
    </div>
    
    <div style="background: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 10px 0;">
        <h4>🧪 TESTS DE NAVIGATION</h4>
        <button id="test-manual" style="padding: 5px 10px; margin: 2px;">Test Manuel - Système</button>
        <button id="test-ajax" style="padding: 5px 10px; margin: 2px;">Test Auto - 3 onglets</button>
        <button id="clear-storage" style="padding: 5px 10px; margin: 2px;">Effacer localStorage</button>
        <button id="reset-tabs" style="padding: 5px 10px; margin: 2px;">Reset Onglets</button>
    </div>
    
    <div style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 10px; margin: 10px 0;">
        <h4>📊 INFORMATIONS ACTUELLES</h4>
        <div id="current-info">Chargement...</div>
    </div>
</div>

<script>
(function() {
    'use strict';
    
    const DEBUG = true;
    
    function logDiagnostic(message, data = null) {
        if (DEBUG) {
            console.log('🔍 DIAGNOSTIC:', message, data || '');
        }
    }
    
    function updateCheck(id, status, message) {
        const element = document.getElementById(id);
        if (element) {
            element.innerHTML = status + ' ' + message;
        }
    }
    
    function updateInfo() {
        const info = document.getElementById('current-info');
        if (!info) return;
        
        const activeTab = document.querySelector('#pdf-builder-tabs .nav-tab-active');
        const activeContent = document.querySelector('#pdf-builder-tab-content .tab-content.active');
        
        info.innerHTML = `
            <strong>Onglet actif:</strong> ${activeTab ? activeTab.textContent.trim() + ' (' + activeTab.getAttribute('data-tab') + ')' : 'Aucun'}<br>
            <strong>Contenu actif:</strong> ${activeContent ? activeContent.id : 'Aucun'}<br>
            <strong>localStorage:</strong> ${localStorage.getItem('pdf_builder_active_tab') || 'Aucun'}<br>
            <strong>Scripts chargés:</strong> ${typeof PDF_BUILDER_CONFIG !== 'undefined' ? 'PDF_BUILDER_CONFIG OK' : 'PDF_BUILDER_CONFIG manquant'}<br>
            <strong>Initialisé:</strong> ${window.PDF_BUILDER_TABS_INITIALIZED ? 'OUI' : 'NON'}
        `;
    }
    
    // Tests de navigation
    function runTests() {
        logDiagnostic('🧪 Lancement des tests de navigation');
        
        // Test 1: Chargement du script
        if (typeof PDF_BUILDER_CONFIG !== 'undefined') {
            updateCheck('check-1', '✅', 'Script PDF_BUILDER_CONFIG chargé');
        } else {
            updateCheck('check-1', '❌', 'Script PDF_BUILDER_CONFIG NON chargé');
        }
        
        // Test 2: Éléments DOM
        const tabs = document.querySelectorAll('#pdf-builder-tabs .nav-tab').length;
        const contents = document.querySelectorAll('#pdf-builder-tab-content .tab-content').length;
        updateCheck('check-2', tabs > 0 && contents > 0 ? '✅' : '❌', `${tabs} onglets, ${contents} contenus trouvés`);
        
        // Test 3: Événements
        const testTab = document.querySelector('[data-tab="systeme"]');
        if (testTab) {
            updateCheck('check-3', '✅', 'Onglet de test "système" trouvé');
        } else {
            updateCheck('check-3', '❌', 'Onglet de test "système" NON trouvé');
        }
        
        // Test 4: Navigation
        if (window.PDF_BUILDER_TABS_INITIALIZED) {
            updateCheck('check-4', '✅', 'Navigation initialisée');
        } else {
            updateCheck('check-4', '⚠️', 'Navigation NON initialisée');
        }
        
        updateInfo();
    }
    
    // Gestionnaires de tests
    document.addEventListener('DOMContentLoaded', function() {
        logDiagnostic('DOM chargé, initialisation du diagnostic');
        
        // Test manuel
        const testManual = document.getElementById('test-manual');
        if (testManual) {
            testManual.addEventListener('click', function() {
                logDiagnostic('🧪 Test manuel: clic sur "Système"');
                const systemTab = document.querySelector('[data-tab="systeme"]');
                if (systemTab) {
                    systemTab.click();
                    setTimeout(updateInfo, 100);
                }
            });
        }
        
        // Test automatique
        const testAjax = document.getElementById('test-ajax');
        if (testAjax) {
            testAjax.addEventListener('click', function() {
                logDiagnostic('🧪 Test automatique: navigation 3 onglets');
                const tabs = ['licence', 'securite', 'general'];
                let i = 0;
                
                function nextTab() {
                    if (i < tabs.length) {
                        const tab = document.querySelector('[data-tab="' + tabs[i] + '"]');
                        if (tab) {
                            tab.click();
                            setTimeout(function() {
                                updateInfo();
                                i++;
                                setTimeout(nextTab, 500);
                            }, 100);
                        }
                    }
                }
                
                nextTab();
            });
        }
        
        // Effacer localStorage
        const clearStorage = document.getElementById('clear-storage');
        if (clearStorage) {
            clearStorage.addEventListener('click', function() {
                logDiagnostic('🧪 Effacement localStorage');
                localStorage.removeItem('pdf_builder_active_tab');
                updateInfo();
            });
        }
        
        // Reset onglets
        const resetTabs = document.getElementById('reset-tabs');
        if (resetTabs) {
            resetTabs.addEventListener('click', function() {
                logDiagnostic('🧪 Reset onglets');
                const allButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
                const allContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
                
                allButtons.forEach(btn => btn.classList.remove('nav-tab-active'));
                allContents.forEach(content => content.classList.remove('active'));
                
                if (allButtons[0]) {
                    allButtons[0].classList.add('nav-tab-active');
                    allButtons[0].setAttribute('aria-selected', 'true');
                }
                if (allContents[0]) {
                    allContents[0].classList.add('active');
                }
                
                localStorage.removeItem('pdf_builder_active_tab');
                updateInfo();
            });
        }
        
        // Surveillance des changements
        setTimeout(runTests, 500);
        setTimeout(runTests, 1500);
        
        // Surveillance continue
        document.addEventListener('pdfBuilderTabChanged', function(e) {
            logDiagnostic('🔄 Changement d\'onglet détecté:', e.detail);
            setTimeout(updateInfo, 50);
        });
    });
    
})();
</script>

<style>
#pdf-builder-diagnostic {
    position: fixed;
    top: 10px;
    right: 10px;
    width: 350px;
    max-height: 80vh;
    overflow-y: auto;
    z-index: 999999;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

#pdf-builder-diagnostic button {
    background: #0073aa;
    color: white;
    border: none;
    border-radius: 3px;
    cursor: pointer;
    font-size: 11px;
}

#pdf-builder-diagnostic button:hover {
    background: #005a87;
}

@media (max-width: 768px) {
    #pdf-builder-diagnostic {
        position: relative;
        width: 100%;
        top: auto;
        right: auto;
    }
}
</style>
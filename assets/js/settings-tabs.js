/**
 * Paramètres PDF Builder Pro - Navigation des onglets
 */

'use strict';

console.log('✅ settings-tabs.js CHARGÉ');

const PDF_BUILDER_CONFIG = typeof window.PDF_BUILDER_CONFIG !== 'undefined' ? window.PDF_BUILDER_CONFIG : {};

document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 DOM chargé - Initialisation onglets');
    
    const tabButtons = document.querySelectorAll('#pdf-builder-tabs .nav-tab');
    const tabContents = document.querySelectorAll('#pdf-builder-tab-content .tab-content');
    
    console.log('🔍 Trouvé:', tabButtons.length, 'onglets et', tabContents.length, 'contenus');
    
    function switchTab(tabId) {
        console.log('→ Changement vers:', tabId);
        tabButtons.forEach(btn => btn.classList.remove('nav-tab-active'));
        tabContents.forEach(c => c.classList.remove('active'));
        
        const btn = document.querySelector(`[data-tab="${tabId}"]`);
        const content = document.getElementById(tabId);
        
        if (btn) btn.classList.add('nav-tab-active');
        if (content) content.classList.add('active');
        
        try {
            localStorage.setItem('pdf_builder_active_tab', tabId);
        } catch(e) {}
    }
    
    tabButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            switchTab(btn.getAttribute('data-tab'));
        });
    });
    
    try {
        const saved = localStorage.getItem('pdf_builder_active_tab');
        if (saved && document.getElementById(saved)) {
            switchTab(saved);
        }
    } catch(e) {}
    
    console.log('✅ Onglets initialisés');
});

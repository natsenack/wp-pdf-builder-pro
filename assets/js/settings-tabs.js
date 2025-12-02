/**
 * Navigation des onglets pour les paramètres
 */

console.log('✅ settings-tabs.js CHARGÉ');

jQuery(document).ready(function($) {
    console.log('✅ jQuery ready - Navigation des onglets activée');
    
    $(document).on('click', '#pdf-builder-tabs .nav-tab', function(e) {
        e.preventDefault();
        
        var tabId = $(this).data('tab');
        console.log('🔗 Navigation vers onglet:', tabId);
        
        if (!tabId) return;
        
        // Enlever active de tous les onglets et contenus
        $('#pdf-builder-tabs .nav-tab').removeClass('nav-tab-active');
        $('#pdf-builder-tab-content .tab-content').removeClass('active');
        
        // Ajouter active au nouvel onglet et contenu
        $(this).addClass('nav-tab-active');
        $('#' + tabId).addClass('active');
        
        // Sauvegarder dans localStorage
        try {
            localStorage.setItem('pdf_builder_active_tab', tabId);
            console.log('💾 Onglet sauvegardé dans localStorage');
        } catch (err) {
            console.warn('⚠️ Impossible de sauvegarder dans localStorage');
        }
    });
    
    // Restaurer l'onglet actif depuis localStorage
    var savedTab = localStorage.getItem('pdf_builder_active_tab');
    if (savedTab) {
        console.log('📂 Restauration de l\'onglet sauvegardé:', savedTab);
        $('[data-tab="' + savedTab + '"]').click();
    }
});

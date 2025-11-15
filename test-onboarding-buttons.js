/**
 * Script de test pour vérifier le fonctionnement des boutons d'onboarding
 * Ce script peut être exécuté dans la console du navigateur pour tester la navigation
 */

(function() {
    'use strict';

    console.log('=== TEST DES BOUTONS D\'ONBOARDING ===');

    // Test 1: Vérifier que les éléments existent
    console.log('1. Vérification des éléments DOM...');

    const modal = document.getElementById('pdf-builder-onboarding-modal');
    console.log('   - Modal trouvé:', !!modal);

    if (modal) {
        const prevButton = modal.querySelector('.button-previous');
        const nextButton = modal.querySelector('[data-action="next-step"]');
        const skipButton = modal.querySelector('[data-action="skip-onboarding"]');

        console.log('   - Bouton précédent:', !!prevButton);
        console.log('   - Bouton suivant:', !!nextButton);
        console.log('   - Bouton ignorer:', !!skipButton);

        // Test 2: Vérifier les event listeners
        console.log('2. Vérification des event listeners...');

        if (prevButton) {
            console.log('   - Bouton précédent visible:', prevButton.offsetParent !== null);
            console.log('   - Classes du bouton précédent:', prevButton.className);
        }

        if (nextButton) {
            console.log('   - Bouton suivant visible:', nextButton.offsetParent !== null);
            console.log('   - Attributs du bouton suivant:', {
                'data-action': nextButton.getAttribute('data-action'),
                'data-step': nextButton.getAttribute('data-step'),
                disabled: nextButton.disabled
            });
        }
    }

    // Test 3: Vérifier que l'objet JavaScript existe
    console.log('3. Vérification de l\'objet JavaScript...');
    console.log('   - pdfBuilderOnboarding existe:', typeof window.pdfBuilderOnboarding !== 'undefined');

    if (typeof window.pdfBuilderOnboarding !== 'undefined') {
        console.log('   - Propriétés:', Object.keys(window.pdfBuilderOnboarding));
        console.log('   - AJAX URL:', !!window.pdfBuilderOnboarding.ajax_url);
        console.log('   - Nonce:', !!window.pdfBuilderOnboarding.nonce);
    }

    // Test 4: Vérifier la classe PDFBuilderOnboarding
    console.log('4. Vérification de la classe PDFBuilderOnboarding...');
    console.log('   - Classe disponible:', typeof PDFBuilderOnboarding !== 'undefined');

    if (typeof PDFBuilderOnboarding !== 'undefined') {
        console.log('   - Instance créée:', !!window.pdfBuilderOnboardingInstance);
    }

    console.log('=== FIN DU TEST ===');
    console.log('Pour tester manuellement:');
    console.log('- Cliquez sur les boutons et vérifiez la console');
    console.log('- Utilisez les flèches gauche/droite du clavier');
    console.log('- Vérifiez que les étapes changent correctement');

})();
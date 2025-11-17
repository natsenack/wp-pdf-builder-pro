/**
 * Script de test pour vérifier le fonctionnement des boutons d'onboarding
 * Ce script peut être exécuté dans la console du navigateur pour tester la navigation
 */

(function() {
    'use strict';

    // Test 1: Vérifier que les éléments existent

    const modal = document.getElementById('pdf-builder-onboarding-modal');

    if (modal) {
        const prevButton = modal.querySelector('.button-previous');
        const nextButton = modal.querySelector('[data-action="next-step"]');
        const skipButton = modal.querySelector('[data-action="skip-onboarding"]');

        // Test 2: Vérifier les event listeners

        if (prevButton) {
        }

        if (nextButton) {
                'data-action': nextButton.getAttribute('data-action'),
                'data-step': nextButton.getAttribute('data-step'),
                disabled: nextButton.disabled
            });
        }
    }

    // Test 3: Vérifier que l'objet JavaScript existe

    if (typeof window.pdfBuilderOnboarding !== 'undefined') {
    }

    // Test 4: Vérifier la classe PDFBuilderOnboarding

    if (typeof PDFBuilderOnboarding !== 'undefined') {
    }

})();

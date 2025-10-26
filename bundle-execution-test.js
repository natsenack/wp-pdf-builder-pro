// Test d'exécution du bundle principal
console.log('=== Test d\'exécution du bundle principal ===');

// Vérifier si le bundle a été chargé
if (typeof window.pdfBuilderInitReact === 'function') {
    console.log('✅ pdfBuilderInitReact est défini et est une fonction');
    try {
        // Tester l'appel de la fonction
        const result = window.pdfBuilderInitReact();
        console.log('✅ pdfBuilderInitReact() appelé avec succès:', result);
    } catch (error) {
        console.error('❌ Erreur lors de l\'appel de pdfBuilderInitReact():', error);
    }
} else {
    console.error('❌ pdfBuilderInitReact n\'est pas défini ou n\'est pas une fonction');
    console.log('Type de pdfBuilderInitReact:', typeof window.pdfBuilderInitReact);
    console.log('Valeur de pdfBuilderInitReact:', window.pdfBuilderInitReact);
}

// Vérifier pdfBuilderPro
if (window.pdfBuilderPro && typeof window.pdfBuilderPro.init === 'function') {
    console.log('✅ pdfBuilderPro.init est défini et est une fonction');
} else {
    console.error('❌ pdfBuilderPro.init n\'est pas défini ou n\'est pas une fonction');
    console.log('pdfBuilderPro:', window.pdfBuilderPro);
}

// Vérifier si React est disponible
if (typeof React !== 'undefined') {
    console.log('✅ React est disponible, version:', React.version);
} else {
    console.error('❌ React n\'est pas disponible');
}

// Vérifier si ReactDOM est disponible
if (typeof ReactDOM !== 'undefined') {
    console.log('✅ ReactDOM est disponible');
} else {
    console.error('❌ ReactDOM n\'est pas disponible');
}

console.log('=== Fin du test du bundle principal ===');
// Test minimal pour vérifier si le bundle s'exécute
console.log('🔍 TEST BUNDLE EXECUTION - MINIMAL');

// Test 1: Le script lui-même s'exécute
console.log('✅ Script de test chargé et exécuté');

// Test 2: Vérifier les variables globales avant
console.log('📊 État avant bundle:');
console.log('  pdfBuilderInitReact:', typeof window.pdfBuilderInitReact);
console.log('  pdfBuilderPro:', typeof window.pdfBuilderPro);

// Test 3: Simuler ce que fait le bundle
try {
  console.log('🧪 Simulation bundle execution...');

  // Simuler les imports (ce que fait webpack)
  if (typeof React === 'undefined') {
    console.log('❌ React pas disponible pour le bundle');
  } else {
    console.log('✅ React disponible pour le bundle');

    // Simuler la création d'un élément
    const testElement = React.createElement('div', {className: 'test'}, 'Test');
    console.log('✅ React.createElement fonctionne:', !!testElement);

    // Simuler l'exposition globale
    if (typeof window !== 'undefined') {
      window.pdfBuilderInitReact = function() { return 'test'; };
      console.log('✅ Exposition globale simulée');
      console.log('  pdfBuilderInitReact après:', typeof window.pdfBuilderInitReact);
    }
  }

} catch (error) {
  console.error('❌ Erreur lors de la simulation:', error);
  console.error('Stack:', error.stack);
}

console.log('🔍 TEST BUNDLE EXECUTION - TERMINÉ');
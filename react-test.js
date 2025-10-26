// Test rapide de React
console.log('🧪 TEST RAPIDE REACT');

try {
  // Test 1: React disponible
  if (typeof React === 'undefined') {
    throw new Error('React n\'est pas disponible globalement');
  }
  console.log('✅ React disponible:', React.version || 'version inconnue');

  // Test 2: ReactDOM disponible
  if (typeof ReactDOM === 'undefined') {
    throw new Error('ReactDOM n\'est pas disponible globalement');
  }
  console.log('✅ ReactDOM disponible');

  // Test 3: Créer un élément simple
  const element = React.createElement('div', {className: 'test'}, 'Hello React');
  console.log('✅ React.createElement fonctionne');

  // Test 4: pdfBuilderInitReact disponible
  if (typeof pdfBuilderInitReact === 'undefined') {
    console.warn('⚠️ pdfBuilderInitReact n\'est pas encore disponible (normal si le bundle ne s\'est pas chargé)');
  } else {
    console.log('✅ pdfBuilderInitReact disponible');
  }

  // Test 5: pdfBuilderPro disponible
  if (typeof pdfBuilderPro === 'undefined') {
    console.warn('⚠️ pdfBuilderPro n\'est pas encore disponible');
  } else {
    console.log('✅ pdfBuilderPro disponible:', typeof pdfBuilderPro.init);
  }

  console.log('🎉 Tests React réussis !');

} catch (error) {
  console.error('❌ ERREUR REACT:', error.message);
  console.error('Stack:', error.stack);
}
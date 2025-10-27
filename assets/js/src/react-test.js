// Test rapide de React


try {
  // Test 1: React disponible
  if (typeof React === 'undefined') {
    throw new Error('React n\'est pas disponible globalement');
  }
  

  // Test 2: ReactDOM disponible
  if (typeof ReactDOM === 'undefined') {
    throw new Error('ReactDOM n\'est pas disponible globalement');
  }
  

  // Test 3: Créer un élément simple
  const element = React.createElement('div', {className: 'test'}, 'Hello React');
  

  // Test 4: pdfBuilderInitReact disponible
  if (typeof pdfBuilderInitReact === 'undefined') {
    // pdfBuilderInitReact not available yet
  } else {

  }

  // Test 5: pdfBuilderPro disponible
  if (typeof pdfBuilderPro === 'undefined') {
    
  } else {
    
  }

  

} catch (error) {
  
  
}

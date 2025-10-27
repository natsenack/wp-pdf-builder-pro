// Test minimal pour vérifier si le bundle s'exécute


// Test 1: Le script lui-même s'exécute


// Test 2: Vérifier les variables globales avant




// Test 3: Simuler ce que fait le bundle
try {
  

  // Simuler les imports (ce que fait webpack)
  if (typeof React === 'undefined') {
    
  } else {
    

    // Simuler la création d'un élément
    const testElement = React.createElement('div', {className: 'test'}, 'Test');
    

    // Simuler l'exposition globale
    if (typeof window !== 'undefined') {
      window.pdfBuilderInitReact = function() { return 'test'; };
      
      
    }
  }

} catch (error) {
  
  
}



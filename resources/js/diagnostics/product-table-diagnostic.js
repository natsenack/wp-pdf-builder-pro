/**
 * Script de diagnostic pour les propriétés product_table
 * Vérifie la cohérence des propriétés entre les éléments existants et les définitions
 */

console.log('=== DIAGNOSTIC PRODUCT_TABLE PROPERTIES ===');

// Fonction pour vérifier les propriétés d'un élément product_table
function diagnoseProductTableProperties(element) {
  if (element.type !== 'product_table') return null;

  const requiredProperties = [
    'tableStyle',
    'columns',
    'showHeaders',
    'showBorders',
    'showSubtotal',
    'showShipping',
    'showTaxes',
    'showDiscount',
    'showTotal',
    'evenRowBg',
    'evenRowTextColor',
    'oddRowBg',
    'oddRowTextColor'
  ];

  const missingProperties = [];
  const invalidProperties = [];

  requiredProperties.forEach(prop => {
    if (!(prop in element)) {
      missingProperties.push(prop);
    }
  });

  // Vérifications spécifiques
  if (element.columns && typeof element.columns !== 'string') {
    invalidProperties.push(`columns: expected string, got ${typeof element.columns}`);
  }

  if (element.tableStyle && typeof element.tableStyle !== 'string') {
    invalidProperties.push(`tableStyle: expected string, got ${typeof element.tableStyle}`);
  }

  // Vérifications des couleurs
  const colorProperties = ['evenRowBg', 'evenRowTextColor', 'oddRowBg', 'oddRowTextColor'];
  colorProperties.forEach(prop => {
    if (element[prop] && !/^#[0-9A-Fa-f]{6}$/.test(element[prop])) {
      invalidProperties.push(`${prop}: invalid color format`);
    }
  });

  return {
    elementId: element.id,
    missingProperties,
    invalidProperties,
    hasIssues: missingProperties.length > 0 || invalidProperties.length > 0
  };
}

// Fonction principale de diagnostic
function runProductTableDiagnostic(elements) {
  console.log(`Analysing ${elements.length} elements...`);

  const productTableElements = elements.filter(el => el.type === 'product_table');
  console.log(`Found ${productTableElements.length} product_table elements`);

  const diagnostics = productTableElements.map(diagnoseProductTableProperties).filter(Boolean);

  const elementsWithIssues = diagnostics.filter(d => d.hasIssues);
  const healthyElements = diagnostics.filter(d => !d.hasIssues);

  console.log(`\n=== RESULTS ===`);
  console.log(`✅ Healthy elements: ${healthyElements.length}`);
  console.log(`❌ Elements with issues: ${elementsWithIssues.length}`);

  if (elementsWithIssues.length > 0) {
    console.log(`\n=== ISSUES FOUND ===`);
    elementsWithIssues.forEach(issue => {
      console.log(`Element ID ${issue.elementId}:`);
      if (issue.missingProperties.length > 0) {
        console.log(`  Missing properties: ${issue.missingProperties.join(', ')}`);
      }
      if (issue.invalidProperties.length > 0) {
        console.log(`  Invalid properties: ${issue.invalidProperties.join(', ')}`);
      }
    });
  }

  return {
    totalElements: productTableElements.length,
    healthyElements: healthyElements.length,
    elementsWithIssues: elementsWithIssues.length,
    issues: elementsWithIssues
  };
}

// Exposer la fonction globalement pour utilisation dans la console
window.runProductTableDiagnostic = runProductTableDiagnostic;

console.log('Diagnostic function loaded. Use: runProductTableDiagnostic(elements)');
console.log('=== DIAGNOSTIC READY ===');
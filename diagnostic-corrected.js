const fs = require('fs');
const path = require('path');

// Charger les constantes
const constantsPath = path.join(__dirname, 'resources/js/components/PropertiesPanel/utils/constants.js');
const constantsContent = fs.readFileSync(constantsPath, 'utf8');

// Extraire ELEMENT_PROPERTY_PROFILES
let profiles = {};
try {
  const match = constantsContent.match(/export const ELEMENT_PROPERTY_PROFILES = ({[\s\S]*?});/);
  if (match) {
    const code = `const ELEMENT_PROPERTY_PROFILES = ${match[1]}; ELEMENT_PROPERTY_PROFILES;`;
    profiles = eval(code);
  }
} catch (error) {
  console.error('Erreur lors du chargement des profils:', error);
  process.exit(1);
}

// Collecter toutes les propriétés définies
const allDefinedProperties = new Set();
const propertiesByElement = {};

Object.keys(profiles).forEach(elementType => {
  propertiesByElement[elementType] = new Set();
  const profile = profiles[elementType];

  Object.values(profile).forEach(section => {
    if (section.properties) {
      Object.values(section.properties).forEach(propList => {
        if (Array.isArray(propList)) {
          propList.forEach(prop => {
            allDefinedProperties.add(prop);
            propertiesByElement[elementType].add(prop);
          });
        }
      });
    }
  });
});

// Analyser tous les composants UI
const uiComponents = [
  'resources/js/components/PropertiesPanel/FontControls.jsx',
  'resources/js/components/PropertiesPanel/sections/BordersSection.jsx',
  'resources/js/components/PropertiesPanel/sections/ColorsSection.jsx',
  'resources/js/components/PropertiesPanel/sections/EffectsSection.jsx',
  'resources/js/components/PropertiesPanel/sections/FontSection.jsx',
  'resources/js/components/PropertiesPanel/sections/TypographySection.jsx',
  'resources/js/components/PropertiesPanel/sections/LayoutSection.jsx',
  'resources/js/components/PropertiesPanel/sections/ContentSection.jsx'
];

const uiImplementedProperties = new Set();

uiComponents.forEach(componentPath => {
  if (fs.existsSync(componentPath)) {
    const content = fs.readFileSync(componentPath, 'utf8');

    // Chercher les appels onPropertyChange et handlePropertyChange avec les noms de propriétés
    const propertyChangePatterns = [
      /onPropertyChange\([^,]+,\s*['"]([^'"]+)['"]/g,
      /handlePropertyChange\([^,]+,\s*['"]([^'"]+)['"]/g
    ];

    propertyChangePatterns.forEach(pattern => {
      while ((match = pattern.exec(content)) !== null) {
        const prop = match[1];
        if (allDefinedProperties.has(prop)) {
          uiImplementedProperties.add(prop);
        }
      }
    });

    // Chercher les propriétés dans les value et autres attributs
    const propertyPatterns = [
      /value=\{properties\.([a-zA-Z0-9_]+)\}/g,
      /properties\.([a-zA-Z0-9_]+)\s*\?/g,
      /properties\.([a-zA-Z0-9_]+)/g
    ];

    propertyPatterns.forEach(pattern => {
      while ((match = pattern.exec(content)) !== null) {
        const prop = match[1];
        if (allDefinedProperties.has(prop)) {
          uiImplementedProperties.add(prop);
        }
      }
    });
  }
});

// Analyser le rendu Canvas
const canvasFile = 'resources/js/components/PDFEditor.jsx';
const canvasImplementedProperties = new Set();

if (fs.existsSync(canvasFile)) {
  const content = fs.readFileSync(canvasFile, 'utf8');

  // Chercher les propriétés utilisées dans le rendu
  allDefinedProperties.forEach(prop => {
    const patterns = [
      new RegExp(`element\\.${prop}`, 'g'),
      new RegExp(`properties\\.${prop}`, 'g'),
      new RegExp(`props\\.${prop}`, 'g'),
      new RegExp(`\\b${prop}\\b`, 'g')
    ];

    patterns.forEach(pattern => {
      if (pattern.test(content)) {
        canvasImplementedProperties.add(prop);
      }
    });
  });
}

console.log('🔍 DIAGNOSTIC COMPLET CORRIGÉ - IMPLÉMENTATION PAR PROPRIÉTÉ\n');

const implementationStatus = {};

allDefinedProperties.forEach(prop => {
  const uiImplemented = uiImplementedProperties.has(prop);
  const canvasImplemented = canvasImplementedProperties.has(prop);
  const elementsUsing = Array.from(Object.keys(propertiesByElement)).filter(el => propertiesByElement[el].has(prop));

  implementationStatus[prop] = {
    ui: uiImplemented,
    canvas: canvasImplemented,
    elements: elementsUsing.length,
    fullyImplemented: uiImplemented && canvasImplemented
  };
});

// Grouper par statut
const fullyImplemented = [];
const uiOnly = [];
const canvasOnly = [];
const notImplemented = [];

Object.entries(implementationStatus).forEach(([prop, status]) => {
  if (status.fullyImplemented) {
    fullyImplemented.push(prop);
  } else if (status.ui && !status.canvas) {
    uiOnly.push(prop);
  } else if (!status.ui && status.canvas) {
    canvasOnly.push(prop);
  } else {
    notImplemented.push(prop);
  }
});

console.log(`✅ FULLY IMPLEMENTED (${fullyImplemented.length}/${allDefinedProperties.size}):`);
fullyImplemented.sort().forEach(prop => {
  const status = implementationStatus[prop];
  console.log(`  ${prop} (${status.elements} éléments)`);
});

console.log(`\n⚠️  UI ONLY - Missing Canvas (${uiOnly.length}):`);
uiOnly.sort().forEach(prop => {
  const status = implementationStatus[prop];
  console.log(`  ${prop} (${status.elements} éléments)`);
});

console.log(`\n⚠️  CANVAS ONLY - Missing UI (${canvasOnly.length}):`);
canvasOnly.sort().forEach(prop => {
  const status = implementationStatus[prop];
  console.log(`  ${prop} (${status.elements} éléments)`);
});

console.log(`\n❌ NOT IMPLEMENTED (${notImplemented.length}):`);
notImplemented.sort().forEach(prop => {
  const status = implementationStatus[prop];
  console.log(`  ${prop} (${status.elements} éléments)`);
});

console.log(`\n📊 RÉSUMÉ:`);
console.log(`  Total propriétés: ${allDefinedProperties.size}`);
console.log(`  Fully implemented: ${fullyImplemented.length} (${Math.round(fullyImplemented.length/allDefinedProperties.size*100)}%)`);
console.log(`  UI coverage: ${uiImplementedProperties.size} (${Math.round(uiImplementedProperties.size/allDefinedProperties.size*100)}%)`);
console.log(`  Canvas coverage: ${canvasImplementedProperties.size} (${Math.round(canvasImplementedProperties.size/allDefinedProperties.size*100)}%)`);

// Propriétés critiques à implémenter
const criticalProperties = [
  'scaleX', 'scaleY', 'brightness', 'contrast', 'saturate',
  'textShadowColor', 'textShadowOffsetX', 'textShadowOffsetY', 'textShadowBlur',
  'textTransform', 'shadowBlur'
];

console.log(`\n🚨 PROPRIÉTÉS CRITIQUES À IMPLÉMENTER:`);
criticalProperties.forEach(prop => {
  const status = implementationStatus[prop];
  if (status) {
    const uiStatus = status.ui ? '✅' : '❌';
    const canvasStatus = status.canvas ? '✅' : '❌';
    console.log(`  ${prop}: UI ${uiStatus} | Canvas ${canvasStatus} (${status.elements} éléments)`);
  }
});
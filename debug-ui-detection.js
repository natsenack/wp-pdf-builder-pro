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

// Analyser les composants UI
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

console.log('🔍 ANALYSE DÉTAILLÉE DES COMPOSANTS UI\n');

uiComponents.forEach(componentPath => {
  if (fs.existsSync(componentPath)) {
    const content = fs.readFileSync(componentPath, 'utf8');
    const fileName = path.basename(componentPath);
    console.log(`📄 ${fileName}:`);

    // Chercher les appels onPropertyChange et handlePropertyChange avec les noms de propriétés
    const propertyChangePatterns = [
      /onPropertyChange\([^,]+,\s*['"]([^'"]+)['"]/g,
      /handlePropertyChange\([^,]+,\s*['"]([^'"]+)['"]/g
    ];
    let match;
    const foundProps = new Set();

    propertyChangePatterns.forEach(pattern => {
      while ((match = pattern.exec(content)) !== null) {
        const prop = match[1];
        if (allDefinedProperties.has(prop)) {
          foundProps.add(prop);
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
          foundProps.add(prop);
          uiImplementedProperties.add(prop);
        }
      }
    });

    if (foundProps.size > 0) {
      console.log(`   ✅ Propriétés trouvées: ${Array.from(foundProps).join(', ')}`);
    } else {
      console.log(`   ❌ Aucune propriété trouvée`);
    }
    console.log('');
  } else {
    console.log(`❌ ${componentPath} n'existe pas\n`);
  }
});

console.log(`📊 TOTAL: ${uiImplementedProperties.size} propriétés UI détectées sur ${allDefinedProperties.size} définies`);
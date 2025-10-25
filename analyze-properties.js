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

console.log('🔍 ANALYSE DÉTAILLÉE PAR ÉLÉMENT\n');

// Analyser chaque élément
Object.keys(profiles).forEach(elementType => {
  console.log(`📋 ${elementType.toUpperCase()}`);
  console.log(`   Propriétés définies: ${propertiesByElement[elementType].size}`);

  const profile = profiles[elementType];
  Object.keys(profile).forEach(sectionName => {
    const section = profile[sectionName];
    if (section.properties) {
      console.log(`   📁 ${sectionName}:`);
      Object.keys(section.properties).forEach(subSection => {
        const props = section.properties[subSection];
        if (Array.isArray(props)) {
          console.log(`      ${subSection}: ${props.join(', ')}`);
        }
      });
    }
  });

  console.log('');
});

console.log('📊 PROPRIÉTÉS LES PLUS COURANTES:');
const propertyCount = {};
allDefinedProperties.forEach(prop => {
  propertyCount[prop] = 0;
  Object.values(propertiesByElement).forEach(elementProps => {
    if (elementProps.has(prop)) propertyCount[prop]++;
  });
});

Object.entries(propertyCount)
  .sort(([,a], [,b]) => b - a)
  .slice(0, 15)
  .forEach(([prop, count]) => {
    console.log(`  ${prop}: ${count}/${Object.keys(profiles).length} éléments`);
  });

console.log(`\n📈 TOTAL: ${allDefinedProperties.size} propriétés uniques sur ${Object.keys(profiles).length} éléments`);
const fs = require('fs');
const path = require('path');

// Charger les constantes JavaScript
const constantsPath = path.join(__dirname, 'resources/js/components/PropertiesPanel/utils/constants.js');
const constantsContent = fs.readFileSync(constantsPath, 'utf8');

// Extraire ELEMENT_PROPERTY_PROFILES
let elementProfiles = {};
try {
  const match = constantsContent.match(/export const ELEMENT_PROPERTY_PROFILES = ({[\s\S]*?});/);
  if (match) {
    const code = `const ELEMENT_PROPERTY_PROFILES = ${match[1]}; ELEMENT_PROPERTY_PROFILES;`;
    elementProfiles = eval(code);
  }
} catch (error) {
  console.error('Erreur lors du chargement des profils:', error);
  process.exit(1);
}

// Collecter les types d'éléments des profils
const profileTypes = Object.keys(elementProfiles);

// Collecter les types supportés par les renderers PHP
const rendererTypes = new Set();

// Fonction pour analyser un fichier renderer
function analyzeRendererFile(filePath) {
  if (fs.existsSync(filePath)) {
    const content = fs.readFileSync(filePath, 'utf8');
    const match = content.match(/const SUPPORTED_TYPES = (\[[\s\S]*?\]);/);
    if (match) {
      try {
        const types = eval(match[1]);
        types.forEach(type => rendererTypes.add(type));
      } catch (error) {
        console.error(`Erreur parsing ${filePath}:`, error);
      }
    }
  }
}

// Analyser tous les renderers
const renderers = [
  'src/Renderers/TextRenderer.php',
  'src/Renderers/InfoRenderer.php',
  'src/Renderers/ImageRenderer.php',
  'src/Renderers/TableRenderer.php',
  'src/Renderers/ShapeRenderer.php'
];

renderers.forEach(renderer => {
  analyzeRendererFile(renderer);
});

console.log('🔍 ANALYSE DES INCOHÉRENCES TYPES D\'ÉLÉMENTS\n');

// Types dans les profils mais pas dans les renderers
const profileOnly = profileTypes.filter(type => !rendererTypes.has(type));

// Types dans les renderers mais pas dans les profils
const rendererOnly = Array.from(rendererTypes).filter(type => !profileTypes.includes(type));

console.log('📋 TYPES DÉFINIS DANS ELEMENT_PROPERTY_PROFILES:');
profileTypes.forEach(type => console.log(`  - ${type}`));

console.log('\n🔧 TYPES SUPPORTÉS PAR LES RENDERERS:');
Array.from(rendererTypes).forEach(type => console.log(`  - ${type}`));

console.log('\n⚠️  INCOHÉRENCES DÉTECTÉES:');

if (profileOnly.length > 0) {
  console.log('\n❌ Types dans profils mais PAS dans renderers:');
  profileOnly.forEach(type => console.log(`  - ${type}`));
}

if (rendererOnly.length > 0) {
  console.log('\n❌ Types dans renderers mais PAS dans profils:');
  rendererOnly.forEach(type => console.log(`  - ${type}`));
}

if (profileOnly.length === 0 && rendererOnly.length === 0) {
  console.log('\n✅ Aucune incohérence détectée !');
}

// Analyse détaillée des propriétés par type
console.log('\n📊 ANALYSE DÉTAILLÉE:');

profileTypes.forEach(type => {
  const profile = elementProfiles[type];
  const hasRenderer = rendererTypes.has(type);

  console.log(`\n🔍 Type: ${type}`);
  console.log(`  - Profil défini: ✅`);
  console.log(`  - Renderer disponible: ${hasRenderer ? '✅' : '❌'}`);

  if (profile) {
    const sections = Object.keys(profile);
    console.log(`  - Sections: ${sections.join(', ')}`);

    let totalProps = 0;
    sections.forEach(section => {
      if (profile[section].properties) {
        Object.values(profile[section].properties).forEach(propList => {
          if (Array.isArray(propList)) {
            totalProps += propList.length;
          }
        });
      }
    });
    console.log(`  - Propriétés totales: ${totalProps}`);
  }
});

// Recommandations
console.log('\n💡 RECOMMANDATIONS:');

if (profileOnly.length > 0) {
  console.log('\n1. Supprimer ou corriger les types suivants dans ELEMENT_PROPERTY_PROFILES:');
  profileOnly.forEach(type => console.log(`   - ${type}`));
}

if (rendererOnly.length > 0) {
  console.log('\n2. Ajouter les types suivants dans ELEMENT_PROPERTY_PROFILES:');
  rendererOnly.forEach(type => console.log(`   - ${type}`));
}

console.log('\n3. Vérifier la cohérence des propriétés avec les capacités des renderers.');
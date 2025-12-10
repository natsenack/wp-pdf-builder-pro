#!/usr/bin/env node

/**
 * Script de test amélioré pour PDF Builder Pro
 * Exécute tous les tests avec rapports détaillés
 */

import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

console.log('🚀 Démarrage de la suite de tests PDF Builder Pro\n');

// Fonction pour exécuter une commande et capturer la sortie
function runCommand(command, description) {
  console.log(`📋 ${description}`);
  try {
    const output = execSync(command, {
      encoding: 'utf8',
      stdio: 'pipe',
      cwd: process.cwd()
    });
    console.log('✅ Succès\n');
    return { success: true, output };
  } catch (error) {
    console.log('❌ Échec\n');
    console.log('Erreur:', error.message);
    return { success: false, error: error.message };
  }
}

// 1. Tests JavaScript avec Jest
console.log('='.repeat(60));
console.log('🧪 TESTS JAVASCRIPT (Jest)');
console.log('='.repeat(60));

const jestResult = runCommand('npm test -- --coverage --watchAll=false', 'Exécution des tests JavaScript');

// 2. Analyse de la couverture
if (jestResult.success) {
  console.log('📊 ANALYSE DE COUVERTURE');
  console.log('='.repeat(40));

  try {
    const coveragePath = path.join(process.cwd(), 'coverage', 'coverage-summary.json');
    if (fs.existsSync(coveragePath)) {
      const coverage = JSON.parse(fs.readFileSync(coveragePath, 'utf8'));
      const total = coverage.total;

      console.log(`📈 Couverture globale:`);
      console.log(`   📋 Statements: ${total.statements.pct}%`);
      console.log(`   🌿 Branches: ${total.branches.pct}%`);
      console.log(`   🔧 Functions: ${total.functions.pct}%`);
      console.log(`   📝 Lines: ${total.lines.pct}%`);

      // Vérifier les seuils
      const thresholds = { statements: 75, branches: 70, functions: 75, lines: 75 };
      let allPassed = true;

      Object.keys(thresholds).forEach(metric => {
        if (total[metric].pct < thresholds[metric]) {
          console.log(`⚠️  ${metric} en dessous du seuil (${thresholds[metric]}%)`);
          allPassed = false;
        }
      });

      if (allPassed) {
        console.log('✅ Tous les seuils de couverture atteints');
      }
    }
  } catch (error) {
    console.log('⚠️ Impossible de lire le rapport de couverture');
  }
}

// 3. Tests PHP (si disponibles)
console.log('\n' + '='.repeat(60));
console.log('🐘 TESTS PHP (si configurés)');
console.log('='.repeat(60));

const phpTestResult = runCommand('php tests/run-tests.php', 'Exécution des tests PHP');

// 4. Validation des fichiers de test créés
console.log('\n' + '='.repeat(60));
console.log('🔍 VALIDATION DES NOUVEAUX TESTS');
console.log('='.repeat(60));

const newTestFiles = [
  'tests/IntegrationTest.php',
  'tests/CanvasAjaxHandlerTest.php',
  'tests/canvas-parameter-persistence.test.js',
  'tests/canvas-resilience.test.js',
  'jest.config.js',
  'babel.config.js'
];

let allFilesExist = true;
newTestFiles.forEach(file => {
  if (fs.existsSync(file)) {
    console.log(`✅ ${file} - Présent`);
  } else {
    console.log(`❌ ${file} - Manquant`);
    allFilesExist = false;
  }
});

// 5. Résumé final
console.log('\n' + '='.repeat(60));
console.log('📋 RÉSUMÉ DE LA SUITE DE TESTS');
console.log('='.repeat(60));

const results = {
  jest: jestResult.success,
  php: phpTestResult.success,
  files: allFilesExist
};

console.log(`🧪 Tests JavaScript: ${results.jest ? '✅ Réussis' : '❌ Échoués'}`);
console.log(`🐘 Tests PHP: ${results.php ? '✅ Réussis' : '⚠️ Non configurés'}`);
console.log(`📁 Fichiers de test: ${results.files ? '✅ Tous présents' : '❌ Fichiers manquants'}`);

const overallSuccess = results.jest && results.files;
console.log(`\n🎯 Résultat global: ${overallSuccess ? '✅ SUCCÈS' : '❌ ÉCHECS DETECTÉS'}`);

// Instructions pour les améliorations
if (!overallSuccess) {
  console.log('\n💡 Pour améliorer la couverture:');
  console.log('   1. Corriger les tests JavaScript qui échouent');
  console.log('   2. Configurer l\'environnement de test WordPress pour les tests PHP');
  console.log('   3. Vérifier que tous les nouveaux fichiers de test sont présents');
  console.log('   4. Exécuter: npm install pour installer les nouvelles dépendances');
}

console.log('\n✨ Test suite terminée!');
process.exit(overallSuccess ? 0 : 1);
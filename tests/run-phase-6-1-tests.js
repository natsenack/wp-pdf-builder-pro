#!/usr/bin/env node

/**
 * Test Runner Complet - Phase 6.1
 * Exécute tous les tests unitaires avancés
 */

const { execSync } = require('child_process');
const path = require('path');

class Phase6_1_Test_Runner {

    constructor() {
        this.results = {
            php: { passed: false, tests: 0, score: 0 },
            react: { passed: false, tests: 0, score: 0 },
            data: { passed: false, tests: 0, score: 0 },
            coverage: { passed: false, coverage: 0 },
            mutations: { passed: false, score: 0 },
            total: { passed: false, completed: 0, total: 5 }
        };
    }

    log(message) {
        console.log(`🔬 ${message}`);
    }

    runPHPTests() {
        this.log('Running PHP Unit Tests...');
        try {
            const projectRoot = path.resolve(__dirname, '..');
            const output = execSync(`php "${projectRoot}/tests/unit/php-unit-tests.php"`, {
                encoding: 'utf8',
                cwd: projectRoot
            });

            // Parser les résultats
            const lines = output.split('\n');
            const testLine = lines.find(line => line.includes('Tests exécutés:'));
            const scoreLine = lines.find(line => line.includes('Taux de réussite:'));

            if (testLine && scoreLine) {
                const tests = parseInt(testLine.match(/Tests exécutés: (\d+)/)?.[1] || '0');
                const score = parseFloat(scoreLine.match(/Taux de réussite: ([\d.]+)/)?.[1] || '0');

                this.results.php = { passed: score === 100, tests, score };
                console.log(`  ✅ PHP Tests: ${tests} tests, ${score}% success`);
            }
        } catch (error) {
            console.log(`  ❌ PHP Tests failed: ${error.message}`);
            this.results.php.passed = false;
        }
    }

    runReactTests() {
        this.log('Running React Unit Tests...');
        try {
            const projectRoot = path.resolve(__dirname, '..');
            const output = execSync(`node "${projectRoot}/tests/unit/react-unit-tests.js"`, {
                encoding: 'utf8',
                cwd: projectRoot
            });

            const lines = output.split('\n');
            const testLine = lines.find(line => line.includes('Tests exécutés:'));
            const scoreLine = lines.find(line => line.includes('Taux de réussite:'));

            if (testLine && scoreLine) {
                const tests = parseInt(testLine.match(/Tests exécutés: (\d+)/)?.[1] || '0');
                const score = parseFloat(scoreLine.match(/Taux de réussite: ([\d.]+)/)?.[1] || '0');

                this.results.react = { passed: score === 100, tests, score };
                console.log(`  ✅ React Tests: ${tests} tests, ${score}% success`);
            }
        } catch (error) {
            console.log(`  ❌ React Tests failed: ${error.message}`);
            this.results.react.passed = false;
        }
    }

    runDataTests() {
        this.log('Running Data Unit Tests...');
        try {
            const projectRoot = path.resolve(__dirname, '..');
            const output = execSync(`node "${projectRoot}/tests/unit/data-unit-tests.js"`, {
                encoding: 'utf8',
                cwd: projectRoot
            });

            const lines = output.split('\n');
            const testLine = lines.find(line => line.includes('Tests exécutés:'));
            const scoreLine = lines.find(line => line.includes('Taux de réussite:'));

            if (testLine && scoreLine) {
                const tests = parseInt(testLine.match(/Tests exécutés: (\d+)/)?.[1] || '0');
                const score = parseFloat(scoreLine.match(/Taux de réussite: ([\d.]+)/)?.[1] || '0');

                this.results.data = { passed: score === 100, tests, score };
                console.log(`  ✅ Data Tests: ${tests} tests, ${score}% success`);
            }
        } catch (error) {
            console.log(`  ❌ Data Tests failed: ${error.message}`);
            this.results.data.passed = false;
        }
    }

    runCoverageTests() {
        this.log('Running Coverage Analysis...');
        try {
            const projectRoot = path.resolve(__dirname, '..');
            const output = execSync(`node "${projectRoot}/tests/unit/coverage-tests.js"`, {
                encoding: 'utf8',
                cwd: projectRoot
            });

            const lines = output.split('\n');
            const coverageLine = lines.find(line => line.includes('Total Coverage:'));

            if (coverageLine) {
                const coverage = parseFloat(coverageLine.match(/Total Coverage: ([\d.]+)/)?.[1] || '0');

                this.results.coverage = { passed: coverage >= 90, coverage };
                console.log(`  ✅ Coverage: ${coverage}% (Target: 90%+)`);
            }
        } catch (error) {
            console.log(`  ❌ Coverage Tests failed: ${error.message}`);
            this.results.coverage.passed = false;
        }
    }

    runMutationTests() {
        this.log('Running Mutation Tests...');
        try {
            const projectRoot = path.resolve(__dirname, '..');
            const output = execSync(`node "${projectRoot}/tests/unit/mutation-tests.js"`, {
                encoding: 'utf8',
                cwd: projectRoot
            });

            const lines = output.split('\n');
            const scoreLine = lines.find(line => line.includes('Score:'));

            if (scoreLine) {
                const score = parseFloat(scoreLine.match(/Score: ([\d.]+)/)?.[1] || '0');

                this.results.mutations = { passed: score >= 80, score };
                console.log(`  ✅ Mutations: ${score}% score (Target: 80%+)`);
            }
        } catch (error) {
            console.log(`  ❌ Mutation Tests failed: ${error.message}`);
            this.results.mutations.passed = false;
        }
    }

    generateFinalReport() {
        console.log('\n' + '='.repeat(60));
        console.log('📊 RAPPORT FINAL - PHASE 6.1 TESTS UNITAIRES AVANCÉS');
        console.log('='.repeat(60));

        const allTests = [
            { name: 'PHP Unit Tests', ...this.results.php },
            { name: 'React Unit Tests', ...this.results.react },
            { name: 'Data Unit Tests', ...this.results.data },
            { name: 'Coverage Analysis', passed: this.results.coverage.passed, coverage: this.results.coverage.coverage },
            { name: 'Mutation Tests', passed: this.results.mutations.passed, score: this.results.mutations.score }
        ];

        let totalTests = 0;
        let totalPassed = 0;

        allTests.forEach(test => {
            const status = test.passed ? '✅' : '❌';
            const details = test.tests ? `${test.tests} tests, ${test.score}%` :
                         test.coverage ? `${test.coverage}% coverage` :
                         test.score ? `${test.score}% score` : '';
            console.log(`${status} ${test.name}: ${details}`);
            totalTests++;
            if (test.passed) totalPassed++;
        });

        console.log('');
        console.log(`RÉSULTAT GLOBAL: ${totalPassed}/${totalTests} catégories réussies`);

        const overallSuccess = totalPassed === totalTests;
        console.log('');
        console.log('='.repeat(60));
        if (overallSuccess) {
            console.log('🎉 PHASE 6.1 TERMINÉE AVEC SUCCÈS !');
            console.log('✅ Tous les tests unitaires avancés validés');
        } else {
            console.log('❌ PHASE 6.1 INCOMPLÈTE');
            console.log('⚠️ Certains tests ont échoué');
        }
        console.log('='.repeat(60));

        return overallSuccess;
    }

    runAllTests() {
        console.log('🚀 DÉMARRAGE TESTS PHASE 6.1 - TESTS UNITAIRES AVANCÉS\n');

        this.runPHPTests();
        this.runReactTests();
        this.runDataTests();
        this.runCoverageTests();
        this.runMutationTests();

        return this.generateFinalReport();
    }
}

// Exécuter tous les tests
if (require.main === module) {
    const testRunner = new Phase6_1_Test_Runner();
    testRunner.runAllTests();
}

module.exports = Phase6_1_Test_Runner;
#!/usr/bin/env node

/**
 * Script de test de charge Artillery Phase 5.8
 * Tests de performance sous charge pour le générateur PDF
 */

const { exec } = require('child_process');
const fs = require('fs');
const path = require('path');

class ArtilleryLoadTester {
    constructor() {
        this.results = {
            tests: [],
            summary: {
                totalTests: 0,
                successfulTests: 0,
                failedTests: 0,
                averageResponseTime: 0,
                maxResponseTime: 0,
                minResponseTime: Infinity,
                totalRequests: 0,
                errorRate: 0
            }
        };
    }

    async runLoadTest(configFile, testName, duration = 60) {
        console.log(`\n🚀 Démarrage test de charge: ${testName}`);
        console.log(`Durée: ${duration}s`);

        return new Promise((resolve, reject) => {
            const startTime = Date.now();
            const outputFile = `artillery-results-${Date.now()}.json`;

            // Commande Artillery
            const command = `npx artillery run ${configFile} --output ${outputFile}`;

            console.log(`Exécution: ${command}`);

            exec(command, { cwd: __dirname, maxBuffer: 1024 * 1024 * 10 }, (error, stdout, stderr) => {
                const endTime = Date.now();
                const executionTime = endTime - startTime;

                try {
                    let report = null;

                    // Essayer de lire le fichier de résultats
                    if (fs.existsSync(outputFile)) {
                        const reportData = fs.readFileSync(outputFile, 'utf8');
                        report = JSON.parse(reportData);
                    }

                    const result = {
                        testName,
                        success: !error && report,
                        executionTime,
                        error: error ? error.message : null,
                        report,
                        timestamp: new Date().toISOString()
                    };

                    // Analyser les métriques Artillery
                    if (report && report.aggregate) {
                        const aggregate = report.aggregate;

                        result.metrics = {
                            totalRequests: aggregate.requestsCompleted || 0,
                            responseTime: {
                                min: aggregate.latency.min || 0,
                                max: aggregate.latency.max || 0,
                                median: aggregate.latency.median || 0,
                                p95: aggregate.latency.p95 || 0,
                                p99: aggregate.latency.p99 || 0
                            },
                            errorRate: aggregate.errors ? (aggregate.errors / aggregate.requestsCompleted) * 100 : 0,
                            throughput: aggregate.rps || 0
                        };

                        // Calculer les erreurs par type
                        if (aggregate.codes) {
                            result.metrics.httpStatusCodes = aggregate.codes;
                        }
                    }

                    this.results.tests.push(result);
                    this.updateSummary(result);

                    if (result.success) {
                        console.log(`✅ Test réussi: ${result.metrics?.totalRequests || 0} requêtes`);
                        console.log(`   Temps réponse moyen: ${result.metrics?.responseTime?.median || 0}ms`);
                        console.log(`   Taux d'erreur: ${result.metrics?.errorRate?.toFixed(2) || 0}%`);
                        console.log(`   Débit: ${result.metrics?.throughput?.toFixed(2) || 0} req/s`);
                    } else {
                        console.log(`❌ Test échoué: ${result.error}`);
                    }

                    // Nettoyer le fichier de résultats
                    if (fs.existsSync(outputFile)) {
                        fs.unlinkSync(outputFile);
                    }

                    resolve(result);

                } catch (parseError) {
                    console.log(`❌ Erreur d'analyse du rapport: ${parseError.message}`);
                    resolve({
                        testName,
                        success: false,
                        executionTime,
                        error: parseError.message,
                        timestamp: new Date().toISOString()
                    });
                }
            });
        });
    }

    updateSummary(result) {
        this.results.summary.totalTests++;

        if (result.success) {
            this.results.summary.successfulTests++;
        } else {
            this.results.summary.failedTests++;
        }

        // Agréger les métriques
        if (result.metrics) {
            this.results.summary.totalRequests += result.metrics.totalRequests || 0;

            if (result.metrics.responseTime) {
                this.results.summary.averageResponseTime += result.metrics.responseTime.median || 0;
                this.results.summary.maxResponseTime = Math.max(
                    this.results.summary.maxResponseTime,
                    result.metrics.responseTime.max || 0
                );
                this.results.summary.minResponseTime = Math.min(
                    this.results.summary.minResponseTime,
                    result.metrics.responseTime.min || 0
                );
            }

            this.results.summary.errorRate += result.metrics.errorRate || 0;
        }
    }

    generateReport() {
        const summary = this.results.summary;

        // Calculer les moyennes
        if (summary.successfulTests > 0) {
            summary.averageResponseTime = summary.averageResponseTime / summary.successfulTests;
            summary.errorRate = summary.errorRate / summary.successfulTests;
        }

        if (summary.minResponseTime === Infinity) {
            summary.minResponseTime = 0;
        }

        return {
            ...this.results,
            generatedAt: new Date().toISOString(),
            phase: '5.8',
            description: 'Tests de charge Artillery génération PDF'
        };
    }

    saveReport(filename = 'pdf-load-test-report.json') {
        const report = this.generateReport();
        const reportPath = path.join(__dirname, filename);

        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n📊 Rapport de charge sauvegardé: ${reportPath}`);

        return report;
    }
}

// Fonction principale
async function runLoadTests() {
    const tester = new ArtilleryLoadTester();

    try {
        // Vérifier si Artillery est installé
        console.log('🔍 Vérification d\'Artillery...');

        // Test 1: Charge légère (warmup)
        await tester.runLoadTest('artillery-config-light.yml', 'Charge légère (warmup)', 30);

        // Test 2: Charge normale
        await tester.runLoadTest('artillery-config.yml', 'Charge normale', 60);

        // Test 3: Charge élevée (si le système le supporte)
        // await tester.runLoadTest('artillery-config-heavy.yml', 'Charge élevée', 45);

        // Générer le rapport
        const report = tester.saveReport('phase5.8-load-test-report.json');

        console.log('\n📊 RÉSULTATS TESTS DE CHARGE:');
        console.log(`Tests totaux: ${report.summary.totalTests}`);
        console.log(`Succès: ${report.summary.successfulTests}`);
        console.log(`Échecs: ${report.summary.failedTests}`);
        console.log(`Requêtes totales: ${report.summary.totalRequests}`);
        console.log(`Temps réponse moyen: ${report.summary.averageResponseTime.toFixed(2)}ms`);
        console.log(`Temps réponse min: ${report.summary.minResponseTime}ms`);
        console.log(`Temps réponse max: ${report.summary.maxResponseTime}ms`);
        console.log(`Taux d'erreur moyen: ${report.summary.errorRate.toFixed(2)}%`);

    } catch (error) {
        console.error('❌ Erreur lors des tests de charge:', error);
    }
}

// Exécuter les tests
if (require.main === module) {
    runLoadTests().catch(console.error);
}

module.exports = ArtilleryLoadTester;
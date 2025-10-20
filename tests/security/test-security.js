#!/usr/bin/env node

/**
 * Script de test de sécurité Phase 5.8
 * Tests de sécurité automatisés pour le générateur PDF
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class PDFSecurityTester {
    constructor() {
        this.results = {
            tests: [],
            summary: {
                totalTests: 0,
                passedTests: 0,
                failedTests: 0,
                vulnerabilities: []
            }
        };
        this.browser = null;
    }

    async init() {
        console.log('🔒 Initialisation du testeur de sécurité PDF...');

        this.browser = await puppeteer.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-web-security',
                '--disable-features=VizDisplayCompositor'
            ]
        });

        console.log('✅ Browser Puppeteer initialisé pour tests de sécurité');
    }

    async close() {
        if (this.browser) {
            await this.browser.close();
            console.log('🛑 Browser fermé');
        }
    }

    async testXSSInjection(testName, maliciousHTML) {
        console.log(`\n🛡️ Test XSS: ${testName}`);

        try {
            const page = await this.browser.newPage();

            // Surveiller les erreurs JavaScript
            const errors = [];
            page.on('pageerror', error => errors.push(error.message));

            // Charger le contenu malveillant
            await page.setContent(maliciousHTML, {
                waitUntil: 'networkidle0',
                timeout: 10000
            });

            // Attendre un peu pour que les scripts s'exécutent
            await page.waitForTimeout(2000);

            // Vérifier s'il y a eu des erreurs ou des comportements suspects
            const hasAlerts = await page.evaluate(() => {
                // Simuler la détection d'alertes XSS
                return window.alert !== undefined && window.alert.toString().includes('native');
            });

            const result = {
                testName,
                type: 'XSS',
                success: errors.length === 0 && !hasAlerts,
                errors: errors,
                hasAlerts,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(result);
            this.updateSummary(result);

            if (result.success) {
                console.log('✅ Aucun XSS détecté');
            } else {
                console.log(`❌ Vulnérabilité XSS détectée: ${errors.join(', ')}`);
            }

            await page.close();
            return result;

        } catch (error) {
            const result = {
                testName,
                type: 'XSS',
                success: false,
                error: error.message,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(result);
            this.updateSummary(result);

            console.log(`❌ Erreur test XSS: ${error.message}`);
            return result;
        }
    }

    async testPathTraversal(testName, maliciousPath) {
        console.log(`\n📁 Test Path Traversal: ${testName}`);

        try {
            // Simuler une tentative d'accès à un fichier système
            const normalizedPath = path.normalize(maliciousPath);
            const isTraversal = normalizedPath.includes('..') ||
                              normalizedPath.startsWith('/') ||
                              normalizedPath.includes('\\');

            // Vérifier si le chemin tente d'accéder à des répertoires sensibles
            const sensitivePaths = ['/etc', '/proc', '/home', 'C:\\Windows', 'C:\\Program Files'];
            const isSensitive = sensitivePaths.some(sensitive =>
                normalizedPath.toLowerCase().includes(sensitive.toLowerCase())
            );

            const result = {
                testName,
                type: 'Path Traversal',
                success: !isTraversal && !isSensitive,
                isTraversal,
                isSensitive,
                normalizedPath,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(result);
            this.updateSummary(result);

            if (result.success) {
                console.log('✅ Aucun path traversal détecté');
            } else {
                console.log(`❌ Path traversal détecté: ${normalizedPath}`);
            }

            return result;

        } catch (error) {
            const result = {
                testName,
                type: 'Path Traversal',
                success: false,
                error: error.message,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(result);
            this.updateSummary(result);

            console.log(`❌ Erreur test path traversal: ${error.message}`);
            return result;
        }
    }

    async testResourceExhaustion(testName, largeHTML) {
        console.log(`\n💥 Test Resource Exhaustion: ${testName}`);

        const startTime = Date.now();
        const startMemory = process.memoryUsage().heapUsed;

        try {
            const page = await this.browser.newPage();

            // Définir un timeout pour éviter les blocages
            page.setDefaultTimeout(30000);

            // Charger le contenu volumineux
            await page.setContent(largeHTML, {
                waitUntil: 'networkidle0',
                timeout: 25000
            });

            // Attendre que tout soit chargé
            await page.waitForTimeout(2000);

            // Tenter de générer le PDF
            const pdfBuffer = await page.pdf({
                format: 'A4',
                printBackground: true,
                timeout: 20000
            });

            const endTime = Date.now();
            const endMemory = process.memoryUsage().heapUsed;
            const duration = endTime - startTime;
            const memoryUsed = endMemory - startMemory;

            // Vérifier les limites
            const maxDuration = 30000; // 30 secondes max
            const maxMemory = 500 * 1024 * 1024; // 500 MB max
            const maxFileSize = 50 * 1024 * 1024; // 50 MB max

            const isDurationOk = duration < maxDuration;
            const isMemoryOk = memoryUsed < maxMemory;
            const isFileSizeOk = pdfBuffer.length < maxFileSize;

            const result = {
                testName,
                type: 'Resource Exhaustion',
                success: isDurationOk && isMemoryOk && isFileSizeOk,
                duration,
                memoryUsed,
                fileSize: pdfBuffer.length,
                limits: {
                    maxDuration,
                    maxMemory,
                    maxFileSize
                },
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(result);
            this.updateSummary(result);

            if (result.success) {
                console.log(`✅ Ressources OK: ${duration}ms, ${(memoryUsed / 1024 / 1024).toFixed(2)} MB, ${(pdfBuffer.length / 1024 / 1024).toFixed(2)} MB`);
            } else {
                console.log(`❌ Dépassement ressources: Durée=${duration}ms, Mémoire=${(memoryUsed / 1024 / 1024).toFixed(2)} MB, Fichier=${(pdfBuffer.length / 1024 / 1024).toFixed(2)} MB`);
            }

            await page.close();
            return result;

        } catch (error) {
            const endTime = Date.now();
            const endMemory = process.memoryUsage().heapUsed;
            const duration = endTime - startTime;
            const memoryUsed = endMemory - startMemory;

            const result = {
                testName,
                type: 'Resource Exhaustion',
                success: false,
                duration,
                memoryUsed,
                error: error.message,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(result);
            this.updateSummary(result);

            console.log(`❌ Erreur resource exhaustion: ${error.message} (${duration}ms, ${(memoryUsed / 1024 / 1024).toFixed(2)} MB)`);
            return result;
        }
    }

    updateSummary(result) {
        this.results.summary.totalTests++;

        if (result.success) {
            this.results.summary.passedTests++;
        } else {
            this.results.summary.failedTests++;

            // Ajouter aux vulnérabilités si c'est un échec
            this.results.summary.vulnerabilities.push({
                type: result.type,
                testName: result.testName,
                details: result
            });
        }
    }

    generateReport() {
        return {
            ...this.results,
            generatedAt: new Date().toISOString(),
            phase: '5.8',
            description: 'Tests de sécurité génération PDF'
        };
    }

    saveReport(filename = 'pdf-security-report.json') {
        const report = this.generateReport();
        const reportPath = path.join(__dirname, filename);

        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n🔒 Rapport de sécurité sauvegardé: ${reportPath}`);

        return report;
    }
}

// Fonction principale
async function runSecurityTests() {
    const tester = new PDFSecurityTester();

    try {
        await tester.init();

        // Tests XSS
        await tester.testXSSInjection(
            'XSS Script Tag',
            `<html><body><script>alert('XSS')</script><h1>Test XSS</h1></body></html>`
        );

        await tester.testXSSInjection(
            'XSS Event Handler',
            `<html><body><img src=x onerror=alert('XSS')><h1>Test XSS</h1></body></html>`
        );

        await tester.testXSSInjection(
            'XSS JavaScript URL',
            `<html><body><a href="javascript:alert('XSS')">Click me</a><h1>Test XSS</h1></body></html>`
        );

        // Tests Path Traversal
        await tester.testPathTraversal(
            'Path Traversal Simple',
            '../../../etc/passwd'
        );

        await tester.testPathTraversal(
            'Path Traversal Windows',
            '..\\..\\..\\Windows\\System32\\config\\sam'
        );

        await tester.testPathTraversal(
            'Path Normal',
            'uploads/pdf-builder-pro/test.pdf'
        );

        // Tests Resource Exhaustion
        const largeHTML = `
            <!DOCTYPE html>
            <html>
            <head><title>Large Document</title></head>
            <body>
                <h1>Document Volumineux</h1>
                ${Array.from({length: 1000}, (_, i) =>
                    `<div>Répétition ${i}: ${'A'.repeat(1000)}</div>`
                ).join('')}
                <table>
                    ${Array.from({length: 500}, (_, i) =>
                        `<tr>${Array.from({length: 20}, (_, j) =>
                            `<td>Cellule ${i}-${j}: ${'Data'.repeat(100)}</td>`
                        ).join('')}</tr>`
                    ).join('')}
                </table>
            </body>
            </html>
        `;

        await tester.testResourceExhaustion(
            'Large HTML Document',
            largeHTML
        );

        // Générer le rapport
        const report = tester.saveReport('phase5.8-security-report.json');

        console.log('\n🔒 RÉSULTATS SÉCURITÉ:');
        console.log(`Tests totaux: ${report.summary.totalTests}`);
        console.log(`Réussis: ${report.summary.passedTests}`);
        console.log(`Échoués: ${report.summary.failedTests}`);
        console.log(`Vulnérabilités: ${report.summary.vulnerabilities.length}`);

        if (report.summary.vulnerabilities.length > 0) {
            console.log('\n🚨 VULNÉRABILITÉS DÉTECTÉES:');
            report.summary.vulnerabilities.forEach(vuln => {
                console.log(`- ${vuln.type}: ${vuln.testName}`);
            });
        }

    } catch (error) {
        console.error('❌ Erreur lors des tests de sécurité:', error);
    } finally {
        await tester.close();
    }
}

// Exécuter les tests
if (require.main === module) {
    runSecurityTests().catch(console.error);
}

module.exports = PDFSecurityTester;
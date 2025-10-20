#!/usr/bin/env node

/**
 * Script de test de performance Phase 5.8
 * Mesure les métriques de génération PDF duale
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class PDFPerformanceTester {
    constructor() {
        this.results = {
            tests: [],
            summary: {
                totalTests: 0,
                successfulTests: 0,
                failedTests: 0,
                averageTime: 0,
                minTime: Infinity,
                maxTime: 0,
                totalTime: 0
            }
        };
        this.browser = null;
    }

    async init() {
        console.log('🚀 Initialisation du testeur de performance PDF...');

        this.browser = await puppeteer.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--single-process',
                '--disable-gpu'
            ]
        });

        console.log('✅ Browser Puppeteer initialisé');
    }

    async close() {
        if (this.browser) {
            await this.browser.close();
            console.log('🛑 Browser fermé');
        }
    }

    async testPDFGeneration(testName, htmlContent, options = {}) {
        const startTime = Date.now();

        try {
            console.log(`\n📄 Test: ${testName}`);

            const page = await this.browser.newPage();

            // Configuration pour performance
            await page.setViewport({ width: 794, height: 1123 }); // A4
            await page.setUserAgent('PDF-Generator-Test/1.0');

            // Charger le contenu HTML
            await page.setContent(htmlContent, {
                waitUntil: 'networkidle0',
                timeout: 30000
            });

            // Attendre que tout soit chargé
            await page.waitForTimeout(1000);

            // Générer le PDF
            const pdfBuffer = await page.pdf({
                format: 'A4',
                printBackground: true,
                margin: {
                    top: '20px',
                    right: '20px',
                    bottom: '20px',
                    left: '20px'
                },
                ...options
            });

            const endTime = Date.now();
            const duration = endTime - startTime;

            // Calculer la taille du fichier
            const fileSize = pdfBuffer.length;

            const result = {
                testName,
                success: true,
                duration,
                fileSize,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(result);
            this.updateSummary(result);

            console.log(`✅ Succès: ${duration}ms, Taille: ${(fileSize / 1024).toFixed(2)} KB`);

            await page.close();

            return result;

        } catch (error) {
            const endTime = Date.now();
            const duration = endTime - startTime;

            const result = {
                testName,
                success: false,
                duration,
                error: error.message,
                timestamp: new Date().toISOString()
            };

            this.results.tests.push(result);
            this.updateSummary(result);

            console.log(`❌ Échec: ${error.message} (${duration}ms)`);

            return result;
        }
    }

    updateSummary(result) {
        this.results.summary.totalTests++;

        if (result.success) {
            this.results.summary.successfulTests++;
        } else {
            this.results.summary.failedTests++;
        }

        if (result.success) {
            this.results.summary.totalTime += result.duration;
            this.results.summary.minTime = Math.min(this.results.summary.minTime, result.duration);
            this.results.summary.maxTime = Math.max(this.results.summary.maxTime, result.duration);
        }
    }

    generateReport() {
        const summary = this.results.summary;

        if (summary.successfulTests > 0) {
            summary.averageTime = summary.totalTime / summary.successfulTests;
        }

        summary.minTime = summary.minTime === Infinity ? 0 : summary.minTime;

        return {
            ...this.results,
            generatedAt: new Date().toISOString(),
            phase: '5.8',
            description: 'Tests de performance génération PDF duale'
        };
    }

    saveReport(filename = 'pdf-performance-report.json') {
        const report = this.generateReport();
        const reportPath = path.join(__dirname, filename);

        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n📊 Rapport sauvegardé: ${reportPath}`);

        return report;
    }
}

// Fonction principale
async function runPerformanceTests() {
    const tester = new PDFPerformanceTester();

    try {
        await tester.init();

        // Test 1: PDF simple
        const simpleHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test Simple PDF</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    h1 { color: #333; }
                    p { line-height: 1.6; }
                </style>
            </head>
            <body>
                <h1>Test de Performance PDF</h1>
                <p>Ceci est un test simple de génération PDF pour mesurer les performances de base.</p>
                <p>Timestamp: ${new Date().toISOString()}</p>
            </body>
            </html>
        `;

        await tester.testPDFGeneration('PDF Simple', simpleHTML);

        // Test 2: PDF avec tableau
        const tableHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test Table PDF</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    h1 { color: #333; }
                </style>
            </head>
            <body>
                <h1>Test PDF avec Tableau</h1>
                <table>
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Quantité</th>
                            <th>Prix</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.from({length: 10}, (_, i) => `
                            <tr>
                                <td>Produit ${i + 1}</td>
                                <td>${Math.floor(Math.random() * 10) + 1}</td>
                                <td>${(Math.random() * 100).toFixed(2)} €</td>
                                <td>${(Math.random() * 500).toFixed(2)} €</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                <p>Total: ${(Math.random() * 2000).toFixed(2)} €</p>
            </body>
            </html>
        `;

        await tester.testPDFGeneration('PDF avec Tableau', tableHTML);

        // Test 3: PDF complexe (simulé WooCommerce)
        const complexHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Facture WooCommerce</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 20px; margin-bottom: 30px; }
                    .company-info { float: right; text-align: right; }
                    .customer-info { margin-bottom: 30px; }
                    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .total { font-weight: bold; font-size: 1.2em; }
                    .footer { margin-top: 50px; font-size: 0.8em; color: #666; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>FACTURE</h1>
                    <div class="company-info">
                        <strong>Ma Société SARL</strong><br>
                        123 Rue de la Paix<br>
                        75001 Paris<br>
                        contact@masociete.com
                    </div>
                </div>

                <div class="customer-info">
                    <h3>Informations Client</h3>
                    <p><strong>Jean Dupont</strong><br>
                    456 Avenue des Champs<br>
                    75008 Paris<br>
                    jean.dupont@email.com</p>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th>Qté</th>
                            <th>Prix</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${Array.from({length: 25}, (_, i) => `
                            <tr>
                                <td>Produit WooCommerce ${i + 1} - Description détaillée du produit avec des caractéristiques techniques importantes</td>
                                <td>${Math.floor(Math.random() * 5) + 1}</td>
                                <td>${(Math.random() * 200 + 10).toFixed(2)} €</td>
                                <td>${(Math.random() * 1000 + 50).toFixed(2)} €</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>

                <div style="text-align: right; margin-top: 20px;">
                    <p class="total">Total HT: ${(Math.random() * 5000 + 1000).toFixed(2)} €</p>
                    <p class="total">TVA (20%): ${(Math.random() * 1000 + 200).toFixed(2)} €</p>
                    <p class="total">Total TTC: ${(Math.random() * 6000 + 1200).toFixed(2)} €</p>
                </div>

                <div class="footer">
                    <p>Merci pour votre confiance. Conditions de paiement: 30 jours.</p>
                    <p>Document généré le ${new Date().toLocaleDateString('fr-FR')}</p>
                </div>
            </body>
            </html>
        `;

        await tester.testPDFGeneration('PDF Complexe WooCommerce', complexHTML);

        // Générer le rapport
        const report = tester.saveReport('phase5.8-performance-baseline.json');

        console.log('\n📊 RÉSULTATS FINAUX:');
        console.log(`Tests totaux: ${report.summary.totalTests}`);
        console.log(`Succès: ${report.summary.successfulTests}`);
        console.log(`Échecs: ${report.summary.failedTests}`);
        console.log(`Temps moyen: ${report.summary.averageTime.toFixed(2)}ms`);
        console.log(`Temps min: ${report.summary.minTime}ms`);
        console.log(`Temps max: ${report.summary.maxTime}ms`);

    } catch (error) {
        console.error('❌ Erreur lors des tests:', error);
    } finally {
        await tester.close();
    }
}

// Exécuter les tests
if (require.main === module) {
    runPerformanceTests().catch(console.error);
}

module.exports = PDFPerformanceTester;
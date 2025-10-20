#!/usr/bin/env node

/**
 * Script de validation croisée-navigateur Phase 5.8
 * Teste la génération PDF sur différents navigateurs
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class CrossBrowserValidator {
    constructor() {
        this.results = {
            browsers: {},
            summary: {
                totalTests: 0,
                successfulTests: 0,
                failedTests: 0,
                compatibilityScore: 0
            }
        };
    }

    async testBrowser(browserName, launchOptions = {}) {
        console.log(`\n🌐 Test navigateur: ${browserName}`);

        let browser = null;
        const browserResults = {
            tests: [],
            success: true,
            error: null
        };

        try {
            browser = await puppeteer.launch({
                headless: true,
                ...launchOptions
            });

            // Test 1: PDF simple
            const simpleResult = await this.testPDFGeneration(
                browser,
                'PDF Simple',
                this.getSimpleHTML()
            );
            browserResults.tests.push(simpleResult);

            // Test 2: PDF avec CSS complexe
            const cssResult = await this.testPDFGeneration(
                browser,
                'PDF CSS Complexe',
                this.getComplexCSSHTML()
            );
            browserResults.tests.push(cssResult);

            // Test 3: PDF avec images
            const imageResult = await this.testPDFGeneration(
                browser,
                'PDF avec Images',
                this.getImageHTML()
            );
            browserResults.tests.push(imageResult);

            // Calculer le score de succès pour ce navigateur
            const successfulTests = browserResults.tests.filter(t => t.success).length;
            browserResults.successRate = (successfulTests / browserResults.tests.length) * 100;

        } catch (error) {
            browserResults.success = false;
            browserResults.error = error.message;
            console.log(`❌ Erreur navigateur ${browserName}: ${error.message}`);
        } finally {
            if (browser) {
                await browser.close();
            }
        }

        this.results.browsers[browserName] = browserResults;
        this.updateSummary(browserResults);

        console.log(`📊 ${browserName}: ${browserResults.successRate || 0}% de succès`);
        return browserResults;
    }

    async testPDFGeneration(browser, testName, htmlContent) {
        const startTime = Date.now();

        try {
            const page = await browser.newPage();

            // Configuration standardisée
            await page.setViewport({ width: 794, height: 1123 }); // A4
            await page.setUserAgent('PDF-Generator-CrossBrowser-Test/1.0');

            // Charger le contenu
            await page.setContent(htmlContent, {
                waitUntil: 'networkidle0',
                timeout: 15000
            });

            // Attendre le rendu complet
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
                }
            });

            const endTime = Date.now();
            const duration = endTime - startTime;

            const result = {
                testName,
                success: true,
                duration,
                fileSize: pdfBuffer.length,
                timestamp: new Date().toISOString()
            };

            console.log(`  ✅ ${testName}: ${duration}ms, ${(pdfBuffer.length / 1024).toFixed(2)} KB`);

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

            console.log(`  ❌ ${testName}: ${error.message} (${duration}ms)`);

            return result;
        }
    }

    getSimpleHTML() {
        return `
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
                <h1>Test de Compatibilité Navigateur</h1>
                <p>Ceci est un test simple pour vérifier la compatibilité PDF.</p>
                <p>Timestamp: ${new Date().toISOString()}</p>
                <ul>
                    <li>Texte normal</li>
                    <li><strong>Texte en gras</strong></li>
                    <li><em>Texte en italique</em></li>
                </ul>
            </body>
            </html>
        `;
    }

    getComplexCSSHTML() {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test CSS Complexe</title>
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

                    body {
                        font-family: 'Roboto', Arial, sans-serif;
                        margin: 40px;
                        line-height: 1.6;
                        color: #333;
                    }

                    .header {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 20px;
                        border-radius: 8px;
                        margin-bottom: 30px;
                    }

                    .grid {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 20px;
                        margin: 20px 0;
                    }

                    .card {
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        padding: 15px;
                        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                    }

                    .flex-container {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin: 20px 0;
                    }

                    @media print {
                        body { margin: 0; }
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Test CSS Avancé</h1>
                    <p>Validation des fonctionnalités CSS modernes</p>
                </div>

                <div class="grid">
                    <div class="card">
                        <h3>Grid Layout</h3>
                        <p>Test des CSS Grid pour la mise en page moderne.</p>
                    </div>
                    <div class="card">
                        <h3>Flexbox</h3>
                        <p>Test des flexbox pour l'alignement flexible.</p>
                    </div>
                </div>

                <div class="flex-container">
                    <span>Élément gauche</span>
                    <span>Élément droite</span>
                </div>

                <div class="no-print">
                    <p>Cette section ne devrait pas apparaître dans le PDF.</p>
                </div>
            </body>
            </html>
        `;
    }

    getImageHTML() {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test Images PDF</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 40px; }
                    .image-container { margin: 20px 0; text-align: center; }
                    img { max-width: 100%; height: auto; border: 1px solid #ddd; }
                    .caption { font-size: 0.9em; color: #666; margin-top: 5px; }
                </style>
            </head>
            <body>
                <h1>Test d'Images dans PDF</h1>

                <div class="image-container">
                    <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjNmNGY2Ii8+CiAgPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtc2l6ZT0iMTgiIGZpbGw9IiMzMzMiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5JTUFHRSBURVNUL0w+CiAgPHN2Zz4K" alt="Test SVG" />
                    <div class="caption">Image SVG encodée en base64</div>
                </div>

                <div class="image-container">
                    <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==" alt="Test PNG" />
                    <div class="caption">Image PNG encodée en base64 (pixel transparent)</div>
                </div>

                <p>Test de rendu d'images dans les PDF générés.</p>
                <p>Timestamp: ${new Date().toISOString()}</p>
            </body>
            </html>
        `;
    }

    updateSummary(browserResults) {
        const totalTests = browserResults.tests.length;
        const successfulTests = browserResults.tests.filter(t => t.success).length;

        this.results.summary.totalTests += totalTests;
        this.results.summary.successfulTests += successfulTests;
        this.results.summary.failedTests += totalTests - successfulTests;
    }

    generateReport() {
        // Calculer le score de compatibilité global
        const browserCount = Object.keys(this.results.browsers).length;
        let totalSuccessRate = 0;

        for (const browser of Object.values(this.results.browsers)) {
            totalSuccessRate += browser.successRate || 0;
        }

        this.results.summary.compatibilityScore = browserCount > 0 ?
            totalSuccessRate / browserCount : 0;

        return {
            ...this.results,
            generatedAt: new Date().toISOString(),
            phase: '5.8',
            description: 'Validation croisée-navigateur génération PDF'
        };
    }

    saveReport(filename = 'pdf-cross-browser-report.json') {
        const report = this.generateReport();
        const reportPath = path.join(__dirname, filename);

        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n🌐 Rapport cross-browser sauvegardé: ${reportPath}`);

        return report;
    }
}

// Fonction principale
async function runCrossBrowserTests() {
    const validator = new CrossBrowserValidator();

    try {
        // Test avec Chrome (par défaut)
        await validator.testBrowser('Chrome', {
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage'
            ]
        });

        // Test avec Chromium (si disponible)
        try {
            await validator.testBrowser('Chromium', {
                executablePath: '/usr/bin/chromium-browser', // Linux
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage'
                ]
            });
        } catch (error) {
            console.log('⚠️ Chromium non disponible, test ignoré');
        }

        // Test avec Chrome en mode headless nouveau
        await validator.testBrowser('Chrome-New-Headless', {
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage'
            ]
        });

        // Générer le rapport
        const report = validator.saveReport('phase5.8-cross-browser-report.json');

        console.log('\n🌐 RÉSULTATS CROSS-BROWSER:');
        console.log(`Tests totaux: ${report.summary.totalTests}`);
        console.log(`Succès: ${report.summary.successfulTests}`);
        console.log(`Échecs: ${report.summary.failedTests}`);
        console.log(`Score de compatibilité: ${report.summary.compatibilityScore.toFixed(2)}%`);

        console.log('\n📊 RÉSULTATS PAR NAVIGATEUR:');
        for (const [browser, results] of Object.entries(report.browsers)) {
            console.log(`${browser}: ${results.successRate?.toFixed(2) || 0}% (${results.tests.filter(t => t.success).length}/${results.tests.length})`);
        }

    } catch (error) {
        console.error('❌ Erreur lors des tests cross-browser:', error);
    }
}

// Exécuter les tests
if (require.main === module) {
    runCrossBrowserTests().catch(console.error);
}

module.exports = CrossBrowserValidator;
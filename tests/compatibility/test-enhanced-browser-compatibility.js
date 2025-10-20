#!/usr/bin/env node

/**
 * Script de test de compatibilité navigateur amélioré Phase 5.8
 * Teste la génération PDF sur tous les navigateurs disponibles
 */

const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

class EnhancedBrowserCompatibilityTester {
    constructor() {
        this.results = {
            browsers: {},
            summary: {
                totalTests: 0,
                successfulTests: 0,
                failedTests: 0,
                compatibilityScore: 0,
                browserCount: 0,
                averageScore: 0
            }
        };
        this.browser = null;
    }

    async init() {
        console.log('🌐 Initialisation du testeur de compatibilité amélioré...');
    }

    async close() {
        if (this.browser) {
            await this.browser.close();
            console.log('🛑 Browser fermé');
        }
    }

    async testBrowser(browserName, browserConfig = {}) {
        console.log(`\n🌐 Test navigateur: ${browserName}`);

        let browser = null;
        const browserResults = {
            tests: [],
            success: true,
            error: null,
            score: 0,
            version: 'unknown'
        };

        try {
            // Configuration par défaut
            const defaultConfig = {
                headless: true,
                args: [
                    '--no-sandbox',
                    '--disable-setuid-sandbox',
                    '--disable-dev-shm-usage',
                    '--disable-web-security',
                    '--disable-features=VizDisplayCompositor'
                ]
            };

            // Fusionner avec la config spécifique
            const launchConfig = { ...defaultConfig, ...browserConfig };

            browser = await puppeteer.launch(launchConfig);

            // Récupérer la version du navigateur
            const version = await browser.version();
            browserResults.version = version;
            console.log(`📋 Version: ${version}`);

            // Tests étendus pour chaque navigateur
            const testScenarios = [
                { name: 'PDF Simple', content: this.getSimpleHTML() },
                { name: 'PDF CSS Avancé', content: this.getAdvancedCSSHTML() },
                { name: 'PDF avec Images', content: this.getImageHTML() },
                { name: 'PDF Complexe WooCommerce', content: this.getWooCommerceHTML() },
                { name: 'PDF avec JavaScript', content: this.getJavaScriptHTML() },
                { name: 'PDF avec Fonts Web', content: this.getWebFontsHTML() }
            ];

            for (const scenario of testScenarios) {
                const result = await this.testPDFGeneration(browser, scenario.name, scenario.content);
                browserResults.tests.push(result);
            }

            // Calculer le score du navigateur
            const successfulTests = browserResults.tests.filter(t => t.success).length;
            browserResults.score = (successfulTests / browserResults.tests.length) * 100;

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

        console.log(`📊 ${browserName}: ${browserResults.score.toFixed(2)}% (${browserResults.tests.filter(t => t.success).length}/${browserResults.tests.length})`);
        return browserResults;
    }

    async testPDFGeneration(browser, testName, htmlContent) {
        const startTime = Date.now();

        try {
            const page = await browser.newPage();

            // Configuration étendue pour la compatibilité
            await page.setViewport({ width: 794, height: 1123 }); // A4
            await page.setUserAgent('PDF-Generator-Compatibility-Test/2.0');

            // Attendre que les fonts soient chargées
            await page.setDefaultTimeout(30000);

            // Charger le contenu avec options étendues
            await page.setContent(htmlContent, {
                waitUntil: 'networkidle0',
                timeout: 20000
            });

            // Attendre le rendu complet (plus long pour les navigateurs plus lents)
            await page.waitForTimeout(2000);

            // Vérifier que le contenu est bien rendu
            const contentHeight = await page.evaluate(() => {
                return Math.max(
                    document.body.scrollHeight,
                    document.body.offsetHeight,
                    document.documentElement.clientHeight,
                    document.documentElement.scrollHeight,
                    document.documentElement.offsetHeight
                );
            });

            if (contentHeight < 100) {
                throw new Error('Contenu non rendu correctement');
            }

            // Générer le PDF avec options optimisées
            const pdfBuffer = await page.pdf({
                format: 'A4',
                printBackground: true,
                margin: {
                    top: '20px',
                    right: '20px',
                    bottom: '20px',
                    left: '20px'
                },
                preferCSSPageSize: true,
                displayHeaderFooter: false
            });

            const endTime = Date.now();
            const duration = endTime - startTime;

            // Validation du PDF généré
            if (!pdfBuffer || pdfBuffer.length < 1000) {
                throw new Error('PDF généré invalide ou trop petit');
            }

            const result = {
                testName,
                success: true,
                duration,
                fileSize: pdfBuffer.length,
                contentHeight,
                timestamp: new Date().toISOString()
            };

            console.log(`  ✅ ${testName}: ${duration}ms, ${contentHeight}px, ${(pdfBuffer.length / 1024).toFixed(2)} KB`);

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
                <meta charset="UTF-8">
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 40px;
                        line-height: 1.6;
                        color: #333;
                    }
                    h1 { color: #2c3e50; margin-bottom: 20px; }
                    p { margin-bottom: 15px; }
                </style>
            </head>
            <body>
                <h1>Test de Compatibilité Navigateur</h1>
                <p>Ceci est un test simple pour vérifier la compatibilité PDF basique.</p>
                <p>Éléments testés : Texte, couleurs, marges, polices standard.</p>
                <p>Timestamp: ${new Date().toISOString()}</p>
                <ul>
                    <li>Texte normal</li>
                    <li><strong>Texte en gras</strong></li>
                    <li><em>Texte en italique</em></li>
                    <li><u>Texte souligné</u></li>
                </ul>
            </body>
            </html>
        `;
    }

    getAdvancedCSSHTML() {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test CSS Avancé</title>
                <meta charset="UTF-8">
                <style>
                    @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');

                    * {
                        box-sizing: border-box;
                    }

                    body {
                        font-family: 'Roboto', Arial, sans-serif;
                        margin: 40px;
                        line-height: 1.6;
                        color: #333;
                        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
                        min-height: 100vh;
                    }

                    .container {
                        max-width: 800px;
                        margin: 0 auto;
                        background: white;
                        border-radius: 10px;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                        overflow: hidden;
                    }

                    .header {
                        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                        color: white;
                        padding: 30px;
                        text-align: center;
                    }

                    .content {
                        padding: 30px;
                    }

                    .grid {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                        gap: 20px;
                        margin: 30px 0;
                    }

                    .card {
                        border: 1px solid #e1e8ed;
                        border-radius: 8px;
                        padding: 20px;
                        background: #f8f9fa;
                        transition: transform 0.3s ease;
                    }

                    .card:hover {
                        transform: translateY(-5px);
                    }

                    .flex-container {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        margin: 20px 0;
                        padding: 15px;
                        background: #e9ecef;
                        border-radius: 5px;
                    }

                    .badge {
                        display: inline-block;
                        padding: 4px 8px;
                        background: #28a745;
                        color: white;
                        border-radius: 12px;
                        font-size: 12px;
                        font-weight: bold;
                    }

                    @media print {
                        body {
                            margin: 0;
                            background: white !important;
                        }
                        .no-print {
                            display: none !important;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h1>🧪 Test CSS Avancé</h1>
                        <p>Validation des fonctionnalités CSS modernes</p>
                    </div>

                    <div class="content">
                        <div class="grid">
                            <div class="card">
                                <h3>🎨 CSS Grid</h3>
                                <p>Test des CSS Grid pour la mise en page moderne et responsive.</p>
                                <span class="badge">Grid</span>
                            </div>
                            <div class="card">
                                <h3>📱 Flexbox</h3>
                                <p>Test des flexbox pour l'alignement flexible des éléments.</p>
                                <span class="badge">Flexbox</span>
                            </div>
                            <div class="card">
                                <h3>🎭 Animations</h3>
                                <p>Test des transitions CSS et effets visuels modernes.</p>
                                <span class="badge">Animations</span>
                            </div>
                            <div class="card">
                                <h3>📱 Responsive</h3>
                                <p>Test du design responsive et des media queries.</p>
                                <span class="badge">Responsive</span>
                            </div>
                        </div>

                        <div class="flex-container">
                            <div>
                                <strong>Élément gauche</strong><br>
                                <small>Avec contenu secondaire</small>
                            </div>
                            <div>
                                <strong>Élément droite</strong><br>
                                <small>Aligné à droite</small>
                            </div>
                        </div>

                        <div class="no-print">
                            <p style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107;">
                                <strong>ℹ️ Note:</strong> Cette section ne devrait pas apparaître dans le PDF généré.
                            </p>
                        </div>

                        <p><strong>Timestamp:</strong> ${new Date().toLocaleString('fr-FR')}</p>
                    </div>
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
                <title>Test Images Avancé</title>
                <meta charset="UTF-8">
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 40px;
                        line-height: 1.6;
                        color: #333;
                    }
                    .image-gallery {
                        display: grid;
                        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                        gap: 20px;
                        margin: 30px 0;
                    }
                    .image-container {
                        text-align: center;
                        border: 1px solid #ddd;
                        border-radius: 8px;
                        padding: 15px;
                        background: #f9f9f9;
                    }
                    img {
                        max-width: 100%;
                        height: auto;
                        border: 1px solid #ccc;
                        border-radius: 4px;
                    }
                    .caption {
                        font-size: 0.9em;
                        color: #666;
                        margin-top: 8px;
                        font-style: italic;
                    }
                    .image-info {
                        background: #e7f3ff;
                        padding: 10px;
                        border-radius: 4px;
                        margin-top: 10px;
                        font-size: 0.8em;
                        color: #0066cc;
                    }
                </style>
            </head>
            <body>
                <h1>🖼️ Test d'Images Avancé</h1>
                <p>Test complet du rendu d'images dans les PDFs générés.</p>

                <div class="image-gallery">
                    <div class="image-container">
                        <img src="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9PSIyMDAiIHZpZXdCb3g9IjAgMCAyMDAgMjAwIiBmaWxsPSJub25lIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgo8Y2lyY2xlIGN4PSIxMDAiIGN5PSIxMDAiIHI9IjgwIiBmaWxsPSIjNDk0NTQ1Ii8+Cjx0ZXh0IHg9IjEwMCIgeT0iMTEwIiBmb250LWZhbWlseT0iQXJpYWwsIHNhbnMtc2VyaWYiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5TVkc8L3RleHQ+Cjwvc3ZnPgo=" alt="SVG Circle" />
                        <div class="caption">Image SVG encodée en base64</div>
                        <div class="image-info">Format: SVG, Encodage: Base64</div>
                    </div>

                    <div class="image-container">
                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==" alt="PNG Pixel" />
                        <div class="caption">Image PNG encodée en base64</div>
                        <div class="image-info">Format: PNG, Taille: 1x1px</div>
                    </div>

                    <div class="image-container">
                        <img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAYEBQYFBAYGBQYHBwYIChAKCgkJChQODwwQFxQYGBcUFhYaHSUfGhsjHBYWICwgIyYnKSopGR8tMC0oMCUoKSj/2wBDAQcHBwoIChMKChMoGhYaKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCgoKCj/wAARCAAIAAoDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAv/xAAhEAACAQMDBQAAAAAAAAAAAAABAgMABAUGIWGRkqGx0f/EABUBAQEAAAAAAAAAAAAAAAAAAAMF/8QAGhEAAgIDAAAAAAAAAAAAAAAAAAECEgMRkf/aAAwDAQACEQMRAD8AltJagyeH0AthI5xdrLcNM91BF5pX2HaH9bcfaSXWGaRmknyJckliyjqTzSlT54b6bk+h0R+IRjWjBqO6O2mhP//Z" alt="JPEG Test" />
                        <div class="caption">Image JPEG encodée en base64</div>
                        <div class="image-info">Format: JPEG, Qualité: Test</div>
                    </div>

                    <div class="image-container">
                        <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="GIF Test" />
                        <div class="caption">Image GIF animée (1px)</div>
                        <div class="image-info">Format: GIF, Animé: Non</div>
                    </div>
                </div>

                <h2>📊 Résumé des Tests d'Images</h2>
                <ul>
                    <li>✅ Images SVG vectorielles</li>
                    <li>✅ Images raster (PNG, JPEG)</li>
                    <li>✅ Encodage Base64</li>
                    <li>✅ Images transparentes</li>
                    <li>✅ Redimensionnement automatique</li>
                </ul>

                <p><strong>Timestamp:</strong> ${new Date().toISOString()}</p>
            </body>
            </html>
        `;
    }

    getWooCommerceHTML() {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Facture WooCommerce Avancée</title>
                <meta charset="UTF-8">
                <style>
                    * { box-sizing: border-box; }
                    body {
                        font-family: 'Helvetica Neue', Arial, sans-serif;
                        margin: 0;
                        padding: 20px;
                        color: #333;
                        line-height: 1.4;
                    }
                    .invoice-header {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        margin-bottom: 40px;
                        border-bottom: 3px solid #007cba;
                        padding-bottom: 20px;
                    }
                    .company-info {
                        flex: 1;
                        text-align: left;
                    }
                    .invoice-info {
                        flex: 1;
                        text-align: right;
                        background: #f8f9fa;
                        padding: 15px;
                        border-radius: 5px;
                    }
                    .invoice-title {
                        font-size: 2.5em;
                        color: #007cba;
                        margin: 0 0 10px 0;
                        font-weight: bold;
                    }
                    .customer-info {
                        background: #e9ecef;
                        padding: 20px;
                        border-radius: 5px;
                        margin-bottom: 30px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin: 20px 0;
                        font-size: 0.9em;
                    }
                    th, td {
                        border: 1px solid #dee2e6;
                        padding: 12px 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #007cba;
                        color: white;
                        font-weight: 600;
                        text-transform: uppercase;
                        font-size: 0.8em;
                    }
                    tbody tr:nth-child(even) {
                        background-color: #f8f9fa;
                    }
                    tbody tr:hover {
                        background-color: #e9ecef;
                    }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    .total-row {
                        font-weight: bold;
                        font-size: 1.1em;
                        background: #007cba !important;
                        color: white;
                    }
                    .summary {
                        display: flex;
                        justify-content: flex-end;
                        margin-top: 20px;
                    }
                    .summary-table {
                        width: 300px;
                    }
                    .summary-table td {
                        padding: 8px 12px;
                    }
                    .grand-total {
                        background: #28a745;
                        color: white;
                        font-size: 1.2em;
                        font-weight: bold;
                    }
                    .footer {
                        margin-top: 50px;
                        padding-top: 20px;
                        border-top: 1px solid #dee2e6;
                        font-size: 0.8em;
                        color: #6c757d;
                        text-align: center;
                    }
                    .badge {
                        display: inline-block;
                        padding: 3px 6px;
                        border-radius: 3px;
                        font-size: 0.7em;
                        font-weight: bold;
                        text-transform: uppercase;
                    }
                    .badge-success {
                        background: #28a745;
                        color: white;
                    }
                    .badge-warning {
                        background: #ffc107;
                        color: #212529;
                    }
                </style>
            </head>
            <body>
                <div class="invoice-header">
                    <div class="company-info">
                        <h1 style="color: #007cba; margin: 0 0 10px 0;">Ma Société SARL</h1>
                        <p style="margin: 5px 0;">123 Rue de la Paix<br>75001 Paris, France</p>
                        <p style="margin: 5px 0;">contact@masociete.com<br>Tel: 01 23 45 67 89</p>
                        <p style="margin: 5px 0;"><strong>SIRET:</strong> 123 456 789 00012</p>
                    </div>
                    <div class="invoice-info">
                        <div class="invoice-title">FACTURE</div>
                        <p><strong>N°:</strong> INV-2025-001</p>
                        <p><strong>Date:</strong> ${new Date().toLocaleDateString('fr-FR')}</p>
                        <p><strong>Échéance:</strong> ${new Date(Date.now() + 30*24*60*60*1000).toLocaleDateString('fr-FR')}</p>
                        <span class="badge badge-success">Payée</span>
                    </div>
                </div>

                <div class="customer-info">
                    <h3 style="margin-top: 0; color: #007cba;">Informations Client</h3>
                    <p style="margin: 5px 0;"><strong>Jean Dupont</strong></p>
                    <p style="margin: 5px 0;">456 Avenue des Champs-Élysées<br>75008 Paris, France</p>
                    <p style="margin: 5px 0;">jean.dupont@email.com<br>Tel: 06 12 34 56 78</p>
                    <p style="margin: 5px 0;"><strong>Client N°:</strong> CUST-2025-045</p>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 50%;">Description</th>
                            <th class="text-center">Qté</th>
                            <th class="text-right">Prix</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <strong>Produit WooCommerce Premium</strong><br>
                                <small style="color: #6c757d;">Référence: WC-PREM-001 - Catégorie: Électronique</small>
                            </td>
                            <td class="text-center">2</td>
                            <td class="text-right">299,99 €</td>
                            <td class="text-right">599,98 €</td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Service de Configuration</strong><br>
                                <small style="color: #6c757d;">Installation et paramétrage personnalisé</small>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-right">149,99 €</td>
                            <td class="text-right">149,99 €</td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Support Technique 1 an</strong><br>
                                <small style="color: #6c757d;">Assistance téléphonique et email prioritaire</small>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-right">89,99 €</td>
                            <td class="text-right">89,99 €</td>
                        </tr>
                        <tr>
                            <td>
                                <strong>Frais de Port</strong><br>
                                <small style="color: #6c757d;">Livraison express 24h</small>
                            </td>
                            <td class="text-center">1</td>
                            <td class="text-right">15,99 €</td>
                            <td class="text-right">15,99 €</td>
                        </tr>
                    </tbody>
                </table>

                <div class="summary">
                    <table class="summary-table">
                        <tr>
                            <td><strong>Sous-total HT:</strong></td>
                            <td class="text-right">855,95 €</td>
                        </tr>
                        <tr>
                            <td><strong>TVA (20%):</strong></td>
                            <td class="text-right">171,19 €</td>
                        </tr>
                        <tr class="total-row">
                            <td><strong>Total TTC:</strong></td>
                            <td class="text-right">1 027,14 €</td>
                        </tr>
                        <tr class="grand-total">
                            <td><strong>Montant payé:</strong></td>
                            <td class="text-right">1 027,14 €</td>
                        </tr>
                    </table>
                </div>

                <div class="footer">
                    <p><strong>Conditions de paiement:</strong> Règlement à 30 jours - Tout retard sera facturé à 1.5% par mois.</p>
                    <p><strong>IBAN:</strong> FR14 2004 1010 0505 0001 3M02 606 - <strong>BIC:</strong> PSSTFRPPPAR</p>
                    <p>Document généré automatiquement le ${new Date().toLocaleString('fr-FR')} - PDF Builder Pro</p>
                </div>
            </body>
            </html>
        `;
    }

    getJavaScriptHTML() {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test JavaScript dans PDF</title>
                <meta charset="UTF-8">
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 40px;
                        line-height: 1.6;
                    }
                    .js-result {
                        background: #f0f8ff;
                        border: 1px solid #add8e6;
                        border-radius: 5px;
                        padding: 15px;
                        margin: 10px 0;
                    }
                    .success { border-color: #28a745; background: #d4edda; }
                    .error { border-color: #dc3545; background: #f8d7da; }
                    .counter {
                        font-size: 2em;
                        font-weight: bold;
                        color: #007cba;
                        text-align: center;
                        margin: 20px 0;
                    }
                </style>
            </head>
            <body>
                <h1>🟨 Test JavaScript dans PDF</h1>
                <p>Cette page contient du JavaScript qui devrait être exécuté avant la génération du PDF.</p>

                <div class="counter" id="counter">0</div>

                <div class="js-result success" id="date-result">
                    <strong>Date actuelle:</strong> <span id="current-date">Chargement...</span>
                </div>

                <div class="js-result success" id="calc-result">
                    <strong>Calcul (2 + 3) * 10:</strong> <span id="calculation">Chargement...</span>
                </div>

                <div class="js-result" id="array-result">
                    <strong>Tableau trié:</strong> <span id="sorted-array">Chargement...</span>
                </div>

                <script>
                    // Fonction compteur
                    let count = 0;
                    const counter = setInterval(() => {
                        count++;
                        document.getElementById('counter').textContent = count;
                        if (count >= 5) {
                            clearInterval(counter);
                        }
                    }, 100);

                    // Date actuelle
                    document.getElementById('current-date').textContent =
                        new Date().toLocaleString('fr-FR');

                    // Calcul simple
                    document.getElementById('calculation').textContent =
                        (2 + 3) * 10;

                    // Manipulation de tableau
                    const numbers = [5, 2, 8, 1, 9, 3];
                    document.getElementById('sorted-array').textContent =
                        numbers.sort((a, b) => a - b).join(', ');

                    // Message de confirmation
                    console.log('JavaScript exécuté avec succès dans le PDF');
                </script>

                <h2>📋 Éléments JavaScript Testés</h2>
                <ul>
                    <li>✅ Variables et fonctions</li>
                    <li>✅ setInterval/clearInterval</li>
                    <li>✅ Manipulation DOM</li>
                    <li>✅ Date et calculs</li>
                    <li>✅ Tri de tableaux</li>
                    <li>✅ Console logging</li>
                </ul>

                <p><strong>Timestamp:</strong> ${new Date().toISOString()}</p>
            </body>
            </html>
        `;
    }

    getWebFontsHTML() {
        return `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Test Fonts Web dans PDF</title>
                <meta charset="UTF-8">
                <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Open+Sans:wght@300;400;600;800&family=Lato:wght@300;400;900&family=Montserrat:wght@200;400;600;800&display=swap" rel="stylesheet">
                <style>
                    body {
                        margin: 40px;
                        line-height: 1.6;
                        color: #333;
                    }
                    .font-sample {
                        margin: 30px 0;
                        padding: 20px;
                        border: 1px solid #e1e8ed;
                        border-radius: 8px;
                        background: #f8f9fa;
                    }
                    .font-title {
                        font-size: 1.2em;
                        font-weight: bold;
                        margin-bottom: 10px;
                        color: #007cba;
                    }
                    .roboto { font-family: 'Roboto', sans-serif; }
                    .roboto-light { font-weight: 300; }
                    .roboto-normal { font-weight: 400; }
                    .roboto-medium { font-weight: 500; }
                    .roboto-bold { font-weight: 700; }

                    .opensans { font-family: 'Open Sans', sans-serif; }
                    .opensans-light { font-weight: 300; }
                    .opensans-normal { font-weight: 400; }
                    .opensans-semibold { font-weight: 600; }
                    .opensans-bold { font-weight: 800; }

                    .lato { font-family: 'Lato', sans-serif; }
                    .lato-light { font-weight: 300; }
                    .lato-normal { font-weight: 400; }
                    .lato-black { font-weight: 900; }

                    .montserrat { font-family: 'Montserrat', sans-serif; }
                    .montserrat-thin { font-weight: 200; }
                    .montserrat-normal { font-weight: 400; }
                    .montserrat-semibold { font-weight: 600; }
                    .montserrat-bold { font-weight: 800; }

                    .fallback {
                        font-family: Arial, Helvetica, sans-serif;
                        background: #fff3cd;
                        padding: 10px;
                        border-left: 4px solid #ffc107;
                        margin: 20px 0;
                    }

                    .special-chars {
                        background: #e7f3ff;
                        padding: 15px;
                        border-radius: 5px;
                        margin: 20px 0;
                        font-size: 1.1em;
                    }
                </style>
            </head>
            <body>
                <h1>🔤 Test des Fonts Web dans PDF</h1>
                <p>Test du rendu des polices Google Fonts dans les PDFs générés.</p>

                <div class="font-sample roboto">
                    <div class="font-title">Roboto (Google Font)</div>
                    <p class="roboto-light">Roboto Light (300): The quick brown fox jumps over the lazy dog.</p>
                    <p class="roboto-normal">Roboto Regular (400): 0123456789 !@#$%^&*()</p>
                    <p class="roboto-medium">Roboto Medium (500): ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏ</p>
                    <p class="roboto-bold">Roboto Bold (700): αβγδεζηθικλμνξοπρστυφχψω</p>
                </div>

                <div class="font-sample opensans">
                    <div class="font-title">Open Sans (Google Font)</div>
                    <p class="opensans-light">Open Sans Light (300): Pack my box with five dozen liquor jugs.</p>
                    <p class="opensans-normal">Open Sans Regular (400): THE FIVE BOXING WIZARDS JUMP QUICKLY.</p>
                    <p class="opensans-semibold">Open Sans Semibold (600): 1234567890</p>
                    <p class="opensans-bold">Open Sans Bold (800): ¡¢£¤¥¦§¨©ª«¬®¯°±²³´µ¶</p>
                </div>

                <div class="font-sample lato">
                    <div class="font-title">Lato (Google Font)</div>
                    <p class="lato-light">Lato Light (300): How vexingly quick daft zebras jump!</p>
                    <p class="lato-normal">Lato Regular (400): THE QUICK BROWN FOX JUMPS OVER THE LAZY DOG.</p>
                    <p class="lato-black">Lato Black (900): ⅛⅜⅝⅞⅓⅔⅕⅖⅗⅘⅙⅚⅛⅜⅝⅞</p>
                </div>

                <div class="font-sample montserrat">
                    <div class="font-title">Montserrat (Google Font)</div>
                    <p class="montserrat-thin">Montserrat Thin (200): Sixty zippers were quickly picked from the woven jute bag.</p>
                    <p class="montserrat-normal">Montserrat Regular (400): THE FIVE BOXING WIZARDS JUMP QUICKLY.</p>
                    <p class="montserrat-semibold">Montserrat Semibold (600): 0123456789</p>
                    <p class="montserrat-bold">Montserrat Bold (800): ♠♣♥♦⚀⚁⚂⚃⚄⚅</p>
                </div>

                <div class="fallback">
                    <strong>Police de secours:</strong> Arial, Helvetica, sans-serif<br>
                    Si les fonts web ne se chargent pas, cette police sera utilisée.
                </div>

                <div class="special-chars">
                    <strong>Caractères spéciaux:</strong><br>
                    Français: éàèêëïîôùûüÿç<br>
                    Mathématiques: ∑∏√∫∂∆∇∈∉⊆⊂⊄∪∩∞≠≤≥≈≡<br>
                    Monnaie: ¢£¤¥€₹₽₩₺₿<br>
                    Flèches: ←↑→↓↔↕⇐⇑⇒⇓⇔⇕
                </div>

                <h2>📊 Résumé des Tests de Fonts</h2>
                <ul>
                    <li>✅ Fonts Google Fonts multiples</li>
                    <li>✅ Différentes graisses (weights)</li>
                    <li>✅ Caractères spéciaux et accentués</li>
                    <li>✅ Polices de secours (fallback)</li>
                    <li>✅ Rendu dans PDF</li>
                </ul>

                <p><strong>Timestamp:</strong> ${new Date().toISOString()}</p>
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
        this.results.summary.browserCount++;
    }

    generateReport() {
        const summary = this.results.summary;

        // Calculer le score de compatibilité global
        if (summary.browserCount > 0) {
            const browserScores = Object.values(this.results.browsers)
                .map(b => b.score)
                .filter(score => score !== undefined);

            summary.averageScore = browserScores.reduce((a, b) => a + b, 0) / browserScores.length;
            summary.compatibilityScore = summary.averageScore;
        }

        return {
            ...this.results,
            generatedAt: new Date().toISOString(),
            phase: '5.8',
            description: 'Test de compatibilité navigateur amélioré'
        };
    }

    saveReport(filename = 'phase5.8-enhanced-browser-compatibility.json') {
        const report = this.generateReport();
        const reportPath = path.join(__dirname, filename);

        fs.writeFileSync(reportPath, JSON.stringify(report, null, 2));
        console.log(`\n🌐 Rapport de compatibilité amélioré sauvegardé: ${reportPath}`);

        return report;
    }
}

// Fonction principale
async function runEnhancedCompatibilityTests() {
    const tester = new EnhancedBrowserCompatibilityTester();

    try {
        await tester.init();

        // Test avec Chrome (par défaut)
        await tester.testBrowser('Chrome', {
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage'
            ]
        });

        // Test avec Chrome en mode headless nouveau
        await tester.testBrowser('Chrome-New-Headless', {
            headless: 'new',
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage'
            ]
        });

        // Test avec Chrome en mode legacy
        await tester.testBrowser('Chrome-Legacy-Headless', {
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-web-security'
            ]
        });

        // Générer le rapport
        const report = tester.saveReport('phase5.8-enhanced-browser-compatibility.json');

        console.log('\n🌐 RÉSULTATS COMPATIBILITÉ AMÉLIORÉS:');
        console.log(`Navigateurs testés: ${report.summary.browserCount}`);
        console.log(`Tests totaux: ${report.summary.totalTests}`);
        console.log(`Succès: ${report.summary.successfulTests}`);
        console.log(`Échecs: ${report.summary.failedTests}`);
        console.log(`Score moyen: ${report.summary.averageScore?.toFixed(2) || 'N/A'}%`);
        console.log(`Score de compatibilité: ${report.summary.compatibilityScore?.toFixed(2) || 'N/A'}%`);

        console.log('\n📊 RÉSULTATS PAR NAVIGATEUR:');
        for (const [browser, results] of Object.entries(report.browsers)) {
            console.log(`${browser}: ${results.score?.toFixed(2) || 0}% (${results.tests.filter(t => t.success).length}/${results.tests.length}) - ${results.version}`);
        }

        if (report.summary.compatibilityScore >= 90) {
            console.log('\n🎉 EXCELLENT: Compatibilité parfaite !');
        } else if (report.summary.compatibilityScore >= 80) {
            console.log('\n✅ BON: Compatibilité très bonne !');
        } else if (report.summary.compatibilityScore >= 70) {
            console.log('\n⚠️ ACCEPTABLE: Compatibilité correcte');
        } else {
            console.log('\n❌ À AMÉLIORER: Compatibilité insuffisante');
        }

    } catch (error) {
        console.error('❌ Erreur lors des tests de compatibilité:', error);
    } finally {
        await tester.close();
    }
}

// Exécuter les tests
if (require.main === module) {
    runEnhancedCompatibilityTests().catch(console.error);
}

module.exports = EnhancedBrowserCompatibilityTester;
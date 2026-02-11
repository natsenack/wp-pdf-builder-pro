const puppeteer = require('puppeteer');

(async () => {
  try {
    console.log('Lancement de Chrome...');
    const browser = await puppeteer.launch({
      headless: true,
      args: ['--no-sandbox', '--disable-setuid-sandbox']
    });
    
    console.log('Création de la page...');
    const page = await browser.newPage();
    
    console.log('Définition du contenu HTML...');
    await page.setContent('<h1 style="color: red;">Test Puppeteer OK!</h1>');
    
    console.log('Capture screenshot...');
    await page.screenshot({ path: 'test-screenshot.png' });
    
    console.log('✅ Screenshot créé: test-screenshot.png');
    
    await browser.close();
  } catch (error) {
    console.error('❌ Erreur:', error.message);
    console.error('Stack:', error.stack);
  }
})();

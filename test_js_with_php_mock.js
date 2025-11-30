const fs = require('fs');
const content = fs.readFileSync('i:\\wp-pdf-builder-pro\\plugin\\templates\\admin\\settings-parts\\settings-developpeur.php', 'utf8');

// Remplacer les balises PHP par des valeurs fictives pour tester la syntaxe JS
let processedContent = content
    .replace(/<\?php\s+echo\s+[^;]+;\s*\?>/g, '"MOCK_VALUE"')
    .replace(/<\?php\s+[^?]+\?>/g, '/* PHP CODE REMOVED */');

// Extraire le JavaScript
const scriptStart = processedContent.indexOf('<script>');
const scriptEnd = processedContent.indexOf('</script>', scriptStart);

if (scriptStart !== -1 && scriptEnd !== -1) {
    const jsCode = processedContent.substring(scriptStart + 8, scriptEnd).trim();

    console.log(`Longueur du JavaScript traité: ${jsCode.length} caractères`);
    console.log(`Nombre de lignes: ${jsCode.split('\\n').length}`);

    // Tester la syntaxe
    try {
        new Function(jsCode);
        console.log('✅ Syntaxe JavaScript valide après traitement PHP');
    } catch (e) {
        console.log('❌ Erreur de syntaxe:', e.message);

        // Afficher le contexte autour de l'erreur
        const lines = jsCode.split('\\n');
        console.log('\\n=== LIGNES AUTOUR DE L\'ERREUR ===');
        const errorLine = Math.min(lines.length - 1, 10); // Montrer les premières lignes
        for (let i = 0; i <= errorLine; i++) {
            console.log(`${(i+1).toString().padStart(3, ' ')}: ${lines[i]}`);
        }
    }
} else {
    console.log('Script non trouvé');
}
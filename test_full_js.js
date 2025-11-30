const fs = require('fs');
const content = fs.readFileSync('i:\\wp-pdf-builder-pro\\plugin\\templates\\admin\\settings-parts\\settings-developpeur.php', 'utf8');

// Extraire le JavaScript entre <script> et </script>
const scriptStart = content.indexOf('<script>');
const scriptEnd = content.indexOf('</script>', scriptStart);

if (scriptStart !== -1 && scriptEnd !== -1) {
    const jsCode = content.substring(scriptStart + 8, scriptEnd).trim();
    console.log('=== LONGUEUR DU JAVASCRIPT ===');
    console.log(`Longueur totale: ${jsCode.length} caractères`);
    console.log(`Nombre de lignes: ${jsCode.split('\\n').length}`);

    console.log('\\n=== TEST DE SYNTAXE COMPLÈTE ===');
    try {
        new Function(jsCode);
        console.log('✅ Le JavaScript complet est syntaxiquement valide');
    } catch (e) {
        console.log('❌ ERREUR DE SYNTAXE:', e.message);
        console.log('Position approximative:', e.message.match(/position (\d+)/)?.[1] || 'N/A');

        // Afficher le contexte autour de l'erreur
        const errorPos = e.message.match(/position (\d+)/);
        if (errorPos) {
            const pos = parseInt(errorPos[1]);
            const start = Math.max(0, pos - 100);
            const end = Math.min(jsCode.length, pos + 100);
            console.log('\\n=== CONTEXTE AUTOUR DE L\'ERREUR ===');
            console.log(jsCode.substring(start, end));
            console.log('\\n'.padEnd(50, '='));
        }

        // Compter les parenthèses et accolades
        const openParens = (jsCode.match(/\(/g) || []).length;
        const closeParens = (jsCode.match(/\)/g) || []).length;
        const openBraces = (jsCode.match(/\{/g) || []).length;
        const closeBraces = (jsCode.match(/\}/g) || []).length;

        console.log('\\n=== COMPTAGE DES SYMBOLES ===');
        console.log(`Parenthèses ouvrantes: ${openParens}`);
        console.log(`Parenthèses fermantes: ${closeParens}`);
        console.log(`Accolades ouvrantes: ${openBraces}`);
        console.log(`Accolades fermantes: ${closeBraces}`);

        if (openParens !== closeParens) {
            console.log('❌ Problème de parenthèses');
        }
        if (openBraces !== closeBraces) {
            console.log('❌ Problème d\'accolades');
        }
    }
} else {
    console.log('Script non trouvé');
}
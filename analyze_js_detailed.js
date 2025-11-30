const fs = require('fs');
const content = fs.readFileSync('i:\\wp-pdf-builder-pro\\plugin\\templates\\admin\\settings-parts\\settings-developpeur.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
    const jsCode = scriptMatch[1];
    console.log('=== ANALYSE DÉTAILLÉE DE LA SYNTAXE ===');

    // Tester par blocs de 50 lignes
    const lines = jsCode.split('\n');
    let currentCode = '';
    let errorFound = false;

    for (let i = 0; i < lines.length && !errorFound; i += 50) {
        const endIndex = Math.min(i + 50, lines.length);
        const chunk = lines.slice(i, endIndex).join('\n');
        currentCode += chunk;

        try {
            new Function(currentCode);
            console.log(`✅ Lignes ${i + 1}-${endIndex}: OK`);
        } catch (e) {
            console.log(`❌ ERREUR aux lignes ${i + 1}-${endIndex}: ${e.message}`);
            console.log('Code problématique:');
            console.log('--- DÉBUT ---');
            console.log(chunk);
            console.log('--- FIN ---');
            errorFound = true;
        }
    }

    if (!errorFound) {
        console.log('✅ Tout le JavaScript semble valide');
    }
} else {
    console.log('Script non trouvé');
}
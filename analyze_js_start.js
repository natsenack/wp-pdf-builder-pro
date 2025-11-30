const fs = require('fs');
const content = fs.readFileSync('i:\\wp-pdf-builder-pro\\plugin\\templates\\admin\\settings-parts\\settings-developpeur.php', 'utf8');
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
    const jsCode = scriptMatch[1];
    console.log('=== PREMIÈRES 100 LIGNES DU JAVASCRIPT ===');
    const lines = jsCode.split('\n');
    lines.slice(0, 100).forEach((line, i) => {
        console.log(`${(i+1).toString().padStart(3, ' ')}: ${line}`);
    });

    console.log('\n=== ANALYSE DES PREMIÈRES LIGNES ===');
    try {
        new Function(jsCode.substring(0, 2000));
        console.log('✅ Les 2000 premiers caractères sont valides');
    } catch (e) {
        console.log('❌ Erreur dans les premiers caractères:', e.message);
        const errorPos = e.message.match(/position (\d+)/);
        if (errorPos) {
            const pos = parseInt(errorPos[1]);
            console.log('Position approximative:', pos);
            console.log('Contexte autour de la position:');
            console.log(jsCode.substring(Math.max(0, pos-50), Math.min(jsCode.length, pos+50)));
        }
    }
} else {
    console.log('Script non trouvé');
}
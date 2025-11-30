const fs = require('fs');
const content = fs.readFileSync('i:\\wp-pdf-builder-pro\\plugin\\templates\\admin\\settings-parts\\settings-developpeur.php', 'utf8');

// Trouver tous les blocs <script> ... </script>
const scriptBlocks = [];
let startIndex = 0;

while (true) {
    const scriptStart = content.indexOf('<script>', startIndex);
    if (scriptStart === -1) break;

    // Chercher la fin du script en comptant les <script> et </script>
    let scriptEnd = scriptStart + 8;
    let depth = 1;

    while (depth > 0 && scriptEnd < content.length) {
        const nextScriptStart = content.indexOf('<script>', scriptEnd);
        const nextScriptEnd = content.indexOf('</script>', scriptEnd);

        if (nextScriptEnd === -1) {
            // Pas de </script> trouvé, prendre jusqu'à la fin
            scriptEnd = content.length;
            break;
        }

        if (nextScriptStart !== -1 && nextScriptStart < nextScriptEnd) {
            // Il y a un <script> imbriqué avant le </script>
            depth++;
            scriptEnd = nextScriptStart + 8;
        } else {
            // </script> trouvé
            depth--;
            scriptEnd = nextScriptEnd + 9;
        }
    }

    if (depth === 0) {
        const scriptContent = content.substring(scriptStart + 8, scriptEnd - 9).trim();
        scriptBlocks.push(scriptContent);
    }

    startIndex = scriptEnd;
}

console.log(`Nombre de blocs script trouvés: ${scriptBlocks.length}`);

if (scriptBlocks.length > 0) {
    const jsCode = scriptBlocks[0]; // Prendre le premier bloc
    console.log(`Longueur du premier bloc: ${jsCode.length} caractères`);
    console.log(`Nombre de lignes: ${jsCode.split('\\n').length}`);

    // Tester la syntaxe
    try {
        new Function(jsCode);
        console.log('✅ Syntaxe valide');
    } catch (e) {
        console.log('❌ Erreur:', e.message);

        // Chercher la ligne problématique
        const lines = jsCode.split('\\n');
        let charCount = 0;
        for (let i = 0; i < lines.length; i++) {
            if (charCount + lines[i].length >= 1000) { // Environ 1000 caractères
                console.log(`Ligne ${i + 1}: ${lines[i]}`);
                break;
            }
            charCount += lines[i].length + 1; // +1 pour \\n
        }
    }
} else {
    console.log('Aucun bloc script trouvé');
}
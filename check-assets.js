import fs from 'fs';

// Vérifier si les fichiers compilés existent et ont du contenu
const filesToCheck = [
    'plugin/assets/js/pdf-builder-react.bundle.js',
    'plugin/assets/js/runtime.bundle.js',
    'plugin/assets/js/vendors.bdb99cf4aa1bb604ae5a.js',
    'plugin/assets/js/271.cd599c5fe9db8bcbe7a0.js'
];

filesToCheck.forEach(file => {
    const fullPath = file;
    if (fs.existsSync(fullPath)) {
        const stats = fs.statSync(fullPath);
        const content = fs.readFileSync(fullPath, 'utf8');
        const firstLine = content.split('\n')[0].trim();

        console.log(`${file}:`);
        console.log(`  Taille: ${stats.size} bytes`);
        console.log(`  Première ligne: ${firstLine.substring(0, 100)}...`);
        console.log(`  Contient 'var': ${content.includes('var ') ? 'OUI' : 'NON'}`);
        console.log('');
    } else {
        console.log(`${file}: FICHIER NON TROUVÉ`);
        console.log('');
    }
});

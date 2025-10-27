const http = require('http');
const fs = require('fs');
const path = require('path');

const server = http.createServer((req, res) => {
    let filePath = path.join(__dirname, req.url === '/' ? 'test-drag-drop.html' : req.url);

    // Gestion des fichiers statiques
    if (req.url.startsWith('/plugin/')) {
        filePath = path.join(__dirname, req.url);
    }

    fs.readFile(filePath, (err, data) => {
        if (err) {
            res.writeHead(404);
            res.end('File not found');
            return;
        }

        // Déterminer le type de contenu
        const ext = path.extname(filePath);
        let contentType = 'text/html';
        switch (ext) {
            case '.js':
                contentType = 'application/javascript';
                break;
            case '.css':
                contentType = 'text/css';
                break;
            case '.json':
                contentType = 'application/json';
                break;
        }

        res.writeHead(200, { 'Content-Type': contentType });
        res.end(data);
    });
});

const PORT = 3000;
server.listen(PORT, () => {
    // Serveur démarré silencieusement
});
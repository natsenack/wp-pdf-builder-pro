const fs = require('fs');
const content = fs.readFileSync('plugin/templates/admin/settings-parts/settings-developpeur.php', 'utf8');

// Extract JavaScript blocks
const scriptRegex = /<script[^>]*>([\s\S]*?)<\/script>/gi;
let match = scriptRegex.exec(content);
if (match) {
  const jsContent = match[1];

  // Try to parse smaller chunks to find the error
  const lines = jsContent.split('\n');
  let currentChunk = '';

  for (let i = 0; i < lines.length; i++) {
    currentChunk += lines[i] + '\n';

    // Try to parse every 50 lines
    if (i % 50 === 0 && i > 0) {
      try {
        new Function(currentChunk);
        console.log('Lines 1-' + i + ': OK');
      } catch (error) {
        console.log('Lines 1-' + i + ': ERROR - ' + error.message);
        // Show the last few lines that caused the error
        const recentLines = lines.slice(Math.max(0, i - 10), i + 1);
        console.log('Recent lines:');
        recentLines.forEach((line, idx) => {
          console.log((i - 10 + idx + 1) + ': ' + line);
        });
        break;
      }
    }
  }

  // If no error found in chunks, try the whole thing
  if (currentChunk) {
    try {
      new Function(jsContent);
      console.log('Full JavaScript: OK');
    } catch (error) {
      console.log('Full JavaScript: ERROR - ' + error.message);
    }
  }
}
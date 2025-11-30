const fs = require('fs');
const content = fs.readFileSync('plugin/templates/admin/settings-parts/settings-developpeur.php', 'utf8');

// Extract JavaScript blocks
const scriptRegex = /<script[^>]*>([\s\S]*?)<\/script>/gi;
let match = scriptRegex.exec(content);
if (match) {
  const jsContent = match[1];
  const lines = jsContent.split('\n');

  console.log('Searching for HTML-like content in JavaScript...');

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const trimmed = line.trim();

    // Check for HTML tags that are not inside strings
    if (trimmed.includes('<') && !trimmed.includes('</script>') && !trimmed.includes('//')) {
      console.log('Line ' + (i + 1) + ': Contains < character: ' + trimmed);
    }

    // Check for template literals with HTML
    if (trimmed.includes('`') && trimmed.includes('<')) {
      console.log('Line ' + (i + 1) + ': Template literal with HTML: ' + trimmed);
    }
  }
}
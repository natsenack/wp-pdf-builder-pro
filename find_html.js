const fs = require('fs');
const content = fs.readFileSync('plugin/templates/admin/settings-parts/settings-developpeur.php', 'utf8');

// Extract JavaScript blocks
const scriptRegex = /<script[^>]*>([\s\S]*?)<\/script>/gi;
let match = scriptRegex.exec(content);
if (match) {
  const jsContent = match[1];
  const lines = jsContent.split('\n');

  // Find lines with HTML-like content in template literals
  console.log('Searching for problematic HTML in template literals...');

  let inTemplateLiteral = false;
  let templateStart = -1;

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];

    if (line.includes('`')) {
      if (line.includes('notification.innerHTML = `')) {
        inTemplateLiteral = true;
        templateStart = i;
        console.log('Template literal starts at line ' + (i + 1));
      }
    }

    if (inTemplateLiteral) {
      // Check for unescaped HTML tags
      if (line.includes('<') && !line.includes('</script>')) {
        console.log('Line ' + (i + 1) + ' contains HTML: ' + line.trim());
      }

      if (line.includes('`;')) {
        console.log('Template literal ends at line ' + (i + 1));
        inTemplateLiteral = false;
      }
    }
  }
}
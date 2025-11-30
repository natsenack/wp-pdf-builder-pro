const fs = require('fs');
const content = fs.readFileSync('plugin/templates/admin/settings-parts/settings-developpeur.php', 'utf8');

// Extract JavaScript blocks
const scriptRegex = /<script[^>]*>([\s\S]*?)<\/script>/gi;
let match = scriptRegex.exec(content);
if (match) {
  const jsContent = match[1];
  const lines = jsContent.split('\n');

  console.log('Checking for HTML tags outside of strings...');

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const trimmed = line.trim();

    // Check for HTML tags that are not inside strings
    if (trimmed.startsWith('<') && !trimmed.includes('</script>')) {
      console.log('Line ' + (i + 1) + ': HTML tag found: ' + trimmed);
    }

    // Check for incomplete strings or template literals
    const singleQuotes = (line.match(/'/g) || []).length;
    const doubleQuotes = (line.match(/"/g) || []).length;
    const backticks = (line.match(/`/g) || []).length;

    if (singleQuotes % 2 !== 0 || doubleQuotes % 2 !== 0 || backticks % 2 !== 0) {
      console.log('Line ' + (i + 1) + ': Possible unclosed string: ' + trimmed);
    }
  }
}
const fs = require('fs');
const content = fs.readFileSync('plugin/templates/admin/settings-parts/settings-developpeur.php', 'utf8');

// Extract JavaScript blocks
const scriptRegex = /<script[^>]*>([\s\S]*?)<\/script>/gi;
let match = scriptRegex.exec(content);
if (match) {
  const jsContent = match[1];

  // Try to find the exact problematic part
  // Let's isolate the template literal
  const templateLiteralRegex = /notification\.innerHTML\s*=\s*`([\s\S]*?)`;/g;
  const templateMatch = templateLiteralRegex.exec(jsContent);

  if (templateMatch) {
    console.log('Found template literal:');
    console.log(templateMatch[0]);

    // Try to parse just this part
    try {
      const testCode = `const notification = {}; ${templateMatch[0]}`;
      new Function(testCode);
      console.log('Template literal syntax is OK');
    } catch (error) {
      console.log('Template literal syntax error:', error.message);
    }
  } else {
    console.log('No template literal found with that pattern');
  }
}
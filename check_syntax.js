const fs = require('fs');
const content = fs.readFileSync('plugin/templates/admin/settings-parts/settings-developpeur.php', 'utf8');

// Extract JavaScript blocks
const scriptRegex = /<script[^>]*>([\s\S]*?)<\/script>/gi;
let match;
let jsBlocks = [];

while ((match = scriptRegex.exec(content)) !== null) {
  jsBlocks.push(match[1]);
}

console.log('Found ' + jsBlocks.length + ' JavaScript blocks');

// Test each block for syntax errors
jsBlocks.forEach((block, index) => {
  try {
    // Try to parse the JavaScript
    new Function(block);
    console.log('Block ' + (index + 1) + ': OK');
  } catch (error) {
    console.log('Block ' + (index + 1) + ': SYNTAX ERROR - ' + error.message);
    console.log('Error location: ' + error.stack);

    // Show the problematic area
    const lines = block.split('\n');
    const errorLine = error.stack.match(/<anonymous>:(\d+)/);
    if (errorLine) {
      const lineNum = parseInt(errorLine[1]) - 1;
      console.log('Problematic line ' + (lineNum + 1) + ': ' + lines[lineNum]);
      // Show context
      for (let i = Math.max(0, lineNum - 2); i <= Math.min(lines.length - 1, lineNum + 2); i++) {
        console.log((i + 1) + ': ' + lines[i]);
      }
    }
  }
});
const fs = require('fs');
const path = 'plugin/templates/admin/settings-parts/settings-developpeur.php';
const content = fs.readFileSync(path, 'utf8');

// Extract JavaScript blocks
const scriptRegex = /<script[^>]*>([\s\S]*?)<\/script>/gi;
let match = scriptRegex.exec(content);
if (match) {
  const jsContent = match[1];
  const lines = jsContent.split('\n');

  // Find the problematic template literal
  let inTemplateLiteral = false;
  let templateStart = -1;

  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];

    if (line.includes('notification.innerHTML = `')) {
      inTemplateLiteral = true;
      templateStart = i;
      console.log('Template literal starts at line ' + (i + 1));
    }

    if (inTemplateLiteral && line.includes('`;')) {
      console.log('Template literal ends at line ' + (i + 1));
      console.log('Template literal spans ' + (i - templateStart + 1) + ' lines');
      inTemplateLiteral = false;

      // Show the template content
      console.log('Template content:');
      for (let j = templateStart; j <= i; j++) {
        console.log((j + 1) + ': ' + lines[j]);
      }
      break;
    }
  }

  // Check for unclosed template literals
  let backtickCount = 0;
  for (let i = 0; i < lines.length; i++) {
    const line = lines[i];
    const backtickMatches = line.match(/`/g);
    if (backtickMatches) {
      backtickCount += backtickMatches.length;
    }
  }
  console.log('Total backticks in file: ' + backtickCount);
  console.log('Backticks should be even: ' + (backtickCount % 2 === 0));
}
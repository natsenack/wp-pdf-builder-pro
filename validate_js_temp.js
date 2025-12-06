const fs = require('fs');
let content = fs.readFileSync('plugin/resources/templates/admin/settings-parts/settings-contenu.php', 'utf8');

// Replace PHP json_encode calls with mock values
content = content.replace(/<\?php echo json_encode\([^;]+\); \?>/g, (match) => {
  if (match.includes('=== \'1\'')) {
    return 'true';
  } else if (match.includes('?? \'#')) {
    return '"#ffffff"';
  } else if (match.includes('?? \'')) {
    return '"mock_string"';
  } else {
    return '"mock_value"';
  }
});

// Extract script content
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
  const jsCode = scriptMatch[1];
  try {
    new Function(jsCode);
    console.log('✅ JavaScript syntax is valid');
  } catch (e) {
    console.log('❌ JavaScript syntax error:', e.message);
  }
} else {
  console.log('No script tag found');
}
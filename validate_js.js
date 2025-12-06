const fs = require('fs');
let content = fs.readFileSync('plugin/resources/templates/admin/settings-parts/settings-contenu.php', 'utf8');

console.log('PHP blocks found:');
const phpBlocks = content.match(/<\?php[\s\S]*?\?>/g);
if (phpBlocks) {
  phpBlocks.forEach((block, i) => {
    console.log(`${i + 1}: ${block.substring(0, 100)}...`);
  });
}

// Replace PHP json_encode calls with appropriate mock values
content = content.replace(/<\?php echo json_encode\([^;]+\); \?>/g, (match) => {
  if (match.includes('=== \'1\'')) {
    return 'true'; // Boolean values
  } else if (match.includes('?? \'#')) {
    return '"#ffffff"'; // Color values
  } else if (match.includes('?? \'')) {
    return '"mock_string"'; // String values
  } else {
    return '"mock_value"'; // Default
  }
});

// Check for remaining PHP blocks
const remainingPhp = content.match(/<\?php[\s\S]*?\?>/g);
if (remainingPhp) {
  console.log('Remaining PHP blocks:');
  remainingPhp.forEach((block, i) => {
    console.log(`${i + 1}: ${block}`);
  });
}

// Extract script content
const scriptMatch = content.match(/<script>([\s\S]*?)<\/script>/);
if (scriptMatch) {
  const jsCode = scriptMatch[1];
  console.log('Script length:', jsCode.length);
  console.log('Last 200 chars:');
  console.log(jsCode.substring(jsCode.length - 200));
  try {
    new Function(jsCode);
    console.log('✅ JavaScript syntax is valid');
  } catch (e) {
    console.log('❌ JavaScript syntax error:', e.message);
    // Check for unclosed strings or brackets
    const openBraces = (jsCode.match(/\{/g) || []).length;
    const closeBraces = (jsCode.match(/\}/g) || []).length;
    const openParens = (jsCode.match(/\(/g) || []).length;
    const closeParens = (jsCode.match(/\)/g) || []).length;
    const openBrackets = (jsCode.match(/\[/g) || []).length;
    const closeBrackets = (jsCode.match(/\]/g) || []).length;
    console.log(`Braces: ${openBraces} open, ${closeBraces} close`);
    console.log(`Parens: ${openParens} open, ${closeParens} close`);
    console.log(`Brackets: ${openBrackets} open, ${closeBrackets} close`);
  }
} else {
  console.log('No script tag found');
}
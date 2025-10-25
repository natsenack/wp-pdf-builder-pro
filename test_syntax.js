
const fs = require('fs');
const content = fs.readFileSync('templates/admin/template-editor.php', 'utf8');
const jsBlocks = content.match(/<script[^>]*>([\s\S]*?)<\/script>/g);
if (jsBlocks) {
  let okCount = 0;
  jsBlocks.forEach((block, i) => {
    const js = block.replace(/<script[^>]*>/, '').replace(/<\/script>/, '').trim();
    if (js.length > 0) {
      try {
        const cleanJs = js.replace(/<\?php[\s\S]*?\?>/g, 'null');
        new Function(cleanJs);
        console.log('Script block', i+1, ': ✓ OK');
        okCount++;
      } catch (e) {
        console.log('Script block', i+1, ': ✗ ERROR -', e.message);
      }
    }
  });
  console.log('Total: ' + okCount + '/' + jsBlocks.length + ' scripts OK');
}


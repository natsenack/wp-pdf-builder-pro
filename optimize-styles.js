// Script pour optimiser les styles de CanvasElement.jsx
const fs = require('fs');
const path = require('path');

const filePath = path.join(__dirname, 'src/components/CanvasElement.jsx');
let content = fs.readFileSync(filePath, 'utf8');

// Trouver la position du return
const returnMatch = content.match(/return \(\s*\n\s*<>\s*\n\s*\{\/\* Élément principal \*\/\}/);
if (!returnMatch) {
  console.log('❌ Impossible de trouver le return');
  process.exit(1);
}

const returnIndex = returnMatch.index + returnMatch[0].length;

// Trouver la fermeture de l'objet style (deuxième occurrence de }})
let braceCount = 0;
let styleEndIndex = -1;
for (let i = returnIndex; i < content.length; i++) {
  if (content[i] === '{') braceCount++;
  if (content[i] === '}') {
    braceCount--;
    if (braceCount === -2) { // Nous avons fermé l'objet style
      styleEndIndex = i + 1;
      break;
    }
  }
}

if (styleEndIndex === -1) {
  console.log('❌ Impossible de trouver la fin de l\'objet style');
  process.exit(1);
}

// Extraire l'objet style complet
const styleContent = content.substring(returnIndex, styleEndIndex);

// Créer le useMemo
const useMemoCode = `  // Optimisation des styles avec useMemo pour éviter les re-calculs inutiles
  const elementStyles = useMemo(() => (${styleContent}), [
    element.x, element.y, element.width, element.height, element.type, element.backgroundColor,
    element.borderWidth, element.borderColor, element.borderStyle, element.borderRadius,
    element.opacity, element.rotation, element.scale, element.brightness, element.contrast,
    element.saturate, element.shadow, element.shadowOffsetX, element.shadowOffsetY, element.shadowColor,
    element.fontSize, element.fontFamily, element.color, element.fontWeight, element.fontStyle,
    element.textAlign, element.textDecoration, element.lineHeight, element.objectFit, element.fit,
    element.src, element.imageUrl, element.lineWidth, element.lineColor, element.boxShadow,
    zoom, dragAndDrop.isDragging
  ]);

  return (
    <>
      {/* Élément principal */}
      <div
        ref={elementRef}
        className={\`canvas-element \${isSelected ? 'selected' : ''}\`}
        style={elementStyles}`;

// Remplacer le contenu
const beforeReturn = content.substring(0, content.indexOf('  return ('));
const afterStyle = content.substring(styleEndIndex);

const newContent = beforeReturn + useMemoCode + afterStyle;

fs.writeFileSync(filePath, newContent);
console.log('✅ Optimisation des styles terminée');
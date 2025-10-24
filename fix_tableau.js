const fs = require('fs');
const path = require('path');

const filepath = 'resources/js/components/PDFEditor.jsx';
let content = fs.readFileSync(filepath, 'utf-8');

console.log(`\nFichier original: ${content.length} caractères`);

// ============ SECTION 1: EN-TÊTES ============
console.log("\n[1/3] Remplaçant les en-têtes...");

// Remplacer le commentaire et hauteur
content = content.replace(
  "// En-têtes du tableau - Design moderne et épuré",
  "// En-têtes du tableau - Ligne simple et minimale"
);

content = content.replace(
  "const headerHeight = 32; // Augmenté pour plus d'espace",
  "const headerHeight = 24;"
);

// Remplacer le gradient et bordures
const headerOldStart = `          // Fond de l'en-tête avec gradient subtil pour plus de profondeur
          const headerGradient = ctx.createLinearGradient(tableX, currentY, tableX, currentY + headerHeight);`;

if (content.includes(headerOldStart)) {
  const startIdx = content.indexOf(headerOldStart);
  // Trouver la fin du bloc (jusqu'à "ctx.textAlign = 'center';")
  const endPattern = "ctx.textAlign = 'center';";
  const endIdx = content.indexOf(endPattern, startIdx);
  
  if (endIdx !== -1) {
    const headerBlock = content.substring(startIdx, endIdx + endPattern.length);
    
    const headerNew = `          // Fond blanc pur
          ctx.fillStyle = '#ffffff';
          ctx.fillRect(tableX, currentY, tableWidth, headerHeight);

          // Bordure unique en bas - fine ligne grise
          ctx.strokeStyle = '#d0d0d0';
          ctx.lineWidth = 1;
          ctx.beginPath();
          ctx.moveTo(tableX, currentY + headerHeight - 0.5);
          ctx.lineTo(tableX + tableWidth, currentY + headerHeight - 0.5);
          ctx.stroke();

          // Texte des en-têtes - sobre et épuré
          ctx.fillStyle = '#404040';
          ctx.font = \`500 \${headerFontSize - 1}px \${fontFamily}\`;
          ctx.textAlign = 'center';`;
    
    content = content.substring(0, startIdx) + headerNew + content.substring(endIdx + endPattern.length);
  }
}
console.log("✓ En-têtes remplacés");

// ============ SECTION 2: POSITION HEADER + DÉBUT LIGNES ============
console.log("[2/3] Remplaçant position et début lignes...");

const posOld = `            // Centrer verticalement le texte de l'en-tête avec meilleur padding
            const headerY = currentY + headerHeight / 2 + (headerFontSize * 0.3) + 2;`;

const posNew = `            // Centrer verticalement
            const headerY = currentY + headerHeight / 2 + (headerFontSize * 0.25);

            ctx.fillText(headerText, headerX, headerY);
          });

          currentY += headerHeight;
        }

        // === LIGNES DE DONNÉES - RENDU ÉPURÉ ===
        ctx.font = \`400 \${rowFontSize}px \${fontFamily}\`;

        tableData.rows.forEach((row, rowIndex) => {
          const rowHeight = 20;
          const isAlternate = rowIndex % 2 === 1;

          // Alternance simple: blanc et gris très clair
          if (isAlternate) {
            ctx.fillStyle = '#fafafa';
            ctx.fillRect(tableX, currentY, tableWidth, rowHeight);
          }

          // Bordure fine en bas de ligne
          ctx.strokeStyle = '#e8e8e8';
          ctx.lineWidth = 0.8;
          ctx.beginPath();
          ctx.moveTo(tableX, currentY + rowHeight);
          ctx.lineTo(tableX + tableWidth, currentY + rowHeight);
          ctx.stroke();

          // Texte des cellules
          ctx.fillStyle = '#2a2a2a';
          ctx.textAlign = 'center';

          row.forEach((cell, cellIndex) => {
            let cellText = String(cell);

            // Transformation de texte
            if (textTransform === 'uppercase') {
              cellText = cellText.toUpperCase();
            } else if (textTransform === 'lowercase') {
              cellText = cellText.toLowerCase();
            } else if (textTransform === 'capitalize') {
              cellText = cellText.replace(/\b\w/g, l => l.toUpperCase());
            }

            // Position X centrée dans la colonne
            let cellX;
            if (cellIndex === 0) {
              cellX = tableX + (columnWidths[0] / 2);
            } else {
              const previousWidth = columnWidths.slice(0, cellIndex).reduce((sum, w) => sum + w, 0);
              cellX = tableX + previousWidth + (columnWidths[cellIndex] / 2);
            }
            const cellY = currentY + rowHeight / 2 + (rowFontSize * 0.3);

            // Gestion des images
            if (cellText.startsWith('data:image') || cellText.includes('.jpg') || cellText.includes('.png')) {
              const imgSize = 12;
              const imgX = cellX - imgSize / 2;
              const imgY = currentY + (rowHeight - imgSize) / 2;

              ctx.fillStyle = '#e5e5e5';
              ctx.fillRect(imgX, imgY, imgSize, imgSize);
              ctx.strokeStyle = '#d0d0d0';
              ctx.lineWidth = 0.5;
              ctx.strokeRect(imgX, imgY, imgSize, imgSize);
            } else {
              // Texte normal
              ctx.font = \`400 \${rowFontSize}px \${fontFamily}\`;
              ctx.fillStyle = '#2a2a2a';
              ctx.textAlign = 'center';
              ctx.fillText(cellText, cellX, cellY);
            }
          });

          currentY += rowHeight;
        });

        // === LIGNES DE TOTAUX - RENDU ÉPURÉ ===
        const totals = tableData.totals;
        if (Object.keys(totals).length > 0) {
          // Séparateur lourd avant totaux
          ctx.strokeStyle = '#c0c0c0';
          ctx.lineWidth = 1.5;
          ctx.beginPath();
          ctx.moveTo(tableX, currentY);
          ctx.lineTo(tableX + tableWidth, currentY);
          ctx.stroke();
          currentY += 8;

          Object.entries(totals).forEach(([key, value]) => {
            const totalHeight = 18;

            // Fond blanc simple
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(tableX, currentY, tableWidth, totalHeight);

            // Libellé à gauche
            ctx.fillStyle = '#404040';
            ctx.font = \`500 \${rowFontSize}px \${fontFamily}\`;
            ctx.textAlign = 'left';
            const labelText = key === 'total' ? 'Total' : key.charAt(0).toUpperCase() + key.slice(1);
            ctx.fillText(labelText, tableX + 8, currentY + totalHeight / 2 + (rowFontSize * 0.3));

            // Valeur à droite
            ctx.textAlign = 'right';
            const valueText = String(value);
            ctx.fillText(valueText, tableX + tableWidth - 8, currentY + totalHeight / 2 + (rowFontSize * 0.3));

            currentY += totalHeight;
          });
        }

        // Fin du rendu product_table (reste du code inchangé)
        } else if (element.type === 'mentions') {`;

if (content.includes(posOld)) {
  const posIdx = content.indexOf(posOld);
  // Trouver la fin du bloc - jusqu'à } else if (element.type === 'mentions') {
  const endPattern = "} else if (element.type === 'mentions') {";
  const endIdx = content.indexOf(endPattern, posIdx);
  
  if (endIdx !== -1) {
    content = content.substring(0, posIdx) + posNew + content.substring(endIdx);
  }
}
console.log("✓ Position et lignes remplacées");

// Sauvegarder
fs.writeFileSync(filepath, content, 'utf-8');

console.log(`\n✓ Fichier sauvegardé: ${content.length} caractères`);
console.log("✓ Replacements terminés avec succès!");

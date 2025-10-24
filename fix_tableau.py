#!/usr/bin/env python3
"""
Script de remplacement pour le tableau produits - Designs épurés et modernes
"""

import re

# Lire le fichier original
filepath = 'resources/js/components/PDFEditor.jsx'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

print(f"Fichier original: {len(content)} caractères")

# ============ SECTION 1: EN-TÊTES ============
print("\n[1/3] Remplaçant les en-têtes...")

# Remplacer le commentaire et hauteur header
content = re.sub(
    r"// En-têtes du tableau - Design moderne et épuré\n        if \(element\.showHeaders !== false",
    "// En-têtes du tableau - Ligne simple et minimale\n        if (element.showHeaders !== false",
    content
)

# Remplacer la hauteur
content = re.sub(
    r"const headerHeight = 32; // Augmenté pour plus d'espace",
    "const headerHeight = 24;",
    content
)

# Remplacer le gradient et bordures du header
content = re.sub(
    r"""          // Fond de l'en-tête avec gradient subtil pour plus de profondeur
          const headerGradient = ctx\.createLinearGradient\(tableX, currentY, tableX, currentY \+ headerHeight\);
          headerGradient\.addColorStop\(0, tableStyleData\.header_bg \? `rgb\(\$\{tableStyleData\.header_bg\.join\(','\)\}\)` : '#f8fafc'\);
          headerGradient\.addColorStop\(1, tableStyleData\.header_bg \? `rgba\(\$\{tableStyleData\.header_bg\.join\(','\)\}, 0\.95\)` : '#f0f4f8'\);
          ctx\.fillStyle = headerGradient;
          ctx\.fillRect\(tableX, currentY, tableWidth, headerHeight\);

          // Bordure supérieure plus visible et moderne
          ctx\.strokeStyle = tableStyleData\.header_border \? `rgb\(\$\{tableStyleData\.header_border\.join\(','\)\}\)` : '#cbd5e1';
          ctx\.lineWidth = 1\.5; // Plus visible
          ctx\.beginPath\(\);
          ctx\.moveTo\(tableX, currentY\);
          ctx\.lineTo\(tableX \+ tableWidth, currentY\);
          ctx\.stroke\(\);

          // Bordure inférieure de l'en-tête - plus prononcée
          ctx\.strokeStyle = tableStyleData\.header_border \? `rgb\(\$\{tableStyleData\.header_border\.join\(','\)\}\)` : '#cbd5e1';
          ctx\.lineWidth = 2; // Plus prononcée pour délimiter clairement
          ctx\.beginPath\(\);
          ctx\.moveTo\(tableX, currentY \+ headerHeight - 0\.5\);
          ctx\.lineTo\(tableX \+ tableWidth, currentY \+ headerHeight - 0\.5\);
          ctx\.stroke\(\);

          // Texte des en-têtes avec style moderne
          ctx\.fillStyle = tableStyleData\.headerTextColor \|\| '#1e293b'; // Couleur plus foncée et moderne
          ctx\.font = `700 \$\{headerFontSize\}px \$\{fontFamily\}`; // Bold au lieu de semi-bold""",
    """          // Fond blanc pur
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
          ctx.font = `500 ${headerFontSize - 1}px ${fontFamily}`""",
    content
)

print("✓ En-têtes remplacés")

# ============ SECTION 2: CALCUL POSITION HEADER + DÉBUT LIGNES ============
print("[2/3] Remplaçant calcul position et lignes de données...")

# Remplacer le bloc de calcul de position + espacement + lignes verticales + début lignes données
pattern = r"""            // Centrer verticalement le texte de l'en-tête avec meilleur padding
            const headerY = currentY \+ headerHeight / 2 \+ \(headerFontSize \* 0\.3\) \+ 2;

            // Afficher le texte avec espacement des lettres si défini
            if \(letterSpacing > 0\) \{
              let charX = headerX - \(headerText\.length \* letterSpacing\) / 2;
              for \(let i = 0; i < headerText\.length; i\+\+\) \{
                ctx\.fillText\(headerText\[i\], charX, headerY\);
                charX \+= ctx\.measureText\(headerText\[i\]\)\.width \+ letterSpacing;
              \}
            \} else \{
              ctx\.fillText\(headerText, headerX, headerY\);
            \}

            // Ligne verticale très subtile entre les colonnes si bordures activées
            if \(element\.showBorders !== false && displayIndex < headersToDisplay\.length - 1\) \{
              ctx\.strokeStyle = tableStyleData\.header_border \? `rgba\(\$\{tableStyleData\.header_border\.join\(','\)\}, 0\.3\)` : 'rgba\(203, 213, 225, 0\.3\)';
              ctx\.lineWidth = 0\.5; // Très subtile
              const nextColumnIndex = headerIndices\[displayIndex \+ 1\];
              let lineX = tableX;
              let nextAccumWidth = 0;
              for \(let i = 0; i < nextColumnIndex; i\+\+\) \{
                nextAccumWidth \+= columnWidths\[i\] \|\| 0;
              \}
              lineX = tableX \+ nextAccumWidth;
              ctx\.beginPath\(\);
              ctx\.moveTo\(lineX, currentY \+ 4\);
              ctx\.lineTo\(lineX, currentY \+ headerHeight - 4\);
              ctx\.stroke\(\);
            \}"""

replacement = r"""            // Centrer verticalement
            const headerY = currentY + headerHeight / 2 + (headerFontSize * 0.25);

            ctx.fillText(headerText, headerX, headerY);"""

content = re.sub(pattern, replacement, content, flags=re.DOTALL)
print("✓ Calcul position et fin header remplacés")

# Remplacer le début des lignes de données
content = re.sub(
    r"        // Lignes de données - Design moderne et aéré\n        ctx\.font = `\$\{fontStyle\}400 \$\{rowFontSize\}px \$\{fontFamily\}`; // Poids normal pour les données\n\n        tableData\.rows\.forEach\(\(row, rowIndex\) => \{\n          const rowHeight = 26; // Augmenté de 22 à 26 pour plus d'espace\n          const isEvenRow = rowIndex % 2 === 0;",
    """        // === LIGNES DE DONNÉES - RENDU ÉPURÉ ===
        ctx.font = `400 ${rowFontSize}px ${fontFamily}`;

        tableData.rows.forEach((row, rowIndex) => {
          const rowHeight = 20;
          const isAlternate = rowIndex % 2 === 1;""",
    content
)
print("✓ Début lignes de données remplacé")

# ============ SECTION 3: ALTERNANCE ET BORDURES LIGNES ============
print("[3/3] Remplaçant alternance et bordures...")

# Remplacer le bloc d'alternance
pattern = r"""          // Fond alterné moderne - plus subtil mais toujours visible
          let bgColor;
          if \(element\.evenRowBg && element\.oddRowBg\) \{
            // Utiliser les couleurs configurées depuis PropertiesPanel
            bgColor = isEvenRow \? element\.evenRowBg : element\.oddRowBg;
          \} else \{
            // Alternance très subtile par défaut - encore plus légère
            bgColor = isEvenRow \? 'rgba\(248, 250, 252, 0\.5\)' : '#ffffff';
          \}

          // Fond uni sans dégradé
          ctx\.fillStyle = bgColor;
          ctx\.fillRect\(tableX, currentY, tableWidth, rowHeight\);

          // Bordure horizontale très fine et moderne entre les lignes
          if \(element\.showBorders !== false\) \{
            ctx\.strokeStyle = 'rgba\(203, 213, 225, 0\.4\)'; // Très subtle
            ctx\.lineWidth = 0\.5; // Très fine
            ctx\.beginPath\(\);
            ctx\.moveTo\(tableX \+ 4, currentY \+ rowHeight\);
            ctx\.lineTo\(tableX \+ tableWidth - 4, currentY \+ rowHeight\);
            ctx\.stroke\(\);
          \}

          // Couleur du texte des cellules \(configurable mais moderne\)
          const rowTextColor = isEvenRow && element\.evenRowTextColor \?
            element\.evenRowTextColor :
            \(!isEvenRow && element\.oddRowTextColor \? element\.oddRowTextColor : '#334155'\); // Gris moderne
          ctx\.fillStyle = rowTextColor;
          ctx\.textAlign = 'center';"""

replacement = r"""          // Alternance simple: blanc et gris très clair
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
          ctx.textAlign = 'center';"""

content = re.sub(pattern, replacement, content, flags=re.DOTALL)
print("✓ Alternance et bordures remplacés")

# ============ REMPLACER LES CELLULES ============
print("[4/5] Remplaçant cellules de données...")

# Remplacer le calcul de cellY
content = re.sub(
    r"const cellY = currentY \+ rowHeight / 2 \+ \(rowFontSize \* 0\.35\);",
    "const cellY = currentY + rowHeight / 2 + (rowFontSize * 0.3);",
    content
)

# Remplacer le bloc des images (important: le caractère spécial)
pattern = r"""            // Gestion spéciale pour les images \(placeholder sobre\)
            if \(cellText\.startsWith\('data:image'\) \|\| cellText\.includes\('\\.jpg'\) \|\| cellText\.includes\('\\.png'\)\) \{
              // Placeholder minimaliste
              const imgSize = 14;
              const imgX = cellX - imgSize / 2;
              const imgY = currentY \+ \(rowHeight - imgSize\) / 2;

              ctx\.fillStyle = '#f3f4f6';
              ctx\.fillRect\(imgX, imgY, imgSize, imgSize\);

              ctx\.strokeStyle = '#d1d5db';
              ctx\.lineWidth = 0\.5;
              ctx\.strokeRect\(imgX, imgY, imgSize, imgSize\);

              ctx\.fillStyle = '#9ca3af';
              ctx\.font = '8px Arial';
              ctx\.textAlign = 'center';
              ctx\.fillText\('.*?', cellX, imgY \+ imgSize - 1\);"""

replacement = r"""            // Gestion des images
            if (cellText.startsWith('data:image') || cellText.includes('.jpg') || cellText.includes('.png')) {
              const imgSize = 12;
              const imgX = cellX - imgSize / 2;
              const imgY = currentY + (rowHeight - imgSize) / 2;

              ctx.fillStyle = '#e5e5e5';
              ctx.fillRect(imgX, imgY, imgSize, imgSize);
              ctx.strokeStyle = '#d0d0d0';
              ctx.lineWidth = 0.5;
              ctx.strokeRect(imgX, imgY, imgSize, imgSize);"""

content = re.sub(pattern, replacement, content, flags=re.DOTALL)
print("✓ Cellules images remplacées")

# ============ REMPLACER TEXTE CELLULES ============
print("[5/5] Remplaçant texte cellules et sections totaux...")

# Remplacer le bloc de texte normal
pattern = r"""            \} else \{
              // Texte normal
              ctx\.font = `\$\{fontStyle\}400 \$\{rowFontSize\}px \$\{fontFamily\}`;
              ctx\.fillStyle = rowTextColor;
              ctx\.textAlign = 'center';

              if \(letterSpacing > 0\) \{
                let charX = cellX - \(cellText\.length \* letterSpacing\) / 2;
                for \(let i = 0; i < cellText\.length; i\+\+\) \{
                  ctx\.fillText\(cellText\[i\], charX, cellY\);
                  charX \+= ctx\.measureText\(cellText\[i\]\)\.width \+ letterSpacing;
                \}
              \} else \{
                ctx\.fillText\(cellText, cellX, cellY\);
              \}
            \}

            // Ligne verticale subtile entre les colonnes si bordures activées
            if \(element\.showBorders !== false && cellIndex < row\.length - 1\) \{
              ctx\.strokeStyle = `rgb\(\$\{tableStyleData\.row_border\.join\(','\)\}\)`;
              ctx\.lineWidth = 0\.8; // Bordure plus visible
              const lineX = tableX \+ columnWidths\.slice\(0, cellIndex \+ 1\)\.reduce\(\(sum, w\) => sum \+ w, 0\);
              ctx\.beginPath\(\);
              ctx\.moveTo\(lineX, currentY \+ 1\);
              ctx\.lineTo\(lineX, currentY \+ rowHeight - 1\);
              ctx\.stroke\(\);
            \}"""

replacement = r"""            } else {
              // Texte normal
              ctx.font = `400 ${rowFontSize}px ${fontFamily}`;
              ctx.fillStyle = '#2a2a2a';
              ctx.textAlign = 'center';
              ctx.fillText(cellText, cellX, cellY);
            }"""

content = re.sub(pattern, replacement, content, flags=re.DOTALL)
print("✓ Texte cellules remplacé")

# Sauvegarder
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"\n✓ Fichier sauvegardé: {len(content)} caractères")
print("✓ Replacements terminés avec succès!")

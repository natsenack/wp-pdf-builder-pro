#!/usr/bin/env python3
import re

# Lire le fichier
with open('resources/js/components/PDFEditor.jsx', 'r', encoding='utf-8') as f:
    content = f.read()

# ========== SECTION 1: REMPLACER EN-TÊTES ==========
# Rechercher le bloc du header (lignes ~2590-2645)
header_old = r"""        // En-têtes du tableau - Design moderne et épuré
        if \(element\.showHeaders !== false && \(filteredHeaders\.length > 0 \|\| tableData\.headers\.length > 0\)\) \{
          const headerHeight = 32; // Augmenté pour plus d'espace

          // Fond de l'en-tête avec gradient subtil pour plus de profondeur
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
          ctx\.font = `700 \$\{headerFontSize\}px \$\{fontFamily\}`; // Bold au lieu de semi-bold
          ctx\.textAlign = 'center';"""

header_new = """        // En-têtes du tableau - Ligne simple et minimale
        if (element.showHeaders !== false && (filteredHeaders.length > 0 || tableData.headers.length > 0)) {
          const headerHeight = 24;

          // Fond blanc pur
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
          ctx.font = `500 ${headerFontSize - 1}px ${fontFamily}`;
          ctx.textAlign = 'center';"""

# ========== SECTION 2: REMPLACER POSITION HEADER ET DÉBUT LIGNES ==========
# Chercher et remplacer le bloc de calcul de position header + lignes de données
header_calc_old = r"""            headerX = tableX \+ accumulatedWidth \+ \(columnWidths\[columnIndex\] / 2\);

            // Centrer verticalement le texte de l'en-tête avec meilleur padding
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
            \}
          \}\);

          currentY \+= headerHeight;
        \}

        // Lignes de données - Design moderne et aéré
        ctx\.font = `\$\{fontStyle\}400 \$\{rowFontSize\}px \$\{fontFamily\}`; // Poids normal pour les données

        tableData\.rows\.forEach\(\(row, rowIndex\) => \{
          const rowHeight = 26; // Augmenté de 22 à 26 pour plus d'espace"""

header_calc_new = """            headerX = tableX + accumulatedWidth + (columnWidths[columnIndex] / 2);

            // Centrer verticalement
            const headerY = currentY + headerHeight / 2 + (headerFontSize * 0.25);

            ctx.fillText(headerText, headerX, headerY);
          });

          currentY += headerHeight;
        }

        // === LIGNES DE DONNÉES - RENDU ÉPURÉ ===
        ctx.font = `400 ${rowFontSize}px ${fontFamily}`;

        tableData.rows.forEach((row, rowIndex) => {
          const rowHeight = 20;"""

# Appliquer les remplacements
print("Remplaçant les 3 sections du tableau...")

# 1. Remplacer header
if re.search(header_old, content, re.DOTALL | re.VERBOSE):
    print("✓ En-têtes trouvé")
    # Utiliser un remplacement plus simple
    pattern = r"// En-têtes du tableau - Design moderne et épuré\n        if \(element\.showHeaders !== false && \(filteredHeaders\.length > 0 \|\| tableData\.headers\.length > 0\)\) \{\n          const headerHeight = 32;"
    replacement = """// En-têtes du tableau - Ligne simple et minimale
        if (element.showHeaders !== false && (filteredHeaders.length > 0 || tableData.headers.length > 0)) {
          const headerHeight = 24;"""
    content = re.sub(pattern, replacement, content)
    print("✓ Section 1 appliquée")
else:
    print("✗ Section 1 NOT found")

# Sauvegarder
with open('resources/js/components/PDFEditor.jsx', 'w', encoding='utf-8') as f:
    f.write(content)

print("✓ Fichier sauvegardé")

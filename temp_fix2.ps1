@'
$content = Get-Content "resources/js/components/PDFEditor.jsx"
$content[2040] = "                  // Pour l''adresse, afficher toujours l''icône sur la première ligne"
$content[2041] = "                  if (field === ''address'' && lineIndex === 0) {"
$content[2042] = "                    const label = field.charAt(0).toUpperCase() + field.slice(1).replace(''_'', '' '');"
$content[2043] = "                    displayText = `${icon} ${line}`;"
$content[2044] = "                  }"
$content[2045] = "                  // Ajouter l''étiquette seulement à la première ligne si demandée (pour les autres champs)"
$content[2046] = "                  else if (showLabels && lineIndex === 0) {"
$content | Set-Content "resources/js/components/PDFEditor.jsx"
'@
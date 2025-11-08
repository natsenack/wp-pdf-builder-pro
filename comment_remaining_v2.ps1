# Script PowerShell simplifié pour commenter le reste du setTimeout
$content = Get-Content "i:\wp-pdf-builder-pro\plugin\templates\admin\settings-page.php" -Raw

# Trouver la ligne avec "window.addEventListener('pageshow', function(e) {"
$lines = $content -split "`n"
$pageshowLineIndex = -1
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match "window\.addEventListener\('pageshow', function\(e\) \{") {
        $pageshowLineIndex = $i
        break
    }
}

if ($pageshowLineIndex -ge 0) {
    # Trouver la ligne avec "}, 2000); // Attendre 2 secondes"
    $setTimeoutEndIndex = -1
    for ($i = $pageshowLineIndex; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match "\}, 2000\); // Attendre 2 secondes") {
            $setTimeoutEndIndex = $i
            break
        }
    }
    
    if ($setTimeoutEndIndex -ge $pageshowLineIndex) {
        # Ajouter les commentaires
        $lines[$pageshowLineIndex - 1] += "`n                // REMAINING SECTIONS - COMMENTED OUT FOR DEBUG`n                /*"
        $lines[$setTimeoutEndIndex] += "`n                */"
        
        # Réécrire le fichier
        $lines | Set-Content "i:\wp-pdf-builder-pro\plugin\templates\admin\settings-page.php" -NoNewline
        
        Write-Host "Commentaire ajouté avec succès"
    } else {
        Write-Host "Fin du setTimeout non trouvée"
    }
} else {
    Write-Host "Section pageshow non trouvée"
}
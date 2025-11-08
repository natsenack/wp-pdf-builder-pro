# Script PowerShell pour commenter le reste du setTimeout
$content = Get-Content "i:\wp-pdf-builder-pro\plugin\templates\admin\settings-page.php" -Raw

# Trouver la position de "window.addEventListener('pageshow'"
$pageshowIndex = $content.IndexOf("window.addEventListener('pageshow', function(e) {")

if ($pageshowIndex -gt 0) {
    # Trouver la fin du setTimeout
    $setTimeoutEndIndex = $content.IndexOf("}, 2000); // Attendre 2 secondes pour que tout soit chargé")
    
    if ($setTimeoutEndIndex -gt $pageshowIndex) {
        # Extraire la partie avant pageshow
        $beforePageshow = $content.Substring(0, $pageshowIndex)
        
        # Extraire la partie de pageshow à la fin du setTimeout
        $pageshowToEnd = $content.Substring($pageshowIndex, $setTimeoutEndIndex - $pageshowIndex + 50)
        
        # Extraire la partie après la fin du setTimeout
        $afterSetTimeout = $content.Substring($setTimeoutEndIndex + 50)
        
        # Créer le nouveau contenu avec les commentaires
        $newContent = $beforePageshow + @"

                // REMAINING SECTIONS - COMMENTED OUT FOR DEBUG
                /*
"@ + $pageshowToEnd + @"
                */
"@ + $afterSetTimeout
        
        # Écrire le fichier
        $newContent | Set-Content "i:\wp-pdf-builder-pro\plugin\templates\admin\settings-page.php" -NoNewline
        
        Write-Host "Commentaire ajouté avec succès"
    } else {
        Write-Host "Fin du setTimeout non trouvée"
    }
} else {
    Write-Host "Section pageshow non trouvée"
}
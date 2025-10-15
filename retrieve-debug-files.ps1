# Script PowerShell pour récupérer les fichiers de debug du serveur
# À exécuter après avoir reproduit l'erreur côté serveur

Write-Host "=== RÉCUPÉRATION DES FICHIERS DE DEBUG ===" -ForegroundColor Green
Write-Host ""

# Récupérer debug_received_json.txt
Write-Host "Récupération de debug_received_json.txt..." -ForegroundColor Yellow
try {
    Invoke-WebRequest -Uri "https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/debug_received_json.txt" -OutFile "debug_received_json_server.txt" -UseBasicParsing
    Write-Host "✅ Fichier debug_received_json.txt récupéré" -ForegroundColor Green
    Write-Host "Contenu (premières lignes):" -ForegroundColor Cyan
    Get-Content "debug_received_json_server.txt" | Select-Object -First 20
    Write-Host ""
} catch {
    Write-Host "❌ Impossible de récupérer debug_received_json.txt" -ForegroundColor Red
}

# Récupérer debug_raw_post_elements.txt
Write-Host "Récupération de debug_raw_post_elements.txt..." -ForegroundColor Yellow
try {
    Invoke-WebRequest -Uri "https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/debug_raw_post_elements.txt" -OutFile "debug_raw_post_elements_server.txt" -UseBasicParsing
    Write-Host "✅ Fichier debug_raw_post_elements.txt récupéré" -ForegroundColor Green
    Write-Host "Contenu (premières lignes):" -ForegroundColor Cyan
    Get-Content "debug_raw_post_elements_server.txt" | Select-Object -First 20
    Write-Host ""
} catch {
    Write-Host "❌ Impossible de récupérer debug_raw_post_elements.txt" -ForegroundColor Red
}

Write-Host "=== ANALYSE DES DONNÉES ===" -ForegroundColor Green
Write-Host ""

if (Test-Path "debug_raw_post_elements_server.txt") {
    Write-Host "Analysant les données brutes $_POST['elements']..." -ForegroundColor Yellow

    $content = Get-Content "debug_raw_post_elements_server.txt" -Raw

    # Vérifier si les données semblent être du JSON
    if ($content -match "Content:") {
        $jsonPart = $content -split "Content:\n" | Select-Object -Last 1
        $jsonPart = $jsonPart -split "=== END RAW ===" | Select-Object -First 1
        $jsonPart = $jsonPart.Trim()

        Write-Host "JSON extrait (premiers 200 chars):" -ForegroundColor Cyan
        Write-Host $jsonPart.Substring(0, [Math]::Min(200, $jsonPart.Length))

        # Tester si c'est du JSON valide
        try {
            $parsed = $jsonPart | ConvertFrom-Json
            Write-Host "✅ JSON brut semble valide côté serveur" -ForegroundColor Green
            Write-Host "Nombre d'éléments: $($parsed.Count)" -ForegroundColor Green
        } catch {
            Write-Host "❌ JSON brut invalide côté serveur: $($_.Exception.Message)" -ForegroundColor Red
        }
    }
}

Write-Host "=== FIN DE L'ANALYSE ===" -ForegroundColor Green
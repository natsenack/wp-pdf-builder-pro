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

# Récupérer debug_failed_json.txt
Write-Host "Récupération de debug_failed_json.txt..." -ForegroundColor Yellow
try {
    Invoke-WebRequest -Uri "https://threeaxe.fr/wp-content/plugins/wp-pdf-builder-pro/debug_failed_json.txt" -OutFile "debug_failed_json_server.txt" -UseBasicParsing
    Write-Host "✅ Fichier debug_failed_json.txt récupéré" -ForegroundColor Green
    Write-Host "Contenu (premières lignes):" -ForegroundColor Cyan
    Get-Content "debug_failed_json_server.txt" | Select-Object -First 20
    Write-Host ""
} catch {
    Write-Host "❌ Impossible de récupérer debug_failed_json.txt" -ForegroundColor Red
}

Write-Host "=== ANALYSE DES DONNÉES ===" -ForegroundColor Green
Write-Host ""

if (Test-Path "debug_failed_json_server.txt") {
    Write-Host "Analysant les erreurs JSON..." -ForegroundColor Yellow

    $content = Get-Content "debug_failed_json_server.txt" -Raw

    if ($content -match "Syntax error") {
        Write-Host "❌ Erreur de syntaxe détectée" -ForegroundColor Red
    }
    if ($content -match "UTF-8") {
        Write-Host "⚠️  Possible problème d'encodage UTF-8" -ForegroundColor Yellow
    }
    if ($content -match "Control character") {
        Write-Host "⚠️  Caractères de contrôle détectés" -ForegroundColor Yellow
    }
    if ($content -match "Unexpected") {
        Write-Host "⚠️  Caractère inattendu détecté" -ForegroundColor Yellow
    }
}

Write-Host "=== FIN DE L'ANALYSE ===" -ForegroundColor Green
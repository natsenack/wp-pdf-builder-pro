###############################################################################
# Script de vérification wkhtmltoimage pour PDF Builder Pro (Windows)
# Usage: .\check-wkhtmltoimage.ps1
###############################################################################

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  Vérification wkhtmltoimage (Windows)" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Fonction de vérification
function Test-Command {
    param(
        [string]$Command,
        [string]$Name
    )
    
    try {
        $path = (Get-Command $Command -ErrorAction Stop).Source
        Write-Host "✅ $Name`: INSTALLÉ" -ForegroundColor Green
        Write-Host "   Chemin: $path" -ForegroundColor Gray
        
        # Obtenir la version
        $version = & $Command --version 2>&1 | Select-Object -First 1
        Write-Host "   Version: $version" -ForegroundColor Gray
        
        return $true
    }
    catch {
        Write-Host "❌ $Name`: NON INSTALLÉ" -ForegroundColor Red
        return $false
    }
}

# 1. Vérifier wkhtmltoimage
Write-Host "1. Vérification binaire wkhtmltoimage" -ForegroundColor Yellow
Write-Host "--------------------------------------" -ForegroundColor Yellow
$wkhtmlInstalled = Test-Command -Command "wkhtmltoimage" -Name "wkhtmltoimage"
Write-Host ""

# 2. Vérifier wkhtmltopdf
Write-Host "2. Vérification wkhtmltopdf (même package)" -ForegroundColor Yellow
Write-Host "------------------------------------------" -ForegroundColor Yellow
Test-Command -Command "wkhtmltopdf" -Name "wkhtmltopdf" | Out-Null
Write-Host ""

# 3. Vérifier les chemins d'installation courants
Write-Host "3. Chemins d'installation Windows" -ForegroundColor Yellow
Write-Host "----------------------------------" -ForegroundColor Yellow

$commonPaths = @(
    "C:\Program Files\wkhtmltopdf\bin\wkhtmltoimage.exe",
    "C:\Program Files (x86)\wkhtmltopdf\bin\wkhtmltoimage.exe",
    "$env:ProgramFiles\wkhtmltopdf\bin\wkhtmltoimage.exe",
    "${env:ProgramFiles(x86)}\wkhtmltopdf\bin\wkhtmltoimage.exe"
)

$foundInPath = $false
foreach ($path in $commonPaths) {
    if (Test-Path $path) {
        Write-Host "  ✅ Trouvé: $path" -ForegroundColor Green
        $foundInPath = $true
        
        # Vérifier si dans le PATH
        $envPath = $env:PATH -split ';'
        $binDir = Split-Path $path
        if ($envPath -contains $binDir) {
            Write-Host "     ✅ Dans le PATH système" -ForegroundColor Green
        } else {
            Write-Host "     ⚠️  Non dans le PATH système" -ForegroundColor Yellow
            Write-Host "     Ajoutez au PATH: $binDir" -ForegroundColor Gray
        }
    }
}

if (-not $foundInPath) {
    Write-Host "  ❌ Aucun binaire trouvé dans les chemins standards" -ForegroundColor Red
}

Write-Host ""

# 4. Test de génération
Write-Host "4. Test de génération d'image" -ForegroundColor Yellow
Write-Host "------------------------------" -ForegroundColor Yellow

if ($wkhtmlInstalled) {
    $testHtml = [System.IO.Path]::GetTempFileName() + ".html"
    $testPng = [System.IO.Path]::GetTempFileName() + ".png"
    
    # Créer HTML de test
    $htmlContent = @"
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 30px;
            text-align: center;
        }
        h1 { font-size: 36px; margin: 0; }
        p { font-size: 18px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ wkhtmltoimage fonctionne!</h1>
        <p>PDF Builder Pro - Test de génération</p>
        <p>Date: $(Get-Date -Format 'dd/MM/yyyy HH:mm:ss')</p>
        <p>Système: Windows</p>
    </div>
</body>
</html>
"@
    
    $htmlContent | Out-File -FilePath $testHtml -Encoding UTF8
    
    Write-Host "Génération d'une image de test..." -ForegroundColor Gray
    
    try {
        $process = Start-Process -FilePath "wkhtmltoimage" `
            -ArgumentList "--format", "png", "--width", "800", $testHtml, $testPng `
            -Wait -PassThru -NoNewWindow -RedirectStandardError "$env:TEMP\wkhtml-error.txt"
        
        if (Test-Path $testPng) {
            $size = (Get-Item $testPng).Length
            Write-Host "✅ Image générée avec succès!" -ForegroundColor Green
            Write-Host "   Fichier: $testPng" -ForegroundColor Gray
            Write-Host "   Taille: $size octets" -ForegroundColor Gray
            Write-Host ""
            Write-Host "Ouverture de l'image..." -ForegroundColor Gray
            Start-Process $testPng
            
            # Nettoyer après 30 secondes
            Start-Job -ScriptBlock {
                param($html, $png)
                Start-Sleep -Seconds 30
                Remove-Item $html -ErrorAction SilentlyContinue
                Remove-Item $png -ErrorAction SilentlyContinue
            } -ArgumentList $testHtml, $testPng | Out-Null
        }
        else {
            Write-Host "❌ Échec: fichier non créé" -ForegroundColor Red
            
            if (Test-Path "$env:TEMP\wkhtml-error.txt") {
                $errorContent = Get-Content "$env:TEMP\wkhtml-error.txt"
                Write-Host "Erreur:" -ForegroundColor Red
                Write-Host $errorContent -ForegroundColor Gray
            }
        }
    }
    catch {
        Write-Host "❌ Erreur lors de la génération: $_" -ForegroundColor Red
    }
    finally {
        Remove-Item $testHtml -ErrorAction SilentlyContinue
    }
}
else {
    Write-Host "⚠️  Test ignoré car wkhtmltoimage n'est pas installé" -ForegroundColor Yellow
}

Write-Host ""

# 5. Vérifier PHP
Write-Host "5. Vérification PHP" -ForegroundColor Yellow
Write-Host "-------------------" -ForegroundColor Yellow

try {
    $phpVersion = php -v 2>&1 | Select-Object -First 1
    Write-Host "PHP version: $phpVersion" -ForegroundColor Gray
    
    # Tester shell_exec
    $testOutput = php -r "echo shell_exec('where wkhtmltoimage 2>&1');" 2>&1
    
    if ($testOutput -and $testOutput -notmatch "error" -and $testOutput -notmatch "disabled") {
        Write-Host "✅ PHP peut exécuter wkhtmltoimage" -ForegroundColor Green
        Write-Host "   Chemin détecté: $testOutput" -ForegroundColor Gray
    }
    else {
        Write-Host "❌ PHP ne peut pas exécuter wkhtmltoimage" -ForegroundColor Red
        Write-Host "   Vérifiez disable_functions dans php.ini" -ForegroundColor Yellow
        Write-Host "   La fonction shell_exec doit être autorisée" -ForegroundColor Yellow
    }
}
catch {
    Write-Host "⚠️  PHP CLI non trouvé dans le PATH" -ForegroundColor Yellow
    Write-Host "   Assurez-vous que PHP est dans votre PATH système" -ForegroundColor Gray
}

Write-Host ""

# Résumé
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "  RÉSUMÉ" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

if ($wkhtmlInstalled) {
    Write-Host "✅ wkhtmltoimage est installé et fonctionnel" -ForegroundColor Green
    Write-Host ""
    Write-Host "Vous pouvez maintenant utiliser la génération PNG/JPG" -ForegroundColor White
    Write-Host "dans PDF Builder Pro (fonctionnalité premium)" -ForegroundColor White
}
else {
    Write-Host "❌ wkhtmltoimage n'est PAS installé" -ForegroundColor Red
    Write-Host ""
    Write-Host "Installation pour Windows:" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "1. Téléchargez l'installateur depuis:" -ForegroundColor White
    Write-Host "   https://wkhtmltopdf.org/downloads.html" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "2. Choisissez la version 64-bit pour Windows" -ForegroundColor White
    Write-Host "   (wkhtmltox-0.12.6-1.msvc2015-win64.exe)" -ForegroundColor Gray
    Write-Host ""
    Write-Host "3. Exécutez l'installateur" -ForegroundColor White
    Write-Host ""
    Write-Host "4. Ajoutez au PATH système:" -ForegroundColor White
    Write-Host "   C:\Program Files\wkhtmltopdf\bin" -ForegroundColor Gray
    Write-Host ""
    Write-Host "5. Redémarrez PowerShell/CMD pour appliquer les changements" -ForegroundColor White
    Write-Host ""
    Write-Host "Ou utilisez Chocolatey:" -ForegroundColor Yellow
    Write-Host "   choco install wkhtmltopdf" -ForegroundColor Gray
}

Write-Host ""
Write-Host "Documentation: docs/WKHTMLTOIMAGE_INSTALLATION.md" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

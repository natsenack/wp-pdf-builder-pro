# Script ULTRA RAPIDE de déploiement# Script ULTRA RAPIDE de nettoyage et redéploiement

# Utilise ZIP + FTP pour une vitesse maximale# Utilise ZIP + FTP pour une vitesse maximale



Write-Host "🚀 DÉPLOIEMENT ULTRA-RAPIDE" -ForegroundColor CyanWrite-Host "🚀 NETTOYAGE ULTRA-RAPIDE ET REDÉPLOIEMENT" -ForegroundColor Cyan

Write-Host "===========================" -ForegroundColor CyanWrite-Host "================================================" -ForegroundColor Cyan



# Configuration FTP# Configuration FTP (récupérée depuis deploy-simple.ps1)

$FtpServer = "65.108.242.181"$FtpServer = "65.108.242.181"

$FtpUsername = "nats"$FtpUsername = "nats"

$FtpPassword = "iZ6vU3zV2y"$FtpPassword = "iZ6vU3zV2y"

$RemotePath = "/wp-content/plugins"$RemotePath = "/wp-content/plugins"

$PluginName = "wp-pdf-builder-pro"$PluginName = "wp-pdf-builder-pro"

$LocalPluginPath = "D:\wp-pdf-builder-pro\plugin"$LocalPluginPath = "D:\wp-pdf-builder-pro\plugin"

$ZipFile = "D:\wp-pdf-builder-pro\plugin-temp.zip"$ZipFile = "D:\wp-pdf-builder-pro\plugin-temp.zip"



Write-Host "🔗 Configuration détectée" -ForegroundColor GreenWrite-Host "🔗 Configuration détectée" -ForegroundColor Green

Write-Host "   FTP: $FtpServer" -ForegroundColor GrayWrite-Host "   FTP: $FtpServer" -ForegroundColor Gray

Write-Host "   Plugin: $PluginName" -ForegroundColor GrayWrite-Host "   Plugin: $PluginName" -ForegroundColor Gray

Write-Host ""Write-Host ""



# Étape 1: Compiler les assets# Étape 1: Compiler les assets

Write-Host "1️⃣ Compilation des assets..." -ForegroundColor YellowWrite-Host "1️⃣ Compilation des assets..." -ForegroundColor Yellow

Push-Location "D:\wp-pdf-builder-pro"Push-Location "D:\wp-pdf-builder-pro"

npm run buildnpm run build

if ($LASTEXITCODE -ne 0) {if ($LASTEXITCODE -ne 0) {

    Write-Host "❌ Erreur de compilation" -ForegroundColor Red    Write-Host "❌ Erreur de compilation" -ForegroundColor Red

    exit 1    exit 1

}}

Pop-LocationPop-Location

Write-Host "✅ Assets compilés" -ForegroundColor GreenWrite-Host "✅ Assets compilés" -ForegroundColor Green



# Étape 2: Créer le ZIP# Étape 2: Créer le ZIP

Write-Host "2️⃣ Création du ZIP..." -ForegroundColor YellowWrite-Host "2️⃣ Création du ZIP..." -ForegroundColor Yellow

if (Test-Path $ZipFile) { Remove-Item $ZipFile -Force }if (Test-Path $ZipFile) { Remove-Item $ZipFile -Force }



# Utiliser Compress-Archive# Utiliser 7zip si disponible, sinon Compress-Archive

Write-Host "   Utilisation de Compress-Archive..." -ForegroundColor Graytry {

Compress-Archive -Path "$LocalPluginPath\*" -DestinationPath $ZipFile -CompressionLevel Fastest    if (Get-Command "7z" -ErrorAction SilentlyContinue) {

        Write-Host "   Utilisation de 7-Zip..." -ForegroundColor Gray

$zipSize = (Get-Item $ZipFile).Length / 1MB        & 7z a -tzip $ZipFile "$LocalPluginPath\*" | Out-Null

Write-Host "✅ ZIP créé: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Green    } else {

        Write-Host "   Utilisation de Compress-Archive..." -ForegroundColor Gray

# Étape 3: Test FTP        Compress-Archive -Path "$LocalPluginPath\*" -DestinationPath $ZipFile -CompressionLevel Fastest

Write-Host "3️⃣ Test connexion FTP..." -ForegroundColor Yellow    }

try {} catch {

    $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpServer$RemotePath")    Write-Host "❌ Erreur lors de la création du ZIP" -ForegroundColor Red

    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)    exit 1

    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory}

    $ftpRequest.Timeout = 10000

    $response = $ftpRequest.GetResponse()$zipSize = (Get-Item $ZipFile).Length / 1MB

    $response.Close()Write-Host "✅ ZIP créé: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Green

    Write-Host "✅ Connexion FTP OK" -ForegroundColor Green

} catch {# Étape 3: Test FTP

    Write-Host "❌ Erreur FTP: $($_.Exception.Message)" -ForegroundColor RedWrite-Host "3️⃣ Test connexion FTP..." -ForegroundColor Yellow

    exit 1try {

}    $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpServer$RemotePath")

    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)

# Étape 4: Upload du ZIP    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory

Write-Host "4️⃣ Upload du ZIP..." -ForegroundColor Yellow    $ftpRequest.Timeout = 10000

$remoteZipPath = "ftp://$FtpServer$RemotePath/$PluginName.zip"    $response = $ftpRequest.GetResponse()

    $response.Close()

try {    Write-Host "✅ Connexion FTP OK" -ForegroundColor Green

    $uploadRequest = [System.Net.FtpWebRequest]::Create($remoteZipPath)} catch {

    $uploadRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)    Write-Host "❌ Erreur FTP: $($_.Exception.Message)" -ForegroundColor Red

    $uploadRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile    exit 1

    $uploadRequest.UseBinary = $true}

    $uploadRequest.Timeout = 300000  # 5 minutes pour l'upload

# Étape 4: Supprimer l'ancien plugin

    $fileContents = [System.IO.File]::ReadAllBytes($ZipFile)Write-Host "4️⃣ Suppression de l'ancien plugin..." -ForegroundColor Yellow

    $uploadRequest.ContentLength = $fileContents.Lengthtry {

    # Supprimer le dossier plugin

    $startTime = Get-Date    $deleteRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpServer$RemotePath/$PluginName")

    Write-Host "   Upload en cours..." -ForegroundColor Gray    $deleteRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)

    $deleteRequest.Method = [System.Net.WebRequestMethods+Ftp]::RemoveDirectory

    $requestStream = $uploadRequest.GetRequestStream()    $deleteRequest.Timeout = 30000

    $requestStream.Write($fileContents, 0, $fileContents.Length)    $deleteRequest.GetResponse() | Out-Null

    $requestStream.Close()    Write-Host "✅ Ancien plugin supprimé" -ForegroundColor Green

} catch {

    $response = $uploadRequest.GetResponse()    Write-Host "⚠️ Impossible de supprimer l'ancien plugin (peut-être déjà absent)" -ForegroundColor Yellow

    $response.Close()}



    $endTime = Get-Date# Étape 5: Upload du script de nettoyage

    $duration = $endTime - $startTimeWrite-Host "5️⃣ Upload du script de nettoyage..." -ForegroundColor Yellow

    $speed = [math]::Round($zipSize / $duration.TotalSeconds, 2)$cleanScriptPath = "D:\wp-pdf-builder-pro\build\clean-server.php"

$remoteCleanScript = "ftp://$FtpServer$RemotePath/clean-server.php"

    Write-Host "✅ ZIP uploadé en $($duration.TotalSeconds.ToString("F1"))s ($speed MB/s)" -ForegroundColor Green

try {

} catch {    $uploadRequest = [System.Net.FtpWebRequest]::Create($remoteCleanScript)

    Write-Host "❌ Erreur upload ZIP: $($_.Exception.Message)" -ForegroundColor Red    $uploadRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)

    exit 1    $uploadRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile

}    $uploadRequest.UseBinary = $true

    $uploadRequest.Timeout = 30000

# Étape 5: Instructions pour l'extraction côté serveur

Write-Host ""    $fileContents = [System.IO.File]::ReadAllBytes($cleanScriptPath)

Write-Host "🎯 DÉPLOIEMENT TERMINÉ !" -ForegroundColor Green    $uploadRequest.ContentLength = $fileContents.Length

Write-Host "==========================" -ForegroundColor Green

Write-Host ""    $requestStream = $uploadRequest.GetRequestStream()

Write-Host "📋 Actions à faire sur le serveur :" -ForegroundColor Cyan    $requestStream.Write($fileContents, 0, $fileContents.Length)

Write-Host ""    $requestStream.Close()

Write-Host "1️⃣ OUVREZ VOTRE NAVIGATEUR" -ForegroundColor Yellow

Write-Host "2️⃣ Allez à cette URL :" -ForegroundColor White    $response = $uploadRequest.GetResponse()

Write-Host "   https://votre-site.com/wp-content/plugins/clean-server.php" -ForegroundColor Cyan    $response.Close()

Write-Host ""

Write-Host "   🔄 Le script va automatiquement :" -ForegroundColor Gray    Write-Host "✅ Script de nettoyage uploadé" -ForegroundColor Green

Write-Host "      • Supprimer l'ancien plugin" -ForegroundColor White

Write-Host "      • Extraire le nouveau ZIP" -ForegroundColor White} catch {

Write-Host "      • Corriger les permissions" -ForegroundColor White    Write-Host "❌ Erreur upload script: $($_.Exception.Message)" -ForegroundColor Red

Write-Host "      • Vérifier les templates" -ForegroundColor White    exit 1

Write-Host ""}

Write-Host "3️⃣ APRÈS EXÉCUTION :" -ForegroundColor Yellow

Write-Host "   • Supprimez le fichier clean-server.php du serveur" -ForegroundColor White# Étape 6: Upload du ZIP

Write-Host "   • Videz le cache WordPress si nécessaire" -ForegroundColor WhiteWrite-Host "6️⃣ Upload du ZIP..." -ForegroundColor Yellow

Write-Host "   • Testez les templates prédéfinis" -ForegroundColor White$remoteZipPath = "ftp://$FtpServer$RemotePath/$PluginName.zip"

Write-Host ""

Write-Host "🔍 Test final :" -ForegroundColor Yellowtry {

Write-Host "   Templates → Galerie → Corporate → 'FACTURE PROFESSIONNELLE'" -ForegroundColor White    $uploadRequest = [System.Net.FtpWebRequest]::Create($remoteZipPath)

    $uploadRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUsername, $FtpPassword)

# Nettoyer le ZIP local    $uploadRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile

Remove-Item $ZipFile -Force -ErrorAction SilentlyContinue    $uploadRequest.UseBinary = $true
    $uploadRequest.Timeout = 300000  # 5 minutes pour l'upload

    $fileContents = [System.IO.File]::ReadAllBytes($ZipFile)
    $uploadRequest.ContentLength = $fileContents.Length

    $startTime = Get-Date
    Write-Host "   Upload en cours..." -ForegroundColor Gray

    $requestStream = $uploadRequest.GetRequestStream()
    $requestStream.Write($fileContents, 0, $fileContents.Length)
    $requestStream.Close()

    $response = $uploadRequest.GetResponse()
    $response.Close()

    $endTime = Get-Date
    $duration = $endTime - $startTime
    $speed = [math]::Round($zipSize / $duration.TotalSeconds, 2)

    Write-Host "✅ ZIP uploadé en $($duration.TotalSeconds.ToString("F1"))s ($speed MB/s)" -ForegroundColor Green

} catch {
    Write-Host "❌ Erreur upload ZIP: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

    Write-Host "✅ ZIP uploadé en $($duration.TotalSeconds.ToString("F1"))s ($speed MB/s)" -ForegroundColor Green

} catch {
    Write-Host "❌ Erreur upload ZIP: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Étape 7: Instructions pour l'extraction côté serveur
Write-Host ""
Write-Host "🎯 DÉPLOIEMENT TERMINÉ !" -ForegroundColor Green
Write-Host "==========================" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Actions à faire sur le serveur :" -ForegroundColor Cyan
Write-Host ""
Write-Host "1️⃣ OUVREZ VOTRE NAVIGATEUR" -ForegroundColor Yellow
Write-Host "2️⃣ Allez à cette URL :" -ForegroundColor White
Write-Host "   https://votre-site.com/wp-content/plugins/clean-server.php" -ForegroundColor Cyan
Write-Host ""
Write-Host "   🔄 Le script va automatiquement :" -ForegroundColor Gray
Write-Host "      • Supprimer l'ancien plugin" -ForegroundColor White
Write-Host "      • Extraire le nouveau ZIP" -ForegroundColor White
Write-Host "      • Corriger les permissions" -ForegroundColor White
Write-Host "      • Vérifier les templates" -ForegroundColor White
Write-Host ""
Write-Host "3️⃣ APRÈS EXÉCUTION :" -ForegroundColor Yellow
Write-Host "   • Supprimez le fichier clean-server.php du serveur" -ForegroundColor White
Write-Host "   • Videz le cache WordPress si nécessaire" -ForegroundColor White
Write-Host "   • Testez les templates prédéfinis" -ForegroundColor White
Write-Host ""
Write-Host "⚡ Avantages de cette méthode :" -ForegroundColor Green
Write-Host "   • Nettoyage automatique complet" -ForegroundColor White
Write-Host "   • Upload ultra-rapide (ZIP compressé)" -ForegroundColor White
Write-Host "   • Pas besoin d'accès SSH" -ForegroundColor White
Write-Host ""
Write-Host "🔍 Test final :" -ForegroundColor Yellow
Write-Host "   Templates → Galerie → Corporate → 'FACTURE PROFESSIONNELLE'" -ForegroundColor White

# Nettoyer le ZIP local
Remove-Item $ZipFile -Force -ErrorAction SilentlyContinue
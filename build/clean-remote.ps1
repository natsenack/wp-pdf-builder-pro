# Script pour nettoyer le dossier distant sur le serveur FTP
# Vide complètement le dossier wp-pdf-builder-pro avant le déploiement

param(
    [switch]$Force
)

$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "🧹 Nettoyage du dossier distant: $FtpPath" -ForegroundColor Yellow
Write-Host "Serveur: $FtpHost" -ForegroundColor White
Write-Host ("=" * 50) -ForegroundColor White

# Fonction pour lister les fichiers distants
function Get-FtpFileList {
    param([string]$path)
    try {
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$path/")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Credentials = $webclient.Credentials
        $response = $ftpRequest.GetResponse()
        $reader = New-Object System.IO.StreamReader($response.GetResponseStream())
        $files = $reader.ReadToEnd().Split("`n") | Where-Object { $_ -and $_ -notmatch '^\.$' -and $_ -notmatch '^\.\.$' }
        $reader.Close()
        $response.Close()
        return $files
    } catch {
        Write-Host "Erreur lors de la liste des fichiers: $($_.Exception.Message)" -ForegroundColor Red
        return $null
    }
}

# Fonction pour supprimer un fichier distant
function Remove-FtpFile {
    param([string]$filePath)
    try {
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$filePath")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $ftpRequest.Credentials = $webclient.Credentials
        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Host "✓ Supprimé: $filePath" -ForegroundColor Green
        return $true
    } catch {
        Write-Host "✗ Erreur suppression $filePath : $($_.Exception.Message)" -ForegroundColor Red
        return $false
    }
}

# Fonction pour supprimer un dossier distant (récursif)
function Remove-FtpDirectory {
    param([string]$dirPath)

    # Lister le contenu du dossier
    $files = Get-FtpFileList -path $dirPath
    if ($null -eq $files) {
        Write-Host "⚠️ Impossible d'accéder au dossier: $dirPath" -ForegroundColor Yellow
        return
    }

    # Supprimer récursivement le contenu
    foreach ($file in $files) {
        $fullPath = "$dirPath/$file"
        try {
            # Tester si c'est un dossier ou un fichier
            $webclient = New-Object System.Net.WebClient
            $webclient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
            $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$fullPath/")
            $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
            $ftpRequest.Credentials = $webclient.Credentials
            $response = $ftpRequest.GetResponse()
            $response.Close()
            # C'est un dossier, supprimer récursivement
            Remove-FtpDirectory -dirPath $fullPath
        } catch {
            # C'est un fichier, supprimer directement
            Remove-FtpFile -filePath $fullPath
        }
    }

    # Supprimer le dossier vide
    try {
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$dirPath/")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::RemoveDirectory
        $ftpRequest.Credentials = $webclient.Credentials
        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Host "✓ Dossier supprimé: $dirPath" -ForegroundColor Green
    } catch {
        Write-Host "✗ Erreur suppression dossier $dirPath : $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Vérifier la connexion
Write-Host "🔍 Test de connexion FTP..." -ForegroundColor Cyan
try {
    $files = Get-FtpFileList -path $FtpPath
    if ($null -eq $files) {
        Write-Host "❌ Impossible de se connecter au serveur FTP" -ForegroundColor Red
        exit 1
    }
    Write-Host "✅ Connexion FTP établie" -ForegroundColor Green
} catch {
    Write-Host "❌ Erreur de connexion FTP: $($_.Exception.Message)" -ForegroundColor Red
    exit 1
}

# Lister les fichiers/dossiers à supprimer
$items = Get-FtpFileList -path $FtpPath
if ($null -eq $items -or $items.Count -eq 0) {
    Write-Host "📁 Le dossier distant est déjà vide" -ForegroundColor Green
    exit 0
}

Write-Host "📋 Contenu du dossier distant:" -ForegroundColor Cyan
foreach ($item in $items) {
    Write-Host "  - $item" -ForegroundColor White
}

# Demander confirmation
if (-not $Force) {
    Write-Host "" -ForegroundColor White
    $confirmation = Read-Host "⚠️  Voulez-vous vraiment supprimer TOUS ces éléments? (oui/non)"
    if ($confirmation -ne "oui") {
        Write-Host "❌ Opération annulée par l'utilisateur" -ForegroundColor Yellow
        exit 0
    }
} else {
    Write-Host "" -ForegroundColor White
    Write-Host "💪 Mode forcé activé - Suppression automatique" -ForegroundColor Yellow
}

Write-Host "" -ForegroundColor White
Write-Host "🗑️  Suppression en cours..." -ForegroundColor Yellow

# Supprimer chaque élément
$deletedCount = 0
$totalCount = $items.Count

foreach ($item in $items) {
    $fullPath = "$FtpPath/$item"
    Write-Host "Suppression de $item..." -ForegroundColor White

    try {
        # Tester si c'est un dossier
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$fullPath/")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
        $ftpRequest.Credentials = $webclient.Credentials
        $response = $ftpRequest.GetResponse()
        $response.Close()
        # C'est un dossier
        Remove-FtpDirectory -dirPath $fullPath
    } catch {
        # C'est un fichier
        Remove-FtpFile -filePath $fullPath
    }

    $deletedCount++
    $percent = [math]::Round(($deletedCount / $totalCount) * 100)
    Write-Progress -Activity "Nettoyage du serveur distant" -Status "$deletedCount/$totalCount éléments supprimés" -PercentComplete $percent
}

Write-Progress -Activity "Nettoyage du serveur distant" -Completed

# Vérification finale
Write-Host "" -ForegroundColor White
Write-Host "🔍 Vérification finale..." -ForegroundColor Cyan
$remainingItems = Get-FtpFileList -path $FtpPath
if ($null -eq $remainingItems -or $remainingItems.Count -eq 0) {
    Write-Host "✅ Dossier distant complètement nettoyé!" -ForegroundColor Green
} else {
    Write-Host "⚠️  Certains éléments n'ont pas pu être supprimés:" -ForegroundColor Yellow
    foreach ($item in $remainingItems) {
        Write-Host "  - $item" -ForegroundColor Red
    }
}

Write-Host "" -ForegroundColor White
Write-Host "🎉 Nettoyage terminé! Le serveur est prêt pour le déploiement." -ForegroundColor Green
# Script simple pour uploader les vendor files
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$WorkingDir = "I:\wp-pdf-builder-pro"

Write-Host "Upload des vendor files..." -ForegroundColor Cyan

# Fonction pour créer un répertoire distant
function Create-RemoteDirectory {
    param([string]$remoteDir)

    try {
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $ftpRequest = [System.Net.FtpWebRequest]::Create("ftp://$FtpHost$FtpPath/$remoteDir")
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $ftpRequest.Credentials = $webclient.Credentials
        $response = $ftpRequest.GetResponse()
        $response.Close()
        Write-Host "✓ Created directory: $remoteDir" -ForegroundColor Green
    } catch {
        # Directory might already exist, ignore error
    }
}

# Fonction pour uploader un fichier
function Upload-File {
    param([string]$localPath, [string]$remotePath)

    try {
        $webclient = New-Object System.Net.WebClient
        $webclient.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)
        $webclient.UploadFile("ftp://$FtpHost$FtpPath/$remotePath", $localPath)
        Write-Host "✓ $remotePath" -ForegroundColor Green
    } catch {
        Write-Host "✗ $remotePath - $($_.Exception.Message)" -ForegroundColor Red
    }
}

# Créer les répertoires nécessaires
Create-RemoteDirectory "plugin/vendor"
Create-RemoteDirectory "plugin/vendor/dompdf"
Create-RemoteDirectory "plugin/vendor/dompdf/lib"
Create-RemoteDirectory "plugin/vendor/dompdf/lib/fonts"
Create-RemoteDirectory "plugin/vendor/masterminds"
Create-RemoteDirectory "plugin/vendor/masterminds/html5"
Create-RemoteDirectory "plugin/vendor/masterminds/html5/Parser"
Create-RemoteDirectory "plugin/vendor/masterminds/html5/Serializer"

# Uploader les fichiers vendor essentiels d'abord
$essentialFiles = @(
    "plugin/vendor/autoload.php",
    "plugin/vendor/dompdf/lib/Dompdf.php",
    "plugin/vendor/dompdf/lib/Options.php",
    "plugin/vendor/masterminds/html5/HTML5.php"
)

foreach ($file in $essentialFiles) {
    $localPath = "$WorkingDir\$($file.Replace('/', '\'))"
    if (Test-Path $localPath) {
        Upload-File -localPath $localPath -remotePath $file
    }
}

Write-Host "Upload termine"
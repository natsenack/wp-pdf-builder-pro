# Script de test FTP simple
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

$files = @("plugin/bootstrap.php", "plugin/rest-api-deep-diagnostic.php", "plugin/rest-api-server-fix.php")

foreach ($file in $files) {
    Write-Host "Upload de $file..."
    try {
        $ftpUri = "ftp://${FtpUser}:${FtpPass}@${FtpHost}${FtpPath}/$file"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 30000

        $fileContent = [System.IO.File]::ReadAllBytes($file)
        $ftpRequest.ContentLength = $fileContent.Length

        $requestStream = $ftpRequest.GetRequestStream()
        $requestStream.Write($fileContent, 0, $fileContent.Length)
        $requestStream.Close()

        $response = $ftpRequest.GetResponse()
        $response.Close()

        Write-Host "✅ $file uploadé avec succès"
    } catch {
        Write-Host "❌ Erreur upload $file : $($_.Exception.Message)"
    }
}

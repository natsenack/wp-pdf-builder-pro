# Upload settings-main.js via FTP
$ftpHost = "65.108.242.181"
$ftpUser = "nats"
$ftpPass = "iZ6vU3zV2y"
$remotePath = "/wp-content/plugins/wp-pdf-builder-pro/assets/js/settings-main.js"
$localFile = "I:\wp-pdf-builder-pro-V2\plugin\assets\js\settings-main.js"

Write-Host "Uploading $localFile to ftp://$ftpHost$remotePath"

try {
    $ftpUri = "ftp://$ftpUser`:$ftpPass@$ftpHost$remotePath"
    $webRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
    $webRequest.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $webRequest.UseBinary = $true
    $webRequest.UsePassive = $true

    $fileContent = [System.IO.File]::ReadAllBytes($localFile)
    $webRequest.ContentLength = $fileContent.Length

    $requestStream = $webRequest.GetRequestStream()
    $requestStream.Write($fileContent, 0, $fileContent.Length)
    $requestStream.Close()

    $response = $webRequest.GetResponse()
    $response.Close()

    Write-Host "✅ Upload successful!" -ForegroundColor Green
} catch {
    Write-Host "❌ Upload failed: $($_.Exception.Message)" -ForegroundColor Red
}
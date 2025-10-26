$ftpServer = "ftp.cluster031.hosting.ovh.net"
$username = "wp-pdf-builder-pro"
$password = "pdfbuilder2024!"
$localFile = "assets/js/dist/bundle-test.js"
$remoteFile = "/www/wp-content/plugins/wp-pdf-builder-pro/assets/js/dist/bundle-test.js"

try {
    $webclient = New-Object System.Net.WebClient
    $webclient.Credentials = New-Object System.Net.NetworkCredential($username, $password)
    $webclient.UploadFile("ftp://$ftpServer$remoteFile", $localFile)
    Write-Host "✅ Bundle test script deployed successfully"
} catch {
    Write-Host "❌ Failed to deploy bundle test script: $($_.Exception.Message)"
}
$ftpHost = "65.108.242.181"$ftpHost = "65.108.242.181"

$ftpUser = "nats"$ftpUser = "nats"

$ftpPass = "iZ6vU3zV2y"$ftpPass = "iZ6vU3zV2y"

$basePath = "d:\wp-pdf-builder-pro"$basePath = "d:\wp-pdf-builder-pro"



$files = @($files = @(

    @{local="src/Managers/PDF_Builder_Template_Manager.php"; remote="/wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_Template_Manager.php"},    @{local="src/Managers/PDF_Builder_Template_Manager.php"; remote="/wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_Template_Manager.php"},

    @{local="src/Managers/PDF_Builder_WooCommerce_Integration.php"; remote="/wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_WooCommerce_Integration.php"},    @{local="src/Managers/PDF_Builder_WooCommerce_Integration.php"; remote="/wp-content/plugins/wp-pdf-builder-pro/src/Managers/PDF_Builder_WooCommerce_Integration.php"},

    @{local="bootstrap.php"; remote="/wp-content/plugins/wp-pdf-builder-pro/bootstrap.php"}    @{local="bootstrap.php"; remote="/wp-content/plugins/wp-pdf-builder-pro/bootstrap.php"}

))



$credential = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)$credential = New-Object System.Net.NetworkCredential($ftpUser, $ftpPass)

$success = 0$success = 0

$failed = 0$failed = 0



Write-Host "`n🚀 DEPLOIEMENT FTP SIMPLE`n" -ForegroundColor GreenWrite-Host "`n🚀 DÉPLOIEMENT FTP SIMPLE`n" -ForegroundColor Green



foreach ($file in $files) {foreach ($file in $files) {

    $localPath = Join-Path $basePath $file.local    $localPath = Join-Path $basePath $file.local

    $ftpUri = "ftp://$ftpHost$($file.remote)"    $ftpUri = "ftp://$ftpHost$($file.remote)"

        

    Write-Host "📤 Uploading: $($file.local)" -ForegroundColor Cyan    Write-Host "📤 Uploading: $($file.local)" -ForegroundColor Cyan

        

    try {    try {

        $webClient = New-Object System.Net.WebClient        $webClient = New-Object System.Net.WebClient

        $webClient.Credentials = $credential        $webClient.Credentials = $credential

        $webClient.UploadFile($ftpUri, $localPath)        $webClient.UploadFile($ftpUri, $localPath)

        Write-Host "✅ Done`n" -ForegroundColor Green        Write-Host "✅ Done`n" -ForegroundColor Green

        $success++        $success++

    }    }

    catch {    catch {

        Write-Host "❌ Error: $($_.Exception.Message)`n" -ForegroundColor Red        Write-Host "❌ Error: $($_.Exception.Message)`n" -ForegroundColor Red

        $failed++        $failed++

    }    }

}}



Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor GrayWrite-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray

Write-Host "✅ Success: $success" -ForegroundColor GreenWrite-Host "✅ Réussi: $success" -ForegroundColor Green

Write-Host "❌ Failed: $failed" -ForegroundColor $(if($failed -gt 0) {"Red"} else {"Green"})Write-Host "❌ Échoué: $failed" -ForegroundColor $(if($failed -gt 0) {"Red"} else {"Green"})

Write-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor GrayWrite-Host "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━" -ForegroundColor Gray


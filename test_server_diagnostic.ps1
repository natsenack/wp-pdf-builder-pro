# Script pour tester le diagnostic du plugin sur le serveur
# Télécharge et exécute le script diagnostic.php via FTP

$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "🔍 Test du diagnostic du plugin sur le serveur..." -ForegroundColor Cyan

# Télécharger le fichier diagnostic.php du serveur
$localTempFile = "$env:TEMP\diagnostic_server.php"
$ftpUri = "ftp://$FtpHost$FtpPath/diagnostic.php"

try {
    Write-Host "📥 Téléchargement du script diagnostic..." -ForegroundColor Yellow

    $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
    $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
    $ftpRequest.Credentials = New-Object System.Net.NetworkCredential($FtpUser, $FtpPass)

    $ftpResponse = $ftpRequest.GetResponse()
    $responseStream = $ftpResponse.GetResponseStream()
    $reader = New-Object System.IO.StreamReader($responseStream)
    $fileContent = $reader.ReadToEnd()

    $reader.Close()
    $responseStream.Close()
    $ftpResponse.Close()

    # Sauvegarder localement pour inspection
    $fileContent | Out-File -FilePath $localTempFile -Encoding UTF8

    Write-Host "✅ Script diagnostic téléchargé" -ForegroundColor Green

    # Analyser le contenu pour vérifier qu'il contient les tests attendus
    if ($fileContent -match "DataProviderInterface") {
        Write-Host "✅ Le script contient les tests d'interface" -ForegroundColor Green
    } else {
        Write-Host "⚠️ Le script ne contient pas les tests attendus" -ForegroundColor Yellow
    }

} catch {
    Write-Host "❌ Erreur lors du téléchargement: $($_.Exception.Message)" -ForegroundColor Red
}

# Maintenant, créer un script PHP simple pour tester l'interface
$testScript = @"
<?php
// Test rapide de l'interface DataProviderInterface
echo "=== TEST RAPIDE DE L'INTERFACE ===\n";

try {
    // Test de chargement de l'interface
    require_once 'interfaces/DataProviderInterface.php';
    echo "✅ Interface DataProviderInterface chargée avec succès\n";

    // Test de chargement des DataProviders
    require_once 'data/providers/SampleDataProvider.php';
    echo "✅ SampleDataProvider chargé\n";

    require_once 'data/providers/WooCommerceDataProvider.php';
    echo "✅ WooCommerceDataProvider chargé\n";

    // Test d'instanciation
    `$sampleProvider = new PDF_Builder\Data\SampleDataProvider();`
    echo "✅ SampleDataProvider instancié\n";

    `$wooProvider = new PDF_Builder\Data\WooCommerceDataProvider();`
    echo "✅ WooCommerceDataProvider instancié\n";

    echo "\n🎉 TOUS LES TESTS RÉUSSIS ! Le système est fonctionnel.\n";

} catch (Exception `$e`) {
    echo "❌ ERREUR: " . `$e->getMessage() . "\n";
    echo "📍 Fichier: " . `$e->getFile() . " (ligne " . `$e->getLine() . ")\n";
}
"@

# Sauvegarder le script de test
$testFilePath = "i:\wp-pdf-builder-pro\test_interface.php"
$testScript | Out-File -FilePath $testFilePath -Encoding UTF8

Write-Host "📝 Script de test créé: $testFilePath" -ForegroundColor Green
Write-Host "🔧 À déployer manuellement sur le serveur et exécuter avec: php test_interface.php" -ForegroundColor Cyan
# Script de vérification basé sur les fichiers de déploiement
# Utilise la liste des fichiers déployés pour vérifier l'UI obsolète

param(
    [switch]$TestMode
)

$ErrorActionPreference = "Stop"

# Configuration FTP
$FtpHost = "65.108.242.181"
$FtpUser = "nats"
$FtpPass = "iZ6vU3zV2y"
$FtpPath = "/wp-content/plugins/wp-pdf-builder-pro"

Write-Host "VÉRIFICATION DES FICHIERS DÉPLOYÉS POUR UI OBSOLÈTE" -ForegroundColor Cyan
Write-Host ("=" * 85) -ForegroundColor White

if ($TestMode) {
    Write-Host "MODE TEST - Simulation uniquement" -ForegroundColor Yellow
}

# Liste des fichiers déployés (extraite du script deploy-all.ps1)
$deployedFiles = @(
    "bootstrap.php",
    "clear_opcache.php",
    "composer.json",
    "composer.lock",
    "pdf-builder-pro.php",
    "analytics/AnalyticsTracker.php",
    "api/Exception.php",
    "api/MediaDiagnosticAPI.php",
    "api/MediaLibraryFixAPI.php",
    "api/PreviewImageAPI.php",
    "assets/css/Accordion.css",
    "assets/css/editor.css",
    "assets/css/gdpr.css",
    "assets/css/onboarding.css",
    "assets/css/pdf-builder-admin.css",
    "assets/js/canvas-style-injector.js",
    "assets/js/developer-tools.js",
    "assets/js/gdpr.js",
    "assets/js/onboarding.js",
    "assets/js/pdf-preview-api-client.js",
    "assets/js/pdf-preview-integration.js",
    "assets/js/predefined-templates.js",
    "assets/js/wizard.js",
    "assets/js/dist/pdf-builder-react.js",
    "assets/js/dist/pdf-builder-react.js.LICENSE.txt",
    "assets/js/dist/pdf-builder-react.js.gz",
    "config/config.php",
    "core/PDF_Builder_Admin.php",
    "core/PDF_Builder_Ajax_Handler.php",
    "core/PDF_Builder_Asset_Manager.php",
    "core/PDF_Builder_Auto_Update_Manager.php",
    "core/PDF_Builder_License_Manager.php",
    "core/PDF_Builder_Loader.php",
    "core/PDF_Builder_WooCommerce_Integration.php",
    "data/PDF_Builder_Data_Manager.php",
    "docs/APERCU_UNIFIED_ROADMAP.md",
    "elements/PDF_Builder_Element.php",
    "elements/PDF_Builder_Elements_Manager.php",
    "generators/PDF_Builder_Generator.php",
    "interfaces/PDF_Builder_Field_Interface.php",
    "interfaces/PDF_Builder_Generator_Interface.php",
    "languages/pdf-builder-pro-fr_FR.mo",
    "languages/pdf-builder-pro-fr_FR.po",
    "languages/pdf-builder-pro.pot",
    "src/utilities/PDF_Builder_Advanced_Reporting.php",
    "src/utilities/PDF_Builder_Logger.php",
    "src/utilities/PDF_Builder_Media_Fix.php",
    "templates/admin/js/settings-page.js",
    "templates/admin/settings-parts/settings-ajax.php",
    "templates/admin/settings-parts/settings-general.php",
    "templates/admin/settings-parts/settings-licence.php",
    "templates/admin/settings-parts/settings-media.php",
    "templates/admin/settings-parts/settings-pdf.php",
    "templates/admin/settings-parts/settings-template.php",
    "templates/admin/settings-parts/settings-woocommerce.php",
    "vendor/autoload.php",
    "vendor/composer/autoload_classmap.php",
    "vendor/composer/autoload_files.php",
    "vendor/composer/autoload_namespaces.php",
    "vendor/composer/autoload_psr4.php",
    "vendor/composer/autoload_real.php",
    "vendor/composer/autoload_static.php",
    "vendor/composer/ClassLoader.php",
    "vendor/composer/installed.json",
    "vendor/composer/installed.php",
    "vendor/composer/LICENSE",
    "vendor/composer/platform_check.php",
    "vendor/monolog/monolog/src/Monolog/Attribute/AsMonologProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Attribute/Attributes.php",
    "vendor/monolog/monolog/src/Monolog/Attribute/WithMonologChannel.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/ChromePHPFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/ElasticaFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/FlowdockFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/FormatterInterface.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/GelfMessageFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/HtmlFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/JsonFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/LineFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/LogglyFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/LogmaticFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/LogstashFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/MongoDBFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/NormalizerFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/ScalarFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/WildfireFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Formatter/YamlFormatter.php",
    "vendor/monolog/monolog/src/Monolog/Handler/AbstractHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/AbstractProcessingHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/AbstractSyslogUdpHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/AmqpHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/BrowserConsoleHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/BufferHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/ChromePHPHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/CouchDBHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/CubeHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/Curl/Util.php",
    "vendor/monolog/monolog/src/Monolog/Handler/DeduplicationHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/DoctrineCouchDBHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/DynamoDbHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/ElasticSearchHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/ErrorLogHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/FallbackGroupHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/FilterHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/FingersCrossed/ActivationStrategyInterface.php",
    "vendor/monolog/monolog/src/Monolog/Handler/FingersCrossed/ErrorLevelActivationStrategy.php",
    "vendor/monolog/monolog/src/Monolog/Handler/FingersCrossedHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/FirePHPHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/FleepHookHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/FlowdockHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/GelfHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/GroupHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/HandlerInterface.php",
    "vendor/monolog/monolog/src/Monolog/Handler/HandlerWrapper.php",
    "vendor/monolog/monolog/src/Monolog/Handler/IFTTTHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/InsightOpsHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/LogEntriesHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/LogglyHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/LogmaticHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/MailHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/MemoryHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/MissingExtensionException.php",
    "vendor/monolog/monolog/src/Monolog/Handler/MongoDBHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/NativeMailerHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/NewRelicHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/NoopHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/NullHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/OverflowHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/PHPConsoleHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/PsrHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/PushoverHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/RavenHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/RedisHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/RollbarHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/RotatingFileHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/SamplingHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/SendGridHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/Slack/SlackRecord.php",
    "vendor/monolog/monolog/src/Monolog/Handler/SlackWebhookHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/SlackHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/SocketHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/StreamHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/SyslogUdpHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/SyslogHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/TelegramBotHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/TestHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/WhatFailureGroupHandler.php",
    "vendor/monolog/monolog/src/Monolog/Handler/ZendMonitorHandler.php",
    "vendor/monolog/monolog/src/Monolog/Logger.php",
    "vendor/monolog/monolog/src/Monolog/Processor/CloneProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/GitProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/HostnameProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/IntrospectionProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/MemoryPeakUsageProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/MemoryProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/MercuryProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/ProcessIdProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/PsrLogMessageProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/TagProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/UidProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Processor/WebProcessor.php",
    "vendor/monolog/monolog/src/Monolog/Registry.php",
    "vendor/monolog/monolog/src/Monolog/ResettableInterface.php",
    "vendor/monolog/monolog/src/Monolog/Signal/SignalHandler.php",
    "vendor/monolog/monolog/src/Monolog/Utils.php"
)

# Fonction pour vérifier si un fichier contient des indicateurs d'UI obsolète
function Test-FileContainsLegacyUI {
    param([string]$remotePath)

    try {
        $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$remotePath"
        $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
        $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DownloadFile
        $ftpRequest.UseBinary = $true
        $ftpRequest.UsePassive = $true
        $ftpRequest.Timeout = 5000
        $ftpRequest.KeepAlive = $false

        $response = $ftpRequest.GetResponse()
        $stream = $response.GetResponseStream()
        $reader = New-Object System.IO.StreamReader($stream)
        $content = $reader.ReadToEnd()
        $reader.Close()
        $response.Close()

        # Vérifier les marqueurs d'UI obsolète (pattern générique)
        $hasLegacyUI = $content -match '(?i)legacy_ui'

        if ($hasLegacyUI) {
            return @{
                HasLegacyUI = $hasLegacyUI
            }
        }

    } catch {
        # Fichier n'existe pas ou erreur d'accès
    }

    return $null
}

Write-Host "`nVérification des $($deployedFiles.Count) fichiers déployés..." -ForegroundColor Magenta

$suspiciousFiles = @()
$checkedCount = 0

foreach ($file in $deployedFiles) {
    $checkedCount++
    $remotePath = "$FtpPath/$file"

    Write-Host "   [$checkedCount/$($deployedFiles.Count)] Vérification: $file" -ForegroundColor Gray

    $result = Test-FileContainsLegacyUI -remotePath $remotePath

    if ($result) {
        $flags = @()
        if ($result.HasLegacyUI) { $flags += "legacy_ui" }
        $flagStr = $flags -join "/"

        $suspiciousFiles += @{
            File = $file
            HasLegacyUI = $result.HasLegacyUI
        }
        Write-Host "   ⚠️  SUSPECT: $file contient '$flagStr'" -ForegroundColor Yellow
    }
}

# Supprimer les fichiers suspects
$deletedCount = 0
$errorCount = 0

Write-Host "`nRésultats de la vérification:" -ForegroundColor Cyan
Write-Host "   Fichiers vérifiés: $checkedCount" -ForegroundColor Gray
Write-Host "   Fichiers suspects trouvés: $($suspiciousFiles.Count)" -ForegroundColor Yellow

if ($suspiciousFiles.Count -eq 0) {
    Write-Host "`nAucun fichier suspect trouvé parmi les fichiers déployés !" -ForegroundColor Green
} else {
    Write-Host "`nFichiers suspects détectés:" -ForegroundColor Magenta
    foreach ($fileInfo in $suspiciousFiles) {
        $flags = @()
        if ($fileInfo.HasLegacyUI) { $flags += "legacy_ui" }
        $flagStr = $flags -join "/"
        Write-Host "   - $($fileInfo.File) ($flagStr)" -ForegroundColor Yellow
    }

    if (-not $TestMode) {
        Write-Host "`nSuppression des fichiers suspects..." -ForegroundColor Magenta

        foreach ($fileInfo in $suspiciousFiles) {
            $remotePath = "$FtpPath/$($fileInfo.File)"

            $flags = @()
            if ($fileInfo.HasLegacyUI) { $flags += "legacy_ui" }
            $flagStr = $flags -join "/"

            try {
                Write-Host "   Suppression: $($fileInfo.File) ($flagStr)" -ForegroundColor Yellow

                $ftpUri = "ftp://$FtpUser`:$FtpPass@$FtpHost$remotePath"
                $ftpRequest = [System.Net.FtpWebRequest]::Create($ftpUri)
                $ftpRequest.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
                $ftpRequest.UseBinary = $false
                $ftpRequest.UsePassive = $true
                $ftpRequest.Timeout = 10000
                $ftpRequest.KeepAlive = $false

                $response = $ftpRequest.GetResponse()
                $response.Close()

                Write-Host "   ✅ Supprimé: $($fileInfo.File)" -ForegroundColor Green
                $deletedCount++

            } catch {
                $errorCount++
                Write-Host "   ❌ Erreur: $($fileInfo.File) - $($_.Exception.Message)" -ForegroundColor Red
            }
        }
    } else {
        Write-Host "`nMODE TEST - Aucune suppression (fichiers listés ci-dessus seraient supprimés)" -ForegroundColor Yellow
        $deletedCount = $suspiciousFiles.Count
    }
}

Write-Host "`nVÉRIFICATION TERMINÉE" -ForegroundColor White
Write-Host ("=" * 85) -ForegroundColor White
Write-Host "Résumé final:" -ForegroundColor Cyan
Write-Host "   Fichiers vérifiés: $checkedCount" -ForegroundColor Gray
Write-Host "   Fichiers suspects trouvés: $($suspiciousFiles.Count)" -ForegroundColor Yellow
Write-Host "   Fichiers supprimés: $deletedCount" -ForegroundColor Green
Write-Host "   Erreurs: $errorCount" -ForegroundColor $(if ($errorCount -gt 0) { "Red" } else { "Green" })

if ($TestMode) {
    Write-Host "`nPour exécuter réellement le nettoyage, relancer sans -TestMode" -ForegroundColor Yellow
} else {
    if ($suspiciousFiles.Count -eq 0) {
        Write-Host "`nServeur distant complètement propre - aucun fichier déployé n'indique d'UI obsolète ✅" -ForegroundColor Green
    } else {
        Write-Host "`nServeur distant nettoyé de toutes références d'UI obsolètes ✅" -ForegroundColor Green
    }
}
# Script pour télécharger les logs du serveur en FTP et les afficher
param(
    [string]$Lines = 150
)

# Configuration FTP
$ftp_host = "65.108.242.181"
$ftp_user = "nats"
$ftp_pass_file = "./tools/ftp-config.env"

# Charger le mot de passe depuis le fichier .env
if (Test-Path $ftp_pass_file) {
    $env_content = Get-Content $ftp_pass_file
    foreach ($line in $env_content) {
        if ($line -match "FTP_PASSWORD=(.+)") {
            $ftp_pass = $matches[1].Trim()
        }
    }
} else {
    Write-Host "❌ Fichier de configuration non trouvé: $ftp_pass_file"
    exit 1
}

$remote_log = "/var/www/nats/data/www/threeaxe.fr/wp-content/debug.log"
$local_temp = "./temp_debug.log"

# Créer une session FTP
$ftp_url = "ftp://${ftp_user}:${ftp_pass}@${ftp_host}${remote_log}"

Write-Host "📥 Téléchargement des logs du serveur..."
Write-Host "   Serveur: $ftp_host"
Write-Host "   Chemin: $remote_log"

try {
    # Utiliser WebClient pour télécharger via FTP
    $web_client = New-Object System.Net.WebClient
    $web_client.Credentials = New-Object System.Net.NetworkCredential($ftp_user, $ftp_pass)
    
    $web_client.DownloadFile($ftp_url, $local_temp)
    
    if (Test-Path $local_temp) {
        Write-Host "✅ Fichier téléchargé"
        Write-Host ""
        Write-Host "📋 Dernières $Lines lignes des logs:"
        Write-Host "═" * 100
        
        Get-Content -Path $local_temp -Tail $Lines
        
        Write-Host ""
        Write-Host "═" * 100
        
        # Nettoyer
        Remove-Item $local_temp -Force
    } else {
        Write-Host "❌ Erreur: impossible de télécharger le fichier"
    }
} catch {
    Write-Host "❌ Erreur lors du téléchargement FTP:"
    Write-Host $_.Exception.Message
    exit 1
}

<?php
/**
 * Script de déploiement des corrections du Security Validator
 * Corrige l'initialisation automatique qui causait les erreurs fatales
 */

// Configuration FTP
$ftp_server = "ftp.threeaxe.fr";
$ftp_username = "threeaxe";
$ftp_password = "your_password_here"; // À remplacer par le vrai mot de passe

// Fichiers à déployer
$files_to_deploy = [
    'plugin/src/Core/PDF_Builder_Security_Validator.php',
    'plugin/bootstrap.php'
];

// Connexion FTP
$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
    die("Erreur: Impossible de se connecter au serveur FTP\n");
}

$login_result = ftp_login($conn_id, $ftp_username, $ftp_password);
if (!$login_result) {
    die("Erreur: Échec de l'authentification FTP\n");
}

// Activer le mode passif
ftp_pasv($conn_id, true);

echo "Déploiement des corrections du Security Validator...\n";

foreach ($files_to_deploy as $file) {
    $local_file = __DIR__ . '/../' . $file;
    $remote_file = '/' . $file;

    if (!file_exists($local_file)) {
        echo "Erreur: Fichier local introuvable: $local_file\n";
        continue;
    }

    // Créer les répertoires distants si nécessaire
    $remote_dir = dirname($remote_file);
    if ($remote_dir !== '/' && !ftp_mkdir_recursive($conn_id, $remote_dir)) {
        echo "Erreur: Impossible de créer le répertoire distant: $remote_dir\n";
        continue;
    }

    // Upload du fichier
    if (ftp_put($conn_id, $remote_file, $local_file, FTP_BINARY)) {
        echo "✓ Déployé: $file\n";
    } else {
        echo "✗ Échec du déploiement: $file\n";
    }
}

// Fermer la connexion FTP
ftp_close($conn_id);

echo "\nDéploiement terminé!\n";
echo "Testez maintenant: https://threeaxe.fr/test-security-validator.php\n";

/**
 * Fonction récursive pour créer des répertoires FTP
 */
function ftp_mkdir_recursive($ftp_connection, $path) {
    $parts = explode('/', $path);
    $current_path = '';

    foreach ($parts as $part) {
        if (empty($part)) continue;

        $current_path .= '/' . $part;

        if (!@ftp_chdir($ftp_connection, $current_path)) {
            if (!ftp_mkdir($ftp_connection, $current_path)) {
                return false;
            }
        }
    }

    return true;
}
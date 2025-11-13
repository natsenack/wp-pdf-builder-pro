<?php
/**
 * Script pour uploader les fichiers Toastr via FTP
 */

// Configuration FTP
$ftp_server = "65.108.242.181";
$ftp_user = "u295426632.web";
$ftp_pass = "Webmaster88";

// Connexion FTP
$conn_id = ftp_connect($ftp_server);
if (!$conn_id) {
    die("❌ Impossible de se connecter au serveur FTP\n");
}

// Login
if (!ftp_login($conn_id, $ftp_user, $ftp_pass)) {
    die("❌ Impossible de se connecter avec les identifiants FTP\n");
}

echo "✅ Connexion FTP établie\n";

// Chemins locaux
$local_css = 'plugin/assets/css/toastr/toastr.min.css';
$local_js = 'plugin/assets/js/toastr/toastr.min.js';

// Chemins FTP
$remote_css = '/wp-content/plugins/wp-pdf-builder-pro/assets/css/toastr/toastr.min.css';
$remote_js = '/wp-content/plugins/wp-pdf-builder-pro/assets/js/toastr/toastr.min.js';

// Uploader CSS
if (file_exists($local_css)) {
    echo "📤 Upload CSS Toastr...\n";
    if (ftp_put($conn_id, $remote_css, $local_css, FTP_BINARY)) {
        echo "✅ CSS Toastr uploadé avec succès\n";
    } else {
        echo "❌ Erreur upload CSS\n";
    }
} else {
    echo "⚠️ Fichier CSS non trouvé: $local_css\n";
}

// Uploader JS
if (file_exists($local_js)) {
    echo "📤 Upload JS Toastr...\n";
    if (ftp_put($conn_id, $remote_js, $local_js, FTP_BINARY)) {
        echo "✅ JS Toastr uploadé avec succès\n";
    } else {
        echo "❌ Erreur upload JS\n";
    }
} else {
    echo "⚠️ Fichier JS non trouvé: $local_js\n";
}

ftp_close($conn_id);
echo "\n✅ Upload terminé\n";
?>

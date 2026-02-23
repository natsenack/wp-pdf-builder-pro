<?php
/**
 * Page d'administration - Vérification du système
 * 
 * Permet de vérifier que wkhtmltoimage est correctement installé
 * et fonctionnel pour la génération PNG/JPG
 * 
 * @package PDF_Builder_Pro
 * @since 1.0.1.1
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    wp_die(__('Vous n\'avez pas les permissions nécessaires.', 'pdf-builder-pro'));
}

// Importer les classes nécessaires
require_once PDF_BUILDER_PATH . 'src/Managers/PDF_Builder_Secure_Shell_Manager.php';

use PDF_Builder\Managers\PDF_Builder_Secure_Shell_Manager as Secure_Shell_Manager;

// Fonction pour vérifier wkhtmltoimage
function pdf_builder_check_wkhtmltoimage() {
    $result = [
        'installed' => false,
        'version' => null,
        'path' => null,
        'test_passed' => false,
        'test_output_size' => 0,
        'error' => null
    ];
    
    // Vérifier si la commande est disponible
    if (!Secure_Shell_Manager::isCommandAvailable('wkhtmltoimage')) {
        $result['error'] = 'wkhtmltoimage n\'est pas installé ou n\'est pas dans le PATH système';
        return $result;
    }
    
    $result['installed'] = true;
    
    // Obtenir la version
    try {
        $version_output = Secure_Shell_Manager::executeSecureCommand('wkhtmltoimage', ['--version']);
        if ($version_output) {
            $lines = explode("\n", $version_output);
            $result['version'] = trim($lines[0]);
        }
    } catch (Exception $e) {
        $result['error'] = 'Erreur lors de la récupération de la version: ' . $e->getMessage();
    }
    
    // Obtenir le chemin
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $which_cmd = 'where';
    } else {
        $which_cmd = 'which';
    }
    
    $path_output = shell_exec("$which_cmd wkhtmltoimage 2>&1");
    if ($path_output) {
        $result['path'] = trim($path_output);
    }
    
    // Test de génération
    $temp_html = tempnam(sys_get_temp_dir(), 'pdf-builder-test-') . '.html';
    $temp_png = tempnam(sys_get_temp_dir(), 'pdf-builder-test-') . '.png';
    
    $test_html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    
</head>
<body>
    <div class="box">
        <h1>✅ Test réussi!</h1>
        <p class="success">wkhtmltoimage fonctionne correctement</p>
        <p>PDF Builder Pro</p>
        <p>' . date('d/m/Y H:i:s') . '</p>
    </div>
</body>
</html>';
    
    file_put_contents($temp_html, $test_html);
    
    try {
        $success = Secure_Shell_Manager::executeWkhtmltoimage(
            $temp_html,
            $temp_png,
            'png',
            800,
            null,
            90
        );
        
        if ($success && file_exists($temp_png)) {
            $result['test_passed'] = true;
            $result['test_output_size'] = filesize($temp_png);
            
            // Stocker temporairement pour affichage
            $display_png = WP_CONTENT_DIR . '/uploads/pdf-builder-test-' . time() . '.png';
            copy($temp_png, $display_png);
            $result['test_image_url'] = content_url('uploads/' . basename($display_png));
            
            // Nettoyer après 5 minutes
            wp_schedule_single_event(time() + 300, 'pdf_builder_cleanup_test_image', [$display_png]);
        } else {
            $result['error'] = 'La génération a échoué: fichier non créé ou vide';
        }
    } catch (Exception $e) {
        $result['error'] = 'Erreur lors du test: ' . $e->getMessage();
    } finally {
        @unlink($temp_html);
        @unlink($temp_png);
    }
    
    return $result;
}

// Action de nettoyage
add_action('pdf_builder_cleanup_test_image', function($file_path) {
    if (file_exists($file_path)) {
        @unlink($file_path);
    }
});

// Lancer la vérification si demandée
$check_result = null;
if (isset($_GET['run_check']) && $_GET['run_check'] === '1') {
    check_admin_referer('pdf_builder_system_check');
    $check_result = pdf_builder_check_wkhtmltoimage();
}

?>

<div class="wrap">
    <h1>
        <span class="dashicons dashicons-admin-tools" style="font-size: 32px; margin-right: 10px;"></span>
        Vérification du système - PDF Builder Pro
    </h1>
    
    <p class="description">
        Cette page permet de vérifier que wkhtmltoimage est correctement installé 
        et fonctionnel pour la génération d'images PNG/JPG.
    </p>
    
    <div class="card" style="max-width: none; margin-top: 20px;">
        <h2>
            <span class="dashicons dashicons-info-outline"></span>
            À propos de wkhtmltoimage
        </h2>
        <p>
            <strong>wkhtmltoimage</strong> est un outil requis pour générer des images PNG ou JPG 
            à partir des templates PDF Builder Pro. Il fait partie du package <code>wkhtmltopdf</code>.
        </p>
        <p>
            <strong>Note:</strong> Cette fonctionnalité nécessite une licence <strong>Premium</strong>.
        </p>
    </div>
    
    <div style="margin-top: 20px;">
        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=pdf-builder-system-check&run_check=1'), 'pdf_builder_system_check')); ?>" 
           class="button button-primary button-large">
            <span class="dashicons dashicons-update" style="margin-top: 3px;"></span>
            Lancer la vérification
        </a>
    </div>
    
    <?php if ($check_result !== null): ?>
        
        <div style="margin-top: 30px;">
            
            <!-- État principal -->
            <div class="notice notice-<?php echo $check_result['installed'] ? 'success' : 'error'; ?> inline" style="padding: 20px; margin: 0;">
                <h2 style="margin-top: 0;">
                    <?php if ($check_result['installed']): ?>
                        <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 32px;"></span>
                        wkhtmltoimage est installé
                    <?php else: ?>
                        <span class="dashicons dashicons-dismiss" style="color: #dc3232; font-size: 32px;"></span>
                        wkhtmltoimage n'est PAS installé
                    <?php endif; ?>
                </h2>
            </div>
            
            <!-- Détails -->
            <div class="card" style="max-width: none; margin-top: 20px;">
                <h3>Détails de la vérification</h3>
                
                <table class="widefat" style="margin-top: 15px;">
                    <tbody>
                        <tr>
                            <th style="width: 250px;">Statut d'installation</th>
                            <td>
                                <?php if ($check_result['installed']): ?>
                                    <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                                    <strong style="color: #46b450;">Installé</strong>
                                <?php else: ?>
                                    <span class="dashicons dashicons-no" style="color: #dc3232;"></span>
                                    <strong style="color: #dc3232;">Non installé</strong>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <?php if ($check_result['version']): ?>
                        <tr>
                            <th>Version</th>
                            <td><code><?php echo esc_html($check_result['version']); ?></code></td>
                        </tr>
                        <?php endif; ?>
                        
                        <?php if ($check_result['path']): ?>
                        <tr>
                            <th>Chemin d'installation</th>
                            <td><code><?php echo esc_html($check_result['path']); ?></code></td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr>
                            <th>Test de génération</th>
                            <td>
                                <?php if ($check_result['test_passed']): ?>
                                    <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                                    <strong style="color: #46b450;">Réussi</strong>
                                    <br>
                                    <small>Image générée: <?php echo esc_html(size_format($check_result['test_output_size'])); ?></small>
                                <?php else: ?>
                                    <span class="dashicons dashicons-no" style="color: #dc3232;"></span>
                                    <strong style="color: #dc3232;">Échec</strong>
                                <?php endif; ?>
                            </td>
                        </tr>
                        
                        <?php if (!empty($check_result['error'])): ?>
                        <tr>
                            <th>Erreur</th>
                            <td style="color: #dc3232;">
                                <code><?php echo esc_html($check_result['error']); ?></code>
                            </td>
                        </tr>
                        <?php endif; ?>
                        
                        <tr>
                            <th>Système d'exploitation</th>
                            <td><code><?php echo esc_html(PHP_OS); ?></code></td>
                        </tr>
                        
                        <tr>
                            <th>PHP version</th>
                            <td><code><?php echo esc_html(PHP_VERSION); ?></code></td>
                        </tr>
                        
                        <tr>
                            <th>shell_exec activé</th>
                            <td>
                                <?php 
                                $disabled = explode(',', ini_get('disable_functions'));
                                $shell_enabled = !in_array('shell_exec', $disabled);
                                ?>
                                <?php if ($shell_enabled): ?>
                                    <span class="dashicons dashicons-yes" style="color: #46b450;"></span>
                                    Activé
                                <?php else: ?>
                                    <span class="dashicons dashicons-no" style="color: #dc3232;"></span>
                                    Désactivé (requis pour wkhtmltoimage)
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- Image de test si générée -->
            <?php if (!empty($check_result['test_image_url'])): ?>
                <div class="card" style="max-width: none; margin-top: 20px;">
                    <h3>
                        <span class="dashicons dashicons-format-image"></span>
                        Image de test générée
                    </h3>
                    <p>Voici un aperçu de l'image générée par wkhtmltoimage:</p>
                    <div style="text-align: center; padding: 20px; background: #f5f5f5; border-radius: 8px;">
                        <img src="<?php echo esc_url($check_result['test_image_url']); ?>" 
                             alt="Test wkhtmltoimage" 
                             style="max-width: 100%; height: auto; border: 2px solid #ddd; border-radius: 4px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                    </div>
                    <p style="text-align: center; margin-top: 15px;">
                        <a href="<?php echo esc_url($check_result['test_image_url']); ?>" 
                           class="button"
                           target="_blank">
                            <span class="dashicons dashicons-download"></span>
                            Télécharger l'image de test
                        </a>
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- Instructions d'installation si non installé -->
            <?php if (!$check_result['installed']): ?>
                <div class="card" style="max-width: none; margin-top: 20px; border-left: 4px solid #dc3232;">
                    <h3>
                        <span class="dashicons dashicons-welcome-learn-more"></span>
                        Instructions d'installation
                    </h3>
                    
                    <p><strong>Pour installer wkhtmltoimage sur votre système:</strong></p>
                    
                    <div style="margin-top: 20px;">
                        <h4>
                            <span class="dashicons dashicons-admin-site"></span>
                            Linux (Debian/Ubuntu)
                        </h4>
                        <pre style="background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; overflow-x: auto;">sudo apt-get update
sudo apt-get install -y wkhtmltopdf</pre>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <h4>
                            <span class="dashicons dashicons-admin-site"></span>
                            Linux (CentOS/RHEL)
                        </h4>
                        <pre style="background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; overflow-x: auto;">sudo yum install -y wkhtmltopdf</pre>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <h4>
                            <span class="dashicons dashicons-admin-site"></span>
                            macOS
                        </h4>
                        <pre style="background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; overflow-x: auto;">brew install wkhtmltopdf</pre>
                    </div>
                    
                    <div style="margin-top: 20px;">
                        <h4>
                            <span class="dashicons dashicons-admin-site"></span>
                            Windows
                        </h4>
                        <p>
                            Téléchargez l'installateur depuis 
                            <a href="https://wkhtmltopdf.org/downloads.html" target="_blank">wkhtmltopdf.org/downloads.html</a>
                        </p>
                        <p>Ou utilisez Chocolatey:</p>
                        <pre style="background: #2c3e50; color: #ecf0f1; padding: 15px; border-radius: 4px; overflow-x: auto;">choco install wkhtmltopdf</pre>
                    </div>
                    
                    <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px;">
                        <p style="margin: 0;">
                            <span class="dashicons dashicons-info" style="color: #856404;"></span>
                            <strong>Documentation complète:</strong> 
                            Consultez le fichier <code>docs/WKHTMLTOIMAGE_INSTALLATION.md</code> 
                            pour des instructions détaillées et le dépannage.
                        </p>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Statut final récapitulatif -->
            <div class="card" style="max-width: none; margin-top: 20px; border-left: 4px solid <?php echo $check_result['test_passed'] ? '#46b450' : '#ffb900'; ?>;">
                <h3>Conclusion</h3>
                <?php if ($check_result['test_passed']): ?>
                    <p style="font-size: 16px;">
                        <span class="dashicons dashicons-yes-alt" style="color: #46b450; font-size: 24px;"></span>
                        <strong>Tout fonctionne correctement!</strong>
                    </p>
                    <p>
                        Les utilisateurs avec une licence <strong>Premium</strong> peuvent maintenant générer 
                        des images PNG et JPG depuis l'éditeur de templates.
                    </p>
                    <p>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=pdf-builder-editor')); ?>" class="button button-primary">
                            Aller à l'éditeur
                        </a>
                    </p>
                <?php else: ?>
                    <p style="font-size: 16px;">
                        <span class="dashicons dashicons-warning" style="color: #ffb900; font-size: 24px;"></span>
                        <strong>Action requise</strong>
                    </p>
                    <p>
                        Veuillez installer wkhtmltoimage en suivant les instructions ci-dessus 
                        pour activer la génération d'images PNG/JPG.
                    </p>
                    <p>
                        Une fois installé, relancez cette vérification pour confirmer que tout fonctionne.
                    </p>
                <?php endif; ?>
            </div>
            
        </div>
        
    <?php else: ?>
        
        <div class="notice notice-info inline" style="margin-top: 20px; padding: 20px;">
            <p style="margin: 0;">
                <span class="dashicons dashicons-info"></span>
                Cliquez sur "Lancer la vérification" pour tester votre installation de wkhtmltoimage.
            </p>
        </div>
        
    <?php endif; ?>
    
</div>


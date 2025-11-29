<?php
/**
 * Page de test pour le système de notifications PDF Builder Pro
 *
 * @package PDF_Builder
 * @since 2.0.0
 */

// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

// Vérifier les permissions
if (!current_user_can('manage_options')) {
    wp_die(__('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'pdf-builder-pro'));
}

$message = '';
$test_type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : '';

// Traiter les tests
if (isset($_POST['test_notification'])) {
    $type = sanitize_text_field($_POST['notification_type']);
    $message = sanitize_text_field($_POST['notification_message']);
    $duration = intval($_POST['notification_duration']);

    if (!empty($message)) {
        // Tester la notification
        PDF_Builder_Notification_Manager::show_toast($message, $type, $duration);
        $test_type = 'success';
    } else {
        $test_type = 'error';
        $message = 'Veuillez entrer un message pour la notification.';
    }
}

?>
<div class="wrap">
    <h1><?php _e('Test du système de notifications PDF Builder Pro', 'pdf-builder-pro'); ?></h1>

    <?php if ($test_type === 'success'): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Test de notification envoyé ! Vérifiez le coin supérieur droit de l\'écran.', 'pdf-builder-pro'); ?></p>
        </div>
    <?php elseif ($test_type === 'error'): ?>
        <div class="notice notice-error is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2><?php _e('Tester une notification toast', 'pdf-builder-pro'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('pdf_builder_test_notifications'); ?>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="notification_type"><?php _e('Type de notification', 'pdf-builder-pro'); ?></label>
                    </th>
                    <td>
                        <select name="notification_type" id="notification_type">
                            <option value="success"><?php _e('Succès', 'pdf-builder-pro'); ?></option>
                            <option value="error"><?php _e('Erreur', 'pdf-builder-pro'); ?></option>
                            <option value="warning"><?php _e('Avertissement', 'pdf-builder-pro'); ?></option>
                            <option value="info"><?php _e('Information', 'pdf-builder-pro'); ?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="notification_message"><?php _e('Message', 'pdf-builder-pro'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="notification_message" id="notification_message" class="regular-text"
                               placeholder="<?php esc_attr_e('Entrez votre message de test', 'pdf-builder-pro'); ?>" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="notification_duration"><?php _e('Durée (ms)', 'pdf-builder-pro'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="notification_duration" id="notification_duration" value="6000" min="1000" max="30000" step="1000">
                        <p class="description"><?php _e('Durée d\'affichage en millisecondes (0 = pas d\'auto-fermeture)', 'pdf-builder-pro'); ?></p>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <input type="submit" name="test_notification" class="button button-primary" value="<?php esc_attr_e('Tester la notification', 'pdf-builder-pro'); ?>">
            </p>
        </form>
    </div>

    <div class="card">
        <h2><?php _e('Tests rapides', 'pdf-builder-pro'); ?></h2>
        <p><?php _e('Cliquez sur les boutons ci-dessous pour tester différents types de notifications :', 'pdf-builder-pro'); ?></p>

        <p>
            <button type="button" class="button" onclick="PDF_Builder_Notification_Manager.show_toast('Ceci est un message de succès !', 'success');">
                <?php _e('Test Succès', 'pdf-builder-pro'); ?>
            </button>
            <button type="button" class="button" onclick="PDF_Builder_Notification_Manager.show_toast('Ceci est un message d\'erreur !', 'error');">
                <?php _e('Test Erreur', 'pdf-builder-pro'); ?>
            </button>
            <button type="button" class="button" onclick="PDF_Builder_Notification_Manager.show_toast('Ceci est un avertissement !', 'warning');">
                <?php _e('Test Avertissement', 'pdf-builder-pro'); ?>
            </button>
            <button type="button" class="button" onclick="PDF_Builder_Notification_Manager.show_toast('Ceci est une information !', 'info');">
                <?php _e('Test Information', 'pdf-builder-pro'); ?>
            </button>
        </p>
    </div>

    <div class="card">
        <h2><?php _e('API JavaScript disponible', 'pdf-builder-pro'); ?></h2>
        <p><?php _e('Vous pouvez utiliser les méthodes suivantes dans votre code JavaScript :', 'pdf-builder-pro'); ?></p>

        <ul>
            <li><code>PDF_Builder_Notification_Manager.show_toast(message, type, duration)</code></li>
            <li><code>pdfBuilderNotifications.show(message, type, duration)</code></li>
            <li><code>pdfBuilderNotifications.success(message, duration)</code></li>
            <li><code>pdfBuilderNotifications.error(message, duration)</code></li>
            <li><code>pdfBuilderNotifications.warning(message, duration)</code></li>
            <li><code>pdfBuilderNotifications.info(message, duration)</code></li>
        </ul>

        <p><strong><?php _e('Types disponibles :', 'pdf-builder-pro'); ?></strong> success, error, warning, info, critical</p>
    </div>

    <div class="card">
        <h2><?php _e('API PHP disponible', 'pdf-builder-pro'); ?></h2>
        <p><?php _e('Vous pouvez utiliser les méthodes suivantes dans votre code PHP :', 'pdf-builder-pro'); ?></p>

        <ul>
            <li><code>PDF_Builder_Notification_Manager::show_toast($message, $type, $duration)</code></li>
            <li><code>PDF_Builder_Notification_Manager::success($message, $style)</code></li>
            <li><code>PDF_Builder_Notification_Manager::error($message, $style)</code></li>
            <li><code>PDF_Builder_Notification_Manager::warning($message, $style)</code></li>
            <li><code>PDF_Builder_Notification_Manager::info($message, $style)</code></li>
        </ul>

        <p><strong><?php _e('Styles disponibles :', 'pdf-builder-pro'); ?></strong> toast, admin_notice, inline</p>
    </div>
</div>

<style>
.card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-top: 20px;
    padding: 20px;
}

.card h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.card p:last-child {
    margin-bottom: 0;
}

.button {
    margin-right: 10px;
    margin-bottom: 10px;
}
</style>
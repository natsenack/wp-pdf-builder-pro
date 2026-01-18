<?php
/**
 * Page d'administration pour la migration des paramètres canvas
 */

// Inclure la classe de migration
require_once dirname(__DIR__) . '/migrate_canvas_settings.php';

$message = '';
$message_type = '';

if (isset($_POST['run_migration']) && check_admin_referer('pdf_builder_migration_nonce')) {
    try {
        if (!class_exists('PDF_Builder_Canvas_Settings_Migration')) {
            throw new Exception('Classe de migration non trouvée');
        }

        $migration = new PDF_Builder_Canvas_Settings_Migration();

        // Exécuter la migration complète
        $migration_result = $migration->migrate();

        // Récupérer les paramètres existants depuis wp_options
        $existing_settings = get_option('pdf_builder_settings', array());

        if (empty($existing_settings)) {
            $message = 'Aucun paramètre existant trouvé dans wp_options';
            $message_type = 'warning';
        } else {
            // Filtrer seulement les paramètres canvas
            $canvas_settings = array();
            foreach ($existing_settings as $key => $value) {
                if (strpos($key, 'pdf_builder_canvas_') === 0) {
                    $canvas_settings[$key] = $value;
                }
            }

            if (empty($canvas_settings)) {
                $message = 'Aucun paramètre canvas trouvé dans wp_options';
                $message_type = 'warning';
            } else {
                // Migrer les paramètres vers la nouvelle table
                $migrated_count = $migration->set_canvas_settings($canvas_settings);

                if ($migrated_count > 0) {
                    $message = "Migration réussie: $migrated_count paramètres déplacés vers la table dédiée";
                    $message_type = 'success';
                } else {
                    $message = 'Erreur lors de la migration';
                    $message_type = 'error';
                }
            }
        }

    } catch (Exception $e) {
        $message = 'Erreur: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Vérifier l'état actuel
$current_canvas_settings = array();
if (class_exists('PDF_Builder_Canvas_Settings_Migration')) {
    $migration = new PDF_Builder_Canvas_Settings_Migration();
    $current_canvas_settings = $migration->get_all_canvas_settings();
}

$existing_wp_options = get_option('pdf_builder_settings', array());
$canvas_in_options = array();
foreach ($existing_wp_options as $key => $value) {
    if (strpos($key, 'pdf_builder_canvas_') === 0) {
        $canvas_in_options[$key] = $value;
    }
}
?>

<div class="wrap">
    <h1><?php _e('Migration des Paramètres Canvas', 'pdf-builder-pro'); ?></h1>

    <?php if (!empty($message)): ?>
        <div class="notice notice-<?php echo esc_attr($message_type); ?> is-dismissible">
            <p><?php echo esc_html($message); ?></p>
        </div>
    <?php endif; ?>

    <div class="card">
        <h2><?php _e('Aperçu de la Migration', 'pdf-builder-pro'); ?></h2>
        <p><?php _e('Cette page vous permet de migrer les paramètres canvas depuis wp_options vers une table dédiée (wp_pdf_builder_settings) pour une meilleure organisation.', 'pdf-builder-pro'); ?></p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
            <div>
                <h3><?php _e('Paramètres dans wp_options', 'pdf-builder-pro'); ?></h3>
                <div style="background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;">
                    <?php if (empty($canvas_in_options)): ?>
                        <p><em><?php _e('Aucun paramètre canvas trouvé', 'pdf-builder-pro'); ?></em></p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($canvas_in_options as $key => $value): ?>
                                <li><code><?php echo esc_html($key); ?></code>: <?php echo esc_html(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : ''); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <h3><?php _e('Paramètres dans la nouvelle table', 'pdf-builder-pro'); ?></h3>
                <div style="background: #f5f5f5; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;">
                    <?php if (empty($current_canvas_settings)): ?>
                        <p><em><?php _e('Aucun paramètre dans la table dédiée', 'pdf-builder-pro'); ?></em></p>
                    <?php else: ?>
                        <ul>
                            <?php foreach ($current_canvas_settings as $setting): ?>
                                <li><code><?php echo esc_html($setting->setting_key); ?></code>: <?php echo esc_html(substr($setting->setting_value, 0, 50)) . (strlen($setting->setting_value) > 50 ? '...' : ''); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <form method="post" style="margin-top: 20px;">
            <?php wp_nonce_field('pdf_builder_migration_nonce'); ?>
            <input type="hidden" name="run_migration" value="1">
            <p>
                <input type="submit" class="button button-primary" value="<?php _e('Exécuter la Migration', 'pdf-builder-pro'); ?>">
            </p>
            <p class="description">
                <?php _e('Cette action déplacera tous les paramètres canvas de wp_options vers la table dédiée. Les données originales resteront intactes pour sécurité.', 'pdf-builder-pro'); ?>
            </p>
        </form>
    </div>
</div>
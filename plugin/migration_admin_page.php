<?php
/**
 * Page admin pour la migration des paramètres canvas
 */

if (!defined('ABSPATH')) {
    die('Accès direct non autorisé');
}

// Inclure le handler AJAX
require_once plugin_dir_path(__FILE__) . 'migrate_canvas_settings_ajax.php';

// Ajouter la page admin
add_action('admin_menu', 'pdf_builder_add_migration_page');

function pdf_builder_add_migration_page() {
    add_submenu_page(
        'pdf-builder-settings',
        'Migration Paramètres Canvas',
        'Migration Canvas',
        'manage_options',
        'pdf-builder-migration',
        'pdf_builder_migration_page'
    );
}

function pdf_builder_migration_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('Vous n\'avez pas les permissions suffisantes pour accéder à cette page.'));
    }

    ?>
    <div class="wrap">
        <h1>Migration des Paramètres Canvas</h1>

        <div class="notice notice-info">
            <p>Cette page permet de migrer les paramètres canvas vers une table dédiée pour une meilleure organisation.</p>
        </div>

        <div id="migration-status" style="display: none;">
            <div class="notice notice-success" id="migration-success">
                <p><strong>Migration réussie!</strong></p>
                <div id="migration-details"></div>
            </div>

            <div class="notice notice-error" id="migration-error">
                <p><strong>Erreur lors de la migration:</strong></p>
                <div id="migration-error-details"></div>
            </div>
        </div>

        <div id="migration-form">
            <p>
                <button type="button" id="run-migration" class="button button-primary">
                    Exécuter la Migration
                </button>
            </p>

            <div id="migration-progress" style="display: none;">
                <p>Migration en cours...</p>
                <div class="spinner is-active" style="float: none;"></div>
            </div>
        </div>

        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#run-migration').on('click', function() {
                var $button = $(this);
                var $progress = $('#migration-progress');
                var $status = $('#migration-status');
                var $form = $('#migration-form');

                // Désactiver le bouton et afficher le progrès
                $button.prop('disabled', true);
                $progress.show();

                // Exécuter la migration AJAX
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'pdf_builder_migrate_canvas_settings',
                        nonce: '<?php echo wp_create_nonce("pdf_builder_migrate_canvas_settings"); ?>'
                    },
                    success: function(response) {
                        $progress.hide();
                        $status.show();

                        if (response.success) {
                            $('#migration-success').show();
                            $('#migration-error').hide();

                            var details = '<ul>';
                            if (response.details.pending_migrations) {
                                details += '<li>Migrations en attente: ' + response.details.pending_migrations.join(', ') + '</li>';
                            }
                            if (response.details.table_created) {
                                details += '<li>Table créée: Oui</li>';
                            }
                            if (response.details.canvas_settings_migrated !== undefined) {
                                details += '<li>Paramètres canvas migrés: ' + response.details.canvas_settings_migrated + '</li>';
                            }
                            details += '<li>Version DB: ' + response.details.current_db_version + '</li>';
                            details += '<li>Table existe: ' + (response.details.table_exists ? 'Oui' : 'Non') + '</li>';
                            if (response.details.canvas_settings_count !== undefined) {
                                details += '<li>Paramètres canvas dans la table: ' + response.details.canvas_settings_count + '</li>';
                            }
                            details += '</ul>';

                            $('#migration-details').html(details);
                        } else {
                            $('#migration-success').hide();
                            $('#migration-error').show();
                            $('#migration-error-details').html('<p>' + response.message + '</p>');
                        }
                    },
                    error: function(xhr, status, error) {
                        $progress.hide();
                        $status.show();
                        $('#migration-success').hide();
                        $('#migration-error').show();
                        $('#migration-error-details').html('<p>Erreur AJAX: ' + error + '</p>');
                    }
                });
            });
        });
        </script>
    </div>
    <?php
}
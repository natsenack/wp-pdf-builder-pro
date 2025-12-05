<?php
if (!defined('ABSPATH')) exit('No direct access');

// Include the cron diagnostic interface
require_once PDF_BUILDER_PLUGIN_DIR . 'src/Core/PDF_Builder_Task_Scheduler.php';

$task_scheduler = PDF_Builder_Task_Scheduler::get_instance();
?>

<div class="pdf-builder-cron-diagnostics">
    <h2><?php _e('Cron System Diagnostics', 'pdf-builder-pro'); ?></h2>

    <div class="cron-status-section">
        <h3><?php _e('System Status', 'pdf-builder-pro'); ?></h3>
        <div id="cron-status-display">
            <p><?php _e('Click "Diagnose Cron System" to check the current status.', 'pdf-builder-pro'); ?></p>
        </div>
    </div>

    <div class="cron-actions-section">
        <h3><?php _e('Actions', 'pdf-builder-pro'); ?></h3>
        <button type="button" id="diagnose-cron-btn" class="button button-secondary">
            <?php _e('Diagnose Cron System', 'pdf-builder-pro'); ?>
        </button>
        <button type="button" id="repair-cron-btn" class="button button-primary">
            <?php _e('Repair Cron System', 'pdf-builder-pro'); ?>
        </button>
        <button type="button" id="backup-stats-btn" class="button button-secondary">
            <?php _e('View Backup Statistics', 'pdf-builder-pro'); ?>
        </button>
        <button type="button" id="manual-backup-btn" class="button button-secondary">
            <?php _e('Create Manual Backup', 'pdf-builder-pro'); ?>
        </button>
    </div>

    <div class="cron-results-section" id="cron-results" style="display: none;">
        <h3><?php _e('Results', 'pdf-builder-pro'); ?></h3>
        <div id="cron-results-content"></div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log('PDF Builder: Cron diagnostics script loaded');
    console.log('ajaxurl:', typeof ajaxurl !== 'undefined' ? ajaxurl : 'NOT DEFINED');

    // Diagnose cron system
    $('#diagnose-cron-btn').on('click', function() {
        console.log('PDF Builder: Diagnose button clicked');
        $(this).prop('disabled', true).text('<?php _e('Diagnosing...', 'pdf-builder-pro'); ?>');

        var ajaxData = {
            action: 'pdf_builder_diagnose_cron',
            nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
        };
        console.log('PDF Builder: Sending AJAX data:', ajaxData);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                console.log('PDF Builder: AJAX success response:', response);
                $('#diagnose-cron-btn').prop('disabled', false).text('<?php _e('Diagnose Cron System', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    $('#cron-status-display').html('<pre>' + response.data.status + '</pre>');
                    $('#cron-results').show();
                    $('#cron-results-content').html('<pre>' + response.data.details + '</pre>');
                } else {
                    alert('<?php _e('Error diagnosing cron system:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.log('PDF Builder: AJAX error - xhr:', xhr);
                console.log('PDF Builder: AJAX error - status:', status);
                console.log('PDF Builder: AJAX error - error:', error);
                $('#diagnose-cron-btn').prop('disabled', false).text('<?php _e('Diagnose Cron System', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // Repair cron system
    $('#repair-cron-btn').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to repair the cron system? This may restart scheduled tasks.', 'pdf-builder-pro'); ?>')) {
            return;
        }

        $(this).prop('disabled', true).text('<?php _e('Repairing...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_repair_cron',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                $('#repair-cron-btn').prop('disabled', false).text('<?php _e('Repair Cron System', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    alert('<?php _e('Cron system repaired successfully!', 'pdf-builder-pro'); ?>');
                    $('#diagnose-cron-btn').click(); // Refresh status
                } else {
                    alert('<?php _e('Error repairing cron system:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function() {
                $('#repair-cron-btn').prop('disabled', false).text('<?php _e('Repair Cron System', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // View backup statistics
    $('#backup-stats-btn').on('click', function() {
        $(this).prop('disabled', true).text('<?php _e('Loading...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_get_backup_stats',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                $('#backup-stats-btn').prop('disabled', false).text('<?php _e('View Backup Statistics', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    $('#cron-results').show();
                    $('#cron-results-content').html('<pre>' + response.data + '</pre>');
                } else {
                    alert('<?php _e('Error loading backup statistics:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function() {
                $('#backup-stats-btn').prop('disabled', false).text('<?php _e('View Backup Statistics', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // Create manual backup
    $('#manual-backup-btn').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to create a manual backup now?', 'pdf-builder-pro'); ?>')) {
            return;
        }

        $(this).prop('disabled', true).text('<?php _e('Creating Backup...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_backup',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                $('#manual-backup-btn').prop('disabled', false).text('<?php _e('Create Manual Backup', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    alert('<?php _e('Manual backup created successfully!', 'pdf-builder-pro'); ?>');
                    $('#backup-stats-btn').click(); // Refresh stats
                } else {
                    alert('<?php _e('Error creating manual backup:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function() {
                $('#manual-backup-btn').prop('disabled', false).text('<?php _e('Create Manual Backup', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });
});
</script>
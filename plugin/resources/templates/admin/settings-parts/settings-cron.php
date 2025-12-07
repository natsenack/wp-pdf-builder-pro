<?php
if (!defined('ABSPATH')) exit('No direct access');

// Task Scheduler is already loaded in bootstrap.php
$task_scheduler = PDF_Builder_Task_Scheduler::get_instance();
?>

<div class="pdf-builder-cron-diagnostics">
    <h2><?php _e('Cron System Diagnostics', 'pdf-builder-pro'); ?></h2>

    <!-- WP Cron Status Indicator -->
    <div class="wp-cron-status-indicator" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #495057;">
            <span class="dashicons dashicons-clock" style="vertical-align: middle; margin-right: 8px;"></span>
            <?php _e('WP Cron Status', 'pdf-builder-pro'); ?>
        </h3>
        <div id="wp-cron-status-content">
            <div class="wp-cron-status-item" style="display: flex; align-items: center; margin-bottom: 8px;">
                <span class="status-indicator" id="wp-cron-enabled-indicator" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 10px; background: #ffc107;"></span>
                <span id="wp-cron-enabled-text"><?php _e('Checking WP Cron status...', 'pdf-builder-pro'); ?></span>
            </div>
            <div class="wp-cron-status-item" style="display: flex; align-items: center; margin-bottom: 8px;">
                <span class="status-indicator" id="wp-cron-scheduled-indicator" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 10px; background: #ffc107;"></span>
                <span id="wp-cron-scheduled-text"><?php _e('Checking scheduled tasks...', 'pdf-builder-pro'); ?></span>
            </div>
            <div class="wp-cron-status-item" style="display: flex; align-items: center;">
                <span class="status-indicator" id="wp-cron-response-indicator" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 10px; background: #ffc107;"></span>
                <span id="wp-cron-response-text"><?php _e('Testing cron response...', 'pdf-builder-pro'); ?></span>
            </div>
        </div>
        <div style="margin-top: 10px;">
            <button type="button" id="check-wp-cron-status-btn" class="button button-small">
                <span class="dashicons dashicons-update" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php _e('Refresh Status', 'pdf-builder-pro'); ?>
            </button>
        </div>
    </div>

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

    <div class="cron-results-section" id="cron-results" class="hidden-element">
        <h3><?php _e('Results', 'pdf-builder-pro'); ?></h3>
        <div id="cron-results-content"></div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    
    
    .toISOString());

    // Diagnose cron system
    $('#diagnose-cron-btn').on('click', function() {
        .toISOString());
        $(this).prop('disabled', true).text('<?php _e('Diagnosing...', 'pdf-builder-pro'); ?>');

        var ajaxData = {
            action: 'pdf_builder_diagnose_cron',
            nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
        };
        

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                
                
                if (response.success) {
                    
                    $('#cron-status-display').html('<pre>' + JSON.stringify(response.data, null, 2) + '</pre>');
                    $('#cron-results').show();
                    $('#cron-results-content').html('<pre>Diagnostics completed successfully</pre>');
                } else {
                    // console.error('PDF Builder: [CRON] Error diagnosing cron system:', response.data);
                    alert('<?php _e('Error diagnosing cron system:', 'pdf-builder-pro'); ?> ' + response.data);
                }
                $('#diagnose-cron-btn').prop('disabled', false).text('<?php _e('Diagnose Cron System', 'pdf-builder-pro'); ?>');
            },
            error: function(xhr, status, error) {
                // console.error('PDF Builder: [CRON] AJAX error - xhr:', xhr);
                // console.error('PDF Builder: [CRON] AJAX error - status:', status);
                // console.error('PDF Builder: [CRON] AJAX error - error:', error);
                // console.error('PDF Builder: [CRON] AJAX error - response text:', xhr.responseText);
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

        .toISOString());
        $(this).prop('disabled', true).text('<?php _e('Repairing...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_repair_cron',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                
                $('#repair-cron-btn').prop('disabled', false).text('<?php _e('Repair Cron System', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    
                    alert('<?php _e('Cron system repaired successfully!', 'pdf-builder-pro'); ?>');
                    $('#diagnose-cron-btn').click(); // Refresh status
                } else {
                    // console.error('PDF Builder: [CRON] Error repairing cron system:', response.data);
                    alert('<?php _e('Error repairing cron system:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.error('PDF Builder: [CRON] Repair AJAX error:', {xhr: xhr, status: status, error: error});
                $('#repair-cron-btn').prop('disabled', false).text('<?php _e('Repair Cron System', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // View backup statistics
    $('#backup-stats-btn').on('click', function() {
        .toISOString());
        $(this).prop('disabled', true).text('<?php _e('Loading...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_get_backup_stats',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                
                $('#backup-stats-btn').prop('disabled', false).text('<?php _e('View Backup Statistics', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    
                    $('#cron-results').show();
                    $('#cron-results-content').html('<pre>' + response.data + '</pre>');
                } else {
                    // console.error('PDF Builder: [BACKUP] Error loading backup statistics:', response.data);
                    alert('<?php _e('Error loading backup statistics:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.error('PDF Builder: [BACKUP] Statistics AJAX error:', {xhr: xhr, status: status, error: error});
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

        .toISOString());
        $(this).prop('disabled', true).text('<?php _e('Creating Backup...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_backup',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                
                $('#manual-backup-btn').prop('disabled', false).text('<?php _e('Create Manual Backup', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    
                    alert('<?php _e('Manual backup created successfully!', 'pdf-builder-pro'); ?>');
                    $('#backup-stats-btn').click(); // Refresh stats
                } else {
                    // console.error('PDF Builder: [BACKUP] Error creating manual backup:', response.data);
                    alert('<?php _e('Error creating manual backup:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.error('PDF Builder: [BACKUP] Manual backup AJAX error:', {xhr: xhr, status: status, error: error});
                $('#manual-backup-btn').prop('disabled', false).text('<?php _e('Create Manual Backup', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // WP Cron Status Check
    function updateWpCronStatusIndicator(elementId, status, text) {
        var indicator = $('#' + elementId + '-indicator');
        var textElement = $('#' + elementId + '-text');

        // Reset classes
        indicator.removeClass('status-good status-warning status-error');

        // Set status
        if (status === 'good') {
            indicator.addClass('status-good').css('background', '#28a745');
        } else if (status === 'warning') {
            indicator.addClass('status-warning').css('background', '#ffc107');
        } else if (status === 'error') {
            indicator.addClass('status-error').css('background', '#dc3545');
        }

        textElement.text(text);
    }

    function checkWpCronStatus() {
        

        // Check if WP Cron is enabled (PHP side check)
        updateWpCronStatusIndicator('wp-cron-enabled', 'warning', '<?php _e('Checking WP Cron configuration...', 'pdf-builder-pro'); ?>');
        updateWpCronStatusIndicator('wp-cron-scheduled', 'warning', '<?php _e('Checking scheduled tasks...', 'pdf-builder-pro'); ?>');
        updateWpCronStatusIndicator('wp-cron-response', 'warning', '<?php _e('Testing cron response...', 'pdf-builder-pro'); ?>');

        // First check: WP Cron configuration
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_check_wp_cron_config',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                
                if (response.success) {
                    if (response.data.cron_disabled) {
                        updateWpCronStatusIndicator('wp-cron-enabled', 'error', '<?php _e('WP Cron is DISABLED (DISABLE_WP_CRON = true)', 'pdf-builder-pro'); ?>');
                    } else {
                        updateWpCronStatusIndicator('wp-cron-enabled', 'good', '<?php _e('WP Cron is ENABLED', 'pdf-builder-pro'); ?>');
                    }
                } else {
                    updateWpCronStatusIndicator('wp-cron-enabled', 'error', '<?php _e('Cannot check WP Cron configuration', 'pdf-builder-pro'); ?>');
                }
            },
            error: function() {
                updateWpCronStatusIndicator('wp-cron-enabled', 'error', '<?php _e('Error checking WP Cron configuration', 'pdf-builder-pro'); ?>');
            }
        });

        // Second check: Scheduled tasks
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_check_scheduled_tasks',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                
                if (response.success) {
                    var taskCount = response.data.scheduled_tasks ? response.data.scheduled_tasks.length : 0;
                    if (taskCount > 0) {
                        updateWpCronStatusIndicator('wp-cron-scheduled', 'good', '<?php _e('Scheduled tasks active (', 'pdf-builder-pro'); ?>' + taskCount + '<?php _e(' tasks)', 'pdf-builder-pro'); ?>');
                    } else {
                        updateWpCronStatusIndicator('wp-cron-scheduled', 'warning', '<?php _e('No scheduled tasks found', 'pdf-builder-pro'); ?>');
                    }
                } else {
                    updateWpCronStatusIndicator('wp-cron-scheduled', 'error', '<?php _e('Cannot check scheduled tasks', 'pdf-builder-pro'); ?>');
                }
            },
            error: function() {
                updateWpCronStatusIndicator('wp-cron-scheduled', 'error', '<?php _e('Error checking scheduled tasks', 'pdf-builder-pro'); ?>');
            }
        });

        // Third check: Cron response test
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php?action=pdf_builder_cron_test&nonce=' . wp_create_nonce('pdf_builder_cron_test')); ?>',
            type: 'GET',
            timeout: 10000, // 10 second timeout
            success: function(response) {
                
                if (response && response.success) {
                    updateWpCronStatusIndicator('wp-cron-response', 'good', '<?php _e('Cron system responding correctly', 'pdf-builder-pro'); ?>');
                } else {
                    updateWpCronStatusIndicator('wp-cron-response', 'warning', '<?php _e('Cron system responding but with issues', 'pdf-builder-pro'); ?>');
                }
            },
            error: function(xhr, status) {
                
                if (status === 'timeout') {
                    updateWpCronStatusIndicator('wp-cron-response', 'warning', '<?php _e('Cron response slow (timeout)', 'pdf-builder-pro'); ?>');
                } else {
                    updateWpCronStatusIndicator('wp-cron-response', 'error', '<?php _e('Cron system not responding', 'pdf-builder-pro'); ?>');
                }
            }
        });
    }

    // Bind refresh button
    $('#check-wp-cron-status-btn').on('click', function() {
        $(this).prop('disabled', true).html('<span class="dashicons dashicons-update spin" style="vertical-align: middle; margin-right: 5px;"></span><?php _e('Checking...', 'pdf-builder-pro'); ?>');
        checkWpCronStatus();
        setTimeout(function() {
            $('#check-wp-cron-status-btn').prop('disabled', false).html('<span class="dashicons dashicons-update" style="vertical-align: middle; margin-right: 5px;"></span><?php _e('Refresh Status', 'pdf-builder-pro'); ?>');
        }, 2000);
    });

    // Auto-refresh logs for cron monitoring
    
    

    // Initialize WP Cron status check on page load
    checkWpCronStatus();
});
</script>

<style>
/* WP Cron Status Indicator Styles */
.status-indicator {
    transition: background-color 0.3s ease;
}

.dashicons.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.wp-cron-status-indicator .status-good {
    background-color: #28a745 !important;
}

.wp-cron-status-indicator .status-warning {
    background-color: #ffc107 !important;
}

.wp-cron-status-indicator .status-error {
    background-color: #dc3545 !important;
}
</style>
</script>


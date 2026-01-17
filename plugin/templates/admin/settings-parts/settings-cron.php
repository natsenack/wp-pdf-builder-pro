<?php
if (!defined('ABSPATH')) exit('No direct access');

// Task Scheduler is already loaded in bootstrap.php
$task_scheduler = PDF_Builder_Task_Scheduler::get_instance();
?>

<div class="pdf-builder-cron-diagnostics">
    <h2><?php echo __('Cron System Diagnostics', 'pdf-builder-pro'); ?></h2>

    <!-- WP Cron Status Indicator -->
    <div class="wp-cron-status-indicator" style="margin-bottom: 20px; padding: 15px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px;">
        <h3 style="margin-top: 0; color: #495057;">
            <span class="dashicons dashicons-clock" style="vertical-align: middle; margin-right: 8px;"></span>
            <?php echo __('WP Cron Status', 'pdf-builder-pro'); ?>
        </h3>
        <div id="wp-cron-status-content">
            <div class="wp-cron-status-item" style="display: flex; align-items: center; margin-bottom: 8px;">
                <span class="status-indicator" id="wp-cron-enabled-indicator" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 10px; background: #ffc107;"></span>
                <span id="wp-cron-enabled-text"><?php echo __('Checking WP Cron status...', 'pdf-builder-pro'); ?></span>
            </div>
            <div class="wp-cron-status-item" style="display: flex; align-items: center; margin-bottom: 8px;">
                <span class="status-indicator" id="wp-cron-scheduled-indicator" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 10px; background: #ffc107;"></span>
                <span id="wp-cron-scheduled-text"><?php echo __('Checking scheduled tasks...', 'pdf-builder-pro'); ?></span>
            </div>
            <div class="wp-cron-status-item" style="display: flex; align-items: center;">
                <span class="status-indicator" id="wp-cron-response-indicator" style="display: inline-block; width: 12px; height: 12px; border-radius: 50%; margin-right: 10px; background: #ffc107;"></span>
                <span id="wp-cron-response-text"><?php echo __('Testing cron response...', 'pdf-builder-pro'); ?></span>
            </div>
        </div>
        <div style="margin-top: 10px;">
            <button type="button" id="check-wp-cron-status-btn" class="button button-small">
                <span class="dashicons dashicons-update" style="vertical-align: middle; margin-right: 5px;"></span>
                <?php echo __('Refresh Status', 'pdf-builder-pro'); ?>
            </button>
        </div>
    </div>

    <div class="cron-status-section">
        <h3><?php echo __('System Status', 'pdf-builder-pro'); ?></h3>
        <div id="cron-status-display">
            <p><?php echo __('Click "Diagnose Cron System" to check the current status.', 'pdf-builder-pro'); ?></p>
        </div>
    </div>

    <div class="cron-actions-section">
        <h3><?php echo __('Actions', 'pdf-builder-pro'); ?></h3>
        <button type="button" id="diagnose-cron-btn" class="button button-secondary">
            <?php echo __('Diagnose Cron System', 'pdf-builder-pro'); ?>
        </button>
        <button type="button" id="repair-cron-btn" class="button button-primary">
            <?php echo __('Repair Cron System', 'pdf-builder-pro'); ?>
        </button>
        <button type="button" id="backup-stats-btn" class="button button-secondary">
            <?php echo __('View Backup Statistics', 'pdf-builder-pro'); ?>
        </button>
        <button type="button" id="manual-backup-btn" class="button button-secondary">
            <?php echo __('Create Manual Backup', 'pdf-builder-pro'); ?>
        </button>
    </div>

    <div class="cron-results-section" id="cron-results" class="hidden-element">
        <h3><?php echo __('Results', 'pdf-builder-pro'); ?></h3>
        <div id="cron-results-content"></div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {

    // Translation strings
    var i18n = {
        diagnosing: '<?php echo esc_js(__('Diagnosing...', 'pdf-builder-pro')); ?>',
        repairing: '<?php echo esc_js(__('Repairing...', 'pdf-builder-pro')); ?>',
        loading: '<?php echo esc_js(__('Loading...', 'pdf-builder-pro')); ?>',
        creatingBackup: '<?php echo esc_js(__('Creating Backup...', 'pdf-builder-pro')); ?>',
        diagnoseCronSystem: '<?php echo esc_js(__('Diagnose Cron System', 'pdf-builder-pro')); ?>',
        repairCronSystem: '<?php echo esc_js(__('Repair Cron System', 'pdf-builder-pro')); ?>',
        viewBackupStatistics: '<?php echo esc_js(__('View Backup Statistics', 'pdf-builder-pro')); ?>',
        createManualBackup: '<?php echo esc_js(__('Create Manual Backup', 'pdf-builder-pro')); ?>',
        ajaxErrorOccurred: '<?php echo esc_js(__('AJAX error occurred', 'pdf-builder-pro')); ?>',
        errorDiagnosingCronSystem: '<?php echo esc_js(__('Error diagnosing cron system:', 'pdf-builder-pro')); ?>',
        cronSystemRepairedSuccessfully: '<?php echo esc_js(__('Cron system repaired successfully!', 'pdf-builder-pro')); ?>',
        errorRepairingCronSystem: '<?php echo esc_js(__('Error repairing cron system:', 'pdf-builder-pro')); ?>',
        errorLoadingBackupStatistics: '<?php echo esc_js(__('Error loading backup statistics:', 'pdf-builder-pro')); ?>',
        manualBackupCreatedSuccessfully: '<?php echo esc_js(__('Manual backup created successfully!', 'pdf-builder-pro')); ?>',
        errorCreatingManualBackup: '<?php echo esc_js(__('Error creating manual backup:', 'pdf-builder-pro')); ?>',
        areYouSureRepairCron: '<?php echo esc_js(__('Are you sure you want to repair the cron system? This may restart scheduled tasks.', 'pdf-builder-pro')); ?>',
        areYouSureCreateBackup: '<?php echo esc_js(__('Are you sure you want to create a manual backup now?', 'pdf-builder-pro')); ?>',
        checkingWpCronConfiguration: '<?php echo esc_js(__('Checking WP Cron configuration...', 'pdf-builder-pro')); ?>',
        checkingScheduledTasks: '<?php echo esc_js(__('Checking scheduled tasks...', 'pdf-builder-pro')); ?>',
        testingCronResponse: '<?php echo esc_js(__('Testing cron response...', 'pdf-builder-pro')); ?>',
        wpCronDisabled: '<?php echo esc_js(__('WP Cron is DISABLED (DISABLE_WP_CRON = true)', 'pdf-builder-pro')); ?>',
        wpCronEnabled: '<?php echo esc_js(__('WP Cron is ENABLED', 'pdf-builder-pro')); ?>',
        cannotCheckWpCronConfiguration: '<?php echo esc_js(__('Cannot check WP Cron configuration', 'pdf-builder-pro')); ?>',
        errorCheckingWpCronConfiguration: '<?php echo esc_js(__('Error checking WP Cron configuration', 'pdf-builder-pro')); ?>',
        errorCheckingWpCronConfiguration: '<?php echo esc_js(__('Error checking WP Cron configuration', 'pdf-builder-pro')); ?>',
        scheduledTasksActive: '<?php echo esc_js(__('Scheduled tasks active (', 'pdf-builder-pro')); ?>',
        tasks: '<?php echo esc_js(__(' tasks)', 'pdf-builder-pro')); ?>',
        noScheduledTasksFound: '<?php echo esc_js(__('No scheduled tasks found', 'pdf-builder-pro')); ?>',
        cannotCheckScheduledTasks: '<?php echo esc_js(__('Cannot check scheduled tasks', 'pdf-builder-pro')); ?>',
        errorCheckingScheduledTasks: '<?php echo esc_js(__('Error checking scheduled tasks', 'pdf-builder-pro')); ?>',
        cronSystemRespondingCorrectly: '<?php echo esc_js(__('Cron system responding correctly', 'pdf-builder-pro')); ?>',
        cronSystemRespondingWithIssues: '<?php echo esc_js(__('Cron system responding but with issues', 'pdf-builder-pro')); ?>',
        cronResponseSlow: '<?php echo esc_js(__('Cron response slow (timeout)', 'pdf-builder-pro')); ?>',
        cronSystemNotResponding: '<?php echo esc_js(__('Cron system not responding', 'pdf-builder-pro')); ?>',
        checking: '<?php echo esc_js(__('Checking...', 'pdf-builder-pro')); ?>',
        refreshStatus: '<?php echo esc_js(__('Refresh Status', 'pdf-builder-pro')); ?>'
    };


    // Diagnose cron system
    $('#diagnose-cron-btn').on('click', function() {
        $(this).prop('disabled', true).text(i18n.diagnosing);
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
                    alert(i18n.errorDiagnosingCronSystem + ' ' + response.data);
                }
                $('#diagnose-cron-btn').prop('disabled', false).text(i18n.diagnoseCronSystem);
            },
            error: function(xhr, status, error) {
                // console.error('PDF Builder: [CRON] AJAX error - xhr:', xhr);
                // console.error('PDF Builder: [CRON] AJAX error - status:', status);
                // console.error('PDF Builder: [CRON] AJAX error - error:', error);
                // console.error('PDF Builder: [CRON] AJAX error - response text:', xhr.responseText);
                $('#diagnose-cron-btn').prop('disabled', false).text(i18n.diagnoseCronSystem);
                alert(i18n.ajaxErrorOccurred);
            }
        });
    });

    // Repair cron system
    $('#repair-cron-btn').on('click', function() {
        if (!confirm(i18n.areYouSureRepairCron)) {

            return;
        }

        $(this).prop('disabled', true).text(i18n.repairing);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_repair_cron',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                
                $('#repair-cron-btn').prop('disabled', false).text(i18n.repairCronSystem);
                if (response.success) {
                    
                    alert(i18n.cronSystemRepairedSuccessfully);
                    $('#diagnose-cron-btn').click(); // Refresh status
                } else {
                    // console.error('PDF Builder: [CRON] Error repairing cron system:', response.data);
                    alert(i18n.errorRepairingCronSystem + ' ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.error('PDF Builder: [CRON] Repair AJAX error:', {xhr: xhr, status: status, error: error});
                $('#repair-cron-btn').prop('disabled', false).text(i18n.repairCronSystem);
                alert(i18n.ajaxErrorOccurred);
            }
        });
    });

    // View backup statistics
    $('#backup-stats-btn').on('click', function() {
        $(this).prop('disabled', true).text(i18n.loading);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_get_backup_stats',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                
                $('#backup-stats-btn').prop('disabled', false).text(i18n.viewBackupStatistics);
                if (response.success) {
                    
                    $('#cron-results').show();
                    $('#cron-results-content').html('<pre>' + response.data + '</pre>');
                } else {
                    // console.error('PDF Builder: [BACKUP] Error loading backup statistics:', response.data);
                    alert(i18n.errorLoadingBackupStatistics + ' ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.error('PDF Builder: [BACKUP] Statistics AJAX error:', {xhr: xhr, status: status, error: error});
                $('#backup-stats-btn').prop('disabled', false).text(i18n.viewBackupStatistics);
                alert(i18n.ajaxErrorOccurred);
            }
        });
    });

    // Create manual backup
    $('#manual-backup-btn').on('click', function() {
        if (!confirm(i18n.areYouSureCreateBackup)) {

            return;
        }

        $(this).prop('disabled', true).text(i18n.creatingBackup);
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_backup',
                nonce: '<?php echo wp_create_nonce('pdf_builder_ajax'); ?>'
            },
            success: function(response) {
                
                $('#manual-backup-btn').prop('disabled', false).text(i18n.createManualBackup);
                if (response.success) {
                    
                    alert(i18n.manualBackupCreatedSuccessfully);
                    $('#backup-stats-btn').click(); // Refresh stats
                } else {
                    // console.error('PDF Builder: [BACKUP] Error creating manual backup:', response.data);
                    alert(i18n.errorCreatingManualBackup + ' ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                // console.error('PDF Builder: [BACKUP] Manual backup AJAX error:', {xhr: xhr, status: status, error: error});
                $('#manual-backup-btn').prop('disabled', false).text(i18n.createManualBackup);
                alert(i18n.ajaxErrorOccurred);
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
        updateWpCronStatusIndicator('wp-cron-enabled', 'warning', i18n.checkingWpCronConfiguration);
        updateWpCronStatusIndicator('wp-cron-scheduled', 'warning', i18n.checkingScheduledTasks);
        updateWpCronStatusIndicator('wp-cron-response', 'warning', i18n.testingCronResponse);

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
                        updateWpCronStatusIndicator('wp-cron-enabled', 'error', i18n.wpCronDisabled);
                    } else {
                        updateWpCronStatusIndicator('wp-cron-enabled', 'good', i18n.wpCronEnabled);
                    }
                } else {
                    updateWpCronStatusIndicator('wp-cron-enabled', 'error', i18n.cannotCheckWpCronConfiguration);
                }
            },
            error: function() {
                updateWpCronStatusIndicator('wp-cron-enabled', 'error', i18n.errorCheckingWpCronConfiguration);
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
                        updateWpCronStatusIndicator('wp-cron-scheduled', 'good', i18n.scheduledTasksActive + taskCount + i18n.tasks);
                    } else {
                        updateWpCronStatusIndicator('wp-cron-scheduled', 'warning', i18n.noScheduledTasksFound);
                    }
                } else {
                    updateWpCronStatusIndicator('wp-cron-scheduled', 'error', i18n.cannotCheckScheduledTasks);
                }
            },
            error: function() {
                updateWpCronStatusIndicator('wp-cron-scheduled', 'error', i18n.errorCheckingScheduledTasks);
            }
        });

        // Third check: Cron response test
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php?action=pdf_builder_cron_test&nonce=' . wp_create_nonce('pdf_builder_cron_test')); ?>',
            type: 'GET',
            timeout: 10000, // 10 second timeout
            success: function(response) {
                
                if (response && response.success) {
                    updateWpCronStatusIndicator('wp-cron-response', 'good', i18n.cronSystemRespondingCorrectly);
                } else {
                    updateWpCronStatusIndicator('wp-cron-response', 'warning', i18n.cronSystemRespondingWithIssues);
                }
            },
            error: function(xhr, status) {
                
                if (status === 'timeout') {
                    updateWpCronStatusIndicator('wp-cron-response', 'warning', i18n.cronResponseSlow);
                } else {
                    updateWpCronStatusIndicator('wp-cron-response', 'error', i18n.cronSystemNotResponding);
                }
            }
        });
    }

    // Bind refresh button
    $('#check-wp-cron-status-btn').on('click', function() {
        $(this).prop('disabled', true).html('<span class="dashicons dashicons-update spin" style="vertical-align: middle; margin-right: 5px;"></span>' + i18n.checking);
        checkWpCronStatus();
        setTimeout(function() {
            $('#check-wp-cron-status-btn').prop('disabled', false).html('<span class="dashicons dashicons-update" style="vertical-align: middle; margin-right: 5px;"></span>' + i18n.refreshStatus);
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




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

        <div class="backup-frequency-controls" style="margin-top: 10px;">
            <label for="backup-frequency-select"><?php _e('Backup Frequency:', 'pdf-builder-pro'); ?></label>
            <select id="backup-frequency-select" class="small-text">
                <option value="every_minute">Toutes les minutes (test)</option>
                <option value="daily" selected>Quotidienne</option>
                <option value="weekly">Hebdomadaire</option>
                <option value="monthly">Mensuelle</option>
            </select>
            <button type="button" id="change-frequency-btn" class="button button-small">
                <?php _e('Change Frequency', 'pdf-builder-pro'); ?>
            </button>
        </div>
    </div>

    <div class="cron-results-section" id="cron-results" style="display: none;">
        <h3><?php _e('Results', 'pdf-builder-pro'); ?></h3>
        <div id="cron-results-content"></div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    console.log('PDF Builder: Cron diagnostics script loaded');
    console.log('PDF Builder: ajaxurl:', typeof ajaxurl !== 'undefined' ? ajaxurl : 'NOT DEFINED');
    console.log('PDF Builder: Current timestamp:', new Date().toISOString());

    // Diagnose cron system
    $('#diagnose-cron-btn').on('click', function() {
        console.log('PDF Builder: [CRON] Diagnose button clicked at', new Date().toISOString());
        $(this).prop('disabled', true).text('<?php _e('Diagnosing...', 'pdf-builder-pro'); ?>');

        var ajaxData = {
            action: 'pdf_builder_diagnose_cron',
            nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
        };
        console.log('PDF Builder: [CRON] Sending AJAX data:', ajaxData);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                console.log('PDF Builder: [CRON] AJAX success response:', response);
                console.log('PDF Builder: [CRON] Response success:', response.success);
                if (response.success) {
                    console.log('PDF Builder: [CRON] Cron status updated successfully');
                    $('#cron-status-display').html('<pre>' + response.data.status + '</pre>');
                    $('#cron-results').show();
                    $('#cron-results-content').html('<pre>' + response.data.details + '</pre>');
                } else {
                    console.error('PDF Builder: [CRON] Error diagnosing cron system:', response.data);
                    alert('<?php _e('Error diagnosing cron system:', 'pdf-builder-pro'); ?> ' + response.data);
                }
                $('#diagnose-cron-btn').prop('disabled', false).text('<?php _e('Diagnose Cron System', 'pdf-builder-pro'); ?>');
            },
            error: function(xhr, status, error) {
                console.error('PDF Builder: [CRON] AJAX error - xhr:', xhr);
                console.error('PDF Builder: [CRON] AJAX error - status:', status);
                console.error('PDF Builder: [CRON] AJAX error - error:', error);
                console.error('PDF Builder: [CRON] AJAX error - response text:', xhr.responseText);
                $('#diagnose-cron-btn').prop('disabled', false).text('<?php _e('Diagnose Cron System', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // Repair cron system
    $('#repair-cron-btn').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to repair the cron system? This may restart scheduled tasks.', 'pdf-builder-pro'); ?>')) {
            console.log('PDF Builder: [CRON] Repair cancelled by user');
            return;
        }

        console.log('PDF Builder: [CRON] Repair button clicked at', new Date().toISOString());
        $(this).prop('disabled', true).text('<?php _e('Repairing...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_repair_cron',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                console.log('PDF Builder: [CRON] Repair AJAX response:', response);
                $('#repair-cron-btn').prop('disabled', false).text('<?php _e('Repair Cron System', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    console.log('PDF Builder: [CRON] Cron system repaired successfully');
                    alert('<?php _e('Cron system repaired successfully!', 'pdf-builder-pro'); ?>');
                    $('#diagnose-cron-btn').click(); // Refresh status
                } else {
                    console.error('PDF Builder: [CRON] Error repairing cron system:', response.data);
                    alert('<?php _e('Error repairing cron system:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('PDF Builder: [CRON] Repair AJAX error:', {xhr: xhr, status: status, error: error});
                $('#repair-cron-btn').prop('disabled', false).text('<?php _e('Repair Cron System', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // View backup statistics
    $('#backup-stats-btn').on('click', function() {
        console.log('PDF Builder: [BACKUP] Statistics button clicked at', new Date().toISOString());
        $(this).prop('disabled', true).text('<?php _e('Loading...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_get_backup_stats',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                console.log('PDF Builder: [BACKUP] Statistics AJAX response:', response);
                $('#backup-stats-btn').prop('disabled', false).text('<?php _e('View Backup Statistics', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    console.log('PDF Builder: [BACKUP] Statistics loaded successfully');
                    $('#cron-results').show();
                    $('#cron-results-content').html('<pre>' + response.data + '</pre>');
                } else {
                    console.error('PDF Builder: [BACKUP] Error loading backup statistics:', response.data);
                    alert('<?php _e('Error loading backup statistics:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('PDF Builder: [BACKUP] Statistics AJAX error:', {xhr: xhr, status: status, error: error});
                $('#backup-stats-btn').prop('disabled', false).text('<?php _e('View Backup Statistics', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // Create manual backup
    $('#manual-backup-btn').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to create a manual backup now?', 'pdf-builder-pro'); ?>')) {
            console.log('PDF Builder: [BACKUP] Manual backup cancelled by user');
            return;
        }

        console.log('PDF Builder: [BACKUP] Manual backup button clicked at', new Date().toISOString());
        $(this).prop('disabled', true).text('<?php _e('Creating Backup...', 'pdf-builder-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'pdf_builder_create_backup',
                nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
            },
            success: function(response) {
                console.log('PDF Builder: [BACKUP] Manual backup AJAX response:', response);
                $('#manual-backup-btn').prop('disabled', false).text('<?php _e('Create Manual Backup', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    console.log('PDF Builder: [BACKUP] Manual backup created successfully');
                    alert('<?php _e('Manual backup created successfully!', 'pdf-builder-pro'); ?>');
                    $('#backup-stats-btn').click(); // Refresh stats
                } else {
                    console.error('PDF Builder: [BACKUP] Error creating manual backup:', response.data);
                    alert('<?php _e('Error creating manual backup:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('PDF Builder: [BACKUP] Manual backup AJAX error:', {xhr: xhr, status: status, error: error});
                $('#manual-backup-btn').prop('disabled', false).text('<?php _e('Create Manual Backup', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // Change backup frequency
    $('#change-frequency-btn').on('click', function() {
        var newFrequency = $('#backup-frequency-select').val();
        console.log('PDF Builder: [BACKUP] Change frequency button clicked at', new Date().toISOString());
        console.log('PDF Builder: [BACKUP] New frequency selected:', newFrequency);

        if (!confirm('<?php _e('Change backup frequency to:', 'pdf-builder-pro'); ?> ' + newFrequency + '?')) {
            console.log('PDF Builder: [BACKUP] Frequency change cancelled by user');
            return;
        }

        $(this).prop('disabled', true).text('<?php _e('Changing...', 'pdf-builder-pro'); ?>');

        var ajaxData = {
            action: 'pdf_builder_change_backup_frequency',
            frequency: newFrequency,
            nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
        };
        console.log('PDF Builder: [BACKUP] Sending frequency change AJAX data:', ajaxData);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: ajaxData,
            success: function(response) {
                console.log('PDF Builder: [BACKUP] Frequency change AJAX response:', response);
                $('#change-frequency-btn').prop('disabled', false).text('<?php _e('Change Frequency', 'pdf-builder-pro'); ?>');
                if (response.success) {
                    console.log('PDF Builder: [BACKUP] Backup frequency changed successfully to:', newFrequency);
                    alert('<?php _e('Backup frequency changed successfully!', 'pdf-builder-pro'); ?>');
                    $('#diagnose-cron-btn').click(); // Refresh status
                } else {
                    console.error('PDF Builder: [BACKUP] Error changing frequency:', response.data);
                    alert('<?php _e('Error changing frequency:', 'pdf-builder-pro'); ?> ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('PDF Builder: [BACKUP] Frequency change AJAX error:', {xhr: xhr, status: status, error: error});
                $('#change-frequency-btn').prop('disabled', false).text('<?php _e('Change Frequency', 'pdf-builder-pro'); ?>');
                alert('<?php _e('AJAX error occurred', 'pdf-builder-pro'); ?>');
            }
        });
    });

    // Auto-refresh logs for cron monitoring
    console.log('PDF Builder: [CRON] Cron diagnostics interface initialized');
    console.log('PDF Builder: [BACKUP] Backup controls initialized');

    // Auto backup monitoring for cron-triggered backups
    var lastBackupCount = 0;
    var autoBackupMonitorInterval;

    function startAutoBackupMonitoring() {
        console.log('PDF Builder: [AUTO BACKUP] 🎯 Starting automatic backup monitoring');
        console.log('PDF Builder: [AUTO BACKUP] 🚀 Monitoring for cron-triggered backups');

        autoBackupMonitorInterval = setInterval(function() {
            console.log('PDF Builder: [AUTO BACKUP] 🔍 Checking for new automatic backups at', new Date().toISOString());

            // Check backup stats to see if count has changed
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'pdf_builder_get_backup_stats',
                    nonce: '<?php echo wp_create_nonce('pdf_builder_admin_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        try {
                            // Parse the backup stats to get current count
                            var statsText = response.data;
                            var backupLines = statsText.split('\n').filter(function(line) {
                                return line.includes('backup-') || line.includes('auto_backup');
                            });

                            var currentBackupCount = backupLines.length;

                            if (lastBackupCount === 0) {
                                // First check - initialize counter
                                lastBackupCount = currentBackupCount;
                                console.log('PDF Builder: [AUTO BACKUP] 📊 Initialized backup counter:', currentBackupCount);
                            } else if (currentBackupCount > lastBackupCount) {
                                // New backup detected!
                                var newBackups = currentBackupCount - lastBackupCount;
                                console.log('PDF Builder: [AUTO BACKUP] 🎉 NEW AUTOMATIC BACKUP DETECTED!');
                                console.log('PDF Builder: [AUTO BACKUP] 📈 Backup count increased from', lastBackupCount, 'to', currentBackupCount);
                                console.log('PDF Builder: [AUTO BACKUP] ➕', newBackups, 'new backup(s) created by cron');

                                // Log details of new backups
                                var newBackupLines = backupLines.slice(-newBackups);
                                newBackupLines.forEach(function(backupLine, index) {
                                    console.log('PDF Builder: [AUTO BACKUP] 🆕 New backup #' + (index + 1) + ':', backupLine.trim());
                                });

                                // Update counter
                                lastBackupCount = currentBackupCount;

                                // Optional: Show notification to user
                                if (window.showInfoNotification) {
                                    window.showInfoNotification('<?php _e('Automatic backup completed', 'pdf-builder-pro'); ?>', '<?php _e('A new backup was created by the scheduled task.', 'pdf-builder-pro'); ?>');
                                }
                            } else if (currentBackupCount < lastBackupCount) {
                                console.warn('PDF Builder: [AUTO BACKUP] ⚠️ Backup count decreased from', lastBackupCount, 'to', currentBackupCount);
                                lastBackupCount = currentBackupCount;
                            } else {
                                console.log('PDF Builder: [AUTO BACKUP] ✅ No new backups detected (count:', currentBackupCount + ')');
                            }
                        } catch (error) {
                            console.error('PDF Builder: [AUTO BACKUP] Error parsing backup stats:', error);
                        }
                    } else {
                        console.error('PDF Builder: [AUTO BACKUP] Failed to check backup stats:', response.data);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('PDF Builder: [AUTO BACKUP] AJAX error checking backups:', {xhr: xhr, status: status, error: error});
                }
            });
        }, 30000); // Check every 30 seconds

        console.log('PDF Builder: [AUTO BACKUP] ⏰ Monitoring started - checking every 30 seconds');
    }

    function stopAutoBackupMonitoring() {
        if (autoBackupMonitorInterval) {
            clearInterval(autoBackupMonitorInterval);
            console.log('PDF Builder: [AUTO BACKUP] 🛑 Monitoring stopped');
        }
    }

    // Start monitoring when page loads
    startAutoBackupMonitoring();

    // Stop monitoring when page unloads
    $(window).on('beforeunload', function() {
        stopAutoBackupMonitoring();
    });

    console.log('PDF Builder: [AUTO BACKUP] 🎯 Automatic backup monitoring initialized');
});
</script>
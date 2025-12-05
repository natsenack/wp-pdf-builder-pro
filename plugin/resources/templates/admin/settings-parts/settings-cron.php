<?php
if (!defined('ABSPATH')) exit('No direct access');

// Simplified version for testing
?>

<div class="pdf-builder-cron-diagnostics">
    <h2>Cron System Diagnostics</h2>
    <p>This is a test version of the cron diagnostics page.</p>

    <div class="cron-status-section">
        <h3>System Status</h3>
        <div id="cron-status-display">
            <p>Cron diagnostic interface loaded successfully.</p>
        </div>
    </div>

    <div class="cron-actions-section">
        <h3>Actions</h3>
        <button type="button" id="diagnose-cron-btn" class="button button-secondary">
            Diagnose Cron System
        </button>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    $('#diagnose-cron-btn').on('click', function() {
        alert('Cron diagnostic button clicked!');
    });
});
</script>
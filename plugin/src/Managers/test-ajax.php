<?php
// Test file to verify AJAX handler is being called
error_log('=== TEST AJAX HANDLER CALLED ===');
error_log('$_POST: ' . print_r($_POST, true));

// Check if the Template Manager class exists
error_log('Class exists: ' . (class_exists('PDF_Builder_Template_Manager') ? 'YES' : 'NO'));

// Try to call the function directly
if (class_exists('PDF_Builder_Template_Manager')) {
    error_log('Creating instance...');
    try {
        $manager = new PDF_Builder_Template_Manager(null);
        error_log('Instance created successfully');
        
        // Check if method exists
        if (method_exists($manager, 'ajax_auto_save_template')) {
            error_log('Method exists: ajax_auto_save_template');
        } else {
            error_log('Method NOT found: ajax_auto_save_template');
        }
    } catch (Exception $e) {
        error_log('Exception creating instance: ' . $e->getMessage());
    }
} else {
    error_log('PDF_Builder_Template_Manager class NOT FOUND');
}

wp_die('Test completed - check debug.log');

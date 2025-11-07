<?php
/**
 * PDF Builder Pro - Settings Page
 * Test box 1800px height
 */

if (!defined('ABSPATH')) {
    exit('Direct access forbidden');
}

if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die(__('You must be logged in', 'pdf-builder-pro'));
}
?>

<div class="wrap">
    <h1><?php _e('PDF Builder Pro Settings', 'pdf-builder-pro'); ?></h1>
    
    <div style="background: #000; color: #0f0; padding: 50px; margin: 50px 0; font-size: 24px; height: 1800px; font-family: monospace; text-align: center; display: flex; align-items: center; justify-content: center;">
        <div>
            <p>TEST BOX - 1800px HEIGHT</p>
            <p style="margin-top: 50px; font-size: 18px;">Footer should appear BELOW this box</p>
        </div>
    </div>
</div>

<?php wp_footer(); ?>

<?php
/**
 * Page des Paramètres - PDF Builder Pro
 * VERSION SIMPLIFIÉE POUR DEBUG - TEST FOOTER
 */

if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

if (!is_user_logged_in() || !current_user_can('read')) {
    wp_die(__('Vous devez être connecté pour accéder à cette page.', 'pdf-builder-pro'));
}
?>

<div class="wrap">
    <h1><?php _e('⚙️ Paramètres - PDF Builder Pro', 'pdf-builder-pro'); ?></h1>
    
    <div style="background: #000; color: #0f0; padding: 50px; margin: 50px 0; font-size: 24px; min-height: 500px; font-family: monospace; text-align: center;">
        <p>ZONE DE CONTENU PRINCIPALE</p>
        <p style="margin-top: 200px;">Footer devrait être DESSOUS cette boîte noire</p>
    </div>
    
</div>


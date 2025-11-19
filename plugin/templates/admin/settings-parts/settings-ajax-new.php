// Canvas settings AJAX handler
function pdf_builder_save_canvas_settings_handler() {
    if (wp_verify_nonce($_POST['nonce'], 'pdf_builder_canvas_nonce')) {
        // Utiliser le Canvas_Manager pour la sauvegarde centralisée
        if (class_exists('PDF_Builder_Canvas_Manager')) {
            $canvas_manager = new PDF_Builder_Canvas_Manager();

            // Mapper les champs du formulaire vers les noms attendus par le Canvas_Manager
            $settings = [];
            if (isset($_POST['canvas_bg_color'])) {
                $settings['canvas_background_color'] = sanitize_text_field($_POST['canvas_bg_color']);
            }
            if (isset($_POST['canvas_border_color'])) {
                $settings['container_background_color'] = sanitize_text_field($_POST['canvas_border_color']);
            }
            if (isset($_POST['canvas_border_width'])) {
                $settings['border_width'] = intval($_POST['canvas_border_width']);
            }
            if (isset($_POST['canvas_grid_size'])) {
                $settings['grid_size'] = intval($_POST['canvas_grid_size']);
            }
            if (isset($_POST['canvas_width'])) {
                $settings['default_canvas_width'] = intval($_POST['canvas_width']);
            }
            if (isset($_POST['canvas_height'])) {
                $settings['default_canvas_height'] = intval($_POST['canvas_height']);
            }
            if (isset($_POST['canvas_zoom_min'])) {
                $settings['zoom_min'] = intval($_POST['canvas_zoom_min']);
            }
            if (isset($_POST['canvas_zoom_max'])) {
                $settings['zoom_max'] = intval($_POST['canvas_zoom_max']);
            }
            if (isset($_POST['canvas_zoom_default'])) {
                $settings['zoom_default'] = intval($_POST['canvas_zoom_default']);
            }

            // Convertir les checkboxes
            $checkboxes = ['canvas_shadow_enabled', 'canvas_grid_enabled', 'canvas_guides_enabled', 'canvas_snap_to_grid', 'canvas_pan_enabled', 'canvas_drag_enabled', 'canvas_resize_enabled', 'canvas_rotate_enabled', 'canvas_multi_select', 'canvas_keyboard_shortcuts', 'canvas_auto_save'];
            foreach ($checkboxes as $checkbox) {
                if (isset($_POST[$checkbox])) {
                    $settings[str_replace('canvas_', '', $checkbox)] = $_POST[$checkbox] === '1' ? 1 : 0;
                }
            }

            $saved = $canvas_manager->saveSettings($settings);
            if ($saved) {
                send_ajax_response(true, 'Paramètres canvas sauvegardés avec succès.', ['saved' => $settings]);
            } else {
                send_ajax_response(false, 'Erreur lors de la sauvegarde des paramètres canvas.');
            }
        } else {
            send_ajax_response(false, 'Canvas_Manager non disponible.');
        }
    } else {
        send_ajax_response(false, 'Erreur de sécurité - nonce invalide.');
    }
}
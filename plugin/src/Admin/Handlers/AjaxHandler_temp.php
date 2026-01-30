<?php

/**
 * PDF Builder Pro - Gestionnaire AJAX
 * Gère tous les appels AJAX de l'administration
 */

namespace PDF_Builder\Admin\Handlers;

use Exception;

/**
 * Classe principale pour gérer les appels AJAX
 */
class AjaxHandler
{
    /**
     * Constructeur
     */
    public function __construct()
    {
        $this->initHooks();
    }

    /**
     * Initialiser les hooks AJAX
     */
    private function initHooks()
    {
        // AJAX pour les paramètres canvas
        add_action('wp_ajax_pdf_builder_save_canvas_modal_settings', [$this, 'ajaxSaveCanvasModalSettings']);
    }

    /**
     * AJAX: Sauvegarder les paramètres des modales canvas
     */
    public function ajaxSaveCanvasModalSettings()
    {
        // Version ultra-simplifiée pour debug
        wp_send_json_success(['message' => 'Test réussi', 'category' => $_POST['category'] ?? 'unknown']);
    }
}


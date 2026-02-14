<?php

/**
 * PDF Builder Pro - Admin Page Renderer
 * Responsable du rendu HTML de la page d'administration (Tableau de bord)
 */

namespace PDF_Builder\Admin\Renderers;

class AdminPageRenderer
{
    private $admin;

    public function __construct($admin)
    {
        $this->admin = $admin;
    }

    public function renderAdminPage()
    {
        // Récupérer les données nécessaires depuis l'admin
        $stats = $this->admin->getDashboardStats();
        $plugin_version = $this->admin->getPluginVersion();

        // Charger le template dashboard-page.php qui utilise les classes avec préfixe pdfb-
        $template_path = plugin_dir_path(dirname(dirname(__FILE__))) . 'templates/admin/dashboard-page.php';
        
        if (file_exists($template_path)) {
            ob_start();
            include $template_path;
            return ob_get_clean();
        }

        // Fallback si le template n'existe pas
        return '<div class="wrap"><h1>PDF Builder Pro</h1><p>Erreur: Template dashboard introuvable.</p></div>';
    }
}

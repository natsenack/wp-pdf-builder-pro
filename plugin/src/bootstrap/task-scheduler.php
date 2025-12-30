<?php
/**
 * PDF Builder Pro - Task Scheduler Module
 * Initialisation du planificateur de tâches
 *
 * @package PDF_Builder
 * @version 1.0.0
 */

// Empêcher l'accès direct
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

// ============================================================================
// INITIALISATION DU PLANIFICATEUR DE TÂCHES
// ============================================================================

// Initialiser le planificateur de tâches
add_action('init', function() {
    // Vérifier si la classe Task_Scheduler existe
    if (!class_exists('PDF_Builder_Task_Scheduler')) {
        // Charger le fichier du planificateur de tâches si nécessaire
        $task_scheduler_file = plugin_dir_path(__FILE__) . '../../core/Task_Scheduler.php';
        if (file_exists($task_scheduler_file)) {
            require_once $task_scheduler_file;
        }
    }

    // Initialiser le planificateur si la classe existe
    if (class_exists('PDF_Builder_Task_Scheduler')) {
        PDF_Builder_Task_Scheduler::get_instance()->init();
    }
});

// ============================================================================
// HOOKS DE DÉSACTIVATION POUR NETTOYER LES TÂCHES PLANIFIÉES
// ============================================================================

// Nettoyer les tâches planifiées lors de la désactivation du plugin
register_deactivation_hook(__FILE__, function() {
    // Supprimer les tâches planifiées si la classe existe
    if (class_exists('PDF_Builder_Task_Scheduler')) {
        PDF_Builder_Task_Scheduler::cleanup_scheduled_tasks();
    }
});
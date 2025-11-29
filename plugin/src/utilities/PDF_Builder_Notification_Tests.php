<?php
/**
 * PDF Builder Pro - Tests du système de notifications
 *
 * Ce fichier contient des tests pour valider le fonctionnement
 * du nouveau système de notifications unifié.
 *
 * @package PDF_Builder
 * @since 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe de tests pour le système de notifications
 */
class PDF_Builder_Notification_Tests {

    /**
     * Exécuter tous les tests
     */
    public static function run_all_tests() {
        echo "<h2>Tests du système de notifications PDF Builder Pro</h2>";

        self::test_class_loading();
        self::test_singleton();
        self::test_toast_notifications();
        self::test_admin_notices();
        self::test_system_notifications();
        self::test_database_operations();
        self::test_ajax_endpoints();

        echo "<p><strong>Tous les tests ont été exécutés.</strong></p>";
    }

    /**
     * Test du chargement de la classe
     */
    private static function test_class_loading() {
        echo "<h3>Test du chargement de classe</h3>";

        if (class_exists('PDF_Builder\Utilities\PDF_Builder_Notification_Manager')) {
            echo "✅ Classe principale chargée<br>";
        } else {
            echo "❌ Classe principale non chargée<br>";
        }

        if (class_exists('PDF_Builder\Utilities\PDF_Builder_UI_Notification_Manager')) {
            echo "✅ Alias UI chargé<br>";
        } else {
            echo "❌ Alias UI non chargé<br>";
        }

        if (function_exists('pdf_builder_notify')) {
            echo "✅ Fonction globale pdf_builder_notify disponible<br>";
        } else {
            echo "❌ Fonction globale pdf_builder_notify non disponible<br>";
        }
    }

    /**
     * Test du pattern Singleton
     */
    private static function test_singleton() {
        echo "<h3>Test du pattern Singleton</h3>";

        $instance1 = PDF_Builder_Notification_Manager::get_instance();
        $instance2 = PDF_Builder_Notification_Manager::get_instance();

        if ($instance1 === $instance2) {
            echo "✅ Pattern Singleton fonctionne<br>";
        } else {
            echo "❌ Pattern Singleton ne fonctionne pas<br>";
        }
    }

    /**
     * Test des notifications toast
     */
    private static function test_toast_notifications() {
        echo "<h3>Test des notifications toast</h3>";

        $manager = PDF_Builder_Notification_Manager::get_instance();

        // Test ajout toast
        $toast_id = $manager->add_toast("Test toast", 'success', 3000);
        if (!empty($toast_id)) {
            echo "✅ Toast ajouté avec ID: {$toast_id}<br>";
        } else {
            echo "❌ Échec ajout toast<br>";
        }

        // Test méthodes statiques
        PDF_Builder_Notification_Manager::success("Test succès");
        PDF_Builder_Notification_Manager::error("Test erreur");
        PDF_Builder_Notification_Manager::warning("Test avertissement");
        PDF_Builder_Notification_Manager::info("Test info");

        echo "✅ Méthodes statiques de toast testées<br>";
    }

    /**
     * Test des notifications admin
     */
    private static function test_admin_notices() {
        echo "<h3>Test des notifications admin</h3>";

        $manager = PDF_Builder_Notification_Manager::get_instance();

        // Test ajout notice admin
        $notice_id = $manager->add_admin_notice("Test notice admin", 'info', true);
        if (!empty($notice_id)) {
            echo "✅ Notice admin ajoutée avec ID: {$notice_id}<br>";
        } else {
            echo "❌ Échec ajout notice admin<br>";
        }

        echo "✅ Notifications admin testées<br>";
    }

    /**
     * Test des notifications système
     */
    private static function test_system_notifications() {
        echo "<h3>Test des notifications système</h3>";

        // Test fonction globale
        $notification_id = pdf_builder_notify(
            PDF_Builder_Notification_Manager::TYPE_SUCCESS,
            'Test système',
            'Notification système de test'
        );

        if (!empty($notification_id)) {
            echo "✅ Notification système créée avec ID: {$notification_id}<br>";
        } else {
            echo "❌ Échec création notification système<br>";
        }
    }

    /**
     * Test des opérations de base de données
     */
    private static function test_database_operations() {
        echo "<h3>Test des opérations de base de données</h3>";

        $manager = PDF_Builder_Notification_Manager::get_instance();

        // Test récupération notifications
        $notifications = $manager->get_notifications(10);
        if (is_array($notifications)) {
            echo "✅ Récupération notifications: " . count($notifications) . " trouvées<br>";
        } else {
            echo "❌ Échec récupération notifications<br>";
        }

        // Test comptage non lues
        $unread_count = $manager->get_unread_count();
        echo "✅ Nombre de notifications non lues: {$unread_count}<br>";
    }

    /**
     * Test des endpoints AJAX
     */
    private static function test_ajax_endpoints() {
        echo "<h3>Test des endpoints AJAX</h3>";

        // Vérifier que les actions sont hookées
        if (has_action('wp_ajax_pdf_builder_dismiss_notification')) {
            echo "✅ Action AJAX dismiss_notification hookée<br>";
        } else {
            echo "❌ Action AJAX dismiss_notification non hookée<br>";
        }

        if (has_action('wp_ajax_pdf_builder_show_toast')) {
            echo "✅ Action AJAX show_toast hookée<br>";
        } else {
            echo "❌ Action AJAX show_toast non hookée<br>";
        }

        if (has_action('wp_ajax_pdf_builder_get_notifications')) {
            echo "✅ Action AJAX get_notifications hookée<br>";
        } else {
            echo "❌ Action AJAX get_notifications non hookée<br>";
        }
    }
}

// Fonction pour exécuter les tests via URL
function pdf_builder_run_notification_tests() {
    if (isset($_GET['pdf_builder_test_notifications']) && current_user_can('manage_options')) {
        PDF_Builder_Notification_Tests::run_all_tests();
        exit;
    }
}
add_action('admin_init', 'pdf_builder_run_notification_tests');

/*
Pour exécuter les tests, ajouter ?pdf_builder_test_notifications=1 à l'URL d'une page admin
Exemple: /wp-admin/admin.php?page=pdf_builder_settings&pdf_builder_test_notifications=1
*/
<?php
/**
 * Script pour corriger les noms de méthodes et de classe dans PDF_Builder_Notification_Manager.php
 */

$filePath = __DIR__ . '/plugin/src/Managers/PDF_Builder_Notification_Manager.php';

if (!file_exists($filePath)) {
    die("Fichier non trouvé: $filePath\n");
}

$content = file_get_contents($filePath);

// 1. Ajouter le namespace
$content = preg_replace('/^<\?php\s*\n/', "<?php\n\nnamespace WP_PDF_Builder_Pro\\Managers;\n\n", $content);

// 2. Corriger le nom de la classe
$content = str_replace('class PDF_Builder_Notification_Manager', 'class PdfBuilderNotificationManager', $content);

// 3. Liste des méthodes à corriger (snake_case vers camelCase)
$methodMappings = [
    'get_instance' => 'getInstance',
    'init_hooks' => 'initHooks',
    'are_email_notifications_enabled' => 'areEmailNotificationsEnabled',
    'get_admin_email' => 'getAdminEmail',
    'get_notification_level' => 'getNotificationLevel',
    'should_send_notification' => 'shouldSendNotification',
    'send_notification' => 'sendNotification',
    'notify_pdf_generated' => 'notifyPdfGenerated',
    'notify_pdf_error' => 'notifyPdfError',
    'notify_template_deleted' => 'notifyTemplateDeleted',
    'notify_performance_warning' => 'notifyPerformanceWarning',
    'send_test_notification' => 'sendTestNotification',
    'send_smtp_email' => 'sendSmtpEmail'
];

// 4. Corriger les appels de méthodes dans le fichier lui-même
foreach ($methodMappings as $old => $new) {
    // Remplacer les appels de méthodes (avec -> ou ::)
    $content = preg_replace('/(->|::)' . preg_quote($old, '/') . '\(/', '$1' . $new . '(', $content);
}

// 5. Corriger les déclarations de méthodes
foreach ($methodMappings as $old => $new) {
    $content = preg_replace('/function ' . preg_quote($old, '/') . '\(/', 'function ' . $new . '(', $content);
}

file_put_contents($filePath, $content);

echo "Corrections appliquées dans PDF_Builder_Notification_Manager.php\n";
echo "- Namespace ajouté: WP_PDF_Builder_Pro\\Managers\n";
echo "- Nom de classe corrigé: PDF_Builder_Notification_Manager → PdfBuilderNotificationManager\n";
echo "- " . count($methodMappings) . " noms de méthodes convertis de snake_case vers camelCase\n";
?>
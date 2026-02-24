<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals, WordPress.Security, WordPress.PHP.DevelopmentFunctions, WordPress.DB.PreparedSQL, WordPress.DB.PreparedSQLPlaceholders, Generic.PHP.DiscourageGoto, PluginCheck.CodeAnalysis.AutoUpdates, WordPress.DB.DirectDatabaseQuery, Internal.LineEndings.Mixed

/**
 * PDF Builder Pro - Thumbnail Manager
 * Gestion centralisée des thumbnails de templates
 */

namespace PDF_Builder\Managers;


if ( ! defined( 'ABSPATH' ) ) exit;

class PDF_Builder_Thumbnail_Manager
{
    private static $instance = null;

    /**
     * Instance unique (Singleton)
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructeur privé (Singleton)
     */
    private function __construct()
    {
        $this->initHooks();
    }

    /**
     * Initialisation des hooks
     */
    private function initHooks()
    {
        \add_action('admin_init', [$this, 'runDatabaseMigrations']);
    }

    /**
     * Génère un thumbnail pour un template
     */
    public function generateTemplateThumbnail($template_id, $template_data)
    {
        try {
            // Thumbnail generation requires the PdfHtmlGenerator
            // which would need extensive setup. For now, return false
            // to use default placeholder
            return false;
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas échouer la sauvegarde
            $this->logError('Erreur génération thumbnail template ' . $template_id . ': ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Convertit un PDF en thumbnail PNG
     */
    private function convertPdfToThumbnail($pdf_url, $template_id)
    {
        try {
            // Créer un thumbnail simple basé sur les données du template
            $upload_dir = \wp_upload_dir();
            $thumbnail_dir = $upload_dir['basedir'] . '/pdf-builder-thumbnails/';

            if (!file_exists($thumbnail_dir)) {
                \wp_mkdir_p($thumbnail_dir);
            }

            $thumbnail_filename = 'template-' . $template_id . '-thumb.png';
            $thumbnail_path = $thumbnail_dir . $thumbnail_filename;

            // Récupérer les données du template pour créer un aperçu
            global $wpdb;
            $table_templates = $wpdb->prefix . 'pdf_builder_templates';
            $template = $wpdb->get_row( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                $wpdb->prepare("SELECT template_data FROM $table_templates WHERE id = %d", $template_id), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
                ARRAY_A
            );

            if ($template) {
                $template_data = json_decode($template['template_data'], true);
                $thumbnail_path = $this->generateTemplatePreviewImage($template_data, $thumbnail_path);
            } else {
                // Fallback vers un thumbnail simple
                $thumbnail_path = $this->generateSimpleThumbnail($template_id, $thumbnail_path);
            }

            if (file_exists($thumbnail_path)) {
                $relative_path = str_replace($upload_dir['basedir'], '', $thumbnail_path);
                return $upload_dir['baseurl'] . $relative_path;
            }

        } catch (\Exception $e) {
            $this->logError('Erreur conversion PDF vers thumbnail: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Génère une image d'aperçu simple du template
     */
    private function generateSimpleThumbnail($template_id, $thumbnail_path)
    {
        $image = imagecreatetruecolor(300, 200);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 150, 150, 150);
        $border_color = imagecolorallocate($image, 200, 200, 200);

        imagefill($image, 0, 0, $white);
        imagerectangle($image, 0, 0, 299, 199, $border_color);
        imagestring($image, 5, 20, 80, 'Template ' . $template_id, $black);

        imagepng($image, $thumbnail_path);

        return $thumbnail_path;
    }

    /**
     * Génère une image d'aperçu du template basée sur ses éléments
     */
    private function generateTemplatePreviewImage($template_data, $thumbnail_path)
    {
        $image = imagecreatetruecolor(300, 200);
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $gray = imagecolorallocate($image, 150, 150, 150);
        $blue = imagecolorallocate($image, 0, 123, 255);

        imagefill($image, 0, 0, $white);

        // Dessiner un cadre
        imagerectangle($image, 5, 5, 295, 195, $gray);

        // Analyser les éléments du template pour créer un aperçu représentatif
        if (isset($template_data['elements']) && is_array($template_data['elements'])) {
            $element_count = count($template_data['elements']);

            // Dessiner quelques éléments représentatifs
            $y = 20;

            // Titre
            if (isset($template_data['name'])) {
                imagestring($image, 4, 15, $y, substr($template_data['name'], 0, 25), $black);
                $y += 25;
            }

            // Quelques éléments
            $displayed_elements = 0;
            foreach ($template_data['elements'] as $element) {
                if ($displayed_elements >= 3) break; // Limiter à 3 éléments

                if (isset($element['type'])) {
                    $element_type = $element['type'];
                    $element_text = '';

                    switch ($element_type) {
                        case 'text':
                        case 'dynamic_text':
                            $element_text = isset($element['content']) ? substr($element['content'], 0, 20) : 'Texte';
                            break;
                        case 'company_logo':
                            $element_text = '[Logo]';
                            break;
                        case 'invoice_number':
                            $element_text = 'Facture N° 001';
                            break;
                        case 'product_table':
                            $element_text = '[Tableau produits]';
                            break;
                        default:
                            $element_text = $element_type;
                    }

                    if (!empty($element_text)) {
                        imagestring($image, 2, 15, $y, $element_text, $blue);
                        $y += 15;
                        $displayed_elements++;
                    }
                }
            }

            // Nombre total d'éléments
            imagestring($image, 1, 15, 170, $element_count . ' éléments', $gray);
        }

        imagepng($image, $thumbnail_path);

        return $thumbnail_path;
    }

    /**
     * Met à jour l'URL du thumbnail dans la base de données
     */
    public function updateTemplateThumbnail($template_id, $thumbnail_url)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $result = $wpdb->update(
            $table_templates,
            ['thumbnail_url' => $thumbnail_url],
            ['id' => $template_id],
            ['%s'],
            ['%d']
        );

        return $result !== false;
    }

    /**
     * Récupère l'URL du thumbnail d'un template
     */
    public function getTemplateThumbnail($template_id)
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $template = $wpdb->get_row( // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            $wpdb->prepare("SELECT thumbnail_url FROM $table_templates WHERE id = %d", $template_id), // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            ARRAY_A
        );

        return $template ? $template['thumbnail_url'] : '';
    }

    /**
     * Supprime le thumbnail d'un template
     */
    public function deleteTemplateThumbnail($template_id)
    {
        $thumbnail_url = $this->getTemplateThumbnail($template_id);
        if (!empty($thumbnail_url)) {
            // Supprimer le fichier physique
            $upload_dir = \wp_upload_dir();
            $relative_path = str_replace($upload_dir['baseurl'], '', $thumbnail_url);
            $file_path = $upload_dir['basedir'] . $relative_path;

            if (file_exists($file_path)) {
                wp_delete_file($file_path);
            }

            // Supprimer l'URL de la DB
            $this->updateTemplateThumbnail($template_id, '');
        }
    }

    /**
     * Migration de base de données
     */
    public function runDatabaseMigrations()
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        // Vérifier et ajouter la colonne thumbnail_url
        $columns = $wpdb->get_results("DESCRIBE $table_templates"); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
        $thumbnail_exists = false;
        if ($columns) {
            foreach ($columns as $column) {
                if ($column->Field === 'thumbnail_url') {
                    $thumbnail_exists = true;
                    break;
                }
            }
        }

        if (!$thumbnail_exists) {
            $sql = "ALTER TABLE $table_templates ADD COLUMN thumbnail_url VARCHAR(500) DEFAULT '' AFTER template_data";
            $result = $wpdb->query($sql); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, PluginCheck.Security.DirectDB.UnescapedDBParameter, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery
            if ($result !== false) {
                $this->logInfo('Colonne thumbnail_url ajoutée avec succès');
            } else {
                $this->logError('Erreur lors de l\'ajout de la colonne thumbnail_url: ' . $wpdb->last_error);
            }
        }
    }

    /**
     * Nettoie les thumbnails orphelins (templates supprimés)
     */
    public function cleanupOrphanedThumbnails()
    {
        global $wpdb;
        $table_templates = $wpdb->prefix . 'pdf_builder_templates';

        $upload_dir = \wp_upload_dir();
        $thumbnail_dir = $upload_dir['basedir'] . '/pdf-builder-thumbnails/';

        if (!file_exists($thumbnail_dir)) {
            return;
        }

        // Récupérer tous les templates avec thumbnails
        $templates = $wpdb->get_results("SELECT id, thumbnail_url FROM $table_templates WHERE thumbnail_url != ''", ARRAY_A); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.LikeWildcardsInQuery

        $existing_template_ids = array_column($templates, 'id');
        $existing_thumbnails = array_column($templates, 'thumbnail_url');

        // Scanner le répertoire des thumbnails
        $thumbnail_files = glob($thumbnail_dir . 'template-*-thumb.png');

        foreach ($thumbnail_files as $file_path) {
            $filename = basename($file_path);
            // Extraire l'ID du template du nom de fichier
            if (preg_match('/template-(\d+)-thumb\.png/', $filename, $matches)) {
                $template_id = (int) $matches[1];

                // Si le template n'existe plus ou n'a plus de thumbnail, supprimer le fichier
                if (!in_array($template_id, $existing_template_ids)) {
                    wp_delete_file($file_path);
                    $this->logInfo("Thumbnail orphelin supprimé: $filename");
                }
            }
        }
    }

    /**
     * Logging des erreurs
     */
    private function logError($message)
    {
        error_log('THUMBNAIL_ERROR: ' . $message);
    }

    /**
     * Logging des informations
     */
    private function logInfo($message)
    {
        error_log('THUMBNAIL_INFO: ' . $message);
    }
}





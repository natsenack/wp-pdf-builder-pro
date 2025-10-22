<?php
/**
 * PDF Builder Pro - PreviewRenderer
 * Phase 3.1.1 - Classe PreviewRenderer de base
 *
 * Classe principale pour le rendu des aperçus PDF avec canvas A4.
 * Gère les dimensions, le mode (Canvas/Metabox) et l'initialisation du rendu.
 */

namespace PDF_Builder\Renderers;

// Sécurité WordPress
if (!defined('ABSPATH')) {
    exit('Accès direct interdit');
}

class PreviewRenderer {

    /**
     * Constantes pour les dimensions A4 standard
     */
    const A4_WIDTH_MM = 210;
    const A4_HEIGHT_MM = 297;
    const A4_DPI = 150;
    const A4_WIDTH_PX = 794;  // 210mm * 150 DPI / 25.4
    const A4_HEIGHT_PX = 1123; // 297mm * 150 DPI / 25.4

    /**
     * Options de configuration du renderer
     * @var array
     */
    private $options;

    /**
     * Mode de rendu (canvas|metabox)
     * @var string
     */
    private $mode;

    /**
     * Dimensions du canvas (largeur, hauteur en pixels)
     * @var array
     */
    private $dimensions;

    /**
     * État d'initialisation du renderer
     * @var bool
     */
    private $initialized;

    /**
     * Constructeur - Initialise le PreviewRenderer avec les options
     *
     * @param array $options Options de configuration
     *                       - mode: 'canvas' ou 'metabox'
     *                       - width: largeur en pixels (optionnel)
     *                       - height: hauteur en pixels (optionnel)
     *                       - zoom: niveau de zoom initial (optionnel, défaut 100)
     */
    public function __construct(array $options = []) {
        // Options par défaut
        $defaultOptions = [
            'mode' => 'canvas',
            'width' => null,
            'height' => null,
            'zoom' => 100,
            'responsive' => true
        ];

        // Fusionner avec les options fournies
        $this->options = array_merge($defaultOptions, $options);

        // Valider le mode
        $this->mode = $this->validateMode($this->options['mode']);

        // Initialiser les dimensions
        $this->dimensions = $this->initializeDimensions();

        // État initial
        $this->initialized = false;

        // Log d'initialisation
        error_log('PreviewRenderer: Instance créée avec mode=' . $this->mode);
    }

    /**
     * Initialise le renderer
     * Méthode appelée avant tout rendu
     *
     * @return bool True si initialisation réussie
     */
    public function init() {
        try {
            // Vérifier que les dimensions sont définies
            if (empty($this->dimensions['width']) || empty($this->dimensions['height'])) {
                throw new \Exception('Dimensions non définies pour le renderer');
            }

            // Vérifier que le mode est valide
            if (!in_array($this->mode, ['canvas', 'metabox'])) {
                throw new \Exception('Mode invalide: ' . $this->mode);
            }

            // Marquer comme initialisé
            $this->initialized = true;

            error_log('PreviewRenderer: Initialisation réussie (mode=' . $this->mode . ', dimensions=' . $this->dimensions['width'] . 'x' . $this->dimensions['height'] . ')');

            return true;

        } catch (\Exception $e) {
            error_log('PreviewRenderer: Erreur d\'initialisation - ' . $e->getMessage());
            $this->initialized = false;
            return false;
        }
    }

    /**
     * Rend un élément dans le canvas
     * Méthode principale appelée pour chaque élément du template
     *
     * @param array $elementData Données de l'élément à rendre
     * @return bool True si rendu réussi
     */
    public function render(array $elementData) {
        // Vérifier l'initialisation
        if (!$this->initialized) {
            error_log('PreviewRenderer: Tentative de rendu sans initialisation');
            return false;
        }

        try {
            // Validation basique des données d'élément
            if (!isset($elementData['type'])) {
                throw new \Exception('Type d\'élément manquant');
            }

            // Log du rendu
            error_log('PreviewRenderer: Rendu élément ' . $elementData['type'] . ' en mode ' . $this->mode);

            // TODO: Implémentation du rendu spécifique selon le type d'élément
            // Pour l'instant, retourner true (sera implémenté dans les renderers spécialisés)

            return true;

        } catch (\Exception $e) {
            error_log('PreviewRenderer: Erreur de rendu - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Détruit le renderer et libère les ressources
     *
     * @return bool True si destruction réussie
     */
    public function destroy() {
        try {
            // Libérer les ressources
            $this->dimensions = null;
            $this->options = null;
            $this->initialized = false;

            error_log('PreviewRenderer: Destruction réussie');

            return true;

        } catch (\Exception $e) {
            error_log('PreviewRenderer: Erreur de destruction - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Valide le mode de rendu
     *
     * @param string $mode Mode à valider
     * @return string Mode validé
     * @throws \Exception Si mode invalide
     */
    private function validateMode($mode) {
        $validModes = ['canvas', 'metabox'];

        if (!in_array($mode, $validModes)) {
            throw new \Exception('Mode invalide: ' . $mode . '. Modes valides: ' . implode(', ', $validModes));
        }

        return $mode;
    }

    /**
     * Initialise les dimensions du canvas
     *
     * @return array Dimensions [width, height]
     */
    private function initializeDimensions() {
        // Utiliser les constantes A4 par défaut
        $defaultWidth = self::A4_WIDTH_PX;
        $defaultHeight = self::A4_HEIGHT_PX;

        return [
            'width' => $this->options['width'] ?? $defaultWidth,
            'height' => $this->options['height'] ?? $defaultHeight
        ];
    }

    /**
     * Définit les dimensions du canvas
     *
     * @param int|null $width Largeur en pixels (null pour utiliser A4 par défaut)
     * @param int|null $height Hauteur en pixels (null pour utiliser A4 par défaut)
     * @return bool True si dimensions définies avec succès
     */
    public function setDimensions(?int $width = null, ?int $height = null) {
        try {
            // Utiliser les dimensions A4 par défaut si non spécifiées
            $newWidth = $width ?? self::A4_WIDTH_PX;
            $newHeight = $height ?? self::A4_HEIGHT_PX;

            // Validation des dimensions
            if ($newWidth <= 0 || $newHeight <= 0) {
                throw new \Exception('Les dimensions doivent être positives');
            }

            // Limites raisonnables (éviter des valeurs trop extrêmes)
            if ($newWidth > 5000 || $newHeight > 5000) {
                throw new \Exception('Dimensions trop grandes (max 5000px)');
            }

            if ($newWidth < 100 || $newHeight < 100) {
                throw new \Exception('Dimensions trop petites (min 100px)');
            }

            // Mettre à jour les dimensions
            $this->dimensions = [
                'width' => $newWidth,
                'height' => $newHeight
            ];

            // Mettre à jour les options
            $this->options['width'] = $newWidth;
            $this->options['height'] = $newHeight;

            error_log('PreviewRenderer: Dimensions définies à ' . $newWidth . 'x' . $newHeight . 'px');

            return true;

        } catch (\Exception $e) {
            error_log('PreviewRenderer: Erreur lors de la définition des dimensions - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retourne les dimensions actuelles du canvas
     *
     * @return array Dimensions [width, height]
     */
    public function getDimensions() {
        return $this->dimensions;
    }

    /**
     * Réinitialise les dimensions aux valeurs A4 par défaut
     *
     * @return bool True si réinitialisation réussie
     */
    public function resetToA4() {
        return $this->setDimensions(self::A4_WIDTH_PX, self::A4_HEIGHT_PX);
    }

    /**
     * Calcule les dimensions en pixels depuis des dimensions en mm
     *
     * @param float $widthMm Largeur en mm
     * @param float $heightMm Hauteur en mm
     * @param int $dpi DPI (par défaut 150)
     * @return array Dimensions en pixels [width, height]
     */
    public static function calculatePixelDimensions(float $widthMm, float $heightMm, int $dpi = 150) {
        // Utiliser la même logique que pour les constantes A4
        // A4_WIDTH_PX = 794 pour 210mm, donc facteur = 794/210 ≈ 3.781
        $factor = self::A4_WIDTH_PX / self::A4_WIDTH_MM;

        $widthPx = round($widthMm * $factor);
        $heightPx = round($heightMm * $factor);

        return [
            'width' => (int) $widthPx,
            'height' => (int) $heightPx
        ];
    }

    /**
     * Vérifie si le renderer est initialisé
     *
     * @return bool
     */
    public function isInitialized() {
        return $this->initialized;
    }

    /**
     * Retourne le mode actuel
     *
     * @return string
     */
    public function getMode() {
        return $this->mode;
    }

    /**
     * Retourne les options actuelles
     *
     * @return array
     */
    public function getOptions() {
        return $this->options;
    }
}

// Initialisation si nécessaire (pour tests)
if (function_exists('add_action')) {
    // Hook pour tests en ligne
    add_action('wp_ajax_pdf_test_renderer', function() {
        try {
            $renderer = new PreviewRenderer(['mode' => 'canvas']);
            $initResult = $renderer->init();

            wp_send_json_success([
                'message' => 'PreviewRenderer test réussi',
                'initialized' => $initResult,
                'mode' => $renderer->getMode(),
                'dimensions' => $renderer->getDimensions()
            ]);
        } catch (\Exception $e) {
            wp_send_json_error(['message' => 'Erreur test PreviewRenderer: ' . $e->getMessage()]);
        }
    });
}
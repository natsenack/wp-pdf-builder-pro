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
        // Dimensions par défaut (A4 à 150 DPI)
        $defaultWidth = 794;  // 210mm * 150 DPI / 25.4
        $defaultHeight = 1123; // 297mm * 150 DPI / 25.4

        return [
            'width' => $this->options['width'] ?? $defaultWidth,
            'height' => $this->options['height'] ?? $defaultHeight
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
     * Retourne les dimensions actuelles
     *
     * @return array
     */
    public function getDimensions() {
        return $this->dimensions;
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
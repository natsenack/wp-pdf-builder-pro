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
     * Niveau de zoom actuel (en pourcentage)
     * @var int
     */
    private $zoomLevel;

    /**
     * Niveaux de zoom autorisés
     * @var array
     */
    private $allowedZoomLevels;

    /**
     * Mode responsive activé
     * @var bool
     */
    private $responsive;

    /**
     * Dimensions du conteneur parent (pour responsive)
     * @var array|null
     */
    private $containerDimensions;

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

        // Initialiser le zoom
        $this->allowedZoomLevels = [50, 75, 100, 125, 150];
        $this->zoomLevel = $this->validateZoomLevel($this->options['zoom']);
        $this->responsive = $this->options['responsive'];
        $this->containerDimensions = null;

        // État initial
        $this->initialized = false;

        // Log d'initialisation
        error_log('PreviewRenderer: Instance créée avec mode=' . $this->mode . ', zoom=' . $this->zoomLevel . '%');
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
     * Valide et normalise un niveau de zoom
     *
     * @param int $zoom Niveau de zoom demandé
     * @return int Niveau de zoom validé
     * @throws \Exception Si zoom invalide
     */
    private function validateZoomLevel($zoom) {
        $zoom = (int) $zoom;

        if (!in_array($zoom, $this->allowedZoomLevels)) {
            // Trouver le niveau le plus proche
            $closest = null;
            $minDiff = PHP_INT_MAX;

            foreach ($this->allowedZoomLevels as $allowedZoom) {
                $diff = abs($zoom - $allowedZoom);
                if ($diff < $minDiff) {
                    $minDiff = $diff;
                    $closest = $allowedZoom;
                }
            }

            if ($closest !== null) {
                error_log('PreviewRenderer: Zoom ' . $zoom . '% ajusté au niveau autorisé le plus proche: ' . $closest . '%');
                return $closest;
            }

            throw new \Exception('Niveau de zoom invalide: ' . $zoom . '%. Niveaux autorisés: ' . implode(', ', $this->allowedZoomLevels));
        }

        return $zoom;
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

    /**
     * Définit le niveau de zoom
     *
     * @param int $zoom Niveau de zoom demandé (50, 75, 100, 125, 150)
     * @return bool True si zoom défini avec succès
     */
    public function setZoom(int $zoom) {
        try {
            $validatedZoom = $this->validateZoomLevel($zoom);

            if ($validatedZoom !== $this->zoomLevel) {
                $this->zoomLevel = $validatedZoom;
                error_log('PreviewRenderer: Zoom défini à ' . $this->zoomLevel . '%');
            }

            return true;

        } catch (\Exception $e) {
            error_log('PreviewRenderer: Erreur lors de la définition du zoom - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Retourne le niveau de zoom actuel
     *
     * @return int Niveau de zoom en pourcentage
     */
    public function getZoom() {
        return $this->zoomLevel;
    }

    /**
     * Retourne les niveaux de zoom autorisés
     *
     * @return array Niveaux de zoom autorisés
     */
    public function getAllowedZoomLevels() {
        return $this->allowedZoomLevels;
    }

    /**
     * Zoom avant (niveau supérieur)
     *
     * @return bool True si zoom augmenté avec succès
     */
    public function zoomIn() {
        $currentIndex = array_search($this->zoomLevel, $this->allowedZoomLevels);

        if ($currentIndex !== false && $currentIndex < count($this->allowedZoomLevels) - 1) {
            return $this->setZoom($this->allowedZoomLevels[$currentIndex + 1]);
        }

        return false; // Déjà au maximum
    }

    /**
     * Zoom arrière (niveau inférieur)
     *
     * @return bool True si zoom diminué avec succès
     */
    public function zoomOut() {
        $currentIndex = array_search($this->zoomLevel, $this->allowedZoomLevels);

        if ($currentIndex !== false && $currentIndex > 0) {
            return $this->setZoom($this->allowedZoomLevels[$currentIndex - 1]);
        }

        return false; // Déjà au minimum
    }

    /**
     * Active/désactive le mode responsive
     *
     * @param bool $responsive True pour activer le responsive
     * @return bool True si responsive défini avec succès
     */
    public function setResponsive(bool $responsive) {
        $this->responsive = $responsive;
        $this->options['responsive'] = $responsive;

        error_log('PreviewRenderer: Mode responsive ' . ($responsive ? 'activé' : 'désactivé'));

        return true;
    }

    /**
     * Vérifie si le mode responsive est activé
     *
     * @return bool True si responsive activé
     */
    public function isResponsive() {
        return $this->responsive;
    }

    /**
     * Définit les dimensions du conteneur parent (pour calculs responsive)
     *
     * @param int $width Largeur du conteneur
     * @param int $height Hauteur du conteneur
     * @return bool True si dimensions définies
     */
    public function setContainerDimensions(int $width, int $height) {
        if ($width <= 0 || $height <= 0) {
            error_log('PreviewRenderer: Dimensions de conteneur invalides');
            return false;
        }

        $this->containerDimensions = [
            'width' => $width,
            'height' => $height
        ];

        error_log('PreviewRenderer: Dimensions de conteneur définies à ' . $width . 'x' . $height);

        return true;
    }

    /**
     * Calcule les dimensions responsive en fonction du conteneur
     *
     * @return array|null Dimensions responsive [width, height] ou null si pas de conteneur
     */
    public function getResponsiveDimensions() {
        if (!$this->responsive || !$this->containerDimensions) {
            return null;
        }

        $canvasWidth = $this->dimensions['width'] * ($this->zoomLevel / 100);
        $canvasHeight = $this->dimensions['height'] * ($this->zoomLevel / 100);

        $containerWidth = $this->containerDimensions['width'];
        $containerHeight = $this->containerDimensions['height'];

        // Calculer le ratio pour s'adapter au conteneur
        $widthRatio = $containerWidth / $canvasWidth;
        $heightRatio = $containerHeight / $canvasHeight;
        $scale = min($widthRatio, $heightRatio, 1); // Ne pas agrandir, seulement réduire

        return [
            'width' => round($canvasWidth * $scale),
            'height' => round($canvasHeight * $scale),
            'scale' => $scale
        ];
    }

    /**
     * Calcule si des scrollbars sont nécessaires
     *
     * @return array État des scrollbars [horizontal, vertical]
     */
    public function getScrollbarState() {
        $responsive = $this->getResponsiveDimensions();

        if ($responsive) {
            // En mode responsive, pas de scrollbars nécessaires
            return [
                'horizontal' => false,
                'vertical' => false
            ];
        }

        // Dimensions avec zoom appliqué
        $zoomedWidth = $this->dimensions['width'] * ($this->zoomLevel / 100);
        $zoomedHeight = $this->dimensions['height'] * ($this->zoomLevel / 100);

        // Si pas de dimensions de conteneur, supposer scrollbars nécessaires pour zoom > 100%
        $needsHorizontal = $this->containerDimensions ?
            $zoomedWidth > $this->containerDimensions['width'] :
            $this->zoomLevel > 100;

        $needsVertical = $this->containerDimensions ?
            $zoomedHeight > $this->containerDimensions['height'] :
            $this->zoomLevel > 100;

        return [
            'horizontal' => $needsHorizontal,
            'vertical' => $needsVertical
        ];
    }

    /**
     * Rend un élément spécifique selon ses propriétés
     * Méthode appelée depuis CanvasElement.jsx pour rendre un élément
     *
     * @param array $elementData Données de l'élément (format CanvasElement)
     * @return array Résultat du rendu avec HTML, CSS, position, etc.
     */
    public function renderElement(array $elementData) {
        // Vérifier l'initialisation
        if (!$this->initialized) {
            error_log('PreviewRenderer: Tentative de rendu d\'élément sans initialisation');
            return [
                'success' => false,
                'error' => 'Renderer non initialisé'
            ];
        }

        try {
            // Validation des données d'élément
            if (!isset($elementData['type'])) {
                throw new \Exception('Type d\'élément manquant');
            }

            // Rendre l'élément selon son type
            $renderResult = $this->renderElementByType($elementData);

            // Appliquer le zoom aux dimensions et positions
            $renderResult = $this->applyZoomToElement($renderResult);

            // Calculer la position responsive si nécessaire
            if ($this->responsive && $this->containerDimensions) {
                $renderResult = $this->applyResponsivePositioning($renderResult);
            }

            return array_merge($renderResult, [
                'success' => true,
                'element_type' => $elementData['type'],
                'zoom_applied' => $this->zoomLevel,
                'responsive_applied' => $this->responsive
            ]);

        } catch (\Exception $e) {
            error_log('PreviewRenderer: Erreur rendu élément - ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'element_type' => $elementData['type'] ?? 'unknown'
            ];
        }
    }

    /**
     * Rend un élément selon son type spécifique
     *
     * @param array $elementData Données de l'élément
     * @return array Résultat du rendu
     */
    private function renderElementByType(array $elementData) {
        $type = $elementData['type'];

        switch ($type) {
            case 'text':
            case 'dynamic-text':
                return $this->renderTextElement($elementData);

            case 'rectangle':
                return $this->renderRectangleElement($elementData);

            case 'image':
            case 'company_logo':
                return $this->renderImageElement($elementData);

            case 'line':
                return $this->renderLineElement($elementData);

            default:
                // Élément non supporté pour l'instant
                return $this->renderUnsupportedElement($elementData);
        }
    }

    /**
     * Rend un élément texte
     */
    private function renderTextElement(array $elementData) {
        $text = $elementData['text'] ?? '';
        $fontSize = $elementData['fontSize'] ?? 12;
        $fontFamily = $elementData['fontFamily'] ?? 'Arial';
        $color = $elementData['color'] ?? '#000000';
        $bold = $elementData['bold'] ?? false;
        $italic = $elementData['italic'] ?? false;
        $underline = $elementData['underline'] ?? false;
        $align = $elementData['textAlign'] ?? 'left';

        // Styles CSS
        $styles = [
            'font-size' => $fontSize . 'px',
            'font-family' => $fontFamily,
            'color' => $color,
            'text-align' => $align,
            'position' => 'absolute',
            'left' => ($elementData['x'] ?? 0) . 'px',
            'top' => ($elementData['y'] ?? 0) . 'px',
            'width' => ($elementData['width'] ?? 100) . 'px',
            'height' => ($elementData['height'] ?? 50) . 'px',
            'overflow' => 'hidden'
        ];

        // Styles de police
        if ($bold) $styles['font-weight'] = 'bold';
        if ($italic) $styles['font-style'] = 'italic';
        if ($underline) $styles['text-decoration'] = 'underline';

        // Générer le CSS inline
        $cssString = '';
        foreach ($styles as $property => $value) {
            $cssString .= $property . ':' . $value . ';';
        }

        return [
            'html' => '<div style="' . htmlspecialchars($cssString, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . '</div>',
            'css' => $cssString,
            'x' => $elementData['x'] ?? 0,
            'y' => $elementData['y'] ?? 0,
            'width' => $elementData['width'] ?? 100,
            'height' => $elementData['height'] ?? 50
        ];
    }

    /**
     * Rend un élément rectangle
     */
    private function renderRectangleElement(array $elementData) {
        $fillColor = $elementData['fillColor'] ?? 'transparent';
        $borderWidth = $elementData['borderWidth'] ?? 1;
        $borderColor = $elementData['borderColor'] ?? '#000000';
        $borderStyle = $elementData['borderStyle'] ?? 'solid';
        $borderRadius = $elementData['borderRadius'] ?? 0;

        $styles = [
            'position' => 'absolute',
            'left' => ($elementData['x'] ?? 0) . 'px',
            'top' => ($elementData['y'] ?? 0) . 'px',
            'width' => ($elementData['width'] ?? 100) . 'px',
            'height' => ($elementData['height'] ?? 50) . 'px',
            'background-color' => $fillColor,
            'border' => $borderWidth . 'px ' . $borderStyle . ' ' . $borderColor,
            'border-radius' => $borderRadius . 'px'
        ];

        $cssString = '';
        foreach ($styles as $property => $value) {
            $cssString .= $property . ':' . $value . ';';
        }

        return [
            'html' => '<div style="' . htmlspecialchars($cssString, ENT_QUOTES, 'UTF-8') . '"></div>',
            'css' => $cssString,
            'x' => $elementData['x'] ?? 0,
            'y' => $elementData['y'] ?? 0,
            'width' => $elementData['width'] ?? 100,
            'height' => $elementData['height'] ?? 50
        ];
    }

    /**
     * Rend un élément image
     */
    private function renderImageElement(array $elementData) {
        $src = $elementData['src'] ?? $elementData['imageUrl'] ?? '';
        $alt = $elementData['alt'] ?? '';

        if (empty($src)) {
            return $this->renderUnsupportedElement($elementData);
        }

        $styles = [
            'position' => 'absolute',
            'left' => ($elementData['x'] ?? 0) . 'px',
            'top' => ($elementData['y'] ?? 0) . 'px',
            'width' => ($elementData['width'] ?? 100) . 'px',
            'height' => ($elementData['height'] ?? 50) . 'px',
            'object-fit' => 'contain'
        ];

        $cssString = '';
        foreach ($styles as $property => $value) {
            $cssString .= $property . ':' . $value . ';';
        }

        return [
            'html' => '<img src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($alt, ENT_QUOTES, 'UTF-8') . '" style="' . htmlspecialchars($cssString, ENT_QUOTES, 'UTF-8') . '" />',
            'css' => $cssString,
            'x' => $elementData['x'] ?? 0,
            'y' => $elementData['y'] ?? 0,
            'width' => $elementData['width'] ?? 100,
            'height' => $elementData['height'] ?? 50
        ];
    }

    /**
     * Rend un élément ligne
     */
    private function renderLineElement(array $elementData) {
        $strokeWidth = $elementData['strokeWidth'] ?? 1;
        $strokeColor = $elementData['strokeColor'] ?? '#000000';

        $styles = [
            'position' => 'absolute',
            'left' => ($elementData['x'] ?? 0) . 'px',
            'top' => ($elementData['y'] ?? 0) . 'px',
            'width' => ($elementData['width'] ?? 100) . 'px',
            'height' => ($elementData['height'] ?? 2) . 'px',
            'background-color' => $strokeColor
        ];

        $cssString = '';
        foreach ($styles as $property => $value) {
            $cssString .= $property . ':' . $value . ';';
        }

        return [
            'html' => '<div style="' . htmlspecialchars($cssString, ENT_QUOTES, 'UTF-8') . '"></div>',
            'css' => $cssString,
            'x' => $elementData['x'] ?? 0,
            'y' => $elementData['y'] ?? 0,
            'width' => $elementData['width'] ?? 100,
            'height' => $elementData['height'] ?? 2
        ];
    }

    /**
     * Rend un élément non supporté (placeholder)
     */
    private function renderUnsupportedElement(array $elementData) {
        $type = $elementData['type'] ?? 'unknown';

        $styles = [
            'position' => 'absolute',
            'left' => ($elementData['x'] ?? 0) . 'px',
            'top' => ($elementData['y'] ?? 0) . 'px',
            'width' => ($elementData['width'] ?? 100) . 'px',
            'height' => ($elementData['height'] ?? 50) . 'px',
            'background-color' => '#f3f4f6',
            'border' => '2px dashed #d1d5db',
            'display' => 'flex',
            'align-items' => 'center',
            'justify-content' => 'center',
            'font-size' => '12px',
            'color' => '#6b7280'
        ];

        $cssString = '';
        foreach ($styles as $property => $value) {
            $cssString .= $property . ':' . $value . ';';
        }

        return [
            'html' => '<div style="' . htmlspecialchars($cssString, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($type, ENT_QUOTES, 'UTF-8') . '</div>',
            'css' => $cssString,
            'x' => $elementData['x'] ?? 0,
            'y' => $elementData['y'] ?? 0,
            'width' => $elementData['width'] ?? 100,
            'height' => $elementData['height'] ?? 50
        ];
    }

    /**
     * Applique le zoom aux dimensions et positions d'un élément rendu
     */
    private function applyZoomToElement(array $renderResult) {
        if ($this->zoomLevel === 100) {
            return $renderResult;
        }

        $zoomFactor = $this->zoomLevel / 100;

        // Ajuster les dimensions
        $renderResult['width'] = round($renderResult['width'] * $zoomFactor);
        $renderResult['height'] = round($renderResult['height'] * $zoomFactor);

        // Ajuster la position
        $renderResult['x'] = round($renderResult['x'] * $zoomFactor);
        $renderResult['y'] = round($renderResult['y'] * $zoomFactor);

        // Mettre à jour le CSS
        $renderResult['css'] = preg_replace_callback(
            '/(width|height|left|top):\s*(\d+)px/',
            function($matches) use ($zoomFactor) {
                $property = $matches[1];
                $value = (int)$matches[2];
                $newValue = round($value * $zoomFactor);
                return $property . ':' . $newValue . 'px';
            },
            $renderResult['css']
        );

        return $renderResult;
    }

    /**
     * Applique le positionnement responsive à un élément rendu
     */
    private function applyResponsivePositioning(array $renderResult) {
        if (!$this->containerDimensions) {
            return $renderResult;
        }

        // Calculer les ratios
        $canvasWidth = $this->dimensions['width'];
        $canvasHeight = $this->dimensions['height'];
        $containerWidth = $this->containerDimensions['width'];
        $containerHeight = $this->containerDimensions['height'];

        $scaleX = $containerWidth / $canvasWidth;
        $scaleY = $containerHeight / $canvasHeight;
        $scale = min($scaleX, $scaleY); // Maintenir les proportions

        // Appliquer l'échelle
        $renderResult['x'] = round($renderResult['x'] * $scale);
        $renderResult['y'] = round($renderResult['y'] * $scale);
        $renderResult['width'] = round($renderResult['width'] * $scale);
        $renderResult['height'] = round($renderResult['height'] * $scale);

        // Mettre à jour le CSS
        $renderResult['css'] = preg_replace_callback(
            '/(width|height|left|top):\s*(\d+)px/',
            function($matches) use ($scale) {
                $property = $matches[1];
                $value = (int)$matches[2];
                $newValue = round($value * $scale);
                return $property . ':' . $newValue . 'px';
            },
            $renderResult['css']
        );

        return $renderResult;
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
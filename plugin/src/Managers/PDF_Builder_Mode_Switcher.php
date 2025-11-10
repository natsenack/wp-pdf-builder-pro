<?php

/**
 * Mode Switcher - Gestionnaire de basculement entre modes Canvas/Metabox
 *
 * @package PDF_Builder_Pro
 * @subpackage Managers
 */

namespace PDF_Builder\Managers;

/**
 * Classe PDF_Builder_Mode_Switcher
 *
 * Gère le basculement entre les modes Canvas (données fictives) et Metabox (données WooCommerce)
 * Implémente le pattern Strategy pour l'injection de dépendances
 */
class PdfBuilderModeSwitcher
{
    /**
     * Modes disponibles
     */
    const MODE_CANVAS = 'canvas';
    const MODE_METABOX = 'metabox';
/**
     * Mode actuel
     *
     * @var string
     */
    private string $currentMode = self::MODE_CANVAS;
/**
     * Instance du provider actuel
     *
     * @var mixed|null
     */
    private mixed $currentProvider = null;
/**
     * Instance du renderer
     *
     * @var mixed|null
     */
    private mixed $renderer = null;
/**
     * Cache des providers instanciés
     *
     * @var array
     */
    private array $providerCache = [];
/**
     * Constructeur
     *
     * @param string $initialMode Mode initial (canvas ou metabox)
     */
    public function __construct(string $initialMode = self::MODE_CANVAS)
    {
        $this->setMode($initialMode);
    }

    /**
     * Définit le mode actif
     *
     * @param string $mode Nouveau mode (canvas ou metabox)
     * @return bool True si le changement a réussi
     * @throws \InvalidArgumentException Si le mode est invalide
     */
    public function setMode(string $mode): bool
    {
        // Validation du mode
        if (!in_array($mode, [self::MODE_CANVAS, self::MODE_METABOX])) {
            throw new \InvalidArgumentException("Mode invalide: {$mode}. Modes valides: " . self::MODE_CANVAS . ", " . self::MODE_METABOX);
        }

        // Si le mode est déjà actif, pas de changement
        if ($this->currentMode === $mode && $this->currentProvider !== null) {
            return true;
        }

        // Créer ou récupérer le provider pour ce mode
        $provider = $this->getProviderForMode($mode);
// Mettre à jour l'état (le renderer recevra les données lors du rendu)
        $this->currentMode = $mode;
        $this->currentProvider = $provider;
        return true;
    }

    /**
     * Obtient le mode actuel
     *
     * @return string Mode actuel
     */
    public function getCurrentMode(): string
    {
        return $this->currentMode;
    }

    /**
     * Obtient le provider actuel
     *
     * @return DataProviderInterface|null Provider actuel
     */
    public function getCurrentProvider()
    {
        return $this->currentProvider;
    }

    /**
     * Définit le renderer pour l'injection de dépendances
     *
     * @param mixed $renderer Instance du renderer
     * @return self
     */
    public function setRenderer($renderer): self
    {
        $this->renderer = $renderer;
// Le renderer recevra les données lors des appels de rendu
        return $this;
    }

    /**
     * Bascule vers le mode Canvas
     *
     * @return bool True si le basculement a réussi
     */
    public function switchToCanvas(): bool
    {
        return $this->setMode(self::MODE_CANVAS);
    }

    /**
     * Bascule vers le mode Metabox
     *
     * @return bool True si le basculement a réussi
     */
    public function switchToMetabox(): bool
    {
        return $this->setMode(self::MODE_METABOX);
    }

    /**
     * Vérifie si le mode actuel est Canvas
     *
     * @return bool True si mode Canvas
     */
    public function isCanvasMode(): bool
    {
        return $this->currentMode === self::MODE_CANVAS;
    }

    /**
     * Vérifie si le mode actuel est Metabox
     *
     * @return bool True si mode Metabox
     */
    public function isMetaboxMode(): bool
    {
        return $this->currentMode === self::MODE_METABOX;
    }

    /**
     * Obtient le provider approprié pour un mode
     *
     * Utilise le cache pour éviter de recréer les instances
     *
     * @param string $mode Mode demandé
     * @return mixed Instance du provider
     */
    private function getProviderForMode(string $mode)
    {
        // Vérifier le cache
        if (isset($this->providerCache[$mode])) {
            return $this->providerCache[$mode];
        }

        // Créer le provider selon le mode
        switch ($mode) {
            case self::MODE_CANVAS:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $provider = [];

                break;
            case self::MODE_METABOX:
                // Pour Metabox, initialiser avec null

                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    $provider = [];

                break;
            default:
                throw new \InvalidArgumentException("Mode non supporté: {$mode}");
        }

        // Mettre en cache
        $this->providerCache[$mode] = $provider;
        return $provider;
    }

    /**
     * Injecte une commande WooCommerce dans le provider Metabox
     *
     * @param mixed $order Commande WooCommerce
     * @return self
     */
    public function injectMetaboxOrder($order): self
    {
        if ($this->currentMode === self::MODE_METABOX) {
            $this->providerCache[self::MODE_METABOX] = $order;
            $this->currentProvider = $order;
        }

        return $this;
    }

    /**
     * Nettoie le cache des providers
     *
     * @return self
     */
    public function clearCache(): self
    {
        $this->providerCache = [];
        return $this;
    }

    /**
     * Initialise le mode
     *
     * @param array $context Contexte d'initialisation
     * @return bool True si l'initialisation a réussi
     */
    public function initialize(array $context = []): bool
    {
        return !empty($this->currentMode);
    }

    /**
     * Récupère le nom du mode actuel
     *
     * @return string Nom du mode
     */
    public function getModeName(): string
    {
        return $this->currentMode;
    }

    /**
     * Vérifie si le mode est actif
     *
     * @return bool True si le mode est actif
     */
    public function isActive(): bool
    {
        return $this->currentProvider !== null;
    }

    /**
     * Récupère les données du provider actuel
     *
     * @param array $context Contexte de récupération
     * @return array Données formatées
     */
    public function getData(array $context = []): array
    {
        if ($this->currentProvider === null) {
            return [];
        }

        return [
            'mode' => $this->currentMode,
            'data' => $this->currentProvider
        ];
    }

    /**
     * Valide les données selon le mode actuel
     *
     * @param array $data Données à valider
     * @return array Résultat de validation
     */
    public function validateData(array $data): array
    {
        if ($this->currentProvider === null) {
            return ['valid' => false, 'errors' => ['Aucun provider actif']];
        }

        return ['valid' => true, 'errors' => []];
    }

    /**
     * Nettoie les ressources
     *
     * @return void
     */
    public function cleanup(): void
    {
        $this->clearCache();
        $this->currentProvider = null;
        $this->renderer = null;
    }

    /**
     * Récupère les options de configuration
     *
     * @return array Options de configuration
     */
    public function getOptions(): array
    {
        return [
            'current_mode' => $this->currentMode,
            'has_provider' => $this->currentProvider !== null,
            'has_renderer' => $this->renderer !== null,
            'cache_enabled' => true,
        ];
    }

    /**
     * Définit une option de configuration
     *
     * @param string $key Clé de l'option
     * @param mixed $value Valeur de l'option
     * @return bool True si option définie avec succès
     */
    public function setOption(string $key, $value): bool
    {
        return false;
    }

    /**
     * Détruit le mode et nettoie les ressources
     *
     * @return bool True si la destruction a réussi
     */
    public function destroy(): bool
    {
        $this->cleanup();
        return true;
    }

    /**
     * Valide la compatibilité du mode avec le contexte
     *
     * @param array $context Contexte à valider
     * @return bool True si compatible
     */
    public function validateContext(array $context = []): bool
    {
        // Pour Canvas, toujours valide
        if ($this->currentMode === self::MODE_CANVAS) {
            return true;
        }

        // Pour Metabox, vérifier qu'on a une commande WooCommerce
        if ($this->currentMode === self::MODE_METABOX) {
            return isset($context['order']) || $this->currentProvider !== null;
        }

        return false;
    }
}

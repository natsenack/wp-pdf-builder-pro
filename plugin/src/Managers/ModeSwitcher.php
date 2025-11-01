<?php
/**
 * Mode Switcher - Gestionnaire de basculement entre modes Canvas/Metabox
 *
 * @package PDF_Builder_Pro
 * @subpackage Managers
 */

namespace PDF_Builder_Pro\Managers;

use PDF_Builder_Pro\Interfaces\ModeInterface;
use PDF_Builder_Pro\Interfaces\DataProviderInterface;
use PDF_Builder_Pro\Interfaces\PreviewRendererInterface;
use PDF_Builder_Pro\Providers\CanvasModeProvider;
use PDF_Builder_Pro\Providers\MetaboxModeProvider;

/**
 * Classe ModeSwitcher
 *
 * Gère le basculement entre les modes Canvas (données fictives) et Metabox (données WooCommerce)
 * Implémente le pattern Strategy pour l'injection de dépendances
 */
class ModeSwitcher implements ModeInterface
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
     * @var DataProviderInterface|null
     */
    private ?DataProviderInterface $currentProvider = null;

    /**
     * Instance du renderer
     *
     * @var PreviewRendererInterface|null
     */
    private ?PreviewRendererInterface $renderer = null;

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
    public function getCurrentProvider(): ?DataProviderInterface
    {
        return $this->currentProvider;
    }

    /**
     * Définit le renderer pour l'injection de dépendances
     *
     * @param PreviewRendererInterface $renderer Instance du renderer
     * @return self
     */
    public function setRenderer(PreviewRendererInterface $renderer): self
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
     * @return DataProviderInterface Instance du provider
     */
    private function getProviderForMode(string $mode): DataProviderInterface
    {
        // Vérifier le cache
        if (isset($this->providerCache[$mode])) {
            return $this->providerCache[$mode];
        }

        // Créer le provider selon le mode
        switch ($mode) {
            case self::MODE_CANVAS:
                $provider = new CanvasModeProvider();
                break;

            case self::MODE_METABOX:
                // Pour Metabox, on aura besoin d'une commande WooCommerce
                // Pour l'instant, créer avec null (sera injecté plus tard)
                $provider = new MetaboxModeProvider(null);
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
     * Utile pour les tests ou l'injection dynamique
     *
     * @param mixed $order Commande WooCommerce (WC_Order ou mock)
     * @return self
     */
    public function injectMetaboxOrder($order): self
    {
        if ($this->currentMode === self::MODE_METABOX) {
            // Créer un nouveau provider avec la commande
            $provider = new MetaboxModeProvider($order);
            $this->providerCache[self::MODE_METABOX] = $provider;
            $this->currentProvider = $provider;

            // Le renderer recevra les données lors des appels de rendu
        }

        return $this;
    }

    /**
     * Nettoie le cache des providers
     *
     * Utile pour les tests ou le rechargement forcé
     *
     * @return self
     */
    public function clearCache(): self
    {
        $this->providerCache = [];
        return $this;
    }

    // Implémentation de l'interface ModeInterface

    /**
     * Initialise le mode
     *
     * @param array $context Contexte d'initialisation
     * @return bool True si l'initialisation a réussi
     */
    public function initialize(array $context = []): bool
    {
        // Pour l'instant, juste valider que le mode est défini
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

        // Selon le mode, appeler la bonne méthode du provider
        switch ($this->currentMode) {
            case self::MODE_CANVAS:
                return [
                    'customer' => $this->currentProvider->getCustomerData($context),
                    'order' => $this->currentProvider->getOrderData($context),
                    'company' => $this->currentProvider->getCompanyData($context),
                    'system' => $this->currentProvider->getSystemData($context),
                ];

            case self::MODE_METABOX:
                return [
                    'customer' => $this->currentProvider->getCustomerData($context),
                    'order' => $this->currentProvider->getOrderData($context),
                    'company' => $this->currentProvider->getCompanyData($context),
                    'system' => $this->currentProvider->getSystemData($context),
                ];

            default:
                return [];
        }
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

        return $this->currentProvider->checkDataCompleteness($data);
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
        // Pour l'instant, pas d'options configurables
        // Peut être étendu plus tard
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
            return isset($context['order']) || $this->currentProvider instanceof MetaboxModeProvider;
        }

        return false;
    }
}
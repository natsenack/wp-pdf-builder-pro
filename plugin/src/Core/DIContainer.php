<?php
/**
 * Dependency Injection Container - Conteneur d'injection de dépendances
 *
 * @package PDF_Builder
 * @subpackage Core
 */

namespace PDF_Builder\Core;

/**
 * Classe DIContainer
 *
 * Conteneur d'injection de dépendances simple pour gérer les instances
 * et leurs dépendances dans l'architecture modulaire
 */
class DIContainer
{
    /**
     * Instances enregistrées
     *
     * @var array
     */
    private array $instances = [];

    /**
     * Définitions des services
     *
     * @var array
     */
    private array $definitions = [];

    /**
     * Instances partagées (singletons)
     *
     * @var array
     */
    private array $shared = [];

    /**
     * Enregistre une définition de service
     *
     * @param string $name Nom du service
     * @param callable $definition Fonction de création du service
     * @param bool $shared Si true, instance partagée (singleton)
     * @return self
     */
    public function set(string $name, callable $definition, bool $shared = false): self
    {
        $this->definitions[$name] = $definition;

        if ($shared) {
            $this->shared[$name] = true;
        }

        return $this;
    }

    /**
     * Enregistre une instance directe
     *
     * @param string $name Nom du service
     * @param mixed $instance Instance à enregistrer
     * @return self
     */
    public function setInstance(string $name, $instance): self
    {
        $this->instances[$name] = $instance;
        return $this;
    }

    /**
     * Obtient une instance de service
     *
     * @param string $name Nom du service
     * @return mixed Instance du service
     * @throws \Exception Si le service n'existe pas
     */
    public function get(string $name)
    {
        // Retourner l'instance directe si elle existe
        if (isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Vérifier si c'est un singleton déjà créé
        if (isset($this->shared[$name]) && isset($this->instances[$name])) {
            return $this->instances[$name];
        }

        // Vérifier si on a une définition
        if (!isset($this->definitions[$name])) {
            throw new \Exception("Service non défini: {$name}");
        }

        // Créer l'instance
        $instance = call_user_func($this->definitions[$name], $this);

        // Stocker si c'est un singleton
        if (isset($this->shared[$name])) {
            $this->instances[$name] = $instance;
        }

        return $instance;
    }

    /**
     * Vérifie si un service existe
     *
     * @param string $name Nom du service
     * @return bool True si le service existe
     */
    public function has(string $name): bool
    {
        return isset($this->instances[$name]) || isset($this->definitions[$name]);
    }

    /**
     * Supprime un service
     *
     * @param string $name Nom du service
     * @return self
     */
    public function remove(string $name): self
    {
        unset($this->instances[$name]);
        unset($this->definitions[$name]);
        unset($this->shared[$name]);

        return $this;
    }

    /**
     * Nettoie toutes les instances (sauf les singletons)
     *
     * @return self
     */
    public function clear(): self
    {
        foreach ($this->instances as $name => $instance) {
            if (!isset($this->shared[$name])) {
                unset($this->instances[$name]);
            }
        }

        return $this;
    }

    /**
     * Nettoie tout (instances et définitions)
     *
     * @return self
     */
    public function clearAll(): self
    {
        $this->instances = [];
        $this->definitions = [];
        $this->shared = [];

        return $this;
    }

    /**
     * Obtient la liste des services disponibles
     *
     * @return array Liste des noms de services
     */
    public function getServices(): array
    {
        return array_unique(array_merge(
            array_keys($this->instances),
            array_keys($this->definitions)
        ));
    }

    // Méthodes utilitaires pour l'injection de dépendances courantes

    /**
     * Enregistre le ModeSwitcher
     *
     * @param string $initialMode Mode initial
     * @return self
     */
    public function registerModeSwitcher(string $initialMode = 'canvas'): self
    {
        return $this->set('mode_switcher', function () use ($initialMode) {
            return new \PDF_Builder\Managers\ModeSwitcher($initialMode);
        }, true); // Singleton
    }

    /**
     * Enregistre le PreviewRenderer
     *
     * @param array $options Options du renderer
     * @return self
     */
    public function registerPreviewRenderer(array $options = []): self
    {
        return $this->set('preview_renderer', function ($container) use ($options) {
            // Note: PreviewRenderer sera injecté plus tard quand disponible
            // Pour l'instant, retourner null ou une implémentation mock
            return null;
        }, true); // Singleton
    }

    /**
     * Enregistre les providers de données
     *
     * @return self
     */
    public function registerDataProviders(): self
    {
        // CanvasModeProvider
        $this->set('canvas_provider', function () {
            return new \PDF_Builder_Pro\Providers\CanvasModeProvider();
        });

        // MetaboxModeProvider (factory pour permettre l'injection d'ordre)
        $this->set('metabox_provider_factory', function () {
            return function ($order = null) {
                return new \PDF_Builder_Pro\Providers\MetaboxModeProvider($order);
            };
        });

        return $this;
    }

    /**
     * Configure tous les services par défaut
     *
     * @param string $initialMode Mode initial
     * @return self
     */
    public function configureDefaults(string $initialMode = 'canvas'): self
    {
        return $this
            ->registerDataProviders()
            ->registerModeSwitcher($initialMode)
            ->registerPreviewRenderer(['mode' => $initialMode]);
    }
}
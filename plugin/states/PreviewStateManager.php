<?php
namespace WP_PDF_Builder_Pro\States;

/**
 * Interface PreviewStateInterface
 * Définit le contrat pour les états d'aperçu
 */
interface PreviewStateInterface {

    /**
     * Récupère le nom de l'état
     *
     * @return string Nom de l'état
     */
    public function getName(): string;

    /**
     * Vérifie si l'état permet certaines actions
     *
     * @param string $action Action à vérifier
     * @return bool true si l'action est autorisée
     */
    public function canPerformAction(string $action): bool;

    /**
     * Exécute une transition vers un nouvel état
     *
     * @param string $new_state Nouvel état souhaité
     * @return bool true si la transition est autorisée
     */
    public function canTransitionTo(string $new_state): bool;

    /**
     * Récupère les actions disponibles dans cet état
     *
     * @return array Liste des actions disponibles
     */
    public function getAvailableActions(): array;

    /**
     * Récupère le message d'état pour l'utilisateur
     *
     * @return string Message d'état
     */
    public function getUserMessage(): string;
}

/**
 * Classe PreviewStateManager
 * Gère les états du système d'aperçu avec transitions fluides
 */
class PreviewStateManager {

    /** @var string État actuel */
    private $current_state;

    /** @var array Historique des états */
    private $state_history = [];

    /** @var array Callbacks pour les changements d'état */
    private $state_change_callbacks = [];

    /** @var array Configuration des états */
    private $states_config;

    /**
     * Constructeur
     */
    public function __construct() {
        $this->initializeStatesConfig();
        $this->current_state = 'idle';
        $this->logStateChange('idle', 'Initial state');
    }

    /**
     * Initialise la configuration des états
     */
    private function initializeStatesConfig(): void {
        $this->states_config = [
            'idle' => [
                'name' => 'idle',
                'label' => 'Prêt',
                'description' => 'Système prêt à générer un aperçu',
                'allowed_actions' => ['start_generation', 'load_template'],
                'allowed_transitions' => ['loading', 'initializing'],
                'user_message' => 'Prêt à générer un aperçu',
                'icon' => 'check-circle',
                'color' => 'green'
            ],
            'initializing' => [
                'name' => 'initializing',
                'label' => 'Initialisation',
                'description' => 'Préparation du système de génération',
                'allowed_actions' => ['cancel'],
                'allowed_transitions' => ['loading', 'error'],
                'user_message' => 'Initialisation en cours...',
                'icon' => 'spinner',
                'color' => 'blue'
            ],
            'loading' => [
                'name' => 'loading',
                'label' => 'Chargement',
                'description' => 'Chargement des données et ressources',
                'allowed_actions' => ['cancel'],
                'allowed_transitions' => ['generating', 'error'],
                'user_message' => 'Chargement des données...',
                'icon' => 'spinner',
                'color' => 'blue'
            ],
            'generating' => [
                'name' => 'generating',
                'label' => 'Génération',
                'description' => 'Génération de l\'aperçu en cours',
                'allowed_actions' => ['cancel'],
                'allowed_transitions' => ['ready', 'error'],
                'user_message' => 'Génération de l\'aperçu...',
                'icon' => 'cog',
                'color' => 'orange'
            ],
            'ready' => [
                'name' => 'ready',
                'label' => 'Terminé',
                'description' => 'Aperçu généré avec succès',
                'allowed_actions' => ['download', 'regenerate', 'close'],
                'allowed_transitions' => ['idle', 'generating'],
                'user_message' => 'Aperçu prêt !',
                'icon' => 'check-circle',
                'color' => 'green'
            ],
            'error' => [
                'name' => 'error',
                'label' => 'Erreur',
                'description' => 'Une erreur s\'est produite lors de la génération',
                'allowed_actions' => ['retry', 'close'],
                'allowed_transitions' => ['idle', 'loading'],
                'user_message' => 'Erreur lors de la génération',
                'icon' => 'exclamation-triangle',
                'color' => 'red'
            ],
            'cancelled' => [
                'name' => 'cancelled',
                'label' => 'Annulé',
                'description' => 'Génération annulée par l\'utilisateur',
                'allowed_actions' => ['restart', 'close'],
                'allowed_transitions' => ['idle'],
                'user_message' => 'Génération annulée',
                'icon' => 'ban',
                'color' => 'gray'
            ]
        ];
    }

    /**
     * Change l'état actuel
     *
     * @param string $new_state Nouvel état
     * @param string $reason Raison du changement
     * @param array $metadata Métadonnées supplémentaires
     * @return bool true si le changement a réussi
     */
    public function changeState(string $new_state, string $reason = '', array $metadata = []): bool {
        if (!isset($this->states_config[$new_state])) {
            $this->logError("Unknown state: {$new_state}");
            return false;
        }

        if (!$this->canTransitionTo($new_state)) {
            $this->logWarning("Invalid transition from {$this->current_state} to {$new_state}");
            return false;
        }

        $old_state = $this->current_state;
        $this->current_state = $new_state;

        // Ajouter à l'historique
        $this->state_history[] = [
            'from' => $old_state,
            'to' => $new_state,
            'timestamp' => time(),
            'reason' => $reason,
            'metadata' => $metadata
        ];

        // Limiter l'historique à 50 entrées
        if (count($this->state_history) > 50) {
            array_shift($this->state_history);
        }

        $this->logStateChange($new_state, $reason, $metadata);

        // Déclencher les callbacks
        $this->triggerStateChangeCallbacks($old_state, $new_state, $reason, $metadata);

        return true;
    }

    /**
     * Vérifie si une transition est possible
     *
     * @param string $new_state État cible
     * @return bool true si la transition est autorisée
     */
    public function canTransitionTo(string $new_state): bool {
        if (!isset($this->states_config[$this->current_state])) {
            return false;
        }

        $current_config = $this->states_config[$this->current_state];
        return in_array($new_state, $current_config['allowed_transitions']);
    }

    /**
     * Vérifie si une action est autorisée dans l'état actuel
     *
     * @param string $action Action à vérifier
     * @return bool true si l'action est autorisée
     */
    public function canPerformAction(string $action): bool {
        if (!isset($this->states_config[$this->current_state])) {
            return false;
        }

        $current_config = $this->states_config[$this->current_state];
        return in_array($action, $current_config['allowed_actions']);
    }

    /**
     * Récupère l'état actuel
     *
     * @return string État actuel
     */
    public function getCurrentState(): string {
        return $this->current_state;
    }

    /**
     * Récupère la configuration de l'état actuel
     *
     * @return array Configuration de l'état
     */
    public function getCurrentStateConfig(): array {
        return $this->states_config[$this->current_state] ?? [];
    }

    /**
     * Récupère les actions disponibles
     *
     * @return array Actions disponibles
     */
    public function getAvailableActions(): array {
        $config = $this->getCurrentStateConfig();
        return $config['allowed_actions'] ?? [];
    }

    /**
     * Récupère le message utilisateur pour l'état actuel
     *
     * @return string Message utilisateur
     */
    public function getUserMessage(): string {
        $config = $this->getCurrentStateConfig();
        return $config['user_message'] ?? '';
    }

    /**
     * Récupère l'historique des états
     *
     * @return array Historique des états
     */
    public function getStateHistory(): array {
        return $this->state_history;
    }

    /**
     * Enregistre un callback pour les changements d'état
     *
     * @param callable $callback Fonction à appeler lors des changements d'état
     */
    public function onStateChange(callable $callback): void {
        $this->state_change_callbacks[] = $callback;
    }

    /**
     * Déclenche les callbacks de changement d'état
     *
     * @param string $old_state Ancien état
     * @param string $new_state Nouvel état
     * @param string $reason Raison
     * @param array $metadata Métadonnées
     */
    private function triggerStateChangeCallbacks(string $old_state, string $new_state, string $reason, array $metadata): void {
        foreach ($this->state_change_callbacks as $callback) {
            try {
                $callback($old_state, $new_state, $reason, $metadata);
            } catch (\Throwable $e) {
                $this->logError("State change callback failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Force un état (usage interne uniquement)
     *
     * @param string $state État à forcer
     * @param string $reason Raison
     */
    public function forceState(string $state, string $reason = 'Forced'): void {
        $old_state = $this->current_state;
        $this->current_state = $state;

        $this->state_history[] = [
            'from' => $old_state,
            'to' => $state,
            'timestamp' => time(),
            'reason' => $reason,
            'forced' => true
        ];

        $this->logWarning("State forced from {$old_state} to {$state}: {$reason}");
    }

    /**
     * Réinitialise le gestionnaire d'état
     */
    public function reset(): void {
        $this->current_state = 'idle';
        $this->state_history = [];
        $this->state_change_callbacks = [];
        $this->logInfo("State manager reset to idle state");
    }

    /**
     * Vérifie si le système est dans un état d'erreur
     *
     * @return bool true si en erreur
     */
    public function isInErrorState(): bool {
        return $this->current_state === 'error';
    }

    /**
     * Vérifie si le système est occupé (chargement/génération)
     *
     * @return bool true si occupé
     */
    public function isBusy(): bool {
        return in_array($this->current_state, ['initializing', 'loading', 'generating']);
    }

    /**
     * Récupère les statistiques d'utilisation des états
     *
     * @return array Statistiques des états
     */
    public function getStateStats(): array {
        $stats = [];
        foreach ($this->state_history as $entry) {
            $state = $entry['to'];
            if (!isset($stats[$state])) {
                $stats[$state] = 0;
            }
            $stats[$state]++;
        }
        return $stats;
    }

    /**
     * Log un changement d'état
     *
     * @param string $new_state Nouvel état
     * @param string $reason Raison
     * @param array $metadata Métadonnées
     */
    private function logStateChange(string $new_state, string $reason = '', array $metadata = []): void {
        $message = "[State Change] {$this->current_state} -> {$new_state}";
        if (!empty($reason)) {
            $message .= " ({$reason})";
        }
        error_log($message);
    }

    /**
     * Log une erreur
     *
     * @param string $message Message d'erreur
     */
    private function logError(string $message): void {
        error_log("[StateManager Error] {$message}");
    }

    /**
     * Log un avertissement
     *
     * @param string $message Message d'avertissement
     */
    private function logWarning(string $message): void {
        error_log("[StateManager Warning] {$message}");
    }

    /**
     * Log une information
     *
     * @param string $message Message d'information
     */
    private function logInfo(string $message): void {
        error_log("[StateManager Info] {$message}");
    }
}
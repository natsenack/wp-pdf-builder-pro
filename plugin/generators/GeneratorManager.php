<?php
namespace WP_PDF_Builder_Pro\Generators;

use WP_PDF_Builder_Pro\Interfaces\DataProviderInterface;

/**
 * Classe GeneratorManager
 * Gère le système double générateur avec fallback automatique
 */
class GeneratorManager {

    /** @var array Configuration des générateurs */
    private $generators_config;

    /** @var string Générateur primaire */
    private $primary_generator;

    /** @var string Générateur secondaire (fallback) */
    private $secondary_generator;

    /** @var string Générateur tertiaire (fallback final) */
    private $tertiary_generator;

    /** @var array Historique des tentatives */
    private $attempt_history = [];

    /**
     * Constructeur
     */
    public function __construct() {
        $this->initializeGeneratorsConfig();
        $this->primary_generator = 'dompdf';
        $this->secondary_generator = 'canvas';
        $this->tertiary_generator = 'image'; // Nouveau fallback
    }

    /**
     * Initialise la configuration des générateurs
     */
    private function initializeGeneratorsConfig(): void {
        $this->generators_config = [
            'dompdf' => [
                'class' => 'WP_PDF_Builder_Pro\\Generators\\PDFGenerator',
                'priority' => 1,
                'enabled' => true,
                'max_attempts' => 2,
                'timeout' => 30,
                'supported_formats' => ['pdf', 'png', 'jpg'],
                'capabilities' => ['high_quality', 'vector_graphics', 'fonts']
            ],
            'canvas' => [
                'class' => 'WP_PDF_Builder_Pro\\Generators\\CanvasGenerator',
                'priority' => 2,
                'enabled' => true,
                'max_attempts' => 3,
                'timeout' => 15,
                'supported_formats' => ['png', 'jpg'],
                'capabilities' => ['fast', 'client_side', 'fallback']
            ],
            'image' => [
                'class' => 'WP_PDF_Builder_Pro\\Generators\\ImageGenerator',
                'priority' => 3,
                'enabled' => true,
                'max_attempts' => 1,
                'timeout' => 10,
                'supported_formats' => ['png', 'jpg'],
                'capabilities' => ['basic', 'fast', 'always_works']
            ]
        ];
    }

    /**
     * Génère un aperçu avec fallback automatique
     *
     * @param array $template_data Données du template
     * @param DataProviderInterface $data_provider Fournisseur de données
     * @param string $output_format Format de sortie souhaité
     * @param array $options Options supplémentaires
     * @return mixed Résultat de la génération ou false en cas d'échec total
     */
    public function generatePreview(array $template_data, DataProviderInterface $data_provider, string $output_format = 'pdf', array $options = []) {
        $this->attempt_history = [];
        $result = false;

        // Tentative avec le générateur primaire
        $result = $this->attemptGeneration($this->primary_generator, $template_data, $data_provider, $output_format, $options);

        // Si échec, tentative avec le générateur secondaire
        if ($result === false) {
            $this->logInfo("Primary generator failed, trying secondary generator");
            $result = $this->attemptGeneration($this->secondary_generator, $template_data, $data_provider, $output_format, $options);
        }

        // Si échec, tentative avec le générateur tertiaire (fallback final)
        if ($result === false) {
            $this->logInfo("Secondary generator failed, trying tertiary generator");
            $result = $this->attemptGeneration($this->tertiary_generator, $template_data, $data_provider, $output_format, $options);
        }

        // Log du résultat final
        if ($result === false) {
            $this->logError("All generators failed for format: {$output_format}");
        } else {
            $this->logInfo("Generation successful with format: {$output_format}");
        }

        return $result;
    }

    /**
     * Tente la génération avec un générateur spécifique
     *
     * @param string $generator_type Type de générateur
     * @param array $template_data Données du template
     * @param DataProviderInterface $data_provider Fournisseur de données
     * @param string $output_format Format de sortie
     * @param array $options Options supplémentaires
     * @return mixed Résultat ou false en cas d'échec
     */
    private function attemptGeneration(string $generator_type, array $template_data, DataProviderInterface $data_provider, string $output_format, array $options = []) {
        if (!isset($this->generators_config[$generator_type])) {
            $this->logError("Unknown generator type: {$generator_type}");
            return false;
        }

        $config = $this->generators_config[$generator_type];

        if (!$config['enabled']) {
            $this->logInfo("Generator {$generator_type} is disabled");
            return false;
        }

        if (!in_array($output_format, $config['supported_formats'])) {
            $this->logInfo("Generator {$generator_type} does not support format: {$output_format}");
            return false;
        }

        $this->attempt_history[] = [
            'generator' => $generator_type,
            'format' => $output_format,
            'timestamp' => time(),
            'status' => 'attempting'
        ];

        try {
            $generator = $this->createGenerator($generator_type, $template_data, $data_provider, $options);

            if (!$generator) {
                throw new \Exception("Failed to create generator: {$generator_type}");
            }

            // Validation du template
            if (!$generator->validateTemplate()) {
                throw new \Exception("Template validation failed for generator: {$generator_type}");
            }

            // Génération avec timeout
            $result = $this->executeWithTimeout(
                function() use ($generator, $output_format) {
                    return $generator->generate($output_format);
                },
                $config['timeout']
            );

            // Mise à jour de l'historique
            $this->attempt_history[count($this->attempt_history) - 1]['status'] = 'success';

            return $result;

        } catch (\Throwable $e) {
            $this->logError("Generator {$generator_type} failed: " . $e->getMessage());

            // Mise à jour de l'historique
            $this->attempt_history[count($this->attempt_history) - 1]['status'] = 'failed';
            $this->attempt_history[count($this->attempt_history) - 1]['error'] = $e->getMessage();

            return false;
        }
    }

    /**
     * Crée une instance de générateur
     *
     * @param string $generator_type Type de générateur
     * @param array $template_data Données du template
     * @param DataProviderInterface $data_provider Fournisseur de données
     * @param array $options Options supplémentaires
     * @return BaseGenerator|null Instance du générateur ou null
     */
    private function createGenerator(string $generator_type, array $template_data, DataProviderInterface $data_provider, array $options = []): ?BaseGenerator {
        $config = $this->generators_config[$generator_type];
        $class_name = $config['class'];

        if (!class_exists($class_name)) {
            $this->logError("Generator class not found: {$class_name}");
            return null;
        }

        try {
            return new $class_name($template_data, $data_provider, true, $options);
        } catch (\Throwable $e) {
            $this->logError("Failed to instantiate generator {$class_name}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Exécute une fonction avec timeout
     *
     * @param callable $callback Fonction à exécuter
     * @param int $timeout Timeout en secondes
     * @return mixed Résultat de la fonction
     * @throws \Exception En cas de timeout
     */
    private function executeWithTimeout(callable $callback, int $timeout) {
        $start_time = time();

        // Utilisation de pcntl si disponible (Linux/Unix)
        if (function_exists('pcntl_fork')) {
            return $this->executeWithPcntl($callback, $timeout);
        }

        // Fallback basique sans timeout réel
        $result = $callback();

        if ((time() - $start_time) > $timeout) {
            throw new \Exception("Operation timed out after {$timeout} seconds");
        }

        return $result;
    }

    /**
     * Exécute avec pcntl pour timeout réel
     *
     * @param callable $callback Fonction à exécuter
     * @param int $timeout Timeout en secondes
     * @return mixed Résultat de la fonction
     * @throws \Exception En cas de timeout ou d'erreur
     */
    private function executeWithPcntl(callable $callback, int $timeout) {
        $pid = pcntl_fork();

        if ($pid == -1) {
            throw new \Exception("Failed to fork process");
        }

        if ($pid) {
            // Process parent
            $status = 0;
            $result = pcntl_waitpid($pid, $status, WNOHANG);

            $elapsed = 0;
            while ($result === 0 && $elapsed < $timeout) {
                sleep(1);
                $elapsed++;
                $result = pcntl_waitpid($pid, $status, WNOHANG);
            }

            if ($result === 0) {
                // Timeout - tuer le processus enfant
                posix_kill($pid, SIGKILL);
                pcntl_waitpid($pid, $status);
                throw new \Exception("Process timed out after {$timeout} seconds");
            }

            if (pcntl_wifexited($status) && pcntl_wexitstatus($status) === 0) {
                // Succès - récupérer le résultat depuis un fichier temporaire ou pipe
                return $this->getResultFromChildProcess($pid);
            } else {
                throw new \Exception("Child process failed");
            }
        } else {
            // Process enfant
            try {
                $result = $callback();
                $this->saveResultForParent($result);
                exit(0);
            } catch (\Throwable $e) {
                error_log("Child process error: " . $e->getMessage());
                exit(1);
            }
        }
    }

    /**
     * Récupère le résultat depuis le processus enfant
     *
     * @param int $pid PID du processus enfant
     * @return mixed Résultat
     */
    private function getResultFromChildProcess(int $pid) {
        $temp_file = sys_get_temp_dir() . "/pdf_generator_result_{$pid}.tmp";

        if (file_exists($temp_file)) {
            $result = unserialize(file_get_contents($temp_file));
            unlink($temp_file);
            return $result;
        }

        return null;
    }

    /**
     * Sauvegarde le résultat pour le processus parent
     *
     * @param mixed $result Résultat à sauvegarder
     */
    private function saveResultForParent($result): void {
        $temp_file = sys_get_temp_dir() . "/pdf_generator_result_" . getmypid() . ".tmp";
        file_put_contents($temp_file, serialize($result));
    }

    /**
     * Vérifie si un générateur est disponible
     *
     * @param string $generator_type Type de générateur
     * @return bool true si disponible
     */
    public function isGeneratorAvailable(string $generator_type): bool {
        if (!isset($this->generators_config[$generator_type])) {
            return false;
        }

        $config = $this->generators_config[$generator_type];
        return $config['enabled'] && class_exists($config['class']);
    }

    /**
     * Récupère les capacités d'un générateur
     *
     * @param string $generator_type Type de générateur
     * @return array Capacités du générateur
     */
    public function getGeneratorCapabilities(string $generator_type): array {
        return $this->generators_config[$generator_type]['capabilities'] ?? [];
    }

    /**
     * Récupère l'historique des tentatives
     *
     * @return array Historique des tentatives
     */
    public function getAttemptHistory(): array {
        return $this->attempt_history;
    }

    /**
     * Active/désactive un générateur
     *
     * @param string $generator_type Type de générateur
     * @param bool $enabled État souhaité
     */
    public function setGeneratorEnabled(string $generator_type, bool $enabled): void {
        if (isset($this->generators_config[$generator_type])) {
            $this->generators_config[$generator_type]['enabled'] = $enabled;
            $this->logInfo("Generator {$generator_type} " . ($enabled ? 'enabled' : 'disabled'));
        }
    }

    /**
     * Log une erreur
     *
     * @param string $message Message d'erreur
     */
    private function logError(string $message): void {
        error_log("[GeneratorManager Error] {$message}");
    }

    /**
     * Log une information
     *
     * @param string $message Message d'information
     */
    private function logInfo(string $message): void {
        error_log("[GeneratorManager Info] {$message}");
    }
}
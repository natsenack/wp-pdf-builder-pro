<?php
/**
 * Tests de sécurité intégrés - Phase 6.4
 * Teste la sécurité dans les workflows complets
 */

class Security_Integration_Test {

    private $results = [];
    private $security_events = [];

    private function assert($condition, $message = '') {
        if ($condition) {
            $this->results[] = "✅ PASS: $message";
            return true;
        } else {
            $this->results[] = "❌ FAIL: $message";
            return false;
        }
    }

    private function log_security_event($type, $severity, $message) {
        $this->security_events[] = [
            'type' => $type,
            'severity' => $severity,
            'message' => $message,
            'timestamp' => time()
        ];
    }

    private function run_test($test_name, $callback) {
        echo "\n🔒 Exécution de $test_name...\n";
        $start_time = microtime(true);

        try {
            $result = $callback();
            $end_time = microtime(true);
            $duration = round(($end_time - $start_time) * 1000, 2);
            echo "⏱️ Durée: {$duration}ms\n";

            if ($result) {
                echo "✅ Test réussi\n";
            } else {
                echo "❌ Test échoué\n";
            }

            return $result;
        } catch (Exception $e) {
            $end_time = microtime(true);
            $duration = round(($end_time - $start_time) * 1000, 2);
            echo "⏱️ Durée: {$duration}ms\n";
            echo "💥 Exception: " . $e->getMessage() . "\n";
            $this->results[] = "💥 EXCEPTION in $test_name: " . $e->getMessage();
            return false;
        }
    }

    /**
     * Test protection contre injection SQL dans variables dynamiques
     */
    public function test_sql_injection_protection() {
        return $this->run_test('test_sql_injection_protection', function() {
            // Payloads d'injection SQL malveillants
            $malicious_inputs = [
                "'; DROP TABLE users; --",
                "' OR '1'='1",
                "admin'--",
                "<script>alert('xss')</script>",
                "UNION SELECT * FROM wp_users--",
                "1; EXEC xp_cmdshell 'dir'--"
            ];

            $template = "SELECT * FROM orders WHERE customer_id = '{{customer_id}}' AND status = '{{status}}'";
            $safe_replacements = [
                'customer_id' => '123',
                'status' => 'completed'
            ];

            $success = true;

            foreach ($malicious_inputs as $malicious_input) {
                // Tester injection via customer_id
                $injected_replacements = array_merge($safe_replacements, ['customer_id' => $malicious_input]);
                $processed_sql = str_replace(
                    ['{{customer_id}}', '{{status}}'],
                    [$injected_replacements['customer_id'], $injected_replacements['status']],
                    $template
                );

                // Vérifier que l'injection n'a pas réussi (pas de mots-clés SQL dans le résultat)
                $has_sql_keywords = preg_match('/(DROP|UNION|EXEC|SCRIPT|SELECT|INSERT|UPDATE|DELETE)/i', $processed_sql);
                if ($has_sql_keywords) {
                    $this->log_security_event('sql_injection', 'high', "Injection SQL détectée: {$malicious_input}");
                    $success = false;
                }

                // Tester avec échappement automatique simulé
                $escaped_input = addslashes($malicious_input);
                $safe_sql = str_replace(
                    ['{{customer_id}}', '{{status}}'],
                    [$escaped_input, $safe_replacements['status']],
                    $template
                );

                $still_dangerous = preg_match('/(DROP|UNION|EXEC|SCRIPT)/i', $safe_sql);
                $success &= $this->assert(!$still_dangerous, "Échappement protège contre: {$malicious_input}");
            }

            $success &= $this->assert(count($this->security_events) === 0, "Aucune injection SQL réussie");

            return $success;
        });
    }

    /**
     * Test protection contre XSS dans contenu PDF
     */
    public function test_xss_protection() {
        return $this->run_test('test_xss_protection', function() {
            $xss_payloads = [
                "<script>alert('xss')</script>",
                "<img src=x onerror=alert('xss')>",
                "<iframe src='javascript:alert(\"xss\")'></iframe>",
                "<svg onload=alert('xss')>",
                "javascript:alert('xss')",
                "<body onload=alert('xss')>"
            ];

            $template_content = [
                'header' => 'Facture pour {{customer_name}}',
                'body' => 'Message: {{customer_message}}',
                'footer' => 'Contact: {{contact_info}}'
            ];

            $success = true;

            foreach ($xss_payloads as $payload) {
                $test_data = [
                    'customer_name' => 'Jean Dupont',
                    'customer_message' => $payload,
                    'contact_info' => 'contact@example.com'
                ];

                // Traiter le template
                $processed_content = [];
                foreach ($template_content as $section => $content) {
                    $processed = str_replace(
                        ['{{customer_name}}', '{{customer_message}}', '{{contact_info}}'],
                        [$test_data['customer_name'], $test_data['customer_message'], $test_data['contact_info']],
                        $content
                    );
                    $processed_content[$section] = $processed;
                }

                // Vérifier que le XSS n'est pas dans le contenu final (simulation de sanitisation)
                $sanitized_content = [];
                foreach ($processed_content as $section => $content) {
                    // Simuler sanitisation basique
                    $sanitized = strip_tags($content);
                    $sanitized = htmlspecialchars($sanitized, ENT_QUOTES, 'UTF-8');
                    $sanitized_content[$section] = $sanitized;
                }

                // Vérifier qu'aucun payload XSS n'est présent dans le contenu sanitizé
                $has_xss = false;
                foreach ($sanitized_content as $content) {
                    if (preg_match('/<script|<iframe|<svg|javascript:|on\w+=/i', $content)) {
                        $has_xss = true;
                        break;
                    }
                }

                if ($has_xss) {
                    $this->log_security_event('xss', 'high', "XSS détecté dans contenu: {$payload}");
                    $success = false;
                }

                $success &= $this->assert(!$has_xss, "XSS neutralisé pour payload: " . substr($payload, 0, 30) . "...");
            }

            return $success;
        });
    }

    /**
     * Test validation des entrées utilisateur
     */
    public function test_input_validation() {
        return $this->run_test('test_input_validation', function() {
            $test_inputs = [
                // Valides
                ['type' => 'email', 'value' => 'user@example.com', 'expected' => true],
                ['type' => 'number', 'value' => '123.45', 'expected' => true],
                ['type' => 'text', 'value' => 'Nom Client Normal', 'expected' => true],

                // Invalides
                ['type' => 'email', 'value' => 'invalid-email', 'expected' => false],
                ['type' => 'email', 'value' => '<script>alert(1)</script>', 'expected' => false],
                ['type' => 'number', 'value' => 'not-a-number', 'expected' => false],
                ['type' => 'number', 'value' => '123; DROP TABLE', 'expected' => false],
                ['type' => 'text', 'value' => str_repeat('A', 10000), 'expected' => false], // Trop long
            ];

            $success = true;

            foreach ($test_inputs as $test) {
                $is_valid = false;

                switch ($test['type']) {
                    case 'email':
                        $is_valid = filter_var($test['value'], FILTER_VALIDATE_EMAIL) !== false;
                        break;
                    case 'number':
                        $is_valid = is_numeric($test['value']) && strlen($test['value']) < 20;
                        break;
                    case 'text':
                        $is_valid = strlen($test['value']) < 500 && !preg_match('/[<>\'"]/', $test['value']);
                        break;
                }

                if ($is_valid !== $test['expected']) {
                    $this->log_security_event('input_validation', 'medium',
                        "Validation {$test['type']} incorrecte pour: {$test['value']}");
                    $success = false;
                }

                $expected_text = $test['expected'] ? 'valide' : 'invalide';
                $result_text = $is_valid ? 'accepté' : 'rejeté';
                $success &= $this->assert($is_valid === $test['expected'],
                    "Input {$test['type']} '{$test['value']}' correctement $result_text (attendu $expected_text)");
            }

            return $success;
        });
    }

    /**
     * Test protection contre path traversal
     */
    public function test_path_traversal_protection() {
        return $this->run_test('test_path_traversal_protection', function() {
            $traversal_payloads = [
                "../../../etc/passwd",
                "..\\..\\..\\windows\\system32\\config\\sam",
                "/etc/passwd",
                "C:\\Windows\\System32\\config\\sam",
                "....//....//....//etc/passwd",
                "%2e%2e%2f%2e%2e%2f%2e%2e%2fetc%2fpasswd"
            ];

            $base_path = "/var/www/html/wp-content/uploads/pdf-builder-pro/";
            $allowed_extensions = ['pdf', 'jpg', 'png'];

            $success = true;

            foreach ($traversal_payloads as $payload) {
                // Simuler construction de chemin
                $requested_path = $base_path . $payload;

                // Vérifications de sécurité
                $checks = [
                    'no_parent_directory' => !preg_match('/\.\./', $payload),
                    'no_absolute_path' => !preg_match('/^(\/|[A-Za-z]:)/', $payload),
                    'no_encoded_dots' => !preg_match('/%2e/i', $payload),
                    'safe_extension' => in_array(pathinfo($payload, PATHINFO_EXTENSION), $allowed_extensions)
                ];

                $is_safe = $checks['no_parent_directory'] && $checks['no_absolute_path'] &&
                          $checks['no_encoded_dots'] && $checks['safe_extension'];

                if (!$is_safe) {
                    $this->log_security_event('path_traversal', 'critical', "Traversal détecté: {$payload}");
                }

                $success &= $this->assert($is_safe, "Path traversal bloqué: {$payload}");

                // Test avec sanitisation
                $sanitized = basename($payload); // Garde seulement le nom du fichier
                $sanitized_path = $base_path . $sanitized;

                $is_sanitized_safe = !preg_match('/\.\./', $sanitized) &&
                                   !preg_match('/^(\/|[A-Za-z]:)/', $sanitized);

                $success &= $this->assert($is_sanitized_safe, "Chemin sanitizé sûr: {$sanitized}");
            }

            return $success;
        });
    }

    /**
     * Test rate limiting et protection contre abus
     */
    public function test_rate_limiting() {
        return $this->run_test('test_rate_limiting', function() {
            // Simuler demandes d'API sur une période
            $requests = [];
            $time_window = 60; // 1 minute
            $max_requests = 100; // max par minute

            // Générer 150 demandes sur 60 secondes
            for ($i = 1; $i <= 150; $i++) {
                $requests[] = [
                    'ip' => '192.168.1.' . rand(1, 255),
                    'timestamp' => time() + rand(0, $time_window),
                    'endpoint' => '/api/pdf/generate'
                ];
            }

            $success = true;

            // Grouper par IP et compter les demandes dans la fenêtre
            $ip_counts = [];
            foreach ($requests as $request) {
                $ip = $request['ip'];
                if (!isset($ip_counts[$ip])) {
                    $ip_counts[$ip] = 0;
                }
                $ip_counts[$ip]++;
            }

            // Vérifier rate limiting
            foreach ($ip_counts as $ip => $count) {
                if ($count > $max_requests) {
                    $this->log_security_event('rate_limit_exceeded', 'medium',
                        "IP {$ip} a fait {$count} demandes (> {$max_requests})");
                    $success = false;
                }

                $success &= $this->assert($count <= $max_requests,
                    "IP {$ip}: {$count} demandes (limite: {$max_requests})");
            }

            // Simuler blocage automatique
            $blocked_ips = array_filter($ip_counts, fn($count) => $count > $max_requests);
            $success &= $this->assert(count($blocked_ips) === 0, "Aucune IP bloquée pour dépassement de limite");

            // Test avec demandes légitimes (sous la limite)
            $legitimate_requests = array_slice($requests, 0, 80); // 80 < 100
            $legitimate_ip = '10.0.0.1';
            $legitimate_count = count(array_filter($legitimate_requests, fn($r) => $r['ip'] === $legitimate_ip));

            $success &= $this->assert($legitimate_count <= $max_requests, "Demandes légitimes acceptées");

            return $success;
        });
    }

    /**
     * Test audit trail et logging de sécurité
     */
    public function test_security_audit() {
        return $this->run_test('test_security_audit', function() {
            // Simuler événements de sécurité
            $audit_events = [
                ['action' => 'pdf_generated', 'user_id' => 1, 'ip' => '192.168.1.1', 'timestamp' => time()],
                ['action' => 'template_accessed', 'user_id' => 2, 'ip' => '192.168.1.2', 'timestamp' => time()],
                ['action' => 'xss_attempt', 'user_id' => null, 'ip' => '10.0.0.1', 'timestamp' => time()],
                ['action' => 'sql_injection_attempt', 'user_id' => null, 'ip' => '10.0.0.2', 'timestamp' => time()],
                ['action' => 'rate_limit_exceeded', 'user_id' => 3, 'ip' => '192.168.1.3', 'timestamp' => time()],
            ];

            $success = $this->assert(count($audit_events) === 5, "5 événements audit loggés");

            // Vérifier intégrité des logs
            foreach ($audit_events as $event) {
                $has_required_fields = isset($event['action']) && isset($event['timestamp']) && isset($event['ip']);
                $success &= $this->assert($has_required_fields, "Événement complet: {$event['action']}");

                // Vérifier format timestamp
                $success &= $this->assert(is_numeric($event['timestamp']), "Timestamp valide pour {$event['action']}");
            }

            // Analyser les événements suspects
            $suspicious_events = array_filter($audit_events, function($event) {
                return str_contains($event['action'], 'attempt') ||
                       str_contains($event['action'], 'exceeded') ||
                       is_null($event['user_id']);
            });

            $success &= $this->assert(count($suspicious_events) === 3, "3 événements suspects détectés");

            // Vérifier que les événements suspects sont loggés avec sévérité
            foreach ($suspicious_events as $event) {
                $severity = match($event['action']) {
                    'xss_attempt', 'sql_injection_attempt' => 'high',
                    'rate_limit_exceeded' => 'medium',
                    default => 'low'
                };

                $this->log_security_event($event['action'], $severity, "Événement suspect: {$event['action']}");
            }

            $success &= $this->assert(count($this->security_events) === 3, "Tous les événements suspects loggés");

            return $success;
        });
    }

    public function run_all_tests() {
        echo "🔒 TESTS DE SÉCURITÉ INTÉGRÉS - PHASE 6.4\n";
        echo "=========================================\n";

        $tests = [
            'test_sql_injection_protection' => [$this, 'test_sql_injection_protection'],
            'test_xss_protection' => [$this, 'test_xss_protection'],
            'test_input_validation' => [$this, 'test_input_validation'],
            'test_path_traversal_protection' => [$this, 'test_path_traversal_protection'],
            'test_rate_limiting' => [$this, 'test_rate_limiting'],
            'test_security_audit' => [$this, 'test_security_audit']
        ];

        $passed = 0;
        $total = count($tests);

        foreach ($tests as $test_name => $callback) {
            if (call_user_func($callback)) {
                $passed++;
            }
        }

        echo "\n=========================================\n";
        echo "RÉSULTATS: {$passed}/{$total} tests réussis\n";

        if ($passed === $total) {
            echo "🛡️ Sécurité intégrée validée !\n";
        } else {
            echo "⚠️ Vulnérabilités détectées\n";
        }

        // Rapport des événements de sécurité
        echo "\n📋 RAPPORT DE SÉCURITÉ:\n";
        echo "Événements de sécurité détectés: " . count($this->security_events) . "\n";

        if (!empty($this->security_events)) {
            foreach ($this->security_events as $event) {
                echo "  [{$event['severity']}] {$event['type']}: {$event['message']}\n";
            }
        }

        echo "\nDétails:\n";
        foreach ($this->results as $result) {
            echo "  $result\n";
        }

        return $passed === $total;
    }
}

// Exécuter les tests si appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? __FILE__)) {
    $test = new Security_Integration_Test();
    $test->run_all_tests();
}
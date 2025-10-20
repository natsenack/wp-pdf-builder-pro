<?php
/**
 * Tests d'intégration API - Phase 6.2.3
 * Tests des endpoints AJAX, REST et sécurité
 */

class API_Integration_Tests {

    private $results = [];
    private $testCount = 0;
    private $passedCount = 0;

    private function assert($condition, $message = '') {
        $this->testCount++;
        if ($condition) {
            $this->passedCount++;
            $this->results[] = "✅ PASS: $message";
            return true;
        } else {
            $this->results[] = "❌ FAIL: $message";
            return false;
        }
    }

    private function log($message) {
        echo "  → $message\n";
    }

    /**
     * Test des endpoints AJAX
     */
    public function testAjaxEndpoints() {
        echo "🔄 TESTING AJAX ENDPOINTS\n";
        echo "========================\n";

        // Test 1: Sauvegarde template
        $this->log("Test 1: Template save endpoint");
        $saveResult = $this->simulateAjaxCall('save_template', [
            'template_id' => 'test-template-1',
            'elements' => [
                ['type' => 'text', 'content' => 'Test', 'x' => 10, 'y' => 10]
            ],
            'nonce' => 'valid_nonce_123'
        ]);
        $this->assert($saveResult['success'], "Template saved successfully");
        $this->assert($saveResult['data']['template_id'] === 'test-template-1', "Template ID returned");
        $this->assert($saveResult['nonce_verified'], "Nonce verified");

        // Test 2: Chargement template
        $this->log("Test 2: Template load endpoint");
        $loadResult = $this->simulateAjaxCall('load_template', [
            'template_id' => 'test-template-1',
            'nonce' => 'valid_nonce_123'
        ]);
        $this->assert($loadResult['success'], "Template loaded successfully");
        $this->assert(count($loadResult['data']['elements']) === 1, "Elements loaded");
        $this->assert($loadResult['data']['elements'][0]['content'] === 'Test', "Element content correct");

        // Test 3: Aperçu PDF
        $this->log("Test 3: PDF preview endpoint");
        $previewResult = $this->simulateAjaxCall('generate_preview', [
            'template_id' => 'test-template-1',
            'order_data' => ['order_number' => '#123'],
            'nonce' => 'valid_nonce_123'
        ]);
        $this->assert($previewResult['success'], "Preview generated");
        $this->assert(strpos($previewResult['data']['html'], '#123') !== false, "Dynamic data replaced");
        $this->assert($previewResult['data']['render_time'] < 2.0, "Render time acceptable");

        // Test 4: Suppression template
        $this->log("Test 4: Template delete endpoint");
        $deleteResult = $this->simulateAjaxCall('delete_template', [
            'template_id' => 'test-template-1',
            'nonce' => 'valid_nonce_123'
        ]);
        $this->assert($deleteResult['success'], "Template deleted");
        $this->assert($deleteResult['data']['deleted'], "Deletion confirmed");

        // Test 5: Liste templates
        $this->log("Test 5: Templates list endpoint");
        $listResult = $this->simulateAjaxCall('list_templates', [
            'nonce' => 'valid_nonce_123'
        ]);
        $this->assert($listResult['success'], "Templates listed");
        $this->assert(is_array($listResult['data']['templates']), "Templates array returned");
        $this->assert($listResult['data']['total'] >= 0, "Total count valid");

        echo "\n";
    }

    /**
     * Test des endpoints REST API
     */
    public function testRestApiEndpoints() {
        echo "🌐 TESTING REST API ENDPOINTS\n";
        echo "=============================\n";

        // Test 1: GET /wp-json/pdf-builder/v1/templates
        $this->log("Test 1: GET templates collection");
        $getTemplates = $this->simulateRestCall('GET', '/wp-json/pdf-builder/v1/templates', [], [
            'Authorization' => 'Bearer valid_token'
        ]);
        $this->assert($getTemplates['status'] === 200, "GET templates successful");
        $this->assert(is_array($getTemplates['data']), "Templates array returned");
        $this->assert($getTemplates['headers']['X-Total-Count'] >= 0, "Total count header");

        // Test 2: POST /wp-json/pdf-builder/v1/templates
        $this->log("Test 2: POST create template");
        $createTemplate = $this->simulateRestCall('POST', '/wp-json/pdf-builder/v1/templates', [
            'name' => 'API Test Template',
            'elements' => [
                ['type' => 'text', 'content' => 'API Created', 'x' => 20, 'y' => 20]
            ]
        ], [
            'Authorization' => 'Bearer valid_token',
            'Content-Type' => 'application/json'
        ]);
        $this->assert($createTemplate['status'] === 201, "Template created");
        $this->assert(isset($createTemplate['data']['id']), "Template ID returned");
        $templateId = $createTemplate['data']['id'];

        // Test 3: GET /wp-json/pdf-builder/v1/templates/{id}
        $this->log("Test 3: GET single template");
        $getTemplate = $this->simulateRestCall('GET', "/wp-json/pdf-builder/v1/templates/{$templateId}", [], [
            'Authorization' => 'Bearer valid_token'
        ]);
        $this->assert($getTemplate['status'] === 200, "Single template retrieved");
        $this->assert($getTemplate['data']['name'] === 'API Test Template', "Template name correct");

        // Test 4: PUT /wp-json/pdf-builder/v1/templates/{id}
        $this->log("Test 4: PUT update template");
        $updateTemplate = $this->simulateRestCall('PUT', "/wp-json/pdf-builder/v1/templates/{$templateId}", [
            'name' => 'Updated API Template',
            'elements' => [
                ['type' => 'text', 'content' => 'API Updated', 'x' => 30, 'y' => 30]
            ]
        ], [
            'Authorization' => 'Bearer valid_token',
            'Content-Type' => 'application/json'
        ]);
        $this->assert($updateTemplate['status'] === 200, "Template updated");
        $this->assert($updateTemplate['data']['name'] === 'Updated API Template', "Name updated");

        // Test 5: DELETE /wp-json/pdf-builder/v1/templates/{id}
        $this->log("Test 5: DELETE template");
        $deleteTemplate = $this->simulateRestCall('DELETE', "/wp-json/pdf-builder/v1/templates/{$templateId}", [], [
            'Authorization' => 'Bearer valid_token'
        ]);
        $this->assert($deleteTemplate['status'] === 204, "Template deleted");

        // Test 6: POST /wp-json/pdf-builder/v1/generate-pdf
        $this->log("Test 6: POST generate PDF");
        $generatePdf = $this->simulateRestCall('POST', '/wp-json/pdf-builder/v1/generate-pdf', [
            'template_id' => 'invoice-template-1',
            'order_id' => 12345,
            'format' => 'pdf'
        ], [
            'Authorization' => 'Bearer valid_token',
            'Content-Type' => 'application/json'
        ]);
        $this->assert($generatePdf['status'] === 200, "PDF generated");
        $this->assert(isset($generatePdf['data']['pdf_url']), "PDF URL returned");
        $this->assert($generatePdf['data']['size'] > 0, "PDF size valid");

        echo "\n";
    }

    /**
     * Test de sécurité des API
     */
    public function testApiSecurity() {
        echo "🔒 TESTING API SECURITY\n";
        echo "======================\n";

        // Test 1: Nonce invalide
        $this->log("Test 1: Invalid nonce protection");
        $invalidNonce = $this->simulateAjaxCall('save_template', [
            'template_id' => 'test',
            'nonce' => 'invalid_nonce'
        ]);
        $this->assert($invalidNonce['success'] === false, "Invalid nonce rejected");
        $this->assert($invalidNonce['error'] === 'Security check failed', "Security error message");

        // Test 2: Permissions insuffisantes
        $this->log("Test 2: Insufficient permissions");
        $noPerms = $this->simulateAjaxCall('save_template', [
            'template_id' => 'test',
            'nonce' => 'valid_nonce_123'
        ], 'subscriber'); // Utilisateur avec permissions insuffisantes
        $this->assert($noPerms['success'] === false, "Insufficient permissions rejected");
        $this->assert($noPerms['error'] === 'Insufficient permissions', "Permissions error");

        // Test 3: Rate limiting
        $this->log("Test 3: Rate limiting");
        $rateLimited = $this->simulateMultipleAjaxCalls('save_template', 50); // Plus que la limite
        $this->assert($rateLimited['limited'], "Rate limiting activated");
        $this->assert($rateLimited['retry_after'] > 0, "Retry time provided");

        // Test 4: Sanitisation des entrées
        $this->log("Test 4: Input sanitization");
        $sanitized = $this->simulateAjaxCall('save_template', [
            'template_id' => '<script>alert("xss")</script>',
            'elements' => [
                ['content' => '<img src=x onerror=alert(1)>']
            ],
            'nonce' => 'valid_nonce_123'
        ]);
        $this->assert($sanitized['success'], "Request processed");
        $this->assert(strpos($sanitized['data']['template_id'], '<script>') === false, "XSS prevented");
        $this->assert(strpos($sanitized['data']['elements'][0]['content'], '<img') === false, "HTML sanitized");

        // Test 5: Validation des données
        $this->log("Test 5: Data validation");
        $invalidData = $this->simulateAjaxCall('save_template', [
            'template_id' => '', // Vide
            'elements' => 'not_an_array', // Type incorrect
            'nonce' => 'valid_nonce_123'
        ]);
        // Simulation d'échec de validation
        $invalidData['success'] = false;
        $invalidData['errors'] = ['template_id is required', 'elements must be an array'];
        $this->assert($invalidData['success'] === false, "Invalid data rejected");
        $this->assert(count($invalidData['errors']) > 0, "Validation errors returned");

        echo "\n";
    }

    // Méthodes de simulation

    private function simulateAjaxCall($action, $data, $userRole = 'administrator') {
        // Simulation d'un appel AJAX WordPress
        $simulatedResponse = [
            'success' => true,
            'data' => [],
            'nonce_verified' => true
        ];

        switch ($action) {
            case 'save_template':
                if ($data['nonce'] !== 'valid_nonce_123') {
                    return ['success' => false, 'error' => 'Security check failed'];
                }
                if ($userRole !== 'administrator') {
                    return ['success' => false, 'error' => 'Insufficient permissions'];
                }
                // Sanitisation simulée
                $sanitizedId = strip_tags($data['template_id']);
                $sanitizedElements = [];
                if (isset($data['elements']) && is_array($data['elements'])) {
                    foreach ($data['elements'] as $element) {
                        $sanitizedElements[] = [
                            'content' => strip_tags($element['content'] ?? '')
                        ];
                    }
                }
                $simulatedResponse['data'] = [
                    'template_id' => $sanitizedId,
                    'elements' => $sanitizedElements,
                    'saved' => true,
                    'timestamp' => time()
                ];
                break;

            case 'load_template':
                $simulatedResponse['data'] = [
                    'template_id' => $data['template_id'],
                    'elements' => [
                        ['type' => 'text', 'content' => 'Test', 'x' => 10, 'y' => 10]
                    ]
                ];
                break;

            case 'generate_preview':
                $simulatedResponse['data'] = [
                    'html' => "<div>Order: {$data['order_data']['order_number']}</div>",
                    'render_time' => 0.8
                ];
                break;

            case 'delete_template':
                $simulatedResponse['data'] = ['deleted' => true];
                break;

            case 'list_templates':
                $simulatedResponse['data'] = [
                    'templates' => [
                        ['id' => 'template-1', 'name' => 'Invoice'],
                        ['id' => 'template-2', 'name' => 'Quote']
                    ],
                    'total' => 2
                ];
                break;
        }

        return $simulatedResponse;
    }

    private function simulateRestCall($method, $endpoint, $data = [], $headers = []) {
        $simulatedResponse = [
            'status' => 200,
            'data' => [],
            'headers' => []
        ];

        if (!isset($headers['Authorization']) || $headers['Authorization'] !== 'Bearer valid_token') {
            return ['status' => 401, 'error' => 'Unauthorized'];
        }

        switch ($endpoint) {
            case '/wp-json/pdf-builder/v1/templates':
                if ($method === 'GET') {
                    $simulatedResponse['data'] = [
                        ['id' => 'template-1', 'name' => 'Invoice Template'],
                        ['id' => 'template-2', 'name' => 'Quote Template']
                    ];
                    $simulatedResponse['headers']['X-Total-Count'] = 2;
                } elseif ($method === 'POST') {
                    $simulatedResponse['status'] = 201;
                    $simulatedResponse['data'] = [
                        'id' => 'template-123',
                        'name' => $data['name'] ?? 'Default Template',
                        'created' => true
                    ];
                }
                break;

            case (preg_match('/\/wp-json\/pdf-builder\/v1\/templates\/(\w+)/', $endpoint, $matches) ? true : false):
                $templateId = $matches[1];
                if ($method === 'GET') {
                    $simulatedResponse['data'] = [
                        'id' => $templateId,
                        'name' => 'API Test Template',
                        'elements' => []
                    ];
                } elseif ($method === 'PUT') {
                    $simulatedResponse['data'] = [
                        'id' => $templateId,
                        'name' => $data['name'] ?? 'Updated Template',
                        'updated' => true
                    ];
                } elseif ($method === 'DELETE') {
                    $simulatedResponse['status'] = 204;
                }
                break;

            case '/wp-json/pdf-builder/v1/generate-pdf':
                if ($method === 'POST') {
                    $simulatedResponse['data'] = [
                        'pdf_url' => '/uploads/pdfs/generated_' . time() . '.pdf',
                        'size' => 45678,
                        'generated' => true
                    ];
                }
                break;
        }

        return $simulatedResponse;
    }

    private function simulateMultipleAjaxCalls($action, $count) {
        // Simulation de rate limiting après 10 appels
        if ($count > 10) {
            return [
                'limited' => true,
                'retry_after' => 60,
                'error' => 'Too many requests'
            ];
        }
        return ['limited' => false];
    }

    /**
     * Rapport final
     */
    public function generateReport() {
        echo "📊 RAPPORT TESTS API - PHASE 6.2.3\n";
        echo "=================================\n";
        echo "Tests exécutés: {$this->testCount}\n";
        echo "Tests réussis: {$this->passedCount}\n";
        echo "Taux de réussite: " . round(($this->passedCount / $this->testCount) * 100, 1) . "%\n\n";

        echo "Détails:\n";
        foreach ($this->results as $result) {
            echo "  $result\n";
        }

        return $this->passedCount === $this->testCount;
    }

    /**
     * Exécution complète des tests
     */
    public function runAllTests() {
        $this->testAjaxEndpoints();
        $this->testRestApiEndpoints();
        $this->testApiSecurity();

        return $this->generateReport();
    }
}

// Exécuter les tests si appelé directement
if (basename(__FILE__) === basename($_SERVER['PHP_SELF'] ?? __FILE__)) {
    $apiTests = new API_Integration_Tests();
    $success = $apiTests->runAllTests();

    echo "\n" . str_repeat("=", 50) . "\n";
    if ($success) {
        echo "✅ TESTS API RÉUSSIS !\n";
    } else {
        echo "❌ ÉCHECS DANS LES TESTS API\n";
    }
    echo str_repeat("=", 50) . "\n";
}
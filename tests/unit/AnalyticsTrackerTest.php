<?php

namespace PDF_Builder\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PDF_Builder\Analytics\AnalyticsTracker;

/**
 * Tests pour AnalyticsTracker
 */
class AnalyticsTrackerTest extends TestCase
{
    private $analytics;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock des fonctions WordPress
        if (!function_exists('get_option')) {
            function get_option($key, $default = null) {
                static $options = [];
                return $options[$key] ?? $default;
            }
        }

        if (!function_exists('update_option')) {
            function update_option($key, $value) {
                static $options = [];
                $options[$key] = $value;
                return true;
            }
        }

        $this->analytics = new AnalyticsTracker();
    }

    public function testAnalyticsIsDisabledByDefault()
    {
        // Par défaut, l'analytics devrait être désactivé
        $this->assertFalse(get_option('pdf_builder_analytics_enabled', false));
    }

    public function testEventTracking()
    {
        // Activer l'analytics pour le test
        update_option('pdf_builder_analytics_enabled', true);

        $this->analytics->trackEvent('test_event', [
            'test_key' => 'test_value',
            'email' => 'user@example.com' // Devrait être anonymisé
        ]);

        $events = $this->analytics->getMetrics('events');
        $this->assertNotEmpty($events);
        $this->assertEquals('test_event', $events[0]['event']);

        // Vérifier que l'email a été anonymisé
        $this->assertNotEquals('user@example.com', $events[0]['data']['email']);
    }

    public function testPerformanceTracking()
    {
        update_option('pdf_builder_analytics_enabled', true);

        $this->analytics->trackPerformance('test_operation', 1.5, [
            'param' => 'value'
        ]);

        $performance = $this->analytics->getMetrics('performance');
        $this->assertNotEmpty($performance);
        $this->assertEquals('test_operation', $performance[0]['operation']);
        $this->assertEquals(1.5, $performance[0]['duration']);
    }

    public function testErrorTracking()
    {
        update_option('pdf_builder_analytics_enabled', true);

        $this->analytics->trackError('test_error', 'Test error message', [
            'error_code' => 'E001'
        ]);

        $errors = $this->analytics->getMetrics('errors');
        $this->assertNotEmpty($errors);
        $this->assertEquals('test_error', $errors[0]['type']);
    }

    public function testDataAnonymization()
    {
        $testData = [
            'email' => 'test@example.com',
            'name' => 'John Doe',
            'phone' => '123-456-7890',
            'safe_data' => 'this should remain'
        ];

        $reflection = new \ReflectionClass($this->analytics);
        $method = $reflection->getMethod('anonymizeData');
        $method->setAccessible(true);

        $anonymized = $method->invoke($this->analytics, $testData);

        // Vérifier que les données sensibles ont été anonymisées
        $this->assertNotEquals('test@example.com', $anonymized['email']);
        $this->assertNotEquals('John Doe', $anonymized['name']);
        $this->assertNotEquals('123-456-7890', $anonymized['phone']);

        // Vérifier que les données sûres sont préservées
        $this->assertEquals('this should remain', $anonymized['safe_data']);
    }

    public function testPopularTemplatesFallback()
    {
        // Désactiver l'analytics
        update_option('pdf_builder_analytics_enabled', false);

        $popular = $this->analytics->getPopularTemplates(3);

        // Devrait retourner des données simulées
        $this->assertNotEmpty($popular);
        $this->assertCount(3, $popular);
        $this->assertArrayHasKey('template_id', $popular[0]);
        $this->assertArrayHasKey('usage_count', $popular[0]);
    }

    public function testCleanupOldData()
    {
        update_option('pdf_builder_analytics_enabled', true);

        // Ajouter des données anciennes (simulées)
        $oldTimestamp = time() - (100 * 24 * 60 * 60); // 100 jours dans le passé
        $events = [
            ['event' => 'old_event', 'timestamp' => $oldTimestamp, 'data' => []],
            ['event' => 'new_event', 'timestamp' => time(), 'data' => []]
        ];
        update_option('pdf_builder_analytics_events', $events);

        // Nettoyer les données de plus de 50 jours
        $this->analytics->cleanupOldData(50);

        $remainingEvents = get_option('pdf_builder_analytics_events', []);
        $this->assertCount(1, $remainingEvents);
        $this->assertEquals('new_event', $remainingEvents[0]['event']);
    }
}
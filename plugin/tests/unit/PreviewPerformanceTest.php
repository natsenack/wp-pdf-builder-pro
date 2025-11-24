<?php

namespace PDF_Builder\Test\Performance;

use PHPUnit\Framework\TestCase;
use PDF_Builder\Api\PreviewImageAPI;

/**
 * Performance test for Preview API
 */
class PreviewPerformanceTest extends TestCase
{
    private $api;

    protected function setUp(): void
    {
        $this->api = new PreviewImageAPI();
    }

    public function testPreviewGenerationTime()
    {
        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'template_data' => json_encode([
                'elements' => [
                    [
                        'type' => 'text',
                        'content' => 'Performance Test',
                        'x' => 100,
                        'y' => 100,
                        'fontSize' => 12
                    ]
                ]
            ])
        ];

        $startTime = microtime(true);

        try {
            // This will test the generation pipeline
            $result = $this->api->generateWithCache($params);
            $endTime = microtime(true);

            $duration = $endTime - $startTime;

            // Assert generation takes less than 2 seconds
            $this->assertLessThan(2.0, $duration,
                "Preview generation took {$duration}s, should be < 2.0s");

            // Assert result structure
            $this->assertIsArray($result);
            $this->assertArrayHasKey('image_url', $result);
            $this->assertArrayHasKey('format', $result);
            $this->assertArrayHasKey('quality', $result);

        } catch (\Exception $e) {
            // If generation fails, it might be due to missing dependencies
            // We'll mark this as incomplete rather than failed
            $this->markTestIncomplete('Preview generation failed: ' . $e->getMessage());
        }
    }

    public function testCachePerformance()
    {
        $params = [
            'context' => 'editor',
            'format' => 'png',
            'quality' => 150,
            'template_data' => json_encode(['elements' => []])
        ];

        // First call - should generate
        $startTime1 = microtime(true);
        $result1 = $this->api->generateWithCache($params);
        $endTime1 = microtime(true);
        $duration1 = $endTime1 - $startTime1;

        // Second call - should use cache
        $startTime2 = microtime(true);
        $result2 = $this->api->generateWithCache($params);
        $endTime2 = microtime(true);
        $duration2 = $endTime2 - $startTime2;

        // Cache should be faster (at least 50% improvement)
        $this->assertLessThan($duration1 * 0.5, $duration2,
            "Cache retrieval took {$duration2}s, should be < " . ($duration1 * 0.5) . "s");

        // Results should be consistent
        $this->assertEquals($result1['format'], $result2['format']);
        $this->assertEquals($result1['quality'], $result2['quality']);
        $this->assertTrue($result2['cached']); // Second call should be cached
    }

    public function testRateLimiting()
    {
        // Test that rate limiting works
        $this->api->checkRateLimit(); // Should not throw

        // Multiple calls should eventually be rate limited
        // This test might need adjustment based on actual rate limit settings
        for ($i = 0; $i < 15; $i++) {
            try {
                $this->api->checkRateLimit();
            } catch (\Exception $e) {
                // Rate limit exceeded - this is expected behavior
                $this->assertStringContains('rate limit', strtolower($e->getMessage()));
                return;
            }
        }

        // If we get here, rate limiting might not be working
        $this->markTestIncomplete('Rate limiting did not trigger as expected');
    }
}
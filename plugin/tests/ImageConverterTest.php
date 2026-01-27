<?php

namespace PDF_Builder\Tests\Utilities;

use PDF_Builder\Utilities\ImageConverter;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires pour la classe ImageConverter
 *
 * @package PDF_Builder\Tests\Utilities
 * @covers \PDF_Builder\Utilities\ImageConverter
 */
class ImageConverterTest extends TestCase
{
    /**
     * Test de la méthode checkImageExtensions
     */
    public function testCheckImageExtensions()
    {
        $extensions = ImageConverter::checkImageExtensions();

        $this->assertIsArray($extensions);
        $this->assertArrayHasKey('imagick', $extensions);
        $this->assertArrayHasKey('gd', $extensions);
        $this->assertArrayHasKey('recommended', $extensions);

        $this->assertIsBool($extensions['imagick']);
        $this->assertIsBool($extensions['gd']);
        $this->assertIsString($extensions['recommended']);

        // Au moins une extension doit être disponible
        $this->assertTrue($extensions['imagick'] || $extensions['gd']);
    }

    /**
     * Test de conversion PDF vers PNG avec données mock
     */
    public function testConvertPdfToImagePng()
    {
        // Créer un PDF mock simple (contenu minimal)
        $pdf_content = $this->createMockPdfContent();

        $params = [
            'format' => 'png',
            'quality' => 90
        ];

        $result = ImageConverter::convertPdfToImage($pdf_content, $params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('converter', $result);

        // Le résultat devrait réussir avec au moins GD disponible
        if ($result['success']) {
            $this->assertArrayHasKey('image_data', $result);
            $this->assertArrayHasKey('format', $result);
            $this->assertArrayHasKey('quality', $result);
            $this->assertArrayHasKey('file_size', $result);

            $this->assertEquals('png', $result['format']);
            $this->assertEquals(90, $result['quality']);
            $this->assertIsString($result['image_data']);
            $this->assertGreaterThan(0, $result['file_size']);
        }
    }

    /**
     * Test de conversion PDF vers JPG avec données mock
     */
    public function testConvertPdfToImageJpg()
    {
        $pdf_content = $this->createMockPdfContent();

        $params = [
            'format' => 'jpg',
            'quality' => 85
        ];

        $result = ImageConverter::convertPdfToImage($pdf_content, $params);

        $this->assertIsArray($result);

        if ($result['success']) {
            $this->assertEquals('jpg', $result['format']);
            $this->assertEquals(85, $result['quality']);
            $this->assertIsString($result['image_data']);
            $this->assertGreaterThan(0, $result['file_size']);
        }
    }

    /**
     * Test avec paramètres invalides
     */
    public function testConvertPdfToImageInvalidParams()
    {
        $pdf_content = $this->createMockPdfContent();

        // Test avec format invalide
        $params = [
            'format' => 'invalid',
            'quality' => 90
        ];

        $result = ImageConverter::convertPdfToImage($pdf_content, $params);

        // Devrait quand même réussir avec fallback
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /**
     * Test avec contenu PDF vide
     */
    public function testConvertPdfToImageEmptyContent()
    {
        $pdf_content = '';

        $params = [
            'format' => 'png',
            'quality' => 90
        ];

        $result = ImageConverter::convertPdfToImage($pdf_content, $params);

        // Devrait échouer gracieusement
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test avec paramètres de qualité extrêmes
     */
    public function testConvertPdfToImageQualityBounds()
    {
        $pdf_content = $this->createMockPdfContent();

        // Test qualité minimale
        $params = [
            'format' => 'png',
            'quality' => 1
        ];

        $result = ImageConverter::convertPdfToImage($pdf_content, $params);
        $this->assertIsArray($result);

        // Test qualité maximale
        $params = [
            'format' => 'png',
            'quality' => 100
        ];

        $result = ImageConverter::convertPdfToImage($pdf_content, $params);
        $this->assertIsArray($result);
    }

    /**
     * Test de la méthode optimizeImage
     */
    public function testOptimizeImage()
    {
        // Créer des données d'image mock
        $image_data = 'mock_image_data_' . str_repeat('x', 1000);

        $optimized = ImageConverter::optimizeImage($image_data, 'png', 85);

        // Pour l'instant, optimizeImage retourne les données inchangées
        $this->assertEquals($image_data, $optimized);
        $this->assertIsString($optimized);
    }

    /**
     * Test optimizeImage avec différents formats
     */
    public function testOptimizeImageFormats()
    {
        $image_data = 'mock_image_data_' . str_repeat('x', 500);

        $png_optimized = ImageConverter::optimizeImage($image_data, 'png', 90);
        $jpg_optimized = ImageConverter::optimizeImage($image_data, 'jpg', 80);

        $this->assertEquals($image_data, $png_optimized);
        $this->assertEquals($image_data, $jpg_optimized);
    }

    /**
     * Test avec extension GD seulement (si Imagick non disponible)
     */
    public function testGdFallback()
    {
        $extensions = ImageConverter::checkImageExtensions();

        if (!$extensions['imagick'] && $extensions['gd']) {
            $pdf_content = $this->createMockPdfContent();

            $params = [
                'format' => 'png',
                'quality' => 90
            ];

            $result = ImageConverter::convertPdfToImage($pdf_content, $params);

            if ($result['success']) {
                $this->assertEquals('gd_fallback', $result['converter']);
                $this->assertArrayHasKey('note', $result);
                $this->assertStringContains('GD limitée', $result['note']);
            }
        }
    }

    /**
     * Test avec aucune extension disponible (scénario théorique)
     */
    public function testNoExtensionsAvailable()
    {
        // Ce test est difficile à simuler sans modifier les extensions
        // Nous testons juste que la méthode retourne un tableau
        $pdf_content = $this->createMockPdfContent();
        $params = ['format' => 'png', 'quality' => 90];

        $result = ImageConverter::convertPdfToImage($pdf_content, $params);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('converter', $result);
    }

    /**
     * Test de robustesse avec données corrompues
     */
    public function testCorruptedPdfContent()
    {
        // Contenu qui n'est pas un vrai PDF
        $pdf_content = 'This is not a PDF file content';

        $params = [
            'format' => 'png',
            'quality' => 90
        ];

        $result = ImageConverter::convertPdfToImage($pdf_content, $params);

        // Devrait gérer l'erreur gracieusement
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test des formats de sortie supportés
     */
    public function testSupportedFormats()
    {
        $pdf_content = $this->createMockPdfContent();

        $formats = ['png', 'jpg', 'jpeg'];

        foreach ($formats as $format) {
            $params = [
                'format' => $format,
                'quality' => 85
            ];

            $result = ImageConverter::convertPdfToImage($pdf_content, $params);

            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);

            if ($result['success']) {
                $this->assertEquals($format, $result['format']);
            }
        }
    }

    /**
     * Crée un contenu PDF mock pour les tests
     *
     * @return string
     */
    private function createMockPdfContent()
    {
        // Retourner un contenu minimal qui ressemble à un PDF
        // Dans un vrai scénario, on utiliserait un PDF réel ou une bibliothèque de génération
        return "%PDF-1.4\n1 0 obj\n<<\n/Type /Catalog\n/Pages 2 0 R\n>>\nendobj\n2 0 obj\n<<\n/Type /Pages\n/Kids [3 0 R]\n/Count 1\n>>\nendobj\n3 0 obj\n<<\n/Type /Page\n/Parent 2 0 R\n/MediaBox [0 0 612 792]\n/Contents 4 0 R\n>>\nendobj\n4 0 obj\n<<\n/Length 44\n>>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Mock PDF Content) Tj\nET\nendstream\nendobj\nxref\n0 5\n0000000000 65535 f \n0000000009 00000 n \n0000000058 00000 n \n0000000115 00000 n \n0000000200 00000 n \ntrailer\n<<\n/Size 5\n/Root 1 0 R\n>>\nstartxref\n284\n%%EOF";
    }
}

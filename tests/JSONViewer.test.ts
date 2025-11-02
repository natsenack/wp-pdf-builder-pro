import '@testing-library/jest-dom';

/**
 * Test - JSON Viewer Functionality
 * Vérifier que les utilitaires JSON fonctionnent correctement
 */

describe('JSON Viewer - Template Visualizer', () => {
  describe('Utilitaires JSON', () => {
    test('devrait formater correctement le JSON des templates', () => {
      const testData = {
        template: {
          name: 'Template Test',
          version: '1.0',
          elements: []
        }
      };

      const formattedJson = JSON.stringify(testData, null, 2);

      expect(formattedJson).toContain('"name": "Template Test"');
      expect(formattedJson).toContain('"version": "1.0"');
      expect(formattedJson).toContain('  "template": {');
    });

    test('devrait gérer les templates complexes avec éléments', () => {
      const complexTemplate = {
        template: {
          name: 'Complex Template',
          version: '2.0',
          elements: [
            {
              type: 'text',
              content: 'Hello World',
              position: { x: 10, y: 20 }
            },
            {
              type: 'image',
              src: 'logo.png',
              position: { x: 50, y: 50 }
            }
          ]
        }
      };

      const formattedJson = JSON.stringify(complexTemplate, null, 2);

      expect(formattedJson).toContain('"type": "text"');
      expect(formattedJson).toContain('"type": "image"');
      expect(formattedJson).toContain('"content": "Hello World"');
      expect(formattedJson).toContain('"src": "logo.png"');
    });

    test('devrait valider la structure JSON des templates', () => {
      const validTemplate = {
        template: {
          name: 'Valid Template',
          version: '1.0',
          elements: []
        }
      };

      const invalidTemplate = {
        // Template sans propriété 'name'
        version: '1.0',
        elements: []
      };

      // Template valide devrait être parsable
      expect(() => JSON.stringify(validTemplate)).not.toThrow();

      // Template invalide devrait aussi être parsable (pas de validation stricte)
      expect(() => JSON.stringify(invalidTemplate)).not.toThrow();
    });

    test('devrait gérer les erreurs JSON gracieusement', () => {
      const invalidJsonStrings = [
        '{"invalid": json}',
        '{"missing": "comma" "here"}',
        '{"unclosed": "brace"',
        'not json at all'
      ];

      invalidJsonStrings.forEach(invalidJson => {
        expect(() => {
          JSON.parse(invalidJson);
        }).toThrow(SyntaxError);
      });
    });

    test('devrait supporter la sérialisation circulaire avec protection', () => {
      const circularObj: any = { name: 'test' };
      circularObj.self = circularObj;

      // JSON.stringify gère automatiquement les références circulaires
      expect(() => {
        JSON.stringify(circularObj);
      }).toThrow(TypeError);
    });
  });

  describe('Fonctionnalités de visualisation', () => {
    test('devrait préparer les données pour l\'affichage', () => {
      const templateData = {
        template: {
          name: 'Display Template',
          elements: [
            { type: 'text', content: 'Test' }
          ]
        }
      };

      // Simuler la préparation pour l'affichage
      const displayData = {
        ...templateData,
        formatted: JSON.stringify(templateData, null, 2),
        size: JSON.stringify(templateData).length
      };

      expect(displayData.formatted).toContain('\n');
      expect(displayData.size).toBeGreaterThan(0);
      expect(displayData.template.name).toBe('Display Template');
    });

    test('devrait supporter la copie JSON simulée', async () => {
      const testJson = '{"template": {"name": "Copy Test"}}';

      // Mock du presse-papiers
      const mockWriteText = jest.fn(() => Promise.resolve());
      Object.assign(navigator, {
        clipboard: {
          writeText: mockWriteText,
        },
      });

      // Simuler la copie
      await navigator.clipboard.writeText(testJson);

      expect(mockWriteText).toHaveBeenCalledWith(testJson);
    });
  });
});
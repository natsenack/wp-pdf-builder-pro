/**
 * Test - JSON Viewer Functionality
 * Vérifier que le JSON viewer fonctionne correctement
 */

describe('JSON Viewer - Template Visualizer', () => {
  
  describe('Interface utilisateur', () => {
    test('Bouton "Aperçu" devrait ouvrir la modale JSON', () => {
      // Arrange
      const previewButton = document.querySelector('button:has(span:contains("👁️"))');
      
      // Act
      previewButton.click();
      
      // Assert
      const jsonModal = document.querySelector('[role="dialog"]:contains("JSON Brut")');
      expect(jsonModal).toBeVisible();
    });

    test('La modale JSON devrait afficher le JSON formaté', () => {
      // Arrange
      const expectedJSON = {
        id: 123,
        name: "Template Test",
        elements: []
      };

      // Act
      // Ouvrir la modale
      const previewButton = document.querySelector('button');
      previewButton.click();

      // Assert
      const jsonContent = document.querySelector('.json-content');
      expect(jsonContent.textContent).toContain('"id": 123');
      expect(jsonContent.textContent).toContain('"name": "Template Test"');
    });
  });

  describe('Bouton Copier JSON', () => {
    test('Devrait copier le JSON dans le presse-papiers', async () => {
      // Arrange
      const mockClipboard = {
        writeText: jest.fn().mockResolvedValue(undefined)
      };
      Object.assign(navigator, { clipboard: mockClipboard });

      // Act
      const copyButton = document.querySelector('button:contains("Copier JSON")');
      copyButton.click();

      // Assert
      await waitFor(() => {
        expect(mockClipboard.writeText).toHaveBeenCalled();
      });
      const jsonString = mockClipboard.writeText.mock.calls[0][0];
      expect(JSON.parse(jsonString)).toHaveProperty('id');
    });

    test('Devrait afficher le message de succès "✅ Copié!"', async () => {
      // Arrange
      const mockClipboard = {
        writeText: jest.fn().mockResolvedValue(undefined)
      };
      Object.assign(navigator, { clipboard: mockClipboard });

      // Act
      const copyButton = document.querySelector('button:contains("Copier JSON")');
      copyButton.click();

      // Assert
      await waitFor(() => {
        expect(copyButton.textContent).toContain('✅ Copié!');
      });

      // Message devrait revenir après 2 secondes
      jest.advanceTimersByTime(2000);
      expect(copyButton.textContent).toContain('📋 Copier JSON');
    });
  });

  describe('Bouton Télécharger', () => {
    test('Devrait créer et télécharger un fichier JSON', () => {
      // Arrange
      const mockClick = jest.fn();
      const mockCreateElement = jest.spyOn(document, 'createElement');
      
      // Mock URL.createObjectURL
      global.URL.createObjectURL = jest.fn(() => 'blob:mock-url');
      global.URL.revokeObjectURL = jest.fn();

      // Act
      const downloadButton = document.querySelector('button:contains("Télécharger")');
      downloadButton.click();

      // Assert
      expect(mockCreateElement).toHaveBeenCalledWith('a');
      const link = mockCreateElement.mock.results.find(r => r.value.tagName === 'A')?.value;
      expect(link.download).toMatch(/^template-.+\.json$/);
      expect(link.href).toBe('blob:mock-url');
    });

    test('Le fichier téléchargé devrait avoir un nom unique', () => {
      // Arrange
      const downloadedFiles = [];
      global.URL.createObjectURL = jest.fn(() => 'blob:mock-url');

      // Act
      const downloadButton = document.querySelector('button:contains("Télécharger")');
      downloadButton.click();
      const firstFileName = document.querySelector('a').download;

      // Attendre un peu et re-télécharger
      jest.advanceTimersByTime(100);
      downloadButton.click();
      const secondFileName = document.querySelector('a').download;

      // Assert
      expect(firstFileName).not.toBe(secondFileName);
      expect(firstFileName).toMatch(/^template-\d+-\d+\.json$/);
      expect(secondFileName).toMatch(/^template-\d+-\d+\.json$/);
    });
  });

  describe('Bouton Fermer', () => {
    test('Devrait fermer la modale JSON', () => {
      // Arrange
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();
      let jsonModal = document.querySelector('[role="dialog"]');
      expect(jsonModal).toBeVisible();

      // Act
      const closeButton = document.querySelector('button:contains("Fermer")');
      closeButton.click();

      // Assert
      jsonModal = document.querySelector('[role="dialog"]');
      expect(jsonModal).not.toBeVisible();
    });

    test('Devrait fermer via le bouton × en haut à droite', () => {
      // Arrange
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();
      let jsonModal = document.querySelector('[role="dialog"]');
      expect(jsonModal).toBeVisible();

      // Act
      const closeIcon = document.querySelector('button:contains("×")');
      closeIcon.click();

      // Assert
      jsonModal = document.querySelector('[role="dialog"]');
      expect(jsonModal).not.toBeVisible();
    });
  });

  describe('Contenu JSON', () => {
    test('Devrait afficher le template ID correct dans le titre', () => {
      // Arrange
      const templateId = 42;

      // Act
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();

      // Assert
      const modalTitle = document.querySelector('h3:contains("JSON Brut")');
      expect(modalTitle.textContent).toContain(`ID: ${templateId}`);
    });

    test('Devrait inclure tous les éléments du template', () => {
      // Arrange
      const mockTemplate = {
        id: 1,
        name: 'Test',
        elements: [
          { id: 'elem1', type: 'text' },
          { id: 'elem2', type: 'image' },
          { id: 'elem3', type: 'line' }
        ]
      };

      // Act
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();

      // Assert
      const jsonContent = document.querySelector('.json-content');
      const displayedJSON = JSON.parse(jsonContent.textContent);
      expect(displayedJSON.elements).toHaveLength(3);
      expect(displayedJSON.elements[0]).toHaveProperty('id', 'elem1');
    });

    test('Devrait afficher la structure complète du template', () => {
      // Arrange
      const expectedStructure = [
        'id',
        'name',
        'description',
        'tags',
        'canvasWidth',
        'canvasHeight',
        'marginTop',
        'marginBottom',
        'showGuides',
        'snapToGrid',
        'elements'
      ];

      // Act
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();

      // Assert
      const jsonContent = document.querySelector('.json-content');
      const displayedJSON = JSON.parse(jsonContent.textContent);
      
      expectedStructure.forEach(key => {
        expect(displayedJSON).toHaveProperty(key);
      });
    });
  });

  describe('Accessibilité', () => {
    test('La modale devrait avoir des labels descriptifs', () => {
      // Arrange
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();

      // Assert
      const copyButton = document.querySelector('button:contains("Copier JSON")');
      const downloadButton = document.querySelector('button:contains("Télécharger")');
      const closeButton = document.querySelector('button:contains("Fermer")');

      expect(copyButton.title || copyButton.getAttribute('aria-label')).toBeDefined();
      expect(downloadButton.title || downloadButton.getAttribute('aria-label')).toBeDefined();
      expect(closeButton.title || closeButton.getAttribute('aria-label')).toBeDefined();
    });

    test('Le JSON devrait être selectionnable pour copie manuelle', () => {
      // Arrange
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();

      // Act
      const jsonContent = document.querySelector('.json-content');

      // Assert
      const computedStyle = window.getComputedStyle(jsonContent);
      expect(computedStyle.userSelect).not.toBe('none');
      expect(computedStyle.whiteSpace).toBe('pre-wrap');
    });
  });

  describe('Performance', () => {
    test('La modale devrait s\'ouvrir rapidement', () => {
      // Arrange
      const startTime = performance.now();

      // Act
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();
      const endTime = performance.now();

      // Assert
      expect(endTime - startTime).toBeLessThan(500); // < 500ms
    });

    test('Le copie JSON devrait être instantanée', async () => {
      // Arrange
      const mockClipboard = {
        writeText: jest.fn().mockResolvedValue(undefined)
      };
      Object.assign(navigator, { clipboard: mockClipboard });
      
      const startTime = performance.now();

      // Act
      const copyButton = document.querySelector('button:contains("Copier JSON")');
      copyButton.click();
      
      await waitFor(() => {
        expect(mockClipboard.writeText).toHaveBeenCalled();
      });
      
      const endTime = performance.now();

      // Assert
      expect(endTime - startTime).toBeLessThan(100); // < 100ms
    });
  });

  describe('Cas limites', () => {
    test('Devrait gérer les templates vides', () => {
      // Arrange
      const emptyTemplate = {
        id: 0,
        name: '',
        elements: []
      };

      // Act
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();

      // Assert
      const jsonContent = document.querySelector('.json-content');
      expect(jsonContent.textContent).toContain('[]');
    });

    test('Devrait gérer les JSON volumineux', () => {
      // Arrange
      const largeTemplate = {
        id: 1,
        name: 'Large',
        elements: Array(1000).fill({ type: 'text', x: 0, y: 0 })
      };

      // Act
      const previewButton = document.querySelector('button:contains("👁️")');
      previewButton.click();

      // Assert - devrait toujours afficher
      const jsonContent = document.querySelector('.json-content');
      expect(jsonContent).toBeTruthy();
      
      // Le conteneur devrait être scrollable
      const computedStyle = window.getComputedStyle(jsonContent.parentElement);
      expect(computedStyle.overflow).toMatch(/auto|scroll/);
    });
  });
});

describe('Intégration avec BuilderContext', () => {
  test('Devrait accéder au state.template du BuilderContext', () => {
    // Arrange
    const { useBuilder } = require('../../contexts/builder/BuilderContext');
    const mockState = {
      template: {
        id: 123,
        name: 'Test',
        elements: []
      }
    };

    // Act
    jest.mock('../../contexts/builder/BuilderContext', () => ({
      useBuilder: jest.fn(() => ({
        state: mockState,
        dispatch: jest.fn()
      }))
    }));

    // Assert
    const { state } = useBuilder();
    expect(state.template).toEqual(mockState.template);
  });
});

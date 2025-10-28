/**
 * Test de la détection et du chargement des templates existants
 */

import React from 'react';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import { PDFBuilder } from '../assets/js/src/pdf-builder-react/PDFBuilder';
import { BuilderProvider } from '../assets/js/src/pdf-builder-react/contexts/builder/BuilderContext';

// Mock de l'URL pour simuler les paramètres
const mockLocation = {
  search: ''
};

Object.defineProperty(window, 'location', {
  value: mockLocation,
  writable: true
});

// Mock de URLSearchParams
global.URLSearchParams = jest.fn().mockImplementation((search) => ({
  get: jest.fn((key) => {
    if (key === 'template_id') {
      return search.includes('template_id=1') ? '1' : null;
    }
    return null;
  })
}));

describe('Template Loading and Editing', () => {
  beforeEach(() => {
    // Reset des mocks
    jest.clearAllMocks();
  });

  test('should display "Enregistrer" button for new template', () => {
    mockLocation.search = '';

    render(
      <BuilderProvider>
        <PDFBuilder />
      </BuilderProvider>
    );

    // Le bouton devrait afficher "Enregistrer" pour un nouveau template
    expect(screen.getByText('Enregistrer')).toBeInTheDocument();
  });

  test('should display "Modifier" button when editing existing template', () => {
    mockLocation.search = '?template_id=1';

    render(
      <BuilderProvider>
        <PDFBuilder />
      </BuilderProvider>
    );

    // Le bouton devrait afficher "Modifier" quand on édite un template existant
    expect(screen.getByText('Modifier')).toBeInTheDocument();
  });

  test('should load existing template when template_id is present in URL', async () => {
    mockLocation.search = '?template_id=1';

    // Mock console.log pour vérifier les appels
    const consoleSpy = jest.spyOn(console, 'log').mockImplementation();

    render(
      <BuilderProvider>
        <PDFBuilder />
      </BuilderProvider>
    );

    // Attendre que le template soit chargé
    await waitFor(() => {
      expect(consoleSpy).toHaveBeenCalledWith('Chargement du template:', '1');
    });

    consoleSpy.mockRestore();
  });

  test('should not load template when no template_id in URL', () => {
    mockLocation.search = '';

    const consoleSpy = jest.spyOn(console, 'log').mockImplementation();

    render(
      <BuilderProvider>
        <PDFBuilder />
      </BuilderProvider>
    );

    // Vérifier qu'aucun chargement de template n'est déclenché
    expect(consoleSpy).not.toHaveBeenCalledWith('Chargement du template:', expect.any(String));

    consoleSpy.mockRestore();
  });

  test('should show correct button tooltip for new template', () => {
    mockLocation.search = '';

    render(
      <BuilderProvider>
        <PDFBuilder />
      </BuilderProvider>
    );

    const saveButton = screen.getByRole('button', { name: /enregistrer/i });
    // Le bouton est disabled quand isModified est false
    expect(saveButton).toBeDisabled();
  });

  test('should show correct button tooltip when editing existing template', () => {
    mockLocation.search = '?template_id=1';

    render(
      <BuilderProvider>
        <PDFBuilder />
      </BuilderProvider>
    );

    const saveButton = screen.getByRole('button', { name: /modifier/i });
    // Le bouton est disabled quand isModified est false
    expect(saveButton).toBeDisabled();
  });
});
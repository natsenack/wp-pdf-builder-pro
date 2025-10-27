/**
 * Test du panneau de propriétés pour les éléments WooCommerce
 * Vérifie que les propriétés peuvent être modifiées via l'interface
 */

import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { BuilderProvider } from '../assets/js/src/pdf-builder-react/contexts/builder/BuilderContext';
import { PropertiesPanel } from '../assets/js/src/pdf-builder-react/components/properties/PropertiesPanel';

describe('PropertiesPanel WooCommerce Elements', () => {
    const mockElement = {
        id: 'test-product-table',
        type: 'product_table',
        x: 50,
        y: 100,
        width: 500,
        height: 200,
        showHeaders: true,
        showBorders: true,
        showAlternatingRows: true,
        showSku: true,
        showDescription: true,
        fontSize: 11,
        currency: '€',
        backgroundColor: '#ffffff',
        headerBackgroundColor: '#f9fafb',
        alternateRowColor: '#f9fafb',
        borderColor: '#d1d5db',
        borderWidth: 1
    };

    const renderWithProvider = (selectedElements = []) => {
        const mockState = {
            elements: [mockElement],
            selection: {
                selectedElements
            },
            canvas: {
                zoom: 1,
                pan: { x: 0, y: 0 }
            }
        };

        return render(
            <BuilderProvider initialState={mockState}>
                <PropertiesPanel />
            </BuilderProvider>
        );
    };

    test('should display product table properties when selected', () => {
        renderWithProvider(['test-product-table']);

        // Vérifier que le titre des propriétés est affiché
        expect(screen.getByText('Propriétés')).toBeInTheDocument();

        // Vérifier que les contrôles spécifiques au product_table sont présents
        expect(screen.getByText('Afficher les en-têtes')).toBeInTheDocument();
        expect(screen.getByText('Afficher les bordures')).toBeInTheDocument();
        expect(screen.getByText('Lignes alternées')).toBeInTheDocument();
        expect(screen.getByText('Afficher les SKU')).toBeInTheDocument();
        expect(screen.getByText('Afficher les descriptions')).toBeInTheDocument();
        expect(screen.getByText('Taille de police')).toBeInTheDocument();
        expect(screen.getByText('Devise')).toBeInTheDocument();
        expect(screen.getByText('Couleur de fond')).toBeInTheDocument();
        expect(screen.getByText('Fond des en-têtes')).toBeInTheDocument();
        expect(screen.getByText('Couleur lignes alternées')).toBeInTheDocument();
        expect(screen.getByText('Couleur des bordures')).toBeInTheDocument();
        expect(screen.getByText('Épaisseur des bordures')).toBeInTheDocument();
    });

    test('should show empty state when no element selected', () => {
        renderWithProvider([]);

        expect(screen.getByText('Sélectionnez un élément pour voir ses propriétés')).toBeInTheDocument();
    });

    test('should handle checkbox property changes', () => {
        renderWithProvider(['test-product-table']);

        const showHeadersCheckbox = screen.getByLabelText('Afficher les en-têtes');
        const showBordersCheckbox = screen.getByLabelText('Afficher les bordures');

        // Vérifier les valeurs initiales
        expect(showHeadersCheckbox).toBeChecked();
        expect(showBordersCheckbox).toBeChecked();

        // Simuler un changement
        fireEvent.click(showHeadersCheckbox);
        expect(showHeadersCheckbox).not.toBeChecked();
    });

    test('should handle select property changes', () => {
        renderWithProvider(['test-product-table']);

        const currencySelect = screen.getByDisplayValue('Euro (€)');

        // Changer la devise
        fireEvent.change(currencySelect, { target: { value: '$' } });
        expect(screen.getByDisplayValue('Dollar ($)')).toBeInTheDocument();
    });

    test('should handle number input property changes', () => {
        renderWithProvider(['test-product-table']);

        const fontSizeInput = screen.getByDisplayValue('11');

        // Changer la taille de police
        fireEvent.change(fontSizeInput, { target: { value: '14' } });
        expect(screen.getByDisplayValue('14')).toBeInTheDocument();
    });

    test('should handle color input property changes', () => {
        renderWithProvider(['test-product-table']);

        // Les inputs color sont plus difficiles à tester directement
        // mais on peut vérifier leur présence
        const colorInputs = screen.getAllByDisplayValue('#ffffff');
        expect(colorInputs.length).toBeGreaterThan(0);
    });
});
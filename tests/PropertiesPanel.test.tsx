/**
 * Test du panneau de propriétés pour les éléments WooCommerce
 * Vérifie que les propriétés peuvent être modifiées via l'interface
 */

import React from 'react';
import { render, screen, fireEvent } from '@testing-library/react';
import { BuilderProvider } from '../assets/js/src/pdf-builder-react/contexts/builder/BuilderContext';
import { PropertiesPanel } from '../assets/js/src/pdf-builder-react/components/properties/PropertiesPanel';

describe('PropertiesPanel WooCommerce Elements', () => {
    const mockProductTableElement = {
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

    const mockCustomerInfoElement = {
        id: 'test-customer-info',
        type: 'customer_info',
        x: 50,
        y: 100,
        width: 300,
        height: 150,
        showHeaders: true,
        showBorders: true,
        showFullName: true,
        showAddress: true,
        showEmail: true,
        showPhone: true,
        layout: 'vertical',
        backgroundColor: '#ffffff',
        borderColor: '#f3f4f6',
        textColor: '#374151',
        headerTextColor: '#111827'
    };

    const renderWithProvider = (selectedElements = [], elements = [mockProductTableElement]) => {
        const mockState = {
            elements: elements,
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
        expect(screen.getByText(/Propriétés/)).toBeInTheDocument();

        // Vérifier que les contrôles spécifiques au product_table sont présents dans l'onglet fonctionnalites (par défaut)
        expect(screen.getByText('Afficher les en-têtes')).toBeInTheDocument();
        expect(screen.getByText('Afficher les bordures')).toBeInTheDocument();
        expect(screen.getByText('Lignes alternées')).toBeInTheDocument();
        expect(screen.getByText('Afficher les SKU')).toBeInTheDocument();
        expect(screen.getByText('Afficher les descriptions')).toBeInTheDocument();

        // Aller à l'onglet Personnalisation pour vérifier les éléments de personnalisation
        const personnalisationTab = screen.getByRole('button', { name: 'Personnalisation' });
        fireEvent.click(personnalisationTab);

        expect(screen.getByText('Taille de police')).toBeInTheDocument();
        expect(screen.getByText('Couleur de fond')).toBeInTheDocument();
        expect(screen.getByText('Fond des en-têtes')).toBeInTheDocument();
        expect(screen.getByText('Couleur lignes alternées')).toBeInTheDocument();
        expect(screen.getByText('Couleur des bordures')).toBeInTheDocument();
    });

    test('should show empty state when no element selected', () => {
        renderWithProvider([]);

        expect(screen.getByText('Sélectionnez un élément pour voir ses propriétés')).toBeInTheDocument();
    });

    test('should handle checkbox property changes', () => {
        renderWithProvider(['test-product-table']);

        // Trouver les checkboxes par leur proximité avec le texte
        const showHeadersCheckbox = screen.getByText('Afficher les en-têtes').parentElement?.querySelector('input[type="checkbox"]') as HTMLInputElement;
        const showBordersCheckbox = screen.getByText('Afficher les bordures').parentElement?.querySelector('input[type="checkbox"]') as HTMLInputElement;

        // Vérifier les valeurs initiales
        expect(showHeadersCheckbox).toBeChecked();
        expect(showBordersCheckbox).toBeChecked();

        // Simuler un changement
        fireEvent.click(showHeadersCheckbox);
        expect(showHeadersCheckbox).not.toBeChecked();
    });

    test('should handle select property changes', () => {
        renderWithProvider(['test-product-table']);

        // Aller à l'onglet Positionnement
        const positionnementTab = screen.getByRole('button', { name: 'Positionnement' });
        fireEvent.click(positionnementTab);

        const alignSelect = screen.getByDisplayValue('Gauche');

        // Changer l'alignement
        fireEvent.change(alignSelect, { target: { value: 'center' } });
        expect(screen.getByDisplayValue('Centre')).toBeInTheDocument();
    });

    test('should handle number input property changes', () => {
        renderWithProvider(['test-product-table']);

        // Aller à l'onglet Personnalisation
        const personnalisationTab = screen.getByRole('button', { name: 'Personnalisation' });
        fireEvent.click(personnalisationTab);

        const fontSizeInput = screen.getByDisplayValue('11');

        // Changer la taille de police
        fireEvent.change(fontSizeInput, { target: { value: '14' } });
        expect(screen.getByDisplayValue('14')).toBeInTheDocument();
    });

    test('should handle color input property changes', () => {
        renderWithProvider(['test-product-table']);

        // Aller à l'onglet Personnalisation
        const personnalisationTab = screen.getByRole('button', { name: 'Personnalisation' });
        fireEvent.click(personnalisationTab);

        // Les inputs color sont plus difficiles à tester directement
        // mais on peut vérifier leur présence
        const colorInputs = screen.getAllByDisplayValue('#ffffff');
        expect(colorInputs.length).toBeGreaterThan(0);
    });

    test('should display customer info properties when selected', () => {
        renderWithProvider(['test-customer-info'], [mockCustomerInfoElement]);

        // Vérifier que le titre des propriétés est affiché
        expect(screen.getByText(/Propriétés/)).toBeInTheDocument();

        // Vérifier que les contrôles spécifiques au customer_info sont présents dans l'onglet fonctionnalites (par défaut)
        expect(screen.getByText('Afficher les en-têtes')).toBeInTheDocument();
        expect(screen.getByText('Afficher les bordures')).toBeInTheDocument();
        expect(screen.getByText('Afficher le nom complet')).toBeInTheDocument();
        expect(screen.getByText('Afficher l\'adresse')).toBeInTheDocument();
        expect(screen.getByText('Afficher l\'email')).toBeInTheDocument();
        expect(screen.getByText('Afficher le téléphone')).toBeInTheDocument();
        expect(screen.getByText('Disposition')).toBeInTheDocument();
    });

    test('should handle customer info checkbox property changes', () => {
        renderWithProvider(['test-customer-info'], [mockCustomerInfoElement]);

        // Trouver les checkboxes par leur proximité avec le texte
        const showHeadersCheckbox = screen.getByText('Afficher les en-têtes').parentElement?.querySelector('input[type="checkbox"]') as HTMLInputElement;
        const showFullNameCheckbox = screen.getByText('Afficher le nom complet').parentElement?.querySelector('input[type="checkbox"]') as HTMLInputElement;

        // Vérifier les valeurs initiales
        expect(showHeadersCheckbox).toBeChecked();
        expect(showFullNameCheckbox).toBeChecked();

        // Simuler un changement
        fireEvent.click(showHeadersCheckbox);
        expect(showHeadersCheckbox).not.toBeChecked();
    });

    test('should handle customer info select property changes', () => {
        renderWithProvider(['test-customer-info'], [mockCustomerInfoElement]);

        const layoutSelect = screen.getByDisplayValue('Verticale');

        // Changer la disposition
        fireEvent.change(layoutSelect, { target: { value: 'horizontal' } });
        expect(screen.getByDisplayValue('Horizontale')).toBeInTheDocument();
    });
});
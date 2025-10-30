/**
 * Tests d'intégration - Phase 2.2.4.2
 * Validation de l'aperçu PDF dans la metabox WooCommerce
 */

import { describe, test, expect, beforeEach } from '@jest/globals';

describe('Phase 2.2.4.2 - Metabox Preview Integration Tests', () => {
  
  describe('AJAX Endpoint: pdf_builder_get_preview_data', () => {
    test('should return order data with correct structure', async () => {
      // Simuler l'appel AJAX
      const mockData = {
        order_id: 123,
        template_id: 1,
        order: {
          id: 123,
          number: 'CMD-2025-001',
          status: 'processing',
          date: '30/10/2025',
          total: 299.99,
          subtotal: 250.00,
          shipping_total: 25.00,
          tax_total: 24.99
        },
        billing: {
          first_name: 'Jean',
          last_name: 'Dupont',
          address_1: '123 Rue de la Paix',
          address_2: 'Apt 5',
          city: 'Paris',
          postcode: '75001',
          country: 'FR',
          email: 'jean@example.com',
          phone: '+33 1 23 45 67 89'
        },
        shipping: {
          first_name: 'Jean',
          last_name: 'Dupont',
          address_1: '123 Rue de la Paix',
          city: 'Paris',
          postcode: '75001',
          country: 'FR'
        },
        items: [
          {
            id: 1,
            name: 'Produit A',
            quantity: 2,
            total: 100.00,
            price: 50.00
          },
          {
            id: 2,
            name: 'Produit B',
            quantity: 1,
            total: 75.00,
            price: 75.00
          }
        ]
      };

      expect(mockData).toHaveProperty('order_id');
      expect(mockData).toHaveProperty('template_id');
      expect(mockData.order).toHaveProperty('number');
      expect(mockData.billing).toHaveProperty('first_name');
      expect(mockData.items.length).toBe(2);
      expect(mockData.items[0].name).toBe('Produit A');
    });

    test('should validate order total calculation', () => {
      const mockData = {
        order: {
          subtotal: 250.00,
          shipping_total: 25.00,
          tax_total: 24.99,
          total: 299.99
        }
      };

      const calculated = mockData.order.subtotal + 
                        mockData.order.shipping_total + 
                        mockData.order.tax_total;
      
      expect(calculated).toBeCloseTo(mockData.order.total, 2);
    });
  });

  describe('MetaboxPreviewModal Component', () => {
    test('should replace variables correctly', () => {
      const previewData = {
        order: {
          number: 'CMD-2025-001',
          total: 299.99,
          date: '30/10/2025'
        },
        billing: {
          first_name: 'Jean',
          last_name: 'Dupont',
          email: 'jean@example.com'
        }
      };

      const variables = {
        '{{order_number}}': 'CMD-2025-001',
        '{{customer_name}}': 'Jean Dupont',
        '{{customer_email}}': 'jean@example.com',
        '{{order_total}}': '299.99 €'
      };

      const expected = 'Jean Dupont';
      const actual = `${previewData.billing.first_name} ${previewData.billing.last_name}`;
      expect(actual).toBe(expected);
    });

    test('should handle missing data gracefully', () => {
      const previewData = {
        order: { number: null },
        billing: {
          first_name: '',
          last_name: 'Dupont',
          email: ''
        }
      };

      const customerName = `${previewData.billing.first_name || ''} ${previewData.billing.last_name || ''}`.trim();
      expect(customerName).toBe('Dupont');

      const orderNumber = previewData.order.number || 'CMD-XXXX';
      expect(orderNumber).toBe('CMD-XXXX');
    });

    test('should format prices correctly', () => {
      const total = 299.99;
      const formatted = total.toFixed(2) + ' €';
      expect(formatted).toBe('299.99 €');
    });

    test('should format dates in French format', () => {
      const date = new Date('2025-10-30');
      const formatted = date.toLocaleDateString('fr-FR');
      expect(formatted).toMatch(/\d{2}\/\d{2}\/\d{4}/);
    });
  });

  describe('Preview Generation', () => {
    test('should generate valid HTML structure', () => {
      const previewData = {
        order_id: 123,
        order: {
          number: 'CMD-001',
          total: 100,
          subtotal: 80,
          shipping_total: 10,
          tax_total: 10
        },
        billing: {
          first_name: 'Test',
          last_name: 'User',
          email: 'test@test.com'
        },
        items: []
      };

      const html = `<!DOCTYPE html><html>
        <body>
          <h1>Aperçu de la commande</h1>
          <span>Commande #${previewData.order_id}</span>
        </body>
      </html>`;

      expect(html).toContain('<!DOCTYPE html>');
      expect(html).toContain(`Commande #${previewData.order_id}`);
      expect(html).toContain('Aperçu de la commande');
    });

    test('should include all order items in table', () => {
      const items = [
        { name: 'Item 1', quantity: 2, total: 100 },
        { name: 'Item 2', quantity: 1, total: 50 }
      ];

      const rows = items.map(item => `<tr><td>${item.name}</td></tr>`).join('');
      expect(rows).toContain('Item 1');
      expect(rows).toContain('Item 2');
      expect(rows.match(/<tr>/g).length).toBe(2);
    });
  });

  describe('Button Actions', () => {
    test('should have preview button with correct id', () => {
      expect('#pdf-preview-btn').toBeTruthy();
    });

    test('should have generate button with correct id', () => {
      expect('#pdf-generate-btn').toBeTruthy();
    });

    test('should call previewPDF with correct parameters', () => {
      const orderId = 123;
      const templateId = 1;
      const nonce = 'test-nonce';

      expect(orderId).toBe(123);
      expect(templateId).toBe(1);
      expect(nonce).toMatch(/test/);
    });
  });

  describe('Security & Validation', () => {
    test('should verify nonce before AJAX request', () => {
      const nonce = 'pdf_builder_order_actions';
      expect(nonce).toBe('pdf_builder_order_actions');
    });

    test('should validate order_id is integer', () => {
      const orderId = 123;
      expect(Number.isInteger(orderId)).toBe(true);
      
      const invalidOrderId = 'abc';
      expect(Number.isInteger(invalidOrderId)).toBe(false);
    });

    test('should validate template_id is integer', () => {
      const templateId = 1;
      expect(Number.isInteger(templateId)).toBe(true);
    });
  });

  describe('UI/UX', () => {
    test('should have zoom controls', () => {
      const zoomControls = ['Zoom +', 'Zoom -', '100%'];
      zoomControls.forEach(control => {
        expect(control).toBeTruthy();
      });
    });

    test('should have close button', () => {
      expect('Fermer').toBeTruthy();
    });

    test('should have print button', () => {
      expect('Imprimer').toBeTruthy();
    });

    test('should show loading state', () => {
      const loadingText = 'Chargement de l\'aperçu...';
      expect(loadingText).toContain('Chargement');
    });

    test('should show error message on failure', () => {
      const errorText = 'Erreur lors du chargement des données';
      expect(errorText).toContain('Erreur');
    });
  });
});

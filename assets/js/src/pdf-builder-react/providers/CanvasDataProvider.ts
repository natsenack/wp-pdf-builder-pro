/**
 * CanvasDataProvider - Fournisseur de données fictives pour le mode Canvas
 *
 * Fournit des données d'exemple cohérentes pour l'aperçu en mode éditeur.
 */

import { DataProvider } from '../renderers/PreviewRenderer';

export class CanvasDataProvider implements DataProvider {
  getMode(): 'canvas' {
    return 'canvas';
  }

  getVariableValue(variable: string): string | any {
    const variables: { [key: string]: string | any } = {
      // Variables client fictives
      'customer_name': 'Jean Dupont',
      'customer_firstname': 'Jean',
      'customer_lastname': 'Dupont',
      'customer_email': 'jean.dupont@email.com',
      'customer_phone': '+33 1 23 45 67 89',
      'customer_address': '123 Rue de la Paix\n75001 Paris\nFrance',

      // Variables commande fictives
      'order_number': 'CMD-2025-001',
      'order_date': '30 octobre 2025',
      'order_status': 'pending',
      'order_total': '299,99 €',
      'document_type': 'FACTURE',

      // Variables entreprise fictives
      'company_name': 'Ma Société SARL',
      'company_address': '456 Avenue des Champs\n75008 Paris\nFrance',
      'company_phone': '+33 1 98 76 54 32',
      'company_email': 'contact@masociete.com',
      'company_vat': 'FR 12 345 678 901',

      // Variables produit fictives
      'products': 'Produit A - 99,99 €\nProduit B - 199,99 €\nSous-total: 299,99 €',
      'order_items': [
        { name: 'Produit A', quantity: 1, price: 99.99, total: 99.99 },
        { name: 'Produit B', quantity: 1, price: 199.99, total: 199.99 }
      ],

      // Variables de test
      'test_variable': 'Valeur de test Canvas'
    };

    return variables[variable] || `{{${variable}}}`;
  }
}
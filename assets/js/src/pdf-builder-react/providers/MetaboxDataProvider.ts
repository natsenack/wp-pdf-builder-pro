/**
 * MetaboxDataProvider - Fournisseur de données réelles pour le mode Metabox
 *
 * Fournit des données WooCommerce réelles pour l'aperçu en mode metabox.
 */

import { DataProvider } from '../renderers/PreviewRenderer';

export interface WooCommerceData {
  order_id: number;
  template_id: number;
  elements: any[];
  order: {
    id: number;
    number: string;
    date: string;
    status: string;
    total: number;
  };
  billing: {
    first_name: string;
    last_name: string;
    email: string;
    phone: string;
    address_1: string;
    address_2?: string;
    postcode: string;
    city: string;
    country: string;
  };
  shipping: {
    first_name: string;
    last_name: string;
    address_1: string;
    address_2?: string;
    postcode: string;
    city: string;
    country: string;
  };
  items: Array<{
    id: number;
    name: string;
    quantity: number;
    price: number;
    total: number;
  }>;
}

export class MetaboxDataProvider implements DataProvider {
  private data: WooCommerceData;

  constructor(data: WooCommerceData) {
    this.data = data;
  }

  getMode(): 'metabox' {
    return 'metabox';
  }

  getVariableValue(variable: string): string | any {
    const variables: { [key: string]: string | any } = {
      // Variables client réelles
      'customer_name': `${this.data.billing.first_name} ${this.data.billing.last_name}`.trim() || 'Client Inconnu',
      'customer_firstname': this.data.billing.first_name || 'Client',
      'customer_lastname': this.data.billing.last_name || 'Inconnu',
      'customer_email': this.data.billing.email || 'email@inconnu.com',
      'customer_phone': this.data.billing.phone || '+33 0 00 00 00 00',
      'customer_address': [
        this.data.billing.address_1,
        this.data.billing.address_2,
        this.data.billing.postcode,
        this.data.billing.city,
        this.data.billing.country
      ].filter(Boolean).join('\n'),

      // Variables commande réelles
      'order_number': this.data.order.number || `CMD-${this.data.order.id}`,
      'order_date': this.formatDate(this.data.order.date),
      'order_status': this.formatOrderStatus(this.data.order.status),
      'order_total': `${this.data.order.total.toFixed(2)} €`,

      // Variables entreprise (à récupérer depuis les settings WooCommerce)
      'company_name': this.getCompanySetting('company_name', 'Ma Société SARL'),
      'company_address': this.getCompanySetting('company_address', '456 Avenue des Champs\n75008 Paris\nFrance'),
      'company_phone': this.getCompanySetting('company_phone', '+33 1 98 76 54 32'),
      'company_email': this.getCompanySetting('company_email', 'contact@masociete.com'),
      'company_vat': this.getCompanySetting('company_vat', 'FR 12 345 678 901'),

      // Variables produits
      'products': this.formatProducts(),
      'order_items': this.data.items || [],

      // Variables de test
      'test_variable': 'Valeur de test Metabox'
    };

    return variables[variable] || `{{${variable}}}`;
  }

  private formatDate(dateString: string): string {
    try {
      const date = new Date(dateString);
      return date.toLocaleDateString('fr-FR');
    } catch {
      return new Date().toLocaleDateString('fr-FR');
    }
  }

  private formatOrderStatus(status: string): string {
    const statusMap: { [key: string]: string } = {
      'pending': 'En attente',
      'processing': 'En cours',
      'on-hold': 'En attente',
      'completed': 'Terminée',
      'cancelled': 'Annulée',
      'refunded': 'Remboursée',
      'failed': 'Échouée'
    };

    return statusMap[status] || status;
  }

  private getCompanySetting(setting: string, defaultValue: string): string {
    // TODO: Récupérer depuis les settings WooCommerce
    // Pour l'instant, retourner les valeurs par défaut
    return defaultValue;
  }

  private formatProducts(): string {
    if (!this.data.items || this.data.items.length === 0) {
      return 'Aucun produit';
    }

    return this.data.items.map(item =>
      `${item.name} (x${item.quantity}) - ${item.total.toFixed(2)} €`
    ).join('\n');
  }

  /**
   * Met à jour les données WooCommerce
   */
  updateData(data: WooCommerceData): void {
    this.data = data;
  }

  /**
   * Retourne les données brutes
   */
  getRawData(): WooCommerceData {
    return this.data;
  }
}
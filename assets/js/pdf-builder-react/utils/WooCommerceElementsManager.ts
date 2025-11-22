import {
  WooCommerceOrder,
  WooCommerceOrderItem,
  WooCommerceCustomer
} from './woocommerce-types';

export class WooCommerceElementsManager {
  private orderData: WooCommerceOrder | null = null;
  private customerData: WooCommerceCustomer | null = null;

  /**
   * Charge les données de commande WooCommerce
   */
  async loadOrderData(orderId: string): Promise<WooCommerceOrder> {
    // Simulation d'un appel API WooCommerce
    // En production, ceci ferait un appel à l'API REST WooCommerce
    const response = await this.mockFetchOrderData(orderId);
    this.orderData = response;
    return response;
  }

  /**
   * Charge les données client WooCommerce
   */
  async loadCustomerData(customerId: number): Promise<WooCommerceCustomer> {
    // Simulation d'un appel API WooCommerce
    const response = await this.mockFetchCustomerData(customerId);
    this.customerData = response;
    return response;
  }

  /**
   * Obtient les données de commande actuelles
   */
  getOrderData(): WooCommerceOrder | null {
    return this.orderData;
  }

  /**
   * Obtient les données client actuelles
   */
  getCustomerData(): WooCommerceCustomer | null {
    return this.customerData;
  }

  /**
   * Obtient le numéro de commande formaté
   */
  getOrderNumber(): string {
    if (!this.orderData) return 'CMD-XXXX-XXXX';
    return this.orderData.order_number || `CMD-${this.orderData.id}`;
  }

  /**
   * Obtient les informations client formatées
   */
  getCustomerInfo(): {
    name: string;
    address: string;
    email: string;
    phone: string;
  } {
    if (!this.customerData && !this.orderData) {
      return {
        name: 'Client Inconnu',
        address: 'Adresse non disponible',
        email: 'email@inconnu.com',
        phone: '+33 0 00 00 00 00'
      };
    }

    const billing = this.customerData?.billing || this.orderData?.billing;

    if (!billing) {
      return {
        name: 'Client Inconnu',
        address: 'Adresse non disponible',
        email: 'email@inconnu.com',
        phone: '+33 0 00 00 00 00'
      };
    }

    const fullName = `${billing.first_name} ${billing.last_name}`.trim();
    const address = [
      billing.address_1,
      billing.address_2,
      `${billing.postcode} ${billing.city}`,
      billing.country
    ].filter(Boolean).join(', ');

    return {
      name: fullName || 'Client Inconnu',
      address: address || 'Adresse non disponible',
      email: billing.email || 'email@inconnu.com',
      phone: billing.phone || '+33 0 00 00 00 00'
    };
  }

  /**
   * Obtient les articles de commande formatés pour l'affichage
   */
  getOrderItems(): Array<{
    sku: string;
    name: string;
    description: string;
    qty: number;
    price: number;
    discount: number;
    total: number;
  }> {
    if (!this.orderData) {
      return [];
    }

    return this.orderData.line_items.map(item => ({
      sku: item.sku || `SKU-${item.product_id}`,
      name: item.name,
      description: this.getProductDescription(item),
      qty: item.quantity,
      price: parseFloat(item.subtotal) / item.quantity,
      discount: this.calculateItemDiscount(item),
      total: parseFloat(item.total)
    }));
  }

  /**
   * Calcule les totaux de commande
   */
  getOrderTotals(): {
    subtotal: number;
    discount: number;
    shipping: number;
    tax: number;
    total: number;
    currency: string;
  } {
    if (!this.orderData) {
      return {
        subtotal: 0,
        discount: 0,
        shipping: 0,
        tax: 0,
        total: 0,
        currency: 'EUR'
      };
    }

    const subtotal = parseFloat(this.orderData.subtotal);
    const discount = parseFloat(this.orderData.discount_total);
    const shipping = parseFloat(this.orderData.shipping_total);
    const tax = parseFloat(this.orderData.total_tax);
    const total = parseFloat(this.orderData.total);

    return {
      subtotal,
      discount,
      shipping,
      tax,
      total,
      currency: this.orderData.currency
    };
  }

  /**
   * Obtient la date de commande formatée
   */
  getOrderDate(): string {
    if (!this.orderData) return new Date().toLocaleDateString('fr-FR');
    return new Date(this.orderData.date_created).toLocaleDateString('fr-FR');
  }

  /**
   * Réinitialise les données
   */
  reset(): void {
    this.orderData = null;
    this.customerData = null;
  }

  // Méthodes privées

  private getProductDescription(item: WooCommerceOrderItem): string {
    // Recherche dans les meta_data pour une description
    const descriptionMeta = item.meta_data.find(meta => meta.key === '_description');
    return descriptionMeta?.value || 'Description non disponible';
  }

  private calculateItemDiscount(item: WooCommerceOrderItem): number {
    const subtotal = parseFloat(item.subtotal);
    const total = parseFloat(item.total);
    return Math.max(0, subtotal - total);
  }

  // Méthodes de simulation (à remplacer par de vrais appels API)

  private async mockFetchOrderData(orderId: string): Promise<WooCommerceOrder> {
    // Simulation d'un délai réseau
    await new Promise(resolve => setTimeout(resolve, 100));

    // Données fictives réalistes
    return {
      id: parseInt(orderId),
      order_number: `CMD-2024-${orderId.padStart(4, '0')}`,
      status: 'completed',
      currency: 'EUR',
      date_created: new Date().toISOString(),
      date_modified: new Date().toISOString(),
      total: '279.96',
      subtotal: '259.96',
      total_tax: '20.00',
      shipping_total: '8.50',
      discount_total: '15.00',
      customer_id: 123,
      billing: {
        first_name: 'Marie',
        last_name: 'Dupont',
        company: '',
        address_1: '15 rue des Lilas',
        address_2: '',
        city: 'Paris',
        state: '',
        postcode: '75001',
        country: 'FR',
        email: 'marie.dupont@email.com',
        phone: '+33 6 12 34 56 78'
      },
      shipping: {
        first_name: 'Marie',
        last_name: 'Dupont',
        company: '',
        address_1: '15 rue des Lilas',
        address_2: '',
        city: 'Paris',
        state: '',
        postcode: '75001',
        country: 'FR'
      },
      line_items: [
        {
          id: 1,
          name: 'T-shirt Premium Bio',
          product_id: 123,
          variation_id: 0,
          quantity: 2,
          tax_class: '',
          subtotal: '59.98',
          subtotal_tax: '11.996',
          total: '59.98',
          total_tax: '11.996',
          taxes: [{ id: 1, total: '11.996', subtotal: '11.996' }],
          meta_data: [],
          sku: 'TSHIRT-001',
          price: 29.99
        },
        {
          id: 2,
          name: 'Jean Slim Fit Noir',
          product_id: 456,
          variation_id: 0,
          quantity: 1,
          tax_class: '',
          subtotal: '89.99',
          subtotal_tax: '17.998',
          total: '79.99',
          total_tax: '15.998',
          taxes: [{ id: 1, total: '15.998', subtotal: '17.998' }],
          meta_data: [],
          sku: 'JEAN-045',
          price: 89.99
        }
      ],
      shipping_lines: [{
        id: 1,
        method_title: 'Livraison Standard',
        method_id: 'flat_rate',
        total: '8.50',
        total_tax: '0.00',
        taxes: []
      }],
      tax_lines: [{
        id: 1,
        rate_code: 'FR-TVA-20',
        rate_id: 1,
        label: 'TVA (20%)',
        compound: false,
        tax_total: '20.00',
        shipping_tax_total: '0.00',
        rate_percent: 20
      }],
      coupon_lines: [{
        id: 1,
        code: 'ETE2024',
        discount: '15.00',
        discount_tax: '0.00'
      }]
    };
  }

  private async mockFetchCustomerData(customerId: number): Promise<WooCommerceCustomer> {
    // Simulation d'un délai réseau
    await new Promise(resolve => setTimeout(resolve, 50));

    return {
      id: customerId,
      date_created: new Date().toISOString(),
      date_modified: new Date().toISOString(),
      email: 'marie.dupont@email.com',
      first_name: 'Marie',
      last_name: 'Dupont',
      role: 'customer',
      username: 'marie_dupont',
      billing: {
        first_name: 'Marie',
        last_name: 'Dupont',
        company: '',
        address_1: '15 rue des Lilas',
        address_2: '',
        city: 'Paris',
        state: '',
        postcode: '75001',
        country: 'FR',
        email: 'marie.dupont@email.com',
        phone: '+33 6 12 34 56 78'
      },
      shipping: {
        first_name: 'Marie',
        last_name: 'Dupont',
        company: '',
        address_1: '15 rue des Lilas',
        address_2: '',
        city: 'Paris',
        state: '',
        postcode: '75001',
        country: 'FR'
      }
    };
  }
}

// Instance singleton
export const wooCommerceManager = new WooCommerceElementsManager();

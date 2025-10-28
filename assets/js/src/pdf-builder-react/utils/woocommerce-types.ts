// Interfaces pour les données WooCommerce
export interface WooCommerceOrder {
  id: number;
  order_number: string;
  status: string;
  currency: string;
  date_created: string;
  date_modified: string;
  total: string;
  subtotal: string;
  total_tax: string;
  shipping_total: string;
  discount_total: string;
  customer_id: number;
  billing: {
    first_name: string;
    last_name: string;
    company: string;
    address_1: string;
    address_2: string;
    city: string;
    state: string;
    postcode: string;
    country: string;
    email: string;
    phone: string;
  };
  shipping: {
    first_name: string;
    last_name: string;
    company: string;
    address_1: string;
    address_2: string;
    city: string;
    state: string;
    postcode: string;
    country: string;
  };
  line_items: WooCommerceOrderItem[];
  shipping_lines: WooCommerceShippingLine[];
  tax_lines: WooCommerceTaxLine[];
  coupon_lines: WooCommerceCouponLine[];
}

export interface WooCommerceOrderItem {
  id: number;
  name: string;
  product_id: number;
  variation_id: number;
  quantity: number;
  tax_class: string;
  subtotal: string;
  subtotal_tax: string;
  total: string;
  total_tax: string;
  taxes: Array<{
    id: number;
    total: string;
    subtotal: string;
  }>;
  meta_data: Array<{
    id: number;
    key: string;
    value: string;
  }>;
  sku: string;
  price: number;
  image?: {
    id: number;
    src: string;
  };
}

export interface WooCommerceShippingLine {
  id: number;
  method_title: string;
  method_id: string;
  total: string;
  total_tax: string;
  taxes: Array<{
    id: number;
    total: string;
    subtotal: string;
  }>;
}

export interface WooCommerceTaxLine {
  id: number;
  rate_code: string;
  rate_id: number;
  label: string;
  compound: boolean;
  tax_total: string;
  shipping_tax_total: string;
  rate_percent: number;
}

export interface WooCommerceCouponLine {
  id: number;
  code: string;
  discount: string;
  discount_tax: string;
}

export interface WooCommerceCustomer {
  id: number;
  date_created: string;
  date_modified: string;
  email: string;
  first_name: string;
  last_name: string;
  role: string;
  username: string;
  billing: {
    first_name: string;
    last_name: string;
    company: string;
    address_1: string;
    address_2: string;
    city: string;
    state: string;
    postcode: string;
    country: string;
    email: string;
    phone: string;
  };
  shipping: {
    first_name: string;
    last_name: string;
    company: string;
    address_1: string;
    address_2: string;
    city: string;
    state: string;
    postcode: string;
    country: string;
  };
}
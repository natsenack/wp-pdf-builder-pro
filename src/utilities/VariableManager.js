// VariableManager.js
class VariableManager {
  // Données de test pour l'aperçu dans l'éditeur (comme OrderValueRetriever->useTestData = true)
  static getTestData() {
    return {
      order_number: 'CMD-001',
      order_date: '15/01/2024',
      order_total: '125,00 €',
      order_subtotal: '100,00 €',
      order_tax: '25,00 €',
      customer_name: 'Jean Dupont',
      customer_email: 'jean.dupont@email.com',
      billing_address: '123 Rue de la Paix\n75001 Paris\nFrance',
      shipping_address: '123 Rue de la Paix\n75001 Paris\nFrance',
      payment_method: 'Carte bancaire',
      shipping_method: 'Colissimo'
    };
  }

  // Récupère les données selon le contexte (test ou réel)
  static getData(useRealData = false, orderData = null) {
    if (useRealData && orderData) {
      // Fusionner avec les données réelles si disponibles
      return { ...this.getTestData(), ...orderData };
    }
    return this.getTestData();
  }

  // Substitution des variables dans le texte (comme TagManager::Process())
  static processText(text, useRealData = false, orderData = null) {
    const data = this.getData(useRealData, orderData);

    return text
      .replace(/\[order_number\]/g, data.order_number)
      .replace(/\[order_date\]/g, data.order_date)
      .replace(/\[order_total\]/g, data.order_total)
      .replace(/\[order_subtotal\]/g, data.order_subtotal)
      .replace(/\[order_tax\]/g, data.order_tax)
      .replace(/\[customer_name\]/g, data.customer_name)
      .replace(/\[customer_email\]/g, data.customer_email)
      .replace(/\[billing_address\]/g, data.billing_address.replace(/\n/g, '<br>'))
      .replace(/\[shipping_address\]/g, data.shipping_address.replace(/\n/g, '<br>'))
      .replace(/\[payment_method\]/g, data.payment_method)
      .replace(/\[shipping_method\]/g, data.shipping_method);
  }

  // Version pour l'affichage HTML (remplace \n par <br>)
  static processTextForHTML(text, useRealData = false, orderData = null) {
    return this.processText(text, useRealData, orderData);
  }

  // Version pour l'affichage texte brut (conserve \n)
  static processTextForPreview(text, useRealData = false, orderData = null) {
    const data = this.getData(useRealData, orderData);

    return text
      .replace(/\[order_number\]/g, data.order_number)
      .replace(/\[order_date\]/g, data.order_date)
      .replace(/\[order_total\]/g, data.order_total)
      .replace(/\[order_subtotal\]/g, data.order_subtotal)
      .replace(/\[order_tax\]/g, data.order_tax)
      .replace(/\[customer_name\]/g, data.customer_name)
      .replace(/\[customer_email\]/g, data.customer_email)
      .replace(/\[billing_address\]/g, data.billing_address)
      .replace(/\[shipping_address\]/g, data.shipping_address)
      .replace(/\[payment_method\]/g, data.payment_method)
      .replace(/\[shipping_method\]/g, data.shipping_method);
  }

  // Liste des variables disponibles pour l'autocomplétion
  static getAvailableVariables() {
    return [
      { key: '[order_number]', label: 'Numéro de commande', example: 'CMD-001' },
      { key: '[order_date]', label: 'Date de commande', example: '15/01/2024' },
      { key: '[order_total]', label: 'Total commande', example: '125,00 €' },
      { key: '[order_subtotal]', label: 'Sous-total', example: '100,00 €' },
      { key: '[order_tax]', label: 'TVA', example: '25,00 €' },
      { key: '[customer_name]', label: 'Nom client', example: 'Jean Dupont' },
      { key: '[customer_email]', label: 'Email client', example: 'jean.dupont@email.com' },
      { key: '[billing_address]', label: 'Adresse de facturation', example: '123 Rue de la Paix...' },
      { key: '[shipping_address]', label: 'Adresse de livraison', example: '123 Rue de la Paix...' },
      { key: '[payment_method]', label: 'Méthode de paiement', example: 'Carte bancaire' },
      { key: '[shipping_method]', label: 'Méthode de livraison', example: 'Colissimo' }
    ];
  }
}

export default VariableManager;
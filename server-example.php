<?php
/**
 * Exemple d'implémentation côté serveur fidèle à woo-pdf-invoice-builder
 * Ce fichier montre comment notre système pourrait générer le PDF
 */

// Simule la classe TagManager de l'autre plugin
class TagManager {
    private $orderValueRetriever;

    public function __construct($orderValueRetriever) {
        $this->orderValueRetriever = $orderValueRetriever;
    }

    // Méthode Process() comme dans l'autre plugin
    public function Process($text) {
        // Données de test ou réelles selon le contexte
        $data = $this->orderValueRetriever->useTestData ?
            $this->getTestData() : $this->getRealData();

        return str_replace(
            array_keys($data),
            array_values($data),
            $text
        );
    }

    private function getTestData() {
        return [
            '[order_number]' => 'CMD-001',
            '[order_date]' => '15/01/2024',
            '[order_total]' => '125,00 €',
            '[customer_name]' => 'Jean Dupont',
            '[customer_email]' => 'jean.dupont@email.com',
            '[billing_address]' => "123 Rue de la Paix\n75001 Paris\nFrance",
            // ... autres variables
        ];
    }

    private function getRealData() {
        // Ici on récupérerait les vraies données WooCommerce
        return [
            '[order_number]' => $this->orderValueRetriever->order->get_order_number(),
            '[order_date]' => date('d/m/Y', strtotime($this->orderValueRetriever->order->get_date_created())),
            '[order_total]' => wc_price($this->orderValueRetriever->order->get_total()),
            // ... autres variables réelles
        ];
    }
}

// Simule la classe OrderValueRetriever
class OrderValueRetriever {
    public $useTestData = true;
    public $order = null;

    public function TranslateText($fieldId, $propertyName, $default) {
        // Logique de traduction comme dans l'autre plugin
        return $default; // Pour l'exemple simplifié
    }
}

// Simule un élément PDF (comme PDFText)
class PDFText {
    private $options;
    private $orderValueRetriever;

    public function __construct($options, $orderValueRetriever) {
        $this->options = $options;
        $this->orderValueRetriever = $orderValueRetriever;
    }

    // Méthode InternalGetHTML() comme dans l'autre plugin
    protected function InternalGetHTML() {
        $text = '<p style="vertical-align: top;">';
        $text .= ' ' . $this->orderValueRetriever->TranslateText(
            $this->options->fieldID,
            'text',
            $this->options->text // Le texte avec variables [order_number], etc.
        );
        $text .= ' </p>';

        // Substitution des variables comme dans l'autre plugin
        $tagManager = new TagManager($this->orderValueRetriever);
        $text = $tagManager->Process($text);

        return $text;
    }

    public function render() {
        return $this->InternalGetHTML();
    }
}

// Exemple d'utilisation
$orderValueRetriever = new OrderValueRetriever();

// Élément avec variables (comme défini dans notre ElementLibrary)
$elementOptions = (object) [
    'fieldID' => 'order_info',
    'text' => 'Commande [order_number] - Date: [order_date]'
];

$element = new PDFText($elementOptions, $orderValueRetriever);
echo $element->render();
// Output: <p style="vertical-align: top;"> Commande CMD-001 - Date: 15/01/2024 </p>

?>
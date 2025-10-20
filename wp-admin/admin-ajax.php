<?php
header('Content-Type: application/json');

// Simuler la réponse WordPress pour pdf_builder_get_canvas_elements
if ($_POST['action'] === 'pdf_builder_get_canvas_elements') {
    $template_id = $_POST['template_id'] ?? 'order';

    // Simuler des éléments de template
    $elements = [
        [
            'id' => 'element_1',
            'type' => 'text',
            'content' => 'Test Element 1',
            'position' => ['x' => 10, 'y' => 10],
            'size' => ['width' => 200, 'height' => 50]
        ],
        [
            'id' => 'element_2',
            'type' => 'text',
            'content' => 'Test Element 2',
            'position' => ['x' => 10, 'y' => 70],
            'size' => ['width' => 200, 'height' => 50]
        ],
        [
            'id' => 'element_3',
            'type' => 'image',
            'content' => 'placeholder.png',
            'position' => ['x' => 10, 'y' => 130],
            'size' => ['width' => 100, 'height' => 100]
        ]
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'elements' => $elements
        ],
        'message' => 'Template elements loaded successfully'
    ]);
    exit;
}

// Simuler la réponse pour pdf_builder_get_order_data
if ($_POST['action'] === 'pdf_builder_get_order_data') {
    $order_id = $_POST['order_id'] ?? 123;

    // Simuler des données de commande WooCommerce
    $order_data = [
        'id' => $order_id,
        'status' => 'completed',
        'currency' => 'EUR',
        'total' => '125.50',
        'subtotal' => '110.00',
        'tax_total' => '15.50',
        'shipping_total' => '5.00',
        'customer_id' => 456,
        'billing' => [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'company' => 'Entreprise ABC',
            'address_1' => '123 Rue de la Paix',
            'address_2' => '',
            'city' => 'Paris',
            'state' => 'Île-de-France',
            'postcode' => '75001',
            'country' => 'FR',
            'email' => 'jean.dupont@email.com',
            'phone' => '+33123456789'
        ],
        'shipping' => [
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'company' => 'Entreprise ABC',
            'address_1' => '123 Rue de la Paix',
            'address_2' => '',
            'city' => 'Paris',
            'state' => 'Île-de-France',
            'postcode' => '75001',
            'country' => 'FR'
        ],
        'line_items' => [
            [
                'id' => 1,
                'name' => 'Produit Test 1',
                'product_id' => 789,
                'variation_id' => 0,
                'quantity' => 2,
                'price' => '25.00',
                'subtotal' => '50.00',
                'total' => '50.00'
            ],
            [
                'id' => 2,
                'name' => 'Produit Test 2',
                'product_id' => 790,
                'variation_id' => 0,
                'quantity' => 1,
                'price' => '60.00',
                'subtotal' => '60.00',
                'total' => '60.00'
            ]
        ],
        'date_created' => '2025-10-21T10:00:00',
        'date_modified' => '2025-10-21T10:30:00'
    ];

    echo json_encode([
        'success' => true,
        'data' => [
            'order' => $order_data
        ],
        'message' => 'Order data loaded successfully'
    ]);
    exit;
}

// Réponse par défaut pour les autres actions
echo json_encode([
    'success' => false,
    'message' => 'Unknown action: ' . ($_POST['action'] ?? 'none')
]);
?>
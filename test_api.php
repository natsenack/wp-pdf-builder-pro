<?php
// Test script for API Preview endpoint using file_get_contents
$data = json_encode([
    'context' => 'editor',
    'templateData' => ['elements' => []]
]);

$options = [
    'http' => [
        'method' => 'POST',
        'header' => 'Content-Type: application/json',
        'content' => $data
    ]
];

$context = stream_context_create($options);
$response = file_get_contents('http://localhost/wp-json/wp-pdf-builder-pro/v1/preview', false, $context);

if ($response === false) {
    echo "Erreur: Impossible de contacter l'endpoint\n";
} else {
    echo "Response: $response\n";
}
?>
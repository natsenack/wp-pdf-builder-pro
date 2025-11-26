<?php
require_once('../../../../wp-load.php');

$settings = get_option('pdf_builder_settings', []);
$preview_data = [
    'company_name' => $settings['pdf_builder_company_name'] ?? '',
    'company_address' => $settings['pdf_builder_company_address'] ?? '',
    'company_phone' => $settings['pdf_builder_company_phone'] ?? '',
    'company_email' => $settings['pdf_builder_company_email'] ?? '',
    'company_website' => $settings['pdf_builder_company_website'] ?? '',
    'company_siret' => $settings['pdf_builder_company_siret'] ?? '',
    'company_rcs' => $settings['pdf_builder_company_rcs'] ?? '',
    'company_capital' => $settings['pdf_builder_company_capital'] ?? '',
    'pdf_quality' => $settings['pdf_builder_pdf_quality'] ?? 90,
    'default_format' => $settings['pdf_builder_default_format'] ?? 'A4',
    'default_orientation' => $settings['pdf_builder_default_orientation'] ?? 'portrait'
];

$json = wp_json_encode($preview_data);
$valid = json_decode($json, true);

if (json_last_error() === JSON_ERROR_NONE) {
    echo 'JSON valide: ' . strlen($json) . ' caractères' . PHP_EOL;
    echo 'Contenu: ' . substr($json, 0, 200) . '...' . PHP_EOL;
} else {
    echo 'JSON invalide: ' . json_last_error_msg() . PHP_EOL;
    echo 'Contenu: ' . $json . PHP_EOL;
}
?>
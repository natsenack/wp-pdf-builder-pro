<?php
// Clean up corrupted PDF Builder settings
$settings = get_option('pdf_builder_settings', []);

// Remove corrupted key
unset($settings['pdf_builder_pdf_builder_allowed_roles']);

// Set proper default roles
$settings['pdf_builder_allowed_roles'] = ['administrator', 'editor', 'shop_manager'];

update_option('pdf_builder_settings', $settings);

echo "Settings cleaned up successfully!\n";
echo "New settings: " . json_encode($settings) . "\n";
?>
<?php
// Simple test for update_option
echo "Testing update_option\n";

// Test option
$option_name = 'test_pdf_builder_option';
$value = 'test_value_' . time();

echo "Setting option $option_name to $value\n";
update_option($option_name, $value);

$retrieved = get_option($option_name);
echo "Retrieved value: $retrieved\n";

if ($retrieved === $value) {
    echo "SUCCESS: update_option works\n";
} else {
    echo "FAILURE: update_option does not work\n";
}
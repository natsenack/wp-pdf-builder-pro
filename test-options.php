<?php
require_once '../../../wp-load.php'; // Adjust path as needed

echo "Cache enabled: " . get_option('pdf_builder_cache_enabled', 'not set') . PHP_EOL;
echo "Cache expiry: " . get_option('pdf_builder_cache_expiry', 'not set') . PHP_EOL;
echo "Max cache size: " . get_option('pdf_builder_max_cache_size', 'not set') . PHP_EOL;
echo "Auto maintenance: " . get_option('pdf_builder_auto_maintenance', 'not set') . PHP_EOL;
echo "Auto backup: " . get_option('pdf_builder_auto_backup', 'not set') . PHP_EOL;
echo "Backup retention: " . get_option('pdf_builder_backup_retention', 'not set') . PHP_EOL;
?>
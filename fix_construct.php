<?php
$files = [
    'plugin/data/SampleDataProvider.php',
    'plugin/data/WooCommerceDataProvider.php',
    'plugin/elements/ElementContracts.php',
    'plugin/generators/BaseGenerator.php'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $content = str_replace('public function Construct(', 'public function __construct(', $content);
        file_put_contents($file, $content);
        echo "Fixed $file\n";
    }
}
?>
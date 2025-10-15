<?php
// Test de décodage JSON pour la validation
$test_json = '[{"id":"element_1","type":"customer_info","x":20,"y":180,"width":230,"height":110,"backgroundColor":"transparent","borderColor":"transparent","borderWidth":0,"borderRadius":4,"color":"#000000","fontSize":12,"fontFamily":"Arial, sans-serif","padding":8,"fields":["name","email","phone","address"],"layout":"vertical","showLabels":true,"labelStyle":"normal","spacing":4}]';

echo 'Test JSON length: ' . strlen($test_json) . PHP_EOL;
echo 'First 200 chars: ' . substr($test_json, 0, 200) . PHP_EOL;

$decoded = json_decode($test_json, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo 'JSON Error: ' . json_last_error_msg() . PHP_EOL;
} else {
    echo 'JSON decoded successfully, elements: ' . count($decoded) . PHP_EOL;
    echo 'First element keys: ' . implode(', ', array_keys($decoded[0])) . PHP_EOL;
}
?>
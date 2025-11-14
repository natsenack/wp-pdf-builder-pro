<?php
// Test script for first visit modal functionality
require_once 'path/to/wp-load.php'; // Adjust path as needed

$user_id = 1; // Test with user ID 1
$meta_key = 'pdf_builder_templates_first_visit';

// Test isFirstVisit function
$has_visited = get_user_meta($user_id, $meta_key, true);
$is_first_visit = empty($has_visited);

echo "User has visited: " . ($has_visited ? 'YES' : 'NO') . "\n";
echo "Is first visit: " . ($is_first_visit ? 'YES' : 'NO') . "\n";

// Test markFirstVisitComplete function
update_user_meta($user_id, $meta_key, '1');
echo "Marked as visited\n";

$has_visited_after = get_user_meta($user_id, $meta_key, true);
$is_first_visit_after = empty($has_visited_after);

echo "User has visited after: " . ($has_visited_after ? 'YES' : 'NO') . "\n";
echo "Is first visit after: " . ($is_first_visit_after ? 'YES' : 'NO') . "\n";

// Clean up
delete_user_meta($user_id, $meta_key);
echo "Cleaned up test data\n";
?>
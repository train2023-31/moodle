<?php
/**
 * Test script to verify civil number search functionality
 * This script can be run to test the search_by_civil.php endpoint
 * 
 * @package    local_oracleFetch
 */

require_once('../../config.php');
require_once('classes/oracle_manager.php');

// Ensure the user is logged in
require_login();

// Test civil numbers from the dummy data
$test_civil_numbers = ['10001', '10002', '10003', '10004', '10005'];

echo "<h2>Testing Civil Number Search Functionality</h2>";
echo "<p>Testing the search_by_civil.php endpoint with sample civil numbers...</p>";

foreach ($test_civil_numbers as $civil_number) {
    echo "<h3>Testing Civil Number: {$civil_number}</h3>";
    
    // Test using the Oracle manager directly
    $person = oracle_manager::get_person_by_civil($civil_number);
    
    if ($person) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>✅ Found Data:</strong><br>";
        echo "Name: " . htmlspecialchars($person['NAME_ARABIC_1'] ?? '') . " " . 
             htmlspecialchars($person['NAME_ARABIC_2'] ?? '') . " " . 
             htmlspecialchars($person['NAME_ARABIC_3'] ?? '') . "<br>";
        echo "Civil Number: " . htmlspecialchars($person['CIVIL_NUMBER'] ?? '') . "<br>";
        echo "Nationality: " . htmlspecialchars($person['NATIONALITY_ARABIC_SYS'] ?? 'Not specified') . "<br>";
        echo "Passport: " . htmlspecialchars($person['PASSPORT_NUMBER'] ?? '') . "<br>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "<strong>❌ No data found for civil number: {$civil_number}</strong>";
        echo "</div>";
    }
}

echo "<hr>";
echo "<h3>AJAX Endpoint Test</h3>";
echo "<p>Test the AJAX endpoint directly:</p>";
echo "<form method='post' action='ajax/search_by_civil.php' target='_blank'>";
echo "<label>Civil Number: <input type='text' name='civil_number' value='10001' /></label>";
echo "<input type='submit' value='Test AJAX Endpoint' />";
echo "</form>";

echo "<hr>";
echo "<h3>Integration Instructions</h3>";
echo "<p>To use this in the externallecturer plugin:</p>";
echo "<ol>";
echo "<li>Open the resident lecturer form</li>";
echo "<li>Enter a civil number in the search field</li>";
echo "<li>The name and nationality should be automatically filled</li>";
echo "<li>Complete the remaining fields manually</li>";
echo "</ol>";

echo "<p><strong>Sample Civil Numbers to Test:</strong> " . implode(', ', $test_civil_numbers) . "</p>";
?>

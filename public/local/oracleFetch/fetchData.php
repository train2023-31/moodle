<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/classes/oracle_manager.php');
require_once(__DIR__ . '/lib.php');

require_login();
// Require Select2 assets via helper (offline-safe)
oracle_select2_require_assets();

echo $OUTPUT->header();

echo "<h2>Centralized Oracle Fetch - Demo</h2>";

/**Example 1: Using centralized Oracle manager */
$employees = oracle_manager::get_all_employees();

echo "<h3>Employees (using centralized manager):</h3>";
if (empty($employees)) {
    echo "<div style='padding:8px;border:1px solid #f0ad4e;background:#fcf8e3;color:#8a6d3b;border-radius:4px;'>لا توجد بيانات موظفين. قد تكون قاعدة بيانات Oracle غير متصلة أو لا توجد سجلات مطابقة.</div>";
} else {
    echo "<ul>";
    foreach ($employees as $employee) {
        echo "<li>Employee PF_Number: " . htmlspecialchars($employee['pf_number']) . 
        "  -- Name: " . htmlspecialchars($employee['fullname']) . "</li>";
    }
    echo "</ul>";
}

/**Example 2: Using helper functions */
echo "<h3>Employee Name Lookup (using helper function):</h3>";
$test_pf = 'PF123'; // Replace with actual PF number
$employee_name = oracle_get_employee_name($test_pf);
echo "<p>Employee with PF '$test_pf': " . htmlspecialchars($employee_name) . "</p>";

/**Example 3: Person Details */
$persons = oracle_manager::get_all_persons();

echo "<h3>Person Details:</h3>";
if (empty($persons)) {
    echo "<div style='padding:8px;border:1px solid #f0ad4e;background:#fcf8e3;color:#8a6d3b;border-radius:4px;'>لا توجد بيانات أفراد. قد تكون قاعدة بيانات Oracle غير متصلة أو لا توجد سجلات مطابقة.</div>";
} else {
    echo "<ul>";
    foreach (array_slice($persons, 0, 10) as $person) { // Show first 10 only
        $nationality = !empty($person['nationality']) ? ' -- Nationality: ' . htmlspecialchars($person['nationality']) : '';
        echo "<li>Civil Number: " . htmlspecialchars($person['civil_number']) .
        " -- Name: " . htmlspecialchars($person['fullname']) . $nationality . "</li>";
    }
    echo "</ul>";
}

/** Example 4: Autocomplete dropdown list with search matching */
echo "<h3>Select a Person (autocomplete):</h3>";
echo "<form>";
echo "<label for='person_select'><strong>Select a Person:</strong></label><br>";
echo "<select id='person_select' name='person_select' style='width: 300px;' dir='rtl'>";
echo "<option value=''>-- الرجاء اختيار شخص --</option>";

foreach ($persons as $person) {
    $civilnumberRaw = isset($person['civil_number']) ? $person['civil_number'] : '';
    $fullnameRaw = isset($person['fullname']) ? $person['fullname'] : '';
    $tribeRaw = isset($person['tribe']) ? trim($person['tribe']) : '';

    $civilnumber = htmlspecialchars($civilnumberRaw, ENT_QUOTES, 'UTF-8');
    $fullname = htmlspecialchars($fullnameRaw, ENT_QUOTES, 'UTF-8');

    // Build searchable text: civil + fullname + tribe (no passport)
    $searchtextRaw = trim($civilnumberRaw . ' ' . $fullnameRaw . ($tribeRaw !== '' ? ' ' . $tribeRaw : ''));
    $searchtext = htmlspecialchars($searchtextRaw, ENT_QUOTES, 'UTF-8');

    echo "<option value='$civilnumber' data-text='$searchtext'>$civilnumber - $fullname</option>";
}

echo "</select>";
echo "</form>";

// Initialize Select2 with shared Arabic defaults
oracle_select2_init('#person_select', '-- الرجاء اختيار شخص --');

echo "<hr>";
echo "<h3>How to use in other plugins:</h3>";
echo "<h4>1. Include the Oracle manager class:</h4>";
echo "<pre>require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');</pre>";

echo "<h4>2. Use the helper functions:</h4>";
echo "<pre>require_once(__DIR__ . '/../oracleFetch/lib.php');
\$employee_name = oracle_get_employee_name(\$pf_number);
\$person_name = oracle_get_person_name(\$civil_number);
\$person_name_with_nationality = oracle_get_person_name(\$civil_number, true);
\$person_nationality = oracle_get_person_nationality(\$civil_number);</pre>";

echo "<h4>3. Use the manager directly:</h4>";
echo "<pre>\$employees = oracle_manager::get_all_employees();
\$persons = oracle_manager::get_all_persons();
\$employee = oracle_manager::get_employee_by_pf(\$pf_number);</pre>";

echo "<h4>4. AJAX endpoints available:</h4>";
echo "<ul>";
echo "<li>/local/oracleFetch/ajax/get_employees.php - Get all employees</li>";
echo "<li>/local/oracleFetch/ajax/get_persons.php - Get all persons</li>";
echo "<li>/local/oracleFetch/ajax/get_employees_and_persons.php - Get combined employees and persons (for beneficiaries)</li>";
echo "<li>/local/oracleFetch/ajax/search_employees.php?term=search - Search employees</li>";
echo "</ul>";

echo "<h4>5. New method for annual plans:</h4>";
echo "<pre>\$data = oracle_manager::get_all_employees_and_persons();</pre>";
echo "<p>This method combines 'employees' and 'person_details' tables with proper formatting for beneficiary selection.</p>";

echo "<h4>6. Updated Database Schema (Real Production Tables):</h4>";
echo "<ul>";
echo "<li><strong>DUNIA_EMPLOYEES</strong> - Contains employee data with PF numbers and civil numbers (format: C-12345)</li>";
echo "<li><strong>DUNIA_PERSONAL_DETAILS</strong> - Contains personal data with civil numbers (format: 12345)</li>";
echo "</ul>";
echo "<h4>7. Field Mappings:</h4>";
echo "<ul>";
echo "<li><strong>Employee Names:</strong> prs_name1_a (first), prs_name2_a (middle), prs_name3_a (last), prs_tribe_a (tribe)</li>";
echo "<li><strong>Personal Names:</strong> name_arabic_1, name_arabic_2, name_arabic_3, name_arabic_6 (tribe)</li>";
echo "<li><strong>Civil Number Link:</strong> aaa_emp_civil_number_ar (C-12345) <-> civil_number (12345)</li>";
echo "</ul>";

echo $OUTPUT->footer();
?>

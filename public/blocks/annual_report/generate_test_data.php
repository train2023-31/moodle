<?php
/**
 * Test Data Generator for Annual Report Block
 * 
 * This script generates sample data for testing the annual report block functionality.
 * Features:
 * - Creates sample course levels (internal and external) matching install.php defaults
 * - Generates course data for the current year
 * - Creates financial data (clauses and services) matching install.php defaults
 * - Includes a "redo" feature to clear and regenerate all data
 * 
 * Usage:
 * - Run directly in browser: /blocks/annual_report/generate_test_data.php
 * - Or via CLI: php generate_test_data.php
 * 
 * @package    block_annual_report
 * @copyright  2025 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_login();

// Security check - only allow admins to run this script
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$action = optional_param('action', 'show', PARAM_ALPHA);
$confirm = optional_param('confirm', 0, PARAM_INT);

// Set up the page
$PAGE->set_url('/blocks/annual_report/generate_test_data.php');
$PAGE->set_context($context);
$PAGE->set_title('Annual Report - Test Data Generator');
$PAGE->set_heading('Annual Report Block - Test Data Generator');

echo $OUTPUT->header();

/**
 * Clear all test data from the database
 */
function clear_test_data() {
    global $DB;
    
    echo "<h3>🧹 Clearing existing test data...</h3>";
    
    // Clear in reverse order to respect foreign key constraints
    $tables_to_clear = [
        'local_financeservices',
        // 'local_financeservices_clause', 
        // 'local_financeservices_funding_type',
        'local_annual_plan_course',
        // 'local_annual_plan_course_level',
        // 'local_annual_plan_finance_source'
        // 'local_annual_plan'
    ];

    // Only clear annual plan if table exists
    if ($DB->get_manager()->table_exists('local_annual_plan')) {
        $tables_to_clear[] = 'local_annual_plan';
    }
    foreach ($tables_to_clear as $table) {
        if ($DB->get_manager()->table_exists($table)) {
            $count = $DB->count_records($table);
            $DB->delete_records($table);
            echo "<div style='color: orange; padding: 5px;'>✓ Cleared {$count} records from {$table}</div>";
        } else {
            echo "<div style='color: red; padding: 5px;'>⚠ Table {$table} does not exist</div>";
        }
    }
    
    echo "<div style='color: green; font-weight: bold; padding: 10px; margin: 10px 0; border: 2px solid green;'>✅ All test data cleared successfully!</div>";
}

/**
 * Generate course level test data - matching install.php defaults
 */
function generate_course_levels() {
    global $DB;
    
    echo "<h3>📚 Generating course levels (matching install.php defaults)...</h3>";
    
    // Create the table if it doesn't exist
    if (!$DB->get_manager()->table_exists('local_annual_plan_course_level')) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red;'>❌ Table 'local_annual_plan_course_level' does not exist. Please create it first.</div>";
        return false;
    }
    
    // Course levels matching annualplans/db/install.php exactly
    $course_levels = [
        [
            'id' => 1,
            'name' => 'BSCOC',
            'description_en' => 'BSC Officer Candidates',
            'description_ar' => 'الدورة التأسيسية ض.م',
            'is_internal' => 1
        ],
        [
            'id' => 2,
            'name' => 'BSCP',
            'description_en' => 'BSC writers',
            'description_ar' => 'الدوة التأسيسة للكتبة',
            'is_internal' => 1
        ],
        [
            'id' => 3,
            'name' => 'SACI',
            'description_en' => 'Security Awareness Course',
            'description_ar' => 'دورات ت.أ',
            'is_internal' => 1
        ],
        [
            'id' => 4,
            'name' => 'DSC',
            'description_en' => 'Department Specialized courses',
            'description_ar' => 'دورات تخصصية لأقسام.د',
            'is_internal' => 1
        ],
        [
            'id' => 5,
            'name' => 'OQC',
            'description_en' => 'Officers Qualification Course',
            'description_ar' => 'دورة ت.ض',
            'is_internal' => 1
        ],
        [
            'id' => 6,
            'name' => 'MSACE',
            'description_en' => 'M Security Awarenss Course - External',
            'description_ar' => 'دورة.ت.أ.ع.خ',
            'is_internal' => 0
        ],
        [
            'id' => 7,
            'name' => 'CSACE',
            'description_en' => 'Civil Security Awarenss Course - External',
            'description_ar' => 'دورة ت.أ.م.خ',
            'is_internal' => 0
        ]        
    ];
    
    foreach ($course_levels as $level) {
        $existing = $DB->get_record('local_annual_plan_course_level', ['id' => $level['id']]);
        if ($existing) {
            $DB->update_record('local_annual_plan_course_level', (object)$level);
            echo "<div style='color: blue; padding: 2px;'>📝 Updated: {$level['description_en']} ({$level['name']})</div>";
        } else {
            $DB->insert_record('local_annual_plan_course_level', (object)$level);
            echo "<div style='color: green; padding: 2px;'>➕ Created: {$level['description_en']} ({$level['name']})</div>";
        }
    }
    
    return true;
}

/**
 * Generate finance source data - matching install.php defaults
 */
function generate_finance_sources() {
    global $DB;
    
    echo "<h3>💳 Generating finance sources (matching install.php defaults)...</h3>";
    
    if (!$DB->get_manager()->table_exists('local_annual_plan_finance_source')) {
        echo "<div style='color: orange; padding: 5px;'>⚠ Table 'local_annual_plan_finance_source' does not exist - skipping finance sources</div>";
        return false;
    }
    
    // Finance sources matching annualplans/db/install.php exactly
    $finance_sources = [
        [
            'id' => 1,
            'code' => 'X',
            'description_en' => 'Others',
            'description_ar' => 'أخرى'
        ],
        [
            'id' => 2,
            'code' => 'T',
            'description_en' => 'Training Plan',
            'description_ar' => 'خطة التدريب'
        ],
        [
            'id' => 3,
            'code' => 'P',
            'description_en' => 'Project',
            'description_ar' => 'مشروع'
        ],
        [
            'id' => 4,
            'code' => 'O',
            'description_en' => 'Work',
            'description_ar' => 'مهمة عمل'
        ]
    ];
    
    foreach ($finance_sources as $source) {
        $existing = $DB->get_record('local_annual_plan_finance_source', ['id' => $source['id']]);
        if ($existing) {
            $DB->update_record('local_annual_plan_finance_source', (object)$source);
            echo "<div style='color: blue; padding: 2px;'>📝 Updated: {$source['description_en']} ({$source['code']})</div>";
        } else {
            $DB->insert_record('local_annual_plan_finance_source', (object)$source);
            echo "<div style='color: green; padding: 2px;'>➕ Created: {$source['description_en']} ({$source['code']})</div>";
        }
    }
    
    return true;
}

/**
 * Generate funding types - matching install.php defaults
 */
function generate_funding_types() {
    global $DB;
    
    echo "<h3>💰 Generating funding types (matching install.php defaults)...</h3>";
    
    if (!$DB->get_manager()->table_exists('local_financeservices_funding_type')) {
        echo "<div style='color: orange; padding: 5px;'>⚠ Table 'local_financeservices_funding_type' does not exist - skipping funding types</div>";
        return false;
    }
    
    // Funding types matching financeservices/db/install.php exactly
    $funding_types = [
        ['funding_type_en' => 'Bonus',      'funding_type_ar' => 'علاوة', 'deleted' => 0],
        ['funding_type_en' => 'Housing',        'funding_type_ar' => 'سكن', 'deleted' => 0],
        ['funding_type_en' => 'Transportation', 'funding_type_ar' => 'نقل', 'deleted' => 0],
        ['funding_type_en' => 'Meals',           'funding_type_ar' => 'تغذية', 'deleted' => 0],
    ];

    foreach ($funding_types as $funding_type) {
        // Check if already exists
        $existing = $DB->get_record('local_financeservices_funding_type', ['funding_type_en' => $funding_type['funding_type_en']]);
        if ($existing) {
            echo "<div style='color: blue; padding: 2px;'>📝 Already exists: {$funding_type['funding_type_en']}</div>";
        } else {
            $DB->insert_record('local_financeservices_funding_type', (object)$funding_type);
            echo "<div style='color: green; padding: 2px;'>➕ Created: {$funding_type['funding_type_en']} ({$funding_type['funding_type_ar']})</div>";
        }
    }
    
    return true;
}

/**
 * Generate annual plan parent record
 */
function generate_annual_plan() {
    global $DB;
    
    echo "<h3>📋 Generating annual plan (matching install.php style)...</h3>";
    
    if (!$DB->get_manager()->table_exists('local_annual_plan')) {
        echo "<div style='color: orange; padding: 5px;'>⚠ Table 'local_annual_plan' does not exist - courses will be created without annual plan reference.</div>";
        return false;
    }
    
    $current_year = date('Y');
    
    // Check if annual plan already exists for current year
    $existing_plan = $DB->get_record('local_annual_plan', ['year' => $current_year]);
    
    if ($existing_plan) {
        echo "<div style='color: blue; padding: 2px;'>📝 Using existing annual plan for {$current_year}: ID {$existing_plan->id}</div>";
        return $existing_plan->id;
    }
    
    // Create new annual plan with Arabic title matching install.php style
    $annual_plan = [
        'year' => $current_year,
        'title' => "الخطة السنوية التجريبية {$current_year}",
        'date_created' => time(),
        'status' => 'نشط',
        'description' => "خطة سنوية تجريبية تم إنشاؤها لعام {$current_year}",
        'disabled' => 0
    ];
    
    $plan_id = $DB->insert_record('local_annual_plan', (object)$annual_plan);
    echo "<div style='color: green; padding: 2px;'>➕ Created annual plan for {$current_year}: ID {$plan_id}</div>";
    
    return $plan_id;
}

/**
 * Generate course data for the current year with proper course IDs following annualplans pattern
 */
function generate_courses() {
    global $DB;
    
    echo "<h3>🎓 Generating courses (using annualplans course ID pattern)...</h3>";
    
    if (!$DB->get_manager()->table_exists('local_annual_plan_course')) {
        echo "<div style='color: red; padding: 10px; border: 1px solid red;'>❌ Table 'local_annual_plan_course' does not exist. Please create it first.</div>";
        return false;
    }
    
    // Try to get an annual plan (optional - some installations may not have this table)
    $annual_plan_id = generate_annual_plan();
    $use_annual_plan = ($annual_plan_id !== false);
    
    // Get current year
    $current_year = date('Y');
    $year_start = strtotime($current_year . '-01-01 00:00:00');
    $year_end = strtotime($current_year . '-12-31 23:59:59');
    
    // Course shortname components following annualplans pattern:
    // [CategoryCode][GradeCode][CourseCode][TargetedGroupCode]-[Year]([GroupNumberCode])
    
    $category_codes = ['INT', 'MGT', 'LAN', 'ADM', 'TEC']; // From install.php
    $grade_codes = ['1', '2', '3']; // Beginner, Intermediate, Advanced
    $course_codes = ['01', '02', '03']; // Batch numbers
    $targeted_group_codes = ['O', 'NO', 'X']; // Officer, Non-officer, Others
    $group_number_codes = ['1', '2', '3']; // Group numbers
    
    // Generate courses for each level
    $course_data = [];
    $course_id = 1;
    
    echo "<div style='color: blue; padding: 5px;'>📋 Using annualplans course ID pattern: [Category][Grade][Course][Group]-[Year]([Number])</div>";
    $year_short = substr($current_year, -2);
    echo "<div style='color: blue; padding: 5px;'>📋 Example: INT301O-{$year_short}(1) = Intelligence + Advanced + Batch01 + Officer + {$year_short} + Group1</div>";
    
    // Internal courses (levels 1-5: BSCOC, BSCP, SACI, DSC, OQC)
    for ($level = 1; $level <= 5; $level++) {
        $courses_per_level = rand(3, 6); // Random number of courses per level
        
        for ($i = 0; $i < $courses_per_level; $i++) {
            $course_date = rand($year_start, $year_end);
            $beneficiaries = rand(15, 120); // Random beneficiaries between 15-120
            $duration = rand(3, 30); // Random duration between 3 and 30 days
            
            // Generate shortname following annualplans pattern
            $category_code = $category_codes[array_rand($category_codes)];
            $grade_code = $grade_codes[array_rand($grade_codes)];
            $course_code = $course_codes[array_rand($course_codes)];
            $targeted_group_code = $targeted_group_codes[array_rand($targeted_group_codes)];
            $group_number_code = $group_number_codes[array_rand($group_number_codes)];
            
            // Build shortname: [Category][Grade][Course][TargetedGroup]-[Year]([GroupNumber])
            // Note: Year should be last 2 digits only (e.g., "24" not "2024")
            $year_short = substr($current_year, -2);
            $shortname = $category_code . $grade_code . $course_code . $targeted_group_code . '-' . $year_short . '(' . $group_number_code . ')';
            
            // Get course level name for course name
            $level_record = $DB->get_record('local_annual_plan_course_level', ['id' => $level]);
            $level_name = $level_record ? $level_record->name : "Level{$level}";
            
            $course_record = [
                'id' => $course_id++,
                'courselevelid' => $level,
                'coursedate' => $duration, // new field for duration in days
                'numberofbeneficiaries' => $beneficiaries,
                'approve' => 0, // All test courses are NOT approved
                'coursename' => "Test {$level_name} Course - {$shortname}",
                'courseid' => $shortname, // Using the proper annualplans pattern
                'category' => get_category_name_from_code($category_code),
                'status' => get_target_group_description($targeted_group_code),
                'timecreated' => time(),
                'timemodified' => time()
            ];
            
            // Only add annualplanid if we have a valid annual plan
            if ($use_annual_plan) {
                $course_record['annualplanid'] = $annual_plan_id;
            }
            
            $course_data[] = $course_record;
        }
    }
    
    // External courses (levels 6-7: MSACE, CSACE)
    for ($level = 6; $level <= 7; $level++) {
        $courses_per_level = rand(2, 4); // Fewer external courses
        
        for ($i = 0; $i < $courses_per_level; $i++) {
            $course_date = rand($year_start, $year_end);
            $beneficiaries = rand(20, 80); // External courses typically smaller
            $duration = rand(3, 30); // Random duration between 3 and 30 days
            
            // Generate shortname following annualplans pattern
            $category_code = $category_codes[array_rand($category_codes)];
            $grade_code = $grade_codes[array_rand($grade_codes)];
            $course_code = $course_codes[array_rand($course_codes)];
            $targeted_group_code = $targeted_group_codes[array_rand($targeted_group_codes)];
            $group_number_code = $group_number_codes[array_rand($group_number_codes)];
            
            // Build shortname: [Category][Grade][Course][TargetedGroup]-[Year]([GroupNumber])
            // Note: Year should be last 2 digits only (e.g., "24" not "2024")
            $year_short = substr($current_year, -2);
            $shortname = $category_code . $grade_code . $course_code . $targeted_group_code . '-' . $year_short . '(' . $group_number_code . ')';
            
            // Get course level name for course name
            $level_record = $DB->get_record('local_annual_plan_course_level', ['id' => $level]);
            $level_name = $level_record ? $level_record->name : "Level{$level}";
            
            $course_record = [
                'id' => $course_id++,
                'courselevelid' => $level,
                'coursedate' => $duration, // new field for duration in days
                'numberofbeneficiaries' => $beneficiaries,
                'approve' => 0, // All test courses are NOT approved
                'coursename' => "Test {$level_name} External Course - {$shortname}",
                'courseid' => $shortname, // Using the proper annualplans pattern
                'category' => get_category_name_from_code($category_code),
                'status' => get_target_group_description($targeted_group_code),
                'timecreated' => time(),
                'timemodified' => time()
            ];
            
            // Only add annualplanid if we have a valid annual plan
            if ($use_annual_plan) {
                $course_record['annualplanid'] = $annual_plan_id;
            }
            
            $course_data[] = $course_record;
        }
    }
    
    // Add some unapproved courses for testing
    for ($i = 0; $i < 3; $i++) {
        $level = rand(1, 7);
        $course_date = rand($year_start, $year_end);
        $duration = rand(3, 30); // Random duration between 3 and 30 days
        
        // Generate shortname following annualplans pattern
        $category_code = $category_codes[array_rand($category_codes)];
        $grade_code = $grade_codes[array_rand($grade_codes)];
        $course_code = $course_codes[array_rand($course_codes)];
        $targeted_group_code = $targeted_group_codes[array_rand($targeted_group_codes)];
        $group_number_code = $group_number_codes[array_rand($group_number_codes)];
        
        // Build shortname: [Category][Grade][Course][TargetedGroup]-[Year]([GroupNumber])
        // Note: Year should be last 2 digits only (e.g., "24" not "2024")
        $year_short = substr($current_year, -2);
        $shortname = $category_code . $grade_code . $course_code . $targeted_group_code . '-' . $year_short . '(' . $group_number_code . ')';
        
        $course_record = [
            'id' => $course_id++,
            'courselevelid' => $level,
            'coursedate' => $duration, // new field for duration in days
            'numberofbeneficiaries' => rand(10, 50),
            'approve' => 0, // Unapproved - should not appear in report
            'coursename' => "Unapproved Course - {$shortname}",
            'courseid' => $shortname, // Using the proper annualplans pattern
            'category' => get_category_name_from_code($category_code),
            'status' => 'pending',
            'timecreated' => time(),
            'timemodified' => time()
        ];
        
        // Only add annualplanid if we have a valid annual plan
        if ($use_annual_plan) {
            $course_record['annualplanid'] = $annual_plan_id;
        }
        
        $course_data[] = $course_record;
    }
    
    // Insert course data
    foreach ($course_data as $course) {
        $DB->insert_record('local_annual_plan_course', (object)$course);
        $date_str = date('Y-m-d', $course['coursedate']);
        $status = $course['approve'] ? '✅' : '❌';
        echo "<div style='padding: 2px; font-size: 12px;'>{$status} Course: {$course['courseid']} | {$course['coursename']} | Date: {$date_str} | Beneficiaries: {$course['numberofbeneficiaries']}</div>";
    }
    
    echo "<div style='color: green; font-weight: bold; padding: 5px;'>📊 Generated " . count($course_data) . " courses for {$current_year} using annualplans pattern (all NOT approved)</div>";
    return true;
}

/**
 * Helper function to get category name from category code
 */
function get_category_name_from_code($code) {
    switch ($code) {
        case 'INT': return 'Intelligence';
        case 'MGT': return 'Management';
        case 'LAN': return 'Language';
        case 'ADM': return 'Administration';
        case 'TEC': return 'Technology';
        default: return 'General';
    }
}

/**
 * Helper function to get target group description from code
 */
function get_target_group_description($code) {
    switch ($code) {
        case 'O': return 'Officer';
        case 'NO': return 'Non-officer';
        case 'X': return 'Others';
        default: return 'General';
    }
}

/**
 * Generate financial test data - matching install.php defaults
 */
function generate_financial_data() {
    global $DB;
    
    echo "<h3>💰 Generating financial data (matching install.php defaults)...</h3>";
    
    // Create clause data - matching financeservices/db/install.php
    if ($DB->get_manager()->table_exists('local_financeservices_clause')) {
        global $USER;
        $current_time = time();
        $user_id = isset($USER->id) ? $USER->id : 1; // Fallback to admin if USER not available
        
        $clauses = [
            [
                'id' => 1, 
                'clause_name_en' => 'Clause 802',
                'clause_name_ar' => 'بند 802',
                'amount' => 500.00,
                'deleted' => 0,
                'created_by' => $user_id,
                'created_date' => $current_time,
                'initial_amount' => 500.00
            ],
            [
                'id' => 2, 
                'clause_name_en' => 'Clause 811',
                'clause_name_ar' => 'بند 811',
                'amount' => 1000.00,
                'deleted' => 0,
                'created_by' => $user_id,
                'created_date' => $current_time,
                'initial_amount' => 1000.00
            ]
        ];
        
        foreach ($clauses as $clause) {
            $existing = $DB->get_record('local_financeservices_clause', ['id' => $clause['id']]);
            if ($existing) {
                // For updates, preserve original audit fields but update amount
                $update_data = [
                    'id' => $clause['id'],
                    'clause_name_en' => $clause['clause_name_en'],
                    'clause_name_ar' => $clause['clause_name_ar'],
                    'amount' => $clause['amount'],
                    'modified_by' => $user_id,
                    'modified_date' => $current_time
                ];
                $DB->update_record('local_financeservices_clause', (object)$update_data);
                echo "<div style='color: blue; padding: 2px;'>📝 Updated clause {$clause['id']}: {$clause['amount']} OMR</div>";
            } else {
                $DB->insert_record('local_financeservices_clause', (object)$clause);
                echo "<div style='color: green; padding: 2px;'>➕ Created clause {$clause['id']}: {$clause['amount']} OMR</div>";
            }
        }
    } else {
        echo "<div style='color: orange; padding: 5px;'>⚠ Table 'local_financeservices_clause' not found - skipping clause data</div>";
    }
    
    // Create service expenditure data
    if ($DB->get_manager()->table_exists('local_financeservices')) {
        global $USER;
        $user_id = isset($USER->id) ? $USER->id : 1; // Fallback to admin if USER not available
        
        // First, get available courses and funding types
        $courses = $DB->get_records('course', ['visible' => 1], '', 'id', 1, 3); // Get first 3 visible courses
        
        $funding_types = [];
        if ($DB->get_manager()->table_exists('local_financeservices_funding_type')) {
            $funding_types = $DB->get_records('local_financeservices_funding_type', ['deleted' => 0], '', 'id', 1, 2);
        }
        
        if (empty($courses)) {
            // Fallback: use site course if no other courses exist
            $courses = $DB->get_records('course', null, '', 'id', 1, 1);
        }
        
        echo "<div style='color: blue; padding: 5px;'>🔍 Found " . count($courses) . " courses for financial services</div>";
        echo "<div style='color: blue; padding: 5px;'>🔍 Found " . count($funding_types) . " funding types</div>";
        
        if (empty($funding_types)) {
            echo "<div style='color: red; padding: 5px;'>❌ No funding types found - cannot create financial services without funding types</div>";
            return false;
        }
        
        // Get approved status ID from status plugin - look for 'Approved' status
        $status_id = 6; // Default fallback
        if ($DB->get_manager()->table_exists('local_status')) {
            try {
                // Look for 'approved' status with display_name_en = 'Approved'
                $approved_status = $DB->get_record('local_status', ['display_name_en' => 'Approved', 'type_id' => 2], 'id');
                
                if ($approved_status) {
                    $status_id = $approved_status->id;
                    echo "<div style='color: green; padding: 2px;'>✓ Found approved status ID: {$status_id}</div>";
                } else {
                    // Try with name field
                    $approved_status = $DB->get_record('local_status', ['name' => 'approved', 'type_id' => 2], 'id');
                    if ($approved_status) {
                        $status_id = $approved_status->id;
                        echo "<div style='color: green; padding: 2px;'>✓ Found approved status ID by name: {$status_id}</div>";
                    } else {
                        echo "<div style='color: orange; padding: 2px;'>⚠ No 'Approved' status found, using default ID: {$status_id}</div>";
                    }
                }
            } catch (Exception $e) {
                echo "<div style='color: orange; padding: 2px;'>⚠ Could not query status table, using default status ID: {$status_id}</div>";
            }
        } else {
            echo "<div style='color: blue; padding: 2px;'>ℹ Status table not found, using default status ID: {$status_id}</div>";
        }
        
        $services = [];
        $current_time = time();
        
        // Check if we have the minimum requirements for creating financial services
        if (empty($courses) || empty($funding_types)) {
            echo "<div style='color: red; padding: 10px; border: 1px solid red;'>❌ Cannot generate financial services: missing required courses or funding types</div>";
            return false;
        }
        
        // Generate expenditures for clause 1 (802) - smaller amounts matching the install.php clause amount
        $total_clause1 = 0;
        for ($i = 0; $i < rand(3, 6); $i++) {
            $amount = rand(50, 150); // Smaller amounts fitting in 500 OMR budget
            $total_clause1 += $amount;
            
            // Stop if we exceed clause budget
            if ($total_clause1 > 450) {
                $amount -= ($total_clause1 - 450);
                $total_clause1 = 450;
            }
            
            // Safely get random course and funding type
            $courses_array = array_values($courses);
            $funding_types_array = array_values($funding_types);
            
            $course = $courses_array[array_rand($courses_array)];
            $funding_type = $funding_types_array[array_rand($funding_types_array)];
            
            $services[] = [
                'course_id' => $course->id,
                'funding_type_id' => $funding_type->id,
                'price_requested' => $amount,
                'notes' => "Test training expenditure #" . ($i + 1),
                'user_id' => $user_id,
                'date_time_requested' => $current_time - rand(0, 86400 * 30), // Random date within last 30 days
                'status_id' => $status_id,
                'clause_id' => 1
            ];
            
            if ($total_clause1 >= 450) break;
        }
        
        // Generate expenditures for clause 2 (811) - fitting in 1000 OMR budget
        $total_clause2 = 0;
        for ($i = 0; $i < rand(4, 8); $i++) {
            $amount = rand(80, 200); // Medium amounts fitting in 1000 OMR budget
            $total_clause2 += $amount;
            
            // Stop if we exceed clause budget
            if ($total_clause2 > 900) {
                $amount -= ($total_clause2 - 900);
                $total_clause2 = 900;
            }
            
            // Safely get random course and funding type
            $courses_array = array_values($courses);
            $funding_types_array = array_values($funding_types);
            
            $course = $courses_array[array_rand($courses_array)];
            $funding_type = $funding_types_array[array_rand($funding_types_array)];
            
            $services[] = [
                'course_id' => $course->id,
                'funding_type_id' => $funding_type->id,
                'price_requested' => $amount,
                'notes' => "Test external service expenditure #" . ($i + 1),
                'user_id' => $user_id,
                'date_time_requested' => $current_time - rand(0, 86400 * 30), // Random date within last 30 days
                'status_id' => $status_id,
                'clause_id' => 2
            ];
            
            if ($total_clause2 >= 900) break;
        }
        
        // Insert service data
        foreach ($services as $service) {
            // Debug: check service data completeness
            $required_fields = ['course_id', 'funding_type_id', 'price_requested', 'user_id', 'date_time_requested', 'status_id', 'clause_id'];
            $missing_fields = [];
            foreach ($required_fields as $field) {
                if (!isset($service[$field])) {
                    $missing_fields[] = $field;
                }
            }
            
            if (!empty($missing_fields)) {
                echo "<div style='color: red; padding: 5px;'>❌ Service record missing fields: " . implode(', ', $missing_fields) . "</div>";
                echo "<div style='color: blue; padding: 5px;'>🔍 Service data: " . json_encode($service) . "</div>";
                continue; // Skip this record
            }
            
            try {
                $DB->insert_record('local_financeservices', (object)$service);
                echo "<div style='padding: 2px; font-size: 12px;'>💳 Service (Clause {$service['clause_id']}): {$service['price_requested']} OMR</div>";
            } catch (Exception $e) {
                echo "<div style='color: red; padding: 5px;'>❌ Error inserting service: " . $e->getMessage() . "</div>";
                echo "<div style='color: blue; padding: 5px;'>🔍 Failed service data: " . json_encode($service) . "</div>";
            }
        }
        
        echo "<div style='color: green; padding: 5px;'>💰 Clause 802 spent: {$total_clause1} OMR / 500 OMR budget</div>";
        echo "<div style='color: green; padding: 5px;'>💰 Clause 811 spent: {$total_clause2} OMR / 1000 OMR budget</div>";
        echo "<div style='color: green; font-weight: bold; padding: 5px;'>📊 Generated " . count($services) . " financial services</div>";
    } else {
        echo "<div style='color: orange; padding: 5px;'>⚠ Table 'local_financeservices' not found - skipping service data</div>";
    }
    
    return true;
}

/**
 * Generate all test data - matching install.php defaults
 */
function generate_all_test_data() {
    echo "<div style='background: #e7f3ff; padding: 15px; border-left: 4px solid #2196F3; margin: 20px 0;'>";
    echo "<h2>🚀 Generating comprehensive test data matching install.php defaults</h2>";
    echo "<p>This will create realistic test data for the current year: <strong>" . date('Y') . "</strong></p>";
    echo "<p>✅ Using exact course levels, funding types, and clause amounts from install.php files</p>";
    echo "</div>";
    
    $success = true;
    
    // Generate in proper dependency order
    $success &= generate_course_levels();
    $success &= generate_finance_sources();
    $success &= generate_funding_types();
    // Note: generate_annual_plan() is called within generate_courses()
    $success &= generate_courses();
    $success &= generate_financial_data();
    
    if ($success) {
        echo "<div style='background: #d4edda; border: 2px solid #28a745; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h3 style='color: #155724; margin: 0 0 10px 0;'>🎉 Test Data Generation Complete!</h3>";
        echo "<p style='margin: 5px 0;'>✅ Course levels created/updated (BSCOC, BSCP, SACI, DSC, OQC, MSACE, CSACE)</p>";
        echo "<p style='margin: 5px 0;'>✅ Finance sources created (Others, Training Plan, Project, Work)</p>";
        echo "<p style='margin: 5px 0;'>✅ Funding types created (Bonus, Housing, Transportation, Meals)</p>";
        echo "<p style='margin: 5px 0;'>✅ Sample courses generated for " . date('Y') . " with annualplans course ID pattern (all NOT approved)</p>";
        echo "<p style='margin: 5px 0;'>✅ Financial data created with proper clause amounts (802: 500 OMR, 811: 1000 OMR)</p>";
        echo "<p style='margin: 10px 0 0 0; font-weight: bold;'>You can now test the Annual Report block functionality!</p>";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; border: 2px solid #dc3545; padding: 20px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h3 style='color: #721c24; margin: 0 0 10px 0;'>❌ Test Data Generation Failed</h3>";
        echo "<p>Some errors occurred during data generation. Please check the messages above.</p>";
        echo "</div>";
    }
}

/**
 * Show current data statistics
 */
function show_current_data() {
    global $DB;
    
    echo "<h2>📊 Current Data Statistics</h2>";
    
    $tables = [
        'local_annual_plan_course_level' => 'Course Levels',
        'local_annual_plan_finance_source' => 'Finance Sources',
        'local_financeservices_funding_type' => 'Funding Types',
        'local_annual_plan_course' => 'Courses',
        'local_financeservices_clause' => 'Financial Clauses',
        'local_financeservices' => 'Financial Services'
    ];
    
    // Add annual plan table if it exists
    if ($DB->get_manager()->table_exists('local_annual_plan')) {
        $tables = ['local_annual_plan' => 'Annual Plans'] + $tables;
    }
    
    echo "<table style='border-collapse: collapse; width: 100%; margin: 20px 0;'>";
    echo "<tr style='background: #f1f1f1;'>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Table</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Status</th>";
    echo "<th style='border: 1px solid #ddd; padding: 12px; text-align: left;'>Record Count</th>";
    echo "</tr>";
    
    foreach ($tables as $table => $name) {
        echo "<tr>";
        echo "<td style='border: 1px solid #ddd; padding: 12px; font-weight: bold;'>{$name}</td>";
        
        if ($DB->get_manager()->table_exists($table)) {
            $count = $DB->count_records($table);
            echo "<td style='border: 1px solid #ddd; padding: 12px; color: green;'>✅ Exists</td>";
            echo "<td style='border: 1px solid #ddd; padding: 12px;'>{$count} records</td>";
        } else {
            echo "<td style='border: 1px solid #ddd; padding: 12px; color: red;'>❌ Missing</td>";
            echo "<td style='border: 1px solid #ddd; padding: 12px;'>-</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    
    // Show current year course statistics
    if ($DB->get_manager()->table_exists('local_annual_plan_course')) {
        $year_start = strtotime(date('Y-01-01 00:00:00'));
        $year_end = strtotime(date('Y-12-31 23:59:59'));
        
        $current_year_courses = $DB->count_records_select('local_annual_plan_course', 
            'coursedate BETWEEN ? AND ?', [$year_start, $year_end]);
        
        $approved_courses = $DB->count_records_select('local_annual_plan_course', 
            'coursedate BETWEEN ? AND ? AND approve = 1', [$year_start, $year_end]);
        
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 20px 0; border-radius: 5px;'>";
        echo "<h3>📅 Current Year (" . date('Y') . ") Course Statistics</h3>";
        echo "<p>Total courses: <strong>{$current_year_courses}</strong></p>";
        echo "<p>Approved courses: <strong>{$approved_courses}</strong></p>";
        echo "</div>";
    }
    
    // Show install.php defaults info
    echo "<div style='background: #e3f2fd; border-left: 4px solid #1976d2; padding: 20px; margin: 30px 0;'>";
    echo "<h3>📋 Install.php Default Values</h3>";
    echo "<div style='display: grid; grid-template-columns: 1fr 1fr; gap: 20px;'>";
    
    echo "<div>";
    echo "<h4>Course Levels:</h4>";
    echo "<ul style='font-size: 12px;'>";
    echo "<li>BSCOC - BSC Officer Candidates (Internal)</li>";
    echo "<li>BSCP - BSC writers (Internal)</li>";
    echo "<li>SACI - Security Awareness Course (Internal)</li>";
    echo "<li>DSC - Department Specialized courses (Internal)</li>";
    echo "<li>OQC - Officers Qualification Course (Internal)</li>";
    echo "<li>MSACE - M Security Awareness Course - External</li>";
    echo "<li>CSACE - Civil Security Awareness Course - External</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "<div>";
    echo "<h4>Financial Data & Course IDs:</h4>";
    echo "<ul style='font-size: 12px;'>";
    echo "<li><strong>Course ID Pattern:</strong> [Category][Grade][Course][Group]-[Year]([Number])</li>";
    echo "<li><strong>Course ID Example:</strong> INT301O-24(1)</li>";
    echo "<li><strong>Funding Types:</strong> Bonus, Housing, Transportation, Meals</li>";
    echo "<li><strong>Clause 802:</strong> 500.00 OMR</li>";
    echo "<li><strong>Clause 811:</strong> 1000.00 OMR</li>";
    echo "<li><strong>Finance Sources:</strong> Others, Training Plan, Project, Work</li>";
    echo "</ul>";
    echo "</div>";
    
    echo "</div>";
    echo "</div>";
}

// Main interface
echo "<div style='max-width: 1200px; margin: 0 auto; padding: 20px;'>";

echo "<div style='background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; border-radius: 10px; margin-bottom: 30px; text-align: center;'>";
echo "<h1 style='margin: 0; font-size: 2.5em;'>🧪 Annual Report Test Data Generator</h1>";
echo "<p style='margin: 10px 0 0 0; font-size: 1.2em; opacity: 0.9;'>Generate realistic test data matching install.php defaults</p>";
echo "</div>";

// Handle actions
if ($action === 'generate') {
    if ($confirm) {
        generate_all_test_data();
    } else {
        echo "<div style='background: #fff3cd; border: 1px solid #ffeaa7; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ Confirm Test Data Generation</h3>";
        echo "<p>This will create new test data matching the install.php defaults for the Annual Report block.</p>";
        echo "<p><strong>Data to be created:</strong></p>";
        echo "<ul>";
        echo "<li>7 Course Levels (BSCOC, BSCP, SACI, DSC, OQC, MSACE, CSACE)</li>";
        echo "<li>4 Finance Sources (Others, Training Plan, Project, Work)</li>";
        echo "<li>4 Funding Types (Bonus, Housing, Transportation, Meals)</li>";
        echo "<li>Sample courses for " . date('Y') . " with annualplans course ID pattern (all NOT approved)</li>";
        echo "<li>Financial clauses (802: 500 OMR, 811: 1000 OMR)</li>";
        echo "<li>Sample financial services</li>";
        echo "</ul>";
        echo "<p><strong>Are you sure you want to proceed?</strong></p>";
        echo "<a href='?action=generate&confirm=1' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>✅ Yes, Generate Test Data</a>";
        echo "<a href='?' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>❌ Cancel</a>";
        echo "</div>";
    }
} elseif ($action === 'clear') {
    if ($confirm) {
        clear_test_data();
    } else {
        echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; padding: 20px; border-radius: 5px; margin: 20px 0;'>";
        echo "<h3>⚠️ Confirm Data Clearing</h3>";
        echo "<p>This will <strong>permanently delete ALL records</strong> from the following tables:</p>";
        echo "<ul>";
        echo "<li>local_annual_plan_course_level</li>";
        echo "<li>local_annual_plan_course</li>";
        echo "<li>local_annual_plan_finance_source</li>";
        echo "<li>local_financeservices_funding_type</li>";
        echo "<li>local_financeservices_clause</li>";
        echo "<li>local_financeservices</li>";
        echo "</ul>";
        echo "<p style='color: red; font-weight: bold;'>⚠️ THIS ACTION CANNOT BE UNDONE!</p>";
        echo "<a href='?action=clear&confirm=1' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🗑️ Yes, Clear All Data</a>";
        echo "<a href='?' style='background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>❌ Cancel</a>";
        echo "</div>";
    }
} else {
    // Show main interface
    show_current_data();
    
    echo "<div style='margin: 30px 0;'>";
    echo "<h2>🎮 Actions</h2>";
    echo "<div style='display: flex; gap: 15px; flex-wrap: wrap;'>";
    
    echo "<a href='?action=generate' style='background: #007cba; color: white; padding: 15px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>";
    echo "🚀 Generate Test Data";
    echo "</a>";
    
    echo "<a href='?action=clear' style='background: #dc3545; color: white; padding: 15px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>";
    echo "🧹 Clear All Data";
    echo "</a>";
    
    echo "<a href='?' style='background: #6c757d; color: white; padding: 15px 25px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;'>";
    echo "🔄 Refresh Status";
    echo "</a>";
    
    echo "</div>";
    echo "</div>";
    
    // Instructions
    echo "<div style='background: #e3f2fd; border-left: 4px solid #1976d2; padding: 20px; margin: 30px 0;'>";
    echo "<h3>📖 Instructions</h3>";
    echo "<ol>";
    echo "<li><strong>Check Current Data:</strong> Review the table above to see existing data</li>";
    echo "<li><strong>Generate Test Data:</strong> Click 'Generate Test Data' to create sample records matching install.php defaults</li>";
    echo "<li><strong>Test the Block:</strong> Add the Annual Report block to any page to see the results</li>";
    echo "<li><strong>Clear Data:</strong> Use 'Clear All Data' to remove test data (use with caution!)</li>";
    echo "<li><strong>Redo Feature:</strong> You can clear and regenerate data as many times as needed</li>";
    echo "</ol>";
    echo "<div style='background: #fff3e0; border: 1px solid #ff9800; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>📋 Install.php Matching:</strong> This generator now uses the exact same default values as the install.php files:<br>";
    echo "• Course levels from local/annualplans/db/install.php<br>";
    echo "• Course ID pattern from local/annualplans (e.g., INT301O-24(1))<br>";
    echo "• Funding types and clauses from local/financeservices/db/install.php<br>";
    echo "• Status workflow from local/status/db/install.php";
    echo "</div>";
    echo "</div>";
    
    // Safety notice
    echo "<div style='background: #fff3e0; border: 1px solid #ffb74d; padding: 15px; border-radius: 5px; margin: 20px 0;'>";
    echo "<h4>🛡️ Safety Notice</h4>";
    echo "<p>This script only affects the annual report related tables. Your Moodle courses, users, and other data remain untouched.</p>";
    echo "<p>Always test on a development environment first!</p>";
    echo "</div>";
}

echo "</div>"; // Close main container

echo $OUTPUT->footer(); 
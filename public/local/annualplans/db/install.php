<?php
/**
 * Installation function for local_annualplans plugin
 * 
 * IMPORTANT INSTALLATION NOTES:
 * =============================
 * Before running this installation, ensure that:
 * 
 * 1. COURSE CATEGORIES SETUP:
 *    - Create the required course categories in Moodle (Site administration > Courses > Manage courses and categories)
 *    - Note down the category IDs from the mdl_course_categories table
 *    - Update the type_id values in the $course_codes_data array below to match your category IDs
 * 
 * 2. TO FIND CATEGORY IDs:
 *    Run this SQL query: SELECT id, name FROM mdl_course_categories WHERE name IN ('Intelligence', 'Management', 'Language', 'Administration', 'Technology');
 *    
 * 3. REQUIRED CATEGORIES:
 *    - Intelligence (update type_id for 'INT' code)
 *    - Management (update type_id for 'MGT' code)  
 *    - Language (update type_id for 'LAN' code)
 *    - Administration (update type_id for 'ADM' code)
 *    - Technology (update type_id for 'TEC' code)
 * 
 * 4. IF CATEGORIES DON'T EXIST:
 *    - Create them manually in Moodle interface, OR
 *    - Set type_id to NULL for codes that don't have matching categories, OR
 *    - Create the categories programmatically before this installation
 */
function xmldb_local_annualplans_install() {
    global $DB;
    $dbman = $DB->get_manager(); // Loads the database manager

    // Begin a transaction before making database changes.
    $transaction = $DB->start_delegated_transaction();
    $dummy_data = [
        [
            'year' => 2025,
            'title' => 'ضمن الخطه السنوية الحالية',
            'date_created' => time(),
            'status' => 'نشط',
            'description' => ' تحتوي على كل الدورات المعتمدة ضمن الخطة'
        ]
    ];
    foreach ($dummy_data as $data) {
        if (!$DB->insert_record('local_annual_plan', (object) $data)) {
            // If there's an error inserting a record, throw an exception which will cause the transaction to roll back.
            throw new dml_exception('inserterror', 'Error inserting dummy data into local_annual_plan_table.');
        }
    } 

        $fin_data = [
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
        ],
    ];
    foreach ($fin_data as $data) {
        if (!$DB->insert_record('local_annual_plan_finance_source', (object) $data)) {
            // If there's an error inserting a record, throw an exception which will cause the transaction to roll back.
            throw new dml_exception('inserterror', 'Error inserting dummy data into local_annual_plan_finance_source table.');
        }
    }


            $courseLvl_data = [
        [
            'id' => 1,
            'name' => 'BSCOC',
            'description_en' => 'BSC off Candidates',
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
            'description_en' => 'Sec Course',
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
            'description_en' => 'Off Qualification Course',
            'description_ar' => 'دورة ت.ض',
            'is_internal' => 1
        ],
        [
            'id' => 6,
            'name' => 'MSACE',
            'description_en' => 'M Sec Course - External',
            'description_ar' => 'دورة.ت.أ.ع.خ',
            'is_internal' => 0
        ],
        [
            'id' => 7,
            'name' => 'CSACE',
            'description_en' => 'Civil Sec Course - External',
            'description_ar' => 'دورة ت.أ.م.خ',
            'is_internal' => 0
        ]        
    ];
    foreach ($courseLvl_data as $data) {
        if (!$DB->insert_record('local_annual_plan_course_level', (object) $data)) {
            // If there's an error inserting a record, throw an exception which will cause the transaction to roll back.
            throw new dml_exception('inserterror', 'Error inserting dummy data into local_annual_plan_course_level table.');
        }
    }

    // Insert default course codes
    // ================================================================================================
    // Ensure required top-level categories exist and capture their IDs
    // This avoids manual updates to type_id values below.
    // Map code => real category name and ensure they exist
    $requiredcategories = [
        'MGT' => 'Management',
        'LAN' => 'Language',
        'IST' => 'Intelligence',
        'COM' => 'Computer',
        'BSC' => 'Basic'
    ];

    $codeToCategoryId = [];
    foreach ($requiredcategories as $code => $name) {
        // Prefer matching by idnumber == code for robustness; fallback to name
        $existing = $DB->get_record('course_categories', ['idnumber' => $code], 'id');
        if (!$existing) {
            $existing = $DB->get_record('course_categories', ['name' => $name], 'id');
        }
        if ($existing) {
            $codeToCategoryId[$code] = (int)$existing->id;
        } else {
            // Create as a parent category (parent = 0)
            $created = \core_course_category::create([
                'name' => $name,
                'idnumber' => $code,
                'description' => '',
                'parent' => 0
            ]);
            $codeToCategoryId[$code] = (int)$created->id;
        }
    }

    $managementId   = $codeToCategoryId['MGT'];
    $languageId     = $codeToCategoryId['LAN'];
    $intelligenceId = $codeToCategoryId['IST'];
    $computerId     = $codeToCategoryId['COM'];
    $basicId        = $codeToCategoryId['BSC'];
    // ================================================================================================
    $course_codes_data = [
        // Category codes
        [
            'type' => 'category',
            'code_en' => 'MGT',
            'code_ar' => 'ادر',
            'description_en' => 'Management',
            'description_ar' => 'إدارة',
            'type_id' => $managementId,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'category',
            'code_en' => 'LAN',
            'code_ar' => 'لغه',
            'description_en' => 'Language',
            'description_ar' => 'اللغة',
            'type_id' => $languageId,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'category', 
            'code_en' => 'IST',
            'code_ar' => 'ستخ',
            'description_en' => 'Intelligence',
            'description_ar' => 'الذكاء',
            'type_id' => $intelligenceId,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'category',
            'code_en' => 'COM',
            'code_ar' => 'حسب',
            'description_en' => 'Computer',
            'description_ar' => 'حاسب آلي',
            'type_id' => $computerId,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'category',
            'code_en' => 'BSC',
            'code_ar' => 'تأس',
            'description_en' => 'Basic',
            'description_ar' => 'تأسيسية',
            'type_id' => $basicId,
            'timecreated' => time(),
            'timemodified' => time()
        ],

        // Targeted group codes
        [
            'type' => 'targeted_group',
            'code_en' => 'O',
            'code_ar' => 'O',
            'description_en' => 'Off',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'targeted_group',
            'code_en' => 'NO',
            'code_ar' => 'NO',
            'description_en' => 'Non-Off',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'targeted_group',
            'code_en' => 'X',
            'code_ar' => 'X',
            'description_en' => 'Others',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        // Group number codes
        [
            'type' => 'group_number',
            'code_en' => '1',
            'code_ar' => '1',
            'description_en' => 'Group 1',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'group_number',
            'code_en' => '2',
            'code_ar' => '2',
            'description_en' => 'Group 2',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'group_number',
            'code_en' => '3',
            'code_ar' => '3',
            'description_en' => 'Group 3',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        // Course codes
        [
            'type' => 'course',
            'code_en' => '01',
            'code_ar' => '01',
            'description_en' => 'Batch no 01',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'course',
            'code_en' => '02',
            'code_ar' => '02',
            'description_en' => 'Batch no 02',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'course',
            'code_en' => '03',
            'code_ar' => '03',
            'description_en' => 'Batch No 03',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
                // Course grades
        [
            'type' => 'grade',
            'code_en' => '1',
            'code_ar' => '1',
            'description_en' => 'Beginner',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'grade',
            'code_en' => '2',
            'code_ar' => '2',
            'description_en' => 'Intermediate',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ],
        [
            'type' => 'grade',
            'code_en' => '3',
            'code_ar' => '3',
            'description_en' => 'Advance',
            'description_ar' => '',
            'type_id' => null,
            'timecreated' => time(),
            'timemodified' => time()
        ]
    ];

    foreach ($course_codes_data as $data) {
        if (!$DB->insert_record('local_annual_plan_course_codes', (object) $data)) {
            // If there's an error inserting a record, throw an exception which will cause the transaction to roll back.
            throw new dml_exception('inserterror', 'Error inserting dummy data into local_annual_plan_course_codes table.');
        }
    }

    // If we reach here, everything has been successful, commit the transaction.
    $transaction->allow_commit();
    
    // ================================================================================================
    // POST-INSTALLATION VERIFICATION
    // ================================================================================================
    // After installation, verify the data was inserted correctly by running these queries:
    // 
    // 1. Check annual plans: SELECT * FROM mdl_local_annual_plan;
    // 2. Check finance sources: SELECT * FROM mdl_local_annual_plan_finance_source;
    // 3. Check course levels: SELECT * FROM mdl_local_annual_plan_course_level;
    // 4. Check course codes: SELECT * FROM mdl_local_annual_plan_course_codes;
    // 5. Verify category links: 
    //    SELECT cc.*, cat.name as category_name 
    //    FROM mdl_local_annual_plan_course_codes cc 
    //    LEFT JOIN mdl_course_categories cat ON cc.type_id = cat.id 
    //    WHERE cc.type = 'category';
    // 
    // If any category shows NULL in category_name, update the type_id value in the course_codes table.
    // ================================================================================================
}
?>

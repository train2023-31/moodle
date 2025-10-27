<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Installation script for Finance Services plugin.
 */
function xmldb_local_financeservices_install() {
    global $DB;

    // Insert Funding Types (English and Arabic)
    $funding_types = [
        ['funding_type_en' => 'Bonus',      'funding_type_ar' => 'علاوة', 'deleted' => 0],
        ['funding_type_en' => 'Housing',        'funding_type_ar' => 'سكن', 'deleted' => 0],
        ['funding_type_en' => 'Transportation', 'funding_type_ar' => 'نقل', 'deleted' => 0],
        ['funding_type_en' => 'Meals',           'funding_type_ar' => 'تغذية', 'deleted' => 0],
        ['funding_type_en' => 'Participant',      'funding_type_ar' => 'محاضر/لاعب دور', 'deleted' => 0],
    ];

    foreach ($funding_types as $funding_type) {
        $DB->insert_record('local_financeservices_funding_type', (object)$funding_type);
    }

    // Insert dummy Clauses  🆕 amount column included
    $now = time();
    $defaultuserid = 1; // استخدم 0 أو 1 حسب ما هو متاح لديك

    $currentyear = (int)date('Y');
    $clauses = [
        [
            'clause_name_en' => 'Clause 802',
            'clause_name_ar' => 'بند 802',
            'amount' => 500.00,
            'deleted' => 0,
            'created_by' => $defaultuserid,
            'created_date' => $now,
            'clause_year' => $currentyear,
            'initial_amount' => 500.00
        ],
        [
            'clause_name_en' => 'Clause 811',
            'clause_name_ar' => 'بند 811',
            'amount' => 1000.00,
            'deleted' => 0,
            'created_by' => $defaultuserid,
            'created_date' => $now,
            'clause_year' => $currentyear,
            'initial_amount' => 1000.00
        ],
        [
            'clause_name_en' => 'Other',
            'clause_name_ar' => 'مصروفات أخرى',
            'amount' => 1000.00,
            'deleted' => 0,
            'created_by' => $defaultuserid,
            'created_date' => $now,
            'clause_year' => $currentyear,
            'initial_amount' => 1000.00
        ],
    ];
    foreach ($clauses as $clause) {
        $DB->insert_record('local_financeservices_clause', (object)$clause);
    }
    
}

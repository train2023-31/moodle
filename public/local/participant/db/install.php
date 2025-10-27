<?php
// This file is part of the Participant plugin for Moodle - http://moodle.org/
//
// It is used to insert default data into the plugin's database tables upon installation.

defined('MOODLE_INTERNAL') || die();

/**
 * Post-installation hook for the local_participant plugin.
 */
function xmldb_local_participant_install() {
    global $DB;
    global $USER;

    // === Insert default request types (without hardcoded IDs) ===
    $defaulttypes = [
        [
            'name_en' => 'Role Player (Internal)',
            'name_ar' => 'لاعب دور (داخل القيادة)',
            'cost' => 10.00,
            'calculation_type' => 'days',
            'description' => 'لاعب دور وتحسب بواقع 10 ريال عن كل يوم داخل مبنى القيادة',
            'active' => 1
        ],
        [
            'name_en' => 'Role Player (External)', 
            'name_ar' => 'لاعب دور (خارج القيادة)',
            'cost' => 15.00,
            'calculation_type' => 'days',
            'description' => 'لاعب دور وتحسب بواقع 15 ريال عن كل يوم خارج مبنى القيادة',
            'active' => 1
        ],
        [
            'name_en' => 'Role Player - Surveillance Exercise',
            'name_ar' => 'لاعب دور تمرين المراقبة',
            'cost' => 40.00,
            'calculation_type' => 'days',
            'description' => 'لاعب دور تمرين المراقبة وتحسب بواقع 40 ر.ع عن كل يوم وتشمل استخدام المركبة الخاصة والاعاشة والمقابلات',
            'active' => 1
        ],
        [
            'name_en' => 'First Period - Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor',
            'name_ar' => 'الفترة الأولى - للمحاضر/مقيم بحث/مدرب رياضة/مشرف بحث/مشرف على تمرين',
            'cost' => 15.00,
            'calculation_type' => 'days',
            'description' => 'محاضر/مقيم بحث/ مدرب رياضة / مشرف بحث/ مشرف على تمرين وتحسب بواقع 15 ريال عماني عن الفترة',
            'active' => 1
        ],
        [
            'name_en' => 'Second Period - Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor',
            'name_ar' => 'الفترة الثانية - للمحاضر/مقيم بحث/مدرب رياضة/مشرف بحث/مشرف على تمرين',
            'cost' => 12.50,
            'calculation_type' => 'days',
            'description' => 'محاضر/مقيم بحث/ مدرب رياضة / مشرف بحث/ مشرف على تمرين وتحسب بواقع 12.5 ريال عماني عن الفترة',
            'active' => 1
        ],
        [
            'name_en' => 'Third Period - Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor',
            'name_ar' => 'الفترة الثالثة - للمحاضر/مقيم بحث/مدرب رياضة/مشرف بحث/مشرف على تمرين',
            'cost' => 12.50,
            'calculation_type' => 'days',
            'description' => 'محاضر/مقيم بحث/ مدرب رياضة / مشرف بحث/ مشرف على تمرين وتحسب بواقع 12.5 ريال عماني عن الفترة',
            'active' => 1
        ],
        [
            'name_en' => 'External Lecturer',
            'name_ar' => 'محاضر خارجي',
            'cost' => 0.00,
            'calculation_type' => 'dynamic',
            'description' => 'خبير خارجي متخصص يقدم تدريباً متخصصاً ويختلف المبلغ من خبير لآخر',
            'active' => 1
        ],
    ];

    foreach ($defaulttypes as $typedata) {
        if (!$DB->record_exists('local_participant_request_types', ['name_en' => $typedata['name_en']])) {
            $record = new stdClass();
            $record->name_en = $typedata['name_en'];
            $record->name_ar = $typedata['name_ar'];
            $record->cost = $typedata['cost'];
            $record->calculation_type = $typedata['calculation_type'];
            $record->description = $typedata['description'];
            $record->active = $typedata['active'];
            $record->createdby = $USER->id ?? 0;
            $record->timecreated = time();
            $DB->insert_record('local_participant_request_types', $record);
        }
    }

    // === Status management now handled by local_status plugin ===
    // The participants_workflow is automatically installed by local_status plugin
    // with type_id=9 and includes all necessary workflow steps:
    // - request_submitted (initial)
    // - leader1_review, leader2_review, leader3_review, boss_review
    // - approved, rejected (final)
    // 
    // No need to insert status data here as it's managed by the workflow system
}

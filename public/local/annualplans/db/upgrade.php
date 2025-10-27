<?php
// This file keeps track of upgrades to the local_annualplans plugin

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function to update the database schema for local_annualplans plugin
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool true on success
 */
function xmldb_local_annualplans_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager(); // Loads the database manager.

    // Check if the version to upgrade from is before the new version (e.g., 2024101000)
    if ($oldversion < 2024101000) {

        // ===== Add new fields to the local_annual_plan_course table =====

        // Define fields to be added to local_annual_plan_course.
        $table = new xmldb_table('local_annual_plan_course');

        // 1. Add the 'disabled' field.
        $field = new xmldb_field('disabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'status');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 2. Add the 'deletion_note' field.
        $field = new xmldb_field('deletion_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'disabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 3. Add the 'unapprove_note' field.
        $field = new xmldb_field('unapprove_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'deletion_note');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // ===== Add new fields to the local_annual_plan table =====

        // Define fields to be added to local_annual_plan.
        $table = new xmldb_table('local_annual_plan');

        // 1. Add the 'disabled' field.
        $field = new xmldb_field('disabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'status');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 2. Add the 'deletion_note' field.
        $field = new xmldb_field('deletion_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'disabled');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }


// ===== Add new fields to the local_annual_plan_course_level table =====

        // Define fields to be added to local_annual_plan_course_level.
        $table1 = new xmldb_table('local_annual_plan_course_level');

        // Add the 'description_ar' field.
        $field = new xmldb_field('description_ar', XMLDB_TYPE_TEXT, null, null, null, null, null, 'disabled');
        if (!$dbman->field_exists($table1, $field)) {
            $dbman->add_field($table1, $field);
        }

           if ($dbman->table_exists($table1)) {
            $field = new xmldb_field('description');

            // Drop the old 'description' field if it exists
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        // Update the plugin's version in the database.
        upgrade_plugin_savepoint(true, 2024101000, 'local', 'annualplans');
        
    }

    // Add new table for course codes (version 2024101004)
    if ($oldversion < 2024101004) {
        
        // Define table local_annual_plan_course_codes to be created
        $table = new xmldb_table('local_annual_plan_course_codes');

        // Adding fields to table local_annual_plan_course_codes
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('code', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_annual_plan_course_codes
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_annual_plan_course_codes
        $table->add_index('type_typeid_idx', XMLDB_INDEX_NOTUNIQUE, ['type', 'type_id']);

        // Conditionally launch create table for local_annual_plan_course_codes
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Annualplans savepoint reached
        upgrade_plugin_savepoint(true, 2024101004, 'local', 'annualplans');
    }
    
    // Add code_name field to course codes (version 2024101005)
    if ($oldversion < 2024101005) {
        
        // Define field code_name to be added to local_annual_plan_course_codes
        $table = new xmldb_table('local_annual_plan_course_codes');
        $field = new xmldb_field('code_name', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'code');
        
        // Conditionally launch add field code_name
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Annualplans savepoint reached
        upgrade_plugin_savepoint(true, 2024101005, 'local', 'annualplans');
    }
    
    // Add description field to course codes (version 2024101006)
    if ($oldversion < 2024101006) {
        
        // Define field description to be added to local_annual_plan_course_codes
        $table = new xmldb_table('local_annual_plan_course_codes');
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null, 'code_name');
        
        // Conditionally launch add field description
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Annualplans savepoint reached
        upgrade_plugin_savepoint(true, 2024101006, 'local', 'annualplans');
    }

    if ($oldversion < 2024101007) {
        // Define fields to add to local_annual_plan_course
        $table = new xmldb_table('local_annual_plan_course');
        
        // Define the category_code_id field
        $field = new xmldb_field('category_code_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'disabled');
        
        // Add the field if it doesn't exist
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define the level_code_id field
        $field = new xmldb_field('level_code_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'category_code_id');
        
        // Add the field if it doesn't exist
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define the course_code_id field
        $field = new xmldb_field('course_code_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'level_code_id');
        
        // Add the field if it doesn't exist
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define the targeted_group_code_id field
        $field = new xmldb_field('targeted_group_code_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'course_code_id');
        
        // Add the field if it doesn't exist
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Define the group_number_code_id field
        $field = new xmldb_field('group_number_code_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'targeted_group_code_id');
        
        // Add the field if it doesn't exist
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Update the plugin version
        upgrade_plugin_savepoint(true, 2024101007, 'local', 'annualplans');
    }

    // Add beneficiaries table (version 2024101008)
    if ($oldversion < 2024101008) {
        
        // Define table local_annual_plan_beneficiaries to be created
        $table = new xmldb_table('local_annual_plan_beneficiaries');

        // Adding fields to table local_annual_plan_beneficiaries
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('courseid', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
        $table->add_field('coursedate', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('annualplanid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('pf_number', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null);
        $table->add_field('fullname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table local_annual_plan_beneficiaries
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Adding indexes to table local_annual_plan_beneficiaries
        $table->add_index('courseid_date_plan_idx', XMLDB_INDEX_NOTUNIQUE, ['courseid', 'coursedate', 'annualplanid']);

        // Conditionally launch create table for local_annual_plan_beneficiaries
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Update the plugin version
        upgrade_plugin_savepoint(true, 2024101008, 'local', 'annualplans');
    }

    // Add is_internal field to course level table (version 2025071400)
    if ($oldversion < 2025071400) {
        // Define table local_annual_plan_course_level to be updated
        $table = new xmldb_table('local_annual_plan_course_level');

        // Define field is_internal to be added to local_annual_plan_course_level
        $field = new xmldb_field('is_internal', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1', 'description_ar');

        // Conditionally launch add field is_internal
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Annualplans savepoint reached
        upgrade_plugin_savepoint(true, 2025071400, 'local', 'annualplans');
    }


    // Clean and repopulate course codes table (version 2025071403)
    if ($oldversion < 2025071403) {
        // Clear all existing course codes
        $DB->delete_records('local_annual_plan_course_codes');
        
        // Reset auto-increment to start from ID = 1
        $DB->execute("ALTER TABLE {local_annual_plan_course_codes} AUTO_INCREMENT = 1");

        // Insert fresh course codes data (matching install.php exactly)
        $course_codes_data = [
            // Category codes
            [
                'type' => 'category',
                'code' => 'INT',
                'description' => 'Intelligence',
                'type_id' => 9,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'category',
                'code' => 'MGT',
                'description' => 'Management',
                'type_id' => 3,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'category',
                'code' => 'LAN',
                'description' => 'Language',
                'type_id' => 6,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'category',
                'code' => 'ADM',
                'description' => 'Administration',
                'type_id' => 7,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'category',
                'code' => 'TEC',
                'description' => 'Technology',
                'type_id' => 5,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            // Targeted group codes
            [
                'type' => 'targeted_group',
                'code' => 'O',
                'description' => 'Officer',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'targeted_group',
                'code' => 'NO',
                'description' => 'Non-officer',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'targeted_group',
                'code' => 'X',
                'description' => 'Others',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            // Group number codes
            [
                'type' => 'group_number',
                'code' => '1',
                'description' => 'Group 1',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'group_number',
                'code' => '2',
                'description' => 'Group 2',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'group_number',
                'code' => '3',
                'description' => 'Group 3',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            // Course codes
            [
                'type' => 'course',
                'code' => '01',
                'description' => 'Batch no 01',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'course',
                'code' => '02',
                'description' => 'Batch no 02',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ],
            [
                'type' => 'course',
                'code' => '03',
                'description' => 'Batch No 03',
                'type_id' => null,
                'timecreated' => time(),
                'timemodified' => time()
            ]
        ];

        // Insert all course codes
        foreach ($course_codes_data as $data) {
            $DB->insert_record('local_annual_plan_course_codes', (object) $data);
        }

        // Annualplans savepoint reached
        upgrade_plugin_savepoint(true, 2025071403, 'local', 'annualplans');
    }

    if ($oldversion < 2025072401) {
        $table = new xmldb_table('local_annual_plan_course_codes');

        // Drop old 'code' field if it exists.
        $field = new xmldb_field('code');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Drop old 'description' field if it exists.
        $field = new xmldb_field('description');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Add new 'code_en' field.
        $field = new xmldb_field('code_en', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'type');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add new 'code_ar' field.
        $field = new xmldb_field('code_ar', XMLDB_TYPE_CHAR, '50', null, XMLDB_NOTNULL, null, null, 'code_en');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add new 'description_en' field.
        $field = new xmldb_field('description_en', XMLDB_TYPE_TEXT, null, null, null, null, null, 'code_ar');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add new 'description_ar' field.
        $field = new xmldb_field('description_ar', XMLDB_TYPE_TEXT, null, null, null, null, null, 'description_en');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2025072401, 'local', 'annualplans');
    }

    // Add audit fields to beneficiaries table and remove redundant ones (version 2025072501)
    if ($oldversion < 2025081200) {
        $table = new xmldb_table('local_annual_plan_beneficiaries');

        // Add created_by
        $createdby = new xmldb_field('created_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timecreated');
        if (!$dbman->field_exists($table, $createdby)) {
            $dbman->add_field($table, $createdby);
        }

        // Add timemodified
        $timemodified = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timecreated');
        if (!$dbman->field_exists($table, $timemodified)) {
            $dbman->add_field($table, $timemodified);
        }

        // Add modified_by
        $modifiedby = new xmldb_field('modified_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');
        if (!$dbman->field_exists($table, $modifiedby)) {
            $dbman->add_field($table, $modifiedby);
        }

        upgrade_plugin_savepoint(true, 2025081200, 'local', 'annualplans');
    }

    // Ensure required parent categories exist and codes are linked (version 2025091100)
    if ($oldversion < 2025091100) {
        // Create/find categories by human-readable names and map to code keys
        $required = [
            'MGT' => 'Management',
            'LAN' => 'Language',
            'IST' => 'Intelligence',
            'COM' => 'Computer',
            'BSC' => 'Basic'
        ];

        $codeToId = [];
        foreach ($required as $code => $name) {
            // Prefer idnumber match; fallback to name match
            $existing = $DB->get_record('course_categories', ['idnumber' => $code], 'id');
            if (!$existing) {
                $existing = $DB->get_record('course_categories', ['name' => $name], 'id');
            }
            if ($existing) {
                $codeToId[$code] = (int)$existing->id;
            } else {
                $created = \core_course_category::create([
                    'name' => $name,
                    'idnumber' => $code,
                    'description' => '',
                    'parent' => 0
                ]);
                $codeToId[$code] = (int)$created->id;
            }
        }

        // Update or insert category code rows to point to the correct type_id
        foreach ($required as $code => $name) {
            $params = ['type' => 'category', 'code_en' => $code];
            $row = $DB->get_record('local_annual_plan_course_codes', $params);
            if ($row) {
                if ((int)$row->type_id !== $codeToId[$code]) {
                    $row->type_id = $codeToId[$code];
                    $row->timemodified = time();
                    $DB->update_record('local_annual_plan_course_codes', $row);
                }
            } else {
                // Create missing code row (e.g., BSC)
                $new = (object) [
                    'type' => 'category',
                    'code_en' => $code,
                    'code_ar' => $code,
                    'description_en' => $name,
                    'description_ar' => $name,
                    'type_id' => $codeToId[$code],
                    'timecreated' => time(),
                    'timemodified' => time()
                ];
                $DB->insert_record('local_annual_plan_course_codes', $new);
            }
        }

        upgrade_plugin_savepoint(true, 2025091100, 'local', 'annualplans');
    }

    return true;
}

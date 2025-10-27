<?php
// This file is part of Moodle - https://moodle.org/
// 
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License...

defined('MOODLE_INTERNAL') || die();

/**
 * Execute participant management upgrade
 *
 * @param int $oldversion the version we are upgrading from
 * @return bool success
 */
function xmldb_local_participant_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();

    // Add 'rejection_note' field to 'local_participant_requests' table.
    if ($oldversion < 2024121100) { // Increment the version number appropriately.
        // Define table local_participant_requests to be updated.
        $table = new xmldb_table('local_participant_requests');

        // Define field rejection_note to be added.
        $field = new xmldb_field('rejection_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'is_approved');

        // Conditionally launch add field rejection_note.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Participant Management savepoint reached.
        upgrade_plugin_savepoint(true, 2024121100, 'local', 'participant');
    }

        if ($oldversion < 2025070101) {
        $table = new xmldb_table('local_participant_request_types');

        $field1 = new xmldb_field('createdby', XMLDB_TYPE_INTEGER, '10', null, false, null, null);
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        $field2 = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, false, null, null);
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        upgrade_plugin_savepoint(true, 2025070101, 'local', 'participant');
    }

    // Add new fields to local_participant_request_types for enhanced management
    if ($oldversion < 2025070801) {
        $table = new xmldb_table('local_participant_request_types');

        // Add cost field
        $field1 = new xmldb_field('cost', XMLDB_TYPE_NUMBER, '10,2', null, false, null, null, 'name');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }

        // Add description field
        $field2 = new xmldb_field('description', XMLDB_TYPE_TEXT, null, null, false, null, null, 'cost');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }

        // Add active field
        $field3 = new xmldb_field('active', XMLDB_TYPE_INTEGER, '1', null, true, null, '1', 'description');
        if (!$dbman->field_exists($table, $field3)) {
            $dbman->add_field($table, $field3);
        }

        // Add name_en field
        $field4 = new xmldb_field('name_en', XMLDB_TYPE_CHAR, '255', null, false, null, null, 'name');
        if (!$dbman->field_exists($table, $field4)) {
            $dbman->add_field($table, $field4);
        }

        // Add name_ar field
        $field5 = new xmldb_field('name_ar', XMLDB_TYPE_CHAR, '255', null, false, null, null, 'name_en');
        if (!$dbman->field_exists($table, $field5)) {
            $dbman->add_field($table, $field5);
        }

        // Populate existing records with multilingual names
        // Only migrate if the old 'name' field still exists
        if ($dbman->field_exists($table, new xmldb_field('name'))) {
            $existingtypes = $DB->get_records('local_participant_request_types');
            foreach ($existingtypes as $type) {
                $update = new stdClass();
                $update->id = $type->id;
                
                // Set English and Arabic names based on existing name
                if (isset($type->name)) {
                    if (strpos($type->name, 'Role Player') !== false || strpos($type->name, 'لاعب') !== false) {
                        $update->name_en = 'Role Player';
                        $update->name_ar = 'لاعب دور';
                    } elseif (strpos($type->name, 'Assistant') !== false || strpos($type->name, 'مشارك') !== false) {
                        $update->name_en = 'Assistant Lecturer';
                        $update->name_ar = 'محاضر مشارك';
                    } elseif (strpos($type->name, 'External') !== false || strpos($type->name, 'خارجي') !== false) {
                        $update->name_en = 'External Lecturer';
                        $update->name_ar = 'محاضر خارجي';
                    } else {
                        // For custom types, use the existing name as fallback
                        $update->name_en = $type->name;
                        $update->name_ar = $type->name;
                    }
                    
                    $DB->update_record('local_participant_request_types', $update);
                }
            }
        }

        upgrade_plugin_savepoint(true, 2025070801, 'local', 'participant');
    }

    // Remove the old 'name' field from local_participant_request_types
    if ($oldversion < 2025070901) {
        $table = new xmldb_table('local_participant_request_types');

        // Drop the old 'name' field since we now use name_en and name_ar
        $field = new xmldb_field('name');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025070901, 'local', 'participant');
    }

    // Update existing participant request types to match install.php default values
    if ($oldversion < 2025070902) {
        $standardtypes = [
            [
                'name_en' => 'Role Player',
                'name_ar' => 'لاعب دور',
                'cost' => 100.00,
                'description' => 'مشارك يلعب دوراً محدداً في سيناريوهات التدريب',
                'active' => 1
            ],
            [
                'name_en' => 'Assistant Lecturer',
                'name_ar' => 'محاضر مشارك',
                'cost' => 200.00,
                'description' => 'محاضر مساعد يقدم الدعم في تنفيذ التدريب',
                'active' => 1
            ],
            [
                'name_en' => 'External Lecturer',
                'name_ar' => 'محاضر خارجي',
                'cost' => 500.00,
                'description' => 'خبير خارجي متخصص يقدم تدريباً متخصصاً',
                'active' => 1
            ],
        ];

        foreach ($standardtypes as $typedata) {
            // Check if a record with this name_en exists
            $existingrecord = $DB->get_record('local_participant_request_types', ['name_en' => $typedata['name_en']]);
            
            if ($existingrecord) {
                // Update existing record to match standard values
                $update = new stdClass();
                $update->id = $existingrecord->id;
                $update->name_en = $typedata['name_en'];
                $update->name_ar = $typedata['name_ar'];
                $update->cost = $typedata['cost'];
                $update->description = $typedata['description'];
                $update->active = $typedata['active'];
                
                $DB->update_record('local_participant_request_types', $update);
            } else {
                // If it doesn't exist, create it
                $record = new stdClass();
                $record->name_en = $typedata['name_en'];
                $record->name_ar = $typedata['name_ar'];
                $record->cost = $typedata['cost'];
                $record->description = $typedata['description'];
                $record->active = $typedata['active'];
                $record->createdby = 0; // System created
                $record->timecreated = time();
                $DB->insert_record('local_participant_request_types', $record);
            }
        }

        upgrade_plugin_savepoint(true, 2025070902, 'local', 'participant');
    }

    // Update participant request types with new values and explicit IDs
    if ($oldversion < 2025070903) {
        // Clear all existing records to start fresh with specific IDs
        $DB->delete_records('local_participant_request_types');
        
        $newtypes = [
            [
                'id' => 1,
                'name_en' => 'Role Player (Internal)',
                'name_ar' => 'لاعب دور (داخل القيادة)',
                'cost' => 10.00,
                'description' => 'لاعب دور وتحسب بواقع 10 ريال عن كل يوم داخل مبنى القيادة',
                'active' => 1
            ],
            [
                'id' => 2,
                'name_en' => 'Role Player (External)', 
                'name_ar' => 'لاعب دور (خارج القيادة)',
                'cost' => 15.00,
                'description' => 'لاعب دور وتحسب بواقع 15 ريال عن كل يوم خارج مبنى القيادة',
                'active' => 1
            ],
            [
                'id' => 3,
                'name_en' => 'Role Player - Surveillance Exercise',
                'name_ar' => 'لاعب دور تمرين المراقبة',
                'cost' => 40.00,
                'description' => 'لاعب دور تمرين المراقبة وتحسب بواقع 40 ر.ع عن كل يوم وتشمل استخدام المركبة الخاصة والاعاشة والمقابلات',
                'active' => 1
            ],
            [
                'id' => 4,
                'name_en' => 'Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor',
                'name_ar' => 'محاضر/مقيم بحث/مدرب رياضة/مشرف بحث/مشرف على تمرين',
                'cost' => 10.00,
                'description' => 'محاضر/مقيم بحث/ مدرب رياضة / مشرف بحث/ مشرف على تمرين وتحسب بواقع 10 ريال عماني عن كل ساعة',
                'active' => 1
            ],
            [
                'id' => 5,
                'name_en' => 'External Lecturer',
                'name_ar' => 'محاضر خارجي',
                'cost' => 0.00,
                'description' => 'خبير خارجي متخصص يقدم تدريباً متخصصاً ويختلف المبلغ من خبير لآخر',
                'active' => 1
            ],
        ];

        foreach ($newtypes as $typedata) {
            $record = new stdClass();
            $record->id = $typedata['id'];
            $record->name_en = $typedata['name_en'];
            $record->name_ar = $typedata['name_ar'];
            $record->cost = $typedata['cost'];
            $record->description = $typedata['description'];
            $record->active = $typedata['active'];
            $record->createdby = 0; // System created
            $record->timecreated = time();
            $DB->insert_record('local_participant_request_types', $record, false); // Don't return ID since we're setting it explicitly
        }
        
        // Reset the auto-increment counter to start from 6 for future records
        if ($DB->get_dbfamily() === 'mysql') {
            $DB->execute("ALTER TABLE {local_participant_request_types} AUTO_INCREMENT = 6");
        }

        upgrade_plugin_savepoint(true, 2025070903, 'local', 'participant');
    }

    // Migrate database structure to improved field names and add audit fields
    if ($oldversion < 2025070904) {
        $dbman = $DB->get_manager();
        
        // === Migrate local_participant_requests table ===
        $table = new xmldb_table('local_participant_requests');
        
        // Drop existing foreign keys before renaming fields (if they exist)
        try {
            $key1 = new xmldb_key('type_fk', XMLDB_KEY_FOREIGN, array('type_id'), 'local_participant_request_types', array('id'));
            $dbman->drop_key($table, $key1);
        } catch (Exception $e) {
            // Key might not exist, continue
        }
        
        try {
            $key2 = new xmldb_key('status_fk', XMLDB_KEY_FOREIGN, array('status_id'), 'local_participant_request_status', array('id'));
            $dbman->drop_key($table, $key2);
        } catch (Exception $e) {
            // Key might not exist, continue
        }
        
        // Rename fields to improved names
        $field = new xmldb_field('plan_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'annual_plan_id');
        }
        
        $field = new xmldb_field('type_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'participant_type_id');
        }
        
        $field = new xmldb_field('exl_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'external_lecturer_id');
        }
        
        $field = new xmldb_field('m_id', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'internal_user_id');
        }
        
        $field = new xmldb_field('is_inside', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'is_internal_participant');
            // Update default value logic: was 0 for external, now 1 for internal
            $DB->execute("UPDATE {local_participant_requests} SET is_internal_participant = CASE WHEN is_internal_participant = 0 THEN 0 ELSE 1 END");
        }
        
        $field = new xmldb_field('n_days_hours', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'duration_amount');
        }
        
        $field = new xmldb_field('c_amount', XMLDB_TYPE_FLOAT, null, null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'compensation_amount');
        }
        
        $field = new xmldb_field('status_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'request_status_id');
        }
        
        $field = new xmldb_field('date', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'requested_date');
        }
        
        $field = new xmldb_field('rejection_note', XMLDB_TYPE_TEXT, null, null, null, null, null);
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'rejection_reason');
        }
        
        // Add new audit fields
        $field = new xmldb_field('created_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'rejection_reason');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        $field = new xmldb_field('time_created', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'created_by');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            // Set creation time for existing records
            $DB->execute("UPDATE {local_participant_requests} SET time_created = ? WHERE time_created IS NULL", [time()]);
        }
        
        $field = new xmldb_field('time_modified', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'time_created');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            // Set modification time for existing records
            $DB->execute("UPDATE {local_participant_requests} SET time_modified = ? WHERE time_modified IS NULL", [time()]);
        }
        
        // Re-add foreign keys with new field names
        $key = new xmldb_key('participant_type_fk', XMLDB_KEY_FOREIGN, array('participant_type_id'), 'local_participant_request_types', array('id'));
        $dbman->add_key($table, $key);
        
        $key = new xmldb_key('request_status_fk', XMLDB_KEY_FOREIGN, array('request_status_id'), 'local_participant_request_status', array('id'));
        $dbman->add_key($table, $key);
        
        // Note: Lookup tables (local_participant_request_types and local_participant_request_status) 
        // maintain their current field names as per install.xml - no migration needed for those tables

        upgrade_plugin_savepoint(true, 2025070904, 'local', 'participant');
    }

    // Add calculation_type field to participant request types
    if ($oldversion < 2025070905) {
        $table = new xmldb_table('local_participant_request_types');

        // Add calculation_type field
        $field = new xmldb_field('calculation_type', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'days', 'cost');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Update existing records with appropriate calculation types based on their descriptions
        $types = $DB->get_records('local_participant_request_types');
        foreach ($types as $type) {
            $update = new stdClass();
            $update->id = $type->id;
            
            // Determine calculation type based on existing data
            if ($type->id == 1 || $type->id == 2 || $type->id == 3) {
                // Role player types - calculated by days
                $update->calculation_type = 'days';
            } elseif ($type->id == 4) {
                // Lecturer/evaluator types - calculated by hours
                $update->calculation_type = 'hours';
            } elseif ($type->id == 5) {
                // External lecturer - dynamic pricing
                $update->calculation_type = 'dynamic';
            } else {
                // For any other types, default to days
                $update->calculation_type = 'days';
            }
            
            $DB->update_record('local_participant_request_types', $update);
        }

        upgrade_plugin_savepoint(true, 2025070905, 'local', 'participant');
    }

    // Migrate to new workflow system using local_status
    if ($oldversion < 2025071302) {
        global $CFG;
        require_once($CFG->dirroot . '/local/participant/classes/simple_workflow_manager.php');
        
        // Step 1: Migrate old status IDs to new workflow status IDs
        \local_participant\simple_workflow_manager::migrate_old_statuses();
        
        // Step 2: Update database structure - change foreign key reference
        $table = new xmldb_table('local_participant_requests');
        
        // Drop old foreign key if it exists
        try {
            $key = new xmldb_key('request_status_fk', XMLDB_KEY_FOREIGN, array('request_status_id'), 'local_participant_request_status', array('id'));
            $dbman->drop_key($table, $key);
        } catch (Exception $e) {
            // Key might not exist, continue
        }
        
        // Add new foreign key to local_status table
        $key = new xmldb_key('request_status_workflow_fk', XMLDB_KEY_FOREIGN, array('request_status_id'), 'local_status', array('id'));
        $dbman->add_key($table, $key);
        
        // Step 3: Update install.xml comment to reflect new structure
        // The install.xml COMMENT will be updated to reference local_status instead of local_participant_request_status
        
        upgrade_plugin_savepoint(true, 2025071302, 'local', 'participant');
    }

    // Add Oracle data fields for participant information
    if ($oldversion < 2025071303) {
        $table = new xmldb_table('local_participant_requests');
        
        // Add pf_number field
        $field1 = new xmldb_field('pf_number', XMLDB_TYPE_CHAR, '50', null, false, null, null, 'internal_user_id');
        if (!$dbman->field_exists($table, $field1)) {
            $dbman->add_field($table, $field1);
        }
        
        // Add participant_full_name field
        $field2 = new xmldb_field('participant_full_name', XMLDB_TYPE_CHAR, '255', null, false, null, null, 'pf_number');
        if (!$dbman->field_exists($table, $field2)) {
            $dbman->add_field($table, $field2);
        }
        
        // Update the comment for internal_user_id to mark it as deprecated
        // This is handled in the install.xml file
        
        upgrade_plugin_savepoint(true, 2025071303, 'local', 'participant');
    }

    // 2025081200 – Add modified_by audit field to requests
    if ($oldversion < 2025081200) {
        $table = new xmldb_table('local_participant_requests');
        $field = new xmldb_field('modified_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'time_modified');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Backfill modified_by with created_by where possible
        $DB->execute("UPDATE {local_participant_requests} SET modified_by = created_by WHERE modified_by IS NULL");

        upgrade_plugin_savepoint(true, 2025081200, 'local', 'participant');
    }

    // 2025081201 – Backfill names and drop deprecated internal_user_id
    if ($oldversion < 2025081201) {
        $table = new xmldb_table('local_participant_requests');
        $field = new xmldb_field('internal_user_id');

        if ($dbman->field_exists($table, $field)) {
            // Backfill participant_full_name from the Moodle user table where possible.
            // Only fill when participant_full_name is empty and internal_user_id is present.
            $records = $DB->get_records_select(
                'local_participant_requests',
                "internal_user_id IS NOT NULL AND (participant_full_name IS NULL OR participant_full_name = '')",
                null,
                '',
                'id, internal_user_id'
            );

            if (!empty($records)) {
                foreach ($records as $rec) {
                    $user = $DB->get_record('user', ['id' => $rec->internal_user_id], 'id, firstname, lastname');
                    if ($user) {
                        $update = new stdClass();
                        $update->id = $rec->id;
                        $update->participant_full_name = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
                        $DB->update_record('local_participant_requests', $update);
                    }
                }
            }

            // Now drop the deprecated field.
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025081201, 'local', 'participant');
    }

    // 2025081800 – Insert three period lecturer/evaluator types
    if ($oldversion < 2025081800) {
        $types = [
            [
                'name_en' => 'First Period - Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor',
                'name_ar' => 'الفترة الأولى - للمحاضر/مقيم بحث/مدرب رياضة/مشرف بحث/مشرف على تمرين',
                'cost' => 15.00,
                'calculation_type' => 'days',
                'description' => 'محاضر/مقيم بحث/ مدرب رياضة / مشرف بحث/ مشرف على تمرين وتحسب بواقع 10 ريال عماني عن كل ساعة',
                'active' => 1
            ],
            [
                'name_en' => 'Second Period - Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor',
                'name_ar' => 'الفترة الثانية - للمحاضر/مقيم بحث/مدرب رياضة/مشرف بحث/مشرف على تمرين',
                'cost' => 12.50,
                'calculation_type' => 'days',
                'description' => 'محاضر/مقيم بحث/ مدرب رياضة / مشرف بحث/ مشرف على تمرين وتحسب بواقع 10 ريال عماني عن كل ساعة',
                'active' => 1
            ],
            [
                'name_en' => 'Third Period - Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor',
                'name_ar' => 'الفترة الثالثة - للمحاضر/مقيم بحث/مدرب رياضة/مشرف بحث/مشرف على تمرين',
                'cost' => 12.50,
                'calculation_type' => 'days',
                'description' => 'محاضر/مقيم بحث/ مدرب رياضة / مشرف بحث/ مشرف على تمرين وتحسب بواقع 10 ريال عماني عن كل ساعة',
                'active' => 1
            ],
        ];

        foreach ($types as $typedata) {
            if (!$DB->record_exists('local_participant_request_types', ['name_en' => $typedata['name_en']])) {
                $record = new stdClass();
                $record->name_en = $typedata['name_en'];
                $record->name_ar = $typedata['name_ar'];
                $record->cost = $typedata['cost'];
                $record->calculation_type = $typedata['calculation_type'];
                $record->description = $typedata['description'];
                $record->active = $typedata['active'];
                $record->createdby = 0; // System created
                $record->timecreated = time();
                $DB->insert_record('local_participant_request_types', $record);
            }
        }

        // Remove row four (id = 4) from the table if it exists and matches the expected name_en (requested change).
        $row = $DB->get_record('local_participant_request_types', ['id' => 4], 'id, name_en');
        if ($row && $row->name_en === 'Lecturer/Research Evaluator/Sports Trainer/Research Supervisor/Exercise Supervisor') {
            $DB->delete_records('local_participant_request_types', ['id' => 4]);
        }

        upgrade_plugin_savepoint(true, 2025081800, 'local', 'participant');
    }


    if ($oldversion < 2025091500) {
        $table = new xmldb_table('local_participant_requests');

        // clause field
        $field = new xmldb_field('clause', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'participant_type_id');
        if (!$DB->get_manager()->field_exists($table, $field)) {
            $DB->get_manager()->add_field($table, $field);
        }

        // funding_type field
        $field = new xmldb_field('funding_type', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'clause');
        if (!$DB->get_manager()->field_exists($table, $field)) {
            $DB->get_manager()->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025091500, 'local', 'participant');
    }


    return true;
}

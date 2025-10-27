<?php
// This file keeps track of upgrades to the local_residencebooking plugin

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function to update the database schema for local_residencebooking plugin
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool true on success
 */
function xmldb_local_residencebooking_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager(); // Loads the database manager.

    // Upgrade step for adding 'unapprove_note' field (previously added)
    if ($oldversion < 2024050102) {

        // Define table and field to be added.
        $table = new xmldb_table('local_residencebooking_request');
        $field = new xmldb_field('unapprove_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'status');

        // Conditionally launch add field 'unapprove_note'.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Upgrade plugin savepoint.
        upgrade_plugin_savepoint(true, 2024050102, 'local', 'residencebooking');
    }

    // **New upgrade step for changing the default value of 'status' field**
    if ($oldversion < 2024050104) {

        // Define the table and field to be modified.
        $table = new xmldb_table('local_residencebooking_request');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'pending', 'service_number');

        // Conditionally launch change of default value for field 'status'.
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_default($table, $field);
        }

        // Upgrade plugin savepoint.
        upgrade_plugin_savepoint(true, 2024050104, 'local', 'residencebooking');
    }

        // **New upgrade step for changing the default value of 'status' field**
        if ($oldversion < 2024050116) {

            // Define the table and field to be modified.
            $table = new xmldb_table('local_residencebooking_request');
            $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'id');
    
            // Conditionally launch change of default value for field 'status'.
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
    
            // Upgrade plugin savepoint.
            upgrade_plugin_savepoint(true, 2024050116, 'local', 'residencebooking');
        }

        // ✅ 2024050200: Migrate from old 'status' to workflow-based 'status_id'
        if ($oldversion < 2024050200) {

            $table = new xmldb_table('local_residencebooking_request');

            // 1. Add status_id field if not present
            $statusidfield = new xmldb_field('status_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'courseid');
            if (!$dbman->field_exists($table, $statusidfield)) {
                $dbman->add_field($table, $statusidfield);
            }

            // 2. Migrate status values to status_id, only if 'status' column still exists
            if ($dbman->field_exists($table, 'status')) {
                require_once(__DIR__ . '/../classes/status_manager.php');

                $pending  = \local_residencebooking\status_manager::initial_status()->id;
                $approved = \local_residencebooking\status_manager::approved_status()->id;
                $rejected = \local_residencebooking\status_manager::rejected_status()->id;

                $DB->execute("
                    UPDATE {local_residencebooking_request}
                    SET status_id = CASE status
                                        WHEN 'pending'  THEN :p
                                        WHEN 'approved' THEN :a
                                        WHEN 'rejected' THEN :r
                                        ELSE :e
                                    END
                ", [
                    'p' => $pending,
                    'a' => $approved,
                    'r' => $rejected,
                    'e' => $pending
                ]);

                // Drop the 'status' field after migrating
                $dbman->drop_field($table, new xmldb_field('status'));
            }


            // 3. Drop old 'status' field if exists
            if ($dbman->field_exists($table, 'status')) {
                $dbman->drop_field($table, new xmldb_field('status'));
            }

            // 4. Rename 'unapprove_note' → 'rejection_note' with full definition
            if ($dbman->field_exists($table, 'unapprove_note')) {
                $field = new xmldb_field('unapprove_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'service_number');
                $dbman->rename_field($table, $field, 'rejection_note');
            }

            // 5. Mark upgrade complete
            upgrade_plugin_savepoint(true, 2024050200, 'local', 'residencebooking');
        }

        /* --------------------------------------------------------------------
        2025051200 – Soft‑delete support for lookup tables
        -------------------------------------------------------------------- */
        if ($oldversion < 2025051200) {

            // 1️⃣ Add deleted flag to local_residencebooking_types
            $table = new xmldb_table('local_residencebooking_types');
            $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null,
                                    XMLDB_NOTNULL, null, 0, 'type_name');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // 2️⃣ Add deleted flag to local_residencebooking_purpose
            $table = new xmldb_table('local_residencebooking_purpose');
            $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null,
                                    XMLDB_NOTNULL, null, 0, 'description');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            upgrade_plugin_savepoint(true, 2025051200, 'local', 'residencebooking');
        }

        /* --------------------------------------------------------------------
        2025052000 – Multilingual fields support for types and purposes
        -------------------------------------------------------------------- */
        if ($oldversion < 2025051201) {
            
            // 1️⃣ Add multilingual fields to local_residencebooking_types
            $table = new xmldb_table('local_residencebooking_types');
            
            // Add type_name_en field
            $field_en = new xmldb_field('type_name_en', XMLDB_TYPE_CHAR, 255, null,
                                    XMLDB_NOTNULL, null, null, 'id');
            if (!$dbman->field_exists($table, $field_en)) {
                $dbman->add_field($table, $field_en);
                
                // Copy current type_name values to type_name_en
                $DB->execute("UPDATE {local_residencebooking_types} SET type_name_en = type_name");
            }
            
            // Add type_name_ar field
            $field_ar = new xmldb_field('type_name_ar', XMLDB_TYPE_CHAR, 255, null,
                                    XMLDB_NOTNULL, null, null, 'type_name_en');
            if (!$dbman->field_exists($table, $field_ar)) {
                $dbman->add_field($table, $field_ar);
                
                // Copy current type_name values to type_name_ar
                $DB->execute("UPDATE {local_residencebooking_types} SET type_name_ar = type_name");
            }
            
            // Update the NOTNULL constraints of the legacy field
            $field_type_name = new xmldb_field('type_name', XMLDB_TYPE_CHAR, 255, null,
                                    null, null, null, 'type_name_ar');
            $dbman->change_field_notnull($table, $field_type_name);
            
            // 2️⃣ Add multilingual fields to local_residencebooking_purpose
            $table = new xmldb_table('local_residencebooking_purpose');
            
            // Add description_en field
            $field_en = new xmldb_field('description_en', XMLDB_TYPE_CHAR, 255, null,
                                    XMLDB_NOTNULL, null, null, 'id');
            if (!$dbman->field_exists($table, $field_en)) {
                $dbman->add_field($table, $field_en);
                
                // Copy current description values to description_en
                $DB->execute("UPDATE {local_residencebooking_purpose} SET description_en = description");
            }
            
            // Add description_ar field
            $field_ar = new xmldb_field('description_ar', XMLDB_TYPE_CHAR, 255, null,
                                    XMLDB_NOTNULL, null, null, 'description_en');
            if (!$dbman->field_exists($table, $field_ar)) {
                $dbman->add_field($table, $field_ar);
                
                // Copy current description values to description_ar
                $DB->execute("UPDATE {local_residencebooking_purpose} SET description_ar = description");
            }
            
            // Update the NOTNULL constraints of the legacy field
            $field_desc = new xmldb_field('description', XMLDB_TYPE_CHAR, 255, null,
                                    null, null, null, 'description_ar');
            $dbman->change_field_notnull($table, $field_desc);
            
            // 3️⃣ Create indexes for better performance
            $table = new xmldb_table('local_residencebooking_types');
            $index = new xmldb_index('type_name_en_idx', XMLDB_INDEX_NOTUNIQUE, ['type_name_en']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
            
            $index = new xmldb_index('type_name_ar_idx', XMLDB_INDEX_NOTUNIQUE, ['type_name_ar']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
            
            $table = new xmldb_table('local_residencebooking_purpose');
            $index = new xmldb_index('description_en_idx', XMLDB_INDEX_NOTUNIQUE, ['description_en']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
            
            $index = new xmldb_index('description_ar_idx', XMLDB_INDEX_NOTUNIQUE, ['description_ar']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
            
            upgrade_plugin_savepoint(true, 2025051201, 'local', 'residencebooking');
        }

        /* --------------------------------------------------------------------
        2025051202 – Remove legacy fields now that multilingual fields are in use
        -------------------------------------------------------------------- */
        if ($oldversion < 2025051202) {
            
            // Verify that all necessary data has been migrated
            $types_migrated = true;
            
            // 1️⃣ Check if all records have multilingual data
            $types_records = $DB->get_records('local_residencebooking_types');
            foreach ($types_records as $record) {
                if (empty($record->type_name_en) || empty($record->type_name_ar)) {
                    // Missing multilingual data, copy from legacy field
                    $DB->set_field('local_residencebooking_types', 'type_name_en', $record->type_name, ['id' => $record->id]);
                    $DB->set_field('local_residencebooking_types', 'type_name_ar', $record->type_name, ['id' => $record->id]);
                }
            }
            
            // Check purposes table similarly
            $purposes_records = $DB->get_records('local_residencebooking_purpose');
            foreach ($purposes_records as $record) {
                if (empty($record->description_en) || empty($record->description_ar)) {
                    // Missing multilingual data, copy from legacy field
                    $DB->set_field('local_residencebooking_purpose', 'description_en', $record->description, ['id' => $record->id]);
                    $DB->set_field('local_residencebooking_purpose', 'description_ar', $record->description, ['id' => $record->id]);
                }
            }
            
            // 2️⃣ Drop the legacy fields from both tables
            $table = new xmldb_table('local_residencebooking_types');
            $field = new xmldb_field('type_name');
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
            
            $table = new xmldb_table('local_residencebooking_purpose');
            $field = new xmldb_field('description');
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
            
            upgrade_plugin_savepoint(true, 2025051202, 'local', 'residencebooking');
        }

        /* --------------------------------------------------------------------
        2025061700 – Add approval_note field & update workflow capabilities
        -------------------------------------------------------------------- */
        if ($oldversion < 2025061700) {
            
            // Add approval_note field to local_residencebooking_request
            $table = new xmldb_table('local_residencebooking_request');
            $field = new xmldb_field('approval_note', XMLDB_TYPE_TEXT, null, null,
                                   null, null, null, 'rejection_note');
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            
            upgrade_plugin_savepoint(true, 2025061700, 'local', 'residencebooking');
        }

    /* --------------------------------------------------------------------
       2025081200 – Add audit fields for requests (Oracle-sourced guest data)
       -------------------------------------------------------------------- */
    if ($oldversion < 2025081200) {
        // Define table
        $table = new xmldb_table('local_residencebooking_request');

        // timecreated
        $timecreated = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'approval_note');
        if (!$dbman->field_exists($table, $timecreated)) {
            $dbman->add_field($table, $timecreated);
        }

        // timemodified
        $timemodified = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');
        if (!$dbman->field_exists($table, $timemodified)) {
            $dbman->add_field($table, $timemodified);
        }

        // created_by
        $createdby = new xmldb_field('created_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'timemodified');
        if (!$dbman->field_exists($table, $createdby)) {
            $dbman->add_field($table, $createdby);
        }

        // modified_by
        $modifiedby = new xmldb_field('modified_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'created_by');
        if (!$dbman->field_exists($table, $modifiedby)) {
            $dbman->add_field($table, $modifiedby);
        }

        // Backfill existing rows with sensible values
        $now = time();
        // Ensure timecreated is set
        $DB->execute("UPDATE {local_residencebooking_request} SET timecreated = :now WHERE timecreated IS NULL OR timecreated = 0", ['now' => $now]);
        // Ensure timemodified is set to timecreated if missing
        $DB->execute("UPDATE {local_residencebooking_request} SET timemodified = CASE WHEN timemodified IS NULL OR timemodified = 0 THEN timecreated ELSE timemodified END");
        // Default created_by and modified_by to the requester (userid) when missing
        $DB->execute("UPDATE {local_residencebooking_request} SET created_by = userid WHERE created_by IS NULL");
        $DB->execute("UPDATE {local_residencebooking_request} SET modified_by = userid WHERE modified_by IS NULL");

        upgrade_plugin_savepoint(true, 2025081200, 'local', 'residencebooking');
    }

    return true;
}

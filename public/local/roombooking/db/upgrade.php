<?php
// This file keeps track of upgrades to the local_roombooking plugin

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function to update the database schema for local_roombooking plugin
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool true on success
 */
function xmldb_local_roombooking_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager(); // Loads the database manager.

    // Upgrade step for adding 'status' and 'rejection_note' fields.
    if ($oldversion < 2024051210) {

        // Define table
        $table = new xmldb_table('local_roombooking_course_bookings');

        // Define the 'status' field
        $status_field = new xmldb_field('status', XMLDB_TYPE_CHAR, '30', null, XMLDB_NOTNULL, null, 'pending',);

        // Conditionally add the 'status' field
        if (!$dbman->field_exists($table, $status_field)) {
            $dbman->add_field($table, $status_field);
        }

        // Define the 'rejection_note' field
        $rejection_note_field = new xmldb_field('rejection_note', XMLDB_TYPE_TEXT, null, null, null, null, null,);

        // Conditionally add the 'rejection_note' field
        if (!$dbman->field_exists($table, $rejection_note_field)) {
            $dbman->add_field($table, $rejection_note_field);
        }

        // Upgrade plugin savepoint
        upgrade_plugin_savepoint(true, 2024051210, 'local', 'roombooking');
    }

    // ──────────────────────────────────────────────────────────────────
    // 2025051300 – integrate local_status workflow fields
    // ──────────────────────────────────────────────────────────────────
    if ($oldversion < 2025051300) {
        $table = new xmldb_table('local_roombooking_course_bookings');

        /* -------- Add status_id column (FK target) -------- */
        $field = new xmldb_field('status_id', XMLDB_TYPE_INTEGER, '10',
                                 null, XMLDB_NOTNULL, null, 0, 'recurrence_end_date');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        /* -------- Add approval_note column -------- */
        $field = new xmldb_field('approval_note', XMLDB_TYPE_TEXT);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        /* -------- Foreign‑key to local_status -------- */
        $key = new xmldb_key('status_fk', XMLDB_KEY_FOREIGN,
                             ['status_id'], 'local_status', ['id']);
        if (!$dbman->find_key_name($table, $key)) {
            $dbman->add_key($table, $key);
        }

        /* -------- Migrate existing status values to status_id -------- */
        if ($dbman->field_exists($table, new xmldb_field('status'))) {
            // Map old string statuses to status IDs in local_status
            $statusmap = [
                'pending' => $DB->get_field('local_status', 'id', 
                                           ['type_id' => 8, 'seq' => 1], MUST_EXIST),
                'approved' => $DB->get_field('local_status', 'id', 
                                            ['type_id' => 8, 'seq' => 10], MUST_EXIST),
                'rejected' => $DB->get_field('local_status', 'id', 
                                           ['type_id' => 8, 'seq' => 1], MUST_EXIST),
            ];
            
            // Update each row according to its current status
            foreach ($statusmap as $oldstatus => $statusid) {
                $DB->execute(
                    "UPDATE {local_roombooking_course_bookings} 
                        SET status_id = :sid
                      WHERE status = :status AND (status_id = 0 OR status_id IS NULL)",
                    ['sid' => $statusid, 'status' => $oldstatus]
                );
            }
            
            // Set a default for any rows missed
            $initialid = $statusmap['pending'];
            $DB->execute(
                "UPDATE {local_roombooking_course_bookings}
                    SET status_id = :sid
                  WHERE status_id = 0 OR status_id IS NULL",
                ['sid' => $initialid]
            );
        }

        // Mark upgrade save‑point
        upgrade_plugin_savepoint(true, 2025051300, 'local', 'roombooking');
    }


    // 2025061600 – add 'deleted' column for soft-delete
    if ($oldversion < 2025061600) {
        $table = new xmldb_table('local_roombooking_rooms');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1',
                                null, XMLDB_NOTNULL, null, 0, 'description');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025061600, 'local', 'roombooking');
    }


    return true;
}

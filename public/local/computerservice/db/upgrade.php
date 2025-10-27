<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade function to update the database schema for local_computerservice plugin
 *
 * @param int $oldversion The version we are upgrading from.
 * @return bool true on success
 */
function xmldb_local_computerservice_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager(); // Loads the database manager.

    // --- Previously added 'unapprove_note' field ---
    if ($oldversion < 2024100905) {
        $table = new xmldb_table('local_computerservice_requests');
        $field = new xmldb_field('unapprove_note', XMLDB_TYPE_TEXT, null, null, null, null, null);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024100905, 'local', 'computerservice');
    }

    // --- Previously added 'status' field to 'local_computerservice_devices' table ---
    if ($oldversion < 2024100910) {
        $table = new xmldb_table('local_computerservice_devices');
        $field = new xmldb_field('status', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL, null, 'active');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024100910, 'local', 'computerservice');
    }

    // --- NEW UPGRADE: Add 'request_needed_by' and 'is_urgent' fields ---
    if ($oldversion < 2025031606) {
        $table = new xmldb_table('local_computerservice_requests');

        // 1) Add request_needed_by
        $field = new xmldb_field('request_needed_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'comments');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 2) Add is_urgent
        $field = new xmldb_field('is_urgent', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'request_needed_by');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025031606, 'local', 'computerservice');
    }

    // --- NEW UPGRADE: Replace status with status_id and FK ---
    if ($oldversion < 2025042204) {

        $table = new xmldb_table('local_computerservice_requests');

        // Add the new column 'status_id' if it doesn't exist
        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('status_id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0); // Adjust default if needed
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }

            // Drop the old foreign key (if it existed)
            $key = new xmldb_key('status_fk', XMLDB_KEY_FOREIGN, ['status_id'], 'local_status_type', ['id']);
            $dbman->drop_key($table, $key); // Safe even if not exists

            // Add the new foreign key
            $dbman->add_key($table, $key);
        }

        if ($dbman->table_exists($table)) {
            $field = new xmldb_field('status');

            // Drop the old 'status' field if it exists
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        // Mark upgrade complete
        upgrade_plugin_savepoint(true, 2025042204, 'local', 'computerservice');
    }

    // --- NEW UPGRADE:
    if ($oldversion < 2025042206) {

        // (1) locate the initial workflow status for Computer‑service.
        $initial = $DB->get_record('local_status', [
            'type_id' => 4,
            'seq'     => 1,
        ], '*', MUST_EXIST);

        // (2) update rows that do not yet reference the workflow engine.
        $DB->set_field_select(
            'local_computerservice_requests',
            'status_id',
            $initial->id,
            'status_id = 0 OR status_id IS NULL'
        );

        upgrade_plugin_savepoint(true, 2025042206, 'local', 'computerservice');
    }

    // -------------------------------------------------------------------------
    // 2025042404 – Add bilingual device labels
    // -------------------------------------------------------------------------
    //
    //  1. Add new CHAR(100) columns:
    //       • devicename_en
    //       • devicename_ar
    //  2. Copy existing values from legacy 'devicename'
    //     so nothing shows up blank after upgrade.
    //  3. *Keep* the old column for now – we'll drop it once the UI is patched.
    //
    // NOTE: We cannot drop it today because live code still reads it.
    //
    if ($oldversion < 2025042404) {
        $table = new xmldb_table('local_computerservice_devices');

        // 1) Add devicename_en (after the legacy devicename column for clarity).
        $field = new xmldb_field('devicename_en',
            XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'devicename');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 2) Add devicename_ar.
        $field = new xmldb_field('devicename_ar',
            XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null, 'devicename_en');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 3) Back-fill: copy *current* single-language name into both columns.
        //    (Skip if table is empty.)
        if ($DB->count_records('local_computerservice_devices') > 0) {
            $DB->execute("
                UPDATE {local_computerservice_devices}
                   SET devicename_en = devicename,
                       devicename_ar = devicename
            ");
        }

        // 4) Save-point – DO NOT drop legacy column yet.
        upgrade_plugin_savepoint(true, 2025042404, 'local', 'computerservice');
    }

    // ──────────────────────────────────────────────────────────────────────
    // 2025042405  —  Drop legacy column & back-fill just in case
    // ──────────────────────────────────────────────────────────────────────
    //
    //  Some sites may have added devices after 2404 but *before* applying
    //  the UI patch.  In those rows devicename_en/devicename_ar will be
    //  empty – so we copy once more before removing the old column.
    //
    if ($oldversion < 2025042405) {
        $table = new xmldb_table('local_computerservice_devices');

        // 1) Back-fill only the rows that are still blank.
        $DB->execute("
            UPDATE {local_computerservice_devices}
               SET devicename_en = COALESCE(NULLIF(devicename_en, ''), devicename),
                   devicename_ar = COALESCE(NULLIF(devicename_ar, ''), devicename)
             WHERE (devicename_en = '' OR devicename_ar = '')
        ");

        // 2) Drop the deprecated column.
        $legacyfield = new xmldb_field('devicename');
        if ($dbman->field_exists($table, $legacyfield)) {
            $dbman->drop_field($table, $legacyfield);
        }

        // 3) Save-point.
        upgrade_plugin_savepoint(true, 2025042405, 'local', 'computerservice');
    }

    // ──────────────────────────────────────────────────────────────────────
    // 2025061901  —  Add enhanced workflow fields for rejection/approval notes
    // ──────────────────────────────────────────────────────────────────────
    //
    //  Add rejection_note and approval_note fields to support enhanced workflow
    //  with proper note tracking for transparency and audit purposes.
    //
    if ($oldversion < 2025061901) {
        $table = new xmldb_table('local_computerservice_requests');

        // 1) Add rejection_note field
       // $field = new xmldb_field('rejection_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'unapprove_note');
       $field = new xmldb_field('rejection_note', XMLDB_TYPE_TEXT);
 
       if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 2) Add approval_note field
        $field = new xmldb_field('approval_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'rejection_note');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 3) Migrate existing unapprove_note data to rejection_note
        /* $DB->execute("
            UPDATE {local_computerservice_requests}
               SET rejection_note = unapprove_note
             WHERE unapprove_note IS NOT NULL 
               AND unapprove_note != ''
        ");
 */
        // 4) Save-point.
        upgrade_plugin_savepoint(true, 2025061901, 'local', 'computerservice');
    }

    // ──────────────────────────────────────────────────────────────────────
    // 2025061902  —  Cleanup: Drop unused unapprove_note field
    // ──────────────────────────────────────────────────────────────────────
    //
    //  Remove the legacy unapprove_note field since we now use rejection_note
    //  and approval_note for better workflow transparency.
    //
    if ($oldversion < 2025061902) {
        $table = new xmldb_table('local_computerservice_requests');

        // Drop the legacy unapprove_note field
        $field = new xmldb_field('unapprove_note');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Also drop the legacy devicename field from devices table
        $devicestable = new xmldb_table('local_computerservice_devices');
        $devicefield = new xmldb_field('devicename');
        
        // Only drop if the bilingual fields exist (safety check)
        $enfield = new xmldb_field('devicename_en');
        $arfield = new xmldb_field('devicename_ar');
        
        if ($dbman->field_exists($devicestable, $devicefield) && 
            $dbman->field_exists($devicestable, $enfield) && 
            $dbman->field_exists($devicestable, $arfield)) {
            $dbman->drop_field($devicestable, $devicefield);
        }

        // Save-point.
        upgrade_plugin_savepoint(true, 2025061902, 'local', 'computerservice');
    }

    // ──────────────────────────────────────────────────────────────────────
    // 2025061903  —  Add deviceid field and FK to devices table
    // ──────────────────────────────────────────────────────────────────────
    if ($oldversion < 2025061903) {
        $table = new xmldb_table('local_computerservice_requests');

        // 1) Add deviceid field if not exists
        $field = new xmldb_field('deviceid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'courseid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // 2) Add FK to local_computerservice_devices.id
        $key = new xmldb_key('deviceid_fk', XMLDB_KEY_FOREIGN, ['deviceid'], 'local_computerservice_devices', ['id']);
        $dbman->add_key($table, $key);

        // 3) Save-point
        upgrade_plugin_savepoint(true, 2025061903, 'local', 'computerservice');
    }

    // ──────────────────────────────────────────────────────────────────────
    // 2025061904  —  Drop legacy devices field (free-text)
    // ──────────────────────────────────────────────────────────────────────
    if ($oldversion < 2025061904) {
        $table = new xmldb_table('local_computerservice_requests');
        $field = new xmldb_field('devices');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2025061904, 'local', 'computerservice');
    }

    return true;
}

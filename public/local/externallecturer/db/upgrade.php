<?php

defined('MOODLE_INTERNAL') || die();

/**
 * Handles upgrading the database for local_externallecturer plugin.
 *
 * @param int $oldversion The version number before the upgrade.
 * @return bool
 */
function xmldb_local_externallecturer_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager(); // Database manager to handle schema changes.

    if ($oldversion < 2024102710) {
        // Rename field 'university' to 'organization' in 'externallecturer' table.
        $table = new xmldb_table('externallecturer');
        $field = new xmldb_field('university', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null, 'specialization');

        // Launch rename field.
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'organization');
        }

        // Externallecturer savepoint reached.
        upgrade_plugin_savepoint(true, 2024102710, 'local', 'externallecturer');
    }

    if ($oldversion < 2025080400) {
        // Add civil_number field to externallecturer table.
        $table = new xmldb_table('externallecturer');
        $field = new xmldb_field('civil_number', XMLDB_TYPE_CHAR, '20', null, null, null, null, 'passport');

        // Conditionally launch add field civil_number.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Externallecturer savepoint reached.
        upgrade_plugin_savepoint(true, 2025080400, 'local', 'externallecturer');
    }

    if ($oldversion < 2025081200) {
        // Add audit fields (timecreated, timemodified, created_by, modified_by) and Oracle provenance to externallecturer table.
        $table = new xmldb_table('externallecturer');
        
        // timecreated
        $timecreated = new xmldb_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'courses_count');
        if (!$dbman->field_exists($table, $timecreated)) {
            $dbman->add_field($table, $timecreated);
        }

        // timemodified
        $timemodified = new xmldb_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timecreated');
        if (!$dbman->field_exists($table, $timemodified)) {
            $dbman->add_field($table, $timemodified);
        }

        // created_by
        $createdby = new xmldb_field('created_by', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'timemodified');
        if (!$dbman->field_exists($table, $createdby)) {
            $dbman->add_field($table, $createdby);
        }

        // modified_by
        $modifiedby = new xmldb_field('modified_by', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'created_by');
        if (!$dbman->field_exists($table, $modifiedby)) {
            $dbman->add_field($table, $modifiedby);
        }

        // Cleanup legacy fields if present
        $legacy = new xmldb_field('created_datetime');
        if ($dbman->field_exists($table, $legacy)) {
            $dbman->drop_field($table, $legacy);
        }
        $legacy = new xmldb_field('modified_datetime');
        if ($dbman->field_exists($table, $legacy)) {
            $dbman->drop_field($table, $legacy);
        }

        // Backfill values
        $now = time();
        $DB->execute("UPDATE {externallecturer} SET timecreated = :now WHERE timecreated IS NULL OR timecreated = 0", ['now' => $now]);
        $DB->execute("UPDATE {externallecturer} SET timemodified = CASE WHEN timemodified IS NULL OR timemodified = 0 THEN timecreated ELSE timemodified END");
        $DB->execute("UPDATE {externallecturer} SET created_by = CASE WHEN created_by IS NULL OR created_by = 0 THEN 1 ELSE created_by END");
        $DB->execute("UPDATE {externallecturer} SET modified_by = CASE WHEN modified_by IS NULL OR modified_by = 0 THEN created_by ELSE modified_by END");

        // Externallecturer savepoint reached.
        upgrade_plugin_savepoint(true, 2025081200, 'local', 'externallecturer');
    }

    if ($oldversion < 2025081400) {
        // Add lecturer_type (NOT NULL with default) and nationality to externallecturer table.
        $table = new xmldb_table('externallecturer');

        // lecturer_type
        $lecturertype = new xmldb_field('lecturer_type', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'external_visitor', 'civil_number');
        if (!$dbman->field_exists($table, $lecturertype)) {
            $dbman->add_field($table, $lecturertype);
        }

        // nationality
        $nationality = new xmldb_field('nationality', XMLDB_TYPE_CHAR, '100', null, null, null, null, 'lecturer_type');
        if (!$dbman->field_exists($table, $nationality)) {
            $dbman->add_field($table, $nationality);
        }

        // Backfill lecturer_type just in case (older DBs may not apply default to existing rows consistently).
        $DB->execute("UPDATE {externallecturer} SET lecturer_type = :default WHERE lecturer_type IS NULL OR lecturer_type = ''", [
            'default' => 'external_visitor'
        ]);

        // Savepoint.
        upgrade_plugin_savepoint(true, 2025081400, 'local', 'externallecturer');
    }

    if ($oldversion < 2025082700) {
        // Drop the externallecturer_courses table as it's no longer needed.
        $table = new xmldb_table('externallecturer_courses');
        
        // Drop the table if it exists.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Externallecturer savepoint reached.
        upgrade_plugin_savepoint(true, 2025082700, 'local', 'externallecturer');
    }

    return true;
}

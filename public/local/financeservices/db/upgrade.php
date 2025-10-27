<?php
// This file is part of Moodle - https://moodle.org/
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License.

defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade script for Finance Services plugin.
 */
function xmldb_local_financeservices_upgrade($oldversion)
{
    global $DB;
    $dbman = $DB->get_manager();

    // ─────────────────────────────────────────────────────────────
    // 2024111208 - Add rejection_note field
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2024111208) {
        $table = new xmldb_table('local_financeservices');
        $field = new xmldb_field('rejection_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'cause');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2024111208, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025042700 - Add status_id field
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025042700) {
        $table = new xmldb_table('local_financeservices');
        $field = new xmldb_field('status_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'rejection_note');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key('status_fk', XMLDB_KEY_FOREIGN, ['status_id'], 'local_status', ['id']);
        $dbman->add_key($table, $key);

        upgrade_plugin_savepoint(true, 2025042700, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025042702 - Drop legacy request_status field
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025042702) {
        $table = new xmldb_table('local_financeservices');
        $field = new xmldb_field('request_status');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025042702, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025042703 - Add clause_id + Create local_financeservices_clause table
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025042703) {
        $table = new xmldb_table('local_financeservices');
        $field = new xmldb_field('clause_id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'status_id');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key('clause_fk', XMLDB_KEY_FOREIGN, ['clause_id'], 'local_financeservices_clause', ['id']);
        $dbman->add_key($table, $key);

        $clausetable = new xmldb_table('local_financeservices_clause');

        if (!$dbman->table_exists($clausetable)) {
            $clausetable->add_field('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $clausetable->add_field('clause_name_en', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $clausetable->add_field('clause_name_ar', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);

            $clausetable->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $dbman->create_table($clausetable);
        }

        upgrade_plugin_savepoint(true, 2025042703, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025042801 - Add funding_type_en and funding_type_ar fields
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025042801) {
        $table = new xmldb_table('local_financeservices_funding_type');

        // Add funding_type_en
        $field = new xmldb_field('funding_type_en', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '', 'funding_type');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Add funding_type_ar
        $field = new xmldb_field('funding_type_ar', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '', 'funding_type_en');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025042801, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025042802 - Drop funding_type field from local_financeservices_funding_type
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025042802) {
        $table = new xmldb_table('local_financeservices_funding_type');
        $field = new xmldb_field('funding_type');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025042802, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025051200 - Add deleted field to funding_type and clause tables
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025051200) {
        // Add deleted field to funding_type table
        $table = new xmldb_table('local_financeservices_funding_type');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'funding_type_ar');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Add deleted field to clause table
        $table = new xmldb_table('local_financeservices_clause');
        $field = new xmldb_field('deleted', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'clause_name_ar');
        
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        upgrade_plugin_savepoint(true, 2025051200, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025051400 - Add amount field to local_financeservices_clause
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025051400) {
        $table = new xmldb_table('local_financeservices_clause');
        $field = new xmldb_field('amount', XMLDB_TYPE_NUMBER, '10,2', null, XMLDB_NOTNULL, null, '0.00', 'clause_name_ar');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025051400, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025060101 - Add audit fields and actual_amount to local_financeservices_clause
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025060101) {
        $table = new xmldb_table('local_financeservices_clause');
        
        // Add created_by field (nullable first)
        $field = new xmldb_field('created_by', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'deleted');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Add created_date field (nullable first)
        $field = new xmldb_field('created_date', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'created_by');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Add modified_by field (nullable)
        $field = new xmldb_field('modified_by', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'created_date');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Add modified_date field (nullable)
        $field = new xmldb_field('modified_date', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, null, null, null, 'modified_by');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Add actual_amount field (nullable first)
        $field = new xmldb_field('actual_amount', XMLDB_TYPE_NUMBER, '10,2', null, null, null, null, 'modified_date');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Set default values for existing records
        global $USER;
        $currenttime = time();
        $userid = isset($USER->id) ? $USER->id : 1; // Fallback to admin if USER not available
        
        // Update existing records with current user and time, and copy amount to actual_amount
        $DB->execute("UPDATE {local_financeservices_clause} 
                      SET created_by = ?, created_date = ?, actual_amount = amount 
                      WHERE created_by IS NULL OR created_by = 0", 
                     [$userid, $currenttime]);
        
        // Now make created_by and created_date NOT NULL since they have values
        $field = new xmldb_field('created_by', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'deleted');
        $dbman->change_field_notnull($table, $field);
        
        $field = new xmldb_field('created_date', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null, 'created_by');
        $dbman->change_field_notnull($table, $field);
        
        $field = new xmldb_field('actual_amount', XMLDB_TYPE_NUMBER, '10,2', null, XMLDB_NOTNULL, null, null, 'modified_date');
        $dbman->change_field_notnull($table, $field);
        
        upgrade_plugin_savepoint(true, 2025060101, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025060102 - Rename actual_amount to initial_amount for clarity
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025060102) {
        $table = new xmldb_table('local_financeservices_clause');
        
        // Add initial_amount field as nullable first
        $field = new xmldb_field('initial_amount', XMLDB_TYPE_NUMBER, '10,2', null, null, null, null, 'modified_date');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        
        // Copy data from actual_amount to initial_amount
        $DB->execute("UPDATE {local_financeservices_clause} SET initial_amount = actual_amount");
        
        // Now make initial_amount NOT NULL since it has values
        $field = new xmldb_field('initial_amount', XMLDB_TYPE_NUMBER, '10,2', null, XMLDB_NOTNULL, null, null, 'modified_date');
        $dbman->change_field_notnull($table, $field);
        
        // Drop the old actual_amount field
        $field = new xmldb_field('actual_amount');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }
        
        upgrade_plugin_savepoint(true, 2025060102, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025061801 - Insert default funding types and clauses if not present
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025061801) {
        // Insert default funding types if not present
        $funding_types = [
            ['funding_type_en' => 'Bonus',      'funding_type_ar' => 'علاوة', 'deleted' => 0],
            ['funding_type_en' => 'Housing',    'funding_type_ar' => 'سكن',   'deleted' => 0],
            ['funding_type_en' => 'Transportation', 'funding_type_ar' => 'نقل', 'deleted' => 0],
            ['funding_type_en' => 'Meals',      'funding_type_ar' => 'تغذية', 'deleted' => 0],
            ['funding_type_en' => 'Participant', 'funding_type_ar' => 'محاضر/لاعب دور', 'deleted' => 0],
        ];
        foreach ($funding_types as $ft) {
            $exists = $DB->record_exists('local_financeservices_funding_type', [
                'funding_type_en' => $ft['funding_type_en'],
                'funding_type_ar' => $ft['funding_type_ar']
            ]);
            if (!$exists) {
                $DB->insert_record('local_financeservices_funding_type', (object)$ft);
            }
        }

        // Insert default clauses if not present
        $now = time();
        $defaultuserid = 1; // Use admin or system user
        $clauses = [
            [
                'clause_name_en' => 'Clause 802',
                'clause_name_ar' => 'بند 802',
                'amount' => 500.00,
                'deleted' => 0,
                'created_by' => $defaultuserid,
                'created_date' => $now,
                'initial_amount' => 500.00
            ],
            [
                'clause_name_en' => 'Clause 811',
                'clause_name_ar' => 'بند 811',
                'amount' => 1000.00,
                'deleted' => 0,
                'created_by' => $defaultuserid,
                'created_date' => $now,
                'initial_amount' => 1000.00
            ],
        ];
        foreach ($clauses as $clause) {
            $exists = $DB->record_exists('local_financeservices_clause', [
                'clause_name_en' => $clause['clause_name_en'],
                'clause_name_ar' => $clause['clause_name_ar']
            ]);
            if (!$exists) {
                $DB->insert_record('local_financeservices_clause', (object)$clause);
            }
        }

        upgrade_plugin_savepoint(true, 2025061801, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025061802 - Add approval_note field to local_financeservices
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025061802) {
        $table = new xmldb_table('local_financeservices');
        $field = new xmldb_field('approval_note', XMLDB_TYPE_TEXT, null, null, null, null, null, 'rejection_note');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_plugin_savepoint(true, 2025061802, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025081400 - Add clause_year to local_financeservices_clause and populate
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025081400) {
        $table = new xmldb_table('local_financeservices_clause');
        $field = new xmldb_field('clause_year', XMLDB_TYPE_INTEGER, '4', XMLDB_UNSIGNED, null, null, null, 'created_date');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Populate clause_year based on created_date, fallback to current year
        $currentyear = (int)date('Y');
        $records = $DB->get_records('local_financeservices_clause', null, '', 'id, created_date, clause_year');
        foreach ($records as $rec) {
            $year = $currentyear;
            if (!empty($rec->created_date)) {
                $year = (int)date('Y', (int)$rec->created_date);
            }
            if (empty($rec->clause_year)) {
                $DB->set_field('local_financeservices_clause', 'clause_year', $year, ['id' => $rec->id]);
            }
        }

        // Add unique indexes for (name_en, year) and (name_ar, year)
        $indexen = new xmldb_index('uniq_clause_en_year', XMLDB_INDEX_UNIQUE, ['clause_name_en', 'clause_year']);
        if (!$dbman->index_exists($table, $indexen)) {
            $dbman->add_index($table, $indexen);
        }
        $indexar = new xmldb_index('uniq_clause_ar_year', XMLDB_INDEX_UNIQUE, ['clause_name_ar', 'clause_year']);
        if (!$dbman->index_exists($table, $indexar)) {
            $dbman->add_index($table, $indexar);
        }

        upgrade_plugin_savepoint(true, 2025081400, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025082400 - Fix clause name uniqueness bug (trim whitespace)
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025082400) {
        // Clean up any existing duplicate entries with whitespace differences
        // First, trim all existing clause names to prevent future duplicates
        $records = $DB->get_records('local_financeservices_clause', null, '', 'id, clause_name_en, clause_name_ar');
        foreach ($records as $record) {
            $trimmed_en = trim($record->clause_name_en);
            $trimmed_ar = trim($record->clause_name_ar);
            
            // Only update if trimming actually changed the value
            if ($trimmed_en !== $record->clause_name_en || $trimmed_ar !== $record->clause_name_ar) {
                $DB->set_field('local_financeservices_clause', 'clause_name_en', $trimmed_en, ['id' => $record->id]);
                $DB->set_field('local_financeservices_clause', 'clause_name_ar', $trimmed_ar, ['id' => $record->id]);
            }
        }

        // Remove any duplicate entries that might have been created before this fix
        // Keep the oldest record and mark others as deleted
        $sql = "SELECT clause_year, LOWER(TRIM(clause_name_en)) as trimmed_en, 
                       LOWER(TRIM(clause_name_ar)) as trimmed_ar, 
                       COUNT(*) as count, MIN(id) as keep_id
                FROM {local_financeservices_clause} 
                WHERE deleted = 0 
                GROUP BY clause_year, LOWER(TRIM(clause_name_en)), LOWER(TRIM(clause_name_ar))
                HAVING COUNT(*) > 1";
        
        $duplicates = $DB->get_records_sql($sql);
        foreach ($duplicates as $duplicate) {
            // Mark all but the oldest record as deleted
            $DB->execute("UPDATE {local_financeservices_clause} 
                         SET deleted = 1 
                         WHERE clause_year = ? 
                         AND LOWER(TRIM(clause_name_en)) = ? 
                         AND LOWER(TRIM(clause_name_ar)) = ? 
                         AND id > ?", 
                         [$duplicate->clause_year, $duplicate->trimmed_en, $duplicate->trimmed_ar, $duplicate->keep_id]);
        }

        upgrade_plugin_savepoint(true, 2025082400, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025082500 - Insert "Other" clause as fixed value
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025082500) {
        $now = time();
        $defaultuserid = 1; // Use admin or system user
        $currentyear = (int)date('Y');
        
        // Check if "Other" clause already exists for current year
        $exists = $DB->record_exists('local_financeservices_clause', [
            'clause_name_en' => 'Other',
            'clause_name_ar' => 'مصروفات أخرى',
            'clause_year' => $currentyear,
            'deleted' => 0
        ]);
        
        if (!$exists) {
            $other_clause = [
                'clause_name_en' => 'Other',
                'clause_name_ar' => 'مصروفات أخرى',
                'amount' => 1000.00,
                'deleted' => 0,
                'created_by' => $defaultuserid,
                'created_date' => $now,
                'clause_year' => $currentyear,
                'initial_amount' => 1000.00
            ];
            
            $DB->insert_record('local_financeservices_clause', (object)$other_clause);
        }

        upgrade_plugin_savepoint(true, 2025082500, 'local', 'financeservices');
    }

    // ─────────────────────────────────────────────────────────────
    // 2025082501 - Add "Participant" funding type
    // ─────────────────────────────────────────────────────────────
    if ($oldversion < 2025092300) {
        // Check if "Participant" funding type already exists
        $exists = $DB->record_exists('local_financeservices_funding_type', [
            'funding_type_en' => 'Participant',
            'funding_type_ar' => 'محاضر/لاعب دور',
            'deleted' => 0
        ]);
        
        if (!$exists) {
            $participant_funding_type = [
                'funding_type_en' => 'Participant',
                'funding_type_ar' => 'محاضر/لاعب دور',
                'deleted' => 0
            ];
            
            $DB->insert_record('local_financeservices_funding_type', (object)$participant_funding_type);
        }

        upgrade_plugin_savepoint(true, 2025092300, 'local', 'financeservices');
    }

    return true;
}

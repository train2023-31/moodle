<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * AJAX script to save selected beneficiaries for a course
 *
 * @package    local_annualplans
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/ddllib.php');

// Ensure the user is logged in
require_login();

// Check permissions
$context = context_system::instance();
require_capability('local/annualplans:manage', $context);

try {
    // Get parameters
    $courseid = required_param('courseid', PARAM_RAW);
    $coursedate = required_param('coursedate', PARAM_INT);
    $annualplanid = required_param('annualplanid', PARAM_INT);
    $beneficiaries = optional_param('beneficiaries', '', PARAM_RAW);
    $roles = optional_param('roles', '', PARAM_RAW);

    // Log received data for debugging
    error_log('Save beneficiaries - Course ID: ' . $courseid . ', Date: ' . $coursedate . ', Plan ID: ' . $annualplanid);
    error_log('Beneficiaries JSON: ' . $beneficiaries);
    error_log('Roles JSON: ' . $roles);

    // Decode the JSON array of selected beneficiaries
    $selected_beneficiaries = json_decode($beneficiaries, true);
    $selected_roles = json_decode($roles, true);

    if (!is_array($selected_beneficiaries)) {
        $selected_beneficiaries = [];
    }
    
    if (!is_array($selected_roles)) {
        $selected_roles = [];
    }

    error_log('Decoded beneficiaries count: ' . count($selected_beneficiaries));
    error_log('Decoded roles count: ' . count($selected_roles));

    global $DB, $USER;

    // Check if table exists
    $dbman = $DB->get_manager();
    $table = new xmldb_table('local_annual_plan_beneficiaries');
    if (!$dbman->table_exists($table)) {
        throw new Exception('Beneficiaries table does not exist. Please run database upgrade.');
    }
    
    // Check if roleid column exists
    $roleid_field = new xmldb_field('roleid');
    $has_roleid_column = $dbman->field_exists($table, $roleid_field);

    // First, delete existing beneficiaries for this course
    $deleted = $DB->delete_records('local_annual_plan_beneficiaries', [
        'courseid' => $courseid,
        'coursedate' => $coursedate,
        'annualplanid' => $annualplanid
    ]);
    
    error_log('Deleted existing beneficiaries: ' . ($deleted ? 'success' : 'none found'));

    // Insert new beneficiaries
    $success = true;
    $inserted_count = 0;
    $errors = [];
    
    if (empty($selected_beneficiaries)) {
        error_log('No beneficiaries to insert - selected_beneficiaries is empty');
        $errors[] = 'No beneficiaries data received';
    } else {
        error_log('Processing ' . count($selected_beneficiaries) . ' beneficiaries');
        
        foreach ($selected_beneficiaries as $pf_number => $fullname) {
            error_log('Processing beneficiary - PF: ' . $pf_number . ', Name: ' . $fullname);
            
            // Validate data
            if (empty($pf_number)) {
                $error = 'Empty PF number for: ' . $fullname;
                error_log($error);
                $errors[] = $error;
                continue;
            }
            
            if (empty($fullname)) {
                $error = 'Empty fullname for PF: ' . $pf_number;
                error_log($error);
                $errors[] = $error;
                continue;
            }
            
            // Check length constraints
            if (strlen($pf_number) > 50) {
                $error = 'PF number too long: ' . $pf_number . ' (' . strlen($pf_number) . ' chars)';
                error_log($error);
                $errors[] = $error;
                continue;
            }
            
            if (strlen($fullname) > 255) {
                $error = 'Fullname too long: ' . substr($fullname, 0, 50) . '... (' . strlen($fullname) . ' chars)';
                error_log($error);
                $errors[] = $error;
                continue;
            }
            
            $record = new stdClass();
            $record->courseid = $courseid;
            $record->coursedate = $coursedate;
            $record->annualplanid = $annualplanid;
            $record->pf_number = $pf_number;
            $record->fullname = $fullname;
            $now = time();
            $record->timecreated = $now;
            // Audit fields
            $record->created_by = $USER->id;
            $record->timemodified = $now;
            $record->modified_by = $USER->id;
            
            // Add role if roleid column exists and role is provided
            if ($has_roleid_column) {
                $roleid = null;
                if (isset($selected_roles[$pf_number]) && is_numeric($selected_roles[$pf_number])) {
                    $roleid = intval($selected_roles[$pf_number]);
                    
                    // Validate that the role exists
                    $role_exists = $DB->get_record('role', ['id' => $roleid], 'id');
                    if ($role_exists) {
                        $record->roleid = $roleid;
                        error_log('Adding role ID ' . $roleid . ' for: ' . $pf_number);
                    } else {
                        error_log('Invalid role ID ' . $roleid . ' for: ' . $pf_number);
                    }
                }
            }
            
            error_log('Attempting to insert record: ' . json_encode($record));
            
            try {
                $result = $DB->insert_record('local_annual_plan_beneficiaries', $record);
                if ($result) {
                    $inserted_count++;
                    error_log('Successfully inserted beneficiary with ID: ' . $result);
                } else {
                    $error = 'insert_record returned false for: ' . $pf_number . ' - ' . $fullname;
                    error_log($error);
                    $errors[] = $error;
                    $success = false;
                    break;
                }
            } catch (Exception $e) {
                $error = 'Exception inserting ' . $pf_number . ': ' . $e->getMessage();
                error_log($error);
                $errors[] = $error;
                $success = false;
                break;
            }
        }
    }

    error_log('Inserted beneficiaries count: ' . $inserted_count);

    // Update the numberofbeneficiaries in the course table
    if ($success) {
        $count = count($selected_beneficiaries);
        $updated = $DB->set_field('local_annual_plan_course', 'numberofbeneficiaries', $count, [
            'courseid' => $courseid,
            'coursedate' => $coursedate,
            'annualplanid' => $annualplanid
        ]);
        
        error_log('Updated course beneficiaries count: ' . ($updated ? 'success' : 'failed'));
    }

    // Return response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'count' => $success ? count($selected_beneficiaries) : 0,
        'message' => $success ? get_string('beneficiariessavedsuccess', 'local_annualplans') : get_string('beneficiariessavefailed', 'local_annualplans'),
        'inserted' => $inserted_count,
        'errors' => $errors,
        'debug' => [
            'courseid' => $courseid,
            'coursedate' => $coursedate,
            'annualplanid' => $annualplanid,
            'beneficiaries_received' => count($selected_beneficiaries),
            'beneficiaries_data' => $selected_beneficiaries
        ]
    ]);

} catch (Exception $e) {
    error_log('Exception in save_beneficiaries.php: ' . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'count' => 0,
        'message' => get_string('error', 'local_annualplans') . ': ' . $e->getMessage(),
        'error' => $e->getMessage(),
        'errors' => ['Exception: ' . $e->getMessage()],
        'inserted' => 0
    ]);
} 
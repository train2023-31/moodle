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
 * AJAX script to get existing beneficiaries for a course
 *
 * @package    local_annualplans
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

// Ensure the user is logged in
require_login();

// Check permissions
$context = context_system::instance();
require_capability('local/annualplans:manage', $context);

// Get parameters
$courseid = required_param('courseid', PARAM_RAW);
$coursedate = required_param('coursedate', PARAM_INT);
$annualplanid = required_param('annualplanid', PARAM_INT);

global $DB;

// Check if roleid column exists
$dbman = $DB->get_manager();
$table = new xmldb_table('local_annual_plan_beneficiaries');
$roleid_field = new xmldb_field('roleid');
$has_roleid_column = $dbman->field_exists($table, $roleid_field);

// Get existing beneficiaries for this course
$beneficiaries = $DB->get_records('local_annual_plan_beneficiaries', [
    'courseid' => $courseid,
    'coursedate' => $coursedate,
    'annualplanid' => $annualplanid
]);

$result = [];
$roles = [];
foreach ($beneficiaries as $beneficiary) {
    $result[$beneficiary->pf_number] = $beneficiary->fullname;
    if ($has_roleid_column && isset($beneficiary->roleid) && $beneficiary->roleid) {
        $roles[$beneficiary->pf_number] = intval($beneficiary->roleid);
    }
}

// Return response with both employees and roles if roleid column exists
if ($has_roleid_column) {
    $response = [
        'employees' => $result,
        'roles' => $roles
    ];
    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    // Old format for backward compatibility
    header('Content-Type: application/json');
    echo json_encode($result);
} 
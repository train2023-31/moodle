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
 * AJAX script to check for existing trainee and courseofficer roles
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

global $DB;

$result = [];

// Check for trainee role
$trainee_role = $DB->get_record('role', ['shortname' => 'trainee']);
if ($trainee_role) {
    $result[] = [
        'id' => intval($trainee_role->id),
        'name' => 'متدرب',
        // 'name' => get_string('trainee', 'local_annualplans'),
        'shortname' => 'trainee'
    ];
}

// Check for courseofficer role  
$courseofficer_role = $DB->get_record('role', ['shortname' => 'courseofficer']);
if ($courseofficer_role) {
    $result[] = [
        'id' => intval($courseofficer_role->id),
        'name' => 'ضابط الدورة',
        // 'name' => get_string('courseofficer', 'local_annualplans'),
        'shortname' => 'courseofficer'
    ];
}

// Return response
header('Content-Type: application/json');
echo json_encode($result); 
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
 * AJAX script to get employee data from Oracle database
 * NOW USING CENTRALIZED ORACLE FETCH
 *
 * @package    local_participant
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');

// Ensure the user is logged in
require_login();

// Set up the context for AJAX calls
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/participant/ajax/get_employee_data.php'));

// Get employee data using centralized Oracle manager
$employee_data = oracle_manager::get_all_employees();

// For debugging, add some test data if Oracle connection fails
if (empty($employee_data)) {
    $employee_data = [
        [
            'pf_number' => 'TEST001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'civil_number' => '12345678',
            'fullname' => 'John Doe',
            'display_text' => 'John Doe (PF: TEST001)'
        ],
        [
            'pf_number' => 'TEST002',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'civil_number' => '87654321',
            'fullname' => 'Jane Smith',
            'display_text' => 'Jane Smith (PF: TEST002)'
        ]
    ];
}

header('Content-Type: application/json');
echo json_encode($employee_data); 
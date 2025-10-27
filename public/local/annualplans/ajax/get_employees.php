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
 * AJAX script to get employees from Oracle database
 * NOW USING CENTRALIZED ORACLE FETCH
 *
 * @package    local_annualplans
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');

// Ensure the user is logged in
require_login();

// Check permissions
$context = context_system::instance();
require_capability('local/annualplans:manage', $context);

// Get employees and persons using centralized Oracle manager
$employees = oracle_manager::get_all_employees_and_persons();

header('Content-Type: application/json');
echo json_encode($employees); 
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
 * AJAX script to get personal data from Oracle database
 *
 * @package    local_externallecturer
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');

// Ensure the user is logged in
require_login();

// For externallecturer functionality, just requiring login is sufficient
// since we're reading from external Oracle database for lecturer management

require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');

// Get personal data using centralized Oracle manager
$personal_data = oracle_manager::get_all_persons();
header('Content-Type: application/json');
echo json_encode($personal_data); 
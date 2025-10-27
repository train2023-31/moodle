<?php
/**
 * AJAX script to get employee data from Oracle database
 * 
 * @package    local_oracleFetch
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../classes/oracle_manager.php');

// Ensure the user is logged in
require_login();

// Get employee data and return as JSON
$employee_data = oracle_manager::get_all_employees();

header('Content-Type: application/json');
echo json_encode($employee_data);
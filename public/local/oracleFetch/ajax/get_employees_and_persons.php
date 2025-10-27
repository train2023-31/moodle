<?php
/**
 * AJAX script to get employees and persons combined from Oracle database
 * 
 * @package    local_oracleFetch
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../classes/oracle_manager.php');

// Ensure the user is logged in
require_login();

// Get employees and persons data and return as JSON
$data = oracle_manager::get_all_employees_and_persons();

header('Content-Type: application/json');
echo json_encode($data);
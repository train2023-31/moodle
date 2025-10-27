<?php
/**
 * AJAX script to get personal data from Oracle database
 * 
 * @package    local_oracleFetch
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../config.php');
require_once(__DIR__ . '/../classes/oracle_manager.php');

// Ensure the user is logged in
require_login();

// Get personal data and return as JSON
$personal_data = oracle_manager::get_all_persons();

header('Content-Type: application/json');
echo json_encode($personal_data);
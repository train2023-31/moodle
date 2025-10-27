<?php
// local/computerservice/version.php
defined('MOODLE_INTERNAL') || die();

/**
 * Version metadata for the Computer-Service plugin.
 *
 * ▸ 2025-06-19  v1.3.1  (Build 2025061902)
 *     • Cleanup: Removed deprecated status_manager.php
 *     • Database: Dropped unused unapprove_note field
 *     • Fixed: Database column name issues (display_name_en/ar)
 *     • Enhanced workflow system with AJAX-based approval/rejection
 *     • Added rejection_note and approval_note fields for transparency
 *     • Color-coded status display with rejection note visibility
 *     • Session key validation and race condition prevention
 *     • Replaced status_manager with simple_workflow_manager
 *     • Improved error handling and user feedback
 */

$plugin->component = 'local_computerservice';
$plugin->version   = 2025072000;             // YYYYMMDDRR
$plugin->requires  = 2022041900;             // Moodle 4.0+
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '1.3.1 (Build: 2025072000) - Please Reinstall this plugin';


/*
 * Optional dependencies – uncomment when you want to enforce them.
 *
$plugin->dependencies = [
    // Workflow engine must exist before this plugin installs.
    // 'local_status' => 2025042201,
];
*/

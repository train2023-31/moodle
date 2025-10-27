<?php
// ============================================================================
//  local_roombooking – Version definition
//  --------------------------------------------------------------------------
//  • Bumped to 2025051300 to trigger Moodle's upgrade process.
//  • Release “2.0 (approval workflow)” introduces integration with the
//    generic local_status engine (workflow type_id = 8).
// ============================================================================

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_roombooking';
$plugin->version   = 2025061600;   // YYYYMMDDRR
$plugin->requires  = 2022111800;   // Moodle 4.1 minimum
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '2.0 (approval workflow)';

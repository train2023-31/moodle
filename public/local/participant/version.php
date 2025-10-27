<?php
// This file is part of the Participant plugin for Moodle - http://moodle.org/

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'local_participant'; // Full name of the plugin (used for diagnostics).
$plugin->version   = 2025092300;           // The current plugin version (Date: YYYYMMDDXX).
$plugin->requires  = 2022111800;           // Requires this Moodle version (4.1 or later).
$plugin->release   = '2.2 - update js code to work with requestservices plugin';                // Human-readable version name - Added Oracle data fields.
$plugin->maturity  = MATURITY_STABLE;      // This is a stable version.
$plugin->cron      = 0;                    // No cron tasks required for this plugin.

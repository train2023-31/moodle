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
 * Library functions for the financecalc plugin.
 *
 * @package    local_financecalc
 * @copyright  2025 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extends the global navigation tree by adding financecalc nodes if there is a capability.
 *
 * @param global_navigation $navigation An object representing the navigation tree
 */
function local_financecalc_extend_navigation_global($navigation) {
    // Navigation is handled via settings.php instead of this hook
    // to avoid conflicts and ensure proper integration with Moodle's admin interface
}

/**
 * Extends the settings navigation with the financecalc settings.
 *
 * @param settings_navigation $settingsnav The settings navigation object
 * @param navigation_node $financecalcnode The node to add to
 */
function local_financecalc_extend_settings_navigation($settingsnav, $financecalcnode) {
    // Settings navigation is handled via settings.php instead of this hook
    // to avoid conflicts and ensure proper integration with Moodle's admin interface
}
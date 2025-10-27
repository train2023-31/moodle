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

namespace bbbext_complex\bigbluebuttonbn;

use mod_bigbluebuttonbn\local\extension\navigation_override_addon as navigation_override_addon_interface;

/**
 * Example override implementation for the complex test subplugin.
 *
 * @package   mod_bigbluebuttonbn
 * @copyright 2025 Blindside Networks Inc
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author    Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 */
class navigation_override_addon implements navigation_override_addon_interface {
    /**
     * Overrides the settings navigation for BigBlueButtonBN.
     *
     * @param \settings_navigation $settingsnav The settings navigation object.
     * @param \navigation_node $nodenav The current navigation node.
     * @return void
     */
    public function override_settings_navigation(\settings_navigation $settingsnav, \navigation_node $nodenav): void {
        $nodenav->add(
            get_string('settings_navigation_override', 'bbbext_complex'),
            new \moodle_url('#'),
            \navigation_node::TYPE_SETTING,
            null,
            'settings_navigation_override'
        );
    }
}

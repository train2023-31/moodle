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
 * Install script for BigBlueButton B3Dummy for Override View
 *
 * Documentation: {@link https://moodledev.io/docs/guides/upgrade}
 *
 * @package    mod_bigbluebuttonbn
 * @copyright  2025 Blindside Networks Inc
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Jesus Federico  (jesus [at] blindsidenetworks [dt] com)
 */

/**
 * Executed on installation of BigBlueButton B3Dummy for Override View
 *
 * @return bool
 */
function xmldb_bbbext_complex_install() {
    set_config('disabled', 1, 'bbbext_complex');
}

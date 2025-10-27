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
 * Settings for Approved Courses Block
 *
 * @package    block_approved_courses
 * @copyright  2024
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    
    $settings->add(new admin_setting_configtext('block_approved_courses/defaulttitle',
        get_string('configtitle', 'block_approved_courses'),
        get_string('configtitle_desc', 'block_approved_courses'),
        get_string('pluginname', 'block_approved_courses'),
        PARAM_TEXT));

    $settings->add(new admin_setting_configtext('block_approved_courses/maxcourses',
        get_string('configmaxcourses', 'block_approved_courses'),
        get_string('configmaxcourses_desc', 'block_approved_courses'),
        0,
        PARAM_INT));

    $settings->add(new admin_setting_configcheckbox('block_approved_courses/showcodes',
        get_string('configshowcodes', 'block_approved_courses'),
        get_string('configshowcodes_desc', 'block_approved_courses'),
        1));

    $settings->add(new admin_setting_configcheckbox('block_approved_courses/showplans',
        get_string('configshowplans', 'block_approved_courses'),
        get_string('configshowplans_desc', 'block_approved_courses'),
        1));

}

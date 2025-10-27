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
 * Caches.
 *
 * @package   local_openlms
 * @copyright 2022 Open LMS (https://www.openlms.net/)
 * @author    Petr Skoda
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$definitions = array(
    // Hook callbacks cache.
    // There is a static cache in hook manager, data is fetched once per page on first hook execution.
    // This cache needs to be invalidated during upgrades when code changes and when callbacks
    // overrides are updated.
    'hookcallbacks' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => false,
        // WARNING: Manual cache purge may be required when overriding hook callbacks.
        'canuselocalstore' => true,
    ),
);

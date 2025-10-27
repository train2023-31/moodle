<?php
// This file is part of the installation process for the local_computerservice plugin.

defined('MOODLE_INTERNAL') || die();

/**
 * Function to insert default device types into the local_computerservice_devices table.
 */
function xmldb_local_computerservice_install() {
    global $DB;

    // Define default devices with both English and Arabic labels.
    $defaultdevices = [
        [
            'devicename_en' => 'Projector',
            'devicename_ar' => 'عارض ضوئي',
            'status' => 'active',
        ],
        [
            'devicename_en' => 'Laptop',
            'devicename_ar' => 'حاسوب محمول',
            'status' => 'active',
        ],
        [
            'devicename_en' => 'Tablet',
            'devicename_ar' => 'جهاز لوحي',
            'status' => 'active',
        ],
        [
            'devicename_en' => 'Printer',
            'devicename_ar' => 'طابعة',
            'status' => 'active',
        ],
    ];

    // Insert records only if not already present (based on devicename_en).
    foreach ($defaultdevices as $device) {
        if (!$DB->record_exists('local_computerservice_devices', ['devicename_en' => $device['devicename_en']])) {
            $DB->insert_record('local_computerservice_devices', (object)$device);
        }
    }
}

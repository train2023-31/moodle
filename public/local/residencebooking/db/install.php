<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Post installation procedure for the Residence Booking plugin
 * - Creates default residence types in both English and Arabic
 * - Creates default purpose options in both English and Arabic
 * - Ensures the plugin is ready to use immediately after installation
 *
 * @see upgrade_plugins_modules()
 */
function xmldb_local_residencebooking_install() {
    global $DB;

    // Check if tables exist before adding default data
    $dbman = $DB->get_manager();
    if (!$dbman->table_exists('local_residencebooking_types') || 
        !$dbman->table_exists('local_residencebooking_purpose')) {
        return false; // Tables don't exist yet, abort
    }

    // Insert default residence types with separate English and Arabic fields
    $residence_types = [
        (object)[
            'type_name_en' => 'Hotel Room',
            'type_name_ar' => 'غرفة فندقية',
            'deleted' => 0
        ],
        (object)[
            'type_name_en' => 'Training Accommodation',
            'type_name_ar' => 'سكن التدريب',
            'deleted' => 0
        ],
        (object)[
            'type_name_en' => 'Apartment',
            'type_name_ar' => 'شقة',
            'deleted' => 0
        ],
        (object)[
            'type_name_en' => 'Private Accommodation',
            'type_name_ar' => 'سكن خاص',
            'deleted' => 0
        ],
    ];

    // Only insert if no records exist yet (avoid duplicates on reinstall)
    if ($DB->count_records('local_residencebooking_types') == 0) {
        foreach ($residence_types as $type) {
            try {
                $DB->insert_record('local_residencebooking_types', $type);
            } catch (Exception $e) {
                // Log error but continue with other records
                error_log('Error inserting residence type: ' . $e->getMessage());
            }
        }
    }

    // Insert default purposes with separate English and Arabic fields
    $purposes = [
        (object)[
            'description_en' => 'Deliver Lecture',
            'description_ar' => 'تقديم محاضرة',
            'deleted' => 0
        ],
        (object)[
            'description_en' => 'Attend Class',
            'description_ar' => 'حضور درس',
            'deleted' => 0
        ],
        (object)[
            'description_en' => 'Organize Course',
            'description_ar' => 'تنظيم دورة',
            'deleted' => 0
        ],
        (object)[
            'description_en' => 'Other',
            'description_ar' => 'أخرى',
            'deleted' => 0
        ],
    ];

    // Only insert if no records exist yet (avoid duplicates on reinstall)
    if ($DB->count_records('local_residencebooking_purpose') == 0) {
        foreach ($purposes as $purpose) {
            try {
                $DB->insert_record('local_residencebooking_purpose', $purpose);
            } catch (Exception $e) {
                // Log error but continue with other records
                error_log('Error inserting purpose: ' . $e->getMessage());
            }
        }
    }

    return true;
}

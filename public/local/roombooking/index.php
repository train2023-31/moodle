<?php
// File: local/roombooking/index.php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');

require_once('classes/form/booking_form.php');
use local_roombooking\service\booking_service;
require_once('pages/manage_bookings.php');

global $DB, $OUTPUT, $PAGE, $USER;

require_login();

// ────────────────────────────────────────────────────────────
// Setup page context
// ────────────────────────────────────────────────────────────
$context = context_system::instance();
$PAGE->set_url(new moodle_url('/local/roombooking/index.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_roombooking'));
$PAGE->set_heading(get_string('pluginname', 'local_roombooking'));
$PAGE->requires->css('/local/shared_styles.css');
// Check if user has permission to view bookings
require_capability('local/roombooking:viewbookings', $context);

$PAGE->requires->js(new moodle_url('/theme/stream/js/custom_dialog.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/export_csv.js'));

// ─────────────────────────────────────────────────────────────
// Global tab: registeringroom | managebookings | manage
// ─────────────────────────────────────────────────────────────
$maintabs = [
    new tabobject('registeringroom', 
        new moodle_url('/local/roombooking/index.php', ['tab' => 'registeringroom']),
        get_string('registeringroom', 'local_roombooking')),
    new tabobject('managebookings', 
        new moodle_url('/local/roombooking/index.php', ['tab' => 'managebookings']),
        get_string('managebookings', 'local_roombooking')),
    new tabobject('manage', 
        new moodle_url('/local/roombooking/index.php', ['tab' => 'manage']),
        get_string('manage', 'local_roombooking')),
];

$currenttab = optional_param('tab', 'registeringroom', PARAM_ALPHA);

// ─────────────────────────────────────────────────────────────
// Common parameters and data
// ─────────────────────────────────────────────────────────────

// Sorting parameters - default to starttime ascending for logical booking order
$sortby = optional_param('sortby', 'starttime', PARAM_ALPHA);
$direction = optional_param('direction', 'asc', PARAM_ALPHA);

// Filter parameters
$filter_courseid = optional_param('courseid', 0, PARAM_INT);
$filter_roomid = optional_param('roomid', 0, PARAM_INT);

// Define allowed columns to sort by and their corresponding SQL expressions
$sortablecolumns = [
    'id' => 'bc.id',
    'courseid' => 'bc.courseid',
    'roomid' => 'bc.roomid',
    'capacity' => 'bc.capacity',
    'starttime' => 'bc.starttime',
    'endtime' => 'bc.endtime',
    'recurrence' => 'bc.recurrence',
    'userid' => 'bc.userid'
];

// Validate 'sortby' parameter
if (!array_key_exists($sortby, $sortablecolumns)) {
    $sortby = 'id';
}

$sortby_sql = $sortablecolumns[$sortby];

// Validate 'direction' parameter - enforce ascending order for manage bookings tab
if ($direction !== 'asc' && $direction !== 'desc') {
    $direction = 'asc';
}
// Force ascending order for better user experience
$direction = 'asc';

// ─────────────────────────────────────────────────────────────
// Initialize booking form for registeringroom tab
// ─────────────────────────────────────────────────────────────
$booking_form = null;
if ($currenttab === 'registeringroom') {
    // Check if user has permission to create bookings
    if (!has_capability('local/roombooking:managebookings', $context)) {
        echo $OUTPUT->notification(get_string('nocreatepermissions', 'local_roombooking'), 'error');
    } else {
        $booking_form = new \local_roombooking\form\booking_form();
        
        // Handle form submission
        if ($booking_form->is_cancelled()) {
            redirect(new moodle_url('/local/roombooking/index.php', ['tab' => 'registeringroom']));
        } else if ($data = $booking_form->get_data()) {
            // Process the form data
            try {
                // Convert form data to the format expected by booking_service
                $converted_data = [
                    'courseid' => $data->courseid,
                    'roomid' => $data->roomid,
                    'start_date_time' => $data->start_date_time, // Keep as timestamp
                    'end_date_time' => $data->end_date_time, // Keep as timestamp
                    'capacity' => $data->capacity,
                    'recurrence' => $data->recurrence,
                    'recurrence_end_date' => isset($data->recurrence_end_date) ? date('Y-m-d', $data->recurrence_end_date) : '', // Convert to date string for recurrence end
                    'userid' => $data->userid
                ];
                
                $result = booking_service::create_booking($converted_data);
                // Note: booking_service already handles notifications internally
                // Redirect immediately to avoid session mutation issues
                redirect(new moodle_url('/local/roombooking/index.php', ['tab' => 'registeringroom']));
            } catch (\Exception $e) {
                \core\notification::error($e->getMessage());
            }
        }
    }
}

// ─────────────────────────────────────────────────────────────
// Header + tab tree
// ─────────────────────────────────────────────────────────────
echo $OUTPUT->header();
echo $OUTPUT->tabtree($maintabs, $currenttab);

// ─────────────────────────────────────────────────────────────
// Handle each tab's content
// ─────────────────────────────────────────────────────────────
if ($currenttab === 'registeringroom') {
    // Display the booking form only if user has permission and form is initialized
    if ($booking_form) {
        $booking_form->display();
    } else if (!has_capability('local/roombooking:managebookings', $context)) {
        // Already displayed error message above, just show nothing or additional help
        echo html_writer::div(
            get_string('nopermissions', 'local_roombooking'),
            'alert alert-warning'
        );
    }
    
} else if ($currenttab === 'managebookings') {
    
    // Filters are now handled via Mustache filter block (GET params courseid, roomid)

    // Build the base SQL query without the SELECT clause
    $params = [];
    $sql_base = "FROM {local_roombooking_course_bookings} bc
                JOIN {course} c ON bc.courseid = c.id
                JOIN {local_roombooking_rooms} r ON bc.roomid = r.id
                JOIN {user} u ON bc.userid = u.id
                LEFT JOIN {local_status} s ON bc.status_id = s.id
                WHERE 1=1";

    if ($filter_courseid && $filter_courseid > 0) {
        $sql_base .= " AND bc.courseid = :courseid";
        $params['courseid'] = $filter_courseid;
    }

    if ($filter_roomid && $filter_roomid > 0) {
        $sql_base .= " AND bc.roomid = :roomid";
        $params['roomid'] = $filter_roomid;
    }

    // Count total records
    $totalrecords = $DB->count_records_sql("SELECT COUNT(*) {$sql_base}", $params);

    // Build the full SQL query for fetching records
    // Get all required user name fields for fullname() function
    $user_name_fields = \core_user\fields::for_name()->get_sql('u', false, '', '', false)->selects;
    
    $sql = "SELECT bc.*, c.fullname AS course_name, r.name AS room_name, r.capacity AS roomcapacity, 
                {$user_name_fields}, s.display_name_en AS status_name, s.seq AS status_seq
            {$sql_base}
            ORDER BY {$sortby_sql} {$direction}";

    // Set up pagination
    $perpage = 20; // Number of records per page
    $page = optional_param('page', 0, PARAM_INT);
    $offset = $page * $perpage;

    // Modify SQL to include LIMIT and OFFSET
    $sql_paginated = $sql . " LIMIT {$perpage} OFFSET {$offset}";

    // Fetch records with applied filters and sorting
    $records = $DB->get_records_sql($sql_paginated, $params);

    // Initialize pagination
    $baseurl = new moodle_url($PAGE->url, [
        'tab' => $currenttab,
        'sortby' => $sortby,
        'direction' => $direction,
        'courseid' => $filter_courseid,
        'roomid' => $filter_roomid,
    ]);

    $pagination = new paging_bar($totalrecords, $page, $perpage, $baseurl);
    
    // Pass required parameters to the manage_bookings function
    $params = [
        'records' => $records,
        'sortby' => $sortby,
        'direction' => $direction,
        'filter_courseid' => $filter_courseid,
        'filter_roomid' => $filter_roomid,
        'pagination' => $pagination
    ];
    
    // Call the function from the included file
    echo local_roombooking_render_manage_bookings_tab($params);
    
} else if ($currenttab === 'manage') {
    // Flag that this is being included from index.php
    define('INCLUDED_FROM_INDEX', true);
    
    // Load the classroom management dashboard with card interface
    require_once($CFG->dirroot . '/local/roombooking/pages/classroom_management.php');
}

// Display the page footer
echo $OUTPUT->footer();

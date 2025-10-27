<?php
// File: local/roombooking/pages/bookings.php
// Standalone page for viewing/managing bookings

require_once('../../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/tablelib.php');

require_once('../classes/form/booking_form.php');
require_once('manage_bookings.php');

global $DB, $OUTPUT, $PAGE, $USER;

require_login();

$context = context_system::instance();
require_capability('local/roombooking:viewbookings', $context);

$PAGE->set_url(new moodle_url('/local/roombooking/pages/bookings.php'));
$PAGE->set_context($context);
$PAGE->set_title(get_string('managebookings', 'local_roombooking'));
$PAGE->set_heading(get_string('managebookings', 'local_roombooking'));

// Capture sorting parameters from URL - default to starttime ascending for logical booking order
$sortby = optional_param('sortby', 'starttime', PARAM_ALPHA);
$direction = optional_param('direction', 'asc', PARAM_ALPHA);

// Capture filter parameters from URL
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

// Initialize the filter form with custom data
$filter_action_url = new moodle_url('/local/roombooking/pages/bookings.php');
$filterForm = new \local_roombooking\form\filter_form($filter_action_url, [
    'sortby' => $sortby,
    'direction' => $direction
], 'post', '', ['class' => 'custom-filter-form']);

// Handle form submission and filtering logic
if ($filterForm->is_submitted() && $filterForm->is_validated()) {
    $data = $filterForm->get_data();

    // Update filter parameters from form data - treat 0 as "no filter"
    $filter_courseid = !empty($data->courseid) ? $data->courseid : 0;
    $filter_roomid = !empty($data->roomid) ? $data->roomid : 0;
}

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
    'sortby' => $sortby,
    'direction' => $direction,
    'courseid' => $filter_courseid,
    'roomid' => $filter_roomid
]);

$pagination = new paging_bar($totalrecords, $page, $perpage, $baseurl);

// Start outputting the page
echo $OUTPUT->header();

// Pass required parameters to the manage_bookings function
$params = [
    'records' => $records,
    'sortby' => $sortby,
    'direction' => $direction,
    'filter_courseid' => $filter_courseid,
    'filter_roomid' => $filter_roomid,
    'filterForm' => $filterForm,
    'pagination' => $pagination
];

// Call the function from the included file
echo local_roombooking_render_manage_bookings_tab($params);

// Display the page footer
echo $OUTPUT->footer(); 
<?php
// File: local/roombooking/pages/manage_bookings.php
// Handles the managebookings tab functionality

defined('MOODLE_INTERNAL') || die();

/**
 * Renders the managebookings tab content
 * 
 * @param array $params Required parameters
 * @return string HTML content
 */
function local_roombooking_render_manage_bookings_tab($params) {
    global $DB, $OUTPUT, $PAGE;
    
    $output = '';
    
    // Extract parameters
    $records = $params['records'];
    $sortby = $params['sortby'];
    $direction = $params['direction'];
    $filter_courseid = $params['filter_courseid'];
    $filter_roomid = $params['filter_roomid'];
    $filterForm = $params['filterForm'];
    $pagination = $params['pagination'];
    
    // Old Moodle form-based filter removed. Filter UI is rendered via template now.

    // Render the data using the custom renderer
    $renderer = $PAGE->get_renderer('local_roombooking');

    // Remove heading display (Filtered results / All Classroom Bookings)

    // Pass sorting and filter parameters to the renderer
    $output .= $renderer->render_classroom_table($records, $sortby, $direction, [
        'courseid' => $filter_courseid,
        'roomid' => $filter_roomid
    ]);

    // Display pagination by rendering the paging_bar object
    $output .= $OUTPUT->render($pagination);
    
    return $output;
} 
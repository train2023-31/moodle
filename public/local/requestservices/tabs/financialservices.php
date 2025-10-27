<?php
// Path: local/requestservices/tabs/financialservices.php
//require_once(__DIR__ . '/../../../config.php');
// Include the add_form from financeservices
require_once($CFG->dirroot . '/local/financeservices/classes/form/add_form.php');

//use local_financeservices\form\add_form;

// Set up the page
// $context = context_system::instance();
// $course_id = required_param('id', PARAM_INT);
// // Instantiate the form
// $mform = new add_form(null, ['id' => $course_id]);
$actionurl = new moodle_url('/local/requestservices/index.php', array('id' => $courseid, 'tab' => 'financialservices'));
    
// Instantiate the form
$mform = new \local_financeservices\form\add_form($actionurl, array('id' => $courseid));



// Handle form submission
if ($mform->is_cancelled()) {
    // If the form is cancelled, redirect to the financial services tab
    redirect(new moodle_url('/local/requestservices/index.php', ['id' => $courseid, 'tab' => 'financialservices']));
} else if ($data = $mform->get_data()) {
    global $DB, $USER;

    // Process form data
    $record = new stdClass();
    $record->course_id = $data->courseid;
    $record->funding_type_id = $data->funding_type_id;
    $record->price_requested = $data->price_requested;
    $record->notes = $data->notes;
    $record->request_status = 'w'; // Assuming 'w' stands for 'waiting' or similar status
    $record->user_id = $USER->id;
    $record->date_time_requested = time(); // Current timestamp
    $record->status_id = 8; 
    $record->clause_id = $data->clause_id; 

    // Insert the record into the local_financeservices table
    $requestid = $DB->insert_record('local_financeservices', $record);

    // Trigger request created event
    $event = \local_financeservices\event\request_created::create([
        'context' => \context_system::instance(),
        'objectid' => $requestid,
        'other' => [
            'course_id' => $record->course_id,
            'funding_type_id' => $record->funding_type_id,
            'price_requested' => $record->price_requested,
            'notes' => $record->notes,
            'clause_id' => $record->clause_id
        ]
    ]);
    $event->trigger();

    // Redirect to the list view or a confirmation page
   /*  \core\notification::add(
        get_string('requestadded', 'local_participant'),
        \core\notification::SUCCESS
    ); */
            //Add a redirect after successful insertion to avoid double submission
        redirect(
    new moodle_url('/local/requestservices/index.php', ['id' => $courseid, 'tab' => 'financialservices']),
    get_string('requestsubmitted', 'local_financeservices'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);
require_sesskey();  // Add this after form submission check if applicable 
} 
    // Display the form
    $mform->display();





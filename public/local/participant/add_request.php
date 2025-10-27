<?php
require_once('../../config.php');
require_once('classes/form/request_form.php');
require_once('classes/simple_workflow_manager.php');
require_once('../financeservices/classes/simple_workflow_manager.php');

require_login();

/**
 * Note: Moodle user creation and enrollment functions have been removed
 * as the system now stores Oracle data directly instead of creating Moodle users.
 * The pf_number and participant_full_name fields are used to store Oracle data.
 */

$context = context_system::instance();
require_capability('local/participant:addrequest', $context);

$PAGE->set_url('/local/participant/add_request.php');
$PAGE->set_context($context);
$PAGE->set_title(get_string('addrequest', 'local_participant'));
$PAGE->set_heading(get_string('addrequest', 'local_participant'));

// Load JavaScript for employee autocomplete functionality
$PAGE->requires->js(new moodle_url('/local/participant/js/main.js'));

$tabs = [];
$tabs[] = new tabobject('addrequest', new moodle_url('/local/participant/add_request.php'), get_string('addrequest', 'local_participant'));
$tabs[] = new tabobject('viewrequests', new moodle_url('/local/participant/index.php'), get_string('viewrequests', 'local_participant'));


echo $OUTPUT->header();
echo $OUTPUT->tabtree($tabs, 'addrequest');

$mform = new \local_participant\form\request_form();



if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/participant/index.php'));
} else if ($data = $mform->get_data()) {

    try {
        // Validate that the course exists
        $moodle_course = $DB->get_record('course', ['id' => $data->courseid], 'idnumber');
        if (!$moodle_course) {
            throw new moodle_exception('invalidcourseid', 'local_participant');
        }
        
        // Initialize annual plan ID - will be null if no annual plan data exists
        $annual_plan_id = null;
        
        // Try to get annual plan data if course has idnumber - prioritize current year and oldest active plans
        if (!empty($moodle_course->idnumber)) {
            debugging('Looking for annual plan with course ID: ' . $moodle_course->idnumber, DEBUG_DEVELOPER);
            
            // Get current year for proper plan selection
            $current_year = date('Y');
            debugging('Current year: ' . $current_year, DEBUG_DEVELOPER);
            
            // First try to find a plan for the current year (2025)
            $sql = "SELECT apc.annualplanid, ap.year, ap.disabled, ap.title 
                    FROM {local_annual_plan_course} apc
                    JOIN {local_annual_plan} ap ON apc.annualplanid = ap.id
                    WHERE apc.courseid = ? 
                    AND ap.disabled = 0
                    ORDER BY ap.year = ? DESC, ap.year ASC, ap.id ASC";
            
            $plan_course = $DB->get_record_sql($sql, [$moodle_course->idnumber, $current_year]);
            
            if ($plan_course) {
                $annual_plan_id = $plan_course->annualplanid;
                debugging('Found annual plan ID: ' . $annual_plan_id . ' for year: ' . $plan_course->year . ' - ' . $plan_course->title, DEBUG_DEVELOPER);
                
                // Log warning if plan year doesn't match current year
                if ($plan_course->year != $current_year) {
                    debugging('WARNING: Selected plan year (' . $plan_course->year . ') does not match current year (' . $current_year . ')', DEBUG_DEVELOPER);
                }
            } else {
                debugging('No active annual plan found for course ID: ' . $moodle_course->idnumber, DEBUG_DEVELOPER);
            }
        } else {
            debugging('Course has no ID number, cannot assign annual plan', DEBUG_DEVELOPER);
        }
        

        // Get participant type data for automatic compensation calculation
        $participant_type = $DB->get_record('local_participant_request_types', 
            ['id' => $data->participant_type_id], 
            'cost, calculation_type', 
            MUST_EXIST
        );
        
        // Calculate compensation amount based on type and duration
        $compensation_amount = 0;
        if ($participant_type->calculation_type == 'dynamic') {
            // For dynamic types (like external lecturers), use the amount from form
            $compensation_amount = $data->compensation_amount;
        } else {
            // For fixed rates (days/hours), calculate automatically
            $compensation_amount = $data->duration_amount * $participant_type->cost;
        }

        // Handle internal vs external participant logic
        $is_external_lecturer = ($data->participant_type_id == 7);

        $new_request = new stdClass();
        $new_request->course_id = $data->courseid;
        $new_request->annual_plan_id = $annual_plan_id ?: 0; // Use 0 if no annual plan data
        $new_request->participant_type_id = $data->participant_type_id;
        $new_request->duration_amount = $data->duration_amount;
        $new_request->compensation_amount = $compensation_amount;
        $new_request->is_internal_participant = $is_external_lecturer ? 0 : 1;
        $new_request->clause = $data->clause;
        $new_request->funding_type = $data->fundingtype;
        // Handle participant assignment based on type
        if ($is_external_lecturer) {
            // External lecturer - store externallecturer table ID
            $new_request->external_lecturer_id = !empty($data->external_lecturer_id) ? (int)$data->external_lecturer_id : null;
            
            // Note: externallecturer_courses table has been removed from the system
            // External lecturer-course relationships are now tracked through participant requests
            if (!empty($new_request->external_lecturer_id)) {
                error_log('External lecturer ID ' . $new_request->external_lecturer_id . ' assigned to course ' . $new_request->course_id . ' via participant request');
                
                // Update the courses_count in externallecturer table directly
                $lecturer = $DB->get_record('externallecturer', ['id' => $new_request->external_lecturer_id], 'courses_count');
                if ($lecturer) {
                    $new_count = $lecturer->courses_count + 1;
                    $DB->set_field('externallecturer', 'courses_count', $new_count, ['id' => $new_request->external_lecturer_id]);
                    error_log('Updated external lecturer courses count to: ' . $new_count);
                }
            }
            
        } else {
            // Internal participant - store Oracle data directly
            $new_request->external_lecturer_id = null; // No external lecturer for internal participants
            
            // Store the PF number and full name from Oracle data
            $employee_pf_number = !empty($data->pf_number) ? $data->pf_number : null;
            $employee_full_name = !empty($data->participant_full_name) ? $data->participant_full_name : null;
            
            if ($employee_pf_number && $employee_full_name) {
                // Store PF number as string (e.g., "PF12345")
                $new_request->pf_number = $employee_pf_number;
                $new_request->participant_full_name = $employee_full_name;
                
                error_log('Stored Oracle data - PF: ' . $employee_pf_number . ', Name: ' . $employee_full_name);
            } else {
                $new_request->pf_number = null;
                $new_request->participant_full_name = null;
                error_log('No Oracle participant data provided');
            }
        }
        
        // Handle date conversion properly - DATABASE REQUIRES DATETIME FORMAT
        if (isset($data->requested_date)) {
            // If it's already a timestamp, use it directly; if it's a date array from Moodle form, convert it
            if (is_array($data->requested_date)) {
                $timestamp = make_timestamp(
                    $data->requested_date['year'],
                    $data->requested_date['month'], 
                    $data->requested_date['day'],
                    $data->requested_date['hour'] ?? 0,
                    $data->requested_date['minute'] ?? 0
                );
            } else {
                $timestamp = $data->requested_date;
            }
        } else {
            $timestamp = time(); // Default to current time
        }
        
        // CRITICAL: Convert timestamp to MySQL datetime format
        $new_request->requested_date = date('Y-m-d H:i:s', $timestamp);
        
        error_log('Requested date converted: ' . $timestamp . ' -> ' . $new_request->requested_date);
        
        // Use the workflow manager's initial status
        $new_request->request_status_id = \local_participant\simple_workflow_manager::get_initial_status_id();        
        $new_request->is_approved = 0;
        $new_request->rejection_reason = null; // Explicitly null, not empty string
        $new_request->created_by = (int)$USER->id;
        $new_request->time_created = time();
        $new_request->time_modified = time();
        $new_request->modified_by = (int)$USER->id;

        error_log('Final request_status_id: ' . $new_request->request_status_id);

        // Validate required fields before insertion
        $required_fields = [
            'course_id' => $new_request->course_id,
            'annual_plan_id' => $new_request->annual_plan_id,
            'participant_type_id' => $new_request->participant_type_id,
            'clause' => $new_request->clause,
            'funding_type' => $new_request->funding_type,
            'duration_amount' => $new_request->duration_amount,
            'compensation_amount' => $new_request->compensation_amount,
            'is_internal_participant' => $new_request->is_internal_participant,
            'requested_date' => $new_request->requested_date,
            'request_status_id' => $new_request->request_status_id,
            'is_approved' => $new_request->is_approved,
            'created_by' => $new_request->created_by,
            'time_created' => $new_request->time_created,
            'time_modified' => $new_request->time_modified
        ];
        
        $validation_errors = [];
        foreach ($required_fields as $field_name => $field_value) {
            if ($field_value === null || $field_value === '') {
                $validation_errors[] = "Required field '{$field_name}' is null or empty";
                error_log("ERROR: Required field '{$field_name}' is null or empty");
            }
        }
        
        // Stop execution if there are validation errors
        if (!empty($validation_errors)) {
            $error_message = "Validation failed: " . implode(', ', $validation_errors);
            error_log($error_message);
            \core\notification::error($error_message);
            $mform->display(); // Redisplay form
            echo $OUTPUT->footer();
            exit;
        }
   
        // CRITICAL: Clean and fix data types BEFORE validation
        
        // 1. AGGRESSIVE fix for empty values - handle all possible empty cases
        
        // Fix external_lecturer_id
        if (!isset($new_request->external_lecturer_id) || $new_request->external_lecturer_id === '' || $new_request->external_lecturer_id === '0' || trim($new_request->external_lecturer_id) === '') {
            $new_request->external_lecturer_id = null;
            error_log('FIXED external_lecturer_id: empty -> NULL');
        } else {
            $new_request->external_lecturer_id = (int)$new_request->external_lecturer_id;
        }
        
        // Fix pf_number
        if (!isset($new_request->pf_number) || $new_request->pf_number === '' || trim($new_request->pf_number) === '') {
            $new_request->pf_number = null;
            error_log('FIXED pf_number: empty -> NULL');
        }
        
        // Fix participant_full_name
        if (!isset($new_request->participant_full_name) || $new_request->participant_full_name === '' || trim($new_request->participant_full_name) === '') {
            $new_request->participant_full_name = null;
            error_log('FIXED participant_full_name: empty -> NULL');
        }
        
        // internal_user_id removed
        
        // Fix rejection_reason
        if (!isset($new_request->rejection_reason) || $new_request->rejection_reason === '' || trim($new_request->rejection_reason) === '') {
            $new_request->rejection_reason = null;
            error_log('FIXED rejection_reason: empty -> NULL');
        }
        
        // 2. Ensure all numeric fields are properly typed (except requested_date which is now datetime string)
        $new_request->course_id = (int)$new_request->course_id;
        $new_request->annual_plan_id = (int)$new_request->annual_plan_id;
        $new_request->participant_type_id = (int)$new_request->participant_type_id;
        $new_request->duration_amount = (int)$new_request->duration_amount;
        $new_request->compensation_amount = (float)$new_request->compensation_amount;
        $new_request->is_internal_participant = (int)$new_request->is_internal_participant;
        $new_request->request_status_id = (int)$new_request->request_status_id;
        $new_request->is_approved = (int)$new_request->is_approved;
        $new_request->created_by = (int)$new_request->created_by;
        $new_request->time_created = (int)$new_request->time_created;
        $new_request->time_modified = (int)$new_request->time_modified;
        
        error_log('Record after cleaning: ' . print_r($new_request, true));
        
        // Validate data types after cleaning
        $validation_issues = [];
        
        if (!is_numeric($new_request->course_id)) {
            $validation_issues[] = 'course_id is not numeric: ' . gettype($new_request->course_id);
        }
        if (!is_numeric($new_request->annual_plan_id)) {
            $validation_issues[] = 'annual_plan_id is not numeric: ' . gettype($new_request->annual_plan_id);
        }
        if (!is_numeric($new_request->participant_type_id)) {
            $validation_issues[] = 'participant_type_id is not numeric: ' . gettype($new_request->participant_type_id);
        }
        if (!is_numeric($new_request->duration_amount)) {
            $validation_issues[] = 'duration_amount is not numeric: ' . gettype($new_request->duration_amount);
        }
        if (!is_numeric($new_request->compensation_amount)) {
            $validation_issues[] = 'compensation_amount is not numeric: ' . gettype($new_request->compensation_amount);
        }
        if (!is_numeric($new_request->is_internal_participant)) {
            $validation_issues[] = 'is_internal_participant is not numeric: ' . gettype($new_request->is_internal_participant);
        }
        if (empty($new_request->requested_date) || !is_string($new_request->requested_date)) {
            $validation_issues[] = 'requested_date is not a valid datetime string: ' . gettype($new_request->requested_date);
        }
        if (!is_numeric($new_request->request_status_id)) {
            $validation_issues[] = 'request_status_id is not numeric: ' . gettype($new_request->request_status_id);
        }
        if (!is_numeric($new_request->is_approved)) {
            $validation_issues[] = 'is_approved is not numeric: ' . gettype($new_request->is_approved);
        }
        if (!is_numeric($new_request->created_by)) {
            $validation_issues[] = 'created_by is not numeric: ' . gettype($new_request->created_by);
        }
        if (!is_numeric($new_request->time_created)) {
            $validation_issues[] = 'time_created is not numeric: ' . gettype($new_request->time_created);
        }
        if (!is_numeric($new_request->time_modified)) {
            $validation_issues[] = 'time_modified is not numeric: ' . gettype($new_request->time_modified);
        }
        
        // Check for NULL values in required fields
        $required_not_null_fields = [
            'course_id', 'annual_plan_id', 'participant_type_id', 'duration_amount', 
            'compensation_amount', 'is_internal_participant', 'requested_date', 
            'request_status_id', 'is_approved', 'created_by', 'time_created', 'time_modified'
        ];
        
        foreach ($required_not_null_fields as $field) {
            if ($new_request->$field === null) {
                $validation_issues[] = $field . ' is NULL but should not be';
            } elseif ($new_request->$field === '') {
                $validation_issues[] = $field . ' is empty string but should not be';
            }
        }
        
        // Special checks for nullable fields (data already cleaned above)
        if ($new_request->external_lecturer_id !== null && !is_numeric($new_request->external_lecturer_id)) {
            $validation_issues[] = 'external_lecturer_id should be numeric or null: ' . gettype($new_request->external_lecturer_id) . ' (' . $new_request->external_lecturer_id . ')';
        }
        // internal_user_id removed
        
        if (!empty($validation_issues)) {
            error_log('Data validation issues: ' . implode(', ', $validation_issues));
            echo "<div style='background-color: #ffe6e6; padding: 15px; margin: 15px 0; border-left: 4px solid #d93025;'>";
            echo "<h4>Data Validation Failed:</h4>";
            echo "<ul>";
            foreach ($validation_issues as $issue) {
                echo "<li>" . htmlspecialchars($issue) . "</li>";
            }
            echo "</ul>";
            echo "<p><strong>Data being validated:</strong></p>";
            echo "<pre style='background: #f0f0f0; padding: 10px; font-size: 11px;'>" . htmlspecialchars(print_r($new_request, true)) . "</pre>";
            echo "</div>";
            $mform->display();
            echo $OUTPUT->footer();
            exit;
        }
        
        // Try direct SQL insert with explicit NULL handling
        try {
            // Build a manual SQL insert to have full control over NULL values
            $sql = "INSERT INTO {local_participant_requests} (
                course_id, annual_plan_id, participant_type_id, clause, funding_type,
                external_lecturer_id, pf_number, participant_full_name, is_internal_participant,  
                duration_amount, compensation_amount, request_status_id, requested_date, 
                 is_approved, rejection_reason, created_by, time_created, time_modified, modified_by
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $params = [
                $new_request->course_id,
                $new_request->annual_plan_id, 
                $new_request->participant_type_id,
                $new_request->clause,
                $new_request->funding_type,
                $new_request->external_lecturer_id, // This should be null or int
                $new_request->pf_number,            // New Oracle PF number field
                $new_request->participant_full_name, // New Oracle full name field
                $new_request->is_internal_participant,
                $new_request->duration_amount,
                $new_request->compensation_amount,
                $new_request->request_status_id,
                $new_request->requested_date,
                $new_request->is_approved,
                $new_request->rejection_reason,
                $new_request->created_by,
                $new_request->time_created,
                $new_request->time_modified,
                $new_request->modified_by
            ];
            
            error_log('SQL: ' . $sql);
            error_log('Parameters: ' . print_r($params, true));
            
            $result = $DB->execute($sql, $params);
            
            // Get the actual inserted ID
            $last_id = $DB->get_field_sql("SELECT LAST_INSERT_ID()");
            error_log('SQL execution result: ' . ($result ? 'SUCCESS' : 'FAILED'));
            error_log('Last insert ID: ' . $last_id);
            
            // Verify the record was actually inserted
            $verify_record = $DB->get_record('local_participant_requests', ['id' => $last_id]);
            if ($verify_record) {
                error_log('Verified record exists in database');
                error_log('Inserted record: ' . print_r($verify_record, true));
            } else {
                error_log('ERROR: Record was not found after insertion!');
            }

            // Add the participant request to local_financeservices table
            try {
                // Get initial status ID from financeservices workflow manager
                $finance_initial_status = \local_financeservices\simple_workflow_manager::get_initial_status_id();
                
                // Create financeservices record
                $finance_request = new stdClass();
                $finance_request->course_id = $new_request->course_id;
                $finance_request->funding_type_id = $new_request->funding_type;
                $finance_request->price_requested = $new_request->compensation_amount;
                $finance_request->notes = 'Participant request - ' . ($is_external_lecturer ? 'External Lecturer' : 'Internal Participant');
                $finance_request->user_id = $new_request->created_by;
                $finance_request->date_time_requested = $new_request->time_created;
                $finance_request->date_type_required = null; // Not specified in participant form
                $finance_request->status_id = $finance_initial_status;
                $finance_request->clause_id = $new_request->clause;
                $finance_request->approval_note = null;
                $finance_request->rejection_note = null;
                $finance_request->timemodified = $new_request->time_modified;
                
                // Insert into local_financeservices table
                $finance_request_id = $DB->insert_record('local_financeservices', $finance_request);
                
                if ($finance_request_id) {
                    error_log('Successfully added participant request to local_financeservices. Finance Request ID: ' . $finance_request_id);
                    error_log('Finance request details: ' . print_r($finance_request, true));
                } else {
                    error_log('ERROR: Failed to insert participant request into local_financeservices table');
                }
                
            } catch (Exception $e) {
                error_log('ERROR adding participant request to local_financeservices: ' . $e->getMessage());
                error_log('Finance services insertion failed, but participant request was still created successfully');
                // Don't fail the entire operation if financeservices insertion fails
            }

            // Show success message with Moodle user creation info
            $success_message = get_string('requestadded', 'local_participant');
            
            // If this was an internal participant, show Oracle data confirmation
            if (!$is_external_lecturer && !empty($data->pf_number)) {
                $employee_pf_number = $data->pf_number;
                $employee_full_name = $data->participant_full_name;
                $success_message .= ' ' . get_string('oracledatastored', 'local_participant', $employee_pf_number);
                
                // Also show a notification
                \core\notification::success($success_message);
            }

        redirect(new moodle_url('/local/participant/index.php'), $success_message);
            
        } catch (Exception $e) {
            error_log('ERROR with direct SQL insert: ' . $e->getMessage());
            error_log('Exception type: ' . get_class($e));
            error_log('SQL Error details: ' . $e->getTraceAsString());
            
            // Try to get more specific database error
            if (method_exists($e, 'getCode')) {
                error_log('Error code: ' . $e->getCode());
            }
            
            // Try the original insert_record method as fallback
            try {
                error_log('Trying fallback insert_record method...');
                $insert_id = $DB->insert_record('local_participant_requests', $new_request);
                error_log('Fallback method succeeded with ID: ' . $insert_id);
                
                // Add the participant request to local_financeservices table (fallback)
                try {
                    // Get initial status ID from financeservices workflow manager
                    $finance_initial_status = \local_financeservices\simple_workflow_manager::get_initial_status_id();
                    
                    // Create financeservices record
                    $finance_request = new stdClass();
                    $finance_request->course_id = $new_request->course_id;
                    $finance_request->funding_type_id = $new_request->funding_type;
                    $finance_request->price_requested = $new_request->compensation_amount;
                    $finance_request->notes = 'Participant request - ' . ($is_external_lecturer ? 'External Lecturer' : 'Internal Participant');
                    $finance_request->user_id = $new_request->created_by;
                    $finance_request->date_time_requested = $new_request->time_created;
                    $finance_request->date_type_required = null; // Not specified in participant form
                    $finance_request->status_id = $finance_initial_status;
                    $finance_request->clause_id = $new_request->clause;
                    $finance_request->approval_note = null;
                    $finance_request->rejection_note = null;
                    $finance_request->timemodified = $new_request->time_modified;
                    
                    // Insert into local_financeservices table
                    $finance_request_id = $DB->insert_record('local_financeservices', $finance_request);
                    
                    if ($finance_request_id) {
                        error_log('Successfully added participant request to local_financeservices (fallback). Finance Request ID: ' . $finance_request_id);
                    } else {
                        error_log('ERROR: Failed to insert participant request into local_financeservices table (fallback)');
                    }
                    
                } catch (Exception $e3) {
                    error_log('ERROR adding participant request to local_financeservices (fallback): ' . $e3->getMessage());
                    // Don't fail the entire operation if financeservices insertion fails
                }
                
                redirect(new moodle_url('/local/participant/index.php'), get_string('requestadded', 'local_participant'));
            } catch (Exception $e2) {
                error_log('Fallback method also failed: ' . $e2->getMessage());
            }
            
            // Show detailed error information directly on page
            echo "<div style='background-color: #ffe6e6; padding: 15px; margin: 15px 0; border-left: 4px solid #d93025;'>";
            echo "<h4>Database Insertion Failed:</h4>";
            echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<p><strong>Exception Type:</strong> " . get_class($e) . "</p>";
            
            if (method_exists($e, 'getCode') && $e->getCode()) {
                echo "<p><strong>Error Code:</strong> " . $e->getCode() . "</p>";
            }
            
            // Show the data that failed to insert
            echo "<p><strong>Data that failed to insert:</strong></p>";
            echo "<pre style='background: #f0f0f0; padding: 10px; font-size: 11px; max-height: 300px; overflow-y: auto;'>";
            echo htmlspecialchars(print_r($new_request, true));
            echo "</pre>";
            
            // Show some database context
            echo "<p><strong>Database Context:</strong></p>";
            echo "<ul>";
            echo "<li>Table: local_participant_requests</li>";
            echo "<li>Database type: " . $DB->get_dbfamily() . "</li>";
            echo "</ul>";
            
            echo "<p><a href='view_logs.php' target='_blank'>View detailed error logs</a></p>";
            echo "</div>";
            
            // Don't redirect, let user see the error
        }
    } catch (dml_exception $e) {
        error_log('=== DML EXCEPTION CAUGHT ===');
        error_log('DML Error: ' . $e->getMessage());
        error_log('DML Error trace: ' . $e->getTraceAsString());


        \core\notification::error('Database error: ' . $e->getMessage());
        // Don't redirect immediately, let user see the error
    } catch (Exception $e) {
        error_log('=== GENERAL EXCEPTION CAUGHT ===');
        error_log('General Error: ' . $e->getMessage());
        error_log('General Error trace: ' . $e->getTraceAsString());
        
        \core\notification::error('Unexpected error: ' . $e->getMessage());
        // Don't redirect immediately, let user see the error
    }
} else {
    
    if ($_POST) {

        error_log('POST data: ' . print_r($_POST, true));
        error_log('Form errors: ' . print_r($mform->_errors ?? [], true));
    }
}


$mform->display();
echo $OUTPUT->footer();

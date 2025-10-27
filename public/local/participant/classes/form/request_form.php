<?php

namespace local_participant\form;

require_once("$CFG->libdir/formslib.php");

class request_form extends \moodleform
{
    public function __construct($actionurl = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true)
    {
        parent::__construct($actionurl, $customdata, $method, $target, $attributes, $editable);
    }

    public function definition()
    {
        global $USER, $DB, $PAGE, $OUTPUT;

        // Use the $courseid from the URL or default to 0.
        $courseid = optional_param('id', 0, PARAM_INT);
        $mform = $this->_form;

        // Conditionally add the hidden 'id' field if 'id' is provided in custom data.
        if (!empty($this->_customdata['id'])) {
            $mform->addElement('hidden', 'id', $this->_customdata['id']);
            $mform->setType('id', PARAM_INT);
        }


        if ($courseid > 0) {
            if(enrol_get_users_courses($USER->id, true, null, 'fullname ASC')){
             $courses = [get_course($courseid)];
            }
            $course = get_course($courseid);
 
             // Step 2: Get the category ID from the course
             $categoryid = $course->category;
 
             // Step 3: Get the context for that category
             $context = \context_coursecat::instance($categoryid);
            
             
              // Check if user has capability in this category
             if (has_capability('moodle/category:viewcourselist', $context, $USER)) {
                 // Get all visible courses in this category
                 // $category_courses = get_courses($category->id, 'fullname ASC', '*');
                 $courses = [get_course($courseid)];
                 
             }
         
         }        
         else {
             // Fetch only approved annual plan courses
             try {
                 // Check if annual plan tables exist
                 $table_exists = $DB->get_manager()->table_exists('local_annual_plan_course');
                 
                 if ($table_exists) {
                     // Get approved courses from annual plans for any year
                     $courses = $DB->get_records_sql(
                         'SELECT DISTINCT c.id, c.fullname, apc.coursename as annual_plan_course_name, apc.courseid as annual_plan_course_id, ap.year as annual_plan_year
                          FROM {course} c 
                          INNER JOIN {local_annual_plan_course} apc ON c.idnumber = apc.courseid 
                          INNER JOIN {local_annual_plan} ap ON apc.annualplanid = ap.id
                          WHERE c.visible = 1 
                          AND c.id != 1 
                          AND c.idnumber IS NOT NULL 
                          AND c.idnumber != ""
                          AND apc.approve = 1 
                          AND apc.disabled = 0
                          AND ap.disabled = 0
                          ORDER BY c.fullname ASC'
                     );
                     
                     error_log('Approved annual plan courses found: ' . count($courses));
                 } else {
                     throw new Exception('local_annual_plan_course table does not exist');
                 }
                 
             } catch (Exception $e) {
                 // If the query fails, show empty courses list
                 error_log('Approved annual plan course query failed: ' . $e->getMessage());
                 $courses = [];
             }
         }

        $courseoptions = [];
        
        if (!empty($courses)) {
            foreach ($courses as $course) {
                // Use annual plan course name if available, otherwise use Moodle course name
                $display_name = !empty($course->annual_plan_course_name) 
                    ? $course->annual_plan_course_name 
                    : format_string($course->fullname);
                
                // Add annual plan course ID to display name for better identification
                if (!empty($course->annual_plan_course_id)) {
                    $display_name .= ' (' . $course->annual_plan_course_id . ')';
                }
                
                $courseoptions[$course->id] = $display_name;
            }
        }
        
        // Add empty option if no courses found
        if (empty($courseoptions)) {
            $courseoptions[0] = get_string('noapprovedcoursesfound', 'local_participant', 'No approved courses found');
        }

        // Add course selection dropdown.
        $mform->addElement('select', 'courseid', get_string('course', 'local_participant'), $courseoptions);
        $mform->setType('courseid', PARAM_INT);
        $mform->addRule('courseid', null, 'required', null, 'client');

        // Get participant types with language-aware names and cost/calculation data
        $types_records = $DB->get_records('local_participant_request_types', null, '', 'id, name_en, name_ar, cost, calculation_type');
        $types = [];
        $types_data = []; // For storing cost and calculation type data
        $current_lang = current_language();
        foreach ($types_records as $type) {
            // Use Arabic name if current language is Arabic, otherwise use English
            $display_name = ($current_lang == 'ar') ? $type->name_ar : $type->name_en;
            
            // Add calculation type to display name
            $calculation_type_string = '';
            if ($type->calculation_type == 'hours') {
                $calculation_type_string = get_string('hours', 'local_participant');
            } else if ($type->calculation_type == 'days') {
                $calculation_type_string = get_string('days', 'local_participant');
            } else {
                $calculation_type_string = $type->calculation_type; // For dynamic or other types
            }
            
            // Add cost information to display name
            $cost_display = '';
            if ($type->calculation_type == 'dynamic') {
                $cost_display = get_string('dynamic', 'local_participant');
            } else {
                $currency = get_string('currency', 'local_participant');
                $cost_display = $type->cost . ' ' . $currency . '/' . $calculation_type_string;
            }
            
            $types[$type->id] = $display_name . ' (' . $cost_display . ')';
            $types_data[$type->id] = [
                'cost' => $type->cost,
                'calculation_type' => $type->calculation_type
            ];
        }
        $mform->addElement('select', 'participant_type_id', get_string('type', 'local_participant'), $types);
        $mform->addRule('participant_type_id', get_string('required'), 'required');
        
        // Add autocomplete dropdown for external lecturers
        $mform->addElement('html', '<div class="form-group row">
            <label for="lecturer_selector" class="col-md-3 col-form-label d-flex">' . get_string('externallecturer', 'local_participant') . '</label>
            <div class="col-md-9">
                <div class="lecturer-selector-container" style="position: relative;">
                    <select id="lecturer_selector" name="lecturer_selector" class="form-control" style="width: 100%; height: calc(1.5em + 0.75rem + 2px); padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; border: 1px solid #ced4da; border-radius: 0.25rem;">
                        <option value="">-- ' . get_string('searchlecturer', 'local_participant') . ' --</option>
                    </select>
                </div>
            </div>
        </div>');
        
        // Hidden field to store the selected lecturer ID
        $mform->addElement('hidden', 'external_lecturer_id', '');
        $mform->setType('external_lecturer_id', PARAM_INT);
        
        // Hidden field to store lecturer details
        $mform->addElement('hidden', 'lecturer_data', '');
        $mform->setType('lecturer_data', PARAM_RAW);

        // Add unified dropdown with autocomplete for internal employees from Oracle database
        $mform->addElement('html', '<div class="form-group row">
            <label for="employee_selector" class="col-md-3 col-form-label d-flex">' . get_string('user', 'local_participant') . '</label>
            <div class="col-md-9">
                <div class="employee-selector-container" style="position: relative;">
                    <select id="employee_selector" name="employee_selector" class="form-control" style="width: 100%; height: calc(1.5em + 0.75rem + 2px); padding: 0.375rem 0.75rem; font-size: 1rem; line-height: 1.5; color: #495057; background-color: #fff; border: 1px solid #ced4da; border-radius: 0.25rem;">
                        <option value="">-- ' . get_string('searchemployee', 'local_participant') . ' --</option>
                    </select>
                </div>
            </div>
        </div>');
        
        // Hidden field to store the selected employee PF number
        $mform->addElement('hidden', 'pf_number', '');
        $mform->setType('pf_number', PARAM_TEXT);
        
        // Hidden field to store the selected employee full name
        $mform->addElement('hidden', 'participant_full_name', '');
        $mform->setType('participant_full_name', PARAM_TEXT);
        
        // Hidden field to store employee details
        $mform->addElement('hidden', 'employee_data', '');
        $mform->setType('employee_data', PARAM_RAW);

        $mform->addElement('text', 'duration_amount', get_string('dayshours', 'local_participant'));
        $mform->setType('duration_amount', PARAM_INT);
        $mform->addRule('duration_amount', get_string('required'), 'required');
        $mform->addRule('duration_amount', get_string('invalidnumber', 'local_participant'), 'numeric', null, 'client');
        
        // Add help button for contract period explaining hours/days/dynamic
        $mform->addHelpButton('duration_amount', 'contractperiod_help', 'local_participant');

        // Store participant types data as hidden field for calculation
        $mform->addElement('hidden', 'types_data', json_encode($types_data));
        $mform->setType('types_data', PARAM_RAW);

        $mform->addElement('text', 'compensation_amount', get_string('cost', 'local_participant'));
        $mform->setType('compensation_amount', PARAM_FLOAT);
        $mform->addRule('compensation_amount', get_string('invalidnumber', 'local_participant'), 'numeric', null, 'client');
        
        // Only require compensation amount for dynamic types (External Lecturer - type 7)
        // For other types, it will be calculated automatically
        $mform->disabledIf('compensation_amount', 'participant_type_id', 'noteq', 7);
        
        // Add help text explaining automatic calculation
        $mform->addHelpButton('compensation_amount', 'compensation_help', 'local_participant');

// ========== CLAUSE: description list ==========
$clauseoptions = [];
try {
    $clauses = $DB->get_records('local_financeservices_clause', ['deleted' => 0], 'id ASC',
        'id, clause_name_en, clause_name_ar');
    $lang = current_language();
    foreach ($clauses as $c) {
        $label = ($lang === 'ar' && !empty($c->clause_name_ar)) ? $c->clause_name_ar : $c->clause_name_en;

        // OPTION A: store the description text in your "clause" column
       // $clauseoptions[$label] = $label; // value = description string
        // OPTION B (recommended): store the clause ID instead
        $clauseoptions[$c->id] = $label; // value = integer ID
    }
} catch (\Throwable $e) {
    debugging('Failed loading clauses: ' . $e->getMessage(), DEBUG_DEVELOPER);
}
if (empty($clauseoptions)) { $clauseoptions = ['' => '-- ' . get_string('choose', 'moodle') . ' --']; }

$mform->addElement('select', 'clause', get_string('clause', 'local_participant'), $clauseoptions);
$mform->addRule('clause', null, 'required', null, 'client');
// If you store ID, use PARAM_INT. If you store text, keep PARAM_TEXT.
$mform->setType('clause', PARAM_INT);


// ===== Funding type always ID = 5 (Participant) =====
$fundinglabel = '';
$fundingoptions = [];
try {
    // Fetch the record with ID = 5
    $funding = $DB->get_record('local_financeservices_funding_type',
        ['id' => 5, 'deleted' => 0],
        'id, funding_type_en, funding_type_ar',
        MUST_EXIST);

    // Choose language dynamically
    $lang = current_language();
    $fundinglabel = ($lang === 'ar' && !empty($funding->funding_type_ar))
        ? $funding->funding_type_ar
        : $funding->funding_type_en;

    $fundingoptions[$funding->id] = $fundinglabel;
} catch (\Throwable $e) {
    $fundingoptions[5] = 'Participant';
}

// Add dropdown with only one option
$mform->addElement('select', 'funding_type_display',
    get_string('fundingtype', 'local_participant'), $fundingoptions);
$mform->setDefault('funding_type_display', 5);
$mform->setType('funding_type_display', PARAM_INT);
$mform->disabledIf('funding_type_display', null, 'eq', null); // makes it look like a dropdown but disabled

// Add hidden element to actually store the value
$mform->addElement('hidden', 'fundingtype');
$mform->setType('fundingtype', PARAM_INT);
// Use setConstant so it’s present even if not in $_POST
$mform->setConstant('fundingtype', 5);

        $mform->addElement('date_selector', 'requested_date', get_string('requestdate', 'local_participant'));
        $mform->addHelpButton('requested_date', 'servicedate_help', 'local_participant');

        $this->add_action_buttons();
    }

    public function validation($data, $files)
    {
        global $DB;
        $errors = parent::validation($data, $files);
        
        // Validate that the selected course exists (annual plan data is optional)
        if (!empty($data['courseid'])) {
            $course = $DB->get_record('course', ['id' => $data['courseid']], 'id, fullname, idnumber');
            if (!$course) {
                $errors['courseid'] = get_string('invalidcourseid', 'local_participant');
            } else {
                // Check if course has annual plan data (optional check for information only)
                $has_annual_plan = false;
                if (!empty($course->idnumber)) {
                    $course_idnumber_part = explode('-', $course->idnumber)[0];
                    $plan_course = $DB->get_record('local_annual_plan_course', ['courseid' => $course_idnumber_part]);
                    $has_annual_plan = ($plan_course !== false);
                }
                
                // Log for debugging but don't block validation
                error_log('Form validation - Course: ' . $course->fullname . ', Has annual plan: ' . ($has_annual_plan ? 'YES' : 'NO'));
                
                // Note: We no longer require annual plan data for form validation
                // The warning will be shown during form processing in add_request.php
            }
        }

        // Get participant type data for validation
        $participant_type = $DB->get_record('local_participant_request_types', 
            ['id' => $data['participant_type_id']], 
            'cost, calculation_type'
        );

        // Validate compensation amount based on calculation type
        if ($participant_type && $participant_type->calculation_type == 'dynamic') {
            // For dynamic types (External Lecturer), compensation amount is required
            if (empty($data['compensation_amount']) || $data['compensation_amount'] <= 0) {
                $errors['compensation_amount'] = get_string('required');
            }
        } else {
            // For fixed rates, compensation amount will be calculated automatically
            // No validation needed as it will be overridden in processing
        }

        // Validate that the number of days/hours is positive.
        if ($data['duration_amount'] <= 0) {
            $errors['duration_amount'] = get_string('invalidnumber', 'local_participant');
        }

        // Validate that only one of `external_lecturer_id` or `pf_number` is selected based on `participant_type_id`.
        if ($data['participant_type_id'] == 1 || $data['participant_type_id'] == 2 || $data['participant_type_id'] == 3 || $data['participant_type_id'] == 4) {
            if (empty($data['pf_number'])) {
                $errors['pf_number'] = get_string('required');
            }
        }

        if ($data['participant_type_id'] == 7) {
            if (empty($data['external_lecturer_id'])) {
                $errors['external_lecturer_id'] = get_string('required');
            }
        }

        return $errors;
    }
}

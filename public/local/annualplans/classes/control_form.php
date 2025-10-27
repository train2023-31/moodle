<?php
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

/**
 * Form for uploading files and selecting options, including adding a new annual plan.
 */
class upload_form extends moodleform
{

    public function definition()
    {
        global $DB,  $PAGE;

        $mform = $this->_form;

        // Add a header to the form
        $mform->addElement('header', 'generalhdr', get_string('createannualplan', 'local_annualplans'));
        $mform->setExpanded('generalhdr', true);

        // Fetch the latest annual plan options directly from the database
        $annual_plan_options = [];
        if ($DB) {
            $annual_plans = $DB->get_records_menu('local_annual_plan', ['disabled' => 0], 'title ASC', 'id, title');
            foreach ($annual_plans as $plan_id => $plan_title) {
                $annual_plan_options[$plan_id] = $plan_title;
            }
        } else {
            throw new moodle_exception('dbaccessunavailable', 'local_annualplans');
        }

        // Add a text box for a new annual plan name
        $mform->addElement('text', 'newannualplanname', get_string('newannualplanname', 'local_annualplans'), array('size' => '30'));
        $mform->setType('newannualplanname', PARAM_TEXT);

        // Add a text box for entering the year of the new annual plan
        $mform->addElement('text', 'newannualplanyear', get_string('newannualplanyear', 'local_annualplans'), array('size' => '10'));
        $mform->setType('newannualplanyear', PARAM_INT);

        // Button to add the new annual plan
        $mform->addElement('submit', 'addnewannualplan', get_string('addnewannualplan', 'local_annualplans'));

        // Add the annual plan select element to your form
        // $mform->addElement('select', 'annualplanid', get_string('selectannualplan', 'local_annualplans'), $annual_plan_options);
        // // File picker options
        // $filepickeroptions = array(
        //     'filetypes' => array('.csv', '.xlsx'),
        //     'maxbytes' => get_max_upload_file_size()
        // );

        // // File picker for uploading course data
        // $mform->addElement('filepicker', 'annualplanfile', get_string('uploadcoursefile', 'local_annualplans'), null, $filepickeroptions);

        // // File picker for uploading user enrollment data
        // //$mform->addElement('filepicker', 'userenrollmentfile', get_string('uploaduserfile', 'local_annualplans'), null, $filepickeroptions);

        // // Upload button
        // $mform->addElement('submit', 'uploadbutton', get_string('upload', 'local_annualplans'));

        // Add a delete button for deleting an annual plan

    }

    public function validation($data, $files)
    {
        $errors = array();

        // Check which submit button was clicked
        if (isset($data['addnewannualplan'])) {
            // Apply validation rules only when 'addnewannualplan' is submitted
            if (empty($data['newannualplanname'])) {
                $errors['newannualplanname'] = get_string('required');
            }

            if (empty($data['newannualplanyear'])) {
                $errors['newannualplanyear'] = get_string('required');
            }
        }

        // You can add other validation rules here for different buttons if needed

        return $errors;
    }


    public function process_data($data)
    {
        global $DB;

        if ($this->is_cancelled()) {
            return;
        } else if ($fromform = $this->get_data()) {
            // Check if the "Add New Annual Plan" button was clicked
            if (isset($fromform->addnewannualplan)) {
                if (!empty($fromform->newannualplanname)) {
                    $this->add_new_annual_plan($fromform->newannualplanname, $fromform->newannualplanyear);
                    redirect(new moodle_url('/local/annualplans/index.php'));
                } else {
                    echo "Please enter a new annual plan name.<br>";
                }
            }
            // Check if the "Upload" button was clicked
            else if (isset($fromform->uploadbutton)) {
                $annualplanid = $fromform->annualplanid;

                // Process the uploaded course file
                if (!empty($fromform->annualplanfile)) {
                    $this->process_uploaded_file($fromform->annualplanfile, $annualplanid, 'process_course_file');
                }

                // Process the uploaded user enrollment file
                if (!empty($fromform->userenrollmentfile)) {
                    $this->process_uploaded_file($fromform->userenrollmentfile, $annualplanid, 'process_user_file');
                }
            }
        }
    }

    /**
     * General method to process uploaded files.
     */
    private function process_uploaded_file($itemid, $annualplanid, $processing_function)
    {
        global $CFG, $USER;

        $context = context_user::instance($USER->id);
        $fs = get_file_storage();

        $uploadpath = $CFG->tempdir . '/upload';
        if (!file_exists($uploadpath)) {
            mkdir($uploadpath, 0777, true);
        }

        $files = $fs->get_area_files($context->id, 'user', 'draft', $itemid, 'sortorder, id', false);

        foreach ($files as $file) {
            $tempfile = $uploadpath . '/' . uniqid('annual_plans_');
            $file->copy_content_to($tempfile);

            if (file_exists($tempfile)) {
                // Call the appropriate processing function
                $this->$processing_function($tempfile, $annualplanid);
                unlink($tempfile);
            }

            $file->delete();
        }
    }

    /**
     * Process the course file. editing csv files inputs 
     */
    private function process_course_file($filepath, $annualplanid)
    {
        global $DB;

        $handle = fopen($filepath, "r");
        if ($handle) {
            fgetcsv($handle, 1000, ","); // Skip the header row
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) >= 6) {
                    $record = new stdClass();
                    $record->annualplanid = $annualplanid;
                    $record->courseid = $data[0];
                    $record->coursename = $data[1];
                    $record->category = $data[2];
                    $record->coursedate = strtotime($data[3]);
                    $record->numberofbeneficiaries = $data[4];
                    $record->status = $data[5];
                    $DB->insert_record('local_annual_plan_course', $record);
                }
            }
            fclose($handle);
            echo "Course data has been processed successfully.<br>";
        } else {
            throw new moodle_exception('erroropenfile', 'local_annualplans', '', null, "Could not open file: {$filepath}");
        }
    }

    /**
     * Process the user enrollment file.
     */
    private function process_user_file($filepath, $annualplanid)
    {
        global $DB;

        $handle = fopen($filepath, "r");
        if ($handle) {
            fgetcsv($handle, 1000, ","); // Skip the header row
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                if (count($data) >= 3) {
                    $fullname = $data[0];
                    $userid = $data[1];
                    $courseid = $data[2];

                    $user = $DB->get_record('user', array('id' => $userid), '*', IGNORE_MISSING);
                    if (!$user) {
                        echo "User with ID $userid does not exist. Skipping.<br>";
                        continue;
                    }

                    $record = new stdClass();
                    $record->userid = $userid;
                    $record->fullname = $fullname;
                    $record->courseid = $courseid;
                    $record->annualplanid = $annualplanid;
                    $record->timecreated = time();
                    $record->timemodified = time();

                    $existing_enrollment = $DB->get_record('local_annual_plan_user', array(
                        'userid' => $userid,
                        'courseid' => $courseid,
                        'annualplanid' => $annualplanid
                    ));

                    if ($existing_enrollment) {
                        $record->id = $existing_enrollment->id;
                        $DB->update_record('local_annual_plan_user', $record);
                        echo "Updated enrollment for user ID $userid in course ID $courseid.<br>";
                    } else {
                        $DB->insert_record('local_annual_plan_user', $record);
                        echo "Inserted enrollment for user ID $userid in course ID $courseid.<br>";
                    }
                }
            }
            fclose($handle);
            echo "User enrollment data has been processed successfully.<br>";
        } else {
            throw new moodle_exception('erroropenfile', 'local_annualplans', '', null, "Could not open file: {$filepath}");
        }
    }

    /**
     * Add a new annual plan to the database.
     */
    private function add_new_annual_plan($plan_name, $plan_year)
    {
        global $DB;

        $existing_plan = $DB->get_record('local_annual_plan', ['title' => $plan_name, 'year' => $plan_year]);
        if (!$existing_plan) {
            $new_plan = new stdClass();
            $new_plan->title = $plan_name;
            $new_plan->year = $plan_year;
            $new_plan->date_created = time();
            $new_plan->timecreated = time();
            $new_plan->timemodified = time();
            $DB->insert_record('local_annual_plan', $new_plan);
            echo "New annual plan '$plan_name' for year '$plan_year' added successfully.<br>";
        } else {
            echo "Annual plan '$plan_name' for year '$plan_year' already exists.<br>";
        }
    }
}
///delete annual plans

class annual_plan_delete_form extends moodleform
{
    public function definition()
    {
        global $DB;

        $mform = $this->_form;

        // Add a header to the form
        $mform->addElement('header', 'generalhdr1', get_string('deleteannualplan', 'local_annualplans'));
        $mform->setExpanded('generalhdr1', false);

        // Fetch active annual plans
        $annual_plan_options = [];
        if ($DB) {
            $annual_plans = $DB->get_records_menu('local_annual_plan', ['disabled' => 0], 'title ASC', 'id, title');
            foreach ($annual_plans as $plan_id => $plan_title) {
                $annual_plan_options[$plan_id] = $plan_title;
            }
        } else {
            throw new moodle_exception('dbaccessunavailable', 'local_annualplans');
        }

        // Add a select element for annual plans
        $mform->addElement('select', 'annualplanid', get_string('selectannualplan', 'local_annualplans'), $annual_plan_options);
        $mform->addRule('annualplanid', null, 'required', null, 'client');

        // Add a hidden field for the deletion note
        $mform->addElement('hidden', 'deletion_note', '');
        $mform->setType('deletion_note', PARAM_TEXT);

        // Add a submit button
        $mform->addElement('submit', 'deleteannualplan', get_string('deleteannualplan', 'local_annualplans'));
    }

    /**
     * Delete an annual plan from the database.
     */
    private function delete_annual_plan($plan_id, $note)
    {
        global $DB;

        if ($DB->record_exists('local_annual_plan', ['id' => $plan_id])) {
            // Disable the annual plan
            $DB->set_field('local_annual_plan', 'disabled', 1, ['id' => $plan_id]);

            // Save the deletion note
            $DB->set_field('local_annual_plan', 'deletion_note', $note, ['id' => $plan_id]);

            // Disable associated courses
            $DB->set_field('local_annual_plan_course', 'disabled', 1, ['annualplanid' => $plan_id]);
            // Alternatively, to delete records:
            // $DB->delete_records('local_annual_plan_course', ['annualplanid' => $plan_id]);

            // Redirect with a success message
            redirect(
                new moodle_url('/local/annualplans/index.php'),
                get_string('annualplandeleted', 'local_annualplans'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            // Redirect with an error message
            redirect(
                new moodle_url('/local/annualplans/index.php'),
                get_string('annualplannotexist', 'local_annualplans'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }
    }

    /**
     * Handle form submission.
     */
    public function process_data($data)
    {
        if ($this->is_cancelled()) {
            return;
        } else if ($fromform = $this->get_data()) {
            $annualplanid = $fromform->annualplanid;
            $deletion_note = $fromform->deletion_note;

            if ($annualplanid) {
                $this->delete_annual_plan($annualplanid, $deletion_note);
            } else {
                // Redirect with an error message if no plan is selected
                redirect(
                    new moodle_url('/local/annualplans/index.php'),
                    get_string('noselectedplan', 'local_annualplans'),
                    null,
                    \core\output\notification::NOTIFY_ERROR
                );
            }
        }
    }
}
/**
 * Form for filtering data.
 */
class filter_form extends moodleform
{
    public function definition()
    {
        global $DB, $SESSION;

        $mform = $this->_form;

        // Add a header to the form
        // $mform->addElement('header', 'filterhdr', get_string('filter', 'local_annualplans'));
        // $mform->setExpanded('filterhdr', false);
        // $mform->setAttributes(['class' => 'none-border-form']);

        // Fetch the latest annual plan options directly from the database, bypassing any cache
        $annual_plan_options = [];
        if ($DB) {
            $annual_plans = $DB->get_records_menu('local_annual_plan', ['disabled' => 0], 'title ASC', 'id, title');
            foreach ($annual_plans as $plan_id => $plan_title) {
                $annual_plan_options[$plan_id] = $plan_title;
            }
        } else {
            throw new moodle_exception('dbaccessunavailable', 'local_annualplans');
        }

        $course_level_options = [];
        if ($DB) {
            $course_level = $DB->get_records_menu('local_annual_plan_course_level', null, 'description_ar ASC', 'id, description_ar');
            foreach ($course_level as $level_id => $level_name) {
                $course_level_options[$level_id] = $level_name;
            }
        } else {
            throw new moodle_exception('dbaccessunavailable', 'local_annual_plan_course_level');
        }


        // Add the annual plan select element to your form
        $mform->addElement('select', 'annualplanid', get_string('selectannualplan', 'local_annualplans'), $annual_plan_options);
        // Set default value from session if available
        if (isset($SESSION->annualplanid) && !empty($SESSION->annualplanid)) {
            $mform->setDefault('annualplanid', $SESSION->annualplanid);
        }

        // Dropdown to select the course category
        $categories = $this->get_unique_categories_from_annual_plans();
        $categories = array('' => get_string('any', 'local_annualplans')) + $categories;
        $mform->addElement('select', 'category', get_string('category', 'local_annualplans'), $categories);
        // Set default value from session if available
        if (isset($SESSION->category) && !empty($SESSION->category)) {
            $mform->setDefault('category', $SESSION->category);
        } else {
            $mform->setDefault('category', '');
        }

        $levels = $this->get_unique_levels_from_annual_plans_course_level();
        $levels = array('' => get_string('any', 'local_annualplans')) + $levels;
        $mform->addElement('select', 'level', get_string('level', 'local_annualplans'), $levels);
        // Set default value from session if available
        if (isset($SESSION->level) && !empty($SESSION->level)) {
            $mform->setDefault('level', $SESSION->level);
        } else {
            $mform->setDefault('level', '');
        }



        // Text input for course name filter
        $mform->addElement('text', 'coursename', get_string('coursename', 'local_annualplans'));
        $mform->setType('coursename', PARAM_TEXT);
        // Set default value from session if available
        if (isset($SESSION->coursename) && !empty($SESSION->coursename)) {
            $mform->setDefault('coursename', $SESSION->coursename);
        }

        $mform->addElement('text', 'courseid', get_string('courseid', 'local_annualplans'));
        $mform->setType('courseid', PARAM_TEXT);
        // Set default value from session if available
        if (isset($SESSION->courseid) && !empty($SESSION->courseid)) {
            $mform->setDefault('courseid', $SESSION->courseid);
        }

        $mform->addElement('text', 'status', get_string('status', 'local_annualplans'));
        $mform->setType('status', PARAM_TEXT);
        // Set default value from session if available
        if (isset($SESSION->status) && !empty($SESSION->status)) {
            $mform->setDefault('status', $SESSION->status);
        }

        $mform->addElement('text', 'place', get_string('place', 'local_annualplans'));
        $mform->setType('place', PARAM_TEXT);
        // Set default value from session if available
        if (isset($SESSION->place) && !empty($SESSION->place)) {
            $mform->setDefault('place', $SESSION->place);
        }

        // Date filters - optional, leave empty to show all courses including those without dates
        $mform->addElement('date_selector', 'startdateinput', get_string('startdate', 'local_annualplans'), array('optional' => true));
        // Set default value from session if available
        if (isset($SESSION->startdateinput) && !empty($SESSION->startdateinput)) {
            $mform->setDefault('startdateinput', $SESSION->startdateinput);
        }
        
        $mform->addElement('date_selector', 'enddateinput', get_string('enddate', 'local_annualplans'), array('optional' => true));
        // Set default value from session if available
        if (isset($SESSION->enddateinput) && !empty($SESSION->enddateinput)) {
            $mform->setDefault('enddateinput', $SESSION->enddateinput);
        }
        
        // Filter button
        $mform->addElement('submit', 'filterbutton', get_string('applyfilters', 'local_annualplans'));
    }

    function get_unique_categories_from_annual_plans()
    {
        global $DB;
        $sql = "SELECT DISTINCT name FROM {course_categories}";
        $categories = $DB->get_records_sql($sql);
        $category_list = [];
        foreach ($categories as $category) {
            $category_list[$category->name] = $category->name;
        }
        return ['' => get_string('selectany', 'local_annualplans')] + $category_list;
    }
    function get_unique_levels_from_annual_plans_course_level()
    {
        global $DB;
        $sql = "SELECT DISTINCT description_ar FROM {local_annual_plan_course_level}";
        $levels = $DB->get_records_sql($sql);
        $levels_list = [];
        foreach ($levels as $level) {
            $levels_list[$level->description_ar] = $level->description_ar;
        }
        return ['' => get_string('selectany', 'local_annualplans')] + $levels_list;
    }
}

//To add new course
class add_course_form extends moodleform
{
    public function definition()
    {
        global $DB;

        $mform = $this->_form;

        // Add a header for the "Add Course" form
        $mform->addElement('header', 'addcoursehdr', get_string('addnewcourse', 'local_annualplans'));
        $mform->setExpanded('addcoursehdr', true);

        // Fetch annual plans directly from the database
        $annual_plan_options = [];
        if ($DB) {
            $annual_plans = $DB->get_records_menu('local_annual_plan', ['disabled' => 0], 'title ASC', 'id, title');
            foreach ($annual_plans as $plan_id => $plan_title) {
                $annual_plan_options[$plan_id] = $plan_title;
            }
        } else {
            throw new moodle_exception('dbaccessunavailable', 'local_annualplans');
        }

        // Select annual plan dropdown
        $mform->addElement('select', 'annualplanid', get_string('selectannualplan', 'local_annualplans'), $annual_plan_options);
        $mform->setType('annualplanid', PARAM_INT);
        
        // Course shortname generator section
        $mform->addElement('header', 'shortnamehdr', get_string('shortnamegenerator', 'local_annualplans'));
        $mform->setExpanded('shortnamehdr', true);
        
        // Get available category codes
        $category_code_options = [0 => get_string('selectany', 'local_annualplans')];
        $category_codes = $DB->get_records('local_annual_plan_course_codes', ['type' => 'category'], 'code_en ASC');
        foreach ($category_codes as $code) {
            $category = $DB->get_record('course_categories', ['id' => $code->type_id], 'description');
            if ($category) {
                $display_text = $category->description . ' (' . $code->code_en . ')';
                if (!empty($code->description_en)) {
                    $display_text = $category->description . ' - '  . ' (' . $code->code_en . ')'; //. shorten_text($code->description_en, 30)
                }
                $category_code_options[$code->id] = $display_text;
            }
        }
        
        $mform->addElement('select', 'category_code_id', get_string('categorycodes', 'local_annualplans'), $category_code_options);
        $mform->setType('category_code_id', PARAM_INT);
        
        // Get available grade codes
        $grade_code_options = [0 => get_string('selectany', 'local_annualplans')];
        $grade_codes = $DB->get_records('local_annual_plan_course_codes', ['type' => 'grade'], 'code_en ASC');
        foreach ($grade_codes as $code) {
            // Display grade codes without relying on a related level record
            if (!empty($code->description_en)) {
                $grade_code_options[$code->id] = shorten_text($code->description_en, 50) . ' (' . $code->code_en . ')';
            } else {
                $grade_code_options[$code->id] = $code->code_en;
            }
        }
        
        $mform->addElement('select', 'grade_code_id', get_string('gradeCodes', 'local_annualplans'), $grade_code_options);
        $mform->setType('grade_code_id', PARAM_INT);
        
        // Get available course codes
        $course_code_options = [0 => get_string('selectany', 'local_annualplans')];
        $course_codes = $DB->get_records('local_annual_plan_course_codes', ['type' => 'course'], 'code_en ASC');
        foreach ($course_codes as $code) {
            // If description is available, use it in the display along with the code
            if (!empty($code->description_en)) {
                $course_code_options[$code->id] = shorten_text($code->description_en, 50) . ' (' . $code->code_en . ')';
            } else {
                $course_code_options[$code->id] = $code->code_en;
            }
        }
        
        $mform->addElement('select', 'course_code_id', get_string('coursecodes', 'local_annualplans'), $course_code_options);
        $mform->setType('course_code_id', PARAM_INT);
        
        // Get available targeted group codes
        $targeted_group_code_options = [0 => get_string('selectany', 'local_annualplans')];
        $targeted_group_codes = $DB->get_records('local_annual_plan_course_codes', ['type' => 'targeted_group'], 'code_en ASC');
        foreach ($targeted_group_codes as $code) {
            if (!empty($code->description_en)) {
                $targeted_group_code_options[$code->id] = shorten_text($code->description_en, 50) . ' (' . $code->code_en . ')';
            } else {
                $targeted_group_code_options[$code->id] = $code->code_en;
            }
        }
        
        $mform->addElement('select', 'targeted_group_code_id', get_string('targetedgroupcodes', 'local_annualplans'), $targeted_group_code_options);
        $mform->setType('targeted_group_code_id', PARAM_INT);
        
        // Get available group number codes
        $group_number_code_options = [0 => get_string('selectany', 'local_annualplans')];
        $group_number_codes = $DB->get_records('local_annual_plan_course_codes', ['type' => 'group_number'], 'code_en ASC');
        foreach ($group_number_codes as $code) {
            if (!empty($code->description_en)) {
                $group_number_code_options[$code->id] = shorten_text($code->description_en, 50) . ' (' . $code->code_en . ')';
            } else {
                $group_number_code_options[$code->id] = $code->code_en;
            }
        }
        
        $mform->addElement('select', 'group_number_code_id', get_string('groupnumbercodes', 'local_annualplans'), $group_number_code_options);
        $mform->setType('group_number_code_id', PARAM_INT);
        
        // Preview of the generated shortname (will be updated via JavaScript)
        $mform->addElement('static', 'shortname_preview', get_string('courseshortname', 'local_annualplans'), 
                          '<div id="shortname_preview_container"></div>');
        $mform->addHelpButton('shortname_preview', 'shortname', 'local_annualplans');
        
        // Add hidden fields for category and level IDs (to be set by JavaScript)
        $mform->addElement('hidden', 'categoryid', '');
        $mform->setType('categoryid', PARAM_INT);
        
        $mform->addElement('hidden', 'levelid', '');
        $mform->setType('levelid', PARAM_INT);
        
        // Course ID field (now will be auto-generated from selected codes)
        $mform->addElement('text', 'courseid', get_string('courseid', 'local_annualplans'));
        $mform->setType('courseid', PARAM_TEXT);
        $mform->addRule('courseid', null, 'required', null, 'client');
        
        // Course name field
        $mform->addElement('text', 'coursename', get_string('coursename', 'local_annualplans'));
        $mform->setType('coursename', PARAM_TEXT);
        $mform->addRule('coursename', null, 'required', null, 'client');

        // Add course level dropdown
        $course_level_options = [0 => get_string('selectany', 'local_annualplans')];
        $course_levels = $DB->get_records('local_annual_plan_course_level', null, 'name ASC', 'id, name, description_ar');
        foreach ($course_levels as $level) {
            $course_level_options[$level->id] = $level->name .' - ' .$level->description_ar;
        }
        $mform->addElement('select', 'courselevelid', get_string('level', 'local_annualplans'), $course_level_options);
        $mform->setType('courselevelid', PARAM_INT);

        // Add duration input in days
        $mform->addElement('text', 'coursedate', get_string('coursedate', 'local_annualplans'));
        $mform->setType('coursedate', PARAM_INT);
        $mform->addRule('coursedate', null, 'required', null, 'client');

        // Number of beneficiaries
        $mform->addElement('text', 'numberofbeneficiaries', get_string('numberofbeneficiaries', 'local_annualplans'));
        $mform->setType('numberofbeneficiaries', PARAM_INT);

        // Change status from text field to hidden field
        $mform->addElement('hidden', 'status', '');
        $mform->setType('status', PARAM_TEXT);


$finance_options = [];
$records = $DB->get_records('local_annual_plan_finance_source', null, 'id ASC', 'code, description_ar');

foreach ($records as $record) {
    $finance_options[$record->code] = $record->code. ' - ' .$record->description_ar;
}

// Finance source input (dropdown field)
$mform->addElement('select', 'finance_source', get_string('financesource', 'local_annualplans'), $finance_options);
$mform->setType('finance_source', PARAM_TEXT);

// Finance remarks input (textarea)
$mform->addElement('textarea', 'finance_remarks', get_string('financeremarks', 'local_annualplans'), 'wrap="virtual" rows="4" cols="50"');
$mform->setType('finance_remarks', PARAM_TEXT);



        // Submit button
        $mform->addElement('submit', 'addcoursebutton', get_string('addcourse', 'local_annualplans'));
    }

    /**
     * Process the form data to add the course to the selected annual plan.
     */
    public function process_data($data)
    {
        global $DB, $USER;

        // Validate required fields
        if (empty($data->annualplanid)) {
            \core\notification::error(get_string('annualplanrequired', 'local_annualplans'));
            return false;
        }
        
        if (empty($data->courseid)) {
            \core\notification::error(get_string('courseidrequired', 'local_annualplans'));
            return false;
        }
        
        if (empty($data->coursename)) {
            \core\notification::error(get_string('coursenamerequired', 'local_annualplans'));
            return false;
        }
        
        // If categoryid is not set but category_code_id is, get the category ID from the code
        if (empty($data->categoryid) && !empty($data->category_code_id)) {
            $code = $DB->get_record('local_annual_plan_course_codes', array('id' => $data->category_code_id));
            if ($code && !empty($code->type_id)) {
                $data->categoryid = $code->type_id;
            }
        }
        
        if (empty($data->categoryid)) {
            \core\notification::error(get_string('categoryrequired', 'local_annualplans'));
            return false;
        }
        
        // If levelid is not set but level_code_id is, get the level ID from the code
        if (empty($data->levelid) && !empty($data->level_code_id)) {
            // For level codes, we use the selected courselevelid directly since 
            // level codes no longer have a type_id to reference a level
            if (!empty($data->courselevelid)) {
                $data->levelid = $data->courselevelid;
            }
        }

        try {
            // Get the category name from the categoryid
            $category = $DB->get_record('course_categories', array('id' => $data->categoryid), 'name', MUST_EXIST);
            
            // Create a new course record
            $new_course = new stdClass();
            $new_course->annualplanid = $data->annualplanid;
            $new_course->courseid = $data->courseid;
            $new_course->coursename = $data->coursename;
            $new_course->category = $category->name;
            
            // Use selected course level if provided
            if (!empty($data->courselevelid)) {
                $new_course->courselevelid = $data->courselevelid;
            } else {
                // Fall back to level from code if no direct level selection
                $new_course->courselevelid = $data->levelid ? $data->levelid : null;
            }
            
            $new_course->coursedate = (int) $data->coursedate; // duration days
            $new_course->numberofbeneficiaries = $data->numberofbeneficiaries ? $data->numberofbeneficiaries : 0;
            
//Store the selected value
$new_course->finance_source = isset($data->finance_source) ? $data->finance_source : null;
$new_course->finance_remarks = isset($data->finance_remarks) ? $data->finance_remarks : null;


            // Get the description directly from the targeted_group_code_id
            if (!empty($data->targeted_group_code_id)) {
                $targeted_group = $DB->get_record('local_annual_plan_course_codes', array('id' => $data->targeted_group_code_id));
                if ($targeted_group && !empty($targeted_group->description_en)) {
                    $new_course->status = $targeted_group->description_en;
                } else {
                    $new_course->status = $data->status ? $data->status : '';
                }
            } else {
                $new_course->status = $data->status ? $data->status : '';
            }
            
            $new_course->place = isset($data->place) ? $data->place : '';
            $new_course->approve = 0; // Default to not approved
            $new_course->userid = $USER->id; // Record who created this course
            $new_course->disabled = 0; // Not disabled by default
            
            // Store the code IDs for reference
            $new_course->category_code_id = !empty($data->category_code_id) ? $data->category_code_id : null;
            $new_course->grade_code_id = !empty($data->grade_code_id) ? $data->grade_code_id : null;
            $new_course->course_code_id = !empty($data->course_code_id) ? $data->course_code_id : null;
            $new_course->targeted_group_code_id = !empty($data->targeted_group_code_id) ? $data->targeted_group_code_id : null;
            $new_course->group_number_code_id = !empty($data->group_number_code_id) ? $data->group_number_code_id : null;
            
            // Insert the new course record into the database
            $course_id = $DB->insert_record('local_annual_plan_course', $new_course);
            
            if (!$course_id) {
                \core\notification::error(get_string('erroraddingcourse', 'local_annualplans'));
                return false;
            }
            
            return $course_id;
        } catch (Exception $e) {
            \core\notification::error(get_string('dberror', 'local_annualplans') . ': ' . $e->getMessage());
            return false;
        }
    }
}


// manage courses levels
/**
 * Form for managing course levels.
 */
class manage_levels_form extends moodleform
{
    public function definition()
    {
        global $DB;

        $mform = $this->_form;

        // Add a header for the "Manage Levels" form
        $mform->addElement('header', 'managelevelshdr', get_string('managelevels', 'local_annualplans'));
        $mform->setExpanded('managelevelshdr', true);

        // Add a text box for the new level name
        $mform->addElement('text', 'newlevelname', get_string('newlevelname', 'local_annualplans'), array('size' => '30'));
        $mform->setType('newlevelname', PARAM_TEXT);

        // Add a text area for the level description
        $mform->addElement('textarea', 'newleveldescription', get_string('newleveldescription', 'local_annualplans'), 'wrap="virtual" rows="5" cols="50"');
        $mform->setType('newleveldescription', PARAM_TEXT);

        // Add checkbox for internal/external classification
        $mform->addElement('checkbox', 'newlevelisinternal', get_string('isinternal', 'local_annualplans'));
        $mform->addHelpButton('newlevelisinternal', 'isinternal', 'local_annualplans');
        $mform->setDefault('newlevelisinternal', 1); // Default to internal

        // Add button to create the new level
        $mform->addElement('submit', 'addnewlevel', get_string('addnewlevel', 'local_annualplans'));

        // Fetch existing course levels from the database
        $course_level_options = [];
        if ($DB) {
            $course_levels = $DB->get_records_menu('local_annual_plan_course_level', null, 'name ASC', 'id, name');
            $course_level_options = ['' => get_string('selectleveltodelete', 'local_annualplans')] + $course_levels;
        } else {
            throw new moodle_exception('dbaccessunavailable', 'local_annualplans');
        }

        //Dropdown to select a level to delete
        $mform->addElement('select', 'leveltodelete', get_string('selectlevel', 'local_annualplans'), $course_level_options);
        $mform->addHelpButton('leveltodelete', 'selectlevel', 'local_annualplans');

        // Button to delete the selected level
        $mform->addElement('submit', 'deletelevelbutton', get_string('deletelevel', 'local_annualplans'));
    }

    public function process_data($data)
    {
        global $DB;

        if ($this->is_cancelled()) {
            return;
        } else if ($fromform = $this->get_data()) {
            // Check if the "Add New Level" button was clicked
            if (isset($fromform->addnewlevel)) {
                if (!empty($fromform->newlevelname)) {
                    $is_internal = isset($fromform->newlevelisinternal) ? 1 : 0;
                    $this->add_new_level($fromform->newlevelname, $fromform->newleveldescription, $is_internal);
                    // Redirect to refresh and load new data
                    redirect(new moodle_url('/local/annualplans/manage_levels.php'));
                } else {
                    echo "Please enter a new level name.<br>";
                }
            }
            // Check if the "Delete Level" button was clicked
            else if (isset($fromform->deletelevelbutton) && !empty($fromform->leveltodelete)) {
                $this->delete_level($fromform->leveltodelete);
                // Redirect to refresh and load new data
                redirect(new moodle_url('/local/annualplans/manage_levels.php'));
            } else {
                echo "No action specified or missing required data.<br>";
            }
        }
    }

    /**
     * Adds a new course level to the database.
     */
    private function add_new_level($level_name, $level_description, $is_internal = 1)
    {
        global $DB;

        // Check if the level already exists
        $existing_level = $DB->get_record('local_annual_plan_course_level', ['name' => $level_name]);
        if (!$existing_level) {
            // Insert the new level into the database
            $new_level = new stdClass();
            $new_level->name = $level_name;
            $new_level->description = $level_description;
            $new_level->is_internal = $is_internal;
            $DB->insert_record('local_annual_plan_course_level', $new_level);
            echo "New level '$level_name' added successfully.<br>";
        } else {
            echo "Level '$level_name' already exists.<br>";
        }
    }

    /**
     * Deletes a course level from the database.
     */
    private function delete_level($level_id)
    {
        global $DB;

        // Check if the level exists
        $existing_level = $DB->get_record('local_annual_plan_course_level', ['id' => $level_id]);
        if ($existing_level) {
            $DB->delete_records('local_annual_plan_course_level', ['id' => $level_id]);
            echo "Level '{$existing_level->name}' deleted successfully.<br>";
        } else {
            echo "The specified level does not exist.<br>";
        }
    }
}


// filter approved courses (view all courses)
class filter_approved_form extends moodleform
{
    public function definition()
    {
        global $DB, $SESSION;

        $mform = $this->_form;

        // Add a header to the form
        // $mform->addElement('header', 'filterhdr', get_string('filter', 'local_annualplans'));
        // $mform->setExpanded('filterhdr', false);
        // $mform->setAttributes(['class' => 'none-border-form']);

        // Fetch the latest annual plan options directly from the database, bypassing any cache
        $annual_plan_options = [];
        if ($DB) {
            $annual_plans = $DB->get_records_menu('local_annual_plan', ['disabled' => 0], 'title ASC', 'id, title');
            foreach ($annual_plans as $plan_id => $plan_title) {
                $annual_plan_options[$plan_id] = $plan_title;
            }
        } else {
            throw new moodle_exception('dbaccessunavailable', 'local_annualplans');
        }

        // Add "All Plans" option at the beginning
        $annual_plan_options = array('' => get_string('allplans', 'local_annualplans')) + $annual_plan_options;

        $course_level_options = [];
        if ($DB) {
            $course_level = $DB->get_records_menu('local_annual_plan_course_level', null, 'name ASC', 'id, name');
            foreach ($course_level as $level_id => $level_name) {
                $course_level_options[$level_id] = $level_name;
            }
        } else {
            throw new moodle_exception('dbaccessunavailable', 'local_annual_plan_course_level');
        }


        // Add the annual plan select element to your form
        $mform->addElement('select', 'annualplanid', get_string('selectannualplan', 'local_annualplans'), $annual_plan_options);
        // Set default value from session if available, otherwise default to "All Plans" (empty string)
        if (isset($SESSION->annualplanid) && !empty($SESSION->annualplanid)) {
            $mform->setDefault('annualplanid', $SESSION->annualplanid);
        } else {
            $mform->setDefault('annualplanid', ''); // Default to "All Plans"
        }

        // Dropdown to select the course category
        $categories = $this->get_unique_categories_from_annual_plans();
        $categories = array('' => get_string('any', 'local_annualplans')) + $categories;
        $mform->addElement('select', 'category', get_string('category', 'local_annualplans'), $categories);
        // Set default value from session if available
        if (isset($SESSION->category) && !empty($SESSION->category)) {
            $mform->setDefault('category', $SESSION->category);
        } else {
            $mform->setDefault('category', '');
        }

        $levels = $this->get_unique_levels_from_annual_plans_course_level();
        $levels = array('' => get_string('any', 'local_annualplans')) + $levels;
        $mform->addElement('select', 'level', get_string('level', 'local_annualplans'), $levels);
        // Set default value from session if available
        if (isset($SESSION->level) && !empty($SESSION->level)) {
            $mform->setDefault('level', $SESSION->level);
        } else {
            $mform->setDefault('level', '');
        }



        // Text input for course name filter
        $mform->addElement('text', 'coursename', get_string('coursename', 'local_annualplans'));
        $mform->setType('coursename', PARAM_TEXT);
        // Set default value from session if available
        if (isset($SESSION->coursename) && !empty($SESSION->coursename)) {
            $mform->setDefault('coursename', $SESSION->coursename);
        }

        $mform->addElement('text', 'courseid', get_string('courseid', 'local_annualplans'));
        $mform->setType('courseid', PARAM_TEXT);
        // Set default value from session if available
        if (isset($SESSION->courseid) && !empty($SESSION->courseid)) {
            $mform->setDefault('courseid', $SESSION->courseid);
        }

        $mform->addElement('text', 'status', get_string('status', 'local_annualplans'));
        $mform->setType('status', PARAM_TEXT);
        // Set default value from session if available
        if (isset($SESSION->status) && !empty($SESSION->status)) {
            $mform->setDefault('status', $SESSION->status);
        }

        $mform->addElement('text', 'place', get_string('place', 'local_annualplans'));
        $mform->setType('place', PARAM_TEXT);
        // Set default value from session if available
        if (isset($SESSION->place) && !empty($SESSION->place)) {
            $mform->setDefault('place', $SESSION->place);
        }

        // Date filters - optional, leave empty to show all courses including those without dates
        $mform->addElement('date_selector', 'startdateinput', get_string('startdate', 'local_annualplans'), array('optional' => true));
        // Set default value from session if available
        if (isset($SESSION->startdateinput) && !empty($SESSION->startdateinput)) {
            $mform->setDefault('startdateinput', $SESSION->startdateinput);
        }
        
        $mform->addElement('date_selector', 'enddateinput', get_string('enddate', 'local_annualplans'), array('optional' => true));
        // Set default value from session if available
        if (isset($SESSION->enddateinput) && !empty($SESSION->enddateinput)) {
            $mform->setDefault('enddateinput', $SESSION->enddateinput);
        }
        
        // Filter button
        $mform->addElement('submit', 'filterbutton', get_string('applyfilters', 'local_annualplans'));
    }

    function get_unique_categories_from_annual_plans()
    {
        global $DB;
        $sql = "SELECT DISTINCT name FROM {course_categories}";
        $categories = $DB->get_records_sql($sql);
        $category_list = [];
        foreach ($categories as $category) {
            $category_list[$category->name] = $category->name;
        }
        return ['' => get_string('selectany', 'local_annualplans')] + $category_list;
    }
    function get_unique_levels_from_annual_plans_course_level()
    {
        global $DB;
        $sql = "SELECT DISTINCT name FROM {local_annual_plan_course_level}";
        $levels = $DB->get_records_sql($sql);
        $levels_list = [];
        foreach ($levels as $level) {
            $levels_list[$level->name] = $level->name;
        }
        return ['' => get_string('selectany', 'local_annualplans')] + $levels_list;
    }
}

/**
 * Form for managing course codes.
 */
class manage_course_codes_form extends moodleform
{
    public function definition()
    {
        global $DB;

        $mform = $this->_form;

        // Add a header for the "Manage Course Codes" form
        $mform->addElement('header', 'managecoursecodes', get_string('managecoursecodes', 'local_annualplans'));
        $mform->setExpanded('managecoursecodes', true);

        // Select the type of code
        $code_types = array(
            'category' => get_string('category', 'local_annualplans'),
            'grade' => get_string('grade', 'local_annualplans'),
            'course' => get_string('course', 'local_annualplans'),
            'targeted_group' => get_string('targeted_group', 'local_annualplans'),
            'group_number' => get_string('group_number', 'local_annualplans')
        );
        $mform->addElement('select', 'code_type', get_string('codetype', 'local_annualplans'), $code_types);
        $mform->setType('code_type', PARAM_TEXT);
        $mform->addRule('code_type', null, 'required', null, 'client');

        // JavaScript to show/hide the type_id select based on code_type selection
        // This code has been moved to manage_codes.php to avoid errors

        // Dynamic dropdown for type_id based on code_type
        // Will be populated via JavaScript
        $type_options = array('' => get_string('pleaseselecttype', 'local_annualplans'));
        
        // Add categories
        $categories = $DB->get_records_menu('course_categories', null, 'name ASC', 'id, name');
        foreach ($categories as $cat_id => $cat_name) {
            $type_options['category_' . $cat_id] = $cat_name;
        }
        
        // Add levels
        $levels = $DB->get_records_menu('local_annual_plan_course_level', null, 'name ASC', 'id, name');
        foreach ($levels as $level_id => $level_name) {
            $type_options['level_' . $level_id] = $level_name;
        }

        $mform->addElement('select', 'type_id', get_string('typeid', 'local_annualplans'), $type_options);
        $mform->setType('type_id', PARAM_TEXT);
        $mform->addRule('type_id', null, 'required', null, 'client');
        $mform->hideIf('type_id', 'code_type', 'eq', 'course');
        $mform->hideIf('type_id', 'code_type', 'eq', 'targeted_group');
        $mform->hideIf('type_id', 'code_type', 'eq', 'group_number');

        // Code field
        $mform->addElement('text', 'code', get_string('code', 'local_annualplans'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');
        $mform->addHelpButton('code', 'code', 'local_annualplans');
        
        // Description field
        $mform->addElement('textarea', 'description', get_string('description', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->addHelpButton('description', 'description', 'local_annualplans');

        // Submit button
        $mform->addElement('submit', 'addcodebutton', get_string('addcode', 'local_annualplans'));

        // Divider
        $mform->addElement('html', '<hr>');

        // List of existing codes with delete buttons
        $mform->addElement('header', 'existingcodes', get_string('existingcodes', 'local_annualplans'));
        $mform->setExpanded('existingcodes', true);

        $codes = $DB->get_records('local_annual_plan_course_codes', null, 'type, code');
        
        if (empty($codes)) {
            $mform->addElement('html', '<div class="alert alert-info">' . get_string('nocodesyet', 'local_annualplans') . '</div>');
        } else {
            $table = new html_table();
            $table->head = array(
                get_string('type', 'local_annualplans'),
                get_string('name', 'local_annualplans'),
                get_string('code', 'local_annualplans'),
                get_string('description', 'local_annualplans'),
                get_string('actions', 'local_annualplans')
            );
            $table->data = array();

            foreach ($codes as $code) {
                $type_name = '';
                if ($code->type == 'category') {
                    $category = $DB->get_record('course_categories', array('id' => $code->type_id), 'name');
                    $type_name = $category ? $category->name : get_string('unknown', 'local_annualplans');
                } else if ($code->type == 'level') {
                    $level = $DB->get_record('local_annual_plan_course_level', array('id' => $code->type_id), 'name');
                    $type_name = $level ? $level->name : get_string('unknown', 'local_annualplans');
                } else {
                    $type_name = get_string('course', 'local_annualplans');
                }

                $delete_url = new moodle_url('/local/annualplans/manage_codes.php', array('delete' => $code->id, 'sesskey' => sesskey()));
                $delete_link = html_writer::link($delete_url, get_string('delete'), array('class' => 'btn btn-danger btn-sm'));

                $description = (current_language() === 'ar' && !empty($code->description_ar)) ? $code->description_ar : $code->description_en;

                $table->data[] = array(
                    get_string($code->type, 'local_annualplans'),
                    $type_name,
                    $code->code_en,
                    $description,
                    $delete_link
                );
            }

            $mform->addElement('html', html_writer::table($table));
        }
    }

    public function process_data($data)
    {
        global $DB;

        if ($this->is_cancelled()) {
            return;
        } else if ($fromform = $this->get_data()) {
            error_log('Form data: ' . print_r($fromform, true));
            
            if (isset($fromform->addcodebutton)) {
                error_log('Add code button was clicked');
                $result = $this->add_new_code($fromform);
                
                if ($result) {
                    error_log('Successfully added code');
                    \core\notification::success(get_string('submission_course_success', 'local_annualplans'));
                } else {
                    error_log('Failed to add code');
                }
            } else {
                error_log('Add code button was NOT clicked');
            }
        } else {
            error_log('No form data received');
        }
    }

    /**
     * Add a new course code to the database.
     */
    private function add_new_code($data)
    {
        global $DB;

        $now = time();
        $new_code = new stdClass();
        $new_code->type = $data->code_type;
        $new_code->code_en = $data->code;
        $new_code->code_ar = $data->code_ar;
        $new_code->description_en = $data->description;
        $new_code->description_ar = $data->description_ar;
        $new_code->timecreated = $now;
        $new_code->timemodified = $now;
        
        error_log('Adding new code of type: ' . $data->code_type);
        error_log('Code: ' . $data->code);
        error_log('Description: ' . $data->description);

        // Handle the type_id based on code_type
        if ($data->code_type !== 'course' && $data->code_type !== 'targeted_group' && $data->code_type !== 'group_number') {
            list($type, $id) = explode('_', $data->type_id);
            $new_code->type_id = $id;
            error_log('Non-course type, type_id: ' . $id);
            
            // Check if this category or level already has any code (enforce one code per category/level)
            $existing_type = $DB->get_records('local_annual_plan_course_codes', [
                'type' => $data->code_type,
                'type_id' => $new_code->type_id
            ]);
            
            if (!empty($existing_type)) {
                error_log($data->code_type . ' already has a code assigned');
                \core\notification::error(get_string($data->code_type . 'hascodealready', 'local_annualplans'));
                return false;
            }
            
            // Check for duplicate codes (for consistency)
            $params = [
                'type' => $new_code->type,
                'code_en' => $new_code->code_en
            ];
            $existing_code = $DB->get_record('local_annual_plan_course_codes', $params);
            
            if (!$existing_code) {
                $id = $DB->insert_record('local_annual_plan_course_codes', $new_code);
                error_log('Successfully inserted non-course code with ID: ' . $id);
                return true;
            } else {
                error_log('Duplicate non-course code found');
                \core\notification::error(get_string('codeexists', 'local_annualplans'));
                return false;
            }
        } else {
            // For course type and other types without type_id, explicitly set type_id to NULL
            $new_code->type_id = null;
            error_log($data->code_type . ' type, type_id: NULL');
            
            // For course type and other types without type_id, need to check that type_id is NULL
            $existing_code = $DB->get_records_select(
                'local_annual_plan_course_codes', 
                "type = :type AND code_en = :code_en AND type_id IS NULL",
                ['type' => $new_code->type, 'code_en' => $new_code->code_en]
            );
            
            if (empty($existing_code)) {
                $id = $DB->insert_record('local_annual_plan_course_codes', $new_code);
                error_log('Successfully inserted ' . $data->code_type . ' code with ID: ' . $id);
                return true;
            } else {
                error_log('Duplicate ' . $data->code_type . ' code found');
                \core\notification::error(get_string('codeexists', 'local_annualplans'));
                return false;
            }
        }
    }

    /**
     * Delete a course code from the database.
     */
    public function delete_code($code_id)
    {
        global $DB;
        return $DB->delete_records('local_annual_plan_course_codes', array('id' => $code_id));
    }
}

/**
 * Form specifically for adding category codes
 */
class CategoryCodeForm extends moodleform {
    public function definition() {
        global $DB;
        
        $mform = $this->_form;
        
        // Add a header
        $mform->addElement('header', 'categorycodeheader', get_string('categorycode', 'local_annualplans'));
        $mform->setExpanded('categorycodeheader', true);
        
        // Category selector
        $categories = $DB->get_records_menu('course_categories', null, 'name ASC', 'id, name');
        $mform->addElement('select', 'category_id', get_string('category', 'local_annualplans'), $categories);
        $mform->setType('category_id', PARAM_INT);
        $mform->addRule('category_id', null, 'required', null, 'client');
        
        // Code field
        $mform->addElement('text', 'code', get_string('code', 'local_annualplans'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');
        $mform->addHelpButton('code', 'code', 'local_annualplans');
        
        // Description field
        $mform->addElement('textarea', 'description', get_string('description', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->addHelpButton('description', 'description', 'local_annualplans');

        // Arabic Code field
        $mform->addElement('text', 'code_ar', get_string('code_ar', 'local_annualplans'));
        $mform->setType('code_ar', PARAM_TEXT);
        $mform->addRule('code_ar', null, 'required', null, 'client');
        $mform->addHelpButton('code_ar', 'code_ar', 'local_annualplans');

        // Arabic Description field
        $mform->addElement('textarea', 'description_ar', get_string('description_ar', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description_ar', PARAM_TEXT);
        $mform->addRule('description_ar', null, 'required', null, 'client');
        $mform->addHelpButton('description_ar', 'description_ar', 'local_annualplans');


        
        // Add hidden field for code type
        $mform->addElement('hidden', 'type', 'category');
        $mform->setType('type', PARAM_TEXT);
        
        // Submit button
        $mform->addElement('submit', 'submitbutton', get_string('addcategorycode', 'local_annualplans'));
    }
    
    /**
     * Process the form data to add a category code
     */
    public function process_data($data) {
        global $DB;
        
        $now = time();
        $new_code = new stdClass();
        $new_code->type = 'category';
        $new_code->code_en = $data->code;
        $new_code->code_ar = $data->code_ar;
        $new_code->description_en = $data->description;
        $new_code->description_ar = $data->description_ar;
        $new_code->type_id = $data->category_id;
        $new_code->timecreated = $now;
        $new_code->timemodified = $now;
        
        // Check if this category already has any code (enforcing one code per category)
        $existing_category = $DB->get_records('local_annual_plan_course_codes', [
            'type' => 'category',
            'type_id' => $data->category_id
        ]);
        
        if (!empty($existing_category)) {
            return false; // Category already has a code
        }
        
        // Also check for duplicates of the same code (for consistency)
        $existing_code = $DB->get_records('local_annual_plan_course_codes', [
            'type' => 'category',
            'code_en' => $data->code
        ]);
        
        if (empty($existing_code)) {
            $id = $DB->insert_record('local_annual_plan_course_codes', $new_code);
            if ($id) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * Form specifically for adding course grade codes
 */
class CourseGradeCodeForm extends moodleform {
    public function definition() {
        global $DB;
        
        $mform = $this->_form;
        
        // Add a header
        $mform->addElement('header', 'courseGradecodeheader', get_string('CourseGradeCode', 'local_annualplans'));
        $mform->setExpanded('courseGradecodeheader', true);
        
        // Code field (no longer associated with a specific level)
        $mform->addElement('text', 'code', get_string('code', 'local_annualplans'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');
        $mform->addHelpButton('code', 'code', 'local_annualplans');
        
        // Description field
        $mform->addElement('textarea', 'description', get_string('description', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->addHelpButton('description', 'description', 'local_annualplans');

        // Arabic Code field
        $mform->addElement('text', 'code_ar', get_string('code_ar', 'local_annualplans'));
        $mform->setType('code_ar', PARAM_TEXT);
        $mform->addRule('code_ar', null, 'required', null, 'client');
        $mform->addHelpButton('code_ar', 'code_ar', 'local_annualplans');

        // Arabic Description field
        $mform->addElement('textarea', 'description_ar', get_string('description_ar', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description_ar', PARAM_TEXT);
        $mform->addRule('description_ar', null, 'required', null, 'client');
        $mform->addHelpButton('description_ar', 'description_ar', 'local_annualplans');
        
        // Add hidden field for code type
        $mform->addElement('hidden', 'type', 'grade');
        $mform->setType('type', PARAM_TEXT);
        
        // Submit button
        $mform->addElement('submit', 'submitbutton', get_string('addCourseGradecode', 'local_annualplans'));
    }
    
    /**
     * Process the form data to add a course grade code
     */
    public function process_data($data) {
        global $DB;
        
        $now = time();
        $new_code = new stdClass();
        $new_code->type = 'grade';
        $new_code->code_en = $data->code;
        $new_code->code_ar = $data->code_ar;
        $new_code->description_en = $data->description;
        $new_code->description_ar = $data->description_ar;
        $new_code->type_id = null; // Now set to null like the other standalone code types
        $new_code->timecreated = $now;
        $new_code->timemodified = $now;
        
        // Insert record without checking for duplicates
        $id = $DB->insert_record('local_annual_plan_course_codes', $new_code);
        if ($id) {
            return true;
        }
        
        return false;
    }
}

/**
 * Form specifically for adding course codes
 */
class CourseCodeForm extends moodleform {
    public function definition() {
        global $DB;
        
        $mform = $this->_form;
        
        // Add a header
        $mform->addElement('header', 'coursecodeheader', get_string('coursecode', 'local_annualplans'));
        $mform->setExpanded('coursecodeheader', true);
        
        // Code field
        $mform->addElement('text', 'code', get_string('code', 'local_annualplans'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');
        $mform->addHelpButton('code', 'code', 'local_annualplans');
        
        // Description field
        $mform->addElement('textarea', 'description', get_string('description', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->addHelpButton('description', 'description', 'local_annualplans');

        // Arabic Code field
        $mform->addElement('text', 'code_ar', get_string('code_ar', 'local_annualplans'));
        $mform->setType('code_ar', PARAM_TEXT);
        $mform->addRule('code_ar', null, 'required', null, 'client');
        $mform->addHelpButton('code_ar', 'code_ar', 'local_annualplans');

        // Arabic Description field
        $mform->addElement('textarea', 'description_ar', get_string('description_ar', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description_ar', PARAM_TEXT);
        $mform->addRule('description_ar', null, 'required', null, 'client');
        $mform->addHelpButton('description_ar', 'description_ar', 'local_annualplans');
        
        // Add hidden field for code type
        $mform->addElement('hidden', 'type', 'course');
        $mform->setType('type', PARAM_TEXT);
        
        // Submit button
        $mform->addElement('submit', 'submitbutton', get_string('addcoursecode', 'local_annualplans'));
    }
    
    /**
     * Process the form data to add a course code
     */
    public function process_data($data) {
        global $DB;
        
        $now = time();
        $new_code = new stdClass();
        $new_code->type = 'course';
        $new_code->code_en = $data->code;
        $new_code->code_ar = $data->code_ar;
        $new_code->description_en = $data->description;
        $new_code->description_ar = $data->description_ar;
        $new_code->type_id = null; // Explicitly null for course codes
        $new_code->timecreated = $now;
        $new_code->timemodified = $now;
        
        // Insert record without checking for duplicates
        $id = $DB->insert_record('local_annual_plan_course_codes', $new_code);
        if ($id) {
            return true;
        }
        
        return false;
    }
}

/**
 * Form specifically for adding targeted group codes
 */
class TargetedGroupCodeForm extends moodleform {
    public function definition() {
        global $DB;
        
        $mform = $this->_form;
        
        // Add a header
        $mform->addElement('header', 'targetedgroupheader', get_string('targetedgroupcode', 'local_annualplans'));
        $mform->setExpanded('targetedgroupheader', true);
        
        // Code field
        $mform->addElement('text', 'code', get_string('code', 'local_annualplans'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');
        $mform->addHelpButton('code', 'code', 'local_annualplans');
        
        // Description field
        $mform->addElement('textarea', 'description', get_string('description', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->addHelpButton('description', 'description', 'local_annualplans');

        // Arabic Code field
        $mform->addElement('text', 'code_ar', get_string('code_ar', 'local_annualplans'));
        $mform->setType('code_ar', PARAM_TEXT);
        $mform->addRule('code_ar', null, 'required', null, 'client');
        $mform->addHelpButton('code_ar', 'code_ar', 'local_annualplans');

        // Arabic Description field
        $mform->addElement('textarea', 'description_ar', get_string('description_ar', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description_ar', PARAM_TEXT);
        $mform->addRule('description_ar', null, 'required', null, 'client');
        $mform->addHelpButton('description_ar', 'description_ar', 'local_annualplans');
        
        // Add hidden field for code type
        $mform->addElement('hidden', 'type', 'targeted_group');
        $mform->setType('type', PARAM_TEXT);
        
        // Submit button
        $mform->addElement('submit', 'submitbutton', get_string('addtargetedgroupcode', 'local_annualplans'));
    }
    
    /**
     * Process the form data to add a targeted group code
     */
    public function process_data($data) {
        global $DB;
        
        $now = time();
        $new_code = new stdClass();
        $new_code->type = 'targeted_group';
        $new_code->code_en = $data->code;
        $new_code->code_ar = $data->code_ar;
        $new_code->description_en = $data->description;
        $new_code->description_ar = $data->description_ar;
        $new_code->type_id = null; // Explicitly null for targeted group codes
        $new_code->timecreated = $now;
        $new_code->timemodified = $now;
        
        // Insert record without checking for duplicates
        $id = $DB->insert_record('local_annual_plan_course_codes', $new_code);
        if ($id) {
            return true;
        }
        
        return false;
    }
}

/**
 * Form specifically for adding group number codes
 */
class GroupNumberCodeForm extends moodleform {
    public function definition() {
        global $DB;
        
        $mform = $this->_form;
        
        // Add a header
        $mform->addElement('header', 'groupnumberheader', get_string('groupnumbercode', 'local_annualplans'));
        $mform->setExpanded('groupnumberheader', true);
        
        // Code field
        $mform->addElement('text', 'code', get_string('code', 'local_annualplans'));
        $mform->setType('code', PARAM_TEXT);
        $mform->addRule('code', null, 'required', null, 'client');
        $mform->addHelpButton('code', 'code', 'local_annualplans');
        
        // Description field
        $mform->addElement('textarea', 'description', get_string('description', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->addHelpButton('description', 'description', 'local_annualplans');

        // Arabic Code field
        $mform->addElement('text', 'code_ar', get_string('code_ar', 'local_annualplans'));
        $mform->setType('code_ar', PARAM_TEXT);
        $mform->addRule('code_ar', null, 'required', null, 'client');
        $mform->addHelpButton('code_ar', 'code_ar', 'local_annualplans');

        // Arabic Description field
        $mform->addElement('textarea', 'description_ar', get_string('description_ar', 'local_annualplans'), array('rows' => 4, 'cols' => 50));
        $mform->setType('description_ar', PARAM_TEXT);
        $mform->addRule('description_ar', null, 'required', null, 'client');
        $mform->addHelpButton('description_ar', 'description_ar', 'local_annualplans');
        
        // Add hidden field for code type
        $mform->addElement('hidden', 'type', 'group_number');
        $mform->setType('type', PARAM_TEXT);
        
        // Submit button
        $mform->addElement('submit', 'submitbutton', get_string('addgroupnumbercode', 'local_annualplans'));
    }
    
    /**
     * Process the form data to add a group number code
     */
    public function process_data($data) {
        global $DB;
        
        $now = time();
        $new_code = new stdClass();
        $new_code->type = 'group_number';
        $new_code->code_en = $data->code;
        $new_code->code_ar = $data->code_ar;
        $new_code->description_en = $data->description;
        $new_code->description_ar = $data->description_ar;
        $new_code->type_id = null; // Explicitly null for group number codes
        $new_code->timecreated = $now;
        $new_code->timemodified = $now;
        
        // Insert record without checking for duplicates
        $id = $DB->insert_record('local_annual_plan_course_codes', $new_code);
        if ($id) {
            return true;
        }
        
        return false;
    }
}

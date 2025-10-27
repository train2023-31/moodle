<?php
class AnnualPlansController
{
    private $upload_form;
    private $filter_form;
    private $annual_plan_delete_form;
    private $CourseManager;
    private $add_course_form;
    //private $manage_levels_form;
    private $context;
    private $PAGE;
    private $SESSION;
    private $OUTPUT;
    private $DB;
    private $USER;


    public function __construct()
    {
        global $PAGE, $SESSION, $OUTPUT, $DB, $USER;
        $this->PAGE = $PAGE;
        $this->SESSION = $SESSION;
        $this->OUTPUT = $OUTPUT;
        $this->DB = $DB;
        $this->USER = $USER;
        $this->context = context_system::instance();

        // Set page title and heading - moved here so context is available
        $PAGE->set_title(get_string('pluginname', 'local_annualplans'));
        $PAGE->set_heading(get_string('pluginname', 'local_annualplans'));

        $this->upload_form = new upload_form();
        $this->filter_form = new filter_form('', [], 'post', '', ['class' => 'custom-filter-form']);
        $this->add_course_form = new add_course_form();
        $this->annual_plan_delete_form = new annual_plan_delete_form();
        $this->CourseManager = new CourseManager();
    }

    public function handle_request()
    {
        global $PAGE;
        // Get the 'tab' parameter to determine which tab is active.

        // Handle form submissions and actions.
        $this->process_forms();

        //handle delete course from annual plan course
        $this->delete_course_row();

        // Handle 'Delete Course from moodle course' action.
        //$this->handle_add_delete_course();

        // Display the page.
        $this->display_page();
    }

    private function process_forms()
    {
        if ($this->add_course_form->is_cancelled()) {
            redirect(new moodle_url("/local/annualplans/index.php"));
        } elseif ($data = $this->add_course_form->get_data()) {
            $this->add_course_form->process_data($data);
        }

        if ($this->upload_form->is_cancelled()) {
            redirect(new moodle_url("/local/annualplans/index.php"));
        } elseif ($data = $this->upload_form->get_data()) {
            // Use process_data() method to handle the upload and insertion logic
            $this->upload_form->process_data($data);
        }
        if ($this->annual_plan_delete_form->is_cancelled()) {
            redirect(new moodle_url("/local/annualplans/index.php"));
        } elseif ($data = $this->annual_plan_delete_form->get_data()) {

            // Use process_data() method to handle the upload and insertion logic
            $this->annual_plan_delete_form->process_data($data);
        }

        $filterparams = new stdClass();
        if ($this->filter_form->is_cancelled()) {
            redirect(new moodle_url('/local/annualplans/index.php', array('tab' => 'table')));
        } elseif ($filter_data = $this->filter_form->get_data()) {

            $this->SESSION->annualplanid = $filter_data->annualplanid;
            $this->SESSION->category = $filter_data->category;
            $this->SESSION->level = $filter_data->level;
            $this->SESSION->coursename = $filter_data->coursename;
            $this->SESSION->courseid = $filter_data->courseid;
            $this->SESSION->status = $filter_data->status;
            // $this->SESSION->coursedate = $filter_data->coursedate;
            // Fix: Only set coursedate if it exists in the filter data
            $this->SESSION->coursedate = property_exists($filter_data, 'coursedate') ? $filter_data->coursedate : null;
            $this->SESSION->place = $filter_data->place;
            $this->SESSION->approve = isset($filter_data->approve) ? 1 : 0;
            $this->SESSION->notapprove = isset($filter_data->notapprove) ? 0 : 0;

            $filterparams->annualplanid = $this->SESSION->annualplanid;
            $filterparams->category = $this->SESSION->category;
            $filterparams->level = $this->SESSION->level;
            $filterparams->coursename = $this->SESSION->coursename;
            $filterparams->courseid = $this->SESSION->courseid;
            $filterparams->status = $this->SESSION->status;
            $filterparams->coursedate = $this->SESSION->coursedate;
            $filterparams->place = $this->SESSION->place;
            $filterparams->notapprove = $this->SESSION->notapprove;

            $this->SESSION->filterparams = $filter_data;

            redirect(new moodle_url('/local/annualplans/index.php', array('tab' => 'table')));
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["formaction"]) && $_POST["formaction"] == "update_category") {
            require_sesskey(); // Security check

            $courseid = required_param("courseid", PARAM_RAW);
            $coursedate = required_param("coursedate", PARAM_INT);
            $categoryid = required_param("category", PARAM_INT);

            // Update the category in the database
            $this->update_course_category($courseid, $coursedate, $categoryid);
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["formaction"]) && $_POST["formaction"] == "update_level") {
            require_sesskey(); // Security check

            $courseid = required_param("courseid", PARAM_RAW);
            $coursedate = required_param("coursedate", PARAM_INT);
            $courselevelid = required_param("courselevelid", PARAM_INT);

            // Update the course level in the database
            $this->update_course_level($courseid, $coursedate, $courselevelid);
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["formaction"]) && $_POST["formaction"] == "update_place") {
            require_sesskey(); // Security check

            $courseid = required_param("courseid", PARAM_RAW);
            $coursedate = required_param("coursedate", PARAM_INT);
            $annualplanid = required_param("annualplanid", PARAM_INT);
            $place  = required_param("place", PARAM_TEXT);

            // Update the course place in the database
            $this->update_course_place($courseid, $coursedate, $annualplanid, $place);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["formaction"])) {
            require_sesskey(); // Ensure the request is valid

            $courseid = required_param("courseid", PARAM_RAW);
            $coursedate = required_param("coursedate", PARAM_INT);



            if ($_POST["formaction"] == "add_course") {
                $this->CourseManager->add_course($_POST);
                \core\notification::success(
                    get_string("courseapproved", "local_annualplans")
                );

                redirect(new moodle_url('/local/annualplans/index.php', array('tab' => 'table')));
            } elseif ($_POST["formaction"] == "delete_course") {
                $annualplanid = required_param('annualplanid', PARAM_INT);
                $unapprove_note = optional_param('unapprove_note', '', PARAM_TEXT);
                if ($unapprove_note !== '') {
                    $this->DB->set_field('local_annual_plan_course', 'unapprove_note', $unapprove_note, [
                        "courseid" => $courseid,
                        "coursedate" => $coursedate,
                        "annualplanid" => $annualplanid,
                    ]);
                }

                // Delete the course logic
                $this->CourseManager->delete_course($_POST);
                \core\notification::success(
                    get_string("coursenotapproved", "local_annualplans")
                );
            }
            // Redirect to avoid form re-submission issues
            redirect(new moodle_url('/local/annualplans/index.php', array('tab' => 'table')));
        }
    }

    //delete course from annual plan course table
    private function delete_course_row()
    {
        error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            error_log("POST data: " . print_r($_POST, true));
        }
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_course_row"]) && isset($_POST["courseid"]) && isset($_POST["coursedate"]) && isset($_POST["deletion_note"])) {

            $courseid = required_param("courseid", PARAM_RAW);
            $coursedate = required_param("coursedate", PARAM_INT);
            $deletion_note = required_param('deletion_note', PARAM_TEXT);
            $annualplanid = required_param('annualplanid', PARAM_INT);
            // Ensure the user has the necessary permissions
            require_capability("local/annualplans:manage", $this->context);

            // Check if the course is already added to Moodle
            $shortname = $courseid;
            $existing_course_in_moodle = $this->DB->get_record('course', ['shortname' => $shortname]);

            if ($existing_course_in_moodle) {
                // Display an error message and redirect
                \core\notification::error(get_string('cannotdeleteaddedcourse', 'local_annualplans'));
                redirect(new moodle_url('/local/annualplans/index.php', array('tab' => 'table')));
            }

            // Perform the deletion
            $this->DB->set_field("local_annual_plan_course", "disabled", 1, [
                "courseid" => $courseid,
                "coursedate" => $coursedate,
            ]);
            if (!empty($deletion_note)) {
                $this->DB->set_field('local_annual_plan_course', 'deletion_note', $deletion_note, [
                    "courseid" => $courseid,
                    "coursedate" => $coursedate,
                    "annualplanid" => $annualplanid,
                ]);
            } else {
                // Handle the case where deletion_note is empty
                // Redirect with an error message
                redirect(
                    new moodle_url('/local/annualplans/index.php', array('tab' => 'table')),
                    get_string('deletenotecomment', 'local_annualplans'),
                    null,
                    \core\output\notification::NOTIFY_ERROR
                );
            }

            // Redirect with success message
            redirect(
                new moodle_url('/local/annualplans/index.php', array('tab' => 'table')),
                get_string('courseDeleted', 'local_annualplans'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        }
    }

    private function display_page()
    {
        $tab = optional_param('tab', 'annualplanforms', PARAM_ALPHA);

        // Create the tabs.
        $tabs = array();
        $tabs[] = new tabobject(
            'annualplanforms',
            new moodle_url('/local/annualplans/index.php', array('tab' => 'annualplanforms')),
            get_string('addnewannualplan', 'local_annualplans')
        );
        $tabs[] = new tabobject(
            'table',
            new moodle_url('/local/annualplans/index.php', array('tab' => 'table')),
            get_string('manage', 'local_annualplans')
        );
        echo $this->OUTPUT->header();
        echo $this->OUTPUT->tabtree($tabs, $tab);


        if ($tab == "annualplanforms") {
            $this->upload_form->display();
            $this->annual_plan_delete_form->display();
        } elseif ($tab == "table") {
            $this->filter_form->display();




            // Prepare data for the template
            $data = $this->prepare_template_data();

            // Render the template
            echo $this->OUTPUT->render_from_template(
                "local_annualplans/courses_table",
                $data
            );
        }


        echo $this->OUTPUT->footer();
    }

    //// updating course categories
    private function update_course_category($courseid, $coursedate, $categoryid)
    {
        global $DB;
        $category = $DB->get_record(
            "course_categories",
            ["id" => $categoryid],
            "name",
            MUST_EXIST
        );
        $categoryname = $category->name;
        // Optional: Check user capabilities
        require_capability("local/annualplans:manage", $this->context);

        // Update the category in the database
        $DB->set_field("local_annual_plan_course", "category", $categoryname, [
            "courseid" => $courseid,
            "coursedate" => $coursedate,
        ]);
    }
    ///// updating coures levels
    private function update_course_level($courseid, $coursedate, $courselevelid)
    {
        global $DB;

        // Retrieve the course level name
        $courselevel = $DB->get_record(
            "local_annual_plan_course_level",
            ["id" => $courselevelid],
            "name",
            MUST_EXIST
        );

        $courselevelname = $courselevel->name;

        // Optional: Check user capabilities
        require_capability("local/annualplans:manage", $this->context);

        // Update the course level in the database
        $DB->set_field(
            "local_annual_plan_course",
            "courselevelid",
            $courselevelid,
            [
                "courseid" => $courseid,
                "coursedate" => $coursedate,
            ]
        );
    }

    //updating the course place
    private function update_course_place($courseid, $coursedate, $annualplanid, $place)
    {
        global $DB;
        require_capability("local/annualplans:manage", $this->context);

        // Update the category in the database
        $DB->set_field("local_annual_plan_course", "place", $place, [
            "courseid" => $courseid,
            "coursedate" => $coursedate,
            "annualplanid" => $annualplanid,
        ]);
    }
    private function prepare_template_data()
    {
        global $SESSION, $DB, $USER;

        $data = [];

        // Fetch categories and course levels
        //categories is the trainig departments
        $categories = $DB->get_records_menu(
            "course_categories",
            null,
            "",
            "id, description"
        );
        // Get the current user's language (e.g., 'en' for English, 'ar' for Arabic)
        $language = current_language();
        // Fetch all course levels with both English and Arabic descriptions
        $course_levels_records = $DB->get_records("local_annual_plan_course_level", null, "", "id, description_en, description_ar");
        $course_levels = [];
        foreach ($course_levels_records as $id => $level) {
            // Use English description if language is English and it's not empty, otherwise default to Arabic
            $course_levels[$id] = ($language === 'en' && !empty($level->description_en)) ? $level->description_en : $level->description_ar;
        }

        // Fetch status list for annual plan workflow (type_id = 7), supporting both languages
        $status_records = $DB->get_records('local_status', ['type_id' => 7], 'seq ASC');
        $status_list = [];
        foreach ($status_records as $status) {
            $status_list[] = [
                'id' => $status->id,
                'display_name_en' => $status->display_name_en,
                'display_name_ar' => $status->display_name_ar
            ];
        }

        $table = new annual_plan_table(
            "uniqueid_annualplans",
            $SESSION->filterparams ?? null
        );
        $table->query_db(600, true);

        $courses = [];
        $displayed_course_ids = [];

        foreach ($table->rawdata as $row) {
            $shortname = $row->courseid;

            // Skip duplicate course IDs
            if (in_array($shortname, $displayed_course_ids)) {
                continue;
            }

            $displayed_course_ids[] = $shortname;

            // Prepare categories as array entries, including "Not Defined"
            $categories_list = [
                [
                    "id" => 0, // Assuming 0 represents "Not Defined"
                    "name" => get_string('categorynotspecified', 'local_annualplans'),
                    "selected" => empty($row->category), // Select this if $row->category is empty
                ],
            ];

            foreach ($categories as $id => $description) {
                // Get the category name for comparison with stored category name
                $category_name = $DB->get_field('course_categories', 'name', ['id' => $id]);
                $categories_list[] = [
                    "id" => $id,
                    "name" => $description,
                    "selected" => $category_name == $row->category, // Compare with stored category name
                ];
            }

            // Prepare course levels as array entries
            $levels_list = [
                [
                    "id" => 0, // Assuming 0 represents "Not Defined"
                    "name" => get_string('levelnotspecified', 'local_annualplans'),
                    "selected" => empty($row->courselevelid), // Select this if $row->courselevelid is empty
                ],
            ];
            foreach ($course_levels as $id => $name) {
                $levels_list[] = [
                    "id" => $id,
                    "name" => $name,
                    "selected" => $id == $row->courselevelid, // Determine if this level is selected
                ];
            }

            // Get the actual count of beneficiaries for this course
            $beneficiaries_count = $DB->count_records('local_annual_plan_beneficiaries', [
                'courseid' => $row->courseid,
                'coursedate' => $row->coursedate,
                'annualplanid' => $row->annualplanid
            ]);
            
            // Ensure numberofbeneficiaries is always a number, not a PF number
            $num_beneficiaries = is_numeric($row->numberofbeneficiaries) ? (int)$row->numberofbeneficiaries : $beneficiaries_count;
            
            // If there's a mismatch, update the database record
            if ($num_beneficiaries != $beneficiaries_count) {
                $DB->set_field('local_annual_plan_course', 'numberofbeneficiaries', $beneficiaries_count, [
                    'courseid' => $row->courseid,
                    'coursedate' => $row->coursedate,
                    'annualplanid' => $row->annualplanid
                ]);
                $num_beneficiaries = $beneficiaries_count;
            }

            // Get category description based on the stored category name
            $category_description = $row->category; // Default to name if description not found
            if (!empty($row->category)) {
                $category_record = $DB->get_record('course_categories', ['name' => $row->category], 'description');
                if ($category_record && !empty($category_record->description)) {
                    $category_description = $category_record->description;
                }
            }

            $course_data = [
                "annualplanid" => $row->annualplanid,
                "annualplantitle" => $row->annualplantitle,
                "courseid" => $row->courseid,
                "coursename" => $row->coursename,
                "category" => $category_description,
                "categories" => $categories_list,
                "status_list" => $status_list,
                "coursedate" => $row->coursedate,
                "coursedate_formatted" => $row->coursedate, // duration in days
                "numberofbeneficiaries" => $num_beneficiaries, // Ensure this is always a number
                "status" => $row->status,
                "plan_title" => $row->annualplantitle,
                "levels_list" => $levels_list, // Add levels list to course data
                "selected_level_name" => isset(
                    $course_levels[$row->courselevelid]
                )
                    ? $course_levels[$row->courselevelid]
                    : "",
            ];

            // Fetch the user who approved the course
            $user = $DB->get_record("user", ["id" => $row->userid]);

            // Fetch users associated with the course
            $users_records = $DB->get_records("local_annual_plan_user", [
                "courseid" => $row->courseid,
                "annualplanid" => $row->annualplanid,
            ]);

            $userlist = array_map(function ($user_record) {
                return [
                    "userid" => $user_record->userid,
                    "fullname" => $user_record->fullname,
                ];
            }, $users_records);

            $userlist = array_values($userlist);

            $course_data["has_users"] = !empty($userlist);
            $course_data["userlist"] = $userlist;
            // Generate unique IDs for modal and button
            $modal_id = "modal_" . $row->courseid . "-" . $row->coursedate;
            $button_id = "button_" . $row->courseid . "-" . $row->coursedate;

            $course_data["modal_id"] = $modal_id;
            $course_data["button_id"] = $button_id;
            $course_data["sesskey"] = sesskey();
            // Check if the course is already in Moodle
            $existing_course_in_moodle = $DB->get_record("course", [
                "shortname" => $shortname,
            ]);


            $place_record = $DB->get_record('local_annual_plan_course', [
                'courseid' => $row->courseid,
                "coursedate" => $row->coursedate,
                "annualplanid" => $row->annualplanid,

            ], 'place'); // Specify 'place' as the fields to retrieve

            $course_data["place"] = $place_record ? $place_record->place : '';

            if (!($existing_course_in_moodle)) {
                $DB->set_field("local_annual_plan_course", "approve", 0, [
                    "courseid" => $row->courseid,
                    "coursedate" => $row->coursedate,
                ]);
                $course_data["is_added_course"] = false;
                $course_data["unapproved_by"] = !empty($user)
                    ? fullname($user)
                    : get_string("unknownuser", "local_annualplans");
            } else {
                $course_data["is_added_course"] = true;
            }




            if ($existing_course_in_moodle) {
                $DB->set_field("local_annual_plan_course", "approve", 1, [
                    "courseid" => $row->courseid,
                    "coursedate" => $row->coursedate,
                ]);
                $course_data["is_added_course"] = true;
                $course_data["approved_by"] = !empty($user)
                    ? fullname($user)
                    : get_string("unknownuser", "local_annualplans");
            } else {
                $course_data["is_added_course"] = false;
            }

            $courses[] = $course_data;
        }

        $data["courses"] = $courses;

        // Add AJAX URLs for the beneficiaries functionality
        global $CFG;
        $data["ajax_urls"] = [
            "get_employees" => $CFG->wwwroot . "/local/annualplans/ajax/get_employees.php",
            "get_beneficiaries" => $CFG->wwwroot . "/local/annualplans/ajax/get_beneficiaries.php", 
            "save_beneficiaries" => $CFG->wwwroot . "/local/annualplans/ajax/save_beneficiaries.php"
        ];

        return $data;
    }
}

<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/annualplans/classes/table.php');
require_once($CFG->dirroot . '/local/annualplans/classes/control_form.php');

$PAGE->set_url(new moodle_url('/local/annualplans/approvedDisplay.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('displayapprovedcourses', 'local_annualplans'));
$PAGE->set_heading(get_string('displayapprovedcourses', 'local_annualplans'));

global $SESSION, $DB, $USER, $OUTPUT, $CFG;

require_login();
require_capability('moodle/site:config', context_system::instance());

$filter_approved_form = new filter_approved_form('', [], 'post', '', ['class' => 'custom-filter-form']);

$filterparams = new stdClass();
if ($filter_approved_form->is_cancelled()) {
    redirect(new moodle_url("/local/annualplans/index.php"));
} elseif ($filter_data = $filter_approved_form->get_data()) {

    $SESSION->annualplanid = $filter_data->annualplanid;
    $SESSION->category = $filter_data->category;
    $SESSION->level = $filter_data->level;
    $SESSION->coursename = $filter_data->coursename;
    $SESSION->courseid = $filter_data->courseid;
    $SESSION->status = $filter_data->status;
    $SESSION->place = $filter_data->place;
    $SESSION->startdateinput = isset($filter_data->startdateinput) ? $filter_data->startdateinput : 0;
    $SESSION->enddateinput = isset($filter_data->enddateinput) ? $filter_data->enddateinput : 0;

    $filterparams->annualplanid = $SESSION->annualplanid;
    $filterparams->category = $SESSION->category;
    $filterparams->level = $SESSION->level;
    $filterparams->coursename = $SESSION->coursename;
    $filterparams->courseid = $SESSION->courseid;
    $filterparams->status = $SESSION->status;
    $filterparams->place = $SESSION->place;
    $filterparams->startdateinput = $SESSION->startdateinput;
    $filterparams->enddateinput = $SESSION->enddateinput;

    $SESSION->filterparams = $filter_data;
} else {
    // Initialize default filter parameters on first load
    if (!isset($SESSION->filterparams)) {
        $default_filter = new stdClass();
        $default_filter->annualplanid = ''; // Default to "All Plans"
        $default_filter->category = '';
        $default_filter->level = '';
        $default_filter->coursename = '';
        $default_filter->courseid = '';
        $default_filter->status = '';
        $default_filter->place = '';
        $default_filter->startdateinput = 0; // No date filtering by default
        $default_filter->enddateinput = 0; // No date filtering by default
        
        $SESSION->filterparams = $default_filter;
        
        // Also set individual session variables for form defaults
        $SESSION->annualplanid = '';
        $SESSION->category = '';
        $SESSION->level = '';
        $SESSION->coursename = '';
        $SESSION->courseid = '';
        $SESSION->status = '';
        $SESSION->place = '';
        $SESSION->startdateinput = 0; // No date filtering by default
        $SESSION->enddateinput = 0; // No date filtering by default
    }
}


function prepare_template_data()
{
    global $SESSION, $DB, $USER;

    $data = [];

    // Fetch categories and course levels
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
    $table = new annual_plan_table(
        "uniqueid_annualplans",
        $SESSION->filterparams ?? null
    );
    $table->query_db_aprroved(600, true);

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

        $categoryid = $row->category;
        $category = $DB->get_record('course_categories', array('id' => $categoryid), 'description');

        // Prepare categories as array entries
        $categories_list = [
            [
                "id" => 0, // Assuming 0 represents "Not Defined"
                "name" => get_string('levelnotspecified', 'local_annualplans'),
                "selected" => empty($row->category), // Select this if $row->category is empty
            ],
        ];
        foreach ($categories as $id => $description) {
            $categories_list[] = [
                "id" => $id,
                "name" => $description,
                "selected" => $id == $row->category, // Determine if this category is selected
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

        $course_data = [
            "annualplantitle" => $row->annualplantitle,
            "courseid" => $row->courseid,
            "coursename" => $row->coursename,
            "category" => $category->description,
            "categories" => $categories_list,
            "coursedate" => $row->coursedate,
            "numberofbeneficiaries" => $row->numberofbeneficiaries,
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

        $course_data["id"] = $DB->get_field('course', 'id', array('shortname' => $shortname));
        
        // Get start and end dates from Moodle course if available
        $course_dates = $DB->get_record('course', array('shortname' => $shortname), 'startdate, enddate');
        
        if ($course_dates && !empty($course_dates->startdate)) {
            $course_data["startdate"] = date("d-m-Y", $course_dates->startdate);
        } else {
            $course_data["startdate"] = get_string('startdatenotspecified', 'local_annualplans');
        }
        
        if ($course_dates && !empty($course_dates->enddate)) {
            $course_data["enddate"] = date("d-m-Y", $course_dates->enddate);
        } else {
            $course_data["enddate"] = get_string('enddatenotspecified', 'local_annualplans');
        }
        
        // Calculate duration from actual course dates
        if ($course_dates && !empty($course_dates->startdate) && !empty($course_dates->enddate)) {
            $duration_seconds = $course_dates->enddate - $course_dates->startdate;
            $duration_days = ceil($duration_seconds / (60 * 60 * 24)); // Round up to include partial days
            
            if ($duration_days > 0) {
                $weeks = floor($duration_days / 7);
                $remaining_days = $duration_days % 7;
                if ($weeks > 0 && $remaining_days > 0) {
                    $course_data["duration"] = "{$remaining_days} " . get_string($remaining_days == 1 ? 'day' : 'days', 'local_annualplans') . "  {$weeks} " . get_string($weeks == 1 ? 'week' : 'weeks', 'local_annualplans');
                } elseif ($weeks > 0) {
                    $course_data["duration"] = "{$weeks} " . get_string($weeks == 1 ? 'week' : 'weeks', 'local_annualplans');
                } else {
                    $course_data["duration"] = "{$remaining_days} " . get_string($remaining_days == 1 ? 'day' : 'days', 'local_annualplans');
                }
            } else {
                $course_data["duration"] = "1 " . get_string('day', 'local_annualplans'); // Minimum 1 day
            }
        } else {
            // Fallback: use the stored coursedate value (duration in days) from annual plan
            $duration_days = (int)$row->coursedate;
            if ($duration_days > 0) {
                $weeks = floor($duration_days / 7);
                $remaining_days = $duration_days % 7;
                if ($weeks > 0 && $remaining_days > 0) {
                    $course_data["duration"] = "{$remaining_days} " . get_string($remaining_days == 1 ? 'day' : 'days', 'local_annualplans') . "  {$weeks} " . get_string($weeks == 1 ? 'week' : 'weeks', 'local_annualplans');
                } elseif ($weeks > 0) {
                    $course_data["duration"] = "{$weeks} " . get_string($weeks == 1 ? 'week' : 'weeks', 'local_annualplans');
                } else {
                    $course_data["duration"] = "{$remaining_days} " . get_string($remaining_days == 1 ? 'day' : 'days', 'local_annualplans');
                }
            } else {
                $course_data["duration"] = "-";
            }
        }

        $place_record = $DB->get_record('local_annual_plan_course', [
            'courseid' => $row->courseid,
            "coursedate" => $row->coursedate,
            "annualplanid" => $row->annualplanid,

        ], 'place'); // Specify 'place' as the fields to retrieve

        $course_data["place"] = $place_record ? $place_record->place : '';


        if ($existing_course_in_moodle) {
            $DB->set_field("local_annual_plan_course", "approve", 1, [
                "courseid" => $row->courseid,
                "coursedate" => $row->coursedate,
            ]);
            $course_data["is_added_course"] = true;
            $course_data["added_by"] = !empty($user)
                ? fullname($user)
                : get_string("unknownuser", "local_annualplans");
            
            // Fetch course sections
            $course_id = $course_data["id"];
            $sections_list = [];
            
            if ($course_id) {
                // Get all course sections for this course
                $sql = "SELECT id, section, name, summary
                        FROM {course_sections}
                        WHERE course = ? AND section > 0
                        ORDER BY section";
                        
                $course_sections = $DB->get_records_sql($sql, [$course_id]);
                
                foreach ($course_sections as $section) {
                    // Use section name if available, otherwise create a default name
                    if (!empty($section->name)) {
                        $sections_list[] = $section->name;
                    } else {
                        $sections_list[] = get_string('section', 'local_annualplans') . " " . $section->section;
                    }
                }
            }
            
            // Set sections data
            if (!empty($sections_list)) {
                $course_data["modules"] = true;
                $course_data["modules_list"] = $sections_list;
            } else {
                $course_data["modules"] = false;
            }
        } else {
            $course_data["is_added_course"] = false;
            $course_data["modules"] = false;
        }

        $courses[] = $course_data;
    }

    $data["courses"] = $courses;

    return $data;
}
echo $OUTPUT->header();
// echo $OUTPUT->heading(
//     get_string("pluginname", "local_annualplans")
// );

$filter_approved_form->display();

$data = prepare_template_data();

echo $OUTPUT->render_from_template(
    "local_annualplans/approved_table",
    $data
);

echo $OUTPUT->footer();

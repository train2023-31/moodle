<!--This is to manage course codes in the Site Administration Page -->

<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/local/annualplans/classes/control_form.php');

// Set up the page
$PAGE->set_url(new moodle_url('/local/annualplans/manage_codes.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('managecodes', 'local_annualplans'));
$PAGE->set_heading(get_string('managecodes', 'local_annualplans'));

// Require login and capability checks
require_login();
require_capability('moodle/site:config', context_system::instance());

// Handle code deletion if requested
if (isset($_GET['delete']) && confirm_sesskey()) {
    $code_id = (int)$_GET['delete'];
    
    global $DB;
    // Check if the code exists
    $code = $DB->get_record('local_annual_plan_course_codes', array('id' => $code_id));
    
    if ($code) {
        // Attempt to delete the code
        if ($DB->delete_records('local_annual_plan_course_codes', array('id' => $code_id))) {
            redirect(
                new moodle_url('/local/annualplans/manage_codes.php'),
                get_string('coursecodedeleted', 'local_annualplans'),
                null,
                \core\output\notification::NOTIFY_SUCCESS
            );
        } else {
            redirect(
                new moodle_url('/local/annualplans/manage_codes.php'),
                get_string('cannotdeletecode', 'local_annualplans'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }
    } else {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('cannotdeletecode', 'local_annualplans'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

// Instantiate forms
$category_form = new CategoryCodeForm();
//$level_form = new LevelCodeForm();
$course_grade_form = new CourseGradeCodeForm();
$course_form = new CourseCodeForm();
$targeted_group_form = new TargetedGroupCodeForm();
$group_number_form = new GroupNumberCodeForm();

// Process category form
if ($data = $category_form->get_data()) {
    if ($category_form->process_data($data)) {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('categorycodeadded', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('categorycodeexists', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

// Process course grade form (Beginner, Intermediate, advance,....)
if ($data = $course_grade_form->get_data()) {
    if ($course_grade_form->process_data($data)) {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('coursegradecodeadded', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('coursegradecodeexists', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

// Process course form
if ($data = $course_form->get_data()) {
    if ($course_form->process_data($data)) {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('coursecodeadded', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('coursecodeexists', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

// Process targeted group form
if ($data = $targeted_group_form->get_data()) {
    if ($targeted_group_form->process_data($data)) {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('targetedgroupcodeadded', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('targetedgroupcodeexists', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

// Process group number form
if ($data = $group_number_form->get_data()) {
    if ($group_number_form->process_data($data)) {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('groupnumbercodeadded', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_SUCCESS
        );
    } else {
        redirect(
            new moodle_url('/local/annualplans/manage_codes.php'),
            get_string('groupnumbercodeexists', 'local_annualplans', $data->code),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }
}

// Output the page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecodes', 'local_annualplans'));

// Add some custom styling for the tabs
echo '<style>
    .tabtree .tabrow0 li.selected a {
        background-color: #fff;
        color: #0f6cbf;
        font-weight: bold;
    }
    .tabtree .tabrow0 li.selected {
        border-bottom: 2px solid #0f6cbf;
    }
    .tab-pane {
        padding: 20px 0;
    }
</style>';

// Add tabs for organizing the forms
$current_tab = optional_param('tab', 'category', PARAM_ALPHANUMEXT);

$tabs = [
    new tabobject('category', new moodle_url('/local/annualplans/manage_codes.php', ['tab' => 'category']), get_string('categorycodes', 'local_annualplans')),
    new tabobject('grade', new moodle_url('/local/annualplans/manage_codes.php', ['tab' => 'grade']), get_string('gradeCodes', 'local_annualplans')),
    new tabobject('course', new moodle_url('/local/annualplans/manage_codes.php', ['tab' => 'course']), get_string('coursecodes', 'local_annualplans')),
    new tabobject('targeted_group', new moodle_url('/local/annualplans/manage_codes.php', ['tab' => 'targeted_group']), get_string('targetedgroupcodes', 'local_annualplans')),
    new tabobject('group_number', new moodle_url('/local/annualplans/manage_codes.php', ['tab' => 'group_number']), get_string('groupnumbercodes', 'local_annualplans')),
    new tabobject('list', new moodle_url('/local/annualplans/manage_codes.php', ['tab' => 'list']), get_string('existingcodes', 'local_annualplans'))
];

echo $OUTPUT->tabtree($tabs, $current_tab);

// Display content based on current tab
switch ($current_tab) {
    case 'category':
        echo '<div id="category-tab" class="tab-pane active">';
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        $category_form->display();
        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // tab-pane
        break;
        
    case 'grade':
        echo '<div id="grade-tab" class="tab-pane active">';
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        $course_grade_form->display();
        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // tab-pane
        break;
        
    case 'course':
        echo '<div id="course-tab" class="tab-pane active">';
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        $course_form->display();
        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // tab-pane
        break;
        
    case 'targeted_group':
        echo '<div id="targeted_group-tab" class="tab-pane active">';
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        $targeted_group_form->display();
        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // tab-pane
        break;
        
    case 'group_number':
        echo '<div id="group_number-tab" class="tab-pane active">';
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        $group_number_form->display();
        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // tab-pane
        break;
        
    case 'list':
        echo '<div id="list-tab" class="tab-pane active">';
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';

        // Get all codes from the database
        global $DB;
        try {
            $codes = $DB->get_records('local_annual_plan_course_codes', null, 'type, code_en');
        } catch (Exception $e) {
            echo '<div class="alert alert-danger">';
            echo '<strong>' . get_string('error', 'local_annualplans') . ':</strong> ';
            echo get_string('databaseerror', 'local_annualplans') . '<br>';
            echo '<small>' . $e->getMessage() . '</small>';
            echo '</div>';
            $codes = array();
        }

        if (empty($codes)) {
            echo '<div class="alert alert-info">' . get_string('nocodesyet', 'local_annualplans') . '</div>';
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
                } else if ($code->type == 'grade') {
                    $type_name = get_string('grade', 'local_annualplans');
                } else if ($code->type == 'course') {
                    $type_name = get_string('course', 'local_annualplans');
                } else if ($code->type == 'targeted_group') {
                    $type_name = get_string('targeted_group', 'local_annualplans');
                } else if ($code->type == 'group_number') {
                    $type_name = get_string('group_number', 'local_annualplans');
                }

                $delete_url = new moodle_url('/local/annualplans/manage_codes.php', array('delete' => $code->id, 'sesskey' => sesskey()));
                $delete_link = html_writer::link($delete_url, get_string('delete'), array('class' => 'btn btn-danger btn-sm'));

                $description = (current_language() === 'ar' && !empty($code->description_ar)) ? shorten_text($code->description_ar, 50) : shorten_text($code->description_en, 50);

                $table->data[] = array(
                    get_string($code->type, 'local_annualplans'),
                    $type_name,
                    $code->code_en,
                    $description,
                    $delete_link
                );
            }

            echo html_writer::table($table);
        }

        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // tab-pane
        break;
        
    default:
        // Default to category tab
        echo '<div id="category-tab" class="tab-pane active">';
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        $category_form->display();
        echo '</div>'; // card-body
        echo '</div>'; // card
        echo '</div>'; // tab-pane
        break;
}

// No JavaScript needed - tabs are handled server-side

// Output the page footer
echo $OUTPUT->footer(); 
<?php
require_once(__DIR__ . '/../../config.php');
global $DB, $PAGE, $OUTPUT, $SESSION;

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/externallecturer/index.php'));
$PAGE->set_title(get_string('pluginname', 'local_externallecturer'));
$PAGE->set_heading(get_string('pluginname', 'local_externallecturer'));
$PAGE->requires->js(new moodle_url('/local/externallecturer/js/main.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/export_csv.js'));
$PAGE->requires->js(new moodle_url('/theme/stream/js/custom_dialog.js'));

// Add language strings to JavaScript
$PAGE->requires->js_init_code("
    window.M = window.M || {};
    M.str = M.str || {};
    M.str.local_externallecturer = {
        addlecturermodal: '" . addslashes_js(get_string('addlecturermodal', 'local_externallecturer')) . "',
        addresidentlecturermodal: '" . addslashes_js(get_string('addresidentlecturermodal', 'local_externallecturer')) . "',
        editlecturermodal: '" . addslashes_js(get_string('editlecturermodal', 'local_externallecturer')) . "',
        save: '" . addslashes_js(get_string('save', 'local_externallecturer')) . "',
        savechanges: '" . addslashes_js(get_string('savechanges', 'local_externallecturer')) . "'
    };
");

// Native autocomplete functionality - no external dependencies needed


// Get the perpage parameter from the URL, with fallback to session or default
$defaultperpage = 10;
$perpage = optional_param('perpage', $SESSION->perpage ?? $defaultperpage, PARAM_INT);

// Save the selected perpage value to the session
$SESSION->perpage = $perpage;

/*
    PAGINATION SYSTEM
    =================
    To add pagination for a new tab:
    1. Add a new page parameter: $newfeaturepage = optional_param('newfeaturepage', 0, PARAM_INT);
    2. Count total records: $totalnewfeature = $DB->count_records('newfeature_table');
    3. Fetch paginated data: $newfeature_data = $DB->get_records('newfeature_table', null, '', '*', $newfeaturepage * $perpage, $perpage);
    4. Create pagination: $newfeaturepagination = new paging_bar($totalnewfeature, $newfeaturepage, $perpage, $PAGE->url, 'newfeaturepage');
    5. Add to data array: 'newfeaturepagination' => $OUTPUT->render($newfeaturepagination), 'totalnewfeature' => $totalnewfeature
*/

// Get the current page for lecturers (default is 0, the first page)
$lecturerspage = optional_param('lecturerspage', 0, PARAM_INT);

// Count total lecturers for pagination
$totallecturers = $DB->count_records('externallecturer');

// Debug: Check if table exists and has data
if ($totallecturers === false) {
    // Table might not exist, try to create it or handle gracefully
    $totallecturers = 0;
}

// Fetch paginated lecturers based on the current page
$lecturers = $DB->get_records('externallecturer', null, '', '*', $lecturerspage * $perpage, $perpage);

// Debug: Check if lecturers were fetched
if ($lecturers === false) {
    $lecturers = [];
}

// Process lecturer data to format audit fields
$processed_lecturers = [];
foreach ($lecturers as $lecturer) {
    // Get username for created_by field
    $created_by_user = $DB->get_record('user', ['id' => $lecturer->created_by], 'firstname, lastname');
    $created_by_name = $created_by_user ? $created_by_user->firstname . ' ' . $created_by_user->lastname : 'Unknown User';
    
    // Format last modified from timemodified
    $modified_date = !empty($lecturer->timemodified) ? date('Y-m-d H:i', $lecturer->timemodified) : 'Never';
    
    $processed_lecturers[] = [
        'id' => $lecturer->id,
        'name' => $lecturer->name,
        'age' => $lecturer->age,
        'specialization' => $lecturer->specialization,
        'organization' => $lecturer->organization,
        'degree' => $lecturer->degree,
        'passport' => $lecturer->passport,
        'civil_number' => $lecturer->civil_number,
        'lecturer_type' => $lecturer->lecturer_type ?? 'external_visitor',
        'nationality' => $lecturer->nationality ?? '',
        'courses_count' => $lecturer->courses_count,
        'created_by' => $created_by_name,
        'modified_datetime' => $modified_date
    ];
}

// Prepare pagination for lecturers
$lecturerspagination = new paging_bar($totallecturers, $lecturerspage, $perpage, $PAGE->url, 'lecturerspage');

// Define the available perpage options
$perpage_options = [
    ['value' => 5, 'selected' => $perpage == 5],
    ['value' => 10, 'selected' => $perpage == 10],
    ['value' => 20, 'selected' => $perpage == 20],
    ['value' => 50, 'selected' => $perpage == 50]
];

/*
    TEMPLATE DATA ARRAY
    ===================
    To add data for a new tab:
    1. Add your data array: 'newfeature' => array_values($processed_newfeature_data)
    2. Add pagination: 'newfeaturepagination' => $OUTPUT->render($newfeaturepagination)
    3. Add total count: 'totalnewfeature' => $totalnewfeature
    4. Add any other required data for the template
*/

// Prepare the data to pass to the template
$data = array(
    'lecturers' => array_values($processed_lecturers),  // Ensure array values are indexed properly
    'lecturerspagination' => $OUTPUT->render($lecturerspagination),
    'perpage_options' => $perpage_options,     // Pass the perpage options to the template
    'perpage' => $perpage,
    'totallecturers' => $totallecturers,
    
    /*
        LANGUAGE STRINGS
        ================
        To add language strings for a new tab:
        1. Add the string key: 'newfeature' => get_string('newfeature', 'local_externallecturer') ?: 'New Feature'
        2. Add any other required strings for buttons, labels, etc.
        3. Make sure to add the corresponding strings to the language files (lang/en/local_externallecturer.php)
    */
    'str' => array(
        'pluginname' => get_string('pluginname', 'local_externallecturer') ?: 'External Lecturer Management',
        'lecturers' => get_string('lecturers', 'local_externallecturer') ?: 'Lecturers',
        'addlecturerbutton' => get_string('addlecturerbutton', 'local_externallecturer') ?: 'Add Lecturer',
        'addresidentlecturerbutton' => get_string('addresidentlecturerbutton', 'local_externallecturer') ?: 'Add Resident Lecturer',
        'exportcsv' => get_string('exportcsv', 'local_externallecturer') ?: 'Export CSV',
        'name' => get_string('name', 'local_externallecturer'),
        'civilnumber'=> get_string('civilnumber', 'local_externallecturer'),
        'age' => get_string('age', 'local_externallecturer'),
        'specialization' => get_string('specialization', 'local_externallecturer'),
        'organization' => get_string('organization', 'local_externallecturer'),
        'degree' => get_string('degree', 'local_externallecturer'),
        'passport' => get_string('passport', 'local_externallecturer'),
        'nationality' => get_string('nationality', 'local_externallecturer'),
        'coursescount' => get_string('coursescount', 'local_externallecturer'),
        'actions' => get_string('actions', 'local_externallecturer'),
        'edit' => get_string('edit', 'local_externallecturer'),
        'delete' => get_string('delete', 'local_externallecturer'),
        'nolecturers' => get_string('nolecturers', 'local_externallecturer'),
        'total' => get_string('total', 'local_externallecturer'),
        'recordsperpage' => get_string('recordsperpage', 'local_externallecturer'),
        'addlecturermodal' => get_string('addlecturermodal', 'local_externallecturer'),
        'editlecturermodal' => get_string('editlecturermodal', 'local_externallecturer'),
        'save' => get_string('save', 'local_externallecturer'),
        'savechanges' => get_string('savechanges', 'local_externallecturer'),
        'cancel' => get_string('cancel', 'local_externallecturer'),
        'agevalidation' => get_string('agevalidation', 'local_externallecturer'),
        'specializationvalidation' => get_string('specializationvalidation', 'local_externallecturer'),
        'organizationvalidation' => get_string('organizationvalidation', 'local_externallecturer'),
        'degreevalidation' => get_string('degreevalidation', 'local_externallecturer'),
        'civilnumberauto' => get_string('civilnumberauto', 'local_externallecturer'),
        'civilnumberplaceholder' => get_string('civilnumberplaceholder', 'local_externallecturer'),
        'confirmdeletelecturer' => get_string('confirmdeletelecturer', 'local_externallecturer'),
        'confirmation' => get_string('confirmation', 'local_externallecturer'),
        'createdby' => get_string('createdby', 'local_externallecturer'),
        'modifieddatetime' => get_string('modifieddatetime', 'local_externallecturer'),
    )
);



// Use the correct renderer
$output = $PAGE->get_renderer('local_externallecturer');

// Render the page
echo $OUTPUT->header();
echo $output->render_main($data);
echo $OUTPUT->footer();

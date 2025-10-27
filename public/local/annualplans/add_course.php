<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/annualplans/classes/control_form.php');

// Set up the page
$PAGE->set_url(new moodle_url('/local/annualplans/add_course.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('addnewcourse', 'local_annualplans'));
$PAGE->set_heading(get_string('addnewcourse', 'local_annualplans'));

// Add JavaScript for the shortname generator and to set categoryid/levelid
$PAGE->requires->js_amd_inline("
    require(['jquery'], function($) {
        function updateShortname() {
            var categoryCode = '';
            var courseGradeCode = '';  // Renamed from levelCode
            var courseCode = '';
            var targetedGroupCode = '';
            var groupNumberCode = '';
            var annualPlanYear = '';
            
            // Get category code and set categoryid
            var categoryCodeId = $('#id_category_code_id').val();
            if (categoryCodeId != 0) {
                // Get the code for shortname generation
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_code.php',
                    data: {id: categoryCodeId},
                    async: false,
                    success: function(data) {
                        categoryCode = data;
                    }
                });
                
                // Get the category_id from the code record
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_type_id.php',
                    data: {code_id: categoryCodeId},
                    async: false,
                    success: function(data) {
                        $('#id_categoryid').val(data);
                    }
                });
            } else {
                $('#id_categoryid').val('');
            }
            
           /*  // Get level code and set levelid
            var levelCodeId = $('#id_level_code_id').val();
            if (levelCodeId != 0) {
                // Get the code for shortname generation
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_code.php',
                    data: {id: levelCodeId},
                    async: false,
                    success: function(data) {
                    levelCode = data; 
                    }
                });
                
                // Level codes no longer have type_id, so we use courselevelid instead
                var courseLevelId = $('#id_courselevelid').val();
                if (courseLevelId && courseLevelId != 0) {
                    $('#id_levelid').val(courseLevelId);
                }
            } else {
                $('#id_levelid').val('');
            }
            
            // Sync levelid with courselevelid
            $('#id_courselevelid').change(function() {
                $('#id_levelid').val($(this).val());
            });
 */

              // Get course grade code
            var courseGradeCodeId = $('#id_grade_code_id').val();
            if (courseGradeCodeId != 0) {
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_code.php',
                    data: {id: courseGradeCodeId},
                    async: false,
                    success: function(data) {
                        courseGradeCode = data;
                    }
                });
            }
            
            // Get course code
            var courseCodeId = $('#id_course_code_id').val();
            if (courseCodeId != 0) {
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_code.php',
                    data: {id: courseCodeId},
                    async: false,
                    success: function(data) {
                        courseCode = data;
                    }
                });
            }
            
            // Get targeted group code and its description for status field
            var targetedGroupCodeId = $('#id_targeted_group_code_id').val();
            if (targetedGroupCodeId != 0) {
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_code.php',
                    data: {id: targetedGroupCodeId},
                    async: false,
                    success: function(data) {
                        targetedGroupCode = data;
                    }
                });
                
                // Get the description for the status field
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_code_description.php',
                    data: {id: targetedGroupCodeId},
                    async: false,
                    success: function(data) {
                        $('#id_status').val(data);
                    }
                });
            } else {
                $('#id_status').val('');
            }
            
            // Get group number code
            var groupNumberCodeId = $('#id_group_number_code_id').val();
            if (groupNumberCodeId != 0) {
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_code.php',
                    data: {id: groupNumberCodeId},
                    async: false,
                    success: function(data) {
                        groupNumberCode = data;
                    }
                });
            }
            
            // Get annual plan year
            var annualPlanId = $('#id_annualplanid').val();
            if (annualPlanId != 0) {
                $.ajax({
                    url: M.cfg.wwwroot + '/local/annualplans/ajax/get_annual_plan_year.php',
                    data: {id: annualPlanId},
                    async: false,
                    success: function(data) {
                        annualPlanYear = data;
                    }
                });
            }
            
            // Build shortname in the specified order: categorylevelcoursetargeted_group-year(group_number)
            var shortname = '';
            
            // Concatenate category, level, course, and targeted group WITHOUT hyphens
            if (categoryCode) {
                shortname += categoryCode;
            }
            
          if (courseGradeCode) {  // Renamed from levelCode
                shortname += courseGradeCode;
            }
            
            if (courseCode) {
                shortname += courseCode;
            }
            
            if (targetedGroupCode) {
                shortname += targetedGroupCode;
            }
            
            // Add year with a hyphen BEFORE it (only hyphen in the shortname)
            if (annualPlanYear) {
                shortname += '-' + annualPlanYear;
            }
            
            // Add group number in parentheses without a hyphen
            if (groupNumberCode) {
                shortname += '(' + groupNumberCode + ')';
            }
            
            // Update preview
            if (shortname) {
                $('#shortname_preview_container').html('<strong>' + shortname + '</strong>');
                $('#id_courseid').val(shortname);
            } else {
                $('#shortname_preview_container').html('<em>Select codes to generate shortname</em>');
            }
        }
        
        $(document).ready(function() {
            $('#id_category_code_id, #id_grade_code_id, #id_course_code_id, #id_targeted_group_code_id, #id_group_number_code_id, #id_annualplanid, #id_courselevelid').change(updateShortname);
            // Initial update
            updateShortname();
            
            // Handle direct course level selection separately
            $('#id_courselevelid').change(function() {
                $('#id_levelid').val($(this).val());
            });
        });
    });
");

// Require login and capability checks if necessary
require_login();
require_capability('moodle/site:config', context_system::instance());

// Instantiate the form
$mform = new add_course_form();

// Process the form data if submitted
if ($mform->is_cancelled()) {
    // Handle form cancellation if necessary
    redirect(new moodle_url('/local/annualplans/index.php'));
} else if ($data = $mform->get_data()) {
    $mform->process_data($data);
    
    redirect(
        new moodle_url('/local/annualplans/add_course.php'), 
        get_string('submission_course_success', 'local_annualplans'), 
        null, 
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Output the page header
echo $OUTPUT->header();
//echo $OUTPUT->heading(get_string('addnewcourse', 'local_annualplans'));

// Get available course codes
$course_code_options = [0 => get_string('selectany', 'local_annualplans')];
$course_codes = $DB->get_records('local_annual_plan_course_codes', ['type' => 'course'], 'code_en ASC');
foreach ($course_codes as $code) {
    $description = (current_language() === 'ar' && !empty($code->description_ar)) ? $code->description_ar : $code->description_en;
    if (!empty($description)) {
        $course_code_options[$code->id] = shorten_text($description, 50) . ' (' . $code->code_en . ')';
    } else {
        $course_code_options[$code->id] = $code->code_en;
    }
}

// Display the form
$mform->display();

// Output the page footer
echo $OUTPUT->footer();

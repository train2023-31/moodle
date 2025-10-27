<?php

class CourseManager {
    private $DB;
    private $USER;

    public function __construct() {
        global $DB, $USER;
        $this->DB = $DB;
        $this->USER = $USER;
    }

    public function delete_course($post_data) {
        $courseidnumber = $post_data['courseid'];
        $coursedate = $post_data['coursedate'];
        $shortname = $courseidnumber;
        // Find the course in the Moodle course table
        $existing_course_in_moodle = $this->DB->get_record('course', ['shortname' => $shortname]);

        if ($existing_course_in_moodle) {
            // Delete the course using Moodle's API
            try {
                delete_course($existing_course_in_moodle);
                $this->DB->set_field('local_annual_plan_course', 'approve', 0, ['courseid' => $courseidnumber, 'coursedate' => $coursedate]);
                echo "Course '{$existing_course_in_moodle->fullname}' with ID '$courseidnumber' has been deleted.<br>";
            } catch (Exception $e) {
                echo "Error deleting course: " . $e->getMessage() . "<br>";
            }
        }
    }

    public function add_course($post_data) {
        // echo '<p> here: ';
        // print_r($post_data);
        // echo '</p>';
        // error_log('kinza');
        // error_log('POST Data: ' . print_r($post_data, true));

        $courseidnumber = $post_data['courseid'];
        $coursename = $post_data['coursename'];
        $categoryname = $post_data['category'];
        $durationdays = $post_data['coursedate']; // now represents duration in days
        $userid = $this->USER->id;
        $DB = $this->DB;

        if (!empty($categoryname) && !empty($coursename)) {
            // Check if the course has already been approved in the 'local_annual_plan_course' table
            $existing_record = $DB->get_record('local_annual_plan_course', ['courseid' => $courseidnumber]);

            // Generate the shortname (courseidnumber + course date)
            $currentdate = date('Ymd'); // Current date in 'YYYYMMDD' format
            // $shortname = $courseidnumber . '-' . date('d/m/Y', $coursedate); // Shortname = idnumber + date
            $shortname = $courseidnumber;

            // Check if the shortname already exists in the Moodle course table
            $existing_course_in_moodle = $DB->get_record('course', ['shortname' => $shortname]);

            // Proceed to add the course since it's not approved and shortname does not exist
            // Check if the category already exists
            $existing_category = $DB->get_record('course_categories', ['name' => $categoryname]);
            if (!$existing_category) {
                // Create new category using Moodle API
                $new_category = \core_course_category::create([
                    'name' => $categoryname,
                    'parent' => 0, // Assuming root category, change as needed
                    'idnumber' => '',
                    'description' => '',
                    'descriptionformat' => 1,
                    'sortorder' => 999
                ]);
                $categoryid = $new_category->id;
                echo "Category '$categoryname' added successfully with ID $categoryid.<br>";
            } else {
                $categoryid = $existing_category->id;
                echo "Category '$categoryname' already exists with ID $categoryid.<br>";
            }

            // Debug: Check if category exists in the database
            $verify_category = $DB->get_record('course_categories', ['id' => $categoryid]);
            if (!$verify_category) {
                echo "Error: Unable to verify category '$categoryname' with ID $categoryid.<br>";
                return; // Stop if the category is invalid
            }

            // $starttimestamp = time();
            // $endtimestamp = $starttimestamp + ($durationdays * 24 * 60 * 60);

            // Create new course using Moodle API
            $new_course = create_course((object)[
                'fullname' => $coursename,
                'shortname' => $shortname,
                'idnumber' => $shortname,
                'category' => $categoryid,
                'summaryformat' => 1,
                'visible' => 1,
                // 'startdate' => $starttimestamp,
                // 'enddate' => $endtimestamp,
                'format' => 'topics',
                'numsections' => 0,
                'newsitems' => 5,
            ]);
            echo "Course '$coursename' added successfully under category ID $categoryid.<br>";
            $DB->set_field('local_annual_plan_course', 'userid', $userid, ['courseid' => $courseidnumber, 'coursedate' => $durationdays]);
            // Update the approve field to true (1) after adding the course
            $DB->set_field('local_annual_plan_course', 'approve', 1, ['courseid' => $courseidnumber, 'coursedate' => $durationdays]);

            echo "Course '$coursename' has been approved.<br>";
            
            // Enroll selected beneficiaries in the course
            $this->enroll_beneficiaries($courseidnumber, $durationdays, $new_course->id);
        }

        // Clear the cache to ensure categories and courses are visible
        cache_helper::purge_by_definition('core', 'coursecat');
        cache_helper::invalidate_by_definition('core', 'coursecat');
    }

    /**
     * Enroll selected beneficiaries in the approved course
     */
    private function enroll_beneficiaries($courseid, $coursedate, $moodle_course_id) {
        global $DB, $CFG;
        
        require_once($CFG->dirroot . '/enrol/locallib.php');
        require_once($CFG->dirroot . '/lib/enrollib.php');
        require_once($CFG->dirroot . '/user/lib.php');
        
        echo "<br><strong>Enrolling beneficiaries...</strong><br>";
        
        // Get the annual plan ID for this course
        $course_record = $DB->get_record('local_annual_plan_course', [
            'courseid' => $courseid,
            'coursedate' => $coursedate
        ]);
        
        if (!$course_record) {
            echo "Error: Could not find course record for enrollment.<br>";
            return;
        }
        
        // Get all beneficiaries for this course
        $beneficiaries = $DB->get_records('local_annual_plan_beneficiaries', [
            'courseid' => $courseid,
            'coursedate' => $coursedate,
            'annualplanid' => $course_record->annualplanid
        ]);
        
        if (empty($beneficiaries)) {
            echo "No beneficiaries selected for this course.<br>";
            return;
        }
        
        echo "Found " . count($beneficiaries) . " beneficiaries to enroll.<br>";
        
        // Get the course context and enrollment instance
        $course = $DB->get_record('course', ['id' => $moodle_course_id]);
        $context = context_course::instance($moodle_course_id);
        
        // Get manual enrollment plugin
        $manual_plugin = enrol_get_plugin('manual');
        $manual_instance = $DB->get_record('enrol', [
            'courseid' => $moodle_course_id,
            'enrol' => 'manual'
        ]);
        
        if (!$manual_instance) {
            // Add manual enrollment instance if it doesn't exist
            $manual_instance_id = $manual_plugin->add_instance($course);
            $manual_instance = $DB->get_record('enrol', ['id' => $manual_instance_id]);
        }
        
        // Get student/trainee role ID
        $student_role = $DB->get_record('role', ['shortname' => 'trainee']);
        if (!$student_role) {
            // Try alternative role name
            $student_role = $DB->get_record('role', ['shortname' => 'student']);
        }
        if (!$student_role) {
            echo "Error: Student/trainee role not found.<br>";
            return;
        }
        
        $enrolled_count = 0;
        $created_users = 0;
        $errors = [];
        
        foreach ($beneficiaries as $beneficiary) {
            try {
                // Get employee details from Oracle database
                $employee_data = $this->get_employee_from_oracle($beneficiary->pf_number);
                
                if (!$employee_data) {
                    $errors[] = "Could not find employee data for identifier: " . $beneficiary->pf_number;
                    continue;
                }
                
                // Check if user already exists in Moodle (by identifier)
                $existing_user = $DB->get_record('user', ['idnumber' => $beneficiary->pf_number]);
                
                // If not found, try alternative lookup based on data type
                if (!$existing_user) {
                    if ($employee_data['SOURCE_TYPE'] == 'employee' && !empty($employee_data['PF_NUMBER'])) {
                        $existing_user = $DB->get_record('user', ['username' => 'emp_' . $employee_data['PF_NUMBER']]);
                    } else if ($employee_data['SOURCE_TYPE'] == 'personal' && !empty($employee_data['CIVIL_NUMBER'])) {
                        $existing_user = $DB->get_record('user', ['username' => 'civil_' . $employee_data['CIVIL_NUMBER']]);
                    }
                }
                
                if (!$existing_user) {
                    // Create new Moodle user
                    $user = $this->create_moodle_user($employee_data);
                    if (!$user) {
                        $errors[] = "Failed to create user for: " . $beneficiary->fullname;
                        continue;
                    }
                    $created_users++;
                    echo "Created user: " . $user->username . " (" . $beneficiary->fullname . ")<br>";
                } else {
                    $user = $existing_user;
                    echo "Found existing user: " . $user->username . " (" . $beneficiary->fullname . ")<br>";
                }
                
                // Check if user is already enrolled
                if (is_enrolled($context, $user->id)) {
                    echo "User " . $user->username . " is already enrolled.<br>";
                    continue;
                }
                
                // Get the role for this beneficiary
                $role_id = $student_role->id; // Default fallback
                if (isset($beneficiary->roleid) && $beneficiary->roleid) {
                    // Check if the specified role exists
                    $selected_role = $DB->get_record('role', ['id' => $beneficiary->roleid]);
                    if ($selected_role) {
                        $role_id = $selected_role->id;
                        echo "Using selected role: " . $selected_role->shortname . " for " . $user->username . "<br>";
                    } else {
                        echo "Invalid role ID " . $beneficiary->roleid . " for " . $user->username . ", using default role<br>";
                    }
                } else {
                    echo "No role specified for " . $user->username . ", using default role<br>";
                }
                
                // Enroll the user with the appropriate role
                $manual_plugin->enrol_user($manual_instance, $user->id, $role_id);
                $enrolled_count++;
                echo "Enrolled: " . $user->username . " (" . $beneficiary->fullname . ") with role ID: " . $role_id . "<br>";
                
            } catch (Exception $e) {
                $errors[] = "Error enrolling " . $beneficiary->fullname . ": " . $e->getMessage();
            }
        }
        
        echo "<br><strong>Enrollment Summary:</strong><br>";
        echo "✅ Users created: $created_users<br>";
        echo "✅ Users enrolled: $enrolled_count<br>";
        
        if (!empty($errors)) {
            echo "❌ Errors encountered:<br>";
            foreach ($errors as $error) {
                echo "  - $error<br>";
            }
        }
    }
    
    /**
     * Get employee data from Oracle database using centralized Oracle manager
     * 
     * This method fetches employee data from the Oracle database using the centralized oracle_manager class.
     * It requires the oracleFetch plugin to be installed and configured.
     *
     * @param string $identifier The employee identifier (PF number or civil ID) to look up
     * @return array|false Returns employee data array if found, false otherwise
     */
    private function get_employee_from_oracle($identifier) {
        // Include the centralized Oracle manager class
        require_once(__DIR__ . '/../../oracleFetch/classes/oracle_manager.php');
        
        // Query Oracle database for employee data using the identifier
        return oracle_manager::get_person_data($identifier);
    }
    
    /**
     * Create a Moodle user from Oracle employee data
     */
    private function create_moodle_user($employee_data) {
        global $DB;
        
        if (!$employee_data) {
            return false;
        }
        
        // Determine identifier and username based on source type
        if ($employee_data['SOURCE_TYPE'] == 'employee' && !empty($employee_data['PF_NUMBER'])) {
            // Employee record - use PF number
            $identifier = $employee_data['PF_NUMBER'];
            $username = 'emp_' . $identifier;
            $user_type = 'employee';
        } else if ($employee_data['SOURCE_TYPE'] == 'personal' && !empty($employee_data['CIVIL_NUMBER'])) {
            // Personal details record - use civil number
            $identifier = $employee_data['CIVIL_NUMBER'];
            $username = 'civil_' . $identifier;
            $user_type = 'personal';
        } else {
            error_log('Invalid employee data structure');
            return false;
        }
        
        // Check if user already exists by username or idnumber
        $existing_user = $DB->get_record('user', ['username' => $username]);
        if (!$existing_user) {
            $existing_user = $DB->get_record('user', ['idnumber' => $identifier]);
        }
        
        if ($existing_user) {
            return $existing_user;
        }
        
        // Prepare user data
        $user = new stdClass();
        $user->confirmed = 1;
        $user->mnethostid = 1;
        $user->username = $username;
        $user->password = hash_internal_user_password('Temp123!'); // Temporary password
        $user->idnumber = $identifier;
        $user->firstname = trim($employee_data['FIRST_NAME']) ?: 'Unknown';
        $user->lastname = trim($employee_data['LAST_NAME']) ?: 'Employee';
        $user->email = $username . '@company.local'; // Generate email based on username
        $user->phone1 = ''; // Phone number not available from Oracle
        $user->city = 'Default City';
        $user->country = 'OM'; // Oman
        $user->lang = 'ar';
        $user->timezone = 'Asia/Muscat';
        $user->mailformat = 1;
        $user->maildisplay = 2;
        $user->autosubscribe = 1;
        $user->trackforums = 0;
        $user->timecreated = time();
        $user->timemodified = time();
        
        // Add custom profile field to indicate user type
        $user->profile_field_usertype = $user_type;
        
        try {
            $user_id = user_create_user($user, false, false);
            return $DB->get_record('user', ['id' => $user_id]);
        } catch (Exception $e) {
            error_log('Error creating user: ' . $e->getMessage());
            return false;
        }
    }
}

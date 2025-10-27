<?php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Annual Plans';
$string['uploadfile'] = 'Upload Excel file';
$string['uploadfile_help'] = 'Upload an Excel file containing the annual plans.';
$string['upload'] = 'Upload';
$string['applyfilters'] = 'Apply filters';
$string['annualplanname'] = 'Annual plan name';
$string['courseid'] = 'Course ID';
$string['coursename'] = 'Course name';
$string['category'] = 'Category';
$string['coursedate'] = 'Duration (days)';
$string['enddate'] = 'End date';
$string['duration'] = 'Duration';
$string['numberofbeneficiaries'] = 'Number of beneficiaries';
$string['room'] = 'Room';
$string['status'] = 'Status';
$string['grade'] = 'Course Grade';
$string['level'] = 'Course Level';
$string['enable'] = 'Enable';
$string['enabledesc'] = 'Enable or disable the Annual Plans feature.';
$string['path'] = 'Path';
$string['pathdesc'] = 'Specify the path to the resource.';
$string['startdate'] = 'Start date';
$string['enddate'] = 'End date';
$string['datefilterhelp'] = 'Leave date fields empty to show all courses including those without dates';
$string['enddateerror'] = 'End date cannot be earlier than start date';
$string['uploadsuccess'] = 'File uploaded successfully';
$string['manage'] = 'Manage Annual Plans';
$string['settings'] = 'Settings';
$string['makethismyhome'] = 'Make this my home page';

$string['csvdelimiter'] = 'CSV separator';
$string['csvdelimiter_help'] = 'The character separating the series of data in each record.';
$string['encoding'] = 'Encoding';
$string['encoding_help'] = 'Encoding of the CSV file.';
$string['createannualplan'] = 'Create Annual Plans';
$string['filter'] = 'Filter data';
$string['filterhdr'] = 'Filter data';

$string['any'] = 'Any';
$string['allplans'] = 'All Plans';
$string['newannualplanname'] = 'Plan Name';
$string['newannualplanyear'] = 'Plan Year';
$string['addnewannualplan'] = 'Add a new plan';
$string['selectany'] = 'Select any';
$string['selectannualplan'] = 'Plan';
$string['deleteannualplan'] = 'Delete Annual Plan';
$string['annualplandeleted'] = 'Annual Plan deleted successfully';
$string['annualplannotexist'] = 'Annual Plan does not exist';
$string['annualplanid'] = 'Annual Plan ID';
$string['selectannualplanid_help'] = 'This is the unique identifier for each annual plan.';
$string['annualplantitle'] = 'Annual Plan Title';
$string['addcourse'] = 'Add Course';
$string['addedcourse'] = 'Course added';
$string['deletecourse'] = 'Delete course';

$string['addnewcourse'] = 'Add new course';
$string['submission_course_success'] = 'Course added successfully';
$string['notapproved'] = 'Not approved';
$string['approved'] = 'Approved';
$string['approve'] = 'Approved';
$string['courseapproved'] = 'Course approved successfully';
$string['coursenotapproved'] = 'Course approval cancelled';
$string['saveCourseinPlan'] = 'Save course in plan';

// Upload user
$string['uploadcoursefile'] = 'Upload courses of annual plan';
$string['uploaduserfile'] = 'Upload users of annual plan';
$string['showusers'] = 'Show students';
$string['userslist'] = 'Students list';
$string['userid'] = 'User ID';
$string['fullname'] = 'Full name';
$string['categorychange'] = 'Category cannot be changed after the course is added';
$string['levelchange'] = 'Level cannot be changed after the course is added';

// Manage levels
$string['managelevels'] = 'Add new level';
$string['newlevelname'] = 'New level name';
$string['newleveldescription'] = 'Level description';
$string['isinternal'] = 'Internal Level';
$string['isinternal_help'] = 'Check this box if this level should be classified as internal staff courses. Uncheck for external/public courses.';
$string['addnewlevel'] = 'Add level';
$string['selectleveltodelete'] = 'Select level to delete';
$string['selectlevel'] = 'Select level';
$string['deletelevel'] = 'Delete level';
$string['levelstitle'] = 'Levels';
$string['manglevelstitle'] = 'Manage levels';
$string['deleteconfirmation'] = 'Are you sure you want to delete this course?';
$string['cannotdeleteaddedcourse'] = 'This course cannot be deleted as it has already been added.';

// Approved Displays
$string['displayapprovedcourses'] = 'View all approved courses';
$string['place'] = 'Place';
$string['modules'] = 'Sections';
$string['sections'] = 'Sections';
$string['didntaddedyet'] = 'Not added yet';

// Course codes
$string['managecodes'] = 'Manage Course Codes';
$string['managecoursecodes'] = 'Manage Course Codes';
$string['managecoursecodesadmin'] = 'Manage Course Codes';
$string['codetype'] = 'Code Type';
$string['typeid'] = 'Select Element';
$string['code'] = 'Code';
$string['code_help'] = 'Enter a short code that will be used in course shortname';
$string['code_name'] = 'Code Description';
$string['code_name_help'] = 'Enter a descriptive name for this code to make it easily identifiable';
$string['addcode'] = 'Add Code';
$string['existingcodes'] = 'Existing Codes';
$string['nocodesyet'] = 'No codes have been created yet.';
$string['type'] = 'Type';
$string['name'] = 'Name';
$string['actions'] = 'Actions';
$string['unknown'] = 'Unknown';
$string['notapplicable'] = 'N/A';
$string['pleaseselecttype'] = 'Please select type...';
$string['course'] = 'Course';
$string['codeexists'] = 'This code already exists.';
$string['deletecode'] = 'Delete Code';
$string['courseshortname'] = 'Course Shortname';
$string['categorycodes'] = 'Category Codes';
$string['gradeCodes'] = 'Level Codes';
$string['coursecodes'] = 'Course Codes';
$string['shortnamegenerator'] = 'Course Shortname Generator';
$string['shortname'] = 'Course Shortname';
$string['shortname_help'] = 'The course shortname will be automatically generated based on your code selections.';
$string['coursecodedeleted'] = 'Course code deleted successfully.';
$string['cannotdeletecode'] = 'Cannot delete this code as it may be in use.';

// New string definitions for the separate code forms
$string['categorycode'] = 'Category Code';
$string['CourseGradeCode'] = 'Course Grade Code';
$string['coursecode'] = 'Course Code';
$string['addcategorycode'] = 'Add Category Code';
$string['addCourseGradecode'] = 'Add Level Code';
$string['addcoursecode'] = 'Add Course Code';
$string['categorycodeadded'] = 'Category code \'{$a}\' added successfully.';
$string['coursegradecodeadded'] = 'Course Grade code \'{$a}\' added successfully.';
$string['coursecodeadded'] = 'Course code \'{$a}\' added successfully.';
$string['categorycodeexists'] = 'Category code \'{$a}\' already exists.';
$string['coursegradecodeexists'] = 'Course Grade code \'{$a}\' already exists.';
$string['coursecodeexists'] = 'Course code \'{$a}\' already exists.';
$string['targetedgroupcodeexists'] = 'Targeted group code \'{$a}\' already exists.';
$string['groupnumbercodeexists'] = 'Group number code \'{$a}\' already exists.';

// Description field
$string['description'] = 'Description';
$string['description_help'] = 'Enter a detailed description for this code (optional)';
$string['code_ar'] = 'Code (Arabic)';
$string['code_ar_help'] = 'Enter the code in Arabic.';
$string['description_ar'] = 'Description (Arabic)';
$string['description_ar_help'] = 'Enter a detailed description for this code in Arabic.';

// Category/level already has a code
$string['categoryhascodealready'] = 'This category already has a code assigned. Each category can only have one code.';
$string['levelhascodealready'] = 'This level already has a code assigned. Each level can only have one code.';

// New code types
$string['targetedgroupcode'] = 'Targeted Group Code';
$string['groupnumbercode'] = 'Group Number Code';
$string['targetedgroupcodes'] = 'Targeted Group Codes';
$string['groupnumbercodes'] = 'Group Number Codes';
$string['addtargetedgroupcode'] = 'Add Targeted Group Code';
$string['addgroupnumbercode'] = 'Add Group Number Code';
$string['targetedgroupcodeadded'] = 'Targeted group code \'{$a}\' added successfully.';
$string['groupnumbercodeadded'] = 'Group number code \'{$a}\' added successfully.';
$string['targeted_group'] = 'Targeted Group';
$string['group_number'] = 'Group Number';

// Other missing strings
$string['annualplanrequired'] = 'Annual plan must be selected';
$string['courseidrequired'] = 'Course ID is required';
$string['coursenamerequired'] = 'Course name is required';
$string['categoryrequired'] = 'Category must be selected';
$string['dberror'] = 'Database error';
$string['erroraddingcourse'] = 'Error occurred while adding the course';

// Capability strings
$string['annualplans:manage'] = 'Manage annual plans';
$string['annualplans:view'] = 'View annual plans';

$string['financesource'] = 'Finance Source';
$string['financeremarks'] = 'Finance Remarks';

// AJAX Messages
$string['beneficiariessavedsuccess'] = 'Beneficiaries saved successfully';
$string['beneficiariessavefailed'] = 'Failed to save beneficiaries';
$string['error'] = 'Error';
$string['databaseerror'] = 'Database error occurred while retrieving codes';

// Role names
$string['trainee'] = 'Trainee';
$string['courseofficer'] = 'Course Officer';

// Display messages
$string['levelnotspecified'] = 'Level not specified';
$string['startdatenotspecified'] = 'Start date not specified';
$string['enddatenotspecified'] = 'End date not specified';
$string['day'] = 'day';
$string['days'] = 'days';
$string['week'] = 'week';
$string['weeks'] = 'weeks';
$string['categorynotspecified'] = 'Category not specified';
$string['deletenotecomment'] = 'You must write a note before deleting the course.';
$string['courseDeleted'] = 'Course deleted successfully.';
$string['section'] = 'Section';

// Add missing string for unknown user
$string['unknownuser'] = 'Unknown user';

$string['coursedurationdays'] = 'Course Duration (days)';
$string['createcourse'] = 'Create Course';

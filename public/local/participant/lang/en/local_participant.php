<?php
$string['pluginname'] = 'Participant Management';
$string['viewrequests'] = 'View Requests';
$string['addrequest'] = 'Add Request';
$string['course'] = 'Course';
$string['plan'] = 'Plan';
$string['type'] = 'Type';
$string['externallecturer'] = 'External Lecturer';
$string['orgname'] = 'Organization Name';
$string['days_hours'] = 'Days/Hours';
$string['contract_amount'] = 'Contract Amount';
$string['status'] = 'Status';
$string['requestadded'] = 'New request has been added';
$string['isapproved'] = 'Approval';
$string['cost'] = 'Contract Amount';
$string['dayshours'] = 'Contract Period';
$string['organization'] = 'Organization';
$string['participant'] = 'Participant';
$string['isinside'] = 'is inside';
$string['user'] = 'User';
$string['searchemployee'] = 'Start typing to search for an employee...';
$string['searchlecturer'] = 'Start typing to search for a lecturer...';
$string['nocoursesfound'] = 'No courses found';
$string['noapprovedcoursesfound'] = 'No approved courses found';
$string['noannualplanwarning'] = 'Warning: Course "{$a}" does not have annual plan data. The request will be processed without annual plan linkage.';
$string['usercreationwarning'] = 'Warning: Could not create/find Moodle user for employee PF number "{$a}". The request will be processed but may require manual user assignment.';
$string['requestdate'] = 'Service Date';
$string['servicedate_help'] = 'Service Date';
$string['servicedate_help_help'] = 'The date when the participant service/work is actually needed. This is the date when the role player, lecturer, or other participant will provide their services.';
$string['invalidnumber'] = 'Please enter a valid number';
$string['filter'] = 'Filter';
$string['allcourses'] = 'All Courses';
$string['allparticipanttypes'] = 'All Participant Types';
$string['allstatuses'] = 'All Statuses';
$string['statusfilter'] = 'Status Filter';
$string['clearfilters'] = 'Clear Filters';

// New strings for template
$string['participanttype'] = 'Participant Type';
$string['internal'] = 'Internal';
$string['external'] = 'External';
$string['contractduration'] = 'Contract Duration';
$string['contractcost'] = 'Contract Cost';
$string['requeststatus'] = 'Request Status';
$string['days'] = 'days';
$string['hours'] = 'hours';
$string['dynamic'] = 'dynamic';
$string['currency'] = 'OMR';
$string['approve'] = 'Approve';
$string['reject'] = 'Reject';
$string['confirmapprove'] = 'Do you want to approve this request?';
$string['confirmreject'] = 'Do you want to reject this request?';
$string['recordsperpage'] = 'Records per page';
$string['noactions'] = 'No actions available';
$string['requestapproved'] = 'Request Approved';
$string['requestrejected'] = 'Request Rejected';
$string['finalstatus'] = 'Final Status';
$string['rejectionreason'] = 'Rejection Reason';
$string['noreason'] = 'No reason provided';

// Capability strings
$string['participant:addrequest'] = 'Add participant request';
$string['participant:view'] = 'View participant information';


//Participant Types
$string['roleplayer'] = 'Role Player';
$string['assistantlecturer'] = 'Assistant Lecturer';
$string['externallecturer'] = 'External Lecturer';

// Help text for compensation calculation
$string['compensation_help'] = 'Compensation Amount Calculation';
$string['compensation_help_help'] = 'The compensation amount is calculated automatically based on the participant type:<br/>
• <strong>Role Player types:</strong> Duration (days) × Fixed rate per day<br/>
• <strong>Lecturer/Evaluator types:</strong> Duration (hours) × Fixed rate per hour<br/>
• <strong>External Lecturer:</strong> Manual entry required (varies by expert)';

// Help text for contract period
$string['contractperiod_help'] = 'Contract Period Guidelines';
$string['contractperiod_help_help'] = 'Enter the contract period based on the selected participant type:<br/>
• <strong>Types calculated by Days:</strong> Enter number of days (e.g., 5 for 5 days)<br/>
• <strong>Types calculated by Hours:</strong> Enter number of hours (e.g., 8 for 8 hours)<br/>
• <strong>Dynamic types:</strong> Enter appropriate units as required<br/>
<em>The calculation method is shown in parentheses next to each participant type above.</em>';

// Error messages
$string['invalidcourseid'] = 'Invalid course selected';
$string['coursemissingidnumber'] = 'Selected course is missing course code (idnumber)';
$string['invalidcourseselection'] = 'Selected course does not have a corresponding annual plan entry';
$string['noplandata'] = 'No annual plan data found for course code: {$a}';
$string['inserterror'] = 'Error occurred while saving the request. Please try again.';

// User creation messages
$string['moodleusercreated'] = 'Moodle user account created for PF: {$a}';
$string['userenrolled'] = 'User enrolled in course successfully';
$string['enrollmentfailed'] = 'Failed to enroll user in course';

// Oracle data messages
$string['oracledatastored'] = 'Oracle data stored for PF: {$a}';
$string['pfnumber'] = 'PF Number';

// Audit fields
$string['createdby'] = 'Created By';
$string['createddate'] = 'Created Date';
$string['clause'] = 'Clause';
$string['fundingtype'] = 'Funding Type';

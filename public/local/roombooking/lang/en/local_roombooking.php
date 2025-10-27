<?php
// General plugin strings
$string['pluginname'] = 'Classroom Booking Service';
$string['registeringroom'] = 'Registering a Classroom';
$string['selectacourse'] = 'Select a course';
$string['selectaroom'] = 'Select a room';
$string['course'] = 'Course';
$string['room'] = 'Room';
$string['startdatetime'] = 'Start date and time';
$string['enddatetime'] = 'End date and time';
$string['recurrence'] = 'Recurrence';
$string['once'] = 'Once';
$string['daily'] = 'Daily';
$string['weekly'] = 'Weekly';
$string['monthly'] = 'Monthly';
$string['bookroom'] = 'Book Room';
$string['required'] = 'Required';
$string['roomunavailable'] = 'The selected room is not available for the chosen time.';
$string['course_help'] = 'Select the course associated with the room booking. Ensure that you choose the correct course for which the room is required.';
$string['room_help'] = 'Select a room based on your needs. You can view the room type (fixed or dynamic) and the maximum capacity next to the room name.';

// Success/Error/Notification Messages
$string['successmessage'] = 'Booking successful! Your room has been reserved.';
$string['bookingupdated'] = 'Booking has been successfully updated.';
$string['errordeletingbooking'] = 'Error deleting booking.';
$string['roomnotavailable'] = 'Room is not available';
$string['alternativerooms'] = 'Alternative available rooms:';
$string['noalternativerooms'] = 'No alternative rooms are available for the selected time period.';
$string['roomnotavailableWithlistofrooms'] = 'The selected room (ID: {$a->roomid}) is not available. Available rooms: {$a->available_rooms}.';
$string['endbeforestart'] = 'End time must be after start time';
$string['recurrenceendbeforestart'] = 'Recurrence end date cannot be before the start date';
$string['invalidbookingid'] = 'Invalid booking ID';
$string['bookingnotfound'] = 'Booking not found';
$string['errorupdatingbooking'] = 'Error updating the booking';
$string['erroraddingbooking'] = 'Error adding the booking';
$string['requestsubmitted'] = 'Your room booking request has been submitted successfully.';
$string['errorcheckingavailability'] = 'Error while checking available rooms';
$string['availableroomsmessage'] = 'Available rooms: {$a->available_rooms}';
$string['booking_success_message'] = 'Booking successful! Room ID: {$a->roomid}, Start Time: {$a->starttime}, End Time: {$a->endtime}, Capacity: {$a->capacity}, User ID: {$a->userid}.';
$string['unexpectederror'] = 'Un Expected Error!';
$string['errorprocessingbooking'] = 'Error while processing room booking!';
$string['successaddclassroom'] = 'Class room booked Successfully';
$string['erroraddclassroom'] = 'Error booking classroom. Please try again.';


// Confirmation Strings
$string['deletebookingconfirm'] = 'Are you sure you want to delete this booking? This action cannot be undone.';
$string['deleteconfirmation'] = 'Are you sure you want to delete booking ID {$a}?';
$string['deleteconfirmationdetails'] = 'Are you sure you want to delete the booking for the course: <strong>{$a->course}</strong>, in room: <strong>{$a->room}</strong>, starting at: <strong>{$a->starttime}</strong> and ending at: <strong>{$a->endtime}</strong> with recurrence: <strong>{$a->recurrence}</strong>?';

// Booking Management & Table UI
$string['username'] = 'Username';
$string['managebookings'] = 'Manage Bookings';
$string['managebookingsdashboard'] = 'Classroom Management Dashboard';
$string['courseid'] = 'Course ID';
$string['roomid'] = 'Room ID';
$string['startdate'] = 'Start Date';
$string['starttime'] = 'Start Time';
$string['endtime'] = 'End Time';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['actions'] = 'Actions';
$string['id'] = 'ID';
$string['editbooking'] = 'Edit Booking';
$string['deletebooking'] = 'Delete Booking';

// Permissions & Access Control
$string['roombooking:viewbookings'] = 'View room bookings';
$string['roombooking:managebookings'] = 'Manage room bookings';
$string['roombooking:deletebookings'] = 'Delete room bookings';
$string['nopermissions'] = 'You do not have the necessary permissions to access this feature.';
$string['nocreatepermissions'] = 'You do not have permission to create a booking request.';
$string['cannoteditbooking'] = 'You do not have permission to edit this booking.';
$string['noactions'] = 'You do not have permission';
$string['nopermission'] = 'You do not have permission to perform this action.';
$string['exceedmaximumduration'] = 'The booking duration exceeds the maximum allowed of {$a} hours.';

// Filter & Search Strings
$string['filter'] = 'Filter Bookings';
$string['applyfilters'] = 'Apply Filters';
$string['startdate'] = 'Start Date';
$string['enddate'] = 'End Date';
$string['recurrenceenddate'] = 'Recurrence End Date';
//$string['any'] = 'Any';
$string['any'] = 'ALL';
$string['course'] = 'Course';
$string['bookingday'] = 'Booking Day';
$string['classroomfilter'] = 'Filter Classroom Bookings';
$string['submit'] = 'Submit';
$string['classroomdata'] = 'Classroom Booking Data';
$string['noclassroomsfound'] = 'No classroom bookings found for the selected filters.';
$string['filteredresults'] = 'Filtered Results';
$string['allclassrooms'] = 'All Classroom Bookings';

// Group Bookings
$string['confirmdeletegroup'] = 'Are you sure you want to delete all bookings for group ID {$a}?';
$string['groupbookingdeleted'] = 'All bookings for group ID {$a} have been deleted.';
$string['editgroupbooking'] = 'Edit Group Booking';
$string['groupbookingdetails'] = 'Bookings in the same group:';
$string['deleteallbookings'] = 'Do you want to delete all bookings in this group?';
$string['bookingdeleted'] = 'All bookings in the group have been successfully deleted.';

// Additional Settings
$string['settings'] = 'Settings';
$string['enable'] = 'Enable Booking System';
$string['enable_desc'] = 'Enable or disable the classroom booking system';
$string['role'] = 'Required Role';
$string['role_desc'] = 'Role required to make bookings';
$string['configuration'] = 'Booking Configuration';

// Date & Time Formats
$string['strftimedatetime'] = '%d %B %Y, %H:%M'; // Example format, adjust as needed

// Additional strings for delete confirmations
$string['deleteallbookings'] = 'Do you want to delete all bookings in this group?';
$string['bookingdetails'] = 'Booking details for ID: {$a}';

$string['managerooms'] = 'Manage Rooms';
$string['roomname'] = 'Room Name';
$string['capacity'] = 'Capacity';
$string['requiredcapacity'] = 'Required Capacity';
$string['location'] = 'Location';
$string['description'] = 'Description';
$string['roomadded'] = 'Room added successfully';
$string['roomupdated'] = 'Room updated successfully';
$string['roomdeleted'] = 'Room deleted successfully';
$string['editroom'] = 'Edit Room';
$string['addroom'] = 'Add New Room';
$string['existingrooms'] = 'Existing Rooms';
$string['actions'] = 'Actions';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['norooms'] = 'No rooms found';
$string['cannotdeleteroom'] = 'Cannot delete room as it is associated with existing bookings';
$string['required'] = 'This field is required';
$string['savechanges'] = 'Save changes';
$string['cancel'] = 'Cancel';

$string['invalidcapacity'] = 'Please enter a valid capacity.';
$string['roomexceedsmaxcapacity'] = 'The requested capacity exceeds the maximum allowed capacity of the room.';
$string['validnumber'] = 'Please enter a valid number.';
$string['invalidrecurrenceenddate'] = 'Invalid Recurrence End date!';
$string['invaliddatetime'] = 'Invalid Datetime!';
$string['invalidtimetobook'] = 'Start time should be before or same End time!';
$string['recurrenceendbeforeend'] = 'Recurrence End Date should be before booking End date!';
$string['invalidrecurrencetype'] = 'Invalid Recurrence Type!';
$string['recurrenceintervalisnotsetproperly'] = 'Recurrence interval is not set properly!';

$string['roomtype'] = 'Room Type';
$string['fixed'] = 'Fixed';
$string['dynamic'] = 'Dynamic';
$string['invalidroomtype'] = 'Invalid room type selected.';
$string['invalidcapacity'] = 'Please enter a valid capacity greater than zero.';

// Column Headers
$string['column_id'] = '#';
$string['column_course'] = 'Course';
$string['column_room'] = 'Room';
$string['column_capacity'] = 'Capacity';
$string['column_start_date'] = 'Start Date';
$string['column_start_time'] = 'Start Time';
$string['column_end_time'] = 'End Time';
$string['column_recurrence'] = 'Recurrence';
$string['column_user'] = 'User';
$string['actions_label'] = 'Actions';
$string['column_status'] = 'Status';
// New Strings for Modals
$string['approve_request'] = 'Approve Request';
$string['reject_request'] = 'Reject Request';
$string['approve_confirmation'] = 'Are you sure you want to approve this request?';
$string['reject_confirmation'] = 'Are you sure you want to reject this request?';
$string['reject_note_placeholder'] = 'Please provide a note for rejecting the request';
$string['reject_note_required'] = 'A rejection note is required.';
$string['approve_success'] = 'Request approved successfully.';
$string['reject_success'] = 'Request rejected successfully.';
$string['approve_error'] = 'An error occurred while approving the request.';
$string['reject_error'] = 'An error occurred while rejecting the request.';
$string['confirm'] = 'Confirm';
$string['cancel'] = 'Cancel';
$string['exportcsv'] = 'Export CSV';
$string['clearfilters'] = 'Clear Filters';

// Status labels
$string['status_pending'] = 'Pending';
$string['status_approved'] = 'Approved';
$string['status_rejected'] = 'Rejected';
$string['unknown_status'] = 'In progress';

$string['recurrence_none'] = 'None';
$string['recurrence_daily'] = 'Daily';
$string['recurrence_weekly'] = 'Weekly';
$string['recurrence_monthly'] = 'Monthly';
$string['recurrence_unknown'] = 'Unknown';

// Workflow strings
$string['booking_approved'] = 'Booking approved successfully';
$string['booking_rejected'] = 'Booking rejected successfully';
$string['confirm_title'] = 'Confirmation';
$string['confirm_approve'] = 'Are you sure you want to approve this booking?';
$string['confirm_reject'] = 'Are you sure you want to reject this booking?';
$string['rejection_reason'] = 'Your booking request has been rejected. Reason';
$string['approval_note'] = 'Approval note';
$string['awaiting_approval'] = 'Awaiting approval at a different stage';
$string['statusalreadyfinal'] = 'This booking is already in final approved state';
$string['cannotrejectapproved'] = 'Cannot reject an already approved booking';
$string['cannotreject'] = 'Cannot reject this booking at its current stage';

// Management Dashboard
$string['manage'] = 'Management';
$string['viewbookings'] = 'View Bookings';
$string['viewfilterbookings'] = 'View and filter {$a->type} bookings';
$string['reports'] = 'Reports';
$string['generatereports'] = 'Generate booking reports and statistics';
$string['vieweditrooms'] = 'View, create and edit classroom details';
$string['bookinginterface'] = 'Booking Interface';
$string['managementdashboard'] = 'Management Dashboard';

// Additional capability strings
$string['roombooking:managerooms'] = 'Manage room booking rooms';

$string['invalidroomid'] = 'Invalid room id.';
$string['roomnotavailableWithlistofrooms'] =
        'The selected room is not available. Available alternatives: {$a->available_rooms}.';

// Show hidden/archived rooms toggle
$string['showhidden'] = 'Show hidden rooms';
$string['showonlyvisible'] = 'Show only visible rooms';
$string['hide'] = 'Hide';
$string['restore'] = 'Restore';
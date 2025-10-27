<?php
$string['pluginname'] = 'Payment Record Management';
$string['settings'] = 'Payment Services Settings';
$string['financeservices'] = 'Payment Services';
$string['enabled'] = 'Enable Payment Services';
$string['enableddesc'] = 'Enable or disable the payment services plugin.';
$string['courseid'] = 'Course Name (ID)';
$string['fundingtype'] = 'Funding Type';
$string['csv'] = 'CSV';
$string['pricerequested'] = 'Price Requested';
$string['notes'] = 'Notes';
$string['requeststatus'] = 'Request Status';
$string['user'] = 'User';
$string['datetimerequested'] = 'Date Requested';
$string['datetimeprocessed'] = 'Date Processed';
$string['norecords'] = 'No payment requests found.';
$string['w'] = 'Waiting';
$string['a'] = 'Accepted';
$string['r'] = 'Rejected';
$string['s'] = 'Payment Submitted';
$string['summary'] = 'Request Summary';
$string['submit'] = 'Submit Request';
$string['cancel'] = 'Cancel Request';
$string['newrequest'] = 'New Payment Request';
$string['financeRecords'] = 'Payment Records';
$string['actions'] = 'Actions';
$string['accept'] = 'Accept';
$string['reject'] = 'Reject';
$string['nounprocessedrequests'] = 'No unprocessed requests';
$string['showmore'] = 'Show more';
$string['showless'] = 'Show less';
$string['noprocessedrequests'] = 'No Processed requests';
$string['daterequested'] = 'Date Requested';
$string['add'] = 'New Request';
$string['list'] = 'All the Requests';
$string['course'] = 'Course';
$string['status'] = 'status';
$string['filter'] = 'filter';
// New strings for Clause and Date Required
$string['clause'] = 'Clause';
$string['daterequired'] = 'Date required';
$string['none'] = '-- None --';
$string['funding_type'] = 'Funding Type';
$string['clause_name'] = 'Clause Name';
$string['price_requested'] = 'Price Requested';
$string['date_time_requested'] = 'Date Time Requested';
$string['statusfilter'] = 'Status';
$string['allstatuses'] = '-- All Statuses --';
$string['allcourses'] = '-- All Courses --';
$string['allfundingtypes'] = '-- All Funding Types --';
$string['allclauses'] = '-- All Clauses --';
$string['managefundingtypes'] = 'Manage Funding Types';
$string['fundingtypeen'] = 'Funding Type (English)';
$string['fundingtypear'] = 'Funding Type (Arabic)';
$string['savechanges'] = 'Save changes';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['editfundingtype'] = 'Edit Funding Type';
$string['deletefundingtype'] = 'Delete Funding Type';
$string['confirmdelete'] = 'Are you sure you want to delete this Funding Type?';
$string['changessaved'] = 'Changes saved successfully';
$string['fundingtypedeleted'] = 'Funding Type deleted successfully';
$string['addfundingtype'] = 'Add Funding Type';
$string['addnewfundingtype'] = 'Register a new funding type for service management.';
$string['manageservices'] = 'Payment Services Management';
$string['vieweditfundingtypes'] = 'View, edit, and manage all available funding types.';
$string['manageclauses'] = 'Manage Clauses';
$string['vieweditclauses'] = 'View, edit, and manage all available payment clauses.';
$string['addclause'] = 'Add Clause';
$string['addnewclause'] = 'Register a new clause for payment services.';
$string['clausenameen'] = 'Clause Name (English)';
$string['clausenamear'] = 'Clause Name (Arabic)';
$string['actions'] = 'Actions';
$string['inserterror']       = 'Insert failed';
$string['editclause']         = 'Edit Clause';
$string['deleteclause']       = 'Delete Clause';
$string['clausedeleted']      = 'Clause deleted successfully.';
$string['newrequest'] = 'New Request';
$string['allrequests'] = 'All the Requests';
$string['managetab'] = 'Payment Services Management';
// Soft deletion strings
$string['hide'] = 'Hide';
$string['hidefundingtype'] = 'Hide Funding Type';
$string['hideclause'] = 'Hide Clause';
$string['confirmhide'] = 'Are you sure you want to hide this item? It will no longer appear in dropdown menus.';
$string['fundingtypehidden'] = 'Funding Type hidden successfully';
$string['clausehidden'] = 'Clause hidden successfully';
$string['restore'] = 'Restore';
$string['showhiddenrecords'] = 'Show hidden records';
$string['hidehiddenrecords'] = 'Hide hidden records';
$string['clauseamount'] = 'Amount';
$string['invalidnumber'] = 'Please enter a valid amount greater than zero.';

// Audit field strings
$string['createdby'] = 'Created by';
$string['createddate'] = 'Created on';
$string['modifiedby'] = 'Modified by';
$string['modifieddate'] = 'Modified on';
$string['initialamount'] = 'Initial Amount';
$string['currentamount'] = 'Current Amount';
$string['spendingdata'] = 'Spending Data';
$string['clauseyear'] = 'Clause Year';
$string['clauseyear_past_not_allowed'] = 'Past years are not allowed for new clauses.';
$string['clauseyear_unique'] = 'A clause with this name already exists for the selected year.';

// Event strings
$string['eventclausecreated'] = 'Payment clause created';
$string['eventclauseupdated'] = 'Payment clause updated';
$string['eventclausehidden'] = 'Payment clause hidden';
$string['eventclauserestored'] = 'Payment clause restored';

// Funding type event strings
$string['eventfundingtypecreated'] = 'Funding type created';
$string['eventfundingtypeupdated'] = 'Funding type updated';
$string['eventfundingtypehidden'] = 'Funding type hidden';
$string['eventfundingtyperestored'] = 'Funding type restored';

// Request event strings
$string['eventrequestcreated'] = 'Payment service request created';
$string['eventrequestupdated'] = 'Payment service request updated';
$string['eventrequeststatuschanged'] = 'Payment service request status changed';
$string['requestsubmitted'] = 'Your request has been submitted successfully!';

// Add missing strings for settings.php
$string['enable'] = 'Enable Payment Services';
$string['enabledesc'] = 'Enable or disable the payment services plugin.';
$string['path'] = 'Resource Path';
$string['pathdesc'] = 'Configure the path to payment service resources.';

// Capability strings
$string['financeservices:manage'] = 'Manage finance services';
$string['financeservices:view'] = 'View finance services';

// Workflow action strings
$string['request_approved'] = 'Request approved successfully';
$string['request_rejected'] = 'Request rejected successfully';
$string['rejectionnotprovided'] = 'Rejection reason is required';
$string['invalidaction'] = 'Invalid action';
$string['invalidsesskey'] = 'Invalid session key';
$string['unexpectederror'] = 'An unexpected error occurred';

// Status messages
$string['status_approved'] = 'Approved';
$string['status_rejected'] = 'Rejected';
$string['awaiting_approval'] = 'Awaiting Approval';
$string['rejection_reason'] = 'Rejection Reason';
$string['approval_note'] = 'Approval Note';
$string['previously_rejected'] = 'Previously Rejected';
$string['rejection_note_visible'] = 'Rejection note is visible to show why this request was previously rejected';
$string['notmodified'] = 'Not modified';

$string['clearfilters'] = 'Clear Filters ';


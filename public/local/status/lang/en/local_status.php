<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * English language strings for local_status
 *
 * @package   local_status
 */

// Plugin strings
$string['pluginname'] = 'Workflow Management System';
$string['workflowdashboard'] = 'Workflow Dashboard';

// Tab strings
$string['workflows'] = 'Workflows';
$string['workflowsteps'] = 'Workflow Steps';
$string['workflowtransitions'] = 'Transitions';
$string['workflowhistory'] = 'History';
$string['workflowtemplates'] = 'Templates';

// Workflow management
$string['manageworkflows'] = 'Manage Workflows';
$string['addworkflow'] = 'Add Workflow';
$string['editworkflow'] = 'Edit Workflow';
$string['copyworkflow'] = 'Copy Workflow';
$string['deleteworkflow'] = 'Delete Workflow';
$string['noworkflows'] = 'No workflows found. Create your first workflow to get started.';
$string['workflow_in_use'] = 'Cannot delete workflow as it is currently in use.';

// Workflow steps
$string['managesteps'] = 'Manage Steps';
$string['addstep'] = 'Add Step';
$string['editstep'] = 'Edit Step';
$string['deletestep'] = 'Delete Step';
$string['nosteps'] = 'No steps defined for this workflow.';
$string['stepsfor'] = 'Steps for: {$a}';
$string['dragtoReorder'] = 'Drag rows to reorder steps';

// Common fields
$string['name'] = 'Name';
$string['displayname'] = 'Display Name';
$string['displayname_en'] = 'Display Name (English)';
$string['displayname_ar'] = 'Display Name (Arabic)';
$string['plugin'] = 'Plugin';
$string['steps'] = 'Steps';
$string['status'] = 'Status';
$string['actions'] = 'Actions';
$string['capability'] = 'Capability';
$string['color'] = 'Color';
$string['flags'] = 'Flags';
$string['active'] = 'Active';
$string['inactive'] = 'Inactive';
$string['sortorder'] = 'Sort Order';
$string['sequence'] = 'Sequence';
$string['workflow'] = 'Workflow';
$string['icon'] = 'Icon';
$string['allstepsactive'] = 'All steps are active';

// Step flags
$string['initial'] = 'Initial';
$string['final'] = 'Final';

// Approval types
$string['approvaltype'] = 'Approval Type';
$string['capabilitybased'] = 'Capability-based';
$string['userbased'] = 'User-based';
$string['approval_any'] = 'Any user';
$string['approval_capability'] = 'Capability-based (legacy)';
$string['approval_user'] = 'Specific users';

// Dynamic Approver Management
$string['manageapprovers'] = 'Manage Approvers';
$string['stepinformation'] = 'Step Information';
$string['addapprover'] = 'Add Approver';
$string['selectuser'] = 'Select User';
$string['required'] = 'Required';
$string['optional'] = 'Optional';
$string['currentapprovers'] = 'Current Approvers';
$string['noapprovers'] = 'No approvers configured for this step.';
$string['approverorderinstructions'] = 'Approvers will be notified in the order shown. Use the arrow buttons to reorder them.';
$string['onlyuserbased_approvers'] = 'Approver management is only available for user-based approval steps.';
$string['moveup'] = 'Move Up';
$string['movedown'] = 'Move Down';
$string['confirmremoveapprover'] = 'Are you sure you want to remove this approver?';
$string['back'] = 'Back';

// Approver management messages
$string['approveradded'] = 'Approver added successfully';
$string['approverremoved'] = 'Approver removed successfully';
$string['approversreordered'] = 'Approvers reordered successfully';
$string['error_adding_approver'] = 'Error adding approver';
$string['error_removing_approver'] = 'Error removing approver';
$string['error_reordering_approvers'] = 'Error reordering approvers';
$string['approver_already_exists'] = 'User is already an approver for this step';

// Workflow instance management
$string['workflowstatus'] = 'Workflow Status';
$string['currentstep'] = 'Current Step';
$string['nextapprover'] = 'Next Approver';
$string['cannotapprove'] = 'You do not have permission to approve this request';
$string['cannotreject'] = 'You do not have permission to reject this request';
$string['workflowstarted'] = 'Workflow started';
$string['workflowapproved'] = 'Request approved';
$string['workflowrejected'] = 'Request rejected';
$string['movedtonextstep'] = 'Moved to next step';

// Workflow errors
$string['workflow_instance_exists'] = 'Workflow instance already exists for this record';
$string['no_initial_step'] = 'No initial step found for this workflow';
$string['cannot_approve'] = 'You cannot approve this request';
$string['cannot_reject'] = 'You cannot reject this request';

// API for other plugins
$string['sequential_workflow_created'] = 'Sequential workflow created successfully';
$string['workflow_started'] = 'Workflow started successfully';
$string['workflow_approval_processed'] = 'Workflow approval processed';

// Enhanced workflow display
$string['approvalsequence'] = 'Approval Sequence';
$string['approver'] = 'Approver';
$string['status_pending'] = 'Pending';
$string['status_inprogress'] = 'In Progress';
$string['status_approved'] = 'Approved';
$string['status_rejected'] = 'Rejected';
$string['waitingfor'] = 'Waiting for: {$a}';
$string['approvedby'] = 'Approved by: {$a}';
$string['rejectedby'] = 'Rejected by: {$a}';

// Quick workflow setup
$string['quicksetup'] = 'Quick Setup';
$string['createsequentialworkflow'] = 'Create Sequential Workflow';
$string['workflowname_placeholder'] = 'e.g., my_plugin_approval';
$string['workflowdisplay_placeholder'] = 'e.g., My Plugin Approval Process';
$string['selectapprovers'] = 'Select Approvers (in order)';
$string['addapprover_button'] = 'Add Approver';
$string['removeapprover_button'] = 'Remove';
$string['createworkflow_button'] = 'Create Workflow';
$string['workflowcreated_success'] = 'Workflow "{$a}" created successfully with {$b} approvers';

// Step positioning and reordering
$string['insertposition'] = 'Insert position';
$string['insertpositionhelp'] = 'Choose where to insert this new step in the workflow sequence.';
$string['selectposition'] = 'Select position...';
$string['firststep'] = 'As first step';
$string['beforestep'] = 'Before: {$a}';
$string['afterstep'] = 'After: {$a}';

// Form help buttons
$string['stepnamehelp'] = 'Unique identifier for this step (cannot be changed after creation).';
$string['namehelp'] = 'Unique identifier for this workflow (cannot be changed after creation).';
$string['displayname_ar_help'] = 'Arabic display name (required).';
$string['pluginhelp'] = 'Related plugin name (optional).';
$string['capabilityhelp'] = 'Moodle capability required for approval (e.g., moodle/site:config).';
$string['approvaltypehelp'] = 'How approvals are handled for this step.';

// Workflow forms
$string['workflowname'] = 'Workflow Name';
$string['workflowname_help'] = 'Internal name for the workflow (used in code)';
$string['workflowdisplayname'] = 'Display Name';
$string['workflowdisplayname_help'] = 'Name shown to users';
$string['workflowplugin'] = 'Plugin';
$string['workflowplugin_help'] = 'Plugin that owns this workflow';
$string['workflowactive'] = 'Active';
$string['workflowactive_help'] = 'Whether this workflow is currently active';

// Step forms
$string['stepname'] = 'Step Name';
$string['stepname_help'] = 'Internal name for the step';
$string['stepdisplayname'] = 'Display Name';
$string['stepdisplayname_help'] = 'Name shown to users';
$string['stepcapability'] = 'Required Capability';
$string['stepcapability_help'] = 'Moodle capability required to approve this step';
$string['stepcolor'] = 'Color';
$string['stepcolor_help'] = 'Color used in UI elements for this step';
$string['stepicon'] = 'Icon';
$string['stepicon_help'] = 'Icon class used for this step';
$string['stepinitial'] = 'Initial Step';
$string['stepinitial_help'] = 'Is this the first step in the workflow?';
$string['stepfinal'] = 'Final Step';
$string['stepfinal_help'] = 'Is this the last step in the workflow?';

// Enhanced step form fields
$string['mustspecifyworkflow'] = 'You must specify a workflow to add a step to';
$string['stepnameexists'] = 'A step with this name already exists in this workflow';
$string['invalidcolor'] = 'Invalid color format. Please use hex format (e.g., #ff0000)';
$string['stepinuse'] = 'Cannot delete step as it is currently in use';
$string['createstep'] = 'Create Step';
$string['updatestep'] = 'Update Step';
$string['stepflags'] = 'Step Flags';
$string['isinitial'] = 'Initial Step';
$string['isinitial_desc'] = 'Make this the starting step of the workflow';
$string['isinitial_help'] = 'The initial step is where all workflow instances begin. There can only be one initial step per workflow.';
$string['isfinal'] = 'Final Step';
$string['isfinal_desc'] = 'Make this the ending step of the workflow';
$string['isfinal_help'] = 'The final step is where workflow instances end. There can only be one final step per workflow.';
$string['initialstepexists'] = 'An initial step already exists for this workflow';
$string['finalstepexists'] = 'A final step already exists for this workflow';
$string['stepactive_desc'] = 'Whether this step is currently active and can be used';

// Delete step confirmation
$string['confirmdeletestep'] = 'Are you sure you want to delete the step "{$a->stepname}" from workflow "{$a->workflowname}"?';
$string['deletestepwillapprovers'] = 'This will also remove {$a} approver(s) assigned to this step.';

// Workflow selection
$string['selectworkflow'] = 'Select Workflow';

// Messages
$string['workflowcreated'] = 'Workflow created successfully.';
$string['workflowupdated'] = 'Workflow updated successfully.';
$string['workflowdeleted'] = 'Workflow deleted successfully';
$string['workflowcopied'] = 'Workflow copied successfully';
$string['stepcreated'] = 'Step created successfully.';
$string['stepupdated'] = 'Step updated successfully.';
$string['stepdeleted'] = 'Step deleted successfully';
$string['stepsreordered'] = 'Steps reordered successfully';

// Error messages
$string['invalidworkflow'] = 'Invalid workflow specified';
$string['invalidstep'] = 'Invalid step specified';
$string['cannotdeletestep'] = 'Cannot delete step as it is currently in use';
$string['duplicateworkflowname'] = 'A workflow with this name already exists.';
$string['duplicatestepname'] = 'A step with this name already exists in this workflow.';
$string['capabilityrequired'] = 'Capability is required for capability-based approval.';
$string['error_reordering_steps'] = 'Error occurred while reordering steps.';
$string['missingworkflow'] = 'Workflow ID is required.';
$string['workflow_has_steps'] = 'Cannot delete workflow as it contains steps.';

// Enhanced error handling
$string['error_occurred'] = 'An error occurred: {$a}';
$string['cannot_delete_active_workflow'] = 'Cannot delete active workflow. Please deactivate it first.';
$string['step_has_dependencies'] = 'Cannot delete step as it has dependencies or is in use.';

// Status messages
$string['workflowstatuschanged'] = 'Workflow status changed to {$a}';
$string['stepstatuschanged'] = 'Step status changed to {$a}';
$string['selectworkflowfirst'] = 'Please select a workflow from the dropdown above to manage its steps.';
$string['nostepshelp'] = 'This workflow has no steps yet. Use the "Add Step" button above to create the first step and start building your workflow.';

// Color names for dropdown
$string['color_gray'] = 'Gray (Default)';
$string['color_yellow'] = 'Yellow (Pending)';
$string['color_blue'] = 'Blue (Review)';
$string['color_green'] = 'Green (Approved)';
$string['color_red'] = 'Red (Rejected)';
$string['color_orange'] = 'Orange';
$string['color_purple'] = 'Purple';
$string['color_teal'] = 'Teal';

// Action buttons
$string['go'] = 'Go';
$string['save'] = 'Save';
$string['cancel'] = 'Cancel';
$string['edit'] = 'Edit';
$string['delete'] = 'Delete';
$string['copy'] = 'Copy';
$string['activate'] = 'Activate';
$string['deactivate'] = 'Deactivate';
$string['activated'] = 'activated';
$string['deactivated'] = 'deactivated';
$string['confirmdelete'] = 'Are you sure you want to delete this item? This action cannot be undone.';
$string['view'] = 'View';
$string['approve'] = 'Approve';
$string['reject'] = 'Reject';
$string['submit'] = 'Submit';

// Transition management
$string['transitionscomingsoon'] = 'Transition management coming soon';
$string['historycomingsoon'] = 'History viewing coming soon';
$string['templatescomingsoon'] = 'Workflow templates coming soon';

// Capabilities
$string['status:manage'] = 'Manage workflow system';
$string['status:viewhistory'] = 'View workflow history';

// New Department-Specific Capabilities
$string['status:manage_workflows'] = 'Manage workflow system';
$string['status:view_all_requests'] = 'View all workflow requests';

// Workflow Step Display Names
// Annual Plan Workflow Steps
$string['status:annual_plan_workflow_step1'] = 'Annual Plan - Step 1: leader 1 Review';
$string['status:annual_plan_workflow_step2'] = 'Annual Plan - Step 2: leader 2 Review';
$string['status:annual_plan_workflow_step3'] = 'Annual Plan - Step 3: leader 3 Review';
$string['status:annual_plan_workflow_step4'] = 'Annual Plan - Step 4: boss Review';

// Classroom Workflow Steps
$string['status:classroom_workflow_step1'] = 'Classroom Booking - Step 1: leader 1 Review';
$string['status:classroom_workflow_step2'] = 'Classroom Booking - Step 2: leader 2 Review';
$string['status:classroom_workflow_step3'] = 'Classroom Booking - Step 3: leader 3 Review';
$string['status:classroom_workflow_step4'] = 'Classroom Booking - Step 4: boss Review';

// Computer Workflow Steps
$string['status:computer_workflow_step1'] = 'Computer Service - Step 1: leader 1 Review';
$string['status:computer_workflow_step2'] = 'Computer Service - Step 2: leader 2 Review';
$string['status:computer_workflow_step3'] = 'Computer Service - Step 3: leader 3 Review';
$string['status:computer_workflow_step4'] = 'Computer Service - Step 4: boss Review';

// Course Workflow Steps
$string['status:course_workflow_step1'] = 'Course Request - Step 1: leader 1 Review';
$string['status:course_workflow_step2'] = 'Course Request - Step 2: leader 2 Review';
$string['status:course_workflow_step3'] = 'Course Request - Step 3: leader 3 Review';
$string['status:course_workflow_step4'] = 'Course Request - Step 4: boss Review';

// Default Workflow Steps
$string['status:default_workflow_step1'] = 'Default Workflow - Step 1: leader 1 Review';
$string['status:default_workflow_step2'] = 'Default Workflow - Step 2: leader 2 Review';
$string['status:default_workflow_step3'] = 'Default Workflow - Step 3: leader 3 Review';
$string['status:default_workflow_step4'] = 'Default Workflow - Step 4: boss Review';

// Finance Workflow Steps
$string['status:finance_workflow_step1'] = 'Finance Request - Step 1: leader 1 Review';
$string['status:finance_workflow_step2'] = 'Finance Request - Step 2: leader 2 Review';
$string['status:finance_workflow_step3'] = 'Finance Request - Step 3: leader 3 Review';
$string['status:finance_workflow_step4'] = 'Finance Request - Step 4: boss Review';

// Reports Workflow Steps
$string['status:reports_workflow_step1'] = 'Report Request - Step 1: leader 1 Review';
$string['status:reports_workflow_step2'] = 'Report Request - Step 2: leader 2 Review';
$string['status:reports_workflow_step3'] = 'Report Request - Step 3: leader 3 Review';

// Residence Workflow Steps
$string['status:residence_workflow_step1'] = 'Residence Booking - Step 1: leader 1 Review';
$string['status:residence_workflow_step2'] = 'Residence Booking - Step 2: leader 2 Review';
$string['status:residence_workflow_step3'] = 'Residence Booking - Step 3: leader 3 Review';
$string['status:residence_workflow_step4'] = 'Residence Booking - Step 4: boss Review';

// Training Workflow Steps
$string['status:training_workflow_step1'] = 'Training Request - Step 1: leader 1 Review';
$string['status:training_workflow_step2'] = 'Training Request - Step 2: leader 2 Review';
$string['status:training_workflow_step3'] = 'Training Request - Step 3: leader 3 Review';
$string['status:training_workflow_step4'] = 'Training Request - Step 4: boss Review';

// Participants Workflow Steps
$string['status:participants_workflow_step1'] = 'Participants Management - Step 1: leader 1 Review';
$string['status:participants_workflow_step2'] = 'Participants Management - Step 2: leader 2 Review';
$string['status:participants_workflow_step3'] = 'Participants Management - Step 3: leader 3 Review';
$string['status:participants_workflow_step4'] = 'Participants Management - Step 4: boss Review';

// Privacy
$string['privacy:metadata:local_status_history'] = 'Records of workflow transitions';
$string['privacy:metadata:local_status_history:user_id'] = 'User who made the transition';
$string['privacy:metadata:local_status_history:note'] = 'Note provided by user';
$string['privacy:metadata:local_status_history:ip_address'] = 'IP address of user';
$string['privacy:metadata:local_status_history:user_agent'] = 'Browser information';
$string['privacy:metadata:local_status_history:timecreated'] = 'When the transition was made';

// Settings
$string['settings'] = 'Settings';
$string['enableaudit'] = 'Enable audit trail';
$string['enableaudit_desc'] = 'Record all workflow transitions for audit purposes';
$string['auditretentiondays'] = 'Audit retention (days)';
$string['auditretentiondays_desc'] = 'Number of days to keep audit records (0 = keep forever)';
$string['enablenotifications'] = 'Enable notifications';
$string['enablenotifications_desc'] = 'Send email notifications when workflow transitions occur';

// Common workflow status terms
$string['pending'] = 'Pending';
$string['submitted'] = 'Submitted';
$string['underreview'] = 'Under Review';
$string['approved'] = 'Approved';
$string['rejected'] = 'Rejected';
$string['cancelled'] = 'Cancelled';

// Enhanced step management strings
$string['cannot_hide_critical_step'] = 'Cannot hide initial or final steps as they are critical to the workflow.';
$string['cannot_delete_critical_step'] = 'Cannot delete initial or final steps as they are critical to the workflow.';
$string['cannot_move_critical_step'] = 'Cannot move initial or final steps as their position is automatically managed.';
$string['cannot_modify_critical_step'] = 'Cannot modify initial or final steps. These are automatically managed by the system.';
$string['cannot_edit_protected'] = 'Protected step - editing is restricted';
$string['cannot_delete_protected'] = 'Protected step - cannot be deleted';
$string['step_in_use'] = 'Cannot delete step as it is currently in use by workflow instances.';

// Step protection and flags
$string['protected_step'] = 'This step is protected';
$string['protected_step_warning'] = 'This is a protected step. Initial and final steps are automatically managed by the system and have limited editing options.';
$string['modifiable'] = 'Modifiable';
$string['hidden'] = 'Hidden';
$string['stepposition'] = 'Step Position';
$string['single_step_workflow'] = 'This is both the initial and final step (single step workflow)';
$string['initial_step_info'] = 'This is the initial step - requests start here';
$string['final_step_info'] = 'This is the final step - requests end here';
$string['intermediate_step_info'] = 'This is an intermediate step - you can modify or hide it';

// Visual settings
$string['visualsettings'] = 'Visual Settings';
$string['stepcolorhelp'] = 'Hex color code for this step (e.g., #007bff). Used in workflow visualizations.';
$string['stepiconhelp'] = 'FontAwesome icon class for this step. Used in workflow displays.';
$string['stepactivehelp'] = 'Inactive steps are hidden from normal workflow execution but can be reactivated later.';

// Icon options
$string['noicon'] = 'No Icon';
$string['checkicon'] = 'Check Mark';
$string['clockicon'] = 'Clock';
$string['usericon'] = 'User';
$string['usersicon'] = 'Users';
$string['cogicon'] = 'Settings';
$string['staricon'] = 'Star';
$string['flagicon'] = 'Flag';

// Step actions
$string['hidestep'] = 'Hide Step';
$string['showstep'] = 'Show Step';
$string['confirmhidestep'] = 'Are you sure you want to hide this step? It will not be available in new workflow instances.';
$string['stephidden'] = 'Step hidden successfully';
$string['stepshown'] = 'Step shown successfully';

// Workflow structure display
$string['workflowstructure'] = 'Workflow Structure';
$string['totalsteps'] = 'Total Steps';
$string['activesteps'] = 'Active Steps';
$string['actualworkflowsteps'] = 'Actual Workflow Steps';
$string['activeworkflowsteps'] = 'Active Workflow Steps';
$string['modifiablesteps'] = 'Modifiable Steps';
$string['showinactivesteps'] = 'Show Inactive Steps';
$string['hideinactivesteps'] = 'Hide Inactive Steps';
$string['systemflags_not_counted'] = 'Note: Initial and final flag steps are system markers and not counted as workflow steps.';

// Step counting clarification
$string['workflow_step_count_help'] = 'This count excludes initial and final flag steps, which are system-managed markers.';
$string['actual_steps_explanation'] = 'Actual workflow steps are the intermediate steps that users interact with. Initial and final steps are just system flags.';
$string['system_managed_flags'] = 'System-managed flags (not counted as steps)';

// Step management help
$string['stepmanagementhelp'] = 'Step Management Guide';
$string['protectedstepshelp'] = 'Initial and final steps are automatically managed and cannot be deleted or moved.';
$string['modifiablestepshelp'] = 'Intermediate steps can be edited, hidden, deleted, or reordered as needed.';
$string['hiddenstepshelp'] = 'Hidden steps are not used in new workflows but existing instances continue normally.';

// Reordering
$string['reordersteps'] = 'Reorder Steps';
$string['dragdropsteps'] = 'Drag and drop to reorder steps';

// Protected step editing
$string['protected_step_updated'] = 'Protected step updated successfully (limited changes only)';
$string['protected_step_limited_editing'] = 'This is a protected step. You can only modify the display names. Other properties are automatically managed by the system.';
$string['step_deactivation_info'] = 'Step Deactivation Available';
$string['step_deactivation_help'] = 'You can deactivate this step by unchecking the "Active" checkbox below. Deactivated steps are hidden from new workflow instances but don\'t affect existing ones.';

// Quick deactivation actions
$string['deactivate_step'] = 'Deactivate Step';
$string['activate_step'] = 'Activate Step';
$string['step_deactivated'] = 'Step deactivated successfully';
$string['step_activated'] = 'Step activated successfully';

// Additional actions section
$string['additionalactions'] = 'Additional Actions';
$string['confirmdeactivatestep'] = 'Are you sure you want to deactivate this step? It will be hidden from new workflow instances.'; 
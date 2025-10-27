<?php
// ============================================================================
//  Local Status – Installation Script
//  Populates initial workflow types and steps for common use cases
// ============================================================================

defined('MOODLE_INTERNAL') || die();

function xmldb_local_status_install() {
    global $DB;

    // Step 1: Insert workflow types into local_status_type
    $types = [
        1 => ['course_workflow', 'Course', 'دورة'],
        2 => ['finance_workflow', 'Finance Services', 'خدمات المالية'],
        3 => ['residence_workflow', 'Residence Booking', 'حجز الإقامة'],
        4 => ['computer_workflow', 'Computer Service', 'خدمة الحاسب الآلي'],
        5 => ['reports_workflow', 'Reports', 'التقارير'],
        6 => ['training_workflow', 'Training Services', 'خدمات التدريب'],
        7 => ['annual_plan_workflow', 'Annual Plan', 'الخطة السنوية'],
        8 => ['classroom_workflow', 'Classroom Booking', 'حجز القاعات'],
        9 => ['participants_workflow', 'Participants Management', 'إدارة المشاركين'],
    ];

    foreach ($types as $id => [$name, $display_en, $display_ar]) {
        $type = new stdClass();
        $type->name = $name;
        $type->display_name_en = $display_en;
        $type->display_name_ar = $display_ar;
        $type->plugin_name = '';
        $type->is_active = 1;
        $type->sort_order = $id;
        $type->timecreated = time();
        
        // Insert and get the actual ID (don't force specific IDs)
        $type_id = $DB->insert_record('local_status_type', $type);
        
        // Step 2: Insert workflow steps for this type with department-specific capabilities
        $statuses = get_workflow_steps_for_type($name);

        $seq = 1;
        foreach ($statuses as $status_data) {
            [$step_name, $display_en, $display_ar, $color, $icon, $is_initial, $is_final, $capability, $approval_type] = $status_data;

            $record = new stdClass();
            $record->type_id = $type_id;
            $record->seq = $seq;
            $record->name = $step_name;
            $record->display_name_en = $display_en;
            $record->display_name_ar = $display_ar;
            $record->capability = $capability;
            $record->approval_type = $approval_type ?? 'capability';
            $record->color = $color;
            $record->icon = $icon;
            $record->is_initial = $is_initial;
            $record->is_final = $is_final;
            $record->is_active = 1;
            $record->timecreated = time();

            $step_id = $DB->insert_record('local_status', $record);
            $seq++;
        }
    }

    // Step 3: Create basic transitions (approve/reject patterns)
    $workflow_types = $DB->get_records('local_status_type');
    
    foreach ($workflow_types as $workflow_type) {
        $steps = $DB->get_records('local_status', ['type_id' => $workflow_type->id], 'seq ASC');
        $steps_array = array_values($steps);
        
        // Create forward transitions (approve)
        for ($i = 0; $i < count($steps_array) - 1; $i++) {
            if ($steps_array[$i]->name !== '-------------') { // Skip placeholder steps
                $transition = new stdClass();
                $transition->type_id = $workflow_type->id;
                $transition->from_status_id = $steps_array[$i]->id;
                $transition->to_status_id = $steps_array[$i + 1]->id;
                $transition->transition_type = 'approve';
                $transition->display_name_en = 'Approve';
                $transition->display_name_ar = 'موافقة';
                $transition->required_capability = $steps_array[$i]->capability;
                $transition->is_active = 1;
                $transition->timecreated = time();
                
                $DB->insert_record('local_status_transition', $transition);
            }
        }
        
        // Create reject transition (from any step to initial)
        $initial_step = reset($steps_array);
        foreach ($steps_array as $step) {
            if ($step->name !== '-------------' && !$step->is_initial && !$step->is_final) {
                $transition = new stdClass();
                $transition->type_id = $workflow_type->id;
                $transition->from_status_id = $step->id;
                $transition->to_status_id = $initial_step->id;
                $transition->transition_type = 'reject';
                $transition->display_name_en = 'Reject';
                $transition->display_name_ar = 'رفض';
                $transition->required_capability = $step->capability;
                $transition->is_active = 1;
                $transition->timecreated = time();
                
                $DB->insert_record('local_status_transition', $transition);
            }
        }
    }
    
    return true;
}

/**
 * Get workflow steps configuration for each department type
 */
function get_workflow_steps_for_type($workflow_name) {
    switch ($workflow_name) {
        case 'course_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:course_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:course_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:course_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:course_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        case 'finance_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:finance_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:finance_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:finance_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:finance_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        case 'residence_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:residence_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:residence_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:residence_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:residence_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        case 'computer_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:computer_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:computer_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:computer_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:computer_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        case 'reports_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:reports_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:reports_workflow_step2', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:reports_workflow_step3', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        case 'training_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:training_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:training_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:training_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:training_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        case 'annual_plan_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:annual_plan_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:annual_plan_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:annual_plan_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:annual_plan_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        case 'classroom_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:classroom_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:classroom_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:classroom_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:classroom_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        case 'participants_workflow':
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:participants_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:participants_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:participants_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:participants_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
            
        default:
            // Fallback to default workflow structure
            return [
                ['request_submitted', 'Request Submitted', 'تم تقديم الطلب', '#6c757d', 'fa-file-alt', 1, 0, null, 'any'],
                ['leader1_review', 'Leader 1', 'قيد اعتماد ض.د', '#17a2b8', 'fa-clock', 0, 0, 'local/status:default_workflow_step1', 'capability'],
                ['leader2_review', 'Leader 2', 'قيد اعتماد ض.ق', '#ffc107', 'fa-hourglass-half', 0, 0, 'local/status:default_workflow_step2', 'capability'],
                ['leader3_review', 'Leader 3', 'قيد اعتماد ض.ق.خ', '#fd7e14', 'fa-spinner', 0, 0, 'local/status:default_workflow_step3', 'capability'],
                ['boss_review', 'BOSS', 'قيد اعتماد ر.د', '#6f42c1', 'fa-cog', 0, 0, 'local/status:default_workflow_step4', 'capability'],
                ['approved', 'Approved', 'تم الإعتماد', '#28a745', 'fa-check-circle', 0, 1, null, 'any'],
                ['rejected', 'Rejected', 'تم الرفض', '#dc3545', 'fa-times-circle', 0, 1, null, 'any'],
            ];
    }
}

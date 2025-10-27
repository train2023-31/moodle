<?php
// Diagnostic script for workflow issues
// Run this from command line: php diagnostic_workflow.php

define('CLI_SCRIPT', true);
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/status/classes/status_workflow_manager.php');

use local_status\status_workflow_manager;

global $DB, $USER;

echo "=== WORKFLOW DIAGNOSTIC TOOL ===\n\n";

// Check classroom workflow (type_id = 8)
$workflow_type_id = 8;

echo "1. Checking Classroom Workflow (ID: $workflow_type_id)\n";
echo "================================================\n";

// Get workflow type
$workflow_type = $DB->get_record('local_status_type', ['id' => $workflow_type_id]);
if (!$workflow_type) {
    echo "❌ ERROR: No workflow type found with ID $workflow_type_id\n";
    exit(1);
}

echo "✅ Workflow Type: {$workflow_type->display_name_en}\n\n";

// Get all steps in this workflow
$steps = $DB->get_records('local_status', ['type_id' => $workflow_type_id], 'seq ASC');
if (empty($steps)) {
    echo "❌ ERROR: No steps found for workflow $workflow_type_id\n";
    exit(1);
}

echo "2. Workflow Steps Analysis\n";
echo "==========================\n";

foreach ($steps as $step) {
    echo "Step #{$step->seq}: {$step->display_name_en}\n";
    echo "  - ID: {$step->id}\n";
    echo "  - Name: {$step->name}\n";
    echo "  - Is Initial: " . ($step->is_initial ? 'YES' : 'NO') . "\n";
    echo "  - Is Final: " . ($step->is_final ? 'YES' : 'NO') . "\n";
    echo "  - Is Active: " . ($step->is_active ? 'YES' : 'NO') . "\n";
    echo "  - Approval Type: " . ($step->approval_type ?? 'NOT SET') . "\n";
    echo "  - Capability: " . ($step->capability ?? 'NOT SET') . "\n";
    
    // Test approval permissions
    if ($step->is_initial || (!$step->is_initial && !$step->is_final)) {
        echo "  - Can Current User Approve: ";
        
        // Test with a dummy booking ID (we'll use 1 for testing)
        try {
            $can_approve = status_workflow_manager::can_user_approve($workflow_type_id, 1, $USER->id);
            echo ($can_approve ? '✅ YES' : '❌ NO') . "\n";
        } catch (Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
        }
        
        // Test legacy method
        echo "  - Legacy Can Approve: ";
        try {
            $can_approve_legacy = local_status_can_approve($step->id);
            echo ($can_approve_legacy ? '✅ YES' : '❌ NO') . "\n";
        } catch (Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
        }
    }
    echo "\n";
}

// Check for actual booking instances
echo "3. Active Booking Instances\n";
echo "===========================\n";

$instances = $DB->get_records_sql("
    SELECT i.*, s.display_name_en as status_name, s.approval_type, s.capability
    FROM {local_status_instance} i
    LEFT JOIN {local_status} s ON i.current_status_id = s.id
    WHERE i.type_id = ? AND (i.is_completed = 0 OR i.is_completed IS NULL) AND (i.is_closed = 0 OR i.is_closed IS NULL)
    ORDER BY i.id DESC
    LIMIT 10
", [$workflow_type_id]);

if (empty($instances)) {
    echo "ℹ️  No active workflow instances found.\n\n";
} else {
    foreach ($instances as $instance) {
        echo "Instance #{$instance->id} (Record: {$instance->record_id})\n";
        echo "  - Current Status: {$instance->status_name}\n";
        echo "  - Approval Type: " . ($instance->approval_type ?? 'NOT SET') . "\n";
        echo "  - Capability: " . ($instance->capability ?? 'NOT SET') . "\n";
        echo "  - Created: " . date('Y-m-d H:i:s', $instance->timecreated) . "\n";
        
        // Test approval for this specific instance
        echo "  - Can Current User Approve This: ";
        try {
            $can_approve = status_workflow_manager::can_user_approve($workflow_type_id, $instance->record_id, $USER->id);
            echo ($can_approve ? '✅ YES' : '❌ NO') . "\n";
        } catch (Exception $e) {
            echo "❌ ERROR: " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
}

// Recommendations
echo "4. Recommendations\n";
echo "==================\n";

$initial_step = null;
$has_any_approval = false;

foreach ($steps as $step) {
    if ($step->is_initial) {
        $initial_step = $step;
    }
    if ($step->approval_type === 'any') {
        $has_any_approval = true;
    }
}

if ($initial_step) {
    echo "Initial Step Analysis:\n";
    if (empty($initial_step->capability) && $initial_step->approval_type !== 'any') {
        echo "❌ PROBLEM: Initial step has no capability and approval_type is not 'any'\n";
        echo "   SOLUTION: Either set approval_type='any' or set a proper capability\n";
    } else if ($initial_step->approval_type === 'any') {
        echo "✅ GOOD: Initial step allows any user to approve\n";
    } else {
        echo "ℹ️  Initial step requires capability: {$initial_step->capability}\n";
    }
} else {
    echo "❌ PROBLEM: No initial step found!\n";
}

if (!$has_any_approval) {
    echo "\nℹ️  No steps use 'any' approval type. All steps require specific capabilities.\n";
} else {
    echo "\n✅ Some steps use 'any' approval type for flexibility.\n";
}

echo "\n=== DIAGNOSTIC COMPLETE ===\n"; 
<?php
// ============================================================================
//  Local Status – Workflows Renderer
//  Handles rendering of the workflows management tab
// ============================================================================

require_once($CFG->dirroot . '/local/status/classes/workflow_dashboard_manager.php');

use local_status\workflow_dashboard_manager;

/**
 * Workflows renderer class
 * Handles all rendering logic for the workflows management tab
 */
class workflows_renderer {
    
    /**
     * Render the workflows management tab
     * 
     * @return string HTML output
     */
    public function render(): string {
        global $OUTPUT;
        
        try {
            $output = '';
            
            // Header with add button
            $output .= $this->render_header();
            
            // Get all workflows
            $workflows = workflow_dashboard_manager::get_workflow_types(null, false);
            
            if (empty($workflows)) {
                $output .= $OUTPUT->notification(get_string('noworkflows', 'local_status'), 'info');
                return $output;
            }
            
            // Workflows table
            $output .= $this->render_workflows_table($workflows);
            
            return $output;
            
        } catch (Exception $e) {
            debugging('Error in workflows_renderer::render(): ' . $e->getMessage(), DEBUG_DEVELOPER);
            return $OUTPUT->notification(
                get_string('errorloadingworkflows', 'local_status') . ': ' . $e->getMessage(), 
                'error'
            );
        }
    }
    
    /**
     * Render the header section with add button
     * 
     * @return string HTML output
     */
    private function render_header(): string {
        $output = html_writer::start_div('d-flex justify-content-between align-items-center mb-3');
        $output .= html_writer::tag('h2', get_string('manageworkflows', 'local_status'));
        $output .= html_writer::link(
            new moodle_url('/local/status/pages/forms/workflow_form.php'),
            get_string('addworkflow', 'local_status'),
            ['class' => 'btn btn-primary']
        );
        $output .= html_writer::end_div();
        
        return $output;
    }
    
    /**
     * Render the workflows table
     * 
     * @param array $workflows Array of workflow objects
     * @return string HTML output
     */
    private function render_workflows_table(array $workflows): string {
        global $OUTPUT;
        
        $table = new html_table();
        $table->head = [
            get_string('name', 'local_status'),
            get_string('displayname', 'local_status'),
            get_string('plugin', 'local_status'),
            get_string('steps', 'local_status'),
            get_string('status', 'local_status'),
            get_string('actions', 'local_status')
        ];
        $table->attributes['class'] = 'table table-striped';
        
        foreach ($workflows as $workflow) {
            try {
                $table->data[] = $this->render_workflow_row($workflow);
            } catch (Exception $e) {
                debugging('Error rendering workflow row for ID ' . $workflow->id . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
                // Add error row instead of breaking the entire table
                $table->data[] = $this->render_error_row($workflow, $e->getMessage());
            }
        }
        
        return html_writer::table($table);
    }
    
    /**
     * Render a single workflow row
     * 
     * @param object $workflow Workflow object
     * @return array Table row data
     */
    private function render_workflow_row(object $workflow): array {
        global $OUTPUT;
        
        // Count only actual workflow steps (exclude system flag steps)
                    $steps_count = workflow_dashboard_manager::get_actual_step_count($workflow->id, false);
        
        $status_badge = $workflow->is_active ? 
            html_writer::span(get_string('active', 'local_status'), 'badge badge-success') :
            html_writer::span(get_string('inactive', 'local_status'), 'badge badge-secondary');
        
        $actions = $this->render_workflow_actions($workflow);
        
        // Get localized display name based on current language
        $current_lang = current_language();
        $display_name = ($current_lang === 'ar' && !empty($workflow->display_name_ar)) 
            ? format_string($workflow->display_name_ar)
            : format_string($workflow->display_name_en);
        
        return [
            format_string($workflow->name),
            $display_name,
            $workflow->plugin_name ?: '-',
            $steps_count,
            $status_badge,
            implode(' ', $actions)
        ];
    }
    
    /**
     * Render workflow action buttons
     * 
     * @param object $workflow Workflow object
     * @return array Array of action HTML strings
     */
    private function render_workflow_actions(object $workflow): array {
        global $OUTPUT;
        
        $actions = [];
        
        // Edit workflow
        $actions[] = html_writer::link(
            new moodle_url('/local/status/pages/forms/workflow_form.php', ['id' => $workflow->id]),
            $OUTPUT->pix_icon('t/edit', get_string('edit')),
            ['title' => get_string('edit'), 'class' => 'btn btn-sm btn-outline-secondary']
        );
        
        // Manage steps
        $actions[] = html_writer::link(
            new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps', 'workflow_id' => $workflow->id]),
            $OUTPUT->pix_icon('t/collapsed', get_string('managesteps', 'local_status')),
            ['title' => get_string('managesteps', 'local_status'), 'class' => 'btn btn-sm btn-outline-info']
        );
        
        // Toggle active/inactive
        $toggle_url = new moodle_url('/local/status/pages/actions/workflow_actions.php', [
            'action' => 'toggle_workflow',
            'workflow_id' => $workflow->id,
            'sesskey' => sesskey()
        ]);
        
        $toggle_icon = $workflow->is_active ? 't/hide' : 't/show';
        $toggle_title = $workflow->is_active ? get_string('deactivate', 'local_status') : get_string('activate', 'local_status');
        $toggle_class = $workflow->is_active ? 'btn-warning' : 'btn-success';
        
        $actions[] = html_writer::link(
            $toggle_url,
            $OUTPUT->pix_icon($toggle_icon, $toggle_title),
            ['title' => $toggle_title, 'class' => 'btn btn-sm btn-outline-' . str_replace('btn-', '', $toggle_class)]
        );
        
        // Delete workflow (only if inactive and no steps in use)
        if (!$workflow->is_active) {
            $delete_url = new moodle_url('/local/status/pages/actions/workflow_actions.php', [
                'action' => 'confirm_delete_workflow',
                'workflow_id' => $workflow->id
            ]);
            
            $actions[] = html_writer::link(
                $delete_url,
                $OUTPUT->pix_icon('t/delete', get_string('delete')),
                ['title' => get_string('delete'), 'class' => 'btn btn-sm btn-outline-danger']
            );
        }
        
        return $actions;
    }
    
    /**
     * Render an error row when a workflow cannot be rendered properly
     * 
     * @param object $workflow Workflow object
     * @param string $error_message Error message
     * @return array Table row data
     */
    private function render_error_row(object $workflow, string $error_message): array {
        $error_display = html_writer::span(
            get_string('errorloadingworkflow', 'local_status') . ': ' . $error_message,
            'text-danger'
        );
        
        return [
            format_string($workflow->name ?? 'Unknown'),
            $error_display,
            '-',
            '-',
            html_writer::span(get_string('error'), 'badge badge-danger'),
            '-'
        ];
    }
} 
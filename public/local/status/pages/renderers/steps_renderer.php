<?php
// ============================================================================
//  Local Status – Steps Renderer
//  Handles rendering of the workflow steps management tab
// ============================================================================

require_once($CFG->dirroot . '/local/status/classes/workflow_dashboard_manager.php');

use local_status\workflow_dashboard_manager;

/**
 * Steps renderer class
 * Handles all rendering logic for the workflow steps management tab
 */
class steps_renderer {
    
    /**
     * Render the workflow steps management tab
     * 
     * @param int $workflow_id Current workflow ID
     * @return string HTML output
     */
    public function render(int $workflow_id = 0): string {
        global $OUTPUT, $DB;
        
        try {
            $output = '';
            
            // Workflow selector
            $workflows = workflow_dashboard_manager::get_workflow_types(null, false);
            if (empty($workflows)) {
                return $this->render_no_workflows_message();
            }
            
            // Workflow selection form
            $output .= $this->render_workflow_selector($workflows, $workflow_id);
            
            if (!$workflow_id) {
                $output .= html_writer::div(
                    get_string('selectworkflowfirst', 'local_status'),
                    'alert alert-info'
                );
                return $output;
            }
            
            // Get workflow details
            $workflow = $DB->get_record('local_status_type', ['id' => $workflow_id]);
            if (!$workflow) {
                return $OUTPUT->notification(get_string('invalidworkflow', 'local_status'), 'error');
            }
            
            // Render steps section
            $output .= $this->render_steps_section($workflow);
            
            return $output;
            
        } catch (Exception $e) {
            debugging('Error in steps_renderer::render(): ' . $e->getMessage(), DEBUG_DEVELOPER);
            return $OUTPUT->notification(
                get_string('errorloadingsteps', 'local_status') . ': ' . $e->getMessage(), 
                'error'
            );
        }
    }
    
    /**
     * Render message when no workflows exist
     * 
     * @return string HTML output
     */
    private function render_no_workflows_message(): string {
        global $OUTPUT;
        
        $output = $OUTPUT->notification(get_string('noworkflows', 'local_status'), 'error');
        $output .= html_writer::div(
            html_writer::link(
                new moodle_url('/local/status/pages/forms/workflow_form.php'),
                get_string('addworkflow', 'local_status'),
                ['class' => 'btn btn-primary']
            ),
            'mt-3'
        );
        
        return $output;
    }
    
    /**
     * Render workflow selector dropdown
     * 
     * @param array $workflows Available workflows
     * @param int $workflow_id Currently selected workflow ID
     * @return string HTML output
     */
    private function render_workflow_selector(array $workflows, int $workflow_id): string {
        $output = html_writer::start_div('row mb-4');
        $output .= html_writer::start_div('col-md-6');
        
        $output .= html_writer::start_tag('form', [
            'method' => 'GET',
            'class' => 'd-flex align-items-center'
        ]);
        $output .= html_writer::tag('input', '', ['type' => 'hidden', 'name' => 'tab', 'value' => 'steps']);
        
        $output .= html_writer::tag('label', get_string('selectworkflow', 'local_status'), [
            'for' => 'workflow_selector',
            'class' => 'mr-2'
        ]);
        
        $select = html_writer::start_tag('select', [
            'id' => 'workflow_selector',
            'name' => 'workflow_id',
            'class' => 'form-control mr-2'
        ]);
        
        $select .= html_writer::tag('option', get_string('selectworkflow', 'local_status'), ['value' => '']);
        
        foreach ($workflows as $wf) {
            // Get localized display name
            $current_lang = current_language();
            $display_name = ($current_lang === 'ar' && !empty($wf->display_name_ar)) 
                ? format_string($wf->display_name_ar)
                : format_string($wf->display_name_en);
                
            $selected = ($wf->id == $workflow_id) ? ['selected' => 'selected'] : [];
            $select .= html_writer::tag('option', $display_name, 
                array_merge(['value' => $wf->id], $selected));
        }
        
        $select .= html_writer::end_tag('select');
        $output .= $select;
        
        $output .= html_writer::tag('button', get_string('go'), [
            'type' => 'submit',
            'class' => 'btn btn-secondary'
        ]);
        
        $output .= html_writer::end_tag('form');
        $output .= html_writer::end_div();
        
        return $output;
    }
    
    /**
     * Render the main steps section for a workflow
     * 
     * @param object $workflow Workflow object
     * @return string HTML output
     */
    private function render_steps_section(object $workflow): string {
        $output = '';
        
        // Header with add button
        $output .= $this->render_steps_header($workflow);
        
        // Get steps data
        $steps_data = $this->get_steps_data($workflow->id);
        
        if (empty($steps_data['all_steps'])) {
            return $output . $this->render_no_steps_message();
        }
        
        // Controls and summary
        $output .= $this->render_steps_controls($workflow->id, $steps_data);
        $output .= $this->render_workflow_summary($steps_data);
        
        // Steps table
        $output .= $this->render_steps_table($steps_data);
        
        // Help section
        $output .= $this->render_help_section();
        
        return $output;
    }
    
    /**
     * Get all steps data for a workflow
     * 
     * @param int $workflow_id Workflow ID
     * @return array Steps data arrays
     */
    private function get_steps_data(int $workflow_id): array {
        return [
            'all_steps' => workflow_dashboard_manager::get_workflow_steps($workflow_id, false, true),
            'active_steps' => workflow_dashboard_manager::get_workflow_steps($workflow_id, true, false),
            'modifiable_steps' => workflow_dashboard_manager::get_modifiable_steps($workflow_id),
            'actual_steps' => workflow_dashboard_manager::get_actual_workflow_steps($workflow_id, false),
            'actual_active_steps' => workflow_dashboard_manager::get_actual_workflow_steps($workflow_id, true)
        ];
    }
    
    /**
     * Render steps section header
     * 
     * @param object $workflow Workflow object
     * @return string HTML output
     */
    private function render_steps_header(object $workflow): string {
        // Get localized workflow name
        $current_lang = current_language();
        $workflow_name = ($current_lang === 'ar' && !empty($workflow->display_name_ar)) 
            ? format_string($workflow->display_name_ar)
            : format_string($workflow->display_name_en);
            
        $output = html_writer::start_div('mb-3');
        $output .= html_writer::tag('h3', get_string('stepsfor', 'local_status', $workflow_name));
        $output .= html_writer::end_div();
        
        return $output;
    }
    
    /**
     * Render no steps message
     * 
     * @return string HTML output
     */
    private function render_no_steps_message(): string {
        global $OUTPUT;
        
        $output = html_writer::start_div('steps-empty-state');
        $output .= html_writer::div(
            html_writer::tag('i', '', ['class' => 'fa fa-list-ol fa-3x text-muted mb-3']),
            'mb-3'
        );
        $output .= html_writer::tag('h4', get_string('nosteps', 'local_status'), ['class' => 'text-muted']);
        $output .= html_writer::tag('p', get_string('nostepshelp', 'local_status'), ['class' => 'text-muted mb-4']);
        $output .= html_writer::end_div();
        
        return $output;
    }
    
    /**
     * Render steps controls (show/hide inactive and add step button)
     * 
     * @param int $workflow_id Workflow ID
     * @param array $steps_data Steps data
     * @return string HTML output
     */
    private function render_steps_controls(int $workflow_id, array $steps_data): string {
        $show_hidden = optional_param('show_hidden', 0, PARAM_BOOL);
        $hidden_count = count($steps_data['actual_steps']) - count($steps_data['actual_active_steps']);
        
        $output = html_writer::start_div('d-flex justify-content-between align-items-center mb-3 p-3 bg-light border rounded');
        
        // Left side: Show/Hide controls
        $left_controls = html_writer::start_div();
        if ($hidden_count > 0) {
            if ($show_hidden) {
                $left_controls .= html_writer::link(
                    new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps', 'workflow_id' => $workflow_id]),
                    get_string('hideinactivesteps', 'local_status') . ' (' . $hidden_count . ')',
                    ['class' => 'btn btn-sm btn-outline-secondary']
                );
            } else {
                $left_controls .= html_writer::link(
                    new moodle_url('/local/status/pages/dashboard.php', ['tab' => 'steps', 'workflow_id' => $workflow_id, 'show_hidden' => 1]),
                    get_string('showinactivesteps', 'local_status') . ' (' . $hidden_count . ')',
                    ['class' => 'btn btn-sm btn-outline-info']
                );
            }
        } else {
            $left_controls .= html_writer::span(
                get_string('allstepsactive', 'local_status'),
                'text-muted small'
            );
        }
        $left_controls .= html_writer::end_div();
        
        // Right side: Add Step button
        $right_controls = html_writer::start_div();
        $right_controls .= html_writer::link(
            new moodle_url('/local/status/pages/forms/step_form.php', ['workflow_id' => $workflow_id]),
            html_writer::tag('i', '', ['class' => 'fa fa-plus mr-1']) . get_string('addstep', 'local_status'),
            ['class' => 'btn btn-primary btn-sm']
        );
        $right_controls .= html_writer::end_div();
        
        $output .= $left_controls;
        $output .= $right_controls;
        $output .= html_writer::end_div();
        
        return $output;
    }
    
    /**
     * Render workflow summary
     * 
     * @param array $steps_data Steps data
     * @return string HTML output
     */
    private function render_workflow_summary(array $steps_data): string {
        $output = html_writer::start_div('alert alert-info mb-3');
        $output .= html_writer::tag('strong', get_string('workflowstructure', 'local_status'));
        $output .= html_writer::tag('br', '');
        $output .= get_string('actualworkflowsteps', 'local_status') . ': ' . count($steps_data['actual_steps']) . ' | ';
        $output .= get_string('activeworkflowsteps', 'local_status') . ': ' . count($steps_data['actual_active_steps']) . ' | ';
        $output .= get_string('modifiablesteps', 'local_status') . ': ' . count($steps_data['modifiable_steps']);
        $output .= html_writer::tag('br', '');
        $output .= html_writer::tag('small', get_string('systemflags_not_counted', 'local_status'), ['class' => 'text-muted']);
        $output .= html_writer::end_div();
        
        return $output;
    }
    
    /**
     * Render the steps table
     * 
     * @param array $steps_data Steps data
     * @return string HTML output
     */
    private function render_steps_table(array $steps_data): string {
        $show_hidden = optional_param('show_hidden', 0, PARAM_BOOL);
        $steps_to_display = $show_hidden ? $steps_data['all_steps'] : $steps_data['active_steps'];
        
        $table_class = 'table table-striped table-hover';
        $output = html_writer::start_tag('table', ['class' => $table_class, 'id' => 'steps-table']);
        
        // Table header
        $output .= $this->render_table_header();
        
        // Table body
        $output .= html_writer::start_tag('tbody');
        
        if (!empty($steps_to_display)) {
            foreach ($steps_to_display as $step) {
                try {
                    $output .= $this->render_step_row($step);
                } catch (Exception $e) {
                    debugging('Error rendering step row for ID ' . $step->id . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
                    $output .= $this->render_step_error_row($step, $e->getMessage());
                }
            }
        } else {
            $output .= $this->render_empty_table_row();
        }
        
        $output .= html_writer::end_tag('tbody');
        $output .= html_writer::end_tag('table');
        
        return $output;
    }
    
    /**
     * Render table header
     * 
     * @return string HTML output
     */
    private function render_table_header(): string {
        $output = html_writer::start_tag('thead', ['class' => 'table-light']);
        $output .= html_writer::start_tag('tr');
        $output .= html_writer::tag('th', '#', ['scope' => 'col', 'style' => 'width: 50px;']);
        $output .= html_writer::tag('th', get_string('name', 'local_status'), ['scope' => 'col']);
        $output .= html_writer::tag('th', get_string('displayname', 'local_status'), ['scope' => 'col']);
        $output .= html_writer::tag('th', get_string('approvaltype', 'local_status'), ['scope' => 'col']);
        $output .= html_writer::tag('th', get_string('capability', 'local_status'), ['scope' => 'col']);
        $output .= html_writer::tag('th', get_string('flags', 'local_status'), ['scope' => 'col', 'style' => 'width: 120px;']);
        $output .= html_writer::tag('th', get_string('status', 'local_status'), ['scope' => 'col', 'style' => 'width: 100px;']);
        $output .= html_writer::tag('th', get_string('actions', 'local_status'), ['scope' => 'col', 'style' => 'width: 180px;']);
        $output .= html_writer::end_tag('tr');
        $output .= html_writer::end_tag('thead');
        
        return $output;
    }
    
    /**
     * Render a single step row
     * 
     * @param object $step Step object
     * @return string HTML output
     */
    private function render_step_row(object $step): string {
        // Determine row styling and modifiability
        $is_modifiable = !$step->is_initial && !$step->is_final;
        $row_class = $this->get_step_row_class($step);
        
        $output = html_writer::start_tag('tr', ['class' => $row_class]);
        
        // Render each column
        $output .= html_writer::tag('td', $this->render_step_sequence($step, $is_modifiable));
        $output .= html_writer::tag('td', $this->render_step_name($step));
        $output .= html_writer::tag('td', $this->render_step_display_name($step));
        $output .= html_writer::tag('td', $this->render_step_approval_type($step));
        $output .= html_writer::tag('td', $this->render_step_capability($step));
        $output .= html_writer::tag('td', $this->render_step_flags($step, $is_modifiable));
        $output .= html_writer::tag('td', $this->render_step_status($step));
        $output .= html_writer::tag('td', $this->render_step_actions($step, $is_modifiable));
        
        $output .= html_writer::end_tag('tr');
        
        return $output;
    }
    
    /**
     * Get CSS class for step row based on step properties
     * 
     * @param object $step Step object
     * @return string CSS class
     */
    private function get_step_row_class(object $step): string {
        if (!$step->is_active) {
            return 'table-secondary'; // Gray out inactive steps
        } else if ($step->is_initial) {
            return 'table-primary'; // Blue for initial
        } else if ($step->is_final) {
            return 'table-success'; // Green for final
        }
        return '';
    }
    
    /**
     * Render step sequence number
     * 
     * @param object $step Step object
     * @param bool $is_modifiable Whether step is modifiable
     * @return string HTML output
     */
    private function render_step_sequence(object $step, bool $is_modifiable): string {
        $seq_display = $step->seq;
        if (!$is_modifiable) {
            $seq_display .= ' ' . html_writer::tag('i', '', [
                'class' => 'fa fa-lock', 
                'title' => get_string('protected_step', 'local_status')
            ]);
        }
        return $seq_display;
    }
    
    /**
     * Render step name
     * 
     * @param object $step Step object
     * @return string HTML output
     */
    private function render_step_name(object $step): string {
        $name_display = format_string($step->name);
        if (!$step->is_active) {
            $name_display = html_writer::tag('s', $name_display) . ' ' . 
                           html_writer::span(get_string('hidden', 'local_status'), 'badge badge-secondary');
        }
        return $name_display;
    }
    
    /**
     * Render step display name
     * 
     * @param object $step Step object
     * @return string HTML output
     */
    private function render_step_display_name(object $step): string {
        // Get localized display name
        $current_lang = current_language();
        $display_name = ($current_lang === 'ar' && !empty($step->display_name_ar)) 
            ? format_string($step->display_name_ar)
            : format_string($step->display_name_en);
            
        if (!$step->is_active) {
            $display_name = html_writer::tag('s', $display_name);
        }
        return $display_name;
    }
    
    /**
     * Render step approval type
     * 
     * @param object $step Step object
     * @return string HTML output
     */
    private function render_step_approval_type(object $step): string {
        $approval_type_labels = [
            'capability' => get_string('capabilitybased', 'local_status'),
            'user' => get_string('userbased', 'local_status'),
            'any' => get_string('approval_any', 'local_status')
        ];
        
        $label = $approval_type_labels[$step->approval_type] ?? $step->approval_type;
        $badge_class = $step->approval_type === 'capability' ? 'badge bg-secondary' : 'badge bg-info';
        
        return html_writer::span($label, $badge_class);
    }
    
    /**
     * Render step capability
     * 
     * @param object $step Step object
     * @return string HTML output
     */
    private function render_step_capability(object $step): string {
        return $step->capability ? format_string($step->capability) : '-';
    }
    
    /**
     * Render step flags
     * 
     * @param object $step Step object
     * @param bool $is_modifiable Whether step is modifiable
     * @return string HTML output
     */
    private function render_step_flags(object $step, bool $is_modifiable): string {
        $flags = [];
        
        if ($step->is_initial) {
            $flags[] = html_writer::span(
                html_writer::tag('i', '', ['class' => 'fa fa-play']) . ' ' . get_string('initial', 'local_status'), 
                'badge bg-primary'
            );
        }
        
        if ($step->is_final) {
            $flags[] = html_writer::span(
                html_writer::tag('i', '', ['class' => 'fa fa-flag-checkered']) . ' ' . get_string('final', 'local_status'), 
                'badge bg-success'
            );
        }
        
        if ($is_modifiable) {
            $flags[] = html_writer::span(
                html_writer::tag('i', '', ['class' => 'fa fa-edit']) . ' ' . get_string('modifiable', 'local_status'), 
                'badge bg-warning'
            );
        }
        
        return implode('<br>', $flags);
    }
    
    /**
     * Render step status
     * 
     * @param object $step Step object
     * @return string HTML output
     */
    private function render_step_status(object $step): string {
        if ($step->is_active) {
            return html_writer::span(
                html_writer::tag('i', '', ['class' => 'fa fa-check']) . ' ' . get_string('active', 'local_status'), 
                'badge bg-success'
            );
        } else {
            return html_writer::span(
                html_writer::tag('i', '', ['class' => 'fa fa-eye-slash']) . ' ' . get_string('hidden', 'local_status'), 
                'badge bg-danger'
            );
        }
    }
    
    /**
     * Render step actions
     * 
     * @param object $step Step object
     * @param bool $is_modifiable Whether step is modifiable
     * @return string HTML output
     */
    private function render_step_actions(object $step, bool $is_modifiable): string {
        $actions = [];
        
        // Edit button
        $edit_disabled = !$is_modifiable ? 'disabled' : '';
        $edit_title = !$is_modifiable ? get_string('cannot_edit_protected', 'local_status') : get_string('edit');
        $actions[] = html_writer::link(
            new moodle_url('/local/status/pages/forms/step_form.php', ['id' => $step->id, 'workflow_id' => $step->type_id]),
            html_writer::tag('i', '', ['class' => 'fa fa-edit']),
            [
                'class' => 'btn btn-sm btn-outline-primary ' . $edit_disabled,
                'title' => $edit_title
            ]
        );
        
        // Manage Approvers button (only for user-based steps)
        if ($step->approval_type === 'user') {
            $actions[] = html_writer::link(
                new moodle_url('/local/status/pages/manage_approvers.php', ['step_id' => $step->id]),
                html_writer::tag('i', '', ['class' => 'fa fa-users']),
                [
                    'class' => 'btn btn-sm btn-outline-success',
                    'title' => get_string('manageapprovers', 'local_status')
                ]
            );
        }
        
        return implode(' ', $actions);
    }
    
    /**
     * Render error row for step that failed to render
     * 
     * @param object $step Step object
     * @param string $error_message Error message
     * @return string HTML output
     */
    private function render_step_error_row(object $step, string $error_message): string {
        $error_display = html_writer::span(
            get_string('errorloadingstep', 'local_status') . ': ' . $error_message,
            'text-danger'
        );
        
        $output = html_writer::start_tag('tr', ['class' => 'table-danger']);
        $output .= html_writer::tag('td', $step->seq ?? '-');
        $output .= html_writer::tag('td', format_string($step->name ?? 'Unknown'));
        $output .= html_writer::tag('td', $error_display);
        $output .= html_writer::tag('td', '-');
        $output .= html_writer::tag('td', '-');
        $output .= html_writer::tag('td', '-');
        $output .= html_writer::tag('td', html_writer::span(get_string('error'), 'badge badge-danger'));
        $output .= html_writer::tag('td', '-');
        $output .= html_writer::end_tag('tr');
        
        return $output;
    }
    
    /**
     * Render empty table row
     * 
     * @return string HTML output
     */
    private function render_empty_table_row(): string {
        $output = html_writer::start_tag('tr');
        $output .= html_writer::tag('td', 
            get_string('nosteps', 'local_status'), 
            ['colspan' => '8', 'class' => 'text-center text-muted p-4']
        );
        $output .= html_writer::end_tag('tr');
        
        return $output;
    }
    
    /**
     * Render help section
     * 
     * @return string HTML output
     */
    private function render_help_section(): string {
        $output = html_writer::start_div('mt-4');
        $output .= html_writer::tag('h5', get_string('stepmanagementhelp', 'local_status'));
        $output .= html_writer::start_tag('ul', ['class' => 'list-unstyled']);
        $output .= html_writer::tag('li', 
            html_writer::tag('i', '', ['class' => 'fa fa-lock text-primary']) . ' ' . 
            get_string('protectedstepshelp', 'local_status')
        );
        $output .= html_writer::tag('li', 
            html_writer::tag('i', '', ['class' => 'fa fa-edit text-warning']) . ' ' . 
            get_string('modifiablestepshelp', 'local_status')
        );
        $output .= html_writer::tag('li', 
            html_writer::tag('i', '', ['class' => 'fa fa-eye-slash text-danger']) . ' ' . 
            get_string('hiddenstepshelp', 'local_status')
        );
        $output .= html_writer::end_tag('ul');
        $output .= html_writer::end_div();
        
        return $output;
    }
} 
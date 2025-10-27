<?php
// local/roombooking/classes/output/renderer.php

namespace local_roombooking\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;
use moodle_url;

// Include the simple workflow manager
require_once(__DIR__ . '/../simple_workflow_manager.php');

/**
 * Renderer class for the roombooking plugin.
 */
class renderer extends plugin_renderer_base
{

    /**
     * Render the classroom bookings table.
     *
     * @param array $records The booking records.
     * @param string $sortby The column to sort by.
     * @param string $direction The sort direction.
     * @param array $filters The applied filters.
     * @return string The rendered table HTML.
     */
    public function render_classroom_table($records, $sortby, $direction, $filters)
    {
        // Use simple workflow manager for status information
        $approved_status_id = \local_roombooking\simple_workflow_manager::STATUS_APPROVED;
        $initial_status_id = \local_roombooking\simple_workflow_manager::STATUS_INITIAL;
        $rejected_status_id = \local_roombooking\simple_workflow_manager::STATUS_REJECTED;
        
        // Prepare data for Mustache template
        $data = [
            'records' => [],
            'actions_label' => get_string('actions_label', 'local_roombooking'),
            'can_manage' => has_capability('local/roombooking:managebookings', \context_system::instance()),
            // Column Headers
            'column_id' => get_string('column_id', 'local_roombooking'),
            'column_course' => get_string('column_course', 'local_roombooking'),
            'column_room' => get_string('column_room', 'local_roombooking'),
            'column_capacity' => get_string('column_capacity', 'local_roombooking'),
            'column_start_date' => get_string('column_start_date', 'local_roombooking'),
            'column_start_time' => get_string('column_start_time', 'local_roombooking'),
            'column_end_time' => get_string('column_end_time', 'local_roombooking'),
            'column_recurrence' => get_string('column_recurrence', 'local_roombooking'),
            'column_user' => get_string('column_user', 'local_roombooking'),
            'column_status' => get_string('column_status', 'local_roombooking'),
            // URL for handling approve and reject actions via AJAX
            'action_url_base' => (new moodle_url('/local/roombooking/actions/action_booking.php'))->out(false),
            'sesskey' => sesskey(),
        ];

        // Build filter block data (match residencebooking structure)
        $data['filter'] = $this->build_filter_block($filters);

        foreach ($records as $record) {
            // Only use status_id-based workflow, no legacy status support
            $status_name = \local_roombooking\simple_workflow_manager::get_status_name($record->status_id);
            $status_is_initial = ($record->status_id == $initial_status_id);
            $status_is_approved = \local_roombooking\simple_workflow_manager::is_approved_status($record->status_id);
            $status_is_rejected = \local_roombooking\simple_workflow_manager::is_rejected_status($record->status_id);
            $can_approve = $this->can_approve_booking($record);
            
            // Combine first name and last name to get the full name
            $fullname = fullname($record);

            // Format dates and times
            $start_date = userdate($record->starttime, get_string('strftimedate', 'langconfig'));
            $start_time = userdate($record->starttime, get_string('strftimetime', 'langconfig'));
            $end_time = userdate($record->endtime, get_string('strftimetime', 'langconfig'));

            // Get the recurrence label
            $recurrence_label = $this->get_recurrence_label($record->recurrence);

            $data['records'][] = [
                'id' => $record->id,
                'course_name' => $record->course_name,
                'room_name' => $record->room_name,
                'capacity' => $record->capacity,
                'roomcapacity' => $record->roomcapacity,
                'start_date' => $start_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'recurrence_label' => $recurrence_label,
                'fullname' => $fullname,
                'status_name' => $status_name,
                'status_is_initial' => $status_is_initial,
                'status_is_approved' => $status_is_approved,
                'status_is_rejected' => $status_is_rejected,
                'can_approve' => $can_approve,
                'rejection_note' => htmlspecialchars((string)$record->rejection_note, ENT_QUOTES, 'UTF-8'),
                'approval_note' => htmlspecialchars((string)$record->approval_note, ENT_QUOTES, 'UTF-8'),
                'can_manage' => $data['can_manage'],
            ];
        }

        // Generate sort links
        $sortablecolumns = [
            'id',
            'courseid',
            'roomid',
            'capacity',
            'starttime',
            'endtime',
            'recurrence',
            'userid'
        ];
        foreach ($sortablecolumns as $column) {
            $currenturl = clone $this->page->url;
            // Always use ascending order for manage bookings tab
            $currentdirection = 'asc';
            $currenturl->param('sortby', $column);
            $currenturl->param('direction', $currentdirection);
            $data['sortlink_' . $column] = $currenturl->out(false);
        }

        // Add filter data if necessary
        if (!empty($filters)) {
            foreach ($filters as $key => $value) {
                $data[$key] = $value;
            }
        }

        // Pass JS variables within a 'js' object
        $data['js'] = [
            'action_url_base' => $data['action_url_base'],
            'sesskey' => $data['sesskey'],
        ];

        return $this->render_from_template('local_roombooking/table_classroom_booking', $data);
    }

    /**
     * Build filter block context similar to residencebooking.
     *
     * @param array $filters Incoming filters (expects 'courseid' and 'roomid')
     * @return array
     */
    private function build_filter_block(array $filters): array {
        global $DB, $PAGE;

        // Accept both raw keys from GET (courseid/roomid) and the wrapper keys
        // passed from index.php (filter_courseid/filter_roomid) – to persist selection
        $selectedCourseId = isset($filters['courseid'])
            ? (int)$filters['courseid']
            : (isset($filters['filter_courseid']) ? (int)$filters['filter_courseid'] : 0);

        $selectedRoomId = isset($filters['roomid'])
            ? (int)$filters['roomid']
            : (isset($filters['filter_roomid']) ? (int)$filters['filter_roomid'] : 0);

        // Courses: only visible courses (exclude front page)
        $courses = $DB->get_records_sql("SELECT id, fullname FROM {course} WHERE visible = 1 AND id > 1 ORDER BY fullname");
        $courseItems = [];
        foreach ($courses as $c) {
            $courseItems[] = [
                'id' => $c->id,
                'fullname' => format_string($c->fullname),
                'selected' => ((int)$c->id === $selectedCourseId)
            ];
        }

        // Rooms: active rooms
        $rooms = $DB->get_records('local_roombooking_rooms', ['deleted' => 0], 'name', 'id, name, roomtype');
        $roomItems = [];
        foreach ($rooms as $r) {
            $labelType = (isset($r->roomtype) && in_array($r->roomtype, ['fixed','dynamic']))
                ? get_string($r->roomtype, 'local_roombooking')
                : get_string('invalidroomtype', 'local_roombooking');
            $roomItems[] = [
                'id' => $r->id,
                'name' => format_string($r->name) . ' (' . $labelType . ')',
                'selected' => ((int)$r->id === $selectedRoomId)
            ];
        }

        $formAction = new moodle_url('/local/roombooking/index.php');
        $cleanManageUrl = new moodle_url('/local/roombooking/index.php', ['tab' => 'managebookings']);

        return [
            'form_action' => $formAction->out(false),
            'filter_url'  => $cleanManageUrl->out(false),
            'courses' => $courseItems,
            'rooms'   => $roomItems,
            'selected_courseid' => $selectedCourseId,
            'selected_roomid'   => $selectedRoomId,
        ];
    }

    /**
     * Check if current user can approve this booking
     *
     * @param object $record The booking record
     * @return bool Can user approve
     */
    private function can_approve_booking($record) {
        if (empty($record->status_id)) {
            return false;
        }
        try {
            // Use the simple workflow manager for direct capability checking
            return \local_roombooking\simple_workflow_manager::can_user_approve($record->status_id);
        } catch (\Exception $e) {
            // If there's an error checking permissions, fail safely by denying approval
            return false;
        }
    }

    /**
     * Get the label for recurrence based on its value.
     *
     * @param int $recurrence The recurrence value.
     * @return string The human-readable recurrence label.
     */
    private function get_recurrence_label($recurrence)
    {
        switch ($recurrence) {
            case 0:
                return get_string('recurrence_none', 'local_roombooking');
            case 1:
                return get_string('recurrence_daily', 'local_roombooking');
            case 2:
                return get_string('recurrence_weekly', 'local_roombooking');
            case 3:
                return get_string('recurrence_monthly', 'local_roombooking');
            default:
                return get_string('recurrence_unknown', 'local_roombooking');
        }
    }
}

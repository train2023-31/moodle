<?php
namespace local_requestservices\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class residencebookingview implements renderable, templatable {
    protected $records;

    public function __construct($records) {
        $this->records = $records;
    }

    public function export_for_template(renderer_base $output) {
        $data = [
            'records' => [],
            // Column Headers
            'column_id' => get_string('id', 'local_residencebooking'),
            'column_course' => get_string('course_name', 'local_residencebooking'),
            'column_guestname' => get_string('guest_name', 'local_residencebooking'),
            'column_service_number' => get_string('service_number', 'local_residencebooking'),
            'column_residence_type' => get_string('residence_type', 'local_residencebooking'),
            'column_start_date' => get_string('start_date', 'local_residencebooking'),
            'column_end_date' => get_string('end_date', 'local_residencebooking'),
            'column_purpose' => get_string('purpose', 'local_residencebooking'),
            'column_status' => get_string('column_status', 'local_roombooking'),
            // Actions label
            'actions_label' => get_string('actions_label', 'local_roombooking'),
        ];

        foreach ($this->records as $record) {
            $fullname = fullname($record);
            $start_date = userdate($record->start_date, get_string('strftimedate', 'langconfig'));
            $end_date = userdate($record->end_date, get_string('strftimedate', 'langconfig'));
            $start_time = userdate($record->starttime, get_string('strftimetime', 'langconfig'));
            $end_time = userdate($record->endtime, get_string('strftimetime', 'langconfig'));
            $recurrence_label = $this->get_recurrence_label($record->recurrence);

            $data['records'][] = [
                'id' => $record->id,
                'course_name' => $record->course_name,
                'guest_name' => $record->guest_name,
                'service_number' => $record->service_number,
                'residence_type' => $record->residence_type,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'purpose_type' => $record->purpose_type,
                'readable_status' => $this->get_status_label($record->status_id),
                'status_pending' => ($record->status_id == 15),
                'status_approved' => ($record->status_id == 20),
                'status_rejected' => ($record->status_id == 21),

                // Set 'can_manage' to false to hide approve/reject buttons
                'can_manage' => false,
            ];
        }

        return $data;
    }

    private function get_recurrence_label($recurrence) {
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

private function get_status_label($statusid) {
    switch ((int)$statusid) {
        case 15:
            return get_string('status_pending', 'local_residencebooking');
        case 20:
            return get_string('status_approved', 'local_residencebooking');
        case 21:
            return get_string('status_rejected', 'local_residencebooking');
        default:
            return get_string('unknown_status', 'local_residencebooking');
    }
}


    public function template() {
        // Specify the template in your plugin
        return 'local_requestservices/residencebookingview';
    }
}

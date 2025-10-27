<?php
namespace local_requestservices\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class registeringroomview implements renderable, templatable {
    protected $records;

    public function __construct($records) {
        $this->records = $records;
    }

    public function export_for_template(renderer_base $output) {
         $data = [];

$data['column_course'] = get_string('column_course', 'local_roombooking');
$data['column_room'] = get_string('column_room', 'local_roombooking');
$data['column_capacity'] = get_string('column_capacity', 'local_roombooking');
$data['column_start_date'] = get_string('column_start_date', 'local_roombooking');
$data['column_start_time'] = get_string('column_start_time', 'local_roombooking');
$data['column_end_time'] = get_string('column_end_time', 'local_roombooking');
$data['column_recurrence'] = get_string('column_recurrence', 'local_roombooking');
$data['column_user'] = get_string('column_user', 'local_roombooking');
$data['column_status'] = get_string('column_status', 'local_roombooking');

        foreach ($this->records as $service) {
            $fullname = fullname($service);
            $start_date = userdate($service->starttime, get_string('strftimedate', 'langconfig'));
            $start_time = userdate($service->starttime, get_string('strftimetime', 'langconfig'));
            $end_time = userdate($service->endtime, get_string('strftimetime', 'langconfig'));
            $recurrence_label = $this->get_recurrence_label($service->recurrence);

            $data['records'][] = [
                'course_name' => $service->course_name,
                'room_name' => $service->room_name,
                'capacity' => $service->capacity,
                'start_date' => $start_date,
                'start_time' =>  $start_time,
                'end_time' => $end_time,
                'recurrence_label' => $recurrence_label,
                'fullname' => $fullname,
                'readable_status'=> $service->readable_status,
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

      public function template() {
        // Specify the template in your plugin
        return 'local_requestservices/registeringroomview';
    }
}

       /*  $data = [
            'records' => [],
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
            // Actions label
            'actions_label' => get_string('actions_label', 'local_roombooking'),
        ]; */

/*         foreach ($this->records as $record) {
            $fullname = fullname($record);
            $start_date = userdate($record->starttime, get_string('strftimedate', 'langconfig'));
            $start_time = userdate($record->starttime, get_string('strftimetime', 'langconfig'));
            $end_time = userdate($record->endtime, get_string('strftimetime', 'langconfig'));
            $recurrence_label = $this->get_recurrence_label($record->recurrence);

            $data['records'][] = [
                'id' => $record->id,
                'course_name' => $record->course_name,
                'room_name' => $record->room_name,
                'capacity' => $record->capacity,
                'start_date' => $start_date,
                'start_time' => $start_time,
                'end_time' => $end_time,
                'recurrence_label' => $recurrence_label,
                'fullname' => $fullname,

                /* 'status' => $record->status,
                'status_pending' => ($record->status === 'pending'),
                'status_approved' => ($record->status === 'approved'),
                'status_rejected' => ($record->status === 'rejected'), */

                /* 'status' => $this->get_status_label($record->status_id),
                'status_pending' => ($record->status_id == 49),
                'status_approved' => ($record->status_id == 54),
                'status_rejected' => ($record->status_id == 55),
 */
                // Set 'can_manage' to false to hide approve/reject buttons
     /*            'can_manage' => false,
            ];
        }

        return $data;
    } */

 

/*     private function get_status_label($statusid) {
    switch ((int)$statusid) {
        case 49:
            return get_string('status_pending', 'local_roombooking');
        case 54:
            return get_string('status_approved', 'local_roombooking');
        case 55:
            return get_string('status_rejected', 'local_roombooking');
        default:
            return get_string('unknown_status', 'local_roombooking');
    }
} */


  

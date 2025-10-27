<?php
namespace local_requestservices\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class computerservices_requests implements renderable, templatable {
    private $computerservices;
    
    public function __construct($computerservices) {
        $this->computerservices = $computerservices;
    }
    
    public function export_for_template(renderer_base $output) {
        $data = [];

        foreach ($this->computerservices as $service) {
            $data['computerservices'][] = [
                'course' => $service->course,
                'numdevices' => $service->numdevices,
                'devices' => $service->devices,
                'id' => $service->id,
                'readable_status'=> $service->readable_status,
                // Format the timestamp to d-m-Y format
                'timecreated' => userdate($service->timecreated, '%d-%m-%Y'),
            ];
        }
       
        return $data;
    }

    // Add this method to specify the template to use
    public function template() {
        // Specify the template from the local_computerservice plugin
        return 'local_requestservices/computerservices_requests';
    }
}

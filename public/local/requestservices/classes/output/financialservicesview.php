<?php
namespace local_requestservices\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class financialservicesview implements renderable, templatable {
    private $financeservices;

    public function __construct($financeservices) {
        $this->financeservices = $financeservices;
    }

    public function export_for_template(renderer_base $output) {
        $data = [];

        // Convert each finance service object to an array to pass to the template
        foreach ($this->financeservices as $service) {
            $data['financeservices'][] = [
                'course' => $service->course,
                'funding_type_ar' => $service->funding_type_ar,
                'price_requested' => $service->price_requested,
                'id' => $service->id,
                'readable_status'=> $service->readable_status,
                // Format the timestamp to d-m-Y format
                'date_time_requested' => userdate($service->date_time_requested, '%d-%m-%Y'),
            ];
        }

        return $data;
    }

    public function template() {
        return 'local_requestservices/financialservicesview';
    }
}

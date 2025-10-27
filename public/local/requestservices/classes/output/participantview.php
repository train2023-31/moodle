<?php
namespace local_requestservices\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class participantview implements renderable, templatable {
    protected $requests;
    protected $totalrequests;
    protected $perpage_options;
    protected $perpage;

    public function __construct($requests, $totalrequests, $perpage_options, $perpage) {
        $this->requests = $requests;
        $this->totalrequests = $totalrequests;
        $this->perpage_options = $perpage_options;
        $this->perpage = $perpage;
    }

    public function export_for_template(renderer_base $output) {
        return [
            'requests' => $this->requests,
            'totalrequests' => $this->totalrequests,
            'perpage_options' => $this->perpage_options,
            'perpage' => $this->perpage,
            // We can exclude 'requestspagination' here and render it directly in the page.
        ];
    }

    public function template() {
        return 'local_requestservices/participantview';
    }
}

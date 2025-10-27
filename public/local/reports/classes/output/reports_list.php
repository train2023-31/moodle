<?php
namespace local_reports\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

/**
 * Renderable list of reports for Mustache.
 */
class reports_list implements renderable, templatable {

    /** @var array[] */
    private $reports;

    /**
     * @param array[] $reports rows already formatted for output
     */
    public function __construct(array $reports) {
        $this->reports = $reports;
    }

    public function export_for_template(renderer_base $output): array {
        return ['reports' => $this->reports];
    }
}

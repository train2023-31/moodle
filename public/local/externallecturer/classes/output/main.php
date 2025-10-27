<?php
namespace local_externallecturer\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

class main implements renderable, templatable {
    protected $lecturers;
    protected $lecturer_courses;

    public function __construct($lecturers, $lecturer_courses) {
        $this->lecturers = $lecturers;
        $this->lecturer_courses = $lecturer_courses;
    }

    public function export_for_template(renderer_base $output) {
        return [
            'lecturers' => array_values($this->lecturers),
            'lecturer_courses' => $this->lecturer_courses
        ];
    }
}

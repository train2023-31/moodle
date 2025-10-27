<?php
defined('MOODLE_INTERNAL') || die();

function local_reports_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('local/reports:viewall', $context)) {
        $url = new moodle_url('/local/reports/index.php', ['id'=>$course->id]);
        $navigation->add(get_string('pluginname', 'local_reports'), $url, navigation_node::TYPE_CUSTOM, null, 'local_reports');
    }
}


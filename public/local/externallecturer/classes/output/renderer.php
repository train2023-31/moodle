<?php
namespace local_externallecturer\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

class renderer extends plugin_renderer_base {

    public function render_main($data) {
        // Prepare data for Mustache templates
    
        // Render the template
        return $this->render_from_template('local_externallecturer/externallecturer', $data);
    }

    /**
     * Helper function to check if the current perpage value is selected.
     *
     * @param int $perpage_value The perpage value to check.
     * @return bool Returns true if the perpage value matches the user's selected value.
     */
    public function is_selected_perpage($perpage_value) {
        return $this->perpage == $perpage_value;
    }
}

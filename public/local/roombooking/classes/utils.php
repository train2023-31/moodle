<?php
// File: local/roombooking/classes/utils.php

namespace local_roombooking;

defined('MOODLE_INTERNAL') || die();

/**
 * Utility functions for the roombooking plugin.
 */
class utils {

    /**
     * Get the name of a room by its ID.
     *
     * @param int $roomid The room ID.
     * @return string The name of the room.
     */
    public static function get_room_name($roomid) {
        global $DB;
        $room = $DB->get_record('local_roombooking_rooms', ['id' => $roomid], 'name', MUST_EXIST);
        return $room->name;
    }
}

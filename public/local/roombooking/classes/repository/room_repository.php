<?php
// ============================================================================
//  RoomRepository – data-access object for local_roombooking_rooms
// ============================================================================
namespace local_roombooking\repository;

defined('MOODLE_INTERNAL') || die();

use stdClass;

class room_repository {

    /** Fetch *one* room by id. */
    public static function get(int $id): ?stdClass {
        global $DB;
        return $DB->get_record('local_roombooking_rooms', ['id' => $id]) ?: null;
    }

    /** Fetch *all* non-deleted rooms, ordered by name. */
    public static function all(): array {
        global $DB;
        return $DB->get_records('local_roombooking_rooms', ['deleted' => 0], 'name');
    }

    /** Fetch *all* rooms including deleted ones, ordered by name. */
    public static function all_including_deleted(): array {
        global $DB;
        return $DB->get_records('local_roombooking_rooms', null, 'name');
    }

    /** Insert new room, return new id. */
    public static function create(stdClass $room): int {
        global $DB;
        $room->timecreated = $room->timemodified = time();
        return $DB->insert_record('local_roombooking_rooms', $room);
    }

    /** Update existing room. */
    public static function update(stdClass $room): void {
        global $DB;
        $room->timemodified = time();
        $DB->update_record('local_roombooking_rooms', $room);
    }

    /** Delete room (caller must check bookings first). */
    public static function delete(int $id): void {
        global $DB;
        $DB->delete_records('local_roombooking_rooms', ['id' => $id]);
    }
}

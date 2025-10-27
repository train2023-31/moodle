<?php
// ============================================================================
//  BookingRepository – raw DB helpers for course room bookings
// ============================================================================

namespace local_roombooking\repository;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use local_roombooking\simple_workflow_manager as swf;

class booking_repository {

    /*═══════════════════════════════════════════════════════════════════════*/
    /*  AVAILABILITY                                                         */
    /*═══════════════════════════════════════════════════════════════════════*/

    /** TRUE if <room> is free in [start, end) – ignores REJECTED bookings. */
    public static function is_room_available(int $roomid, int $start, int $end,
                                             int $excludeid = null): bool {
        global $DB;

        $sql = "SELECT 1
                  FROM {local_roombooking_course_bookings}
                 WHERE roomid = :room
                   AND (starttime < :end AND endtime > :start)
                   AND status_id <> :rejected";
        $p = ['room'=>$roomid,'start'=>$start,'end'=>$end,'rejected'=>swf::STATUS_REJECTED];
        if ($excludeid) { $sql .= " AND id <> :ex"; $p['ex'] = $excludeid; }

        return !$DB->record_exists_sql($sql, $p);
    }

    /** Room-ids that are APPROVED & overlap the window. */
    public static function conflicting_room_ids(int $start, int $end): array {
        global $DB;

        $sql = "SELECT DISTINCT roomid
                  FROM {local_roombooking_course_bookings}
                 WHERE (starttime < :end AND endtime > :start)
                   AND status_id = :approved";
        return $DB->get_fieldset_sql($sql, [
            'start'=>$start,'end'=>$end,'approved'=>swf::STATUS_APPROVED
        ]) ?: [];
    }

    /** ►CHANGE – return **rooms that ARE free** for capacity filter */
    public static function available_room_ids(int $start, int $end, int $mincap): array {
        global $DB;

        $busy = self::conflicting_room_ids($start, $end);
        if ($busy) {
            list($in, $params) = $DB->get_in_or_equal($busy, SQL_PARAMS_NAMED, 'b');
            $wherebusy = "AND id NOT $in";
        } else {
            $wherebusy = '';
            $params = [];
        }
        $params += ['cap'=>$mincap];

        $sql = "SELECT id
                  FROM {local_roombooking_rooms}
                 WHERE capacity >= :cap $wherebusy
              ORDER BY capacity ASC";
        return $DB->get_fieldset_sql($sql, $params) ?: [];
    }

    /*═══════════════════════════════════════════════════════════════════════*/
    /*  CRUD                                                                 */
    /*═══════════════════════════════════════════════════════════════════════*/

    public static function create(stdClass $b): int {
        global $DB;
        $b->timecreated = $b->timemodified = time();
        return $DB->insert_record('local_roombooking_course_bookings', $b);
    }

    public static function update(int $id, array $f): void {
        global $DB;
        $f['id']           = $id;
        $f['timemodified'] = time();
        $DB->update_record('local_roombooking_course_bookings', (object)$f);
    }

    public static function get(int $id): ?stdClass {
        global $DB;
        return $DB->get_record('local_roombooking_course_bookings',['id'=>$id]) ?: null;
    }

    /*═══════════════════════════════════════════════════════════════════════*/
    /*  GROUP helpers                                                        */
    /*═══════════════════════════════════════════════════════════════════════*/

    /** ►CHANGE – fetch all booking IDs that share the same groupid. */
    public static function ids_by_group(string $groupid): array {
        global $DB;
        return $DB->get_fieldset_select('local_roombooking_course_bookings', 'id',
                                        'groupid = :g', ['g'=>$groupid]) ?: [];
    }
}

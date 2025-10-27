<?php
// ============================================================================
//  BookingService – business rules for Classroom Booking
//  --------------------------------------------------------------------------
//  • create_booking()  – validates, expands recurrence, inserts rows  
//  • approve()         – advance clicked row via workflow, then sync siblings  
//  • reject()          – apply workflow rejection to clicked row, then sync siblings
// ============================================================================

namespace local_roombooking\service;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use core\notification;
use local_roombooking\repository\room_repository;
use local_roombooking\repository\booking_repository as repo;
use local_roombooking\simple_workflow_manager as swf;

class booking_service {

/* ────────────────────────────────────────────────────────────────────────── */
/*  BOOKING CREATION                                                         */
/* ────────────────────────────────────────────────────────────────────────── */

    /**
     * Create one-off or recurring booking(s).
     *
     * @param array $data Raw form payload (see index.php)
     * @return bool  TRUE if at least one instance was successfully inserted
     */
    public static function create_booking(array $data): bool {
        global $USER;

        /* -------------------------------------------------------------- */
        /* 1) DATE-TIME VALIDATION  (►FIX)                                */
        /* -------------------------------------------------------------- */
        // The Moodle form element `date_time_selector` posts an **int** Unix
        // timestamp, but older front-end code once sent strings.  We now
        // accept *either*:
        //   – integer / numeric-string → use as-is
        //   – other string            → parse via strtotime()
        //   – anything else           → invalid
        //
        // ►FIX: normalise to int without breaking legacy callers.
        $start = self::normalise_datetime($data['start_date_time'] ?? null);
        $end   = self::normalise_datetime($data['end_date_time']   ?? null);

        if ($start === null || $end === null) {
            notification::error(get_string('invaliddatetime','local_roombooking'));
            return false;
        }
        if ($start >= $end) {
            notification::error(get_string('invalidtimetobook','local_roombooking'));
            return false;
        }

        /* -------------------------------------------------------------- */
        /* 2) ROOM VALIDATION                                             */
        /* -------------------------------------------------------------- */
        $room = room_repository::get((int)$data['roomid']);
        if (!$room) {
            notification::error(get_string('invalidroomid','error'));
            return false;
        }

        $capacity = (int)($data['capacity'] ?? 0);
        if ($capacity <= 0 || $capacity > $room->capacity) {
            $a = (object)['roomcapacity'=>$room->capacity];
            notification::error(get_string('roomexceedsmaxcapacity','local_roombooking',$a));
            return false;
        }

        /* -------------------------------------------------------------- */
        /* 3) PROTOTYPE OBJECT                                            */
        /* -------------------------------------------------------------- */
        $proto                 = new stdClass();
        $proto->courseid       = (int)$data['courseid'];
        $proto->roomid         = $room->id;
        $proto->starttime      = $start;
        $proto->endtime        = $end;
        $proto->capacity       = $capacity;
        $proto->userid         = $data['userid'] ?? $USER->id;
        $proto->groupid        = uniqid('', true);
        $proto->status_id      = swf::get_initial_status_id();
        $proto->recurrence     = $data['recurrence'] ?? 0;
        $proto->approval_note  = '';
        $proto->rejection_note = '';

        /* -------------------------------------------------------------- */
        /* 4) RECURRENCE EXPANSION + INSERT                               */
        /* -------------------------------------------------------------- */
        $ok        = false;
        $instances = self::expand_recurrence($proto,$data['recurrence_end_date'] ?? '');

        foreach ($instances as $inst) {
            if (repo::is_room_available($inst->roomid,$inst->starttime,$inst->endtime)) {
                repo::create($inst);
                $ok = true;
            } else {
                self::suggest_rooms($inst);
            }
        }
        if ($ok) {
            notification::success(get_string('requestsubmitted','local_roombooking'));
        }
        return $ok;
    }

/* ════════════════════════════════════════════════════════════════════════ */
/*  GROUP-AWARE APPROVE                                                     */
/* ════════════════════════════════════════════════════════════════════════ */

    public static function approve(int $bookingid, string $note = ''): bool {
        $clicked = repo::get($bookingid);
        if (!$clicked) {
            return false;
        }

        /* 1️⃣  Advance clicked row via workflow */
        swf::approve_booking($bookingid, $note);

        /* 2️⃣  Sync siblings to new status */
        $targetStatus = repo::get($bookingid)->status_id;
        foreach (repo::ids_by_group($clicked->groupid) as $sid) {
            if ($sid == $bookingid) { continue; }
            repo::update($sid, [
                'status_id'     => $targetStatus,
                'approval_note' => $note
            ]);
            debugging("Approve-sync → booking {$sid} now status {$targetStatus}",DEBUG_DEVELOPER);
        }
        return true;
    }

/* ════════════════════════════════════════════════════════════════════════ */
/*  GROUP-AWARE REJECT                                                      */
/* ════════════════════════════════════════════════════════════════════════ */

    public static function reject(int $bookingid, string $note): bool {
        $clicked = repo::get($bookingid);
        if (!$clicked) {
            return false;
        }

        /* 1️⃣  Workflow rejection on clicked row */
        swf::reject_booking($bookingid, $note);

        /* 2️⃣  Sync siblings */
        $targetStatus = repo::get($bookingid)->status_id;
        foreach (repo::ids_by_group($clicked->groupid) as $sid) {
            if ($sid == $bookingid) { continue; }
            repo::update($sid, [
                'status_id'      => $targetStatus,
                'rejection_note' => $note
            ]);
            debugging("Reject-sync → booking {$sid} now status {$targetStatus}",DEBUG_DEVELOPER);
        }
        return true;
    }

/* ───────────────────────────────────────────────────────────────────────── */
/*  Helper: expand recurrence                                               */
/* ───────────────────────────────────────────────────────────────────────── */

    private static function expand_recurrence(stdClass $p,string $endStr): array {
        $out      = [];
        $interval = 0;

        switch ($p->recurrence) {
            case 0:  return [clone $p];
            case 1:  $interval = DAYSECS;  break;
            case 2:  $interval = WEEKSECS; break;
            default:
                notification::error(get_string('invalidrecurrencetype','local_roombooking'));
                return [];
        }

        $end = strtotime($endStr);
        if ($end === false) {
            notification::error(get_string('invalidrecurrenceenddate','local_roombooking'));
            return [];
        }

        while ($p->starttime <= $end) {
            $out[] = clone $p;
            $p->starttime += $interval;
            $p->endtime   += $interval;
        }
        return $out;
    }

/* ───────────────────────────────────────────────────────────────────────── */
/*  Helper: suggest alternative rooms                                       */
/* ───────────────────────────────────────────────────────────────────────── */

    private static function suggest_rooms(stdClass $inst): void {
        $ids = repo::available_room_ids($inst->starttime,$inst->endtime,$inst->capacity);
        if (!$ids) {
            notification::error(get_string('roomnotavailable','local_roombooking'));
            return;
        }
        $names = array_map(fn($id)=>room_repository::get($id)->name,$ids);
        $a     = (object)['available_rooms'=>implode(', ',$names)];
        notification::error(
            get_string('roomnotavailableWithlistofrooms','local_roombooking',$a)
        );
    }

/* ───────────────────────────────────────────────────────────────────────── */
/*  Helper: normalise input datetime (►FIX)                                 */
/* ───────────────────────────────────────────────────────────────────────── */

    /**
     * Accepts mixed input (int timestamp | numeric string | date string)
     * and returns a Unix timestamp or NULL on failure.
     *
     * @param mixed $raw
     * @return int|null
     */
    private static function normalise_datetime($raw): ?int {
        if ($raw === null || $raw === '') {
            return null;
        }

        // 1) Native int (date_time_selector)
        if (is_int($raw)) {
            return $raw;
        }

        // 2) Numeric string e.g. "1728912000"
        if (is_string($raw) && ctype_digit($raw)) {
            return (int)$raw;
        }

        // 3) Anything else – let strtotime try
        $ts = strtotime($raw);
        return ($ts === false) ? null : $ts;
    }
}

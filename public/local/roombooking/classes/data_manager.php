<?php
// ============================================================================
//  data_manager – legacy façade kept for backward-compatibility
//  --------------------------------------------------------------------------
//  • Delegates everything to repository\* and service\booking_service.
//  • Emits a deprecation notice (DEBUG_DEVELOPER) on every call.
// ============================================================================

namespace local_roombooking;

defined('MOODLE_INTERNAL') || die();

use local_roombooking\service\booking_service      as booking_service;
use local_roombooking\repository\booking_repository as booking_repo;
use local_roombooking\repository\room_repository;

/**
 * @deprecated  Use repository\* and service\booking_service directly.
 */
class data_manager {

    /** Emit one deprecation warning per request. */
    private static function deprecated(): void {
        debugging(
            'local_roombooking\\data_manager is deprecated – '
          . 'switch callers to repository\\* or service\\booking_service.',
            DEBUG_DEVELOPER
        );
    }

    /*╔══════════════════════════════════════════════════════════════════════╗
      ║  WORKFLOW HELPERS (approve / reject)                                ║
      ╚══════════════════════════════════════════════════════════════════════╝*/

    /**
     * Approve a booking (and its sibling recurrences).
     *
     * @param int         $bookingId
     * @param string|null $approvalNote
     */
    public static function approve_booking($bookingId, $approvalNote = null) {
        self::deprecated();

        return booking_service::approve(
            (int) $bookingId,
            (string) ($approvalNote ?? '')
        );
    }

    /**
     * Reject a booking (and its sibling recurrences).
     *
     * @param int    $bookingId
     * @param string $rejectionNote
     */
    public static function reject_booking($bookingId, $rejectionNote) {
        self::deprecated();

        return booking_service::reject(
            (int) $bookingId,
            (string) $rejectionNote
        );
    }

    /*╔══════════════════════════════════════════════════════════════════════╗
      ║  AVAILABILITY QUERIES                                               ║
      ╚══════════════════════════════════════════════════════════════════════╝*/

    /**
     * TRUE if the room is free in the given window.
     *
     * @param int      $roomId
     * @param int      $windowStart  Unix-ts
     * @param int      $windowEnd    Unix-ts
     * @param int|null $excludeBookingId  Ignore this booking in the check
     */
    public static function is_room_available(
        $roomId,
        $windowStart,
        $windowEnd,
        $excludeBookingId = null
    ) {
        self::deprecated();

        return booking_repo::is_room_available(
            (int) $roomId,
            (int) $windowStart,
            (int) $windowEnd,
            $excludeBookingId
        );
    }

    /**
     * Return [roomId => roomName] for rooms that are free in the window.
     */
    public static function get_available_rooms($windowStart, $windowEnd) {
        self::deprecated();

        $busyRoomIds = booking_repo::conflicting_room_ids($windowStart, $windowEnd);

        // Build a full [id => name] map, then strip the busy ones.
        $allRooms     = array_column(room_repository::all(), 'name', 'id');
        $availableMap = array_diff_key($allRooms, array_flip($busyRoomIds));

        return $availableMap;
    }

    /*╔══════════════════════════════════════════════════════════════════════╗
      ║  BOOKING CREATION                                                   ║
      ╚══════════════════════════════════════════════════════════════════════╝*/

    /**
     * Create one-off or recurring booking(s).
     *
     * @param array $data  Same structure expected by booking_service
     */
    public static function add_classroom_booking(array $data) {
        self::deprecated();

        return booking_service::create_booking($data);
    }
}

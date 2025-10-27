# Bug Tracking - External Lecturer Module

## Bug #1: Conflicting Course Assignment Systems ✅ RESOLVED

**Issue:** Two systems could assign courses to lecturers (External Lecturer Plugin vs Participant Plugin), causing data conflicts and user confusion.

**Solution:** Removed course enrollment functionality from External Lecturer Plugin, keeping only lecturer profile management.

**Changes Made:**
- Removed "Registered Courses" tab
- Eliminated course enrollment features
- Cleaned up database queries and JavaScript
- Added development comments for future tabs

**Status:** ✅ **RESOLVED** (20/08/2025)

---

## Bug #2: Unused Database Table ✅ RESOLVED

**Issue:** The `externallecturer_courses` table was no longer being used after removing course enrollment functionality.

**Solution:** Completely removed the `externallecturer_courses` table and all related code.

**Changes Made:**
- Removed `externallecturer_courses` table from `db/install.xml`
- Added upgrade step to drop the table for existing installations
- Deleted `actions/addenrollment.php` and `actions/deletecourse.php`
- Removed `templates/form_enrolllecturer.mustache`
- Cleaned up JavaScript references to course enrollment
- Updated documentation to reflect changes
- Updated version to 1.6.2 (2025081500)

**Status:** ✅ **RESOLVED** (15/08/2025)

---

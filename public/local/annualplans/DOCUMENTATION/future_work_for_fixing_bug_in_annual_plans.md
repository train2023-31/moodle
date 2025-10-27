# Bug Tracking - Annual Plans

## Bug #1: Change the color of the Yes button ✅ DONE

**Issue Description:**
It is congused that the YES button color is RED!!

**Status:** COMPLETED ✅
**Fixed date:** 2025-08-24
**Solution:** Changed the Yes button color from red to a more appropriate color (green)

---

## Bug #2: Course Auto-Approval and Duplicate Shortname Issue ⚠️ PENDING

**Issue Description:**
When adding a new course inside the annual plan, the course gets approved automatically without any user approval action. Additionally, there's a problem with the course core table where a course exists with the same shortname, indicating a lack of proper uniqueness validation.

**Root Cause Analysis:**
1. **Auto-Approval Issue**: In `CourseManager.php` line 105, the course is automatically set to approved (`approve = 1`) immediately after creation without user intervention
2. **Duplicate Shortname Issue**: The shortname generation logic in `add_course.php` doesn't properly validate uniqueness against existing Moodle courses before creation

**Technical Details:**
- **File**: `local/annualplans/classes/CourseManager.php` (line 105)
- **Code**: `$DB->set_field('local_annual_plan_course', 'approve', 1, ['courseid' => $courseidnumber, 'coursedate' => $durationdays]);`
- **Shortname Generation**: Client-side JavaScript in `add_course.php` generates shortname but server-side validation is insufficient

**Impact:**
- Courses are approved without proper workflow
- Potential duplicate shortnames in Moodle core course table
- Data integrity issues

**Status:** PENDING ⚠️
**Priority:** HIGH
**Reported date:** 2025-09-11

**Proposed Solution:**
1. Remove automatic approval and implement proper approval workflow
2. Add server-side shortname uniqueness validation before course creation
3. Implement proper error handling for duplicate shortnames

---
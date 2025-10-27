# Bug Tracking - Participant Module

## Bug #1: Misleading Arabic Naming - "مستخدم" Should Be Changed

**Issue Description:**
The current Arabic naming uses "مستخدم" (user) which is too generic and doesn't accurately describe the training assignment purpose of the system.

**Expected Behavior:**
- Use more specific Arabic terms like "طلب إسناد تدريبي" (Training Assignment Request)
- Consistent terminology throughout the interface

**Current Behavior:**
- Generic "مستخدم" (user) term used throughout
- Confusing for users trying to understand the system's purpose

**Recommended Solution:**
- Change "مستخدم" to "المنتسب"
- Update language strings in `/lang/ar/local_participant.php`
- Ensure consistent terminology across interface

**Priority:** Medium
**Status:** ✅ Fixed
**Date Reported:** 28/8/2025

---
## Bug #2: The cost should be displayed in the field even it is disabled. Can't keep empty!

**Issue Description:**
Once you choose the role player type "any type of Internal" and the days, should automaticaly calculate the cost and fill in the Cost field.
But in the current behaivor, the cost field is empty!

**Expected Behavior:**
The cost field should be auto-filled by the calculated cost after the user choose the role player type "One of the Internal role player" and the number of days.

**Recommended Solution:**
- Auto-fill the Cost field after the user choose one of the internal role player type and the number of days.
- keep the field disabled but filled with the cost.
- This solution is not aplicable if the user choose external player.


**Priority:** Medium
**Status:** Open
**Date Reported:**
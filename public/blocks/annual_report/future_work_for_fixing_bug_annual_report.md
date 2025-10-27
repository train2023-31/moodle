# Bug Tracking - Annual Report Block

## Bug #1: Displaying All Requests Including Rejected and Pending from the local finance plugin

**Issue Description:**
The annual report block is currently showing all requests, including:
- Rejected requests that should not be displayed
- Pending requests that have not been accepted yet (اللي بعدهن في مراحل القبول)

**Expected Behavior:**
- Only approved/accepted requests should be displayed
- Rejected requests should be filtered out
- Pending requests should not appear until they are approved

**Current Behavior:**
- All requests are being shown regardless of their status
- This creates confusion and shows inappropriate data to users

**Priority:** High
**Status:** Fixed
**Date Reported:** 18.8.2025
**Date Fixed:** 18.8.2025

**Solution Applied:**
- Added `AND status_id = 13` filter to the finance services query in `block_annual_report.php`
- This ensures only approved finance requests (status_id = 13) are included in the spent amount calculations
- Rejected and pending requests are now properly filtered out from the annual report display

---

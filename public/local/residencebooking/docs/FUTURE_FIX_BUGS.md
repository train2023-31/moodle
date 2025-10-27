# Bug Tracking - Recidence Booking Module

## Bug #1: Request approval flow behavior (NOT A BUG - Stakeholder Requirement)
**Issue:** If the user approve the request (إعتماد ض.د), then (ض.ق) reject the request and he write rejection note. it stored in DB and displayed in UI.
        But the button Reject and Approve still enabled not disabled.
        If one of them clicked again the status changed and the rejection not removed even from DB.
**Stakeholder Decision:** This is the intended behavior according to stakeholder requirements. The buttons should remain enabled to allow for status changes even after rejection.
**Status:** CLOSED ✅ (Confirmed as intended behavior by stakeholder)
**Fixed date:** N/A - Not a bug

## Bug #2: Approval_Note column in local_residencebooking_request table (NOT A BUG - Required for Audit Trail)
**Issue:** This column always has NULL value because we don't have any approval note functionality in the residence booking page.
**Analysis:** This is not actually a bug. The column is:
- Required for compliance/audit trail requirements, cause we are using it to clean the note at the UI,,, and keeping the previuos rejection note just incase.
- Future-proofing for planned approval workflow features if they asks for it.
**Decision:** Keep the column as it serves important purposes:
- Maintains audit trail integrity
- Enables UI management without losing rejection notes
- Ensures consistency with other plugins
- Provides future-proofing for approval workflow features
**Status:** CLOSED ✅ (Column will be retained)
**Fixed date:** N/A - Not a bug
**Developer Note:** Used across other plugins for UI management without losing rejection notes.

## Bug #3: Autofill the service number field
**Issue:** The service_number field should be autofilled with PF_number once the guest_name is selected from the autocomplete dropdown. Currently, when a user selects a guest from the autocomplete, only the guest name is populated, but the service number field remains empty, requiring manual entry.
**Solution:**
- Modified `guest_search.php` to include PF number in AJAX response
- Created new `get_pf_number.php` endpoint to retrieve PF number for selected guest
- Updated `guest_autocomplete.js` to handle selection and populate service number field
- Added JavaScript initialization in `index.php` to wire up the autocomplete functionality
- Updated minified JavaScript build to include new functionality
- Implemented intelligent name processing with multiple matching strategies
- Added comprehensive testing with `test_ajax.php`
**Status:** Fixed ✅
**Fixed date:** 2025-08-25

## Bug #4: Change the color of Yes button when submitting a form
**Issue:** YES button color is RED which confused the user.
**Solution:** Implemented context-aware button colors - green for positive confirmations, red for destructive actions (rejections). Updated theme/stream/scss/custom.scss and theme/stream/js/custom_dialog.js
**Status:** Fixed ✅
**Fixed date:** 2025-08-24
**Developer Note:** The solution now uses context-aware colors:
- **Green** for positive confirmations (approve, submit, etc.)
- **Red** for destructive actions (reject, delete, etc.)
This maintains the visual indication of consequences while being more intuitive for users.

## Bug #5: Filter in the Manage Accomodition Requests Tab is not working
**Issue:** The filter in this tab is not working for all filter options!
**Solution:** Fixed pagination to preserve filter parameters, improved form action handling, and enhanced clear filters functionality with proper JavaScript reset.
**Status:** Fixed ✅
**Fixed date:** 2025-08-24

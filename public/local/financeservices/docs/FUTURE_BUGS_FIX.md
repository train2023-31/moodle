# Bug Tracking - Finance Services Module

## Bug #1: The clause can be added many times for the same year

**Issue:** The clause name can be added many times for the same year, even if the change only on extra space
**Status:** Fixed ✅
**Fixed Date:** 2025-08-24
**Solution:** 
- Added trimming of whitespace before saving clause names to database
- Updated form validation to use trimmed values for uniqueness checks
- Added database upgrade to clean up existing duplicate entries
- Enhanced SQL queries to use TRIM() function for case-insensitive comparison
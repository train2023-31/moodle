
---

## Core Code Change Documentation

**Change Title:**
Rename hook overview page to `openlms_hooksoverview` for consistency in admin settings

**Commit:**
`de850b0`

**Date:**
July 13, 2025, 8:17 AM (by AhAlsiyabi)

**Files Changed:**

* 2 files
* 2 insertions
* 2 deletions

### Summary

The **hook overview page identifier** in the admin settings was renamed from its previous value to `openlms_hooksoverview` for improved consistency in naming conventions within the admin settings.

### Description of Changes

* The identifier for the hook overview admin settings page was **renamed** to `openlms_hooksoverview`.
* Affected 2 files with 2 lines inserted and 2 lines removed to reflect this change.

### Reason for Change

* To maintain consistent naming conventions for admin settings pages related to Open LMS hooks.
* Helps with clarity and maintainability of the codebase.

### Affected Files

*(List the actual files if possible; otherwise, mention where this is likely to appear, for example:)*

* `admin/settings.php` (or a custom settings file for Open LMS integration)
* Related language or navigation files, if applicable

### Upgrade/Deployment Notes

* Ensure any custom plugins or documentation referencing the previous hook overview page identifier are **updated** to use `openlms_hooksoverview`.
* If you use configuration tools or scripts to automate admin settings, update those as well.

### References

* For more on Moodle admin settings pages:
  [https://docs.moodle.org/dev/Admin\_settings](https://docs.moodle.org/dev/Admin_settings)

* Naming conventions for admin settings:
  [https://docs.moodle.org/dev/Plugin\_contribution\_checklist#Admin\_settings](https://docs.moodle.org/dev/Plugin_contribution_checklist#Admin_settings)

---

#### Example Before/After

**Before:**

```php
$settings->add(new admin_externalpage('old_hooksoverview', ...));
```

**After:**

```php
$settings->add(new admin_externalpage('openlms_hooksoverview', ...));
```

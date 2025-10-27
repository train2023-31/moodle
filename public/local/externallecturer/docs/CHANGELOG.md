# Changelog

All notable changes to the External Lecturer Management plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.6.2] - 2025-08-15

### Removed
- **Course Enrollment System**: Removed `externallecturer_courses` table and all related functionality
- **Course Management Actions**: Deleted `addenrollment.php` and `deletecourse.php` action files
- **Course Enrollment Form**: Removed `form_enrolllecturer.mustache` template
- **Course Enrollment UI**: Removed course enrollment modal and related JavaScript functionality

### Changed
- **Database Schema**: Simplified to only include `externallecturer` table
- **Lecturer Deletion**: Removed course enrollment restrictions - lecturers can now be deleted without checking for enrolled courses
- **Version Number**: Updated to 2025081500
- **Release Notes**: Updated to reflect removal of course enrollment functionality

### Kept
- **Course Count Field**: Maintained `courses_count` field in `externallecturer` table for potential future use
- **Lecturer Management**: All core lecturer management functionality remains intact

---

## [1.6.1] - 2025-08-14

### Added
- **Dual Lecturer Types**: Separate creation forms for "external_visitor" and "resident" lecturers
- **Resident Lecturer Form**: New dedicated form (`form_residentlecturer.mustache`) for creating resident lecturers with Oracle integration for civil number search
- **Lecturer Type Classification**: `lecturer_type` field to distinguish between external visitors and residents
- **Enhanced UI**: Two distinct buttons in main interface ("Add External Lecturer" and "Add Resident Lecturer")
- **Oracle Integration for Residents**: Civil number search functionality that auto-populates name, nationality from Oracle DUNIA database
- **Nationality Field Support**: Enhanced nationality tracking for both lecturer types

### Changed
- External visitor form (`form_externallecturer.mustache`) maintains name-based Oracle autocomplete functionality
- Resident lecturer form uses civil number search with Oracle integration for data validation
- Both forms submit to same backend endpoint (`addlecturer.php`) with appropriate `lecturer_type` value
- Database schema supports `lecturer_type` field with default value "external_visitor"
- Template structure now includes dual form rendering at end of main template

---

## [1.6] - 2025-08-12

### Added
- Audit fields standardized: `timecreated`, `timemodified`, `created_by`, `modified_by` in `externallecturer`.
- Upgrade step 2025081200 to add new fields, drop legacy `created_datetime`/`modified_datetime`, and backfill values.
- Schema comments noting Oracle (DUNIA) provenance for `civil_number`/`passport` when sourced externally.

### Changed
- Insert/update flows now populate `timemodified`/`modified_by` and `timecreated`/`created_by` accordingly.
- UI now reads last modified from `timemodified`.

---

## [1.5] - 2025-08-04

### Added
- **Enhanced Bilingual Support**: Complete Arabic translation with RTL layout support
- **Improved Data Validation**: Enhanced input validation for all form fields
- **Better Error Handling**: More descriptive error messages in both languages
- **Civil Number Field**: Optional civil identification number tracking
- **Enhanced CSV Export**: Improved export functionality with better formatting
- **Audit Trail Fields**: Added created_by, created_datetime, and modified_datetime fields for tracking record changes
- **Audit Trail Display**: Created By and Last Modified columns now visible in the lecturers table interface

### Changed
- **Updated Version Number**: Bumped to version 1.5 (2025080500)
- **Improved Database Schema**: Added civil_number field and audit trail fields to externallecturer table
- **Enhanced User Interface**: Better responsive design and accessibility with audit trail columns
- **Updated Language Strings**: Comprehensive translation coverage for all features including audit fields
- **Enhanced Data Tracking**: Automatic population of audit fields when creating/editing records
- **Interface Updates**: Added Created By and Last Modified columns to lecturers table view

### Fixed
- **AJAX Response Handling**: Improved error handling for AJAX operations
- **Form Validation**: Fixed validation issues with special characters
- **Database Constraints**: Enhanced foreign key relationships
- **Permission Handling**: Improved capability checking

### Security
- **Input Sanitization**: Enhanced parameter validation and sanitization
- **SQL Injection Prevention**: Improved database query security
- **XSS Protection**: Better output escaping in templates
- **Audit Trail**: Enhanced tracking of who created/modified records and when

---

## [1.4] - 2024-10-27

### Added
- **Lecturer Management System**
  - Add external lecturers with complete profile information
  - Edit existing lecturer details
  - Delete lecturers with cascade cleanup of course enrollments
  - Paginated lecturer listing with configurable page sizes

- **Course Enrollment Management**
  - Enroll lecturers in existing Moodle courses
  - Track costs associated with each lecturer-course assignment
  - Prevent duplicate enrollments
  - Automatic course count tracking per lecturer

- **User Interface**
  - Tabbed interface for lecturers and course enrollments
  - Modal dialogs for data entry and editing
  - AJAX-powered operations for smooth user experience
  - Responsive design with Bootstrap compatibility

- **Data Export**
  - CSV export functionality for lecturer data
  - CSV export functionality for course enrollment data
  - Integration with theme-specific export utilities

- **Internationalization**
  - English language support (primary)
  - Arabic language structure prepared
  - Mustache template-based string rendering

### Database Schema
- **externallecturer** table for lecturer profiles
  - Fields: id, name, age, specialization, organization, degree, passport, courses_count
- **externallecturer_courses** table for course enrollments
  - Fields: id, lecturerid, courseid, cost
  - Foreign key relationships to lecturer and course tables

### Technical Implementation
- **PHP Classes**
  - Custom renderer extending plugin_renderer_base
  - Proper Moodle namespace usage
  
- **AJAX Endpoints**
  - addlecturer.php - Add new lecturers
  - editlecturer.php - Update lecturer information
  - deletelecturer.php - Remove lecturers
  - addenrollment.php - Enroll lecturers in courses
  - deletecourse.php - Remove course enrollments

- **Frontend**
  - JavaScript-based interface management (main.js)
  - Mustache templates for rendering
  - Modal dialog system integration

### Known Limitations
- Depends on theme-specific JavaScript files for CSV export and dialogs
- Currently requires manual permission management
- No built-in reporting beyond CSV export

---

## [1.6.1] - 2025-08-14

### Added
- **Lecturer Type Field**: Added `lecturer_type` field to database schema to distinguish between external visitors and residents
- **Nationality Field**: Added `nationality` field for enhanced lecturer profile information
- **Oracle DUNIA Integration**: Enhanced integration with Oracle database for data retrieval

### Changed
- Database schema updated to support lecturer type classification
- Enhanced backend support for dual lecturer types in add/edit operations
- Version number updated to 2025081400 (1.6.1)

---

## Future Versions

### Planned Features (Next Release)
- Enhanced reporting dashboard
- Advanced search and filtering
- Bulk operations for lecturer management
- API endpoints for external integrations
- Event logging and audit trail

### Long-term Roadmap
- Integration with Moodle's user management system
- Calendar integration for scheduling
- Advanced cost calculation and reporting
- Mobile app compatibility
- Multi-language support expansion

---

## Version History Template

```
## [Version Number] - YYYY-MM-DD

### Added
- New features

### Changed
- Changes in existing functionality

### Deprecated
- Soon-to-be removed features

### Removed
- Now removed features

### Fixed
- Bug fixes

### Security
- Security vulnerabilities fixed
```

---

## Contributing to Changelog

When contributing to this plugin, please update this changelog with:

1. **Version Number**: Follow semantic versioning (MAJOR.MINOR.PATCH)
2. **Release Date**: Use ISO date format (YYYY-MM-DD)
3. **Categories**: Use Added, Changed, Deprecated, Removed, Fixed, Security
4. **Clear Descriptions**: Write clear, concise descriptions of changes
5. **User Impact**: Focus on how changes affect end users

## Upgrade Notes

### From Version 1.4 to 1.5
- Database upgrade will automatically add the new `civil_number` field
- No manual intervention required
- All existing data will be preserved

### Database Migrations
Database schema changes will be documented here with each release that requires them.

### Configuration Changes
Any required configuration updates will be noted here. 
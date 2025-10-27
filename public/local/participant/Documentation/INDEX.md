# Documentation Index

Welcome to the **Participant Management Plugin** documentation. This folder contains comprehensive documentation for understanding, developing, and maintaining the plugin.

## Documentation Structure

### 📋 [README.md](README.md)
**Main Overview Document**
- Plugin overview and key features
- Quick start guide
- Basic file structure
- Plugin metadata and requirements

### 🏗️ [FILE_STRUCTURE.md](FILE_STRUCTURE.md)
**Detailed File Structure Reference**
- Complete explanation of every file and directory
- File dependencies and relationships
- Integration points with Moodle core
- External dependencies and requirements

### 💻 [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md)
**Development Guidelines and Best Practices**
- Development environment setup
- Code standards and conventions
- Database management procedures
- Testing guidelines and checklists
- Security best practices
- Deployment procedures

### ⚙️ [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md)
**Workflow System Documentation**
- Complete workflow architecture explanation
- Status definitions and transitions
- Capability system and permissions
- Implementation details and examples
- Customization guide
- Troubleshooting guide

### 📚 [API_REFERENCE.md](API_REFERENCE.md)
**API Reference Documentation**
- Core class documentation
- Method signatures and examples
- Database API usage patterns
- JavaScript API reference
- Template and language string APIs
- Error handling patterns

## Quick Navigation

### For New Developers
1. Start with [README.md](README.md) for overview
2. Review [FILE_STRUCTURE.md](FILE_STRUCTURE.md) to understand the codebase
3. Follow [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) for setup and standards

### For System Administrators
1. Read [README.md](README.md) for plugin information
2. Check [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md) for approval process
3. Use [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) for deployment procedures

### For Developers Working on Features
1. Use [API_REFERENCE.md](API_REFERENCE.md) for technical details
2. Follow [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) for standards
3. Refer to [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md) for workflow modifications

### For Troubleshooting
1. Check [WORKFLOW_SYSTEM.md](WORKFLOW_SYSTEM.md) for workflow issues
2. Use [DEVELOPMENT_GUIDE.md](DEVELOPMENT_GUIDE.md) for general debugging
3. Refer to [API_REFERENCE.md](API_REFERENCE.md) for error handling

## Plugin Information Summary

- **Name**: Participant Management Plugin
- **Component**: `local_participant`
- **Version**: 2.2 (2025081800)
- **Moodle Requirement**: 4.1+
- **Status**: Stable
- **Languages**: English, Arabic

## Key Features

- ✅ Multi-level approval workflow system
- ✅ Role-based permission system
- ✅ Bilingual interface (EN/AR)
- ✅ Request filtering and pagination
- ✅ CSV export functionality
- ✅ Audit trail on requests (creator/last modifier and timestamps)
- ✅ Form validation and error handling

## Getting Help

### Documentation Issues
If you find issues with this documentation:
1. Check if the information is outdated
2. Verify against the current codebase
3. Update the relevant documentation file
4. Follow the development guidelines for changes

### Code Issues
For code-related problems:
1. Enable debug mode in Moodle
2. Check the troubleshooting sections
3. Review the API reference for proper usage
4. Test in a development environment first

### Workflow Issues
For workflow-related problems:
1. Check user capabilities and role assignments
2. Verify status plugin is properly configured
3. Review workflow documentation
4. Test workflow transitions manually

## Contributing to Documentation

When updating documentation:
1. Keep information current with the codebase
2. Use clear, concise language
3. Include practical examples
4. Update this index if adding new files
5. Follow the established format and structure

## Document Maintenance

This documentation should be updated whenever:
- New features are added to the plugin
- Existing functionality is modified
- Database schema changes are made
- New API methods are introduced
- Workflow processes are updated

---

*Last Updated: August 18, 2025*  
*Plugin Version: 2.2 (2025081800)*

Recent changes:

- 2025-08-18: Added upgrade step `2025081800` — inserts First/Second/Third Period lecturer request types and removes legacy single-lecturer entry (id=4). Administrators should run Site administration -> Notifications to apply the upgrade and verify the `local_participant_request_types` table after the upgrade.

Runtime behaviour change (hardcoded ID):

- The plugin code was updated to treat participant type id `7` as the canonical External Lecturer type at runtime. This is a code-level (hardcoded) change — existing database rows using id `5` will no longer be treated as external lecturers unless you migrate them.

Recommended migration steps:

1. Backup your database.
2. Insert a new `local_participant_request_types` record with id=7 copying values from the existing id=5 record.
3. Run an UPDATE: `UPDATE {local_participant_requests} SET participant_type_id = 7 WHERE participant_type_id = 5;`
4. Verify functionality and then optionally delete the old id=5 type when safe.

If you prefer, I can implement an automatic migration in `db/upgrade.php` to perform steps 2-3 during upgrade `2025081800`.

## Recent Updates

### September 3, 2025
- ✅ **JavaScript Integration Fixed**: AJAX URLs updated to work correctly from any plugin context
- ✅ **Cross-Plugin Integration**: Successfully integrated with requestservices plugin for seamless functionality
- ✅ **Dropdown Functionality**: Employee and lecturer dropdowns now working correctly with data population

### August 18, 2025
- ✅ **Workflow System Migration**: Completed workflow system migration with new period-based request types
- ✅ **Database Upgrade**: Added upgrade step 2025081800 for new lecturer request types
- ✅ **Workflow Rejection Behavior Updated**: Modified rejection flow so that rejections from Leader 2, 3, and Boss levels return to Leader 1 Review for re-evaluation, while Leader 1 rejections go directly to final rejection status